<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

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
        'moco_created_at',
        'source'
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

    // Ein Projekt hat viele Mitarbeiter über Assignments
    public function employees()
    {
        return $this->belongsToMany(Employee::class, 'assignments')
            ->withPivot('weekly_hours', 'start_date', 'end_date', 'task_name', 'task_description')
            ->withTimestamps();
    }

    /**
     * Holt alle zugewiesenen Personen für ein Projekt
     * 
     * Datenquellen-Priorität:
     * 1. Übergebene MOCO-Daten ($mocoTeamData)
     * 2. Lokale Assignments (assignments-Tabelle)
     * 3. Fallback: Verantwortlicher (responsible_id)
     * 4. Leer: Keine Zuweisung
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
                    $name = $assignment->employee->first_name . ' ' . $assignment->employee->last_name;
                    // Füge "(Inaktiv)" Badge für inaktive Mitarbeiter hinzu
                    if (!$assignment->employee->is_active) {
                        $name .= ' (Inaktiv)';
                    }
                    return $name;
                }
                return null;
            })
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        if (!empty($localPersons)) {
            return $localPersons;
        }

        // 3. Fallback: Verantwortlicher (wenn Assignments leer)
        // GRUND: Sicherstellt dass jedes Projekt mindestens eine Person anzeigt
        // ALTERNATIVE: UI-Zuweisung bleibt jederzeit möglich
        if ($this->responsible_id && $this->responsible) {
            $name = $this->responsible->first_name . ' ' . $this->responsible->last_name;
            // Füge "(Inaktiv)" Badge für inaktive Verantwortliche hinzu
            if (!$this->responsible->is_active) {
                $name .= ' (Inaktiv)';
            }
            return [$name];
        }

        // 4. Leer: Keine Zuweisung
        return [];
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

    // ============================================================
    // SOURCE-TRACKING LOGIC (Data Protection)
    // ============================================================

    /**
     * Boot method: Auto-set source on create if not provided
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($project) {
            // Auto-detect source if not explicitly set
            if (empty($project->source)) {
                $project->source = $project->moco_id ? 'moco' : 'manual';
            }
        });
    }

    /**
     * Query Scopes for filtering by data source
     */
    public function scopeManual($query)
    {
        return $query->where('source', 'manual');
    }

    public function scopeMoco($query)
    {
        return $query->where('source', 'moco');
    }

    public function scopeImport($query)
    {
        return $query->where('source', 'import');
    }

    /**
     * Accessors for easy source checking
     */
    public function getIsManualAttribute(): bool
    {
        return $this->source === 'manual';
    }

    public function getIsMocoAttribute(): bool
    {
        return $this->source === 'moco';
    }

    public function getIsImportAttribute(): bool
    {
        return $this->source === 'import';
    }

    /**
     * Check if project is safe to delete (only import data)
     */
    public function isSafeToDelete(): bool
    {
        return $this->source === 'import';
    }

    /**
     * Check if project can be auto-deleted by cleanup commands
     * Manual deletion via UI is always allowed (uses soft-delete)
     */
    public function canBeAutoDeleted(): bool
    {
        return $this->source === 'import';
    }
}
