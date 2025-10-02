<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\MocoService;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;

class AnalyzeJorgMichnoWorkingHoursSeeder extends Seeder
{
    protected $mocoService;

    public function __construct()
    {
        $this->mocoService = new MocoService();
    }

    public function run(): void
    {
        $this->command->info('📊 Analysiere Arbeitszeiten von Jörg Michno...');

        try {
            // Finde Jörg Michno in der lokalen Datenbank
            $employee = Employee::where('first_name', 'LIKE', '%Jörg%')
                ->where('last_name', 'LIKE', '%Michno%')
                ->first();

            if (!$employee) {
                $this->command->error('❌ Jörg Michno nicht in der lokalen Datenbank gefunden');
                return;
            }

            $this->command->info("👤 Gefunden: {$employee->first_name} {$employee->last_name} (MOCO ID: {$employee->moco_id})");
            $this->command->info("📋 Aktuelle Kapazität: {$employee->weekly_capacity}h/Woche");

            // Zeitraum für Analyse (letzte 8 Wochen für bessere Statistik)
            $endDate = now();
            $startDate = now()->subWeeks(8);
            
            $this->command->info("📅 Analysiere Zeitraum: {$startDate->format('d.m.Y')} - {$endDate->format('d.m.Y')}");

            // MOCO Aktivitäten abrufen
            $mocoActivities = $this->mocoService->getActivities([
                'from' => $startDate->format('Y-m-d'),
                'to' => $endDate->format('Y-m-d')
            ]);

            $this->command->info('📊 ' . count($mocoActivities) . ' Aktivitäten aus MOCO gefunden');

            // Filtere Aktivitäten für Jörg Michno
            $jorgActivities = array_filter($mocoActivities, function($activity) use ($employee) {
                return isset($activity['user_id']) && $activity['user_id'] == $employee->moco_id;
            });

            $this->command->info('👤 ' . count($jorgActivities) . ' Aktivitäten von Jörg Michno gefunden');

            if (empty($jorgActivities)) {
                $this->command->warn('⚠️ Keine Aktivitäten für Jörg Michno gefunden');
                return;
            }

            // Analysiere Arbeitszeiten
            $weeklyHours = [];
            $totalHours = 0;
            $activityCount = 0;

            foreach ($jorgActivities as $activity) {
                try {
                    $hours = $activity['hours'] ?? 0;
                    $date = isset($activity['date']) ? Carbon::parse($activity['date']) : null;

                    if (!$date) {
                        continue;
                    }

                    $weekKey = $date->format('Y-W');
                    $weekLabel = $date->format('W/Y');
                    
                    if (!isset($weeklyHours[$weekKey])) {
                        $weeklyHours[$weekKey] = [
                            'label' => $weekLabel,
                            'hours' => 0,
                            'days' => []
                        ];
                    }
                    
                    $weeklyHours[$weekKey]['hours'] += $hours;
                    $weeklyHours[$weekKey]['days'][] = $date->format('d.m.Y');
                    $totalHours += $hours;
                    $activityCount++;

                } catch (Exception $e) {
                    $this->command->warn("⚠️ Fehler beim Analysieren von Aktivität {$activity['id']}: " . $e->getMessage());
                }
            }

            // Zeige Wochenanalyse
            $this->command->info('📈 Wochenanalyse:');
            foreach ($weeklyHours as $weekData) {
                $this->command->info("   📅 KW {$weekData['label']}: {$weekData['hours']}h");
                $uniqueDays = array_unique($weekData['days']);
                $this->command->info("      📋 Arbeitstage: " . implode(', ', $uniqueDays));
            }

            // Berechne Durchschnittswerte
            $weekCount = count($weeklyHours);
            $averageHours = $weekCount > 0 ? $totalHours / $weekCount : 0;
            
            $this->command->info('');
            $this->command->info('📊 Zusammenfassung:');
            $this->command->info("   📈 Durchschnitt: " . round($averageHours, 1) . "h/Woche");
            $this->command->info("   📋 Gesamtstunden: {$totalHours}h über {$weekCount} Wochen");
            $this->command->info("   📊 Aktivitäten: {$activityCount}");
            
            // Empfehlung für Kapazitäts-Anpassung
            $recommendedCapacity = $this->getRecommendedCapacity($averageHours, $employee->weekly_capacity);
            $this->command->info("   💡 Empfohlene Kapazität: {$recommendedCapacity}h/Woche");
            
            if ($recommendedCapacity != $employee->weekly_capacity) {
                $this->command->info("   🔄 Änderung: {$employee->weekly_capacity}h → {$recommendedCapacity}h");
                
                // Aktualisiere Kapazität
                $employee->update(['weekly_capacity' => $recommendedCapacity]);
                $this->command->info("   ✅ Kapazität aktualisiert!");
            } else {
                $this->command->info("   ✅ Kapazität ist angemessen");
            }

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









