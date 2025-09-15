<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use App\Models\Attendance;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Analytics API Controller
 *
 * Handles analytics and reports for Flutter app including:
 * - Mobile-optimized dashboard analytics
 * - Revenue and payment analytics
 * - Student and course performance metrics
 * - Attendance analytics
 * - Exportable reports data
 */
class AnalyticsController extends BaseApiController
{
    /**
     * Get mobile dashboard analytics
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        $period = $request->get('period', 'month'); // day, week, month, year

        if ($user->isStudent()) {
            return $this->getStudentAnalytics($user, $period);
        } elseif ($user->isAdmin()) {
            return $this->getAdminAnalytics($user, $period);
        } else {
            return $this->forbiddenResponse('Analytics not available for this user role');
        }
    }

    /**
     * Get student analytics
     */
    private function getStudentAnalytics(User $user, string $period): JsonResponse
    {
        $dateFilter = $this->getDateFilter($period);

        // Basic stats
        $stats = [
            'enrolled_courses' => $user->courseEnrollments()->where('status', 'active')->count(),
            'completed_courses' => $user->courseEnrollments()->where('status', 'completed')->count(),
            'total_payments' => $user->payments()->where('status', 'completed')->sum('amount'),
            'pending_payments' => $user->payments()->where('status', 'pending')->sum('amount'),
            'attendance_sessions' => $user->attendanceRecords()->where('attendance_date', '>=', $dateFilter)->count(),
            'present_sessions' => $user->attendanceRecords()
                ->where('status', 'present')
                ->where('attendance_date', '>=', $dateFilter)
                ->count(),
        ];

        $stats['attendance_rate'] = $stats['attendance_sessions'] > 0
            ? round(($stats['present_sessions'] / $stats['attendance_sessions']) * 100, 1)
            : 0;

        // Recent activity
        $recentEnrollments = $user->courseEnrollments()
            ->with('course')
            ->latest('enrollment_date')
            ->take(5)
            ->get()
            ->map(function($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'course_name' => $enrollment->course->name,
                    'enrollment_date' => $enrollment->enrollment_date->format('Y-m-d'),
                    'status' => $enrollment->status,
                ];
            });

