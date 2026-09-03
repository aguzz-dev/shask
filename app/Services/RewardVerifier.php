<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Verifies AdMob rewarded SSV callback signatures against Google's public keys.
 *
 * AdMob signs the callback with an ECDSA key; the signature covers the raw
 * query string up to (but excluding) the trailing `&signature=...&key_id=...`.
 * We fetch Google's rotating public keys, pick the one named by `key_id`, and
 * verify. A forged callback (cloned client) cannot produce a valid signature.
 *
 * Docs: https://developers.google.com/admob/android/ssv
 */
class RewardVerifier
{
    private const CACHE_KEY = 'admob_verifier_keys';

    /**
     * @param  string  $contentToVerify  Raw query string minus the trailing
     *                                    `&signature=...&key_id=...`.
     * @param  string  $signature        Base64url `signature` param value.
     * @param  string  $keyId            `key_id` param value.
     */
    public function verify(string $contentToVerify, string $signature, string $keyId): bool
    {
        $pem = $this->publicKeyPem($keyId);
        if ($pem === null) {
            return false;
        }

        // AdMob signatures are web-safe base64 (URL alphabet).
        $decoded = base64_decode(strtr($signature, '-_', '+/'), true);
        if ($decoded === false || $decoded === '') {
            return false;
        }

        return openssl_verify($contentToVerify, $decoded, $pem, OPENSSL_ALGO_SHA256) === 1;
    }

    private function publicKeyPem(string $keyId): ?string
    {
        return $this->keys()[$keyId] ?? null;
    }

    /**
     * keyId => PEM public key. Cached, but failures are never cached so a
     * transient fetch error doesn't lock out verification for the whole TTL.
     *
     * @return array<string, string>
     */
    private function keys(): array
    {
        $cached = Cache::get(self::CACHE_KEY);
        if (is_array($cached) && $cached !== []) {
            return $cached;
        }

        try {
            $response = Http::timeout(5)->get(config('admob.verifier_keys_url'));
        } catch (\Throwable $e) {
            return [];
        }

        $map = [];
        foreach (($response->json('keys') ?? []) as $key) {
            if (isset($key['keyId'], $key['pem'])) {
                $map[(string) $key['keyId']] = $key['pem'];
            }
        }

        if ($map !== []) {
            Cache::put(self::CACHE_KEY, $map, config('admob.verifier_keys_ttl'));
        }

        return $map;
    }
}
