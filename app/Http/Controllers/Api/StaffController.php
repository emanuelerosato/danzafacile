<?php

namespace App\Http\Controllers\Api;

use App\Helpers\QueryHelper;
use App\Models\User;
use App\Models\StaffRole;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * Staff API Controller
 *
 * Handles staff management for Flutter app (Admin only) including:
 * - Staff members CRUD
 * - Staff roles and assignments
 * - Staff schedules and availability
 * - Staff performance metrics
 */
class StaffController extends BaseApiController
{
    /**
     * Get all staff members for admin's school
     */
    public function index(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        // Only admins can access staff management
        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can manage staff');
        }

        $params = $this->getPaginationParams($request);
        $sort = $this->getSortingParams($request, 'name', 'asc');

        $query = User::query();
        $this->scopeToUserSchool($query);

        // Only get staff members (admin role in current schema)
        $query->where('role', 'admin');

        // Apply filters
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        // Search functionality
        // SECURITY: Sanitize LIKE input to prevent SQL wildcard injection
        if ($request->has('search')) {
            $search = QueryHelper::sanitizeLikeInput($request->get('search'));
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%");
            });
        }

        $query->orderBy($sort['sort'], $sort['order']);
        $staff = $query->with(['school', 'staffRoles'])->paginate($params['per_page'], ['*'], 'page', $params['page']);

        // Transform data for mobile
        $staff->getCollection()->transform(function ($member) {
            return [
                'id' => $member->id,
                'name' => $member->name,
                'first_name' => $member->first_name,
                'last_name' => $member->last_name,
                'email' => $member->email,
                'phone' => $member->phone,
                'active' => $member->active,
                'profile_image_url' => $member->profile_image_url,
                'roles' => $member->staffRoles->pluck('role_name'),
                'total_courses' => $member->school->courses()->where('instructor_id', $member->id)->count(),
                'created_at' => $member->created_at->toISOString(),
            ];
        });

        return $this->paginatedResponse($staff, 'Staff members retrieved successfully');
    }

    /**
     * Get staff member details
     */
    public function show(Request $request, User $staff): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can view staff details');
        }

        // Check multi-tenant access
        if (!$this->validateTenantAccess($staff)) {
            return $this->forbiddenResponse('Access denied to this staff member');
        }

        // Load relationships
        $staff->load(['school', 'staffRoles']);

        // Get additional data
        $assignedCourses = $staff->school->courses()->where('instructor_id', $staff->id)->get();
        $upcomingEvents = $staff->school->events()
            ->where('start_date', '>', now())
            ->take(5)
            ->get();

        return $this->successResponse([
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,
                'email' => $staff->email,
                'phone' => $staff->phone,
                'date_of_birth' => $staff->date_of_birth?->format('Y-m-d'),
                'address' => $staff->address,
                'emergency_contact' => $staff->emergency_contact,
                'medical_notes' => $staff->medical_notes,
                'active' => $staff->active,
                'profile_image_url' => $staff->profile_image_url,
                'created_at' => $staff->created_at->toISOString(),
                'school' => [
                    'id' => $staff->school->id,
                    'name' => $staff->school->name,
                ],
                'roles' => $staff->staffRoles->map(function($role) {
                    return [
                        'id' => $role->id,
                        'role_name' => $role->role_name,
                        'permissions' => $role->permissions,
                        'hourly_rate' => $role->hourly_rate,
                    ];
                }),
                'assigned_courses' => $assignedCourses->map(function($course) {
                    return [
                        'id' => $course->id,
                        'name' => $course->name,
                        'schedule' => $course->schedule,
                        'active' => $course->active,
                        'enrolled_students' => $course->enrollments()->where('status', 'active')->count(),
                    ];
                }),
                'stats' => [
                    'total_courses' => $assignedCourses->count(),
                    'active_courses' => $assignedCourses->where('active', true)->count(),
                    'total_students' => $assignedCourses->sum(function($course) {
                        return $course->enrollments()->where('status', 'active')->count();
                    }),
                ]
            ]
        ], 'Staff member details retrieved successfully');
    }

    /**
     * Create new staff member
     */
    public function store(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can create staff members');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string|max:1000',
            'emergency_contact' => 'nullable|string|max:1000',
            'medical_notes' => 'nullable|string|max:1000',
            'roles' => 'nullable|array',
            'roles.*' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $userSchool = $this->getUserSchool();
        if (!$userSchool) {
            return $this->forbiddenResponse('User not associated with any school');
        }

        // Create staff member
        $staffMember = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'address' => $request->address,
            'emergency_contact' => $request->emergency_contact,
            'medical_notes' => $request->medical_notes,
            'school_id' => $userSchool->id,
            'role' => 'admin', // Staff members are admins in current schema
            'active' => true,
        ]);

        // Create staff roles if provided
        if ($request->has('roles')) {
            foreach ($request->roles as $roleName) {
                StaffRole::create([
                    'user_id' => $staffMember->id,
                    'role_name' => $roleName,
                    'permissions' => ['basic'], // Default permissions
                    'hourly_rate' => 0.00,
                ]);
            }
        }

        $staffMember->load(['staffRoles']);

        return $this->successResponse([
            'staff' => [
                'id' => $staffMember->id,
                'name' => $staffMember->name,
                'email' => $staffMember->email,
                'first_name' => $staffMember->first_name,
                'last_name' => $staffMember->last_name,
                'phone' => $staffMember->phone,
                'active' => $staffMember->active,
                'roles' => $staffMember->staffRoles->pluck('role_name'),
            ]
        ], 'Staff member created successfully', 201);
    }

    /**
     * Update staff member
     */
    public function update(Request $request, User $staff): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can update staff members');
        }

        // Check multi-tenant access
        if (!$this->validateTenantAccess($staff)) {
            return $this->forbiddenResponse('Access denied to this staff member');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $staff->id,
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'date_of_birth' => 'sometimes|date',
            'address' => 'sometimes|string|max:1000',
            'emergency_contact' => 'sometimes|string|max:1000',
            'medical_notes' => 'sometimes|string|max:1000',
            'active' => 'sometimes|boolean',
            'roles' => 'sometimes|array',
            'roles.*' => 'string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->validationErrorResponse($validator);
        }

        $staff->update($request->only([
            'name', 'email', 'first_name', 'last_name', 'phone',
            'date_of_birth', 'address', 'emergency_contact', 'medical_notes', 'active'
        ]));

        // Update roles if provided
        if ($request->has('roles')) {
            // Delete existing roles
            $staff->staffRoles()->delete();

            // Create new roles
            foreach ($request->roles as $roleName) {
                StaffRole::create([
                    'user_id' => $staff->id,
                    'role_name' => $roleName,
                    'permissions' => ['basic'],
                    'hourly_rate' => 0.00,
                ]);
            }
        }

        $staff->load(['staffRoles']);

        return $this->successResponse([
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'email' => $staff->email,
                'first_name' => $staff->first_name,
                'last_name' => $staff->last_name,
                'phone' => $staff->phone,
                'active' => $staff->active,
                'roles' => $staff->staffRoles->pluck('role_name'),
            ]
        ], 'Staff member updated successfully');
    }

    /**
     * Delete staff member
     */
    public function destroy(Request $request, User $staff): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can delete staff members');
        }

        // Check multi-tenant access
        if (!$this->validateTenantAccess($staff)) {
            return $this->forbiddenResponse('Access denied to this staff member');
        }

        // Check if staff has assigned courses
        $assignedCourses = $staff->school->courses()->where('instructor_id', $staff->id)->count();
        if ($assignedCourses > 0) {
            return $this->errorResponse('Cannot delete staff member with assigned courses. Please reassign courses first.', 400);
        }

        // Delete related staff roles
        $staff->staffRoles()->delete();

        // Delete staff member
        $staff->delete();

        return $this->successResponse(null, 'Staff member deleted successfully');
    }

    /**
     * Toggle staff member active status
     */
    public function toggleStatus(Request $request, User $staff): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can change staff status');
        }

        // Check multi-tenant access
        if (!$this->validateTenantAccess($staff)) {
            return $this->forbiddenResponse('Access denied to this staff member');
        }

        $staff->update(['active' => !$staff->active]);

        return $this->successResponse([
            'staff' => [
                'id' => $staff->id,
                'name' => $staff->name,
                'active' => $staff->active,
            ]
        ], 'Staff status updated successfully');
    }

    /**
     * Get staff schedule and assignments
     */
    public function schedule(Request $request, User $staff): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can view staff schedules');
        }

        // Check multi-tenant access
        if (!$this->validateTenantAccess($staff)) {
            return $this->forbiddenResponse('Access denied to this staff member');
        }

        // Get assigned courses with schedules
        $assignedCourses = $staff->school->courses()
            ->where('instructor_id', $staff->id)
            ->where('active', true)
            ->get()
            ->map(function($course) {
                return [
                    'id' => $course->id,
                    'name' => $course->name,
                    'schedule' => $course->schedule,
                    'start_date' => $course->start_date->format('Y-m-d'),
                    'end_date' => $course->end_date->format('Y-m-d'),
                    'location' => $course->location,
                    'max_students' => $course->max_students,
                    'enrolled_students' => $course->enrollments()->where('status', 'active')->count(),
                ];
            });

        // Get upcoming events (if staff can be assigned to events in future)
        $upcomingEvents = $staff->school->events()
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->take(10)
            ->get()
            ->map(function($event) {
                return [
                    'id' => $event->id,
                    'name' => $event->name,
                    'start_date' => $event->start_date->toISOString(),
                    'end_date' => $event->end_date->toISOString(),
                    'location' => $event->location,
                ];
            });

        return $this->successResponse([
            'staff_member' => [
                'id' => $staff->id,
                'name' => $staff->name,
            ],
            'assigned_courses' => $assignedCourses,
            'upcoming_events' => $upcomingEvents,
            'stats' => [
                'total_courses' => $assignedCourses->count(),
                'total_students' => $assignedCourses->sum('enrolled_students'),
                'upcoming_events' => $upcomingEvents->count(),
            ]
        ], 'Staff schedule retrieved successfully');
    }

    /**
     * Get staff statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $this->getAuthenticatedUser();

        if (!$user->isAdmin()) {
            return $this->forbiddenResponse('Only administrators can view staff statistics');
        }

        $userSchool = $this->getUserSchool();
        if (!$userSchool) {
            return $this->forbiddenResponse('User not associated with any school');
        }

        $totalStaff = User::where('school_id', $userSchool->id)
            ->where('role', 'admin')
            ->count();

        $activeStaff = User::where('school_id', $userSchool->id)
            ->where('role', 'admin')
            ->where('active', true)
            ->count();

        $staffWithCourses = User::where('school_id', $userSchool->id)
            ->where('role', 'admin')
            ->whereHas('school.courses', function($q) {
                $q->where('active', true);
            })
            ->count();

        return $this->successResponse([
            'stats' => [
                'total_staff' => $totalStaff,
                'active_staff' => $activeStaff,
                'staff_with_courses' => $staffWithCourses,
                'utilization_rate' => $totalStaff > 0 ? round(($staffWithCourses / $totalStaff) * 100, 1) : 0,
            ],
            'recent_hires' => User::where('school_id', $userSchool->id)
                ->where('role', 'admin')
                ->where('created_at', '>', now()->subDays(30))
                ->count()
        ], 'Staff statistics retrieved successfully');
    }
}