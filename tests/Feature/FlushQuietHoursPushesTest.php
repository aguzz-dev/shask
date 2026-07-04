<?php

use App\Database;
use App\Services\PushNotifier;
use Carbon\Carbon;

// ── T4.3.1: push:flush-quiet-hours ───────────────────────────────────────────
//
// Delivers individual pushes that were deferred during quiet-hours once the
// window has closed. Never discards them (creator-acquisition-push /
// Frequency Caps and Quiet Hours — deferred push is deferred, not dropped).

beforeEach(function () {
    $this->db = new Database;
    $suffix   = uniqid();

    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype, fcm_token)
         VALUES ('Flush Creator', 'fc_{$suffix}', 'fc_{$suffix}@test.com', 'x', 0, 'fake-token')"
    );
    $this->creatorId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM creator_push_log WHERE creator_user_id = {$this->creatorId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->creatorId}");
});

it('flushes a deferred push once the quiet-hours window has ended', function () {
    $this->db->query(
        "INSERT INTO creator_push_log (creator_user_id, day, acquired_count, deferred_pending)
         VALUES ({$this->creatorId}, CURDATE(), 1, 1)"
    );

    // "Now" is outside quiet hours, so the deferred row is eligible to flush.
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

    $this->artisan('push:flush-quiet-hours')->assertExitCode(0);

    $row = $this->db->query(
        "SELECT single_sent, deferred_pending FROM creator_push_log
         WHERE creator_user_id = {$this->creatorId} AND day = CURDATE()"
    )->fetch_assoc();

    expect((int) $row['single_sent'])->toBe(1)
        ->and((int) $row['deferred_pending'])->toBe(0);
});

it('does not flush a deferred row while still inside quiet hours', function () {
    $this->db->query(
        "INSERT INTO creator_push_log (creator_user_id, day, acquired_count, deferred_pending)
         VALUES ({$this->creatorId}, CURDATE(), 1, 1)"
    );

    config([
        'marketplace.push_quiet_start' => Carbon::now()->subHour()->format('H:i'),
        'marketplace.push_quiet_end'   => Carbon::now()->addHour()->format('H:i'),
    ]);

    // Shared dev DB (like NotifyLifecycleTest): don't assert a global call
    // count, only that OUR row was never touched by this run.
    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->zeroOrMoreTimes()
        ->withArgs(fn (int $userId) => $userId !== (int) $this->creatorId)->andReturn(true);
    $push->shouldReceive('sendToUser')
        ->withArgs(fn (int $userId) => $userId === (int) $this->creatorId)->never();

    $this->artisan('push:flush-quiet-hours')->assertExitCode(0);

    $row = $this->db->query(
        "SELECT single_sent, deferred_pending FROM creator_push_log
         WHERE creator_user_id = {$this->creatorId} AND day = CURDATE()"
    )->fetch_assoc();

    expect((int) $row['single_sent'])->toBe(0)
        ->and((int) $row['deferred_pending'])->toBe(1);
});

it('does not touch rows that are not deferred', function () {
    $this->db->query(
        "INSERT INTO creator_push_log (creator_user_id, day, acquired_count, single_sent)
         VALUES ({$this->creatorId}, CURDATE(), 1, 1)"
    );

    config([
        'marketplace.push_quiet_start' => Carbon::now()->addHours(3)->format('H:i'),
        'marketplace.push_quiet_end'   => Carbon::now()->addHours(4)->format('H:i'),
    ]);

    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->zeroOrMoreTimes()
        ->withArgs(fn (int $userId) => $userId !== (int) $this->creatorId)->andReturn(true);
    $push->shouldReceive('sendToUser')
        ->withArgs(fn (int $userId) => $userId === (int) $this->creatorId)->never();

    $this->artisan('push:flush-quiet-hours')->assertExitCode(0);
});
