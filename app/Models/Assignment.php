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
        'role',
        'task_description',
        'weekly_hours',
        'start_date',
        'end_date',
        'is_active',
        'priority_level',
        'source',
        'display_order',
        'moco_contract_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'weekly_hours' => 'decimal:2', // Ticket #6: Dezimalstunden (z.B. 2.5 = 2h 30min)
    ];

    // Konstanten für Source
    const SOURCE_MANUAL = 'manual';
    const SOURCE_MOCO_SYNC = 'moco_sync';
    const SOURCE_RESPONSIBLE_FALLBACK = 'responsible_fallback';

    // Konstanten für Roles
    const ROLE_PROJECT_LEAD = 'project_lead';
    const ROLE_DEVELOPER = 'developer';
    const ROLE_DESIGNER = 'designer';
    const ROLE_TESTER = 'tester';
    const ROLE_TEAM_MEMBER = 'team_member';

    // Scope: Nur aktive Assignments
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope: Nur manuelle Assignments
    public function scopeManual($query)
    {
        return $query->where('source', self::SOURCE_MANUAL);
    }

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
