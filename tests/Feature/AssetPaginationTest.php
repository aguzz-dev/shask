<?php

use App\Database;

// ── Fixtures ──────────────────────────────────────────────────────────────────

beforeEach(function () {
    $this->db          = new Database;
    $this->assetIds    = [];
    $this->categoryIds = [];

    $suffix = uniqid();

    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Pagination Test User', 'ptu_{$suffix}', 'ptu_{$suffix}@test.com', 'x', 0)"
    );
    $this->userId = $this->db->dbConnection->insert_id;

    // Category used to scope every request in this file to only the
    // fixtures created here, isolating assertions from any other data
    // already present in the catalog (mirrors AssetFiltersTest's pattern).
    $stmt = $this->db->dbConnection->prepare(
        "INSERT INTO categories (slug, name, position) VALUES (?, ?, 0)"
    );
    $slug = "pagination-cat-{$suffix}";
    $name = "Pagination Category {$suffix}";
    $stmt->bind_param('ss', $slug, $name);
    $stmt->execute();
    $this->categoryId    = $this->db->dbConnection->insert_id;
    $this->categoryIds[] = $this->categoryId;
    $this->categorySlug  = $slug;
    $stmt->close();
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
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

/**
 * Inserts one public_assets row per entry in $downloadsCounts, scoped to
 * $categoryId/$userId. Returns the inserted ids in insertion order (ids are
 * monotonically increasing, so the last entry has the highest id).
 *
 * @param  array<int,int>  $downloadsCounts
 * @param  array<int,int>  $featuredFlags  Optional, defaults to 0 (not featured).
 * @return array<int,int>
 */
function insertCatalogAssets(Database $db, int $userId, int $categoryId, array $downloadsCounts, array $featuredFlags = []): array
{
    $ids  = [];
    $stmt = $db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, is_featured, category_id, submitter_user_id)
         VALUES (?, '[]', '', '', 'approved', ?, ?, ?, ?)"
    );

    foreach (array_values($downloadsCounts) as $i => $downloads) {
        $title    = 'Pagination Asset ' . uniqid();
        $featured = $featuredFlags[$i] ?? 0;
        $stmt->bind_param('siiii', $title, $downloads, $featured, $categoryId, $userId);
        $stmt->execute();
        $ids[] = $db->dbConnection->insert_id;
    }

    $stmt->close();
    return $ids;
}

// ── T1.1(i): opt-in contract — no offset → byte-compatible shape ──────────────

it('GET /api/assets without offset returns byte-compatible shape (no has_more, no next_offset)', function () {
    $ids = insertCatalogAssets($this->db, $this->userId, $this->categoryId, [30, 20, 10]);
    $this->assetIds = $ids;

    $response = $this->getJson("/api/assets?catalog=1&category={$this->categorySlug}");

    $response->assertOk();
    $json = $response->json();

    expect($json)->toHaveKeys(['success', 'public_assets']);
    expect($json)->not->toHaveKey('has_more');
    expect($json)->not->toHaveKey('next_offset');
    expect($json['public_assets'])->toHaveCount(3);
});

// ── T1.1(ii): opt-in contract — offset=0 → paginated envelope ─────────────────

it('GET /api/assets?offset=0 returns paginated envelope with has_more and next_offset', function () {
    config(['marketplace.catalog_page_size' => 2]);

    $ids = insertCatalogAssets($this->db, $this->userId, $this->categoryId, [30, 20, 10]);
    $this->assetIds = $ids;

    $response = $this->getJson("/api/assets?catalog=1&category={$this->categorySlug}&offset=0");

    $response->assertOk();
    $json = $response->json();

    expect($json)->toHaveKeys(['success', 'public_assets', 'has_more', 'next_offset']);
    expect($json['public_assets'])->toHaveCount(2);
    expect($json['has_more'])->toBeTrue();
    expect($json['next_offset'])->toBe(2);

    // Default sort = id DESC → most recently inserted asset first.
    expect($json['public_assets'][0]['id'])->toBe($ids[2]);
    expect($json['public_assets'][1]['id'])->toBe($ids[1]);
});

// ── T1.1(iii)(a): contiguous pages, default sort, no duplicates/gaps ──────────

it('two contiguous default-sort pages produce no duplicates or gaps', function () {
    config(['marketplace.catalog_page_size' => 2]);

    $ids = insertCatalogAssets($this->db, $this->userId, $this->categoryId, [40, 30, 20, 10]);
    $this->assetIds = $ids;

    $page1 = $this->getJson("/api/assets?catalog=1&category={$this->categorySlug}&offset=0")->json();
    $page2 = $this->getJson("/api/assets?catalog=1&category={$this->categorySlug}&offset=2")->json();

    $page1Ids = array_column($page1['public_assets'], 'id');
    $page2Ids = array_column($page2['public_assets'], 'id');

    expect($page1Ids)->toHaveCount(2);
    expect($page2Ids)->toHaveCount(2);
    expect(array_intersect($page1Ids, $page2Ids))->toBeEmpty();
    expect(array_merge($page1Ids, $page2Ids))->toEqualCanonicalizing($ids);
    expect($page1['has_more'])->toBeTrue();
    expect($page2['has_more'])->toBeFalse();
    expect($page2['next_offset'])->toBe(4);
});

