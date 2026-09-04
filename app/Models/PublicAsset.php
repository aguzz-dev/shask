<?php

namespace App\Models;

use App\Database;

class PublicAsset extends Database
{
    protected $table = 'public_assets';


    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }

    /** Lista liviana (id + title + is_premium) para el back office. */
    public function all(): array
    {
        return $this->query("SELECT id, title, is_premium FROM {$this->table} ORDER BY id DESC")
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteById($id): void
    {
        $id = (int) $id;
        $this->query("DELETE FROM {$this->table} WHERE id = {$id}");
    }

    /** Marca un diseño como premium (paga con ad) o gratis. Admin-only. */
    public function setPremium(int $id, bool $premium): void
    {
        $stmt = $this->dbConnection->prepare(
            "UPDATE {$this->table} SET is_premium = ? WHERE id = ?"
        );
        $value = $premium ? 1 : 0;
        $stmt->bind_param('ii', $value, $id);
        $stmt->execute();
        $stmt->close();
    }
}
