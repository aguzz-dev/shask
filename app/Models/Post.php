<?php
namespace App\Models;

use App\Database;
use Carbon\Carbon;

class Post extends Database
{
    protected $table = 'posts';

    const DEFAULT_ASSET = 0;

    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
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

    public function getAllPosts($userId): array
    {
        $userId = (int) $userId;
        $posts = [];

        $allPosts = $this->query("SELECT posts.*, public_posts.url,
                (SELECT COUNT(*) FROM questions WHERE questions.public_post_id = posts.id AND questions.status = 0) AS sin_responder,
                (SELECT COUNT(*) FROM questions WHERE questions.public_post_id = posts.id) AS total_questions,
                (SELECT COUNT(*) FROM questions WHERE questions.public_post_id = posts.id AND questions.hint IS NOT NULL AND questions.hint != '') AS with_hint,
                (SELECT COUNT(*) FROM questions WHERE questions.public_post_id = posts.id AND questions.status = 1) AS answered
            FROM {$this->table} AS posts
            LEFT JOIN public_posts ON public_posts.post_id = posts.id
            WHERE posts.user_id = '{$userId}'");

        foreach ($allPosts as $post) {
            $post['sin_responder']   = (int) $post['sin_responder'];
            $post['total_questions'] = (int) $post['total_questions'];
            $post['with_hint']       = (int) $post['with_hint'];
            $post['answered']        = (int) $post['answered'];
            $post['closed']          = (strtotime($post['expires_at']) <= time()) ? 1 : 0;
            // Compat con versiones viejas de la app: vencido oculta el post
            $post['vencido'] = $post['closed'];
            $posts[] = $post;
        }

        return $posts;
    }


    public function store($request)
    {
        $userId = (int) $request->id;
        $title  = $request->title;
        $fechaHoy = Carbon::now()->toDateString();
        $expiresAt = Carbon::now()->addHours(72)->toDateTimeString();
        $this->query("INSERT INTO {$this->table} (`title`, `asset_id`, `user_id`, `created_at`, `expires_at`)
            VALUES ('{$title}', '{$request->asset_id}', '{$userId}', '{$fechaHoy}', '{$expiresAt}')");
        $idPost = $this->dbConnection->insert_id;

        return (new PublicPost)->makePublicPost($idPost);
    }

    public function update($request)
    {
        $post = $this->findById($request->id);
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
        return $this->findById($request->id);
    }

    public function destroy($id)
    {
        $post = $this->findById($id);
        if(count($post) == 0){
            return response()->json('Post no encontrado', 404);
        }
        $sql = "DELETE FROM {$this->table} WHERE id = {$id}";
        $this->query($sql);
        return response()->json('Post eliminado con éxito', 200);
    }

    public function getUserIdByPostId($postId)
    {
        return $this->query("SELECT user_id FROM {$this->table} WHERE id = '{$postId}'")->fetch_all(MYSQLI_ASSOC);
    }
}
