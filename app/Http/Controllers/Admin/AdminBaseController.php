<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Helpers\QueryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

abstract class AdminBaseController extends Controller
{
    private $school;
    private $user;

    public function __construct()
    {
        // Initialize context will be set up when needed
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
     * Call this method at the beginning of every controller action
     */
    protected function setupContext(): void
    {
        $this->initializeSchoolContext();
    }

    /**
     * Get school property with lazy initialization
     */
    public function getSchoolProperty()
    {
        if (!$this->school) {
            $this->initializeSchoolContext();
        }
        return $this->school;
    }

    /**
     * Get user property with lazy initialization
     */
    public function getUserProperty()
    {
        if (!$this->user) {
            $this->initializeSchoolContext();
        }
        return $this->user;
    }

    /**
     * Magic getter to auto-initialize context when accessed
     */
    public function __get($property)
    {
        if ($property === 'school') {
            if (!$this->school) {
                $this->initializeSchoolContext();
            }
            return $this->school;
        }

        if ($property === 'user') {
            if (!$this->user) {
                $this->initializeSchoolContext();
            }
            return $this->user;
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
                'total_students' => $this->school->users()->where('role', 'student')->count(),
                'active_students' => $this->school->users()->where('role', 'student')->where('active', true)->count(),
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
     * Verifica che un model appartenga alla scuola corrente
     */
    protected function verifyResourceOwnership($model, string $resourceName = 'Resource'): void
    {
        $this->setupContext();

        if (!$model) {
            abort(404, $resourceName . ' non trovato.');
        }

        // Verifica ownership diretta tramite school_id
        if (property_exists($model, 'school_id') && $model->school_id !== $this->school->id) {
            abort(404, $resourceName . ' non trovato o accesso negato.');
        }

        // Verifica ownership tramite relazione user per model senza school_id
        if (!property_exists($model, 'school_id') && method_exists($model, 'user')) {
            $user = $model->user;
            if (!$user || $user->school_id !== $this->school->id) {
                abort(404, $resourceName . ' non trovato o accesso negato.');
            }
        }
    }

    /**
     * Trova un model in modo sicuro bypassando il global scope
     */
    protected function findResourceSecurely(string $modelClass, $id, string $resourceName = 'Resource')
    {
        $model = $modelClass::withoutGlobalScopes()->find($id);
        $this->verifyResourceOwnership($model, $resourceName);
        return $model;
    }

    /**
     * Response helper methods per consistenza
     */
    protected function successResponse(string $message = 'Operazione completata con successo', array $data = []): \Illuminate\Http\JsonResponse
    {
        return $this->jsonResponse(true, $message, $data, 200);
    }

    protected function errorResponse(string $message = 'Si è verificato un errore', array $errors = [], int $status = 422): \Illuminate\Http\JsonResponse
    {
        return $this->jsonResponse(false, $message, ['errors' => $errors], $status);
    }

    protected function notFoundResponse(string $message = 'Risorsa non trovata'): \Illuminate\Http\JsonResponse
    {
        return $this->jsonResponse(false, $message, [], 404);
    }

    protected function unauthorizedResponse(string $message = 'Accesso non autorizzato'): \Illuminate\Http\JsonResponse
    {
        return $this->jsonResponse(false, $message, [], 403);
    }

    /**
     * Handle bulk actions with proper validation
     * SECURITY FIX: Multi-tenant isolation - solo risorse della propria scuola
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

        // SECURITY: Ensure school context is initialized
        $this->setupContext();

        try {
            switch ($action) {
                case 'activate':
                    // SECURITY FIX: Multi-tenant isolation
                    $affected = $model::whereIn('id', $ids)
                        ->where('school_id', $this->school->id)
                        ->update(['active' => true]);
                    $message = "Elementi attivati con successo ($affected).";
                    break;

                case 'deactivate':
                    // SECURITY FIX: Multi-tenant isolation
                    $affected = $model::whereIn('id', $ids)
                        ->where('school_id', $this->school->id)
                        ->update(['active' => false]);
                    $message = "Elementi disattivati con successo ($affected).";
                    break;

                case 'delete':
                    // SECURITY FIX: Multi-tenant isolation
                    $affected = $model::whereIn('id', $ids)
                        ->where('school_id', $this->school->id)
                        ->delete();
                    $message = "Elementi eliminati con successo ($affected).";
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
    protected function getFilteredResults($query, Request $request, ?int $perPage = 15, array $allowedSortFields = ['created_at', 'updated_at'])
    {
        // Apply search - SECURE: sanitized via applySearch method
        if ($request->filled('search')) {
            $searchTerm = QueryHelper::sanitizeLikeInput($request->get('search'));
            if (!empty($searchTerm)) {
                $query = $this->applySearch($query, $searchTerm);
            }
        }

        // Apply filters
        if ($request->filled('status')) {
            $query->where('active', $request->get('status') === 'active');
        }

        // Apply sorting - SECURE: validated against whitelist
        $query = QueryHelper::applySafeSort(
            $query,
            $request->get('sort'),
            $request->get('direction'),
            $allowedSortFields,
            'created_at',
            'desc'
        );

        // Pagination - SECURE: validated per_page (null = get all without pagination)
        if ($perPage === null) {
            return $query->get();
        }

        $validatedPerPage = QueryHelper::validatePerPage($perPage, 15, 100);
        return $query->paginate($validatedPerPage);
    }

    /**
     * Apply search to query (to be overridden in child controllers)
     */
    protected function applySearch($query, string $searchTerm)
    {
        return $query;
    }
}