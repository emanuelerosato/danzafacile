<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEnrollmentRequest;
use App\Models\CourseEnrollment;
use App\Models\Course;
use App\Models\User;
use App\Models\Payment;
use Illuminate\Http\Request;

class EnrollmentController extends Controller
{
    /**
     * Display a listing of enrollments for the admin's school
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;

        $query = CourseEnrollment::with(['user', 'course'])
            ->whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            });

        // Search functionality
        // SECURITY: Sanitize LIKE input to prevent SQL wildcard injection
        if ($request->filled('search')) {
            $search = \App\Helpers\QueryHelper::sanitizeLikeInput($request->get('search'));
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($subq) use ($search) {
                    $subq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })->orWhereHas('course', function($subq) use ($search) {
                    $subq->where('name', 'like', "%{$search}%");
                });
            });
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->get('course_id'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by enrollment date
        if ($request->filled('date_from')) {
            $query->whereDate('enrollment_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('enrollment_date', '<=', $request->get('date_to'));
        }

        $enrollments = $query->orderBy('enrollment_date', 'desc')->paginate(15);

        // Get courses for filter dropdown
        $courses = Course::where('school_id', $school->id)
                        ->orderBy('name')
                        ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.enrollments.partials.table', compact('enrollments'))->render(),
                'pagination' => $enrollments->links()->render()
            ]);
        }

        return view('admin.enrollments.index', compact('enrollments', 'courses'));
    }

    /**
     * Show the form for creating a new enrollment
     */
    public function create()
    {
        $user = auth()->user();
        $school = $user->school;

        $courses = Course::where('school_id', $school->id)
                        ->where('active', true)
                        ->where('start_date', '>', now())
                        ->orderBy('name')
                        ->get();

        $students = User::where('school_id', $school->id)
                       ->where('role', User::ROLE_STUDENT)
                       ->where('active', true)
                       ->orderBy('name')
                       ->get();

        return view('admin.enrollments.create', compact('courses', 'students'));
    }

