<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\PublicPost;
use App\Helpers\JsonRequest;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Middleware\VerifyToken;
use App\Models\PersonalAccessToken;

class PublicPostController extends Controller
{
    public function makePublicPost(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $res = (new PublicPost)->makePublicPost($request->id);
        return response()->json(['Post publicado con éxito', $res]);
    }

    public function makePrivatePost(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $res = (new PublicPost)->makePrivatePost($request->id);
        return response()->json(['Post ocultado con éxito', $res]);
    }
}
