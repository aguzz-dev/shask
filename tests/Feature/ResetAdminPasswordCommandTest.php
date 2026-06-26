<?php

use App\Database;
use App\Models\AdminUser;

beforeEach(function () {
    $this->db = new Database;
    $this->email = 'reset_' . uniqid() . '@test.com';
    $this->adminId = (new AdminUser)->create('Reset Test', $this->email, 'clave-vieja-123');
});

afterEach(function () {
    $this->db->query("DELETE FROM admin_users WHERE id = {$this->adminId}");
});

it('resetea el password de un admin existente', function () {
    $this->artisan('admin:password')
        ->expectsQuestion('Email', $this->email)
        ->expectsQuestion('Password nuevo (mín. 10 caracteres)', 'clave-nueva-456')
        ->assertSuccessful();

    $model = new AdminUser;
    expect($model->verifyCredentials($this->email, 'clave-vieja-123'))->toBeNull()
        ->and($model->verifyCredentials($this->email, 'clave-nueva-456'))->not->toBeNull();
});

it('rechaza si el email no existe', function () {
    $this->artisan('admin:password')
        ->expectsQuestion('Email', 'no_existe_' . uniqid() . '@test.com')
        ->expectsQuestion('Password nuevo (mín. 10 caracteres)', 'clave-nueva-456')
        ->assertFailed();
});

it('rechaza un password corto', function () {
    $this->artisan('admin:password')
        ->expectsQuestion('Email', $this->email)
        ->expectsQuestion('Password nuevo (mín. 10 caracteres)', 'corta')
        ->assertFailed();

    // El password viejo sigue siendo válido (no se tocó).
    expect((new AdminUser)->verifyCredentials($this->email, 'clave-vieja-123'))->not->toBeNull();
});
