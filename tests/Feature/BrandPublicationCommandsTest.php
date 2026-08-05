<?php

use App\Database;
use App\Models\Asset;
use App\Support\BrandDesignHasher;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

// ── official-brand-designs / PR2 — publication pipeline (export/import) ──
//
// Phase 1/2: `createPublicAsset()` gains an optional `?string $forceStatus`
// so a cross-DB import can insert designs already `approved`, bypassing the
// "first asset from this submitter → pending" rule that exists for organic
// user submissions.
//
// Phase 3/4: `brand:export-designs` / `brand:import-designs`.
//
// IMPORTANT TEST LIMITATION (documented, not a gap in this batch): phpunit.xml
// leaves its sqlite override commented out, so this suite's "test DB" IS the
// same local `sshask` DB the app runs against (see Database::__construct(),
// which always reads DB_HOST/DB_DATABASE/etc from .env — there is no
// connection-swap between origin and destination in test). These tests
// therefore exercise export → import against a SINGLE database and cannot
// prove real cross-environment credential/connection switching — that part
// of PR2's contract (local `sshask` → production) is verified manually, see
// the apply-progress "Manual / Pending" section.

beforeEach(function () {
    $this->db          = new Database;
    $this->asset       = new Asset;
    $this->userIds     = [];
    $this->assetIds    = [];
    $this->categoryIds = [];
    $this->files       = [];
    $this->auditIdBefore = (int) ($this->db->query(
        'SELECT COALESCE(MAX(id), 0) AS m FROM admin_audit_log'
    )->fetch_assoc()['m']);
});

afterEach(function () {
    foreach ($this->assetIds as $id) {
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    foreach ($this->categoryIds as $id) {
        $this->db->query("DELETE FROM categories WHERE id = {$id}");
    }
    foreach ($this->userIds as $id) {
        $this->db->query("DELETE FROM users WHERE id = {$id}");
    }
    // Only deletes rows created DURING this test (id > snapshot taken in
    // beforeEach) — never touches pre-existing audit history.
    $this->db->query("DELETE FROM admin_audit_log WHERE id > {$this->auditIdBefore}");
    foreach ($this->files as $f) {
        if (file_exists($f)) {
            unlink($f);
        }
    }
});

function createBrandPubTestUser(Database $db, string $suffix): int
{
    $db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Brand Pub Test', 'bpt_{$suffix}', 'bpt_{$suffix}@test.com', 'x', 0)"
    );
    return (int) $db->dbConnection->insert_id;
}

/**
 * Builds a well-formed export envelope in memory (mirrors what
 * brand:export-designs would write), writes it to a temp file, and returns
 * [path, designsArray] so tests can assert against the exact same data they
 * fed in.
 */
function writeBrandEnvelopeFile(array $designs, int $submitterId, string $suffix): string
{
    $envelope = [
        'version'                  => 1,
        'exported_at'              => now()->toIso8601String(),
        'source_db'                => DB::connection()->getDatabaseName(),
        'source_submitter_user_id' => $submitterId,
        'designs'                  => $designs,
    ];

    $path = sys_get_temp_dir() . "/brand-import-test-{$suffix}.json";
    file_put_contents($path, json_encode($envelope, JSON_UNESCAPED_UNICODE));

    return $path;
}

function brandDesignEntry(string $title, ?string $categorySlug = null): array
{
    $color      = ['r' => 1, 'g' => 2, 'b' => 3];
    $icon       = 'star';
    $background = 'bg';
    $canvas     = ['layers' => [['type' => 'text', 'value' => $title]]];

    return [
        'title'           => $title,
        'color'           => $color,
        'icon'            => $icon,
        'background'      => $background,
        'canvas'          => $canvas,
        'category_slug'   => $categorySlug,
        'idempotency_key' => BrandDesignHasher::hash($title, $color, $icon, $background, $canvas),
    ];
}

it('createPublicAsset() forces the given status for a brand-new submitter, bypassing the first-asset-pending trap', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    // This submitter has ZERO prior designs — without forceStatus this would
    // normally land as 'pending' (the organic first-asset-pending rule).
    $id = $this->asset->createPublicAsset(
        "Forced Approved {$suffix}",
        [],
        'star',
        '',
        null,
        $userId,
        'approved'
    );
    $this->assetIds[] = $id;

    $row = $this->db->query("SELECT status FROM public_assets WHERE id = {$id}")->fetch_assoc();
    expect($row['status'])->toBe('approved');
});

