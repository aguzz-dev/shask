<?php

namespace  App\Models;

use App\Database;

class PersonalAccessToken extends Database
{
    protected string $table = 'personal_access_tokens';

    public function generateToken($userId): string
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < 150; $i++) {
            $token .= $chars[rand(0, 61)];
        }

        $this->query("INSERT INTO {$this->table} (token, user_id) VALUES ('{$token}', '{$userId}')");

        return $token;
    }

    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }

    public function validateToken($token, $userId)
    {
        $existToken = $this->query("SELECT * FROM {$this->table} WHERE token = '{$token}' AND user_id = '{$userId}'")->fetch_all(MYSQLI_ASSOC);

        if (empty($existToken)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalido'
            ], 401);
        }
        return true;
    }

    public function getIdByToken($token)
    {
        $idByToken = $this->query("SELECT user_id FROM {$this->table} WHERE `token` = '{$token}'")->fetch_all(MYSQLI_ASSOC);
        if(empty($idByToken)){
            return response()->json([
                'status' => 'error',
                'message' => 'Token invalido'
            ], 401);
        }
        return implode($idByToken[0]);
    }

    public function destroyToken($userId)
    {
        $this->query("DELETE FROM `personal_access_tokens` WHERE `user_id` = '{$userId}'");
    }
}
