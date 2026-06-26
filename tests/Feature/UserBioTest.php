<?php

use App\Database;
use App\Models\User;

beforeEach(function () {
    $this->db = new Database;
    $suffix = uniqid();
    $this->username = "bio_{$suffix}";
    $this->email    = "bio_{$suffix}@test.com";
    $this->db->query(
        "INSERT INTO users (full_name, username, email, password, hype)
         VALUES ('Test Bio', '{$this->username}', '{$this->email}', 'x', 0)"
    );
    $this->userId = $this->db->dbConnection->insert_id;
});

afterEach(function () {
    $this->db->query("DELETE FROM users WHERE id = {$this->userId}");
});

// --- A.1.3: User::updateBio() ---

it('persiste la bio del usuario', function () {
    (new User)->updateBio($this->userId, 'Soy desarrollador.');

    $row = $this->db->query(
        "SELECT bio FROM users WHERE id = {$this->userId}"
    )->fetch_assoc();

    expect($row['bio'])->toBe('Soy desarrollador.');
});

it('sobreescribe la bio existente', function () {
    $model = new User;
    $model->updateBio($this->userId, 'Primera bio');
    $model->updateBio($this->userId, 'Bio actualizada');

    $row = $this->db->query(
        "SELECT bio FROM users WHERE id = {$this->userId}"
    )->fetch_assoc();

    expect($row['bio'])->toBe('Bio actualizada');
});

it('limpia la bio cuando se envía cadena vacía', function () {
    $model = new User;
    $model->updateBio($this->userId, 'Tenía algo');
    $model->updateBio($this->userId, '');

    $row = $this->db->query(
        "SELECT bio FROM users WHERE id = {$this->userId}"
    )->fetch_assoc();

    expect($row['bio'])->toBe('');
});

it('rechaza bio con más de 200 caracteres y lanza excepción 422', function () {
    $bio = str_repeat('a', 201);

    expect(fn () => (new User)->updateBio($this->userId, $bio))
        ->toThrow(Exception::class);

    $row = $this->db->query(
        "SELECT bio FROM users WHERE id = {$this->userId}"
    )->fetch_assoc();

    // La bio en DB no debe haber cambiado (sigue siendo NULL)
    expect($row['bio'])->toBeNull();
});

it('almacena intentos de inyección SQL como texto literal', function () {
    $payload = "'; DROP TABLE users; --";
    (new User)->updateBio($this->userId, $payload);

    $row = $this->db->query(
        "SELECT bio FROM users WHERE id = {$this->userId}"
    )->fetch_assoc();

    expect($row['bio'])->toBe($payload);
    // Verificar que la tabla sigue en pie
    $count = $this->db->query("SELECT COUNT(*) as c FROM users")->fetch_assoc();
    expect($count['c'])->toBeGreaterThan(0);
});

// --- A.1.3b: User::update() no acepta campo bio ---

it('update() no sobreescribe la columna bio', function () {
    // Primero establecemos una bio conocida
    (new User)->updateBio($this->userId, 'bio intacta');

    // Llamamos a update() con bio en el payload
    $request = new \Illuminate\Http\Request();
    $request->replace([
        'id'        => (string) $this->userId,
        'full_name' => 'Nombre Nuevo',
        'username'  => $this->username,
        'email'     => $this->email,
        'bio'       => 'intento sobrescribir via update()',
    ]);

    (new User)->update($request);

    // La bio en DB debe permanecer igual
    $row = $this->db->query(
        "SELECT bio FROM users WHERE id = {$this->userId}"
    )->fetch_assoc();

    expect($row['bio'])->toBe('bio intacta');
});
