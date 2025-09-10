<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\CourseEnrollment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class StudentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:admin');
    }

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $query = User::where('school_id', $schoolId)
            ->where('role', 'student')
            ->withCount(['enrollments', 'payments']);

        // Filtering
        if ($request->has('active')) {
            $query->where('active', $request->boolean('active'));
        }

        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $students = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $students->items(),
            'pagination' => [
                'current_page' => $students->currentPage(),
                'last_page' => $students->lastPage(),
                'per_page' => $students->perPage(),
                'total' => $students->total(),
                'from' => $students->firstItem(),
                'to' => $students->lastItem(),
            ]
        ]);
    }

    public function show(Request $request, User $student): JsonResponse
    {
        $user = $request->user();
        
        // Check if student belongs to admin's school
        if ($student->school_id !== $user->school_id || $student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied'
            ], 404);
        }

        $student->load([
            'enrollments.course:id,name,instructor,schedule,price',
            'payments.course:id,name',
            'documents',
            'school:id,name,email,phone'
        ]);

        // Calculate additional stats
        $stats = [
            'total_courses' => $student->enrollments->count(),
            'active_courses' => $student->enrollments->where('status', 'active')->count(),
            'completed_courses' => $student->enrollments->where('status', 'completed')->count(),
            'total_paid' => $student->payments->where('status', 'completed')->sum('amount'),
            'pending_payments' => $student->payments->where('status', 'pending')->sum('amount'),
            'enrollment_rate' => $student->enrollments->count() > 0 
                ? round(($student->enrollments->where('status', 'completed')->count() / $student->enrollments->count()) * 100, 2)
                : 0
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'student' => $student,
                'stats' => $stats
            ]
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_notes' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['role'] = 'student';
        $validated['school_id'] = $user->school_id;
        $validated['active'] = $validated['active'] ?? true;

        $student = User::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student created successfully',
            'data' => $student->fresh(['school:id,name'])
        ], 201);
    }

    public function update(Request $request, User $student): JsonResponse
    {
        $user = $request->user();
        
        // Check if student belongs to admin's school
        if ($student->school_id !== $user->school_id || $student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied'
            ], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($student->id)
            ],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_notes' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $student->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Student updated successfully',
            'data' => $student->refresh()
        ]);
    }

    public function destroy(Request $request, User $student): JsonResponse
    {
        $user = $request->user();
        
        // Check if student belongs to admin's school
        if ($student->school_id !== $user->school_id || $student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied'
            ], 404);
        }

        // Check if student has active enrollments
        $activeEnrollments = $student->enrollments()->where('status', 'active')->count();
        if ($activeEnrollments > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete student with active enrollments. Please deactivate instead.'
            ], 422);
        }

        $student->delete();

        return response()->json([
            'success' => true,
            'message' => 'Student deleted successfully'
        ]);
    }

    public function activate(Request $request, User $student): JsonResponse
    {
        $user = $request->user();
        
        if ($student->school_id !== $user->school_id || $student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied'
            ], 404);
        }

        $student->update(['active' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Student activated successfully',
            'data' => $student
        ]);
    }

    public function deactivate(Request $request, User $student): JsonResponse
    {
        $user = $request->user();
        
        if ($student->school_id !== $user->school_id || $student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied'
            ], 404);
        }

        $student->update(['active' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Student deactivated successfully',
            'data' => $student
        ]);
    }

    public function enrollments(Request $request, User $student): JsonResponse
    {
        $user = $request->user();
        
        if ($student->school_id !== $user->school_id || $student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied'
            ], 404);
        }

        $enrollments = CourseEnrollment::where('user_id', $student->id)
            ->with(['course:id,name,instructor,schedule,price,start_date,end_date'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $enrollments
        ]);
    }

    public function payments(Request $request, User $student): JsonResponse
    {
        $user = $request->user();
        
        if ($student->school_id !== $user->school_id || $student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied'
            ], 404);
        }

        $payments = $student->payments()
            ->with(['course:id,name'])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_paid' => $payments->where('status', 'completed')->sum('amount'),
            'pending_amount' => $payments->where('status', 'pending')->sum('amount'),
            'failed_payments' => $payments->where('status', 'failed')->count(),
            'payment_history_count' => $payments->count()
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $payments,
                'summary' => $summary
            ]
        ]);
    }

    public function resetPassword(Request $request, User $student): JsonResponse
    {
        $user = $request->user();
        
        if ($student->school_id !== $user->school_id || $student->role !== 'student') {
            return response()->json([
                'success' => false,
                'message' => 'Student not found or access denied'
            ], 404);
        }

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $student->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Student password reset successfully'
        ]);
    }

    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();
        $schoolId = $user->school_id;

        $stats = [
            'total_students' => User::where('school_id', $schoolId)->where('role', 'student')->count(),
            'active_students' => User::where('school_id', $schoolId)
                ->where('role', 'student')
                ->where('active', true)
                ->count(),
            'new_students_this_month' => User::where('school_id', $schoolId)
                ->where('role', 'student')
                ->whereMonth('created_at', now()->month)
                ->count(),
            'avg_enrollments_per_student' => User::where('school_id', $schoolId)
                ->where('role', 'student')
                ->withCount('enrollments')
                ->get()
                ->avg('enrollments_count'),
            'age_distribution' => User::where('school_id', $schoolId)
                ->where('role', 'student')
                ->whereNotNull('date_of_birth')
                ->selectRaw('
                    CASE 
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) < 18 THEN "Under 18"
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 18 AND 30 THEN "18-30"
                        WHEN TIMESTAMPDIFF(YEAR, date_of_birth, CURDATE()) BETWEEN 31 AND 50 THEN "31-50"
                        ELSE "Over 50"
                    END as age_group,
                    COUNT(*) as count
                ')
                ->groupBy('age_group')
                ->get(),
            'retention_rate' => $this->calculateRetentionRate($schoolId)
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    private function calculateRetentionRate($schoolId): float
    {
        $totalStudents = User::where('school_id', $schoolId)
            ->where('role', 'student')
            ->where('created_at', '<', now()->subMonths(3))
            ->count();

        $retainedStudents = User::where('school_id', $schoolId)
            ->where('role', 'student')
            ->where('created_at', '<', now()->subMonths(3))
            ->whereHas('enrollments', function($query) {
                $query->where('created_at', '>', now()->subMonths(1));
            })
            ->count();

        return $totalStudents > 0 ? round(($retainedStudents / $totalStudents) * 100, 2) : 0;
    }
}