<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('Download');
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
