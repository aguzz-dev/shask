<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    public function show()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = (new AdminUser)->verifyCredentials(
            $request->input('email'),
            $request->input('password')
        );

        $base = '/' . config('app.admin_path');
        if (!$admin) {
            return redirect($base . '/login')
                ->withErrors(['login' => 'Credenciales inválidas'])
                ->withInput(['email' => $request->input('email')]);
        }

        // Anti session fixation.
        $request->session()->regenerate();
        $request->session()->put('admin_id', $admin['id']);
        (new AdminUser)->touchLastLogin($admin['id']);
        AdminAuditLog::log($admin['id'], 'auth.login', [], $request->ip());

        return redirect($base);
    }

    public function logout(Request $request)
    {
        $adminId = (int) $request->session()->get('admin_id', 0);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        if ($adminId > 0) {
            AdminAuditLog::log($adminId, 'auth.logout', [], $request->ip());
        }
        return redirect('/' . config('app.admin_path') . '/login');
    }
}
