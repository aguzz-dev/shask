<?php

use App\Database;
use App\Models\Asset;

// ── T4.1.1: server-driven price stamp on the public catalog ─────────────────
//
// `Asset::getPublicCatalog` must stamp `price` on every row it returns, from
// `config('marketplace.acquisition_hype_cost')`. This closes the gap where
// the UGC catalog (public_assets) has no `price` column and the client used
// to fall back to a hardcoded constant (marketplace-surface / Price Is
// Always Server-Driven).

beforeEach(function () {
    $this->db       = new Database;
    $this->assetIds = [];
    $this->categoryIds = [];

    $suffix = uniqid();

    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Price Test User', 'ptu_{$suffix}', 'ptu_{$suffix}@test.com', 'x', 0)"
    );
    $this->userId = $this->db->dbConnection->insert_id;

    $stmt = $this->db->dbConnection->prepare(
        "INSERT INTO categories (slug, name, position) VALUES (?, ?, 0)"
    );
    $slug = "price-cat-{$suffix}";
    $name = "Price Category {$suffix}";
    $stmt->bind_param('ss', $slug, $name);
    $stmt->execute();
    $this->categoryId   = $this->db->dbConnection->insert_id;
    $this->categoryIds[] = $this->categoryId;
    $this->categorySlug = $slug;
    $stmt->close();

    $stmt2 = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, is_featured, category_id, submitter_user_id)
         VALUES (?, '[]', '', '', 'approved', 0, 0, ?, ?)"
    );

    $titleNoCat = "No Category {$suffix}";
    $catNull    = null;
    $stmt2->bind_param('sii', $titleNoCat, $catNull, $this->userId);
    $stmt2->execute();
    $this->assetNoCategoryId = $this->db->dbConnection->insert_id;
    $this->assetIds[]        = $this->assetNoCategoryId;

    $titleWithCat = "With Category {$suffix}";
    $stmt2->bind_param('sii', $titleWithCat, $this->categoryId, $this->userId);
    $stmt2->execute();
    $this->assetWithCategoryId = $this->db->dbConnection->insert_id;
    $this->assetIds[]          = $this->assetWithCategoryId;

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
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('getPublicCatalog stamps price on every row (no categorySlug path)', function () {
    $cost = (int) config('marketplace.acquisition_hype_cost', 20);

    $rows = (new Asset)->getPublicCatalog();
    $row  = collect($rows)->firstWhere('id', $this->assetNoCategoryId);

    expect($row)->not->toBeNull()
        ->and((int) $row['price'])->toBe($cost);
});

it('getPublicCatalog stamps price on every row (categorySlug path)', function () {
    $cost = (int) config('marketplace.acquisition_hype_cost', 20);

    $rows = (new Asset)->getPublicCatalog(null, $this->categorySlug);
    $row  = collect($rows)->firstWhere('id', $this->assetWithCategoryId);

    expect($row)->not->toBeNull()
        ->and((int) $row['price'])->toBe($cost);
});
