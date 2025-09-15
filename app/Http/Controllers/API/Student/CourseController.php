<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\API\BaseApiController;
use App\Models\Course;
use App\Models\CourseEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CourseController extends BaseApiController
{

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $query = Course::where('school_id', $schoolId)
            ->where('active', true)
            ->with(['instructor:id,name'])
            ->withCount(['enrollments' => function($q) {
                $q->where('status', 'active');
            }]);

        // Filtering
        if ($request->has('difficulty_level')) {
            $query->where('difficulty_level', $request->get('difficulty_level'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('instructor', 'like', "%{$search}%");
            });
        }

        if ($request->has('available_only')) {
            $query->whereRaw('(SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = courses.id AND ce.status = "active") < courses.max_students');
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'start_date');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $courses = $query->paginate($perPage);

        // Add enrollment status for current user
        $coursesWithStatus = $courses->getCollection()->map(function($course) use ($user) {
            $enrollment = CourseEnrollment::where('course_id', $course->id)
                ->where('user_id', $user->id)
                ->first();

            $courseData = [
                'id' => $course->id,
                'name' => $course->name,
                'description' => $course->description,
                'instructor_name' => $course->instructor ? $course->instructor->name : 'TBD',
                'price' => $course->price,
                'schedule' => $course->schedule,
                'available_spots' => max(0, $course->max_students - $course->enrollments_count),
                'can_enroll' => $this->canUserEnroll($user, $course, $enrollment),
                'user_enrollment_status' => $enrollment ? $enrollment->status : 'not_enrolled',
                'is_enrolled' => (bool) $enrollment,
                'is_full' => $course->enrollments_count >= $course->max_students,
            ];

            return $courseData;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'courses' => $coursesWithStatus,
                'pagination' => [
                    'current_page' => $courses->currentPage(),
                    'last_page' => $courses->lastPage(),
                    'per_page' => $courses->perPage(),
                    'total' => $courses->total(),
                    'from' => $courses->firstItem(),
                    'to' => $courses->lastItem(),
                ]
            ]
        ]);
    }

    public function show(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        // Check if course belongs to student's school
        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found'
            ], 404);
        }

        // Load instructor relationship
        $course->load(['instructor:id,name']);

        // Check user's enrollment status
        $enrollment = CourseEnrollment::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->first();

        // Get course statistics
        $activeEnrollments = CourseEnrollment::where('course_id', $course->id)
            ->where('status', 'active')
            ->count();

        $courseData = [
            'id' => $course->id,
            'name' => $course->name,
            'description' => $course->description,
            'instructor_name' => $course->instructor ? $course->instructor->name : 'TBD',
            'schedule' => $course->schedule,
            'max_students' => $course->max_students,
            'price' => $course->price,
            'level' => $course->level,
            'start_date' => $course->start_date,
            'end_date' => $course->end_date,
            'active' => $course->active,
            'location' => $course->location ?? null,
            'created_at' => $course->created_at,
            'updated_at' => $course->updated_at,

            // Enrollment info
            'user_enrollment_status' => $enrollment ? $enrollment->status : 'not_enrolled',
            'is_enrolled' => (bool) $enrollment,
            'enrollment_date' => $enrollment ? $enrollment->enrollment_date : null,

            // Availability info
            'enrolled_students' => $activeEnrollments,
            'available_spots' => max(0, $course->max_students - $activeEnrollments),
            'is_full' => $activeEnrollments >= $course->max_students,
            'enrollment_open' => $course->active && $activeEnrollments < $course->max_students,

            // Additional info
            'can_enroll' => $this->canUserEnroll($user, $course, $enrollment),
            'enrollment_deadline' => $course->start_date->subDays(1)->format('Y-m-d'),
            'is_past_deadline' => now() > $course->start_date->subDays(1),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'course' => $courseData
            ]
        ]);
    }

    public function enrolled(Request $request): JsonResponse
    {
        $user = $request->user();

        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->with(['course' => function($query) {
                $query->select('id', 'name', 'description', 'instructor_id', 'schedule', 'price', 'start_date', 'end_date', 'active')
                      ->with(['instructor:id,name']);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Group by status
        $groupedEnrollments = [
            'active' => $enrollments->where('status', 'active')->values(),
            'completed' => $enrollments->where('status', 'completed')->values(),
            'cancelled' => $enrollments->where('status', 'cancelled')->values(),
            'pending' => $enrollments->where('status', 'pending')->values(),
        ];

        $stats = [
            'total_enrollments' => $enrollments->count(),
            'active_courses' => $enrollments->where('status', 'active')->count(),
            'completed_courses' => $enrollments->where('status', 'completed')->count(),
            'cancelled_courses' => $enrollments->where('status', 'cancelled')->count(),
            'completion_rate' => $enrollments->count() > 0 
                ? round(($enrollments->where('status', 'completed')->count() / $enrollments->count()) * 100, 2)
                : 0
        ];

        // Transform enrollments to match expected format
        $enrolledCoursesData = $enrollments->map(function($enrollment) {
            return [
                'id' => $enrollment->id,
                'course_name' => $enrollment->course->name,
                'instructor_name' => $enrollment->course->instructor ? $enrollment->course->instructor->name : 'TBD',
                'schedule' => $enrollment->course->schedule,
                'enrollment_date' => $enrollment->enrollment_date,
                'status' => $enrollment->status,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'enrolled_courses' => $enrolledCoursesData,
                'stats' => $stats
            ]
        ]);
    }

    public function recommendations(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        // Get user's completed courses to understand preferences
        $completedCourses = CourseEnrollment::where('user_id', $user->id)
            ->where('status', 'completed')
            ->with('course')
            ->get()
            ->pluck('course');

        // Get user's preferred difficulty levels
        $preferredDifficulties = $completedCourses->pluck('difficulty_level')->unique();

        // Build recommendation query
        $query = Course::where('school_id', $schoolId)
            ->where('active', true)
            ->where('start_date', '>', now())
            ->whereNotIn('id', function($subQuery) use ($user) {
                $subQuery->select('course_id')
                         ->from('course_enrollments')
                         ->where('user_id', $user->id)
                         ->whereIn('status', ['active', 'pending']);
            })
            ->withCount(['enrollments' => function($q) {
                $q->where('status', 'active');
            }]);

        // Priority 1: Courses with preferred difficulty levels
        if ($preferredDifficulties->isNotEmpty()) {
            $query->orderByRaw("CASE WHEN difficulty_level IN ('" . implode("','", $preferredDifficulties->toArray()) . "') THEN 0 ELSE 1 END");
        }

        // Priority 2: Courses with available spots
        $query->orderByRaw('(max_students - (SELECT COUNT(*) FROM course_enrollments ce WHERE ce.course_id = courses.id AND ce.status = "active")) DESC');

        // Priority 3: Recently created courses
        $query->orderBy('created_at', 'desc');

        $recommendations = $query->limit(10)->get()->map(function($course) {
            $course->available_spots = max(0, $course->max_students - $course->enrollments_count);
            $course->is_full = $course->enrollments_count >= $course->max_students;
            return $course;
        });

        return response()->json([
            'success' => true,
            'data' => [
                'recommendations' => $recommendations,
                'recommendation_reasons' => [
                    'based_on_completed_courses' => $completedCourses->count(),
                    'preferred_difficulties' => $preferredDifficulties->toArray(),
                    'available_courses' => $recommendations->count()
                ]
            ]
        ]);
    }

    public function categories(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        // Get difficulty level distribution
        $difficultyStats = Course::where('school_id', $schoolId)
            ->where('active', true)
            ->selectRaw('difficulty_level, COUNT(*) as count, AVG(price) as avg_price')
            ->groupBy('difficulty_level')
            ->get();

        // Get instructor stats
        $instructorStats = Course::where('school_id', $schoolId)
            ->where('active', true)
            ->selectRaw('instructor, COUNT(*) as course_count')
            ->groupBy('instructor')
            ->orderBy('course_count', 'desc')
            ->limit(5)
            ->get();

        // Get price ranges
        $priceRanges = Course::where('school_id', $schoolId)
            ->where('active', true)
            ->selectRaw('
                CASE 
                    WHEN price <= 50 THEN "Under €50"
                    WHEN price <= 100 THEN "€50 - €100"
                    WHEN price <= 200 THEN "€100 - €200"
                    ELSE "Over €200"
                END as price_range,
                COUNT(*) as count
            ')
            ->groupBy('price_range')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'difficulty_levels' => $difficultyStats,
                'top_instructors' => $instructorStats,
                'price_ranges' => $priceRanges,
                'total_active_courses' => Course::where('school_id', $schoolId)->where('active', true)->count()
            ]
        ]);
    }

    private function canUserEnroll($user, $course, $enrollment): bool
    {
        // Already enrolled
        if ($enrollment && in_array($enrollment->status, ['active', 'pending'])) {
            return false;
        }

        // Course not active
        if (!$course->active) {
            return false;
        }

        // Course is full
        $activeEnrollments = CourseEnrollment::where('course_id', $course->id)
            ->where('status', 'active')
            ->count();
        
        if ($activeEnrollments >= $course->max_students) {
            return false;
        }

        // Past enrollment deadline (1 day before start)
        if (now() > $course->start_date->subDays(1)) {
            return false;
        }

        // Student not active
        if (!$user->active) {
            return false;
        }

        return true;
    }
}