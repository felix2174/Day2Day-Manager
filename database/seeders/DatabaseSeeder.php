<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            EmployeeSeeder::class,
            TeamSeeder::class,
            ProjectSeeder::class,
            AssignmentSeeder::class,
            AbsenceSeeder::class,
            TeamAssignmentSeeder::class,
            TimeEntrySeeder::class,
            AssignProjectResponsiblesSeeder::class,
        ]);
    }
}
