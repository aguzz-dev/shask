<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Exception;
use App\Database;
use App\Helpers\GenerateToken;
use App\Middleware\VerifyToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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
        return $this->find($id);
    }

    public function store($request)
    {
        $sql = "INSERT INTO {$this->table}
                (`full_name`,
                `username`,
                `email`,
                `password`)
                VALUES (
                    '{$request->full_name}',
                    '{$request->username}',
                    '{$request->email}',
                    '" . password_hash($request->password, PASSWORD_DEFAULT) . "'
                )";

        $this->query($sql);
        $idUser = $this->dbConnection->insert_id;
        $userData = $this->query("SELECT id, full_name, username, email FROM {$this->table} WHERE id = {$idUser}")->fetch_all(MYSQLI_ASSOC);
        return $userData;
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
        $token = GenerateToken::auth();
        (new PersonalAccessToken)->destroyToken($user['id']);
        $this->query("INSERT INTO `personal_access_tokens` (`token`, `user_id`) VALUES ('{$token}', '{$user['id']}')");
        $userData = [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'username' => $user['username'],
            'email' => $user['email']
        ];
        return [
            'token' => $token,
            'user' => $userData
        ];
    }

    public function update($request)
    {
        $User = $this->find($request['id']);
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
        return $this->find($request['id']);
    }

    public function destroy($id)
    {
        $user = $this->find($id);
        if(!$user){
            throw new Exception('Usuario no encontrado', 404);
        }
        $sql = "DELETE FROM {$this->table} WHERE id = {$id}";
        $this->query($sql);
    }

    public function changePassword($request)
    {
        VerifyToken::jwt();
        $password = password_hash($request->password, PASSWORD_DEFAULT);
        $isUserExist = $this->find($request->id);
        if (!$isUserExist){
            throw new Exception('Usuario no encontrado', 404);
        }
        $this->query("UPDATE `users` SET `password` = '{$password}' WHERE id = '{$request->id}'");
    }

}
