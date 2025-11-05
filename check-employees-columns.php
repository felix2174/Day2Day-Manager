<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;

echo "📋 employees-Tabelle Spalten:\n";
echo str_repeat("=", 50) . "\n";

$columns = Schema::getColumnListing('employees');
foreach ($columns as $col) {
    echo "  - {$col}\n";
}

echo "\n✅ moco_id vorhanden: " . (in_array('moco_id', $columns) ? 'JA' : 'NEIN') . "\n";
