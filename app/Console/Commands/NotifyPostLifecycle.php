<?php
namespace App\Console\Commands;

use App\Database;
use App\Services\PushNotifier;
use Illuminate\Console\Command;

class NotifyPostLifecycle extends Command
{
    protected $signature = 'posts:notify-lifecycle';
    protected $description = 'Pushes del ciclo de vida: T-24h, T-2h y cierre del buzón';

    public function handle(PushNotifier $push): int
    {
        $db = new Database;

        // T-24h: activo, vence dentro de las próximas 24h
        $rows = $db->query("SELECT id, user_id FROM posts
            WHERE notified_24h = 0 AND expires_at > NOW()
            AND expires_at <= DATE_ADD(NOW(), INTERVAL 24 HOUR)")->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as $p) {
            $push->sendToUser((int) $p['user_id'], '⏰ Tu buzón vence en 24h',
                'Renovalo gratis y volvé a compartirlo', ['post_id' => (string) $p['id'], 'kind' => 'expiring_24h']);
            $db->query("UPDATE posts SET notified_24h = 1 WHERE id = {$p['id']}");
        }

        // T-2h: último aviso
        $rows = $db->query("SELECT id, user_id FROM posts
            WHERE notified_2h = 0 AND expires_at > NOW()
            AND expires_at <= DATE_ADD(NOW(), INTERVAL 2 HOUR)")->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as $p) {
            $push->sendToUser((int) $p['user_id'], '🔥 Últimas 2 horas',
                'Tu buzón está por cerrar', ['post_id' => (string) $p['id'], 'kind' => 'expiring_2h']);
            $db->query("UPDATE posts SET notified_2h = 1 WHERE id = {$p['id']}");
        }

        // Cierre: recap listo + sin leer
        $rows = $db->query("SELECT p.id, p.user_id,
                (SELECT COUNT(*) FROM questions q WHERE q.public_post_id = p.id AND q.status = 0) AS unread
            FROM posts p
            WHERE p.notified_closed = 0 AND p.expires_at <= NOW()")->fetch_all(MYSQLI_ASSOC);
        foreach ($rows as $p) {
            $body = ((int) $p['unread'] > 0)
                ? "Recap listo. Te quedaron {$p['unread']} preguntas sin leer"
                : 'Mirá el recap y compartilo en tu story';
            $push->sendToUser((int) $p['user_id'], '🤫 Tu buzón cerró', $body,
                ['post_id' => (string) $p['id'], 'kind' => 'closed']);
            $db->query("UPDATE posts SET notified_closed = 1 WHERE id = {$p['id']}");
        }

        return self::SUCCESS;
    }
}
