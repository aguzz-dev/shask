<?php

namespace App\Models;

use App\Database;

class Blacklist extends Database
{
    protected $table = 'blacklist_user';

    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }

    public function findByIp($ip)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE ip = '{$ip}'")->fetch_all(MYSQLI_ASSOC);
    }

}
