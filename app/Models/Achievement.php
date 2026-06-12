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
     * Evalúa las condiciones y persiste los desbloqueos nuevos. Idempotente:
     * un logro desbloqueado queda para siempre aunque su condición sea
     * transitoria (ej. mailbox_exploded).
     */
    public function evaluate(int $userId, array $stats): void
    {
        $conditions = [
            'hype_10k'         => $stats['hype'] >= 10000,
            'mailbox_exploded' => $stats['max_unread_in_a_mailbox'] > 5,
            'received_100'     => $stats['questions_received'] >= 100,
            'answered_50'      => $stats['questions_answered'] >= 50,
            'hype_1k'          => $stats['hype'] >= 1000,
            'answered_10'      => $stats['questions_answered'] >= 10,
            'first_question'   => $stats['questions_received'] >= 1,
            'first_mailbox'    => $stats['mailboxes_created'] >= 1,
            'custom_avatar'    => $stats['has_custom_avatar'],
        ];

        $already = $this->unlockedFor($userId);
        foreach ($this->catalog() as $achievement) {
            $code = $achievement['code'];
            if (($conditions[$code] ?? false) && !isset($already[$code])) {
                $this->query(
                    "INSERT IGNORE INTO achievement_user (user_id, achievement_id)
                     VALUES ({$userId}, {$achievement['id']})"
                );
            }
        }
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
                'unlocked' => isset($unlocked[$a['code']]),
            ];
            if (isset($unlocked[$a['code']])) {
                $entry['unlocked_at'] = substr($unlocked[$a['code']], 0, 10);
            }
            $out[] = $entry;
        }
        return $out;
    }
}
