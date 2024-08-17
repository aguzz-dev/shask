<?php

namespace App\Request;

class RegisterUserRequest implements Request
{
    public static function validate($request)
    {
        $errors = [];

        if (preg_match('/[_!-+*\/%$\d]/',  $request->full_name) || $request->full_name == ''){
            array_push($errors, 'Nombre inválido');
        }
        if(preg_match('/[!+*\/%$]/', $request->username) || $request->username == ''){
            array_push($errors, 'Username inválido');
        }
        if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)){
            array_push($errors, 'Email inválido');
        }
        if(strlen($request->password) < 8){
            array_push($errors, 'La contraseña debe tener al menos 8 digitos');
        }

        if (!empty($errors)){
            http_response_code(422);
            echo json_encode($errors);
            exit;
        }
    }
}