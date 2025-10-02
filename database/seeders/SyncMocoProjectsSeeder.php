<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\MocoService;
use App\Models\Project;
use Exception;

class SyncMocoProjectsSeeder extends Seeder
{
    protected $mocoService;

    public function __construct()
    {
        $this->mocoService = new MocoService();
    }

    public function run(): void
    {
        $this->command->info('🚀 Starte MOCO-Projekte Synchronisation...');

        try {
            // 1. API-Verbindung testen
            $this->command->info('📡 Teste MOCO API-Verbindung...');
            $apiStatus = $this->mocoService->verifyApiKey();
            
            if (!$apiStatus['valid']) {
                $this->command->error('❌ MOCO API-Verbindung fehlgeschlagen: ' . $apiStatus['error']);
                return;
            }
            
            $this->command->info('✅ MOCO API-Verbindung erfolgreich');

            // 2. Alle MOCO-Projekte laden
            $this->command->info('📋 Lade alle Projekte aus MOCO...');
            $mocoProjects = $this->mocoService->getProjects();
            $this->command->info("📊 Gefunden: " . count($mocoProjects) . " Projekte in MOCO");

            $importedProjects = 0;
            $updatedProjects = 0;
            $errors = [];

            foreach ($mocoProjects as $mocoProject) {
                try {
                    $this->command->info("🔄 Verarbeite Projekt: {$mocoProject['name']} (ID: {$mocoProject['id']})");
                    
                    // Prüfe ob Projekt bereits existiert
                    $existingProject = Project::where('moco_id', $mocoProject['id'])->first();
                    
                    $projectData = [
                        'moco_id' => $mocoProject['id'],
                        'name' => $mocoProject['name'],
                        'description' => $mocoProject['description'] ?? null,
                        'status' => $mocoProject['active'] ? 'active' : 'completed',
                        'start_date' => isset($mocoProject['created_at']) ? date('Y-m-d', strtotime($mocoProject['created_at'])) : null,
                        'end_date' => isset($mocoProject['updated_at']) ? date('Y-m-d', strtotime($mocoProject['updated_at'])) : null,
                        'estimated_hours' => $mocoProject['budget'] ?? null,
                        'hourly_rate' => $mocoProject['hourly_rate'] ?? null,
                        'progress' => 0,
                    ];

                    if ($existingProject) {
                        // Update existing project
                        $existingProject->update($projectData);
                        $updatedProjects++;
                        $this->command->info("✅ Projekt aktualisiert: {$mocoProject['name']}");
                    } else {
                        // Create new project
                        $project = Project::create($projectData);
                        $importedProjects++;
                        $this->command->info("✅ Neues Projekt erstellt: {$mocoProject['name']}");
                    }

                } catch (Exception $e) {
                    $error = "Fehler beim Import von Projekt {$mocoProject['id']} ({$mocoProject['name']}): " . $e->getMessage();
                    $errors[] = $error;
                    $this->command->warn("⚠️ {$error}");
                }
            }

            // 3. Zusammenfassung
            $this->command->info('📊 Synchronisation-Zusammenfassung:');
            $this->command->info("   📋 Gesamt MOCO-Projekte: " . count($mocoProjects));
            $this->command->info("   ➕ Neue Projekte: {$importedProjects}");
            $this->command->info("   🔄 Aktualisierte Projekte: {$updatedProjects}");
            $this->command->info("   ❌ Fehler: " . count($errors));
            $this->command->info("   📊 Lokale Projekte gesamt: " . Project::count());
            
            if (!empty($errors)) {
                $this->command->warn('⚠️ Fehler aufgetreten:');
                foreach ($errors as $error) {
                    $this->command->warn("   - {$error}");
                }
            }
            
            $this->command->info('🎉 MOCO-Projekte Synchronisation abgeschlossen!');

        } catch (Exception $e) {
            $this->command->error('❌ Fehler bei der MOCO-Projekte Synchronisation: ' . $e->getMessage());
        }
    }
}



