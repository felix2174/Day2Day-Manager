<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestGanttUI extends Command
{
    protected $signature = 'gantt:test-ui';
    protected $description = 'Testet die Gantt UI-Komponenten (Dropdown, Filter, Zoom)';

    public function handle()
    {
        $this->info("==========================================");
        $this->info("🎨 GANTT UI KOMPONENTEN TEST");
        $this->info("==========================================");
        $this->line("");

        $totalTests = 0;
        $passedTests = 0;

        // Test 1: Alpine.js Check
        $this->comment("Test 1: Alpine.js Verfügbarkeit");
        $layoutFile = resource_path('views/layouts/app.blade.php');
        $layoutContent = file_get_contents($layoutFile);
        
        $totalTests++;
        if (str_contains($layoutContent, 'alpinejs')) {
            $this->info("   ✅ Alpine.js ist eingebunden");
            $passedTests++;
        } else {
            $this->error("   ❌ Alpine.js fehlt im Layout");
            $this->warn("   💡 Füge hinzu: <script defer src=\"https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js\"></script>");
        }
        $this->line("");

        // Test 2: Dropdown Markup
        $this->comment("Test 2: Alpine.js Direktiven");
        $ganttFile = resource_path('views/gantt/index.blade.php');
        $ganttContent = file_get_contents($ganttFile);
        
        $checks = [
            'x-data' => 'Alpine.js Initialisierung',
            '@click' => 'Click Handler',
            '@click.away' => 'Click-away Detection',
            'x-show' => 'Conditional Rendering',
            'x-transition' => 'Smooth Transitions',
        ];
        
        foreach ($checks as $directive => $description) {
            $totalTests++;
            if (str_contains($ganttContent, $directive)) {
                $this->info("   ✅ {$description} ({$directive})");
                $passedTests++;
            } else {
                $this->error("   ❌ {$description} ({$directive}) fehlt");
            }
        }
        $this->line("");

        // Test 3: JavaScript Funktionen
        $this->comment("Test 3: JavaScript Funktionen");
        
        $jsFunctions = [
            'toggleFilters' => 'Filter Panel Toggle',
            'updateFilterIndicators' => 'Filter Badge Update',
            'clearAllFilters' => 'Filter Reset',
        ];
        
        foreach ($jsFunctions as $func => $description) {
            $totalTests++;
            if (str_contains($ganttContent, "function {$func}")) {
                $this->info("   ✅ {$description} ({$func})");
                $passedTests++;
            } else {
                $this->error("   ❌ {$description} ({$func}) fehlt");
            }
        }
        $this->line("");

        // Test 4: Dropdown Menu Items
        $this->comment("Test 4: Dropdown Menü-Items");
        
        $menuItems = [
            'Filter & Suche' => '🔍',
            'Excel Export' => '📊',
            'PDF Export' => '📄',
            'Einstellungen' => '⚙️',
        ];
        
        foreach ($menuItems as $label => $icon) {
            $totalTests++;
            if (str_contains($ganttContent, $label)) {
                $this->info("   ✅ {$icon} {$label}");
                $passedTests++;
            } else {
                $this->warn("   ⚠️  {$icon} {$label} fehlt");
            }
        }
        $this->line("");

        // Test 5: SVG Icons (statt Emojis)
        $this->comment("Test 5: SVG Icons");
        $totalTests++;
        if (preg_match('/<svg.*?viewBox="0 0 24 24".*?>/', $ganttContent)) {
            $this->info("   ✅ SVG Icons vorhanden");
            $passedTests++;
        } else {
            $this->warn("   ⚠️  Keine SVG Icons gefunden");
        }
        $this->line("");

        // Test 6: Filter Indicator
        $this->comment("Test 6: Filter Badge Anzeige");
        $totalTests++;
        if (str_contains($ganttContent, 'menuFilterIndicator')) {
            $this->info("   ✅ Filter Badge vorhanden");
            $passedTests++;
        } else {
            $this->error("   ❌ Filter Badge fehlt");
        }
        $this->line("");

        // Test 7: Export Routes
        $this->comment("Test 7: Export-Funktionen");
        $totalTests++;
        if (str_contains($ganttContent, "route('gantt.export')")) {
            $this->info("   ✅ Excel Export Route eingebunden");
            $passedTests++;
        } else {
            $this->error("   ❌ Excel Export Route fehlt");
        }
        $this->line("");

        // Zusammenfassung
        $percentage = round(($passedTests / $totalTests) * 100);
        $this->info("==========================================");
        $this->info("📊 TEST ZUSAMMENFASSUNG");
        $this->info("==========================================");
        $this->info("Gesamt Tests: {$totalTests}");
        $this->info("✅ Bestanden: {$passedTests}");
        if ($totalTests - $passedTests > 0) {
            $this->error("❌ Fehlgeschlagen: " . ($totalTests - $passedTests));
        }
        $this->info("Erfolgsrate: {$percentage}%");
        $this->line("");
        
        if ($percentage === 100) {
            $this->info("� Alle Tests bestanden!");
        } elseif ($percentage >= 80) {
            $this->warn("⚠️  Die meisten Tests bestanden, aber es gibt noch Verbesserungspotenzial");
        } else {
            $this->error("❌ Mehrere Tests fehlgeschlagen. Bitte Code überprüfen.");
        }
        
        $this->line("");
        $this->info("�🌐 Teste manuell: http://127.0.0.1:8000/gantt");
        $this->info("🔍 Browser-Konsole: F12 → Prüfe auf JavaScript-Fehler");
        $this->info("🎯 Interaktivität: Klicke auf ⋮ Icon zum Testen");
        $this->line("");

        return $percentage === 100 ? 0 : 1;
    }
}
