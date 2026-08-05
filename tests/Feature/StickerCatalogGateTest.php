<?php

use App\Database;
use App\Models\Asset;
use App\Support\BrandDesignHasher;
use Illuminate\Support\Facades\Artisan;

// ── Sticker catalog integrity gate ──────────────────────────────────────────
//
// Root cause under test: a canvas layer `type: "sticker"` can reference a
// `src` that exists physically on the `media` disk (so it renders fine in
// the app, served straight from disk via /api/image/{name}) without having
// a matching row in `media_images` (the catalog the admin panel actually
// reads from, see MediaCatalog). This is exactly what happened in production
// with the imported "Y2K" design (submitter_user_id=2): its canvas
// references `y2k_sticker_1` / `y2k_sticker_2`, neither of which is
// catalogued. `Asset::findMissingStickerRefs()` closes the gap by gating
// publish paths (createPublicAsset / updatePublicAsset / brand:import-designs)
// and by powering a read-only audit command (media:audit-orphans).

beforeEach(function () {
    $this->db            = new Database;
    $this->asset          = new Asset;
    $this->userIds        = [];
    $this->assetIds        = [];
    $this->mediaImageNames = [];
    $this->files            = [];
});

afterEach(function () {
    foreach ($this->assetIds as $id) {
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    foreach ($this->mediaImageNames as $name) {
        $escaped = $this->db->dbConnection->real_escape_string($name);
        $this->db->query("DELETE FROM media_images WHERE name = '{$escaped}'");
    }
    foreach ($this->userIds as $id) {
        $this->db->query("DELETE FROM users WHERE id = {$id}");
    }
    foreach ($this->files as $f) {
        if (file_exists($f)) {
            unlink($f);
        }
    }
});

function stickerGateTestUser(Database $db, string $suffix): int
{
    $db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Sticker Gate Test', 'sgt_{$suffix}', 'sgt_{$suffix}@test.com', 'x', 0)"
    );
    return (int) $db->dbConnection->insert_id;
}

function catalogMediaImage(Database $db, string $name): void
{
    $escaped = $db->dbConnection->real_escape_string($name);
    $db->query(
        "INSERT INTO media_images (name, type, created_at, updated_at)
         VALUES ('{$escaped}', 'sticker', NOW(), NOW())"
    );
}

/**
 * Directly inserts a public_assets row bypassing Asset::createPublicAsset(),
 * mirroring how the real "Y2K" bad row got into production: BEFORE this
 * gate existed, nothing stopped a canvas with phantom sticker refs from
 * being persisted. Used only to build fixtures for the read-only audit
 * command, which must find rows regardless of how they got there.
 */
function insertRawPublicAsset(Database $db, string $title, ?array $canvas, ?int $submitterId): int
{
    $titleEsc  = $db->dbConnection->real_escape_string($title);
    $canvasSql = $canvas !== null
        ? "'" . $db->dbConnection->real_escape_string(json_encode($canvas)) . "'"
        : 'NULL';
    $submitterSql = $submitterId !== null ? (string) $submitterId : 'NULL';

    $db->query(
        "INSERT INTO public_assets (title, color, icon, background, canvas, submitter_user_id, status, created_at)
         VALUES ('{$titleEsc}', '[]', '', '', {$canvasSql}, {$submitterSql}, 'approved', NOW())"
    );

    return (int) $db->dbConnection->insert_id;
}

// ── findMissingStickerRefs(): canvas shapes ─────────────────────────────────

it('finds a missing sticker ref in v3 bundle shape (pillCard/shareCard/storyCard)', function () {
    $suffix = uniqid();
    $canvas = [
        'pillCard'  => ['layers' => [['type' => 'sticker', 'src' => "ghost_v3_{$suffix}"]]],
        'shareCard' => ['layers' => []],
        'storyCard' => ['layers' => []],
    ];

    $missing = $this->asset->findMissingStickerRefs($canvas);

    expect($missing)->toBe(["ghost_v3_{$suffix}"]);
});

