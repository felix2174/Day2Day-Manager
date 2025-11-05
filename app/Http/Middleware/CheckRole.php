<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * 
     * Prüft ob der eingeloggte User eine der angegebenen Rollen hat.
     * Usage: Route::middleware('role:admin,management')
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  z.B. "admin", "management"
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
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
        
        // Role-Check
        if (!$user->hasAnyRole($roles)) {
            // Log unauthorized access attempt
            \Log::warning('Unauthorized role access attempt', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'user_role' => $user->role->name ?? 'none',
                'required_roles' => $roles,
                'route' => $request->path(),
                'ip' => $request->ip(),
            ]);
            
            // Bei AJAX: JSON-Response
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Keine Berechtigung für diesen Bereich.',
                    'required_roles' => $roles,
                ], 403);
            }
            
            // Bei normaler Request: Fehlerseite
            abort(403, 'Du hast keine Berechtigung für diesen Bereich. Erforderliche Rolle: ' . implode(' oder ', $roles));
        }
        
        return $next($request);
    }
}
