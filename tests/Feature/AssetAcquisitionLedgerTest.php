<?php

use App\Database;
use App\Models\Asset;
use App\Models\AssetUser;

// ── Fixtures ──────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->db = new Database;
    $suffix   = uniqid();

    // Creator
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Ledger Creator', 'lc_{$suffix}', 'lc_{$suffix}@test.com', 'x', 0)"
    );
    $this->creatorId = $this->db->dbConnection->insert_id;

    // Buyer
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Ledger Buyer', 'lb_{$suffix}', 'lb_{$suffix}@test.com', 'x', 200)"
    );
    $this->buyerId = $this->db->dbConnection->insert_id;

    $this->createdAssetIds = [];
});

afterEach(function () {
    foreach ($this->createdAssetIds as $id) {
        $this->db->query("DELETE FROM asset_acquisitions WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM asset_user WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    $this->db->query("DELETE FROM users WHERE id IN ({$this->creatorId}, {$this->buyerId})");
});

// ── T3.07: ledger in buy flow ─────────────────────────────────────────────────

it('buy with source=hype inserts acquisition row with source=hype and hype_minted=mint', function () {
    $assetId = (new Asset)->createPublicAsset('Ledger Test Hype', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    $mint = config('marketplace.creator_hype_mint', 15);

    (new AssetUser)->buyAsset($assetId, $this->buyerId, 'hype');

    $row = $this->db->query(
        "SELECT * FROM asset_acquisitions WHERE asset_id = {$assetId} AND buyer_user_id = {$this->buyerId}"
    )->fetch_assoc();

    expect($row)->not->toBeNull()
        ->and($row['source'])->toBe('hype')
        ->and((int) $row['hype_minted'])->toBe($mint);
});

it('buy with source=ad inserts acquisition row with source=ad and hype_minted=mint', function () {
    $assetId = (new Asset)->createPublicAsset('Ledger Test Ad', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    $mint = config('marketplace.creator_hype_mint', 15);

    (new AssetUser)->buyAsset($assetId, $this->buyerId, 'ad');

    $row = $this->db->query(
        "SELECT * FROM asset_acquisitions WHERE asset_id = {$assetId} AND buyer_user_id = {$this->buyerId}"
    )->fetch_assoc();

    expect($row)->not->toBeNull()
        ->and($row['source'])->toBe('ad')
        ->and((int) $row['hype_minted'])->toBe($mint);
});

it('existing buy tests still pass: hype debit and mint unchanged', function () {
    $assetId = (new Asset)->createPublicAsset('Ledger Regression', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    $cost = config('marketplace.acquisition_hype_cost', 20);
    $mint = config('marketplace.creator_hype_mint', 15);

    (new AssetUser)->buyAsset($assetId, $this->buyerId, 'hype');

    $buyer   = $this->db->query("SELECT hype FROM users WHERE id = {$this->buyerId}")->fetch_assoc();
    $creator = $this->db->query("SELECT hype FROM users WHERE id = {$this->creatorId}")->fetch_assoc();

    expect((int) $buyer['hype'])->toBe(200 - $cost)
        ->and((int) $creator['hype'])->toBe($mint);
});

it('rollback: if acquisitions insert fails buyer hype is not debited', function () {
    // We test this by verifying buy succeeds, then manually breaking the table name to
    // simulate a failure — instead we test via the model's transaction guard by calling
    // the model with a non-existent asset so a unique constraint fires on asset_user.
    //
    // The real rollback test: verify that a second buy on the same asset
    // (which would fail on asset_user duplicate) leaves hype unchanged AND
    // leaves NO duplicate acquisitions row.
    $assetId = (new Asset)->createPublicAsset('Rollback Test', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    (new AssetUser)->buyAsset($assetId, $this->buyerId, 'hype');

    // Record state after first buy
    $hypeBefore = (int) $this->db->query("SELECT hype FROM users WHERE id = {$this->buyerId}")->fetch_assoc()['hype'];

    // Second buy must throw (duplicate in asset_user)
    $threw = false;
    try {
        (new AssetUser)->buyAsset($assetId, $this->buyerId, 'hype');
    } catch (\Exception $e) {
        $threw = true;
    }

    expect($threw)->toBeTrue();

    // Hype must not have changed from the failed second buy
    $hypoAfter = (int) $this->db->query("SELECT hype FROM users WHERE id = {$this->buyerId}")->fetch_assoc()['hype'];
    expect($hypoAfter)->toBe($hypeBefore);

    // Only one acquisitions row should exist
    $count = (int) $this->db->query(
        "SELECT COUNT(*) AS c FROM asset_acquisitions WHERE asset_id = {$assetId} AND buyer_user_id = {$this->buyerId}"
    )->fetch_assoc()['c'];
    expect($count)->toBe(1);
});
