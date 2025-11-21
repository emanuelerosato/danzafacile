<?php

namespace App\Http\Controllers\Api;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoomController extends BaseApiController
{
    /**
     * Get rooms list (admin only)
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono visualizzare le aule', 403);
        }

        $query = Room::where('school_id', $user->school_id);

        // Filters
        if ($request->filled('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Search
        if ($request->filled('search')) {
            $search = \App\Helpers\QueryHelper::sanitizeLikeInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $rooms = $query->orderBy('name')->paginate($perPage);

        return $this->successResponse([
            'rooms' => $rooms->items(),
            'pagination' => [
                'current_page' => $rooms->currentPage(),
                'total_pages' => $rooms->lastPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
            ]
        ]);
    }

    /**
     * Get room statistics (admin only)
     */
    public function statistics(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono visualizzare le statistiche', 403);
        }

        $query = Room::where('school_id', $user->school_id);

        $stats = [
            'total' => $query->count(),
            'active' => (clone $query)->where('active', true)->count(),
            'inactive' => (clone $query)->where('active', false)->count(),
            'total_capacity' => (clone $query)->sum('capacity'),
            'avg_capacity' => round((clone $query)->avg('capacity')),
        ];

        return $this->successResponse($stats);
    }

    /**
     * Create new room (admin only)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono creare aule', 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'capacity' => 'required|integer|min:1',
            'active' => 'boolean',
        ]);

        $room = Room::create([
            'school_id' => $user->school_id,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'location' => $validated['location'] ?? null,
            'capacity' => $validated['capacity'],
            'active' => $validated['active'] ?? true,
        ]);

        return $this->successResponse([
            'room' => $room,
            'message' => 'Aula creata con successo'
        ], 201);
    }

    /**
     * Show room details
     */
    public function show($id)
    {
        $user = Auth::user();

        $room = Room::find($id);

        if (!$room) {
            return $this->errorResponse('Aula non trovata', 404);
        }

        // Check authorization
        if ($user->isAdmin() && $room->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isStudent() && $room->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        return $this->successResponse(['room' => $room]);
    }

    /**
     * Update room (admin only)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono aggiornare le aule', 403);
        }

        $room = Room::find($id);

        if (!$room) {
            return $this->errorResponse('Aula non trovata', 404);
        }

        if ($room->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'capacity' => 'nullable|integer|min:1',
            'active' => 'nullable|boolean',
        ]);

        $room->update(array_filter($validated, function($value) {
            return !is_null($value);
        }));

        return $this->successResponse([
            'room' => $room->fresh(),
            'message' => 'Aula aggiornata con successo'
        ]);
    }

    /**
     * Delete room (admin only)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono eliminare le aule', 403);
        }

        $room = Room::find($id);

        if (!$room) {
            return $this->errorResponse('Aula non trovata', 404);
        }

        if ($room->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        // Check if room is used in courses
        $coursesCount = $room->courses()->count();
        if ($coursesCount > 0) {
            return $this->errorResponse("L'aula è utilizzata in {$coursesCount} corsi. Impossibile eliminarla.", 422);
        }

        $room->delete();

        return $this->successResponse([
            'message' => 'Aula eliminata con successo'
        ]);
    }

    /**
     * Toggle room status (admin only)
     */
    public function toggleStatus($id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono modificare lo stato delle aule', 403);
        }

        $room = Room::find($id);

        if (!$room) {
            return $this->errorResponse('Aula non trovata', 404);
        }

        if ($room->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $room->update(['active' => !$room->active]);

        return $this->successResponse([
            'room' => $room,
            'message' => $room->active ? 'Aula attivata' : 'Aula disattivata'
        ]);
    }

    /**
     * Get room availability/schedule
     */
    public function availability(Request $request, $id)
    {
        $user = Auth::user();

        $room = Room::find($id);

        if (!$room) {
            return $this->errorResponse('Aula non trovata', 404);
        }

        if ($room->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        // Get courses using this room
        $courses = $room->courses()
            ->with(['instructor', 'enrollments'])
            ->where('active', true)
            ->get()
            ->map(function($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'instructor' => $course->instructor ? $course->instructor->name : null,
                    'schedule' => $course->schedule,
                    'start_date' => $course->start_date,
                    'end_date' => $course->end_date,
                    'enrolled_count' => $course->enrollments->where('status', 'active')->count(),
                ];
            });

        return $this->successResponse([
            'room' => $room,
            'courses' => $courses,
            'utilization' => [
                'total_courses' => $courses->count(),
                'total_students' => $courses->sum('enrolled_count'),
                'capacity_usage' => $room->capacity > 0
                    ? round(($courses->sum('enrolled_count') / $room->capacity) * 100, 2)
                    : 0,
            ]
        ]);
    }
}
