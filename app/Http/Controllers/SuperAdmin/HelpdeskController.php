<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketResponse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HelpdeskController extends Controller
{
    /**
     * Display tickets list with filters and pagination
     */
    public function index(Request $request)
    {
        try {
            // Get filter parameters
            $status = $request->get('status', 'all');
            $priority = $request->get('priority', 'all');
            $category = $request->get('category');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');
            $search = $request->get('search');
            $perPage = $request->get('per_page', 25);

            // Build query with eager loading
            $query = Ticket::with(['user', 'assignedTo'])
                          ->withCount('responses');

            // Apply filters
            if ($status !== 'all') {
                $query->where('status', $status);
            }

            if ($priority !== 'all') {
                $query->where('priority', $priority);
            }

            if ($category) {
                $query->where('category', $category);
            }

            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }

            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }

            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('title', 'LIKE', "%{$search}%")
                      ->orWhere('description', 'LIKE', "%{$search}%")
                      ->orWhereHas('user', function($userQuery) use ($search) {
                          $userQuery->where('name', 'LIKE', "%{$search}%")
                                   ->orWhere('email', 'LIKE', "%{$search}%");
                      });
                });
            }

            // Get paginated results
            $tickets = $query->orderBy('created_at', 'desc')->paginate($perPage);

            // Get statistics
            $stats = $this->getTicketStats();

            // Get categories for filter
            $categories = Ticket::distinct('category')
                               ->whereNotNull('category')
                               ->pluck('category')
                               ->sort();

            return view('super-admin.helpdesk.index', compact(
                'tickets', 'stats', 'categories', 'status', 'priority', 
                'category', 'dateFrom', 'dateTo', 'search'
            ));

        } catch (\Exception $e) {
            \Log::error('Error loading helpdesk tickets', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Errore durante il caricamento dei ticket.');
        }
    }

    /**
     * Show ticket details with response timeline
     */
    public function show(Ticket $ticket)
    {
        try {
            // Load relationships
            $ticket->load([
                'user',
                'assignedTo', 
                'responses' => function($query) {
                    $query->with('user')->orderBy('created_at', 'asc');
                }
            ]);

            // Get assignable users (Super Admins only)
            $assignableUsers = User::where('role', 'super_admin')
                                  ->where('id', '!=', $ticket->user_id)
                                  ->orderBy('name')
                                  ->get();

            return view('super-admin.helpdesk.show', compact('ticket', 'assignableUsers'));

        } catch (\Exception $e) {
            \Log::error('Error loading ticket details', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Errore durante il caricamento del ticket.');
        }
    }

    /**
     * Store new ticket response with file upload
     */
    public function reply(Request $request, Ticket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|min:10|max:5000',
            'is_internal' => 'boolean',
            'attachments.*' => 'nullable|file|mimes:jpg,jpeg,png,gif,pdf|max:5120', // 5MB max
        ], [
            'message.required' => 'Il messaggio è obbligatorio.',
            'message.min' => 'Il messaggio deve essere di almeno 10 caratteri.',
            'message.max' => 'Il messaggio non può superare i 5000 caratteri.',
            'attachments.*.mimes' => 'Solo file JPG, PNG, GIF, PDF sono permessi.',
            'attachments.*.max' => 'Ogni file non può superare i 5MB.',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            // Handle file uploads
            $attachmentPaths = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store('helpdesk/attachments/' . date('Y/m/d'), 'public');
                    $attachmentPaths[] = $path;
                }
            }

            // Create response
            $response = TicketResponse::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => $request->message,
                'attachments' => !empty($attachmentPaths) ? $attachmentPaths : null,
                'is_internal' => $request->boolean('is_internal', false)
            ]);

            // Update ticket status if was open
            if ($ticket->status === 'open') {
                $ticket->update(['status' => 'pending']);
            }

            \Log::info('Ticket response added', [
                'ticket_id' => $ticket->id,
                'response_id' => $response->id,
                'user_id' => auth()->id(),
                'has_attachments' => !empty($attachmentPaths)
            ]);

            return back()->with('success', 'Risposta aggiunta con successo.');

        } catch (\Exception $e) {
            \Log::error('Error adding ticket response', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Errore durante l\'aggiunta della risposta.');
        }
    }

    /**
     * Update ticket status, priority, or assignment
     */
    public function update(Request $request, Ticket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'status' => ['nullable', Rule::in(['open', 'pending', 'closed'])],
            'priority' => ['nullable', Rule::in(['low', 'medium', 'high', 'critical'])],
            'assigned_to' => 'nullable|exists:users,id',
            'category' => 'nullable|string|max:255'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            $changes = [];
            $oldValues = [
                'status' => $ticket->status,
                'priority' => $ticket->priority,
                'assigned_to' => $ticket->assigned_to,
                'category' => $ticket->category
            ];

            // Update fields
            if ($request->filled('status') && $request->status !== $ticket->status) {
                $ticket->status = $request->status;
                $changes['status'] = ['from' => $oldValues['status'], 'to' => $request->status];
                
                // Set closed_at timestamp if closing
                if ($request->status === 'closed') {
                    $ticket->closed_at = now();
                } elseif ($oldValues['status'] === 'closed') {
                    $ticket->closed_at = null;
                }
            }

            if ($request->filled('priority') && $request->priority !== $ticket->priority) {
                $ticket->priority = $request->priority;
                $changes['priority'] = ['from' => $oldValues['priority'], 'to' => $request->priority];
            }

            if ($request->has('assigned_to') && $request->assigned_to != $ticket->assigned_to) {
                $ticket->assigned_to = $request->assigned_to;
                $changes['assigned_to'] = ['from' => $oldValues['assigned_to'], 'to' => $request->assigned_to];
            }

            if ($request->filled('category') && $request->category !== $ticket->category) {
                $ticket->category = $request->category;
                $changes['category'] = ['from' => $oldValues['category'], 'to' => $request->category];
            }

            $ticket->save();

            // Log changes as system message if there are any
            if (!empty($changes)) {
                $changeMessage = $this->formatChangeMessage($changes);
                TicketResponse::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id(),
                    'message' => $changeMessage,
                    'is_internal' => true
                ]);
            }

            \Log::info('Ticket updated', [
                'ticket_id' => $ticket->id,
                'changes' => $changes,
                'user_id' => auth()->id()
            ]);

            return back()->with('success', 'Ticket aggiornato con successo.');

        } catch (\Exception $e) {
            \Log::error('Error updating ticket', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Errore durante l\'aggiornamento del ticket.');
        }
    }

    /**
     * Close ticket with optional final message
     */
    public function close(Request $request, Ticket $ticket)
    {
        $validator = Validator::make($request->all(), [
            'final_message' => 'nullable|string|max:2000'
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        try {
            // Add final message if provided
            if ($request->filled('final_message')) {
                TicketResponse::create([
                    'ticket_id' => $ticket->id,
                    'user_id' => auth()->id(),
                    'message' => $request->final_message,
                    'is_internal' => false
                ]);
            }

            // Close ticket
            $ticket->update([
                'status' => 'closed',
                'closed_at' => now()
            ]);

            // Add system message
            TicketResponse::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => '🔒 Ticket chiuso dal Super Admin',
                'is_internal' => true
            ]);

            \Log::info('Ticket closed', [
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'had_final_message' => $request->filled('final_message')
            ]);

            return back()->with('success', 'Ticket chiuso con successo.');

        } catch (\Exception $e) {
            \Log::error('Error closing ticket', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Errore durante la chiusura del ticket.');
        }
    }

    /**
     * Reopen closed ticket
     */
    public function reopen(Ticket $ticket)
    {
        try {
            if ($ticket->status !== 'closed') {
                return back()->with('error', 'Solo i ticket chiusi possono essere riaperti.');
            }

            $ticket->update([
                'status' => 'open',
                'closed_at' => null
            ]);

            // Add system message
            TicketResponse::create([
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id(),
                'message' => '🔓 Ticket riaperto dal Super Admin',
                'is_internal' => true
            ]);

            \Log::info('Ticket reopened', [
                'ticket_id' => $ticket->id,
                'user_id' => auth()->id()
            ]);

            return back()->with('success', 'Ticket riaperto con successo.');

        } catch (\Exception $e) {
            \Log::error('Error reopening ticket', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Errore durante la riapertura del ticket.');
        }
    }

    /**
     * Delete ticket (soft delete or permanent based on age)
     */
    public function destroy(Ticket $ticket)
    {
        try {
            // Delete associated files
            if ($ticket->responses()->whereNotNull('attachments')->exists()) {
                $ticket->responses()->whereNotNull('attachments')->each(function($response) {
                    if ($response->attachments) {
                        foreach ($response->attachments as $attachment) {
                            Storage::disk('public')->delete($attachment);
                        }
                    }
                });
            }

            $ticketId = $ticket->id;
            $ticket->delete();

            \Log::info('Ticket deleted', [
                'ticket_id' => $ticketId,
                'user_id' => auth()->id()
            ]);

            return redirect()->route('super-admin.helpdesk.index')
                           ->with('success', 'Ticket eliminato con successo.');

        } catch (\Exception $e) {
            \Log::error('Error deleting ticket', [
                'ticket_id' => $ticket->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Errore durante l\'eliminazione del ticket.');
        }
    }

    /**
     * Export tickets data
     */
    public function export(Request $request, $format = 'csv')
    {
        try {
            // This would implement CSV/Excel export functionality
            // For now, return a simple implementation
            
            return back()->with('info', 'Funzionalità di export in sviluppo.');

        } catch (\Exception $e) {
            \Log::error('Error exporting tickets', [
                'format' => $format,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return back()->with('error', 'Errore durante l\'export dei ticket.');
        }
    }

    /**
     * Get ticket statistics for dashboard
     */
    private function getTicketStats()
    {
        return [
            'total' => Ticket::count(),
            'open' => Ticket::where('status', 'open')->count(),
            'pending' => Ticket::where('status', 'pending')->count(),
            'closed' => Ticket::where('status', 'closed')->count(),
            'high_priority' => Ticket::whereIn('priority', ['high', 'critical'])->count(),
            'overdue' => Ticket::where('status', 'open')
                              ->where('created_at', '<=', now()->subHours(48))
                              ->count(),
            'today' => Ticket::whereDate('created_at', today())->count(),
            'this_week' => Ticket::whereBetween('created_at', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])->count()
        ];
    }

    /**
     * Format change message for system logs
     */
    private function formatChangeMessage(array $changes)
    {
        $messages = [];
        
        foreach ($changes as $field => $change) {
            $fieldName = match($field) {
                'status' => 'Status',
                'priority' => 'Priorità',
                'assigned_to' => 'Assegnato a',
                'category' => 'Categoria',
                default => ucfirst($field)
            };
            
            $from = $change['from'] ?? 'Nessuno';
            $to = $change['to'] ?? 'Nessuno';
            
            if ($field === 'assigned_to') {
                $fromUser = $from ? User::find($from)?->name : 'Nessuno';
                $toUser = $to ? User::find($to)?->name : 'Nessuno';
                $messages[] = "{$fieldName}: {$fromUser} → {$toUser}";
            } else {
                $messages[] = "{$fieldName}: {$from} → {$to}";
            }
        }
        
        return '🔄 ' . implode(', ', $messages);
    }
}
