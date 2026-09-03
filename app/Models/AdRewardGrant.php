<?php

namespace App\Models;

use App\Database;

/**
 * Ledger access for AdMob rewarded grants. Security-critical: every write uses
 * prepared statements (never string interpolation) because nonce, transaction
 * id and reward fields originate from untrusted input (client + AdMob callback).
 *
 * Lifecycle: pending (issued) -> verified (SSV signature OK) -> consumed (spent).
 */
class AdRewardGrant extends Database
{
    public const PENDING  = 'pending';
    public const VERIFIED = 'verified';
    public const CONSUMED = 'consumed';

    // consume() outcomes.
    public const OK        = 'ok';        // verified -> consumed
    public const PENDING_R = 'pending';   // exists but SSV not yet confirmed
    public const NOT_FOUND = 'not_found'; // no matching spendable grant

    /** Issue a fresh single-use nonce bound to the user, purpose and target. */
    public function issueNonce(int $userId, string $purpose, ?int $referenceId): string
    {
        $nonce = bin2hex(random_bytes(24)); // 48 hex chars

        $stmt = $this->dbConnection->prepare(
            'INSERT INTO ad_reward_grants (user_id, nonce, purpose, reference_id, status, created_at)
             VALUES (?, ?, ?, ?, ?, NOW())'
        );
        $status = self::PENDING;
        $stmt->bind_param('issis', $userId, $nonce, $purpose, $referenceId, $status);
        $stmt->execute();
        $stmt->close();

        return $nonce;
    }

    /**
     * Flip a pending grant to verified after an SSV signature check.
     * Idempotent + replay-safe: the unique transaction_id blocks a repeated
     * callback, and only rows still 'pending' are advanced.
     */
    public function markVerified(string $nonce, string $transactionId, ?string $rewardItem, ?int $rewardAmount): bool
    {
        $stmt = $this->dbConnection->prepare(
            "UPDATE ad_reward_grants
                SET status = ?, transaction_id = ?, reward_item = ?, reward_amount = ?, verified_at = NOW()
              WHERE nonce = ? AND status = ?"
        );
        $verified = self::VERIFIED;
        $pending  = self::PENDING;
        $stmt->bind_param('sssiss', $verified, $transactionId, $rewardItem, $rewardAmount, $nonce, $pending);
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();

        return $changed === 1;
    }

    /**
     * Atomically spend a verified grant. Returns one of OK / PENDING_R /
     * NOT_FOUND so the caller can answer 200 / retry / reject respectively.
     * The nonce must match user, purpose, optional target and still be within
     * its TTL — otherwise it is treated as not spendable.
     */
    public function consume(int $userId, string $nonce, string $purpose, ?int $referenceId): string
    {
        $ttl = (int) config('admob.nonce_ttl_minutes', 30);

        // Bind target only when the caller pins one; a null reference_id grant
        // is usable for any target of that purpose.
        $refClause = $referenceId === null
            ? ''
            : ' AND (reference_id IS NULL OR reference_id = ?)';

        $sql = "UPDATE ad_reward_grants
                   SET status = ?, consumed_at = NOW()
                 WHERE nonce = ? AND user_id = ? AND purpose = ? AND status = ?
                   AND created_at >= (NOW() - INTERVAL {$ttl} MINUTE)" . $refClause;

        $stmt = $this->dbConnection->prepare($sql);
        $consumed = self::CONSUMED;
        $verified = self::VERIFIED;
        if ($referenceId === null) {
            $stmt->bind_param('ssiss', $consumed, $nonce, $userId, $purpose, $verified);
        } else {
            $stmt->bind_param('ssissi', $consumed, $nonce, $userId, $purpose, $verified, $referenceId);
        }
        $stmt->execute();
        $changed = $stmt->affected_rows;
        $stmt->close();

        if ($changed === 1) {
            return self::OK;
        }

        // Not spendable — disambiguate for the caller.
        $stmt = $this->dbConnection->prepare(
            'SELECT status FROM ad_reward_grants WHERE nonce = ? AND user_id = ? AND purpose = ? LIMIT 1'
        );
        $stmt->bind_param('sis', $nonce, $userId, $purpose);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($row && $row['status'] === self::PENDING) {
            return self::PENDING_R;
        }

        return self::NOT_FOUND;
    }
}
