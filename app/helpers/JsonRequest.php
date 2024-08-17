<?php

namespace App\Helpers;

class JsonRequest
{
    public static function get()
    {
        $request = json_decode(file_get_contents("php://input"));
        return $request;
    }
}