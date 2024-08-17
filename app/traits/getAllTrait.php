<?php 

namespace App\Traits;

trait GetAllTrait
{
    public function getAll()
    {
        return $this->query("SELECT * FROM {$this->table}")->fetch_all(MYSQLI_ASSOC);
    }
}