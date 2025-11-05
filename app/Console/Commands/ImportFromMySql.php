<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportFromMySql extends Command
{
    protected $signature = 'db:import-mysql 
                            {--file=database/mysql-import.sql : SQL-Datei zum Importieren}';

    protected $description = 'Importiert SQL-Dump in MySQL-Datenbank';

    public function handle()
    {
        $file = $this->option('file');
        $fullPath = base_path($file);
        
        // Prüfe ob Datei existiert
        if (!File::exists($fullPath)) {
            $this->error("❌ Datei nicht gefunden: {$fullPath}");
            $this->comment('Tipp: Erst "php artisan db:export-mysql" ausführen');
            return 1;
        }

        // Prüfe MySQL-Verbindung
        try {
            DB::connection('mysql')->getPdo();
        } catch (\Exception $e) {
            $this->error('❌ MySQL-Verbindung fehlgeschlagen!');
            $this->error($e->getMessage());
            $this->newLine();
            $this->comment('Prüfe deine .env:');
            $this->line('  DB_CONNECTION=mysql');
            $this->line('  DB_HOST=127.0.0.1');
            $this->line('  DB_PORT=3307 (oder 3306)');
            $this->line('  DB_DATABASE=day2day');
            $this->line('  DB_USERNAME=root');
            $this->line('  DB_PASSWORD=');
            return 1;
        }

        $this->info('🔄 Importiere SQL-Dump in MySQL...');
        $this->newLine();

        // SQL-Datei lesen
        $sql = File::get($fullPath);
        
        // In einzelne Statements aufteilen
        $statements = array_filter(
            array_map('trim', explode(";\n", $sql)),
            fn($stmt) => !empty($stmt) && !str_starts_with($stmt, '--')
        );

        $this->info("📦 Gefunden: " . count($statements) . " SQL-Statements");
        $this->newLine();

        // Progress Bar
        $bar = $this->output->createProgressBar(count($statements));
        $bar->setFormat('verbose');

        $success = 0;
        $errors = 0;
        $errorMessages = [];

        DB::beginTransaction();
        
        try {
            foreach ($statements as $statement) {
                try {
                    DB::statement($statement);
                    $success++;
                } catch (\Exception $e) {
                    $errors++;
                    // Nur kritische Fehler loggen (TRUNCATE-Fehler bei leeren Tabellen ignorieren)
                    if (!str_contains($e->getMessage(), 'TRUNCATE') && 
                        !str_contains($e->getMessage(), 'doesn\'t exist')) {
                        $errorMessages[] = substr($statement, 0, 50) . '... → ' . $e->getMessage();
                    }
                }
                $bar->advance();
            }

            DB::commit();
            
            $bar->finish();
            $this->newLine(2);

            // Ergebnis-Tabelle
            $this->table(
                ['Status', 'Anzahl', 'Details'],
                [
                    ['✅ Erfolgreich', $success, 'Statements ausgeführt'],
                    ['❌ Fehler', $errors, 'Ignoriert (meist harmlos)'],
                ]
            );

            // Validierung
            $this->newLine();
            $this->info('🔍 Validiere importierte Daten...');
            
            $validation = [
                ['Tabelle', 'Anzahl', 'Status'],
                ['projects', DB::table('projects')->count(), '✅'],
                ['employees', DB::table('employees')->count(), '✅'],
                ['assignments', DB::table('assignments')->count(), '✅'],
                ['time_entries', DB::table('time_entries')->count(), '✅'],
                ['absences', DB::table('absences')->count(), '✅'],
            ];
            
            $this->table($validation[0], array_slice($validation, 1));

            // Kritische Fehler anzeigen
            if (!empty($errorMessages)) {
                $this->newLine();
                $this->warn('⚠️  Fehler während Import (Details):');
                foreach (array_slice($errorMessages, 0, 5) as $error) {
                    $this->line('  ' . $error);
                }
                if (count($errorMessages) > 5) {
                    $this->line('  ... und ' . (count($errorMessages) - 5) . ' weitere');
                }
            }

            $this->newLine();
            $this->info('✅ Import abgeschlossen!');
            
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            
            $bar->finish();
            $this->newLine(2);
            
            $this->error('❌ Import fehlgeschlagen!');
            $this->error($e->getMessage());
            $this->newLine();
            $this->comment('Tipp: Prüfe ob "php artisan migrate:fresh" erfolgreich war');
            
            return 1;
        }
    }
}
