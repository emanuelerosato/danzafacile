<?php

namespace App\Services;

use App\Models\EventRegistration;
use App\Models\User;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class QRCodeService
{
    /**
     * Genera il QR code per una registrazione evento
     *
     * @param EventRegistration $registration
     * @return string URL del file QR code generato
     */
    public function generateQRCode(EventRegistration $registration): string
    {
        // Ottiene i dati da codificare nel QR
        $qrData = $this->getQRData($registration);

        // Genera il QR code usando la configurazione dal config
        $qrCode = QrCode::format(config('qrcode.format', 'svg'))
            ->size(config('qrcode.size', 300))
            ->errorCorrection(config('qrcode.error_correction', 'M'))
            ->generate($qrData);

        // Salva il QR code su storage
        $filename = "qr_codes/registration_{$registration->id}_{$registration->qr_code_token}.svg";
        Storage::disk('public')->put($filename, $qrCode);

        $url = Storage::disk('public')->url($filename);

        Log::info('QR code generated', [
            'registration_id' => $registration->id,
            'event_id' => $registration->event_id,
            'filename' => $filename,
        ]);

        return $url;
    }

    /**
     * Ottiene i dati da codificare nel QR code (URL per check-in)
     *
     * @param EventRegistration $registration
     * @return string
     */
    protected function getQRData(EventRegistration $registration): string
    {
        // Return JSON data that will be sent via AJAX to checkin endpoint
        return json_encode([
            'event_id' => $registration->event_id,
            'token' => $registration->qr_code_token,
            'registration_id' => $registration->id,
        ]);
    }

    /**
     * Verifica il QR code e effettua il check-in del partecipante
     *
     * @param string $token Token del QR code
     * @param User $staffMember Membro dello staff che effettua il check-in
     * @return EventRegistration|null
     */
    public function checkInWithQRCode(string $token, User $staffMember): ?EventRegistration
    {
        // Trova la registrazione dal token
        $registration = EventRegistration::where('qr_code_token', $token)->first();

        if (!$registration) {
            Log::warning('QR code check-in failed: Invalid token', [
                'token' => $token,
                'staff_member_id' => $staffMember->id,
            ]);
            return null;
        }

        // Verifica se già effettuato il check-in
        if ($registration->checked_in_at) {
            Log::info('QR code check-in attempted on already checked-in registration', [
                'registration_id' => $registration->id,
                'already_checked_in_at' => $registration->checked_in_at,
                'staff_member_id' => $staffMember->id,
            ]);
            return $registration; // Ritorna comunque la registrazione
        }

        // Effettua il check-in
        $registration->update([
            'checked_in_at' => now(),
            'checked_in_by' => $staffMember->id,
        ]);

        Log::info('QR code check-in successful', [
            'registration_id' => $registration->id,
            'event_id' => $registration->event_id,
            'user_id' => $registration->user_id,
            'checked_in_by' => $staffMember->id,
        ]);

        return $registration;
    }

    /**
     * Verifica se una registrazione ha effettuato il check-in
     *
     * @param EventRegistration $registration
     * @return bool
     */
    public function isCheckedIn(EventRegistration $registration): bool
    {
        return !is_null($registration->checked_in_at);
    }

    /**
     * Annulla il check-in di una registrazione
     *
     * @param EventRegistration $registration
     * @param User $staffMember Membro dello staff che annulla il check-in
     * @return bool
     */
    public function undoCheckIn(EventRegistration $registration, User $staffMember): bool
    {
        if (!$this->isCheckedIn($registration)) {
            Log::warning('Attempted to undo check-in on non-checked-in registration', [
                'registration_id' => $registration->id,
                'staff_member_id' => $staffMember->id,
            ]);
            return false;
        }

        $registration->update([
            'checked_in_at' => null,
            'checked_in_by' => null,
        ]);

        Log::info('Check-in undone', [
            'registration_id' => $registration->id,
            'undone_by' => $staffMember->id,
        ]);

        return true;
    }

    /**
     * Ottiene le statistiche di check-in per un evento
     *
     * @param int $eventId
     * @return array
     */
    public function getEventCheckInStats(int $eventId): array
    {
        $totalRegistrations = EventRegistration::where('event_id', $eventId)
            ->whereIn('status', ['confirmed', 'registered'])
            ->count();

        $checkedInCount = EventRegistration::where('event_id', $eventId)
            ->whereNotNull('checked_in_at')
            ->count();

        $pendingCheckIn = $totalRegistrations - $checkedInCount;
        $checkInRate = $totalRegistrations > 0
            ? round(($checkedInCount / $totalRegistrations) * 100, 2)
            : 0;

        return [
            'total_registrations' => $totalRegistrations,
            'checked_in' => $checkedInCount,
            'pending_check_in' => $pendingCheckIn,
            'check_in_rate' => $checkInRate,
        ];
    }

    /**
     * Genera QR code in batch per tutte le registrazioni di un evento
     *
     * @param int $eventId
     * @return int Numero di QR code generati
     */
    public function generateBatchQRCodes(int $eventId): int
    {
        $registrations = EventRegistration::where('event_id', $eventId)
            ->whereIn('status', ['confirmed', 'registered'])
            ->get();

        $count = 0;
        foreach ($registrations as $registration) {
            try {
                $this->generateQRCode($registration);
                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to generate QR code in batch', [
                    'registration_id' => $registration->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        Log::info('Batch QR code generation completed', [
            'event_id' => $eventId,
            'total_generated' => $count,
        ]);

        return $count;
    }

    /**
     * Elimina il QR code di una registrazione
     *
     * @param EventRegistration $registration
     * @return bool
     */
    public function deleteQRCode(EventRegistration $registration): bool
    {
        $filename = "qr_codes/registration_{$registration->id}_{$registration->qr_code_token}.svg";

        if (Storage::disk('public')->exists($filename)) {
            Storage::disk('public')->delete($filename);

            Log::info('QR code deleted', [
                'registration_id' => $registration->id,
                'filename' => $filename,
            ]);

            return true;
        }

        return false;
    }
}
