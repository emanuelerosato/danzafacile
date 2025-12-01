<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Services\GuestRegistrationService;
use App\Services\PaymentService;
use App\Rules\Recaptcha;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class PublicEventController extends Controller
{
    public function __construct(
        protected GuestRegistrationService $guestRegistrationService,
        protected PaymentService $paymentService
    ) {}

    /**
     * Show public events listing (all upcoming public events)
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $events = Event::where('is_public', true)
            ->where('start_date', '>=', now())
            ->where('active', true)
            ->orderBy('start_date', 'asc')
            ->paginate(12);

        return view('public.events.index', compact('events'));
    }

    /**
     * Show single public event landing page
     *
     * @param string $slug
     * @return \Illuminate\View\View
     */
    public function show(string $slug)
    {
        $event = Event::where('slug', $slug)
            ->where('is_public', true)
            ->where('active', true)
            ->firstOrFail();

        $spotsRemaining = $event->max_participants > 0
            ? $event->max_participants - $event->eventRegistrations()->whereIn('status', ['confirmed', 'pending_payment'])->count()
            : null;

        return view('public.events.show', compact('event', 'spotsRemaining'));
    }

    /**
     * Handle guest registration form submission
     *
     * @param Request $request
     * @param string $slug
     * @return \Illuminate\Http\RedirectResponse
     */
    public function register(Request $request, string $slug)
    {
        // Rate limiting: max 3 registration attempts per 10 minutes per IP
        $key = 'event-registration:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'rate_limit' => "Troppi tentativi. Riprova tra {$seconds} secondi."
            ])->withInput();
        }

        RateLimiter::hit($key, 600); // 10 minutes

        $event = Event::where('slug', $slug)
            ->where('is_public', true)
            ->where('active', true)
            ->firstOrFail();

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'privacy_consent' => 'required|accepted',
            'marketing_consent' => 'nullable|boolean',
            'newsletter_consent' => 'nullable|boolean',
            'g-recaptcha-response' => ['required', new Recaptcha()],
        ], [
            'privacy_consent.required' => 'Devi accettare l\'Informativa Privacy per proseguire.',
            'privacy_consent.accepted' => 'Devi accettare l\'Informativa Privacy per proseguire.',
            'g-recaptcha-response.required' => 'La verifica reCAPTCHA è obbligatoria.',
        ]);

        try {
            // Check event capacity
            if ($event->max_participants > 0) {
                $currentRegistrations = $event->eventRegistrations()
                    ->whereIn('status', ['confirmed', 'pending_payment'])
                    ->count();

                if ($currentRegistrations >= $event->max_participants) {
                    return back()->withErrors([
                        'event' => 'Questo evento ha raggiunto il numero massimo di partecipanti.'
                    ])->withInput();
                }
            }

            // Register guest using service
            $result = $this->guestRegistrationService->registerGuest($event, [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'gdpr_consents' => [
                    'privacy' => true, // Required
                    'marketing' => $validated['marketing_consent'] ?? false,
                    'newsletter' => $validated['newsletter_consent'] ?? false,
                ],
            ]);

            $user = $result['user'];
            $registration = $result['registration'];
            $magicLink = $result['magic_link'];

            Log::info('Guest registration completed', [
                'event_id' => $event->id,
                'user_id' => $user->id,
                'registration_id' => $registration->id,
                'requires_payment' => $event->requiresPayment(),
            ]);

            // If event requires payment, redirect to payment
            if ($event->requiresPayment()) {
                $payment = $this->paymentService->createPayment($registration, 'paypal');

                return redirect()->route('public.events.payment', [
                    'slug' => $slug,
                    'registration' => $registration->id,
                ])->with('success', 'Registrazione completata! Procedi al pagamento per confermare.');
            }

            // Free event - send magic link and redirect to success
            // TODO Phase 5: Send email with magic link
            return redirect()->route('public.events.registration.success', [
                'slug' => $slug,
                'registration' => $registration->id,
            ])->with('success', 'Registrazione completata! Controlla la tua email per accedere.');

        } catch (\Exception $e) {
            Log::error('Guest registration failed', [
                'event_slug' => $slug,
                'email' => $validated['email'],
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'error' => 'Si è verificato un errore durante la registrazione. Riprova o contatta il supporto.'
            ])->withInput();
        }
    }

    /**
     * Show payment page for event registration
     *
     * @param string $slug
     * @param int $registration
     * @return \Illuminate\View\View
     */
    public function payment(string $slug, int $registration)
    {
        $event = Event::where('slug', $slug)->where('is_public', true)->firstOrFail();
        $eventRegistration = EventRegistration::findOrFail($registration);

        // Verify registration belongs to this event
        if ($eventRegistration->event_id !== $event->id) {
            abort(403, 'Registrazione non valida per questo evento.');
        }

        // Check if already paid
        if ($eventRegistration->status === 'confirmed') {
            return redirect()->route('public.events.registration.success', [
                'slug' => $slug,
                'registration' => $registration,
            ]);
        }

        $payment = $eventRegistration->eventPayment;

        if (!$payment) {
            // Create payment if it doesn't exist
            $payment = $this->paymentService->createPayment($eventRegistration, 'paypal');
        }

        return view('public.events.payment', compact('event', 'eventRegistration', 'payment'));
    }

    /**
     * Show registration success page
     *
     * @param string $slug
     * @param int $registration
     * @return \Illuminate\View\View
     */
    public function registrationSuccess(string $slug, int $registration)
    {
        $event = Event::where('slug', $slug)->where('is_public', true)->firstOrFail();
        $eventRegistration = EventRegistration::with('user')->findOrFail($registration);

        if ($eventRegistration->event_id !== $event->id) {
            abort(403);
        }

        return view('public.events.success', compact('event', 'eventRegistration'));
    }
}
