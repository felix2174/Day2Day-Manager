<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\MocoService;
use App\Models\Employee;

$mocoService = app(MocoService::class);

echo "=== MOCO Absences Diagnose ===\n\n";

// Steffen Armgart (SA) prüfen
$steffen = Employee::where('first_name', 'Steffen')
    ->where('last_name', 'Armgart')
    ->first();

if ($steffen) {
    echo "✅ Steffen Armgart gefunden:\n";
    echo "   ID: {$steffen->id}\n";
    echo "   MOCO-ID: " . ($steffen->moco_id ?? 'NULL') . "\n\n";
    
    if ($steffen->moco_id) {
        echo "Teste getUserAbsences für Steffen:\n";
        try {
            $absences = $mocoService->getUserAbsences($steffen->moco_id, []);
            echo "   Ergebnis: " . count($absences) . " Absences\n";
            
            if (count($absences) > 0) {
                echo "\n   Erste 3 Absences:\n";
                foreach (array_slice($absences, 0, 3) as $abs) {
                    $date = $abs['date'] ?? 'N/A';
                    $type = $abs['absence_type'] ?? 'N/A';
                    echo "   - {$date} | {$type}\n";
                }
            }
        } catch (\Exception $e) {
            echo "   ERROR: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "❌ Steffen Armgart NICHT gefunden in DB\n";
}

echo "\n";

// Jörg Michno (JM) prüfen
$jorg = Employee::where(function($q) {
    $q->where('first_name', 'LIKE', '%Jörg%')
      ->orWhere('first_name', 'LIKE', '%Joerg%')
      ->orWhere('first_name', 'LIKE', '%Jorg%');
})->where(function($q) {
    $q->where('last_name', 'LIKE', '%Michno%');
})->first();

if ($jorg) {
    echo "✅ Jörg Michno gefunden:\n";
    echo "   ID: {$jorg->id}\n";
    echo "   MOCO-ID: " . ($jorg->moco_id ?? 'NULL') . "\n\n";
    
    if ($jorg->moco_id) {
        echo "Teste getUserAbsences für Jörg:\n";
        try {
            $absences = $mocoService->getUserAbsences($jorg->moco_id, []);
            echo "   Ergebnis: " . count($absences) . " Absences\n";
            
            if (count($absences) > 0) {
                echo "\n   Erste 3 Absences:\n";
                foreach (array_slice($absences, 0, 3) as $abs) {
                    $date = $abs['date'] ?? 'N/A';
                    $type = $abs['absence_type'] ?? 'N/A';
                    echo "   - {$date} | {$type}\n";
                }
            }
        } catch (\Exception $e) {
            echo "   ERROR: " . $e->getMessage() . "\n";
        }
    }
} else {
    echo "❌ Jörg Michno NICHT gefunden in DB\n";
}

echo "\n=== Diagnose abgeschlossen ===\n";