it('finds a missing sticker ref in legacy flat shape ({layers: [...]})', function () {
    $suffix = uniqid();
    $canvas = [
        'layers' => [['type' => 'sticker', 'src' => "ghost_legacy_{$suffix}"]],
    ];

    $missing = $this->asset->findMissingStickerRefs($canvas);

    expect($missing)->toBe(["ghost_legacy_{$suffix}"]);
});

it('finds a sticker nested inside a group layer via its children array', function () {
    $suffix = uniqid();
    $canvas = [
        'shareCard' => [
            'layers' => [
                [
                    'type'     => 'group',
                    'children' => [
                        ['type' => 'text', 'content' => 'hi'],
                        ['type' => 'sticker', 'src' => "ghost_nested_{$suffix}"],
                    ],
                ],
            ],
        ],
    ];

    $missing = $this->asset->findMissingStickerRefs($canvas);

    expect($missing)->toBe(["ghost_nested_{$suffix}"]);
});

it('returns only the truly missing srcs when some stickers are catalogued and others are not', function () {
    $suffix   = uniqid();
    $good     = "catalogued_{$suffix}";
    $bad      = "ghost_{$suffix}";
    catalogMediaImage($this->db, $good);
    $this->mediaImageNames[] = $good;

    $canvas = [
        'shareCard' => [
            'layers' => [
                ['type' => 'sticker', 'src' => $good],
                ['type' => 'sticker', 'src' => $bad],
            ],
        ],
    ];

    $missing = $this->asset->findMissingStickerRefs($canvas);

    expect($missing)->toBe([$bad]);
});

it('returns an empty array when the canvas has no sticker layers at all', function () {
    $canvas = [
        'shareCard' => [
            'layers' => [
                ['type' => 'background', 'fill' => ['type' => 'color', 'value' => '#fff']],
                ['type' => 'text', 'content' => 'hello'],
            ],
        ],
    ];

    expect($this->asset->findMissingStickerRefs($canvas))->toBe([]);
});

it('does not treat an "image" layer as a sticker (bundled Flutter assets are a different mechanism)', function () {
    $canvas = [
        'shareCard' => [
            'layers' => [
                ['type' => 'image', 'src' => 'assets/new_design/shhask_black_logo.png'],
            ],
        ],
    ];

    expect($this->asset->findMissingStickerRefs($canvas))->toBe([]);
});

it('a null canvas has no missing stickers', function () {
    expect($this->asset->findMissingStickerRefs(null))->toBe([]);
});

// ── Sanity check against the real known-bad production shape (Y2K) ─────────

it('sanity check: reproduces the real Y2K production bug with an equivalent fixture', function () {
    // Mirrors the actual reported shape: shareCard.layers has two sticker
    // layers pointing at y2k_sticker_1 / y2k_sticker_2, neither catalogued.
    $canvas = [
        'shareCard' => [
            'layers' => [
                ['type' => 'sticker', 'src' => 'y2k_sticker_1'],
                ['type' => 'sticker', 'src' => 'y2k_sticker_2'],
            ],
        ],
    ];

    $missing = $this->asset->findMissingStickerRefs($canvas);

    expect($missing)->toEqualCanonicalizing(['y2k_sticker_1', 'y2k_sticker_2']);
});

// ── createPublicAsset() gate ─────────────────────────────────────────────────

it('createPublicAsset() throws 422 and inserts nothing when the canvas references an uncatalogued sticker', function () {
    $suffix = uniqid();
    $userId = stickerGateTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $title  = "Phantom Sticker {$suffix}";
    $canvas = ['layers' => [['type' => 'sticker', 'src' => "ghost_create_{$suffix}"]]];

    $threw = false;
    try {
        $this->asset->createPublicAsset($title, [], '', '', $canvas, $userId);
    } catch (\Exception $e) {
        $threw = true;
        expect($e->getCode())->toBe(422);
        expect($e->getMessage())->toContain("ghost_create_{$suffix}");
    }

    expect($threw)->toBeTrue();

    $count = (int) $this->db->query(
        "SELECT COUNT(*) c FROM public_assets WHERE title = '{$title}'"
    )->fetch_assoc()['c'];
    expect($count)->toBe(0);
});

