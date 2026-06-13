<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Resp Test', 'resp_{$suffix}', 'resp_{$suffix}@test.com', 'x', 0)");
    $this->userId = $this->db->dbConnection->insert_id;
    $this->token = str_repeat('r', 100) . $suffix;
    $this->db->query("INSERT INTO personal_access_tokens (token, user_id)
        VALUES ('{$this->token}', {$this->userId})");

    // Post activo con 1 pregunta sin leer con pista y 1 respondida
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at)
        VALUES ('Activo', 1, {$this->userId}, 1, CURDATE(), DATE_ADD(NOW(), INTERVAL 48 HOUR))");
    $this->activeId = $this->db->dbConnection->insert_id;
    $url = 'r' . substr($suffix, -3);
    $this->db->query("INSERT INTO public_posts (post_id, user_id, url) VALUES ({$this->activeId}, {$this->userId}, '{$url}')");
    $this->ppId = $this->db->dbConnection->insert_id;
    $this->db->query("INSERT INTO questions (public_post_id, text, hint, ip, status)
        VALUES ({$this->activeId}, 'sin leer', 'una pista', '1.1.1.1', 0)");
    $this->db->query("INSERT INTO questions (public_post_id, text, hint, ip, status)
        VALUES ({$this->activeId}, 'respondida', NULL, '1.1.1.1', 1)");

    // Post vencido
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at)
        VALUES ('Vencido', 1, {$this->userId}, 1, CURDATE(), DATE_SUB(NOW(), INTERVAL 1 HOUR))");
    $this->closedId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM questions WHERE public_post_id IN ({$this->activeId}, {$this->closedId})");
    $this->db->query("DELETE FROM public_posts WHERE post_id IN ({$this->activeId}, {$this->closedId})");
    $this->db->query("DELETE FROM posts WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('la lista de posts trae estado del ciclo y bloque recap', function () {
    $response = $this->postJson('/api/posts', ['id' => $this->userId],
        ['Authorization' => "Bearer {$this->token}"]);
    $response->assertOk();

    $posts = collect($response->json());
    $active = $posts->firstWhere('id', $this->activeId);
    $closed = $posts->firstWhere('id', $this->closedId);

    expect($active['closed'])->toBe(0)
        ->and($active['total_questions'])->toBe(2)
        ->and($active['with_hint'])->toBe(1)
        ->and($active['answered'])->toBe(1)
        ->and($active['sin_responder'])->toBe(1)
        ->and($active)->toHaveKeys(['expires_at', 'unlocked', 'extended', 'renewed_count'])
        ->and($closed['closed'])->toBe(1);
});

it('crear un post setea expires_at a 72h', function () {
    $response = $this->postJson('/api/posts/create',
        ['id' => $this->userId, 'asset_id' => 1, 'title' => 'Nuevo'],
        ['Authorization' => "Bearer {$this->token}"]);
    $response->assertOk();

    $row = $this->db->query(
        "SELECT expires_at FROM posts WHERE user_id = {$this->userId} AND title = 'Nuevo'"
    )->fetch_assoc();
    expect($row['expires_at'])->not->toBeNull();
    expect(strtotime($row['expires_at']))->toBeGreaterThan(time() + 71 * 3600);
});
