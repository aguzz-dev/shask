<?php

namespace App\Console\Commands;

use App\Database;
use App\Services\CreatorPushCopy;
use App\Services\PushNotifier;
use Illuminate\Console\Command;

/**
 * Pieza 3 (D4.2) of the creator-acquisition push model: daily digest for
 * creators whose acquisitions today crossed `marketplace.push_digest_threshold`.
 * Runs at 20:30 (routes/console.php), inside active hours.
 *
 * Reconciled behaviour (gate-review): a creator with acquired_count >= N
 * gets the individual push from Pieza 1 (first acquisition) PLUS this
 * digest — never digest-only, never more than one of each per day
 * (cap <=2/day via single_sent + digest_sent).
 */
class SendCreatorDigest extends Command
{
    protected $signature = 'push:creator-digest';
    protected $description = 'Envía el digest diario de adquisiciones a creadores que superan el umbral';

    public function handle(PushNotifier $push): int
    {
        $threshold = (int) config('marketplace.push_digest_threshold', 3);
        $db        = new Database;

        $stmt = $db->dbConnection->prepare(
            "SELECT creator_user_id, acquired_count FROM creator_push_log
             WHERE day = CURDATE() AND digest_sent = 0 AND acquired_count >= ?"
        );
        $stmt->bind_param('i', $threshold);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as $row) {
            $creatorId = (int) $row['creator_user_id'];
            $count     = (int) $row['acquired_count'];

            $sent = $push->sendToUser(
                $creatorId,
                CreatorPushCopy::DIGEST_TITLE,
                CreatorPushCopy::digestBody($count),
                ['type' => 'asset_acquired', 'count' => $count],
            );

            if (!$sent) {
                continue;
            }

            $updateStmt = $db->dbConnection->prepare(
                "UPDATE creator_push_log SET digest_sent = 1, last_push_at = NOW()
                 WHERE creator_user_id = ? AND day = CURDATE()"
            );
            $updateStmt->bind_param('i', $creatorId);
            $updateStmt->execute();
            $updateStmt->close();
        }

        return self::SUCCESS;
    }
}
