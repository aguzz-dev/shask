<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype, avatar)
        VALUES ('Web Test', 'web_{$suffix}', 'web_{$suffix}@test.com', 'x', 0, '{}')");
    $this->userId = $this->db->dbConnection->insert_id;
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at)
        VALUES ('Cerrado web', 1, {$this->userId}, 1, CURDATE(), DATE_SUB(NOW(), INTERVAL 1 HOUR))");
    $this->postId = $this->db->dbConnection->insert_id;
    $this->url = 'w' . substr($suffix, -3);
    $this->db->query("INSERT INTO public_posts (post_id, user_id, url)
        VALUES ({$this->postId}, {$this->userId}, '{$this->url}')");
    $this->ppId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM questions WHERE public_post_id = {$this->postId}");
    $this->db->query("DELETE FROM public_posts WHERE id = {$this->ppId}");
    $this->db->query("DELETE FROM posts WHERE id = {$this->postId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('la pagina del buzon cerrado muestra el estado cerrado', function () {
    $response = $this->get("/{$this->url}");
    $response->assertOk();
    $response->assertViewIs('MailboxClosed');
});

it('rechaza preguntas a un buzon cerrado con 410 y no inserta', function () {
    $this->postJson('/api/question/create-web', [
        'id_post' => $this->postId,
        'text' => 'llego tarde',
        'hint' => '',
    ])->assertStatus(410);

    $row = $this->db->query(
        "SELECT COUNT(*) AS c FROM questions WHERE public_post_id = {$this->postId}"
    )->fetch_assoc();
    expect((int) $row['c'])->toBe(0);
});
