<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Foreign Keys on users table ===\n\n";

$query = "
    SELECT 
        CONSTRAINT_NAME,
        COLUMN_NAME,
        REFERENCED_TABLE_NAME,
        REFERENCED_COLUMN_NAME
    FROM 
        information_schema.KEY_COLUMN_USAGE
    WHERE 
        TABLE_SCHEMA = 'day2day' 
        AND TABLE_NAME = 'users'
        AND REFERENCED_TABLE_NAME IS NOT NULL
";

$foreignKeys = DB::select($query);

if (empty($foreignKeys)) {
    echo "No foreign keys found.\n";
} else {
    foreach ($foreignKeys as $fk) {
        echo "{$fk->CONSTRAINT_NAME}: {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
    }
}
