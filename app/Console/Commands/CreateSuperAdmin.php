<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateSuperAdmin extends Command
{
    protected $signature = 'app:create-super-admin {--name= : Administrator name} {--email= : Administrator email address} {--password= : Administrator password}';

    protected $description = 'Create the first store administrator using an interactive password prompt';

    public function handle(): int
    {
        $name = $this->option('name') ?: $this->ask('Administrator name');
        $email = $this->option('email') ?: $this->ask('Administrator email');
        $password = $this->option('password') ?: $this->secret('Administrator password');
        $passwordConfirmation = $this->option('password') ? $this->option('password') : $this->secret('Confirm administrator password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $admin = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('super_admin');

        $this->components->info('Super Admin created. Sign in at /admin.');

        return self::SUCCESS;
    }
}
