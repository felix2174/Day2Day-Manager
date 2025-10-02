<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\MocoService;
use App\Models\User;
use App\Models\Employee;
use App\Models\Project;
use App\Models\Team;
use App\Models\Assignment;
use App\Models\Absence;
use Exception;

class MocoDataSeeder extends Seeder
{
    protected $mocoService;

    public function __construct()
    {
        $this->mocoService = new MocoService();
    }

    public function run(): void
    {
        $this->command->info('🚀 Starte MOCO Daten-Import...');

        try {
            // 1. API-Verbindung testen
            $this->command->info('📡 Teste MOCO API-Verbindung...');
            $apiStatus = $this->mocoService->verifyApiKey();
            
            if (!$apiStatus['valid']) {
                $this->command->error('❌ MOCO API-Verbindung fehlgeschlagen: ' . $apiStatus['error']);
                return;
            }
            
            $this->command->info('✅ MOCO API-Verbindung erfolgreich');

            // 2. Admin-User erstellen
            $this->command->info('👤 Erstelle Admin-User...');
            $admin = User::firstOrCreate(
                ['email' => 'admin@enodia.de'],
                [
                    'name' => 'Admin',
                    'password' => bcrypt('password'),
                ]
            );
            $this->command->info('✅ Admin-User erstellt: ' . $admin->email);

            // 3. MOCO Benutzer importieren
            $this->command->info('👥 Importiere Benutzer aus MOCO...');
            $mocoUsers = $this->mocoService->getUsers();
            $importedUsers = 0;
            
            foreach ($mocoUsers as $mocoUser) {
                try {
                    $employee = Employee::updateOrCreate(
                        ['moco_id' => $mocoUser['id']],
                        [
                            'first_name' => $mocoUser['firstname'] ?? 'Unbekannt',
                            'last_name' => $mocoUser['lastname'] ?? 'Unbekannt',
                            'department' => $mocoUser['company'] ?? 'Unbekannt',
                            'weekly_capacity' => $mocoUser['weekly_target_hours'] ?? 40,
                            'is_active' => $mocoUser['active'] ?? true,
                            'moco_id' => $mocoUser['id'],
                        ]
                    );
                    $importedUsers++;
                } catch (Exception $e) {
                    $this->command->warn("⚠️ Fehler beim Import von Benutzer {$mocoUser['id']}: " . $e->getMessage());
                }
            }
            $this->command->info("✅ {$importedUsers} Benutzer aus MOCO importiert");

            // 4. MOCO Projekte importieren
            $this->command->info('📋 Importiere Projekte aus MOCO...');
            $mocoProjects = $this->mocoService->getProjects();
            $importedProjects = 0;
            
            foreach ($mocoProjects as $mocoProject) {
                try {
                    $project = Project::updateOrCreate(
                        ['moco_id' => $mocoProject['id']],
                        [
                            'name' => $mocoProject['name'],
                            'description' => $mocoProject['description'] ?? null,
                            'status' => $mocoProject['active'] ? 'active' : 'completed',
                            'start_date' => isset($mocoProject['created_at']) ? date('Y-m-d', strtotime($mocoProject['created_at'])) : null,
                            'end_date' => isset($mocoProject['updated_at']) ? date('Y-m-d', strtotime($mocoProject['updated_at'])) : null,
                            'estimated_hours' => $mocoProject['budget'] ?? null,
                            'hourly_rate' => $mocoProject['hourly_rate'] ?? null,
                            'progress' => 0,
                            'moco_id' => $mocoProject['id'],
                        ]
                    );
                    $importedProjects++;
                } catch (Exception $e) {
                    $this->command->warn("⚠️ Fehler beim Import von Projekt {$mocoProject['id']}: " . $e->getMessage());
                }
            }
            $this->command->info("✅ {$importedProjects} Projekte aus MOCO importiert");

            // 5. MOCO Aktivitäten importieren (für Zuweisungen)
            $this->command->info('⏱️ Importiere Aktivitäten aus MOCO...');
            $mocoActivities = $this->mocoService->getActivities();
            $importedAssignments = 0;
            
            foreach ($mocoActivities as $activity) {
                try {
                    // Prüfe ob user_id existiert
                    if (!isset($activity['user_id']) || !isset($activity['project_id'])) {
                        continue;
                    }
                    
                    // Finde entsprechenden Mitarbeiter
                    $employee = Employee::where('moco_id', $activity['user_id'])->first();
                    if (!$employee) continue;
                    
                    // Finde entsprechendes Projekt
                    $project = Project::where('moco_id', $activity['project_id'])->first();
                    if (!$project) continue;
                    
                    // Erstelle Zuweisung
                    Assignment::updateOrCreate(
                        [
                            'employee_id' => $employee->id,
                            'project_id' => $project->id,
                        ],
                        [
                            'weekly_hours' => $activity['hours'] ?? 10,
                            'start_date' => isset($activity['date']) ? date('Y-m-d', strtotime($activity['date'])) : now()->format('Y-m-d'),
                            'end_date' => isset($activity['date']) ? date('Y-m-d', strtotime($activity['date'])) : null,
                            'priority_level' => 'medium',
                        ]
                    );
                    $importedAssignments++;
                } catch (Exception $e) {
                    $this->command->warn("⚠️ Fehler beim Import von Aktivität {$activity['id']}: " . $e->getMessage());
                }
            }
            $this->command->info("✅ {$importedAssignments} Zuweisungen aus MOCO importiert");

            // 6. Standard-Teams erstellen
            $this->command->info('👥 Erstelle Standard-Teams...');
            $teams = [
                ['name' => 'Entwicklung', 'department' => 'IT', 'description' => 'Software-Entwicklung'],
                ['name' => 'Design', 'department' => 'Design', 'description' => 'UI/UX Design'],
                ['name' => 'Management', 'department' => 'Management', 'description' => 'Projektmanagement'],
            ];
            
            foreach ($teams as $teamData) {
                Team::firstOrCreate(
                    ['name' => $teamData['name']],
                    $teamData
                );
            }
            $this->command->info('✅ Standard-Teams erstellt');

            // 7. Zusammenfassung
            $this->command->info('📊 Import-Zusammenfassung:');
            $this->command->info("   👥 Mitarbeiter: " . Employee::count());
            $this->command->info("   📋 Projekte: " . Project::count());
            $this->command->info("   🔗 Zuweisungen: " . Assignment::count());
            $this->command->info("   👥 Teams: " . Team::count());
            
            $this->command->info('🎉 MOCO Daten-Import erfolgreich abgeschlossen!');

        } catch (Exception $e) {
            $this->command->error('❌ Fehler beim MOCO Daten-Import: ' . $e->getMessage());
        }
    }
}
