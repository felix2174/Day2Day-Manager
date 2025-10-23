<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use App\Models\Project;

class DebugGantt extends Command
{
    protected $signature = 'gantt:debug';
    protected $description = 'Quick diagnostics for Gantt frontend hooks and data availability.';

    public function handle(): int
    {
        $jsPath = public_path('build/assets/app-BDUGe7Bi.js');
        if (File::exists($jsPath)) {
            $this->info('JavaScript bundle found: ' . $jsPath);
            $snippet = File::get($jsPath, true);
            if (str_contains($snippet, 'ganttInit')) {
                $this->info('Found ganttInit definition inside bundle.');
            } else {
                $this->warn('ganttInit definition not found in current bundle.');
            }
        } else {
            $this->error('Bundle file not found: ' . $jsPath);
        }

        $countProjects = Project::count();
        $this->info('Projects in database: ' . $countProjects);

        return Command::SUCCESS;
    }
}
