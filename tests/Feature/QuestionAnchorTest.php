<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Anchor Test', 'anchor_{$suffix}', 'anchor_{$suffix}@test.com', 'x', 0)");
    $this->userId = $this->db->dbConnection->insert_id;

    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at)
        VALUES ('Anchor post', 1, {$this->userId}, 1, CURDATE())");
    $this->postId = $this->db->dbConnection->insert_id;

    // public_post con id deliberadamente distinto a post_id (autoincrement propio)
    $url = 'a' . substr($suffix, -3);
    $this->db->query("INSERT INTO public_posts (post_id, user_id, url)
        VALUES ({$this->postId}, {$this->userId}, '{$url}')");
    $this->publicPostId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM questions WHERE public_post_id = {$this->postId}");
    $this->db->query("DELETE FROM public_posts WHERE id = {$this->publicPostId}");
    $this->db->query("DELETE FROM posts WHERE id = {$this->postId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('ancla la pregunta web al post id y la app la ve', function () {
    // La web manda id_post = posts.id (lo que renderiza la vista Index)
    $this->postJson('/api/question/create-web', [
        'id_post' => $this->postId,
        'text' => 'pregunta anclada',
        'hint' => '',
    ])->assertOk();

    // La app pide las preguntas con posts.id
    $response = $this->postJson('/api/question', ['id' => $this->postId]);
    $response->assertOk();
    expect($response->json())->toHaveCount(1)
        ->and($response->json()[0]['text'])->toBe('pregunta anclada');
});
