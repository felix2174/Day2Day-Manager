<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@enodia.de',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Marc Hanke',
            'email' => 'it@enodia.de',
            'password' => Hash::make('password123'),
        ]);

        User::create([
            'name' => 'Jörg Michno',
            'email' => 'j.michno@enodia.de',
            'password' => Hash::make('password123'),
        ]);
    }
}
