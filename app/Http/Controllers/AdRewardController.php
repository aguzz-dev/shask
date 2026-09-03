<?php

namespace App\Http\Controllers;

use App\Models\AdRewardGrant;
use App\Models\PersonalAccessToken;
use App\Services\RewardVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Rewarded-ad anti-fraud endpoints.
 *
 *  1. POST /api/ad-reward/nonce  (auth) — issues a single-use nonce the client
 *     passes to AdMob as custom_data before showing the rewarded ad.
 *  2. GET  /api/ad-reward/ssv    (public) — AdMob's signed Server-Side
 *     Verification callback. Verifies Google's signature and marks the matching
 *     nonce as verified, making it spendable exactly once.
 *
 * The spend itself happens in the gated action (post create / lifecycle), which
 * atomically consumes the verified grant.
 */
class AdRewardController extends Controller
{
    private const PURPOSES = ['asset_use', 'extend', 'unlock', 'revive'];

    public function nonce(Request $request): JsonResponse
    {
        $userId = (int) $request->id;

        $check = (new PersonalAccessToken)->validateToken($request->bearerToken(), $userId);
        if ($check !== true) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $purpose = (string) $request->purpose;
        if (!in_array($purpose, self::PURPOSES, true)) {
            return response()->json(['message' => 'purpose inválido'], 422);
        }

        $referenceId = $request->reference_id !== null ? (int) $request->reference_id : null;

        $nonce = (new AdRewardGrant)->issueNonce($userId, $purpose, $referenceId);

        return response()->json(['nonce' => $nonce]);
    }

    public function ssv(Request $request, RewardVerifier $verifier): JsonResponse
    {
        $signature = (string) $request->query('signature', '');
        $keyId     = (string) $request->query('key_id', '');
        $nonce     = (string) $request->query('custom_data', '');

        // AdMob's URL validation (and a bare curl) may omit the signature or the
        // custom_data nonce. Acknowledge with 200 so the callback URL is accepted
        // and AdMob doesn't retry — nothing is ever granted without a valid Google
        // signature, so a 200 here carries no security cost.
        if ($signature === '' || $keyId === '') {
            return response()->json(['ok' => true]);
        }

        // The signed content is the raw query string up to `&signature=...`.
        $queryString     = (string) $request->server('QUERY_STRING', '');
        $sigMarker       = strpos($queryString, '&signature=');
        $contentToVerify = $sigMarker === false ? '' : substr($queryString, 0, $sigMarker);

        // Only a signature-valid callback carrying a nonce marks a grant as
        // spendable. The HTTP code is always 200 (an acknowledgement); the real
        // gate is that consume() requires status=verified, set only here.
        if ($contentToVerify !== '' && $nonce !== ''
            && $verifier->verify($contentToVerify, $signature, $keyId)) {
            $transactionId = (string) $request->query('transaction_id', $nonce);
            $rewardItem    = $request->query('reward_item');
            $rewardAmount  = $request->query('reward_amount') !== null
                ? (int) $request->query('reward_amount')
                : null;

            (new AdRewardGrant)->markVerified($nonce, $transactionId, $rewardItem, $rewardAmount);
        }

        return response()->json(['ok' => true]);
    }
}
