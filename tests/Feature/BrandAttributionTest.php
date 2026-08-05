<?php

use App\Database;
use App\Models\Asset;
use Illuminate\Support\Facades\Schema;

// ── official-brand-designs / PR1 — brand attribution in catalog + moderation ──
//
// `users.is_brand_creator` masks the real handle behind "Shhask" in every
// getPublicCatalog() branch and in getModerationQueue(). The mask is computed
// in the SQL projection — no fake "Shhask" user row is ever created.

beforeEach(function () {
    $this->db          = new Database;
    // Reused across all assertions in a test — avoids opening one mysqli
    // connection per getPublicCatalog()/getModerationQueue() call, which
    // matters in a suite where connections aren't explicitly closed.
    $this->asset       = new Asset;
    $this->userIds     = [];
    $this->assetIds    = [];
    $this->categoryIds = [];

    $suffix = uniqid();

    // Brand creator — flag ON
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype, is_brand_creator)
         VALUES ('Brand Creator', 'brand_{$suffix}', 'brand_{$suffix}@test.com', 'x', 0, 1)"
    );
    $this->brandUserId = $this->db->dbConnection->insert_id;
    $this->userIds[]   = $this->brandUserId;

    // Normal creator — flag left at column default (never touched)
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Normal Creator', 'normal_{$suffix}', 'normal_{$suffix}@test.com', 'x', 0)"
    );
    $this->normalUserId = $this->db->dbConnection->insert_id;
    $this->normalUsername = "normal_{$suffix}";
    $this->userIds[]    = $this->normalUserId;

    // Explicit flag-0 creator — flag set to 0 on purpose (not just default)
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype, is_brand_creator)
         VALUES ('Flag Zero Creator', 'flag0_{$suffix}', 'flag0_{$suffix}@test.com', 'x', 0, 0)"
    );
    $this->flagZeroUserId   = $this->db->dbConnection->insert_id;
    $this->flagZeroUsername = "flag0_{$suffix}";
    $this->userIds[]        = $this->flagZeroUserId;

    // Category used by the category-filter branch
    $stmt = $this->db->dbConnection->prepare(
        "INSERT INTO categories (slug, name, position) VALUES (?, ?, 0)"
    );
    $slug = "brand-cat-{$suffix}";
    $name = "Brand Category {$suffix}";
    $stmt->bind_param('ss', $slug, $name);
    $stmt->execute();
    $this->categoryId    = $this->db->dbConnection->insert_id;
    $this->categorySlug  = $slug;
    $this->categoryIds[] = $this->categoryId;
    $stmt->close();

    // Approved public asset from the brand creator, in the category
    $assetStmt = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, category_id, submitter_user_id)
         VALUES (?, '[]', '', '', 'approved', 0, ?, ?)"
    );
    $titleBrand = "Brand Design {$suffix}";
    $assetStmt->bind_param('sii', $titleBrand, $this->categoryId, $this->brandUserId);
    $assetStmt->execute();
    $this->brandAssetId = $this->db->dbConnection->insert_id;
    $this->assetIds[]   = $this->brandAssetId;

    // Approved public asset from the normal creator, in the same category
    $titleNormal = "Normal Design {$suffix}";
    $assetStmt->bind_param('sii', $titleNormal, $this->categoryId, $this->normalUserId);
    $assetStmt->execute();
    $this->normalAssetId = $this->db->dbConnection->insert_id;
    $this->assetIds[]    = $this->normalAssetId;

    // Approved public asset from the explicit flag-0 creator, in the same category
    $titleFlagZero = "Flag Zero Design {$suffix}";
    $assetStmt->bind_param('sii', $titleFlagZero, $this->categoryId, $this->flagZeroUserId);
    $assetStmt->execute();
    $this->flagZeroAssetId = $this->db->dbConnection->insert_id;
    $this->assetIds[]      = $this->flagZeroAssetId;
    $assetStmt->close();

    // Pending brand design — for the moderation queue mask test
    $pendingBrandStmt = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, submitter_user_id)
         VALUES (?, '[]', '', '', 'pending', 0, ?)"
    );
    $titlePendingBrand = "Pending Brand Design {$suffix}";
    $pendingBrandStmt->bind_param('si', $titlePendingBrand, $this->brandUserId);
    $pendingBrandStmt->execute();
    $this->pendingBrandAssetId = $this->db->dbConnection->insert_id;
    $this->assetIds[]          = $this->pendingBrandAssetId;
    $pendingBrandStmt->close();

    // Pending system preset (NULL submitter) — for the moderation NULL-submitter test
    $pendingPresetStmt = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, submitter_user_id)
         VALUES (?, '[]', '', '', 'pending', 0, NULL)"
    );
    $titlePendingPreset = "Pending Preset {$suffix}";
    $pendingPresetStmt->bind_param('s', $titlePendingPreset);
    $pendingPresetStmt->execute();
    $this->pendingPresetAssetId = $this->db->dbConnection->insert_id;
    $this->assetIds[]           = $this->pendingPresetAssetId;
    $pendingPresetStmt->close();
});

