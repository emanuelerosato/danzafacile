<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SuperAdminUserController extends Controller
{
    /**
     * Display a listing of all users with advanced filtering
     */
    public function index(Request $request)
    {
        $query = User::with('school');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Filter by school
        if ($request->filled('school_id')) {
            $query->where('school_id', $request->get('school_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status') === 'active';
            $query->where('active', $status);
        }

        $users = $query->orderBy('name')->paginate(20);
        $schools = School::orderBy('name')->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('super-admin.users.partials.table', compact('users'))->render(),
                'pagination' => $users->links()->render()
            ]);
        }

        return view('super-admin.users.index', compact('users', 'schools'));
    }

    /**
     * Show the form for creating a new admin user
     */
    public function create()
    {
        $schools = School::where('active', true)->orderBy('name')->get();
        return view('super-admin.users.create', compact('schools'));
    }

    /**
     * Store a newly created admin user
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'school_id' => 'nullable|exists:schools,id',
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_INSTRUCTOR, User::ROLE_STUDENT])],
            'active' => 'boolean',
        ]);

        $user = User::create([
            'name' => $request->name,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'school_id' => $request->school_id,
            'role' => $request->role,
            'active' => $request->boolean('active', true),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Utente creato con successo.',
                'user' => $user->load('school')
            ]);
        }

        return redirect()->route('super-admin.users.index')
                        ->with('success', 'Utente creato con successo.');
    }

    /**
     * Display the specified user with detailed information
     */
    public function show(User $user)
    {
        $user->load([
            'school',
            'courseEnrollments.course',
            'payments' => function($q) {
                $q->latest()->take(10);
            },
            'documents' => function($q) {
                $q->latest()->take(10);
            }
        ]);

        $stats = [
            'total_enrollments' => $user->courseEnrollments()->count(),
            'active_enrollments' => $user->courseEnrollments()->whereHas('course', function($q) {
                $q->where('active', true);
            })->count(),
            'total_payments' => $user->payments()->where('status', 'completed')->sum('amount'),
            'pending_payments' => $user->payments()->where('status', 'pending')->sum('amount'),
            'documents_count' => $user->documents()->count(),
            'pending_documents' => $user->documents()->where('status', 'pending')->count(),
        ];

        return view('super-admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        $schools = School::where('active', true)->orderBy('name')->get();
        return view('super-admin.users.edit', compact('user', 'schools'));
    }

    /**
     * Update the specified user
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'school_id' => 'nullable|exists:schools,id',
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_INSTRUCTOR, User::ROLE_STUDENT])],
            'active' => 'boolean',
        ]);

        $data = $request->except(['password', 'password_confirmation']);
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Utente aggiornato con successo.',
                'user' => $user->fresh()->load('school')
            ]);
        }

        return redirect()->route('super-admin.users.show', $user)
                        ->with('success', 'Utente aggiornato con successo.');
    }

    /**
     * Remove the specified user
     */
    public function destroy(User $user)
    {
        // Check if user has enrollments or payments
        if ($user->courseEnrollments()->count() > 0 || $user->payments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile eliminare l\'utente. Ha iscrizioni o pagamenti associati.'
            ], 422);
        }

        $user->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Utente eliminato con successo.'
            ]);
        }

        return redirect()->route('super-admin.users.index')
                        ->with('success', 'Utente eliminato con successo.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(User $user)
    {
        $user->update(['active' => !$user->active]);

        $status = $user->active ? 'attivato' : 'disattivato';
        $message = "Utente {$status} con successo.";

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $user->active
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk actions for multiple users
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete,change_school',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'school_id' => 'required_if:action,change_school|nullable|exists:schools,id'
        ]);

        $userIds = $request->get('user_ids');
        $action = $request->get('action');

        switch ($action) {
            case 'activate':
                User::whereIn('id', $userIds)->update(['active' => true]);
                $message = 'Utenti attivati con successo.';
                break;

            case 'deactivate':
                User::whereIn('id', $userIds)->update(['active' => false]);
                $message = 'Utenti disattivati con successo.';
                break;

            case 'change_school':
                User::whereIn('id', $userIds)->update(['school_id' => $request->get('school_id')]);
                $message = 'Scuola assegnata agli utenti selezionati.';
                break;

            case 'delete':
                $users = User::whereIn('id', $userIds)->get();
                foreach ($users as $user) {
                    if ($user->courseEnrollments()->count() === 0 && $user->payments()->count() === 0) {
                        $user->delete();
                    }
                }
                $message = 'Utenti eliminati con successo (quelli senza iscrizioni o pagamenti).';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Impersonate user (login as user)
     */
    public function impersonate(User $user)
    {
        if ($user->isSuperAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Non puoi impersonare un Super Amministratore.'
            ], 403);
        }

        session(['impersonating' => $user->id, 'original_user' => auth()->id()]);
        auth()->login($user);

        return redirect()->route('dashboard')
                        ->with('info', "Stai impersonando {$user->full_name}. Clicca qui per tornare al tuo account.");
    }

    /**
     * Stop impersonating and return to original user
     */
    public function stopImpersonating()
    {
        if (!session()->has('impersonating')) {
            return redirect()->route('super-admin.dashboard');
        }

        $originalUserId = session('original_user');
        $originalUser = User::find($originalUserId);

        session()->forget(['impersonating', 'original_user']);
        auth()->login($originalUser);

        return redirect()->route('super-admin.dashboard')
                        ->with('success', 'Impersonazione terminata.');
    }

    /**
     * Export users data to CSV
     */
    public function export(Request $request)
    {
        $query = User::with('school');

        // Apply same filters as index
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        if ($request->filled('school_id')) {
            $query->where('school_id', $request->get('school_id'));
        }

        $users = $query->get();

        $filename = 'users_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'ID', 'Nome', 'Nome', 'Cognome', 'Email', 'Telefono', 
                'Ruolo', 'Scuola', 'Attivo', 'Data Nascita', 'Data Registrazione'
            ]);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->first_name,
                    $user->last_name,
                    $user->email,
                    $user->phone,
                    ucfirst($user->role),
                    $user->school ? $user->school->name : 'N/A',
                    $user->active ? 'Sì' : 'No',
                    $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'N/A',
                    $user->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}