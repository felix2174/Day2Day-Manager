<?php
/**
 * Route Test Script
 * Testet alle wichtigen Routes und zeigt Status
 */

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;

echo "🔍 Day2Day Routes Test\n";
echo str_repeat("=", 60) . "\n\n";

// Test MySQL Connection
try {
    DB::connection()->getPdo();
    echo "✅ MySQL-Verbindung: OK\n\n";
} catch (\Exception $e) {
    echo "❌ MySQL-Verbindung: FEHLER\n";
    echo "   " . $e->getMessage() . "\n\n";
    exit(1);
}

// Test wichtige Routes
$routesToTest = [
    'dashboard' => '/dashboard',
    'projects.index' => '/projects',
    'employees.index' => '/employees',
    'gantt.index' => '/gantt',
    'absences.index' => '/absences',
    'moco.index' => '/moco',
];

echo "📋 Route-Überprüfung:\n";
echo str_repeat("-", 60) . "\n";

foreach ($routesToTest as $name => $uri) {
    $route = Route::getRoutes()->getByName($name);
    
    if ($route) {
        echo "✅ {$name}\n";
        echo "   URI: {$uri}\n";
        echo "   Action: " . $route->getActionName() . "\n";
    } else {
        echo "❌ {$name} - NICHT GEFUNDEN\n";
    }
    echo "\n";
}

// Test Controllers
echo "\n📦 Controller-Überprüfung:\n";
echo str_repeat("-", 60) . "\n";

$controllers = [
    'DashboardController' => 'App\\Http\\Controllers\\DashboardController',
    'ProjectController' => 'App\\Http\\Controllers\\ProjectController',
    'EmployeeController' => 'App\\Http\\Controllers\\EmployeeController',
    'GanttController' => 'App\\Http\\Controllers\\GanttController',
    'AbsenceController' => 'App\\Http\\Controllers\\AbsenceController',
    'MocoController' => 'App\\Http\\Controllers\\MocoController',
];

foreach ($controllers as $name => $class) {
    if (class_exists($class)) {
        echo "✅ {$name}: Existiert\n";
    } else {
        echo "❌ {$name}: FEHLT\n";
    }
}

echo "\n";
echo str_repeat("=", 60) . "\n";
echo "✅ Test abgeschlossen!\n";
echo "\nNächster Schritt: Öffne http://127.0.0.1:8000/test-navigation.html\n";