it('createPublicAsset() still applies the organic first-asset-pending rule when forceStatus is not given', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $id = $this->asset->createPublicAsset(
        "Organic First {$suffix}",
        [],
        '',
        '',
        null,
        $userId
    );
    $this->assetIds[] = $id;

    $row = $this->db->query("SELECT status FROM public_assets WHERE id = {$id}")->fetch_assoc();
    expect($row['status'])->toBe('pending');
});

// ── 3.1: brand:export-designs — envelope shape + idempotency_key ───────────

it('brand:export-designs writes a JSON envelope with version, source metadata and a per-design idempotency_key', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $canvas = ['layers' => [['type' => 'text', 'value' => 'hi']]];
    $id = $this->asset->createPublicAsset(
        "Export Me {$suffix}",
        ['r' => 9],
        'sticker',
        'bg-key',
        $canvas,
        $userId,
        'approved'
    );
    $this->assetIds[] = $id;

    $out = sys_get_temp_dir() . "/brand-export-test-{$suffix}.json";
    $this->files[] = $out;

    Artisan::call('brand:export-designs', ['--submitter' => (string) $userId, '--out' => $out]);

    expect(file_exists($out))->toBeTrue();
    $envelope = json_decode(file_get_contents($out), true);

    expect($envelope['version'])->toBe(1)
        ->and($envelope)->toHaveKey('exported_at')
        ->and($envelope)->toHaveKey('source_db')
        ->and($envelope['source_submitter_user_id'])->toBe($userId)
        ->and($envelope['designs'])->toBeArray();

    $design = collect($envelope['designs'])->firstWhere('title', "Export Me {$suffix}");
    expect($design)->not->toBeNull()
        ->and($design)->toHaveKeys(['title', 'color', 'icon', 'background', 'canvas', 'category_slug', 'idempotency_key']);

    $expectedHash = BrandDesignHasher::hash(
        $design['title'],
        $design['color'],
        $design['icon'],
        $design['background'],
        $design['canvas']
    );
    expect($design['idempotency_key'])->toBe($expectedHash);
});

it('brand:export-designs refuses to run without --submitter', function () {
    $exit = Artisan::call('brand:export-designs', []);
    expect($exit)->not->toBe(0);
});

// ── 3.2 / 4: brand:import-designs ───────────────────────────────────────────

it('brand:import-designs aborts before inserting any row when --submitter is missing', function () {
    $suffix  = uniqid();
    $design  = brandDesignEntry("No Submitter {$suffix}");
    $file    = writeBrandEnvelopeFile([$design], 0, $suffix);
    $this->files[] = $file;

    $before = (int) $this->db->query(
        "SELECT COUNT(*) c FROM public_assets WHERE title = '{$design['title']}'"
    )->fetch_assoc()['c'];

    $exit = Artisan::call('brand:import-designs', ['file' => $file]);

    $after = (int) $this->db->query(
        "SELECT COUNT(*) c FROM public_assets WHERE title = '{$design['title']}'"
    )->fetch_assoc()['c'];

    expect($exit)->not->toBe(0)
        ->and($after)->toBe($before)
        ->and($after)->toBe(0);

    $auditAfter = (int) $this->db->query(
        "SELECT COUNT(*) c FROM admin_audit_log WHERE id > {$this->auditIdBefore}"
    )->fetch_assoc()['c'];
    expect($auditAfter)->toBe(0);
});

it('brand:import-designs --dry-run inserts zero rows and logs zero audit rows', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $design = brandDesignEntry("Dry Run {$suffix}");
    $file   = writeBrandEnvelopeFile([$design], $userId, $suffix);
    $this->files[] = $file;

    $exit = Artisan::call('brand:import-designs', [
        'file'         => $file,
        '--submitter'  => (string) $userId,
        '--dry-run'    => true,
    ]);

    expect($exit)->toBe(0);

    $count = (int) $this->db->query(
        "SELECT COUNT(*) c FROM public_assets WHERE submitter_user_id = {$userId}"
    )->fetch_assoc()['c'];
    expect($count)->toBe(0);

    $auditAfter = (int) $this->db->query(
        "SELECT COUNT(*) c FROM admin_audit_log WHERE id > {$this->auditIdBefore}"
    )->fetch_assoc()['c'];
    expect($auditAfter)->toBe(0);
});

