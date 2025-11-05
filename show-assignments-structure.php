<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Zeige Struktur der assignments-Tabelle
$columns = \Illuminate\Support\Facades\Schema::getColumnListing('assignments');

echo "📊 assignments-Tabelle Struktur:\n";
echo "================================\n\n";

foreach ($columns as $column) {
    $type = \Illuminate\Support\Facades\DB::selectOne("
        SELECT DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
        FROM INFORMATION_SCHEMA.COLUMNS
        WHERE TABLE_NAME = 'assignments'
        AND COLUMN_NAME = ?
    ", [$column]);
    
    echo sprintf("%-20s | %-15s | Nullable: %-3s | Default: %s\n",
        $column,
        $type->DATA_TYPE ?? 'unknown',
        $type->IS_NULLABLE ?? 'unknown',
        $type->COLUMN_DEFAULT ?? 'NULL'
    );
}

echo "\n================================\n";
echo "Anzahl Datensätze: " . \App\Models\Assignment::count() . "\n";
