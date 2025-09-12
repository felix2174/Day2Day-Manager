<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;  // WICHTIG: Nicht vergessen!

class AssignmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('assignments')->insert([
            // Thomas (ID=1) arbeitet am Webshop (ID=1)
            [
                'employee_id' => 1,
                'project_id' => 1,
                'weekly_hours' => 20,
                'start_date' => '2025-08-01',
                'end_date' => null,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Thomas arbeitet auch an App Kunde B
            [
                'employee_id' => 1,
                'project_id' => 2,
                'weekly_hours' => 15,
                'start_date' => '2025-09-01',
                'end_date' => null,
                'priority_level' => 'medium',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Sarah (ID=2) arbeitet an App Kunde B
            [
                'employee_id' => 2,
                'project_id' => 2,
                'weekly_hours' => 20,
                'start_date' => '2025-09-01',
                'end_date' => null,
                'priority_level' => 'high',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Sarah arbeitet am Intranet
            [
                'employee_id' => 2,
                'project_id' => 3,
                'weekly_hours' => 10,
                'start_date' => '2025-07-15',
                'end_date' => null,
                'priority_level' => 'low',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
