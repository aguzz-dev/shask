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

    /** Lista liviana (id + title) para marcar presets publicados en el back office. */
    public function all(): array
    {
        return $this->query("SELECT id, title FROM {$this->table} ORDER BY id DESC")
            ->fetch_all(MYSQLI_ASSOC);
    }

    public function deleteById($id): void
    {
        $id = (int) $id;
        $this->query("DELETE FROM {$this->table} WHERE id = {$id}");
    }
}
