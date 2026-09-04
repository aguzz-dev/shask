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
        Route::get('/images/upload', [\App\Http\Controllers\Admin\AdminImageController::class, 'showUpload']);
        Route::post('/images/upload', [\App\Http\Controllers\Admin\AdminImageController::class, 'upload']);
        Route::get('/images/{id}/edit', [\App\Http\Controllers\Admin\AdminImageController::class, 'edit'])->whereNumber('id');
        Route::post('/images/{id}/edit', [\App\Http\Controllers\Admin\AdminImageController::class, 'update'])->whereNumber('id');
        Route::post('/images/{id}/delete', [\App\Http\Controllers\Admin\AdminImageController::class, 'delete'])->whereNumber('id');

        Route::get('/designs', [\App\Http\Controllers\Admin\AdminDesignController::class, 'index']);
        Route::post('/designs/{key}/publish', [\App\Http\Controllers\Admin\AdminDesignController::class, 'publish'])
            ->where('key', '[a-z0-9_]+');
        Route::post('/designs/{id}/unpublish', [\App\Http\Controllers\Admin\AdminDesignController::class, 'unpublish'])
            ->whereNumber('id');
        Route::post('/designs/{id}/premium', [\App\Http\Controllers\Admin\AdminDesignController::class, 'setPremium'])
            ->whereNumber('id');
        Route::post('/designs/{id}/takedown', [\App\Http\Controllers\Admin\AdminModerationController::class, 'takedown'])
            ->whereNumber('id');
        Route::post('/designs/{id}/approve', [\App\Http\Controllers\Admin\AdminModerationController::class, 'approveReported'])
            ->whereNumber('id');

        Route::get('/packs', [\App\Http\Controllers\Admin\AdminPackController::class, 'index']);
        Route::get('/packs/create', [\App\Http\Controllers\Admin\AdminPackController::class, 'createForm']);
        Route::post('/packs/create', [\App\Http\Controllers\Admin\AdminPackController::class, 'create']);
        Route::get('/packs/{id}/edit', [\App\Http\Controllers\Admin\AdminPackController::class, 'editForm'])->whereNumber('id');
        Route::post('/packs/{id}/edit', [\App\Http\Controllers\Admin\AdminPackController::class, 'edit'])->whereNumber('id');
        Route::post('/packs/{id}/delete', [\App\Http\Controllers\Admin\AdminPackController::class, 'delete'])->whereNumber('id');
    });
});
