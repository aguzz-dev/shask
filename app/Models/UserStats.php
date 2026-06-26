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
            "SELECT hype, avatar, created_at, streak_days FROM users WHERE id = {$userId}"
        )->fetch_assoc();

        $maxUnread = $this->query(
            "SELECT COALESCE(MAX(t.unread), 0) AS m FROM (
                SELECT COUNT(*) AS unread FROM questions q
                JOIN public_posts pp ON pp.post_id = q.public_post_id
                WHERE pp.user_id = {$userId} AND q.status = 0
                GROUP BY pp.id
            ) t"
        )->fetch_assoc()['m'];

        // KPIs de alcance: totales de visitas de todos los posts del usuario
        $viewTotals = $this->query(
            "SELECT COALESCE(SUM(p.views), 0) AS total_views,
                    COALESCE(SUM(p.unique_views), 0) AS total_unique_views,
                    COALESCE(MAX(p.unique_views), 0) AS max_unique_views_in_a_mailbox
             FROM posts p
             JOIN public_posts pp ON pp.post_id = p.id
             WHERE pp.user_id = {$userId}"
        )->fetch_assoc();

        // Mejor conversión: (preguntas recibidas / visitas únicas) en el buzón
        // con más visitas. Requiere al menos `conversion_ace_min_views` visitas únicas.
        $minViews = (int) config('achievements.conversion_ace_min_views', 50);
        $convRow  = $this->query(
            "SELECT COALESCE(MAX(t.ratio), 0) AS best_conversion
             FROM (
                 SELECT CASE
                     WHEN p.unique_views >= {$minViews}
                     THEN COUNT(q.id) / p.unique_views
                     ELSE 0
                 END AS ratio
                 FROM posts p
                 JOIN public_posts pp ON pp.post_id = p.id
                 LEFT JOIN questions q ON q.public_post_id = pp.id
                 WHERE pp.user_id = {$userId}
                 GROUP BY p.id, p.unique_views
             ) t"
        )->fetch_assoc();

        return [
            'questions_received'            => (int) $received,
            'questions_answered'            => (int) $answered,
            'mailboxes_created'             => (int) $mailboxes,
            'streak_days'                   => (int) $user['streak_days'],
            'member_since'                  => $user['created_at'] ? substr($user['created_at'], 0, 10) : null,
            'hype'                          => (int) $user['hype'],
            'has_custom_avatar'             => !empty($user['avatar']),
            'max_unread_in_a_mailbox'       => (int) $maxUnread,
            'total_views'                   => (int) $viewTotals['total_views'],
            'total_unique_views'            => (int) $viewTotals['total_unique_views'],
            'max_unique_views_in_a_mailbox' => (int) $viewTotals['max_unique_views_in_a_mailbox'],
            'best_conversion'               => (float) $convRow['best_conversion'],
            // first_revive solo se activa explícitamente desde revive(); default false.
            'first_revive'                  => false,
        ];
    }
}
