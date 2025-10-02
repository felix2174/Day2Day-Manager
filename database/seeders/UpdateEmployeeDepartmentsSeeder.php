<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\MocoService;
use App\Models\Employee;
use Exception;

class UpdateEmployeeDepartmentsSeeder extends Seeder
{
    protected $mocoService;

    public function __construct()
    {
        $this->mocoService = new MocoService();
    }

    public function run(): void
    {
        $this->command->info('🔍 Analysiere MOCO-Daten für Abteilungs-Zuordnung...');

        try {
            // MOCO Benutzer abrufen
            $mocoUsers = $this->mocoService->getUsers();
            $this->command->info('📊 ' . count($mocoUsers) . ' Benutzer aus MOCO gefunden');

            $updatedEmployees = 0;
            $departmentMapping = [];

            foreach ($mocoUsers as $mocoUser) {
                try {
                    // Finde entsprechenden Mitarbeiter
                    $employee = Employee::where('moco_id', $mocoUser['id'])->first();
                    
                    if (!$employee) {
                        $this->command->warn("⚠️ Kein lokaler Mitarbeiter für MOCO ID {$mocoUser['id']} gefunden");
                        continue;
                    }

                    // Analysiere Abteilungs-Informationen
                    $department = $this->determineDepartment($mocoUser);
                    
                    if ($department) {
                        $employee->update(['department' => $department]);
                        $updatedEmployees++;
                        
                        // Sammle Statistiken
                        if (!isset($departmentMapping[$department])) {
                            $departmentMapping[$department] = 0;
                        }
                        $departmentMapping[$department]++;
                        
                        $this->command->info("✅ {$employee->first_name} {$employee->last_name} → {$department}");
                    } else {
                        $this->command->warn("⚠️ Keine Abteilung für {$employee->first_name} {$employee->last_name} ermittelt");
                    }

                } catch (Exception $e) {
                    $this->command->warn("⚠️ Fehler beim Update von Mitarbeiter {$mocoUser['id']}: " . $e->getMessage());
                }
            }

            // Zusammenfassung
            $this->command->info('📊 Abteilungs-Zuordnung abgeschlossen:');
            $this->command->info("   👥 Aktualisierte Mitarbeiter: {$updatedEmployees}");
            $this->command->info("   📋 Abteilungs-Verteilung:");
            
            foreach ($departmentMapping as $department => $count) {
                $this->command->info("      {$department}: {$count} Mitarbeiter");
            }

        } catch (Exception $e) {
            $this->command->error('❌ Fehler bei der Abteilungs-Analyse: ' . $e->getMessage());
        }
    }

    /**
     * Bestimme die Abteilung basierend auf MOCO-Daten
     */
    private function determineDepartment($mocoUser): ?string
    {
        // 1. Direkte Abteilungs-Information
        if (isset($mocoUser['department']) && !empty($mocoUser['department'])) {
            return $this->normalizeDepartment($mocoUser['department']);
        }

        // 2. Firmen-Information als Abteilung verwenden
        if (isset($mocoUser['company']) && !empty($mocoUser['company'])) {
            return $this->normalizeDepartment($mocoUser['company']);
        }

        // 3. Rollen-basierte Zuordnung
        if (isset($mocoUser['role']) && !empty($mocoUser['role'])) {
            return $this->mapRoleToDepartment($mocoUser['role']);
        }

        // 4. Name-basierte Zuordnung (Fallback)
        $firstName = $mocoUser['firstname'] ?? '';
        $lastName = $mocoUser['lastname'] ?? '';
        
        return $this->mapNameToDepartment($firstName, $lastName);
    }

    /**
     * Normalisiere Abteilungsnamen
     */
    private function normalizeDepartment($department): string
    {
        $department = trim($department);
        
        // Standard-Abteilungen
        $standardDepartments = [
            'IT' => ['IT', 'Information Technology', 'Informatik', 'Software', 'Development', 'Entwicklung'],
            'Management' => ['Management', 'Führung', 'Leitung', 'Geschäftsführung'],
            'Design' => ['Design', 'UI/UX', 'Grafik', 'Kreativ'],
            'Support' => ['Support', 'Kundenservice', 'Service'],
            'Marketing' => ['Marketing', 'Vertrieb', 'Sales'],
            'HR' => ['HR', 'Personal', 'Human Resources'],
            'Finance' => ['Finance', 'Buchhaltung', 'Accounting', 'Finanzen']
        ];

        foreach ($standardDepartments as $standard => $variations) {
            foreach ($variations as $variation) {
                if (stripos($department, $variation) !== false) {
                    return $standard;
                }
            }
        }

        // Wenn keine Standard-Abteilung gefunden, verwende den ursprünglichen Namen
        return ucfirst(strtolower($department));
    }

    /**
     * Mappe Rollen zu Abteilungen
     */
    private function mapRoleToDepartment($role): string
    {
        $role = strtolower($role);
        
        if (strpos($role, 'developer') !== false || strpos($role, 'programmer') !== false) {
            return 'IT';
        }
        if (strpos($role, 'designer') !== false || strpos($role, 'ui') !== false || strpos($role, 'ux') !== false) {
            return 'Design';
        }
        if (strpos($role, 'manager') !== false || strpos($role, 'lead') !== false) {
            return 'Management';
        }
        if (strpos($role, 'support') !== false || strpos($role, 'service') !== false) {
            return 'Support';
        }
        if (strpos($role, 'sales') !== false || strpos($role, 'marketing') !== false) {
            return 'Marketing';
        }
        if (strpos($role, 'hr') !== false || strpos($role, 'personal') !== false) {
            return 'HR';
        }
        if (strpos($role, 'finance') !== false || strpos($role, 'accounting') !== false) {
            return 'Finance';
        }

        return 'IT'; // Standard-Abteilung
    }

    /**
     * Mappe Namen zu Abteilungen (Fallback)
     */
    private function mapNameToDepartment($firstName, $lastName): string
    {
        // Spezifische Zuordnungen basierend auf bekannten Namen
        $nameMappings = [
            'Jörg' => 'Management',
            'Marc' => 'IT',
            'Tim' => 'IT',
            'Mathias' => 'IT',
            'Wladislav' => 'IT',
            'Björn' => 'IT',
            'Steffen' => 'IT',
            'Hannes' => 'IT',
            'Uwe' => 'IT',
            'Antje' => 'HR',
            'Buchhaltung' => 'Finance'
        ];

        foreach ($nameMappings as $name => $department) {
            if (stripos($firstName, $name) !== false || stripos($lastName, $name) !== false) {
                return $department;
            }
        }

        return 'IT'; // Standard-Abteilung
    }
}









