<?php

use App\Database;
use App\Models\AdminUser;

it('crea un admin por consola', function () {
    $email = 'cmd_' . uniqid() . '@test.com';

    $this->artisan('admin:create')
        ->expectsQuestion('Nombre', 'Gonza CLI')
        ->expectsQuestion('Email', $email)
        ->expectsQuestion('Password (mín. 10 caracteres)', 'clave-larga-123')
        ->assertSuccessful();

    $admin = (new AdminUser)->findByEmail($email);
    expect($admin)->not->toBeNull();

    (new Database)->query("DELETE FROM admin_users WHERE id = {$admin['id']}");
});

it('rechaza un password corto', function () {
    $this->artisan('admin:create')
        ->expectsQuestion('Nombre', 'Gonza CLI')
        ->expectsQuestion('Email', 'corto_' . uniqid() . '@test.com')
        ->expectsQuestion('Password (mín. 10 caracteres)', 'corta')
        ->assertFailed();
});
