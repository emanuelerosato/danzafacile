<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\API\BaseApiController;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminController extends BaseApiController
{

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        // Get dashboard statistics
        $totalStudents = User::where('school_id', $schoolId)->where('role', 'user')->count();
        $activeStudents = User::where('school_id', $schoolId)->where('role', 'user')->where('active', true)->count();
        $activeCourses = Course::where('school_id', $schoolId)->where('active', true)->count();
        $monthlyRevenue = Payment::whereHas('course', function($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->where('status', 'completed')
          ->whereMonth('created_at', Carbon::now()->month)
          ->sum('amount');

        $stats = [
            'students_total' => $totalStudents,
            'students_active' => $activeStudents,
            'courses_active' => $activeCourses,
            'revenue_this_month' => $monthlyRevenue,
        ];

        // Recent activities
        $recent_enrollments = CourseEnrollment::with(['user:id,name,email', 'course:id,name'])
            ->whereHas('course', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Popular courses
        $popular_courses = Course::where('school_id', $schoolId)
            ->withCount('enrollments')
            ->orderBy('enrollments_count', 'desc')
            ->limit(5)
            ->get(['id', 'name', 'max_students', 'price']);

        // Pending payments
        $pending_payments = Payment::whereHas('course', function($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->where('status', 'pending')
          ->with(['user:id,name,email', 'course:id,name'])
          ->limit(5)
          ->get();

        // Upcoming events (we'll mock this for now)
        $upcoming_events = [];

        return response()->json([
            'success' => true,
            'data' => [
                'school' => [
                    'id' => $user->school->id,
                    'name' => $user->school->name,
                    'email' => $user->school->email,
                    'phone' => $user->school->phone,
                ],
                'stats' => $stats,
                'recent_enrollments' => $recent_enrollments,
                'pending_payments' => $pending_payments,
                'upcoming_events' => $upcoming_events,
                'popular_courses' => $popular_courses,
                'admin_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ]
            ]
        ]);
    }

    public function analytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;
        
        $period = $request->get('period', '30'); // days
        $startDate = Carbon::now()->subDays($period);

        // Enrollment trends
        $enrollment_trends = CourseEnrollment::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereHas('course', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue trends
        $revenue_trends = Payment::selectRaw('DATE(created_at) as date, SUM(amount) as revenue')
            ->whereHas('course', function($query) use ($schoolId) {
                $query->where('school_id', $schoolId);
            })
            ->where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Course performance
        $course_performance = Course::where('school_id', $schoolId)
            ->withCount(['enrollments' => function($query) use ($startDate) {
                $query->where('created_at', '>=', $startDate);
            }])
            ->with(['payments' => function($query) use ($startDate) {
                $query->where('status', 'completed')
                      ->where('created_at', '>=', $startDate);
            }])
            ->get()
            ->map(function($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'enrollments_count' => $course->enrollments_count,
                    'revenue' => $course->payments->sum('amount'),
                    'avg_revenue_per_student' => $course->enrollments_count > 0 
                        ? $course->payments->sum('amount') / $course->enrollments_count 
                        : 0
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'enrollment_trends' => $enrollment_trends,
                'revenue_trends' => $revenue_trends,
                'course_performance' => $course_performance,
                'summary' => [
                    'total_enrollments' => $enrollment_trends->sum('count'),
                    'total_revenue' => $revenue_trends->sum('revenue'),
                    'avg_daily_enrollments' => round($enrollment_trends->avg('count'), 2),
                    'avg_daily_revenue' => round($revenue_trends->avg('revenue'), 2),
                ]
            ]
        ]);
    }

    public function notifications(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        // Pending actions that need admin attention
        $notifications = [];

        // Pending payments
        $pending_payments = Payment::whereHas('course', function($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->where('status', 'pending')->count();

        if ($pending_payments > 0) {
            $notifications[] = [
                'type' => 'payment',
                'title' => 'Pending Payments',
                'message' => "{$pending_payments} payments require your attention",
                'count' => $pending_payments,
                'priority' => 'high'
            ];
        }

        // New enrollments awaiting approval
        $pending_enrollments = CourseEnrollment::whereHas('course', function($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->where('status', 'pending')->count();

        if ($pending_enrollments > 0) {
            $notifications[] = [
                'type' => 'enrollment',
                'title' => 'New Enrollments',
                'message' => "{$pending_enrollments} students waiting for enrollment approval",
                'count' => $pending_enrollments,
                'priority' => 'medium'
            ];
        }

        // Courses nearing capacity
        $full_courses = Course::where('school_id', $schoolId)
            ->where('active', true)
            ->withCount('enrollments')
            ->get()
            ->filter(function($course) {
                return $course->enrollments_count >= ($course->max_students * 0.9);
            })
            ->count();

        if ($full_courses > 0) {
            $notifications[] = [
                'type' => 'capacity',
                'title' => 'Course Capacity Alert',
                'message' => "{$full_courses} courses are nearing full capacity",
                'count' => $full_courses,
                'priority' => 'low'
            ];
        }

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'total_count' => count($notifications)
        ]);
    }

    public function quickStats(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        $stats = [
            'today' => [
                'new_enrollments' => CourseEnrollment::whereHas('course', function($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })->whereDate('created_at', $today)->count(),
                
                'payments_received' => Payment::whereHas('course', function($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })->where('status', 'completed')->whereDate('created_at', $today)->sum('amount'),
            ],
            'this_week' => [
                'new_students' => User::where('school_id', $schoolId)
                    ->where('role', 'student')
                    ->where('created_at', '>=', $thisWeek)
                    ->count(),
                    
                'active_classes' => Course::where('school_id', $schoolId)
                    ->where('active', true)
                    ->count(),
            ],
            'this_month' => [
                'total_revenue' => Payment::whereHas('course', function($query) use ($schoolId) {
                    $query->where('school_id', $schoolId);
                })->where('status', 'completed')
                  ->where('created_at', '>=', $thisMonth)
                  ->sum('amount'),
                  
                'completion_rate' => $this->calculateCompletionRate($schoolId, $thisMonth),
            ]
        ];

        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    }

    private function calculateCompletionRate($schoolId, $startDate): float
    {
        $total_enrollments = CourseEnrollment::whereHas('course', function($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->where('created_at', '>=', $startDate)->count();

        $completed_enrollments = CourseEnrollment::whereHas('course', function($query) use ($schoolId) {
            $query->where('school_id', $schoolId);
        })->where('status', 'completed')
          ->where('created_at', '>=', $startDate)
          ->count();

        return $total_enrollments > 0 ? round(($completed_enrollments / $total_enrollments) * 100, 2) : 0;
    }
}