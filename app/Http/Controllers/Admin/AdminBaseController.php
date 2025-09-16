<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

abstract class AdminBaseController extends Controller
{
    protected $school;
    protected $user;

    public function __construct()
    {
        // Auto-initialize school context on first access
        $this->middleware(function ($request, $next) {
            if (auth()->check()) {
                $this->user = auth()->user();
                $this->school = $this->user->school;

                if ($this->school) {
                    view()->share('currentSchool', $this->school);
                }
                view()->share('currentUser', $this->user);
            }
            return $next($request);
        });
    }

    /**
     * Initialize school context for the current request
     */
    protected function initializeSchoolContext(): void
    {
        if (!$this->user) {
            $this->user = Auth::user();
        }
        if (!$this->school && $this->user) {
            $this->school = $this->user->school ?? null;
        }

        // Share school context with all views
        if ($this->school) {
            view()->share('currentSchool', $this->school);
        }
        view()->share('currentUser', $this->user);
    }

    /**
     * Get the school property, auto-initializing if needed
     */
    protected function getSchool()
    {
        if (!$this->school) {
            $this->initializeSchoolContext();
        }
        return $this->school;
    }

    /**
     * Get the user property, auto-initializing if needed
     */
    protected function getUser()
    {
        if (!$this->user) {
            $this->initializeSchoolContext();
        }
        return $this->user;
    }

    /**
     * Magic getter to auto-initialize context
     */
    public function __get($property)
    {
        if ($property === 'school') {
            return $this->getSchool();
        }

        if ($property === 'user') {
            return $this->getUser();
        }

        return null;
    }

    /**
     * Get school statistics for dashboard cards
     */
    protected function getSchoolStats(): array
    {
        $cacheKey = "school_stats_{$this->school->id}";

        return Cache::remember($cacheKey, 300, function () { // 5 minutes cache
            return [
                'total_students' => $this->school->users()->where('role', 'user')->count(),
                'active_students' => $this->school->users()->where('role', 'user')->where('active', true)->count(),
                'total_courses' => $this->school->courses()->count(),
                'active_courses' => $this->school->courses()->where('active', true)->count(),
                'total_events' => $this->school->events()->count(),
                'upcoming_events' => $this->school->events()->where('start_date', '>', now())->count(),
                'total_payments' => $this->school->payments()->count(),
                'completed_payments' => $this->school->payments()->where('status', 'completed')->count(),
                'pending_payments' => $this->school->payments()->where('status', 'pending')->count(),
                'monthly_revenue' => $this->school->payments()
                    ->where('status', 'completed')
                    ->whereMonth('payment_date', now()->month)
                    ->whereYear('payment_date', now()->year)
                    ->sum('amount'),
                'total_revenue' => $this->school->payments()
                    ->where('status', 'completed')
                    ->sum('amount'),
                'staff_count' => $this->school->staffRoles()->where('active', true)->count(),
                'instructors_count' => $this->school->staffRoles()
                    ->where('active', true)
                    ->where('role_name', 'Istruttore')
                    ->count()
            ];
        });
    }

