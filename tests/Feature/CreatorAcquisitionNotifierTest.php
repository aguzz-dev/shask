<?php

use App\Database;
use App\Services\CreatorAcquisitionNotifier;
use App\Services\PushNotifier;
use Carbon\Carbon;

// ── T4.2.3 / T4.2.4: CreatorAcquisitionNotifier (Pieza 1, real-time) ─────────

beforeEach(function () {
    $this->db = new Database;
    $suffix   = uniqid();

    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype, fcm_token)
         VALUES ('Notifier Creator', 'nc_{$suffix}', 'nc_{$suffix}@test.com', 'x', 0, 'fake-token')"
    );
    $this->creatorId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM creator_push_log WHERE creator_user_id = {$this->creatorId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->creatorId}");
});

function pushLogRow($db, int $creatorId): ?array
{
    $row = $db->query(
        "SELECT * FROM creator_push_log WHERE creator_user_id = {$creatorId} AND day = CURDATE()"
    )->fetch_assoc();
    return $row === false ? null : $row;
}

it('sends an immediate individual push outside quiet hours and sets single_sent', function () {
    // Force "now" to be outside the quiet-hours window regardless of the
    // wall-clock time the suite runs at.
    config([
        'marketplace.push_quiet_start' => Carbon::now()->addHours(3)->format('H:i'),
        'marketplace.push_quiet_end'   => Carbon::now()->addHours(4)->format('H:i'),
    ]);

    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')
        ->once()
        ->withArgs(fn (int $userId, string $title, string $body, array $data) =>
            $userId === (int) $this->creatorId && ($data['type'] ?? null) === 'asset_acquired')
        ->andReturn(true);

    $notifier = app(CreatorAcquisitionNotifier::class);
    $notifier->notify((int) $this->creatorId, 99);

    $row = pushLogRow($this->db, (int) $this->creatorId);
    expect($row)->not->toBeNull()
        ->and((int) $row['acquired_count'])->toBe(1)
        ->and((int) $row['single_sent'])->toBe(1)
        ->and((int) $row['deferred_pending'])->toBe(0);
});

it('defers the push (never discards it) during quiet hours', function () {
    config([
        'marketplace.push_quiet_start' => Carbon::now()->subHour()->format('H:i'),
        'marketplace.push_quiet_end'   => Carbon::now()->addHour()->format('H:i'),
    ]);

    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->never();

    $notifier = app(CreatorAcquisitionNotifier::class);
    $notifier->notify((int) $this->creatorId, 99);

    $row = pushLogRow($this->db, (int) $this->creatorId);
    expect($row)->not->toBeNull()
        ->and((int) $row['acquired_count'])->toBe(1)
        ->and((int) $row['single_sent'])->toBe(0)
        ->and((int) $row['deferred_pending'])->toBe(1);
});

it('does not send a second individual push the same day (cap respected)', function () {
    config([
        'marketplace.push_quiet_start' => Carbon::now()->addHours(3)->format('H:i'),
        'marketplace.push_quiet_end'   => Carbon::now()->addHours(4)->format('H:i'),
    ]);

    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->once()->andReturn(true);

    $notifier = app(CreatorAcquisitionNotifier::class);
    $notifier->notify((int) $this->creatorId, 99);
    // Second acquisition same day — must only bump acquired_count, no 2nd push.
    $notifier->notify((int) $this->creatorId, 100);

    $row = pushLogRow($this->db, (int) $this->creatorId);
    expect((int) $row['acquired_count'])->toBe(2)
        ->and((int) $row['single_sent'])->toBe(1);
});

it('creator with no fcm_token still completes without throwing (opt-out respected)', function () {
    config([
        'marketplace.push_quiet_start' => Carbon::now()->addHours(3)->format('H:i'),
        'marketplace.push_quiet_end'   => Carbon::now()->addHours(4)->format('H:i'),
    ]);

    // Real PushNotifier (no mock): sendToUser returns false when fcm_token
    // is missing, per PushNotifier::sendToUser — the notifier must not throw.
    $this->db->query("UPDATE users SET fcm_token = NULL WHERE id = {$this->creatorId}");

    $notifier = new CreatorAcquisitionNotifier(new PushNotifier);

    $threw = false;
    try {
        $notifier->notify((int) $this->creatorId, 99);
    } catch (\Throwable $e) {
        $threw = true;
    }

    expect($threw)->toBeFalse();

    $row = pushLogRow($this->db, (int) $this->creatorId);
    // single_sent stays 0 because sendToUser returned false (no token) —
    // the notifier doesn't set the flag on a failed send, so a later
    // opportunity (e.g. once fcm_token is set) could still notify.
    expect((int) $row['single_sent'])->toBe(0);
});
