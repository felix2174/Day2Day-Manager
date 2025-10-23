<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Assignment;
use Illuminate\Support\Facades\DB;

class GanttTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Use a transaction to ensure data integrity
        DB::transaction(function () {
            // 1. Clean up previous test data first
            $this->command->info('Cleaning up old Gantt test data...');
            $testProject = Project::where('name', 'Gantt-Testprojekt')->first();
            if ($testProject) {
                // Find employees that only belong to this test project
                $assignments = Assignment::where('project_id', $testProject->id)->get();
                $employeeIdsToDelete = [];

                foreach ($assignments as $assignment) {
                    $employeeId = $assignment->employee_id;
                    $otherAssignmentsCount = Assignment::where('employee_id', $employeeId)
                                                       ->where('project_id', '!=', $testProject->id)
                                                       ->count();

                    if ($otherAssignmentsCount === 0) {
                        $employeeIdsToDelete[] = $employeeId;
                    }
                }

                // Delete data
                Assignment::where('project_id', $testProject->id)->delete();
                if (!empty($employeeIdsToDelete)) {
                    Employee::whereIn('id', array_unique($employeeIdsToDelete))->delete();
                }
                $testProject->delete();
                $this->command->info('Cleanup complete.');
            }

            // 2. Create the test project
            $this->command->info('Creating new Gantt test project...');
            $project = Project::create([
                'name' => 'Gantt-Testprojekt',
                'description' => 'Ein Projekt zum Testen und Entwickeln der neuen Gantt-Funktionen.',
                'start_date' => '1970-01-01', // Set a very old start date to always be on top
                'end_date' => now()->addMonths(6)->endOfMonth(),
                'status' => 'in_progress',
            ]);

            // 3. Create realistic test employees
            $employees = Employee::factory()->count(5)->create();
            $this->command->info("Created {$employees->count()} test employees.");

            // 4. Create meaningful tasks (assignments) for each employee
            foreach ($employees as $employee) {
                $taskCount = rand(2, 4);
                $lastTaskEndDate = now()->startOfMonth()->addDays(rand(0, 10));

                for ($i = 0; $i < $taskCount; $i++) {
                    $startDate = $lastTaskEndDate->copy()->addDays(rand(1, 5));
                    $endDate = $startDate->copy()->addDays(rand(7, 20));

                    Assignment::factory()->create([
                        'project_id' => $project->id,
                        'employee_id' => $employee->id,
                        'task_name' => "Konzeptphase " . ($i + 1),
                        'task_description' => "Detailplanung für die Konzeptphase " . ($i + 1) . " für {$employee->first_name}.",
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'display_order' => $i + 1,
                    ]);

                    $lastTaskEndDate = $endDate;
                }
            }
            $this->command->info("Created tasks for all employees.");
        });

        $this->command->info('Gantt test project has been successfully seeded!');
    }
}
