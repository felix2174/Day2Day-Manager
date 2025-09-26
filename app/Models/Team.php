<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    protected $fillable = [
        'name',
        'description',
        'department'
    ];

    public function projects()
    {
        return $this->belongsToMany(Project::class, 'team_assignments');
    }
}
