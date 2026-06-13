<?php
namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;

class CreateAdminUser extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Crea un usuario del back office admin';

    public function handle(): int
    {
        $name = (string) $this->ask('Nombre');
        $email = (string) $this->ask('Email');
        $password = (string) $this->secret('Password (mín. 10 caracteres)');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('Email inválido.');
            return self::FAILURE;
        }
        if (strlen($password) < 10) {
            $this->error('El password debe tener al menos 10 caracteres.');
            return self::FAILURE;
        }
        $model = new AdminUser;
        if ($model->findByEmail($email)) {
            $this->error('Ya existe un admin con ese email.');
            return self::FAILURE;
        }

        $id = $model->create($name, $email, $password);
        $this->info("Admin #{$id} creado.");
        return self::SUCCESS;
    }
}
