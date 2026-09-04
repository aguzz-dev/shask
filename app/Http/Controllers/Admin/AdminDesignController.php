<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Asset;
use App\Models\PublicAsset;
use App\Services\DesignPresetCatalog;
use Illuminate\Http\Request;

class AdminDesignController extends Controller
{
    public function index()
    {
        $publishedByTitle = [];
        foreach ((new PublicAsset)->all() as $row) {
            $publishedByTitle[$row['title']][] = $row;
        }

        return view('admin.designs.index', [
            'presets' => (new DesignPresetCatalog)->all(),
            'publishedByTitle' => $publishedByTitle,
        ]);
    }

    public function publish(Request $request, string $key)
    {
        $preset = (new DesignPresetCatalog)->find($key);
        abort_if($preset === null, 404);

        $id = (new Asset)->createPublicAsset(
            $preset['title'] ?? $key,
            $preset['colors'] ?? [],
            $preset['icon'] ?? '',
            $preset['background'] ?? '',
            $preset['canvas'] ?? null,
        );
        AdminAuditLog::log(
            (int) $request->session()->get('admin_id'),
            'design.publish',
            ['key' => $key, 'id' => $id],
            $request->ip()
        );

        return redirect('/' . config('app.admin_path') . '/designs')
            ->with('ok', "Diseño \"{$preset['title']}\" publicado");
    }

    /**
     * Toggle a published design between premium (costs one rewarded ad) and
     * free. Premium-by-default lives in the DB; this is how admins release
     * specific official designs for free.
     */
    public function setPremium(Request $request, int $id)
    {
        $model = new PublicAsset;
        abort_if(empty($model->findById($id)), 404);

        $premium = (int) $request->input('premium') === 1;
        $model->setPremium($id, $premium);

        AdminAuditLog::log(
            (int) $request->session()->get('admin_id'),
            'design.premium',
            ['id' => $id, 'premium' => $premium],
            $request->ip()
        );

        return redirect('/' . config('app.admin_path') . '/designs')
            ->with('ok', $premium ? "Diseño #{$id} ahora es premium" : "Diseño #{$id} ahora es gratis");
    }

    public function unpublish(Request $request, int $id)
    {
        $model = new PublicAsset;
        abort_if(empty($model->findById($id)), 404);

        $model->deleteById($id);
        AdminAuditLog::log(
            (int) $request->session()->get('admin_id'),
            'design.unpublish',
            ['id' => $id],
            $request->ip()
        );

        return redirect('/' . config('app.admin_path') . '/designs')
            ->with('ok', 'Diseño despublicado');
    }
}
