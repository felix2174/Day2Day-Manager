<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test: Zeige ein Projekt mit Assignment
$project = \App\Models\Project::with(['assignments.employee', 'responsible'])
    ->where('name', 'Day2Day-Flow')
    ->first();

if (!$project) {
    echo "Project nicht gefunden!\n";
    exit;
}

echo "📊 Projekt: {$project->name}\n";
echo "================================\n\n";

echo "👤 Verantwortlich:\n";
if ($project->responsible) {
    echo "   {$project->responsible->first_name} {$project->responsible->last_name}\n";
} else {
    echo "   Nicht gesetzt\n";
}

echo "\n✅ Assignments ({$project->assignments->count()}):\n";
foreach ($project->assignments as $assignment) {
    $employee = $assignment->employee;
    echo sprintf(
        "   • %s %s\n     Role: %s | Source: %s | Active: %s | Hours: %sh/week\n",
        $employee->first_name ?? 'Unknown',
        $employee->last_name ?? 'Unknown',
        $assignment->role,
        $assignment->source,
        $assignment->is_active ? 'Yes' : 'No',
        $assignment->weekly_hours
    );
}

echo "\n📋 Model-Methode getAssignedPersonsList():\n";
$persons = $project->getAssignedPersonsList();
foreach ($persons as $person) {
    echo "   • $person\n";
}

echo "\n================================\n";
echo "✅ Test erfolgreich!\n";
