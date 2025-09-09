<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\School;

class SchoolOwnershipMiddleware
{
    /**
     * Handle an incoming request.
     * Verifica che l'admin possa accedere solo ai dati della propria scuola
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Super Admin può accedere a tutto
        if ($user->isSuperAdmin()) {
            return $next($request);
        }

        // Verifica che l'admin abbia una scuola assegnata
        if ($user->isAdmin() && !$user->school_id) {
            abort(403, 'Account amministratore non associato a nessuna scuola.');
        }

        // Verifica ownership per risorse specifiche
        $schoolId = $this->getSchoolIdFromRequest($request);
        
        if ($schoolId && $user->school_id && $user->school_id != $schoolId) {
            abort(403, 'Accesso negato. Non puoi accedere ai dati di altre scuole.');
        }

        return $next($request);
    }

    /**
     * Estrae l'ID della scuola dalla richiesta in base al parametro o route
     */
    private function getSchoolIdFromRequest(Request $request): ?int
    {
        // Verifica parametro school_id nella route
        if ($request->route('school')) {
            $school = $request->route('school');
            return is_object($school) ? $school->id : (int) $school;
        }

        // Verifica parametro school_id nel query string o form data
        if ($request->has('school_id')) {
            return (int) $request->input('school_id');
        }

        // Per risorse che appartengono a una scuola (corsi, iscrizioni, etc.)
        // dovrai estendere questa logica in base alle tue necessità
        
        return null;
    }
}