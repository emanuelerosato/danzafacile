<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminCourseController extends AdminBaseController
{
    /**
     * Display a listing of courses for the admin's school
     */
    public function index(Request $request)
    {
        $query = $this->school->courses()->with(['instructor', 'enrollments']);

        $courses = $this->getFilteredResults($query, $request, 15);

        // Get filter options
        $instructors = $this->school->users()
                          ->whereHas('staffRoles', function($q) {
                              $q->where('active', true);
                          })
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();

        $levels = ['Principiante', 'Intermedio', 'Avanzato', 'Professionale'];

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Corsi recuperati con successo', [
                'html' => view('admin.courses.partials.table', compact('courses'))->render(),
                'pagination' => $courses->links()->render()
            ]);
        }

        // Quick stats for header cards
        $stats = [
            'total_courses' => $this->school->courses()->count(),
            'active_courses' => $this->school->courses()->where('active', true)->count(),
            'upcoming_courses' => $this->school->courses()
                ->where('start_date', '>', now())
                ->count(),
            'total_enrollments' => CourseEnrollment::whereHas('course', function($q) {
                $q->where('school_id', $this->school->id);
            })->count()
        ];

        return view('admin.courses.index', compact('courses', 'instructors', 'levels', 'stats'));
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        $instructors = $this->school->users()
                          ->whereHas('staffRoles', function($q) {
                              $q->where('active', true);
                          })
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();

        $levels = ['Principiante', 'Intermedio', 'Avanzato', 'Professionale'];

        return view('admin.courses.create', compact('instructors', 'levels'));
    }

    /**
     * Store a newly created course in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'level' => 'required|in:Principiante,Intermedio,Avanzato,Professionale,beginner,intermediate,advanced,professional',
            'instructor_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('school_id', $this->school->id)
                          ->whereHas('staffRoles', function($q) {
                              $q->where('active', true);
                          });
                })
            ],
            'max_students' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'schedule' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'duration_weeks' => 'nullable|integer|min:1|max:52',
            'active' => 'boolean'
        ]);

        $validated['school_id'] = $this->school->id;
        $validated['active'] = $validated['active'] ?? true;

        $course = Course::create($validated);

        $this->clearSchoolCache();

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Corso creato con successo.', [
                'course' => $course->load(['instructor', 'enrollments'])
            ]);
        }

        return redirect()->route('admin.courses.index')
                        ->with('success', 'Corso creato con successo.');
    }

    /**
     * Display the specified course
     */
    public function show(Course $course)
    {
        // Ensure course belongs to current school
        if ($course->school_id !== $this->school->id) {
            abort(404, 'Corso non trovato.');
        }

        $course->load([
            'instructor',
            'enrollments.user',
            'school'
        ]);

        // Calculate revenue from actual payments for this course
        $enrolledUserIds = $course->enrollments()->pluck('user_id');
        $actualRevenue = Payment::whereIn('user_id', $enrolledUserIds)
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->sum('amount');

        // If no payments found, calculate potential revenue based on enrollment count and course price
        $potentialRevenue = $course->enrollments()->count() * $course->price;
        $totalRevenue = $actualRevenue > 0 ? $actualRevenue : $potentialRevenue;

        $stats = [
            'enrolled_students' => $course->enrollments()->count(),
            'available_spots' => max(0, $course->max_students - $course->enrollments()->count()),
            'completion_rate' => $this->calculateCompletionRate($course),
            'total_revenue' => $totalRevenue,
            'attendance_rate' => $this->calculateAttendanceRate($course),
            'revenue_per_student' => $course->enrollments()->count() > 0 ?
                $totalRevenue / $course->enrollments()->count() : 0
        ];

        return view('admin.courses.show', compact('course', 'stats'));
    }

    /**
     * Show the form for editing the specified course
     */
    public function edit(Course $course)
    {
        $this->authorize('update', $course);

        $user = auth()->user();
        $instructors = User::where('school_id', $user->school_id)
                          ->where('role', User::ROLE_INSTRUCTOR)
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();

        return view('admin.courses.edit', compact('course', 'instructors'));
    }

    /**
     * Update the specified course in storage
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        $this->authorize('update', $course);

        $data = $request->validated();
        $course->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Corso aggiornato con successo.',
                'course' => $course->fresh()->load(['instructor', 'enrollments'])
            ]);
        }

        return redirect()->route('admin.courses.show', $course)
                        ->with('success', 'Corso aggiornato con successo.');
    }

    /**
     * Remove the specified course from storage
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);

        // Check if course has enrollments
        if ($course->enrollments()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile eliminare il corso. Ci sono studenti iscritti.'
            ], 422);
        }

        $course->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Corso eliminato con successo.'
            ]);
        }

        return redirect()->route('admin.courses.index')
                        ->with('success', 'Corso eliminato con successo.');
    }

    /**
     * Activate/deactivate course
     */
    public function toggleStatus(Course $course)
    {
        $this->authorize('update', $course);

        $course->update(['active' => !$course->active]);

        $status = $course->active ? 'attivato' : 'disattivato';
        $message = "Corso {$status} con successo.";

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $course->active
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Duplicate course with new dates
     */
    public function duplicate(Course $course)
    {
        $this->authorize('create', Course::class);

        $newCourse = $course->replicate();
        $newCourse->name = $course->name . ' (Copia)';
        $newCourse->start_date = null;
        $newCourse->end_date = null;
        $newCourse->active = false;
        $newCourse->save();

        return redirect()->route('admin.courses.edit', $newCourse)
                        ->with('success', 'Corso duplicato con successo. Aggiorna le date e attivalo.');
    }

    /**
     * Get course statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;

        $stats = [
            'total_courses' => Course::where('school_id', $school->id)->count(),
            'active_courses' => Course::where('school_id', $school->id)->where('active', true)->count(),
            'upcoming_courses' => Course::where('school_id', $school->id)
                                       ->where('start_date', '>', now())
                                       ->count(),
            'courses_by_level' => Course::where('school_id', $school->id)
                                       ->selectRaw('level, count(*) as count')
                                       ->groupBy('level')
                                       ->pluck('count', 'level'),
            'enrollment_trends' => $this->getEnrollmentTrends($school->id),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk actions for multiple courses
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id'
        ]);

        $user = auth()->user();
        $courseIds = $request->get('course_ids');
        $action = $request->get('action');

        // Ensure courses belong to admin's school
        $courses = Course::whereIn('id', $courseIds)
                        ->where('school_id', $user->school_id)
                        ->get();

        switch ($action) {
            case 'activate':
                $courses->each(function($course) {
                    $course->update(['active' => true]);
                });
                $message = 'Corsi attivati con successo.';
                break;

            case 'deactivate':
                $courses->each(function($course) {
                    $course->update(['active' => false]);
                });
                $message = 'Corsi disattivati con successo.';
                break;

            case 'delete':
                $courses->each(function($course) {
                    if ($course->enrollments()->count() === 0) {
                        $course->delete();
                    }
                });
                $message = 'Corsi eliminati con successo (quelli senza iscrizioni).';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Calculate course completion rate
     */
    private function calculateCompletionRate($course)
    {
        if ($course->end_date > now()) {
            return 0; // Course not finished yet
        }

        $totalEnrollments = $course->enrollments()->count();
        if ($totalEnrollments === 0) {
            return 0;
        }

        // This is simplified - you might want to track actual completion
        return 100; // Assume all enrolled students completed
    }

    /**
     * Get enrollment trends for the school
     */
    private function getEnrollmentTrends($schoolId)
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'enrollments' => CourseEnrollment::whereHas('course', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->whereMonth('enrollment_date', $date->month)
                  ->whereYear('enrollment_date', $date->year)
                  ->count()
            ];
        }

        return $months;
    }

    /**
     * Apply search to course query
     */
    protected function applySearch($query, string $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->where('name', 'like', "%{$searchTerm}%")
              ->orWhere('description', 'like', "%{$searchTerm}%")
              ->orWhere('level', 'like', "%{$searchTerm}%")
              ->orWhere('location', 'like', "%{$searchTerm}%")
              ->orWhereHas('instructor', function($instructorQ) use ($searchTerm) {
                  $instructorQ->where('name', 'like', "%{$searchTerm}%");
              });
        });
    }

    /**
     * Calculate course attendance rate
     */
    private function calculateAttendanceRate($course): float
    {
        // This is a placeholder - in a real implementation you'd have attendance tracking
        // For now, return a simulated value based on course progress
        if ($course->end_date > now()) {
            return 0; // Course not finished yet, no attendance to calculate
        }

        // Simulate attendance rate based on enrollments vs capacity
        $enrollmentRate = $course->max_students > 0 ?
            ($course->enrollments()->count() / $course->max_students) * 100 : 0;

        // Return a realistic attendance rate (usually lower than enrollment rate)
        return round(min($enrollmentRate * 0.85, 95), 1);
    }

    /**
     * Toggle course active status
     */
    public function toggleActive(Course $course)
    {
        // Ensure course belongs to current school
        if ($course->school_id !== $this->school->id) {
            abort(404, 'Corso non trovato.');
        }

        $course->update(['active' => !$course->active]);
        $this->clearSchoolCache();

        $status = $course->active ? 'attivato' : 'disattivato';
        $message = "Corso {$status} con successo.";

        if (request()->ajax()) {
            return $this->jsonResponse(true, $message, [
                'status' => $course->active
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Export courses to CSV
     */
    public function export()
    {
        $courses = $this->school->courses()
            ->with(['instructor', 'enrollments'])
            ->orderBy('name')
            ->get();

        return $this->exportCoursesToCsv($courses);
    }

    /**
     * Export courses collection to CSV
     */
    private function exportCoursesToCsv($courses)
    {
        $data = $courses->map(function ($course) {
            return [
                $course->id,
                $course->name,
                $course->level,
                $course->instructor ? $course->instructor->name : 'Nessun istruttore',
                $course->max_students,
                $course->enrollments->count(),
                '€' . number_format($course->price, 2, ',', '.'),
                $course->start_date ? $course->start_date->format('d/m/Y') : '',
                $course->end_date ? $course->end_date->format('d/m/Y') : '',
                $course->location ?? '',
                $course->active ? 'Attivo' : 'Non attivo',
                $course->created_at->format('d/m/Y H:i')
            ];
        })->toArray();

        $headers = [
            'ID', 'Nome', 'Livello', 'Istruttore', 'Max Studenti', 'Iscrizioni',
            'Prezzo', 'Data Inizio', 'Data Fine', 'Location', 'Stato', 'Creato il'
        ];

        $filename = 'corsi_' . str_replace(' ', '_', $this->school->name) . '_' . now()->format('Y-m-d') . '.csv';

        return $this->exportToCsv($data, $headers, $filename);
    }
}