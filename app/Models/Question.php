<?php
namespace App\Models;

use App\Database;
use Illuminate\Support\Facades\DB;

class Question extends Database
{
    const NO_RESPONDIDA = 0;
    const RESPONDIDA = 1;
    protected $table = 'questions';

    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }

    public function getQuestionsByPostId($postId)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE public_post_id = $postId")->fetch_all(MYSQLI_ASSOC);
    }

    public function store($request, $userId)
    {
        $publicPostId = $request->id_post;

        $isPublicPostExist = (new PublicPost)->findById($publicPostId);

        if (!$isPublicPostExist) {
            throw new \Exception('Post público no encontrado', 404);
        }
        $text = $request->text;
        $hint = $request->hint;
        $hype = (new User)->getHypeById($userId)['hype'];
        $hype++;
        $this->query("UPDATE users SET hype = {$hype} WHERE id = {$userId}");

        $id = DB::table($this->table)->insertGetId([
            'public_post_id' => $isPublicPostExist[0]['id'],
            'text' => $text,
            'hint' => $hint,
            'ip' => $request->ip(),
        ]);

        return [
            'id' => $id,
            'text' => $text,
            'hint' => $hint,
            'id_post' => $publicPostId
        ];
    }

    public function answerQuestion($request)
    {
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $question = $this->findById($request->id)[0];
        if(!$question){
            throw new \Exception('Pregunta no encontrada', 404);
        }
        $this->query("UPDATE {$this->table} SET status = 1 WHERE id = {$request->id}");
        return [
            'id' => $question['id'],
            'text' => $question['text'],
            'post_id' => $question['public_post_id'],
            'status' => Question::RESPONDIDA
        ];
    }
}
