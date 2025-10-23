<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Services\MocoService;
use Illuminate\Support\Facades\Cache;

class WarmMocoCache extends Command
{
    protected $signature = 'moco:warm-cache {--clear : Cache vorher leeren}';
    protected $description = 'Lädt alle MOCO Projektteams im Hintergrund und cached sie für 24 Stunden';

    public function handle()
    {
        $this->info("🔥 MOCO Cache Warming gestartet...");
        $this->line("");

        if ($this->option('clear')) {
            $this->comment("🗑️  Lösche alten Cache...");
            Cache::flush();
            $this->info("   ✅ Cache geleert");
            $this->line("");
        }

        $mocoService = app(MocoService::class);
        
        // Hole alle Projekte mit moco_id
        $projects = Project::whereNotNull('moco_id')->get();
        $total = $projects->count();
        
        $this->info("📊 {$total} Projekte mit MOCO-ID gefunden");
        $this->line("");

        $bar = $this->output->createProgressBar($total);
        $bar->setFormat('very_verbose');
        
        $cached = 0;
        $failed = 0;
        $empty = 0;

        foreach ($projects as $project) {
            try {
                $team = $mocoService->getProjectTeam((int)$project->moco_id);
                
                if ($team === null) {
                    $failed++;
                } elseif (empty($team)) {
                    $empty++;
                } else {
                    $cached++;
                }
                
                $bar->advance();
                
                // Kleine Pause um API nicht zu überlasten
                usleep(100000); // 100ms
                
            } catch (\Exception $e) {
                $failed++;
                $bar->advance();
            }
        }

        $bar->finish();
        $this->line("");
        $this->line("");

        // Zusammenfassung
        $this->info("==========================================");
        $this->info("✅ MOCO Cache Warming abgeschlossen");
        $this->info("==========================================");
        $this->info("Gesamt: {$total} Projekte");
        $this->info("✅ Erfolgreich gecached: {$cached}");
        $this->warn("⚠️  Leer (keine Contracts): {$empty}");
        if ($failed > 0) {
            $this->error("❌ Fehler: {$failed}");
        }
        $this->line("");
        $this->info("Cache-Gültigkeit: 24 Stunden");
        $this->line("");

        return 0;
    }
}
