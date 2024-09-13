<?php
namespace App\Models;

use App\Database;
use App\Middleware\VerifyToken;
use App\Models\PersonalAccessToken;

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
        return $this->query("SELECT * FROM {$this->table} WHERE post_id = $postId")->fetch_all(MYSQLI_ASSOC);
    }

    public function store($request)
    {
        $publicPostId = $request->id_post;
        $isPublicPostExist = (new Post)->getPostId($publicPostId);
        if (!$isPublicPostExist) {
            throw new \Exception('Post público no encontrado', 404);
        }
        $text = $request->text;
        $hint = $request->hint;
        $this->query("INSERT INTO `{$this->table}` (post_id, text, hint) VALUES ({$publicPostId}, '{$text}', '{$hint}')");
        return [
            'id' => $this->dbConnection->insert_id,
            'text' => $text,
            'id_post' => $publicPostId
        ];
    }

    public function answerQuestion($request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $question = $this->findById($request->id)[0];
        if(!$question){
            throw new \Exception('Pregunta no encontrada', 404);
        }
        $this->query("UPDATE {$this->table} SET status = 1 WHERE id = {$request->id}");
        return [
            'id' => $question['id'],
            'text' => $question['text'],
            'post_id' => $question['post_id'],
            'status' => Question::RESPONDIDA
        ];
    }
}
