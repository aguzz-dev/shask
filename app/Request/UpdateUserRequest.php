<?php

namespace App\Request;

class UpdateUserRequest implements Request
{
    public static function validate($request)
    {
        $errors = [];

        if(!isset($request['id'])){
            array_push($errors, 'El campo id es obligatorio');
            http_response_code(422);
            echo json_encode($errors);
            exit;
        }

        if (!is_numeric($request['id'])){
            array_push($errors, 'ID inválido');
        }

        if (isset($request['email'])){
            if (!filter_var($request['email'], FILTER_VALIDATE_EMAIL)){
                array_push($errors, 'Email inválido');
             }
        }

        if (isset($request['username'])){
            if (preg_match('/[!+*\/%$]/', $request['username'])){
                array_push($errors, 'Username inválido');
             }
        }

        if (isset($request['full_name'])){
            if (preg_match('/[_!-+*\/%$\d]/', $request['full_name'])){
                array_push($errors, 'Nombre inválido');
             }
        }

        if (!empty($errors)){
            http_response_code(422);
            echo json_encode($errors);
            exit;
        }
    }
}