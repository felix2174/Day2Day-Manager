<?php

namespace App\Console\Commands;

use App\Models\Project;
use App\Models\Employee;
use App\Services\MocoService;
use Illuminate\Console\Command;

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
    public function handle(MocoService $mocoService): int
    {
        $this->info('Starting MOCO project synchronization...');

        try {
            // Test connection first
            if (!$mocoService->testConnection()) {
                $this->error('Failed to connect to MOCO API. Please check your credentials.');
                return Command::FAILURE;
            }

            $params = [];
            if ($this->option('active')) {
                $params['active'] = true;
            }

            $mocoProjects = $mocoService->getProjects($params);
            $this->info('Found ' . count($mocoProjects) . ' projects in MOCO');

            $synced = 0;
            $created = 0;
            $updated = 0;

            foreach ($mocoProjects as $mocoProject) {
                // Find or create project
                $project = Project::where('moco_id', $mocoProject['id'])->first();

                // Map MOCO status to our status
                $status = $this->mapStatus($mocoProject);

                // Find responsible employee if exists
                $responsibleId = null;
                if (isset($mocoProject['leader']) && isset($mocoProject['leader']['id'])) {
                    $responsible = Employee::where('moco_id', $mocoProject['leader']['id'])->first();
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
                    'moco_id' => $mocoProject['id'],
                ];

                if ($project) {
                    $project->update($projectData);
                    $updated++;
                    $this->line("Updated: {$mocoProject['name']}");
                } else {
                    Project::create($projectData);
                    $created++;
                    $this->line("Created: {$mocoProject['name']}");
                }

                $synced++;
            }

            $this->newLine();
            $this->info("Synchronization completed!");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total synced', $synced],
                    ['Created', $created],
                    ['Updated', $updated],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error during synchronization: ' . $e->getMessage());
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

