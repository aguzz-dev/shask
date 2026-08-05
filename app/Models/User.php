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

    /** Usernames reservados para cuentas de marca — nadie puede registrarlos ni cambiarse a ellos. */
    private const RESERVED_USERNAMES = ['shhask'];

    private static function isReservedUsername(string $username): bool
    {
        return in_array(strtolower($username), self::RESERVED_USERNAMES, true);
    }

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
        $age = ($request->age !== null && $request->age !== '') ? (int)$request->age : 'NULL';

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
                    {$age}
                )";

        try {
            $this->query($sql);
            $idUser = $this->dbConnection->insert_id;
            return $this->query("SELECT id, full_name, username, email, age FROM {$this->table} WHERE id = {$idUser}")->fetch_all(MYSQLI_ASSOC);
        } catch (\Throwable $th) {
            throw new Exception($th->getMessage(), 422);
        }
    }

    public function googleRegister($userData)
    {
        $age = ($userData['age'] !== null && $userData['age'] !== '') ? (int)$userData['age'] : 'NULL';

        $sql = "INSERT INTO {$this->table}
                (`full_name`,
                `password`,
                `username`,
                `email`,
                `age`)
                VALUES (
                    '{$userData['full_name']}',
                    'google-register',
                    '{$userData['username']}',
                    '{$userData['email']}',
                    {$age}
                )";

        $this->query($sql);

        $userId = $this->dbConnection->insert_id;
        $token = (new PersonalAccessToken())->generateToken($userId);

        $userData = $this->findById($userId)[0];
        $userData['notificaciones_activadas'] = !empty($userData['fcm_token']);

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
        $token = (new PersonalAccessToken)->generateToken($user['id']);

        $userData = [
            'id'                      => $user['id'],
            'full_name'               => $user['full_name'],
            'username'                => $user['username'],
            'email'                   => $user['email'],
            'avatar'                  => $user['avatar'],
            'hype'                    => $user['hype'],
            'bio'                     => $user['bio'] ?? null,
            'notificaciones_activadas' => empty($user['fcm_token']) ? false : true,
        ];

        return [
            'token' => $token,
            'user' => $userData
        ];
    }

    /**
     * Actualiza la bio del usuario usando prepared statement.
     * Lanza Exception(422) si la bio supera los 200 caracteres.
     * Nunca usa el SET dinámico de update() para evitar SQL injection.
     */
    public function updateBio(int $userId, string $bio): void
    {
        if (mb_strlen($bio) > 200) {
            throw new Exception('Bio demasiado larga (máximo 200 caracteres)', 422);
        }
        $stmt = $this->dbConnection->prepare('UPDATE users SET bio = ? WHERE id = ?');
        $stmt->bind_param('si', $bio, $userId);
        $stmt->execute();
        $stmt->close();
    }

    public function update($request)
    {
        $User = $this->findById($request->id);
        if(!$User){
            throw new Exception('Usuario no encontrado', 404);
        }

        $requestId = $request->id;

        if (isset($request->username)) {
            $usernameInput = $request->username;
            if (self::isReservedUsername($usernameInput)) {
                throw new Exception('El nombre de usuario ya está en uso', 422);
            }
            $stmt = $this->dbConnection->prepare(
                'SELECT * FROM `users` WHERE `username` = ? AND id != ?'
            );
            $stmt->bind_param('si', $usernameInput, $requestId);
            $stmt->execute();
            $existUsername = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($existUsername) {
                throw new Exception('El nombre de usuario ya está en uso', 422);
            }
        }

        if (isset($request->email)) {
            $emailInput = $request->email;
            $stmt = $this->dbConnection->prepare(
                'SELECT * FROM `users` WHERE `email` = ? AND id != ?'
            );
            $stmt->bind_param('si', $emailInput, $requestId);
            $stmt->execute();
            $existEmail = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            if ($existEmail) {
                throw new Exception('El correo electrónico ya está en uso', 422);
            }
        }

        // 'bio' se excluye del SET dinámico (se actualiza solo via updateBio()).
        $excluded = ['bio'];
        $setClauses = [];
        $values = [];
        $types = '';
        foreach ($request->all() as $key => $value) {
            if (in_array($key, $excluded)) {
                continue;
            }
            // Solo identificadores SQL válidos como nombre de columna — los
            // placeholders de mysqli no cubren identificadores, así que esto
            // es lo único que evita inyección vía una clave manipulada del body.
            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', (string) $key)) {
                continue;
            }
            $setClauses[] = "`{$key}` = ?";
            $values[]     = $value;
            $types       .= 's';
        }

        if (!empty($setClauses)) {
            $types   .= 'i';
            $values[] = $request->id;

            $sql  = "UPDATE {$this->table} SET " . implode(', ', $setClauses) . ' WHERE id = ?';
            $stmt = $this->dbConnection->prepare($sql);
            $stmt->bind_param($types, ...$values);
            $stmt->execute();
            $stmt->close();
        }

        $userUpdated = $this->findById($request->id)[0];
        return [
            'id'                      => $userUpdated['id'],
            'full_name'               => $userUpdated['full_name'],
            'username'                => $userUpdated['username'],
            'email'                   => $userUpdated['email'],
            'age'                     => $userUpdated['age'],
            'bio'                     => $userUpdated['bio'] ?? null,
            'notificaciones_activadas' => empty($userUpdated['fcm_token']) ? false : true,
        ];
    }

    public function checkUsername($username)
    {
        if (self::isReservedUsername($username)) {
            return false;
        }
        $user = $this->query("SELECT * FROM `users` WHERE `username` = '{$username}'")->fetch_assoc();
        if ($user) {
            return false;
        }
        return true;
    }

    public function checkEmail($email)
    {
        $stmt = $this->connection->prepare("SELECT * FROM `users` WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return false;
        }
        return true;
    }

    /**
     * Actualiza el avatar del usuario usando prepared statement.
     * Nunca interpola $avatarJson directo en el SQL: viene del cliente y
     * antes se pegaba crudo en el UPDATE (SQL injection).
     */
    public function updateAvatar($id, string $avatarJson)
    {
        $User = $this->findById($id);
        if(!$User){
            throw new Exception('Usuario no encontrado', 404);
        }
        $userId = (int) $id;
        $stmt = $this->dbConnection->prepare('UPDATE users SET avatar = ? WHERE id = ?');
        $stmt->bind_param('si', $avatarJson, $userId);
        $stmt->execute();
        $stmt->close();
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
        (new PersonalAccessToken)->validateToken($request->bearerToken(), $request->id);
        $password = password_hash($request->password, PASSWORD_DEFAULT);
        $isUserExist = $this->findById($request->id);
        if (!$isUserExist){
            throw new Exception('Usuario no encontrado', 404);
        }
        $this->query("UPDATE `users` SET `password` = '{$password}' WHERE id = '{$request->id}'");
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

    public function getHypeById($id)
    {
        return $this->query("SELECT hype FROM `users` WHERE `id` = '{$id}'")->fetch_assoc();
    }
}
