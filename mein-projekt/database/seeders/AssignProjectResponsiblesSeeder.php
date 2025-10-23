<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\Employee;

class AssignProjectResponsiblesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hole alle aktiven Mitarbeiter
        $employees = Employee::where('is_active', true)->get();
        
        if ($employees->isEmpty()) {
            $this->command->warn('Keine aktiven Mitarbeiter gefunden. Bitte erstellen Sie zuerst Mitarbeiter.');
            return;
        }

        // Hole alle Projekte ohne Verantwortlichen
        $projects = Project::whereNull('responsible_id')->get();
        
        if ($projects->isEmpty()) {
            $this->command->info('Alle Projekte haben bereits einen Verantwortlichen.');
            return;
        }

        $assigned = 0;
        
        foreach ($projects as $project) {
            // Wähle einen zufälligen aktiven Mitarbeiter
            $randomEmployee = $employees->random();
            
            // Weise den Verantwortlichen zu
            $project->update(['responsible_id' => $randomEmployee->id]);
            $assigned++;
            
            $this->command->info("Projekt '{$project->name}' → Verantwortlicher: {$randomEmployee->first_name} {$randomEmployee->last_name}");
        }
        
        $this->command->info("✅ {$assigned} Projekten wurde ein Verantwortlicher zugewiesen.");
    }
}
