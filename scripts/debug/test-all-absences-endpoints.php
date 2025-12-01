<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\MocoService;
use App\Models\Employee;
use Illuminate\Support\Facades\Log;

$mocoService = app(MocoService::class);
$steffen = Employee::where('first_name', 'Steffen')->where('last_name', 'Armgart')->first();

echo "=== MOCO API Endpunkte-Test (Steffen Armgart: {$steffen->moco_id}) ===\n\n";

$endpoints = [
    ['method' => 'GET', 'path' => "users/{$steffen->moco_id}/absences", 'desc' => 'User Absences'],
    ['method' => 'GET', 'path' => "users/{$steffen->moco_id}/schedules", 'desc' => 'User Schedules'],
    ['method' => 'GET', 'path' => "users/{$steffen->moco_id}/planning", 'desc' => 'User Planning'],
    ['method' => 'GET', 'path' => 'users/absences', 'desc' => 'All User Absences'],
    ['method' => 'GET', 'path' => 'schedules', 'desc' => 'All Schedules'],
    ['method' => 'GET', 'path' => 'planning/entries', 'desc' => 'Planning Entries'],
];

$reflection = new ReflectionClass($mocoService);
$clientProperty = $reflection->getProperty('client');
$clientProperty->setAccessible(true);
$client = $clientProperty->getValue($mocoService);

foreach ($endpoints as $endpoint) {
    echo "Teste: {$endpoint['method']} /{$endpoint['path']}\n";
    echo "       ({$endpoint['desc']})\n";
    
    try {
        $response = $client->get($endpoint['path'], [
            'query' => [
                'from' => '2025-10-01',
                'to' => '2025-12-31',
            ]
        ]);
        
        $data = json_decode($response->getBody()->getContents(), true);
        $count = is_array($data) ? count($data) : 0;
        
        echo "   ✅ SUCCESS: {$count} Einträge\n";
        
        if ($count > 0) {
            echo "   🎯 DIESER ENDPUNKT FUNKTIONIERT!\n";
            echo "\n   Beispiel-Daten:\n";
            $sample = is_array($data) && isset($data[0]) ? $data[0] : $data;
            echo "   " . json_encode($sample, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        }
        
    } catch (\Exception $e) {
        $code = method_exists($e, 'getResponse') && $e->getResponse() ? 
                $e->getResponse()->getStatusCode() : 'N/A';
        echo "   ❌ FEHLER ({$code})\n";
    }
    
    echo "\n";
}

echo "=== Test abgeschlossen ===\n";
