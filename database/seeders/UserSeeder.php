<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('LOCAL_ADMIN_EMAIL');
        $password = env('LOCAL_ADMIN_PASSWORD');

        if (! $email || ! $password) {
            if (app()->environment('production')) {
                throw new RuntimeException('LOCAL_ADMIN_EMAIL and LOCAL_ADMIN_PASSWORD must be explicitly set in production.');
            }

            $email = 'admin@localhost.test';
            $password = 'ChangeMe123!';
        }

        $role = Roles::firstOrCreate(['name' => 'Admin']);

        User::updateOrCreate(
            ['email' => $email],
            [
                'role_id' => $role->id,
                'username' => '@admin',
                'name' => env('LOCAL_ADMIN_NAME', 'Local Administrator'),
                'email_verified_at' => now(),
                'password' => Hash::make($password),
            ],
        );
    }
}
