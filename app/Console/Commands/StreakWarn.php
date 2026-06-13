<?php
namespace App\Console\Commands;

use App\Database;
use App\Services\PushNotifier;
use Illuminate\Console\Command;

class StreakWarn extends Command
{
    protected $signature = 'streak:warn';
    protected $description = 'Avisa a usuarios cuya racha se rompe hoy si no renuevan';

    public function handle(PushNotifier $push): int
    {
        $db = new Database;
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        // Racha viva hasta ayer, sin ningún buzón activo ahora
        $rows = $db->query("SELECT u.id, u.streak_days FROM users u
            WHERE u.streak_days > 0 AND u.streak_date = '{$yesterday}'
            AND NOT EXISTS (SELECT 1 FROM posts p WHERE p.user_id = u.id AND p.expires_at > NOW())")
            ->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as $u) {
            $push->sendToUser((int) $u['id'], "🔥 Racha de {$u['streak_days']} días en riesgo",
                'Creá o reviví un buzón antes de medianoche para salvarla', ['kind' => 'streak_risk']);
        }
        return self::SUCCESS;
    }
}
