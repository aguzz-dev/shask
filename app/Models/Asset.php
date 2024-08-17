<?php

namespace App\Models;

use App\Database;
use App\Traits\FindTrait;
use App\Traits\getAllTrait;

class Asset extends Database
{
    protected $table = 'assets';

    use getAllTrait;
    use FindTrait;

    public function findById($id)
    {
        return $this->find($id);
    }

    public function getAllAssets()
    {
        return $this->getAll();
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
