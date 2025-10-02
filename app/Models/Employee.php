<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'first_name',
        'last_name',
        'department',
        'weekly_capacity',
        'is_active'
    ];

    // Relationship: Ein Mitarbeiter hat viele Zuweisungen
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    // Relationship: Ein Mitarbeiter hat viele Abwesenheiten
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    // Relationship: Ein Mitarbeiter hat viele Zeiteinträge
    public function timeEntries()
    {
        return $this->hasMany(TimeEntry::class);
    }

    // Prüft ob Mitarbeiter an einem bestimmten Tag verfügbar ist
    public function isAvailable($date)
    {
        return !$this->absences()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Berechnet die aktuelle Auslastung des Mitarbeiters
     */
    public function getCurrentUtilization()
    {
        $now = \Carbon\Carbon::now();
        $weekStart = $now->startOfWeek();
        $weekEnd = $now->endOfWeek();

        $totalHoursWorked = $this->timeEntries()
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->sum('hours');

        $weeklyCapacity = $this->weekly_capacity ?? 40;
        
        return $weeklyCapacity > 0 ? ($totalHoursWorked / $weeklyCapacity) * 100 : 0;
    }

    /**
     * Berechnet die Auslastung für einen bestimmten Zeitraum
     */
    public function getUtilizationForPeriod($startDate, $endDate)
    {
        $totalHoursWorked = $this->timeEntries()
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('hours');

        $days = \Carbon\Carbon::parse($startDate)->diffInDays(\Carbon\Carbon::parse($endDate)) + 1;
        $weeklyCapacity = $this->weekly_capacity ?? 40;
        $totalCapacity = ($weeklyCapacity / 7) * $days;
        
        return $totalCapacity > 0 ? ($totalHoursWorked / $totalCapacity) * 100 : 0;
    }

    /**
     * Gibt die Gesamtstunden für ein Projekt zurück
     */
    public function getHoursForProject($projectId)
    {
        return $this->timeEntries()
            ->where('project_id', $projectId)
            ->sum('hours');
    }

    /**
     * Gibt die aktuellen Projekte mit Stunden zurück
     */
    public function getCurrentProjectsWithHours()
    {
        $now = \Carbon\Carbon::now();
        
        return $this->assignments()
            ->with('project')
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->get()
            ->map(function ($assignment) {
                $hoursWorked = $this->getHoursForProject($assignment->project_id);
                return [
                    'assignment' => $assignment,
                    'project' => $assignment->project,
                    'hours_worked' => $hoursWorked,
                    'hours_planned' => $assignment->weekly_hours,
                    'progress' => $assignment->weekly_hours > 0 ? 
                        min(100, ($hoursWorked / $assignment->weekly_hours) * 100) : 0
                ];
            });
    }
}
