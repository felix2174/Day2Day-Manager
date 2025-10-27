<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'project_id',
        'task_name',
        'task_description',
        'weekly_hours',
        'start_date',
        'end_date',
        'priority_level',
        'display_order'
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
