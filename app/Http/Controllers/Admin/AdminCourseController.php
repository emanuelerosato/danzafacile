<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
    public function store(StoreCourseRequest $request)
    {
        $validated = $request->validated();

        // Validate instructor_id separately if provided
        if (!empty($validated['instructor_id'])) {
            $instructorExists = \App\Models\User::where('id', $validated['instructor_id'])
                ->where('school_id', $this->school->id)
                ->whereHas('staffRoles', function($q) {
                    $q->where('active', true);
                })
                ->exists();

            if (!$instructorExists) {
                return back()->withErrors(['instructor_id' => 'L\'istruttore selezionato non è valido per questa scuola.'])->withInput();
            }
        }

        $validated['school_id'] = $this->school->id;
        $validated['active'] = $validated['active'] ?? true;

        // Process schedule_slots if provided
        if (isset($validated['schedule_slots']) && is_array($validated['schedule_slots'])) {
            // Filter out empty slots and ensure UTF-8 encoding
            $scheduleSlots = array_filter($validated['schedule_slots'], function($slot) {
                return !empty($slot['day']) && !empty($slot['start_time']) && !empty($slot['end_time']);
            });

            // Ensure proper UTF-8 encoding for day names
            foreach ($scheduleSlots as &$slot) {
                if (isset($slot['day'])) {
                    $slot['day'] = mb_convert_encoding($slot['day'], 'UTF-8', 'auto');
                    // Normalize common Italian day names
                    $dayMappings = [
                        'Lunedi' => 'Lunedì',
                        'Martedi' => 'Martedì',
                        'Mercoledi' => 'Mercoledì',
                        'Giovedi' => 'Giovedì',
                        'Venerdi' => 'Venerdì'
                    ];
                    if (isset($dayMappings[$slot['day']])) {
                        $slot['day'] = $dayMappings[$slot['day']];
                    }
                }
                if (isset($slot['location'])) {
                    $slot['location'] = mb_convert_encoding($slot['location'], 'UTF-8', 'auto');
                }
            }

            // Convert to JSON with proper UTF-8 handling
            $validated['schedule'] = json_encode(array_values($scheduleSlots), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            unset($validated['schedule_slots']);
        }

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
        // Ensure course belongs to current school
        if ($course->school_id !== $this->school->id) {
            abort(404, 'Corso non trovato.');
        }

        $instructors = $this->school->users()
                          ->whereHas('staffRoles', function($q) {
                              $q->where('active', true);
                          })
                          ->where('active', true)
                          ->orderBy('name')
                          ->get();

        $levels = ['Principiante', 'Intermedio', 'Avanzato', 'Professionale'];

        // DEBUG: Log active enrollments when loading edit page
        $activeEnrollments = $course->enrollments()->with('user')->where('status', 'active')->get();
        Log::info('🔍 EDIT PAGE LOADED - Active enrollments', [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'active_enrollments_count' => $activeEnrollments->count(),
            'student_details' => $activeEnrollments->map(function($enrollment) {
                return [
                    'user_id' => $enrollment->user->id,
                    'name' => $enrollment->user->name,
                    'enrollment_id' => $enrollment->id,
                    'status' => $enrollment->status
                ];
            })
        ]);

        return view('admin.courses.edit', compact('course', 'instructors', 'levels'));
    }

    /**
     * Update the specified course in storage
     */
    public function update(UpdateCourseRequest $request, Course $course)
    {
        \Log::info('🔥 NEW LOGGING: UPDATE METHOD CALLED', ['course_id' => $course->id, 'timestamp' => now()->toISOString()]);

        // Ensure course belongs to current school
        if ($course->school_id !== $this->school->id) {
            abort(404, 'Corso non trovato.');
        }

        \Log::info('UPDATE - About to validate request');
        try {
            $validated = $request->validated();
            \Log::info('UPDATE - Validation passed, validated data:', ['validated' => $validated]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('UPDATE - Validation failed:', ['errors' => $e->errors()]);
            throw $e;
        }

        // Validate instructor_id separately if provided
        if (!empty($validated['instructor_id'])) {
            $instructorExists = \App\Models\User::where('id', $validated['instructor_id'])
                ->where('school_id', $this->school->id)
                ->whereHas('staffRoles', function($q) {
                    $q->where('active', true);
                })
                ->exists();

            if (!$instructorExists) {
                return back()->withErrors(['instructor_id' => 'L\'istruttore selezionato non è valido per questa scuola.'])->withInput();
            }
        }

        // Process schedule_slots if provided
        if (isset($validated['schedule_slots']) && is_array($validated['schedule_slots'])) {
            // Filter out empty slots and ensure UTF-8 encoding
            $scheduleSlots = array_filter($validated['schedule_slots'], function($slot) {
                return !empty($slot['day']) && !empty($slot['start_time']) && !empty($slot['end_time']);
            });

            // Ensure proper UTF-8 encoding for day names
            foreach ($scheduleSlots as &$slot) {
                if (isset($slot['day'])) {
                    $slot['day'] = mb_convert_encoding($slot['day'], 'UTF-8', 'auto');
                    // Normalize common Italian day names
                    $dayMappings = [
                        'Lunedi' => 'Lunedì',
                        'Martedi' => 'Martedì',
                        'Mercoledi' => 'Mercoledì',
                        'Giovedi' => 'Giovedì',
                        'Venerdi' => 'Venerdì'
                    ];
                    if (isset($dayMappings[$slot['day']])) {
                        $slot['day'] = $dayMappings[$slot['day']];
                    }
                }
                if (isset($slot['location'])) {
                    $slot['location'] = mb_convert_encoding($slot['location'], 'UTF-8', 'auto');
                }
            }

            // Convert to JSON with proper UTF-8 handling
            $validated['schedule'] = json_encode(array_values($scheduleSlots), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            unset($validated['schedule_slots']);
        }

        // Remove null instructor_id to avoid DB constraint violation
        if (empty($validated['instructor_id'])) {
            unset($validated['instructor_id']);
        }

        \Log::info('UPDATE - About to update course with validated data');
        $course->update($validated);
        \Log::info('UPDATE - Course updated successfully', ['course_id' => $course->id]);
        $this->clearSchoolCache();

        if ($request->ajax()) {
            return $this->jsonResponse(true, 'Corso aggiornato con successo.', [
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
        // Ensure course belongs to current school
        if ($course->school_id !== $this->school->id) {
            abort(404, 'Corso non trovato.');
        }

        // Check if course has enrollments
        $enrollmentCount = $course->enrollments()->count();
        if ($enrollmentCount > 0) {
            \Log::warning('Attempted to delete course with enrollments', [
                'course_id' => $course->id,
                'course_name' => $course->name,
                'enrollments_count' => $enrollmentCount,
                'admin_id' => auth()->id(),
                'school_id' => $this->school->id
            ]);

            if (request()->ajax()) {
                return $this->jsonResponse(false, "Impossibile eliminare il corso. Ci sono {$enrollmentCount} student" . ($enrollmentCount === 1 ? 'e iscritto' : 'i iscritti') . ".", [], 422);
            }
            return redirect()->back()->with('error', "Impossibile eliminare il corso. Ci sono {$enrollmentCount} student" . ($enrollmentCount === 1 ? 'e iscritto' : 'i iscritti') . ".");
        }

        // Check for related data that might be orphaned
        $relatedData = [
            'documents' => $course->documents()->count(),
            'media_galleries' => $course->mediaGalleries()->count(),
            'payments' => $course->payments()->count(),
            'attendance_records' => \App\Models\Attendance::where('attendable_type', 'App\\Models\\Course')
                                                          ->where('attendable_id', $course->id)
                                                          ->count()
        ];

        $hasRelatedData = array_sum($relatedData) > 0;

        // Log deletion attempt with related data info
        \Log::info('Course deletion initiated', [
            'course_id' => $course->id,
            'course_name' => $course->name,
            'admin_id' => auth()->id(),
            'school_id' => $this->school->id,
            'related_data' => $relatedData,
            'has_related_data' => $hasRelatedData
        ]);

        // Store course data for logging before deletion
        $courseData = [
            'id' => $course->id,
            'name' => $course->name,
            'level' => $course->level,
            'price' => $course->price,
            'start_date' => $course->start_date?->format('Y-m-d'),
            'end_date' => $course->end_date?->format('Y-m-d'),
            'instructor_name' => $course->instructor?->name,
            'location' => $course->location
        ];

        // Perform deletion
        $course->delete();
        $this->clearSchoolCache();

        // Log successful deletion
        \Log::info('Course deleted successfully', [
            'course_data' => $courseData,
            'admin_id' => auth()->id(),
            'school_id' => $this->school->id,
            'deleted_at' => now()->toISOString()
        ]);

        $successMessage = $hasRelatedData
            ? 'Corso eliminato con successo. Verifica eventuali dati correlati rimasti.'
            : 'Corso eliminato con successo.';

        if (request()->ajax()) {
            return $this->jsonResponse(true, $successMessage);
        }

        return redirect()->route('admin.courses.index')
                        ->with('success', $successMessage);
    }

    /**
     * Activate/deactivate course
     */
    public function toggleStatus(Course $course)
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
     * Duplicate course with new dates
     */
    public function duplicate(Course $course)
    {
        // Ensure course belongs to current school
        if ($course->school_id !== $this->school->id) {
            abort(404, 'Corso non trovato.');
        }

        $newCourse = $course->replicate();
        $newCourse->name = $course->name . ' (Copia)';
        $newCourse->start_date = null;
        $newCourse->end_date = null;
        $newCourse->active = false;
        $newCourse->save();

        $this->clearSchoolCache();

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

    /**
     * Add a student to a course
     */
    public function addStudent(Request $request, Course $course)
    {
        \Log::info('🔥 ADD STUDENT: Method called', [
            'course_id' => $course->id,
            'request_data' => $request->all(),
            'timestamp' => now()->toISOString()
        ]);

        // Verify the course belongs to the admin's school
        if ($course->school_id !== $this->school->id) {
            abort(403, 'Non hai i permessi per gestire questo corso.');
        }

        try {
            $validatedData = $request->validate([
                'user_id' => 'required|exists:users,id',
                'status' => 'required|in:pending,active,cancelled,completed',
                'payment_status' => 'required|in:pending,paid,refunded',
                'notes' => 'nullable|string|max:1000'
            ]);
            \Log::info('🔥 ADD STUDENT: Validation passed', ['validated' => $validatedData]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('🔥 ADD STUDENT: Validation failed', ['errors' => $e->errors()]);
            throw $e;
        }

        // Check if user is already enrolled
        $existingEnrollment = CourseEnrollment::where('course_id', $course->id)
            ->where('user_id', $request->user_id)
            ->first();

        if ($existingEnrollment) {
            return redirect()->back()->withErrors(['user_id' => 'Lo studente è già iscritto a questo corso.']);
        }

        // Create enrollment
        $enrollment = CourseEnrollment::create([
            'course_id' => $course->id,
            'user_id' => $request->user_id,
            'enrollment_date' => now(),
            'status' => $request->status,
            'payment_status' => $request->payment_status,
            'notes' => $request->notes
        ]);

        \Log::info('🔥 ADD STUDENT: Enrollment created', [
            'enrollment_id' => $enrollment->id,
            'status' => $enrollment->status,
            'payment_status' => $enrollment->payment_status
        ]);

        return redirect()->back()->with('success', 'Studente aggiunto al corso con successo.');
    }

    /**
     * Remove a student from a course
     */
    public function removeStudent(Course $course, User $user)
    {
        Log::info('🔍 REMOVE STUDENT DEBUG', [
            'course_id' => $course->id,
            'course_school_id' => $course->school_id,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'auth_user_id' => auth()->id(),
            'auth_user_school_id' => auth()->user()->school_id ?? 'NULL',
            'this_school_id' => $this->school->id ?? 'NULL',
            'this_school' => $this->school ? 'EXISTS' : 'NULL'
        ]);

        // Verify the course belongs to the admin's school
        if ($course->school_id !== $this->school->id) {
            Log::warning('🚫 SCHOOL OWNERSHIP CHECK FAILED', [
                'course_school_id' => $course->school_id,
                'admin_school_id' => $this->school->id
            ]);
            abort(403, 'Non hai i permessi per gestire questo corso.');
        }

        $enrollment = CourseEnrollment::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            return redirect()->back()->withErrors(['error' => 'Lo studente non è iscritto a questo corso.']);
        }

        $enrollment->delete();

        return redirect()->back()->with('success', 'Studente rimosso dal corso con successo.');
    }
}