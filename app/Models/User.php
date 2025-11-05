<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'employee_id',
        'role_id',
        'is_active',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }
    
    // ========== RELATIONSHIPS ==========
    
    /**
     * User gehört zu einem Employee
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
    
    /**
     * User hat eine Rolle
     */
    public function role()
    {
        return $this->belongsTo(Role::class);
    }
    
    /**
     * User hat custom Permissions (zusätzlich zu Role-Permissions)
     * Many-to-Many mit granted-Flag
     */
    public function customPermissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions')
            ->withPivot('granted', 'reason', 'granted_by')
            ->withTimestamps();
    }
    
    /**
     * Alle Permissions die der User hat (Role + Custom)
     * CACHED für Performance!
     */
    public function permissions()
    {
        return Cache::remember("user.{$this->id}.permissions", 3600, function () {
            // 1. Role-Permissions
            $rolePermissions = $this->role->permissions ?? collect();
            
            // 2. Custom Permissions (granted=true)
            $grantedPermissions = $this->customPermissions()
                ->wherePivot('granted', true)
                ->get();
            
            // 3. Custom Permissions (granted=false) = REMOVE
            $revokedPermissionIds = $this->customPermissions()
                ->wherePivot('granted', false)
                ->pluck('permissions.id');
            
            // Merge & Filter
            return $rolePermissions
                ->merge($grantedPermissions)
                ->reject(fn($perm) => $revokedPermissionIds->contains($perm->id))
                ->unique('id');
        });
    }
    
    // ========== PERMISSION CHECKS ==========
    
    /**
     * Hat User diese Permission? (Name-based)
     * 
     * @param string $permissionName z.B. "projects.create"
     * @return bool
     */
    public function hasPermission(string $permissionName): bool
    {
        // Admin hat IMMER alle Rechte
        if ($this->isAdmin()) {
            return true;
        }
        
        return $this->permissions()->contains('name', $permissionName);
    }
    
    /**
     * Hat User EINE von mehreren Permissions?
     * 
     * @param array $permissionNames z.B. ["projects.create", "projects.edit"]
     * @return bool
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        foreach ($permissionNames as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Hat User ALLE Permissions?
     * 
     * @param array $permissionNames
     * @return bool
     */
    public function hasAllPermissions(array $permissionNames): bool
    {
        foreach ($permissionNames as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }
    
    // ========== ROLE CHECKS ==========
    
    /**
     * Hat User diese Rolle?
     * 
     * @param string $roleName z.B. "admin", "management", "employee"
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        return $this->role && $this->role->name === $roleName;
    }
    
    /**
     * Hat User EINE von mehreren Rollen?
     * 
     * @param array $roleNames
     * @return bool
     */
    public function hasAnyRole(array $roleNames): bool
    {
        return $this->role && in_array($this->role->name, $roleNames);
    }
    
    /**
     * Ist User Admin?
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }
    
    /**
     * Ist User Management?
     */
    public function isManagement(): bool
    {
        return $this->hasRole('management');
    }
    
    /**
     * Ist User Employee?
     */
    public function isEmployee(): bool
    {
        return $this->hasRole('employee');
    }
    
    /**
     * Kann User Permissions verwalten?
     * (Admin oder Management mit permissions.manage)
     */
    public function canManagePermissions(): bool
    {
        return $this->isAdmin() || $this->hasPermission('permissions.manage');
    }
    
    // ========== UTILITY METHODS ==========
    
    /**
     * Invalidate Permissions Cache (z.B. nach Role-Change)
     */
    public function flushPermissionsCache(): void
    {
        Cache::forget("user.{$this->id}.permissions");
    }
    
    /**
     * Update last login tracking
     */
    public function updateLastLogin(?string $ipAddress = null): void
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress ?? request()->ip(),
        ]);
    }
}
