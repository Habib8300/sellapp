<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = config('initial-admin.email');
        $password = config('initial-admin.password');

        if (blank($email) && blank($password)) {
            return;
        }

        if (blank($email) || blank($password)) {
            throw new RuntimeException('Set both SUPER_ADMIN_EMAIL and SUPER_ADMIN_PASSWORD to create the initial admin.');
        }

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => config('initial-admin.name'),
                'password' => $password,
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles(['super_admin']);
    }
}