it('brand:import-designs is idempotent: a second run creates 0 and skips everything already imported', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $designs = [
        brandDesignEntry("Idem A {$suffix}"),
        brandDesignEntry("Idem B {$suffix}"),
    ];
    $file = writeBrandEnvelopeFile($designs, $userId, $suffix);
    $this->files[] = $file;

    $firstExit = Artisan::call('brand:import-designs', ['file' => $file, '--submitter' => (string) $userId]);
    expect($firstExit)->toBe(0);

    $created = $this->db->query(
        "SELECT id FROM public_assets WHERE submitter_user_id = {$userId}"
    )->fetch_all(MYSQLI_ASSOC);
    foreach ($created as $row) {
        $this->assetIds[] = (int) $row['id'];
    }
    expect(count($created))->toBe(2);

    $secondExit = Artisan::call('brand:import-designs', ['file' => $file, '--submitter' => (string) $userId]);
    $secondOutput = Artisan::output();
    expect($secondExit)->toBe(0);

    $countAfterSecondRun = (int) $this->db->query(
        "SELECT COUNT(*) c FROM public_assets WHERE submitter_user_id = {$userId}"
    )->fetch_assoc()['c'];
    expect($countAfterSecondRun)->toBe(2)
        ->and($secondOutput)->toContain('created: 0')
        ->and($secondOutput)->toContain('skipped: 2');
});

it('all N designs from a first import are inserted as approved, including the submitter\'s very first design', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $design = brandDesignEntry("First Ever {$suffix}");
    $file   = writeBrandEnvelopeFile([$design], $userId, $suffix);
    $this->files[] = $file;

    Artisan::call('brand:import-designs', ['file' => $file, '--submitter' => (string) $userId]);

    $row = $this->db->query(
        "SELECT id, status FROM public_assets WHERE submitter_user_id = {$userId} ORDER BY id DESC LIMIT 1"
    )->fetch_assoc();
    $this->assetIds[] = (int) $row['id'];

    expect($row['status'])->toBe('approved');
});

it('logs an admin_audit_log row with the sentinel admin_id = 0 and actor system:cli when --admin is omitted', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $design = brandDesignEntry("Audit Default {$suffix}");
    $file   = writeBrandEnvelopeFile([$design], $userId, $suffix);
    $this->files[] = $file;

    Artisan::call('brand:import-designs', ['file' => $file, '--submitter' => (string) $userId]);

    $created = $this->db->query(
        "SELECT id FROM public_assets WHERE submitter_user_id = {$userId}"
    )->fetch_all(MYSQLI_ASSOC);
    foreach ($created as $row) {
        $this->assetIds[] = (int) $row['id'];
    }

    $auditRow = $this->db->query(
        "SELECT admin_id, detail FROM admin_audit_log WHERE id > {$this->auditIdBefore} ORDER BY id DESC LIMIT 1"
    )->fetch_assoc();

    expect($auditRow)->not->toBeNull()
        ->and((int) $auditRow['admin_id'])->toBe(0);

    $detail = json_decode($auditRow['detail'], true);
    expect($detail['actor'])->toBe('system:cli');
});

it('logs the real admin id when --admin is provided', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $design = brandDesignEntry("Audit Admin {$suffix}");
    $file   = writeBrandEnvelopeFile([$design], $userId, $suffix);
    $this->files[] = $file;

    Artisan::call('brand:import-designs', [
        'file'        => $file,
        '--submitter' => (string) $userId,
        '--admin'     => '42',
    ]);

    $created = $this->db->query(
        "SELECT id FROM public_assets WHERE submitter_user_id = {$userId}"
    )->fetch_all(MYSQLI_ASSOC);
    foreach ($created as $row) {
        $this->assetIds[] = (int) $row['id'];
    }

    $auditRow = $this->db->query(
        "SELECT admin_id FROM admin_audit_log WHERE id > {$this->auditIdBefore} ORDER BY id DESC LIMIT 1"
    )->fetch_assoc();

    expect((int) $auditRow['admin_id'])->toBe(42);
});

