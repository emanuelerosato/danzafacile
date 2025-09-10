<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Document;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SuperAdminController extends Controller
{
    /**
     * Display Super Admin dashboard with system overview
     */
    public function index()
    {
        $stats = [
            'schools_total' => School::count(),
            'schools_active' => School::where('active', true)->count(),
            'users_total' => User::count(),
            'admins_total' => User::where('role', User::ROLE_ADMIN)->count(),
            'students_total' => User::where('role', User::ROLE_STUDENT)->count(),
            'courses_total' => Course::count(),
            'courses_active' => Course::where('active', true)->count(),
            'payments_total' => Payment::sum('amount'),
            'payments_month' => Payment::whereMonth('payment_date', now()->month)->sum('amount'),
            'documents_pending' => Document::where('status', 'pending')->count(),
        ];

        $recent_schools = School::latest()->take(5)->get();
        $recent_users = User::latest()->take(10)->get();
        $pending_documents = Document::where('status', 'pending')->with('user')->latest()->take(5)->get();

        return view('super-admin.dashboard', compact('stats', 'recent_schools', 'recent_users', 'pending_documents'));
    }

    /**
     * System settings and configuration
     */
    public function settings()
    {
        return view('super-admin.settings');
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'maintenance_mode' => 'boolean',
        ]);

        // Logic to update system settings
        // This could be implemented using a Settings model or configuration files

        return redirect()->back()->with('success', 'Impostazioni sistema aggiornate con successo.');
    }

    /**
     * System logs and activity monitor
     */
    public function logs(Request $request)
    {
        // This would typically integrate with Laravel's logging system
        // or a custom activity log package like spatie/laravel-activitylog
        
        return view('super-admin.logs');
    }

    /**
     * Database backup and maintenance
     */
    public function maintenance()
    {
        return view('super-admin.maintenance');
    }

    /**
     * Generate system reports
     */
    public function reports(Request $request)
    {
        $type = $request->get('type', 'schools');
        $period = $request->get('period', 'month');

        $data = [];

        switch ($type) {
            case 'schools':
                $data = $this->getSchoolsReport($period);
                break;
            case 'users':
                $data = $this->getUsersReport($period);
                break;
            case 'payments':
                $data = $this->getPaymentsReport($period);
                break;
            case 'courses':
                $data = $this->getCoursesReport($period);
                break;
        }

        return view('super-admin.reports', compact('data', 'type', 'period'));
    }

    /**
     * Get schools statistics report
     */
    private function getSchoolsReport($period)
    {
        // Implementation for schools report
        return [
            'total' => School::count(),
            'active' => School::where('active', true)->count(),
            'inactive' => School::where('active', false)->count(),
            'new_this_period' => School::where('created_at', '>=', now()->subMonth())->count(),
        ];
    }

    /**
     * Get users statistics report
     */
    private function getUsersReport($period)
    {
        return [
            'total' => User::count(),
            'admins' => User::where('role', User::ROLE_ADMIN)->count(),
            'instructors' => User::where('role', User::ROLE_INSTRUCTOR)->count(),
            'students' => User::where('role', User::ROLE_STUDENT)->count(),
            'active' => User::where('active', true)->count(),
        ];
    }

    /**
     * Get payments statistics report
     */
    private function getPaymentsReport($period)
    {
        $query = Payment::query();
        
        switch ($period) {
            case 'week':
                $query->where('payment_date', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('payment_date', '>=', now()->subMonth());
                break;
            case 'year':
                $query->where('payment_date', '>=', now()->subYear());
                break;
        }

        return [
            'total_amount' => $query->sum('amount'),
            'total_count' => $query->count(),
            'completed' => $query->where('status', 'completed')->sum('amount'),
            'pending' => $query->where('status', 'pending')->sum('amount'),
        ];
    }

    /**
     * Get courses statistics report
     */
    private function getCoursesReport($period)
    {
        return [
            'total' => Course::count(),
            'active' => Course::where('active', true)->count(),
            'inactive' => Course::where('active', false)->count(),
            'by_level' => Course::selectRaw('difficulty_level, count(*) as count')
                ->groupBy('difficulty_level')
                ->pluck('count', 'difficulty_level'),
        ];
    }

    // API METHODS FOR MOBILE/EXTERNAL ACCESS

    /**
     * Get dashboard statistics for API
     */
    public function getStats(Request $request): JsonResponse
    {
        $stats = [
            'system_overview' => [
                'schools_total' => School::count(),
                'schools_active' => School::where('active', true)->count(),
                'schools_inactive' => School::where('active', false)->count(),
                'users_total' => User::count(),
                'admins_total' => User::where('role', User::ROLE_ADMIN)->count(),
                'instructors_total' => User::where('role', User::ROLE_INSTRUCTOR)->count(),
                'students_total' => User::where('role', User::ROLE_STUDENT)->count(),
                'courses_total' => Course::count(),
                'courses_active' => Course::where('active', true)->count(),
                'enrollments_total' => CourseEnrollment::count(),
                'enrollments_active' => CourseEnrollment::where('status', 'active')->count(),
            ],
            'financial_overview' => [
                'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
                'monthly_revenue' => Payment::where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'),
                'pending_payments' => Payment::where('status', 'pending')->sum('amount'),
                'average_course_price' => Course::avg('price'),
            ],
            'recent_activity' => [
                'new_schools_this_month' => School::whereMonth('created_at', now()->month)->count(),
                'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
                'new_enrollments_this_week' => CourseEnrollment::where('created_at', '>=', now()->subWeek())->count(),
                'pending_documents' => Document::where('status', 'pending')->count(),
            ],
            'growth_metrics' => [
                'school_growth_rate' => $this->calculateGrowthRate('schools'),
                'user_growth_rate' => $this->calculateGrowthRate('users'),
                'revenue_growth_rate' => $this->calculateRevenueGrowthRate(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Calculate growth rate for given entity
     */
    private function calculateGrowthRate($entity): float
    {
        $model = match($entity) {
            'schools' => School::class,
            'users' => User::class,
            default => School::class
        };

        $currentMonth = $model::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $previousMonth = $model::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        if ($previousMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
    }

    /**
     * Calculate revenue growth rate
     */
    private function calculateRevenueGrowthRate(): float
    {
        $currentMonth = Payment::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $previousMonth = Payment::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');

        if ($previousMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
    }

    /**
     * Get detailed analytics data
     */
    public function analytics(Request $request): JsonResponse
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        // Revenue trends
        $revenueTrends = Payment::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Enrollment trends
        $enrollmentTrends = CourseEnrollment::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // School performance
        $schoolPerformance = School::with('courses')
            ->get()
            ->map(function ($school) {
                return [
                    'school_id' => $school->id,
                    'school_name' => $school->name,
                    'total_courses' => $school->courses->count(),
                    'active_courses' => $school->courses->where('active', true)->count(),
                    'total_students' => $school->users()->where('role', User::ROLE_STUDENT)->count(),
                    'total_revenue' => $school->payments()->where('status', 'completed')->sum('amount'),
                ];
            });

        // User distribution by role
        $userDistribution = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->role => $item->count];
            });

        // Course difficulty distribution
        $courseDifficulty = Course::selectRaw('difficulty_level, COUNT(*) as count')
            ->groupBy('difficulty_level')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->difficulty_level => $item->count];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'revenue_trends' => $revenueTrends,
                'enrollment_trends' => $enrollmentTrends,
                'school_performance' => $schoolPerformance,
                'user_distribution' => $userDistribution,
                'course_difficulty_distribution' => $courseDifficulty,
            ]
        ]);
    }

    /**
     * Get system reports in API format
     */
    public function reportsApi(Request $request): JsonResponse
    {
        $type = $request->get('type', 'overview');
        $period = $request->get('period', 'month');

        $reports = [
            'overview' => $this->getOverviewReport($period),
            'schools' => $this->getSchoolsReport($period),
            'users' => $this->getUsersReport($period),
            'payments' => $this->getPaymentsReport($period),
            'courses' => $this->getCoursesReport($period),
        ];

        $data = $reports[$type] ?? $reports['overview'];

        return response()->json([
            'success' => true,
            'data' => [
                'type' => $type,
                'period' => $period,
                'report' => $data,
                'generated_at' => now()
            ]
        ]);
    }

    /**
     * Get overview report
     */
    private function getOverviewReport($period): array
    {
        return [
            'system_health' => [
                'total_schools' => School::count(),
                'active_schools' => School::where('active', true)->count(),
                'total_users' => User::count(),
                'active_users' => User::where('active', true)->count(),
                'total_courses' => Course::count(),
                'active_courses' => Course::where('active', true)->count(),
            ],
            'business_metrics' => [
                'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
                'total_enrollments' => CourseEnrollment::count(),
                'active_enrollments' => CourseEnrollment::where('status', 'active')->count(),
                'average_revenue_per_school' => School::count() > 0 
                    ? round(Payment::where('status', 'completed')->sum('amount') / School::count(), 2) 
                    : 0,
            ]
        ];
    }
}