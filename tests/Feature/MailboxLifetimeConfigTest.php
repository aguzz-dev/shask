<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Life Test', 'life_{$suffix}', 'life_{$suffix}@test.com', 'x', 0)");
    $this->userId = $this->db->dbConnection->insert_id;
    $this->token = str_repeat('l', 100) . $suffix;
    $this->db->query("INSERT INTO personal_access_tokens (token, user_id)
        VALUES ('{$this->token}', {$this->userId})");
});

afterEach(function () {
    $this->db->query("DELETE FROM posts WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('crear un buzon usa la duracion configurada en minutos', function () {
    config(['app.mailbox_lifetime_minutes' => 30]);

    $this->postJson('/api/posts/create',
        ['id' => $this->userId, 'asset_id' => 1, 'title' => 'Corto'],
        ['Authorization' => "Bearer {$this->token}"])->assertOk();

    $row = $this->db->query(
        "SELECT expires_at FROM posts WHERE user_id = {$this->userId} AND title = 'Corto'"
    )->fetch_assoc();
    $secondsLeft = strtotime($row['expires_at']) - time();
    // ~30 min (1800s); margen amplio por la latencia del test.
    expect($secondsLeft)->toBeGreaterThan(1700)->toBeLessThan(1900);
});

it('renovar usa la duracion configurada en minutos', function () {
    config(['app.mailbox_lifetime_minutes' => 30]);

    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at)
        VALUES ('Renov', 1, {$this->userId}, 1, CURDATE(), DATE_ADD(NOW(), INTERVAL 1 HOUR))");
    $postId = $this->db->dbConnection->insert_id;

    $this->postJson('/api/posts/renew',
        ['id' => $postId, 'user_id' => $this->userId],
        ['Authorization' => "Bearer {$this->token}"])->assertOk();

    $row = $this->db->query("SELECT expires_at FROM posts WHERE id = {$postId}")->fetch_assoc();
    $secondsLeft = strtotime($row['expires_at']) - time();
    expect($secondsLeft)->toBeGreaterThan(1700)->toBeLessThan(1900);
});
