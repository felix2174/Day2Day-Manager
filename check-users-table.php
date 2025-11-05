<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Users Table Structure ===\n\n";

$columns = DB::select("DESCRIBE users");

foreach ($columns as $column) {
    echo "{$column->Field} ({$column->Type}) - Null: {$column->Null} - Key: {$column->Key}\n";
}

echo "\n=== Existing Users ===\n\n";

$users = DB::table('users')->get();

if ($users->isEmpty()) {
    echo "No users in table.\n";
} else {
    foreach ($users as $user) {
        echo "ID: {$user->id} - Email: {$user->email}\n";
    }
}
