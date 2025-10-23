<?php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@enodia.de'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Test1234'), // Geändert von password123
            ]
        );
    }
}