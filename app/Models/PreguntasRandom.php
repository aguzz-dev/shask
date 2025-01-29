<?php

namespace App\Models;


use App\Database;

class PreguntasRandom extends Database
{
    protected $table = 'preguntas_random';

    public function getRandomQuestion()
    {
        return $this->query("SELECT * FROM `preguntas_random` ORDER BY RAND() LIMIT 1")->fetch_all(MYSQLI_ASSOC)[0]['pregunta'];
    }
}
