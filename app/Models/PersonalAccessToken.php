<?php

namespace  App\Models;

use App\Database;

class PersonalAccessToken extends Database
{
    protected $table = 'personal_access_tokens';

    public function generateToken($userId)
    {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < 150; $i++) {
            $token .= $chars[rand(0, 61)];
        }

        $this->query("DELETE FROM {$this->table} WHERE user_id = '{$userId}'");

        $this->query("INSERT INTO {$this->table} (token, user_id) VALUES ('{$token}', '{$userId}')");

        return $token;
    }


    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }

    public function getTokenById($id)
    {
        $userId = is_array($id) ? implode($id) : $id;
        $token = $this->query("SELECT `token` FROM {$this->table} WHERE user_id = '{$userId}' ORDER BY 'id' DESC LIMIT 1;")->fetch_row();
        return  json_encode($token);
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
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token invalido'
            ]);
            exit;
        }
        $id = implode($idByToken[0]);
        return $id;
    }

    public function destroyToken($userId)
    {
        $this->query("DELETE FROM `personal_access_tokens` WHERE `user_id` = '{$userId}'");
    }
}
