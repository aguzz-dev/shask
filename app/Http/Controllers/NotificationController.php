<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PushNotifier;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendNotification(Request $request, PushNotifier $push)
    {
        $fcm = (new User)->getFcmByUsername($request->username)['fcm_token'];
        $ok = $push->sendToToken($fcm, '¡Nuevo mensaje!', 'Toca para leerlo ahora', [
            'post_id' => (string) $request->postId,
        ]);

        if ($ok) {
            return response()->json(['message' => 'Notification has been sent']);
        }
        return response()->json(['message' => 'HTTP Error al enviar la notificación'], 500);
    }
}
