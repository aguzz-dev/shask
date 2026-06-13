<?php

use App\Database;
use App\Models\AdminUser;

beforeEach(function () {
    config(['app.admin_path' => 'panel-test']);
    $this->db = new Database;
    $this->adminId = (new AdminUser)->create('Cat Test', 'cat_' . uniqid() . '@test.com', 'clave-larga-123');
    $this->suffix = uniqid();
    $this->db->query("INSERT INTO sticker_packs (name, slug, is_premium, price) VALUES ('Pack {$this->suffix}', 'pk_{$this->suffix}', 1, 100)");
    $this->packId = $this->db->dbConnection->insert_id;
    $this->db->query("INSERT INTO media_images (name, type, category, tags, pack_id)
        VALUES ('img_{$this->suffix}', 'sticker', 'fiesta', '[\"fuego\"]', {$this->packId})");
    $this->imageId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM media_images WHERE id = {$this->imageId}");
    $this->db->query("DELETE FROM sticker_packs WHERE id = {$this->packId}");
    $this->db->query("DELETE FROM admin_audit_log WHERE admin_id = {$this->adminId}");
    $this->db->query("DELETE FROM admin_users WHERE id = {$this->adminId}");
});

it('lista el catalogo con la imagen y su pack', function () {
    $this->withSession(['admin_id' => $this->adminId])
        ->get('/panel-test')
        ->assertOk()
        ->assertSee("img_{$this->suffix}")
        ->assertSee("Pack {$this->suffix}");
});

it('busca por nombre y filtra por tipo', function () {
    $this->withSession(['admin_id' => $this->adminId])
        ->get('/panel-test?q=img_' . $this->suffix)
        ->assertOk()
        ->assertSee("img_{$this->suffix}");

    // El input de búsqueda re-muestra el query, así que el assert apunta a
    // la celda de la tabla (<b>nombre</b>), no al texto suelto.
    $this->withSession(['admin_id' => $this->adminId])
        ->get('/panel-test?type=background&q=img_' . $this->suffix)
        ->assertOk()
        ->assertDontSee("<b>img_{$this->suffix}</b>", false);
});
