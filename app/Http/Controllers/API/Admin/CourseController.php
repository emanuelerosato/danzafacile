<?php

namespace App\Http\Controllers\API\Admin;

use App\Helpers\QueryHelper;
use App\Http\Controllers\API\BaseApiController;
use App\Models\Course;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CourseController extends BaseApiController
{
    /**
     * Sanitize input data to prevent XSS attacks
     */
    private function sanitizeInput(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove null bytes
                $value = str_replace("\0", '', $value);

                // Remove script tags and their content
                $value = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $value);

                // Remove javascript: URLs
                $value = preg_replace('/javascript:/i', '', $value);

                // Remove on* event handlers
                $value = preg_replace('/\bon\w+\s*=\s*["\'][^"\']*["\']/', '', $value);

                // Remove dangerous HTML elements
                $dangerousTags = ['script', 'iframe', 'object', 'embed', 'form', 'input', 'button', 'select', 'textarea', 'meta', 'link', 'style'];
                foreach ($dangerousTags as $tag) {
                    $value = preg_replace("/<\\/?{$tag}\\b[^>]*>/i", '', $value);
                }

                // For most fields, strip all HTML tags
                if (!in_array($key, ['description'])) {
                    $value = strip_tags($value);
                }

                // Trim whitespace
                $value = trim($value);

                $sanitized[$key] = $value;
            } else {
                // Non-string values pass through unchanged
                $sanitized[$key] = $value;
            }
        }

        return $sanitized;
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $query = Course::where('school_id', $schoolId)
            ->withCount(['enrollments', 'payments'])
            ->with('instructor:id,name');

        // Filtering
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // SECURITY: Sanitize LIKE input to prevent SQL wildcard injection
        if ($request->has('search')) {
            $search = QueryHelper::sanitizeLikeInput($request->get('search'));
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $courses = $query->paginate($perPage);

        // Transform courses to include instructor_name
        $transformedCourses = $courses->getCollection()->map(function ($course) {
            return [
                'id' => $course->id,
                'name' => $course->name,
                'instructor_name' => $course->instructor ? $course->instructor->name : 'No Instructor',
                'price' => $course->price,
                'enrolled_students' => $course->enrollments_count,
                'max_students' => $course->max_students,
                'active' => $course->active,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'courses' => $transformedCourses,
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
        
        // Check if course belongs to admin's school
        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or access denied'
            ], 404);
        }

        $course->load([
            'enrollments.user:id,name,email,phone',
            'payments' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'course' => $course,
                'stats' => [
                    'total_enrollments' => $course->enrollments->count(),
                    'active_enrollments' => $course->enrollments->where('status', 'active')->count(),
                    'total_revenue' => $course->payments->where('status', 'completed')->sum('amount'),
                    'pending_payments' => $course->payments->where('status', 'pending')->count(),
                    'capacity_percentage' => $course->max_students > 0 
                        ? round(($course->enrollments->count() / $course->max_students) * 100, 2)
                        : 0
                ]
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'instructor_id' => 'nullable|exists:users,id',
            'level' => ['required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'price' => 'required|numeric|min:0|max:999.99',
            'max_students' => 'nullable|integer|min:1|max:100',
            'start_date' => 'required|date|after:today',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'duration_weeks' => 'nullable|integer|min:1|max:52',
            'schedule' => 'nullable|string|max:255',
            'active' => 'boolean',
        ]);

        // Sanitize input data
        $validated = $this->sanitizeInput($validated);

        $validated['school_id'] = $user->school_id;
        $validated['active'] = $validated['active'] ?? true;

        $course = Course::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Course created successfully',
            'data' => [
                'course' => [
                    'id' => $course->id,
                    'name' => $course->name,
                    'price' => $course->price,
                    'max_students' => $course->max_students,
                    'active' => $course->active,
                ]
            ]
        ], 201);
    }

    public function update(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        
        // Check if course belongs to admin's school
        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or access denied'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'instructor' => 'sometimes|required|string|max:255',
            'schedule' => 'sometimes|required|string|max:255',
            'max_students' => 'sometimes|required|integer|min:1|max:100',
            'price' => 'sometimes|required|numeric|min:0',
            'duration_weeks' => 'sometimes|required|integer|min:1|max:52',
            'difficulty_level' => ['sometimes', 'required', Rule::in(['beginner', 'intermediate', 'advanced'])],
            'start_date' => 'sometimes|required|date',
            'end_date' => 'sometimes|required|date|after:start_date',
            'active' => 'boolean',
        ]);

        $course->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Course updated successfully',
            'data' => $course->refresh()
        ]);
    }

    public function destroy(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        
        // Check if course belongs to admin's school
        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or access denied'
            ], 404);
        }

        // Check if course has active enrollments
        $activeEnrollments = $course->enrollments()->where('status', 'active')->count();
        if ($activeEnrollments > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete course with active enrollments. Please deactivate instead.'
            ], 422);
        }

        $course->delete();

        return response()->json([
            'success' => true,
            'message' => 'Course deleted successfully'
        ]);
    }

    public function activate(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        
        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or access denied'
            ], 404);
        }

        $course->update(['active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Course activated successfully',
            'data' => $course
        ]);
    }

    public function deactivate(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or access denied'
            ], 404);
        }

        $course->update(['active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Course deactivated successfully',
            'data' => $course
        ]);
    }

    public function toggleStatus(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();

        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or access denied'
            ], 404);
        }

        $course->update(['active' => !$course->active]);
        $status = $course->active ? 'activated' : 'deactivated';

        return response()->json([
            'success' => true,
            'message' => "Course {$status} successfully",
            'data' => [
                'course' => $course,
                'status' => $course->active ? 'active' : 'inactive'
            ]
        ]);
    }

    public function duplicate(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        
        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found or access denied'
            ], 404);
        }

        $newCourse = $course->replicate();
        $newCourse->name = $course->name . ' (Copy)';
        $newCourse->active = false; // New course starts inactive
        $newCourse->save();

        return response()->json([
            'success' => true,
            'message' => 'Course duplicated successfully',
            'data' => $newCourse
        ], 201);
    }

    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $stats = [
            'total_courses' => Course::where('school_id', $schoolId)->count(),
            'active_courses' => Course::where('school_id', $schoolId)->where('active', true)->count(),
            'avg_enrollment_per_course' => Course::where('school_id', $schoolId)
                ->withCount('enrollments')
                ->get()
                ->avg('enrollments_count'),
            'most_popular_course' => Course::where('school_id', $schoolId)
                ->withCount('enrollments')
                ->orderBy('enrollments_count', 'desc')
                ->first(['id', 'name', 'enrollments_count']),
            'difficulty_distribution' => Course::where('school_id', $schoolId)
                ->selectRaw('difficulty_level, COUNT(*) as count')
                ->groupBy('difficulty_level')
                ->get(),
            'revenue_by_course' => Course::where('school_id', $schoolId)
                ->with(['payments' => function($query) {
                    $query->where('status', 'completed');
                }])
                ->get()
                ->map(function($course) {
                    return [
                        'course_name' => $course->name,
                        'revenue' => $course->payments->sum('amount')
                    ];
                })
                ->sortByDesc('revenue')
                ->take(5)
                ->values()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'stats' => [
                    'total_courses' => $stats['total_courses'],
                    'active_courses' => $stats['active_courses'],
                    'total_enrollments' => $stats['avg_enrollment_per_course'] * $stats['total_courses'],
                    'revenue_this_month' => $stats['revenue_by_course'][0]['revenue'] ?? 0,
                ]
            ]
        ]);
    }
}