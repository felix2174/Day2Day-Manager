<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Employee;
use App\Models\Assignment;
use App\Models\MocoSyncLog;
use App\Services\MocoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMocoContracts extends Command
{
    protected $signature = 'sync:moco-contracts 
                            {--dry-run : Zeigt nur Vorschau ohne Änderungen}
                            {--force : Überschreibt bestehende Assignments}';

    protected $description = 'Synchronisiert MOCO Project Contracts → Assignments (DIE echten Mitarbeiter-Zuweisungen!)';

    protected $mocoService;

    public function __construct(MocoService $mocoService)
    {
        parent::__construct();
        $this->mocoService = $mocoService;
    }

    public function handle()
    {
        $startTime = now();
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔄 Sync MOCO Contracts → Assignments');
        $this->newLine();

        // 1. Hole alle Projekte mit MOCO-ID
        $projects = Project::whereNotNull('moco_id')->get();

        if ($projects->isEmpty()) {
            $this->warn('⚠️  Keine Projekte mit MOCO-ID gefunden.');
            return 0;
        }

        $this->info("📊 Gefundene MOCO-Projekte: {$projects->count()}");
        $this->newLine();

        if ($isDryRun) {
            $this->warn('🔍 DRY-RUN MODE - Keine Änderungen werden gespeichert');
            $this->newLine();
        }

        $created = 0;
        $skipped = 0;
        $errors = 0;
        $noContracts = 0;

        // Progress Bar nur im Console Mode
        $bar = null;
        if (app()->runningInConsole()) {
            $bar = $this->output->createProgressBar($projects->count());
            $bar->start();
        }

        foreach ($projects as $project) {
            try {
                // Hole Projekt-Details von MOCO
                $mocoProject = $this->mocoService->getProject($project->moco_id);

                if (!$mocoProject) {
                    $noContracts++;
                    if ($bar) {
                        $bar->advance();
                    }
                    continue;
                }

                // Prüfe ob Contracts vorhanden
                if (!isset($mocoProject['contracts']) || empty($mocoProject['contracts'])) {
                    $noContracts++;
                    if ($bar) {
                        $bar->advance();
                    }
                    continue;
                }

                // Iteriere durch alle Contracts
                foreach ($mocoProject['contracts'] as $contract) {
                    $mocoUserId = $contract['user_id'] ?? null;
                    $hoursPerWeek = $contract['hours_per_week'] ?? 20;

                    if (!$mocoUserId) {
                        continue;
                    }

                    // Finde Employee anhand MOCO-ID
                    $employee = Employee::where('moco_id', $mocoUserId)->first();

                    if (!$employee) {
                        $this->warn("\n⚠️  Employee MOCO-ID {$mocoUserId} nicht in DB gefunden");
                        continue;
                    }

                    // Prüfe ob Assignment bereits existiert
                    $exists = Assignment::where('project_id', $project->id)
                        ->where('employee_id', $employee->id)
                        ->exists();

                    if ($exists && !$force) {
                        $skipped++;
                        continue;
                    }

                    if (!$isDryRun) {
                        // Erstelle oder Update Assignment
                        Assignment::updateOrCreate(
                            [
                                'project_id' => $project->id,
                                'employee_id' => $employee->id,
                            ],
                            [
                                'weekly_hours' => $hoursPerWeek,
                                'start_date' => $project->start_date ?? now(),
                                'end_date' => $project->end_date ?? now()->addMonths(6),
                                'task_name' => 'Projektarbeit', // Default
                                'role' => Assignment::ROLE_TEAM_MEMBER,
                                'source' => Assignment::SOURCE_MOCO_SYNC, // ✅ Tracking!
                                'is_active' => true,
                                'display_order' => 0,
                            ]
                        );
                    }

                    $created++;
                }

                if ($bar) {
                    $bar->advance();
                }

            } catch (\Exception $e) {
                $errors++;
                Log::error('MOCO Contract Sync failed', [
                    'project_id' => $project->id,
                    'moco_id' => $project->moco_id,
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

        // Zusammenfassung
        $this->table(
            ['Status', 'Anzahl', 'Details'],
            [
                ['✅ Erstellt/Aktualisiert', $created, 'Assignments aus MOCO Contracts'],
                ['⏭️  Übersprungen', $skipped, 'Bereits vorhanden'],
                ['📋 Keine Contracts', $noContracts, 'Projekte ohne Contracts'],
                ['❌ Fehler', $errors, 'Fehlgeschlagen'],
            ]
        );

        if ($isDryRun) {
            $this->newLine();
            $this->info('💡 Führe den Command ohne --dry-run aus um die Änderungen zu speichern:');
            $this->line('   php artisan sync:moco-contracts');
        } else {
            $this->newLine();
            $this->info('✅ Sync abgeschlossen!');
            $this->line('📊 Öffne das Gantt-Diagramm um ALLE Mitarbeiter zu sehen:');
            $this->line('   http://127.0.0.1:8000/gantt');
            
            // Log Success zu MocoSyncLog
            MocoSyncLog::create([
                'sync_type' => 'contracts',
                'status' => 'completed',
                'started_at' => $startTime,
                'completed_at' => now(),
                'items_processed' => $created + $skipped + $noContracts,
                'items_created' => $created,
                'items_updated' => 0,
                'items_skipped' => $skipped + $noContracts,
            ]);
        }

        return $created > 0 ? 0 : 1;
    }
}
