<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamAssignmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Lösche alte Team-Zuweisungen
        DB::table('team_assignments')->truncate();

        // Hole Team IDs
        $teams = [
            'entwicklung' => DB::table('teams')->where('name', 'Entwicklungsteam')->first()->id,
            'projektmanagement' => DB::table('teams')->where('name', 'Projektmanagement-Team')->first()->id,
            'support' => DB::table('teams')->where('name', 'Support-Team')->first()->id,
            'frontend' => DB::table('teams')->where('name', 'Frontend-Team')->first()->id,
            'backend' => DB::table('teams')->where('name', 'Backend-Team')->first()->id,
            'devops' => DB::table('teams')->where('name', 'DevOps-Team')->first()->id,
            'qa' => DB::table('teams')->where('name', 'QA-Team')->first()->id,
            'design' => DB::table('teams')->where('name', 'Design-Team')->first()->id,
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

        // Team-Zuweisungen erstellen
        DB::table('team_assignments')->insert([
            // Entwicklungsteam - mehrere Projekte
            [
                'team_id' => $teams['entwicklung'],
                'project_id' => $projects['projektmanagement'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['entwicklung'],
                'project_id' => $projects['crm'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['entwicklung'],
                'project_id' => $projects['ecommerce'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Frontend-Team - UI/UX Projekte
            [
                'team_id' => $teams['frontend'],
                'project_id' => $projects['mobile'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['frontend'],
                'project_id' => $projects['ecommerce'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['frontend'],
                'project_id' => $projects['crm'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Backend-Team - Server-seitige Projekte
            [
                'team_id' => $teams['backend'],
                'project_id' => $projects['projektmanagement'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['backend'],
                'project_id' => $projects['ecommerce'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['backend'],
                'project_id' => $projects['database'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['backend'],
                'project_id' => $projects['chatbot'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // DevOps-Team - Infrastruktur-Projekte
            [
                'team_id' => $teams['devops'],
                'project_id' => $projects['cloud'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['devops'],
                'project_id' => $projects['monitoring'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['devops'],
                'project_id' => $projects['ecommerce'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // QA-Team - Testing für alle Projekte
            [
                'team_id' => $teams['qa'],
                'project_id' => $projects['ecommerce'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['qa'],
                'project_id' => $projects['crm'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['qa'],
                'project_id' => $projects['mobile'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['qa'],
                'project_id' => $projects['projektmanagement'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Design-Team - UX/UI für Frontend-Projekte
            [
                'team_id' => $teams['design'],
                'project_id' => $projects['mobile'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['design'],
                'project_id' => $projects['ecommerce'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['design'],
                'project_id' => $projects['crm'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Projektmanagement-Team - Koordination aller Projekte
            [
                'team_id' => $teams['projektmanagement'],
                'project_id' => $projects['projektmanagement'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['projektmanagement'],
                'project_id' => $projects['crm'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['projektmanagement'],
                'project_id' => $projects['mobile'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['projektmanagement'],
                'project_id' => $projects['ecommerce'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['projektmanagement'],
                'project_id' => $projects['chatbot'],
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Support-Team - Wartung und Legacy
            [
                'team_id' => $teams['support'],
                'project_id' => $projects['legacy'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['support'],
                'project_id' => $projects['crm'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'team_id' => $teams['support'],
                'project_id' => $projects['monitoring'],
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        echo "Team-Zuweisungen erfolgreich erstellt!\n";
        echo "Verschiedene Teams wurden verschiedenen Projekten zugewiesen.\n";
    }
}


















