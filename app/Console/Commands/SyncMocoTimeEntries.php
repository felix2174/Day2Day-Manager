<?php

namespace App\Console\Commands;

use App\Models\TimeEntry;
use App\Models\Employee;
use App\Models\Project;
use App\Services\MocoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncMocoTimeEntries extends Command
{
    protected $signature = 'sync:moco-time-entries 
                            {--days=7 : Anzahl Tage in der Vergangenheit (default: 7)}
                            {--full : Voller Sync (alle Daten, ignoriert --days)}
                            {--no-cache : Cache deaktivieren}';

    protected $description = 'Synchronisiert MOCO Zeiterfassungen (optimiert: nur letzte X Tage)';

    protected $mocoService;

    public function __construct(MocoService $mocoService)
    {
        parent::__construct();
        $this->mocoService = $mocoService;
    }

    public function handle()
    {
        $startTime = now();
        $isFull = $this->option('full');
        $days = $this->option('days');
        $noCache = $this->option('no-cache');

        $this->info('🔄 Sync MOCO Zeiterfassungen');
        $this->newLine();

        // Cache-Key für Last-Sync Timestamp
        $cacheKey = 'moco:time_entries:last_sync';
        
        if (!$noCache && !$isFull) {
            $lastSync = Cache::get($cacheKey);
            if ($lastSync && $lastSync->diffInMinutes(now()) < 60) {
                $this->warn('⚠️  Letzter Sync vor ' . $lastSync->diffInMinutes(now()) . ' Minuten');
                $this->line('   Cache ist noch gültig (TTL: 1h)');
                $this->newLine();
                
                if (!$this->confirm('Trotzdem synchronisieren?', false)) {
                    $this->info('✅ Abgebrochen');
                    return 0;
                }
            }
        }

        // Zeitraum berechnen
        $fromDate = $isFull ? now()->subYears(2) : now()->subDays($days);
        $toDate = now();

        $this->line("📅 Zeitraum: {$fromDate->format('d.m.Y')} - {$toDate->format('d.m.Y')}");
        $this->newLine();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        try {
            // Hole Zeiterfassungen von MOCO
            $this->info('🔍 Hole Zeiterfassungen von MOCO...');
            
            $activities = $this->mocoService->getActivities([
                'from' => $fromDate->format('Y-m-d'),
                'to' => $toDate->format('Y-m-d'),
            ]);

            $this->info("📊 Gefundene Activities: " . count($activities));
            $this->newLine();

            if (empty($activities)) {
                $this->warn('⚠️  Keine Activities gefunden');
                return 0;
            }

            $bar = $this->output->createProgressBar(count($activities));
            $bar->start();

            foreach ($activities as $activity) {
                try {
                    $mocoUserId = $activity['user']['id'] ?? null;
                    $mocoProjectId = $activity['project']['id'] ?? null;
                    $date = $activity['date'] ?? null;
                    $hours = $activity['hours'] ?? 0;

                    if (!$mocoUserId || !$date) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    // Finde Employee
                    $employee = Employee::where('moco_id', $mocoUserId)->first();
                    if (!$employee) {
                        $skipped++;
                        $bar->advance();
                        continue;
                    }

                    // Finde Project (optional)
                    $project = null;
                    if ($mocoProjectId) {
                        $project = Project::where('moco_id', $mocoProjectId)->first();
                    }

                    // Erstelle oder Update TimeEntry
                    $timeEntry = TimeEntry::updateOrCreate(
                        [
                            'moco_id' => $activity['id'],
                        ],
                        [
                            'employee_id' => $employee->id,
                            'project_id' => $project?->id,
                            'date' => $date,
                            'hours' => $hours,
                            'description' => $activity['description'] ?? null,
                            'billable' => $activity['billable'] ?? false,
                        ]
                    );

                    if ($timeEntry->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }

                    $bar->advance();

                } catch (\Exception $e) {
                    $errors++;
                    Log::error('TimeEntry Sync failed', [
                        'activity_id' => $activity['id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                    $bar->advance();
                }
            }

            $bar->finish();
            $this->newLine(2);

            // Cache Last-Sync Timestamp
            if (!$noCache) {
                Cache::put($cacheKey, now(), now()->addHour());
            }

        } catch (\Exception $e) {
            $this->error('❌ MOCO-API Fehler: ' . $e->getMessage());
            Log::error('MOCO Time Entries Sync failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }

        $duration = $startTime->diffInSeconds(now());

        // Zusammenfassung
        $this->table(
            ['Status', 'Anzahl', 'Details'],
            [
                ['✅ Neu erstellt', $created, 'Neue Zeiterfassungen'],
                ['🔄 Aktualisiert', $updated, 'Bestehende aktualisiert'],
                ['⏭️  Übersprungen', $skipped, 'Keine Zuordnung möglich'],
                ['❌ Fehler', $errors, 'Fehlgeschlagen'],
            ]
        );

        $this->newLine();
        $this->info("⏱️  Dauer: {$duration}s");
        $this->info('✅ Sync abgeschlossen!');

        return $created > 0 || $updated > 0 ? 0 : 1;
    }
}
