<?php

use App\Database;
use App\Models\AdminUser;
use App\Models\AdminAuditLog;

beforeEach(function () {
    $this->db = new Database;
    $this->email = 'admin_' . uniqid() . '@test.com';
});

afterEach(function () {
    if (isset($this->adminId)) {
        $this->db->query("DELETE FROM admin_audit_log WHERE admin_id = {$this->adminId}");
        $this->db->query("DELETE FROM admin_users WHERE id = {$this->adminId}");
    }
});

it('crea un admin con password hasheado y lo encuentra por email', function () {
    $model = new AdminUser;
    $this->adminId = $model->create('Gonza', $this->email, 'secreto123');

    $admin = $model->findByEmail($this->email);
    expect($admin)->not->toBeNull()
        ->and($admin['name'])->toBe('Gonza')
        ->and((int) $admin['active'])->toBe(1)
        ->and(password_verify('secreto123', $admin['password']))->toBeTrue();
});

it('verifica credenciales y registra last_login', function () {
    $model = new AdminUser;
    $this->adminId = $model->create('Gonza', $this->email, 'secreto123');

    expect($model->verifyCredentials($this->email, 'mala'))->toBeNull()
        ->and($model->verifyCredentials($this->email, 'secreto123')['id'])
        ->toBe($this->adminId);

    $model->touchLastLogin($this->adminId);
    $admin = $model->findByEmail($this->email);
    expect($admin['last_login_at'])->not->toBeNull();
});

it('no verifica credenciales de un admin inactivo', function () {
    $model = new AdminUser;
    $this->adminId = $model->create('Gonza', $this->email, 'secreto123');
    $this->db->query("UPDATE admin_users SET active = 0 WHERE id = {$this->adminId}");

    expect($model->verifyCredentials($this->email, 'secreto123'))->toBeNull();
});

it('registra acciones en el audit log', function () {
    $model = new AdminUser;
    $this->adminId = $model->create('Gonza', $this->email, 'x');

    AdminAuditLog::log($this->adminId, 'image.upload', ['name' => 'fire'], '1.2.3.4');

    $row = $this->db->query(
        "SELECT * FROM admin_audit_log WHERE admin_id = {$this->adminId}"
    )->fetch_assoc();
    expect($row['action'])->toBe('image.upload')
        ->and(json_decode($row['detail'], true))->toBe(['name' => 'fire'])
        ->and($row['ip'])->toBe('1.2.3.4');
});
