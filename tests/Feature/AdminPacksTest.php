<?php

use App\Database;
use App\Models\AdminUser;
use Illuminate\Support\Str;

beforeEach(function () {
    config(['app.admin_path' => 'panel-test']);
    $this->db = new Database;
    $this->adminId = (new AdminUser)->create('Pk Test', 'pk_' . uniqid() . '@test.com', 'clave-larga-123');
    $this->createdPackIds = [];
    $this->createdImageIds = [];
});

afterEach(function () {
    foreach ($this->createdImageIds as $id) {
        $this->db->query("DELETE FROM media_images WHERE id = {$id}");
    }
    foreach ($this->createdPackIds as $id) {
        $this->db->query("DELETE FROM sticker_packs WHERE id = {$id}");
    }
    $this->db->query("DELETE FROM admin_audit_log WHERE admin_id = {$this->adminId}");
    $this->db->query("DELETE FROM admin_users WHERE id = {$this->adminId}");
});

it('crea un pack con slug autogenerado y audita', function () {
    $name = 'Pack Góticos ' . uniqid();
    $this->withSession(['admin_id' => $this->adminId])
        ->post('/panel-test/packs/create', [
            'name' => $name,
            'is_premium' => '1',
            'price' => 150,
            'sort' => 0,
        ])->assertRedirect('/panel-test/packs');

    $slug = Str::slug($name, '_');
    $pack = $this->db->query("SELECT * FROM sticker_packs WHERE slug = '{$slug}'")->fetch_assoc();
    expect($pack)->not->toBeNull()->and((int) $pack['is_premium'])->toBe(1);
    $this->createdPackIds[] = (int) $pack['id'];

    $audit = $this->db->query(
        "SELECT COUNT(*) AS c FROM admin_audit_log WHERE admin_id = {$this->adminId} AND action = 'pack.create'"
    )->fetch_assoc();
    expect((int) $audit['c'])->toBe(1);
});

it('edita un pack', function () {
    $slug = 'pke_' . uniqid();
    $this->db->query("INSERT INTO sticker_packs (name, slug, price) VALUES ('Original', '{$slug}', 0)");
    $packId = $this->db->dbConnection->insert_id;
    $this->createdPackIds[] = $packId;

    $this->withSession(['admin_id' => $this->adminId])
        ->post("/panel-test/packs/{$packId}/edit", [
            'name' => 'Renombrado',
            'is_premium' => '0',
            'price' => 50,
            'sort' => 2,
        ])->assertRedirect('/panel-test/packs');

    $pack = $this->db->query("SELECT * FROM sticker_packs WHERE id = {$packId}")->fetch_assoc();
    expect($pack['name'])->toBe('Renombrado')->and((int) $pack['price'])->toBe(50);
});

it('borra un pack y sus imagenes quedan sin pack', function () {
    $slug = 'pkd_' . uniqid();
    $this->db->query("INSERT INTO sticker_packs (name, slug) VALUES ('Borrable', '{$slug}')");
    $packId = $this->db->dbConnection->insert_id;
    $imgName = 'pkimg_' . uniqid();
    $this->db->query("INSERT INTO media_images (name, type, pack_id) VALUES ('{$imgName}', 'sticker', {$packId})");
    $this->createdImageIds[] = $this->db->dbConnection->insert_id;

    $this->withSession(['admin_id' => $this->adminId])
        ->post("/panel-test/packs/{$packId}/delete")
        ->assertRedirect('/panel-test/packs');

    $count = $this->db->query("SELECT COUNT(*) AS c FROM sticker_packs WHERE id = {$packId}")->fetch_assoc();
    $img = $this->db->query("SELECT pack_id FROM media_images WHERE name = '{$imgName}'")->fetch_assoc();
    expect((int) $count['c'])->toBe(0)->and($img['pack_id'])->toBeNull();
});
