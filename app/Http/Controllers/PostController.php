<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\AssetUser;
use App\Models\AdRewardGrant;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;
use App\Services\CreatorAcquisitionNotifier;

class PostController extends Controller
{
    public function index(Request $request):array
    {
        $userId = $request->id;
        return (new Post)->getAllPosts($userId);
    }

    public function getPostByIdWithQuestions(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new Post)->findById($request->id);
        return !empty($res) ? $res : response()->json('No se ha podido encontrar ningún post público', 422);
    }

    /**
     * Creates a mailbox. Premium designs (server-decided via is_premium) are the
     * paywall: a non-subscriber must present a verified rewarded-ad grant, which
     * is atomically consumed here. Subscribers skip the ad. Free/system designs
     * create straight through. The client never decides premium or entitlement.
     */
    public function store(Request $request, CreatorAcquisitionNotifier $notifier)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);

        $userId  = (int) $request->id;
        $assetId = (int) $request->asset_id;

        $gate   = (new AssetUser)->premiumGate($assetId);
        $source = null;

        // A creator using their OWN premium design pays nothing — no ad, no
        // mint. Only other people's premium designs are gated.
        $isOwnDesign = $gate['is_premium'] && $gate['creator_id'] === $userId;

        if ($gate['is_premium'] && !$isOwnDesign) {
            $nonce = (string) $request->ad_nonce;
            if ((new User)->isSubscriber($userId)) {
                $source = 'sub';
            } elseif ($nonce === '' && config('admob.legacy_ad_grace')) {
                // Legacy build during rollout: no SSV nonce available.
                $source = 'ad';
            } else {
                $result = (new AdRewardGrant)->consume($userId, $nonce, 'asset_use', $assetId);
                if ($result === AdRewardGrant::PENDING_R) {
                    return response()->json(
                        ['message' => 'Estamos confirmando tu anuncio, probá de nuevo en un momento'],
                        425
                    );
                }
                if ($result !== AdRewardGrant::OK) {
                    return response()->json(
                        ['message' => 'Este diseño es premium: mirá un anuncio para usarlo'],
                        402
                    );
                }
                $source = 'ad';
            }
        }

        $res = (new Post)->store($request);

        // Earnings/analytics for the premium use — best-effort, never blocks the
        // mailbox that was already created. Skipped when the creator uses their
        // own design (no self-acquisition).
        if ($gate['is_premium'] && !$isOwnDesign && $source !== null) {
            try {
                $creatorId = (new AssetUser)->registerPremiumUse($assetId, $userId, $source);
                if ($creatorId !== null && $creatorId !== $userId) {
                    $notifier->notify($creatorId, $assetId);
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        (new \App\Models\Streak)->touch($userId);
        return response()->json(['Post creado con éxito', $res]);
    }

    public function update(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new Post)->update($request);
        return response()->json(['Post actualizado con éxito', $res]);
    }

    public function destroy(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
         return (new Post)->destroy($request->id);
    }
}