it('a fully-skipped re-run still logs exactly one audit row reflecting the skip counts', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $design = brandDesignEntry("Skip Audit {$suffix}");
    $file   = writeBrandEnvelopeFile([$design], $userId, $suffix);
    $this->files[] = $file;

    Artisan::call('brand:import-designs', ['file' => $file, '--submitter' => (string) $userId]);
    $created = $this->db->query(
        "SELECT id FROM public_assets WHERE submitter_user_id = {$userId}"
    )->fetch_all(MYSQLI_ASSOC);
    foreach ($created as $row) {
        $this->assetIds[] = (int) $row['id'];
    }

    $auditIdAfterFirstRun = (int) $this->db->query(
        'SELECT COALESCE(MAX(id), 0) AS m FROM admin_audit_log'
    )->fetch_assoc()['m'];

    Artisan::call('brand:import-designs', ['file' => $file, '--submitter' => (string) $userId]);

    $newAuditRows = (int) $this->db->query(
        "SELECT COUNT(*) c FROM admin_audit_log WHERE id > {$auditIdAfterFirstRun}"
    )->fetch_assoc()['c'];

    expect($newAuditRows)->toBe(1);
});

it('resolves category_slug to category_id on import when the category exists', function () {
    $suffix = uniqid();
    $userId = createBrandPubTestUser($this->db, $suffix);
    $this->userIds[] = $userId;

    $slug = "brand-import-{$suffix}";
    $name = "Brand Import {$suffix}";
    $stmt = $this->db->dbConnection->prepare(
        'INSERT INTO categories (slug, name, position) VALUES (?, ?, 0)'
    );
    $stmt->bind_param('ss', $slug, $name);
    $stmt->execute();
    $categoryId = (int) $this->db->dbConnection->insert_id;
    $this->categoryIds[] = $categoryId;
    $stmt->close();

    $design = brandDesignEntry("Categorized {$suffix}", $slug);
    $file   = writeBrandEnvelopeFile([$design], $userId, $suffix);
    $this->files[] = $file;

    Artisan::call('brand:import-designs', ['file' => $file, '--submitter' => (string) $userId]);

    $row = $this->db->query(
        "SELECT id, category_id FROM public_assets WHERE submitter_user_id = {$userId} ORDER BY id DESC LIMIT 1"
    )->fetch_assoc();
    $this->assetIds[] = (int) $row['id'];

    expect((int) $row['category_id'])->toBe($categoryId);
});

// ── preset-retirement regression lock (Testing Strategy, PR2) ──────────────
//
// Despublishing the 5 legacy NULL-submitter system presets (manual step 5.4)
// must not break getAllAssets()'s other branch (the editor-ingredient path)
// and must remove the preset from the catalog list. This test doesn't touch
// any PR2 code path directly — it locks pre-existing, unrelated behavior so
// a future change to getAllAssets() can't silently regress the rollout step.

it('despublishing a NULL-submitter preset removes it from getAllAssets() public_assets without breaking the assets branch', function () {
    $suffix = uniqid();
    $stmt = $this->db->dbConnection->prepare(
        "INSERT INTO public_assets (title, color, icon, background, status, submitter_user_id)
         VALUES (?, '[]', '', '', 'approved', NULL)"
    );
    $title = "Legacy Preset {$suffix}";
    $stmt->bind_param('s', $title);
    $stmt->execute();
    $presetId = (int) $this->db->dbConnection->insert_id;
    $this->assetIds[] = $presetId;
    $stmt->close();

    $before = collect($this->asset->getAllAssets()['public_assets'])->pluck('id')->map(fn ($id) => (int) $id);
    expect($before)->toContain($presetId);

    $this->db->query("UPDATE public_assets SET status = 'removed' WHERE id = {$presetId}");

    $afterAssets = $this->asset->getAllAssets();
    $after = collect($afterAssets['public_assets'])->pluck('id')->map(fn ($id) => (int) $id);
    expect($after)->not->toContain($presetId)
        ->and($afterAssets)->toHaveKey('assets');
});
