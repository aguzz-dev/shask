<?php
namespace App\Http\Controllers;

use App\Models\PublicPost;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;

class PublicPostController extends Controller
{
    public function makePublicPost(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        try {
            $res = (new PublicPost)->makePublicPost($request->id);
            return response()->json(['Post publicado con éxito', $res]);
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                return response()->json(['error' => $e->getMessage()], 404);
            }else{
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }
    }

    public function makePrivatePost(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new PublicPost)->makePrivatePost($request->id);
        return response()->json(['Post ocultado con éxito', $res]);
    }
}
