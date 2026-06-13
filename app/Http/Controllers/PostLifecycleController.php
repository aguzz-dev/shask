<?php
namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Models\Post;
use App\Models\Streak;
use Illuminate\Http\Request;

class PostLifecycleController extends Controller
{
    public function renew(Request $request)
    {
        $post = $this->authorizePost($request);
        if ($this->isClosed($post)) {
            return response()->json('El buzón ya venció: usá revivir', 409);
        }
        $id = (int) $post['id'];
        $minutes = (int) config('app.mailbox_lifetime_minutes');
        (new Post)->query("UPDATE posts SET expires_at = DATE_ADD(NOW(), INTERVAL {$minutes} MINUTE),
            extended = 0, renewed_count = renewed_count + 1,
            notified_24h = 0, notified_2h = 0, notified_closed = 0
            WHERE id = {$id}");
        (new Streak)->touch((int) $post['user_id']);
        return response()->json(['Buzón renovado', (new Post)->findEnriched($id)]);
    }

    /**
     * Cierra el buzón manualmente: lo vence ya. Pasa a la sección de cerrados
     * (legible con desbloqueo, revivible). notified_closed = 1 porque el cierre
     * fue intencional: no hace falta el push de "tu buzón cerró".
     */
    public function close(Request $request)
    {
        $post = $this->authorizePost($request);
        if ($this->isClosed($post)) {
            return response()->json('El buzón ya estaba cerrado', 409);
        }
        $id = (int) $post['id'];
        (new Post)->query("UPDATE posts SET expires_at = NOW(), notified_closed = 1 WHERE id = {$id}");
        return response()->json(['Buzón cerrado', (new Post)->findEnriched($id)]);
    }

    public function extend(Request $request)
    {
        $post = $this->authorizePost($request);
        if ($this->isClosed($post)) {
            return response()->json('El buzón ya venció: usá revivir', 409);
        }
        if ((int) $post['extended'] >= 1) {
            return response()->json('Ya usaste la extensión de este ciclo', 409);
        }
        $this->charge($request, (int) config('app.hype_extend'));
        $id = (int) $post['id'];
        (new Post)->query("UPDATE posts SET expires_at = DATE_ADD(expires_at, INTERVAL 24 HOUR), extended = 1 WHERE id = {$id}");
        return response()->json(['Buzón extendido', (new Post)->findEnriched($id)]);
    }

    public function unlock(Request $request)
    {
        $post = $this->authorizePost($request);
        if (!$this->isClosed($post)) {
            return response()->json('El buzón sigue activo: no hace falta desbloquear', 409);
        }
        $this->charge($request, (int) config('app.hype_unlock'));
        $id = (int) $post['id'];
        (new Post)->query("UPDATE posts SET unlocked = 1 WHERE id = {$id}");
        return response()->json(['Buzón desbloqueado', (new Post)->findEnriched($id)]);
    }

    public function revive(Request $request)
    {
        $post = $this->authorizePost($request);
        if (!$this->isClosed($post)) {
            return response()->json('El buzón sigue activo: usá renovar', 409);
        }
        $this->charge($request, (int) config('app.hype_revive'));
        $id = (int) $post['id'];
        $minutes = (int) config('app.mailbox_lifetime_minutes');
        (new Post)->query("UPDATE posts SET expires_at = DATE_ADD(NOW(), INTERVAL {$minutes} MINUTE),
            unlocked = 1, extended = 0,
            notified_24h = 0, notified_2h = 0, notified_closed = 0
            WHERE id = {$id}");
        (new Streak)->touch((int) $post['user_id']);
        return response()->json(['Buzón revivido', (new Post)->findEnriched($id)]);
    }

    /** Token válido + post existente + ownership. Aborta con 401/404/403. */
    private function authorizePost(Request $request): array
    {
        $check = (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->user_id);
        if ($check !== true) {
            abort(401, 'Token inválido');
        }
        $post = (new Post)->findById((int) $request->id)[0] ?? null;
        if (!$post) {
            abort(404, 'Post no encontrado');
        }
        if ((int) $post['user_id'] !== (int) $request->user_id) {
            abort(403, 'El buzón no pertenece al usuario');
        }
        return $post;
    }

    private function isClosed(array $post): bool
    {
        return strtotime($post['expires_at']) <= time();
    }

    /**
     * Cobra una acción paga. source=ad confía en el rewarded client-side
     * (mismo modelo que las pistas); source=hype valida saldo y descuenta.
     */
    private function charge(Request $request, int $price): void
    {
        $source = $request->source;
        if ($source === 'ad') {
            return;
        }
        if ($source !== 'hype') {
            abort(422, 'source debe ser ad o hype');
        }
        $db = new Post;
        $userId = (int) $request->user_id;
        $user = $db->query("SELECT hype FROM users WHERE id = {$userId}")->fetch_assoc();
        if ((int) $user['hype'] < $price) {
            abort(422, 'Hype insuficiente');
        }
        $db->query("UPDATE users SET hype = hype - {$price} WHERE id = {$userId}");
    }
}
