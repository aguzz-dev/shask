<?php
namespace App\Models;

use App\Database;

class UserStats extends Database
{
    /**
     * Stats del usuario para el perfil v3. Las claves extra (hype,
     * has_custom_avatar, max_unread_in_a_mailbox) son insumos del evaluador
     * de logros y NO viajan en la respuesta del endpoint.
     */
    public function forUser(int $userId): array
    {
        $received = $this->query(
            "SELECT COUNT(*) AS c FROM questions q
             JOIN public_posts pp ON pp.post_id = q.public_post_id
             WHERE pp.user_id = {$userId}"
        )->fetch_assoc()['c'];

        $answered = $this->query(
            "SELECT COUNT(*) AS c FROM questions q
             JOIN public_posts pp ON pp.post_id = q.public_post_id
             WHERE pp.user_id = {$userId} AND q.status = 1"
        )->fetch_assoc()['c'];

        // public_posts sobrevive al borrado de posts vencidos: es el mejor
        // proxy de "buzones creados" histórico (se pierde solo si el usuario
        // oculta el post: limitación aceptada en el spec).
        $mailboxes = $this->query(
            "SELECT COUNT(*) AS c FROM public_posts WHERE user_id = {$userId}"
        )->fetch_assoc()['c'];

        $user = $this->query(
            "SELECT hype, avatar, created_at FROM users WHERE id = {$userId}"
        )->fetch_assoc();

        $maxUnread = $this->query(
            "SELECT COALESCE(MAX(t.unread), 0) AS m FROM (
                SELECT COUNT(*) AS unread FROM questions q
                JOIN public_posts pp ON pp.post_id = q.public_post_id
                WHERE pp.user_id = {$userId} AND q.status = 0
                GROUP BY pp.id
            ) t"
        )->fetch_assoc()['m'];

        return [
            'questions_received' => (int) $received,
            'questions_answered' => (int) $answered,
            'mailboxes_created'  => (int) $mailboxes,
            'member_since'       => $user['created_at'] ? substr($user['created_at'], 0, 10) : null,
            'hype'               => (int) $user['hype'],
            'has_custom_avatar'  => !empty($user['avatar']),
            'max_unread_in_a_mailbox' => (int) $maxUnread,
        ];
    }
}
