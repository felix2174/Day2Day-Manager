<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Assignment;

echo "=== HANNES BOEKHOFF ASSIGNMENTS (Employee ID 3) ===\n\n";

$assignments = Assignment::where('employee_id', 3)
    ->with('project')
    ->orderBy('id')
    ->get();

echo sprintf("Total Assignments: %d\n\n", $assignments->count());

foreach ($assignments as $assignment) {
    $project = $assignment->project;
    echo sprintf(
        "ID: %3d | %-35s | Ass-Start: %-12s | Ass-End: %-12s | Proj-End: %-12s\n",
        $assignment->id,
        substr($project->name ?? 'Unknown', 0, 35),
        $assignment->start_date ?? 'NULL',
        $assignment->end_date ?? 'NULL',
        $project->end_date ?? 'NULL'
    );
}