// ── T1.1(iii)(b): contiguous pages, trending sort with tied downloads_count ───

it('two contiguous trending-sort pages with tied downloads_count produce no duplicates or gaps', function () {
    config(['marketplace.catalog_page_size' => 3]);

    // Two tied groups: three assets at 50 downloads, three assets at 20 downloads.
    $ids = insertCatalogAssets($this->db, $this->userId, $this->categoryId, [50, 50, 50, 20, 20, 20]);
    $this->assetIds = $ids;

    $page1 = $this->getJson("/api/assets?catalog=1&category={$this->categorySlug}&sort=trending&offset=0")->json();
    $page2 = $this->getJson("/api/assets?catalog=1&category={$this->categorySlug}&sort=trending&offset=3")->json();

    $page1Ids = array_column($page1['public_assets'], 'id');
    $page2Ids = array_column($page2['public_assets'], 'id');

    expect($page1Ids)->toHaveCount(3);
    expect($page2Ids)->toHaveCount(3);
    expect(array_intersect($page1Ids, $page2Ids))->toBeEmpty();
    expect(array_merge($page1Ids, $page2Ids))->toEqualCanonicalizing($ids);

    // Tie broken by id DESC: within the "50" group (ids[0..2], inserted in that
    // order) the highest id comes first; same for the "20" group (ids[3..5]).
    expect($page1Ids)->toBe([$ids[2], $ids[1], $ids[0]]);
    expect($page2Ids)->toBe([$ids[5], $ids[4], $ids[3]]);
});

// ── T1.1(iv): featured rail cap, no offset ─────────────────────────────────────

it('GET /api/assets?featured=1 without offset is capped to rail_cap', function () {
    config(['marketplace.rail_cap' => 2]);

    $ids = insertCatalogAssets(
        $this->db,
        $this->userId,
        $this->categoryId,
        [10, 20, 30, 40],
        [1, 1, 1, 1]
    );
    $this->assetIds = $ids;

    $response = $this->getJson("/api/assets?featured=1&category={$this->categorySlug}");

    $response->assertOk();
    $json = $response->json();

    expect($json)->toHaveKeys(['success', 'public_assets']);
    expect($json)->not->toHaveKey('has_more');
    expect($json['public_assets'])->toHaveCount(2);

    // Rail cap keeps the highest ids first (default order, id DESC).
    $returnedIds = array_column($json['public_assets'], 'id');
    expect($returnedIds)->toBe([$ids[3], $ids[2]]);
});

// ── T1.1(v): trending rail cap, no offset ─────────────────────────────────────

it('GET /api/assets?sort=trending without offset is capped to rail_cap', function () {
    config(['marketplace.rail_cap' => 2]);

    $ids = insertCatalogAssets($this->db, $this->userId, $this->categoryId, [10, 20, 30, 40]);
    $this->assetIds = $ids;

    $response = $this->getJson("/api/assets?sort=trending&category={$this->categorySlug}");

    $response->assertOk();
    $json = $response->json();

    expect($json)->toHaveKeys(['success', 'public_assets']);
    expect($json)->not->toHaveKey('has_more');
    expect($json['public_assets'])->toHaveCount(2);

    // Rail cap keeps the two highest-downloads assets (tiebreaker id DESC).
    $returnedIds = array_column($json['public_assets'], 'id');
    expect($returnedIds)->toBe([$ids[3], $ids[2]]);
});

// ── T1.1(vi): hostile offset handled safely (no 500, no SQL injection) ────────

it('hostile offset values are handled safely without a 500 or SQL injection', function () {
    $ids = insertCatalogAssets($this->db, $this->userId, $this->categoryId, [10, 20, 30]);
    $this->assetIds = $ids;

    $nonNumeric = $this->getJson("/api/assets?catalog=1&category={$this->categorySlug}&offset=abc");
    $nonNumeric->assertOk();
    expect($nonNumeric->json())->toHaveKeys(['success', 'public_assets', 'has_more', 'next_offset']);

    $negative = $this->getJson("/api/assets?catalog=1&category={$this->categorySlug}&offset=-1");
    $negative->assertOk();
    expect($negative->json())->toHaveKeys(['success', 'public_assets', 'has_more', 'next_offset']);

    $injection = $this->getJson(
        "/api/assets?catalog=1&category={$this->categorySlug}&offset=" . urlencode('1; DROP TABLE public_assets; --')
    );
    $injection->assertOk();

    // The table must still be queryable after the injection attempt.
    $followUp = $this->getJson("/api/assets?catalog=1&category={$this->categorySlug}");
    $followUp->assertOk();
});
