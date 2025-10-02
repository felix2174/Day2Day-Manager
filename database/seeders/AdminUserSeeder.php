<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('👤 Erstelle Admin-User...');
        
        $admin = User::updateOrCreate(
            ['email' => 'admin@enodia.de'],
            [
                'name' => 'Admin',
                'password' => bcrypt('Test1234'),
            ]
        );
        
        $this->command->info('✅ Admin-User erstellt: ' . $admin->email);
        $this->command->info('🔑 Passwort: Test1234');
    }
}
