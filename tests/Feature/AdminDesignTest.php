<?php

use App\Database;
use App\Models\AdminUser;

beforeEach(function () {
    config(['app.admin_path' => 'panel-test']);
    $this->db = new Database;
    $this->adminId = (new AdminUser)->create('Dz Test', 'dz_' . uniqid() . '@test.com', 'clave-larga-123');
    $this->createdAssetIds = [];
});

afterEach(function () {
    foreach ($this->createdAssetIds as $id) {
        $this->db->query("DELETE FROM public_assets WHERE id = {$id}");
    }
    $this->db->query("DELETE FROM admin_audit_log WHERE admin_id = {$this->adminId}");
    $this->db->query("DELETE FROM admin_users WHERE id = {$this->adminId}");
});

it('lista los presets de diseño disponibles', function () {
    $this->withSession(['admin_id' => $this->adminId])
        ->get('/panel-test/designs')
        ->assertOk()
        ->assertSee('Coquette')
        ->assertSee('Y2K');
});

it('publica un preset como asset global y audita', function () {
    $this->withSession(['admin_id' => $this->adminId])
        ->post('/panel-test/designs/coquette/publish')
        ->assertRedirect('/panel-test/designs');

    $row = $this->db->query(
        "SELECT * FROM public_assets WHERE title = 'Coquette' ORDER BY id DESC LIMIT 1"
    )->fetch_assoc();
    expect($row)->not->toBeNull();
    $this->createdAssetIds[] = (int) $row['id'];

    // El canvas se guardó como bundle (tiene shareCard).
    $canvas = json_decode($row['canvas'], true);
    expect($canvas)->toHaveKey('shareCard');

    $audit = $this->db->query(
        "SELECT COUNT(*) AS c FROM admin_audit_log WHERE admin_id = {$this->adminId} AND action = 'design.publish'"
    )->fetch_assoc();
    expect((int) $audit['c'])->toBe(1);
});

it('rechaza publicar un preset inexistente', function () {
    $this->withSession(['admin_id' => $this->adminId])
        ->post('/panel-test/designs/no_existe/publish')
        ->assertNotFound();
});

it('despublica un asset global y audita', function () {
    $this->db->query(
        "INSERT INTO public_assets (title, color, icon, background, canvas)
         VALUES ('Borrable', '[]', '', '', '{\"shareCard\":{}}')"
    );
    $id = $this->db->dbConnection->insert_id;
    $this->createdAssetIds[] = $id;

    $this->withSession(['admin_id' => $this->adminId])
        ->post("/panel-test/designs/{$id}/unpublish")
        ->assertRedirect('/panel-test/designs');

    $count = $this->db->query("SELECT COUNT(*) AS c FROM public_assets WHERE id = {$id}")->fetch_assoc();
    expect((int) $count['c'])->toBe(0);

    $audit = $this->db->query(
        "SELECT COUNT(*) AS c FROM admin_audit_log WHERE admin_id = {$this->adminId} AND action = 'design.unpublish'"
    )->fetch_assoc();
    expect((int) $audit['c'])->toBe(1);
});
