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
        if (!isset($request->id)) {
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

    public function blockUser(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        try {
            (new User)->blockUser(
                $request->user_id,
                $request->question_id
            );
            return response()->json('Usuario bloqueado con éxito');
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    public function desblockUser(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        try {
            (new User)->desblockUser(
                $request->id,
                $request->random_user
            );
            return response()->json('Usuario desbloqueado con éxito');
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    public function getUserBlockedList($userId)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        try {
            $res = (new User)->getUserBlockedList($userId);
            $formatted = [];
            foreach ($res as $index => $user) {
                $formatted[$index + 1] = $user[0];
            }
            return response()->json($formatted);
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    public function saveFcm(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        try {
            (new User)->saveFcm($request);
            return response()->json('FCM guardado correctamente');
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    public function desactivarFcm(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        try {
            (new User)->desactivarFcm($request->id);
            return response()->json('FCM desactivado correctamente');
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }
}
