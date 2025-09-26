<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Carbon\Carbon;

class EventRegistrationController extends AdminBaseController
{
    /**
     * Display the event registrations overview
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = EventRegistration::with(['event', 'user'])
            ->latest('registration_date');

        // Apply filters
        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('event', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('registration_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('registration_date', '<=', $request->date_to);
        }

        $registrations = $query->paginate(20);

        // Get data for filters
        $events = Event::where('school_id', $this->school->id)
            ->where('requires_registration', true)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Statistics
        $stats = [
            'total' => EventRegistration::count(),
            'registered' => EventRegistration::where('status', 'registered')->count(),
            'confirmed' => EventRegistration::where('status', 'confirmed')->count(),
            'waitlist' => EventRegistration::where('status', 'waitlist')->count(),
            'cancelled' => EventRegistration::where('status', 'cancelled')->count(),
            'attended' => EventRegistration::where('status', 'attended')->count(),
        ];

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.event-registrations.partials.table', compact('registrations'))->render(),
                'pagination' => $registrations->links()->render()
            ]);
        }

        return view('admin.event-registrations.index', compact(
            'registrations',
            'events',
            'stats'
        ));
    }

    /**
     * Show registrations for a specific event or return available users for AJAX
     */
    public function byEvent(Request $request, Event $event): View|JsonResponse
    {
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        // If AJAX request, return available users for registration
        if ($request->ajax()) {
            // Get all users in the school
            $allUsers = User::where('school_id', $this->school->id)
                ->where('role', 'user')
                ->select('id', 'name', 'email')
                ->orderBy('name')
                ->get();

            // Get users already registered for this event
            $registeredUserIds = EventRegistration::where('event_id', $event->id)
                ->pluck('user_id')
                ->toArray();

            // Filter out already registered users
            $availableUsers = $allUsers->filter(function($user) use ($registeredUserIds) {
                return !in_array($user->id, $registeredUserIds);
            })->values();

            return response()->json([
                'success' => true,
                'users' => $availableUsers
            ]);
        }

        $registrations = EventRegistration::with('user')
            ->where('event_id', $event->id)
            ->orderByRaw("FIELD(status, 'confirmed', 'registered', 'waitlist', 'cancelled', 'attended')")
            ->orderBy('registration_date')
            ->paginate(50);

        $stats = [
            'total' => $registrations->total(),
            'confirmed' => $registrations->where('status', 'confirmed')->count(),
            'registered' => $registrations->where('status', 'registered')->count(),
            'waitlist' => $registrations->where('status', 'waitlist')->count(),
            'cancelled' => $registrations->where('status', 'cancelled')->count(),
            'attended' => $registrations->where('status', 'attended')->count(),
            'available_spots' => max(0, ($event->max_participants ?? 999) - $registrations->whereIn('status', ['confirmed', 'registered'])->count()),
        ];

        return view('admin.event-registrations.by-event', compact(
            'event',
            'registrations',
            'stats'
        ));
    }

    /**
     * Show registration details
     */
    public function show(EventRegistration $registration): View
    {
        if ($registration->school_id !== $this->school->id) {
            abort(404, 'Registrazione non trovata.');
        }

        $registration->load(['event', 'user']);

        return view('admin.event-registrations.show', compact('registration'));
    }

