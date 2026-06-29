<?php

use App\Database;

// ── Fixtures ──────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->db          = new Database;
    $this->assetIds    = [];
    $this->categoryIds = [];

    $suffix = uniqid();

    // Insert a category for category-filter tests
    $stmt = $this->db->dbConnection->prepare(
        "INSERT INTO categories (slug, name, position) VALUES (?, ?, 0)"
    );
    $slug = "filter-cat-{$suffix}";
    $name = "Filter Category {$suffix}";
    $stmt->bind_param('ss', $slug, $name);
    $stmt->execute();
    $this->categoryId  = $this->db->dbConnection->insert_id;
    $this->categoryIds[] = $this->categoryId;
    $this->categorySlug = $slug;
    $stmt->close();

    // Asset A — high downloads, no category, not featured
    $stmt2 = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, is_featured, category_id)
         VALUES (?, '[]', '', '', 'approved', ?, ?, ?)"
    );
    $titleA = "Neon Grid {$suffix}";
    $dlA = 100; $featA = 0; $catA = null;
    $stmt2->bind_param('siii', $titleA, $dlA, $featA, $catA);
    $stmt2->execute();
    $this->assetAId = $this->db->dbConnection->insert_id;
    $this->assetIds[] = $this->assetAId;

    // Asset B — low downloads, in category, not featured
    $titleB = "Dark Mode {$suffix}";
    $dlB = 5; $featB = 0;
    $stmt2->bind_param('siii', $titleB, $dlB, $featB, $this->categoryId);
    $stmt2->execute();
    $this->assetBId = $this->db->dbConnection->insert_id;
    $this->assetIds[] = $this->assetBId;

    // Asset C — medium downloads, no category, featured
    $titleC = "Retro Wave {$suffix}";
    $dlC = 50; $featC = 1; $catC = null;
    $stmt2->bind_param('siii', $titleC, $dlC, $featC, $catC);
    $stmt2->execute();
    $this->assetCId = $this->db->dbConnection->insert_id;
    $this->assetIds[] = $this->assetCId;

    $stmt2->close();
});

afterEach(function () {
    foreach ($this->assetIds as $id) {
        $this->db->query("DELETE FROM asset_user WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM asset_acquisitions WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    foreach ($this->categoryIds as $id) {
        $this->db->query("DELETE FROM categories WHERE id = {$id}");
    }
});

// ── T3.05: GET /api/assets with filters ───────────────────────────────────────

it('GET /api/assets without params returns backward-compatible response', function () {
    $response = $this->getJson('/api/assets');

    // Original response has 'public_assets' and 'assets' keys
    $response->assertOk()
             ->assertJsonStructure(['public_assets', 'assets']);
});

it('GET /api/assets?q= filters by title (case-insensitive LIKE)', function () {
    $response = $this->getJson('/api/assets?q=neon+grid');

    $response->assertOk()
             ->assertJsonStructure(['success', 'assets']);

    $ids = array_column($response->json('assets'), 'id');

    expect($ids)->toContain($this->assetAId)
                ->not->toContain($this->assetBId)
                ->not->toContain($this->assetCId);
});

it('GET /api/assets?sort=trending orders by downloads_count DESC', function () {
    $response = $this->getJson('/api/assets?sort=trending');

    $response->assertOk()
             ->assertJsonStructure(['success', 'assets']);

    $assets = collect($response->json('assets'))
        ->whereIn('id', $this->assetIds)
        ->values()
        ->toArray();

    // Asset A (100) > Asset C (50) > Asset B (5)
    $idxA = array_search($this->assetAId, array_column($assets, 'id'));
    $idxC = array_search($this->assetCId, array_column($assets, 'id'));
    $idxB = array_search($this->assetBId, array_column($assets, 'id'));

    expect($idxA)->toBeLessThan($idxC)
                 ->and($idxC)->toBeLessThan($idxB);
});

it('GET /api/assets?category=slug filters by category slug', function () {
    $response = $this->getJson('/api/assets?category=' . $this->categorySlug);

    $response->assertOk()
             ->assertJsonStructure(['success', 'assets']);

    $ids = array_column($response->json('assets'), 'id');

    // Only Asset B belongs to this category
    expect($ids)->toContain($this->assetBId)
                ->not->toContain($this->assetAId)
                ->not->toContain($this->assetCId);
});

it('GET /api/assets?category=nonexistent returns empty assets array', function () {
    $response = $this->getJson('/api/assets?category=does-not-exist-xyz');

    $response->assertOk()
             ->assertJson(['success' => true, 'assets' => []]);
});

it('GET /api/assets?featured=1 returns only featured assets', function () {
    $response = $this->getJson('/api/assets?featured=1');

    $response->assertOk()
             ->assertJsonStructure(['success', 'assets']);

    $ids = array_column($response->json('assets'), 'id');

    // Only Asset C is featured in our fixture
    expect($ids)->toContain($this->assetCId)
                ->not->toContain($this->assetBId);
});

it('SQL injection attempt in q param is handled safely', function () {
    // This should run without error and return no results (or safe results)
    $response = $this->getJson("/api/assets?q=" . urlencode("' OR 1=1 --"));

    $response->assertOk()
             ->assertJsonStructure(['success', 'assets']);
});
