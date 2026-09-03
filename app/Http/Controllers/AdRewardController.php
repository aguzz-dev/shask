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

        // Reachability/validation probe: AdMob (and manual curl) may hit the URL
        // with no params to confirm it exists. Answer 200 so the callback URL is
        // accepted — real callbacks always carry all three fields.
        if ($signature === '' && $keyId === '' && $nonce === '') {
            return response()->json(['ok' => true]);
        }

        if ($signature === '' || $keyId === '' || $nonce === '') {
            return response()->json(['message' => 'callback incompleto'], 400);
        }

        // The signed content is the raw query string up to `&signature=...`.
        $queryString = (string) $request->server('QUERY_STRING', '');
        $sigMarker   = strpos($queryString, '&signature=');
        if ($sigMarker === false) {
            return response()->json(['message' => 'query inválida'], 400);
        }
        $contentToVerify = substr($queryString, 0, $sigMarker);

        if (!$verifier->verify($contentToVerify, $signature, $keyId)) {
            return response()->json(['message' => 'firma inválida'], 400);
        }

        $transactionId = (string) $request->query('transaction_id', $nonce);
        $rewardItem    = $request->query('reward_item');
        $rewardAmount  = $request->query('reward_amount') !== null
            ? (int) $request->query('reward_amount')
            : null;

        (new AdRewardGrant)->markVerified($nonce, $transactionId, $rewardItem, $rewardAmount);

        // Ack even if the nonce was already verified (idempotent) so AdMob stops retrying.
        return response()->json(['ok' => true]);
    }
}
