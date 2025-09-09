<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $role
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Super Admin può accedere ovunque
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verifica il ruolo specifico
        switch ($role) {
            case 'super_admin':
                if (!$user->isSuperAdmin()) {
                    abort(403, 'Accesso negato. Solo i Super Amministratori possono accedere.');
                }
                break;
            
            case 'admin':
                if (!$user->isAdmin() && !$user->isSuperAdmin()) {
                    abort(403, 'Accesso negato. Solo gli Amministratori possono accedere.');
                }
                break;
            
            case 'instructor':
                if (!$user->isInstructor() && !$user->isAdmin() && !$user->isSuperAdmin()) {
                    abort(403, 'Accesso negato. Solo gli Istruttori possono accedere.');
                }
                break;
            
            case 'student':
                if (!$user->isStudent() && !$user->isInstructor() && !$user->isAdmin() && !$user->isSuperAdmin()) {
                    abort(403, 'Accesso negato.');
                }
                break;
            
            default:
                abort(403, 'Ruolo non riconosciuto.');
        }

        return $next($request);
    }
}