<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GanttFilterSet extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
        'filters',
        'is_default'
    ];

    protected $casts = [
        'filters' => 'array',
        'is_default' => 'boolean'
    ];

    /**
     * Beziehung zum User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope für Benutzer-spezifische Filter-Sets
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope für Standard-Filter-Set
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
