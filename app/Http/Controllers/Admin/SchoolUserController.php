<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\School;
use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SchoolUserController extends Controller
{
    /**
     * Mostra lista degli studenti/utenti della scuola dell'admin
     */
    public function index(Request $request)
    {
        $school = auth()->user()->school;
        
        if (!$school) {
            abort(403, 'Non hai accesso a una scuola specifica.');
        }

        // Query base per gli studenti della scuola
        $query = User::where('school_id', $school->id)
                    ->where('role', 'user') // Solo studenti
                    ->with(['courseEnrollments.course', 'payments', 'documents']);

        // Filtri di ricerca
        // SECURITY: Sanitize LIKE input to prevent SQL wildcard injection
        if ($request->filled('search')) {
            $search = \App\Helpers\QueryHelper::sanitizeLikeInput($request->search);
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('active', $request->status === 'active');
        }

        // Ordinamento
        $sortBy = $request->get('sort', 'created_at');
        $sortDir = $request->get('direction', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $students = $query->paginate(15)->appends($request->all());

        // Statistiche rapide
        $stats = [
            'total' => User::where('school_id', $school->id)->where('role', 'user')->count(),
            'active' => User::where('school_id', $school->id)->where('role', 'user')->where('active', true)->count(),
            'enrolled' => User::where('school_id', $school->id)
                             ->where('role', 'user')
                             ->whereHas('courseEnrollments', function($q) {
                                 $q->where('status', 'active');
                             })->count(),
        ];

        return view('admin.users.index', compact('students', 'stats', 'school'));
    }

    /**
     * Mostra dettaglio studente specifico
     */
    public function show(User $user)
    {
        $school = auth()->user()->school;
        
        // Verifica che lo studente appartenga alla scuola dell'admin
        if ($user->school_id !== $school->id || $user->role !== 'user') {
            abort(403, 'Non puoi accedere a questo studente.');
        }

        $user->load([
            'courseEnrollments.course',
            'payments.course',
            'documents'
        ]);

        // Statistiche studente
        $studentStats = [
            'total_courses' => $user->courseEnrollments()->count(),
            'active_courses' => $user->courseEnrollments()->where('status', 'active')->count(),
            'total_payments' => $user->payments()->where('status', 'completed')->sum('amount'),
            'pending_documents' => $user->documents()->where('status', 'pending')->count(),
        ];

        return view('admin.users.show', compact('user', 'studentStats'));
    }

    /**
     * Mostra form per modificare profilo studente
     */
    public function edit(User $user)
    {
        $school = auth()->user()->school;
        
        if ($user->school_id !== $school->id || $user->role !== 'user') {
            abort(403, 'Non puoi modificare questo studente.');
        }

        return view('admin.users.edit', compact('user'));
    }

    /**
     * Aggiorna profilo studente
     */
    public function update(UpdateProfileRequest $request, User $user)
    {
        $school = auth()->user()->school;
        
        if ($user->school_id !== $school->id || $user->role !== 'user') {
            abort(403, 'Non puoi modificare questo studente.');
        }

        $validated = $request->validated();
        
        // Rimuovi password se non fornita
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        return redirect()
            ->route('admin.users.show', $user)
            ->with('success', 'Profilo studente aggiornato con successo.');
    }

    /**
     * Attiva/Disattiva studente
     */
    public function toggleActive(User $user)
    {
        $school = auth()->user()->school;
        
        if ($user->school_id !== $school->id || $user->role !== 'user') {
            abort(403, 'Non puoi modificare questo studente.');
        }

        $user->update(['active' => !$user->active]);

        $status = $user->active ? 'attivato' : 'disattivato';
        
        return back()->with('success', "Studente {$status} con successo.");
    }

    /**
     * Azioni di massa sui studenti
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        $school = auth()->user()->school;
        $studentIds = $request->student_ids;
        
        // Verifica che tutti gli studenti appartengano alla scuola
        $students = User::whereIn('id', $studentIds)
                       ->where('school_id', $school->id)
                       ->where('role', 'user')
                       ->get();

        if ($students->count() !== count($studentIds)) {
            return back()->with('error', 'Alcuni studenti selezionati non sono validi.');
        }

        switch ($request->action) {
            case 'activate':
                User::whereIn('id', $studentIds)->update(['active' => true]);
                $message = 'Studenti attivati con successo.';
                break;
                
            case 'deactivate':
                User::whereIn('id', $studentIds)->update(['active' => false]);
                $message = 'Studenti disattivati con successo.';
                break;
                
            case 'delete':
                // Soft delete o controlli aggiuntivi se necessario
                User::whereIn('id', $studentIds)->delete();
                $message = 'Studenti eliminati con successo.';
                break;
        }

        return back()->with('success', $message);
    }

    /**
     * Esporta lista studenti in CSV
     */
    public function export(Request $request)
    {
        $school = auth()->user()->school;
        
        $students = User::where('school_id', $school->id)
                       ->where('role', 'user')
                       ->with(['courseEnrollments.course'])
                       ->get();

        $filename = "studenti_" . $school->name . "_" . now()->format('Y-m-d') . ".csv";
        
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nome', 'Cognome', 'Email', 'Telefono', 'Corsi Attivi', 'Data Registrazione', 'Status']);

            foreach ($students as $student) {
                $activeCourses = $student->courseEnrollments()
                                        ->where('status', 'active')
                                        ->with('course')
                                        ->get()
                                        ->pluck('course.name')
                                        ->implode(', ');
                                        
                fputcsv($file, [
                    $student->first_name,
                    $student->last_name,
                    $student->email,
                    $student->phone,
                    $activeCourses,
                    $student->created_at->format('d/m/Y'),
                    $student->active ? 'Attivo' : 'Inattivo'
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
