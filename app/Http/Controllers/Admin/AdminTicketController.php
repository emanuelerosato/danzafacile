<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTicketController extends Controller
{
    /**
     * Display a listing of school's tickets
     */
    public function index(Request $request)
    {
        $school = Auth::user()->school;

        // Base query - tickets from students of this school
        $query = Ticket::whereHas('user', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })->with(['user', 'assignedTo', 'responses']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('user', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $tickets = $query->latest()->paginate(15)->withQueryString();

        // Stats for cards
        $stats = [
            'total' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count(),
            'open' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'open')->count(),
            'pending' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'pending')->count(),
            'closed' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'closed')->count(),
            'high_priority' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->whereIn('priority', ['high', 'critical'])->count(),
        ];

        // Filter options
        $filterOptions = [
            'statuses' => [
                'open' => 'Aperto',
                'pending' => 'In Attesa',
                'closed' => 'Chiuso',
            ],
            'priorities' => [
                'low' => 'Bassa',
                'medium' => 'Media',
                'high' => 'Alta',
                'critical' => 'Critica',
            ],
            'categories' => [
                'technical' => 'Problema Tecnico',
                'payment' => 'Pagamenti',
                'course' => 'Corso/Lezioni',
                'account' => 'Account/Profilo',
                'general' => 'Informazioni Generali',
                'other' => 'Altro',
            ],
        ];

        // Staff members for bulk assign action
        $staffMembers = User::where('school_id', $school->id)
                           ->where('role', 'admin')
                           ->get();

        return view('admin.tickets.index', compact('tickets', 'stats', 'filterOptions', 'staffMembers'));
    }

    /**
     * Display the specified ticket
     */
    public function show(Ticket $ticket)
    {
        $school = Auth::user()->school;

        // Check if ticket belongs to a student of this school
        if ($ticket->user->school_id !== $school->id) {
            abort(403, 'Non autorizzato ad accedere a questo ticket.');
        }

        $ticket->load(['user', 'assignedTo', 'responses.user']);

        // Get staff members for assignment dropdown
        $staffMembers = User::where('school_id', $school->id)
                           ->where('role', 'admin')
                           ->get();

        return view('admin.tickets.show', compact('ticket', 'staffMembers'));
    }

    /**
     * Update ticket (status, priority, assigned_to)
     */
    public function update(Request $request, Ticket $ticket)
    {
        $school = Auth::user()->school;

        // Check authorization
        if ($ticket->user->school_id !== $school->id) {
            abort(403, 'Non autorizzato.');
        }

        $request->validate([
            'status' => 'nullable|in:open,pending,closed',
            'priority' => 'nullable|in:low,medium,high,critical',
            'assigned_to' => 'nullable|exists:users,id',
        ]);

        $ticket->update($request->only(['status', 'priority', 'assigned_to']));

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket aggiornato con successo!',
            ]);
        }

        return redirect()->back()->with('success', 'Ticket aggiornato con successo!');
    }

    /**
     * Add admin reply to ticket
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $school = Auth::user()->school;

        // Check authorization
        if ($ticket->user->school_id !== $school->id) {
            abort(403, 'Non autorizzato.');
        }

        // Check if ticket is closed
        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Non è possibile rispondere a un ticket chiuso.',
            ], 422);
        }

        $request->validate([
            'message' => 'required|string|max:2000',
        ]);

        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
        ]);

        // Update ticket status to pending (waiting for student reply)
        $ticket->update(['status' => 'pending']);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Risposta inviata con successo.',
                'response' => $response->load('user'),
            ]);
        }

        return redirect()->back()->with('success', 'Risposta inviata con successo.');
    }

    /**
     * Close ticket
     */
    public function close(Ticket $ticket)
    {
        $school = Auth::user()->school;

        // Check authorization
        if ($ticket->user->school_id !== $school->id) {
            abort(403, 'Non autorizzato.');
        }

        if ($ticket->status === 'closed') {
            return redirect()->back()->with('error', 'Il ticket è già chiuso.');
        }

        $ticket->update([
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Ticket chiuso con successo.');
    }

    /**
     * Reopen closed ticket
     */
    public function reopen(Ticket $ticket)
    {
        $school = Auth::user()->school;

        // Check authorization
        if ($ticket->user->school_id !== $school->id) {
            abort(403, 'Non autorizzato.');
        }

        if ($ticket->status !== 'closed') {
            return redirect()->back()->with('error', 'Il ticket non è chiuso.');
        }

        $ticket->update([
            'status' => 'open',
            'closed_at' => null,
        ]);

        return redirect()->back()->with('success', 'Ticket riaperto con successo.');
    }

    /**
     * Assign ticket to staff member
     */
    public function assign(Request $request, Ticket $ticket)
    {
        $school = Auth::user()->school;

        // Check authorization
        if ($ticket->user->school_id !== $school->id) {
            abort(403, 'Non autorizzato.');
        }

        $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        // Verify assigned user belongs to same school
        $assignedUser = User::findOrFail($request->assigned_to);
        if ($assignedUser->school_id !== $school->id) {
            return redirect()->back()->with('error', 'L\'utente selezionato non appartiene a questa scuola.');
        }

        $ticket->update(['assigned_to' => $request->assigned_to]);

        return redirect()->back()->with('success', 'Ticket assegnato con successo.');
    }

    /**
     * Bulk actions on multiple tickets
     */
    public function bulkActions(Request $request)
    {
        $school = Auth::user()->school;

        $request->validate([
            'action' => 'required|in:close,reopen,delete,assign',
            'ticket_ids' => 'required|array',
            'ticket_ids.*' => 'exists:tickets,id',
            'assigned_to' => 'required_if:action,assign|exists:users,id',
        ]);

        $tickets = Ticket::whereIn('id', $request->ticket_ids)
                        ->whereHas('user', function($q) use ($school) {
                            $q->where('school_id', $school->id);
                        })->get();

        $count = 0;

        foreach ($tickets as $ticket) {
            switch ($request->action) {
                case 'close':
                    if ($ticket->status !== 'closed') {
                        $ticket->update([
                            'status' => 'closed',
                            'closed_at' => now(),
                        ]);
                        $count++;
                    }
                    break;

                case 'reopen':
                    if ($ticket->status === 'closed') {
                        $ticket->update([
                            'status' => 'open',
                            'closed_at' => null,
                        ]);
                        $count++;
                    }
                    break;

                case 'delete':
                    $ticket->delete();
                    $count++;
                    break;

                case 'assign':
                    $ticket->update(['assigned_to' => $request->assigned_to]);
                    $count++;
                    break;
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "{$count} ticket elaborati con successo.",
            ]);
        }

        return redirect()->back()->with('success', "{$count} ticket elaborati con successo.");
    }

    /**
     * Get ticket statistics for dashboard
     */
    public function getStats()
    {
        $school = Auth::user()->school;

        $stats = [
            'total' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count(),
            'open' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'open')->count(),
            'pending' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'pending')->count(),
            'closed' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'closed')->count(),
            'high_priority' => Ticket::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->whereIn('priority', ['high', 'critical'])->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get recent tickets for dashboard widget
     */
    public function getRecent(int $limit = 5)
    {
        $school = Auth::user()->school;

        $tickets = Ticket::whereHas('user', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })->with('user')->latest()->limit($limit)->get();

        return response()->json($tickets);
    }
}