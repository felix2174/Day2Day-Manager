<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$moco = app(App\Services\MocoService::class);

echo "=== Sync Employees from Project Contracts ===\n\n";

// Hole alle Projekte
$projects = $moco->getProjects();
echo "Found " . count($projects) . " projects in MOCO\n\n";

// Sammle alle einzigartigen User aus Contracts
$uniqueUsers = [];

foreach ($projects as $project) {
    // Hole vollständige Projekt-Details mit Contracts
    $fullProject = $moco->getProject($project['id']);
    
    if (!$fullProject || !isset($fullProject['contracts'])) {
        continue;
    }
    
    foreach ($fullProject['contracts'] as $contract) {
        if (!isset($contract['user']) || !isset($contract['user']['id'])) {
            continue;
        }
        
        $userId = $contract['user']['id'];
        
        // Speichere User-Daten
        if (!isset($uniqueUsers[$userId])) {
            $uniqueUsers[$userId] = [
                'id' => $userId,
                'firstname' => $contract['user']['firstname'] ?? 'Unknown',
                'lastname' => $contract['user']['lastname'] ?? '',
                'active' => $contract['user']['active'] ?? false,
                'projects_count' => 1,
            ];
        } else {
            $uniqueUsers[$userId]['projects_count']++;
        }
    }
}

echo "=== Found " . count($uniqueUsers) . " unique users in contracts ===\n\n";

// Zeige alle User sortiert nach Status
$activeUsers = array_filter($uniqueUsers, fn($u) => $u['active']);
$inactiveUsers = array_filter($uniqueUsers, fn($u) => !$u['active']);

echo "ACTIVE Users (" . count($activeUsers) . "):\n";
foreach ($activeUsers as $user) {
    echo "  [{$user['id']}] {$user['firstname']} {$user['lastname']} (Projects: {$user['projects_count']})\n";
}

echo "\nINACTIVE Users (" . count($inactiveUsers) . "):\n";
foreach ($inactiveUsers as $user) {
    echo "  [{$user['id']}] {$user['firstname']} {$user['lastname']} (Projects: {$user['projects_count']})\n";
}

// Prüfe welche User NICHT in lokaler DB
echo "\n=== Check Local Database ===\n";
$missingUsers = [];

foreach ($uniqueUsers as $user) {
    $exists = \App\Models\Employee::where('moco_id', $user['id'])->exists();
    if (!$exists) {
        $missingUsers[] = $user;
        echo "MISSING: [{$user['id']}] {$user['firstname']} {$user['lastname']} (Active: " . ($user['active'] ? 'Yes' : 'No') . ")\n";
    }
}

if (empty($missingUsers)) {
    echo "All contract users are in local database!\n";
} else {
    echo "\n=== Would create " . count($missingUsers) . " new employees ===\n";
}

echo "\nDone.\n";
