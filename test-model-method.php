<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test Project Model Method
$project = \App\Models\Project::with(['assignments.employee', 'responsible'])->find(10);

if (!$project) {
    echo json_encode(['error' => 'Project ID 10 not found'], JSON_PRETTY_PRINT);
    exit;
}

$result = [
    'id' => $project->id,
    'name' => $project->name,
    'responsible_id' => $project->responsible_id,
    'responsible_name' => $project->responsible ? 
        $project->responsible->first_name . ' ' . $project->responsible->last_name : 
        null,
    'assignments_count' => $project->assignments->count(),
    'assignments' => $project->assignments->map(function($a) {
        return [
            'employee' => $a->employee ? 
                $a->employee->first_name . ' ' . $a->employee->last_name : 
                null,
            'weekly_hours' => $a->weekly_hours,
        ];
    })->toArray(),
    'getAssignedPersonsList' => $project->getAssignedPersonsList(),
    'getAssignedPersonsString' => $project->getAssignedPersonsString(),
    'hasAssignedPersons' => $project->hasAssignedPersons(),
];

echo json_encode($result, JSON_PRETTY_PRINT);
