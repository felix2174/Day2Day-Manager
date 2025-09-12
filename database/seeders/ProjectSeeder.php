<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('projects')->insert([
            [
                'name' => 'Webshop Kunde A',
                'description' => 'E-Commerce Lösung mit Laravel und Vue.js',
                'status' => 'active',
                'start_date' => '2025-08-01',
                'end_date' => '2025-12-15',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'App Kunde B',
                'description' => 'Mobile Anwendung für iOS und Android',
                'status' => 'active',
                'start_date' => '2025-09-01',
                'end_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Intranet Upgrade',
                'description' => 'Modernisierung der internen Systeme',
                'status' => 'active',
                'start_date' => '2025-07-15',
                'end_date' => '2025-11-30',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
