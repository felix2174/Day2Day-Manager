<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Employee;
use App\Services\MocoService;
use Illuminate\Support\Facades\Cache;

class DiagnoseGanttProjects extends Command
{
    protected $signature = 'gantt:diagnose-projects {projectId? : Optionale Projekt-ID für einzelne Analyse}';
    protected $description = 'Diagnostiziert warum Projekte keine Mitarbeiter im Gantt-Diagramm anzeigen';

    public function handle()
    {
        $projectId = $this->argument('projectId');
        
        $this->info("==========================================");
        $this->info("📊 GANTT-PROJEKTE DIAGNOSE");
        $this->info("==========================================");
        $this->line("");

        if ($projectId) {
            $projects = Project::where('id', $projectId)->get();
            if ($projects->isEmpty()) {
                $this->error("❌ Projekt mit ID {$projectId} nicht gefunden!");
                return 1;
            }
        } else {
            $projects = Project::orderBy('name')->limit(20)->get();
            $this->info("Analysiere die ersten 20 Projekte...");
            $this->line("");
        }

        $totalProjects = 0;
        $projectsWithEmployees = 0;
        $projectsWithoutEmployees = 0;
        $mocoApiErrors = 0;

        foreach ($projects as $project) {
            $totalProjects++;
            $this->analyzeProject($project, $totalProjects, $projectsWithEmployees, $projectsWithoutEmployees, $mocoApiErrors);
        }

        // Zusammenfassung
        $this->line("");
        $this->info("==========================================");
        $this->info("📈 ZUSAMMENFASSUNG");
        $this->info("==========================================");
        $this->info("Analysierte Projekte: {$totalProjects}");
        $this->info("✅ Mit Mitarbeitern: {$projectsWithEmployees}");
        $this->error("❌ Ohne Mitarbeiter: {$projectsWithoutEmployees}");
        if ($mocoApiErrors > 0) {
            $this->warn("⚠️  MOCO API Fehler: {$mocoApiErrors}");
        }

        return 0;
    }

    private function analyzeProject(Project $project, &$totalProjects, &$projectsWithEmployees, &$projectsWithoutEmployees, &$mocoApiErrors)
    {
        $this->info("------------------------------------------");
        $this->info("Projekt #{$totalProjects}: {$project->name}");
        $this->info("------------------------------------------");
        $this->line("ID: {$project->id}");
        $this->line("MOCO-ID: " . ($project->moco_id ?? 'NICHT GESETZT'));
        $this->line("Status: {$project->status}");
        $this->line("Zeitraum: " . ($project->start_date ?? 'N/A') . " bis " . ($project->end_date ?? 'N/A'));
        $this->line("");

        // SOURCE 1: Lokale Assignments prüfen
        $this->comment("📋 SOURCE 1: Lokale Assignments");
        $assignments = $project->assignments()->with('employee')->get();
        $assignmentCount = $assignments->count();
        
        if ($assignmentCount > 0) {
            $this->info("   ✅ {$assignmentCount} Assignment(s) gefunden");
            foreach ($assignments->take(3) as $assignment) {
                $empName = $assignment->employee 
                    ? "{$assignment->employee->first_name} {$assignment->employee->last_name}" 
                    : "EMPLOYEE FEHLT";
                $this->line("      - {$empName} ({$assignment->task})");
            }
            if ($assignmentCount > 3) {
                $this->line("      ... und " . ($assignmentCount - 3) . " weitere");
            }
            $projectsWithEmployees++;
        } else {
            $this->warn("   ⚠️  Keine lokalen Assignments vorhanden");
        }
        $this->line("");

        // SOURCE 2: MOCO API prüfen (nur wenn keine lokalen Assignments)
        if ($assignmentCount === 0) {
            $this->comment("🌐 SOURCE 2: MOCO API Fallback");
            
            if (!$project->moco_id) {
                $this->error("   ❌ MOCO-ID fehlt - API kann nicht aufgerufen werden!");
                $this->warn("   💡 Lösung: Sync mit MOCO durchführen oder moco_id manuell setzen");
                $projectsWithoutEmployees++;
                $this->line("");
                return;
            }

            try {
                $mocoService = app(MocoService::class);
                
                // Cache prüfen
                $cacheKey = 'moco:project_team:' . $project->moco_id;
                $cached = Cache::get($cacheKey);
                if ($cached !== null) {
                    $this->line("   ℹ️  Cache gefunden (30 Min TTL)");
                }

                // Live API Call
                $this->line("   🔍 Rufe getProjectTeam({$project->moco_id}) auf...");
                $mocoTeam = $mocoService->getProjectTeam((int)$project->moco_id);
                
                if ($mocoTeam === null) {
                    $this->error("   ❌ MOCO API gab NULL zurück (Projekt nicht gefunden oder API-Fehler)");
                    $mocoApiErrors++;
                    $projectsWithoutEmployees++;
                } elseif (empty($mocoTeam)) {
                    $this->warn("   ⚠️  MOCO API gab leeres Array zurück (Projekt hat keine Contracts)");
                    $this->line("   💡 Im MOCO müssen User dem Projekt per Contract zugewiesen werden");
                    $projectsWithoutEmployees++;
                } else {
                    $this->info("   ✅ {count($mocoTeam)} MOCO-Teammitglied(er) gefunden:");
                    
                    $mappingSuccess = 0;
                    $mappingFailed = 0;
                    
                    foreach ($mocoTeam as $member) {
                        $userId = $member['user_id'] ?? null;
                        $userName = $member['name'] ?? 'Unknown';
                        $hoursPerWeek = $member['hours_per_week'] ?? 'N/A';
                        
                        $this->line("      - {$userName} (MOCO user_id: {$userId}, {$hoursPerWeek}h/Woche)");
                        
                        if ($userId) {
                            // Prüfe ob lokaler Employee existiert
                            $localEmployee = Employee::where('moco_id', $userId)->first();
                            
                            if ($localEmployee) {
                                $this->info("         ✓ Gemappt zu: {$localEmployee->first_name} {$localEmployee->last_name} (ID: {$localEmployee->id})");
                                $mappingSuccess++;
                            } else {
                                $this->error("         ✗ KEIN lokaler Employee mit moco_id={$userId} gefunden!");
                                $mappingFailed++;
                            }
                        }
                    }
                    
                    $this->line("");
                    if ($mappingSuccess > 0) {
                        $this->info("   ✅ Mapping erfolgreich: {$mappingSuccess} von " . count($mocoTeam));
                        $projectsWithEmployees++;
                    } else {
                        $this->error("   ❌ Mapping fehlgeschlagen: 0 von " . count($mocoTeam) . " Mitarbeitern gemappt");
                        $this->warn("   💡 Lösung: php artisan moco:sync-employees ausführen");
                        $projectsWithoutEmployees++;
                    }
                }
                
            } catch (\Exception $e) {
                $this->error("   ❌ EXCEPTION: " . $e->getMessage());
                $this->line("   Stack: " . $e->getFile() . ':' . $e->getLine());
                $mocoApiErrors++;
                $projectsWithoutEmployees++;
            }
        }

        $this->line("");
    }
}
