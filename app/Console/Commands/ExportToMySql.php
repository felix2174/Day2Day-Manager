<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Assignment;
use App\Models\TimeEntry;
use App\Models\Absence;
use App\Models\Team;
use App\Models\TeamAssignment;
use App\Models\GanttFilterSet;
use App\Models\ProjectAssignmentOverride;
use App\Models\MocoSyncLog;

class ExportToMySql extends Command
{
    protected $signature = 'db:export-mysql 
                            {--file=database/mysql-import.sql : Output-Datei für SQL-Dump}';

    protected $description = 'Exportiert SQLite-Daten als MySQL-kompatibles SQL';

    public function handle()
    {
        $file = $this->option('file');
        $fullPath = base_path($file);
        
        $this->info('🔄 Starte Export aus SQLite...');
        $this->newLine();

        // Datenmengen anzeigen
        $stats = [
            ['Tabelle', 'Anzahl'],
            ['Projects', Project::count()],
            ['Employees', Employee::count()],
            ['Assignments', Assignment::count()],
            ['TimeEntries', TimeEntry::count()],
            ['Absences', Absence::count()],
            ['Teams', Team::count()],
            ['TeamAssignments', TeamAssignment::count()],
            ['GanttFilterSets', GanttFilterSet::count()],
            ['ProjectAssignmentOverrides', ProjectAssignmentOverride::count()],
            ['MocoSyncLogs', MocoSyncLog::count()],
        ];
        
        $this->table($stats[0], array_slice($stats, 1));
        $this->newLine();

        // SQL-Export erstellen
        $sql = $this->generateSqlDump();
        
        // In Datei schreiben
        file_put_contents($fullPath, $sql);
        
        $this->info("✅ Export erfolgreich: {$fullPath}");
        $this->info('📦 Dateigröße: ' . round(filesize($fullPath) / 1024, 2) . ' KB');
        $this->newLine();
        
        $this->comment('Nächste Schritte:');
        $this->line('1. MySQL-Datenbank in phpMyAdmin erstellen');
        $this->line('2. .env anpassen (DB_CONNECTION=mysql)');
        $this->line('3. php artisan migrate:fresh ausführen');
        $this->line("4. Import in phpMyAdmin: {$file}");
        
        return 0;
    }

    private function generateSqlDump(): string
    {
        $sql = "-- Day2Day-Manager MySQL Import\n";
        $sql .= "-- Exportiert: " . now()->toDateTimeString() . "\n";
        $sql .= "-- SQLite → MySQL Migration\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

        // Projects
        $sql .= $this->exportTable('projects', Project::all());
        
        // Employees
        $sql .= $this->exportTable('employees', Employee::all());
        
        // Assignments
        $sql .= $this->exportTable('assignments', Assignment::all());
        
        // TimeEntries
        $sql .= $this->exportTable('time_entries', TimeEntry::all());
        
        // Absences
        $sql .= $this->exportTable('absences', Absence::all());
        
        // Teams
        $sql .= $this->exportTable('teams', Team::all());
        
        // TeamAssignments
        $sql .= $this->exportTable('team_assignments', TeamAssignment::all());
        
        // GanttFilterSets
        $sql .= $this->exportTable('gantt_filter_sets', GanttFilterSet::all());
        
        // ProjectAssignmentOverrides
        $sql .= $this->exportTable('project_assignment_overrides', ProjectAssignmentOverride::all());
        
        // MocoSyncLogs
        $sql .= $this->exportTable('moco_sync_logs', MocoSyncLog::all());

        $sql .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";
        
        return $sql;
    }

    private function exportTable(string $tableName, $records): string
    {
        if ($records->isEmpty()) {
            return "-- Tabelle {$tableName}: Keine Daten\n\n";
        }

        $sql = "-- Tabelle: {$tableName}\n";
        $sql .= "TRUNCATE TABLE `{$tableName}`;\n";
        
        foreach ($records as $record) {
            $attributes = $record->getAttributes();
            
            // Spaltennamen
            $columns = array_keys($attributes);
            $columnList = '`' . implode('`, `', $columns) . '`';
            
            // Werte escapen
            $values = array_map(function($value) {
                if (is_null($value)) {
                    return 'NULL';
                }
                if (is_bool($value)) {
                    return $value ? '1' : '0';
                }
                if (is_numeric($value)) {
                    return $value;
                }
                return "'" . addslashes($value) . "'";
            }, $attributes);
            
            $valueList = implode(', ', $values);
            
            $sql .= "INSERT INTO `{$tableName}` ({$columnList}) VALUES ({$valueList});\n";
        }
        
        $sql .= "\n";
        
        return $sql;
    }
}
