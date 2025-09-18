<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    /**
     * Display a listing of user's tickets
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Ticket::where('user_id', $user->id)
                      ->with(['responses' => function($q) {
                          $q->latest()->limit(1); // Solo l'ultima risposta per anteprima
                      }])
                      ->withCount('responses');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->get('priority'));
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $tickets = $query->orderBy('created_at', 'desc')->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('student.tickets.partials.list', compact('tickets'))->render(),
                'pagination' => $tickets->links()->render()
            ]);
        }

        return view('student.tickets.index', compact('tickets'));
    }

    /**
     * Show the form for creating a new ticket
     */
    public function create()
    {
        $categories = [
            'technical' => 'Problema Tecnico',
            'payment' => 'Pagamenti',
            'course' => 'Corso/Lezioni',
            'account' => 'Account/Profilo',
            'general' => 'Informazioni Generali',
            'other' => 'Altro'
        ];

        $priorities = [
            'low' => 'Bassa',
            'medium' => 'Media',
            'high' => 'Alta',
            'critical' => 'Critica'
        ];

        return view('student.tickets.create', compact('categories', 'priorities'));
    }

    /**
     * Store a newly created ticket
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'category' => 'required|in:technical,payment,course,account,general,other',
            'priority' => 'required|in:low,medium,high,critical'
        ]);

        $ticket = Ticket::create([
            'title' => $request->title,
            'description' => $request->description,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
            'user_id' => Auth::id()
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Ticket creato con successo! Ti risponderemo al più presto.',
                'ticket_id' => $ticket->id
            ]);
        }

        return redirect()->route('student.tickets.show', $ticket)
                        ->with('success', 'Ticket creato con successo! Ti risponderemo al più presto.');
    }

    /**
     * Display the specified ticket
     */
    public function show(Ticket $ticket)
    {
        // Check if user owns this ticket
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Non autorizzato ad accedere a questo ticket.');
        }

        $ticket->load(['responses.user', 'assignedTo']);

        return view('student.tickets.show', compact('ticket'));
    }

    /**
     * Add a response to the ticket
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // Check if user owns this ticket
        if ($ticket->user_id !== Auth::id()) {
            abort(403, 'Non autorizzato ad accedere a questo ticket.');
        }

        // Check if ticket is closed
        if ($ticket->status === 'closed') {
            return response()->json([
                'success' => false,
                'message' => 'Non è possibile rispondere a un ticket chiuso.'
            ], 422);
        }

        $request->validate([
            'message' => 'required|string|max:1000'
        ]);

        $response = TicketResponse::create([
            'ticket_id' => $ticket->id,
            'user_id' => Auth::id(),
            'message' => $request->message
        ]);

        // Update ticket status to pending if it was closed
        if ($ticket->status === 'closed') {
            $ticket->update(['status' => 'pending']);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Risposta inviata con successo.',
                'response' => $response->load('user')
            ]);
        }

        return redirect()->back()->with('success', 'Risposta inviata con successo.');
    }

    /**
     * Get ticket statistics for dashboard
     */
    public function getStats()
    {
        $user = Auth::user();

        $stats = [
            'total' => Ticket::where('user_id', $user->id)->count(),
            'open' => Ticket::where('user_id', $user->id)->where('status', 'open')->count(),
            'pending' => Ticket::where('user_id', $user->id)->where('status', 'pending')->count(),
            'closed' => Ticket::where('user_id', $user->id)->where('status', 'closed')->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get recent tickets for dashboard
     */
    public function getRecent(int $limit = 5)
    {
        $user = Auth::user();

        $tickets = Ticket::where('user_id', $user->id)
                        ->with('responses')
                        ->orderBy('created_at', 'desc')
                        ->limit($limit)
                        ->get();

        return response()->json($tickets);
    }
}