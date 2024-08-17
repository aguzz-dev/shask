<?php

namespace App\Request;

class LoginRequest implements Request
{
    public static function validate($request)
    {
        $errors = [];

        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)){
            array_push($errors, 'Email inválido');
        }
        if (!empty($errors)){
            http_response_code(422);
            echo json_encode($errors);
            exit;
        }
    }
}