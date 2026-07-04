<?php

use App\Database;
use App\Models\Asset;
use App\Models\PersonalAccessToken;
use App\Services\CreatorAcquisitionNotifier;
use Carbon\Carbon;

// ── T4.2.5: AssetController::buyAsset invokes CreatorAcquisitionNotifier ────

beforeEach(function () {
    $this->db = new Database;
    $suffix   = uniqid();

    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Buy Notify Creator', 'bnc_{$suffix}', 'bnc_{$suffix}@test.com', 'x', 0)"
    );
    $this->creatorId = $this->db->dbConnection->insert_id;

    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Buy Notify Buyer', 'bnb_{$suffix}', 'bnb_{$suffix}@test.com', 'x', 200)"
    );
    $this->buyerId = $this->db->dbConnection->insert_id;

    $this->buyerToken = (new PersonalAccessToken)->generateToken($this->buyerId);

    // Never fall inside quiet-hours during the test run.
    config([
        'marketplace.push_quiet_start' => Carbon::now()->addHours(3)->format('H:i'),
        'marketplace.push_quiet_end'   => Carbon::now()->addHours(4)->format('H:i'),
    ]);

    $this->createdAssetIds = [];
});

afterEach(function () {
    foreach ($this->createdAssetIds as $id) {
        $this->db->query("DELETE FROM asset_acquisitions WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM asset_user WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    $this->db->query("DELETE FROM creator_push_log WHERE creator_user_id = {$this->creatorId}");
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->buyerId}");
    $this->db->query("DELETE FROM users WHERE id IN ({$this->creatorId}, {$this->buyerId})");
});

it('invokes the notifier after a successful purchase from another creator', function () {
    $assetId = (new Asset)->createPublicAsset('Notify Test', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    $mock = $this->mock(CreatorAcquisitionNotifier::class);
    $mock->shouldReceive('notify')->once()->with((int) $this->creatorId, $assetId);

    $response = $this->postJson('/api/assets/buy', [
        'user_id'  => $this->buyerId,
        'asset_id' => $assetId,
        'source'   => 'hype',
    ], ['Authorization' => "Bearer {$this->buyerToken}"]);

    $response->assertOk();
});

it('does not invoke the notifier when the buyer is the creator', function () {
    $assetId = (new Asset)->createPublicAsset('Self Buy Test', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    $creatorToken = (new PersonalAccessToken)->generateToken($this->creatorId);

    $mock = $this->mock(CreatorAcquisitionNotifier::class);
    $mock->shouldReceive('notify')->never();

    $response = $this->postJson('/api/assets/buy', [
        'user_id'  => $this->creatorId,
        'asset_id' => $assetId,
        'source'   => 'ad', // avoids the hype-balance check for this self-buy edge case
    ], ['Authorization' => "Bearer {$creatorToken}"]);

    $response->assertOk();

    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->creatorId}");
});

it('purchase still succeeds (200) even if the notifier throws', function () {
    $assetId = (new Asset)->createPublicAsset('Notifier Failure Test', [], '', '', null, $this->creatorId);
    $this->createdAssetIds[] = $assetId;

    $mock = $this->mock(CreatorAcquisitionNotifier::class);
    $mock->shouldReceive('notify')->once()->andThrow(new \RuntimeException('push provider down'));

    $response = $this->postJson('/api/assets/buy', [
        'user_id'  => $this->buyerId,
        'asset_id' => $assetId,
        'source'   => 'hype',
    ], ['Authorization' => "Bearer {$this->buyerToken}"]);

    $response->assertOk()
             ->assertJson(['message' => 'Asset adquirido con éxito']);

    // The purchase itself must have gone through despite the notifier throwing.
    $owned = $this->db->query(
        "SELECT COUNT(*) AS c FROM asset_user WHERE asset_id = {$assetId} AND user_id = {$this->buyerId}"
    )->fetch_assoc();
    expect((int) $owned['c'])->toBe(1);
});
