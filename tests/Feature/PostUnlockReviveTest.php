<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Ulk Test', 'ulk_{$suffix}', 'ulk_{$suffix}@test.com', 'x', 100)");
    $this->userId = $this->db->dbConnection->insert_id;
    $this->token = str_repeat('u', 100) . $suffix;
    $this->db->query("INSERT INTO personal_access_tokens (token, user_id)
        VALUES ('{$this->token}', {$this->userId})");
    // Buzón vencido con flags de notificación gastados
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at,
        extended, notified_24h, notified_2h, notified_closed)
        VALUES ('Cerrado', 1, {$this->userId}, 1, CURDATE(), DATE_SUB(NOW(), INTERVAL 5 HOUR), 1, 1, 1, 1)");
    $this->postId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM posts WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('desbloquea un buzon cerrado cobrando hype', function () {
    $this->postJson('/api/posts/unlock',
        ['id' => $this->postId, 'user_id' => $this->userId, 'source' => 'hype'],
        ['Authorization' => "Bearer {$this->token}"])->assertOk();

    $post = $this->db->query("SELECT unlocked FROM posts WHERE id = {$this->postId}")->fetch_assoc();
    $user = $this->db->query("SELECT hype FROM users WHERE id = {$this->userId}")->fetch_assoc();
    expect((int) $post['unlocked'])->toBe(1)
        ->and((int) $user['hype'])->toBe(100 - (int) config('app.hype_unlock'));
});

it('rechaza desbloquear un buzon activo con 409', function () {
    $this->db->query("UPDATE posts SET expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = {$this->postId}");
    $this->postJson('/api/posts/unlock',
        ['id' => $this->postId, 'user_id' => $this->userId, 'source' => 'ad'],
        ['Authorization' => "Bearer {$this->token}"])->assertStatus(409);
});

it('revivir arranca ciclo nuevo, desbloquea y resetea flags', function () {
    config(['app.mailbox_lifetime_minutes' => 4320]); // independiente del .env local
    $this->postJson('/api/posts/revive',
        ['id' => $this->postId, 'user_id' => $this->userId, 'source' => 'hype'],
        ['Authorization' => "Bearer {$this->token}"])->assertOk();

    $post = $this->db->query("SELECT * FROM posts WHERE id = {$this->postId}")->fetch_assoc();
    $user = $this->db->query("SELECT hype FROM users WHERE id = {$this->userId}")->fetch_assoc();
    expect(strtotime($post['expires_at']))->toBeGreaterThan(time() + 71 * 3600)
        ->and((int) $post['unlocked'])->toBe(1)
        ->and((int) $post['extended'])->toBe(0)
        ->and((int) $post['notified_24h'])->toBe(0)
        ->and((int) $post['notified_closed'])->toBe(0)
        ->and((int) $user['hype'])->toBe(100 - (int) config('app.hype_revive'));

    // Revivir también alimenta la racha
    $u = $this->db->query("SELECT streak_days FROM users WHERE id = {$this->userId}")->fetch_assoc();
    expect((int) $u['streak_days'])->toBe(1);
});

it('rechaza revivir un buzon activo con 409', function () {
    $this->db->query("UPDATE posts SET expires_at = DATE_ADD(NOW(), INTERVAL 1 HOUR) WHERE id = {$this->postId}");
    $this->postJson('/api/posts/revive',
        ['id' => $this->postId, 'user_id' => $this->userId, 'source' => 'ad'],
        ['Authorization' => "Bearer {$this->token}"])->assertStatus(409);
});
