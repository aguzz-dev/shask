<?php

use App\Database;
use App\Models\AdminUser;

beforeEach(function () {
    config(['app.admin_path' => 'panel-test']);
    $this->db = new Database;
    $this->email = 'auth_' . uniqid() . '@test.com';
    $this->adminId = (new AdminUser)->create('Auth Test', $this->email, 'clave-larga-123');
});

afterEach(function () {
    $this->db->query("DELETE FROM admin_audit_log WHERE admin_id = {$this->adminId}");
    $this->db->query("DELETE FROM admin_users WHERE id = {$this->adminId}");
});

it('sin sesion redirige al login', function () {
    $this->get('/panel-test')->assertRedirect('/panel-test/login');
});

it('login con credenciales validas entra y audita', function () {
    $response = $this->post('/panel-test/login', [
        'email' => $this->email,
        'password' => 'clave-larga-123',
    ]);
    $response->assertRedirect('/panel-test');
    $this->assertEquals($this->adminId, session('admin_id'));

    $row = $this->db->query(
        "SELECT action FROM admin_audit_log WHERE admin_id = {$this->adminId}"
    )->fetch_assoc();
    expect($row['action'])->toBe('auth.login');
});

it('login con password malo no entra', function () {
    $this->post('/panel-test/login', [
        'email' => $this->email,
        'password' => 'incorrecta',
    ])->assertRedirect('/panel-test/login');
    $this->assertNull(session('admin_id'));
});

it('admin inactivo no entra', function () {
    $this->db->query("UPDATE admin_users SET active = 0 WHERE id = {$this->adminId}");
    $this->post('/panel-test/login', [
        'email' => $this->email,
        'password' => 'clave-larga-123',
    ])->assertRedirect('/panel-test/login');
    $this->assertNull(session('admin_id'));
});

it('con sesion el catalogo responde 200 con headers de seguridad', function () {
    $response = $this->withSession(['admin_id' => $this->adminId])->get('/panel-test');
    $response->assertOk()
        ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
        ->assertHeader('X-Frame-Options', 'DENY');
});

it('logout invalida la sesion', function () {
    $this->withSession(['admin_id' => $this->adminId])
        ->post('/panel-test/logout')
        ->assertRedirect('/panel-test/login');
    $this->assertNull(session('admin_id'));
});

it('aplica rate limit al login', function () {
    // IP propia para no envenenar el limiter de los otros tests (la clave
    // del throttle incluye la IP).
    for ($i = 0; $i < 5; $i++) {
        $this->withServerVariables(['REMOTE_ADDR' => '10.99.99.99'])
            ->post('/panel-test/login', ['email' => $this->email, 'password' => 'mala']);
    }
    $this->withServerVariables(['REMOTE_ADDR' => '10.99.99.99'])
        ->post('/panel-test/login', ['email' => $this->email, 'password' => 'mala'])
        ->assertStatus(429);
});

it('el panel no se expone fuera del ADMIN_PATH', function () {
    // El catch-all /{id} de los buzones responde esas rutas con su vista
    // 404 (status 200, comportamiento preexistente): lo que importa es que
    // el panel admin no aparezca ahí.
    $this->get('/admin')->assertDontSee('SHHASK');
    $this->get('/panel-inexistente')->assertDontSee('SHHASK');
});
