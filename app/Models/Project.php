<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name',
        'description',
        'status',
        'start_date',
        'end_date',
        'estimated_hours',
        'hourly_rate',
        'progress',
        'responsible_id',
        'moco_id'
    ];

    // Ein Projekt hat viele Zuweisungen
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    // Ein Projekt kann mehreren Teams zugewiesen werden
    public function teams()
    {
        return $this->belongsToMany(Team::class, 'team_assignments');
    }

    // Ein Projekt hat einen Verantwortlichen (Mitarbeiter)
    public function responsible()
    {
        return $this->belongsTo(Employee::class, 'responsible_id');
    }
}
