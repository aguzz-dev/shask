<?php
namespace App\Http\Middleware;

use App\Models\AdminUser;
use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    public function handle(Request $request, Closure $next)
    {
        $adminId = (int) $request->session()->get('admin_id', 0);
        if ($adminId <= 0) {
            return redirect('/' . config('app.admin_path') . '/login');
        }
        $admin = (new AdminUser)->findById($adminId);
        if (!$admin || (int) $admin['active'] !== 1) {
            $request->session()->invalidate();
            return redirect('/' . config('app.admin_path') . '/login');
        }
        // Disponible para las vistas (nombre en la topbar) y controllers.
        $request->attributes->set('admin', $admin);
        return $next($request);
    }
}
