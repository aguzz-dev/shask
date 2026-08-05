<?php

use App\Database;
use App\Models\User;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->username = "res_{$suffix}";
    $this->email    = "res_{$suffix}@test.com";
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Test Reserved', '{$this->username}', '{$this->email}', 'x', 0)"
    );
    $this->userId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

it('checkUsername rechaza el username reservado shhask', function () {
    expect((new User)->checkUsername('shhask'))->toBeFalse();
});

it('checkUsername rechaza el username reservado sin importar mayúsculas', function () {
    expect((new User)->checkUsername('ShHask'))->toBeFalse();
});

it('checkUsername sigue aceptando usernames libres', function () {
    expect((new User)->checkUsername('libre_' . uniqid()))->toBeTrue();
});

it('update() rechaza cambiarse al username reservado shhask', function () {
    $request = new \Illuminate\Http\Request();
    $request->replace([
        'id'       => (string) $this->userId,
        'username' => 'shhask',
    ]);

    expect(fn () => (new User)->update($request))->toThrow(Exception::class);

    $row = $this->db->query(
        "SELECT username FROM users WHERE id = {$this->userId}"
    )->fetch_assoc();
    expect($row['username'])->toBe($this->username);
});

it('update() sigue persistiendo cambios legítimos tras pasar a prepared statements', function () {
    $request = new \Illuminate\Http\Request();
    $request->replace([
        'id'        => (string) $this->userId,
        'full_name' => 'Nombre Actualizado',
        'username'  => $this->username,
        'email'     => $this->email,
    ]);

    $result = (new User)->update($request);

    expect($result['full_name'])->toBe('Nombre Actualizado');

    $row = $this->db->query(
        "SELECT full_name FROM users WHERE id = {$this->userId}"
    )->fetch_assoc();
    expect($row['full_name'])->toBe('Nombre Actualizado');
});

it('update() guarda un intento de inyección SQL como texto literal', function () {
    $payload = "x'; DROP TABLE users; --";
    $request = new \Illuminate\Http\Request();
    $request->replace([
        'id'        => (string) $this->userId,
        'full_name' => $payload,
        'username'  => $this->username,
        'email'     => $this->email,
    ]);

    (new User)->update($request);

    $row = $this->db->query(
        "SELECT full_name FROM users WHERE id = {$this->userId}"
    )->fetch_assoc();
    expect($row['full_name'])->toBe($payload);

    $count = $this->db->query('SELECT COUNT(*) as c FROM users')->fetch_assoc();
    expect($count['c'])->toBeGreaterThan(0);
});

it('update() ignora claves del body que no son identificadores SQL válidos', function () {
    $request = new \Illuminate\Http\Request();
    $request->replace([
        'id'                     => (string) $this->userId,
        'username'               => $this->username,
        'email'                  => $this->email,
        "id = 1) --"             => 'malicioso',
    ]);

    $result = (new User)->update($request);

    expect((int) $result['id'])->toBe($this->userId);
});
