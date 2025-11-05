<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Assignment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncResponsibleToAssignments extends Command
{
    protected $signature = 'sync:responsible-to-assignments 
                            {--dry-run : Zeigt nur Vorschau ohne Änderungen}
                            {--force : Überschreibt bestehende Assignments}';

    protected $description = 'Erstellt Assignments für Projekte basierend auf responsible_id (Fallback wenn keine Assignments existieren)';

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔄 Sync Responsible → Assignments');
        $this->newLine();

        // 1. Projekte mit responsible_id aber ohne Assignments
        $query = Project::with('responsible')
            ->whereNotNull('responsible_id')
            ->whereHas('responsible'); // Employee existiert

        if (!$force) {
            // Nur Projekte OHNE Assignments
            $query->doesntHave('assignments');
        }

        $projects = $query->get();

        if ($projects->isEmpty()) {
            $this->warn('⚠️  Keine Projekte gefunden die synchronisiert werden müssen.');
            return 0;
        }

        $this->info("📊 Gefundene Projekte: {$projects->count()}");
        $this->newLine();

        if ($isDryRun) {
            $this->warn('🔍 DRY-RUN MODE - Keine Änderungen werden gespeichert');
            $this->newLine();
        }

        $created = 0;
        $skipped = 0;
        $errors = 0;

        $bar = $this->output->createProgressBar($projects->count());
        $bar->start();

        foreach ($projects as $project) {
            try {
                $responsible = $project->responsible;

                // Prüfe ob Assignment bereits existiert
                if (!$force && $project->assignments()->where('employee_id', $responsible->id)->exists()) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                if (!$isDryRun) {
                    // Erstelle Assignment
                    Assignment::create([
                        'project_id' => $project->id,
                        'employee_id' => $responsible->id,
                        'weekly_hours' => 20, // Default: 20h/Woche
                        'start_date' => $project->start_date ?? now(),
                        'end_date' => $project->end_date ?? now()->addMonths(3),
                        'task_name' => 'Projektleitung', // Default Task
                        'role' => Assignment::ROLE_PROJECT_LEAD, // 👑 Verantwortlicher = Project Lead
                        'source' => Assignment::SOURCE_RESPONSIBLE_FALLBACK, // ✅ Tracking
                        'is_active' => true,
                        'display_order' => 0,
                    ]);
                }

                $created++;
                $bar->advance();

            } catch (\Exception $e) {
                $errors++;
                Log::error('Assignment creation failed', [
                    'project_id' => $project->id,
                    'error' => $e->getMessage(),
                ]);
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        // Zusammenfassung
        $this->table(
            ['Status', 'Anzahl', 'Details'],
            [
                ['✅ Erstellt', $created, 'Neue Assignments'],
                ['⏭️  Übersprungen', $skipped, 'Bereits vorhanden'],
                ['❌ Fehler', $errors, 'Fehlgeschlagen'],
            ]
        );

        if ($isDryRun) {
            $this->newLine();
            $this->info('💡 Führe den Command ohne --dry-run aus um die Änderungen zu speichern:');
            $this->line('   php artisan sync:responsible-to-assignments');
        } else {
            $this->newLine();
            $this->info('✅ Sync abgeschlossen!');
            $this->line('📊 Öffne das Gantt-Diagramm um die Mitarbeiter zu sehen:');
            $this->line('   http://127.0.0.1:8000/gantt');
        }

        return $created > 0 ? 0 : 1;
    }
}
