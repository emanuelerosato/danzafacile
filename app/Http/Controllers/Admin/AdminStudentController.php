<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\Document;
use App\Helpers\QueryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminStudentController extends AdminBaseController
{
    /**
     * Display a listing of students for the current school
     */
    public function index(Request $request)
    {
        $this->setupContext();

        $query = $this->school->users()->where('role', 'student');

        // SECURE: allowed sort fields for students
        $allowedSortFields = ['name', 'first_name', 'last_name', 'email', 'created_at', 'updated_at'];
        $students = $this->getFilteredResults($query, $request, 15, $allowedSortFields);

        // Get filter options
        $enrollmentStatuses = ['active', 'completed', 'cancelled', 'pending'];

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Students retrieved successfully', [
                'html' => view('admin.students.partials.table', compact('students'))->render(),
                'pagination' => $students->links()->render()
            ]);
        }

        // Quick stats for header cards
        $stats = [
            'total_students' => $this->school->users()->where('role', 'student')->count(),
            'active_students' => $this->school->users()->where('role', 'student')->where('active', true)->count(),
            'new_this_month' => $this->school->users()->where('role', 'student')
                ->whereMonth('created_at', now()->month)->count(),
            'with_enrollments' => $this->school->users()->where('role', 'student')
                ->whereHas('enrollments')->count()
        ];

        return view('admin.students.index', compact('students', 'stats', 'enrollmentStatuses'));
    }

    /**
     * Show the form for creating a new student
     */
    public function create()
    {
        $this->setupContext();

        return view('admin.students.create');
    }

    /**
     * Store a newly created student
     */
    public function store(Request $request)
    {
        $this->setupContext();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('school_id', $this->school->id);
                })
            ],
            'phone' => 'nullable|string|max:20',
            'codice_fiscale' => [
                'required',
                'string',
                'size:16',
                'regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/',
                Rule::unique('users')->where(function ($query) {
                    return $query->where('school_id', $this->school->id);
                })
            ],
            'date_of_birth' => 'required|date|before:today',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:500',
            'medical_notes' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'send_welcome_email' => 'boolean',
            'is_minor' => 'boolean',  // SENIOR FIX: Task #4
        ]);

        // SENIOR FIX: Task #4 - Validazione condizionale campi genitore per minorenni
        if ($request->boolean('is_minor')) {
            $guardianValidation = $request->validate([
                'guardian_first_name' => 'required|string|max:255',
                'guardian_last_name' => 'required|string|max:255',
                'guardian_fiscal_code' => [
                    'required',
                    'string',
                    'size:16',
                    'regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/',
                ],
                'guardian_email' => [
                    'required',
                    'email',
                    'max:255',
                ],
                'guardian_phone' => 'required|string|max:20',
            ], [
                'guardian_first_name.required' => 'Il nome del genitore/tutore è obbligatorio per i minorenni.',
                'guardian_last_name.required' => 'Il cognome del genitore/tutore è obbligatorio per i minorenni.',
                'guardian_fiscal_code.required' => 'Il codice fiscale del genitore/tutore è obbligatorio per i minorenni.',
                'guardian_fiscal_code.regex' => 'Il codice fiscale del genitore/tutore non è valido.',
                'guardian_email.required' => 'L\'email del genitore/tutore è obbligatoria per i minorenni.',
                'guardian_email.email' => 'L\'email del genitore/tutore non è valida.',
                'guardian_phone.required' => 'Il telefono del genitore/tutore è obbligatorio per i minorenni.',
            ]);

            $validated = array_merge($validated, $guardianValidation);
        }

        // Generate password
        $password = $this->generateStudentPassword();

        $validated['password'] = Hash::make($password);
        $validated['role'] = 'student';
        $validated['school_id'] = $this->school->id;
        $validated['email_verified_at'] = now();
        $validated['active'] = $validated['active'] ?? true;
        $validated['is_minor'] = $validated['is_minor'] ?? false;  // SENIOR FIX: Task #4

        $student = User::create($validated);

        // Send welcome email if requested
        if ($validated['send_welcome_email'] ?? false) {
            // TODO: Implement welcome email notification
        }

        $this->clearSchoolCache();

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Studente creato con successo.', [
                'student' => $student,
                'password' => $password
            ]);
        }

        return redirect()->route('admin.students.show', $student)
                        ->with('success', 'Studente creato con successo.')
                        ->with('student_password', $password);
    }

    /**
     * Display the specified student with detailed information
     */
    public function show(User $student)
    {
        $this->setupContext();
        $this->verifyResourceOwnership($student, 'Studente');

        // Ensure student belongs to current school
        if ($student->school_id !== $this->school->id || $student->role !== 'student') {
            abort(404, 'Studente non trovato.');
        }

        // Load relationships
        $student->load([
            'enrollments.course',
            'payments' => function($query) {
                $query->orderBy('payment_date', 'desc');
            },
            'documents' => function($query) {
                $query->orderBy('uploaded_at', 'desc');
            },
            'attendance' => function($query) {
                $query->with('attendable')->orderBy('date', 'desc')->limit(20);
            }
        ]);

        // Calculate student stats
        $stats = [
            'total_courses' => $student->enrollments->count(),
            'active_courses' => $student->enrollments()->whereIn('status', ['active', 'enrolled'])->count(),
            'total_payments' => $student->payments->sum('amount'),
            'pending_payments' => $student->payments()->where('status', 'pending')->sum('amount'),
            'attendance_rate' => $this->calculateAttendanceRate($student),
            'documents_status' => $this->getDocumentsStatus($student)
        ];

        return view('admin.students.show', compact('student', 'stats'));
    }

    /**
     * Show the form for editing the specified student
     */
    public function edit(User $student)
    {
        $this->setupContext();
        $this->verifyResourceOwnership($student, 'Studente');

        // Ensure student belongs to current school
        if ($student->school_id !== $this->school->id || $student->role !== 'student') {
            abort(404, 'Studente non trovato.');
        }

        // Load enrollments with course relationship for display
        $student->load(['enrollments' => function($query) {
            $query->with('course')->latest('enrollment_date');
        }]);

        return view('admin.students.edit', compact('student'));
    }

    /**
     * Update the specified student
     */
    public function update(Request $request, User $student)
    {
        $this->setupContext();
        $this->verifyResourceOwnership($student, 'Studente');

        // Ensure student belongs to current school
        if ($student->school_id !== $this->school->id || $student->role !== 'student') {
            abort(404, 'Studente non trovato.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($student->id)->where(function ($query) {
                    return $query->where('school_id', $this->school->id);
                })
            ],
            'phone' => 'nullable|string|max:20',
            'codice_fiscale' => [
                'required',
                'string',
                'size:16',
                'regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/',
                Rule::unique('users')->ignore($student->id)->where(function ($query) {
                    return $query->where('school_id', $this->school->id);
                })
            ],
            'date_of_birth' => 'required|date|before:today',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:500',
            'medical_notes' => 'nullable|string|max:1000',
            'active' => 'boolean',
            'is_minor' => 'boolean',  // SENIOR FIX: Task #4
        ]);

        // SENIOR FIX: Task #4 - Validazione condizionale campi genitore per minorenni
        if ($request->boolean('is_minor')) {
            $guardianValidation = $request->validate([
                'guardian_first_name' => 'required|string|max:255',
                'guardian_last_name' => 'required|string|max:255',
                'guardian_fiscal_code' => [
                    'required',
                    'string',
                    'size:16',
                    'regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/',
                ],
                'guardian_email' => [
                    'required',
                    'email',
                    'max:255',
                ],
                'guardian_phone' => 'required|string|max:20',
            ], [
                'guardian_first_name.required' => 'Il nome del genitore/tutore è obbligatorio per i minorenni.',
                'guardian_last_name.required' => 'Il cognome del genitore/tutore è obbligatorio per i minorenni.',
                'guardian_fiscal_code.required' => 'Il codice fiscale del genitore/tutore è obbligatorio per i minorenni.',
                'guardian_fiscal_code.regex' => 'Il codice fiscale del genitore/tutore non è valido.',
                'guardian_email.required' => 'L\'email del genitore/tutore è obbligatoria per i minorenni.',
                'guardian_email.email' => 'L\'email del genitore/tutore non è valida.',
                'guardian_phone.required' => 'Il telefono del genitore/tutore è obbligatorio per i minorenni.',
            ]);

            $validated = array_merge($validated, $guardianValidation);
        } else {
            // Se non è più minore, azzera i campi del genitore
            $validated['guardian_first_name'] = null;
            $validated['guardian_last_name'] = null;
            $validated['guardian_fiscal_code'] = null;
            $validated['guardian_email'] = null;
            $validated['guardian_phone'] = null;
        }

        $validated['is_minor'] = $validated['is_minor'] ?? false;  // SENIOR FIX: Task #4

        $student->update($validated);
        $this->clearSchoolCache();

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Studente aggiornato con successo.', [
                'student' => $student->fresh()
            ]);
        }

        return redirect()->route('admin.students.show', $student)
                        ->with('success', 'Studente aggiornato con successo.');
    }

    /**
     * Remove the specified student from storage
     */
    public function destroy(User $student)
    {
        // Ensure student belongs to current school
        if ($student->school_id !== $this->school->id || $student->role !== 'student') {
            abort(404, 'Studente non trovato.');
        }

        // Check if student has active enrollments
        $activeEnrollments = $student->enrollments()
            ->whereIn('status', ['active', 'enrolled'])
            ->count();

        if ($activeEnrollments > 0) {
            return $this->jsonResponse(false, 'Impossibile eliminare lo studente. Ha iscrizioni attive.', [], 422);
        }

        // Soft delete or hard delete based on business rules
        $studentName = $student->name;
        $student->delete();

        $this->clearSchoolCache();

        if (request()->ajax()) {
            return $this->jsonResponse(true, "Studente {$studentName} eliminato con successo.");
        }

        return redirect()->route('admin.students.index')
                        ->with('success', "Studente {$studentName} eliminato con successo.");
    }

    /**
     * Toggle student active status
     */
    public function toggleActive(User $student)
    {
        // Ensure student belongs to current school
        if ($student->school_id !== $this->school->id || $student->role !== 'student') {
            abort(404, 'Studente non trovato.');
        }

        $student->update(['active' => !$student->active]);
        $this->clearSchoolCache();

        $status = $student->active ? 'attivato' : 'disattivato';
        $message = "Studente {$status} con successo.";

        if (request()->ajax()) {
            return $this->jsonResponse(true, $message, [
                'status' => $student->active
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Bulk actions for multiple students
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete,export',
            'student_ids' => 'required|array',
            'student_ids.*' => 'integer|exists:users,id'
        ]);

        $studentIds = $request->get('student_ids');

        // Ensure all students belong to current school
        $students = $this->school->users()
            ->where('role', 'student')
            ->whereIn('id', $studentIds)
            ->get();

        if ($students->count() !== count($studentIds)) {
            return $this->jsonResponse(false, 'Alcuni studenti non appartengono alla tua scuola.', [], 403);
        }

        $action = $request->get('action');

        try {
            switch ($action) {
                case 'activate':
                    User::whereIn('id', $studentIds)->update(['active' => true]);
                    $message = 'Studenti attivati con successo.';
                    break;

                case 'deactivate':
                    User::whereIn('id', $studentIds)->update(['active' => false]);
                    $message = 'Studenti disattivati con successo.';
                    break;

                case 'delete':
                    // Check for active enrollments
                    $activeCount = CourseEnrollment::whereIn('user_id', $studentIds)
                        ->whereIn('status', ['active', 'enrolled'])
                        ->count();

                    if ($activeCount > 0) {
                        return $this->jsonResponse(false, 'Alcuni studenti hanno iscrizioni attive e non possono essere eliminati.', [], 422);
                    }

                    User::whereIn('id', $studentIds)->delete();
                    $message = 'Studenti eliminati con successo.';
                    break;

                case 'export':
                    return $this->exportStudents($students);

                default:
                    return $this->jsonResponse(false, 'Azione non supportata.', [], 400);
            }

            $this->clearSchoolCache();
            return $this->jsonResponse(true, $message);

        } catch (\Exception $e) {
            \Log::error('Student bulk action failed: ' . $e->getMessage());
            return $this->jsonResponse(false, 'Errore durante l\'operazione.', [], 500);
        }
    }

    /**
     * Export students data to CSV
     */
    public function export()
    {
        $students = $this->school->users()
            ->where('role', 'student')
            ->with(['enrollments.course', 'payments'])
            ->orderBy('name')
            ->get();

        return $this->exportStudents($students);
    }

    /**
     * Apply search to student query
     */
    protected function applySearch($query, string $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('first_name', 'like', "%{$searchTerm}%")
              ->orWhere('last_name', 'like', "%{$searchTerm}%")
              ->orWhere('email', 'like', "%{$searchTerm}%")
              ->orWhere('phone', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Generate a secure password for new students
     *
     * SECURITY: Generates strong random password with:
     * - 2 random words (capitalized)
     * - 4 random digits
     * - 1 random special character
     * Format: WordWord1234!
     *
     * @return string Strong password (e.g., "QuickLion5847!")
     */
    private function generateStudentPassword(): string
    {
        // Word lists for memorable yet secure passwords
        $words = [
            'Quick', 'Brave', 'Swift', 'Bright', 'Clever', 'Bold', 'Smart', 'Wise',
            'Strong', 'Mighty', 'Noble', 'Proud', 'Sharp', 'Keen', 'Fierce', 'Loyal',
            'Lion', 'Tiger', 'Eagle', 'Wolf', 'Bear', 'Hawk', 'Fox', 'Owl',
            'Dragon', 'Phoenix', 'Falcon', 'Panther', 'Leopard', 'Cheetah', 'Cobra', 'Shark'
        ];

        $specialChars = ['!', '@', '#', '$', '%', '&', '*'];

        // Generate password components
        $word1 = $words[array_rand($words)];
        $word2 = $words[array_rand($words)];
        $numbers = str_pad(random_int(1000, 9999), 4, '0', STR_PAD_LEFT);
        $special = $specialChars[array_rand($specialChars)];

        // Combine: WordWord1234!
        return $word1 . $word2 . $numbers . $special;
    }

    /**
     * Calculate student attendance rate
     */
    private function calculateAttendanceRate(User $student): float
    {
        $totalAttendance = $student->attendance()->count();

        if ($totalAttendance === 0) {
            return 0;
        }

        $presentCount = $student->attendance()
            ->where('status', 'present')
            ->count();

        return round(($presentCount / $totalAttendance) * 100, 1);
    }

    /**
     * Get documents status summary
     */
    private function getDocumentsStatus(User $student): array
    {
        $documents = $student->documents;

        return [
            'total' => $documents->count(),
            'approved' => $documents->where('status', 'approved')->count(),
            'pending' => $documents->where('status', 'pending')->count(),
            'rejected' => $documents->where('status', 'rejected')->count()
        ];
    }

    /**
     * Export students collection to CSV
     */
    private function exportStudents($students)
    {
        $data = $students->map(function ($student) {
            return [
                $student->id,
                $student->name,
                $student->first_name,
                $student->last_name,
                $student->email,
                $student->phone,
                $student->date_of_birth ? $student->date_of_birth->format('d/m/Y') : '',
                $student->address,
                $student->active ? 'Attivo' : 'Non attivo',
                $student->enrollments->count(),
                $student->payments->sum('amount'),
                $student->created_at->format('d/m/Y H:i')
            ];
        })->toArray();

        $headers = [
            'ID', 'Nome Completo', 'Nome', 'Cognome', 'Email', 'Telefono',
            'Data Nascita', 'Indirizzo', 'Stato', 'Iscrizioni', 'Pagamenti Tot', 'Registrato il'
        ];

        $filename = 'studenti_' . str_replace(' ', '_', $this->school->name) . '_' . now()->format('Y-m-d') . '.csv';

        return $this->exportToCsv($data, $headers, $filename);
    }
}