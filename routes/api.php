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

Route::post('/posts', [PostController::class, 'index']);
Route::post('/posts/create', [PostController::class, 'store']);
Route::put('/posts', [PostController::class, 'update']);
Route::delete('/posts', [PostController::class, 'destroy']);
Route::post('/posts/questions', [PostController::class, 'getPostByIdWithQuestions']);

Route::post('/user/create', [UserController::class, 'store']);
Route::put('/user/update', [UserController::class, 'update']);
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

Route::get('/login', function(){ return view('Download');});

Route::get('/csrf-token', function () {
    return response()->json([
        'CSRF-TOKEN' => 'cookie csrf recibida',
    ])->withCookie(cookie('XSRF-TOKEN', csrf_token(), 60*24))->withoutCookie('X-XSRF-TOKEN');
});

