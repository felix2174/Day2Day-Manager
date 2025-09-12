<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsenceSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('absences')->insert([
            [
                'employee_id' => 1, // Thomas
                'type' => 'urlaub',
                'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(14)->format('Y-m-d'),
                'reason' => 'Sommerurlaub',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => 2, // Sarah
                'type' => 'fortbildung',
                'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(4)->format('Y-m-d'),
                'reason' => 'Laravel Workshop',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
