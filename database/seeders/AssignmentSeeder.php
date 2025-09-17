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

        // Hole IDs
        $thomasId = DB::table('employees')->where('first_name', 'Thomas')->first()->id;
        $sarahId = DB::table('employees')->where('first_name', 'Sarah')->first()->id;
        $davidId = DB::table('employees')->where('first_name', 'David')->first()->id;
        $annaId = DB::table('employees')->where('first_name', 'Anna')->first()->id;

        $webshopId = DB::table('projects')->where('name', 'LIKE', '%Webshop%')->first()->id;
        $appId = DB::table('projects')->where('name', 'LIKE', '%App%')->first()->id;
        $intranetId = DB::table('projects')->where('name', 'LIKE', '%Intranet%')->first()->id;

        // Zuweisungen erstellen
        DB::table('assignments')->insert([
            // Thomas Müller - 35h von 40h (88% ausgelastet)
            [
                'employee_id' => $thomasId,
                'project_id' => $webshopId,
                'start_date' => Carbon::now()->subWeeks(2),
                'end_date' => Carbon::now()->addWeeks(8),
                'weekly_hours' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'employee_id' => $thomasId,
                'project_id' => $appId,
                'start_date' => Carbon::now()->subWeek(),
                'end_date' => Carbon::now()->addWeeks(4),
                'weekly_hours' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Sarah Weber - 20h von 35h (57% ausgelastet)
            [
                'employee_id' => $sarahId,
                'project_id' => $appId,
                'start_date' => Carbon::now()->subWeeks(3),
                'end_date' => Carbon::now()->addWeeks(6),
                'weekly_hours' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // David Klein - 0h von 40h (0% ausgelastet)
            // Keine Zuweisungen

            // Anna Fischer - 10h von 32h (31% ausgelastet)
            [
                'employee_id' => $annaId,
                'project_id' => $intranetId,
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addWeeks(12),
                'weekly_hours' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        echo "Zuweisungen erfolgreich erstellt!\n";
        echo "- Thomas: 35h/40h (88%)\n";
        echo "- Sarah: 20h/35h (57%)\n";
        echo "- David: 0h/40h (0%)\n";
        echo "- Anna: 10h/32h (31%)\n";
    }
}
