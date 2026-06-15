<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => config('app.admin_email', 'admin@vitrine.local')],
            [
                'name'              => 'Admin',
                'password'          => Hash::make(config('app.admin_password', 'changeme')),
                'role'              => 'admin',
                'email_verified_at' => now(),
            ]
        );
    }
}
