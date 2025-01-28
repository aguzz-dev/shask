<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Request\UpdateUserRequest;
use App\Models\PersonalAccessToken;
use App\Request\RegisterUserRequest;
use Illuminate\Support\Facades\Validator;

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
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
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

    /*
     * Check disponibilidad Username / email
     */
    public function checkUsername($username)
    {
        return (new User)->checkUsername($username);
    }

    public function checkEmail($email)
    {
        return (new User)->checkEmail($email);
    }

    /*
     * Actualizar avatar
     */
    public function updateAvatar(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        (new User)->updateAvatar($request->id, $request->avatar);
        return response()->json('Avatar actualizado correctamente');
    }

    /*
     * Eliminar cuenta
     */
    public function destroy(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
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

    /*
     * Cambiar contraseña estando logeado
     */
    public function changePassword(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
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

    /*
     * Bloquear / desbloquear usuario
     */
    public function blockUser(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
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
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
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
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
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

    /*
     * Activar / desactivar notificaciones
     */
    public function saveFcm(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
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
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
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

    /*
     * Verificar cuenta
     */
    public function getCode(Request $request)
    {
        try {
            (new User)->generateCode($request);
            return response()->json(['success' => 'Código enviado exitosamente a ' . $request->email]);
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => 'Ha ocurrido un error al generar el código de verificación de cuenta'], 400
            );
        }
    }

    public function verifyCode(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        try {
            $user = (new User)->verifyCodeAndActivateUser($request);
            return response()->json(['success' => 'Usuario ' . $user['username'] . ' verificado correctamente']);
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    /*
     * Restablecer contraseña
     */
    public function getResetPasswordCode(Request $request)
    {
        try {
            (new User)->generateResetPasswordCode($request);
            return response()->json(['success' =>'Código enviado exitosamente a ' . $request->email]);
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => 'Ha ocurrido un error al generar el código de restablecimiento de contraseña'], 400
            );
        }
    }

    public function verifyResetPasswordCode(Request $request)
    {
        try {
            (new User)->verifyResetPasswordCode($request);
            return response()->json(['success' =>'Codigo de restablecimiento verificado correctamente']);
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = (new User)->resetPassword($request);

        return response()->json(['success' => 'Contraseña restablecida con éxito', 'user' => $user]);
    }
}
