<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\Asset;
use App\Models\PublicAsset;
use Illuminate\Http\Request;

/**
 * Moderación de assets UGC: takedown y restauración.
 * Forma parte del back office admin (rutas bajo el prefijo $adminPath).
 */
class AdminModerationController extends Controller
{
    /**
     * Takedown administrativo: marca el asset como 'removed' y lo saca de
     * todos los listados públicos. Queda en BD para auditoría.
     */
    public function takedown(Request $request, int $id)
    {
        $rows = (new PublicAsset)->findById($id);
        abort_if(empty($rows), 404);

        (new Asset)->takedownAsset($id);

        AdminAuditLog::log(
            (int) $request->session()->get('admin_id'),
            'design.takedown',
            ['id' => $id],
            $request->ip()
        );

        return redirect('/' . config('app.admin_path') . '/designs')
            ->with('ok', 'Diseño removido del catálogo');
    }

    /**
     * Aprueba un asset reportado: lo restaura en el catálogo público.
     */
    public function approveReported(Request $request, int $id)
    {
        $rows = (new PublicAsset)->findById($id);
        abort_if(empty($rows), 404);

        (new Asset)->approveAsset($id);

        AdminAuditLog::log(
            (int) $request->session()->get('admin_id'),
            'design.approve_reported',
            ['id' => $id],
            $request->ip()
        );

        return redirect('/' . config('app.admin_path') . '/designs')
            ->with('ok', 'Diseño restaurado al catálogo');
    }
}
