<?php

use App\Database;
use App\Models\Asset;
use App\Models\AssetUser;

// ── Fixtures ──────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->db    = new Database;
    $suffix      = uniqid();

    // Creator
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Stats Creator', 'sc_{$suffix}', 'sc_{$suffix}@test.com', 'x', 0)"
    );
    $this->creatorId = $this->db->dbConnection->insert_id;

    // Creator token
    $this->creatorToken = 'stok_c_' . $suffix;
    $this->db->query(
        "INSERT INTO personal_access_tokens (token, user_id)
         VALUES ('{$this->creatorToken}', {$this->creatorId})"
    );

    // Buyer (acquires the creator's assets to populate the ledger)
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Stats Buyer', 'sb_{$suffix}', 'sb_{$suffix}@test.com', 'x', 500)"
    );
    $this->buyerId = $this->db->dbConnection->insert_id;

    $this->assetIds = [];
});

afterEach(function () {
    foreach ($this->assetIds as $id) {
        $this->db->query("DELETE FROM asset_acquisitions WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM asset_user WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$this->creatorId}");
    $this->db->query("DELETE FROM users WHERE id IN ({$this->creatorId}, {$this->buyerId})");
});

// ── T3.09: GET /api/creator/stats ─────────────────────────────────────────────

it('GET /api/creator/stats returns 401 when no auth token provided', function () {
    $response = $this->getJson('/api/creator/stats');
    $response->assertStatus(401);
});

it('GET /api/creator/stats returns 401 for invalid token', function () {
    $response = $this->getJson('/api/creator/stats', [
        'Authorization' => 'Bearer invalid-token-xyz',
    ]);
    $response->assertStatus(401);
});

it('GET /api/creator/stats returns empty stats when creator has no designs', function () {
    $response = $this->getJson('/api/creator/stats', [
        'Authorization' => "Bearer {$this->creatorToken}",
    ]);

    $response->assertOk()
             ->assertJson(['success' => true])
             ->assertJsonPath('stats.total_downloads', 0)
             ->assertJsonPath('stats.designs_count', 0)
             ->assertJsonPath('stats.designs', []);
});

it('GET /api/creator/stats returns correct aggregates with designs and acquisitions', function () {
    $mint = config('marketplace.creator_hype_mint', 15);

    // Create two assets for the creator
    $idA = (new Asset)->createPublicAsset('Stats Design A', [], '', '', null, $this->creatorId);
    $this->assetIds[] = $idA;
    $idB = (new Asset)->createPublicAsset('Stats Design B', [], '', '', null, $this->creatorId);
    $this->assetIds[] = $idB;

    // Force approved so the buyer can purchase them
    $this->db->query("UPDATE public_assets SET status = 'approved' WHERE id IN ({$idA}, {$idB})");

    // Buyer acquires both assets (each acquisition mints $mint to creator)
    (new AssetUser)->buyAsset($idA, $this->buyerId, 'hype');
    (new AssetUser)->buyAsset($idB, $this->buyerId, 'hype');

    $response = $this->getJson('/api/creator/stats', [
        'Authorization' => "Bearer {$this->creatorToken}",
    ]);

    $response->assertOk()
             ->assertJson(['success' => true]);

    $stats = $response->json('stats');
    expect($stats['designs_count'])->toBe(2)
        ->and($stats['total_downloads'])->toBe(2);

    // Each design should have hype_earned = $mint (one acquisition each)
    $designA = collect($stats['designs'])->firstWhere('id', $idA);
    $designB = collect($stats['designs'])->firstWhere('id', $idB);

    expect((int) $designA['hype_earned'])->toBe($mint)
        ->and((int) $designB['hype_earned'])->toBe($mint);
});

it('GET /api/creator/stats response includes total_hype from users table', function () {
    // Give the creator some hype directly
    $this->db->query("UPDATE users SET hype = 99 WHERE id = {$this->creatorId}");

    $response = $this->getJson('/api/creator/stats', [
        'Authorization' => "Bearer {$this->creatorToken}",
    ]);

    $response->assertOk()
             ->assertJsonPath('stats.total_hype', 99);
});

it('GET /api/creator/stats response structure is correct', function () {
    $response = $this->getJson('/api/creator/stats', [
        'Authorization' => "Bearer {$this->creatorToken}",
    ]);

    $response->assertOk()
             ->assertJsonStructure([
                 'success',
                 'stats' => [
                     'total_downloads',
                     'total_hype',
                     'designs_count',
                     'designs',
                 ],
             ]);
});
