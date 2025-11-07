<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// ==================== MOCO SYNCHRONIZATION SCHEDULE ====================
// 
// ARCHITEKTUR: MOCO ist Single Source of Truth
// - Stammdaten (Mitarbeiter, Projekte): Täglich 1x synchronisieren
// - Zeiterfassungen & Abwesenheiten: Mehrmals täglich synchronisieren
// 
// DEPLOYMENT: Auf Plesk/Server muss Cron-Job eingerichtet werden:
// * * * * * cd /var/www/vhosts/daytoday.enodia-software.de/httpdocs && php artisan schedule:run >> /dev/null 2>&1
// =========================================================================

// Stammdaten: 1x täglich um 2:00 Uhr nachts
Schedule::command('moco:sync-employees')->dailyAt('02:00')->withoutOverlapping();
Schedule::command('moco:sync-projects')->dailyAt('02:15')->withoutOverlapping();
Schedule::command('moco:sync-contracts')->dailyAt('02:30')->withoutOverlapping();

// Zeiterfassungen & Abwesenheiten: Alle 2 Stunden während Arbeitszeit (8-18 Uhr)
Schedule::command('moco:sync-time-entries')->hourlyAt(0)->between('8:00', '18:00')->withoutOverlapping();
Schedule::command('moco:sync-absences')->hourlyAt(30)->between('8:00', '18:00')->withoutOverlapping();

// Assignments: Alle 4 Stunden
Schedule::command('moco:sync-assignments')->cron('0 */4 * * *')->withoutOverlapping();

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
