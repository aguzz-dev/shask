<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seed (upsert) del catálogo de logros.
 * ON DUPLICATE KEY UPDATE garantiza idempotencia: correr el seeder
 * dos veces no duplica rows.
 */
class AchievementSeeder extends Seeder
{
    public function run(): void
    {
        $achievements = [
            // ── Logros originales (actualizados con reward) ────────────────────
            ['code' => 'hype_10k',         'weight' => 90, 'emoji' => '👑', 'name_es' => '10K de hype',          'name_en' => '10K hype',            'reward' => '+50 🔥'],
            ['code' => 'mailbox_exploded',  'weight' => 80, 'emoji' => '💥', 'name_es' => 'Buzón explotado',      'name_en' => 'Exploded mailbox',     'reward' => '+10 🔥'],
            ['code' => 'received_100',      'weight' => 70, 'emoji' => '🚀', 'name_es' => '100 recibidas',        'name_en' => '100 received',         'reward' => '+5 🔥'],
            ['code' => 'answered_50',       'weight' => 60, 'emoji' => '🤐', 'name_es' => '50 respondidas',       'name_en' => '50 answered',          'reward' => '+5 🔥'],
            ['code' => 'hype_1k',           'weight' => 50, 'emoji' => '🔥', 'name_es' => '1K de hype',           'name_en' => '1K hype',              'reward' => '+10 🔥'],
            ['code' => 'answered_10',       'weight' => 40, 'emoji' => '💬', 'name_es' => '10 respondidas',       'name_en' => '10 answered',          'reward' => '+2 🔥'],
            ['code' => 'first_question',    'weight' => 30, 'emoji' => '📨', 'name_es' => 'Primera pregunta',     'name_en' => 'First question',       'reward' => '+1 🔥'],
            ['code' => 'first_mailbox',     'weight' => 20, 'emoji' => '📬', 'name_es' => 'Primer buzón',         'name_en' => 'First mailbox',        'reward' => '+1 🔥'],
            ['code' => 'custom_avatar',     'weight' => 10, 'emoji' => '🎨', 'name_es' => 'Avatar personalizado', 'name_en' => 'Custom avatar',        'reward' => '+2 🔥'],

            // ── Alcance de vistas (Slice C) ────────────────────────────────────
            ['code' => 'reach_100',         'weight' => 35, 'emoji' => '👀', 'name_es' => '100 visitas',          'name_en' => '100 views',            'reward' => '+2 🔥'],
            ['code' => 'reach_1k',          'weight' => 55, 'emoji' => '🌟', 'name_es' => '1K visitas',           'name_en' => '1K views',             'reward' => '+5 🔥'],
            ['code' => 'reach_10k',         'weight' => 75, 'emoji' => '🌍', 'name_es' => '10K visitas',          'name_en' => '10K views',            'reward' => '+20 🔥'],

            // ── Visitas únicas (Slice C) ───────────────────────────────────────
            ['code' => 'unique_50',         'weight' => 33, 'emoji' => '🔍', 'name_es' => '50 visitas únicas',    'name_en' => '50 unique views',      'reward' => '+2 🔥'],
            ['code' => 'unique_500',        'weight' => 53, 'emoji' => '🎯', 'name_es' => '500 visitas únicas',   'name_en' => '500 unique views',     'reward' => '+10 🔥'],

            // ── Post viral (Slice C) ───────────────────────────────────────────
            ['code' => 'viral_post',        'weight' => 65, 'emoji' => '📡', 'name_es' => 'Post viral',           'name_en' => 'Viral post',           'reward' => '+15 🔥'],

            // ── Conversión (Slice C) ───────────────────────────────────────────
            ['code' => 'conversion_ace',    'weight' => 68, 'emoji' => '🃏', 'name_es' => 'As de la conversión',  'name_en' => 'Conversion ace',       'reward' => '+10 🔥'],

            // ── Racha (Slice C) ────────────────────────────────────────────────
            ['code' => 'streak_3',          'weight' => 25, 'emoji' => '🔥', 'name_es' => 'Racha de 3 días',      'name_en' => '3-day streak',         'reward' => '+3 🔥'],
            ['code' => 'streak_7',          'weight' => 45, 'emoji' => '⚡', 'name_es' => 'Racha de 7 días',      'name_en' => '7-day streak',         'reward' => '+7 🔥'],
            ['code' => 'streak_30',         'weight' => 72, 'emoji' => '🏆', 'name_es' => 'Racha de 30 días',     'name_en' => '30-day streak',        'reward' => '+30 🔥'],

            // ── Primer revive (Slice C) ────────────────────────────────────────
            ['code' => 'first_revive',      'weight' => 28, 'emoji' => '💫', 'name_es' => 'Primera revivida',     'name_en' => 'First revival',        'reward' => '+5 🔥'],
        ];

        foreach ($achievements as $data) {
            DB::statement(
                "INSERT INTO achievements (code, weight, emoji, name_es, name_en, active, reward)
                 VALUES (?, ?, ?, ?, ?, 1, ?)
                 ON DUPLICATE KEY UPDATE
                     weight  = VALUES(weight),
                     emoji   = VALUES(emoji),
                     name_es = VALUES(name_es),
                     name_en = VALUES(name_en),
                     reward  = VALUES(reward)",
                [
                    $data['code'],
                    $data['weight'],
                    $data['emoji'],
                    $data['name_es'],
                    $data['name_en'],
                    $data['reward'],
                ]
            );
        }
    }
}
