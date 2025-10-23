<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Standardmäßig nur Benutzer anlegen. Alle weiteren Daten kommen über MOCO-Sync.
        $this->call([
            UserSeeder::class,
        ]);

        if (app()->environment('local', 'testing')) {
            $this->call(GanttTestSeeder::class);
        }
    }
}
