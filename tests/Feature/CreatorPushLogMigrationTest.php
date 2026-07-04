<?php

use App\Database;
use Illuminate\Support\Facades\Schema;

// ── T4.2.1: creator_push_log migration ───────────────────────────────────────
//
// Backs the three-piece batching model (D4.2): real-time single push,
// quiet-hours deferral, and daily digest — all deduped per creator/day via
// this table's flags.

it('creates the creator_push_log table with the expected columns', function () {
    expect(Schema::hasTable('creator_push_log'))->toBeTrue();

    foreach ([
        'id',
        'creator_user_id',
        'day',
        'acquired_count',
        'single_sent',
        'digest_sent',
        'deferred_pending',
        'last_push_at',
    ] as $column) {
        expect(Schema::hasColumn('creator_push_log', $column))->toBeTrue();
    }
});

it('enforces a unique (creator_user_id, day) constraint', function () {
    $db     = new Database;
    $suffix = uniqid();

    $db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Push Log User', 'plu_{$suffix}', 'plu_{$suffix}@test.com', 'x', 0)"
    );
    $userId = $db->dbConnection->insert_id;

    $stmt = $db->dbConnection->prepare(
        "INSERT INTO creator_push_log (creator_user_id, day, acquired_count) VALUES (?, CURDATE(), 1)"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();

    $threw = false;
    try {
        $stmt2 = $db->dbConnection->prepare(
            "INSERT INTO creator_push_log (creator_user_id, day, acquired_count) VALUES (?, CURDATE(), 1)"
        );
        $stmt2->bind_param('i', $userId);
        $stmt2->execute();
        $stmt2->close();
    } catch (\Throwable $e) {
        $threw = true;
    }

    expect($threw)->toBeTrue();

    $db->query("DELETE FROM creator_push_log WHERE creator_user_id = {$userId}");
    $db->query("DELETE FROM users WHERE id = {$userId}");
});

it('cascades delete from users to creator_push_log', function () {
    $db     = new Database;
    $suffix = uniqid();

    $db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Push Log Cascade', 'plc_{$suffix}', 'plc_{$suffix}@test.com', 'x', 0)"
    );
    $userId = $db->dbConnection->insert_id;

    $stmt = $db->dbConnection->prepare(
        "INSERT INTO creator_push_log (creator_user_id, day, acquired_count) VALUES (?, CURDATE(), 1)"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stmt->close();

    $db->query("DELETE FROM users WHERE id = {$userId}");

    $remaining = $db->query(
        "SELECT COUNT(*) AS c FROM creator_push_log WHERE creator_user_id = {$userId}"
    )->fetch_assoc();

    expect((int) $remaining['c'])->toBe(0);
});
