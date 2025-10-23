<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TimeEntry extends Model
{
    protected $fillable = [
        'employee_id',
        'project_id',
        'date',
        'hours',
        'description',
        'billable',
        'moco_id'
    ];

    protected $casts = [
        'date' => 'date',
        'hours' => 'decimal:2',
        'billable' => 'boolean',
    ];

    // Relationship: Eine Zeiterfassung gehört zu einem Mitarbeiter
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Relationship: Eine Zeiterfassung gehört zu einem Projekt
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
