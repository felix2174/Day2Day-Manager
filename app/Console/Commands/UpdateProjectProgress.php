<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;

class UpdateProjectProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projects:update-progress {--force : Force update even if progress hasn\'t changed}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the progress of all projects based on time entries and other factors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating project progress...');
        
        $projects = Project::all();
        $updated = 0;
        $unchanged = 0;
        
        $progressBar = $this->output->createProgressBar($projects->count());
        $progressBar->start();
        
        foreach ($projects as $project) {
            $oldProgress = $project->progress;
            $newProgress = $project->calculateAutomaticProgress();
            
            if ($this->option('force') || abs($oldProgress - $newProgress) > 0.1) {
                $project->updateProgress();
                $updated++;
                
                if ($oldProgress != $newProgress) {
                    $this->line("\nProject '{$project->name}': {$oldProgress}% → {$newProgress}%");
                }
            } else {
                $unchanged++;
            }
            
            $progressBar->advance();
        }
        
        $progressBar->finish();
        
        $this->newLine();
        $this->info("Progress update completed!");
        $this->info("Updated: {$updated} projects");
        $this->info("Unchanged: {$unchanged} projects");
        
        return Command::SUCCESS;
    }
}