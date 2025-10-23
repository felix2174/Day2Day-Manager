<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SyncMocoAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:sync-all 
                            {--active : Only sync active items}
                            {--days=30 : Number of days to sync for activities}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize all data from MOCO API (employees, projects, activities)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting full MOCO synchronization...');
        $this->newLine();

        // Step 1: Sync employees first (needed for projects and activities)
        $this->info('Step 1/3: Syncing employees...');
        $employeesResult = $this->call('moco:sync-employees', [
            '--active' => $this->option('active'),
        ]);

        if ($employeesResult !== Command::SUCCESS) {
            $this->error('Employee synchronization failed. Aborting...');
            return Command::FAILURE;
        }

        $this->newLine();

        // Step 2: Sync projects
        $this->info('Step 2/3: Syncing projects...');
        $projectsResult = $this->call('moco:sync-projects', [
            '--active' => $this->option('active'),
        ]);

        if ($projectsResult !== Command::SUCCESS) {
            $this->error('Project synchronization failed. Aborting...');
            return Command::FAILURE;
        }

        $this->newLine();

        // Step 3: Sync activities
        $this->info('Step 3/3: Syncing activities...');
        $activitiesResult = $this->call('moco:sync-activities', [
            '--days' => $this->option('days'),
        ]);

        if ($activitiesResult !== Command::SUCCESS) {
            $this->error('Activities synchronization failed. Aborting...');
            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('✓ Full MOCO synchronization completed successfully!');

        return Command::SUCCESS;
    }
}

