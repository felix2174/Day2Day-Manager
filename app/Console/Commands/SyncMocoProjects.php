<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Employee;
use App\Services\MocoService;
use App\Services\MocoSyncLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncMocoProjects extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:sync-projects {--active : Only sync active projects}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize projects from MOCO API';

    /**
     * Execute the console command.
     */
    public function handle(MocoService $mocoService, MocoSyncLogger $logger): int
    {
        Log::info('SyncMocoProjects gestartet.');
        $this->info('Starting MOCO project synchronization...');

        try {
            // Test connection first
            Log::info('Teste MOCO API-Verbindung...');
            if (!$mocoService->testConnection()) {
                Log::error('MOCO API-Verbindung fehlgeschlagen.');
                $this->error('Failed to connect to MOCO API. Please check your credentials.');
                return Command::FAILURE;
            }
            Log::info('MOCO API-Verbindung erfolgreich.');

            $params = [];
            if ($this->option('active')) {
                $params['active'] = true;
                Log::info('Nur aktive Projekte werden synchronisiert.', ['params' => $params]);
            }

            // Start logging
            $logger->start('projects', $params);

            try {
                Log::info('Rufe Projekte von MOCO API ab...', ['params' => $params]);
                $mocoProjects = $mocoService->getProjects($params);
                Log::info('Daten von MOCO API geholt.');
                Log::info(count($mocoProjects) . ' Projekte empfangen.', ['count' => count($mocoProjects)]);
            } catch (\Throwable $e) {
                Log::error('Fehler beim Abrufen der Projekte von MOCO API.', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->error('Failed to fetch projects from MOCO: ' . $e->getMessage());
                $logger->fail($e->getMessage());
                return Command::FAILURE;
            }

            $this->info('Found ' . count($mocoProjects) . ' projects in MOCO');

            $synced = 0;
            $created = 0;
            $updated = 0;
            $skipped = 0;
            $errors = 0;

            foreach ($mocoProjects as $index => $mocoProject) {
                try {
                    // Skip if no ID
                    if (!isset($mocoProject['id'])) {
                        Log::warning('Projekt übersprungen: Keine ID vorhanden.', [
                            'index' => $index,
                            'project_data' => $mocoProject
                        ]);
                        $skipped++;
                        continue;
                    }

                    $projectId = $mocoProject['id'] ?? 'unbekannte_id';
                    
                    Log::info('Verarbeite Projekt-ID: ' . $projectId, [
                        'index' => $index + 1,
                        'total' => count($mocoProjects)
                    ]);
                    
                    // Skip "Auftäge auf Zuruf" projects
                    if (isset($mocoProject['name']) && $mocoProject['name'] === 'Aufträge auf Zuruf') {
                        $skipped++;
                        $this->line("Skipped 'Aufträge auf Zuruf' project (ID: {$projectId})");
                        continue;
                    }

                    // Find or create project
                    $project = Project::where('moco_id', $projectId)->first();

                    // Map MOCO status to our status
                    $status = $this->mapStatus($mocoProject);

                    // Find responsible employee if exists
                    $responsibleId = null;
                    if (isset($mocoProject['leader']) && isset($mocoProject['leader']['id'])) {
                        $leaderId = $mocoProject['leader']['id'];
                        $responsible = Employee::where('moco_id', $leaderId)->first();
                        $responsibleId = $responsible?->id;
                    }

                    $projectData = [
                        'name' => $mocoProject['name'],
                        'description' => $mocoProject['info'] ?? null,
                        'status' => $status,
                        'start_date' => $mocoProject['start_date'] ?? null,
                        'end_date' => $mocoProject['finish_date'] ?? null,
                        'estimated_hours' => $mocoProject['budget'] ?? null,
                        'hourly_rate' => $mocoProject['hourly_rate'] ?? null,
                        'responsible_id' => $responsibleId,
                        'moco_id' => $projectId,
                        'moco_created_at' => $mocoProject['created_at'] ?? null,
                        'source' => 'moco', // CRITICAL: Mark as MOCO data
                    ];

                    if ($project) {
                        $project->update($projectData);
                        $updated++;
                        Log::info('Projekt-ID ' . $projectId . ' erfolgreich in DB gespeichert/aktualisiert.', [
                            'action' => 'updated',
                            'local_id' => $project->id
                        ]);
                        $this->line("Updated: " . ($mocoProject['name'] ?? 'Unknown'));
                    } else {
                        $newProject = Project::create($projectData);
                        $created++;
                        Log::info('Projekt-ID ' . $projectId . ' erfolgreich in DB gespeichert/aktualisiert.', [
                            'action' => 'created',
                            'local_id' => $newProject->id
                        ]);
                        $this->line("Created: " . ($mocoProject['name'] ?? 'Unknown'));
                    }

                    $synced++;
                    
                } catch (\Throwable $e) {
                    $errors++;
                    $errorProjectId = $mocoProject['id'] ?? 'unbekannte_id';
                    
                    Log::error('Fehler bei Verarbeitung von Projekt-ID: ' . $errorProjectId, [
                        'exception_message' => $e->getMessage()
                    ]);
                    
                    $this->error("Error processing project ID: {$errorProjectId}");
                    
                    // Continue with next project
                    continue;
                }
            }

            $this->newLine();
            $this->info("Synchronization completed!");
            
            Log::info('Synchronisierung abgeschlossen.', [
                'synced' => $synced,
                'created' => $created,
                'updated' => $updated,
                'skipped' => $skipped,
                'errors' => $errors,
                'total_projects' => count($mocoProjects)
            ]);
            
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total synced', $synced],
                    ['Created', $created],
                    ['Updated', $updated],
                    ['Skipped', $skipped],
                    ['Errors', $errors],
                ]
            );

            // Complete logging
            $logger->complete($synced, $created, $updated, $skipped);

            Log::info('SyncMocoProjects beendet.');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            Log::error('Kritischer Fehler.', [
                'exception_message' => $e->getMessage()
            ]);
            $this->error('Error during synchronization: ' . $e->getMessage());
            $logger->fail($e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Map MOCO project status to our status
     */
    protected function mapStatus(array $mocoProject): string
    {
        if (isset($mocoProject['active']) && !$mocoProject['active']) {
            return 'abgeschlossen';
        }

        if (isset($mocoProject['finish_date'])) {
            $finishDate = \Carbon\Carbon::parse($mocoProject['finish_date']);
            if ($finishDate->isPast()) {
                return 'abgeschlossen';
            }
        }

        if (isset($mocoProject['start_date'])) {
            $startDate = \Carbon\Carbon::parse($mocoProject['start_date']);
            if ($startDate->isFuture()) {
                return 'geplant';
            }
        }

        return 'in_bearbeitung';
    }
}

