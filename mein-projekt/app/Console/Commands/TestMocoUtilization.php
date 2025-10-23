<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Employee;
use App\Services\MocoService;
use Carbon\Carbon;

class TestMocoUtilization extends Command
{
    protected $signature = 'moco:test-utilization {employee_id?}';
    protected $description = 'Test MOCO utilization calculation for an employee';

    public function handle()
    {
        $employeeId = $this->argument('employee_id');
        
        if ($employeeId) {
            $employees = Employee::where('id', $employeeId)->get();
        } else {
            $employees = Employee::whereNotNull('moco_id')->take(3)->get();
        }
        
        if ($employees->count() === 0) {
            $this->error('No employees found with MOCO ID');
            return 1;
        }
        
        $mocoService = app(MocoService::class);
        
        foreach ($employees as $employee) {
            $this->info("==========================================");
            $this->info("Employee: {$employee->first_name} {$employee->last_name}");
            $this->info("MOCO ID: {$employee->moco_id}");
            $this->info("------------------------------------------");
            
            try {
                // 1. User-Daten
                $mocoUser = $mocoService->getUser($employee->moco_id);
                
                if ($mocoUser) {
                    $this->info("✓ MOCO User found");
                    
                    // Work Schedule
                    if (isset($mocoUser['work_schedule'])) {
                        $this->info("Work Schedule:");
                        foreach ($mocoUser['work_schedule'] as $day => $hours) {
                            $this->line("  - {$day}: {$hours}h");
                        }
                        $weeklyCapacity = array_sum($mocoUser['work_schedule']);
                        $this->info("Total weekly capacity: {$weeklyCapacity}h");
                    } else {
                        $this->warn("No work_schedule found in MOCO user data");
                    }
                    
                    // Custom Properties
                    if (isset($mocoUser['custom_properties'])) {
                        $this->info("Custom Properties: " . json_encode($mocoUser['custom_properties']));
                    }
                } else {
                    $this->error("✗ MOCO User not found");
                }
                
                $this->info("------------------------------------------");
                
                // 2. Projekte und Contracts
                $userProjects = $mocoService->getUserProjects($employee->moco_id);
                $this->info("Found " . count($userProjects) . " projects for this user");
                
                $totalPlannedHours = 0;
                
                foreach ($userProjects as $project) {
                    $isActive = $project['active'] ?? false;
                    $startDate = isset($project['start_date']) ? Carbon::parse($project['start_date']) : null;
                    $finishDate = isset($project['finish_date']) ? Carbon::parse($project['finish_date']) : null;
                    
                    $this->line("");
                    $this->line("Project: {$project['name']}");
                    $this->line("  Active: " . ($isActive ? 'Yes' : 'No'));
                    $this->line("  Start: " . ($startDate ? $startDate->format('d.m.Y') : 'N/A'));
                    $this->line("  Finish: " . ($finishDate ? $finishDate->format('d.m.Y') : 'N/A'));
                    
                    // Prüfe Zeitraum
                    $isInTimeframe = true;
                    if ($startDate && $startDate->isFuture()) {
                        $isInTimeframe = false;
                        $this->line("  ⚠ Not started yet");
                    }
                    if ($finishDate && $finishDate->isPast()) {
                        $isInTimeframe = false;
                        $this->line("  ⚠ Already finished");
                    }
                    
                    // Contracts
                    if (isset($project['contracts']) && is_array($project['contracts'])) {
                        $this->line("  Contracts:");
                        foreach ($project['contracts'] as $contract) {
                            if (isset($contract['user_id']) && $contract['user_id'] == $employee->moco_id) {
                                $hoursPerWeek = $contract['hours_per_week'] ?? 0;
                                $this->line("    - User ID: {$contract['user_id']}");
                                $this->line("    - Hours/Week: {$hoursPerWeek}h");
                                $this->line("    - Active: " . ($contract['active'] ?? false ? 'Yes' : 'No'));
                                
                                if ($isActive && $isInTimeframe && $hoursPerWeek > 0) {
                                    $totalPlannedHours += $hoursPerWeek;
                                    $this->info("    ✓ Counted: {$hoursPerWeek}h");
                                }
                            }
                        }
                    } else {
                        $this->line("  No contracts found");
                    }
                }
                
                $this->info("------------------------------------------");
                $this->info("SUMMARY:");
                $weeklyCapacity = $employee->moco_weekly_capacity ?? $employee->weekly_capacity ?? 40;
                $this->info("Weekly Capacity: {$weeklyCapacity}h");
                $this->info("Planned Hours: {$totalPlannedHours}h");
                $utilization = $weeklyCapacity > 0 ? round(($totalPlannedHours / $weeklyCapacity) * 100) : 0;
                $this->info("Utilization: {$utilization}%");
                $freeHours = $weeklyCapacity - $totalPlannedHours;
                $this->info("Free Hours: {$freeHours}h");
                
            } catch (\Exception $e) {
                $this->error("Error: " . $e->getMessage());
                $this->error($e->getTraceAsString());
            }
            
            $this->info("==========================================");
            $this->line("");
        }
        
        return 0;
    }
}























