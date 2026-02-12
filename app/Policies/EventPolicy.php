<?php

namespace App\Policies;

use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determina se l'utente può visualizzare la lista di eventi.
     *
     * Super Admin: può visualizzare eventi di tutte le scuole
     * Admin Scuola: può visualizzare solo eventi della propria scuola
     * Studente: può visualizzare solo eventi della propria scuola (attivi e pubblici)
     */
    public function viewAny(User $user): bool
    {
        // Super Admin può visualizzare tutti gli eventi
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin e Studenti possono visualizzare eventi della propria scuola
        return $user->school_id !== null;
    }

    /**
     * Determina se l'utente può visualizzare un evento specifico.
     *
     * Verifica multi-tenant: l'utente può accedere solo ad eventi della sua scuola
     */
    public function view(User $user, Event $event): bool
    {
        // Super Admin può visualizzare qualsiasi evento
        if ($user->isSuperAdmin()) {
            return true;
        }

        // L'utente deve avere una scuola associata
        if (!$user->school_id) {
            return false;
        }

        // L'evento deve appartenere alla scuola dell'utente
        if ($event->school_id !== $user->school_id) {
            return false;
        }

        // Gli studenti possono visualizzare solo eventi attivi
        if ($user->isStudent()) {
            return $event->active;
        }

        // Admin scuola può visualizzare tutti gli eventi della propria scuola
        return true;
    }

    /**
     * Determina se l'utente può creare eventi.
     *
     * Solo Admin Scuola e Super Admin possono creare eventi
     */
    public function create(User $user): bool
    {
        // Super Admin può creare eventi
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Scuola può creare eventi per la propria scuola
        return $user->isAdminScuola() && $user->school_id !== null;
    }

    /**
     * Determina se l'utente può aggiornare un evento.
     *
     * Verifica multi-tenant: solo Admin della stessa scuola può modificare
     */
    public function update(User $user, Event $event): bool
    {
        // Super Admin può aggiornare qualsiasi evento
        if ($user->isSuperAdmin()) {
            return true;
        }

        // L'utente deve essere Admin Scuola
        if (!$user->isAdminScuola()) {
            return false;
        }

        // L'utente deve avere una scuola associata
        if (!$user->school_id) {
            return false;
        }

        // L'evento deve appartenere alla scuola dell'utente
        return $event->school_id === $user->school_id;
    }

    /**
     * Determina se l'utente può eliminare un evento.
     *
     * Verifica multi-tenant + business rule: non eliminare eventi con registrazioni confermate
     */
    public function delete(User $user, Event $event): bool
    {
        // Super Admin può eliminare qualsiasi evento
        if ($user->isSuperAdmin()) {
            return true;
        }

        // L'utente deve essere Admin Scuola
        if (!$user->isAdminScuola()) {
            return false;
        }

        // L'utente deve avere una scuola associata
        if (!$user->school_id) {
            return false;
        }

        // L'evento deve appartenere alla scuola dell'utente
        if ($event->school_id !== $user->school_id) {
            return false;
        }

        // Business rule: non eliminare eventi con registrazioni confermate
        $confirmedRegistrations = $event->registrations()->confirmed()->count();
        return $confirmedRegistrations === 0;
    }

    /**
     * Determina se l'utente può eliminare definitivamente un evento (force delete).
     *
     * Solo Super Admin può fare force delete
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return $user->isSuperAdmin();
    }

    /**
     * Determina se l'utente può ripristinare un evento soft-deleted.
     *
     * Solo Super Admin e Admin della stessa scuola
     */
    public function restore(User $user, Event $event): bool
    {
        // Super Admin può ripristinare qualsiasi evento
        if ($user->isSuperAdmin()) {
            return true;
        }

        // L'utente deve essere Admin Scuola
        if (!$user->isAdminScuola()) {
            return false;
        }

        // L'utente deve avere una scuola associata
        if (!$user->school_id) {
            return false;
        }

        // L'evento deve appartenere alla scuola dell'utente
        return $event->school_id === $user->school_id;
    }

    /**
     * Determina se l'utente può attivare/disattivare un evento.
     *
     * Stesse regole di update
     */
    public function toggleActive(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    /**
     * Determina se l'utente può personalizzare la landing page di un evento.
     *
     * Solo Admin della stessa scuola e solo per eventi pubblici
     */
    public function customizeLanding(User $user, Event $event): bool
    {
        // Super Admin può personalizzare qualsiasi landing
        if ($user->isSuperAdmin()) {
            return true;
        }

        // L'utente deve essere Admin Scuola
        if (!$user->isAdminScuola()) {
            return false;
        }

        // L'utente deve avere una scuola associata
        if (!$user->school_id) {
            return false;
        }

        // L'evento deve appartenere alla scuola dell'utente
        if ($event->school_id !== $user->school_id) {
            return false;
        }

        // L'evento deve essere pubblico
        return $event->is_public;
    }

    /**
     * Determina se l'utente può registrare altri utenti a un evento.
     *
     * Solo Admin della stessa scuola
     */
    public function registerUsers(User $user, Event $event): bool
    {
        return $this->update($user, $event);
    }

    /**
     * Determina se l'utente può esportare dati eventi.
     *
     * Solo Admin della stessa scuola
     */
    public function export(User $user): bool
    {
        // Super Admin può esportare
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Scuola può esportare eventi della propria scuola
        return $user->isAdminScuola() && $user->school_id !== null;
    }

    /**
     * Determina se l'utente può eseguire azioni bulk.
     *
     * Solo Admin della stessa scuola
     */
    public function bulkActions(User $user): bool
    {
        // Super Admin può fare bulk actions
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Scuola può fare bulk actions per eventi della propria scuola
        return $user->isAdminScuola() && $user->school_id !== null;
    }

    /**
     * Determina se l'utente può visualizzare il report guest registrations.
     *
     * Solo Admin della stessa scuola
     */
    public function viewGuestReport(User $user): bool
    {
        // Super Admin può visualizzare report
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Scuola può visualizzare report della propria scuola
        return $user->isAdminScuola() && $user->school_id !== null;
    }
}
