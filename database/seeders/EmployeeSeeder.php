<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('employees')->insert([
            // Bestehende Mitarbeiter
            [
                'first_name' => 'Thomas',
                'last_name' => 'Müller',
                'department' => 'Backend-Entwicklung',
                'weekly_capacity' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Sarah',
                'last_name' => 'Weber',
                'department' => 'Frontend-Entwicklung',
                'weekly_capacity' => 35,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'David',
                'last_name' => 'Klein',
                'department' => 'Support',
                'weekly_capacity' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Anna',
                'last_name' => 'Fischer',
                'department' => 'Projektmanagement',
                'weekly_capacity' => 32,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Neue Mitarbeiter für mehr Vielfalt
            [
                'first_name' => 'Michael',
                'last_name' => 'Schmidt',
                'department' => 'Backend-Entwicklung',
                'weekly_capacity' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Lisa',
                'last_name' => 'Wagner',
                'department' => 'Frontend-Entwicklung',
                'weekly_capacity' => 38,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Andreas',
                'last_name' => 'Becker',
                'department' => 'DevOps',
                'weekly_capacity' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Julia',
                'last_name' => 'Richter',
                'department' => 'UX/UI Design',
                'weekly_capacity' => 30,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Stefan',
                'last_name' => 'Hoffmann',
                'department' => 'Quality Assurance',
                'weekly_capacity' => 40,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Nadine',
                'last_name' => 'Koch',
                'department' => 'Projektmanagement',
                'weekly_capacity' => 35,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Marco',
                'last_name' => 'Bauer',
                'department' => 'Backend-Entwicklung',
                'weekly_capacity' => 40,
                'is_active' => false, // Inaktiv für Tests
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'first_name' => 'Petra',
                'last_name' => 'Schulz',
                'department' => 'Support',
                'weekly_capacity' => 25,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
