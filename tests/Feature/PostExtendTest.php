<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Ext Test', 'ext_{$suffix}', 'ext_{$suffix}@test.com', 'x', 100)");
    $this->userId = $this->db->dbConnection->insert_id;
    $this->token = str_repeat('e', 100) . $suffix;
    $this->db->query("INSERT INTO personal_access_tokens (token, user_id)
        VALUES ('{$this->token}', {$this->userId})");
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at)
        VALUES ('Extensible', 1, {$this->userId}, 1, CURDATE(), DATE_ADD(NOW(), INTERVAL 10 HOUR))");
    $this->postId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM posts WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('extiende 24h con hype y descuenta el precio', function () {
    $this->postJson('/api/posts/extend',
        ['id' => $this->postId, 'user_id' => $this->userId, 'source' => 'hype'],
        ['Authorization' => "Bearer {$this->token}"])->assertOk();

    $post = $this->db->query("SELECT * FROM posts WHERE id = {$this->postId}")->fetch_assoc();
    $user = $this->db->query("SELECT hype FROM users WHERE id = {$this->userId}")->fetch_assoc();
    expect(strtotime($post['expires_at']))->toBeGreaterThan(time() + 33 * 3600)
        ->and((int) $post['extended'])->toBe(1)
        ->and((int) $user['hype'])->toBe(100 - (int) config('app.hype_extend'));
});

it('extiende gratis con source=ad sin tocar hype', function () {
    $this->postJson('/api/posts/extend',
        ['id' => $this->postId, 'user_id' => $this->userId, 'source' => 'ad'],
        ['Authorization' => "Bearer {$this->token}"])->assertOk();

    $user = $this->db->query("SELECT hype FROM users WHERE id = {$this->userId}")->fetch_assoc();
    expect((int) $user['hype'])->toBe(100);
});

it('rechaza la segunda extension del ciclo con 409', function () {
    $this->db->query("UPDATE posts SET extended = 1 WHERE id = {$this->postId}");
    $this->postJson('/api/posts/extend',
        ['id' => $this->postId, 'user_id' => $this->userId, 'source' => 'ad'],
        ['Authorization' => "Bearer {$this->token}"])->assertStatus(409);
});

it('rechaza hype insuficiente con 422', function () {
    $this->db->query("UPDATE users SET hype = 3 WHERE id = {$this->userId}");
    $this->postJson('/api/posts/extend',
        ['id' => $this->postId, 'user_id' => $this->userId, 'source' => 'hype'],
        ['Authorization' => "Bearer {$this->token}"])->assertStatus(422);
});

it('rechaza extender un buzon vencido con 409', function () {
    $this->db->query("UPDATE posts SET expires_at = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE id = {$this->postId}");
    $this->postJson('/api/posts/extend',
        ['id' => $this->postId, 'user_id' => $this->userId, 'source' => 'ad'],
        ['Authorization' => "Bearer {$this->token}"])->assertStatus(409);
});