        // Upcoming events
        $upcomingEvents = Event::where('school_id', $user->school_id)
            ->where('start_date', '>', now())
            ->whereHas('registrations', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->orderBy('start_date')
            ->take(5)
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'start_date' => $event->start_date->toISOString(),
                    'location' => $event->location,
                ];
            });

        // Monthly attendance trend (last 6 months)
        $attendanceTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $total = $user->attendanceRecords()
                ->whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->count();
            $present = $user->attendanceRecords()
                ->where('status', 'present')
                ->whereMonth('attendance_date', $month->month)
                ->whereYear('attendance_date', $month->year)
                ->count();

            $attendanceTrend->push([
                'month' => $month->format('M Y'),
                'total_sessions' => $total,
                'present_sessions' => $present,
                'rate' => $total > 0 ? round(($present / $total) * 100, 1) : 0,
            ]);
        }

        return $this->successResponse([
            'user_role' => 'student',
            'period' => $period,
            'stats' => $stats,
            'recent_enrollments' => $recentEnrollments,
            'upcoming_events' => $upcomingEvents,
            'attendance_trend' => $attendanceTrend,
        ], 'Student analytics retrieved successfully');
    }

    /**
     * Get admin analytics
     */
    private function getAdminAnalytics(User $user, string $period): JsonResponse
    {
        $school = $user->school;
        $dateFilter = $this->getDateFilter($period);

        // Basic stats
        $stats = [
            'total_students' => $school->users()->where('role', 'user')->count(),
            'active_students' => $school->users()->where('role', 'user')->where('active', true)->count(),
            'total_courses' => $school->courses()->count(),
            'active_courses' => $school->courses()->where('active', true)->count(),
            'total_staff' => $school->users()->where('role', 'admin')->count(),
            'total_revenue' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'completed')->sum('amount'),
            'period_revenue' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'completed')->where('payment_date', '>=', $dateFilter)->sum('amount'),
            'pending_payments' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'pending')->sum('amount'),
        ];

        // New enrollments this period
        $stats['new_enrollments'] = CourseEnrollment::whereHas('course', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })->where('enrollment_date', '>=', $dateFilter)->count();

        // Course utilization
        $courseUtilization = $school->courses()
            ->where('active', true)
            ->selectRaw('
                COUNT(*) as total_courses,
                AVG(
                    (SELECT COUNT(*) FROM course_enrollments WHERE course_enrollments.course_id = courses.id AND status = "active") * 100.0 / max_students
                ) as avg_utilization
            ')
            ->first();

        $stats['course_utilization'] = round($courseUtilization->avg_utilization ?? 0, 1);

        // Monthly revenue trend (last 6 months)
        $revenueTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $revenue = Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->where('status', 'completed')
            ->whereMonth('payment_date', $month->month)
            ->whereYear('payment_date', $month->year)
            ->sum('amount');

            $revenueTrend->push([
                'month' => $month->format('M Y'),
                'revenue' => (float) $revenue,
            ]);
        }

        // Student growth trend (last 6 months)
        $studentGrowth = collect();
        for ($i = 5; $i >= 0; $i--) {
            $month = now()->subMonths($i);
            $newStudents = $school->users()
                ->where('role', 'user')
                ->whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();

            $studentGrowth->push([
                'month' => $month->format('M Y'),
                'new_students' => $newStudents,
            ]);
        }

        // Top performing courses
        $topCourses = $school->courses()
            ->withCount(['enrollments as active_enrollments' => function($q) {
                $q->where('status', 'active');
            }])
            ->where('active', true)
            ->orderBy('active_enrollments', 'desc')
            ->take(5)
            ->get()
            ->map(function($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'active_enrollments' => $course->active_enrollments,
                    'max_students' => $course->max_students,
                    'utilization' => $course->max_students > 0
                        ? round(($course->active_enrollments / $course->max_students) * 100, 1)
                        : 0,
                ];
            });

        // Recent activities
        $recentEnrollments = CourseEnrollment::whereHas('course', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })
        ->with(['user', 'course'])
        ->latest('enrollment_date')
        ->take(5)
        ->get()
        ->map(function($enrollment) {
            return [
                'id' => $enrollment->id,
                'student_name' => $enrollment->user->name,
                'course_name' => $enrollment->course->name,
                'enrollment_date' => $enrollment->enrollment_date->format('Y-m-d'),
                'status' => $enrollment->status,
            ];
        });

        return $this->successResponse([
            'user_role' => 'admin',
            'period' => $period,
            'school' => [
                'id' => $school->id,
                'name' => $school->name,
            ],
            'stats' => $stats,
            'revenue_trend' => $revenueTrend,
            'student_growth' => $studentGrowth,
            'top_courses' => $topCourses,
            'recent_enrollments' => $recentEnrollments,
        ], 'Admin analytics retrieved successfully');
    }

    /**
     * Get revenue analytics
     */
    public function revenue(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can access revenue analytics');
        }

        $school = $user->school;
        $period = $request->get('period', 'month');

        // Total revenue stats
        $totalRevenue = Payment::whereHas('user', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })->where('status', 'completed')->sum('amount');

        $pendingRevenue = Payment::whereHas('user', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })->where('status', 'pending')->sum('amount');

        $thisMonthRevenue = Payment::whereHas('user', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })
        ->where('status', 'completed')
        ->whereMonth('payment_date', now()->month)
        ->whereYear('payment_date', now()->year)
        ->sum('amount');

        // Revenue by payment method
        $revenueByMethod = Payment::whereHas('user', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })
        ->where('status', 'completed')
        ->selectRaw('payment_method, SUM(amount) as total')
        ->groupBy('payment_method')
        ->get()
        ->map(function($item) {
            return [
                'method' => $item->payment_method ?? 'cash',
                'total' => (float) $item->total,
            ];
        });

        // Daily revenue for current month
        $dailyRevenue = Payment::whereHas('user', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })
        ->where('status', 'completed')
        ->whereMonth('payment_date', now()->month)
        ->whereYear('payment_date', now()->year)
        ->selectRaw('DATE(payment_date) as date, SUM(amount) as revenue')
        ->groupBy('date')
        ->orderBy('date')
        ->get()
        ->map(function($item) {
            return [
                'date' => Carbon::parse($item->date)->format('M d'),
                'revenue' => (float) $item->revenue,
            ];
        });

        return $this->successResponse([
            'stats' => [
                'total_revenue' => (float) $totalRevenue,
                'pending_revenue' => (float) $pendingRevenue,
                'this_month_revenue' => (float) $thisMonthRevenue,
            ],
            'revenue_by_method' => $revenueByMethod,
            'daily_revenue' => $dailyRevenue,
        ], 'Revenue analytics retrieved successfully');
    }

    /**
     * Get attendance analytics
     */
    public function attendance(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if ($user->isStudent()) {
            // Student sees only their attendance
            $totalSessions = $user->attendanceRecords()->count();
            $presentSessions = $user->attendanceRecords()->where('status', 'present')->count();
            $attendanceRate = $totalSessions > 0 ? ($presentSessions / $totalSessions) * 100 : 0;

            return $this->successResponse([
                'user_type' => 'student',
                'stats' => [
                    'total_sessions' => $totalSessions,
                    'present_sessions' => $presentSessions,
                    'attendance_rate' => round($attendanceRate, 1),
                ]
            ], 'Student attendance analytics retrieved');

        } elseif ($user->isAdmin()) {
            // Admin sees school-wide attendance
            $school = $user->school;

            $totalSessions = Attendance::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count();

            $presentSessions = Attendance::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'present')->count();

            $attendanceRate = $totalSessions > 0 ? ($presentSessions / $totalSessions) * 100 : 0;

            // Attendance by course
            $attendanceByCourse = Course::where('school_id', $school->id)
                ->withCount([
                    'attendances as total_sessions',
                    'attendances as present_sessions' => function($q) {
                        $q->where('status', 'present');
                    }
                ])
                ->having('total_sessions', '>', 0)
                ->get()
                ->map(function($course) {
                    $rate = $course->total_sessions > 0
                        ? ($course->present_sessions / $course->total_sessions) * 100
                        : 0;

                    return [
                        'course_name' => $course->name,
                        'total_sessions' => $course->total_sessions,
                        'present_sessions' => $course->present_sessions,
                        'attendance_rate' => round($rate, 1),
                    ];
                });

            return $this->successResponse([
                'user_type' => 'admin',
                'stats' => [
                    'total_sessions' => $totalSessions,
                    'present_sessions' => $presentSessions,
                    'attendance_rate' => round($attendanceRate, 1),
                ],
                'attendance_by_course' => $attendanceByCourse,
            ], 'Admin attendance analytics retrieved');

        } else {
            return $this->forbiddenResponse('Attendance analytics not available for this user role');
        }
    }

    /**
     * Export analytics data for reports
     */
    public function export(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();
        $format = $request->get('format', 'json'); // json, csv
        $period = $request->get('period', 'month');

        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can export analytics data');
        }

        $analytics = $this->getAdminAnalytics($user, $period);
        $data = $analytics->getData(true)['data'];

        if ($format === 'csv') {
            // In a real implementation, you would generate CSV and return download link
            return $this->successResponse([
                'download_url' => '/api/download/analytics-export-' . time() . '.csv',
                'expires_at' => now()->addHours(1)->toISOString(),
            ], 'Analytics export prepared for download');
        }

        return $this->successResponse($data, 'Analytics data exported successfully');
    }

    /**
     * Get date filter based on period
     */
    private function getDateFilter(string $period): Carbon
    {
        return match($period) {
            'day' => now()->subDay(),
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subMonths(3),
            'year' => now()->subYear(),
            default => now()->subMonth(),
        };
    }
}