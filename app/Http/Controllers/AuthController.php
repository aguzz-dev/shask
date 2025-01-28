<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Request\LoginRequest;
use App\Models\PersonalAccessToken;

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
        $userId =  $request->id;
        if (!$userId) {
            return response()->json([
               'error' => 'Debe proporcionar el id del usuario para validar la sesión'
            ]);
        }

        $token = (string)$request->bearerToken();

        if((new PersonalAccessToken)->validateToken($token, $userId)) {
            $userData = (new User)->findById($userId)[0];
            return response()->json(['Sesión validada con éxito', $userData]);
        }
        return response()->json('Token inválido', 401);
    }

    public function logout()
    {
        $userId = (new PersonalAccessToken)->getIdByToken($this->getToken());
        (new PersonalAccessToken)->destroyToken($userId);
        return response()->json('Sesión eliminada con éxito');
    }
}
