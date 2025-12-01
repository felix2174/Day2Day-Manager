<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\MocoService;
use App\Models\Employee;

$mocoService = app(MocoService::class);

echo "=== MOCO Absences API Test ===\n\n";

// Teste verschiedene Endpunkte
$endpoints = [
    'schedules/absences',
    'users/absences', 
    'planning/absences',
];

echo "Test 1: Verschiedene API-Endpunkte testen\n";
foreach ($endpoints as $endpoint) {
    echo "\nTeste: GET /{$endpoint}\n";
    try {
        $response = $mocoService->client->get($endpoint, [
            'query' => [
                'from' => '2025-10-01',
                'to' => '2025-12-31',
            ]
        ]);
        $data = json_decode($response->getBody()->getContents(), true);
        echo "  ✅ SUCCESS: " . count($data) . " Einträge gefunden\n";
        
        if (count($data) > 0) {
            $first = $data[0];
            echo "  Beispiel: User " . ($first['user']['id'] ?? 'N/A') . " | ";
            echo "Date: " . ($first['date'] ?? 'N/A') . " | ";
            echo "Type: " . ($first['absence_type'] ?? 'N/A') . "\n";
        }
        
        // Gefunden! Verwende diesen
        echo "  🎯 DIESER ENDPUNKT FUNKTIONIERT!\n";
        break;
        
    } catch (\Exception $e) {
        $code = method_exists($e, 'getResponse') ? $e->getResponse()->getStatusCode() : 'N/A';
        echo "  ❌ FEHLER ({$code}): " . $e->getMessage() . "\n";
    }
}

echo "\n";

// Test 2: User-spezifische Absences (mit User-ID)
echo "Test 2: User-spezifische Absences\n";
$testEmployee = Employee::whereNotNull('moco_id')->first();

if ($testEmployee) {
    echo "Test-User: {$testEmployee->first_name} {$testEmployee->last_name} (MOCO-ID: {$testEmployee->moco_id})\n\n";
    
    $userEndpoints = [
        "users/{$testEmployee->moco_id}/absences",
        "schedules/absences?user_id={$testEmployee->moco_id}",
    ];
    
    foreach ($userEndpoints as $endpoint) {
        echo "Teste: GET /{$endpoint}\n";
        try {
            $response = $mocoService->client->get($endpoint, [
                'query' => []
            ]);
            $data = json_decode($response->getBody()->getContents(), true);
            echo "  ✅ SUCCESS: " . count($data) . " Absences für User\n";
            
            if (count($data) > 0) {
                echo "  🎯 DIESER ENDPUNKT FUNKTIONIERT FÜR USER!\n";
            }
            
        } catch (\Exception $e) {
            $code = method_exists($e, 'getResponse') ? $e->getResponse()->getStatusCode() : 'N/A';
            echo "  ❌ FEHLER ({$code}): " . $e->getMessage() . "\n";
        }
    }
}

echo "\n=== Test abgeschlossen ===\n";
