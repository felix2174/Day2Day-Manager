<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\MocoService;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;

class AnalyzePlanningEntriesSeeder extends Seeder
{
    protected $mocoService;

    public function __construct()
    {
        $this->mocoService = new MocoService();
    }

    public function run(): void
    {
        $this->command->info('📊 Analysiere Planungseinträge aus MOCO für Kapazitätsberechnung...');

        try {
            // Zeitraum für Analyse (letzte 12 Wochen für bessere Statistik)
            $endDate = now();
            $startDate = now()->subWeeks(12);
            
            $this->command->info("📅 Analysiere Zeitraum: {$startDate->format('d.m.Y')} - {$endDate->format('d.m.Y')}");

            // MOCO Planungseinträge abrufen
            $planningEntries = $this->mocoService->getPlanningEntries([
                'from' => $startDate->format('Y-m-d'),
                'to' => $endDate->format('Y-m-d')
            ]);

            $this->command->info('📊 ' . count($planningEntries) . ' Planungseinträge aus MOCO gefunden');

            // Analysiere geplante Arbeitszeiten pro Mitarbeiter
            $employeePlanningHours = [];
            $employeeStats = [];

            foreach ($planningEntries as $entry) {
                try {
                    // Prüfe ob user_id existiert
                    if (!isset($entry['user']['id'])) {
                        continue;
                    }

                    $userId = $entry['user']['id'];
                    $hoursPerDay = $entry['hours_per_day'] ?? 0;
                    $startsOn = isset($entry['starts_on']) ? Carbon::parse($entry['starts_on']) : null;
                    $endsOn = isset($entry['ends_on']) ? Carbon::parse($entry['ends_on']) : null;

                    if (!$startsOn || !$endsOn || $hoursPerDay <= 0) {
                        continue;
                    }

                    // Berechne Anzahl Arbeitstage
                    $workDays = $startsOn->diffInDays($endsOn) + 1;
                    $totalHours = $hoursPerDay * $workDays;

                    // Gruppiere nach Mitarbeiter und Woche
                    $weekKey = $startsOn->format('Y-W');
                    
                    if (!isset($employeePlanningHours[$userId])) {
                        $employeePlanningHours[$userId] = [
                            'name' => $entry['user']['firstname'] . ' ' . $entry['user']['lastname'],
                            'weeks' => [],
                            'total_hours' => 0,
                            'total_days' => 0,
                            'entries' => []
                        ];
                    }
                    
                    if (!isset($employeePlanningHours[$userId]['weeks'][$weekKey])) {
                        $employeePlanningHours[$userId]['weeks'][$weekKey] = 0;
                    }
                    
                    $employeePlanningHours[$userId]['weeks'][$weekKey] += $totalHours;
                    $employeePlanningHours[$userId]['total_hours'] += $totalHours;
                    $employeePlanningHours[$userId]['total_days'] += $workDays;
                    $employeePlanningHours[$userId]['entries'][] = [
                        'project' => $entry['project']['name'] ?? 'Unbekannt',
                        'hours_per_day' => $hoursPerDay,
                        'work_days' => $workDays,
                        'total_hours' => $totalHours,
                        'period' => $startsOn->format('d.m.Y') . ' - ' . $endsOn->format('d.m.Y'),
                        'tentative' => $entry['tentative'] ?? false
                    ];

                } catch (Exception $e) {
                    $this->command->warn("⚠️ Fehler beim Analysieren von Planungseintrag {$entry['id']}: " . $e->getMessage());
                }
            }

            // Berechne Durchschnittswerte und aktualisiere Kapazitäten
            $this->command->info('📈 Berechne Durchschnittswerte und aktualisiere Kapazitäten...');
            
            $updatedCount = 0;
            
            foreach ($employeePlanningHours as $userId => $data) {
                $weekCount = count($data['weeks']);
                $averageHoursPerWeek = $weekCount > 0 ? $data['total_hours'] / $weekCount : 0;
                $averageHoursPerDay = $data['total_days'] > 0 ? $data['total_hours'] / $data['total_days'] : 0;
                
                // Finde lokalen Mitarbeiter
                $employee = Employee::where('moco_id', $userId)->first();
                if (!$employee) {
                    $this->command->warn("⚠️ Lokaler Mitarbeiter für MOCO ID {$userId} ({$data['name']}) nicht gefunden");
                    continue;
                }

                $oldCapacity = $employee->weekly_capacity;
                $newCapacity = $this->getRecommendedCapacity($averageHoursPerWeek, $oldCapacity);
                
                $this->command->info("👤 {$data['name']}:");
                $this->command->info("   📊 Geplante Stunden: {$data['total_hours']}h über {$data['total_days']} Tage");
                $this->command->info("   📈 Durchschnitt: " . round($averageHoursPerWeek, 1) . "h/Woche, " . round($averageHoursPerDay, 1) . "h/Tag");
                $this->command->info("   📋 Aktuelle Kapazität: {$oldCapacity}h/Woche");
                
                // Zeige Projektaufteilung
                $this->command->info("   📋 Projektaufteilung:");
                foreach ($data['entries'] as $entry) {
                    $tentative = $entry['tentative'] ? ' (vorläufig)' : '';
                    $this->command->info("      • {$entry['project']}: {$entry['total_hours']}h ({$entry['period']}){$tentative}");
                }
                
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
            $this->command->info("   📋 Analysierte Mitarbeiter: " . count($employeePlanningHours));
            $this->command->info("   🔄 Aktualisierte Kapazitäten: {$updatedCount}");
            $this->command->info("   ⏱️ Gesamt geplante Stunden: " . array_sum(array_column($employeePlanningHours, 'total_hours')) . "h");

        } catch (Exception $e) {
            $this->command->error('❌ Fehler bei der Planungseinträge-Analyse: ' . $e->getMessage());
        }
    }

    /**
     * Empfehle Kapazität basierend auf geplanten Arbeitszeiten
     */
    private function getRecommendedCapacity($averageHoursPerWeek, $currentCapacity): int
    {
        // Wenn keine Daten vorhanden, behalte aktuelle Kapazität
        if ($averageHoursPerWeek == 0) {
            return $currentCapacity;
        }

        // Berechne empfohlene Kapazität basierend auf Durchschnitt
        $recommended = round($averageHoursPerWeek);
        
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









