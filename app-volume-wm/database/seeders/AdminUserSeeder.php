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
            ['id' => 1],
            [
                'name' => 'Admin',
                'email' => 'admin@localhost',
                'password' => Hash::make('ChangeMe'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}