    /**
     * Register a user for an event
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'event_id' => 'required|exists:events,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'in:registered,waitlist,confirmed',
            'notes' => 'nullable|string|max:1000',
            'additional_info' => 'nullable|array'
        ]);

        // Verify event belongs to school
        $event = Event::findOrFail($validated['event_id']);
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        // Verify user belongs to school
        $user = User::findOrFail($validated['user_id']);
        if ($user->school_id !== $this->school->id) {
            abort(404, 'Utente non trovato.');
        }

        // Check if already registered
        $existingRegistration = EventRegistration::where('event_id', $validated['event_id'])
            ->where('user_id', $validated['user_id'])
            ->first();

        if ($existingRegistration) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Utente già registrato a questo evento.'
                ], 422);
            }
            return back()->withErrors(['message' => 'Utente già registrato a questo evento.']);
        }

        // Determine status based on availability
        $confirmedCount = EventRegistration::where('event_id', $validated['event_id'])
            ->whereIn('status', ['confirmed', 'registered'])
            ->count();

        $status = $validated['status'] ?? 'registered';
        if ($event->max_participants && $confirmedCount >= $event->max_participants) {
            $status = 'waitlist';
        }

        $registration = EventRegistration::create([
            'event_id' => $validated['event_id'],
            'user_id' => $validated['user_id'],
            'school_id' => $this->school->id,
            'status' => $status,
            'registration_date' => now(),
            'notes' => $validated['notes'] ?? null,
            'additional_info' => $validated['additional_info'] ?? null,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Registrazione creata con successo.',
                'registration' => $registration->load(['event', 'user'])
            ]);
        }

        return redirect()->route('admin.event-registrations.index')
                        ->with('success', 'Registrazione creata con successo.');
    }

    /**
     * Update registration status
     */
    public function updateStatus(Request $request, EventRegistration $registration): JsonResponse|RedirectResponse
    {
        if ($registration->school_id !== $this->school->id) {
            abort(404, 'Registrazione non trovata.');
        }

        $validated = $request->validate([
            'status' => 'required|in:registered,waitlist,confirmed,cancelled,attended',
            'notes' => 'nullable|string|max:1000'
        ]);

        $oldStatus = $registration->status;
        $newStatus = $validated['status'];

        // Handle status-specific logic
        if ($newStatus === 'confirmed' && $oldStatus !== 'confirmed') {
            $registration->confirmed_at = now();
        } elseif ($newStatus !== 'confirmed') {
            $registration->confirmed_at = null;
        }

        $registration->update([
            'status' => $newStatus,
            'notes' => $validated['notes'] ?? $registration->notes
        ]);

        // Check if we can move someone from waitlist to registered
        if ($newStatus === 'cancelled' && in_array($oldStatus, ['confirmed', 'registered'])) {
            $this->processWaitlist($registration->event_id);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Status aggiornato con successo.',
                'registration' => $registration->fresh(['event', 'user'])
            ]);
        }

        return back()->with('success', 'Status aggiornato con successo.');
    }

    /**
     * Bulk update registrations
     */
    public function bulkUpdate(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'registration_ids' => 'required|array',
            'registration_ids.*' => 'exists:event_registrations,id',
            'action' => 'required|in:confirm,waitlist,cancel,mark_attended',
        ]);

        $registrations = EventRegistration::whereIn('id', $validated['registration_ids'])
            ->where('school_id', $this->school->id)
            ->get();

        if ($registrations->isEmpty()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nessuna registrazione trovata.'
                ], 422);
            }
            return back()->withErrors(['message' => 'Nessuna registrazione trovata.']);
        }

        $statusMap = [
            'confirm' => 'confirmed',
            'waitlist' => 'waitlist',
            'cancel' => 'cancelled',
            'mark_attended' => 'attended'
        ];

        $newStatus = $statusMap[$validated['action']];
        $updatedCount = 0;

        foreach ($registrations as $registration) {
            if ($newStatus === 'confirmed') {
                $registration->confirmed_at = now();
            }

            $registration->update(['status' => $newStatus]);
            $updatedCount++;
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "Aggiornate {$updatedCount} registrazioni."
            ]);
        }

        return back()->with('success', "Aggiornate {$updatedCount} registrazioni.");
    }

    /**
     * Delete a registration
     */
    public function destroy(EventRegistration $registration): JsonResponse|RedirectResponse
    {
        if ($registration->school_id !== $this->school->id) {
            abort(404, 'Registrazione non trovata.');
        }

        $eventId = $registration->event_id;
        $wasConfirmedOrRegistered = in_array($registration->status, ['confirmed', 'registered']);

        $registration->delete();

        // Process waitlist if spot became available
        if ($wasConfirmedOrRegistered) {
            $this->processWaitlist($eventId);
        }

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Registrazione eliminata con successo.'
            ]);
        }

        return back()->with('success', 'Registrazione eliminata con successo.');
    }

    /**
     * Export registrations
     */
    public function export(Request $request)
    {
        $query = EventRegistration::with(['event', 'user']);

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $registrations = $query->orderBy('registration_date')->get();

        $filename = 'registrazioni_eventi_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($registrations) {
            $file = fopen('php://output', 'w');

            // Header
            fputcsv($file, ['Registrazioni Eventi - ' . $this->school->name]);
            fputcsv($file, ['Generato il: ' . now()->format('d/m/Y H:i')]);
            fputcsv($file, []);
            fputcsv($file, [
                'Evento',
                'Utente',
                'Email',
                'Status',
                'Data Registrazione',
                'Data Conferma',
                'Note'
            ]);

            foreach ($registrations as $registration) {
                fputcsv($file, [
                    $registration->event->name,
                    $registration->user->name,
                    $registration->user->email,
                    ucfirst($registration->status),
                    $registration->registration_date->format('d/m/Y H:i'),
                    $registration->confirmed_at?->format('d/m/Y H:i') ?? '',
                    $registration->notes ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Process waitlist for an event
     */
    private function processWaitlist(int $eventId): void
    {
        $event = Event::find($eventId);
        if (!$event || !$event->max_participants) {
            return;
        }

        $confirmedCount = EventRegistration::where('event_id', $eventId)
            ->whereIn('status', ['confirmed', 'registered'])
            ->count();

        $availableSpots = $event->max_participants - $confirmedCount;

        if ($availableSpots > 0) {
            $waitlistRegistrations = EventRegistration::where('event_id', $eventId)
                ->where('status', 'waitlist')
                ->orderBy('registration_date')
                ->take($availableSpots)
                ->get();

            foreach ($waitlistRegistrations as $registration) {
                $registration->update(['status' => 'registered']);
            }
        }
    }
}