<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Employees with Emails ===\n\n";

$employees = DB::table('employees')
    ->select('id', 'first_name', 'last_name', 'email')
    ->get();

foreach ($employees as $employee) {
    echo "ID: {$employee->id} - {$employee->first_name} {$employee->last_name} - Email: " . ($employee->email ?? 'NULL') . "\n";
}

echo "\n=== Looking for specific users ===\n";
$jorg = DB::table('employees')->where('first_name', 'LIKE', '%Jörg%')->orWhere('last_name', 'LIKE', '%Michno%')->first();
echo "Jörg: " . ($jorg ? "{$jorg->first_name} {$jorg->last_name} ({$jorg->email})" : "NOT FOUND") . "\n";

$marc = DB::table('employees')->where('first_name', 'LIKE', '%Marc%')->orWhere('last_name', 'LIKE', '%Hanke%')->first();
echo "Marc: " . ($marc ? "{$marc->first_name} {$marc->last_name} ({$marc->email})" : "NOT FOUND") . "\n";

$hannes = DB::table('employees')->where('first_name', 'LIKE', '%Hannes%')->orWhere('last_name', 'LIKE', '%Boekhoff%')->first();
echo "Hannes: " . ($hannes ? "{$hannes->first_name} {$hannes->last_name} ({$hannes->email})" : "NOT FOUND") . "\n";
