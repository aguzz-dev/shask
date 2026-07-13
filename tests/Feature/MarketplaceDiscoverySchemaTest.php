<?php

use App\Database;
use Illuminate\Support\Facades\Schema;

// ── T3.01: migration schema assertions (RED before migrations run) ────────────

it('categories table exists with required columns', function () {
    expect(Schema::hasTable('categories'))->toBeTrue();

    $db   = new Database;
    $cols = array_column($db->query('DESCRIBE categories')->fetch_all(MYSQLI_ASSOC), 'Field');
    expect($cols)->toContain('id', 'slug', 'name', 'position', 'created_at');
});

it('public_assets has category_id column', function () {
    $db   = new Database;
    $cols = array_column($db->query('DESCRIBE public_assets')->fetch_all(MYSQLI_ASSOC), 'Field');
    expect($cols)->toContain('category_id');
});

it('asset_acquisitions table exists with required columns', function () {
    expect(Schema::hasTable('asset_acquisitions'))->toBeTrue();

    $db   = new Database;
    $cols = array_column($db->query('DESCRIBE asset_acquisitions')->fetch_all(MYSQLI_ASSOC), 'Field');
    expect($cols)->toContain('id', 'asset_id', 'buyer_user_id', 'source', 'hype_minted', 'created_at');
});

it('asset_acquisitions source column is an ENUM of hype and ad', function () {
    expect(Schema::hasTable('asset_acquisitions'))->toBeTrue();

    $db  = new Database;
    $row = $db->query("DESCRIBE asset_acquisitions")->fetch_all(MYSQLI_ASSOC);
    $src = collect($row)->firstWhere('Field', 'source');
    expect($src)->not->toBeNull()
        ->and($src['Type'])->toContain('hype')
        ->and($src['Type'])->toContain('ad');
});

// ── Batch 1 (marketplace-item-showcase): additive JSON contract ───────────────
//
// created_at surfaces on every catalog branch (nullable — legacy rows never
// invent history). recent_acquisitions is exclusive to the trending branch
// (only branch running the momentum JOIN). Existing clients ignoring these
// new keys keep working — nothing renamed or removed.

describe('additive catalog contract (created_at / recent_acquisitions)', function () {
    beforeEach(function () {
        $this->db      = new Database;
        $this->suffix  = uniqid();
        $this->assetIds = [];

        $this->db->query(
            "INSERT INTO users (full_name, username, email, password, hype)
             VALUES ('Contract Test User', 'ctu_{$this->suffix}', 'ctu_{$this->suffix}@test.com', 'x', 0)"
        );
        $this->userId = $this->db->dbConnection->insert_id;

        // Two assets with distinct downloads_count so an invalid `sort`
        // value's fallback (id DESC) is distinguishable from downloads_count
        // ordering — proves the whitelist, not accidental agreement. The
        // "high" asset is also featured, so the featured branch has at
        // least one guaranteed row regardless of pre-existing catalog data.
        $stmt = $this->db->dbConnection->prepare(
            "INSERT INTO public_assets (title, color, icon, background, status, downloads_count, is_featured, submitter_user_id)
             VALUES (?, '[]', '', '', 'approved', ?, ?, ?)"
        );
        $titleLow  = "Contract Low {$this->suffix}";
        $dlLow     = 1;
        $featLow   = 0;
        $stmt->bind_param('siii', $titleLow, $dlLow, $featLow, $this->userId);
        $stmt->execute();
        $this->lowId = $this->db->dbConnection->insert_id;
        $this->assetIds[] = $this->lowId;

        $titleHigh = "Contract High {$this->suffix}";
        $dlHigh    = 999;
        $featHigh  = 1;
        $stmt->bind_param('siii', $titleHigh, $dlHigh, $featHigh, $this->userId);
        $stmt->execute();
        $this->highId = $this->db->dbConnection->insert_id;
        $this->assetIds[] = $this->highId;
        $stmt->close();
    });

    afterEach(function () {
        foreach ($this->assetIds as $id) {
            $this->db->query("DELETE FROM asset_acquisitions WHERE asset_id = {$id}");
            $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
        }
        $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
    });

    it('surfaces created_at (nullable) on the plain catalog, featured, and trending branches', function () {
        $plain    = $this->getJson('/api/assets?catalog=1')->json('public_assets');
        $featured = $this->getJson('/api/assets?featured=1')->json('public_assets');
        $trending = $this->getJson('/api/assets?sort=trending')->json('public_assets');

        foreach ([$plain, $featured, $trending] as $branch) {
            expect($branch)->not->toBeEmpty();
            foreach ($branch as $row) {
                expect(array_key_exists('created_at', $row))->toBeTrue();
            }
        }
    });

    it('surfaces recent_acquisitions ONLY on the trending branch, absent from plain catalog and featured', function () {
        $plain    = $this->getJson('/api/assets?catalog=1')->json('public_assets');
        $featured = $this->getJson('/api/assets?featured=1')->json('public_assets');
        $trending = $this->getJson('/api/assets?sort=trending')->json('public_assets');

        expect($plain)->not->toBeEmpty()
            ->and($featured)->not->toBeEmpty()
            ->and($trending)->not->toBeEmpty();

        foreach ($plain as $row) {
            expect(array_key_exists('recent_acquisitions', $row))->toBeFalse();
        }
        foreach ($featured as $row) {
            expect(array_key_exists('recent_acquisitions', $row))->toBeFalse();
        }
        foreach ($trending as $row) {
            expect(array_key_exists('recent_acquisitions', $row))->toBeTrue();
        }
    });

    it('falls back to id DESC when an unknown sort value is supplied (whitelist regression)', function () {
        $response = $this->getJson('/api/assets?catalog=1&sort=' . urlencode("id; DROP TABLE public_assets;--"));

        $response->assertOk();

        $items = collect($response->json('public_assets'))
            ->whereIn('id', $this->assetIds)
            ->values()
            ->toArray();

        $ids = array_column($items, 'id');

        // id DESC → highId (inserted second, higher id) ranks before lowId,
        // even though lowId has the lower downloads_count and higher id
        // ordering has nothing to do with downloads_count DESC (999 vs 1).
        $idxHigh = array_search($this->highId, $ids);
        $idxLow  = array_search($this->lowId, $ids);

        expect($idxHigh)->toBeLessThan($idxLow);
    });
});
