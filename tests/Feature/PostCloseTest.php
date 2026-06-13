<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Close Test', 'cls_{$suffix}', 'cls_{$suffix}@test.com', 'x', 0)");
    $this->userId = $this->db->dbConnection->insert_id;
    $this->token = str_repeat('c', 100) . $suffix;
    $this->db->query("INSERT INTO personal_access_tokens (token, user_id)
        VALUES ('{$this->token}', {$this->userId})");
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at, notified_closed)
        VALUES ('Activo', 1, {$this->userId}, 1, CURDATE(), DATE_ADD(NOW(), INTERVAL 10 HOUR), 0)");
    $this->postId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM posts WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('cierra el buzon: lo vence y marca notified_closed', function () {
    $response = $this->postJson('/api/posts/close',
        ['id' => $this->postId, 'user_id' => $this->userId],
        ['Authorization' => "Bearer {$this->token}"]);
    $response->assertOk();

    $post = $this->db->query("SELECT * FROM posts WHERE id = {$this->postId}")->fetch_assoc();
    expect(strtotime($post['expires_at']))->toBeLessThanOrEqual(time() + 2)
        ->and((int) $post['notified_closed'])->toBe(1);

    // El post devuelto ya viaja como cerrado
    expect((int) $response->json('1.closed'))->toBe(1);
});

it('rechaza cerrar un buzon ya cerrado con 409', function () {
    $this->db->query("UPDATE posts SET expires_at = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE id = {$this->postId}");
    $this->postJson('/api/posts/close',
        ['id' => $this->postId, 'user_id' => $this->userId],
        ['Authorization' => "Bearer {$this->token}"])->assertStatus(409);
});

it('rechaza cerrar un buzon ajeno', function () {
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Otro', 'oc_{$suffix}', 'oc_{$suffix}@test.com', 'x', 0)");
    $otherId = $this->db->dbConnection->insert_id;
    $otherToken = str_repeat('z', 100) . $suffix;
    $this->db->query("INSERT INTO personal_access_tokens (token, user_id) VALUES ('{$otherToken}', {$otherId})");

    $this->postJson('/api/posts/close',
        ['id' => $this->postId, 'user_id' => $otherId],
        ['Authorization' => "Bearer {$otherToken}"])->assertForbidden();

    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$otherId}");
    $this->db->query("DELETE FROM users WHERE id = {$otherId}");
});