it('createPublicAsset() succeeds normally when every referenced sticker is catalogued', function () {
    $suffix = uniqid();
    $userId = stickerGateTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $stickerName = "good_create_{$suffix}";
    catalogMediaImage($this->db, $stickerName);
    $this->mediaImageNames[] = $stickerName;

    $title  = "Clean Sticker {$suffix}";
    $canvas = ['layers' => [['type' => 'sticker', 'src' => $stickerName]]];

    $id = $this->asset->createPublicAsset($title, [], '', '', $canvas, $userId);
    $this->assetIds[] = $id;

    expect($id)->toBeInt();
    $row = $this->db->query("SELECT id FROM public_assets WHERE id = {$id}")->fetch_assoc();
    expect($row)->not->toBeNull();
});

it('createPublicAsset() succeeds normally when the canvas has no stickers', function () {
    $suffix = uniqid();
    $userId = stickerGateTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $title  = "No Stickers {$suffix}";
    $canvas = ['layers' => [['type' => 'text', 'content' => 'hi']]];

    $id = $this->asset->createPublicAsset($title, [], '', '', $canvas, $userId);
    $this->assetIds[] = $id;

    expect($id)->toBeInt();
});

// ── updatePublicAsset() gate ─────────────────────────────────────────────────

it('updatePublicAsset() throws 422 and does not update when the canvas references an uncatalogued sticker', function () {
    $suffix = uniqid();
    $userId = stickerGateTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $originalTitle = "Original Title {$suffix}";
    $id = $this->asset->createPublicAsset($originalTitle, [], '', '', null, $userId);
    $this->assetIds[] = $id;

    $badCanvas = ['layers' => [['type' => 'sticker', 'src' => "ghost_update_{$suffix}"]]];

    $threw = false;
    try {
        $this->asset->updatePublicAsset($id, "Should Not Apply {$suffix}", [], '', '', $badCanvas, $userId);
    } catch (\Exception $e) {
        $threw = true;
        expect($e->getCode())->toBe(422);
    }

    expect($threw)->toBeTrue();

    $row = $this->db->query("SELECT title FROM public_assets WHERE id = {$id}")->fetch_assoc();
    expect($row['title'])->toBe($originalTitle);
});

it('updatePublicAsset() succeeds normally when every referenced sticker is catalogued', function () {
    $suffix = uniqid();
    $userId = stickerGateTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $id = $this->asset->createPublicAsset("Before Update {$suffix}", [], '', '', null, $userId);
    $this->assetIds[] = $id;

    $stickerName = "good_update_{$suffix}";
    catalogMediaImage($this->db, $stickerName);
    $this->mediaImageNames[] = $stickerName;

    $goodCanvas  = ['layers' => [['type' => 'sticker', 'src' => $stickerName]]];
    $updatedTitle = "After Update {$suffix}";

    $this->asset->updatePublicAsset($id, $updatedTitle, [], '', '', $goodCanvas, $userId);

    $row = $this->db->query("SELECT title FROM public_assets WHERE id = {$id}")->fetch_assoc();
    expect($row['title'])->toBe($updatedTitle);
});

// ── brand:import-designs — reject one, keep the batch alive ────────────────

it('brand:import-designs creates the valid design, rejects the one with a missing sticker, and does not fail the whole batch', function () {
    $suffix = uniqid();
    $userId = stickerGateTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $validTitle   = "Import Valid {$suffix}";
    $invalidTitle = "Import Invalid {$suffix}";

    $validCanvas   = ['layers' => [['type' => 'text', 'content' => 'hi']]];
    $invalidCanvas = ['layers' => [['type' => 'sticker', 'src' => "ghost_import_{$suffix}"]]];

    $designs = [
        [
            'title'           => $validTitle,
            'color'           => [],
            'icon'            => '',
            'background'      => '',
            'canvas'          => $validCanvas,
            'category_slug'   => null,
            'idempotency_key' => BrandDesignHasher::hash($validTitle, [], '', '', $validCanvas),
        ],
        [
            'title'           => $invalidTitle,
            'color'           => [],
            'icon'            => '',
            'background'      => '',
            'canvas'          => $invalidCanvas,
            'category_slug'   => null,
            'idempotency_key' => BrandDesignHasher::hash($invalidTitle, [], '', '', $invalidCanvas),
        ],
    ];

    $envelope = [
        'version'                  => 1,
        'exported_at'              => now()->toIso8601String(),
        'source_db'                => 'test',
        'source_submitter_user_id' => $userId,
        'designs'                  => $designs,
    ];

    $file = sys_get_temp_dir() . "/brand-import-sticker-gate-{$suffix}.json";
    file_put_contents($file, json_encode($envelope, JSON_UNESCAPED_UNICODE));
    $this->files[] = $file;

    $exit = Artisan::call('brand:import-designs', ['file' => $file, '--submitter' => (string) $userId]);
    $output = Artisan::output();

    expect($exit)->toBe(0);
    expect($output)->toContain('created: 1');
    expect($output)->toContain('rejected: 1');

    $validRow = $this->db->query(
        "SELECT id FROM public_assets WHERE title = '{$validTitle}'"
    )->fetch_assoc();
    expect($validRow)->not->toBeNull();
    $this->assetIds[] = (int) $validRow['id'];

    $invalidCount = (int) $this->db->query(
        "SELECT COUNT(*) c FROM public_assets WHERE title = '{$invalidTitle}'"
    )->fetch_assoc()['c'];
    expect($invalidCount)->toBe(0);
});

