<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimeEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lösche alte Zeiteinträge
        DB::table('time_entries')->truncate();

        // Hole Employee IDs
        $employees = [
            'thomas' => DB::table('employees')->where('first_name', 'Thomas')->first()->id,
            'sarah' => DB::table('employees')->where('first_name', 'Sarah')->first()->id,
            'david' => DB::table('employees')->where('first_name', 'David')->first()->id,
            'anna' => DB::table('employees')->where('first_name', 'Anna')->first()->id,
            'michael' => DB::table('employees')->where('first_name', 'Michael')->first()->id,
            'lisa' => DB::table('employees')->where('first_name', 'Lisa')->first()->id,
            'andreas' => DB::table('employees')->where('first_name', 'Andreas')->first()->id,
            'julia' => DB::table('employees')->where('first_name', 'Julia')->first()->id,
            'stefan' => DB::table('employees')->where('first_name', 'Stefan')->first()->id,
            'nadine' => DB::table('employees')->where('first_name', 'Nadine')->first()->id,
            'petra' => DB::table('employees')->where('first_name', 'Petra')->first()->id,
        ];

        // Hole Project IDs
        $projects = [
            'projektmanagement' => DB::table('projects')->where('name', 'LIKE', '%Projektmanagement%')->first()->id,
            'crm' => DB::table('projects')->where('name', 'LIKE', '%CRM%')->first()->id,
            'mobile' => DB::table('projects')->where('name', 'LIKE', '%Mobile%')->first()->id,
            'ecommerce' => DB::table('projects')->where('name', 'LIKE', '%E-Commerce%')->first()->id,
            'database' => DB::table('projects')->where('name', 'LIKE', '%Datenbank%')->first()->id,
            'cloud' => DB::table('projects')->where('name', 'LIKE', '%Cloud%')->first()->id,
            'legacy' => DB::table('projects')->where('name', 'LIKE', '%Legacy%')->first()->id,
            'monitoring' => DB::table('projects')->where('name', 'LIKE', '%Monitoring%')->first()->id,
        ];

        $timeEntries = [];

        // Generiere Zeiteinträge für die letzten 30 Tage
        for ($i = 0; $i < 30; $i++) {
            $date = Carbon::now()->subDays($i);
            
            // Überspringe Wochenenden
            if ($date->isWeekend()) {
                continue;
            }

            // Thomas Müller - Projektmanagement & CRM
            if ($i < 20) { // Projektmanagement (läuft noch)
                $timeEntries[] = [
                    'employee_id' => $employees['thomas'],
                    'project_id' => $projects['projektmanagement'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(3, 6),
                    'description' => 'Backend-Entwicklung und API-Integration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($i < 15) { // CRM (läuft noch)
                $timeEntries[] = [
                    'employee_id' => $employees['thomas'],
                    'project_id' => $projects['crm'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(2, 4),
                    'description' => 'Datenbankoptimierung und Performance-Tuning',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Sarah Weber - Mobile App & E-Commerce
            if ($i < 25) { // Mobile App
                $timeEntries[] = [
                    'employee_id' => $employees['sarah'],
                    'project_id' => $projects['mobile'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(4, 7),
                    'description' => 'React Native Entwicklung und UI-Komponenten',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($i < 10) { // E-Commerce
                $timeEntries[] = [
                    'employee_id' => $employees['sarah'],
                    'project_id' => $projects['ecommerce'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(2, 4),
                    'description' => 'Frontend-Integration und Responsive Design',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Michael Schmidt - E-Commerce & Datenbank
            if ($i < 20) { // E-Commerce
                $timeEntries[] = [
                    'employee_id' => $employees['michael'],
                    'project_id' => $projects['ecommerce'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(5, 8),
                    'description' => 'Backend-Architektur und Payment-Integration',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($i < 12) { // Datenbank
                $timeEntries[] = [
                    'employee_id' => $employees['michael'],
                    'project_id' => $projects['database'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(2, 4),
                    'description' => 'Migration Scripts und Datenvalidierung',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Lisa Wagner - E-Commerce & Mobile
            if ($i < 20) { // E-Commerce
                $timeEntries[] = [
                    'employee_id' => $employees['lisa'],
                    'project_id' => $projects['ecommerce'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(4, 7),
                    'description' => 'Vue.js Frontend und State Management',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($i < 8) { // Mobile
                $timeEntries[] = [
                    'employee_id' => $employees['lisa'],
                    'project_id' => $projects['mobile'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(1, 3),
                    'description' => 'Code Review und Bug Fixes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Andreas Becker - Cloud & Monitoring
            if ($i < 25) { // Cloud
                $timeEntries[] = [
                    'employee_id' => $employees['andreas'],
                    'project_id' => $projects['cloud'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(6, 9),
                    'description' => 'AWS Infrastructure Setup und Docker Container',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($i < 15) { // Monitoring (bereits abgeschlossen)
                $timeEntries[] = [
                    'employee_id' => $employees['andreas'],
                    'project_id' => $projects['monitoring'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(2, 4),
                    'description' => 'Monitoring Dashboard und Alerting',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Julia Richter - Mobile & E-Commerce Design
            if ($i < 22) { // Mobile
                $timeEntries[] = [
                    'employee_id' => $employees['julia'],
                    'project_id' => $projects['mobile'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(3, 6),
                    'description' => 'UX Research und Prototyping',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($i < 8) { // E-Commerce
                $timeEntries[] = [
                    'employee_id' => $employees['julia'],
                    'project_id' => $projects['ecommerce'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(2, 4),
                    'description' => 'UI Design und Style Guide',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Stefan Hoffmann - QA für E-Commerce & CRM
            if ($i < 18) { // E-Commerce
                $timeEntries[] = [
                    'employee_id' => $employees['stefan'],
                    'project_id' => $projects['ecommerce'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(4, 7),
                    'description' => 'Automated Testing und Bug Reports',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($i < 12) { // CRM
                $timeEntries[] = [
                    'employee_id' => $employees['stefan'],
                    'project_id' => $projects['crm'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(3, 5),
                    'description' => 'Integration Testing und Performance Tests',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Anna Fischer - Projektmanagement
            if ($i < 20) { // CRM
                $timeEntries[] = [
                    'employee_id' => $employees['anna'],
                    'project_id' => $projects['crm'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(2, 4),
                    'description' => 'Projektplanung und Stakeholder Management',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if ($i < 15) { // E-Commerce
                $timeEntries[] = [
                    'employee_id' => $employees['anna'],
                    'project_id' => $projects['ecommerce'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(3, 5),
                    'description' => 'Ressourcenplanung und Timeline Management',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Nadine Koch - Projektmanagement
            if ($i < 25) { // Mobile
                $timeEntries[] = [
                    'employee_id' => $employees['nadine'],
                    'project_id' => $projects['mobile'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(3, 6),
                    'description' => 'Agile Methoden und Sprint Planning',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // David Klein - Legacy Support
            if ($i < 28) { // Legacy
                $timeEntries[] = [
                    'employee_id' => $employees['david'],
                    'project_id' => $projects['legacy'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(4, 6),
                    'description' => 'Systemwartung und Bug Fixes',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            // Petra Schulz - Legacy Support
            if ($i < 20) { // Legacy
                $timeEntries[] = [
                    'employee_id' => $employees['petra'],
                    'project_id' => $projects['legacy'],
                    'date' => $date->format('Y-m-d'),
                    'hours' => rand(3, 5),
                    'description' => 'Kundensupport und Dokumentation',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Füge Zeiteinträge in die Datenbank ein
        DB::table('time_entries')->insert($timeEntries);

        echo "Zeiteinträge erfolgreich erstellt!\n";
        echo "Historische Daten für die letzten 30 Arbeitstage wurden generiert.\n";
    }
}









