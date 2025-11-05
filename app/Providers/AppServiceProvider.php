<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ========== RBAC GATES ==========
        // Define Gates für Laravel's @can/@cannot Direktiven
        
        // Super-Admin bypass: Admin hat IMMER alle Rechte
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true; // Admin bypass
            }
        });
        
        // Permission-based Gates (automatisch für alle Permissions)
        // z.B. Gate::allows('projects.create')
        Gate::define('*', function (User $user, string $ability) {
            return $user->hasPermission($ability);
        });
    }
}
