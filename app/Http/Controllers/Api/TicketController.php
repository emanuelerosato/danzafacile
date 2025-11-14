<?php

namespace App\Http\Controllers\API;

use App\Models\Ticket;
use App\Models\TicketResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends BaseApiController
{
    /**
     * Get tickets list (different view for admin vs student)
     * Admin: sees tickets from students + tickets sent to SuperAdmin
     * Student: sees only their own tickets
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Ticket::with(['user', 'assignedTo', 'responses']);

        // Role-based filtering
        if ($user->isAdmin()) {
            // Admin sees tickets from students of their school + tickets they created
            $query->where(function($q) use ($user) {
                $q->whereHas('user', function($userQuery) use ($user) {
                    $userQuery->where('school_id', $user->school_id);
                });
            });
        } elseif ($user->isStudent()) {
            // Students see only their own tickets
            $query->where('user_id', $user->id);
        } else {
            return $this->errorResponse('Unauthorized', 403);
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('direction') && $user->isAdmin()) {
            if ($request->direction === 'sent') {
                $query->whereHas('user', function($q) {
                    $q->where('role', 'admin');
                });
            } elseif ($request->direction === 'received') {
                $query->whereHas('user', function($q) {
                    $q->where('role', 'user');
                });
            }
        }

        // Search
        if ($request->filled('search')) {
            $search = \App\Helpers\QueryHelper::sanitizeLikeInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = $request->input('per_page', 15);
        $tickets = $query->latest()->paginate($perPage);

        return $this->successResponse([
            'tickets' => $tickets->items(),
            'pagination' => [
                'current_page' => $tickets->currentPage(),
                'total_pages' => $tickets->lastPage(),
                'per_page' => $tickets->perPage(),
                'total' => $tickets->total(),
            ]
        ]);
    }

    /**
     * Get ticket statistics
     */
    public function statistics(Request $request)
    {
        $user = Auth::user();

        $query = Ticket::query();

        if ($user->isAdmin()) {
            $query->whereHas('user', function($q) use ($user) {
                $q->where('school_id', $user->school_id);
            });
        } elseif ($user->isStudent()) {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'total' => $query->count(),
            'open' => (clone $query)->where('status', 'open')->count(),
            'pending' => (clone $query)->where('status', 'pending')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'high_priority' => (clone $query)->whereIn('priority', ['high', 'critical', 'urgent'])->count(),
        ];

        if ($user->isAdmin()) {
            $stats['sent'] = (clone $query)->whereHas('user', function($q) {
                $q->where('role', 'admin');
            })->count();
            $stats['received'] = (clone $query)->whereHas('user', function($q) {
                $q->where('role', 'user');
            })->count();
        }

        return $this->successResponse($stats);
    }

    /**
     * Create new ticket
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'required|in:technical,payment,course,account,general,billing,feature,other',
            'priority' => 'required|in:low,medium,high,critical,urgent',
        ];

        $validated = $request->validate($rules);

        $ticket = Ticket::create([
            'user_id' => $user->id,
            'school_id' => $user->school_id,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        $ticket->load(['user', 'responses']);

        return $this->successResponse([
            'ticket' => $ticket,
            'message' => 'Ticket creato con successo'
        ], 201);
    }

    /**
     * Show ticket details
     */
    public function show($id)
    {
        $user = Auth::user();

        $ticket = Ticket::with(['user', 'assignedTo', 'responses.user'])->find($id);

        if (!$ticket) {
            return $this->errorResponse('Ticket non trovato', 404);
        }

        // Check authorization
        if ($user->isStudent() && $ticket->user_id !== $user->id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isAdmin() && $ticket->user->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        return $this->successResponse(['ticket' => $ticket]);
    }

    /**
     * Update ticket (admin only - status, priority, assignment)
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return $this->errorResponse('Solo gli admin possono aggiornare i ticket', 403);
        }

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return $this->errorResponse('Ticket non trovato', 404);
        }

        if ($ticket->user->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        $validated = $request->validate([
            'status' => 'nullable|in:open,pending,closed',
            'priority' => 'nullable|in:low,medium,high,critical,urgent',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update($validated);

        return $this->successResponse([
            'ticket' => $ticket->fresh(['user', 'assignedTo']),
            'message' => 'Ticket aggiornato con successo'
        ]);
    }

    /**
     * Reply to ticket
     */
    public function reply(Request $request, $id)
    {
        $user = Auth::user();

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return $this->errorResponse('Ticket non trovato', 404);
        }

        // Check authorization
        if ($user->isStudent() && $ticket->user_id !== $user->id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isAdmin() && $ticket->user->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($ticket->status === 'closed') {
            return $this->errorResponse('Non è possibile rispondere a un ticket chiuso', 422);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'message' => $validated['message'],
        ]);

        // Update ticket status
        if ($user->isAdmin()) {
            $ticket->update(['status' => 'pending']); // Admin replied, waiting for student
        } else {
            $ticket->update(['status' => 'open']); // Student replied, needs admin attention
        }

        $response->load('user');

        return $this->successResponse([
            'response' => $response,
            'message' => 'Risposta inviata con successo'
        ], 201);
    }

    /**
     * Close ticket
     */
    public function close($id)
    {
        $user = Auth::user();

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return $this->errorResponse('Ticket non trovato', 404);
        }

        // Authorization
        if ($user->isStudent() && $ticket->user_id !== $user->id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isAdmin() && $ticket->user->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($ticket->status === 'closed') {
            return $this->errorResponse('Il ticket è già chiuso', 422);
        }

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return $this->successResponse([
            'ticket' => $ticket,
            'message' => 'Ticket chiuso con successo'
        ]);
    }

    /**
     * Reopen ticket
     */
    public function reopen($id)
    {
        $user = Auth::user();

        $ticket = Ticket::find($id);

        if (!$ticket) {
            return $this->errorResponse('Ticket non trovato', 404);
        }

        // Authorization
        if ($user->isStudent() && $ticket->user_id !== $user->id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($user->isAdmin() && $ticket->user->school_id !== $user->school_id) {
            return $this->errorResponse('Non autorizzato', 403);
        }

        if ($ticket->status !== 'closed') {
            return $this->errorResponse('Il ticket non è chiuso', 422);
        }

        $ticket->update([
            'status' => 'open',
            'closed_at' => null,
        ]);

        return $this->successResponse([
            'ticket' => $ticket,
            'message' => 'Ticket riaperto con successo'
        ]);
    }
}