// ── media:audit-orphans — read-only audit ───────────────────────────────────

it('media:audit-orphans reports exactly the known orphan sticker and no false positives on a clean design', function () {
    $suffix = uniqid();
    $userId = stickerGateTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $cleanSticker = "audit_clean_{$suffix}";
    catalogMediaImage($this->db, $cleanSticker);
    $this->mediaImageNames[] = $cleanSticker;

    $cleanTitle  = "Audit Clean {$suffix}";
    $cleanCanvas = ['layers' => [['type' => 'sticker', 'src' => $cleanSticker]]];
    $cleanId = insertRawPublicAsset($this->db, $cleanTitle, $cleanCanvas, $userId);
    $this->assetIds[] = $cleanId;

    $orphanTitle  = "Audit Orphan {$suffix}";
    $orphanSrc    = "audit_orphan_{$suffix}";
    $orphanCanvas = ['layers' => [['type' => 'sticker', 'src' => $orphanSrc]]];
    $orphanId = insertRawPublicAsset($this->db, $orphanTitle, $orphanCanvas, $userId);
    $this->assetIds[] = $orphanId;

    $exit = Artisan::call('media:audit-orphans');
    $output = Artisan::output();

    expect($exit)->toBe(0);
    expect($output)->toContain((string) $orphanId);
    expect($output)->toContain($orphanSrc);
    expect($output)->not->toContain($cleanSticker);
});

// ── HTTP layer: the gate must surface as a clean 422, not a raw 500 ─────────
//
// createPublicAsset() throws a 422 Exception when a sticker is missing from
// the catalog, but AssetController::createPublicAsset() (the HTTP endpoint,
// used by real users submitting UGC from the app) didn't wrap the model call
// in a try/catch — unlike updatePublicAsset(), which already did. Without the
// catch, this exact exception would have surfaced as a generic Laravel 500.

it('POST /api/assets/create responde 422 (no 500) cuando el canvas referencia un sticker no catalogado', function () {
    $suffix = uniqid();
    $userId = stickerGateTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $token = 'tok_sgt_' . $suffix;
    $this->db->query(
        "INSERT INTO personal_access_tokens (token, user_id) VALUES ('{$token}', {$userId})"
    );

    $orphanSrc = "http_orphan_{$suffix}";
    $canvas = ['layers' => [['type' => 'sticker', 'src' => $orphanSrc]]];

    $response = $this->postJson('/api/assets/create', [
        'user_id' => $userId,
        'title'   => 'Diseño con sticker huérfano',
        'colors'  => [],
        'icon'    => '',
        'background' => '',
        'canvas'  => $canvas,
    ], ['Authorization' => "Bearer {$token}"]);

    $response->assertStatus(422);

    $this->db->query("DELETE FROM personal_access_tokens WHERE user_id = {$userId}");
    $row = $this->db->query(
        "SELECT COUNT(*) AS c FROM public_assets WHERE submitter_user_id = {$userId}"
    )->fetch_assoc();
    expect((int) $row['c'])->toBe(0);
});
