<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$moco = app(App\Services\MocoService::class);

echo "=== Test Project: test1 (MOCO ID: 947240883) ===\n\n";

$project = $moco->getProject(947240883);

if (!$project) {
    echo "ERROR: Could not fetch project from MOCO!\n";
    exit(1);
}

echo "Project Name: " . ($project['name'] ?? 'N/A') . "\n";
echo "Leader: " . ($project['leader']['firstname'] ?? 'N/A') . " " . ($project['leader']['lastname'] ?? '') . "\n";
echo "Leader ID: " . ($project['leader']['id'] ?? 'N/A') . "\n";
echo "Leader Active: " . (isset($project['leader']['active']) ? ($project['leader']['active'] ? 'Yes' : 'No') : 'N/A') . "\n";

echo "\n=== Contracts ===\n";
if (isset($project['contracts']) && !empty($project['contracts'])) {
    foreach ($project['contracts'] as $contract) {
        $user = $contract['user'] ?? [];
        echo "  User: " . ($user['firstname'] ?? 'N/A') . " " . ($user['lastname'] ?? '') . "\n";
        echo "    ID: " . ($user['id'] ?? 'N/A') . "\n";
        echo "    Active: " . (isset($user['active']) ? ($user['active'] ? 'Yes' : 'No') : 'N/A') . "\n";
        echo "    Hours/Week: " . ($contract['hours_per_week'] ?? 'N/A') . "\n\n";
    }
} else {
    echo "  No contracts found.\n";
}

echo "\n=== Check Leader in Local DB ===\n";
$leaderId = $project['leader']['id'] ?? null;
if ($leaderId) {
    $localEmployee = \App\Models\Employee::where('moco_id', $leaderId)->first();
    if ($localEmployee) {
        echo "Leader EXISTS in DB: {$localEmployee->first_name} {$localEmployee->last_name} (is_active: {$localEmployee->is_active})\n";
    } else {
        echo "Leader MISSING in DB: {$project['leader']['firstname']} {$project['leader']['lastname']} (MOCO ID: {$leaderId})\n";
        echo "  Should create with: is_active = " . (($project['leader']['active'] ?? false) ? '1' : '0') . "\n";
    }
}

echo "\nDone.\n";
