<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of courses for the admin's school
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;

        $query = Course::where('school_id', $school->id)->with(['instructor', 'enrollments']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status') === 'active';
            $query->where('active', $status);
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->get('level'));
        }

        // Filter by instructor
        if ($request->filled('instructor_id')) {
            $query->where('instructor_id', $request->get('instructor_id'));
        }

        $courses = $query->orderBy('start_date', 'desc')->paginate(15);
        $instructors = User::where('school_id', $school->id)
                          ->where('role', User::ROLE_INSTRUCTOR)
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.courses.partials.table', compact('courses'))->render(),
                'pagination' => $courses->links()->render()
            ]);
        }

        return view('admin.courses.index', compact('courses', 'instructors'));
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        $user = auth()->user();
        $school = $user->school;
        
        $instructors = User::where('school_id', $school->id)
                          ->where('role', User::ROLE_INSTRUCTOR)
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();

        return view('admin.courses.create', compact('instructors', 'school'));
    }

    /**
     * Store a newly created course in storage
     */
    public function store(StoreCourseRequest $request)
    {
        $user = auth()->user();
        
        // Ensure the course belongs to admin's school
        $data = $request->validated();
        $data['school_id'] = $user->school_id;

        $course = Course::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Corso creato con successo.',
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
        $this->authorize('view', $course);

        $course->load([
            'instructor', 
            'enrollments.user',
            'school'
        ]);

        $stats = [
            'enrolled_students' => $course->enrollments()->count(),
            'available_spots' => $course->max_students - $course->enrollments()->count(),
            'completion_rate' => $this->calculateCompletionRate($course),
            'total_revenue' => $course->enrollments()->sum('amount_paid') ?? ($course->enrollments()->count() * $course->price),
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
}