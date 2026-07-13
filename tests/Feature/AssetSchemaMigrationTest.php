<?php

use App\Database;
use Illuminate\Support\Facades\Schema;

// ── Batch 1: marketplace-item-showcase — momentum schema foundation ───────────
//
// Two additive changes: a nullable `created_at` on `public_assets` (NEW-badge
// signal, legacy rows stay NULL forever — no invented history) and a
// composite index on `asset_acquisitions` supporting the 7-day momentum
// window query without a table scan.

it('public_assets has a nullable created_at column with no default backfill value', function () {
    expect(Schema::hasColumn('public_assets', 'created_at'))->toBeTrue();

    $db  = new Database;
    $row = $db->query('DESCRIBE public_assets')->fetch_all(MYSQLI_ASSOC);
    $col = collect($row)->firstWhere('Field', 'created_at');

    expect($col)->not->toBeNull()
        ->and($col['Null'])->toBe('YES');
});

it('a legacy row inserted without created_at reads NULL, never a fabricated timestamp', function () {
    $db     = new Database;
    $suffix = uniqid();

    $db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Schema Migration User', 'smu_{$suffix}', 'smu_{$suffix}@test.com', 'x', 0)"
    );
    $userId = $db->dbConnection->insert_id;

    // Insert WITHOUT specifying created_at, simulating a legacy row that
    // existed before this migration ran — the column must default to NULL,
    // never auto-stamp a fabricated "now" for pre-existing data.
    $stmt = $db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, submitter_user_id)
         VALUES (?, '[]', '', '', 'approved', ?)"
    );
    $title = "Legacy Row {$suffix}";
    $stmt->bind_param('si', $title, $userId);
    $stmt->execute();
    $assetId = $db->dbConnection->insert_id;
    $stmt->close();

    $row = $db->query("SELECT created_at FROM public_assets WHERE id = {$assetId}")->fetch_assoc();

    expect($row['created_at'])->toBeNull();

    $db->query("DELETE FROM public_assets WHERE id = {$assetId}");
    $db->query("DELETE FROM users WHERE id = {$userId}");
});

it('asset_acquisitions has the idx_acq_created_asset composite index', function () {
    $db  = new Database;
    $rows = $db->query("SHOW INDEX FROM asset_acquisitions WHERE Key_name = 'idx_acq_created_asset'")
                ->fetch_all(MYSQLI_ASSOC);

    expect($rows)->not->toBeEmpty();

    $columns = collect($rows)->sortBy('Seq_in_index')->pluck('Column_name')->values()->toArray();
    expect($columns)->toBe(['created_at', 'asset_id']);
});
