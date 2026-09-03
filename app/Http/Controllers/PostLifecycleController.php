<?php
namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\PersonalAccessToken;
use App\Models\Post;
use App\Models\Streak;
use App\Models\UserStats;
use Illuminate\Http\Request;

class PostLifecycleController extends Controller
{
    public function renew(Request $request)
    {
        $post = $this->authorizePost($request);
        if ($this->isClosed($post)) {
            return response()->json('El buzón ya venció: usá revivir', 409);
        }
        $id     = (int) $post['id'];
        $userId = (int) $post['user_id'];
        $minutes = (int) config('app.mailbox_lifetime_minutes');
        (new Post)->query("UPDATE posts SET expires_at = DATE_ADD(NOW(), INTERVAL {$minutes} MINUTE),
            extended = 0, renewed_count = renewed_count + 1,
            notified_24h = 0, notified_2h = 0, notified_closed = 0
            WHERE id = {$id}");
        (new Streak)->touch($userId);

        // Evaluar logros tras la renovación e incluir el delta en la respuesta.
        $lang   = $request->lang === 'en' ? 'en' : 'es';
        $stats  = (new UserStats)->forUser($userId);
        $model  = new Achievement;
        $delta  = $model->evaluate($userId, $stats);

        $postData                  = (new Post)->findEnriched($id);
        $postData['newly_unlocked'] = $model->formatNewlyUnlocked($delta, $lang);

        return response()->json(['Buzón renovado', $postData]);
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
        $this->charge($request, 'extend');
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
        $this->charge($request, 'unlock');
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
        $this->charge($request, 'revive');
        $id     = (int) $post['id'];
        $userId = (int) $post['user_id'];
        $minutes = (int) config('app.mailbox_lifetime_minutes');
        (new Post)->query("UPDATE posts SET expires_at = DATE_ADD(NOW(), INTERVAL {$minutes} MINUTE),
            unlocked = 1, extended = 0,
            notified_24h = 0, notified_2h = 0, notified_closed = 0
            WHERE id = {$id}");
        (new Streak)->touch($userId);

        // Evaluar logros: first_revive se activa en esta acción específica.
        $lang   = $request->lang === 'en' ? 'en' : 'es';
        $stats  = (new UserStats)->forUser($userId);
        $stats['first_revive'] = true;
        $model  = new Achievement;
        $delta  = $model->evaluate($userId, $stats);

        $postData                   = (new Post)->findEnriched($id);
        $postData['newly_unlocked'] = $model->formatNewlyUnlocked($delta, $lang);

        return response()->json(['Buzón revivido', $postData]);
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
     * Gates a paid lifecycle action. Subscribers act for free. Everyone else
     * must present a signature-verified rewarded-ad grant, consumed atomically
     * so it can't be replayed. The legacy grace flag lets pre-SSV builds through
     * without a nonce during the coordinated rollout.
     *
     * @param  string  $purpose  extend | unlock | revive
     */
    private function charge(Request $request, string $purpose): void
    {
        $userId = (int) $request->user_id;

        if ((new \App\Models\User)->isSubscriber($userId)) {
            return;
        }

        $nonce = (string) $request->ad_nonce;
        if ($nonce === '' && config('admob.legacy_ad_grace')) {
            return; // legacy build during rollout
        }

        $postId = (int) $request->id;
        $result = (new \App\Models\AdRewardGrant)->consume($userId, $nonce, $purpose, $postId);

        if ($result === \App\Models\AdRewardGrant::PENDING_R) {
            abort(425, 'Estamos confirmando tu anuncio, probá de nuevo en un momento');
        }
        if ($result !== \App\Models\AdRewardGrant::OK) {
            abort(402, 'Mirá un anuncio para completar esta acción');
        }
    }
}
