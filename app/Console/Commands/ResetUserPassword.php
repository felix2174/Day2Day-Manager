<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ResetUserPassword extends Command
{
    protected $signature = 'user:reset-password 
                            {email : Die E-Mail-Adresse des Users}
                            {--password=password : Das neue Passwort}';
    
    protected $description = 'Setzt das Passwort für einen bestehenden User zurück';

    public function handle()
    {
        $email = $this->argument('email');
        $password = $this->option('password');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("❌ User mit Email '{$email}' nicht gefunden!");
            $this->newLine();
            
            $this->comment('Verfügbare User:');
            foreach (User::take(5)->get() as $u) {
                $this->line("  - {$u->email} ({$u->name})");
            }
            
            return 1;
        }

        $user->password = Hash::make($password);
        $user->save();

        $this->info('✅ Passwort erfolgreich zurückgesetzt!');
        $this->newLine();
        
        $this->table(
            ['Feld', 'Wert'],
            [
                ['Name', $user->name],
                ['Email', $user->email],
                ['Neues Passwort', $password],
                ['Login-URL', 'http://127.0.0.1:8000/login'],
            ]
        );

        return 0;
    }
}
