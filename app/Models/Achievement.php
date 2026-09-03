<?php
namespace App\Models;

use App\Database;

class Achievement extends Database
{
    protected $table = 'achievements';

    /** Catálogo activo ordenado del logro más importante al menos. */
    public function catalog(): array
    {
        return $this->query(
            "SELECT * FROM {$this->table} WHERE active = 1 ORDER BY weight DESC"
        )->fetch_all(MYSQLI_ASSOC);
    }

    /** Map code => unlocked_at de los logros ya desbloqueados. */
    public function unlockedFor(int $userId): array
    {
        $rows = $this->query(
            "SELECT a.code, au.unlocked_at FROM achievement_user au
             JOIN achievements a ON a.id = au.achievement_id
             WHERE au.user_id = {$userId}"
        )->fetch_all(MYSQLI_ASSOC);

        $map = [];
        foreach ($rows as $row) {
            $map[$row['code']] = $row['unlocked_at'];
        }
        return $map;
    }

    /**
     * Evalúa las condiciones, persiste los nuevos desbloqueos y devuelve
     * únicamente los codes recién desbloqueados en esta llamada (delta).
     *
     * Idempotente: si el usuario ya tenía todos los logros alcanzables, el
     * delta es un array vacío. Los logros ya desbloqueados no se modifican.
     *
     * @param  int   $userId
     * @param  array $stats  Resultado de UserStats::forUser() con las claves:
     *   questions_received, questions_answered, mailboxes_created, hype,
     *   has_custom_avatar, max_unread_in_a_mailbox, total_views,
     *   total_unique_views, max_unique_views_in_a_mailbox, best_conversion,
     *   first_revive (bool), streak_days.
     * @return array Codes recién desbloqueados, ej. ['reach_100', 'streak_3'].
     */
    public function evaluate(int $userId, array $stats): array
    {
        $conditions = [
            // ── Logros originales ─────────────────────────────────────────────
            // hype_1k / hype_10k retired in the ad-consumable model (hype is no
            // longer earned by regular users).
            'mailbox_exploded' => ($stats['max_unread_in_a_mailbox'] ?? 0) > 5,
            'received_100'     => ($stats['questions_received'] ?? 0) >= 100,
            'answered_50'      => ($stats['questions_answered'] ?? 0) >= 50,
            'answered_10'      => ($stats['questions_answered'] ?? 0) >= 10,
            'first_question'   => ($stats['questions_received'] ?? 0) >= 1,
            'first_mailbox'    => ($stats['mailboxes_created'] ?? 0) >= 1,
            'custom_avatar'    => (bool) ($stats['has_custom_avatar'] ?? false),

            // ── Alcance de vistas (Slice C) ───────────────────────────────────
            'reach_100'  => ($stats['total_views'] ?? 0) >= config('achievements.reach_100_views', 100),
            'reach_1k'   => ($stats['total_views'] ?? 0) >= config('achievements.reach_1k_views', 1000),
            'reach_10k'  => ($stats['total_views'] ?? 0) >= config('achievements.reach_10k_views', 10000),

            // ── Visitas únicas totales (Slice C) ──────────────────────────────
            'unique_50'  => ($stats['total_unique_views'] ?? 0) >= config('achievements.unique_50_views', 50),
            'unique_500' => ($stats['total_unique_views'] ?? 0) >= config('achievements.unique_500_views', 500),

            // ── Post viral: pico de únicos en un buzón (Slice C) ─────────────
            'viral_post' => ($stats['max_unique_views_in_a_mailbox'] ?? 0) >= config('achievements.viral_post_views', 100),

            // ── Conversión: mejor ratio con piso de visitas (Slice C) ─────────
            'conversion_ace' => ($stats['max_unique_views_in_a_mailbox'] ?? 0) >= config('achievements.conversion_ace_min_views', 50)
                             && ($stats['best_conversion'] ?? 0.0) >= config('achievements.conversion_ace_ratio', 0.10),

            // ── Racha de días (Slice C) ───────────────────────────────────────
            'streak_3'  => ($stats['streak_days'] ?? 0) >= config('achievements.streak_3_days', 3),
            'streak_7'  => ($stats['streak_days'] ?? 0) >= config('achievements.streak_7_days', 7),
            'streak_30' => ($stats['streak_days'] ?? 0) >= config('achievements.streak_30_days', 30),

            // ── Primer revive (Slice C) — condición transitoria ───────────────
            // Solo se activa cuando el caller pasa first_revive=true (p.ej. revive()).
            'first_revive' => (bool) ($stats['first_revive'] ?? false),
        ];

        // Captura el estado ANTES de insertar para calcular el diff.
        $before = $this->unlockedFor($userId);

        foreach ($this->catalog() as $achievement) {
            $code = $achievement['code'];
            if (($conditions[$code] ?? false) && !isset($before[$code])) {
                $this->query(
                    "INSERT IGNORE INTO achievement_user (user_id, achievement_id)
                     VALUES ({$userId}, {$achievement['id']})"
                );
            }
        }

        // Delta: codes presentes después pero no antes.
        $after  = $this->unlockedFor($userId);
        $newCodes = array_values(array_diff(array_keys($after), array_keys($before)));

        return $newCodes;
    }

    /** Catálogo completo con estado de desbloqueo, para el endpoint. */
    public function listFor(int $userId, string $lang): array
    {
        $unlocked = $this->unlockedFor($userId);
        $out = [];
        foreach ($this->catalog() as $a) {
            $entry = [
                'code'     => $a['code'],
                'weight'   => (int) $a['weight'],
                'emoji'    => $a['emoji'],
                'name'     => $lang === 'en' ? $a['name_en'] : $a['name_es'],
                'reward'   => $a['reward'] ?? null,
                'unlocked' => isset($unlocked[$a['code']]),
            ];
            if (isset($unlocked[$a['code']])) {
                $entry['unlocked_at'] = substr($unlocked[$a['code']], 0, 10);
            }
            $out[] = $entry;
        }
        return $out;
    }

    /**
     * Construye el array newly_unlocked para adjuntar a respuestas de API.
     * Mapea los codes devueltos por evaluate() al formato {code, emoji, name, reward}.
     *
     * @param  array  $newCodes  Resultado de evaluate().
     * @param  string $lang      'es' | 'en'
     * @return array
     */
    public function formatNewlyUnlocked(array $newCodes, string $lang): array
    {
        if (empty($newCodes)) {
            return [];
        }

        $catalog = $this->catalog();
        $byCode  = [];
        foreach ($catalog as $a) {
            $byCode[$a['code']] = $a;
        }

        $result = [];
        foreach ($newCodes as $code) {
            if (!isset($byCode[$code])) {
                continue;
            }
            $a        = $byCode[$code];
            $result[] = [
                'code'   => $code,
                'emoji'  => $a['emoji'],
                'name'   => $lang === 'en' ? $a['name_en'] : $a['name_es'],
                'reward' => $a['reward'] ?? null,
            ];
        }
        return $result;
    }
}
