<?php

use App\Database;
use App\Models\Achievement;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->db->query("INSERT INTO users (full_name, username, email, password, hype)
        VALUES ('Test Logros', 'ach_{$suffix}', 'ach_{$suffix}@test.com', 'x', 0)");
    $this->userId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM achievement_user WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

function statsBase(): array
{
    return [
        'questions_received' => 0,
        'questions_answered' => 0,
        'mailboxes_created'  => 0,
        'member_since'       => '2024-01-01',
        'hype'               => 0,
        'has_custom_avatar'  => false,
        'max_unread_in_a_mailbox' => 0,
    ];
}

it('desbloquea los logros cuyas condiciones se cumplen', function () {
    $stats = array_merge(statsBase(), ['hype' => 1200, 'mailboxes_created' => 1]);
    $model = new Achievement;
    $model->evaluate($this->userId, $stats);

    $unlocked = array_keys($model->unlockedFor($this->userId));
    sort($unlocked);
    expect($unlocked)->toBe(['first_mailbox', 'hype_1k']);
});

it('es idempotente y conserva logros con condicion transitoria', function () {
    $model = new Achievement;
    $model->evaluate($this->userId, array_merge(statsBase(), ['max_unread_in_a_mailbox' => 6]));
    // Segunda pasada: la condición ya no se cumple, el logro debe persistir.
    $model->evaluate($this->userId, statsBase());
    $model->evaluate($this->userId, statsBase());

    $unlocked = $model->unlockedFor($this->userId);
    expect($unlocked)->toHaveKey('mailbox_exploded')->toHaveCount(1);
});

it('lista el catalogo completo ordenado por peso con estado y nombre localizado', function () {
    $model = new Achievement;
    $model->evaluate($this->userId, array_merge(statsBase(), ['mailboxes_created' => 1]));

    $listEs = $model->listFor($this->userId, 'es');
    $listEn = $model->listFor($this->userId, 'en');

    expect(count($listEs))->toBe(9)
        ->and($listEs[0]['code'])->toBe('hype_10k')        // weight 90 primero
        ->and($listEs[0]['unlocked'])->toBeFalse()
        ->and($listEs[0]['name'])->toBe('10K de hype')
        ->and($listEn[0]['name'])->toBe('10K hype');

    $firstMailbox = collect($listEs)->firstWhere('code', 'first_mailbox');
    expect($firstMailbox['unlocked'])->toBeTrue()
        ->and($firstMailbox)->toHaveKey('unlocked_at');
});
