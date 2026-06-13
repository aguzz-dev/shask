<?php

use App\Database;
use App\Services\PushNotifier;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $yesterday = date('Y-m-d', strtotime('-1 day'));

    // Usuario con buzón activo y racha de ayer → tick debe incrementar
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype, fcm_token, streak_days, streak_date)
        VALUES ('Tick', 'tck_{$suffix}', 'tck_{$suffix}@test.com', 'x', 0, 'fake', 3, '{$yesterday}')");
    $this->activeUser = $this->db->dbConnection->insert_id;
    $this->db->query("INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at)
        VALUES ('Activo', 1, {$this->activeUser}, 1, CURDATE(), DATE_ADD(NOW(), INTERVAL 10 HOUR))");

    // Usuario con racha de ayer y SIN buzón activo → warn debe avisarle
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype, fcm_token, streak_days, streak_date)
        VALUES ('Warn', 'wrn_{$suffix}', 'wrn_{$suffix}@test.com', 'x', 0, 'fake', 5, '{$yesterday}')");
    $this->riskUser = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM posts WHERE user_id IN ({$this->activeUser}, {$this->riskUser})");
    $this->db->query("DELETE FROM users WHERE id IN ({$this->activeUser}, {$this->riskUser})");
});

it('tick incrementa la racha de usuarios con buzon activo', function () {
    $this->artisan('streak:tick')->assertExitCode(0);

    $active = $this->db->query("SELECT streak_days, streak_date FROM users WHERE id = {$this->activeUser}")->fetch_assoc();
    $risk = $this->db->query("SELECT streak_days FROM users WHERE id = {$this->riskUser}")->fetch_assoc();
    expect((int) $active['streak_days'])->toBe(4)
        ->and($active['streak_date'])->toBe(date('Y-m-d'))
        ->and((int) $risk['streak_days'])->toBe(5); // sin buzón activo: no tickea
});

it('warn avisa solo a usuarios con racha en riesgo', function () {
    $push = $this->mock(PushNotifier::class);
    // Específica primero (Mockery matchea en orden de definición):
    $push->shouldReceive('sendToUser')
        ->withArgs(fn (int $userId) => $userId === $this->riskUser)->once()->andReturn(true);
    // Catch-all para cualquier otro usuario de la DB compartida:
    $push->shouldReceive('sendToUser')
        ->withArgs(fn (int $userId) => $userId !== $this->riskUser)->zeroOrMoreTimes()->andReturn(true);

    $this->artisan('streak:warn')->assertExitCode(0);
});
