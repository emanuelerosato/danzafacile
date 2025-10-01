<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Helpers\QueryHelper;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminEventController extends AdminBaseController
{
    /**
     * Display a listing of events for the current school
     */
    public function index(Request $request)
    {
        $query = $this->school->events()->with(['registrations.user']);

        // SECURE: allowed sort fields for events
        $allowedSortFields = ['title', 'start_date', 'end_date', 'max_participants', 'created_at', 'updated_at'];
        $events = $this->getFilteredResults($query, $request, 15, $allowedSortFields);

        // Get filter options
        $eventTypes = [
            'saggio' => 'Saggio',
            'workshop' => 'Workshop',
            'competizione' => 'Competizione',
            'seminario' => 'Seminario',
            'altro' => 'Altro'
        ];

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Eventi recuperati con successo', [
                'html' => view('admin.events.partials.table', compact('events'))->render(),
                'pagination' => $events->links()->render()
            ]);
        }

        // Quick stats for header cards
        $stats = [
            'total_events' => $this->school->events()->count(),
            'upcoming_events' => $this->school->events()->upcoming()->count(),
            'active_events' => $this->school->events()->active()->count(),
            'total_registrations' => EventRegistration::whereHas('event', function($q) {
                $q->where('school_id', $this->school->id);
            })->count()
        ];

        return view('admin.events.index', compact('events', 'eventTypes', 'stats'));
    }

    /**
     * Show the form for creating a new event
     */
    public function create()
    {
        $eventTypes = [
            'saggio' => 'Saggio',
            'workshop' => 'Workshop',
            'competizione' => 'Competizione',
            'seminario' => 'Seminario',
            'altro' => 'Altro'
        ];

        return view('admin.events.create', compact('eventTypes'));
    }

    /**
     * Store a newly created event in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:saggio,workshop,competizione,seminario,altro',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'requires_registration' => 'boolean',
            'registration_deadline' => 'nullable|date|before:start_date',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
            'is_public' => 'boolean',
            'active' => 'boolean'
        ]);

        $validated['school_id'] = $this->school->id;
        $validated['requires_registration'] = $validated['requires_registration'] ?? false;
        $validated['is_public'] = $validated['is_public'] ?? true;
        $validated['active'] = $validated['active'] ?? true;
        $validated['price'] = $validated['price'] ?? 0.00;

        $event = Event::create($validated);

        $this->clearSchoolCache();

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Evento creato con successo.', [
                'event' => $event->load(['registrations.user'])
            ]);
        }

        return redirect()->route('admin.events.show', $event)
                        ->with('success', 'Evento creato con successo.');
    }

    /**
     * Display the specified event
     */
    public function show(Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        $event->load([
            'registrations.user',
            'attendance.user',
            'payments'
        ]);

        $stats = [
            'total_registrations' => $event->registrations()->active()->count(),
            'confirmed_registrations' => $event->registrations()->confirmed()->count(),
            'waitlist_count' => $event->registrations()->waitlist()->count(),
            'available_spots' => $event->available_spots,
            'total_revenue' => $event->payments()->where('status', 'completed')->sum('amount') ??
                             ($event->registrations()->confirmed()->count() * ($event->price ?? 0)),
            'attendance_count' => $event->attendance()->where('status', 'present')->count(),
            'no_show_count' => $event->registrations()->confirmed()->count() -
                              $event->attendance()->where('status', 'present')->count()
        ];

        return view('admin.events.show', compact('event', 'stats'));
    }

    /**
     * Show the form for editing the specified event
     */
    public function edit(Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        $eventTypes = [
            'saggio' => 'Saggio',
            'workshop' => 'Workshop',
            'competizione' => 'Competizione',
            'seminario' => 'Seminario',
            'altro' => 'Altro'
        ];

        return view('admin.events.edit', compact('event', 'eventTypes'));
    }

    /**
     * Update the specified event in storage
     */
    public function update(Request $request, Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:saggio,workshop,competizione,seminario,altro',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'location' => 'nullable|string|max:255',
            'max_participants' => 'nullable|integer|min:1',
            'price' => 'nullable|numeric|min:0',
            'requires_registration' => 'boolean',
            'registration_deadline' => 'nullable|date|before:start_date',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
            'is_public' => 'boolean',
            'active' => 'boolean'
        ]);

        $validated['price'] = $validated['price'] ?? 0.00;
        $event->update($validated);
        $this->clearSchoolCache();

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Evento aggiornato con successo.', [
                'event' => $event->fresh()->load(['registrations.user'])
            ]);
        }

        return redirect()->route('admin.events.show', $event)
                        ->with('success', 'Evento aggiornato con successo.');
    }

    /**
     * Remove the specified event from storage
     */
    public function destroy(Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        // Check if event has confirmed registrations
        $confirmedRegistrations = $event->registrations()->confirmed()->count();

        if ($confirmedRegistrations > 0) {
            return $this->jsonResponse(false, 'Impossibile eliminare l\'evento. Ci sono ' . $confirmedRegistrations . ' registrazioni confermate.', [], 422);
        }

        $eventName = $event->name;
        $event->delete();

        $this->clearSchoolCache();

        if (request()->ajax() || request()->wantsJson()) {
            return $this->jsonResponse(true, "Evento {$eventName} eliminato con successo.");
        }

        return redirect()->route('admin.events.index')
                        ->with('success', "Evento {$eventName} eliminato con successo.");
    }

    /**
     * Toggle event active status
     */
    public function toggleActive(Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        $event->update(['active' => !$event->active]);
        $this->clearSchoolCache();

        $status = $event->active ? 'attivato' : 'disattivato';
        $message = "Evento {$status} con successo.";

        if (request()->ajax() || request()->wantsJson()) {
            return $this->jsonResponse(true, $message, [
                'status' => $event->active
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk actions for multiple events
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete,export',
            'event_ids' => 'required|array',
            'event_ids.*' => 'integer|exists:events,id'
        ]);

        $eventIds = $request->get('event_ids');

        // Ensure all events belong to current school
        $events = $this->school->events()
            ->whereIn('id', $eventIds)
            ->get();

        if ($events->count() !== count($eventIds)) {
            return $this->jsonResponse(false, 'Alcuni eventi non appartengono alla tua scuola.', [], 403);
        }

        $action = $request->get('action');

        try {
            switch ($action) {
                case 'activate':
                    Event::whereIn('id', $eventIds)->update(['active' => true]);
                    $message = 'Eventi attivati con successo.';
                    break;

                case 'deactivate':
                    Event::whereIn('id', $eventIds)->update(['active' => false]);
                    $message = 'Eventi disattivati con successo.';
                    break;

                case 'delete':
                    // Check for confirmed registrations
                    $confirmedCount = EventRegistration::whereIn('event_id', $eventIds)
                        ->confirmed()
                        ->count();

                    if ($confirmedCount > 0) {
                        return $this->jsonResponse(false, 'Alcuni eventi hanno registrazioni confermate e non possono essere eliminati.', [], 422);
                    }

                    Event::whereIn('id', $eventIds)->delete();
                    $message = 'Eventi eliminati con successo.';
                    break;

                case 'export':
                    return $this->exportEvents($events);

                default:
                    return $this->jsonResponse(false, 'Azione non supportata.', [], 400);
            }

            $this->clearSchoolCache();
            return $this->jsonResponse(true, $message);

        } catch (\Exception $e) {
            \Log::error('Event bulk action failed: ' . $e->getMessage());
            return $this->jsonResponse(false, 'Errore durante l\'operazione.', [], 500);
        }
    }

    /**
     * Export events to CSV
     */
    public function export()
    {
        $events = $this->school->events()
            ->with(['registrations'])
            ->orderBy('start_date', 'desc')
            ->get();

        return $this->exportEventsToCSV($events);
    }

    /**
     * Register a user for an event
     */
    public function registerUser(Request $request, Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where('school_id', $this->school->id)
            ],
            'notes' => 'nullable|string|max:500'
        ]);

        $user = User::findOrFail($request->user_id);

        // Check if user is already registered
        $existingRegistration = $event->registrations()
            ->where('user_id', $user->id)
            ->first();

        if ($existingRegistration) {
            return $this->jsonResponse(false, 'L\'utente è già registrato per questo evento.', [], 422);
        }

        // Check availability
        $status = 'registered';
        if ($event->max_participants && $event->current_registrations_count >= $event->max_participants) {
            $status = 'waitlist';
        }

        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'school_id' => $this->school->id,
            'status' => $status,
            'registration_date' => now(),
            'notes' => $request->notes
        ]);

        $message = $status === 'waitlist' ?
            'Utente aggiunto alla lista d\'attesa.' :
            'Utente registrato con successo.';

        return $this->jsonResponse(true, $message, [
            'registration' => $registration->load('user')
        ]);
    }

    /**
     * Apply search to event query
     */
    protected function applySearch($query, string $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%")
              ->orWhere('type', 'like', "%{$searchTerm}%")
              ->orWhere('location', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Export events collection to CSV
     */
    private function exportEventsToCSV($events)
    {
        $data = $events->map(function ($event) {
            return [
                $event->id,
                $event->name,
                $event->type,
                $event->start_date ? $event->start_date->format('d/m/Y H:i') : '',
                $event->end_date ? $event->end_date->format('d/m/Y H:i') : '',
                $event->location ?? '',
                $event->max_participants ?? 'Illimitato',
                $event->registrations()->active()->count(),
                $event->price ? '€' . number_format($event->price, 2, ',', '.') : 'Gratuito',
                $event->requires_registration ? 'Sì' : 'No',
                $event->is_public ? 'Pubblico' : 'Privato',
                $event->active ? 'Attivo' : 'Non attivo',
                $event->created_at->format('d/m/Y H:i')
            ];
        })->toArray();

        $headers = [
            'ID', 'Nome', 'Tipo', 'Data Inizio', 'Data Fine', 'Location',
            'Max Partecipanti', 'Registrazioni', 'Prezzo', 'Richiede Registrazione',
            'Visibilità', 'Stato', 'Creato il'
        ];

        $filename = 'eventi_' . str_replace(' ', '_', $this->school->name) . '_' . now()->format('Y-m-d') . '.csv';

        return $this->exportToCsv($data, $headers, $filename);
    }
}