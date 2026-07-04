<?php

use App\Database;
use App\Services\PushNotifier;

// ── T4.3.2: push:creator-digest ──────────────────────────────────────────────
//
// Reconciled scenario (gate-review): a creator with 3 acquisitions the same
// day and threshold N=2 gets 1 individual push (Pieza 1, already sent by the
// time the digest runs) + 1 daily digest — never 3 separate individual
// pushes, and never digest-only. Cap <=2 pushes/creator/day via
// single_sent + digest_sent.

beforeEach(function () {
    $this->db = new Database;
    $suffix   = uniqid();

    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype, fcm_token)
         VALUES ('Digest Creator', 'dc_{$suffix}', 'dc_{$suffix}@test.com', 'x', 0, 'fake-token')"
    );
    $this->creatorId = $this->db->dbConnection->insert_id;

    config(['marketplace.push_digest_threshold' => 2]);
});

afterEach(function () {
    $this->db->query("DELETE FROM creator_push_log WHERE creator_user_id = {$this->creatorId}");
    $this->db->query("DELETE FROM users WHERE id = {$this->creatorId}");
});

it('sends a digest (1 individual + 1 digest, not digest-only) when acquired_count crosses the threshold', function () {
    // Simulates the reconciled scenario: 3 acquisitions today, Pieza 1
    // already sent the individual push for the first one (single_sent=1).
    $this->db->query(
        "INSERT INTO creator_push_log (creator_user_id, day, acquired_count, single_sent)
         VALUES ({$this->creatorId}, CURDATE(), 3, 1)"
    );

    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->zeroOrMoreTimes()
        ->withArgs(fn (int $userId) => $userId !== (int) $this->creatorId)->andReturn(true);
    $push->shouldReceive('sendToUser')
        ->once()
        ->withArgs(fn (int $userId, string $title, string $body, array $data) =>
            $userId === (int) $this->creatorId && ($data['type'] ?? null) === 'asset_acquired')
        ->andReturn(true);

    $this->artisan('push:creator-digest')->assertExitCode(0);

    $row = $this->db->query(
        "SELECT single_sent, digest_sent FROM creator_push_log
         WHERE creator_user_id = {$this->creatorId} AND day = CURDATE()"
    )->fetch_assoc();

    // Cap respected: exactly one of each flag, never a 3rd/4th push.
    expect((int) $row['single_sent'])->toBe(1)
        ->and((int) $row['digest_sent'])->toBe(1);
});

it('does not send a digest when acquired_count is below the threshold', function () {
    $this->db->query(
        "INSERT INTO creator_push_log (creator_user_id, day, acquired_count, single_sent)
         VALUES ({$this->creatorId}, CURDATE(), 1, 1)"
    );

    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->zeroOrMoreTimes()
        ->withArgs(fn (int $userId) => $userId !== (int) $this->creatorId)->andReturn(true);
    $push->shouldReceive('sendToUser')
        ->withArgs(fn (int $userId) => $userId === (int) $this->creatorId)->never();

    $this->artisan('push:creator-digest')->assertExitCode(0);

    $row = $this->db->query(
        "SELECT digest_sent FROM creator_push_log
         WHERE creator_user_id = {$this->creatorId} AND day = CURDATE()"
    )->fetch_assoc();

    expect((int) $row['digest_sent'])->toBe(0);
});

it('is idempotent: does not re-send a digest already marked as sent', function () {
    $this->db->query(
        "INSERT INTO creator_push_log (creator_user_id, day, acquired_count, single_sent, digest_sent)
         VALUES ({$this->creatorId}, CURDATE(), 5, 1, 1)"
    );

    $push = $this->mock(PushNotifier::class);
    $push->shouldReceive('sendToUser')->zeroOrMoreTimes()
        ->withArgs(fn (int $userId) => $userId !== (int) $this->creatorId)->andReturn(true);
    $push->shouldReceive('sendToUser')
        ->withArgs(fn (int $userId) => $userId === (int) $this->creatorId)->never();

    $this->artisan('push:creator-digest')->assertExitCode(0);
});
