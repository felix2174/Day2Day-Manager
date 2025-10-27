<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

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
        'moco_id',
        'moco_created_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'moco_created_at' => 'datetime',
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

    /**
     * Holt alle zugewiesenen Personen für ein Projekt
     * MOCO-Daten haben Vorrang, Fallback auf lokale Assignments
     * 
     * @param array|null $mocoTeamData Vorgefertigte MOCO-Team-Daten
     * @return array Array mit Personennamen
     */
    public function getAssignedPersonsList($mocoTeamData = null): array
    {
        // 1. Versuche MOCO-Daten (falls übergeben)
        if ($mocoTeamData && !empty($mocoTeamData)) {
            if (is_array($mocoTeamData)) {
                return array_values(array_filter(array_map('trim', $mocoTeamData)));
            }

            if (is_string($mocoTeamData)) {
                $persons = array_map('trim', explode(',', $mocoTeamData));
                return array_values(array_filter($persons));
            }
        }

        // 2. Fallback auf lokale Assignments
        $localPersons = $this->assignments
            ->map(function($assignment) {
                if ($assignment->employee) {
                    return $assignment->employee->first_name . ' ' . $assignment->employee->last_name;
                }
                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return $localPersons;
    }

    /**
     * Holt zugewiesene Personen als kommaseparierten String
     * 
     * @param array|null $mocoTeamData Vorgefertigte MOCO-Team-Daten
     * @param int $maxPersons Maximale Anzahl Personen (0 = unbegrenzt)
     * @return string Kommaseparierte Liste der Personennamen
     */
    public function getAssignedPersonsString($mocoTeamData = null, int $maxPersons = 5): string
    {
        $persons = $this->getAssignedPersonsList($mocoTeamData);
        
        if (empty($persons)) {
            return 'Keine Personen zugewiesen';
        }

        if ($maxPersons > 0 && count($persons) > $maxPersons) {
            $persons = array_slice($persons, 0, $maxPersons);
            $remaining = count($this->getAssignedPersonsList($mocoTeamData)) - $maxPersons;
            return implode(', ', $persons) . " (+{$remaining} weitere)";
        }

        return implode(', ', $persons);
    }

    /**
     * Prüft ob das Projekt Personen zugewiesen hat
     * 
     * @param array|null $mocoTeamData Vorgefertigte MOCO-Team-Daten
     * @return bool
     */
    public function hasAssignedPersons($mocoTeamData = null): bool
    {
        return !empty($this->getAssignedPersonsList($mocoTeamData));
    }
}
