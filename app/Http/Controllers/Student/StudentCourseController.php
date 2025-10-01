<?php

namespace App\Http\Controllers\Student;

use App\Helpers\QueryHelper;
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
        // SECURITY: Sanitize LIKE input to prevent SQL wildcard injection
        if ($request->filled('search')) {
            $search = QueryHelper::sanitizeLikeInput($request->get('search'));
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
     * Display student's unified course dashboard (schedule + enrollments + stats)
     */
    public function myEnrollments(Request $request)
    {
        $user = auth()->user();

        // Get student's active enrollments with schedule data
        $activeEnrollments = $user->courseEnrollments()
            ->with(['course.instructor'])
            ->where('status', 'active')
            ->get();

        // Get all enrollments for detailed list
        $query = $user->courseEnrollments()->with(['course.instructor', 'course.school']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $enrollments = $query->orderBy('enrollment_date', 'desc')->paginate(10);

        // Generate schedule data (reusing logic from schedule method)
        $upcomingEvents = [];
        $weeklySchedule = [
            'monday' => [], 'tuesday' => [], 'wednesday' => [], 'thursday' => [],
            'friday' => [], 'saturday' => [], 'sunday' => []
        ];

        foreach ($activeEnrollments as $enrollment) {
            $course = $enrollment->course;

            // Parse schedule and build weekly view
            if ($course->schedule) {
                $scheduleData = is_string($course->schedule) ? json_decode($course->schedule, true) : $course->schedule;

                if (is_array($scheduleData) && !isset($scheduleData['description'])) {
                    foreach ($scheduleData as $day => $times) {
                        $dayKey = strtolower($day);

                        // Map Italian day names to English (handle both with and without accents)
                        $dayMapping = [
                            'lunedì' => 'monday', 'lunedi' => 'monday',
                            'martedì' => 'tuesday', 'martedi' => 'tuesday',
                            'mercoledì' => 'wednesday', 'mercoledi' => 'wednesday',
                            'giovedì' => 'thursday', 'giovedi' => 'thursday',
                            'venerdì' => 'friday', 'venerdi' => 'friday',
                            'sabato' => 'saturday',
                            'domenica' => 'sunday'
                        ];

                        if (isset($dayMapping[$dayKey])) {
                            $dayKey = $dayMapping[$dayKey];
                        }

                        if (isset($weeklySchedule[$dayKey])) {
                            if (is_array($times) && count($times) > 0) {
                                $timeRange = $times[0];
                                if (is_string($timeRange) && strpos($timeRange, '-') !== false) {
                                    [$start, $end] = explode('-', $timeRange);
                                    $parsedTimes = ['start' => trim($start), 'end' => trim($end), 'duration' => 1.5];
                                } else {
                                    $parsedTimes = ['start' => '18:00', 'end' => '19:30', 'duration' => 1.5];
                                }
                            } else {
                                $parsedTimes = ['start' => '18:00', 'end' => '19:30', 'duration' => 1.5];
                            }

                            $weeklySchedule[$dayKey][] = [
                                'course' => $course,
                                'enrollment' => $enrollment,
                                'times' => $parsedTimes,
                                'instructor' => $course->instructor ? $course->instructor->name : 'TBD'
                            ];
                        }
                    }
                }
            }

            // Generate upcoming events (next 14 days)
            $startDate = max(now(), $course->start_date);
            $endDate = min(now()->addDays(14), $course->end_date);

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if ($this->isDayInCourseSchedule($currentDate, $course)) {
                    $upcomingEvents[] = [
                        'date' => $currentDate->copy(),
                        'course' => $course,
                        'enrollment' => $enrollment,
                        'instructor' => $course->instructor ? $course->instructor->name : 'TBD'
                    ];
                }
                $currentDate->addDay();
            }
        }

        // Sort upcoming events by date
        usort($upcomingEvents, function($a, $b) {
            return $a['date']->timestamp - $b['date']->timestamp;
        });
        $upcomingEvents = array_slice($upcomingEvents, 0, 6);

        // Calculate statistics
        $stats = [
            'active_courses' => $activeEnrollments->count(),
            'total_hours_per_week' => $this->calculateWeeklyHours($weeklySchedule),
            'next_class' => !empty($upcomingEvents) ? $upcomingEvents[0] : null,
            'completed_classes' => $this->calculateCompletedClasses($activeEnrollments)
        ];

        // Get enrollment statistics by status
        $enrollmentStats = [
            'active' => $user->courseEnrollments()->where('status', 'active')->count(),
            'completed' => $user->courseEnrollments()->where('status', 'completed')->count(),
            'cancelled' => $user->courseEnrollments()->where('status', 'cancelled')->count(),
            'pending' => $user->courseEnrollments()->where('status', 'pending')->count(),
        ];

        // Get payment data
        $userPayments = $user->payments()
            ->with(['course', 'event'])
            ->orderBy('payment_date', 'desc')
            ->take(10)
            ->get();

        // Get all payments for statistics and filtering
        $allUserPayments = $user->payments();

        // Payment statistics
        $paymentStats = [
            'total_spent' => $user->payments()->where('status', 'completed')->sum('amount'),
            'pending_amount' => $user->payments()->whereIn('status', ['pending', 'processing'])->sum('amount'),
            'overdue_count' => $user->payments()->where('due_date', '<', now())->whereIn('status', ['pending', 'processing'])->count(),
            'this_month_spent' => $user->payments()->where('status', 'completed')->whereMonth('payment_date', now()->month)->sum('amount'),
        ];

        // Payment status counts
        $paymentStatusStats = [
            'completed' => $user->payments()->where('status', 'completed')->count(),
            'pending' => $user->payments()->where('status', 'pending')->count(),
            'failed' => $user->payments()->where('status', 'failed')->count(),
            'overdue' => $user->payments()->where('due_date', '<', now())->whereIn('status', ['pending', 'processing'])->count(),
        ];

        // Upcoming payments (next 30 days)
        $upcomingPayments = $user->payments()
            ->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(30)])
            ->orderBy('due_date')
            ->take(5)
            ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('student.my-courses-partial', compact('enrollments'))->render(),
                'pagination' => $enrollments->links()->render()
            ]);
        }

        return view('student.my-courses', compact(
            'enrollments',
            'activeEnrollments',
            'weeklySchedule',
            'upcomingEvents',
            'stats',
            'enrollmentStats',
            'userPayments',
            'paymentStats',
            'paymentStatusStats',
            'upcomingPayments'
        ));
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

    /**
     * Display student's personalized schedule/calendar
     */
    public function schedule()
    {
        $user = auth()->user();

        // Get student's active enrollments
        $activeEnrollments = $user->courseEnrollments()
            ->with(['course.instructor'])
            ->where('status', 'active')
            ->get();

        // Get upcoming events for enrolled courses
        $upcomingEvents = [];
        $weeklySchedule = [
            'monday' => [],
            'tuesday' => [],
            'wednesday' => [],
            'thursday' => [],
            'friday' => [],
            'saturday' => [],
            'sunday' => []
        ];

        foreach ($activeEnrollments as $enrollment) {
            $course = $enrollment->course;

            // Parse schedule and build weekly view
            if ($course->schedule) {
                $scheduleData = is_string($course->schedule) ? json_decode($course->schedule, true) : $course->schedule;

                if (is_array($scheduleData)) {
                    // Check if it's the old format (description only)
                    if (isset($scheduleData['description'])) {
                        // Old format - try to parse from description
                        $description = $scheduleData['description'];
                        if (stripos($description, 'lunedì') !== false || stripos($description, 'monday') !== false) {
                            $weeklySchedule['monday'][] = [
                                'course' => $course,
                                'enrollment' => $enrollment,
                                'times' => ['start' => '18:00', 'end' => '19:30', 'duration' => 1.5],
                                'instructor' => $course->instructor ? $course->instructor->name : 'TBD'
                            ];
                        }
                        if (stripos($description, 'mercoledì') !== false || stripos($description, 'wednesday') !== false) {
                            $weeklySchedule['wednesday'][] = [
                                'course' => $course,
                                'enrollment' => $enrollment,
                                'times' => ['start' => '18:00', 'end' => '19:30', 'duration' => 1.5],
                                'instructor' => $course->instructor ? $course->instructor->name : 'TBD'
                            ];
                        }
                    } else {
                        // New format - proper structure
                        foreach ($scheduleData as $day => $times) {
                            $dayKey = strtolower($day);

                            // Map Italian day names to English
                            $dayMapping = [
                                'lunedì' => 'monday',
                                'martedì' => 'tuesday',
                                'mercoledì' => 'wednesday',
                                'giovedì' => 'thursday',
                                'venerdì' => 'friday',
                                'sabato' => 'saturday',
                                'domenica' => 'sunday'
                            ];

                            if (isset($dayMapping[$dayKey])) {
                                $dayKey = $dayMapping[$dayKey];
                            }

                            if (isset($weeklySchedule[$dayKey])) {
                                // Parse time range if it's a simple string like "19:00-20:30"
                                if (is_array($times) && count($times) > 0) {
                                    $timeRange = $times[0];
                                    if (is_string($timeRange) && strpos($timeRange, '-') !== false) {
                                        [$start, $end] = explode('-', $timeRange);
                                        $parsedTimes = [
                                            'start' => trim($start),
                                            'end' => trim($end),
                                            'duration' => 1.5 // Default duration
                                        ];
                                    } else {
                                        $parsedTimes = is_array($timeRange) ? $timeRange : ['start' => '18:00', 'end' => '19:30', 'duration' => 1.5];
                                    }
                                } else {
                                    $parsedTimes = ['start' => '18:00', 'end' => '19:30', 'duration' => 1.5];
                                }

                                $weeklySchedule[$dayKey][] = [
                                    'course' => $course,
                                    'enrollment' => $enrollment,
                                    'times' => $parsedTimes,
                                    'instructor' => $course->instructor ? $course->instructor->name : 'TBD'
                                ];
                            }
                        }
                    }
                }
            }

            // Generate upcoming events (next 30 days)
            $startDate = max(now(), $course->start_date);
            $endDate = min(now()->addDays(30), $course->end_date);

            $currentDate = $startDate->copy();
            while ($currentDate <= $endDate) {
                if ($this->isDayInCourseSchedule($currentDate, $course)) {
                    $upcomingEvents[] = [
                        'date' => $currentDate->copy(),
                        'course' => $course,
                        'enrollment' => $enrollment,
                        'instructor' => $course->instructor ? $course->instructor->name : 'TBD'
                    ];
                }
                $currentDate->addDay();
            }
        }

        // Sort upcoming events by date
        usort($upcomingEvents, function($a, $b) {
            return $a['date']->timestamp - $b['date']->timestamp;
        });

        // Take only next 10 events
        $upcomingEvents = array_slice($upcomingEvents, 0, 10);

        // Calculate statistics
        $stats = [
            'active_courses' => $activeEnrollments->count(),
            'total_hours_per_week' => $this->calculateWeeklyHours($weeklySchedule),
            'next_class' => !empty($upcomingEvents) ? $upcomingEvents[0] : null,
            'completed_classes' => $this->calculateCompletedClasses($activeEnrollments)
        ];

        return view('student.schedule.index', compact(
            'activeEnrollments',
            'weeklySchedule',
            'upcomingEvents',
            'stats'
        ));
    }

    /**
     * Check if a date falls within a course's schedule
     */
    private function isDayInCourseSchedule($date, $course)
    {
        if (!$course->schedule) {
            return false;
        }

        $scheduleData = is_string($course->schedule) ? json_decode($course->schedule, true) : $course->schedule;
        if (!is_array($scheduleData)) {
            return false;
        }

        $dayName = strtolower($date->format('l'));
        $dayNameItalianVariants = $this->translateDayToItalian($dayName);

        // Check if it's the old format (description only)
        if (isset($scheduleData['description'])) {
            $description = strtolower($scheduleData['description']);
            foreach ($dayNameItalianVariants as $variant) {
                if (stripos($description, $variant) !== false) {
                    return true;
                }
            }
            return stripos($description, $dayName) !== false;
        }

        // New format - check keys
        if (isset($scheduleData[$dayName])) {
            return true;
        }
        foreach ($dayNameItalianVariants as $variant) {
            if (isset($scheduleData[$variant])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Translate English day names to Italian (including variants without accents)
     */
    private function translateDayToItalian($englishDay)
    {
        $translations = [
            'monday' => ['lunedì', 'lunedi'],
            'tuesday' => ['martedì', 'martedi'],
            'wednesday' => ['mercoledì', 'mercoledi'],
            'thursday' => ['giovedì', 'giovedi'],
            'friday' => ['venerdì', 'venerdi'],
            'saturday' => ['sabato'],
            'sunday' => ['domenica']
        ];

        return $translations[$englishDay] ?? [$englishDay];
    }

    /**
     * Calculate total weekly hours from schedule
     */
    private function calculateWeeklyHours($weeklySchedule)
    {
        $totalHours = 0;

        foreach ($weeklySchedule as $day => $classes) {
            foreach ($classes as $class) {
                if (isset($class['times']['duration'])) {
                    $totalHours += (float) $class['times']['duration'];
                } else {
                    // Default to 1.5 hours if duration not specified
                    $totalHours += 1.5;
                }
            }
        }

        return $totalHours;
    }

    /**
     * Calculate completed classes for active enrollments
     */
    private function calculateCompletedClasses($activeEnrollments)
    {
        $completedClasses = 0;

        foreach ($activeEnrollments as $enrollment) {
            $course = $enrollment->course;

            // Calculate how many classes have occurred since enrollment
            $enrollmentDate = $enrollment->enrollment_date;
            $today = now();

            if ($course->start_date <= $today) {
                $startCountingFrom = max($enrollmentDate, $course->start_date);
                $daysBetween = $startCountingFrom->diffInDays($today);

                // Rough calculation: assume 1-2 classes per week
                if ($course->schedule) {
                    $scheduleData = json_decode($course->schedule, true);
                    $classesPerWeek = is_array($scheduleData) ? count($scheduleData) : 1;
                } else {
                    $classesPerWeek = 1;
                }

                $completedClasses += floor(($daysBetween / 7) * $classesPerWeek);
            }
        }

        return $completedClasses;
    }
}