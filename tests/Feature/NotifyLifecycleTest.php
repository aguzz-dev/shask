<?php

use App\Database;
use App\Services\PushNotifier;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype, fcm_token)
        VALUES ('Ntf Test', 'ntf_{$suffix}', 'ntf_{$suffix}@test.com', 'x', 0, 'fake-token')");
    $this->userId = $this->db->dbConnection->insert_id;

    $mk = function (string $title, string $expiresExpr, int $n24, int $n2, int $nc) {
        $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at,
            notified_24h, notified_2h, notified_closed)
            VALUES ('{$title}', 1, {$this->userId}, 1, CURDATE(), {$expiresExpr}, {$n24}, {$n2}, {$nc})");
        return $this->db->dbConnection->insert_id;
    };
    $this->post24h   = $mk('Por vencer 24', 'DATE_ADD(NOW(), INTERVAL 10 HOUR)', 0, 0, 0);
    $this->post2h    = $mk('Por vencer 2', 'DATE_ADD(NOW(), INTERVAL 1 HOUR)', 1, 0, 0);
    $this->postDead  = $mk('Muerto', 'DATE_SUB(NOW(), INTERVAL 1 HOUR)', 1, 1, 0);
    $this->postQuiet = $mk('Tranquilo', 'DATE_ADD(NOW(), INTERVAL 60 HOUR)', 0, 0, 0);
});

afterEach(function () {
    $this->db->query("DELETE FROM posts WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('notifica cada umbral una sola vez y marca flags', function () {
    // La tabla posts es global (DB compartida): no fijamos un conteo exacto de
    // envíos. El comportamiento se verifica por los flags de nuestros posts.
    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->zeroOrMoreTimes()->andReturn(true);

    $this->artisan('posts:notify-lifecycle')->assertExitCode(0);

    $flags = fn (int $id) => $this->db->query(
        "SELECT notified_24h, notified_2h, notified_closed FROM posts WHERE id = {$id}")->fetch_assoc();

    expect((int) $flags($this->post24h)['notified_24h'])->toBe(1)
        ->and((int) $flags($this->post2h)['notified_2h'])->toBe(1)
        ->and((int) $flags($this->postDead)['notified_closed'])->toBe(1)
        ->and((int) $flags($this->postQuiet)['notified_24h'])->toBe(0);
});

it('es idempotente: la segunda corrida no vuelve a notificar mis posts', function () {
    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->zeroOrMoreTimes()->andReturn(true);
    $this->artisan('posts:notify-lifecycle')->assertExitCode(0);

    // Tras la primera corrida, todos mis posts ya tienen sus flags en 1.
    $mine = $this->db->query("SELECT notified_24h, notified_2h, notified_closed
        FROM posts WHERE user_id = {$this->userId}
        AND (expires_at <= DATE_ADD(NOW(), INTERVAL 24 HOUR))")->fetch_all(MYSQLI_ASSOC);

    // La segunda corrida no debe disparar NINGÚN envío a mi usuario.
    $push2 = $this->mock(PushNotifier::class);
    $push2->shouldReceive('sendToUser')->zeroOrMoreTimes()
        ->withArgs(fn (int $userId) => $userId !== $this->userId)->andReturn(true);
    $push2->shouldReceive('sendToUser')
        ->withArgs(fn (int $userId) => $userId === $this->userId)->never();

    $this->artisan('posts:notify-lifecycle')->assertExitCode(0);
});
