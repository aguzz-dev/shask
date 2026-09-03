<?php

use App\Services\RewardVerifier;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Anti-fraud core: AdMob rewarded SSV signature verification. These prove a
 * cloned/modified client cannot fake an ad watch — only Google's real signed
 * callback verifies, and any tampering (a swapped nonce) is rejected.
 */

/** @return array{0: string, 1: string} [privatePem, publicPem] */
function makeEcKeyPair(): array
{
    $res = openssl_pkey_new([
        'private_key_type' => OPENSSL_KEYTYPE_EC,
        'curve_name' => 'prime256v1',
    ]);
    openssl_pkey_export($res, $privatePem);
    $details = openssl_pkey_get_details($res);

    return [$privatePem, $details['key']];
}

function urlSafeSign(string $content, string $privatePem): string
{
    openssl_sign($content, $rawSig, $privatePem, OPENSSL_ALGO_SHA256);

    return strtr(base64_encode($rawSig), '+/', '-_');
}

beforeEach(function () {
    Cache::flush();
});

it('accepts a correctly signed SSV callback', function () {
    [$privatePem, $publicPem] = makeEcKeyPair();
    $keyId = '3335741209';

    Http::fake([
        '*' => Http::response(['keys' => [
            ['keyId' => $keyId, 'pem' => $publicPem],
        ]]),
    ]);

    $content = 'ad_network=5450213213286189855&ad_unit=1234&custom_data=nonce-abc'
        .'&reward_amount=1&reward_item=coins&timestamp=1700000000000'
        .'&transaction_id=tx-1&user_id=42';

    $signature = urlSafeSign($content, $privatePem);

    expect((new RewardVerifier)->verify($content, $signature, $keyId))->toBeTrue();
});

it('rejects a tampered payload — the fake-ad attack', function () {
    [$privatePem, $publicPem] = makeEcKeyPair();
    $keyId = '3335741209';

    Http::fake([
        '*' => Http::response(['keys' => [
            ['keyId' => $keyId, 'pem' => $publicPem],
        ]]),
    ]);

    $content = 'custom_data=nonce-legit&user_id=42';
    $signature = urlSafeSign($content, $privatePem);

    // Attacker reuses a real signature but swaps in a different nonce.
    $tampered = 'custom_data=nonce-evil&user_id=42';

    expect((new RewardVerifier)->verify($tampered, $signature, $keyId))->toBeFalse();
});

it('rejects an unknown key id', function () {
    [$privatePem, $publicPem] = makeEcKeyPair();

    Http::fake([
        '*' => Http::response(['keys' => [
            ['keyId' => 'known-key', 'pem' => $publicPem],
        ]]),
    ]);

    $content = 'custom_data=n&user_id=1';
    $signature = urlSafeSign($content, $privatePem);

    expect((new RewardVerifier)->verify($content, $signature, 'unknown-key'))->toBeFalse();
});

it('rejects a signature from a different key pair', function () {
    [, $publicPem] = makeEcKeyPair();
    [$attackerPrivate] = makeEcKeyPair();
    $keyId = '3335741209';

    Http::fake([
        '*' => Http::response(['keys' => [
            ['keyId' => $keyId, 'pem' => $publicPem],
        ]]),
    ]);

    $content = 'custom_data=n&user_id=1';
    // Signed with the attacker's key, not the one Google published.
    $signature = urlSafeSign($content, $attackerPrivate);

    expect((new RewardVerifier)->verify($content, $signature, $keyId))->toBeFalse();
});
