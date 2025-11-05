<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;

echo "👥 User in Datenbank:\n";
echo str_repeat("=", 40) . "\n";
echo "Anzahl: " . User::count() . "\n\n";

if (User::count() > 0) {
    echo "Erste User:\n";
    foreach (User::take(3)->get() as $user) {
        echo "  - {$user->name} ({$user->email})\n";
    }
} else {
    echo "⚠️  KEINE User vorhanden!\n";
    echo "Führe aus: php artisan user:create-test\n";
}
