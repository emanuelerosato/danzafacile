<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use App\Models\School;
use App\Models\Course;
use App\Models\User;

class CacheService
{
    // Cache durations in minutes
    private const CACHE_DASHBOARD_STATS = 15; // 15 minutes
    private const CACHE_COURSE_LIST = 60; // 1 hour
    private const CACHE_SCHOOL_DATA = 30; // 30 minutes
    private const CACHE_USER_PROFILE = 60; // 1 hour
    private const CACHE_MENU_DATA = 120; // 2 hours

    /**
     * Get or cache dashboard statistics
     */
    public function getDashboardStats(int $userId, string $role): array
    {
        $key = "dashboard_stats_{$role}_{$userId}";
        
        return Cache::remember($key, self::CACHE_DASHBOARD_STATS, function () use ($userId, $role) {
            $user = User::find($userId);
            
            switch ($role) {
                case 'super_admin':
                    return [
                        'total_schools' => School::count(),
                        'total_users' => User::count(),
                        'total_courses' => Course::count(),
                        'active_schools' => School::active()->count(),
                        'recent_registrations' => User::where('created_at', '>=', now()->subDays(7))->count(),
                    ];
                
                case 'admin':
                    $school = $user->school;
                    return [
                        'total_students' => $school->students()->count(),
                        'total_courses' => $school->courses()->count(),
                        'active_courses' => $school->courses()->active()->count(),
                        'monthly_revenue' => $school->payments()
                            ->completed()
                            ->whereMonth('created_at', now()->month)
                            ->sum('amount'),
                        'pending_enrollments' => $school->courses()
                            ->with('enrollments')
                            ->get()
                            ->pluck('enrollments')
                            ->flatten()
                            ->where('status', 'pending')
                            ->count(),
                    ];
                
                default: // student
                    return [
                        'my_courses' => $user->courseEnrollments()->active()->count(),
                        'completed_courses' => $user->courseEnrollments()
                            ->where('status', 'completed')
                            ->count(),
                        'upcoming_classes' => $user->courseEnrollments()
                            ->with('course')
                            ->get()
                            ->filter(fn($enrollment) => 
                                $enrollment->course->start_date && 
                                $enrollment->course->start_date->isFuture()
                            )
                            ->count(),
                        'total_spent' => $user->payments()->completed()->sum('amount'),
                    ];
            }
        });
    }

    /**
     * Cache course list with filters
     */
    public function getCourseList(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        $key = 'courses_list_' . md5(serialize($filters));
        
        return Cache::remember($key, self::CACHE_COURSE_LIST, function () use ($filters) {
            $query = Course::with(['school', 'enrollments'])
                          ->active();

            if (!empty($filters['school_id'])) {
                $query->where('school_id', $filters['school_id']);
            }

            if (!empty($filters['level'])) {
                $query->where('level', $filters['level']);
            }

            if (!empty($filters['category'])) {
                $query->where('category', $filters['category']);
            }

            return $query->paginate(12);
        });
    }

    /**
     * Cache school data
     */
    public function getSchoolData(int $schoolId): ?School
    {
        $key = "school_data_{$schoolId}";
        
        return Cache::remember($key, self::CACHE_SCHOOL_DATA, function () use ($schoolId) {
            return School::with(['users', 'courses.enrollments'])
                        ->find($schoolId);
        });
    }

    /**
     * Cache user profile data
     */
    public function getUserProfile(int $userId): ?User
    {
        $key = "user_profile_{$userId}";
        
        return Cache::remember($key, self::CACHE_USER_PROFILE, function () use ($userId) {
            return User::with(['school', 'courseEnrollments.course', 'payments'])
                      ->find($userId);
        });
    }

