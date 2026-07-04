<?php

namespace App\Services;

use App\Database;

/**
 * Pieza 1 (real-time) of the creator-acquisition push model (D4.2).
 *
 * Called after a successful UGC asset purchase, outside the purchase
 * transaction, wrapped in try/catch by the caller (D4.1 — a push failure
 * must never fail the purchase).
 *
 * Cap: <=2 pushes/creator/day, enforced via `single_sent` + `digest_sent`
 * flags on `creator_push_log`. Quiet-hours pushes are deferred (never
 * discarded) — `push:flush-quiet-hours` delivers them once the window
 * closes. The daily digest (Pieza 3) is handled by `push:creator-digest`,
 * not here.
 */
class CreatorAcquisitionNotifier
{
    public function __construct(private readonly PushNotifier $push)
    {
    }

    public function notify(int $creatorId, ?int $assetId = null): void
    {
        $db = new Database;

        // Upsert today's row for this creator, incrementing acquired_count.
        $stmt = $db->dbConnection->prepare(
            "INSERT INTO creator_push_log (creator_user_id, day, acquired_count)
             VALUES (?, CURDATE(), 1)
             ON DUPLICATE KEY UPDATE acquired_count = acquired_count + 1"
        );
        $stmt->bind_param('i', $creatorId);
        $stmt->execute();
        $stmt->close();

        $rowStmt = $db->dbConnection->prepare(
            "SELECT single_sent, deferred_pending FROM creator_push_log
             WHERE creator_user_id = ? AND day = CURDATE()"
        );
        $rowStmt->bind_param('i', $creatorId);
        $rowStmt->execute();
        $row = $rowStmt->get_result()->fetch_assoc();
        $rowStmt->close();

        if ($row === null || (int) $row['single_sent'] === 1 || (int) $row['deferred_pending'] === 1) {
            // Already sent, or already deferred and awaiting flush — the
            // digest (Pieza 3) will cover further acquisitions today.
            return;
        }

        if (QuietHours::isNow()) {
            $deferStmt = $db->dbConnection->prepare(
                "UPDATE creator_push_log SET deferred_pending = 1
                 WHERE creator_user_id = ? AND day = CURDATE()"
            );
            $deferStmt->bind_param('i', $creatorId);
            $deferStmt->execute();
            $deferStmt->close();
            return;
        }

        $data = ['type' => 'asset_acquired'];
        if ($assetId !== null) {
            $data['asset_id'] = $assetId;
        }

        $sent = $this->push->sendToUser(
            $creatorId,
            CreatorPushCopy::SINGLE_TITLE,
            CreatorPushCopy::SINGLE_BODY,
            $data,
        );

        // Opt-out is respected (creator-acquisition-push / Opt-Out Is
        // Respected): if sendToUser returns false (no fcm_token), the flag
        // is left unset so a later acquisition today can still try once the
        // creator has a token — the purchase itself never fails either way.
        if (!$sent) {
            return;
        }

        $sentStmt = $db->dbConnection->prepare(
            "UPDATE creator_push_log SET single_sent = 1, last_push_at = NOW()
             WHERE creator_user_id = ? AND day = CURDATE()"
        );
        $sentStmt->bind_param('i', $creatorId);
        $sentStmt->execute();
        $sentStmt->close();
    }
}
