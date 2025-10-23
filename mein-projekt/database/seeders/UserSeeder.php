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
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Marc Hanke',
            'email' => 'it@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Jörg Michno',
            'email' => 'j.michno@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        // Zusätzliche Benutzer für verschiedene Rollen
        User::create([
            'name' => 'Thomas Müller',
            'email' => 'thomas.mueller@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Sarah Weber',
            'email' => 'sarah.weber@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Anna Fischer',
            'email' => 'anna.fischer@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Michael Schmidt',
            'email' => 'michael.schmidt@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Lisa Wagner',
            'email' => 'lisa.wagner@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Andreas Becker',
            'email' => 'andreas.becker@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Julia Richter',
            'email' => 'julia.richter@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Stefan Hoffmann',
            'email' => 'stefan.hoffmann@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Nadine Koch',
            'email' => 'nadine.koch@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'David Klein',
            'email' => 'david.klein@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        User::create([
            'name' => 'Petra Schulz',
            'email' => 'petra.schulz@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        // Projektmanager
        User::create([
            'name' => 'Dr. Maria Schmidt',
            'email' => 'maria.schmidt@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        // Geschäftsführung
        User::create([
            'name' => 'Robert Weber',
            'email' => 'robert.weber@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);

        // HR
        User::create([
            'name' => 'Sabine Müller',
            'email' => 'sabine.mueller@enodia.de',
            'password' => Hash::make('Test1234'),
        ]);
    }
}
