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
        $posts = [];

        $allPosts = $this->query("SELECT posts.*, public_posts.url,
                                             (SELECT COUNT(*)
                                              FROM questions
                                              WHERE questions.public_post_id = public_posts.post_id
                                              AND questions.status = 0) AS sin_responder
                                      FROM {$this->table} AS posts
                                      LEFT JOIN public_posts ON public_posts.post_id = posts.id
                                      WHERE posts.user_id = '{$userId}'");

        foreach ($allPosts as $post) {
            $post['sin_responder'] = (int) $post['sin_responder'];

            $createdAt = Carbon::parse($post['created_at']);

            $post['vencido'] = $createdAt->diffInDays(Carbon::now()) > 3 ? 1 : 0;

            $posts[] = $post;
        }

        return $posts;
    }


    public function store($request)
    {
        $title  = $request->title;
        $userId = (new PersonalAccessToken)->getIdByToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $fechaHoy = Carbon::now()->toDateString();
        $this->query("INSERT INTO {$this->table} (`title`, `asset_id`, `user_id`, `created_at`) VALUES ('{$title}', '{$request->asset_id}', '{$userId}', '{$fechaHoy}')");
        $idPost = $this->dbConnection->insert_id;
        $postCreated = $this->findById($idPost);

        $this->eliminarPostsVencidos($userId);

        return $postCreated;
    }

    public function eliminarPostsVencidos($userId)
    {
        $fechaVencimiento = Carbon::now()->subDays(3)->toDateString();

        $this->query("DELETE FROM {$this->table} WHERE created_at <= '{$fechaVencimiento}' AND user_id = '{$userId}'");
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
}
