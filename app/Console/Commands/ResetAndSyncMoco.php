<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class ResetAndSyncMoco extends Command
{
    protected $signature = 'moco:reset-and-sync {--days=60 : Activities lookback window in days} {--active : Only sync active entities}';

    protected $description = 'Delete dummy/local data and import employees, projects and activities from MOCO';

    public function handle(): int
    {
        $this->warn('This will delete local dummy data (employees, projects, assignments, time entries, absences, teams & pivots).');

        try {
            DB::beginTransaction();

            // Disable FK checks for truncate order simplicity
            DB::statement('PRAGMA foreign_keys = OFF'); // SQLite
            try { DB::statement('SET FOREIGN_KEY_CHECKS=0'); } catch (\Throwable $e) {}

            // Truncate in safe order
            $tables = [
                'time_entries',
                'assignments',
                'absences',
                'team_assignments',
                'teams',
                'projects',
                'employees',
            ];

            foreach ($tables as $table) {
                if (DB::getSchemaBuilder()->hasTable($table)) {
                    DB::table($table)->truncate();
                    $this->line("Truncated: {$table}");
                }
            }

            // Re-enable FK checks
            try { DB::statement('SET FOREIGN_KEY_CHECKS=1'); } catch (\Throwable $e) {}
            DB::statement('PRAGMA foreign_keys = ON');

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->error('Reset failed: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('Starting MOCO import...');

        // Run syncs in proper order
        $employeesResult = Artisan::call('moco:sync-employees', [
            '--active' => $this->option('active') ?? false,
        ]);
        if ($employeesResult !== Command::SUCCESS) {
            $this->error('Employee sync failed.');
            return Command::FAILURE;
        }

        $projectsResult = Artisan::call('moco:sync-projects', [
            '--active' => $this->option('active') ?? false,
        ]);
        if ($projectsResult !== Command::SUCCESS) {
            $this->error('Project sync failed.');
            return Command::FAILURE;
        }

        $activitiesResult = Artisan::call('moco:sync-activities', [
            '--days' => (int)$this->option('days'),
        ]);
        if ($activitiesResult !== Command::SUCCESS) {
            $this->error('Activities sync failed.');
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('✓ Reset + MOCO import completed');
        return Command::SUCCESS;
    }
}


