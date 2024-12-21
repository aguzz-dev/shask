<?php
namespace App\Http\Controllers;

use App\Models\PublicPost;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;

class PublicPostController extends Controller
{
    public function makePublicPost(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        try {
            $res = (new PublicPost)->makePublicPost($request->id);
            return response()->json(['Post publicado con éxito', $res]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    public function makePrivatePost(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $res = (new PublicPost)->makePrivatePost($request->id);
        return response()->json(['Post ocultado con éxito', $res]);
    }
}
