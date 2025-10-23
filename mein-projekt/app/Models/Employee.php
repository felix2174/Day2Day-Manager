<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'department',
        'weekly_capacity',
        'is_active',
        'timeline_order',
        'email',
        'phone',
        'role',
        'position',
        'hourly_rate'
    ];

    // Relationship: Ein Mitarbeiter hat viele Zuweisungen
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

    // Relationship: Ein Mitarbeiter hat viele Abwesenheiten
    public function absences()
    {
        return $this->hasMany(Absence::class);
    }

    // Prüft ob Mitarbeiter an einem bestimmten Tag verfügbar ist
    public function isAvailable($date)
    {
        return !$this->absences()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }
}  // <- Klasse endet hier
