<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $fillable = [
        'employee_id',
        'project_id',
        'weekly_hours',
        'start_date',
        'end_date',
        'priority_level'
    ];

    // Eine Zuweisung gehört zu einem Mitarbeiter
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    // Eine Zuweisung gehört zu einem Projekt
    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
