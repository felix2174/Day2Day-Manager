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
    protected $description = 'Synchronize all data from MOCO API (employees, projects, activities, absences, contracts)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting full MOCO synchronization...');
        $this->newLine();

        $startTime = microtime(true);
        $results = [];

        // Step 1: Sync employees first (needed for all other syncs)
        $this->info('Step 1/5: 👥 Syncing employees...');
        $employeesResult = $this->call('moco:sync-employees');
        $results['employees'] = $employeesResult === Command::SUCCESS;

        if ($employeesResult !== Command::SUCCESS) {
            $this->error('❌ Employee synchronization failed. Continuing with other syncs...');
        } else {
            $this->info('✅ Employees synchronized successfully!');
        }

        $this->newLine();

        // Step 2: Sync projects (needed for activities and contracts)
        $this->info('Step 2/5: 📁 Syncing projects...');
        $projectsResult = $this->call('moco:sync-projects');
        $results['projects'] = $projectsResult === Command::SUCCESS;

        if ($projectsResult !== Command::SUCCESS) {
            $this->error('❌ Project synchronization failed. Continuing with other syncs...');
        } else {
            $this->info('✅ Projects synchronized successfully!');
        }

        $this->newLine();

        // Step 3: Sync activities/time entries
        $this->info('Step 3/5: ⏱️  Syncing time entries...');
        $activitiesResult = $this->call('moco:sync-activities', [
            '--days' => $this->option('days'),
        ]);
        $results['activities'] = $activitiesResult === Command::SUCCESS;

        if ($activitiesResult !== Command::SUCCESS) {
            $this->error('❌ Time entries synchronization failed. Continuing with other syncs...');
        } else {
            $this->info('✅ Time entries synchronized successfully!');
        }

        $this->newLine();

        // Step 4: Sync absences
        $this->info('Step 4/5: 🏖️  Syncing absences...');
        $absencesResult = $this->call('sync:moco-absences', [
            '--days' => $this->option('days'),
        ]);
        $results['absences'] = $absencesResult === Command::SUCCESS;

        if ($absencesResult !== Command::SUCCESS) {
            $this->error('❌ Absences synchronization failed. Continuing with other syncs...');
        } else {
            $this->info('✅ Absences synchronized successfully!');
        }

        $this->newLine();

        // Step 5: Sync contracts/assignments
        $this->info('Step 5/5: 📋 Syncing employee assignments (contracts)...');
        $contractsResult = $this->call('sync:moco-contracts');
        $results['contracts'] = $contractsResult === Command::SUCCESS;

        if ($contractsResult !== Command::SUCCESS) {
            $this->error('❌ Contracts synchronization failed.');
        } else {
            $this->info('✅ Contracts synchronized successfully!');
        }

        $this->newLine(2);

        // Summary
        $duration = round(microtime(true) - $startTime, 2);
        $successCount = count(array_filter($results));
        $totalCount = count($results);

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->info("📊 SYNC SUMMARY");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        
        foreach ($results as $type => $success) {
            $icon = $success ? '✅' : '❌';
            $status = $success ? 'Success' : 'Failed';
            $this->line("  {$icon} " . ucfirst($type) . ": {$status}");
        }

        $this->newLine();
        $this->line("  🎯 Success Rate: {$successCount}/{$totalCount} (" . round(($successCount/$totalCount)*100) . "%)");
        $this->line("  ⏱️  Duration: {$duration}s");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Return success if at least 80% succeeded
        return ($successCount / $totalCount) >= 0.8 ? Command::SUCCESS : Command::FAILURE;
    }
}

