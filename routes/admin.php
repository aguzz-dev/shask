<?php

use App\Http\Controllers\Admin\AdminLoginController;
use Illuminate\Support\Facades\Route;

// El back office solo existe si ADMIN_PATH está configurado.
$adminPath = config('app.admin_path');
if (!$adminPath) {
    return;
}

Route::prefix($adminPath)->middleware(['web', 'admin.headers'])->group(function () {
    Route::get('/login', [AdminLoginController::class, 'show'])->name('admin.login');
    Route::post('/login', [AdminLoginController::class, 'login'])
        ->middleware('throttle:5,1');

    Route::middleware('admin.auth')->group(function () {
        Route::post('/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');
        Route::get('/', [\App\Http\Controllers\Admin\AdminImageController::class, 'index'])->name('admin.home');
    });
});
