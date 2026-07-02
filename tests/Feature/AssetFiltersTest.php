<?php

use App\Database;

// ── Fixtures ──────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->db          = new Database;
    $this->assetIds    = [];
    $this->categoryIds = [];
    $this->presetId    = null;

    $suffix = uniqid();

    // Create a real user so fixtures are treated as user-submitted designs.
    // System presets have submitter_user_id = NULL; user designs have a real user id.
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Filter Test User', 'ftu_{$suffix}', 'ftu_{$suffix}@test.com', 'x', 0)"
    );
    $this->userId = $this->db->dbConnection->insert_id;

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

    // Asset A — high downloads, no category, not featured, user-submitted
    $stmt2 = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, is_featured, category_id, submitter_user_id)
         VALUES (?, '[]', '', '', 'approved', ?, ?, ?, ?)"
    );
    $titleA = "Neon Grid {$suffix}";
    $dlA = 100; $featA = 0; $catA = null;
    $stmt2->bind_param('siiii', $titleA, $dlA, $featA, $catA, $this->userId);
    $stmt2->execute();
    $this->assetAId = $this->db->dbConnection->insert_id;
    $this->assetIds[] = $this->assetAId;

    // Asset B — low downloads, in category, not featured, user-submitted
    $titleB = "Dark Mode {$suffix}";
    $dlB = 5; $featB = 0;
    $stmt2->bind_param('siiii', $titleB, $dlB, $featB, $this->categoryId, $this->userId);
    $stmt2->execute();
    $this->assetBId = $this->db->dbConnection->insert_id;
    $this->assetIds[] = $this->assetBId;

    // Asset C — medium downloads, no category, featured, user-submitted
    $titleC = "Retro Wave {$suffix}";
    $dlC = 50; $featC = 1; $catC = null;
    $stmt2->bind_param('siiii', $titleC, $dlC, $featC, $catC, $this->userId);
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
    if ($this->presetId !== null) {
        $this->db->query("DELETE FROM public_assets WHERE id = {$this->presetId}");
    }
    foreach ($this->categoryIds as $id) {
        $this->db->query("DELETE FROM categories WHERE id = {$id}");
    }
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

// ── T3.05: GET /api/assets with filters ───────────────────────────────────────

it('GET /api/assets without params returns backward-compatible response', function () {
    $response = $this->getJson('/api/assets');

    // Original response has 'public_assets' and 'assets' keys (editor path — unchanged)
    $response->assertOk()
             ->assertJsonStructure(['public_assets', 'assets']);
});

it('GET /api/assets?q= filters by title (case-insensitive LIKE)', function () {
    $response = $this->getJson('/api/assets?q=neon+grid');

    $response->assertOk()
             ->assertJsonStructure(['success', 'public_assets']);

    $ids = array_column($response->json('public_assets'), 'id');

    expect($ids)->toContain($this->assetAId)
                ->not->toContain($this->assetBId)
                ->not->toContain($this->assetCId);
});

it('GET /api/assets?sort=trending orders by downloads_count DESC', function () {
    $response = $this->getJson('/api/assets?sort=trending');

    $response->assertOk()
             ->assertJsonStructure(['success', 'public_assets']);

    $assets = collect($response->json('public_assets'))
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
             ->assertJsonStructure(['success', 'public_assets']);

    $ids = array_column($response->json('public_assets'), 'id');

    // Only Asset B belongs to this category
    expect($ids)->toContain($this->assetBId)
                ->not->toContain($this->assetAId)
                ->not->toContain($this->assetCId);
});

it('GET /api/assets?category=nonexistent returns empty assets array', function () {
    $response = $this->getJson('/api/assets?category=does-not-exist-xyz');

    $response->assertOk()
             ->assertJson(['success' => true, 'public_assets' => []]);
});

it('GET /api/assets?featured=1 returns only featured assets', function () {
    $response = $this->getJson('/api/assets?featured=1');

    $response->assertOk()
             ->assertJsonStructure(['success', 'public_assets']);

    $ids = array_column($response->json('public_assets'), 'id');

    // Only Asset C is featured in our fixture
    expect($ids)->toContain($this->assetCId)
                ->not->toContain($this->assetBId);
});

it('SQL injection attempt in q param is handled safely', function () {
    // This should run without error and return no results (or safe results)
    $response = $this->getJson("/api/assets?q=" . urlencode("' OR 1=1 --"));

    $response->assertOk()
             ->assertJsonStructure(['success', 'public_assets']);
});

// ── New: system preset exclusion ──────────────────────────────────────────────

it('system preset with NULL submitter_user_id is excluded from the catalog', function () {
    // Insert a system preset: no submitter = admin/seed asset, must NOT appear in UGC catalog
    $stmtPreset = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, submitter_user_id)
         VALUES ('System Preset', '[]', '', '', 'approved', 0, NULL)"
    );
    $stmtPreset->execute();
    $this->presetId = $this->db->dbConnection->insert_id;
    $stmtPreset->close();

    $response = $this->getJson('/api/assets?catalog=1');

    $response->assertOk()
             ->assertJsonStructure(['success', 'public_assets']);

    $ids = array_column($response->json('public_assets'), 'id');

    expect($ids)->not->toContain($this->presetId)
                ->toContain($this->assetAId);
});

it('GET /api/assets?catalog=1 returns user designs and excludes system preset', function () {
    // Insert a null-submitter system preset (seed / admin design)
    $stmtPreset = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, submitter_user_id)
         VALUES ('Catalog Preset', '[]', '', '', 'approved', 0, NULL)"
    );
    $stmtPreset->execute();
    $this->presetId = $this->db->dbConnection->insert_id;
    $stmtPreset->close();

    $response = $this->getJson('/api/assets?catalog=1');

    $response->assertOk();

    $ids = array_column($response->json('public_assets'), 'id');

    // All three user-design fixtures must appear; the null-submitter preset must not
    expect($ids)->toContain($this->assetAId)
                ->toContain($this->assetBId)
                ->toContain($this->assetCId)
                ->not->toContain($this->presetId);
});
