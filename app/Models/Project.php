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
        'end_date'
    ];

    // Ein Projekt hat viele Zuweisungen
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
