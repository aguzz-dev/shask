<?php
namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Asset;
use App\Models\Question;
use App\Models\Blacklist;
use App\Models\PublicPost;
use App\Models\PublicAsset;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function getQuestionById(Request $request)
    {
        $res = (new Question)->findById($request->id);
        if(empty($res)){
            return response()->json('Pregunta no encontrada', 404);
        }
        return response()->json(['Solicitud exitosa', $res]);
    }

    public function getQuestionsByPostId(Request $request)
    {
        $postId = $request->id;
        return (new Question)->getQuestionsByPostId($postId);
    }

    public function store(Request $request)
    {
        $res = (new Question)->store($request);
        return response()->json(['Pregunta creada con éxito', $res]);
    }

    public function storeQuestionFromWeb(Request $request)
    {
        $userId = (new Post)->getUserIdByPostId($request->id_post)[0]['user_id'];
        $isBlacklisted = (new Blacklist)->findByIp($request->ip(), $userId);
        if($isBlacklisted){
            return response()->json('Usuario bloqueado por molesto', 423);
        }
        $res = (new Question)->store((object)$request, $userId);
        return response()->json(['Pregunta creada con éxito', $res]);
    }

    public function answerQuestion(Request $request)
    {
        $res = (new Question)->answerQuestion($request);
        return response()->json(['Se ha actualizado el estado de la pregunta a Respondida', $res]);
    }

    public function sendQuestion($url)
    {
        $existPost = (new PublicPost)->getPostDataByUrl($url);
        if(!$existPost){
            return view('errors/404');
        }
        if ($existPost['asset_id'] > 10000){
            $dataPost = (new Asset)->findById($existPost['asset_id'])[0];
        }else {
            $dataPost = (new PublicAsset)->findById($existPost['asset_id'])[0];
        }
        $userData = (new User)->findById($existPost['user_id'])[0];
        return view('Index', [
            'idPublicPost' => $existPost['id'],
            'idPost' => $existPost['post_id'],
            'idUser' => $existPost['user_id'],
            'fullNameUser' => $userData['full_name'],
            'usernameUser' => $userData['username'],
            'emailUser' => $userData['email'],
            'avatarUser' => json_decode($userData['avatar']),
            'title' => $existPost['title'],
            'url' => $existPost['url'],
            'colors' => json_decode($dataPost['color']),
            'assetIcon' => $dataPost['icon']
        ]);
    }
}
