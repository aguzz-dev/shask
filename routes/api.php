<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\PublicPostController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/image/{name}', [ImageController::class, 'get']);

Route::post('/login', [AuthController::class, 'login']);
Route::post('/check-session', [AuthController::class, 'checkSession']);
Route::get('/destroy-session', [AuthController::class, 'logout']);
Route::get('/check-username/{username}', [UserController::class, 'checkUsername']);
Route::get('/check-email/{email}', [UserController::class, 'checkEmail']);

Route::post('/posts', [PostController::class, 'index']);
Route::post('/posts/create', [PostController::class, 'store']);
Route::put('/posts', [PostController::class, 'update']);
Route::delete('/posts', [PostController::class, 'destroy']);
Route::post('/posts/questions', [PostController::class, 'getPostByIdWithQuestions']);

Route::post('/block', [UserController::class, 'blockUser']);
Route::post('/unblock', [UserController::class, 'desblockUser']);
Route::get('/blocked/{id}', [UserController::class, 'getUserBlockedList']);

Route::post('/user/create', [UserController::class, 'store']);
Route::put('/user/update', [UserController::class, 'update']);
Route::put('/user/avatar', [UserController::class, 'updateAvatar']);
Route::delete('/user/destroy', [UserController::class, 'destroy']);

Route::post('/change-password', [UserController::class, 'changePassword']);

Route::post('/share-post', [PublicPostController::class, 'makePublicPost']);
Route::post('/hide-post', [PublicPostController::class, 'makePrivatePost']);

Route::post('question/get', [QuestionController::class, 'getQuestionById']);
Route::post('/question', [QuestionController::class, 'getQuestionsByPostId']);
Route::post('/question/create', [QuestionController::class, 'store']);
Route::post('/question/create-web', [QuestionController::class, 'storeQuestionFromWeb']);
Route::post('/question/answer', [QuestionController::class, 'answerQuestion']);

Route::get('/assets', [AssetController::class, 'getAllAssets']);
Route::post('/assets/buy', [AssetController::class, 'buyAsset']);
Route::post('/assets/check', [AssetController::class, 'checkAssetExpired']);
Route::post('/assets/id', [AssetController::class, 'getUserAssetsByUserId']);

//Verificar cuenta de usuario
Route::post('/code', [UserController::class, 'getCode'])->middleware('throttle:1,10');
Route::post('/verify', [UserController::class, 'verifyCode']);

//Recuperar contraseña
Route::post('/resetPasswordCode', [UserController::class, 'getResetPasswordCode']);
Route::post('/verifyResetPasswordCode', [UserController::class, 'verifyResetPasswordCode']);
Route::post('/resetPassword', [UserController::class, 'resetPassword']);

Route::get('/login', function(){ return view('Download');});

Route::post('/fcm-token', [UserController::class, 'saveFcm']); //Activar notificaciones
Route::post('/fcm-token/desactivar', [UserController::class, 'desactivarFcm']); //Desactivar notificaciones

Route::get('/csrf-token', function () {
    return response()->json([
        'CSRF-TOKEN' => 'cookie csrf recibida',
    ])->withCookie(cookie('XSRF-TOKEN', csrf_token(), 60*24))->withoutCookie('X-XSRF-TOKEN');
});
