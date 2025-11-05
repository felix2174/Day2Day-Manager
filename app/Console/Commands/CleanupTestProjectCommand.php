<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Assignment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class CleanupTestProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gantt:cleanup-test-project 
                            {--dry-run : Show what would be deleted without actually deleting}
                            {--force : Force deletion without confirmation}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SAFELY removes ONLY test/import data. NEVER deletes manual or MOCO-synced data.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧹 Safe Cleanup: Test/Import Data Only');
        $this->newLine();

        // CRITICAL: Only delete data marked as 'import'
        $projectsToDelete = Project::where('source', 'import')->get();
        $employeesToDelete = Employee::where('source', 'import')->get();

        // Also find specific test projects by name (backward compatibility)
        $testProjectNames = ['Gantt-Testprojekt', 'Test Project', 'Demo Project'];
        $namedTestProjects = Project::whereIn('name', $testProjectNames)
                                    ->where('source', '!=', 'moco') // NEVER delete MOCO data
                                    ->get();

        $allProjectsToDelete = $projectsToDelete->merge($namedTestProjects)->unique('id');

        if ($allProjectsToDelete->isEmpty() && $employeesToDelete->isEmpty()) {
            $this->info('✅ No test/import data found. Database is clean!');
            return Command::SUCCESS;
        }

        // Show what will be deleted
        $this->warn('📋 Items marked for deletion:');
        $this->newLine();

        if ($allProjectsToDelete->isNotEmpty()) {
            $this->line('🗂️  Projects (' . $allProjectsToDelete->count() . '):');
            foreach ($allProjectsToDelete as $project) {
                $this->line("  - [{$project->source}] {$project->name}");
            }
            $this->newLine();
        }

        if ($employeesToDelete->isNotEmpty()) {
            $this->line('👥 Employees (' . $employeesToDelete->count() . '):');
            foreach ($employeesToDelete as $employee) {
                $this->line("  - [{$employee->source}] {$employee->first_name} {$employee->last_name}");
            }
            $this->newLine();
        }

        // Safety checks
        $manualProjects = $allProjectsToDelete->where('source', 'manual')->count();
        $mocoProjects = $allProjectsToDelete->where('source', 'moco')->count();
        
        if ($manualProjects > 0 || $mocoProjects > 0) {
            $this->error('❌ SAFETY ABORT: Cannot delete manual or MOCO data!');
            $this->error("   Manual projects: {$manualProjects}");
            $this->error("   MOCO projects: {$mocoProjects}");
            return Command::FAILURE;
        }

        // Dry-run mode
        if ($this->option('dry-run')) {
            $this->info('🔍 DRY RUN: No data was deleted (use without --dry-run to actually delete)');
            return Command::SUCCESS;
        }

        // Confirmation prompt (unless --force)
        if (!$this->option('force')) {
            if (!$this->confirm('⚠️  Are you sure you want to delete this test data?')) {
                $this->info('Cleanup cancelled.');
                return Command::SUCCESS;
            }
        }

        // Perform deletion in transaction
        DB::transaction(function() use ($allProjectsToDelete, $employeesToDelete) {
            $deletedAssignments = 0;
            $deletedProjects = 0;
            $deletedEmployees = 0;

            // Delete assignments for these projects
            foreach ($allProjectsToDelete as $project) {
                $count = Assignment::where('project_id', $project->id)->count();
                Assignment::where('project_id', $project->id)->delete();
                $deletedAssignments += $count;
            }

            // Delete projects
            foreach ($allProjectsToDelete as $project) {
                $project->delete(); // Soft-delete
                $deletedProjects++;
            }

            // Delete employees
            foreach ($employeesToDelete as $employee) {
                $employee->delete(); // Soft-delete
                $deletedEmployees++;
            }

            $this->info("✅ Deleted {$deletedAssignments} assignments");
            $this->info("✅ Deleted {$deletedProjects} projects");
            $this->info("✅ Deleted {$deletedEmployees} employees");
        });

        $this->newLine();
        $this->info('✅ Cleanup complete!');
        
        return Command::SUCCESS;
    }
}
