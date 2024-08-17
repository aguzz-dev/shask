<?php 

namespace App\Traits;

trait FindTrait
{
    public function find($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }
}