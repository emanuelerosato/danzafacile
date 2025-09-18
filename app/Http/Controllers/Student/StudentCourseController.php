<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;

class StudentCourseController extends Controller
{
    /**
     * Display available courses for enrollment
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Course::where('school_id', $user->school_id)
                      ->where('active', true)
                      ->with(['instructor', 'school']);

        // Show only courses that haven't started yet or are currently running
        $query->where(function($q) {
            $q->where('start_date', '>', now())
              ->orWhere(function($subq) {
                  $subq->where('start_date', '<=', now())
                       ->where('end_date', '>=', now());
              });
        });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by level
        if ($request->filled('level')) {
            $query->where('level', $request->get('level'));
        }

        // Filter by age appropriateness
        if ($request->filled('age_filter') && $user->date_of_birth) {
            $age = now()->diffInYears($user->date_of_birth);
            $query->where(function($q) use ($age) {
                $q->where(function($subq) use ($age) {
                    $subq->whereNull('age_min')
                         ->whereNull('age_max');
                })->orWhere(function($subq) use ($age) {
                    $subq->where('age_min', '<=', $age)
                         ->where('age_max', '>=', $age);
                });
            });
        }

        // Filter by schedule (if user wants courses on specific days)
        if ($request->filled('schedule_day')) {
            $day = $request->get('schedule_day');
            $query->whereJsonContains('schedule_days', $day);
        }

        // Exclude courses where user is already enrolled
        $query->whereDoesntHave('enrollments', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->whereIn('status', ['active', 'pending']);
        });

        $courses = $query->orderBy('start_date')->paginate(12);

        // Get user's current enrollments for reference
        $currentEnrollments = $user->courseEnrollments()
                                  ->with('course')
                                  ->where('status', 'active')
                                  ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('student.courses.partials.grid', compact('courses'))->render(),
                'pagination' => $courses->links()->render()
            ]);
        }

        return view('student.courses.index', compact('courses', 'currentEnrollments'));
    }

    /**
     * Display detailed information about a specific course
     */
    public function show(Course $course)
    {
        $user = auth()->user();

        // Check if course belongs to user's school
        if ($course->school_id !== $user->school_id) {
            abort(403, 'Corso non disponibile per la tua scuola.');
        }

        $course->load(['instructor', 'school', 'enrollments.user']);

        // Check enrollment status
        $userEnrollment = $course->enrollments()
                                ->where('user_id', $user->id)
                                ->first();

        $enrollmentStatus = $userEnrollment ? $userEnrollment->status : null;

        // Calculate availability
        $availableSpots = $course->max_students - $course->enrollments()
                                                         ->where('status', 'active')
                                                         ->count();

        // Check age eligibility
        $ageEligible = $this->checkAgeEligibility($user, $course);

        // Get similar courses
        $similarCourses = Course::where('school_id', $user->school_id)
                                ->where('level', $course->level)
                                ->where('id', '!=', $course->id)
                                ->where('active', true)
                                ->take(3)
                                ->get();

        return view('student.courses.show', compact(
            'course',
            'enrollmentStatus',
            'availableSpots',
            'ageEligible',
            'similarCourses'
        ));
    }

