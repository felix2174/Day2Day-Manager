<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\MocoService;

$mocoService = app(MocoService::class);

echo "Fetching first project with contracts...\n\n";

$projects = $mocoService->getProjects();
$foundContract = false;

foreach (array_slice($projects, 0, 10) as $project) {
    $fullProject = $mocoService->getProject($project['id']);
    
    if (!empty($fullProject['contracts'])) {
        echo "✅ Project: {$fullProject['name']} (ID: {$fullProject['id']})\n";
        echo "Contracts: " . count($fullProject['contracts']) . "\n\n";
        
        foreach ($fullProject['contracts'] as $contract) {
            echo "Contract Structure:\n";
            print_r($contract);
            echo "\n---\n";
            $foundContract = true;
            break 2; // Exit both loops
        }
    }
}

if (!$foundContract) {
    echo "❌ No contracts found in first 10 projects\n";
}
