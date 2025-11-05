<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use App\Models\Assignment;

echo "=== PROJEKT-DATEN ANALYSE ===\n\n";

echo "Projekte mit end_date: " . Project::whereNotNull('end_date')->count() . "\n";
echo "Projekte ohne end_date: " . Project::whereNull('end_date')->count() . "\n\n";

echo "=== ASSIGNMENTS-DATEN ANALYSE ===\n\n";
echo "Assignments mit end_date: " . Assignment::whereNotNull('end_date')->count() . "\n";
echo "Assignments ohne end_date: " . Assignment::whereNull('end_date')->count() . "\n\n";

echo "=== BEISPIEL: Hannes Boekhoff (ID 3) ===\n\n";
$hannesAssignments = Assignment::where('employee_id', 3)
    ->with('project')
    ->take(10)
    ->get();

foreach ($hannesAssignments as $assignment) {
    $project = $assignment->project;
    echo sprintf(
        "Projekt: %-40s | Projekt-Ende: %-12s | Assignment-Ende: %-12s\n",
        substr($project->name ?? 'Unknown', 0, 40),
        $project->end_date ?? 'NULL',
        $assignment->end_date ?? 'NULL'
    );
}
