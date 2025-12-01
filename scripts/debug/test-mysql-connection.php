<?php
/**
 * Quick Database Test Script
 * Prüft MySQL-Verbindung und zeigt Daten an
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Assignment;

echo "🔍 Day2Day-Manager MySQL Test\n";
echo str_repeat("=", 50) . "\n\n";

try {
    // Test 1: Connection
    echo "✅ MySQL-Verbindung: ";
    $pdo = DB::connection()->getPdo();
    echo "ERFOLGREICH\n";
    echo "   Database: " . DB::connection()->getDatabaseName() . "\n";
    echo "   Driver: " . DB::connection()->getDriverName() . "\n\n";

    // Test 2: Daten zählen
    echo "📊 Datenbestand:\n";
    echo "   Projects: " . Project::count() . "\n";
    echo "   Employees: " . Employee::count() . "\n";
    echo "   Assignments: " . Assignment::count() . "\n\n";

    // Test 3: Erste Einträge anzeigen
    echo "🔎 Erste Einträge:\n\n";
    
    $project = Project::first();
    if ($project) {
        echo "   Projekt:\n";
        echo "   - ID: {$project->id}\n";
        echo "   - Name: {$project->name}\n";
        echo "   - Status: {$project->status}\n\n";
    }
    
    $employees = Employee::limit(3)->get();
    echo "   Mitarbeiter:\n";
    foreach ($employees as $emp) {
        echo "   - {$emp->first_name} {$emp->last_name} ({$emp->department})\n";
    }
    echo "\n";

    $assignments = Assignment::with(['project', 'employee'])->limit(3)->get();
    echo "   Assignments:\n";
    foreach ($assignments as $ass) {
        echo "   - {$ass->employee->first_name} → {$ass->project->name} ({$ass->weekly_hours}h/Woche)\n";
    }
    echo "\n";

    echo "✅ ALLE TESTS ERFOLGREICH!\n";
    echo "🚀 MySQL-Migration abgeschlossen!\n\n";

} catch (\Exception $e) {
    echo "❌ FEHLER: " . $e->getMessage() . "\n";
    echo "   Datei: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
