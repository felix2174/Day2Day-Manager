<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
    ];
    
    protected $casts = [
        'level' => 'integer',
    ];
    
    // ========== RELATIONSHIPS ==========
    
    /**
     * Rolle hat viele Users
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Rolle hat viele Permissions (Many-to-Many)
     */
    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')
            ->withTimestamps();
    }
    
    // ========== HELPER METHODS ==========
    
    /**
     * Gebe Permission zu dieser Rolle hinzu
     * 
     * @param Permission|int $permission
     * @return void
     */
    public function grantPermission($permission): void
    {
        $permissionId = is_object($permission) ? $permission->id : $permission;
        
        if (!$this->permissions()->where('permissions.id', $permissionId)->exists()) {
            $this->permissions()->attach($permissionId);
        }
    }
    
    /**
     * Entziehe Permission von dieser Rolle
     * 
     * @param Permission|int $permission
     * @return void
     */
    public function revokePermission($permission): void
    {
        $permissionId = is_object($permission) ? $permission->id : $permission;
        $this->permissions()->detach($permissionId);
    }
    
    /**
     * Hat diese Rolle die Permission?
     * 
     * @param string $permissionName
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }
    
    /**
     * Synchronisiere Permissions (ersetzt alle bestehenden)
     * 
     * @param array $permissionIds
     * @return void
     */
    public function syncPermissions(array $permissionIds): void
    {
        $this->permissions()->sync($permissionIds);
        
        // Invalidate Cache für alle User mit dieser Rolle
        foreach ($this->users as $user) {
            $user->flushPermissionsCache();
        }
    }
    
    // ========== SCOPES ==========
    
    /**
     * Admin-Rolle
     */
    public function scopeAdmin($query)
    {
        return $query->where('name', 'admin');
    }
    
    /**
     * Management-Rolle
     */
    public function scopeManagement($query)
    {
        return $query->where('name', 'management');
    }
    
    /**
     * Employee-Rolle
     */
    public function scopeEmployee($query)
    {
        return $query->where('name', 'employee');
    }
    
    /**
     * Sortiere nach Level (höchste zuerst)
     */
    public function scopeByLevel($query)
    {
        return $query->orderBy('level', 'desc');
    }
}