    /**
     * Store a newly created enrollment
     */
    public function store(StoreEnrollmentRequest $request)
    {
        $data = $request->validated();
        $data['enrollment_date'] = $data['enrollment_date'] ?? now();
        $data['status'] = 'active';

        // Check if course belongs to admin's school
        $course = Course::findOrFail($data['course_id']);
        if ($course->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        // Check if user belongs to admin's school
        $student = User::findOrFail($data['user_id']);
        if ($student->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        // Check if enrollment already exists
        $existingEnrollment = CourseEnrollment::where('user_id', $data['user_id'])
                                             ->where('course_id', $data['course_id'])
                                             ->first();

        if ($existingEnrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Lo studente è già iscritto a questo corso.'
            ], 422);
        }

        // Check course capacity
        $currentEnrollments = CourseEnrollment::where('course_id', $data['course_id'])
                                             ->where('status', 'active')
                                             ->count();

        if ($currentEnrollments >= $course->max_students) {
            return response()->json([
                'success' => false,
                'message' => 'Il corso ha raggiunto il numero massimo di studenti.'
            ], 422);
        }

        $enrollment = CourseEnrollment::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Iscrizione creata con successo.',
                'enrollment' => $enrollment->load(['user', 'course'])
            ]);
        }

        return redirect()->route('admin.enrollments.index')
                        ->with('success', 'Iscrizione creata con successo.');
    }

    /**
     * Display the specified enrollment
     */
    public function show(CourseEnrollment $enrollment)
    {
        // Check authorization
        if ($enrollment->course->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        // Carica le relazioni principali
        $enrollment->load(['user', 'course']);

        // Carica i pagamenti manualmente per evitare errori di relazione
        try {
            $enrollment->load(['payments']);
        } catch (\Exception $e) {
            // Se la relazione payments non funziona, caricala manualmente
            $enrollment->payments = Payment::where('course_id', $enrollment->course_id)
                                          ->where('user_id', $enrollment->user_id)
                                          ->where('payment_type', Payment::TYPE_COURSE_ENROLLMENT)
                                          ->get();
        }

        return view('admin.enrollments.show', compact('enrollment'));
    }

    /**
     * Show the form for editing the specified enrollment
     */
    public function edit(CourseEnrollment $enrollment)
    {
        // Check authorization
        if ($enrollment->course->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $user = auth()->user();
        $school = $user->school;

        $courses = Course::where('school_id', $school->id)
                        ->where('active', true)
                        ->orderBy('name')
                        ->get();

        $students = User::where('school_id', $school->id)
                       ->where('role', User::ROLE_STUDENT)
                       ->where('active', true)
                       ->orderBy('name')
                       ->get();

        return view('admin.enrollments.edit', compact('enrollment', 'courses', 'students'));
    }

    /**
     * Update the specified enrollment
     */
    public function update(Request $request, CourseEnrollment $enrollment)
    {
        // Check authorization
        if ($enrollment->course->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'enrollment_date' => 'nullable|date',
            'status' => 'required|in:active,cancelled,completed,suspended',
            'notes' => 'nullable|string|max:500',
        ]);

        $enrollment->update($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Iscrizione aggiornata con successo.',
                'enrollment' => $enrollment->fresh()->load(['user', 'course'])
            ]);
        }

        return redirect()->route('admin.enrollments.show', $enrollment)
                        ->with('success', 'Iscrizione aggiornata con successo.');
    }

    /**
     * Remove the specified enrollment
     */
    public function destroy(CourseEnrollment $enrollment)
    {
        // Check authorization
        if ($enrollment->course->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $enrollment->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Iscrizione eliminata con successo.'
            ]);
        }

        return redirect()->route('admin.enrollments.index')
                        ->with('success', 'Iscrizione eliminata con successo.');
    }

    /**
     * Cancel enrollment
     */
    public function cancel(CourseEnrollment $enrollment)
    {
        // Check authorization
        if ($enrollment->course->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $enrollment->update(['status' => 'cancelled']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Iscrizione cancellata con successo.'
            ]);
        }

        return redirect()->back()->with('success', 'Iscrizione cancellata con successo.');
    }

    /**
     * Reactivate enrollment
     */
    public function reactivate(CourseEnrollment $enrollment)
    {
        // Check authorization
        if ($enrollment->course->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        // Check course capacity
        $currentEnrollments = CourseEnrollment::where('course_id', $enrollment->course_id)
                                             ->where('status', 'active')
                                             ->count();

        if ($currentEnrollments >= $enrollment->course->max_students) {
            return response()->json([
                'success' => false,
                'message' => 'Il corso ha raggiunto il numero massimo di studenti.'
            ], 422);
        }

        $enrollment->update(['status' => 'active']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Iscrizione riattivata con successo.'
            ]);
        }

        return redirect()->back()->with('success', 'Iscrizione riattivata con successo.');
    }

    /**
     * Bulk actions for multiple enrollments
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:cancel,reactivate,delete',
            'enrollment_ids' => 'required|array',
            'enrollment_ids.*' => 'exists:course_enrollments,id'
        ]);

        $user = auth()->user();
        $enrollmentIds = $request->get('enrollment_ids');
        $action = $request->get('action');

        // Ensure enrollments belong to admin's school
        $enrollments = CourseEnrollment::whereIn('id', $enrollmentIds)
                                      ->whereHas('course', function($q) use ($user) {
                                          $q->where('school_id', $user->school_id);
                                      })
                                      ->get();

        switch ($action) {
            case 'cancel':
                $enrollments->each(function($enrollment) {
                    $enrollment->update(['status' => 'cancelled']);
                });
                $message = 'Iscrizioni cancellate con successo.';
                break;

            case 'reactivate':
                $enrollments->each(function($enrollment) {
                    $currentEnrollments = CourseEnrollment::where('course_id', $enrollment->course_id)
                                                         ->where('status', 'active')
                                                         ->count();
                    if ($currentEnrollments < $enrollment->course->max_students) {
                        $enrollment->update(['status' => 'active']);
                    }
                });
                $message = 'Iscrizioni riattivate con successo (dove possibile).';
                break;

            case 'delete':
                $enrollments->each(function($enrollment) {
                    $enrollment->delete();
                });
                $message = 'Iscrizioni eliminate con successo.';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Get enrollment statistics
     */
    public function getStatistics(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;

        $stats = [
            'total_enrollments' => CourseEnrollment::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count(),
            
            'active_enrollments' => CourseEnrollment::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'active')->count(),
            
            'this_month_enrollments' => CourseEnrollment::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->whereMonth('enrollment_date', now()->month)->count(),
            
            'enrollments_by_course' => CourseEnrollment::with('course')
                ->whereHas('course', function($q) use ($school) {
                    $q->where('school_id', $school->id);
                })
                ->selectRaw('course_id, count(*) as count')
                ->groupBy('course_id')
                ->get()
                ->mapWithKeys(function($item) {
                    return [$item->course->name => $item->count];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Export enrollments to CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;

        $query = CourseEnrollment::with(['user', 'course'])
            ->whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            });

        // Apply same filters as index
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->get('course_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $enrollments = $query->get();

        $filename = 'enrollments_' . $school->name . '_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($enrollments) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'ID', 'Studente', 'Email Studente', 'Corso', 'Data Iscrizione', 
                'Stato', 'Note', 'Data Creazione'
            ]);

            foreach ($enrollments as $enrollment) {
                fputcsv($file, [
                    $enrollment->id,
                    $enrollment->user->full_name,
                    $enrollment->user->email,
                    $enrollment->course->name,
                    $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : '',
                    ucfirst($enrollment->status),
                    $enrollment->notes,
                    $enrollment->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}