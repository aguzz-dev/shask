<?php

use App\Database;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Endpoint Test', 'ep_{$suffix}', 'ep_{$suffix}@test.com', 'x', 1500)");
    $this->userId = $this->db->dbConnection->insert_id;
    $this->token = str_repeat('t', 100) . $suffix;
    $this->db->query("INSERT INTO personal_access_tokens (token, user_id)
        VALUES ('{$this->token}', {$this->userId})");
});

afterEach(function () {
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM achievement_user WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('devuelve stats y logros, y persiste los desbloqueos', function () {
    $response = $this->postJson('/api/user/stats',
        ['id' => $this->userId, 'lang' => 'es'],
        ['Authorization' => "Bearer {$this->token}"]);

    $response->assertOk()
        ->assertJsonPath('stats.questions_received', 0)
        ->assertJsonPath('stats.questions_answered', 0)
        ->assertJsonPath('stats.mailboxes_created', 0)
        ->assertJsonPath('stats.streak_days', 0)
        // El catálogo ahora incluye los logros de Slice C (20 en total).
        ->assertJsonCount(20, 'achievements')
        ->assertJsonPath('achievements.0.code', 'hype_10k')
        // El endpoint ahora incluye el delta de desbloqueos.
        ->assertJsonStructure(['newly_unlocked']);

    // hype 1500 => hype_1k desbloqueado y persistido
    $hype1k = collect($response->json('achievements'))->firstWhere('code', 'hype_1k');
    expect($hype1k['unlocked'])->toBeTrue();

    $row = $this->db->query(
        "SELECT COUNT(*) AS c FROM achievement_user WHERE user_id = {$this->userId}"
    )->fetch_assoc();
    expect((int) $row['c'])->toBeGreaterThanOrEqual(1);
});
