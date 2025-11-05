<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     * 
     * Prüft ob der eingeloggte User die angegebene Permission hat.
     * Usage: Route::middleware('permission:projects.create')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  z.B. "projects.create"
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // User muss eingeloggt sein
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Bitte melde dich an um fortzufahren.');
        }
        
        $user = auth()->user();
        
        // User muss aktiv sein
        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')
                ->with('error', 'Dein Account wurde deaktiviert. Kontaktiere einen Administrator.');
        }
        
        // Permission-Check
        if (!$user->hasPermission($permission)) {
            // Log unauthorized access attempt
            \Log::warning('Unauthorized access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'permission' => $permission,
                'route' => $request->path(),
                'ip' => $request->ip(),
            ]);
            
            // Bei AJAX: JSON-Response
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Keine Berechtigung für diese Aktion.',
                    'required_permission' => $permission,
                ], 403);
            }
            
            // Bei normaler Request: Fehlerseite
            abort(403, 'Du hast keine Berechtigung für diese Aktion. Erforderliche Berechtigung: ' . $permission);
        }
        
        return $next($request);
    }
}
