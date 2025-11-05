<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    // Nur created_at, kein updated_at
    const UPDATED_AT = null;
    
    protected $fillable = [
        'user_id',
        'action',
        'resource_type',
        'resource_id',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
    ];
    
    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];
    
    // ========== RELATIONSHIPS ==========
    
    /**
     * Audit-Log gehört zu einem User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    // ========== HELPER METHODS ==========
    
    /**
     * Logge eine Aktion
     * 
     * @param string $action create|update|delete|login|logout
     * @param string $resourceType projects|employees|tasks|users|permissions
     * @param int|null $resourceId
     * @param array|null $oldValues
     * @param array|null $newValues
     * @return self
     */
    public static function logAction(
        string $action,
        string $resourceType,
        ?int $resourceId = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'resource_type' => $resourceType,
            'resource_id' => $resourceId,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
    
    /**
     * Logge CREATE
     */
    public static function logCreate(string $resourceType, int $resourceId, array $values): self
    {
        return static::logAction('create', $resourceType, $resourceId, null, $values);
    }
    
    /**
     * Logge UPDATE
     */
    public static function logUpdate(string $resourceType, int $resourceId, array $oldValues, array $newValues): self
    {
        return static::logAction('update', $resourceType, $resourceId, $oldValues, $newValues);
    }
    
    /**
     * Logge DELETE
     */
    public static function logDelete(string $resourceType, int $resourceId, array $oldValues): self
    {
        return static::logAction('delete', $resourceType, $resourceId, $oldValues, null);
    }
    
    /**
     * Logge LOGIN
     */
    public static function logLogin(int $userId): self
    {
        return static::create([
            'user_id' => $userId,
            'action' => 'login',
            'resource_type' => 'users',
            'resource_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
    
    /**
     * Logge LOGOUT
     */
    public static function logLogout(int $userId): self
    {
        return static::create([
            'user_id' => $userId,
            'action' => 'logout',
            'resource_type' => 'users',
            'resource_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
    
    // ========== SCOPES ==========
    
    /**
     * Filter by Action
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }
    
    /**
     * Filter by Resource Type
     */
    public function scopeResourceType($query, string $resourceType)
    {
        return $query->where('resource_type', $resourceType);
    }
    
    /**
     * Filter by User
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
    
    /**
     * Neueste zuerst
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
    
    /**
     * Nur Logs der letzten X Tage
     */
    public function scopeLastDays($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
