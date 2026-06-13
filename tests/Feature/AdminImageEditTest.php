<?php

use App\Database;
use App\Models\AdminUser;

beforeEach(function () {
    config(['app.admin_path' => 'panel-test']);
    $this->db = new Database;
    $this->adminId = (new AdminUser)->create('Edit Test', 'ed_' . uniqid() . '@test.com', 'clave-larga-123');
    $this->name = 'edit_' . uniqid();
    $this->db->query("INSERT INTO media_images (name, type) VALUES ('{$this->name}', 'sticker')");
    $this->imageId = $this->db->dbConnection->insert_id;
    file_put_contents(public_path("images/{$this->name}.png"), 'x');
    $slug = 'cv_' . uniqid();
    $this->db->query("INSERT INTO sticker_packs (name, slug, cover) VALUES ('CoverPack', '{$slug}', '{$this->name}')");
    $this->packId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM media_images WHERE id = {$this->imageId}");
    $this->db->query("DELETE FROM sticker_packs WHERE id = {$this->packId}");
    @unlink(public_path("images/{$this->name}.png"));
    $this->db->query("DELETE FROM admin_audit_log WHERE admin_id = {$this->adminId}");
    $this->db->query("DELETE FROM admin_users WHERE id = {$this->adminId}");
});

it('edita la metadata y audita', function () {
    $this->withSession(['admin_id' => $this->adminId])
        ->post("/panel-test/images/{$this->imageId}/edit", [
            'type' => 'background',
            'category' => 'noche',
            'tags' => 'ciudad, luces',
            'pack_id' => '',
            'sort' => 5,
        ])->assertRedirect('/panel-test');

    $row = $this->db->query("SELECT * FROM media_images WHERE id = {$this->imageId}")->fetch_assoc();
    expect($row['type'])->toBe('background')
        ->and($row['category'])->toBe('noche')
        ->and((int) $row['sort'])->toBe(5);

    $audit = $this->db->query(
        "SELECT COUNT(*) AS c FROM admin_audit_log WHERE admin_id = {$this->adminId} AND action = 'image.edit'"
    )->fetch_assoc();
    expect((int) $audit['c'])->toBe(1);
});

it('borra la imagen, el archivo, limpia portadas y audita', function () {
    $this->withSession(['admin_id' => $this->adminId])
        ->post("/panel-test/images/{$this->imageId}/delete")
        ->assertRedirect('/panel-test');

    $row = $this->db->query("SELECT COUNT(*) AS c FROM media_images WHERE id = {$this->imageId}")->fetch_assoc();
    expect((int) $row['c'])->toBe(0)
        ->and(file_exists(public_path("images/{$this->name}.png")))->toBeFalse();

    $pack = $this->db->query("SELECT cover FROM sticker_packs WHERE id = {$this->packId}")->fetch_assoc();
    expect($pack['cover'])->toBeNull();

    $audit = $this->db->query(
        "SELECT COUNT(*) AS c FROM admin_audit_log WHERE admin_id = {$this->adminId} AND action = 'image.delete'"
    )->fetch_assoc();
    expect((int) $audit['c'])->toBe(1);
});
