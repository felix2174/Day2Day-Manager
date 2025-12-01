<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$moco = app(App\Services\MocoService::class);

echo "=== MOCO Project Analysis ===\n\n";

$projects = $moco->getProjects();
echo "Total Projects in MOCO: " . count($projects) . "\n\n";

$withLeader = 0;
$withoutLeader = 0;
$withoutAny = 0;
$allLeaders = [];
$projectsWithoutAny = [];

foreach ($projects as $project) {
    $hasLeader = !empty($project['leader']['id']);
    
    if ($hasLeader) {
        $withLeader++;
        $leaderId = $project['leader']['id'];
        $leaderName = ($project['leader']['firstname'] ?? '') . ' ' . ($project['leader']['lastname'] ?? '');
        $allLeaders[$leaderId] = $leaderName;
    } else {
        $withoutLeader++;
        
        // Check if project has contracts (assigned employees)
        try {
            $contracts = $moco->getProjectContracts($project['id']);
            if (empty($contracts)) {
                $withoutAny++;
                $projectsWithoutAny[] = [
                    'id' => $project['id'],
                    'name' => $project['name'] ?? 'Unknown',
                ];
            } else {
                // Collect employees from contracts
                foreach ($contracts as $contract) {
                    if (!empty($contract['user']['id'])) {
                        $userId = $contract['user']['id'];
                        $userName = ($contract['user']['firstname'] ?? '') . ' ' . ($contract['user']['lastname'] ?? '');
                        $allLeaders[$userId] = $userName;
                    }
                }
            }
        } catch (Exception $e) {
            echo "Error checking contracts for project {$project['id']}: " . $e->getMessage() . "\n";
        }
    }
}

echo "Projects WITH Leader: $withLeader\n";
echo "Projects WITHOUT Leader: $withoutLeader\n";
echo "Projects WITHOUT Leader AND Contracts: $withoutAny\n\n";

if (!empty($projectsWithoutAny)) {
    echo "=== Projects without ANY employees ===\n";
    foreach ($projectsWithoutAny as $p) {
        echo "- [{$p['id']}] {$p['name']}\n";
    }
    echo "\n";
}

echo "=== All Unique Leaders/Employees in MOCO ===\n";
echo "Total: " . count($allLeaders) . "\n\n";
foreach ($allLeaders as $id => $name) {
    echo "- [$id] $name\n";
}

// Check which are NOT in local DB
echo "\n=== Leaders NOT in local DB ===\n";
$missing = [];
foreach ($allLeaders as $mocoId => $name) {
    $exists = \App\Models\Employee::where('moco_id', $mocoId)->exists();
    if (!$exists) {
        $missing[] = "[$mocoId] $name";
    }
}

if (empty($missing)) {
    echo "All leaders exist in local DB! ✅\n";
} else {
    echo "Missing " . count($missing) . " leaders:\n";
    foreach ($missing as $m) {
        echo "- $m\n";
    }
}
