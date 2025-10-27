<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Assignment;
use App\Models\Employee;

class CleanupTestProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gantt:cleanup-test-project';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Removes the Gantt test project and associated employees and assignments.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting cleanup of Gantt test project...');

        // Find the project
        $project = Project::where('name', 'Gantt-Testprojekt')->first();

        if (!$project) {
            $this->info('Gantt test project not found. Nothing to clean up.');
            return 0;
        }

        // Get all assignments for this project
        $assignments = Assignment::where('project_id', $project->id)->get();
        $employeeIdsToDelete = [];

        foreach ($assignments as $assignment) {
            $employeeId = $assignment->employee_id;
            // Check if this employee has assignments in other projects
            $otherAssignmentsCount = Assignment::where('employee_id', $employeeId)
                                               ->where('project_id', '!=', $project->id)
                                               ->count();

            // If the employee has no other assignments, mark for deletion
            if ($otherAssignmentsCount === 0) {
                $employeeIdsToDelete[] = $employeeId;
            }
        }
        
        // Delete assignments for the project
        $assignmentCount = $assignments->count();
        if ($assignmentCount > 0) {
            Assignment::where('project_id', $project->id)->delete();
            $this->info("Deleted {$assignmentCount} assignments.");
        }

        // Delete employees who are only in this project
        $employeeIdsToDelete = array_unique($employeeIdsToDelete);
        if (!empty($employeeIdsToDelete)) {
            $employeeCount = count($employeeIdsToDelete);
            Employee::whereIn('id', $employeeIdsToDelete)->delete();
            $this->info("Deleted {$employeeCount} associated employees.");
        }

        // Delete the project itself
        $projectName = $project->name;
        $project->delete();
        $this->info("Deleted project: '{$projectName}'.");
        
        $this->info('Cleanup complete.');
        return 0;
    }
}
