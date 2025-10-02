<?php

# use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Inspiring;

/*
|--------------------------------------------------------------------------
| Console Routes & Scheduling (Laravel 12)
|--------------------------------------------------------------------------
| Hier definierst du Konsolenbefehle und Zeitpläne.
| "php artisan schedule:list" zeigt die Einträge an.
*/

// Beispiel-Konsolenbefehl (optional)
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// MOCO Sync: inkrementell alle 15 Minuten
# Schedule::command('moco:sync-projects')->everyFifteenMinutes();

// MOCO Sync: Vollabgleich täglich um 03:00
# Schedule::command('moco:sync-projects --full')->dailyAt('03:00');
