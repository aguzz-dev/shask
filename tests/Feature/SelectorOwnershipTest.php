<?php

use App\Database;
use App\Models\Asset;
use App\Models\AssetUser;

// ── official-brand-designs / PR3 — ownership-scoped selector (backend) ──
//
// `Asset::getUserAssetsByUserId()` adds a NEW, additive `owned_assets` key
// resolved by `AssetUser::ownedAssetIds()`, following the same id-namespace
// precedence `AssetUser::buyAsset()` already uses (public_assets checked
// before assets), fail-open on collision, never closed:
//
//   1. asset_acquisitions ledger (post-ledger UGC purchases)
//   2. own public_assets rows (any status — authorship is never gated)
//   3. legacy asset_user rows with no ledger match, probed public_assets
//      first, then assets (system assets stay in the existing `assets` key)
//
// `public_assets` is narrowed to the same owned rows and documented as a
// DEPRECATED alias (kept for installed clients without PR4's fallback).
// `user_designs` and `assets` MUST stay byte-identical — contract freeze.

beforeEach(function () {
    $this->db          = new Database;
    $this->asset       = new Asset;
    $this->assetUser   = new AssetUser;
    $this->userIds     = [];
    $this->publicAssetIds = [];
    $this->systemAssetIds  = [];

    $suffix = uniqid();

    // Subject user (the one whose selector payload we request)
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Selector Subject', 'sel_subj_{$suffix}', 'sel_subj_{$suffix}@test.com', 'x', 0)"
    );
    $this->subjectId = $this->db->dbConnection->insert_id;
    $this->userIds[] = $this->subjectId;

    // Another creator, used for ledger-acquired + never-acquired designs
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Selector Creator', 'sel_creator_{$suffix}', 'sel_creator_{$suffix}@test.com', 'x', 0)"
    );
    $this->creatorId = $this->db->dbConnection->insert_id;
    $this->userIds[] = $this->creatorId;

    $this->suffix = $suffix;
});

afterEach(function () {
    foreach ($this->publicAssetIds as $id) {
        $this->db->query("DELETE FROM asset_acquisitions WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM asset_user WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    foreach ($this->systemAssetIds as $id) {
        $this->db->query("DELETE FROM asset_user WHERE asset_id = {$id}");
        $this->db->query("DELETE FROM assets WHERE id = {$id}");
    }
    foreach ($this->userIds as $id) {
        $this->db->query("DELETE FROM users WHERE id = {$id}");
    }
});

// ── helpers ─────────────────────────────────────────────────────────────────

function selectorPublicAsset(Database $db, string $title, ?int $submitterUserId, string $status = 'approved'): int
{
    $stmt = $db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, submitter_user_id)
         VALUES (?, '[]', '', '', ?, 0, ?)"
    );
    $stmt->bind_param('ssi', $title, $status, $submitterUserId);
    $stmt->execute();
    $id = (int) $db->dbConnection->insert_id;
    $stmt->close();
    return $id;
}

function selectorSystemAsset(Database $db): int
{
    $db->query("INSERT INTO assets (color, icon, background, price) VALUES ('[]', '', '', 0)");
    return (int) $db->dbConnection->insert_id;
}

function selectorLegacyAssetUserRow(Database $db, int $assetId, int $userId): void
{
    // Mirrors AssetUser::_insertAssetUser() but WITHOUT the matching
    // asset_acquisitions ledger row — simulates a purchase made before the
    // ledger table (2026-06-29) existed.
    $now  = (new DateTime())->format('Y-m-d H:i:s');
    $stmt = $db->dbConnection->prepare(
        'INSERT INTO asset_user (asset_id, user_id, created_at) VALUES (?, ?, ?)'
    );
    $stmt->bind_param('iis', $assetId, $userId, $now);
    $stmt->execute();
    $stmt->close();
}

// ── 1.1: ownership scenarios ────────────────────────────────────────────────

it('includes a design acquired via the asset_acquisitions ledger', function () {
    $assetId = selectorPublicAsset($this->db, "Ledger Owned {$this->suffix}", $this->creatorId);
    $this->publicAssetIds[] = $assetId;

    $stmt = $this->db->dbConnection->prepare(
        "INSERT INTO asset_acquisitions (asset_id, buyer_user_id, source, hype_minted) VALUES (?, ?, 'ad', 0)"
    );
    $stmt->bind_param('ii', $assetId, $this->subjectId);
    $stmt->execute();
    $stmt->close();

    $result = $this->asset->getUserAssetsByUserId($this->subjectId);
    $ids    = collect($result['owned_assets'])->pluck('id')->map(fn ($id) => (int) $id);

    expect($ids)->toContain($assetId);
});

it('includes a self-authored design with zero acquisitions, in any status', function () {
    $pendingId = selectorPublicAsset($this->db, "Own Pending {$this->suffix}", $this->subjectId, 'pending');
    $this->publicAssetIds[] = $pendingId;

    $result = $this->asset->getUserAssetsByUserId($this->subjectId);
    $ids    = collect($result['owned_assets'])->pluck('id')->map(fn ($id) => (int) $id);

    expect($ids)->toContain($pendingId)
        ->and($ids)->toHaveCount(1);
});

