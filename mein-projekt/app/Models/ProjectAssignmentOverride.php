<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectAssignmentOverride extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'project_id',
        'project_name',
        'start_date',
        'end_date',
        'weekly_hours',
        'activity',
        'source_label',
        'is_active',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getStartCarbonAttribute(): Carbon
    {
        return Carbon::parse($this->start_date)->startOfDay();
    }

    public function getEndCarbonAttribute(): Carbon
    {
        $end = $this->end_date ? Carbon::parse($this->end_date) : Carbon::parse($this->start_date);
        return $end->endOfDay();
    }
}





