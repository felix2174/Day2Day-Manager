<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\MocoService;
use App\Models\Employee;
use Exception;

class UpdateEmployeeCapacitiesSeeder extends Seeder
{
    protected $mocoService;

    public function __construct()
    {
        $this->mocoService = new MocoService();
    }

    public function run(): void
    {
        $this->command->info('⚡ Aktualisiere Mitarbeiter-Kapazitäten aus MOCO...');

        try {
            // MOCO Benutzer abrufen
            $mocoUsers = $this->mocoService->getUsers();
            $this->command->info('📊 ' . count($mocoUsers) . ' Benutzer aus MOCO gefunden');

            $updatedEmployees = 0;
            $capacityStats = [];

            foreach ($mocoUsers as $mocoUser) {
                try {
                    // Finde entsprechenden Mitarbeiter
                    $employee = Employee::where('moco_id', $mocoUser['id'])->first();
                    
                    if (!$employee) {
                        $this->command->warn("⚠️ Kein lokaler Mitarbeiter für MOCO ID {$mocoUser['id']} gefunden");
                        continue;
                    }

                    // Bestimme Kapazität basierend auf MOCO-Daten
                    $weeklyCapacity = $this->determineWeeklyCapacity($mocoUser);
                    
                    if ($weeklyCapacity && $weeklyCapacity != $employee->weekly_capacity) {
                        $oldCapacity = $employee->weekly_capacity;
                        $employee->update(['weekly_capacity' => $weeklyCapacity]);
                        $updatedEmployees++;
                        
                        // Sammle Statistiken
                        if (!isset($capacityStats[$weeklyCapacity])) {
                            $capacityStats[$weeklyCapacity] = 0;
                        }
                        $capacityStats[$weeklyCapacity]++;
                        
                        $this->command->info("✅ {$employee->first_name} {$employee->last_name}: {$oldCapacity}h → {$weeklyCapacity}h/Woche");
                    } else {
                        $this->command->info("ℹ️ {$employee->first_name} {$employee->last_name}: Kapazität bereits korrekt ({$employee->weekly_capacity}h/Woche)");
                    }

                } catch (Exception $e) {
                    $this->command->warn("⚠️ Fehler beim Update von Mitarbeiter {$mocoUser['id']}: " . $e->getMessage());
                }
            }

            // Zusammenfassung
            $this->command->info('📊 Kapazitäts-Update abgeschlossen:');
            $this->command->info("   👥 Aktualisierte Mitarbeiter: {$updatedEmployees}");
            $this->command->info("   📋 Kapazitäts-Verteilung:");
            
            foreach ($capacityStats as $capacity => $count) {
                $this->command->info("      {$capacity}h/Woche: {$count} Mitarbeiter");
            }

        } catch (Exception $e) {
            $this->command->error('❌ Fehler bei der Kapazitäts-Aktualisierung: ' . $e->getMessage());
        }
    }

    /**
     * Bestimme die Wochenkapazität basierend auf MOCO-Daten
     */
    private function determineWeeklyCapacity($mocoUser): ?int
    {
        // 1. Direkte Wochenkapazität aus MOCO
        if (isset($mocoUser['weekly_target_hours']) && is_numeric($mocoUser['weekly_target_hours'])) {
            return (int) $mocoUser['weekly_target_hours'];
        }

        // 2. Tägliche Stunden * 5 (Standard-Arbeitswoche)
        if (isset($mocoUser['daily_target_hours']) && is_numeric($mocoUser['daily_target_hours'])) {
            return (int) ($mocoUser['daily_target_hours'] * 5);
        }

        // 3. Monatliche Stunden / 4.33 (Durchschnittliche Wochen pro Monat)
        if (isset($mocoUser['monthly_target_hours']) && is_numeric($mocoUser['monthly_target_hours'])) {
            return (int) ($mocoUser['monthly_target_hours'] / 4.33);
        }

        // 4. Arbeitszeit-Modell basierend auf Rolle/Abteilung
        $department = $this->getDepartmentFromUser($mocoUser);
        $capacity = $this->getCapacityByDepartment($department);
        
        if ($capacity) {
            return $capacity;
        }

        // 5. Status-basierte Zuordnung
        if (isset($mocoUser['active']) && !$mocoUser['active']) {
            return 0; // Inaktive Mitarbeiter haben 0 Stunden
        }

        // 6. Standard-Kapazität
        return 40; // Standard Vollzeit
    }

    /**
     * Ermittle Abteilung aus Benutzer-Daten
     */
    private function getDepartmentFromUser($mocoUser): ?string
    {
        if (isset($mocoUser['department']) && !empty($mocoUser['department'])) {
            return $mocoUser['department'];
        }
        if (isset($mocoUser['company']) && !empty($mocoUser['company'])) {
            return $mocoUser['company'];
        }
        return null;
    }

    /**
     * Bestimme Kapazität basierend auf Abteilung
     */
    private function getCapacityByDepartment($department): ?int
    {
        if (!$department) {
            return null;
        }

        $department = strtolower($department);
        
        // Abteilungs-spezifische Kapazitäten
        $departmentCapacities = [
            'management' => 50,  // Management hat oft höhere Arbeitszeiten
            'it' => 40,         // Standard Vollzeit
            'development' => 40, // Standard Vollzeit
            'design' => 35,     // Kreative Bereiche oft Teilzeit
            'hr' => 30,         // HR oft Teilzeit
            'finance' => 40,    // Buchhaltung Standard
            'support' => 40,    // Support Standard
            'marketing' => 35,  // Marketing oft Teilzeit
            'sales' => 45,      // Sales oft mehr Stunden
        ];

        foreach ($departmentCapacities as $dept => $capacity) {
            if (strpos($department, $dept) !== false) {
                return $capacity;
            }
        }

        return null; // Keine spezifische Zuordnung gefunden
    }
}









