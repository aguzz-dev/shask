<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Mail\ResetPasswordCodeMail;
use App\Mail\VerificationCodeMail;
use Exception;
use App\Database;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Mail;

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

    public function findByMail($mail)
    {
        return $this->query("SELECT * FROM {$this->table} WHERE email = '{$mail}'")->fetch_all(MYSQLI_ASSOC);
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

    public function googleRegister($userData)
    {
        $token = $this->generateToken();
        $sql = "INSERT INTO {$this->table}
                (`full_name`,
                `username`,
                `email`,
                `age`)
                VALUES (
                    '{$userData['full_name']}',
                    '{$userData['username']}',
                    '{$userData['email']}',
                    '{$userData['age']}'
                )";
        $this->query($sql);

        $userId = $this->dbConnection->insert_id;
        $this->query("INSERT INTO `personal_access_tokens` (`token`, `user_id`) VALUES ('{$token}', '{$userId}')");

        $userData = $this->findById($userId)[0];
        $userData['notificaciones_activadas'] = empty($userData['fcm_token']) ? false : true;
        return [
            'token' => $token,
            'user' => $userData
        ];
    }

    public function getFcmByUsername($username)
    {
        return $this->query("SELECT `fcm_token` FROM {$this->table} WHERE username = '{$username}'")->fetch_assoc();
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
            'avatar' => $user['avatar'],
            'notificaciones_activadas' => empty($user['fcm_token'])? false : true
        ];
        return [
            'token' => $token,
            'user' => $userData
        ];
    }

    public function update($request)
    {
        $User = $this->findById($request->id);
        if(!$User){
            throw new Exception('Usuario no encontrado', 404);
        }

        if (isset($request->username)) {
            $existUsername = $this->query("SELECT * FROM `users` WHERE `username` = '{$request->username}' AND id != '{$request->id}'")->fetch_assoc();
            if ($existUsername) {
                throw new Exception('El nombre de usuario ya está en uso', 422);
            }
        }

        if (isset($request->email)) {
            $existEmail = $this->query("SELECT * FROM `users` WHERE `email` = '{$request->email}' AND id != '{$request->id}'")->fetch_assoc();
            if ($existEmail) {
                throw new Exception('El correo electrónico ya está en uso', 422);
            }
        }

        $fields = [];
        foreach ($request->all() as $key => $value) {
            $fields[] = "{$key} = '{$value}'";
        }

        $fields = implode(', ', $fields);

        $sql = "UPDATE {$this->table} SET {$fields} WHERE id = {$request->id}";
        $this->query($sql);
        $userUpdated = $this->findById($request->id)[0];
        return [
            'id' => $userUpdated['id'],
            'full_name' => $userUpdated['full_name'],
            'username' => $userUpdated['username'],
            'email' => $userUpdated['email'],
            'age' => $userUpdated['age'],
            'notificaciones_activadas' => empty($userUpdated['fcm_token']) ? false : true
        ];
    }

    public function checkUsername($username)
    {
        $user = $this->query("SELECT * FROM `users` WHERE `username` = '{$username}'")->fetch_assoc();
        if ($user) {
            return false;
        }
        return true;
    }

    public function checkEmail($email)
    {
        $user = $this->query("SELECT * FROM `users` WHERE `email` = '{$email}'")->fetch_assoc();
        if ($user) {
            return false;
        }
        return true;
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

        $randomUser = '@user' . substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 4);

        $this->query("INSERT INTO `blacklist_user` (`user_id`, `ip`, `random_user`) VALUES ('{$userId}', '{$question[0]['ip']}', '{$randomUser}')");
    }

    public function desblockUser($userId, $randomUser)
    {
        $user = $this->findById($userId);
        if(!$user){
            throw new Exception('Usuario no encontrado', 404);
        }

        $isExistRandomUser = $this->query("SELECT * FROM `blacklist_user` WHERE `random_user` = '{$randomUser}'")->fetch_assoc();
        if (!$isExistRandomUser) {
            throw new Exception('Usuario random no encontrado en la lista negra', 404);
        }
        $this->query("DELETE FROM `blacklist_user` WHERE `user_id` = '{$userId}' AND `random_user` = '{$randomUser}'");
    }

    public function getUserBlockedList($userId)
    {
        $user = $this->findById($userId);
        if(!$user){
            throw new Exception('Usuario no encontrado', 404);
        }

        return $this->query("SELECT random_user FROM `blacklist_user` WHERE `user_id` = '{$userId}'")->fetch_all();
    }

    public function getFcm($id)
    {
        return $this->query("SELECT fcm FROM {$this->table} WHERE id = '{$id}'")->fetch_assoc();
    }

    public function saveFcm($request)
    {
        $isUserExist = $this->findById($request->id);
        if (!$isUserExist){
            throw new Exception('Usuario no encontrado', 404);
        }
        return $this->query("UPDATE `users` SET `fcm_token` = '{$request->fcm}' WHERE id = '{$request->id}'");
    }

    public function desactivarFcm($id)
    {
        $isUserExist = $this->findById($id);
        if (!$isUserExist){
            throw new Exception('Usuario no encontrado', 404);
        }
        return $this->query("UPDATE `users` SET `fcm_token` = NULL WHERE id = '{$id}'");
    }

    public function generateCode($request)
    {
        $user = $this->findByMail($request->email)[0];
        if (!$user){
            throw new Exception('Usuario no encontrado', 404);
        }

        $code = rand(100, 999);

        $this->query("UPDATE users SET code = {$code} WHERE id = {$user['id']}");

        Mail::to($user['email'])->send(new VerificationCodeMail($code));
    }

    public function verifyCodeAndActivateUser($request)
    {
        $user = $this->findByMail($request->email)[0];
        if ($user['status'] == 1){
            throw new Exception('El usuario ya se encuentra verificado', 400);
        }
        if ($user['code'] != $request->code){
            throw new Exception('Código inválido', 422);
        }

        $this->query("UPDATE users SET status = 1 WHERE id = {$user['id']}");

        return $user;
    }

    public function generateResetPasswordCode($request)
    {
        $user = $this->findByMail($request->email)[0];
        if (!$user){
            throw new Exception('Usuario no encontrado', 404);
        }

        $code = rand(100, 999);

        $this->query("UPDATE users SET code = {$code} WHERE id = {$user['id']}");

        Mail::to($user['email'])->send(new ResetPasswordCodeMail($code));
    }

    public function verifyResetPasswordCode($request)
    {
        $user = $this->findByMail($request->email)[0];

        if ($user['code'] != $request->code){
            throw new Exception('Código inválido', 422);
        }
    }

    public function resetPassword($request)
    {
        $user = $this->findByMail($request->email)[0];

        if ($user['code'] != $request->code){
            throw new Exception('Código inválido', 422);
        }

        $password = password_hash($request->password, PASSWORD_DEFAULT);

        $this->query("UPDATE `users` SET `password` = '{$password}' WHERE id = '{$request->id}'");

        return [
            'id' => $user['id'],
            'full_name' => $user['full_name'],
            'user' => $user['username'],
            'email' => $user['email'],
        ];
    }
}
