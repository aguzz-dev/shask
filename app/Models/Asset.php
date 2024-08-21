<?php

namespace App\Models;

use App\Database;
use App\Traits\FindTrait;

class Asset extends Database
{
    protected $table = 'assets';


    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }

    public function getAllAssets()
    {
        return $this->query("SELECT * FROM {$this->table}")->fetch_all(MYSQLI_ASSOC);
    }

    public function getUserAssetsByUserId($id)
    {
        return $this->query(
                "SELECT a.*
                FROM assets a
                INNER JOIN asset_user ua ON a.id = ua.asset_id
                WHERE ua.user_id = '{$id}'"
                )->fetch_all(MYSQLI_ASSOC);
    }
}
