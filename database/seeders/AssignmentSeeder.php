<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AssignmentSeeder extends Seeder
{
    public function run()
    {
        // Lösche alte Zuweisungen
        DB::table('assignments')->truncate();

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
            'security' => DB::table('projects')->where('name', 'LIKE', '%Security%')->first()->id,
            'cloud' => DB::table('projects')->where('name', 'LIKE', '%Cloud%')->first()->id,
            'legacy' => DB::table('projects')->where('name', 'LIKE', '%Legacy%')->first()->id,
            'chatbot' => DB::table('projects')->where('name', 'LIKE', '%Chatbot%')->first()->id,
            'monitoring' => DB::table('projects')->where('name', 'LIKE', '%Monitoring%')->first()->id,
        ];

        // Zuweisungen erstellen
        DB::table('assignments')->insert([
            // Thomas Müller - Backend-Entwicklung (40h Kapazität)
            [
                'employee_id' => $employees['thomas'],
                'project_id' => $projects['projektmanagement'],
                'start_date' => Carbon::now()->subWeeks(2),
                'end_date' => Carbon::now()->addWeeks(2),
                'weekly_hours' => 20,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['thomas'],
                'project_id' => $projects['crm'],
                'start_date' => Carbon::now()->subWeek(),
                'end_date' => Carbon::now()->addWeeks(8),
                'weekly_hours' => 15,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sarah Weber - Frontend-Entwicklung (35h Kapazität)
            [
                'employee_id' => $employees['sarah'],
                'project_id' => $projects['mobile'],
                'start_date' => Carbon::now()->subWeeks(1),
                'end_date' => Carbon::now()->addWeeks(16),
                'weekly_hours' => 20,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['sarah'],
                'project_id' => $projects['ecommerce'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(12),
                'weekly_hours' => 10,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Michael Schmidt - Backend-Entwicklung (40h Kapazität)
            [
                'employee_id' => $employees['michael'],
                'project_id' => $projects['ecommerce'],
                'start_date' => Carbon::now()->subWeeks(1),
                'end_date' => Carbon::now()->addWeeks(15),
                'weekly_hours' => 25,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['michael'],
                'project_id' => $projects['database'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(8),
                'weekly_hours' => 10,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Lisa Wagner - Frontend-Entwicklung (38h Kapazität)
            [
                'employee_id' => $employees['lisa'],
                'project_id' => $projects['ecommerce'],
                'start_date' => Carbon::now()->subWeeks(1),
                'end_date' => Carbon::now()->addWeeks(15),
                'weekly_hours' => 25,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['lisa'],
                'project_id' => $projects['mobile'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(12),
                'weekly_hours' => 8,
                'priority_level' => 'low',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Andreas Becker - DevOps (40h Kapazität)
            [
                'employee_id' => $employees['andreas'],
                'project_id' => $projects['cloud'],
                'start_date' => Carbon::now()->subWeeks(8),
                'end_date' => Carbon::now()->addWeeks(4),
                'weekly_hours' => 30,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['andreas'],
                'project_id' => $projects['monitoring'],
                'start_date' => Carbon::now()->subWeeks(12),
                'end_date' => Carbon::now()->subWeeks(2),
                'weekly_hours' => 10,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Julia Richter - UX/UI Design (30h Kapazität)
            [
                'employee_id' => $employees['julia'],
                'project_id' => $projects['mobile'],
                'start_date' => Carbon::now()->subWeeks(2),
                'end_date' => Carbon::now()->addWeeks(14),
                'weekly_hours' => 20,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['julia'],
                'project_id' => $projects['ecommerce'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(10),
                'weekly_hours' => 8,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Stefan Hoffmann - Quality Assurance (40h Kapazität)
            [
                'employee_id' => $employees['stefan'],
                'project_id' => $projects['ecommerce'],
                'start_date' => Carbon::now()->subWeeks(1),
                'end_date' => Carbon::now()->addWeeks(12),
                'weekly_hours' => 20,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['stefan'],
                'project_id' => $projects['crm'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(8),
                'weekly_hours' => 15,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Anna Fischer - Projektmanagement (32h Kapazität)
            [
                'employee_id' => $employees['anna'],
                'project_id' => $projects['crm'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(12),
                'weekly_hours' => 10,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['anna'],
                'project_id' => $projects['ecommerce'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(15),
                'weekly_hours' => 15,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Nadine Koch - Projektmanagement (35h Kapazität)
            [
                'employee_id' => $employees['nadine'],
                'project_id' => $projects['mobile'],
                'start_date' => Carbon::now()->subWeeks(1),
                'end_date' => Carbon::now()->addWeeks(16),
                'weekly_hours' => 20,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['nadine'],
                'project_id' => $projects['chatbot'],
                'start_date' => Carbon::now()->addWeeks(8),
                'end_date' => Carbon::now()->addWeeks(20),
                'weekly_hours' => 10,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // David Klein - Support (40h Kapazität) - Teilweise ausgelastet
            [
                'employee_id' => $employees['david'],
                'project_id' => $projects['legacy'],
                'start_date' => Carbon::now()->subWeeks(20),
                'end_date' => Carbon::now()->addWeeks(8),
                'weekly_hours' => 20,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Petra Schulz - Support (25h Kapazität)
            [
                'employee_id' => $employees['petra'],
                'project_id' => $projects['legacy'],
                'start_date' => Carbon::now()->subWeeks(15),
                'end_date' => Carbon::now()->addWeeks(10),
                'weekly_hours' => 15,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // --- Zusätzliche Lastspitzen für Engpässe ---
            // Thomas: zusätzliche Datenbank-Aufgabe (Engpass Backend)
            [
                'employee_id' => $employees['thomas'],
                'project_id' => $projects['database'],
                'start_date' => Carbon::now()->addDays(3),
                'end_date' => Carbon::now()->addWeeks(6),
                'weekly_hours' => 25,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Sarah: zusätzliches CRM-Feature (Engpass Frontend)
            [
                'employee_id' => $employees['sarah'],
                'project_id' => $projects['crm'],
                'start_date' => Carbon::now()->addDays(2),
                'end_date' => Carbon::now()->addWeeks(10),
                'weekly_hours' => 20,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Lisa: zusätzliches Mobile-Redesign (Engpass Frontend)
            [
                'employee_id' => $employees['lisa'],
                'project_id' => $projects['mobile'],
                'start_date' => Carbon::now()->addDays(1),
                'end_date' => Carbon::now()->addWeeks(8),
                'weekly_hours' => 15,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Michael: Security-Hardening zusätzlich zu eCommerce/DB (Engpass Backend)
            [
                'employee_id' => $employees['michael'],
                'project_id' => $projects['security'],
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(7),
                'weekly_hours' => 20,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        echo "Erweiterte Zuweisungen erfolgreich erstellt!\n";
        echo "Verschiedene Auslastungsgrade und Prioritäten wurden simuliert.\n";
    }
}
