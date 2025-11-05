<?php

namespace App\Console\Commands;

use App\Models\Absence;
use App\Models\Employee;
use App\Models\MocoSyncLog;
use App\Services\MocoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncMocoAbsences extends Command
{
    protected $signature = 'sync:moco-absences 
                            {--days=30 : Anzahl Tage in der Vergangenheit (default: 30)}
                            {--full : Voller Sync (alle Daten, ignoriert --days)}
                            {--no-cache : Cache deaktivieren}';

    protected $description = 'Synchronisiert MOCO Abwesenheiten (Urlaub, Krankheit, etc.)';

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

        $this->info('🔄 Sync MOCO Abwesenheiten');
        $this->newLine();

        // Cache-Key für Last-Sync Timestamp
        $cacheKey = 'moco:absences:last_sync';
        
        if (!$noCache && !$isFull) {
            $lastSync = Cache::get($cacheKey);
            if ($lastSync && $lastSync->diffInMinutes(now()) < 60) {
                // Im Web-Context: Skip Cache-Check, im CLI: Warnung
                if (app()->runningInConsole()) {
                    $this->warn('⚠️  Letzter Sync vor ' . $lastSync->diffInMinutes(now()) . ' Minuten');
                    $this->line('   Cache ist noch gültig (TTL: 1h)');
                    $this->newLine();
                    
                    if (!$this->confirm('Trotzdem synchronisieren?', false)) {
                        $this->info('✅ Abgebrochen');
                        return 0;
                    }
                }
                // Wenn Web-Request: Ignoriere Cache-Warnung, sync trotzdem
            }
        }

        // Zeitraum berechnen
        $fromDate = $isFull ? now()->subYear() : now()->subDays($days);
        $toDate = now()->addMonths(6); // Zukünftige Abwesenheiten auch

        $this->line("📅 Zeitraum: {$fromDate->format('d.m.Y')} - {$toDate->format('d.m.Y')}");
        $this->newLine();

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        try {
            // Hole Abwesenheiten von MOCO
            // WICHTIG: MOCO API hat keinen globalen /schedules/absences Endpunkt
            // Lösung: Iteriere über alle Employees mit moco_id
            $this->info('🔍 Hole Abwesenheiten von MOCO (pro Mitarbeiter)...');
            $this->newLine();
            
            $employees = Employee::whereNotNull('moco_id')->get();
            
            if ($employees->isEmpty()) {
                $this->error('❌ Keine Mitarbeiter mit MOCO-ID gefunden');
                return 1;
            }
            
            $this->line("👥 {$employees->count()} Mitarbeiter werden geprüft");
            $this->newLine();
            
            $allAbsences = [];
            
            // Progress Bar nur im Console Mode
            $empBar = null;
            if (app()->runningInConsole()) {
                $empBar = $this->output->createProgressBar($employees->count());
                $empBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
                $empBar->setMessage('Starte...');
                $empBar->start();
            }
            
            foreach ($employees as $employee) {
                try {
                    if ($empBar) {
                        $empBar->setMessage($employee->first_name . ' ' . $employee->last_name);
                    }
                    
                    $userAbsences = $this->mocoService->getUserAbsences($employee->moco_id, [
                        'from' => $fromDate->format('Y-m-d'),
                        'to' => $toDate->format('Y-m-d'),
                    ]);
                    
                    // Debug: Log für ersten Mitarbeiter
                    if ($employees->first()->id === $employee->id && !empty($userAbsences)) {
                        Log::info('MOCO Absences Debug (erster Mitarbeiter)', [
                            'employee' => $employee->first_name . ' ' . $employee->last_name,
                            'moco_id' => $employee->moco_id,
                            'count' => count($userAbsences),
                            'sample' => array_slice($userAbsences, 0, 2),
                        ]);
                    }
                    
                    if (!empty($userAbsences)) {
                        $allAbsences = array_merge($allAbsences, $userAbsences);
                    }
                    
                    if ($empBar) {
                        $empBar->advance();
                    }
                    
                } catch (\Exception $e) {
                    Log::warning('MOCO Absences Fehler für Employee: ' . $employee->moco_id, [
                        'employee' => $employee->first_name . ' ' . $employee->last_name,
                        'error' => $e->getMessage()
                    ]);
                    $errors++;
                    if ($empBar) {
                        $empBar->advance();
                    }
                }
            }
            
            if ($empBar) {
                $empBar->finish();
            }
            $this->newLine(2);
            
            $this->info("📊 Gefundene Abwesenheiten: " . count($allAbsences));
            $this->newLine();

            if (empty($allAbsences)) {
                $this->warn('⚠️  Keine Abwesenheiten gefunden');
                return 0;
            }

            // Progress Bar nur im Console Mode
            $bar = null;
            if (app()->runningInConsole()) {
                $bar = $this->output->createProgressBar(count($allAbsences));
                $bar->start();
            }

            foreach ($allAbsences as $absence) {
                try {
                    // /schedules Struktur: user.id, date, assignment.name
                    $mocoUserId = $absence['user']['id'] ?? null;
                    $startDate = $absence['date'] ?? null;
                    
                    // Enddate ist nur bei Multi-Day Absences vorhanden
                    // Ansonsten: Same-Day
                    $endDate = $startDate;
                    
                    // Type kommt aus assignment.name (z.B. "Urlaub", "Krank")
                    $assignmentName = $absence['assignment']['name'] ?? 'other';

                    if (!$mocoUserId || !$startDate) {
                        $skipped++;
                        if ($bar) {
                            $bar->advance();
                        }
                        continue;
                    }

                    // Finde Employee
                    $employee = Employee::where('moco_id', $mocoUserId)->first();
                    if (!$employee) {
                        $skipped++;
                        if ($bar) {
                            $bar->advance();
                        }
                        continue;
                    }

                    // Mapping MOCO assignment.name → unsere Types
                    // WICHTIG: absences.type ist ENUM('urlaub', 'krankheit', 'fortbildung')
                    $typeMapping = [
                        'Urlaub' => 'urlaub',
                        'Krank' => 'krankheit',
                        'Fortbildung' => 'fortbildung',
                        'Sonderurlaub' => 'urlaub',  // Als Urlaub behandeln
                        'Unbezahlt' => 'urlaub',      // Als Urlaub behandeln
                        'Feiertag' => 'urlaub',       // Als Urlaub behandeln
                    ];
                    $mappedType = $typeMapping[$assignmentName] ?? 'urlaub';  // Fallback: urlaub

                    // Erstelle oder Update Absence
                    $absenceEntry = Absence::updateOrCreate(
                        [
                            'moco_id' => $absence['id'],
                        ],
                        [
                            'employee_id' => $employee->id,
                            'start_date' => $startDate,
                            'end_date' => $endDate,
                            'type' => $mappedType,
                            'reason' => $assignmentName,  // z.B. "Urlaub", "Krank", "Feiertag"
                        ]
                    );

                    if ($absenceEntry->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }

                    if ($bar) {
                        $bar->advance();
                    }

                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Absence Sync failed', [
                        'absence_id' => $absence['id'] ?? null,
                        'error' => $e->getMessage(),
                    ]);
                    if ($bar) {
                        $bar->advance();
                    }
                }
            }

            if ($bar) {
                $bar->finish();
            }
            $this->newLine(2);

            // Cache Last-Sync Timestamp
            if (!$noCache) {
                Cache::put($cacheKey, now(), now()->addHour());
            }

        } catch (\Exception $e) {
            $this->error('❌ MOCO-API Fehler: ' . $e->getMessage());
            Log::error('MOCO Absences Sync failed', [
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
                ['✅ Neu erstellt', $created, 'Neue Abwesenheiten'],
                ['🔄 Aktualisiert', $updated, 'Bestehende aktualisiert'],
                ['⏭️  Übersprungen', $skipped, 'Keine Zuordnung möglich'],
                ['❌ Fehler', $errors, 'Fehlgeschlagen'],
            ]
        );

        $this->newLine();
        $this->info("⏱️  Dauer: {$duration}s");
        $this->info('✅ Sync abgeschlossen!');

        // Log Success zu MocoSyncLog
        MocoSyncLog::create([
            'sync_type' => 'absences',
            'status' => 'completed',
            'started_at' => $startTime,
            'completed_at' => now(),
            'items_processed' => $created + $updated + $skipped,
            'items_created' => $created,
            'items_updated' => $updated,
            'items_skipped' => $skipped,
        ]);

        return $created > 0 || $updated > 0 ? 0 : 1;
    }
}
