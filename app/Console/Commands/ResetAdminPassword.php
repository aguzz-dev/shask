<?php
namespace App\Console\Commands;

use App\Models\AdminUser;
use Illuminate\Console\Command;

class ResetAdminPassword extends Command
{
    protected $signature = 'admin:password';
    protected $description = 'Resetea el password de un admin existente del back office';

    public function handle(): int
    {
        $email = (string) $this->ask('Email');
        $password = (string) $this->secret('Password nuevo (mín. 10 caracteres)');

        if (strlen($password) < 10) {
            $this->error('El password debe tener al menos 10 caracteres.');
            return self::FAILURE;
        }

        $model = new AdminUser;
        $admin = $model->findByEmail($email);
        if (!$admin) {
            $this->error('No existe un admin con ese email.');
            return self::FAILURE;
        }

        $model->setPassword((int) $admin['id'], $password);
        $this->info("Password actualizado para admin #{$admin['id']} ({$email}).");
        return self::SUCCESS;
    }
}