    /**
     * Get analytics data for charts
     */
    protected function getAnalyticsData(): array
    {
        $cacheKey = "school_analytics_{$this->school->id}";

        return Cache::remember($cacheKey, 600, function () { // 10 minutes cache
            // Monthly revenue for last 12 months
            $monthlyRevenue = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $revenue = $this->school->payments()
                    ->where('status', 'completed')
                    ->whereYear('payment_date', $date->year)
                    ->whereMonth('payment_date', $date->month)
                    ->sum('amount');

                $monthlyRevenue[] = [
                    'month' => $date->format('M Y'),
                    'revenue' => (float) $revenue
                ];
            }

            // Student enrollment trends for last 12 months
            $enrollmentTrends = [];
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $enrollments = $this->school->courseEnrollments()
                    ->whereYear('enrollment_date', $date->year)
                    ->whereMonth('enrollment_date', $date->month)
                    ->count();

                $enrollmentTrends[] = [
                    'month' => $date->format('M Y'),
                    'enrollments' => $enrollments
                ];
            }

            // Course popularity
            $coursePopularity = $this->school->courses()
                ->withCount('enrollments')
                ->orderBy('enrollments_count', 'desc')
                ->limit(10)
                ->get(['name', 'enrollments_count'])
                ->map(function ($course) {
                    return [
                        'name' => $course->name,
                        'enrollments' => $course->enrollments_count
                    ];
                });

            // Attendance rates by course (last 30 days)
            $attendanceRates = $this->school->courses()
                ->with(['attendance' => function ($query) {
                    $query->where('date', '>=', now()->subDays(30));
                }])
                ->get()
                ->map(function ($course) {
                    $totalSessions = $course->attendance->count();
                    $presentSessions = $course->attendance->where('status', 'present')->count();

                    return [
                        'course' => $course->name,
                        'rate' => $totalSessions > 0 ? round(($presentSessions / $totalSessions) * 100, 1) : 0
                    ];
                })
                ->sortByDesc('rate')
                ->take(10)
                ->values();

            return [
                'monthly_revenue' => $monthlyRevenue,
                'enrollment_trends' => $enrollmentTrends,
                'course_popularity' => $coursePopularity,
                'attendance_rates' => $attendanceRates
            ];
        });
    }

    /**
     * Standard JSON response format
     */
    protected function jsonResponse($success = true, $message = '', $data = [], $status = 200): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    /**
     * Handle bulk actions with proper validation
     */
    protected function handleBulkAction(Request $request, $model, array $allowedActions = ['activate', 'deactivate', 'delete']): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'action' => 'required|in:' . implode(',', $allowedActions),
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:' . (new $model)->getTable() . ',id'
        ]);

        $action = $request->get('action');
        $ids = $request->get('ids');

        try {
            switch ($action) {
                case 'activate':
                    $model::whereIn('id', $ids)->update(['active' => true]);
                    $message = 'Elementi attivati con successo.';
                    break;

                case 'deactivate':
                    $model::whereIn('id', $ids)->update(['active' => false]);
                    $message = 'Elementi disattivati con successo.';
                    break;

                case 'delete':
                    $model::whereIn('id', $ids)->delete();
                    $message = 'Elementi eliminati con successo.';
                    break;

                default:
                    return $this->jsonResponse(false, 'Azione non supportata.', [], 400);
            }

            // Clear related cache
            $this->clearSchoolCache();

            return $this->jsonResponse(true, $message);

        } catch (\Exception $e) {
            \Log::error('Bulk action failed: ' . $e->getMessage());
            return $this->jsonResponse(false, 'Errore durante l\'operazione.', [], 500);
        }
    }

    /**
     * Clear school-related cache
     */
    protected function clearSchoolCache(): void
    {
        Cache::forget("school_stats_{$this->school->id}");
        Cache::forget("school_analytics_{$this->school->id}");
    }

    /**
     * Export data to CSV
     */
    protected function exportToCsv($data, $headers, $filename = null): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $filename = $filename ?: 'export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $response = new \Symfony\Component\HttpFoundation\StreamedResponse();
        $response->setCallback(function() use ($data, $headers) {
            $file = fopen('php://output', 'w');

            // Add BOM for UTF-8 Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Write headers
            fputcsv($file, $headers);

            // Write data
            foreach ($data as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }

    /**
     * Get filtered and paginated results
     */
    protected function getFilteredResults($query, Request $request, int $perPage = 15)
    {
        // Apply search
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query = $this->applySearch($query, $searchTerm);
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('active', $request->get('status') === 'active');
        }

        // Apply sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Apply search to query (to be overridden in child controllers)
     */
    protected function applySearch($query, string $searchTerm)
    {
        return $query;
    }
}