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
}
