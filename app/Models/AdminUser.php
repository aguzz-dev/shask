<?php
namespace App\Models;

use App\Database;

class AdminUser extends Database
{
    protected $table = 'admin_users';

    public function create(string $name, string $email, string $plainPassword): int
    {
        $name = $this->dbConnection->real_escape_string($name);
        $email = $this->dbConnection->real_escape_string($email);
        $hash = password_hash($plainPassword, PASSWORD_DEFAULT);
        $this->query(
            "INSERT INTO {$this->table} (name, email, password, created_at, updated_at)
             VALUES ('{$name}', '{$email}', '{$hash}', NOW(), NOW())"
        );
        return (int) $this->dbConnection->insert_id;
    }

    public function findByEmail(string $email): ?array
    {
        $email = $this->dbConnection->real_escape_string($email);
        $rows = $this->query(
            "SELECT * FROM {$this->table} WHERE email = '{$email}'"
        )->fetch_all(MYSQLI_ASSOC);
        return $rows[0] ?? null;
    }

    public function findById(int $id): ?array
    {
        $rows = $this->query(
            "SELECT * FROM {$this->table} WHERE id = {$id}"
        )->fetch_all(MYSQLI_ASSOC);
        return $rows[0] ?? null;
    }

    /** Devuelve el admin si email+password son válidos y está activo. */
    public function verifyCredentials(string $email, string $plainPassword): ?array
    {
        $admin = $this->findByEmail($email);
        if (!$admin || (int) $admin['active'] !== 1) {
            return null;
        }
        if (!password_verify($plainPassword, $admin['password'])) {
            return null;
        }
        $admin['id'] = (int) $admin['id'];
        return $admin;
    }

    public function touchLastLogin(int $id): void
    {
        $this->query("UPDATE {$this->table} SET last_login_at = NOW() WHERE id = {$id}");
    }
}
