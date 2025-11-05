<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Assignment;
use App\Services\MocoService;
use Illuminate\Support\Facades\Log;

class SyncMocoAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moco:sync-assignments {--force : Löscht bestehende Assignments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronisiert Projekt-Assignments aus MOCO-Contracts';

    /**
     * Execute the console command.
     */
    public function handle(MocoService $mocoService)
    {
        $this->info('🔄 Synchronisiere MOCO-Assignments...');

        if ($this->option('force')) {
            $count = Assignment::count();
            Assignment::truncate();
            $this->warn("⚠️  {$count} bestehende Assignments gelöscht");
        }

        // Hole alle Projekte mit MOCO-ID
        $projects = Project::whereNotNull('moco_id')->get();
        
        if ($projects->isEmpty()) {
            $this->error('❌ Keine Projekte mit MOCO-ID gefunden. Führe zuerst "php artisan moco:sync" aus.');
            return 1;
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $this->info("📦 Verarbeite {$projects->count()} Projekte...");
        $bar = $this->output->createProgressBar($projects->count());

        foreach ($projects as $project) {
            try {
                // Hole Team-Mitglieder für dieses Projekt
                $teamMembers = $mocoService->getProjectTeam($project->moco_id);
                
                if (!is_array($teamMembers) || empty($teamMembers)) {
                    $skipped++;
                    $bar->advance();
                    continue;
                }

                foreach ($teamMembers as $member) {
                    $userId = $member['user_id'] ?? ($member['id'] ?? null);
                    
                    if (!$userId) {
                        continue;
                    }

                    // Finde Mitarbeiter anhand MOCO-ID
                    $employee = Employee::where('moco_id', $userId)->first();
                    
                    if (!$employee) {
                        $skipped++;
                        continue;
                    }

                    // Erstelle oder update Assignment
                    $assignment = Assignment::updateOrCreate(
                        [
                            'project_id' => $project->id,
                            'employee_id' => $employee->id,
                        ],
                        [
                            'task_name' => $member['role'] ?? 'MOCO-Zuweisung',
                            'task_description' => $member['title'] ?? null,
                            'start_date' => $member['start_date'] ?? $project->start_date ?? now(),
                            'end_date' => $member['finish_date'] ?? $member['end_date'] ?? $project->end_date,
                            'weekly_hours' => $member['hours_per_week'] ?? 20,
                        ]
                    );

                    if ($assignment->wasRecentlyCreated) {
                        $created++;
                    } else {
                        $updated++;
                    }
                }
            } catch (\Exception $e) {
                $errors++;
                Log::error("MOCO Assignment Sync Error for Project {$project->id}: " . $e->getMessage());
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("✅ {$created} Assignments erstellt");
        $this->info("🔄 {$updated} Assignments aktualisiert");
        
        if ($skipped > 0) {
            $this->warn("⚠️  {$skipped} Assignments übersprungen (Mitarbeiter nicht gefunden)");
        }
        
        if ($errors > 0) {
            $this->error("❌ {$errors} Fehler aufgetreten (siehe Logs)");
        }

        $totalAssignments = Assignment::count();
        $this->info("📊 Gesamt: {$totalAssignments} Assignments in der Datenbank");

        return 0;
    }
}
