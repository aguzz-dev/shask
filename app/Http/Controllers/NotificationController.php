<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
    {
        $title = 'Nuevo mensaje!';
        $fcm = (new User)->getFcmByUsername($request->username)['fcm_token'];
        $description = $request->text;

        $firebaseCredentials = [
            'type' => 'service_account',
            'project_id' => env('FIREBASE_PROJECT_ID'),
            'private_key_id' => env('FIREBASE_PRIVATE_KEY_ID'),
            'private_key' => str_replace('\\n', "\n", env('FIREBASE_PRIVATE_KEY')),
            'client_email' => env('FIREBASE_CLIENT_EMAIL'),
            'client_id' => env('FIREBASE_CLIENT_ID'),
            'auth_uri' => env('FIREBASE_AUTH_URI'),
            'token_uri' => env('FIREBASE_TOKEN_URI'),
            'auth_provider_x509_cert_url' => env('FIREBASE_AUTH_PROVIDER_X509_CERT_URL'),
            'client_x509_cert_url' => env('FIREBASE_CLIENT_X509_CERT_URL'),
            'universe_domain' => env('FIREBASE_UNIVERSE_DOMAIN'),
        ];

        $client = new GoogleClient();
        $client->setAuthConfig($firebaseCredentials);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $token = $client->fetchAccessTokenWithAssertion();
        $access_token = $token['access_token'];

        $data = [
            "message" => [
                "token" => $fcm,
                "notification" => [
                    "title" => $title,
                    "body" => $description,
                ],
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $access_token,
            'Content-Type' => 'application/json'
        ])
        ->post('https://fcm.googleapis.com/v1/projects/shhask-1/messages:send', $data);

        if ($response->successful()) {
            return response()->json([
                'message' => 'Notification has been sent',
                'response' => $response->json()
            ]);
        } else {
            return response()->json([
                'message' => 'HTTP Error: ' . $response->body()
            ], 500);
        }
    }
}
