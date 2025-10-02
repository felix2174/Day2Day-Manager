<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Employee;
use Carbon\Carbon;
use Exception;

class UpdateCapacitiesFromPlanningSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📊 Aktualisiere Mitarbeiter-Kapazitäten basierend auf MOCO Planungseinträgen...');

        // Simuliere die Planungseinträge-Daten (basierend auf der bereitgestellten JSON)
        $planningEntries = [
            // Beispiel-Einträge aus den bereitgestellten Daten
            [
                'user' => ['id' => 933651010, 'firstname' => 'Uwe', 'lastname' => 'Harnischmacher'],
                'hours_per_day' => 8,
                'starts_on' => '2025-03-17',
                'ends_on' => '2025-03-21',
                'tentative' => false
            ],
            [
                'user' => ['id' => 933651010, 'firstname' => 'Uwe', 'lastname' => 'Harnischmacher'],
                'hours_per_day' => 8,
                'starts_on' => '2025-03-18',
                'ends_on' => '2025-03-19',
                'tentative' => false
            ],
            [
                'user' => ['id' => 933651006, 'firstname' => 'Wladislav', 'lastname' => 'Miller'],
                'hours_per_day' => 8,
                'starts_on' => '2025-03-19',
                'ends_on' => '2025-03-20',
                'tentative' => false
            ],
            [
                'user' => ['id' => 933651006, 'firstname' => 'Wladislav', 'lastname' => 'Miller'],
                'hours_per_day' => 2,
                'starts_on' => '2025-03-26',
                'ends_on' => '2025-03-26',
                'tentative' => false
            ],
            [
                'user' => ['id' => 933719913, 'firstname' => 'Tim', 'lastname' => 'Hoffmann'],
                'hours_per_day' => 8,
                'starts_on' => '2025-03-17',
                'ends_on' => '2025-03-21',
                'tentative' => true
            ],
            [
                'user' => ['id' => 933719913, 'firstname' => 'Tim', 'lastname' => 'Hoffmann'],
                'hours_per_day' => 8,
                'starts_on' => '2025-03-24',
                'ends_on' => '2025-03-24',
                'tentative' => false
            ],
            [
                'user' => ['id' => 933724267, 'firstname' => 'Mathias', 'lastname' => 'Liecker'],
                'hours_per_day' => 8,
                'starts_on' => '2025-03-17',
                'ends_on' => '2025-03-21',
                'tentative' => true
            ],
            [
                'user' => ['id' => 933710265, 'firstname' => 'Lukas', 'lastname' => 'Meinecke'],
                'hours_per_day' => 0.25,
                'starts_on' => '2025-02-04',
                'ends_on' => '2025-02-04',
                'tentative' => false
            ],
            [
                'user' => ['id' => 933710265, 'firstname' => 'Lukas', 'lastname' => 'Meinecke'],
                'hours_per_day' => 0.5,
                'starts_on' => '2025-03-07',
                'ends_on' => '2025-03-07',
                'tentative' => false
            ]
        ];

        $this->command->info('📊 Analysiere ' . count($planningEntries) . ' Planungseinträge...');

        // Analysiere geplante Arbeitszeiten pro Mitarbeiter
        $employeePlanningHours = [];

        foreach ($planningEntries as $entry) {
            try {
                $userId = $entry['user']['id'];
                $hoursPerDay = $entry['hours_per_day'] ?? 0;
                $startsOn = Carbon::parse($entry['starts_on']);
                $endsOn = Carbon::parse($entry['ends_on']);

                if ($hoursPerDay <= 0) {
                    continue;
                }

                // Berechne Anzahl Arbeitstage
                $workDays = $startsOn->diffInDays($endsOn) + 1;
                $totalHours = $hoursPerDay * $workDays;

                // Gruppiere nach Mitarbeiter
                if (!isset($employeePlanningHours[$userId])) {
                    $employeePlanningHours[$userId] = [
                        'name' => $entry['user']['firstname'] . ' ' . $entry['user']['lastname'],
                        'total_hours' => 0,
                        'total_days' => 0,
                        'entries' => []
                    ];
                }
                
                $employeePlanningHours[$userId]['total_hours'] += $totalHours;
                $employeePlanningHours[$userId]['total_days'] += $workDays;
                $employeePlanningHours[$userId]['entries'][] = [
                    'hours_per_day' => $hoursPerDay,
                    'work_days' => $workDays,
                    'total_hours' => $totalHours,
                    'period' => $startsOn->format('d.m.Y') . ' - ' . $endsOn->format('d.m.Y'),
                    'tentative' => $entry['tentative'] ?? false
                ];

            } catch (Exception $e) {
                $this->command->warn("⚠️ Fehler beim Analysieren von Planungseintrag: " . $e->getMessage());
            }
        }

        // Berechne Durchschnittswerte und aktualisiere Kapazitäten
        $this->command->info('📈 Berechne Durchschnittswerte und aktualisiere Kapazitäten...');
        
        $updatedCount = 0;
        
        foreach ($employeePlanningHours as $userId => $data) {
            $averageHoursPerDay = $data['total_days'] > 0 ? $data['total_hours'] / $data['total_days'] : 0;
            $averageHoursPerWeek = $averageHoursPerDay * 5; // 5 Arbeitstage pro Woche
            
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
            $this->command->info("   📋 Planungsaufteilung:");
            foreach ($data['entries'] as $entry) {
                $tentative = $entry['tentative'] ? ' (vorläufig)' : '';
                $this->command->info("      • {$entry['total_hours']}h ({$entry['period']}){$tentative}");
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
        $this->command->info("   📋 Analysierte Mitarbeiter: " . count($employeePlanningHours));
        $this->command->info("   🔄 Aktualisierte Kapazitäten: {$updatedCount}");
        $this->command->info("   ⏱️ Gesamt geplante Stunden: " . array_sum(array_column($employeePlanningHours, 'total_hours')) . "h");
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