    /**
     * Enroll student in a course
     */
    public function enroll(Request $request, Course $course)
    {
        $user = auth()->user();

        // Validation checks
        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Corso non disponibile per la tua scuola.'
            ], 403);
        }

        if (!$course->active) {
            return response()->json([
                'success' => false,
                'message' => 'Il corso non è attualmente disponibile.'
            ], 422);
        }

        // Check if already enrolled
        $existingEnrollment = CourseEnrollment::where('user_id', $user->id)
                                             ->where('course_id', $course->id)
                                             ->whereIn('status', ['active', 'pending'])
                                             ->first();

        if ($existingEnrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Sei già iscritto a questo corso.'
            ], 422);
        }

        // Check course capacity
        $currentEnrollments = CourseEnrollment::where('course_id', $course->id)
                                             ->where('status', 'active')
                                             ->count();

        if ($currentEnrollments >= $course->max_students) {
            return response()->json([
                'success' => false,
                'message' => 'Il corso ha raggiunto il numero massimo di studenti.'
            ], 422);
        }

        // Check age eligibility
        if (!$this->checkAgeEligibility($user, $course)) {
            return response()->json([
                'success' => false,
                'message' => 'Non soddisfi i requisiti di età per questo corso.'
            ], 422);
        }

        // Create enrollment
        $enrollment = CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrollment_date' => now(),
            'status' => 'active',
            'notes' => $request->get('notes'),
        ]);

        // TODO: Here you might want to create a pending payment record
        // or integrate with a payment system

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Iscrizione completata con successo! Verrai contattato per i dettagli del pagamento.',
                'enrollment' => $enrollment->load('course')
            ]);
        }

        return redirect()->route('student.my-courses')
                        ->with('success', 'Iscrizione completata con successo!');
    }

    /**
     * Cancel enrollment in a course
     */
    public function cancelEnrollment(Course $course)
    {
        $user = auth()->user();

        $enrollment = CourseEnrollment::where('user_id', $user->id)
                                     ->where('course_id', $course->id)
                                     ->whereIn('status', ['active', 'pending'])
                                     ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Iscrizione non trovata.'
            ], 404);
        }

        // Check if course has started (allow cancellation only before start or within grace period)
        $gracePeriod = now()->subDays(7); // 7 days grace period
        if ($course->start_date < $gracePeriod) {
            return response()->json([
                'success' => false,
                'message' => 'Non è più possibile cancellare l\'iscrizione per questo corso.'
            ], 422);
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
     * Display student's current enrollments
     */
    public function myEnrollments(Request $request)
    {
        $user = auth()->user();

        $query = $user->courseEnrollments()->with(['course.instructor', 'course.school']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $enrollments = $query->orderBy('enrollment_date', 'desc')->paginate(10);

        if ($request->ajax()) {
            // TODO: Create partial view for AJAX loading
            return response()->json([
                'html' => '<div class="p-4 text-center">AJAX loading non ancora implementato</div>',
                'pagination' => $enrollments->links()->render()
            ]);
        }

        return view('student.my-courses', compact('enrollments'));
    }

    /**
     * Display detailed view of a specific enrollment
     */
    public function showEnrollment(CourseEnrollment $enrollment)
    {
        // Check authorization
        if ($enrollment->user_id !== auth()->id()) {
            abort(403, 'Non autorizzato.');
        }

        $enrollment->load(['course.instructor', 'course.school', 'payments']);

        // Get course schedule details
        $scheduleDetails = $this->getScheduleDetails($enrollment->course);

        // For now, redirect to dashboard - TODO: create dedicated enrollment view
        return redirect()->route('student.dashboard')->with('info', 'Dettagli iscrizione: ' . $enrollment->course->name);
    }

    /**
     * Get available time slots for course selection
     */
    public function getAvailableSlots(Request $request)
    {
        $user = auth()->user();
        $selectedDay = $request->get('day');

        $courses = Course::where('school_id', $user->school_id)
                        ->where('active', true)
                        ->whereJsonContains('schedule_days', $selectedDay)
                        ->where('start_date', '>', now())
                        ->whereDoesntHave('enrollments', function($q) use ($user) {
                            $q->where('user_id', $user->id)
                              ->whereIn('status', ['active', 'pending']);
                        })
                        ->orderBy('start_time')
                        ->get();

        return response()->json([
            'slots' => $courses->map(function($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'time' => $course->start_time . ' - ' . $course->end_time,
                    'instructor' => $course->instructor ? $course->instructor->full_name : 'TBD',
                    'level' => $course->level,
                    'available_spots' => $course->max_students - $course->enrollments()->where('status', 'active')->count()
                ];
            })
        ]);
    }

    /**
     * Check if user meets age requirements for course
     */
    private function checkAgeEligibility($user, $course)
    {
        if (!$user->date_of_birth || (!$course->age_min && !$course->age_max)) {
            return true; // No age restrictions or no birth date provided
        }

        $age = now()->diffInYears($user->date_of_birth);

        if ($course->age_min && $age < $course->age_min) {
            return false;
        }

        if ($course->age_max && $age > $course->age_max) {
            return false;
        }

        return true;
    }

    /**
     * Get detailed schedule information for a course
     */
    private function getScheduleDetails($course)
    {
        $schedule = [];
        $scheduleDays = $course->schedule_days ?? [];

        foreach ($scheduleDays as $day) {
            $schedule[] = [
                'day' => ucfirst($day),
                'time' => $course->start_time . ' - ' . $course->end_time,
                'next_occurrence' => $this->getNextOccurrence($day, $course)
            ];
        }

        return $schedule;
    }

    /**
     * Get next occurrence of a specific day within course period
     */
    private function getNextOccurrence($dayName, $course)
    {
        $today = now();
        $endDate = $course->end_date;

        for ($i = 0; $i < 30; $i++) { // Check next 30 days
            $checkDate = $today->copy()->addDays($i);
            
            if (strtolower($checkDate->format('l')) === $dayName 
                && $checkDate >= $course->start_date 
                && $checkDate <= $endDate) {
                return $checkDate;
            }
        }

        return null;
    }
}