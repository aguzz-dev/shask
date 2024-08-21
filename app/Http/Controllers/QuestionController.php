<?php
namespace App\Http\Controllers;

use Exception;
use App\Models\Question;
use App\Helpers\JsonRequest;
use Illuminate\Http\Request;
use App\Helpers\JsonResponse;

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
        $res = (new Question)->store((object)$request);
        return response()->json(['Pregunta creada con éxito', $res]);
    }

    public function answerQuestion(Request $request)
    {
        $res = (new Question)->answerQuestion($request);
        return response()->json(['Se ha actualizado el estado de la pregunta a Respondida', $res]);
    }
}
