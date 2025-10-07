<?php

namespace App\Console\Commands;

use App\Models\TimeEntry;
use App\Models\Employee;
use App\Models\Project;
use App\Services\MocoService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SyncMocoActivities extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:sync-activities 
                            {--from= : Start date (YYYY-MM-DD)}
                            {--to= : End date (YYYY-MM-DD)}
                            {--days=30 : Number of days to sync (default: 30)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize time entries (activities) from MOCO API';

    /**
     * Execute the console command.
     */
    public function handle(MocoService $mocoService): int
    {
        $this->info('Starting MOCO activities synchronization...');

        try {
            // Test connection first
            if (!$mocoService->testConnection()) {
                $this->error('Failed to connect to MOCO API. Please check your credentials.');
                return Command::FAILURE;
            }

            // Determine date range
            $from = $this->option('from') 
                ? Carbon::parse($this->option('from')) 
                : Carbon::now()->subDays($this->option('days'));
            
            $to = $this->option('to') 
                ? Carbon::parse($this->option('to')) 
                : Carbon::now();

            $params = [
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ];

            $this->info("Syncing activities from {$params['from']} to {$params['to']}");

            $mocoActivities = $mocoService->getActivities($params);
            $this->info('Found ' . count($mocoActivities) . ' activities in MOCO');

            $synced = 0;
            $created = 0;
            $updated = 0;
            $skipped = 0;

            foreach ($mocoActivities as $mocoActivity) {
                // Find employee
                $employee = Employee::where('moco_id', $mocoActivity['user']['id'])->first();
                if (!$employee) {
                    $this->warn("Employee not found for MOCO user ID: {$mocoActivity['user']['id']}");
                    $skipped++;
                    continue;
                }

                // Find project
                $project = null;
                if (isset($mocoActivity['project']) && isset($mocoActivity['project']['id'])) {
                    $project = Project::where('moco_id', $mocoActivity['project']['id'])->first();
                    if (!$project) {
                        $this->warn("Project not found for MOCO project ID: {$mocoActivity['project']['id']}");
                        $skipped++;
                        continue;
                    }
                }

                // Find or create time entry
                $timeEntry = TimeEntry::where('moco_id', $mocoActivity['id'])->first();

                $timeEntryData = [
                    'employee_id' => $employee->id,
                    'project_id' => $project?->id,
                    'date' => $mocoActivity['date'],
                    'hours' => $mocoActivity['hours'],
                    'description' => $mocoActivity['description'] ?? '',
                    'billable' => $mocoActivity['billable'] ?? true,
                    'moco_id' => $mocoActivity['id'],
                ];

                if ($timeEntry) {
                    $timeEntry->update($timeEntryData);
                    $updated++;
                } else {
                    TimeEntry::create($timeEntryData);
                    $created++;
                }

                $synced++;
            }

            $this->newLine();
            $this->info("Synchronization completed!");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total synced', $synced],
                    ['Created', $created],
                    ['Updated', $updated],
                    ['Skipped', $skipped],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

