<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$moco = app(App\Services\MocoService::class);

echo "=== Testing MOCO Users API Filters ===\n\n";

echo "1. Active users (active=true):\n";
$activeUsers = $moco->getUsers(['active' => true]);
echo "   Count: " . count($activeUsers) . "\n";
if (!empty($activeUsers)) {
    echo "   Sample: " . ($activeUsers[0]['firstname'] ?? 'N/A') . " " . ($activeUsers[0]['lastname'] ?? 'N/A') . "\n";
}

echo "\n2. Inactive users (active=false):\n";
$inactiveUsers = $moco->getUsers(['active' => false]);
echo "   Count: " . count($inactiveUsers) . "\n";
if (!empty($inactiveUsers)) {
    echo "   Sample: " . ($inactiveUsers[0]['firstname'] ?? 'N/A') . " " . ($inactiveUsers[0]['lastname'] ?? 'N/A') . "\n";
}

echo "\n3. All users (no filter):\n";
$allUsers = $moco->getUsers();
echo "   Count: " . count($allUsers) . "\n";
if (!empty($allUsers)) {
    echo "   Sample: " . ($allUsers[0]['firstname'] ?? 'N/A') . " " . ($allUsers[0]['lastname'] ?? 'N/A') . "\n";
}

echo "\n=== Search for Veronika & Sven ===\n";
foreach ($allUsers as $user) {
    $name = ($user['firstname'] ?? '') . ' ' . ($user['lastname'] ?? '');
    if (str_contains($name, 'Veronika') || str_contains($name, 'Sven')) {
        echo "Found: {$name} (ID: {$user['id']}, Active: " . ($user['active'] ? 'Yes' : 'No') . ")\n";
    }
}

echo "\nDone.\n";
