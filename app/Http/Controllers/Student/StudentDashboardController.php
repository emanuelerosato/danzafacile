<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\Document;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    /**
     * Display Student dashboard with personal information
     */
    public function index()
    {
        $user = auth()->user();

        // Student statistics
        $stats = [
            'active_enrollments' => $user->courseEnrollments()
                ->where('status', 'active')
                ->count(),
            
            'completed_courses' => $user->courseEnrollments()
                ->where('status', 'completed')
                ->count(),
            
            'total_payments' => $user->payments()
                ->where('status', 'completed')
                ->sum('amount'),
            
            'pending_payments' => $user->payments()
                ->where('status', 'pending')
                ->sum('amount'),
            
            'documents_uploaded' => $user->documents()->count(),
            
            'documents_pending' => $user->documents()
                ->where('status', 'pending')
                ->count(),
        ];

        // Current active enrollments with course details
        $activeEnrollments = $user->courseEnrollments()
            ->with(['course.instructor', 'course.school'])
            ->where('status', 'active')
            ->whereHas('course', function($q) {
                $q->where('active', true);
            })
            ->latest('enrollment_date')
            ->get();

        // Upcoming classes/courses
        $upcomingCourses = Course::whereHas('enrollments', function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'active');
            })
            ->where('start_date', '>', now())
            ->where('active', true)
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // Recent payments
        $recentPayments = $user->payments()
            ->with('course')
            ->latest('payment_date')
            ->take(5)
            ->get();

        // Pending documents
        $pendingDocuments = $user->documents()
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        // Announcements or news (if you have a system for this)
        $announcements = collect(); // Placeholder for announcements

        return view('student.dashboard', compact(
            'user',
            'stats',
            'activeEnrollments',
            'upcomingCourses',
            'recentPayments',
            'pendingDocuments',
            'announcements'
        ));
    }

    /**
     * Get dashboard statistics for AJAX requests
     */
    public function getStats(Request $request)
    {
        $user = auth()->user();
        $period = $request->get('period', 'month');

        $dateFilter = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        $stats = [
            'classes_attended' => $this->getClassesAttended($user, $dateFilter),
            'payments_made' => $user->payments()
                ->where('status', 'completed')
                ->where('payment_date', '>=', $dateFilter)
                ->sum('amount'),
            'new_enrollments' => $user->courseEnrollments()
                ->where('enrollment_date', '>=', $dateFilter)
                ->count(),
            'documents_submitted' => $user->documents()
                ->where('created_at', '>=', $dateFilter)
                ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Get student progress summary
     */
    public function getProgress()
    {
        $user = auth()->user();

        $progress = [
            'enrollment_history' => $this->getEnrollmentHistory($user),
            'payment_history' => $this->getPaymentHistory($user),
            'course_completion' => $this->getCourseCompletion($user),
            'attendance_rate' => $this->getAttendanceRate($user),
        ];

        return response()->json($progress);
    }

    /**
     * Get student's next classes/activities
     */
    public function getUpcomingActivities()
    {
        $user = auth()->user();

        // Get courses where student is enrolled and active
        $activities = [];

        $activeCourses = Course::whereHas('enrollments', function($q) use ($user) {
            $q->where('user_id', $user->id)
              ->where('status', 'active');
        })->where('active', true)->get();

        foreach ($activeCourses as $course) {
            // Calculate next class based on schedule
            $nextClass = $this->getNextClassDate($course);
            
            if ($nextClass) {
                $activities[] = [
                    'type' => 'class',
                    'title' => $course->name,
                    'date' => $nextClass,
                    'time' => $course->start_time . ' - ' . $course->end_time,
                    'instructor' => $course->instructor ? $course->instructor->full_name : 'TBD',
                    'location' => $course->school->name,
                ];
            }
        }

        // Sort activities by date
        usort($activities, function($a, $b) {
            return $a['date'] <=> $b['date'];
        });

        return response()->json(array_slice($activities, 0, 10)); // Return next 10 activities
    }

    /**
     * Student quick actions (enroll, pay, etc.)
     */
    public function quickActions()
    {
        $user = auth()->user();

        $actions = [
            'can_enroll_courses' => $this->getAvailableCoursesCount($user),
            'pending_payments' => $user->payments()->where('status', 'pending')->count(),
            'missing_documents' => $this->getMissingDocumentsCount($user),
            'profile_completion' => $this->getProfileCompletion($user),
        ];

        return response()->json($actions);
    }

    /**
     * Calculate classes attended (simplified implementation)
     */
    private function getClassesAttended($user, $dateFilter)
    {
        // This is a simplified calculation
        // In a real implementation, you'd have an attendance tracking system
        
        $activeEnrollments = $user->courseEnrollments()
            ->where('status', 'active')
            ->where('enrollment_date', '<=', $dateFilter)
            ->count();

        // Estimate based on average attendance (this is just an example)
        return $activeEnrollments * 8; // Assume 8 classes per course on average
    }

    /**
     * Get enrollment history data
     */
    private function getEnrollmentHistory($user)
    {
        return $user->courseEnrollments()
            ->with('course')
            ->orderBy('enrollment_date', 'desc')
            ->take(12)
            ->get()
            ->map(function($enrollment) {
                return [
                    'course_name' => $enrollment->course->name,
                    'enrollment_date' => $enrollment->enrollment_date->format('d/m/Y'),
                    'status' => $enrollment->status,
                    'level' => $enrollment->course->level,
                ];
            });
    }

    /**
     * Get payment history data
     */
    private function getPaymentHistory($user)
    {
        return $user->payments()
            ->with('course')
            ->orderBy('payment_date', 'desc')
            ->take(12)
            ->get()
            ->map(function($payment) {
                return [
                    'amount' => $payment->amount,
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('d/m/Y') : null,
                    'status' => $payment->status,
                    'course_name' => $payment->course ? $payment->course->name : 'General Payment',
                    'method' => $payment->payment_method,
                ];
            });
    }

    /**
     * Calculate course completion rate
     */
    private function getCourseCompletion($user)
    {
        $totalEnrollments = $user->courseEnrollments()->count();
        $completedEnrollments = $user->courseEnrollments()->where('status', 'completed')->count();

        if ($totalEnrollments === 0) {
            return 0;
        }

        return round(($completedEnrollments / $totalEnrollments) * 100, 2);
    }

    /**
     * Calculate attendance rate (simplified)
     */
    private function getAttendanceRate($user)
    {
        // This is a placeholder implementation
        // In a real system, you'd track actual attendance
        return 85; // Assume 85% attendance rate
    }

    /**
     * Get next class date for a course
     */
    private function getNextClassDate($course)
    {
        // Simplified implementation - this would be more complex in reality
        // considering course schedule, holidays, etc.
        
        $today = now();
        $courseSchedule = $course->schedule_days ?? [];

        // Find next occurrence of course days
        for ($i = 0; $i < 14; $i++) { // Check next 2 weeks
            $checkDate = $today->copy()->addDays($i);
            $dayName = strtolower($checkDate->format('l'));
            
            if (in_array($dayName, $courseSchedule) && $checkDate >= $course->start_date && $checkDate <= $course->end_date) {
                return $checkDate;
            }
        }

        return null;
    }

    /**
     * Get count of available courses for enrollment
     */
    private function getAvailableCoursesCount($user)
    {
        return Course::where('school_id', $user->school_id)
            ->where('active', true)
            ->where('start_date', '>', now())
            ->whereDoesntHave('enrollments', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->count();
    }

    /**
     * Get count of missing required documents
     */
    private function getMissingDocumentsCount($user)
    {
        // Define required document types
        $requiredTypes = ['medical_certificate', 'identity_document'];
        $uploadedTypes = $user->documents()->pluck('type')->toArray();
        
        return count(array_diff($requiredTypes, $uploadedTypes));
    }

    /**
     * Calculate profile completion percentage
     */
    private function getProfileCompletion($user)
    {
        $fields = [
            'name' => !empty($user->name),
            'first_name' => !empty($user->first_name),
            'last_name' => !empty($user->last_name),
            'email' => !empty($user->email),
            'phone' => !empty($user->phone),
            'date_of_birth' => !empty($user->date_of_birth),
            'profile_image' => !empty($user->profile_image_path),
        ];

        $completedFields = count(array_filter($fields));
        $totalFields = count($fields);

        return round(($completedFields / $totalFields) * 100, 2);
    }
}