<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AbsenceSeeder extends Seeder
{
    public function run(): void
    {
        // Lösche alte Abwesenheiten
        DB::table('absences')->truncate();

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

        DB::table('absences')->insert([
            // Bestehende Abwesenheiten
            [
                'employee_id' => $employees['thomas'],
                'type' => 'urlaub',
                'start_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(9)->format('Y-m-d'),
                'reason' => 'Herbsturlaub',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['sarah'],
                'type' => 'fortbildung',
                'start_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(2)->format('Y-m-d'),
                'reason' => 'IHK-Prüfungsvorbereitung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['david'],
                'type' => 'krankheit',
                'start_date' => Carbon::now()->addDays(1)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'reason' => 'Grippe',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['anna'],
                'type' => 'urlaub',
                'start_date' => Carbon::now()->addDays(10)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
                'reason' => 'Kurzer Urlaub',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Neue Abwesenheiten für mehr Realismus
            [
                'employee_id' => $employees['michael'],
                'type' => 'urlaub',
                'start_date' => Carbon::now()->addDays(15)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(22)->format('Y-m-d'),
                'reason' => 'Weihnachtsurlaub',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['lisa'],
                'type' => 'fortbildung',
                'start_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
                'reason' => 'React Workshop',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['andreas'],
                'type' => 'urlaub',
                'start_date' => Carbon::now()->addDays(20)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(24)->format('Y-m-d'),
                'reason' => 'Silvesterurlaub',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['julia'],
                'type' => 'fortbildung',
                'start_date' => Carbon::now()->addDays(3)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(5)->format('Y-m-d'),
                'reason' => 'UX Design Konferenz',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['stefan'],
                'type' => 'krankheit',
                'start_date' => Carbon::now()->subDays(2)->format('Y-m-d'),
                'end_date' => Carbon::now()->format('Y-m-d'),
                'reason' => 'Erkältung',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['nadine'],
                'type' => 'urlaub',
                'start_date' => Carbon::now()->addDays(25)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(30)->format('Y-m-d'),
                'reason' => 'Neujahrsurlaub',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['petra'],
                'type' => 'fortbildung',
                'start_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(12)->format('Y-m-d'),
                'reason' => 'Kundenservice Training',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Vergangene Abwesenheiten für historische Daten
            [
                'employee_id' => $employees['thomas'],
                'type' => 'urlaub',
                'start_date' => Carbon::now()->subDays(30)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(25)->format('Y-m-d'),
                'reason' => 'Sommerurlaub',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['sarah'],
                'type' => 'krankheit',
                'start_date' => Carbon::now()->subDays(15)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(13)->format('Y-m-d'),
                'reason' => 'Migräne',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['michael'],
                'type' => 'fortbildung',
                'start_date' => Carbon::now()->subDays(20)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(18)->format('Y-m-d'),
                'reason' => 'Laravel Konferenz',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['andreas'],
                'type' => 'urlaub',
                'start_date' => Carbon::now()->subDays(45)->format('Y-m-d'),
                'end_date' => Carbon::now()->subDays(40)->format('Y-m-d'),
                'reason' => 'Geburtstagsurlaub',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Zukünftige Abwesenheiten
            [
                'employee_id' => $employees['lisa'],
                'type' => 'urlaub',
                'start_date' => Carbon::now()->addDays(40)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(47)->format('Y-m-d'),
                'reason' => 'Osterurlaub',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $employees['julia'],
                'type' => 'fortbildung',
                'start_date' => Carbon::now()->addDays(35)->format('Y-m-d'),
                'end_date' => Carbon::now()->addDays(37)->format('Y-m-d'),
                'reason' => 'Design Thinking Workshop',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
