<?php

namespace  App\Models;

use App\Database;

class PersonalAccessToken extends Database
{
    protected $table = 'personal_access_tokens';

    public function findById($id)
    {
        return $this->find($id);
    }

    public function getTokenById($id)
    {
        $userId = is_array($id) ? implode($id) : $id;
        $token = $this->query("SELECT `token` FROM {$this->table} WHERE user_id = '{$userId}' ORDER BY 'id' DESC LIMIT 1;")->fetch_row();
        return  json_encode($token);
    }

    public function validateToken($token)
    {
        $existToken = $this->query("SELECT * FROM {$this->table} WHERE `token` = '{$token}'")->fetch_all(MYSQLI_ASSOC);
        if(!$existToken){
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => 'Token invalido'
            ]);
            exit;
        }
        http_response_code(200);
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
