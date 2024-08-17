<?php
namespace App\Models;

use App\Helpers\JsonResponse;
use App\Database;
use App\Helpers\GetHeader;
use App\Traits\FindTrait;

class Post extends Database
{
    use FindTrait;
    protected $table = 'posts';

    const DEFAULT_ASSET = 0;

    public function findById($id)
    {
        return $this->find($id);
    }

    public function findPublishPostById($postId)
    {
        return $this->query(
            "SELECT p.*, COUNT(q.id) AS total_questions
            FROM posts p
            LEFT JOIN questions q ON p.id = q.post_id
            WHERE p.status = 1 AND p.id = '{$postId}'
            GROUP BY p.id"
        )->fetch_all(MYSQLI_ASSOC);
    }

    public function getPostId($postId)
    {
        return $this->query("SELECT `id` FROM {$this->table} WHERE id = '{$postId}'")->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllPosts($userId):array
    {
        $posts = [];
        $allPosts = $this->query("SELECT * FROM " . $this->table. " WHERE user_id = '{$userId}'") ;
        foreach($allPosts as $post){
            array_push($posts, $post);
        }
        return $posts;
    }

    public function store($request)
    {
        $title  = $request->title;
        $userId = (new PersonalAccessToken)->getIdByToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $this->query("INSERT INTO {$this->table} (`title`, `asset_id`, `user_id`) VALUES ('{$title}', '{$request->asset_id}', '{$userId}')");
        $idPost = $this->dbConnection->insert_id;
        $postCreated = $this->find($idPost);
        return $postCreated;
    }

    public function update($request)
    {
        $post = $this->find($request->id);
        if(!$post){
            return response()->json('Post no encontrado', 404);
        }
        $fields = [];
        foreach ($request as $key => $value) {
            $fields[] = "{$key} = '{$value}'";
        }
        unset($fields[0]);
        $fields = implode(', ', $fields);
        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = {$request->id}";
        $this->query($sql);
        return $this->find($request->id);
    }

    public function destroy($id)
    {
        $post = $this->find($id);
        if(!$post){
            return response()->json('Post no encontrado', 404);
        }
        $sql = "DELETE FROM {$this->table} WHERE id = {$id}";
        $this->query($sql);
    }
}
