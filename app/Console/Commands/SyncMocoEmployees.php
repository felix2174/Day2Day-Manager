<?php

namespace App\Console\Commands;

use App\Models\Employee;
use App\Services\MocoService;
use Illuminate\Console\Command;

class SyncMocoEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:sync-employees {--active : Only sync active employees}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize employees (users) from MOCO API';

    /**
     * Execute the console command.
     */
    public function handle(MocoService $mocoService): int
    {
        $this->info('Starting MOCO employee synchronization...');

        try {
            // Test connection first
            if (!$mocoService->testConnection()) {
                $this->error('Failed to connect to MOCO API. Please check your credentials.');
                return Command::FAILURE;
            }

            $params = [];
            if ($this->option('active')) {
                $params['active'] = true;
            }

            try {
                $mocoUsers = $mocoService->getUsers($params);
            } catch (\Throwable $e) {
                $this->error('Failed to fetch users from MOCO: ' . $e->getMessage());
                return Command::FAILURE;
            }
            $this->info('Found ' . count($mocoUsers) . ' employees in MOCO');

            $synced = 0;
            $created = 0;
            $updated = 0;

            foreach ($mocoUsers as $mocoUser) {
                // Find or create employee
                if (!isset($mocoUser['id'])) { continue; }
                $employee = Employee::where('moco_id', $mocoUser['id'])->first();

                $employeeData = [
                    'first_name' => $mocoUser['firstname'] ?? ($mocoUser['first_name'] ?? 'Unknown'),
                    'last_name' => $mocoUser['lastname'] ?? ($mocoUser['last_name'] ?? ''),
                    'department' => ($mocoUser['unit']['name'] ?? ($mocoUser['department'] ?? 'Keine Abteilung')),
                    'weekly_capacity' => $this->calculateWeeklyCapacity($mocoUser),
                    'is_active' => (bool)($mocoUser['active'] ?? true),
                    'moco_id' => $mocoUser['id'],
                ];

                if ($employee) {
                    $employee->update($employeeData);
                    $updated++;
                    $this->line("Updated: {$mocoUser['firstname']} {$mocoUser['lastname']}");
                } else {
                    Employee::create($employeeData);
                    $created++;
                    $this->line("Created: {$mocoUser['firstname']} {$mocoUser['lastname']}");
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
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Calculate weekly capacity from MOCO user data
     */
    protected function calculateWeeklyCapacity(array $mocoUser): float
    {
        // MOCO stores capacity per week in hours
        // Default to 40 hours if not set
        return $mocoUser['work_time_per_day'] ?? 8.0;
    }
}

