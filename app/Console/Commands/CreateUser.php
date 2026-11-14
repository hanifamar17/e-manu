<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CreateUser extends Command
{
    protected $signature = 'emanu:user {username} {--nama=} {--role=admin}';

    protected $description = 'Buat atau perbarui akun (password diminta interaktif)';

    public function handle(): int
    {
        $password = (string) $this->secret('Password (min 8 karakter)');
        if (strlen($password) < 8) {
            $this->error('Password minimal 8 karakter.');

            return self::FAILURE;
        }

        $username = trim($this->argument('username'));
        User::updateOrCreate(
            ['username' => $username],
            ['nama' => $this->option('nama') ?: $username, 'role' => $this->option('role'), 'password' => $password],
        );
        $this->info("Akun '{$username}' tersimpan.");

        return self::SUCCESS;
    }
}
