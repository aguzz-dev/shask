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