afterEach(function () {
    foreach ($this->assetIds as $id) {
        $this->db->query("DELETE FROM asset_acquisitions WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    foreach ($this->categoryIds as $id) {
        $this->db->query("DELETE FROM categories WHERE id = {$id}");
    }
    foreach ($this->userIds as $id) {
        $this->db->query("DELETE FROM users WHERE id = {$id}");
    }
});

// ── 2.1: characterization — column key-set includes submitter_username ────────

it('getPublicCatalog() plain branch exposes submitter_username', function () {
    $rows = $this->asset->getPublicCatalog();
    $row  = collect($rows)->firstWhere('id', $this->brandAssetId);

    expect($row)->not->toBeNull()
        ->and(array_key_exists('submitter_username', $row))->toBeTrue();
});

it('getPublicCatalog() category branch exposes submitter_username', function () {
    $rows = $this->asset->getPublicCatalog(null, $this->categorySlug);
    $row  = collect($rows)->firstWhere('id', $this->brandAssetId);

    expect($row)->not->toBeNull()
        ->and(array_key_exists('submitter_username', $row))->toBeTrue();
});

it('getPublicCatalog() trending branch exposes submitter_username', function () {
    $rows = $this->asset->getPublicCatalog(null, null, 'trending');
    $row  = collect($rows)->firstWhere('id', $this->brandAssetId);

    expect($row)->not->toBeNull()
        ->and(array_key_exists('submitter_username', $row))->toBeTrue();
});

// ── 2.2: mask matrix ────────────────────────────────────────────────────────

it('masks the brand creator handle as "Shhask" in every catalog branch', function () {
    $plain    = collect($this->asset->getPublicCatalog())->firstWhere('id', $this->brandAssetId);
    $category = collect($this->asset->getPublicCatalog(null, $this->categorySlug))->firstWhere('id', $this->brandAssetId);
    $trending = collect($this->asset->getPublicCatalog(null, null, 'trending'))->firstWhere('id', $this->brandAssetId);

    expect($plain['submitter_username'])->toBe('Shhask')
        ->and($category['submitter_username'])->toBe('Shhask')
        ->and($trending['submitter_username'])->toBe('Shhask');
});

it('keeps the real handle for a normal (non-brand) creator', function () {
    $row = collect($this->asset->getPublicCatalog())->firstWhere('id', $this->normalAssetId);

    expect($row['submitter_username'])->toBe($this->normalUsername);
});

it('keeps the real handle when is_brand_creator is explicitly 0', function () {
    $row = collect($this->asset->getPublicCatalog())->firstWhere('id', $this->flagZeroAssetId);

    expect($row['submitter_username'])->toBe($this->flagZeroUsername);
});

it('masks the brand creator handle as "Shhask" in the moderation queue', function () {
    $row = collect($this->asset->getModerationQueue())->firstWhere('id', $this->pendingBrandAssetId);

    expect($row)->not->toBeNull()
        ->and($row['submitter_username'])->toBe('Shhask');
});

it('leaves submitter_username NULL in the moderation queue for a NULL-submitter preset', function () {
    $row = collect($this->asset->getModerationQueue())->firstWhere('id', $this->pendingPresetAssetId);

    expect($row)->not->toBeNull()
        ->and($row['submitter_username'])->toBeNull();
});

// ── 2.3: schema test ─────────────────────────────────────────────────────────

it('users has a non-nullable is_brand_creator boolean column defaulting to 0', function () {
    expect(Schema::hasColumn('users', 'is_brand_creator'))->toBeTrue();

    $rows = $this->db->query('DESCRIBE users')->fetch_all(MYSQLI_ASSOC);
    $col  = collect($rows)->firstWhere('Field', 'is_brand_creator');

    expect($col)->not->toBeNull()
        ->and($col['Null'])->toBe('NO')
        ->and((int) $col['Default'])->toBe(0);
});
