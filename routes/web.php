<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\PublicPostController;


Route::get('/login', function(){ return view('Download');});



