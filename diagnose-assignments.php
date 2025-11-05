<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "🔍 DIAGNOSE: Warum werden nicht alle Mitarbeiter angezeigt?\n";
echo "================================================================\n\n";

// 1. Prüfe MOCO-Test-Projekt (sollte mehrere Mitarbeiter haben)
echo "1️⃣ MOCO-Test-Projekt (Gantt-Testprojekt):\n";
echo "-------------------------------------------\n";
$mocoTest = \App\Models\Project::where('name', 'Gantt-Testprojekt')->with('assignments.employee')->first();
if ($mocoTest) {
    echo "   Assignments: {$mocoTest->assignments->count()}\n";
    foreach ($mocoTest->assignments as $a) {
        echo "   • {$a->employee->first_name} {$a->employee->last_name} ({$a->role})\n";
    }
} else {
    echo "   ❌ Projekt nicht gefunden\n";
}

echo "\n2️⃣ Alle Projekte mit Assignments:\n";
echo "-------------------------------------------\n";
$projects = \App\Models\Project::has('assignments')->withCount('assignments')->orderBy('assignments_count', 'desc')->limit(10)->get();
foreach ($projects as $p) {
    echo "   {$p->name}: {$p->assignments_count} Mitarbeiter\n";
}

echo "\n3️⃣ Total Assignments pro Source:\n";
echo "-------------------------------------------\n";
$stats = \App\Models\Assignment::selectRaw('source, COUNT(*) as count')->groupBy('source')->get();
foreach ($stats as $stat) {
    echo "   {$stat->source}: {$stat->count}\n";
}

echo "\n4️⃣ Problem: Warum nur 1 Mitarbeiter pro Projekt?\n";
echo "-------------------------------------------\n";
$singleAssignmentProjects = \App\Models\Project::has('assignments', '=', 1)->count();
$multiAssignmentProjects = \App\Models\Project::has('assignments', '>', 1)->count();
echo "   Projekte mit genau 1 Assignment: $singleAssignmentProjects\n";
echo "   Projekte mit >1 Assignments: $multiAssignmentProjects\n";

echo "\n5️⃣ Lösung: Mehr Mitarbeiter zuweisen!\n";
echo "-------------------------------------------\n";
echo "   ✅ Option A: Manuell über UI (Drei-Punkte-Menü → Mitarbeiter hinzufügen)\n";
echo "   ✅ Option B: Aus MOCO synchronisieren (wenn verfügbar)\n";
echo "   ✅ Option C: Bulk-Import basierend auf Projektgröße\n";

echo "\n================================================================\n";
echo "✅ Diagnose abgeschlossen!\n";
