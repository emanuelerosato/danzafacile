<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class GuestDashboardController extends Controller
{
    /**
     * Authenticate guest user via magic link token
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        $token = $request->get('token');

        if (!$token) {
            return redirect()->route('home')->withErrors([
                'token' => 'Link non valido o scaduto.'
            ]);
        }

        $user = User::where('guest_token', $token)
            ->where('is_guest', true)
            ->first();

        if (!$user || !$user->hasValidGuestToken()) {
            Log::warning('Invalid or expired guest token', [
                'token' => substr($token, 0, 8) . '...',
                'ip' => $request->ip(),
            ]);

            return redirect()->route('home')->withErrors([
                'token' => 'Link non valido o scaduto. Contatta il supporto se hai bisogno di assistenza.'
            ]);
        }

        // Log in guest user
        Auth::login($user);

        Log::info('Guest user authenticated via magic link', [
            'user_id' => $user->id,
            'email' => $user->email,
        ]);

        return redirect()->route('guest.dashboard')->with('success', 'Benvenuto! Ecco le tue iscrizioni agli eventi.');
    }

    /**
     * Show guest dashboard with their event registrations
     *
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        if (!Auth::check() || !Auth::user()->isGuest()) {
            return redirect()->route('home')->withErrors([
                'auth' => 'Devi effettuare l\'accesso come ospite.'
            ]);
        }

        $user = Auth::user();

        // Get all event registrations for this guest
        $upcomingRegistrations = EventRegistration::with(['event', 'eventPayment'])
            ->where('user_id', $user->id)
            ->whereHas('event', function ($query) {
                $query->where('start_date', '>=', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $pastRegistrations = EventRegistration::with(['event', 'eventPayment'])
            ->where('user_id', $user->id)
            ->whereHas('event', function ($query) {
                $query->where('start_date', '<', now());
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('guest.dashboard', compact('user', 'upcomingRegistrations', 'pastRegistrations'));
    }

    /**
     * Show QR code for specific registration
     *
     * @param int $registration
     * @return \Illuminate\View\View
     */
    public function showQRCode(int $registration)
    {
        if (!Auth::check() || !Auth::user()->isGuest()) {
            abort(403);
        }

        $eventRegistration = EventRegistration::with('event')
            ->where('id', $registration)
            ->where('user_id', Auth::id())
            ->whereIn('status', ['confirmed', 'checked_in'])
            ->firstOrFail();

        // Generate QR code if not already generated
        $qrCodeService = app(\App\Services\QRCodeService::class);
        $qrCodeUrl = $qrCodeService->generateQRCode($eventRegistration);

        return view('guest.qrcode', compact('eventRegistration', 'qrCodeUrl'));
    }

    /**
     * Logout guest user
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Logout effettuato con successo.');
    }
}
