<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateTestUser extends Command
{
    protected $signature = 'user:create-test';
    protected $description = 'Erstellt einen Test-User für Login';

    public function handle()
    {
        $this->info('🔐 Erstelle Test-User...');
        $this->newLine();

        // Prüfe ob User bereits existiert
        $existing = User::where('email', 'admin@day2day.local')->first();
        
        if ($existing) {
            $this->warn('⚠️  User existiert bereits:');
            $this->line("   Email: {$existing->email}");
            $this->line("   Name: {$existing->name}");
            $this->newLine();
            
            if ($this->confirm('Passwort zurücksetzen?', true)) {
                $existing->password = Hash::make('password');
                $existing->save();
                
                $this->info('✅ Passwort zurückgesetzt!');
                $this->newLine();
                $this->showCredentials();
            }
            
            return 0;
        }

        // Erstelle neuen User
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@day2day.local',
            'password' => Hash::make('password'),
        ]);

        $this->info('✅ Test-User erstellt!');
        $this->newLine();
        $this->showCredentials();

        return 0;
    }

    private function showCredentials()
    {
        $this->table(
            ['Feld', 'Wert'],
            [
                ['Email', 'admin@day2day.local'],
                ['Passwort', 'password'],
                ['Login-URL', 'http://127.0.0.1:8000/login'],
            ]
        );
        
        $this->newLine();
        $this->comment('💡 Tipp: Nach Login kannst du alle Bereiche nutzen!');
    }
}
