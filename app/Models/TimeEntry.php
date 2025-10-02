<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimeEntry extends Model
{
    protected $fillable = [
        'employee_id',
        'project_id',
        'date',
        'hours',
        'description',
        'billable'
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'billable' => 'boolean'
    ];

    // Ein Zeiteintrag gehört zu einem Mitarbeiter
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Ein Zeiteintrag gehört zu einem Projekt
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Aktualisiert den Projektfortschritt nach Zeiteintrag
     */
    public static function boot()
    {
        parent::boot();

        static::saved(function ($timeEntry) {
            $timeEntry->project->updateProgress();
        });

        static::deleted(function ($timeEntry) {
            $timeEntry->project->updateProgress();
        });
    }

    /**
     * Gibt die Gesamtstunden für einen Mitarbeiter an einem Projekt zurück
     */
    public static function getTotalHoursForEmployeeProject($employeeId, $projectId)
    {
        return static::where('employee_id', $employeeId)
                    ->where('project_id', $projectId)
                    ->sum('hours');
    }

    /**
     * Gibt die Gesamtstunden für ein Projekt zurück
     */
    public static function getTotalHoursForProject($projectId)
    {
        return static::where('project_id', $projectId)
                    ->sum('hours');
    }

    /**
     * Gibt die Gesamtstunden für einen Mitarbeiter in einem Zeitraum zurück
     */
    public static function getTotalHoursForEmployeeInPeriod($employeeId, $startDate, $endDate)
    {
        return static::where('employee_id', $employeeId)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->sum('hours');
    }
}