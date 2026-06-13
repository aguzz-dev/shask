<?php
namespace App\Services;

use App\Models\User;
use Google\Client as GoogleClient;
use Illuminate\Support\Facades\Http;

class PushNotifier
{
    public function sendToUser(int $userId, string $title, string $body, array $data = []): bool
    {
        $user = (new User)->findById($userId)[0] ?? null;
        if (!$user || empty($user['fcm_token'])) {
            return false;
        }
        return $this->sendToToken($user['fcm_token'], $title, $body, $data);
    }

    public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
    {
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

        $payload = [
            'message' => [
                'token' => $fcmToken,
                'notification' => ['title' => $title, 'body' => $body],
                'data' => array_map('strval', $data),
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $token['access_token'],
            'Content-Type' => 'application/json',
        ])->post('https://fcm.googleapis.com/v1/projects/shhask-1/messages:send', $payload);

        return $response->successful();
    }
}
