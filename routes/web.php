<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/descarga', function () {
    return redirect('https://play.google.com/store/apps/details?id=com.mateine.quest_app_2');
});

Route::get('/', function () {
    return view('Download');
});

Route::get('/app-ads.txt', function () {
    $path = public_path('app-ads.txt');

    if(file_exists($path)) {
        return response()->file($path, [
            'Content-Type' => 'text/plain']);
    }
    return abort(404, 'File Not Found');
});

Route::get('/login', function(){
    return view('Download');
});

Route::get('/how-to-delete-user', function(){
    return view('DeleteUserGuide');
});

Route::get('/privacy-policy', function(){
    return view('PrivacyPolicy');
});

Route::get('/terms-of-service', function(){
    return view('TermsService');
});

Route::post('/pushNotification', [NotificationController::class, 'sendNotification']);

//Vista con formulario para enviar pregunta
Route::get('/{id}', [QuestionController::class, 'sendQuestion']);

//Endpoint para almacenar pregunta desde web
Route::post('question/create-web', [QuestionController::class, 'storeQuestionFromWeb'])->middleware('throttle:2,0.5');
