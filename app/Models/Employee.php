<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'moco_id',
        'source',
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
    
    // Relationship: Ein Mitarbeiter hat einen User-Account
    public function user()
    {
        return $this->hasOne(User::class);
    }

    // Prüft ob Mitarbeiter an einem bestimmten Tag verfügbar ist
    public function isAvailable($date)
    {
        return !$this->absences()
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
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

        static::creating(function ($employee) {
            // Auto-detect source if not explicitly set
            if (empty($employee->source)) {
                $employee->source = $employee->moco_id ? 'moco' : 'manual';
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
     * Check if employee is safe to delete (only import data)
     */
    public function isSafeToDelete(): bool
    {
        return $this->source === 'import';
    }

    /**
     * Check if employee can be auto-deleted by cleanup commands
     * Manual deletion via UI is always allowed (uses soft-delete)
     */
    public function canBeAutoDeleted(): bool
    {
        return $this->source === 'import';
    }
}  // <- Klasse endet hier
