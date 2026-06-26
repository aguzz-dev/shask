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
