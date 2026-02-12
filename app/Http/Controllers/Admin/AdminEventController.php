<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\User;
use App\Helpers\QueryHelper;
use App\Helpers\FileUploadHelper;
use App\Http\Requests\Admin\StoreEventRequest;
use App\Http\Requests\Admin\UpdateEventRequest;
use App\Services\EventService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminEventController extends AdminBaseController
{
    /**
     * EventService per business logic eventi
     */
    protected EventService $eventService;

    /**
     * Dependency injection del service
     */
    public function __construct(EventService $eventService)
    {
        parent::__construct();
        $this->eventService = $eventService;
    }

    /**
     * Display a listing of events for the current school
     */
    public function index(Request $request)
    {
        if (!$this->school) {
            abort(403, 'Nessuna scuola associata al tuo account.');
        }

        $query = $this->school->events()->with(['registrations.user']);

        // SECURE: allowed sort fields for events
        // SENIOR FIX: Changed 'title' to 'name' (correct column name in events table)
        $allowedSortFields = ['name', 'start_date', 'end_date', 'max_participants', 'created_at', 'updated_at'];
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
        if (!$this->school) {
            abort(403, 'Nessuna scuola associata al tuo account.');
        }

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
     * Store a newly created event in storage.
     *
     * Usa StoreEventRequest per validation, EventPolicy per authorization,
     * EventService per business logic (image upload, slug generation, cache).
     */
    public function store(StoreEventRequest $request)
    {
        // Authorization tramite EventPolicy (già verificata in StoreEventRequest::authorize())
        $this->authorize('create', Event::class);

        try {
            DB::beginTransaction();

            // Ottieni dati validati con school_id incluso
            $validated = $request->validatedWithSchool();

            // Prepara dati evento (slug, defaults) usando EventService
            $validated = $this->eventService->prepareEventData($validated);

            // Handle image upload con EventService (3 dimensioni + compressione)
            if ($request->hasFile('image')) {
                try {
                    $imagePaths = $this->eventService->handleImageUpload(
                        $request->file('image'),
                        null // eventId null perché ancora non creato, verrà spostato dopo create
                    );

                    // Salva path della versione original (EventService crea 3 dimensioni)
                    $validated['image_path'] = $imagePaths['original'];

                } catch (\RuntimeException $e) {
                    return back()->withErrors(['image' => $e->getMessage()])->withInput();
                }
            }

            // Crea evento
            $event = Event::create($validated);

            // Logging dettagliato per audit trail
            Log::info('Event created successfully', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'event_type' => $event->type,
                'school_id' => $event->school_id,
                'school_name' => $event->school->name ?? 'N/A',
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'user_name' => auth()->user()->name,
                'is_public' => $event->is_public,
                'requires_registration' => $event->requires_registration,
                'price_students' => $event->price_students,
                'price_guests' => $event->price_guests,
                'start_date' => $event->start_date->toDateTimeString(),
                'created_at' => now()->toDateTimeString(),
            ]);

            // Invalida cache usando EventService con Redis tags
            $this->eventService->clearEventCache($event->school_id, $event->is_public);

            DB::commit();

            if ($request->ajax()) {
                return $this->jsonResponse(true, 'Evento creato con successo.', [
                    'event' => $event->load(['registrations.user'])
                ]);
            }

            return redirect()->route('admin.events.show', $event)
                            ->with('success', 'Evento creato con successo.');

        } catch (\Exception $e) {
            DB::rollBack();

            // Cleanup uploaded images se esistono (tutte e 3 le dimensioni)
            if (isset($validated['image_path'])) {
                $this->eventService->deleteEventImage($validated['image_path']);
            }

            // Logging errore per debugging
            Log::error('Event creation failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'school_id' => $this->school->id ?? null,
                'user_id' => auth()->id(),
                'request_data' => $request->except(['image', '_token'])
            ]);

            return back()
                ->withErrors(['error' => 'Si è verificato un errore durante la creazione dell\'evento. Riprova.'])
                ->withInput();
        }
    }

    /**
     * Display the specified event
     */
    public function show(Event $event)
    {
        if (!$this->school) {
            abort(403, 'Nessuna scuola associata al tuo account.');
        }

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
        if (!$this->school) {
            abort(403, 'Nessuna scuola associata al tuo account.');
        }

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
     * Update the specified event in storage.
     *
     * Usa UpdateEventRequest per validation, EventPolicy per authorization,
     * EventService per business logic.
     */
    public function update(UpdateEventRequest $request, Event $event)
    {
        // Authorization tramite EventPolicy
        $this->authorize('update', $event);

        try {
            // Salva valori originali per audit log
            $originalValues = [
                'name' => $event->name,
                'active' => $event->active,
                'price_students' => $event->price_students,
                'price_guests' => $event->price_guests,
                'start_date' => $event->start_date->toDateTimeString(),
            ];

            // Ottieni dati validati
            $validated = $request->validated();

            // Prepara dati (defaults, slug se cambia nome)
            $validated = $this->eventService->prepareEventData($validated, $event->id);

            // Handle image upload con EventService
            if ($request->hasFile('image')) {
                try {
                    // Elimina vecchia immagine (tutte le 3 dimensioni)
                    if ($event->image_path) {
                        $this->eventService->deleteEventImage($event->image_path);
                    }

                    // Upload nuova immagine (3 dimensioni + compressione)
                    $imagePaths = $this->eventService->handleImageUpload(
                        $request->file('image'),
                        $event->id
                    );

                    $validated['image_path'] = $imagePaths['original'];

                } catch (\RuntimeException $e) {
                    return back()->withErrors(['image' => $e->getMessage()])->withInput();
                }
            }

            // Update evento
            $event->update($validated);

            // Logging dettagliato per audit trail
            $changes = [];
            foreach ($originalValues as $key => $originalValue) {
                if ($event->{$key} != $originalValue) {
                    $changes[$key] = [
                        'old' => $originalValue,
                        'new' => $event->{$key}
                    ];
                }
            }

            Log::info('Event updated successfully', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'school_id' => $event->school_id,
                'school_name' => $event->school->name ?? 'N/A',
                'user_id' => auth()->id(),
                'user_email' => auth()->user()->email,
                'user_name' => auth()->user()->name,
                'changes' => $changes,
                'updated_at' => now()->toDateTimeString(),
            ]);

            // Invalida cache
            $this->eventService->clearEventCache($event->school_id, $event->is_public);

            if ($request->ajax()) {
                return $this->jsonResponse(true, 'Evento aggiornato con successo.', [
                    'event' => $event->fresh()->load(['registrations.user'])
                ]);
            }

            return redirect()->route('admin.events.show', $event)
                            ->with('success', 'Evento aggiornato con successo.');

        } catch (\Exception $e) {
            // Logging errore
            Log::error('Event update failed', [
                'event_id' => $event->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id(),
                'request_data' => $request->except(['image', '_token'])
            ]);

            return back()
                ->withErrors(['error' => 'Si è verificato un errore durante l\'aggiornamento dell\'evento. Riprova.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified event from storage.
     *
     * Usa EventPolicy per authorization, verifica business rules,
     * logging dettagliato per audit trail.
     */
    public function destroy(Event $event)
    {
        // Authorization tramite EventPolicy
        $this->authorize('delete', $event);

        // Verifica business rule: no delete se ci sono registrazioni confermate
        $deleteCheck = $this->eventService->canDeleteEvent($event);

        if (!$deleteCheck['can_delete']) {
            Log::warning('Event deletion blocked - has confirmed registrations', [
                'event_id' => $event->id,
                'event_name' => $event->name,
                'school_id' => $event->school_id,
                'user_id' => auth()->id(),
                'reason' => $deleteCheck['reason']
            ]);

            if (request()->ajax() || request()->wantsJson()) {
                return $this->jsonResponse(false, $deleteCheck['reason'], [], 422);
            }

            return back()->withErrors(['error' => $deleteCheck['reason']]);
        }

        // Salva dati per logging prima della delete
        $eventData = [
            'id' => $event->id,
            'name' => $event->name,
            'type' => $event->type,
            'school_id' => $event->school_id,
            'school_name' => $event->school->name ?? 'N/A',
            'start_date' => $event->start_date->toDateTimeString(),
            'created_at' => $event->created_at->toDateTimeString(),
        ];

        $eventName = $event->name;

        // Elimina immagine se esiste (tutte le 3 dimensioni)
        if ($event->image_path) {
            $this->eventService->deleteEventImage($event->image_path);
        }

        // Elimina evento
        $event->delete();

        // Logging dettagliato per audit trail
        Log::info('Event deleted successfully', [
            'event_data' => $eventData,
            'user_id' => auth()->id(),
            'user_email' => auth()->user()->email,
            'user_name' => auth()->user()->name,
            'deleted_at' => now()->toDateTimeString(),
        ]);

        // Invalida cache
        $this->eventService->clearEventCache($eventData['school_id']);

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

        // Logging per toggleActive
        Log::info('Event active status toggled', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'new_status' => $event->active ? 'active' : 'inactive',
            'user_id' => auth()->id(),
        ]);

        $this->eventService->clearEventCache($event->school_id);

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

            // Invalida cache dopo bulk action
            $this->eventService->clearEventCache($this->school->id);

            // Logging bulk action
            Log::info('Event bulk action executed', [
                'action' => $action,
                'event_ids' => $eventIds,
                'count' => count($eventIds),
                'school_id' => $this->school->id,
                'user_id' => auth()->id(),
            ]);

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
            ->withCount([
                'registrations as active_registrations_count' => function($query) {
                    $query->where('status', 'registered')
                          ->orWhere('status', 'confirmed');
                }
            ])
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
                $event->active_registrations_count ?? 0,
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

    /**
     * Dashboard dedicata agli eventi pubblici con stats e KPI
     */
    public function publicDashboard()
    {
        $school = $this->school;

        // Eventi pubblici attivi
        $publicEvents = $school->events()
            ->where('is_public', true)
            ->where('active', true)
            ->with(['registrations', 'payments'])
            ->orderBy('start_date', 'desc')
            ->get();

        // Calcola stats
        $stats = [
            'total_public_events' => $publicEvents->count(),
            'upcoming_events' => $publicEvents->where('start_date', '>', now())->count(),
            'past_events' => $publicEvents->where('start_date', '<', now())->count(),
            'total_registrations' => $publicEvents->sum(fn($e) => $e->registrations->count()),
            'guest_registrations' => EventRegistration::whereIn('event_id', $publicEvents->pluck('id'))
                ->whereHas('user', fn($q) => $q->where('is_guest', true))
                ->count(),
            'total_revenue' => $publicEvents->sum(fn($e) =>
                $e->payments()->where('status', 'completed')->sum('amount')
            ),
            'pending_payments' => $publicEvents->sum(fn($e) =>
                $e->payments()->where('status', 'pending')->count()
            ),
        ];

        // Ultimi 5 eventi pubblici
        $recentEvents = $publicEvents->take(5);

        return view('admin.events.public-dashboard', compact('stats', 'recentEvents'));
    }

    /**
     * Mostra form per personalizzare landing page evento
     */
    public function customizeLanding(Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        // Verifica che sia evento pubblico
        if (!$event->is_public) {
            abort(403, 'Solo eventi pubblici possono avere landing page personalizzate.');
        }

        return view('admin.events.customize-landing', compact('event'));
    }

    /**
     * Salva personalizzazioni landing page
     */
    public function updateLanding(Request $request, Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        $validated = $request->validate([
            'short_description' => 'nullable|string|max:200',
            'landing_description' => 'nullable|string|max:5000',
            'landing_cta_text' => 'nullable|string|max:50',
            'event_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
            'show_location_map' => 'boolean',
            'show_instructors' => 'boolean',
            'meta_title' => 'nullable|string|max:200',
            'meta_description' => 'nullable|string|max:300',
        ]);

        // Gestione upload immagine
        $imagePath = $event->image_path;
        if ($request->hasFile('event_image')) {
            // Elimina vecchia immagine se esiste
            if ($event->image_path && \Storage::disk('public')->exists($event->image_path)) {
                \Storage::disk('public')->delete($event->image_path);
            }

            // Salva nuova immagine
            $imagePath = $request->file('event_image')->store('events', 'public');
        }

        // Aggiorna campi diretti dell'evento
        $event->update([
            'short_description' => $validated['short_description'],
            'landing_description' => $validated['landing_description'],
            'landing_cta_text' => $validated['landing_cta_text'] ?? 'Iscriviti Ora',
            'image_path' => $imagePath,
        ]);

        // Salva opzioni avanzate in additional_info JSON
        $additionalInfo = $event->additional_info ?? [];
        $additionalInfo['landing_customization'] = [
            'show_location_map' => $validated['show_location_map'] ?? false,
            'show_instructors' => $validated['show_instructors'] ?? false,
            'meta_title' => $validated['meta_title'],
            'meta_description' => $validated['meta_description'],
        ];

        $event->update([
            'additional_info' => $additionalInfo,
        ]);

        // Logging landing page customization
        Log::info('Event landing page customized', [
            'event_id' => $event->id,
            'event_name' => $event->name,
            'school_id' => $event->school_id,
            'user_id' => auth()->id(),
            'has_meta' => isset($validated['meta_title']) || isset($validated['meta_description']),
        ]);

        $this->eventService->clearEventCache($event->school_id, true); // publicOnly = true

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Landing page personalizzata aggiornata con successo!');
    }

    /**
     * Report dettagliato iscrizioni guest per eventi pubblici
     */
    public function guestRegistrationsReport(Request $request)
    {
        $school = $this->school;

        // Filtri
        $eventId = $request->get('event_id');
        $status = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // Query base
        $query = EventRegistration::whereHas('event', function($q) use ($school) {
                $q->where('school_id', $school->id)
                  ->where('is_public', true);
            })
            ->whereHas('user', function($q) {
                $q->where('is_guest', true);
            })
            ->with(['event', 'user', 'eventPayment']);

        // Applica filtri
        if ($eventId) {
            $query->where('event_id', $eventId);
        }
        if ($status) {
            $query->where('status', $status);
        }
        if ($dateFrom) {
            $query->where('registration_date', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('registration_date', '<=', $dateTo);
        }

        $registrations = $query->orderBy('registration_date', 'desc')
            ->paginate(25);

        // Eventi pubblici per select filtro
        $publicEvents = $school->events()
            ->where('is_public', true)
            ->orderBy('name')
            ->get();

        // Stats
        $stats = [
            'total' => $query->count(),
            'confirmed' => (clone $query)->where('status', 'confirmed')->count(),
            'pending' => (clone $query)->where('status', 'pending_payment')->count(),
            'cancelled' => (clone $query)->where('status', 'cancelled')->count(),
            'checked_in' => (clone $query)->whereNotNull('checked_in_at')->count(),
        ];

        return view('admin.events.guest-report', compact('registrations', 'publicEvents', 'stats'));
    }

    /**
     * Export CSV guest registrations con filtri
     */
    public function exportGuestRegistrations(Request $request)
    {
        $school = $this->school;

        // Stessi filtri del report
        $eventId = $request->get('event_id');
        $status = $request->get('status');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $query = EventRegistration::whereHas('event', function($q) use ($school) {
                $q->where('school_id', $school->id)
                  ->where('is_public', true);
            })
            ->whereHas('user', function($q) {
                $q->where('is_guest', true);
            })
            ->with(['event', 'user', 'eventPayment']);

        // Applica filtri
        if ($eventId) $query->where('event_id', $eventId);
        if ($status) $query->where('status', $status);
        if ($dateFrom) $query->where('registration_date', '>=', $dateFrom);
        if ($dateTo) $query->where('registration_date', '<=', $dateTo);

        $registrations = $query->orderBy('registration_date', 'desc')->get();

        // Genera CSV
        $csv = $this->generateGuestRegistrationsCSV($registrations);

        $filename = 'guest-registrations-' . now()->format('Y-m-d-His') . '.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    /**
     * Genera CSV guest registrations
     */
    private function generateGuestRegistrationsCSV($registrations)
    {
        $output = fopen('php://temp', 'r+');

        // UTF-8 BOM per Excel
        fwrite($output, "\xEF\xBB\xBF");

        // Header
        fputcsv($output, [
            'ID Registrazione',
            'Nome Guest',
            'Email',
            'Telefono',
            'Evento',
            'Data Evento',
            'Data Registrazione',
            'Status',
            'Check-in',
            'Importo',
            'Metodo Pagamento',
            'Privacy Consent',
            'Marketing Consent',
            'Newsletter Consent',
        ]);

        // Dati
        foreach ($registrations as $reg) {
            fputcsv($output, [
                $reg->id,
                $reg->user->name,
                $reg->user->email,
                $reg->user->guest_phone ?? 'N/A',
                $reg->event->name,
                $reg->event->start_date->format('d/m/Y H:i'),
                $reg->registration_date->format('d/m/Y H:i'),
                $reg->status,
                $reg->checked_in_at ? $reg->checked_in_at->format('d/m/Y H:i') : 'No',
                $reg->eventPayment ? '€' . number_format($reg->eventPayment->amount, 2) : '€0.00',
                $reg->eventPayment->payment_method ?? 'N/A',
                $this->getGdprConsent($reg->user, 'privacy'),
                $this->getGdprConsent($reg->user, 'marketing'),
                $this->getGdprConsent($reg->user, 'newsletter'),
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Helper per ottenere consent GDPR
     */
    private function getGdprConsent($user, $type)
    {
        $consent = \App\Models\GdprConsent::where('user_id', $user->id)
            ->where('consent_type', $type)
            ->where('consented', true)
            ->latest('consented_at')
            ->first();

        return $consent ? 'Sì (' . $consent->consented_at->format('d/m/Y') . ')' : 'No';
    }
}