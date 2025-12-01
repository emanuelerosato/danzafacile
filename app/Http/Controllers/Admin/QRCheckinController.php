<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\QRCodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class QRCheckinController extends Controller
{
    public function __construct(
        protected QRCodeService $qrCodeService
    ) {
        $this->middleware('auth');
        $this->middleware('role:admin,staff');
    }

    /**
     * Show QR code scanner interface for event check-in
     *
     * @param int $eventId
     * @return \Illuminate\View\View
     */
    public function scanner(int $eventId)
    {
        $event = Event::with('school')->findOrFail($eventId);

        // Verify user has access to this event's school
        if (Auth::user()->school_id !== $event->school_id && !Auth::user()->isSuperAdmin()) {
            abort(403, 'Non hai accesso a questo evento.');
        }

        $stats = $this->qrCodeService->getEventCheckInStats($eventId);

        return view('admin.events.qr-scanner', compact('event', 'stats'));
    }

    /**
     * Process QR code check-in via AJAX
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkin(Request $request)
    {
        $validated = $request->validate([
            'event_id' => 'required|integer|exists:events,id',
            'token' => 'required|string|size:64',
        ]);

        $event = Event::findOrFail($validated['event_id']);

        // Verify access
        if (Auth::user()->school_id !== $event->school_id && !Auth::user()->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Non hai accesso a questo evento.',
            ], 403);
        }

        try {
            $registration = $this->qrCodeService->checkInWithQRCode(
                $validated['token'],
                Auth::user()
            );

            if (!$registration) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code non valido o partecipante già registrato.',
                ], 404);
            }

            // Verify registration belongs to this event
            if ($registration->event_id !== $event->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Questo QR Code non è valido per questo evento.',
                ], 400);
            }

            Log::info('Participant checked in via QR code', [
                'event_id' => $event->id,
                'registration_id' => $registration->id,
                'user_id' => $registration->user_id,
                'checked_in_by' => Auth::id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Check-in effettuato con successo!',
                'participant' => [
                    'id' => $registration->id,
                    'name' => $registration->user->name,
                    'email' => $registration->user->email,
                    'checked_in_at' => $registration->checked_in_at->format('d/m/Y H:i'),
                    'checked_in_by' => Auth::user()->name,
                ],
            ], 200);

        } catch (\Exception $e) {
            Log::error('QR check-in failed', [
                'event_id' => $event->id,
                'token' => substr($validated['token'], 0, 8) . '...',
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante il check-in. Riprova.',
            ], 500);
        }
    }

    /**
     * Manual check-in (backup for QR code failure)
     *
     * @param Request $request
     * @param int $eventId
     * @param int $registrationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function manualCheckin(int $eventId, int $registrationId)
    {
        $event = Event::findOrFail($eventId);
        $registration = EventRegistration::findOrFail($registrationId);

        // Verify access and registration
        if (Auth::user()->school_id !== $event->school_id && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        if ($registration->event_id !== $event->id) {
            return back()->withErrors(['error' => 'Registrazione non valida per questo evento.']);
        }

        if ($registration->checked_in_at) {
            return back()->with('info', 'Partecipante già registrato.');
        }

        $registration->update([
            'checked_in_at' => now(),
            'checked_in_by' => Auth::id(),
        ]);

        Log::info('Manual check-in performed', [
            'event_id' => $eventId,
            'registration_id' => $registrationId,
            'staff_id' => Auth::id(),
        ]);

        return back()->with('success', 'Check-in effettuato manualmente con successo.');
    }

    /**
     * Undo check-in
     *
     * @param int $eventId
     * @param int $registrationId
     * @return \Illuminate\Http\RedirectResponse
     */
    public function undoCheckin(int $eventId, int $registrationId)
    {
        $event = Event::findOrFail($eventId);
        $registration = EventRegistration::findOrFail($registrationId);

        if (Auth::user()->school_id !== $event->school_id && !Auth::user()->isSuperAdmin()) {
            abort(403);
        }

        if ($registration->event_id !== $event->id) {
            return back()->withErrors(['error' => 'Registrazione non valida.']);
        }

        $this->qrCodeService->undoCheckIn($registration, Auth::user());

        return back()->with('success', 'Check-in annullato.');
    }
}
