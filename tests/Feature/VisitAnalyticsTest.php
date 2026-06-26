<?php

use App\Database;
use App\Models\Post;
use App\Models\UserStats;
use App\Services\PushNotifier;

beforeEach(function () {
    $this->db = new Database;
    $suffix   = uniqid();

    // Usuario con avatar (necesario para que la vista Index no falle con json_decode)
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype, avatar)
         VALUES ('Visit Test', 'va_{$suffix}', 'va_{$suffix}@test.com', 'x', 0, '{}')"
    );
    $this->userId = $this->db->dbConnection->insert_id;

    $this->token = str_repeat('k', 100) . $suffix;
    $this->db->query(
        "INSERT INTO personal_access_tokens (token, user_id) VALUES ('{$this->token}', {$this->userId})"
    );

    // Asset público mínimo para que sendQuestion no explote en la rama activa
    $this->db->query("INSERT INTO public_assets (color, icon) VALUES ('{}', 'star')");
    $this->assetId = $this->db->dbConnection->insert_id;

    // Buzón activo (vence mañana)
    $this->db->query(
        "INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at)
         VALUES ('Active Box', {$this->assetId}, {$this->userId}, 1, CURDATE(),
                 DATE_ADD(NOW(), INTERVAL 1 DAY))"
    );
    $this->postId = $this->db->dbConnection->insert_id;
    $this->url    = 'va' . substr($suffix, -3);
    $this->db->query(
        "INSERT INTO public_posts (post_id, user_id, url)
         VALUES ({$this->postId}, {$this->userId}, '{$this->url}')"
    );
    $this->ppId = $this->db->dbConnection->insert_id;

    // Buzón cerrado (venció hace 1 hora)
    $this->db->query(
        "INSERT INTO posts (title, asset_id, user_id, status, created_at, expires_at)
         VALUES ('Closed Box', {$this->assetId}, {$this->userId}, 1, CURDATE(),
                 DATE_SUB(NOW(), INTERVAL 1 HOUR))"
    );
    $this->closedPostId = $this->db->dbConnection->insert_id;
    $this->closedUrl    = 'vc' . substr($suffix, -3);
    $this->db->query(
        "INSERT INTO public_posts (post_id, user_id, url)
         VALUES ({$this->closedPostId}, {$this->userId}, '{$this->closedUrl}')"
    );
    $this->closedPpId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query(
        "DELETE FROM post_view_dedup WHERE post_id IN ({$this->postId}, {$this->closedPostId})"
    );
    $this->db->query(
        "DELETE FROM public_posts WHERE id IN ({$this->ppId}, {$this->closedPpId})"
    );
    $this->db->query(
        "DELETE FROM posts WHERE id IN ({$this->postId}, {$this->closedPostId})"
    );
    $this->db->query("DELETE FROM public_assets WHERE id = {$this->assetId}");
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->userId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

// --- B.1.4: incremento de visitas humanas ---

it('incrementa views y unique_views en la primera visita humana', function () {
    config(['visit_tracking.daily_salt' => 'test_salt_fixed']);

    // El template de Index puede 500 por datos incompletos del test; el conteo
    // ocurre ANTES del render, así que la validación es sobre la BD.
    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
         ->withHeaders(['User-Agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0)'])
         ->get("/{$this->url}");

    $row = $this->db->query(
        "SELECT views, unique_views FROM posts WHERE id = {$this->postId}"
    )->fetch_assoc();

    expect((int) $row['views'])->toBe(1)
        ->and((int) $row['unique_views'])->toBe(1);
});

it('incrementa views pero NO unique_views en visita repetida del mismo visitante en el mismo dia', function () {
    config(['visit_tracking.daily_salt' => 'test_salt_fixed']);

    $env = ['REMOTE_ADDR' => '10.0.0.2'];
    $ua  = 'Mozilla/5.0 (Android 13)';

    $this->withServerVariables($env)->withHeaders(['User-Agent' => $ua])->get("/{$this->url}");
    $this->withServerVariables($env)->withHeaders(['User-Agent' => $ua])->get("/{$this->url}");

    $row = $this->db->query(
        "SELECT views, unique_views FROM posts WHERE id = {$this->postId}"
    )->fetch_assoc();

    expect((int) $row['views'])->toBe(2)
        ->and((int) $row['unique_views'])->toBe(1);
});

it('ignora bots y prefetch de redes sociales sin contar ninguna vista', function () {
    config(['visit_tracking.daily_salt' => 'test_salt_fixed']);

    $this->withHeaders(['User-Agent' => 'facebookexternalhit/1.1'])->get("/{$this->url}");

    $row = $this->db->query(
        "SELECT views, unique_views FROM posts WHERE id = {$this->postId}"
    )->fetch_assoc();

    expect((int) $row['views'])->toBe(0)
        ->and((int) $row['unique_views'])->toBe(0);
});

it('cuenta visitas a un buzon cerrado igual que a uno activo', function () {
    config(['visit_tracking.daily_salt' => 'test_salt_fixed']);

    $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.3'])
         ->withHeaders(['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0)'])
         ->get("/{$this->closedUrl}")
         ->assertOk();

    $row = $this->db->query(
        "SELECT views FROM posts WHERE id = {$this->closedPostId}"
    )->fetch_assoc();

    expect((int) $row['views'])->toBe(1);
});

// --- B.1.7: totales en UserStats ---

it('total_views y total_unique_views suman los posts del usuario', function () {
    // Pre-cargar contadores directamente sin pasar por HTTP
    $this->db->query(
        "UPDATE posts SET views = 80, unique_views = 50 WHERE id = {$this->postId}"
    );
    $this->db->query(
        "UPDATE posts SET views = 40, unique_views = 20 WHERE id = {$this->closedPostId}"
    );

    $stats = (new UserStats)->forUser($this->userId);

    expect($stats['total_views'])->toBe(120)
        ->and($stats['total_unique_views'])->toBe(70);
});

// --- B.1.5: purga del scheduler ---

it('el scheduler de lifecycle purga filas de dedup con mas de 2 dias', function () {
    // Insertar fila de hace 3 días (debe purgar) y una de hoy (debe conservar)
    $this->db->query(
        "INSERT INTO post_view_dedup (post_id, day, visitor_hash)
         VALUES ({$this->postId}, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'old_hash_abc123')"
    );
    $this->db->query(
        "INSERT IGNORE INTO post_view_dedup (post_id, day, visitor_hash)
         VALUES ({$this->postId}, CURDATE(), 'today_hash_xyz789')"
    );

    // Mockear PushNotifier para que el comando no intente enviar notificaciones reales
    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->zeroOrMoreTimes()->andReturn(true);

    $this->artisan('posts:notify-lifecycle')->assertExitCode(0);

    $old = $this->db->query(
        "SELECT COUNT(*) AS c FROM post_view_dedup
         WHERE post_id = {$this->postId} AND day < DATE_SUB(CURDATE(), INTERVAL 2 DAY)"
    )->fetch_assoc();

    $today = $this->db->query(
        "SELECT COUNT(*) AS c FROM post_view_dedup
         WHERE post_id = {$this->postId} AND day = CURDATE()"
    )->fetch_assoc();

    expect((int) $old['c'])->toBe(0)
        ->and((int) $today['c'])->toBe(1);
});
