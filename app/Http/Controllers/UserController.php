<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Request\UpdateUserRequest;
use App\Models\PersonalAccessToken;
use App\Request\RegisterUserRequest;

class UserController extends Controller
{
    public function store(Request $request)
    {
        RegisterUserRequest::validate($request);

        try {
            $res = (new User)->store($request);
            return response()->json(['User registrado correctamente', $res]);
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    public function update(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        UpdateUserRequest::validate($request);
        try {
            $res = (new User)->update($request);
            return response()->json(['User actualizado con éxito', $res]);
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    public function updateAvatar(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        (new User)->updateAvatar($request->id, $request->avatar);
        return response()->json('Avatar actualizado correctamente');
    }

    public function destroy(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        if(!isset($request->id)){
            http_response_code(422);
            echo json_encode(['El campo id es obligatorio']);
            exit;
        }
        try {
            (new User)->destroy($request->id);
            return response()->json('User eliminado correctamente del sistema');
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    public function changePassword(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        try {
            (new User)->changePassword($request);
            return response()->json('Password actualizada con éxito');
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }
}
