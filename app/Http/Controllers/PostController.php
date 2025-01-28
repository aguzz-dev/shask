<?php
namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;

class PostController extends Controller
{
    public function index(Request $request):array
    {
        $userId = $request->id;
        return (new Post)->getAllPosts($userId);
    }

    public function getPostByIdWithQuestions(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new Post)->findById($request->id);
        return !empty($res) ? $res : response()->json('No se ha podido encontrar ningún post público', 422);
    }

    public function store(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new Post)->store($request);
        return response()->json(['Post creado con éxito', $res]);
    }

    public function update(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $res = (new Post)->update($request);
        return response()->json(['Post actualizado con éxito', $res]);
    }

    public function destroy(Request $request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
         return (new Post)->destroy($request->id);
    }
}
