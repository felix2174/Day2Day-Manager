<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddEmployeeEmailsSeeder extends Seeder
{
    /**
     * Füge E-Mail-Adressen zu bestehenden Employees hinzu
     * 
     * WICHTIG: Lauf BEVOR RolesAndPermissionsSeeder ausgeführt wird!
     * Damit die User-Accounts korrekt erstellt werden können.
     */
    public function run(): void
    {
        echo "📧 Adding email addresses to employees...\n\n";
        
        // E-Mail-Mapping: Name -> E-Mail
        $emails = [
            'Jörg Michno' => 'jm@enodia.de',
            'Marc Hanke' => 'mh@enodia.de',
            'Hannes Boekhoff' => 'hb@enodia.de',
            'Steffen Armgart' => 'sa@enodia.de',
            'Uwe Harnischmacher' => 'uh@enodia.de',
            'Tim Hoffmann' => 'th@enodia.de',
            'Mathias Liecker' => 'ml@enodia.de',
            'Wladislav Miller' => 'wm@enodia.de',
            'Antje Nordmeyer' => 'an@enodia.de',
            'Björn Pippig' => 'bp@enodia.de',
            // Buchhaltung = kein Login
            // Test Lösch-Test = kein Login
        ];
        
        $updated = 0;
        $skipped = 0;
        
        foreach ($emails as $name => $email) {
            // Suche Employee (first_name + last_name kombiniert)
            $parts = explode(' ', $name, 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
            
            $employee = DB::table('employees')
                ->where('first_name', $firstName)
                ->where('last_name', $lastName)
                ->first();
                
            if ($employee) {
                DB::table('employees')
                    ->where('id', $employee->id)
                    ->update(['email' => $email]);
                    
                echo "  ✅ {$name} -> {$email}\n";
                $updated++;
            } else {
                echo "  ⚠️  {$name} nicht gefunden\n";
                $skipped++;
            }
        }
        
        echo "\n✅ Email update completed!\n";
        echo "  Updated: {$updated}\n";
        echo "  Skipped: {$skipped}\n";
    }
}
