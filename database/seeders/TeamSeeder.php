<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('teams')->insert([
            [
                'name' => 'Entwicklungsteam',
                'description' => 'Hauptentwicklungsteam für Webanwendungen',
                'department' => 'IT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Projektmanagement-Team',
                'description' => 'Team für Projektplanung und -koordination',
                'department' => 'Management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Support-Team',
                'description' => 'Kundensupport und Wartung',
                'department' => 'Support',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Frontend-Team',
                'description' => 'Spezialisiert auf Benutzeroberflächen und UX/UI',
                'department' => 'IT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Backend-Team',
                'description' => 'Server-seitige Entwicklung und APIs',
                'department' => 'IT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'DevOps-Team',
                'description' => 'Infrastruktur, Deployment und Monitoring',
                'department' => 'IT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'QA-Team',
                'description' => 'Quality Assurance und Testing',
                'department' => 'IT',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Design-Team',
                'description' => 'UX/UI Design und Prototyping',
                'department' => 'Design',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
