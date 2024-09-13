<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Helpers\JsonRequest;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;
use App\Middleware\VerifyToken;
use App\Models\PersonalAccessToken;

class PostController extends Controller
{
    public function index(Request $request):array
    {
        $userId = $request->id;
        $res = (new Post)->getAllPosts($userId);
        return $res;
    }

    public function getPostByIdWithQuestions(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $res = (new Post)->findById($request->id);
        return !empty($res) ? $res : response()->json('No se ha podido encontrar ningún post público', 422);
    }

    public function store(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $res = (new Post)->store($request);
        return response()->json(['Post creado con éxito', $res]);
    }

    public function update(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $res = (new Post)->update($request);
        return response()->json(['Post actualizado con éxito', $res]);
    }

    public function destroy(Request $request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        (new Post)->destroy($request->id);
        return response()->json('Post eliminado correctamente');
    }
}
