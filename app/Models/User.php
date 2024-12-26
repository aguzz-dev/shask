<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Exception;
use App\Database;
use App\Helpers\GenerateToken;
use App\Middleware\VerifyToken;
use App\Models\PersonalAccessToken;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Database
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function findById($id)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE id = {$id}")->fetch_all(MYSQLI_ASSOC);
    }

    public function store($request)
    {
        $sql = "INSERT INTO {$this->table}
                (`full_name`,
                `username`,
                `email`,
                `password`,
                `age`)
                VALUES (
                    '{$request->full_name}',
                    '{$request->username}',
                    '{$request->email}',
                    '" . password_hash($request->password, PASSWORD_DEFAULT) . "',
                    '{$request->age}'
                )";

        try {
            $this->query($sql);
            $idUser = $this->dbConnection->insert_id;
            $userData = $this->query("SELECT id, full_name, username, email, age FROM {$this->table} WHERE id = {$idUser}")->fetch_all(MYSQLI_ASSOC);
            return $userData;
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 422);
        }
    }

    public function login($request)
    {
        $email = $request->email;
        $password = $request->password;

        $user = $this->query("SELECT * FROM {$this->table} WHERE email = '{$email}' LIMIT 1")->fetch_assoc();
        if (is_null($user)) {
            throw new Exception('Usuario no encontrado', 404);
        }
        if (!password_verify($password, $user['password'])) {
            throw new Exception('Credenciales incorrectas', 422);
        }
        $token = $this->generateToken();
        (new PersonalAccessToken)->destroyToken($user['id']);
        $this->query("INSERT INTO `personal_access_tokens` (`token`, `user_id`) VALUES ('{$token}', '{$user['id']}')");
        $userData = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'username' => $user['username'],
            'email' => $user['email'],
            'avatar' => $user['avatar']
        ];
        return [
            'token' => $token,
            'user' => $userData
        ];
    }

    public function update($request)
    {
        $User = $this->findById($request['id']);
        if(!$User){
            throw new Exception('Usuario no encontrado', 404);
        }
        $fields = [];
        foreach ($request as $key => $value) {
            $fields[] = "{$key} = '{$value}'";
        }
        $fields = implode(', ', $fields);
        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = {$request['id']}";
        $this->query($sql);
        return $this->findById($request['id']);
    }

    public function updateAvatar($id,$avatarJson)
    {
        $User = $this->findById($id);
        if(!$User){
            throw new Exception('Usuario no encontrado', 404);
        }
        $this->query("UPDATE `users` SET `avatar` = '{$avatarJson}' WHERE id = '{$id}'");
    }

    public function destroy($id)
    {
        $user = $this->findById($id);
        if(!$user){
            throw new Exception('Usuario no encontrado', 404);
        }
        $sql = "DELETE FROM {$this->table} WHERE id = {$id}";
        $this->query($sql);
    }

    public function changePassword($request)
    {
        (new PersonalAccessToken)->validateToken(str_replace('Bearer ', '', (string)$_SERVER['HTTP_AUTHORIZATION']));
        $password = password_hash($request->password, PASSWORD_DEFAULT);
        $isUserExist = $this->findById($request->id);
        if (!$isUserExist){
            throw new Exception('Usuario no encontrado', 404);
        }
        $this->query("UPDATE `users` SET `password` = '{$password}' WHERE id = '{$request->id}'");
    }

    public static function generateToken() {
        $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $token = '';
        for ($i = 0; $i < 250; $i++) {
            $token .= $chars[rand(0, 61)];
        }
        return $token;
    }

    public function blockUser($userId, $questionId)
    {
        $user = $this->findById($userId);
        if(!$user){
            throw new Exception('Usuario no encontrado', 404);
        }

        $question = (new Question)->findById($questionId);
        if(!$question){
            throw new Exception('Pregunta no encontrada', 404);
        }

        $this->query("INSERT INTO `blacklist_user` (`user_id`, `ip`) VALUES ('{$userId}', '{$question[0]['ip']}')");
    }

    public function getFcm($id)
    {
        return $this->query("SELECT fcm FROM {$this->table} WHERE id = '{$id}'")->fetch_assoc();
    }

    public function saveFcm($request)
    {
        return $this->query("UPDATE `users` SET `fcm_token` = '{$request->fcm}' WHERE id = '{$request->id}'");
    }
}
