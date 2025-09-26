<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamAssignment extends Model
{
    protected $fillable = [
        'team_id',
        'project_id'
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