it('excludes a design never acquired nor authored by the user', function () {
    $otherId = selectorPublicAsset($this->db, "Never Acquired {$this->suffix}", $this->creatorId, 'approved');
    $this->publicAssetIds[] = $otherId;

    $result = $this->asset->getUserAssetsByUserId($this->subjectId);
    $ids    = collect($result['owned_assets'])->pluck('id')->map(fn ($id) => (int) $id);

    expect($ids)->not->toContain($otherId);
});

it('resolves a legacy asset_user row with no ledger match via buyAsset() precedence (fail-open)', function () {
    $legacyId = selectorPublicAsset($this->db, "Legacy Purchase {$this->suffix}", $this->creatorId, 'approved');
    $this->publicAssetIds[] = $legacyId;

    // No asset_acquisitions row for this pair — pre-ledger legacy purchase.
    selectorLegacyAssetUserRow($this->db, $legacyId, $this->subjectId);

    $result = $this->asset->getUserAssetsByUserId($this->subjectId);
    $ids    = collect($result['owned_assets'])->pluck('id')->map(fn ($id) => (int) $id);

    expect($ids)->toContain($legacyId);
});

it('returns owned_assets == [] when the user has zero acquisitions and zero own designs', function () {
    $result = $this->asset->getUserAssetsByUserId($this->subjectId);

    expect($result['owned_assets'])->toBe([]);
});

it('does not let a legacy asset_user row pointing at a system asset leak into owned_assets', function () {
    $systemId = selectorSystemAsset($this->db);
    $this->systemAssetIds[] = $systemId;

    selectorLegacyAssetUserRow($this->db, $systemId, $this->subjectId);

    $result = $this->asset->getUserAssetsByUserId($this->subjectId);
    $ids    = collect($result['owned_assets'])->pluck('id')->map(fn ($id) => (int) $id);

    // The system asset must still be reachable via the existing `assets` key,
    // never duplicated into the UGC-scoped owned_assets.
    $systemIds = collect($result['assets'])->pluck('id')->map(fn ($id) => (int) $id);

    expect($ids)->not->toContain($systemId)
        ->and($systemIds)->toContain($systemId);
});

// ── 1.2: contract freeze ─────────────────────────────────────────────────────

it('CONTRACT FREEZE: user_designs and assets stay byte-identical, public_assets narrows to owned_assets', function () {
    $ownDesignId = selectorPublicAsset($this->db, "Own Design {$this->suffix}", $this->subjectId, 'approved');
    $this->publicAssetIds[] = $ownDesignId;

    $neverAcquiredId = selectorPublicAsset($this->db, "Catalog Only {$this->suffix}", $this->creatorId, 'approved');
    $this->publicAssetIds[] = $neverAcquiredId;

    $systemId = selectorSystemAsset($this->db);
    $this->systemAssetIds[] = $systemId;
    $this->assetUser->buyAsset($systemId, $this->subjectId, 'ad');

    $result = $this->asset->getUserAssetsByUserId($this->subjectId);

    expect($result)->toHaveKeys(['owned_assets', 'public_assets', 'user_designs', 'assets']);

    // public_assets is now an alias of owned_assets (deprecated, narrowed) —
    // it must NOT still contain the never-acquired catalog-only design.
    expect($result['public_assets'])->toBe($result['owned_assets']);
    $publicIds = collect($result['public_assets'])->pluck('id')->map(fn ($id) => (int) $id);
    expect($publicIds)->not->toContain($neverAcquiredId);

    // user_designs untouched: still every own design regardless of status,
    // same query as before this change.
    $userDesignIds = collect($result['user_designs'])->pluck('id')->map(fn ($id) => (int) $id);
    expect($userDesignIds)->toContain($ownDesignId);

    // assets (system, owned via asset_user) untouched: system asset bought
    // above is still present exactly as the pre-existing query would return.
    $systemIds = collect($result['assets'])->pluck('id')->map(fn ($id) => (int) $id);
    expect($systemIds)->toContain($systemId);
});

// ── retirement regression lock (spec: "Retiro de presets") ──────────────────
//
// Retiring the 5 legacy system presets (submitter_user_id IS NULL) must not
// resurrect them in owned_assets for a user who never acquired them, and
// must not break the create-asset editor flow (design.md: create_asset_page
// never reads public_assets — locked separately in BrandPublicationCommandsTest).

it('a despublished NULL-submitter preset never appears in owned_assets for a user who never acquired it', function () {
    $presetId = selectorPublicAsset($this->db, "System Preset {$this->suffix}", null, 'approved');
    $this->publicAssetIds[] = $presetId;

    $this->db->query("UPDATE public_assets SET status = 'removed' WHERE id = {$presetId}");

    $result = $this->asset->getUserAssetsByUserId($this->subjectId);
    $ids    = collect($result['owned_assets'])->pluck('id')->map(fn ($id) => (int) $id);

    expect($ids)->not->toContain($presetId);
});
