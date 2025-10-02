<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\MocoService;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;

class AnalyzeActualWorkingHoursSeeder extends Seeder
{
    protected $mocoService;

    public function __construct()
    {
        $this->mocoService = new MocoService();
    }

    public function run(): void
    {
        $this->command->info('📊 Analysiere tatsächliche Arbeitszeiten aus MOCO Aktivitäten...');

        try {
            // Zeitraum für Analyse (letzte 12 Wochen für bessere Statistik)
            $endDate = now();
            $startDate = now()->subWeeks(12);
            
            $this->command->info("📅 Analysiere Zeitraum: {$startDate->format('d.m.Y')} - {$endDate->format('d.m.Y')}");

            // MOCO Aktivitäten abrufen
            $mocoActivities = $this->mocoService->getActivities([
                'from' => $startDate->format('Y-m-d'),
                'to' => $endDate->format('Y-m-d')
            ]);

            $this->command->info('📊 ' . count($mocoActivities) . ' Aktivitäten aus MOCO gefunden');

            // Analysiere Arbeitszeiten pro Mitarbeiter
            $employeeWorkingHours = [];
            $employeeStats = [];

            foreach ($mocoActivities as $activity) {
                try {
                    // Prüfe ob user_id existiert
                    if (!isset($activity['user_id'])) {
                        continue;
                    }

                    $userId = $activity['user_id'];
                    $hours = $activity['hours'] ?? 0;
                    $date = isset($activity['date']) ? Carbon::parse($activity['date']) : null;

                    if (!$date || $hours <= 0) {
                        continue;
                    }

                    // Gruppiere nach Mitarbeiter und Woche
                    $weekKey = $date->format('Y-W');
                    
                    if (!isset($employeeWorkingHours[$userId])) {
                        $employeeWorkingHours[$userId] = [];
                    }
                    
                    if (!isset($employeeWorkingHours[$userId][$weekKey])) {
                        $employeeWorkingHours[$userId][$weekKey] = 0;
                    }
                    
                    $employeeWorkingHours[$userId][$weekKey] += $hours;

                } catch (Exception $e) {
                    $this->command->warn("⚠️ Fehler beim Analysieren von Aktivität {$activity['id']}: " . $e->getMessage());
                }
            }

            // Berechne Durchschnittswerte und aktualisiere Kapazitäten
            $this->command->info('📈 Berechne Durchschnittswerte und aktualisiere Kapazitäten...');
            
            $updatedCount = 0;
            
            foreach ($employeeWorkingHours as $userId => $weeks) {
                $totalHours = array_sum($weeks);
                $weekCount = count($weeks);
                $averageHours = $weekCount > 0 ? $totalHours / $weekCount : 0;
                
                // Finde lokalen Mitarbeiter
                $employee = Employee::where('moco_id', $userId)->first();
                if (!$employee) {
                    continue;
                }

                $oldCapacity = $employee->weekly_capacity;
                $newCapacity = $this->getRecommendedCapacity($averageHours, $oldCapacity);
                
                $this->command->info("👤 {$employee->first_name} {$employee->last_name}:");
                $this->command->info("   📊 Durchschnitt: " . round($averageHours, 1) . "h/Woche über {$weekCount} Wochen");
                $this->command->info("   📋 Aktuelle Kapazität: {$oldCapacity}h/Woche");
                
                if ($newCapacity != $oldCapacity) {
                    $employee->update(['weekly_capacity' => $newCapacity]);
                    $this->command->info("   🔄 Aktualisiert: {$oldCapacity}h → {$newCapacity}h");
                    $updatedCount++;
                } else {
                    $this->command->info("   ✅ Kapazität ist angemessen");
                }
                
                $this->command->info(""); // Leerzeile
            }

            // Zusammenfassung
            $this->command->info('📊 Analyse-Zusammenfassung:');
            $this->command->info("   📅 Analysierter Zeitraum: 12 Wochen");
            $this->command->info("   📋 Analysierte Mitarbeiter: " . count($employeeWorkingHours));
            $this->command->info("   🔄 Aktualisierte Kapazitäten: {$updatedCount}");
            $this->command->info("   ⏱️ Gesamtstunden: " . array_sum(array_map('array_sum', $employeeWorkingHours)) . "h");

        } catch (Exception $e) {
            $this->command->error('❌ Fehler bei der Arbeitszeit-Analyse: ' . $e->getMessage());
        }
    }

    /**
     * Empfehle Kapazität basierend auf tatsächlichen Arbeitszeiten
     */
    private function getRecommendedCapacity($averageHours, $currentCapacity): int
    {
        // Wenn keine Daten vorhanden, behalte aktuelle Kapazität
        if ($averageHours == 0) {
            return $currentCapacity;
        }

        // Berechne empfohlene Kapazität basierend auf Durchschnitt
        $recommended = round($averageHours);
        
        // Mindest-Kapazität: 20h (Teilzeit)
        if ($recommended < 20) {
            return 20;
        }
        
        // Maximum-Kapazität: 50h (Überstunden)
        if ($recommended > 50) {
            return 50;
        }
        
        // Runde auf Standard-Werte
        $standardCapacities = [20, 25, 30, 35, 40, 45, 50];
        $closest = $standardCapacities[0];
        $minDiff = abs($recommended - $closest);
        
        foreach ($standardCapacities as $capacity) {
            $diff = abs($recommended - $capacity);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $capacity;
            }
        }
        
        return $closest;
    }
}









