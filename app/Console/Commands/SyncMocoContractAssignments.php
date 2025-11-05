<?php

namespace App\Console\Commands;

use App\Models\Assignment;
use App\Models\Employee;
use App\Models\Project;
use App\Services\MocoService;
use App\Services\MocoSyncLogger;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SyncMocoContractAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:sync-contract-assignments 
                            {--dry-run : Show what would be synced without saving}
                            {--project= : Sync only specific project by MOCO ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync assignment start/end dates from MOCO project contracts';

    /**
     * Execute the console command.
     */
    public function handle(MocoService $mocoService, MocoSyncLogger $logger): int
    {
        $isDryRun = $this->option('dry-run');
        $specificProject = $this->option('project');
        
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be saved');
        }
        
        $this->info('Starting MOCO contract assignments synchronization...');

        try {
            // Test connection first
            if (!$mocoService->testConnection()) {
                $this->error('❌ Cannot connect to MOCO API. Please check your configuration.');
                return Command::FAILURE;
            }

            // Start logging (skip in dry-run)
            if (!$isDryRun) {
                $logger->start('moco:sync-contract-assignments');
            }

            // Get projects
            if ($specificProject) {
                $projects = [$mocoService->getProject($specificProject)];
                $this->info('ℹ️  Syncing specific project: ' . $specificProject);
            } else {
                $projects = $mocoService->getProjects();
                $this->info('✅ Found ' . count($projects) . ' projects in MOCO');
            }
            
            // Track statistics
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $errors = 0;
            
            $bar = $this->output->createProgressBar(count($projects));
            $bar->start();
            
            foreach ($projects as $project) {
                try {
                    // Get full project details with contracts
                    $fullProject = $mocoService->getProject($project['id']);
                    
                    if (!$fullProject || !isset($fullProject['contracts'])) {
                        $bar->advance();
                        continue;
                    }
                    
                    // Find local project
                    $localProject = Project::where('moco_id', $fullProject['id'])->first();
                    if (!$localProject) {
                        $bar->advance();
                        continue;
                    }
                    
                    // Process each contract
                    foreach ($fullProject['contracts'] as $contract) {
                        // FIXED: Contracts have user_id, not user object
                        if (!isset($contract['user_id'])) {
                            continue;
                        }
                        
                        $mocoUserId = $contract['user_id'];
                        
                        // Find local employee
                        $employee = Employee::where('moco_id', $mocoUserId)->first();
                        if (!$employee) {
                            continue; // Silent skip - employee might not be synced yet
                        }
                        
                        // FIXED: Use project start/end dates (contracts don't have their own dates in MOCO API)
                        $startDate = $fullProject['start_date'] ?? null;
                        $endDate = $fullProject['finish_date'] ?? null;
                        
                        // Skip if no start date (required field in assignments table)
                        if (!$startDate) {
                            continue;
                        }
                        
                        $budget = $contract['budget'] ?? 0;
                        
                        // Calculate weekly hours from project budget and timeframe
                        $weeklyHours = 20; // Default fallback
                        
                        if ($budget > 0 && $startDate) {
                            $start = Carbon::parse($startDate);
                            $end = $endDate ? Carbon::parse($endDate) : Carbon::now()->addMonths(6);
                            $totalWeeks = max(1, $start->diffInWeeks($end));
                            $weeklyHours = round($budget / $totalWeeks, 2);
                            $weeklyHours = min($weeklyHours, $employee->weekly_capacity ?? 40);
                        }
                        
                        // Prepare assignment data
                        $assignmentData = [
                            'project_id' => $localProject->id,
                            'employee_id' => $employee->id,
                        ];
                        
                        $updateData = [
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'weekly_hours' => max($weeklyHours, 1), // Min 1h
                            'task_name' => 'Aus MOCO-Contract',
                            'moco_contract_id' => $contract['id'] ?? null,
                        ];
                        
                        // Check if assignment exists
                        $existingAssignment = Assignment::where('project_id', $localProject->id)
                            ->where('employee_id', $employee->id)
                            ->first();
                        
                        if ($existingAssignment) {
                            // Update only if MOCO data is newer or more complete
                            $shouldUpdate = false;
                            
                            if (!$existingAssignment->start_date && $startDate) {
                                $shouldUpdate = true;
                            }
                            if (!$existingAssignment->end_date && $endDate) {
                                $shouldUpdate = true;
                            }
                            if ($existingAssignment->task_name === 'Aus MOCO-Contract' || 
                                $existingAssignment->moco_contract_id === null) {
                                $shouldUpdate = true;
                            }
                            
                            if ($shouldUpdate) {
                                if (!$isDryRun) {
                                    $existingAssignment->update($updateData);
                                }
                                $updated++;
                            } else {
                                $skipped++;
                            }
                        } else {
                            // Create new assignment
                            if (!$isDryRun) {
                                Assignment::create(array_merge($assignmentData, $updateData));
                            }
                            $created++;
                        }
                    }
                    
                } catch (\Exception $e) {
                    $this->error("❌ Error processing project {$project['id']}: " . $e->getMessage());
                    $errors++;
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            $this->info("🎉 Contract synchronization completed!");
            $this->table(
                ['Status', 'Count', 'Details'],
                [
                    ['✅ Created', $created, 'New assignments from contracts'],
                    ['🔄 Updated', $updated, 'Existing assignments updated'],
                    ['⏭️ Skipped', $skipped, 'No update needed'],
                    ['❌ Errors', $errors, 'Failed to process'],
                ]
            );
            
            if ($created > 0 || $updated > 0) {
                $this->newLine();
                $this->info('💡 Tip: Refresh Gantt diagram to see updated timelines');
            }
            
            // Complete logging
            if (!$isDryRun) {
                $logger->complete($created + $updated + $skipped, $created, $updated, $errors);
            }
            
            return $errors > 0 ? Command::FAILURE : Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error during synchronization: ' . $e->getMessage());
            if (!$isDryRun) {
                $logger->fail($e->getMessage());
            }
            return Command::FAILURE;
        }
    }
}
