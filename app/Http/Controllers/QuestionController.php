<?php
namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\Post;
use App\Models\User;
use App\Models\Asset;
use App\Models\Question;
use App\Models\Blacklist;
use App\Models\PublicPost;
use App\Models\PublicAsset;
use App\Models\UserStats;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function getQuestionById(Request $request)
    {
        $res = (new Question)->findById($request->id);
        if(empty($res)){
            return response()->json('Pregunta no encontrada', 404);
        }
        return response()->json(['Solicitud exitosa', $res]);
    }

    public function getQuestionsByPostId(Request $request)
    {
        $postId = $request->id;
        return (new Question)->getQuestionsByPostId($postId);
    }

    public function store(Request $request)
    {
        $res = (new Question)->store($request);
        return response()->json(['Pregunta creada con éxito', $res]);
    }

    public function storeQuestionFromWeb(Request $request)
    {
        $postRow = (new Post)->findById((int) $request->id_post)[0] ?? null;
        if (!$postRow) {
            return response()->json('Post no encontrado', 404);
        }
        if (strtotime($postRow['expires_at']) <= time()) {
            return response()->json('El buzón ya cerró', 410);
        }
        $userId = (new Post)->getUserIdByPostId($request->id_post)[0]['user_id'];
        $isBlacklisted = (new Blacklist)->findByIp($request->ip(), $userId);
        if ($isBlacklisted) {
            return response()->json('Usuario bloqueado por molesto', 423);
        }
        $res = (new Question)->store((object) $request, $userId);

        // Evaluar logros del dueño del buzón tras recibir la pregunta.
        $lang          = ($request->lang === 'en') ? 'en' : 'es';
        $stats         = (new UserStats)->forUser((int) $userId);
        $achievement   = new Achievement;
        $delta         = $achievement->evaluate((int) $userId, $stats);
        $newlyUnlocked = $achievement->formatNewlyUnlocked($delta, $lang);

        return response()->json(['Pregunta creada con éxito', $res, ['newly_unlocked' => $newlyUnlocked]]);
    }

    public function answerQuestion(Request $request)
    {
        $res = (new Question)->answerQuestion($request);
        return response()->json(['Se ha actualizado el estado de la pregunta a Respondida', $res]);
    }

    /**
     * Luminancia relativa WCAG (misma fórmula que Color.computeLuminance()
     * en Flutter) — permite decidir el color de texto (negro/blanco) sobre
     * el acento del asset con el mismo criterio que usa la app nativa.
     */
    private function relativeLuminance(array $rgb): float
    {
        $linearize = function (float $c): float {
            $c /= 255;
            return $c <= 0.03928 ? $c / 12.92 : pow(($c + 0.055) / 1.055, 2.4);
        };
        return 0.2126 * $linearize((float) ($rgb[0] ?? 0))
            + 0.7152 * $linearize((float) ($rgb[1] ?? 0))
            + 0.0722 * $linearize((float) ($rgb[2] ?? 0));
    }

    public function sendQuestion(Request $request, $url)
    {
        $existPost = (new PublicPost)->getPostDataByUrl($url);
        if (!$existPost) {
            return view('errors/404');
        }

        // --- Conteo de visitas (ANTES del render, para ambos estados del buzón) ---
        // 1. Filtro anti-bot: si el UA coincide con la lista de config → no contar
        $ua    = $request->userAgent() ?? '';
        $isBot = false;
        foreach (config('visit_tracking.bot_user_agents', []) as $bot) {
            if (stripos($ua, $bot) !== false) {
                $isBot = true;
                break;
            }
        }

        if (!$isBot) {
            $ip   = $request->ip();
            $salt = config('visit_tracking.daily_salt', date('Y-m-d'));
            $hash = hash('sha256', $ip . $ua . $salt);
            (new Post)->incrementViews((int) $existPost['post_id'], $hash);
        }
        // --- Fin conteo ---

        if (!empty($existPost['expires_at']) && strtotime($existPost['expires_at']) <= time()) {
            $userData = (new User)->findById($existPost['user_id'])[0];
            return view('MailboxClosed', [
                'usernameUser' => $userData['username'],
                'title' => $existPost['title'],
            ]);
        }
        if ($existPost['asset_id'] > 10000){
            $dataPost = (new Asset)->findById($existPost['asset_id'])[0];
        }else {
            $dataPost = (new PublicAsset)->findById($existPost['asset_id'])[0];
        }
        $userData = (new User)->findById($existPost['user_id'])[0];

        // Acento = colors[0] del asset (mismo criterio que themedAccentColor
        // en la app Flutter: el color que el propio diseño eligió). El
        // contraste del texto sobre ese acento usa la misma fórmula WCAG que
        // Color.computeLuminance(), para que la landing web y la app decidan
        // negro/blanco de forma idéntica.
        $colors      = json_decode($dataPost['color']);
        $accentRgb   = $colors[0] ?? [255, 106, 19];
        $onAccent    = $this->relativeLuminance($accentRgb) > 0.5 ? '#000000' : '#ffffff';

        return view('Index', [
            'idPublicPost' => $existPost['id'],
            'idPost' => $existPost['post_id'],
            'idUser' => $existPost['user_id'],
            'fullNameUser' => $userData['full_name'],
            'usernameUser' => $userData['username'],
            'emailUser' => $userData['email'],
            'avatarUser' => json_decode($userData['avatar']),
            'title' => $existPost['title'],
            'url' => $existPost['url'],
            'colors' => $colors,
            'accentRgb' => $accentRgb,
            'onAccent' => $onAccent,
            'assetIcon' => $dataPost['icon']
        ]);
    }
}
