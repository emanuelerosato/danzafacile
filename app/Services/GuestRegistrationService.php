<?php

namespace App\Services;

use App\Models\User;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\GdprConsent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class GuestRegistrationService
{
    /**
     * Registra un utente guest per un evento pubblico
     *
     * @param Event $event
     * @param array $data ['name', 'email', 'phone', 'gdpr_consents']
     * @return array ['user' => User, 'registration' => EventRegistration, 'magic_link' => string]
     */
    public function registerGuest(Event $event, array $data): array
    {
        return DB::transaction(function () use ($event, $data) {
            // 1. Crea o trova l'utente guest
            $user = $this->findOrCreateGuestUser($data);

            // 2. Crea la registrazione all'evento
            $status = $event->requiresPayment() ? 'pending_payment' : 'confirmed';

            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'school_id' => $event->school_id,
                'status' => $status,
                'registration_date' => now(),
                'qr_code_token' => Str::random(64),
                'notes' => $data['notes'] ?? null,
                'additional_info' => $data['additional_info'] ?? [],
            ]);

            // 3. Registra i consensi GDPR
            $this->recordGdprConsents($user, $registration, $data['gdpr_consents'] ?? []);

            // 4. Genera il link di login magico
            $magicLink = $user->getMagicLoginLink();

            Log::info('Guest user registered for event', [
                'user_id' => $user->id,
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                'status' => $status,
            ]);

            return [
                'user' => $user,
                'registration' => $registration,
                'magic_link' => $magicLink,
            ];
        });
    }

    /**
     * Trova o crea un utente guest
     *
     * @param array $data
     * @return User
     */
    protected function findOrCreateGuestUser(array $data): User
    {
        // Cerca un utente guest esistente con la stessa email
        $user = User::where('email', $data['email'])
                    ->where('is_guest', true)
                    ->first();

        if ($user) {
            // Aggiorna le informazioni se necessario
            $user->update([
                'name' => $data['name'],
                'guest_phone' => $data['phone'] ?? $user->guest_phone,
            ]);

            // Rigenera il token se scaduto
            if (!$user->hasValidGuestToken()) {
                $user->generateGuestToken();
            }

            Log::info('Existing guest user found and updated', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return $user;
        }

        // Crea un nuovo utente guest
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'guest_phone' => $data['phone'] ?? null,
            'password' => Hash::make(Str::random(32)), // Password casuale non utilizzabile
            'is_guest' => true,
            'role' => 'user',
            'active' => true,
        ]);

        // Genera il token per il login magico
        $user->generateGuestToken();

        Log::info('New guest user created', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return $user;
    }

    /**
     * Registra i consensi GDPR per l'utente
     *
     * @param User $user
     * @param EventRegistration $registration
     * @param array $consents
     * @return void
     */
    protected function recordGdprConsents(User $user, EventRegistration $registration, array $consents): void
    {
        // Tipi di consenso standard
        $consentTypes = ['privacy', 'marketing', 'cookies', 'terms', 'newsletter'];

        foreach ($consentTypes as $type) {
            // Verifica se il consenso è stato fornito nei dati
            if (isset($consents[$type])) {
                GdprConsent::record(
                    userId: $user->id,
                    consentType: $type,
                    consented: (bool) $consents[$type],
                    eventRegistrationId: $registration->id
                );
            }
        }

        Log::info('GDPR consents recorded for guest registration', [
            'user_id' => $user->id,
            'registration_id' => $registration->id,
            'consents' => array_keys(array_filter($consents)),
        ]);
    }

    /**
     * Invia il link di login magico all'utente guest
     * NOTA: Implementazione completa in Phase 5 (Email System)
     *
     * @param User $guestUser
     * @return void
     */
    public function sendMagicLink(User $guestUser): void
    {
        // TODO: Implementare in Phase 5 (Email System)
        // Per ora loggiamo solo l'azione
        Log::info('Magic link ready to be sent (Email implementation pending)', [
            'user_id' => $guestUser->id,
            'email' => $guestUser->email,
            'magic_link' => $guestUser->getMagicLoginLink(),
        ]);
    }

    /**
     * Pulisce gli utenti guest scaduti (GDPR compliance)
     *
     * @param int $daysOld Archivia guest più vecchi di N giorni
     * @return int Numero di utenti archiviati
     */
    public function cleanupExpiredGuests(int $daysOld = 180): int
    {
        $expiredGuests = User::guests()
            ->where('created_at', '<', now()->subDays($daysOld))
            ->whereNull('is_archived')
            ->get();

        $count = 0;
        foreach ($expiredGuests as $guest) {
            $guest->archiveGuest('auto_cleanup_expired');
            $count++;
        }

        Log::info('Expired guest users cleanup completed', [
            'archived_count' => $count,
            'days_old' => $daysOld,
        ]);

        return $count;
    }
}
