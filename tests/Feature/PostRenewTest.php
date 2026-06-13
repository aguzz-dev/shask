<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Renew Test', 'rnw_{$suffix}', 'rnw_{$suffix}@test.com', 'x', 0)");
    $this->userId = $this->db->dbConnection->insert_id;
    $this->token = str_repeat('w', 100) . $suffix;
    $this->db->query("INSERT INTO personal_access_tokens (token, user_id)
        VALUES ('{$this->token}', {$this->userId})");
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at,
        extended, notified_24h, notified_2h, notified_closed)
        VALUES ('Renovable', 1, {$this->userId}, 1, CURDATE(), DATE_ADD(NOW(), INTERVAL 2 HOUR), 1, 1, 1, 0)");
    $this->postId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM posts WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('renovar resetea ciclo, flags y alimenta la racha', function () {
    $response = $this->postJson('/api/posts/renew',
        ['id' => $this->postId, 'user_id' => $this->userId],
        ['Authorization' => "Bearer {$this->token}"]);
    $response->assertOk();

    $post = $this->db->query("SELECT * FROM posts WHERE id = {$this->postId}")->fetch_assoc();
    expect(strtotime($post['expires_at']))->toBeGreaterThan(time() + 71 * 3600)
        ->and((int) $post['extended'])->toBe(0)
        ->and((int) $post['renewed_count'])->toBe(1)
        ->and((int) $post['notified_24h'])->toBe(0)
        ->and((int) $post['notified_2h'])->toBe(0);

    $user = $this->db->query("SELECT streak_days, streak_date FROM users WHERE id = {$this->userId}")->fetch_assoc();
    expect((int) $user['streak_days'])->toBe(1)
        ->and($user['streak_date'])->toBe(date('Y-m-d'));
});

it('renovar dos veces el mismo dia no duplica la racha', function () {
    $this->postJson('/api/posts/renew', ['id' => $this->postId, 'user_id' => $this->userId],
        ['Authorization' => "Bearer {$this->token}"])->assertOk();
    $this->postJson('/api/posts/renew', ['id' => $this->postId, 'user_id' => $this->userId],
        ['Authorization' => "Bearer {$this->token}"])->assertOk();

    $user = $this->db->query("SELECT streak_days FROM users WHERE id = {$this->userId}")->fetch_assoc();
    expect((int) $user['streak_days'])->toBe(1);
});

it('la racha incrementa si ayer estaba viva', function () {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $this->db->query("UPDATE users SET streak_days = 4, streak_date = '{$yesterday}' WHERE id = {$this->userId}");

    $this->postJson('/api/posts/renew', ['id' => $this->postId, 'user_id' => $this->userId],
        ['Authorization' => "Bearer {$this->token}"])->assertOk();

    $user = $this->db->query("SELECT streak_days FROM users WHERE id = {$this->userId}")->fetch_assoc();
    expect((int) $user['streak_days'])->toBe(5);
});

it('rechaza renovar un buzon ajeno', function () {
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Otro', 'otr_{$suffix}', 'otr_{$suffix}@test.com', 'x', 0)");
    $otherId = $this->db->dbConnection->insert_id;
    $otherToken = str_repeat('o', 100) . $suffix;
    $this->db->query("INSERT INTO personal_access_tokens (token, user_id) VALUES ('{$otherToken}', {$otherId})");

    $this->postJson('/api/posts/renew', ['id' => $this->postId, 'user_id' => $otherId],
        ['Authorization' => "Bearer {$otherToken}"])->assertForbidden();

    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$otherId}");
    $this->db->query("DELETE FROM users WHERE id = {$otherId}");
});

it('rechaza renovar un buzon vencido', function () {
    $this->db->query("UPDATE posts SET expires_at = DATE_SUB(NOW(), INTERVAL 1 HOUR) WHERE id = {$this->postId}");
    $this->postJson('/api/posts/renew', ['id' => $this->postId, 'user_id' => $this->userId],
        ['Authorization' => "Bearer {$this->token}"])->assertStatus(409);
});