    /**
     * Cache navigation menu data
     */
    public function getMenuData(User $user): array
    {
        $key = "menu_data_{$user->role}_{$user->id}";
        
        return Cache::remember($key, self::CACHE_MENU_DATA, function () use ($user) {
            $menu = [];
            
            switch ($user->role) {
                case 'super_admin':
                    $menu = [
                        'dashboard' => ['icon' => 'fas fa-tachometer-alt', 'url' => route('super-admin.dashboard')],
                        'schools' => ['icon' => 'fas fa-school', 'url' => route('super-admin.schools.index')],
                        'users' => ['icon' => 'fas fa-users', 'url' => route('super-admin.users.index')],
                    ];
                    break;
                
                case 'admin':
                    $menu = [
                        'dashboard' => ['icon' => 'fas fa-tachometer-alt', 'url' => route('admin.dashboard')],
                        'courses' => ['icon' => 'fas fa-graduation-cap', 'url' => route('admin.courses.index')],
                        'students' => ['icon' => 'fas fa-user-graduate', 'url' => route('admin.users.index')],
                        'enrollments' => ['icon' => 'fas fa-list-alt', 'url' => route('admin.enrollments.index')],
                        'payments' => ['icon' => 'fas fa-credit-card', 'url' => route('admin.payments.index')],
                    ];
                    break;
                
                default: // student
                    $menu = [
                        'dashboard' => ['icon' => 'fas fa-tachometer-alt', 'url' => route('student.dashboard')],
                        'courses' => ['icon' => 'fas fa-graduation-cap', 'url' => route('student.courses.index')],
                        'my-courses' => ['icon' => 'fas fa-bookmark', 'url' => route('student.my-courses')],
                        'documents' => ['icon' => 'fas fa-file-alt', 'url' => route('documents.index')],
                        'media' => ['icon' => 'fas fa-images', 'url' => route('media.index')],
                    ];
                    break;
            }
            
            return $menu;
        });
    }

    /**
     * Invalidate specific cache keys
     */
    public function invalidateUserCache(int $userId): void
    {
        $patterns = [
            "user_profile_{$userId}",
            "dashboard_stats_*_{$userId}",
            "menu_data_*_{$userId}",
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                $this->invalidateByPattern($pattern);
            } else {
                Cache::forget($pattern);
            }
        }
    }

    /**
     * Invalidate school-related caches
     */
    public function invalidateSchoolCache(int $schoolId): void
    {
        Cache::forget("school_data_{$schoolId}");
        Cache::forget('courses_list_*');
        
        // Invalidate dashboard stats for school admins
        $adminIds = User::where('school_id', $schoolId)
                       ->where('role', 'admin')
                       ->pluck('id');
        
        foreach ($adminIds as $adminId) {
            Cache::forget("dashboard_stats_admin_{$adminId}");
        }
    }

    /**
     * Invalidate course-related caches
     */
    public function invalidateCourseCache(): void
    {
        $this->invalidateByPattern('courses_list_*');
        $this->invalidateByPattern('dashboard_stats_*');
    }

    /**
     * Store frequently accessed data in Redis for ultra-fast retrieval
     */
    public function setHotData(string $key, $value, int $seconds = 300): void
    {
        Redis::setex("hot:{$key}", $seconds, json_encode($value));
    }

    /**
     * Get hot data from Redis
     */
    public function getHotData(string $key)
    {
        $data = Redis::get("hot:{$key}");
        return $data ? json_decode($data, true) : null;
    }

    /**
     * Cache expensive query results
     */
    public function cacheExpensiveQuery(string $key, callable $callback, int $minutes = 60)
    {
        return Cache::remember($key, $minutes, $callback);
    }

    /**
     * Warm up cache with commonly accessed data
     */
    public function warmUpCache(): void
    {
        // Cache active schools
        $this->cacheExpensiveQuery('active_schools', function () {
            return School::active()->with('courses')->get();
        }, 120);

        // Cache popular courses
        $this->cacheExpensiveQuery('popular_courses', function () {
            return Course::active()
                        ->withCount('enrollments')
                        ->having('enrollments_count', '>', 10)
                        ->orderByDesc('enrollments_count')
                        ->limit(10)
                        ->get();
        }, 60);

        // Cache system statistics
        $this->cacheExpensiveQuery('system_stats', function () {
            return [
                'total_users' => User::count(),
                'total_schools' => School::count(),
                'total_courses' => Course::count(),
                'total_enrollments' => \DB::table('course_enrollments')->count(),
            ];
        }, 30);
    }

    /**
     * Clear all application caches
     */
    public function clearAllCaches(): void
    {
        Cache::flush();
        Redis::flushdb();
    }

    // PRIVATE METHODS

    private function invalidateByPattern(string $pattern): void
    {
        if (config('cache.default') === 'redis') {
            $keys = Redis::keys($pattern);
            if (!empty($keys)) {
                Redis::del($keys);
            }
        } else {
            // For other cache drivers, we can't easily pattern match
            // So we'll just flush the entire cache
            Cache::flush();
        }
    }
}