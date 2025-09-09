<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Course;
use App\Models\School;
use App\Models\User;
use App\Models\CourseEnrollment;

class DatabaseOptimizationService
{
    /**
     * Optimize frequently used queries with proper eager loading
     */
    public function getOptimizedCoursesList(array $filters = [])
    {
        return Course::select([
                'id', 'name', 'description', 'level', 'category', 
                'price', 'duration_weeks', 'max_students', 
                'start_date', 'end_date', 'schedule', 'active', 'school_id'
            ])
            ->with([
                'school:id,name,address,phone',
                'enrollments:id,course_id,status',
            ])
            ->withCount(['enrollments as active_enrollments_count' => function ($query) {
                $query->where('status', 'active');
            }])
            ->active()
            ->when(!empty($filters['school_id']), function ($query) use ($filters) {
                return $query->where('school_id', $filters['school_id']);
            })
            ->when(!empty($filters['level']), function ($query) use ($filters) {
                return $query->where('level', $filters['level']);
            })
            ->when(!empty($filters['category']), function ($query) use ($filters) {
                return $query->where('category', $filters['category']);
            })
            ->when(!empty($filters['available_only']), function ($query) {
                return $query->whereRaw('max_students > (
                    SELECT COUNT(*) FROM course_enrollments 
                    WHERE course_id = courses.id AND status IN ("active", "pending")
                )');
            })
            ->orderBy('start_date', 'asc')
            ->paginate(15);
    }

    /**
     * Get dashboard data with optimized queries
     */
    public function getDashboardData(User $user): array
    {
        switch ($user->role) {
            case 'super_admin':
                return $this->getSuperAdminDashboard();
            
            case 'admin':
                return $this->getAdminDashboard($user->school_id);
            
            default:
                return $this->getStudentDashboard($user->id);
        }
    }

    private function getSuperAdminDashboard(): array
    {
        return DB::transaction(function () {
            // Use raw queries for better performance on large datasets
            $stats = DB::select("
                SELECT 
                    (SELECT COUNT(*) FROM schools WHERE active = 1) as active_schools,
                    (SELECT COUNT(*) FROM schools) as total_schools,
                    (SELECT COUNT(*) FROM users) as total_users,
                    (SELECT COUNT(*) FROM courses WHERE active = 1) as active_courses,
                    (SELECT COUNT(*) FROM users WHERE created_at >= ?) as recent_users,
                    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE status = 'completed' AND created_at >= ?) as monthly_revenue
            ", [now()->subDays(30), now()->subMonth()]);

            $recentSchools = School::select('id', 'name', 'email', 'created_at')
                                  ->latest()
                                  ->limit(5)
                                  ->get();

            return [
                'stats' => $stats[0] ?? [],
                'recent_schools' => $recentSchools,
            ];
        });
    }

