<?php

namespace App\Console\Commands;

use App\Models\Employee;
use Illuminate\Console\Command;

class RemoveFictionalEmployees extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'employees:remove-fictional';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Entfernt alle fiktiven Mitarbeiter (ohne MOCO-ID) aus der Datenbank';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Suche nach fiktiven Mitarbeitern...');

        // Finde alle Mitarbeiter ohne MOCO-ID
        $fictionalEmployees = Employee::whereNull('moco_id')->get();

        if ($fictionalEmployees->isEmpty()) {
            $this->info('✓ Keine fiktiven Mitarbeiter gefunden.');
            return 0;
        }

        // Zeige gefundene Mitarbeiter in einer Tabelle
        $this->table(
            ['ID', 'Name', 'Email', 'Abteilung', 'Status'],
            $fictionalEmployees->map(fn($emp) => [
                $emp->id,
                $emp->name,
                $emp->email ?? 'N/A',
                $emp->department ?? 'N/A',
                $emp->status ?? 'N/A'
            ])
        );

        $count = $fictionalEmployees->count();
        $this->warn("⚠ Es wurden {$count} fiktive Mitarbeiter (ohne MOCO-ID) gefunden.");

        if (!$this->confirm('Möchten Sie diese Mitarbeiter wirklich unwiderruflich löschen?', false)) {
            $this->info('Abgebrochen. Keine Änderungen vorgenommen.');
            return 0;
        }

        // Lösche die Mitarbeiter
        $deletedCount = 0;
        foreach ($fictionalEmployees as $employee) {
            try {
                $name = $employee->name;
                $employee->delete();
                $this->line("✓ Gelöscht: {$name}");
                $deletedCount++;
            } catch (\Exception $e) {
                $this->error("✗ Fehler beim Löschen von {$employee->name}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("✓ {$deletedCount} von {$count} fiktiven Mitarbeitern erfolgreich gelöscht.");
        
        return 0;
    }
}
