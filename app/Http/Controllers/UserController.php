<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Middleware\VerifyToken;
use App\Request\UpdateUserRequest;
use App\Models\PersonalAccessToken;
use App\Request\RegisterUserRequest;

class UserController extends Controller
{
    public function store(Request $request)
    {
        RegisterUserRequest::validate($request);
        $res = (new User)->store($request);
        return response()->json(['User registrado correctamente', $res]);
    }

    public function update(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        UpdateUserRequest::validate($request);
        $res = (new User)->update($request);
        return response()->json(['User actualizado con éxito', $res]);
    }

    public function destroy(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        if(!isset($request->id)){
            http_response_code(422);
            echo json_encode(['El campo id es obligatorio']);
            exit;
        }
        (new User)->destroy($request->id);
        return response()->json('User eliminado correctamente del sistema');
    }

    public function changePassword(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        (new User)->changePassword($request);
        return response()->json('Password actualizada con éxito');
    }


}
