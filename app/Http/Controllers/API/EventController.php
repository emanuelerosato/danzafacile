<?php

namespace App\Http\Controllers\Api;

use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

/**
 * Events API Controller
 *
 * Handles event management for Flutter app including:
 * - Browse available events
 * - Event registration
 * - My registered events
 * - Event details with registration status
 */
class EventController extends BaseApiController
{
    /**
     * Get available events for user's school
     */
    public function index(Request $request): JsonResponse
    {
        $params = $this->getPaginationParams($request);
        $sort = $this->getSortingParams($request, 'start_date', 'asc');

        $query = Event::query();
        $this->scopeToUserSchool($query);

        // Filter by upcoming events by default
        if (!$request->has('show_all')) {
            $query->where('start_date', '>', now());
        }

        // Apply filters
        $filterableFields = ['category', 'location'];
        $this->applyFilters($query, $request, $filterableFields);

        // Search
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sort['sort'], $sort['order']);
        $events = $query->paginate($params['per_page'], ['*'], 'page', $params['page']);

        // Add registration status for authenticated user
        $user = $this->getAuthenticatedUser();
        $events->getCollection()->transform(function ($event) use ($user) {
            $event->is_registered = $user ?
                EventRegistration::where('event_id', $event->id)
                    ->where('user_id', $user->id)
                    ->exists() : false;

            $event->available_spots = $event->max_participants - $event->registrations()->count();
            $event->is_full = $event->available_spots <= 0;

            return $event;
        });

        return $this->paginatedResponse($events, 'Events retrieved successfully');
    }

    /**
     * Get event details with full information
     */
    public function show(Request $request, Event $event): JsonResponse
    {
        // Check multi-tenant access
        if (!$this->validateTenantAccess($event)) {
            return $this->forbiddenResponse('Access denied to this event');
        }

        $user = $this->getAuthenticatedUser();

        // Load relationships
        $event->load(['school', 'registrations.user']);

        // Add computed fields
        $event->is_registered = $user ?
            EventRegistration::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->exists() : false;

        $event->available_spots = $event->max_participants - $event->registrations()->count();
        $event->is_full = $event->available_spots <= 0;
        $event->registration_count = $event->registrations()->count();

        // Add user's registration details if registered
        if ($event->is_registered && $user) {
            $event->my_registration = EventRegistration::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();
        }

        return $this->successResponse([
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'description' => $event->description,
                'start_date' => $event->start_date->toISOString(),
                'end_date' => $event->end_date->toISOString(),
                'location' => $event->location,
                'max_participants' => $event->max_participants,
                'price' => $event->price,
                'category' => $event->category,
                'image_url' => $event->image_url,
                'is_registered' => $event->is_registered,
                'available_spots' => $event->available_spots,
                'is_full' => $event->is_full,
                'registration_count' => $event->registration_count,
                'school' => [
                    'id' => $event->school->id,
                    'name' => $event->school->name,
                ],
                'my_registration' => $event->my_registration ?? null,
            ]
        ], 'Event details retrieved successfully');
    }

    /**
     * Register for an event
     */
    public function register(Request $request, Event $event): JsonResponse
    {
        // Check multi-tenant access
        if (!$this->validateTenantAccess($event)) {
            return $this->forbiddenResponse('Access denied to this event');
        }

        $user = $this->getAuthenticatedUser();

        // Validation
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        // Check if event is in the future
        if ($event->start_date <= now()) {
            return $this->errorResponse('Cannot register for past events', 400);
        }

        // Check if already registered
        $existingRegistration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existingRegistration) {
            return $this->errorResponse('Already registered for this event', 400);
        }

        // Check availability
        $currentRegistrations = $event->registrations()->count();
        if ($currentRegistrations >= $event->max_participants) {
            return $this->errorResponse('Event is full', 400);
        }

        // Create registration
        $registration = EventRegistration::create([
            'event_id' => $event->id,
            'user_id' => $user->id,
            'registration_date' => now(),
            'status' => 'confirmed',
            'notes' => $request->input('notes'),
        ]);

        return $this->successResponse([
            'registration' => [
                'id' => $registration->id,
                'event_id' => $registration->event_id,
                'registration_date' => $registration->registration_date->toISOString(),
                'status' => $registration->status,
                'notes' => $registration->notes,
            ],
            'event' => [
                'id' => $event->id,
                'name' => $event->name,
                'start_date' => $event->start_date->toISOString(),
                'location' => $event->location,
            ]
        ], 'Successfully registered for event', 201);
    }

    /**
     * Cancel event registration
     */
    public function cancelRegistration(Request $request, Event $event): JsonResponse
    {
        // Check multi-tenant access
        if (!$this->validateTenantAccess($event)) {
            return $this->forbiddenResponse('Access denied to this event');
        }

        $user = $this->getAuthenticatedUser();

        $registration = EventRegistration::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$registration) {
            return $this->errorResponse('Not registered for this event', 400);
        }

        // Check if event is in the future (allow cancellation up to 24h before)
        if ($event->start_date <= now()->addHours(24)) {
            return $this->errorResponse('Cannot cancel registration less than 24 hours before event', 400);
        }

        $registration->delete();

        return $this->successResponse(null, 'Registration cancelled successfully');
    }

    /**
     * Get user's registered events
     */
    public function myEvents(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        $params = $this->getPaginationParams($request);

        $query = EventRegistration::where('user_id', $user->id)
            ->with(['event.school']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by upcoming/past
        if ($request->get('type') === 'upcoming') {
            $query->whereHas('event', function($q) {
                $q->where('start_date', '>', now());
            });
        } elseif ($request->get('type') === 'past') {
            $query->whereHas('event', function($q) {
                $q->where('start_date', '<=', now());
            });
        }

        $query->orderBy('registration_date', 'desc');
        $registrations = $query->paginate($params['per_page'], ['*'], 'page', $params['page']);

        // Transform data
        $registrations->getCollection()->transform(function ($registration) {
            return [
                'id' => $registration->id,
                'registration_date' => $registration->registration_date->toISOString(),
                'status' => $registration->status,
                'notes' => $registration->notes,
                'event' => [
                    'id' => $registration->event->id,
                    'name' => $registration->event->name,
                    'description' => $registration->event->description,
                    'start_date' => $registration->event->start_date->toISOString(),
                    'end_date' => $registration->event->end_date->toISOString(),
                    'location' => $registration->event->location,
                    'price' => $registration->event->price,
                    'category' => $registration->event->category,
                    'image_url' => $registration->event->image_url,
                    'school' => [
                        'id' => $registration->event->school->id,
                        'name' => $registration->event->school->name,
                    ]
                ]
            ];
        });

        return $this->paginatedResponse($registrations, 'Registered events retrieved successfully');
    }

    /**
     * Get event categories (for filtering)
     */
    public function categories(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        $userSchool = $this->getUserSchool();

        if (!$userSchool) {
            return $this->forbiddenResponse('User not associated with any school');
        }

        $categories = Event::where('school_id', $userSchool->id)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();

        return $this->successResponse([
            'categories' => $categories
        ], 'Event categories retrieved successfully');
    }
}