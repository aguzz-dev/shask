<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function getToken()
    {
        return str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']);
    }
}