    private function getAdminDashboard(int $schoolId): array
    {
        return DB::transaction(function () use ($schoolId) {
            // Optimized queries for school admin dashboard
            $stats = DB::select("
                SELECT 
                    (SELECT COUNT(*) FROM users WHERE school_id = ? AND role = 'user') as total_students,
                    (SELECT COUNT(*) FROM courses WHERE school_id = ? AND active = 1) as active_courses,
                    (SELECT COUNT(*) FROM course_enrollments ce 
                     JOIN courses c ON ce.course_id = c.id 
                     WHERE c.school_id = ? AND ce.status = 'pending') as pending_enrollments,
                    (SELECT COALESCE(SUM(amount), 0) FROM payments p 
                     JOIN users u ON p.user_id = u.id 
                     WHERE u.school_id = ? AND p.status = 'completed' AND p.created_at >= ?) as monthly_revenue,
                    (SELECT COUNT(*) FROM course_enrollments ce 
                     JOIN courses c ON ce.course_id = c.id 
                     WHERE c.school_id = ? AND ce.created_at >= ?) as recent_enrollments
            ", [$schoolId, $schoolId, $schoolId, $schoolId, now()->subMonth(), $schoolId, now()->subWeek()]);

            $recentEnrollments = DB::select("
                SELECT ce.id, ce.created_at, ce.status,
                       c.name as course_name,
                       u.first_name, u.last_name, u.email
                FROM course_enrollments ce
                JOIN courses c ON ce.course_id = c.id
                JOIN users u ON ce.user_id = u.id
                WHERE c.school_id = ?
                ORDER BY ce.created_at DESC
                LIMIT 10
            ", [$schoolId]);

            return [
                'stats' => $stats[0] ?? [],
                'recent_enrollments' => $recentEnrollments,
            ];
        });
    }

    private function getStudentDashboard(int $userId): array
    {
        return DB::transaction(function () use ($userId) {
            // Optimized query for student dashboard
            $stats = DB::select("
                SELECT 
                    (SELECT COUNT(*) FROM course_enrollments WHERE user_id = ? AND status = 'active') as active_enrollments,
                    (SELECT COUNT(*) FROM course_enrollments WHERE user_id = ? AND status = 'completed') as completed_courses,
                    (SELECT COALESCE(SUM(amount), 0) FROM payments WHERE user_id = ? AND status = 'completed') as total_spent,
                    (SELECT COUNT(*) FROM payments WHERE user_id = ? AND status = 'pending') as pending_payments
            ", [$userId, $userId, $userId, $userId]);

            $myCourses = DB::select("
                SELECT c.id, c.name, c.start_date, c.end_date, c.schedule,
                       ce.status, ce.enrolled_at,
                       s.name as school_name
                FROM course_enrollments ce
                JOIN courses c ON ce.course_id = c.id
                JOIN schools s ON c.school_id = s.id
                WHERE ce.user_id = ? AND ce.status IN ('active', 'pending')
                ORDER BY c.start_date ASC
                LIMIT 5
            ", [$userId]);

            return [
                'stats' => $stats[0] ?? [],
                'my_courses' => $myCourses,
            ];
        });
    }

    /**
     * Optimize enrollment queries with batch operations
     */
    public function getSchoolEnrollmentSummary(int $schoolId): array
    {
        return DB::select("
            SELECT 
                c.id as course_id,
                c.name as course_name,
                c.max_students,
                COUNT(CASE WHEN ce.status = 'active' THEN 1 END) as active_students,
                COUNT(CASE WHEN ce.status = 'pending' THEN 1 END) as pending_students,
                COUNT(CASE WHEN ce.status = 'waitlist' THEN 1 END) as waitlist_students,
                (c.max_students - COUNT(CASE WHEN ce.status IN ('active', 'pending') THEN 1 END)) as available_spots
            FROM courses c
            LEFT JOIN course_enrollments ce ON c.id = ce.course_id
            WHERE c.school_id = ? AND c.active = 1
            GROUP BY c.id, c.name, c.max_students
            ORDER BY c.start_date ASC
        ", [$schoolId]);
    }

    /**
     * Get revenue analytics with optimized aggregations
     */
    public function getRevenueAnalytics(int $schoolId, int $months = 12): array
    {
        $monthlyRevenue = DB::select("
            SELECT 
                DATE_FORMAT(p.created_at, '%Y-%m') as month,
                COUNT(p.id) as payment_count,
                SUM(p.amount) as total_revenue,
                AVG(p.amount) as avg_payment
            FROM payments p
            JOIN users u ON p.user_id = u.id
            WHERE u.school_id = ? 
            AND p.status = 'completed'
            AND p.created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(p.created_at, '%Y-%m')
            ORDER BY month ASC
        ", [$schoolId, $months]);

        $courseRevenue = DB::select("
            SELECT 
                c.name as course_name,
                COUNT(p.id) as enrollments,
                SUM(p.amount) as revenue,
                AVG(p.amount) as avg_price
            FROM payments p
            JOIN users u ON p.user_id = u.id
            JOIN course_enrollments ce ON ce.user_id = u.id
            JOIN courses c ON ce.course_id = c.id
            WHERE c.school_id = ?
            AND p.status = 'completed'
            AND p.created_at >= DATE_SUB(NOW(), INTERVAL ? MONTH)
            GROUP BY c.id, c.name
            ORDER BY revenue DESC
            LIMIT 10
        ", [$schoolId, $months]);

        return [
            'monthly_revenue' => $monthlyRevenue,
            'course_revenue' => $courseRevenue,
        ];
    }

    /**
     * Bulk operations for better performance
     */
    public function bulkUpdateEnrollmentStatus(array $enrollmentIds, string $status): bool
    {
        try {
            DB::beginTransaction();
            
            CourseEnrollment::whereIn('id', $enrollmentIds)
                          ->update([
                              'status' => $status,
                              'updated_at' => now()
                          ]);
            
            // Log the bulk operation
            Log::info("Bulk updated enrollment status", [
                'enrollment_ids' => $enrollmentIds,
                'new_status' => $status,
                'count' => count($enrollmentIds)
            ]);
            
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Bulk enrollment update failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Database health check and optimization suggestions
     */
    public function getDatabaseHealth(): array
    {
        $health = [
            'status' => 'healthy',
            'issues' => [],
            'suggestions' => []
        ];

        // Check for slow queries (this would need to be enabled in MySQL)
        $slowQueries = DB::select("SHOW GLOBAL STATUS LIKE 'Slow_queries'");
        if (!empty($slowQueries) && $slowQueries[0]->Value > 0) {
            $health['issues'][] = "Found {$slowQueries[0]->Value} slow queries";
            $health['suggestions'][] = "Consider optimizing slow queries or adjusting slow_query_log_time";
        }

        // Check table sizes
        $largeTables = DB::select("
            SELECT table_name, 
                   ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
            FROM information_schema.tables 
            WHERE table_schema = DATABASE()
            AND ((data_length + index_length) / 1024 / 1024) > 100
            ORDER BY size_mb DESC
        ");

        if (!empty($largeTables)) {
            $health['issues'][] = "Large tables detected: " . count($largeTables) . " tables over 100MB";
            $health['suggestions'][] = "Consider archiving old data or implementing table partitioning";
        }

        return $health;
    }

    /**
     * Clean up old data to improve performance
     */
    public function cleanupOldData(): array
    {
        $cleaned = [];

        // Clean up old completed enrollments (older than 2 years)
        $oldEnrollments = CourseEnrollment::where('status', 'completed')
                                        ->where('updated_at', '<', now()->subYears(2))
                                        ->count();
        
        if ($oldEnrollments > 0) {
            // Archive instead of delete
            DB::table('archived_enrollments')->insertUsing([
                'user_id', 'course_id', 'status', 'enrolled_at', 
                'completed_at', 'created_at', 'updated_at', 'archived_at'
            ], function ($query) {
                $query->select([
                    'user_id', 'course_id', 'status', 'enrolled_at',
                    'completed_at', 'created_at', 'updated_at', DB::raw('NOW()')
                ])
                ->from('course_enrollments')
                ->where('status', 'completed')
                ->where('updated_at', '<', now()->subYears(2));
            });

            CourseEnrollment::where('status', 'completed')
                          ->where('updated_at', '<', now()->subYears(2))
                          ->delete();

            $cleaned['archived_enrollments'] = $oldEnrollments;
        }

        return $cleaned;
    }
}