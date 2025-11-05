<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Project;
use Carbon\Carbon;

$today = Carbon::today()->toDateString();

echo "=== PROJEKT END-DATES ANALYSE ===\n\n";
echo "Heute: $today\n\n";

echo "Projekte mit end_date = HEUTE: " . Project::whereDate('end_date', $today)->count() . "\n";
echo "Projekte mit end_date < HEUTE: " . Project::whereDate('end_date', '<', $today)->count() . "\n";
echo "Projekte mit end_date > HEUTE: " . Project::whereDate('end_date', '>', $today)->count() . "\n";
echo "Projekte mit end_date = NULL: " . Project::whereNull('end_date')->count() . "\n\n";

echo "=== PROJEKTE DIE GENAU HEUTE ENDEN ===\n\n";
$todayProjects = Project::whereDate('end_date', $today)->get(['id', 'name', 'end_date', 'moco_id']);

foreach ($todayProjects as $project) {
    echo sprintf(
        "ID: %3d | MOCO: %8s | End: %s | %s\n",
        $project->id,
        $project->moco_id ?? 'NULL',
        $project->end_date,
        $project->name
    );
}

echo "\n=== BEISPIEL: Hannes' rote Projekte ===\n\n";

// Get projects via MOCO assignments for Hannes
$hannesProjects = Project::whereHas('assignments', function($q) {
    // This won't work if no assignments... let me try differently
})->orWhere(function($q) use ($today) {
    $q->whereDate('end_date', $today);
})->take(5)->get(['id', 'name', 'end_date', 'start_date']);

foreach ($hannesProjects as $project) {
    echo sprintf(
        "%s | Start: %s | Ende: %s\n",
        substr($project->name, 0, 40),
        $project->start_date ?? 'NULL',
        $project->end_date ?? 'NULL'
    );
}
