<?php

use App\Database;
use App\Models\Asset;
use App\Models\AssetUser;

// ── T4.2.2: AssetUser::buyAsset returns ?int $creatorId ──────────────────────
//
// Needed so AssetController::buyAsset can invoke CreatorAcquisitionNotifier
// post-commit (D4.1) without a second query to resolve the creator.

beforeEach(function () {
    $this->db = new Database;
    $suffix   = uniqid();

    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('BuyReturn Creator', 'brc_{$suffix}', 'brc_{$suffix}@test.com', 'x', 0)"
    );
    $this->creatorId = $this->db->dbConnection->insert_id;

    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('BuyReturn Buyer', 'brb_{$suffix}', 'brb_{$suffix}@test.com', 'x', 200)"
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

it('buyAsset returns the creator id for a UGC asset', function () {
    $assetId = (new Asset)->createPublicAsset('BuyReturn Test', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    $creatorId = (new AssetUser)->buyAsset($assetId, $this->buyerId, 'hype');

    expect($creatorId)->toBe((int) $this->creatorId);
});

it('buyAsset returns null when the public asset has no submitter (system preset)', function () {
    $stmt = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, submitter_user_id)
         VALUES (?, '[]', '', '', 'approved', 0, NULL)"
    );
    $title = 'BuyReturn Preset';
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $assetId = $this->db->dbConnection->insert_id;
    $stmt->close();
    $this->createdAssetIds[] = $assetId;

    $creatorId = (new AssetUser)->buyAsset($assetId, $this->buyerId, 'hype');

    expect($creatorId)->toBeNull();
});
