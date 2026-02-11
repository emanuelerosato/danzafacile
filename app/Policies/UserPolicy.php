<?php

namespace App\Policies;

use App\Models\User;

/**
 * Policy per l'autorizzazione delle operazioni sugli studenti (User)
 *
 * MULTI-TENANT AUTHORIZATION:
 * - Super Admin: Accesso completo a tutti gli studenti
 * - Admin: Solo studenti della propria scuola (school_id match)
 * - Student: Solo il proprio profilo
 *
 * DEFENSE IN DEPTH:
 * Questa Policy fornisce un layer di autorizzazione aggiuntivo.
 * NON sostituisce il middleware SchoolOwnership né i check manuali esistenti.
 * È una best practice Laravel per centralizzare la logica di autorizzazione.
 *
 * USAGE IN CONTROLLERS:
 * $this->authorize('update', $student);
 * $this->authorize('viewAny', User::class);
 * Gate::allows('delete', $student)
 */
class UserPolicy
{
    /**
     * Determina se l'utente può visualizzare la lista di studenti
     *
     * RULES:
     * - Super Admin: SI (può vedere tutti)
     * - Admin: SI (può vedere studenti della sua scuola)
     * - Student: NO (non può vedere lista altri studenti)
     *
     * @param User $user Utente autenticato che richiede l'operazione
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        // Solo Super Admin e Admin possono vedere la lista studenti
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determina se l'utente può visualizzare un singolo studente
     *
     * RULES:
     * - Super Admin: SI (qualsiasi studente)
     * - Admin: SI (solo se studente appartiene alla sua scuola)
     * - Student: SI (solo il proprio profilo)
     *
     * @param User $user Utente autenticato
     * @param User $student Studente da visualizzare
     * @return bool
     */
    public function view(User $user, User $student): bool
    {
        // Super Admin: accesso illimitato
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin: può visualizzare solo studenti della propria scuola
        if ($user->isAdmin()) {
            return $this->belongsToSameSchool($user, $student);
        }

        // Student: può visualizzare solo il proprio profilo
        if ($user->isStudent()) {
            return $user->id === $student->id;
        }

        return false;
    }

    /**
     * Determina se l'utente può creare un nuovo studente
     *
     * RULES:
     * - Super Admin: SI (può creare studenti in qualsiasi scuola, ma raramente lo fa)
     * - Admin: SI (può creare studenti nella propria scuola)
     * - Student: NO
     *
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        // Solo Super Admin e Admin possono creare studenti
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    /**
     * Determina se l'utente può aggiornare uno studente
     *
     * RULES:
     * - Super Admin: SI (qualsiasi studente)
     * - Admin: SI (solo se studente appartiene alla sua scuola)
     * - Student: SI (solo il proprio profilo - partial updates)
     *
     * @param User $user
     * @param User $student
     * @return bool
     */
    public function update(User $user, User $student): bool
    {
        // Super Admin: accesso illimitato
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin: può aggiornare solo studenti della propria scuola
        if ($user->isAdmin()) {
            return $this->belongsToSameSchool($user, $student);
        }

        // Student: può aggiornare solo il proprio profilo
        if ($user->isStudent()) {
            return $user->id === $student->id;
        }

        return false;
    }

    /**
     * Determina se l'utente può eliminare uno studente
     *
     * RULES:
     * - Super Admin: SI (qualsiasi studente, ma solo soft-delete)
     * - Admin: SI (solo studenti della propria scuola, con vincoli business)
     * - Student: NO (non può auto-eliminarsi)
     *
     * BUSINESS LOGIC:
     * - Verificare iscrizioni attive DOPO questo check (nel controller)
     * - Questa Policy verifica solo l'autorizzazione, non le regole business
     *
     * @param User $user
     * @param User $student
     * @return bool
     */
    public function delete(User $user, User $student): bool
    {
        // Student non può eliminare account (policy aziendale)
        if ($user->isStudent()) {
            return false;
        }

        // Super Admin: può eliminare qualsiasi studente
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin: può eliminare solo studenti della propria scuola
        if ($user->isAdmin()) {
            return $this->belongsToSameSchool($user, $student);
        }

        return false;
    }

    /**
     * Determina se l'utente può ripristinare uno studente soft-deleted
     *
     * RULES:
     * - Super Admin: SI
     * - Admin: SI (solo se studente apparteneva alla sua scuola)
     * - Student: NO
     *
     * @param User $user
     * @param User $student
     * @return bool
     */
    public function restore(User $user, User $student): bool
    {
        // Solo Super Admin e Admin possono ripristinare
        if ($user->isStudent()) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($user->isAdmin()) {
            return $this->belongsToSameSchool($user, $student);
        }

        return false;
    }

    /**
     * Determina se l'utente può eliminare permanentemente uno studente
     *
     * RULES:
     * - Super Admin: SI (per GDPR compliance e cleanup)
     * - Admin: NO (solo soft-delete consentito agli admin)
     * - Student: NO
     *
     * SECURITY:
     * Force delete rimuove definitivamente i dati. Solo Super Admin.
     *
     * @param User $user
     * @param User $student
     * @return bool
     */
    public function forceDelete(User $user, User $student): bool
    {
        // Solo Super Admin può fare force delete (GDPR, data cleanup)
        return $user->isSuperAdmin();
    }

    /**
     * HELPER: Verifica se due utenti appartengono alla stessa scuola
     *
     * MULTI-TENANT CRITICAL:
     * Questo è il cuore dell'isolamento multi-tenant.
     * Verifica che entrambi gli utenti abbiano lo stesso school_id.
     *
     * @param User $user Utente autenticato (admin)
     * @param User $student Studente target
     * @return bool True se stessa scuola
     */
    private function belongsToSameSchool(User $user, User $student): bool
    {
        // Entrambi devono avere school_id valorizzato
        if (!$user->school_id || !$student->school_id) {
            return false;
        }

        // Stesso school_id = stessa scuola
        return $user->school_id === $student->school_id;
    }
}
