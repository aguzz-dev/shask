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

/** Stats base con todos los campos que evaluate() puede consumir. */
function statsBase(): array
{
    return [
        'questions_received'            => 0,
        'questions_answered'            => 0,
        'mailboxes_created'             => 0,
        'member_since'                  => '2024-01-01',
        'hype'                          => 0,
        'has_custom_avatar'             => false,
        'max_unread_in_a_mailbox'       => 0,
        'total_views'                   => 0,
        'total_unique_views'            => 0,
        'max_unique_views_in_a_mailbox' => 0,
        'best_conversion'               => 0.0,
        'first_revive'                  => false,
        'streak_days'                   => 0,
    ];
}

// ── Tests originales ──────────────────────────────────────────────────────────

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

    // El catálogo ahora incluye los nuevos codes de Slice C: 20 en total.
    expect(count($listEs))->toBeGreaterThanOrEqual(20)
        ->and($listEs[0]['code'])->toBe('hype_10k')        // weight 90 primero
        ->and($listEs[0]['unlocked'])->toBeFalse()
        ->and($listEs[0]['name'])->toBe('10K de hype')
        ->and($listEn[0]['name'])->toBe('10K hype');

    // El campo reward está presente en todos los items del catálogo.
    foreach ($listEs as $item) {
        expect($item)->toHaveKey('reward');
    }

    $firstMailbox = collect($listEs)->firstWhere('code', 'first_mailbox');
    expect($firstMailbox['unlocked'])->toBeTrue()
        ->and($firstMailbox)->toHaveKey('unlocked_at');
});

// ── Tests nuevos: Slice C ─────────────────────────────────────────────────────

it('evaluate devuelve solo los codes recien desbloqueados (delta)', function () {
    $model = new Achievement;
    $delta = $model->evaluate($this->userId, array_merge(statsBase(), ['mailboxes_created' => 1]));

    expect($delta)->toBeArray()
        ->and($delta)->toContain('first_mailbox')
        ->and(count($delta))->toBe(1);
});

it('evaluate es idempotente: reintento devuelve array vacio', function () {
    $model  = new Achievement;
    $stats  = array_merge(statsBase(), ['mailboxes_created' => 1]);
    $model->evaluate($this->userId, $stats);
    $second = $model->evaluate($this->userId, $stats);

    expect($second)->toBeArray()->toBeEmpty();
});

it('reach_100 se dispara al cruzar el umbral de vistas configurado', function () {
    $model = new Achievement;
    $stats = array_merge(statsBase(), [
        'total_views' => config('achievements.reach_100_views', 100),
    ]);
    $delta = $model->evaluate($this->userId, $stats);

    expect($delta)->toContain('reach_100');
});

it('first_revive se desbloquea cuando se pasa first_revive=true en stats', function () {
    $model = new Achievement;
    $delta = $model->evaluate($this->userId, array_merge(statsBase(), ['first_revive' => true]));

    expect($delta)->toContain('first_revive');
});

it('revive incluye newly_unlocked en la respuesta JSON', function () {
    // Post vencido para que revive lo acepte.
    $this->db->query(
        "INSERT INTO posts (title, user_id, expires_at, views, unique_views)
         VALUES ('Buzón C', {$this->userId}, DATE_SUB(NOW(), INTERVAL 2 HOUR), 0, 0)"
    );
    $postId = $this->db->dbConnection->insert_id;
    $url    = 'r' . substr(uniqid(), -8);
    $this->db->query(
        "INSERT INTO public_posts (post_id, user_id, url) VALUES ({$postId}, {$this->userId}, '{$url}')"
    );
    $token = str_repeat('v', 100) . uniqid();
    $this->db->query(
        "INSERT INTO personal_access_tokens (token, user_id) VALUES ('{$token}', {$this->userId})"
    );

    $response = $this->postJson(
        '/api/posts/revive',
        ['id' => $postId, 'user_id' => $this->userId, 'source' => 'ad'],
        ['Authorization' => "Bearer {$token}"]
    );

    $response->assertOk();
    // Respuesta: [mensaje, post_con_newly_unlocked].
    // El post (índice 1) debe contener el campo newly_unlocked.
    expect($response->json('1.newly_unlocked'))->toBeArray();

    // Limpieza
    $this->db->query("DELETE FROM personal_access_tokens WHERE token = '{$token}'");
    $this->db->query("DELETE FROM public_posts WHERE post_id = {$postId}");
    $this->db->query("DELETE FROM posts WHERE id = {$postId}");
});
