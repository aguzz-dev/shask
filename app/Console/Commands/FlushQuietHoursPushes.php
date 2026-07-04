<?php

namespace App\Console\Commands;

use App\Database;
use App\Services\CreatorPushCopy;
use App\Services\PushNotifier;
use App\Services\QuietHours;
use Illuminate\Console\Command;

/**
 * Pieza 2 (D4.2) of the creator-acquisition push model: delivers individual
 * pushes that were deferred during quiet-hours, once the window has closed.
 * Runs every 15 minutes (routes/console.php) — same pattern as
 * NotifyPostLifecycle.
 */
class FlushQuietHoursPushes extends Command
{
    protected $signature = 'push:flush-quiet-hours';
    protected $description = 'Envía los pushes de adquisición diferidos por quiet-hours una vez cerrada la ventana';

    public function handle(PushNotifier $push): int
    {
        if (QuietHours::isNow()) {
            // Still inside the window — nothing to flush yet.
            return self::SUCCESS;
        }

        $db = new Database;

        $rows = $db->query(
            "SELECT creator_user_id FROM creator_push_log WHERE deferred_pending = 1"
        )->fetch_all(MYSQLI_ASSOC);

        foreach ($rows as $row) {
            $creatorId = (int) $row['creator_user_id'];

            $sent = $push->sendToUser(
                $creatorId,
                CreatorPushCopy::SINGLE_TITLE,
                CreatorPushCopy::SINGLE_BODY,
                ['type' => 'asset_acquired'],
            );

            if (!$sent) {
                // Opt-out respected — leave deferred_pending as-is so a
                // future flush run can retry once the creator has a token.
                continue;
            }

            $stmt = $db->dbConnection->prepare(
                "UPDATE creator_push_log
                 SET single_sent = 1, deferred_pending = 0, last_push_at = NOW()
                 WHERE creator_user_id = ? AND deferred_pending = 1"
            );
            $stmt->bind_param('i', $creatorId);
            $stmt->execute();
            $stmt->close();
        }

        return self::SUCCESS;
    }
}
