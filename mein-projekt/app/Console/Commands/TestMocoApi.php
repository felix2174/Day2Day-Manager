<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MocoService;

class TestMocoApi extends Command
{
    protected $signature = 'moco:test-api {projectId?}';
    protected $description = 'Testet die MOCO API direkt';

    public function handle()
    {
        $projectId = $this->argument('projectId') ?? 947236903; // Adis Relaunch
        
        $this->info("🔍 Teste MOCO API...");
        $this->line("");
        
        try {
            $mocoService = app(MocoService::class);
            
            // Test 1: Verbindung
            $this->comment("Test 1: API Verbindung");
            $connected = $mocoService->testConnection();
            if ($connected) {
                $this->info("   ✅ API Verbindung erfolgreich");
            } else {
                $this->error("   ❌ API Verbindung fehlgeschlagen");
                return 1;
            }
            $this->line("");
            
            // Test 2: Projekt laden
            $this->comment("Test 2: Projekt laden (ID: {$projectId})");
            $project = $mocoService->getProject($projectId);
            
            if ($project === null) {
                $this->error("   ❌ getProject() gab NULL zurück");
                $this->warn("   Mögliche Gründe:");
                $this->warn("   - Projekt existiert nicht in MOCO");
                $this->warn("   - API-Key hat keine Berechtigung");
                $this->warn("   - Falsche MOCO-Domain konfiguriert");
            } else {
                $this->info("   ✅ Projekt gefunden:");
                $this->line("      Name: " . ($project['name'] ?? 'N/A'));
                $this->line("      ID: " . ($project['id'] ?? 'N/A'));
                $this->line("      Active: " . (isset($project['active']) ? ($project['active'] ? 'Ja' : 'Nein') : 'N/A'));
                $contractCount = isset($project['contracts']) ? count($project['contracts']) : 0;
                $this->line("      Contracts: {$contractCount}");
                
                if ($contractCount > 0 && isset($project['contracts'])) {
                    $this->line("");
                    $this->comment("      Contract Details:");
                    foreach (array_slice($project['contracts'], 0, 3) as $idx => $contract) {
                        $this->line("      Contract #" . ($idx + 1) . ":");
                        $this->line("         user_id: " . ($contract['user_id'] ?? 'MISSING'));
                        $this->line("         user: " . (isset($contract['user']) ? 'EXISTS' : 'MISSING'));
                        if (isset($contract['user'])) {
                            $this->line("         user.id: " . ($contract['user']['id'] ?? 'N/A'));
                            $this->line("         user.firstname: " . ($contract['user']['firstname'] ?? 'N/A'));
                            $this->line("         user.lastname: " . ($contract['user']['lastname'] ?? 'N/A'));
                        }
                    }
                }
            }
            $this->line("");
            
            // Test 3: Projekt Team laden
            $this->comment("Test 3: Projekt Team laden");
            $team = $mocoService->getProjectTeam($projectId);
            
            if ($team === null) {
                $this->error("   ❌ getProjectTeam() gab NULL zurück");
            } elseif (empty($team)) {
                $this->warn("   ⚠️  Team ist leer (Projekt hat keine Contracts)");
            } else {
                $this->info("   ✅ " . count($team) . " Teammitglied(er) gefunden:");
                foreach ($team as $member) {
                    $this->line("      - " . ($member['name'] ?? 'Unknown') . " (user_id: " . ($member['user_id'] ?? 'N/A') . ")");
                }
            }
            $this->line("");
            
            // Test 4: Alle Projekte abrufen (erste 5)
            $this->comment("Test 4: Erste 5 Projekte abrufen");
            $projects = $mocoService->getProjects(['limit' => 5]);
            $this->info("   ✅ " . count($projects) . " Projekte gefunden");
            foreach ($projects as $p) {
                $this->line("      - [" . ($p['id'] ?? 'N/A') . "] " . ($p['name'] ?? 'N/A'));
            }
            
        } catch (\Exception $e) {
            $this->error("❌ EXCEPTION: " . $e->getMessage());
            $this->line("   File: " . $e->getFile() . ':' . $e->getLine());
            return 1;
        }
        
        $this->line("");
        $this->info("✅ Tests abgeschlossen");
        return 0;
    }
}
