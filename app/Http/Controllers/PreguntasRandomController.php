<?php

namespace App\Http\Controllers;

use App\Models\PreguntasRandom;

class PreguntasRandomController extends Controller
{
    public function getRandomQuestion(): string
    {
        $pregunta = (new PreguntasRandom)->getRandomQuestion();
        return response()->json($pregunta);
    }
}
