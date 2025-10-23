<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Models\Assignment;
use App\Models\Absence;
use Carbon\Carbon;

class TestGanttData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gantt:test-employee {employeeId : Die ID des Mitarbeiters}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testet und validiert die Gantt-Daten für einen einzelnen Mitarbeiter';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $employeeId = $this->argument('employeeId');
        
        // ==================== MITARBEITER LADEN ====================
        $employee = Employee::find($employeeId);
        
        if (!$employee) {
            $this->error("❌ Mitarbeiter mit ID {$employeeId} wurde nicht gefunden.");
            return 1;
        }
        
        $this->info("==========================================");
        $this->info("📊 GANTT-DATEN VALIDIERUNG");
        $this->info("==========================================");
        $this->line("");
        $this->info("Mitarbeiter: {$employee->first_name} {$employee->last_name}");
        $this->info("ID: {$employee->id}");
        $this->info("MOCO-ID: " . ($employee->moco_id ?? 'Nicht verknüpft'));
        $this->info("Abteilung: {$employee->department}");
        $this->info("Wochenkapazität: {$employee->weekly_capacity}h");
        $this->line("");
        
        // ==================== ZEITRAUM FESTLEGEN ====================
        $startDate = Carbon::now()->startOfDay();
        $endDate = Carbon::now()->addDays(30)->endOfDay();
        
        $this->info("📅 Analysezeitraum: {$startDate->format('d.m.Y')} - {$endDate->format('d.m.Y')}");
        $this->line("");
        
        // ==================== ZUWEISUNGEN LADEN ====================
        $assignments = Assignment::where('employee_id', $employee->id)
            ->with('project')
            ->orderBy('start_date')
            ->get();
        
        $this->info("==========================================");
        $this->info("📋 ZUWEISUNGEN (Assignments)");
        $this->info("==========================================");
        $this->info("Gesamt: {$assignments->count()}");
        
        if ($assignments->isEmpty()) {
            $this->warn("⚠️  Keine Zuweisungen für diesen Mitarbeiter gefunden.");
        } else {
            $this->line("");
            foreach ($assignments as $index => $assignment) {
                $projectName = $assignment->project ? $assignment->project->name : 'Unbekanntes Projekt';
                $this->line(($index + 1) . ". {$assignment->task_name}");
                $this->line("   Projekt: {$projectName}");
                $this->line("   Zeitraum: {$assignment->start_date} bis {$assignment->end_date}");
                $this->line("   Stunden/Woche: {$assignment->weekly_hours}h");
                
                // Prüfe ob im Analysezeitraum
                $assignmentStart = Carbon::parse($assignment->start_date);
                $assignmentEnd = Carbon::parse($assignment->end_date);
                
                if ($assignmentEnd->gte($startDate) && $assignmentStart->lte($endDate)) {
                    $this->line("   ✅ Im Analysezeitraum");
                } else {
                    $this->line("   ⏭️  Außerhalb des Analysezeitraums");
                }
                $this->line("");
            }
        }
        
        // ==================== ABWESENHEITEN LADEN ====================
        $absences = Absence::where('employee_id', $employee->id)
            ->where('end_date', '>=', $startDate->format('Y-m-d'))
            ->where('start_date', '<=', $endDate->format('Y-m-d'))
            ->orderBy('start_date')
            ->get();
        
        $this->info("==========================================");
        $this->info("🏖️  ABWESENHEITEN");
        $this->info("==========================================");
        $this->info("Im Analysezeitraum: {$absences->count()}");
        
        if ($absences->isEmpty()) {
            $this->info("✅ Keine Abwesenheiten im analysierten Zeitraum.");
        } else {
            $this->line("");
            foreach ($absences as $index => $absence) {
                $this->line(($index + 1) . ". {$absence->type}");
                $this->line("   Zeitraum: {$absence->start_date} bis {$absence->end_date}");
                $this->line("   Grund: " . ($absence->reason ?? 'Nicht angegeben'));
                
                // Berechne betroffene Tage
                $absenceStart = Carbon::parse($absence->start_date);
                $absenceEnd = Carbon::parse($absence->end_date);
                $days = $absenceStart->diffInDays($absenceEnd) + 1;
                $this->line("   Tage: {$days}");
                $this->line("");
            }
        }
        
        // ==================== AUSLASTUNGSBERECHNUNG ====================
        $this->info("==========================================");
        $this->info("📈 AUSLASTUNGSBERECHNUNG");
        $this->info("==========================================");
        
        $utilizationData = $this->calculateTimeBasedUtilization($assignments, $employee->weekly_capacity, $absences);
        
        $this->info("Gesamt geplante Stunden/Woche: {$utilizationData['total_hours']}h");
        $this->info("Peak-Auslastung (höchste Woche): {$utilizationData['peak_hours']}h");
        $this->info("Durchschnittliche Auslastung: {$utilizationData['average_hours']}h");
        $this->info("Wochen mit Überlastung (>40h): {$utilizationData['overlap_weeks']}");
        
        if ($utilizationData['has_overlaps']) {
            $this->warn("⚠️  Überlappungen erkannt! Mitarbeiter ist zeitweise überbucht.");
        } else {
            $this->info("✅ Keine kritischen Überlappungen.");
        }
        
        // Kapazitätsprüfung (jetzt mit echter Auslastung inkl. Abwesenheiten)
        $capacityUsage = $utilizationData['peak_utilization_percent'];
        
        $this->line("");
        $this->info("Kapazitätsauslastung (Peak): {$capacityUsage}%");
        $this->info("Durchschnittliche Kapazitätsauslastung: {$utilizationData['average_utilization_percent']}%");
        
        if ($capacityUsage > 100) {
            $this->error("🔴 KRITISCH: Mitarbeiter ist überbucht!");
        } elseif ($capacityUsage > 85) {
            $this->warn("🟡 WARNUNG: Hohe Auslastung, wenig Puffer.");
        } elseif ($capacityUsage >= 70) {
            $this->info("🟢 OPTIMAL: Gute Auslastung mit ausreichend Puffer.");
        } elseif ($capacityUsage > 0) {
            $this->info("🔵 NIEDRIG: Mitarbeiter hat noch freie Kapazität.");
        } else {
            $this->warn("⚪ KEINE AUSLASTUNG: Keine Zuweisungen gefunden.");
        }
        
        // ==================== WOCHENWEISE AUFSCHLÜSSELUNG ====================
        $this->line("");
        $this->info("==========================================");
        $this->info("📅 WOCHENWEISE AUFSCHLÜSSELUNG");
        $this->info("==========================================");
        
        $weeklyBreakdown = $this->getWeeklyBreakdown($assignments, $absences, $employee->weekly_capacity, $startDate, $endDate);
        
        if (empty($weeklyBreakdown)) {
            $this->warn("Keine Daten für wochenweise Aufschlüsselung verfügbar.");
        } else {
            $this->table(
                ['Woche', 'Zeitraum', 'Geplante Stunden', 'Auslastung', 'Status'],
                $weeklyBreakdown
            );
        }
        
        // ==================== JSON-AUSGABE ====================
        $this->line("");
        $this->info("==========================================");
        $this->info("📦 VOLLSTÄNDIGE DATENSTRUKTUR (JSON)");
        $this->info("==========================================");
        
        $fullData = [
            'employee' => [
                'id' => $employee->id,
                'name' => $employee->first_name . ' ' . $employee->last_name,
                'moco_id' => $employee->moco_id,
                'department' => $employee->department,
                'weekly_capacity' => $employee->weekly_capacity,
            ],
            'analysis_period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => $startDate->diffInDays($endDate) + 1,
            ],
            'assignments' => $assignments->map(function ($assignment) {
                return [
                    'id' => $assignment->id,
                    'task_name' => $assignment->task_name,
                    'task_description' => $assignment->task_description,
                    'project_id' => $assignment->project_id,
                    'project_name' => $assignment->project ? $assignment->project->name : null,
                    'start_date' => $assignment->start_date,
                    'end_date' => $assignment->end_date,
                    'weekly_hours' => $assignment->weekly_hours,
                ];
            })->toArray(),
            'absences' => $absences->map(function ($absence) {
                return [
                    'id' => $absence->id,
                    'type' => $absence->type,
                    'start_date' => $absence->start_date,
                    'end_date' => $absence->end_date,
                    'reason' => $absence->reason,
                    'days' => Carbon::parse($absence->start_date)->diffInDays(Carbon::parse($absence->end_date)) + 1,
                ];
            })->toArray(),
            'utilization' => [
                'total_weekly_hours' => $utilizationData['total_hours'],
                'peak_weekly_hours' => $utilizationData['peak_hours'],
                'average_weekly_hours' => $utilizationData['average_hours'],
                'has_overlaps' => $utilizationData['has_overlaps'],
                'overlap_weeks' => $utilizationData['overlap_weeks'],
                'capacity_usage_percent' => $capacityUsage,
                'peak_utilization_percent' => $utilizationData['peak_utilization_percent'],
                'average_utilization_percent' => $utilizationData['average_utilization_percent'],
            ],
            'weekly_breakdown' => $weeklyBreakdown,
        ];
        
        $this->line(json_encode($fullData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        
        $this->line("");
        $this->info("==========================================");
        $this->info("✅ Daten-Validierung abgeschlossen");
        $this->info("==========================================");
        
        return 0;
    }
    
    /**
     * Calculate time-based utilization considering overlaps AND absences
     * (Erweiterte Version mit Abwesenheitsintegration)
     */
    private function calculateTimeBasedUtilization($assignments, $weeklyCapacity = 40, $absences = null)
    {
        if ($assignments->isEmpty()) {
            return [
                'total_hours' => 0,
                'peak_hours' => 0,
                'average_hours' => 0,
                'has_overlaps' => false,
                'overlap_weeks' => 0,
                'peak_utilization_percent' => 0,
                'average_utilization_percent' => 0,
            ];
        }

        // Define analysis period (next 6 months from now)
        $analysisStart = Carbon::now()->startOfWeek();
        $analysisEnd = Carbon::now()->addMonths(6)->endOfWeek();

        // Create a map of weeks with their assigned hours
        $weeklyHours = [];
        // Create a map of weeks with their effective capacity (accounting for absences)
        $weeklyEffectiveCapacity = [];
        
        foreach ($assignments as $assignment) {
            $start = Carbon::parse($assignment->start_date)->startOfWeek();
            $end = Carbon::parse($assignment->end_date)->endOfWeek();
            
            // Only consider assignments within analysis period
            if ($end->lt($analysisStart) || $start->gt($analysisEnd)) {
                continue;
            }
            
            // Clamp to analysis period
            $start = $start->lt($analysisStart) ? $analysisStart->copy() : $start;
            $end = $end->gt($analysisEnd) ? $analysisEnd->copy() : $end;
            
            // Add hours for each week in the assignment period
            $currentWeek = $start->copy();
            while ($currentWeek->lte($end)) {
                $weekKey = $currentWeek->format('Y-W');
                
                if (!isset($weeklyHours[$weekKey])) {
                    $weeklyHours[$weekKey] = 0;
                    $weeklyEffectiveCapacity[$weekKey] = $weeklyCapacity; // Start with full capacity
                }
                
                $weeklyHours[$weekKey] += $assignment->weekly_hours ?? 0;
                $currentWeek->addWeek();
            }
        }

        // Subtract absence hours from weekly capacity
        if ($absences && $absences->isNotEmpty()) {
            foreach ($absences as $absence) {
                $absenceStart = Carbon::parse($absence->start_date)->startOfWeek();
                $absenceEnd = Carbon::parse($absence->end_date)->endOfWeek();
                
                // Only consider absences within analysis period
                if ($absenceEnd->lt($analysisStart) || $absenceStart->gt($analysisEnd)) {
                    continue;
                }
                
                // Clamp to analysis period
                $absenceStart = $absenceStart->lt($analysisStart) ? $analysisStart->copy() : $absenceStart;
                $absenceEnd = $absenceEnd->gt($analysisEnd) ? $analysisEnd->copy() : $absenceEnd;
                
                // Calculate absence hours for each affected week
                $currentWeek = $absenceStart->copy();
                while ($currentWeek->lte($absenceEnd)) {
                    $weekKey = $currentWeek->format('Y-W');
                    
                    // Initialize capacity if not set
                    if (!isset($weeklyEffectiveCapacity[$weekKey])) {
                        $weeklyEffectiveCapacity[$weekKey] = $weeklyCapacity;
                    }
                    
                    // Calculate how many days of this absence fall in this week
                    $weekStart = $currentWeek->copy()->startOfWeek();
                    $weekEnd = $currentWeek->copy()->endOfWeek();
                    
                    $overlapStart = Carbon::parse($absence->start_date)->max($weekStart);
                    $overlapEnd = Carbon::parse($absence->end_date)->min($weekEnd);
                    
                    if ($overlapStart->lte($overlapEnd)) {
                        // Count business days (Mon-Fri) in overlap period
                        $absenceDays = 0;
                        $checkDay = $overlapStart->copy();
                        while ($checkDay->lte($overlapEnd)) {
                            if ($checkDay->isWeekday()) {
                                $absenceDays++;
                            }
                            $checkDay->addDay();
                        }
                        
                        // Calculate hours lost (assuming 8h per day for 40h week = 5 days)
                        $hoursPerDay = $weeklyCapacity / 5;
                        $absenceHours = $absenceDays * $hoursPerDay;
                        
                        // Reduce capacity for this week
                        $weeklyEffectiveCapacity[$weekKey] = max(0, $weeklyEffectiveCapacity[$weekKey] - $absenceHours);
                    }
                    
                    $currentWeek->addWeek();
                }
            }
        }

        // Calculate statistics
        if (empty($weeklyHours)) {
            return [
                'total_hours' => 0,
                'peak_hours' => 0,
                'average_hours' => 0,
                'has_overlaps' => false,
                'overlap_weeks' => 0,
                'peak_utilization_percent' => 0,
                'average_utilization_percent' => 0,
            ];
        }

        $peakHours = max($weeklyHours);
        $averageHours = round(array_sum($weeklyHours) / count($weeklyHours), 1);
        $totalHours = $assignments->sum('weekly_hours');
        
        // Calculate utilization percentages considering effective capacity
        $utilizationPercentages = [];
        $overlapWeeks = 0;
        
        foreach ($weeklyHours as $weekKey => $hours) {
            $effectiveCapacity = $weeklyEffectiveCapacity[$weekKey] ?? $weeklyCapacity;
            
            // Edge case: if effective capacity is 0 but hours > 0, set to 999%
            if ($effectiveCapacity <= 0 && $hours > 0) {
                $utilizationPercent = 999;
            } elseif ($effectiveCapacity > 0) {
                $utilizationPercent = round(($hours / $effectiveCapacity) * 100, 1);
            } else {
                $utilizationPercent = 0;
            }
            
            $utilizationPercentages[] = $utilizationPercent;
            
            // Count as overlap if exceeds original standard capacity
            if ($hours > $weeklyCapacity) {
                $overlapWeeks++;
            }
        }
        
        $hasOverlaps = $overlapWeeks > 0;
        $peakUtilizationPercent = !empty($utilizationPercentages) ? max($utilizationPercentages) : 0;
        $averageUtilizationPercent = !empty($utilizationPercentages) 
            ? round(array_sum($utilizationPercentages) / count($utilizationPercentages), 1) 
            : 0;

        return [
            'total_hours' => $totalHours,
            'peak_hours' => $peakHours,
            'average_hours' => $averageHours,
            'has_overlaps' => $hasOverlaps,
            'overlap_weeks' => $overlapWeeks,
            'peak_utilization_percent' => $peakUtilizationPercent,
            'average_utilization_percent' => $averageUtilizationPercent,
        ];
    }
    
    /**
     * Get weekly breakdown for table display (with absence consideration)
     */
    private function getWeeklyBreakdown($assignments, $absences, $weeklyCapacity, $startDate, $endDate)
    {
        $weeklyData = [];
        $currentWeek = $startDate->copy()->startOfWeek();
        $endWeek = $endDate->copy()->endOfWeek();
        
        while ($currentWeek->lte($endWeek)) {
            $weekStart = $currentWeek->copy();
            $weekEnd = $currentWeek->copy()->endOfWeek();
            $weekKey = $weekStart->format('Y-W');
            
            $weekHours = 0;
            $effectiveCapacity = $weeklyCapacity;
            
            // Calculate assigned hours
            foreach ($assignments as $assignment) {
                $assignmentStart = Carbon::parse($assignment->start_date);
                $assignmentEnd = Carbon::parse($assignment->end_date);
                
                // Check if assignment overlaps with this week
                if ($assignmentEnd->gte($weekStart) && $assignmentStart->lte($weekEnd)) {
                    $weekHours += $assignment->weekly_hours ?? 0;
                }
            }
            
            // Calculate absence impact on capacity
            if ($absences && $absences->isNotEmpty()) {
                foreach ($absences as $absence) {
                    $absenceStart = Carbon::parse($absence->start_date);
                    $absenceEnd = Carbon::parse($absence->end_date);
                    
                    // Check if absence overlaps with this week
                    if ($absenceEnd->gte($weekStart) && $absenceStart->lte($weekEnd)) {
                        $overlapStart = $absenceStart->max($weekStart);
                        $overlapEnd = $absenceEnd->min($weekEnd);
                        
                        if ($overlapStart->lte($overlapEnd)) {
                            // Count business days
                            $absenceDays = 0;
                            $checkDay = $overlapStart->copy();
                            while ($checkDay->lte($overlapEnd)) {
                                if ($checkDay->isWeekday()) {
                                    $absenceDays++;
                                }
                                $checkDay->addDay();
                            }
                            
                            // Calculate hours lost
                            $hoursPerDay = $weeklyCapacity / 5;
                            $absenceHours = $absenceDays * $hoursPerDay;
                            $effectiveCapacity = max(0, $effectiveCapacity - $absenceHours);
                        }
                    }
                }
            }
            
            if ($weekHours > 0 || $effectiveCapacity < $weeklyCapacity) {
                // Calculate utilization based on effective capacity
                if ($effectiveCapacity <= 0 && $weekHours > 0) {
                    $utilization = 999;
                } elseif ($effectiveCapacity > 0) {
                    $utilization = round(($weekHours / $effectiveCapacity) * 100, 1);
                } else {
                    $utilization = 0;
                }
                
                $status = match(true) {
                    $utilization >= 999 => '🔴 Kritisch (Urlaub!)',
                    $utilization > 100 => '🔴 Überbucht',
                    $utilization > 85 => '🟡 Hoch',
                    $utilization >= 70 => '🟢 Optimal',
                    $utilization > 0 => '🔵 Niedrig',
                    default => '⚪ Frei',
                };
                
                $capacityInfo = $effectiveCapacity < $weeklyCapacity 
                    ? " (Kapazität: {$effectiveCapacity}h)" 
                    : '';
                
                $weeklyData[] = [
                    $weekKey,
                    $weekStart->format('d.m.Y') . ' - ' . $weekEnd->format('d.m.Y'),
                    $weekHours . 'h' . $capacityInfo,
                    $utilization . '%',
                    $status,
                ];
            }
            
            $currentWeek->addWeek();
        }
        
        return $weeklyData;
    }
}
