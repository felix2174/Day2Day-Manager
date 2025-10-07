<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class MocoSyncLog extends Model
{
    protected $fillable = [
        'sync_type',
        'status',
        'items_processed',
        'items_created',
        'items_updated',
        'items_skipped',
        'error_message',
        'parameters',
        'started_at',
        'completed_at',
        'duration_seconds',
        'user_id',
    ];

    protected $casts = [
        'parameters' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'items_processed' => 'integer',
        'items_created' => 'integer',
        'items_updated' => 'integer',
        'items_skipped' => 'integer',
        'duration_seconds' => 'integer',
    ];

    // Relationship: Log belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope: Only successful syncs
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'completed');
    }

    // Scope: Only failed syncs
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    // Scope: By sync type
    public function scopeOfType($query, string $type)
    {
        return $query->where('sync_type', $type);
    }

    // Helper: Get duration formatted
    public function getDurationFormatted(): string
    {
        if (!$this->duration_seconds) {
            return 'N/A';
        }

        if ($this->duration_seconds < 60) {
            return $this->duration_seconds . ' Sekunden';
        }

        $minutes = floor($this->duration_seconds / 60);
        $seconds = $this->duration_seconds % 60;
        return $minutes . ' Min ' . $seconds . ' Sek';
    }

    // Helper: Check if sync is in progress
    public function isInProgress(): bool
    {
        return $this->status === 'started' && !$this->completed_at;
    }

    // Helper: Get success rate as percentage
    public function getSuccessRate(): float
    {
        if ($this->items_processed === 0) {
            return 0;
        }

        $successful = $this->items_created + $this->items_updated;
        return round(($successful / $this->items_processed) * 100, 2);
    }
}

