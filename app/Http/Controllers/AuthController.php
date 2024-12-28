<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Helpers\getHeader;
use App\Helpers\JsonRequest;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Request\LoginRequest;
use App\Models\PersonalAccessToken;
use App\Http\Controllers\Controller;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        LoginRequest::validate($request);

        try {
            $data = (new User)->login($request);
            return response()->json(['Inicio de sesión exitoso', $data]);
        } catch (\Throwable $th) {
            return response()->json(
                ['error' => $th->getMessage()],
                $th->getCode()
            );
        }
    }

    public function checkSession(Request $request)
    {
        $userToken = (new PersonalAccessToken)->getTokenById($request->id);
        $charsToRemove = ['[','"',']'];
        $token = str_replace($charsToRemove, '', $userToken);

        if($token != $this->getToken()){
            return response()->json('Token inválido', 401);
        }
        $user = (new User)->findById($request->id)[0];
        $userData = [
            'id' => $user['id'],
            'username' => $user['username'],
            'email' => $user['email'],
            'avatar' => $user['avatar'],
            'notificaciones_activadas' => empty($user['fcm_token']) ? false : true
        ];
        return response()->json(['Sesión validada con éxito', $userData]);
    }

    public function logout()
    {
        $userId = (new PersonalAccessToken)->getIdByToken($this->getToken());
        (new PersonalAccessToken)->destroyToken($userId);
        return response()->json('Sesión eliminada con éxito');
    }
}
