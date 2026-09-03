<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Revamp the achievement catalog for the ad-consumable model:
 *  - Drop the hype-balance achievements (hype_1k / hype_10k) — hype is no
 *    longer earned by regular users, so they were unreachable.
 *  - Refresh names to punchier, on-brand copy.
 *  - Clear the `reward` field ("+N hype") everywhere — rewards are gone.
 *
 * Idempotent: upserts by unique `code`, safe to run repeatedly. Runs on deploy.
 */
return new class extends Migration
{
    public function up(): void
    {
        $catalog = [
            ['first_mailbox',    20, '📬', 'Tu primer buzón',              'Your first mailbox'],
            ['first_question',   30, '📨', 'Te llegó la primera',          'First one in'],
            ['answered_10',      40, '💬', 'Arrancaste a responder',       'Started replying'],
            ['answered_50',      60, '🗣️', 'No dejás una sin responder',    'No message left unanswered'],
            ['received_100',     70, '📥', '100 en el buzón',              '100 in the box'],
            ['mailbox_exploded', 80, '💥', 'Se te llenó el buzón',         'Your box blew up'],
            ['custom_avatar',    10, '🙂', 'Pusiste tu cara',              'Put your face on it'],
            ['reach_100',        35, '👀', '100 miradas',                  '100 looks'],
            ['reach_1k',         55, '📈', 'Mil vistas',                   'A thousand views'],
            ['reach_10k',        75, '🌍', 'Diez mil vistas',              'Ten thousand views'],
            ['unique_50',        33, '🔎', '50 curiosos',                  '50 curious minds'],
            ['unique_500',       53, '👥', '500 curiosos',                 '500 curious minds'],
            ['viral_post',       65, '⚡', 'Se volvió viral',              'Went viral'],
            ['conversion_ace',   68, '✨', 'Todos te preguntan',           'Everyone asks you'],
            ['streak_3',         25, '🔥', 'Tres días seguidos',           'Three days straight'],
            ['streak_7',         45, '📅', 'Una semana entera',            'A full week'],
            ['streak_30',        72, '🏅', 'Un mes sin fallar',            'A month straight'],
            ['first_revive',     28, '↩️', 'Lo reviviste',                 'Brought it back'],
        ];

        foreach ($catalog as [$code, $weight, $emoji, $nameEs, $nameEn]) {
            DB::statement(
                "INSERT INTO achievements (code, weight, emoji, name_es, name_en, active, reward)
                 VALUES (?, ?, ?, ?, ?, 1, NULL)
                 ON DUPLICATE KEY UPDATE
                     weight  = VALUES(weight),
                     emoji   = VALUES(emoji),
                     name_es = VALUES(name_es),
                     name_en = VALUES(name_en),
                     active  = 1,
                     reward  = NULL",
                [$code, $weight, $emoji, $nameEs, $nameEn]
            );
        }

        // Retire the hype achievements — no longer earnable.
        DB::statement("UPDATE achievements SET active = 0 WHERE code IN ('hype_1k', 'hype_10k')");
    }

    public function down(): void
    {
        // Non-destructive: re-enable the hype achievements. Names/rewards are
        // not restored (that data lived only in the pre-revamp seeder).
        DB::statement("UPDATE achievements SET active = 1 WHERE code IN ('hype_1k', 'hype_10k')");
    }
};
