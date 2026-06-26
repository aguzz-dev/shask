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

    // SELECT compartido por getAllPosts y findEnriched: trae url, recap y KPIs de visitas.
    private function enrichedSelect(string $where): string
    {
        return "SELECT posts.*, public_posts.url,
                (SELECT COUNT(*) FROM questions WHERE questions.public_post_id = posts.id AND questions.status = 0) AS sin_responder,
                (SELECT COUNT(*) FROM questions WHERE questions.public_post_id = posts.id) AS total_questions,
                (SELECT COUNT(*) FROM questions WHERE questions.public_post_id = posts.id AND questions.hint IS NOT NULL AND questions.hint != '') AS with_hint,
                (SELECT COUNT(*) FROM questions WHERE questions.public_post_id = posts.id AND questions.status = 1) AS answered
            FROM {$this->table} AS posts
            LEFT JOIN public_posts ON public_posts.post_id = posts.id
            WHERE {$where}";
    }

    // Normaliza tipos y deriva closed/vencido de expires_at.
    private function decoratePost(array $post): array
    {
        $post['sin_responder']   = (int) $post['sin_responder'];
        $post['total_questions'] = (int) $post['total_questions'];
        $post['with_hint']       = (int) $post['with_hint'];
        $post['answered']        = (int) $post['answered'];
        $post['views']           = (int) ($post['views'] ?? 0);
        $post['unique_views']    = (int) ($post['unique_views'] ?? 0);
        $post['closed']          = (strtotime($post['expires_at']) <= time()) ? 1 : 0;
        // Compat con versiones viejas de la app: vencido oculta el post
        $post['vencido'] = $post['closed'];
        return $post;
    }

    /**
     * Registra una visita humana al post. Debe llamarse ANTES de renderizar
     * la vista para que el conteo ocurra incluso si el template falla.
     *
     * Orden de operaciones (obligatorio según spec):
     *   1. INSERT IGNORE en post_view_dedup → si inserta, unique_views++
     *   2. views++ siempre
     *
     * Usa prepare()+bind_param() directamente; NO usar query() con interpolación.
     */
    public function incrementViews(int $postId, string $visitorHash): void
    {
        // Intento de dedup: INSERT IGNORE falla silenciosamente si la fila ya existe
        $stmt = $this->dbConnection->prepare(
            "INSERT IGNORE INTO post_view_dedup (post_id, day, visitor_hash)
             VALUES (?, CURDATE(), ?)"
        );
        $stmt->bind_param("is", $postId, $visitorHash);
        $stmt->execute();
        $inserted = $this->dbConnection->affected_rows;
        $stmt->close();

        // unique_views++ solo si es la primera visita del visitante en el día
        if ($inserted === 1) {
            $stmt = $this->dbConnection->prepare(
                "UPDATE posts SET unique_views = unique_views + 1 WHERE id = ?"
            );
            $stmt->bind_param("i", $postId);
            $stmt->execute();
            $stmt->close();
        }

        // views++ siempre para todas las visitas humanas
        $stmt = $this->dbConnection->prepare(
            "UPDATE posts SET views = views + 1 WHERE id = ?"
        );
        $stmt->bind_param("i", $postId);
        $stmt->execute();
        $stmt->close();
    }

    public function getAllPosts($userId): array
    {
        $userId = (int) $userId;
        $allPosts = $this->query($this->enrichedSelect("posts.user_id = '{$userId}'"));
        return array_map([$this, 'decoratePost'], $allPosts->fetch_all(MYSQLI_ASSOC));
    }

    /// Un solo post con la misma forma enriquecida que getAllPosts (url + recap
    /// + closed). Lo devuelven las acciones del ciclo de vida.
    public function findEnriched(int $id): ?array
    {
        $rows = $this->query($this->enrichedSelect("posts.id = {$id}"))
            ->fetch_all(MYSQLI_ASSOC);
        return $rows ? $this->decoratePost($rows[0]) : null;
    }


    public function store($request)
    {
        $userId = (int) $request->id;
        $title  = $request->title;
        $fechaHoy = Carbon::now()->toDateString();
        $expiresAt = Carbon::now()
            ->addMinutes((int) config('app.mailbox_lifetime_minutes'))
            ->toDateTimeString();
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
