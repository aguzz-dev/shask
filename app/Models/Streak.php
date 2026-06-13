<?php
namespace App\Models;

use App\Database;

class Streak extends Database
{
    /**
     * Marca "hoy tuviste actividad de buzón" para la racha.
     * Idempotente por día: renovar/crear N veces en el día cuenta una vez.
     */
    public function touch(int $userId): void
    {
        $user = $this->query(
            "SELECT streak_days, streak_date FROM users WHERE id = {$userId}"
        )->fetch_assoc();
        if (!$user) {
            return;
        }
        $today = date('Y-m-d');
        if ($user['streak_date'] === $today) {
            return;
        }
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $days = ($user['streak_date'] === $yesterday) ? ((int) $user['streak_days']) + 1 : 1;
        $this->query("UPDATE users SET streak_days = {$days}, streak_date = '{$today}' WHERE id = {$userId}");
    }
}
