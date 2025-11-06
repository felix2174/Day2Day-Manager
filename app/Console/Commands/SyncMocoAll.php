<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncMocoAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:moco-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all data from MOCO (employees, projects, contracts, absences, time entries)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting full MOCO synchronization...');
        $this->newLine();

        $commands = [
            'sync:moco-employees' => 'Syncing Employees',
            'sync:moco-projects' => 'Syncing Projects',
            'sync:moco-contracts' => 'Syncing Contracts',
            'sync:moco-absences' => 'Syncing Absences',
            'sync:moco-time-entries' => 'Syncing Time Entries',
            'sync:responsible-to-assignments' => 'Syncing Responsible Assignments',
        ];

        $startTime = microtime(true);
        $successCount = 0;
        $errorCount = 0;

        foreach ($commands as $command => $description) {
            $this->info("⏳ {$description}...");
            
            try {
                $exitCode = Artisan::call($command);
                
                if ($exitCode === 0) {
                    $this->line("   ✅ {$description} completed successfully");
                    $successCount++;
                } else {
                    $this->error("   ❌ {$description} failed with exit code {$exitCode}");
                    $errorCount++;
                }
            } catch (\Exception $e) {
                $this->error("   ❌ {$description} failed: {$e->getMessage()}");
                $errorCount++;
            }
            
            $this->newLine();
        }

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        $this->newLine();
        $this->info('═══════════════════════════════════════════════════');
        $this->info("✨ Full MOCO synchronization completed in {$duration} seconds");
        $this->info("   ✅ Successful: {$successCount}");
        
        if ($errorCount > 0) {
            $this->error("   ❌ Failed: {$errorCount}");
        }
        
        $this->info('═══════════════════════════════════════════════════');

        return $errorCount === 0 ? 0 : 1;
    }
}
