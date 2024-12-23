<?php

use App\Http\Controllers\QuestionController;
use Illuminate\Support\Facades\Route;

Route::get('/login', function(){ return view('Download');});
Route::post('question/create-web', [QuestionController::class, 'storeQuestionFromWeb'])->middleware('throttle:2,0.5');

Route::get('/how-to-delete-user', function(){
    return view('DeleteUserGuide');
});

Route::get('/{id}', [QuestionController::class, 'sendQuestion']);


