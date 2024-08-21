<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\PublicPost;
use App\Helpers\JsonRequest;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Middleware\VerifyToken;

class PublicPostController extends Controller
{
    public function makePublicPost(Request $request)
    {
        VerifyToken::jwt();
        $res = (new PublicPost)->makePublicPost($request->id);
        return response()->json(['Post publicado con éxito', $res]);
    }

    public function makePrivatePost(Request $request)
    {
        VerifyToken::jwt();
        $res = (new PublicPost)->makePrivatePost($request->id);
        return response()->json(['Post ocultado con éxito', $res]);
    }
}
