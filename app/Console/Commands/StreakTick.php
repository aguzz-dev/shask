<?php
namespace App\Console\Commands;

use App\Database;
use App\Models\Streak;
use Illuminate\Console\Command;

class StreakTick extends Command
{
    protected $signature = 'streak:tick';
    protected $description = 'Mantiene la racha de usuarios con al menos un buzón activo hoy';

    public function handle(): int
    {
        $db = new Database;
        $rows = $db->query("SELECT DISTINCT user_id FROM posts WHERE expires_at > NOW()")
            ->fetch_all(MYSQLI_ASSOC);
        $streak = new Streak;
        foreach ($rows as $row) {
            $streak->touch((int) $row['user_id']);
        }
        return self::SUCCESS;
    }
}
