<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category',
    ];
    
    // ========== RELATIONSHIPS ==========
    
    /**
     * Permission gehört zu vielen Rollen (Many-to-Many)
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }
    
    /**
     * Permission kann an User als Custom Permission vergeben werden
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permissions')
            ->withPivot('granted', 'reason', 'granted_by')
            ->withTimestamps();
    }
    
    // ========== SCOPES ==========
    
    /**
     * Filter by Category
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
    
    /**
     * Projects-Permissions
     */
    public function scopeProjects($query)
    {
        return $query->category('projects');
    }
    
    /**
     * Employees-Permissions
     */
    public function scopeEmployees($query)
    {
        return $query->category('employees');
    }
    
    /**
     * Tasks-Permissions
     */
    public function scopeTasks($query)
    {
        return $query->category('tasks');
    }
    
    /**
     * Time-Permissions
     */
    public function scopeTime($query)
    {
        return $query->category('time');
    }
    
    /**
     * Reports-Permissions
     */
    public function scopeReports($query)
    {
        return $query->category('reports');
    }
    
    /**
     * System-Permissions
     */
    public function scopeSystem($query)
    {
        return $query->category('system');
    }
    
    /**
     * Gruppiere Permissions nach Category
     * 
     * @return \Illuminate\Support\Collection
     */
    public static function grouped()
    {
        return static::all()->groupBy('category');
    }
}
