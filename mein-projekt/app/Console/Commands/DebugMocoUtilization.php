<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Services\MocoService;
use Carbon\Carbon;

class DebugMocoUtilization extends Command
{
    protected $signature = 'moco:debug-utilization {employee_id?}';
    protected $description = 'Debug MOCO utilization calculation based on Activities';

    public function handle()
    {
        $employeeId = $this->argument('employee_id');
        
        if ($employeeId) {
            $employee = Employee::find($employeeId);
            if (!$employee || !$employee->moco_id) {
                $this->error('Employee not found or has no MOCO ID');
                return 1;
            }
        } else {
            $employee = Employee::whereNotNull('moco_id')->first();
            if (!$employee) {
                $this->error('No employee with MOCO ID found');
                return 1;
            }
        }
        
        $mocoService = app(MocoService::class);
        
        $this->info("==========================================");
        $this->info("Employee: {$employee->first_name} {$employee->last_name}");
        $this->info("MOCO ID: {$employee->moco_id}");
        $this->info("==========================================");
        $this->line("");
        
        // 1. Kapazität
        $this->info("1. WEEKLY CAPACITY:");
        $mocoUser = $mocoService->getUser($employee->moco_id);
        $weeklyCapacity = 40; // Default
        
        if ($mocoUser && isset($mocoUser['work_schedule']) && is_array($mocoUser['work_schedule'])) {
            $weeklyCapacity = array_sum($mocoUser['work_schedule']);
            $this->line("  Source: MOCO work_schedule");
            $this->line("  Details:");
            foreach ($mocoUser['work_schedule'] as $day => $hours) {
                $this->line("    - {$day}: {$hours}h");
            }
        } else {
            $weeklyCapacity = $employee->weekly_capacity ?? 40;
            $this->line("  Source: Local database (fallback)");
        }
        $this->info("  Total: {$weeklyCapacity}h/week");
        
        $this->line("");
        $this->info("------------------------------------------");
        
        // 2. Activities
        $this->info("2. ACTIVITIES (last 4 weeks):");
        $fourWeeksAgo = Carbon::now()->subWeeks(4);
        $activities = $mocoService->getUserActivities($employee->moco_id, [
            'from' => $fourWeeksAgo->format('Y-m-d'),
            'to' => Carbon::now()->format('Y-m-d')
        ]);
        
        $this->line("  Period: {$fourWeeksAgo->format('d.m.Y')} - " . Carbon::now()->format('d.m.Y'));
        $this->line("  Total activities: " . count($activities));
        $this->line("");
        
        $totalHours = 0;
        $byProject = [];
        $byWeek = [];
        
        // Gruppiere nach Projekt und Woche
        foreach ($activities as $activity) {
            $hours = $activity['hours'] ?? 0;
            $totalHours += $hours;
            
            // Nach Projekt
            $projectName = 'Unknown';
            if (isset($activity['project']) && isset($activity['project']['name'])) {
                $projectName = $activity['project']['name'];
            }
            
            if (!isset($byProject[$projectName])) {
                $byProject[$projectName] = 0;
            }
            $byProject[$projectName] += $hours;
            
            // Nach Woche
            if (isset($activity['date'])) {
                $date = Carbon::parse($activity['date']);
                $week = $date->weekOfYear;
                if (!isset($byWeek[$week])) {
                    $byWeek[$week] = 0;
                }
                $byWeek[$week] += $hours;
            }
        }
        
        // Sortiere nach Stunden
        arsort($byProject);
        
        $this->line("  By Project (Top 10):");
        $count = 0;
        foreach ($byProject as $projectName => $hours) {
            if ($count >= 10) break;
            $this->line("    - {$projectName}: {$hours}h");
            $count++;
        }
        
        if (count($byProject) > 10) {
            $remaining = count($byProject) - 10;
            $this->line("    ... und {$remaining} weitere Projekte");
        }
        
        $this->line("");
        $this->line("  By Week:");
        ksort($byWeek);
        foreach ($byWeek as $week => $hours) {
            $this->line("    - KW {$week}: {$hours}h");
        }
        
        $this->line("");
        $this->info("  Total hours (4 weeks): {$totalHours}h");
        
        // 3. Berechnung
        $maxHours = $weeklyCapacity * 4; // Maximale Kapazität über 4 Wochen
        $utilization = $maxHours > 0 ? round(($totalHours / $maxHours) * 100) : 0;
        
        $this->line("");
        $this->info("------------------------------------------");
        $this->info("3. CALCULATION:");
        $this->line("  Total hours (4 weeks): {$totalHours}h");
        $this->line("  Maximum capacity (4 weeks): {$maxHours}h ({$weeklyCapacity}h/week × 4)");
        $this->line("");
        
        // Projekt-Verteilung in Prozent
        $this->line("  Project Distribution:");
        foreach ($byProject as $projectName => $hours) {
            $percentage = $totalHours > 0 ? round(($hours / $totalHours) * 100, 1) : 0;
            $bar = str_repeat('█', min(50, (int)($percentage / 2)));
            $this->line("    {$projectName}:");
            $this->line("      {$bar} {$percentage}% ({$hours}h)");
        }
        
        $this->line("");
        
        // Farbige Ausgabe der Auslastung
        if ($utilization >= 90) {
            $this->error("  Overall Utilization: {$utilization}% (ÜBERLASTET)");
        } elseif ($utilization >= 70) {
            $this->warn("  Overall Utilization: {$utilization}% (Gut ausgelastet)");
        } else {
            $this->info("  Overall Utilization: {$utilization}% (Unterausgelastet)");
        }
        
        $this->line("");
        $this->info("==========================================");
        
        return 0;
    }
}

