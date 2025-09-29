<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SchoolScopeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Questa middleware imposta il context della scuola per le query
     * evitando i problemi di ricorsione dei global scope
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Solo per utenti autenticati
        if (Auth::check()) {
            $user = Auth::user();

            // Solo per utenti con school_id (esclusi super_admin)
            if ($user->school_id && $user->role !== 'super_admin') {
                // Impostiamo il school_id nel session per uso nei query builder
                session(['current_school_id' => $user->school_id]);

                // Opzione alternativa: usare un service container binding
                app()->instance('current_school_id', $user->school_id);
            }
        }

        return $next($request);
    }
}