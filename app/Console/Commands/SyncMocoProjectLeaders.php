<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\MocoService;
use App\Services\MocoSyncLogger;
use Illuminate\Console\Command;

class SyncMocoProjectLeaders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:sync-project-leaders {--dry-run : Show what would be synced without saving}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync project leaders and contract users from MOCO projects (includes inactive employees)';

    /**
     * Execute the console command.
     */
    public function handle(MocoService $mocoService, MocoSyncLogger $logger): int
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->warn('🔍 DRY RUN MODE - No changes will be saved');
        }
        
        $this->info('Starting MOCO project leaders synchronization...');

        try {
            // Test connection first
            if (!$mocoService->testConnection()) {
                $this->error('❌ Failed to connect to MOCO API. Please check your credentials.');
                return Command::FAILURE;
            }

            // Start logging (skip in dry-run)
            if (!$isDryRun) {
                $logger->start('project_leaders', []);
            }

            // Get all projects
            $projects = $mocoService->getProjects();
            $this->info('✅ Found ' . count($projects) . ' projects in MOCO');
            
            // Collect unique users from projects
            $uniqueUsers = [];
            $bar = $this->output->createProgressBar(count($projects));
            $bar->start();
            
            foreach ($projects as $project) {
                // Get full project details
                $fullProject = $mocoService->getProject($project['id']);
                
                if (!$fullProject) {
                    $bar->advance();
                    continue;
                }
                
                // Extract leader
                if (isset($fullProject['leader']) && isset($fullProject['leader']['id'])) {
                    $leader = $fullProject['leader'];
                    $userId = $leader['id'];
                    
                    if (!isset($uniqueUsers[$userId])) {
                        $uniqueUsers[$userId] = [
                            'id' => $userId,
                            'firstname' => $leader['firstname'] ?? 'Unknown',
                            'lastname' => $leader['lastname'] ?? '',
                            'active' => $leader['active'] ?? false, // Default: inactive if not specified
                            'source' => 'leader',
                        ];
                    }
                }
                
                // Extract contract users
                if (isset($fullProject['contracts']) && is_array($fullProject['contracts'])) {
                    foreach ($fullProject['contracts'] as $contract) {
                        if (!isset($contract['user']) || !isset($contract['user']['id'])) {
                            continue;
                        }
                        
                        $user = $contract['user'];
                        $userId = $user['id'];
                        
                        if (!isset($uniqueUsers[$userId])) {
                            $uniqueUsers[$userId] = [
                                'id' => $userId,
                                'firstname' => $user['firstname'] ?? 'Unknown',
                                'lastname' => $user['lastname'] ?? '',
                                'active' => $user['active'] ?? false,
                                'source' => 'contract',
                            ];
                        }
                    }
                }
                
                $bar->advance();
            }
            
            $bar->finish();
            $this->newLine(2);
            
            $this->info('ℹ️  Found ' . count($uniqueUsers) . ' unique users in projects');
            
            // Sync users to database
            $created = 0;
            $updated = 0;
            $skipped = 0;
            
            foreach ($uniqueUsers as $userData) {
                $employee = Employee::where('moco_id', $userData['id'])->first();
                
                $employeeData = [
                    'first_name' => $userData['firstname'],
                    'last_name' => $userData['lastname'],
                    'is_active' => $userData['active'],
                    'moco_id' => $userData['id'],
                    'department' => 'Keine Abteilung', // Default, will be updated by main sync
                    'weekly_capacity' => 40.0, // Default
                ];
                
                if ($employee) {
                    // Update existing employee (only if not active to preserve data)
                    if (!$employee->is_active && $userData['active']) {
                        if (!$isDryRun) {
                            $employee->update(['is_active' => true]);
                        }
                        $this->line("🔄 Updated: {$userData['firstname']} {$userData['lastname']} (now active)");
                        $updated++;
                    } else {
                        $this->line("⏭️  Skipped: {$userData['firstname']} {$userData['lastname']} (already exists)");
                        $skipped++;
                    }
                } else {
                    // Create new employee
                    if (!$isDryRun) {
                        Employee::create($employeeData);
                    }
                    $status = $userData['active'] ? '✅ Active' : '⚪ Inactive';
                    $this->line("➕ Created: {$userData['firstname']} {$userData['lastname']} ({$status})");
                    $created++;
                }
            }
            
            $this->newLine();
            $this->info("🎉 Synchronization completed!");
            $this->table(
                ['Status', 'Count', 'Details'],
                [
                    ['✅ Created', $created, 'New employees from projects'],
                    ['🔄 Updated', $updated, 'Existing employees updated'],
                    ['⏭️ Skipped', $skipped, 'Already in database'],
                ]
            );
            
            // Complete logging
            if (!$isDryRun) {
                $logger->complete(count($uniqueUsers), $created, $updated, $skipped);
            }
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Error during synchronization: ' . $e->getMessage());
            if (!$isDryRun) {
                $logger->fail($e->getMessage());
            }
            return Command::FAILURE;
        }
    }
}
