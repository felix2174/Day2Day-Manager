<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$projects = \App\Models\Project::whereNotNull('moco_id')->limit(10)->get();
$mocoService = app(\App\Services\MocoService::class);

$projectsWithTeam = 0;
$projectsWithoutTeam = 0;

foreach ($projects as $project) {
    $team = $mocoService->getProjectTeam($project->moco_id);
    
    if ($team && !empty($team)) {
        $projectsWithTeam++;
        echo "✅ {$project->name}: " . count($team) . " Mitarbeiter\n";
        print_r($team[0] ?? []); // Zeige ersten Mitarbeiter
        break; // Stoppe nach erstem Fund
    } else {
        $projectsWithoutTeam++;
    }
}

echo "\n\n📊 Ergebnis:\n";
echo "- Projekte MIT Team: {$projectsWithTeam}\n";
echo "- Projekte OHNE Team: {$projectsWithoutTeam}\n";

