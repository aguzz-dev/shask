<?php

namespace App\Http\Controllers;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Http\Request;

use Google\Client as GoogleClient;

class GoogleAuthController extends Controller
{
    public function register(Request $request)
    {
        $accessToken = $request->googleToken;
        $client = new GoogleClient(['client_id' => env('GOOGLE_CLIENT_KEY')]);
        $client->setAccessToken($accessToken);

        $googleOAuth = new \Google_Service_Oauth2($client);

        try {
            $userInfo = $googleOAuth->userinfo->get();
        }catch (\Exception $exception){
            return response()->json([
               'error' => 'Google token inválido'
            ], 401);
        }

        $user = (new User)->findByMail($userInfo['email']);
        if (!empty($user)) {
            $user['notificaciones_activadas'] = !empty($user['fcm_token']);
            $token = (new PersonalAccessToken())->generateToken($user['id']);

            return response()->json([
                'token' => $token,
                'user' => $user
            ]);
        }

        $res = (new User)->googleRegister([
            'email' => $userInfo['email'],
            'full_name' => ucwords(strtolower($userInfo['name'])) ?? null,
            'age' => $request->age,
            'username' => $request->username,
            'avatar' => $request->avatar ?? null
        ]);

        return response()->json($res);

    }

    public function login(Request $request)
    {
        $accessToken = $request->googleToken;
        $client = new GoogleClient(['client_id' => env('GOOGLE_CLIENT_KEY')]);
        $client->setAccessToken($accessToken);

        $googleOAuth = new \Google_Service_Oauth2($client);

        try {
            $userInfo = $googleOAuth->userinfo->get();
        }catch (\Exception $exception){
            return response()->json([
                'error' => 'Google token inválido'
            ], 401);
        }

        try {
            $isUserExist = (new User)->findByMail($userInfo['email'])[0];
        }catch (\Exception $exception){
            return response()->json([
                'error' => 'Usuario no registrado en el sistema'
            ], 400);
        }

        $userToken = (new PersonalAccessToken)->getTokenById($isUserExist['id']);
        if (!empty($userToken)) {
            $charsToRemove = ['[','"',']'];
            $token = str_replace($charsToRemove, '', $userToken);
        }else{
            $token = (new User)->regenerateTokenForGoogleLogin($isUserExist['id']);
        }
        $isUserExist['notificaciones_activadas'] = empty($isUserExist['fcm_token']) ? false : true;
        return response()->json([
            'token' => $token,
            'user' => $isUserExist
        ]);
    }
}
