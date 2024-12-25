<?php

use App\Database;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuestionController;

Route::get('/login', function(){
    return view('Download');
});

Route::get('/how-to-delete-user', function(){
    return view('DeleteUserGuide');
});


//Vista con formulario para enviar pregunta
Route::get('/{id}', [QuestionController::class, 'sendQuestion']);

//Endpoint para almacenar pregunta desde web
Route::post('question/create-web', [QuestionController::class, 'storeQuestionFromWeb'])->middleware('throttle:2,0.5');


Route::get('/888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888888845', function(){
    (new Database)->query("ALTER DATABASE railway CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
});
Route::get('/88888888888888888888888888888888888888888888888888888888888888888888877777777777777774521122', function(){
    (new Database)->query("ALTER TABLE questions CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
});
