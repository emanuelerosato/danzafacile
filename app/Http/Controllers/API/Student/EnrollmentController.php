<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\API\BaseApiController;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnrollmentController extends BaseApiController
{

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
            'payment_method' => 'nullable|in:credit_card,bank_transfer,cash',
            'notes' => 'nullable|string|max:500',
        ]);

        $course = Course::findOrFail($validated['course_id']);

        // Verify course belongs to student's school
        if ($course->school_id !== $user->school_id) {
            return response()->json([
                'success' => false,
                'message' => 'Course not available for your school'
            ], 403);
        }

        // Check if student is already enrolled
        $existingEnrollment = CourseEnrollment::where('course_id', $course->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['active', 'pending'])
            ->first();

        if ($existingEnrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Already enrolled in this course'
            ], 400);
        }

        // Check course availability
        $activeEnrollments = CourseEnrollment::where('course_id', $course->id)
            ->where('status', 'active')
            ->count();

        if ($activeEnrollments >= $course->max_students) {
            return response()->json([
                'success' => false,
                'message' => 'Course is full'
            ], 400);
        }

        // Check enrollment deadline
        if (now() > $course->start_date->subDays(1)) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment deadline has passed'
            ], 422);
        }

        // Check if course is active
        if (!$course->active) {
            return response()->json([
                'success' => false,
                'message' => 'Course is not available for enrollment'
            ], 422);
        }

        DB::beginTransaction();
        
        try {
            // Create enrollment
            $enrollment = CourseEnrollment::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'enrollment_date' => now(),
                'status' => 'active', // Set to active for testing without payment
                'payment_status' => 'pending',
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create payment record only if payment method is provided
            $payment = null;
            if (!empty($validated['payment_method'])) {
                $payment = Payment::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
                    'amount' => $course->price,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'pending',
                    'due_date' => now()->addDays(7), // Payment due in 7 days
                ]);
            }

            DB::commit();

            $responseData = [
                'enrollment' => [
                    'id' => $enrollment->id,
                    'course_name' => $course->name,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'status' => $enrollment->status,
                ]
            ];

            if ($payment) {
                $responseData['payment'] = [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'status' => $payment->status,
                    'due_date' => $payment->due_date,
                ];
                $responseData['next_steps'] = [
                    'complete_payment' => "Please complete payment of €{$course->price} within 7 days",
                    'payment_methods' => ['credit_card', 'bank_transfer', 'cash'],
                    'contact_school' => 'Contact the school for payment instructions'
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Enrollment created successfully',
                'data' => $responseData
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create enrollment. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function cancel(Request $request, CourseEnrollment $enrollment): JsonResponse
    {
        $user = $request->user();

        // Check if enrollment belongs to the user
        if ($enrollment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment not found'
            ], 404);
        }

        // Check if enrollment can be cancelled
        if (!in_array($enrollment->status, ['pending', 'active'])) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment cannot be cancelled'
            ], 422);
        }

        $course = $enrollment->course;

        // Check cancellation deadline (e.g., 48 hours before start)
        $cancellationDeadline = $course->start_date->subHours(48);
        if (now() > $cancellationDeadline) {
            return response()->json([
                'success' => false,
                'message' => 'Cancellation deadline has passed. Please contact the school.'
            ], 422);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        
        try {
            // Update enrollment status
            $enrollment->update([
                'status' => 'cancelled',
                'notes' => $enrollment->notes . "\nCancelled: " . ($validated['reason'] ?? 'No reason provided'),
            ]);

            // Cancel related pending payments
            Payment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('status', 'pending')
                ->update(['status' => 'cancelled']);

            // Handle refunds for completed payments (would need specific logic)
            $completedPayments = Payment::where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->where('status', 'completed')
                ->get();

            foreach ($completedPayments as $payment) {
                // In a real implementation, you'd process refunds here
                // For now, just mark as refund_requested
                $payment->update([
                    'status' => 'refund_requested',
                    'notes' => 'Refund requested due to enrollment cancellation'
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Enrollment cancelled successfully',
                'data' => [
                    'enrollment' => $enrollment->refresh(),
                    'refund_info' => [
                        'refund_amount' => $completedPayments->sum('amount'),
                        'refund_status' => $completedPayments->isNotEmpty() ? 'processing' : 'no_refund_needed',
                        'processing_time' => '3-5 business days'
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel enrollment. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(Request $request, CourseEnrollment $enrollment): JsonResponse
    {
        $user = $request->user();

        // Check if enrollment belongs to the user
        if ($enrollment->user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Enrollment not found'
            ], 404);
        }

        $enrollment->load([
            'course:id,name,description,instructor,schedule,price,start_date,end_date,max_students',
            'payments' => function($query) {
                $query->orderBy('created_at', 'desc');
            }
        ]);

        // Calculate enrollment statistics
        $course = $enrollment->course;
        $totalEnrollments = CourseEnrollment::where('course_id', $course->id)
            ->where('status', 'active')
            ->count();

        $enrollmentData = [
            'enrollment' => $enrollment,
            'course_stats' => [
                'total_enrolled' => $totalEnrollments,
                'available_spots' => max(0, $course->max_students - $totalEnrollments),
                'is_full' => $totalEnrollments >= $course->max_students,
            ],
            'payment_summary' => [
                'total_paid' => $enrollment->payments->where('status', 'completed')->sum('amount'),
                'pending_amount' => $enrollment->payments->where('status', 'pending')->sum('amount'),
                'last_payment_date' => $enrollment->payments->where('status', 'completed')->first()?->created_at,
            ],
            'schedule_info' => [
                'start_date' => $course->start_date,
                'end_date' => $course->end_date,
                'schedule' => $course->schedule,
                'instructor' => $course->instructor,
            ],
            'cancellation_info' => [
                'can_cancel' => $this->canCancelEnrollment($enrollment),
                'cancellation_deadline' => $course->start_date->subHours(48),
                'refund_policy' => 'Full refund if cancelled 48 hours before start'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $enrollmentData
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = CourseEnrollment::where('user_id', $user->id)
            ->with(['course:id,name,instructor,schedule,price,start_date,end_date']);

        // Filtering by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filtering by date range
        if ($request->has('from_date')) {
            $query->where('enrollment_date', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date')) {
            $query->where('enrollment_date', '<=', $request->get('to_date'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'enrollment_date');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $enrollments = $query->paginate($perPage);

        // Add summary statistics
        $allEnrollments = CourseEnrollment::where('user_id', $user->id)->get();
        $stats = [
            'total_enrollments' => $allEnrollments->count(),
            'active_enrollments' => $allEnrollments->where('status', 'active')->count(),
            'completed_enrollments' => $allEnrollments->where('status', 'completed')->count(),
            'cancelled_enrollments' => $allEnrollments->where('status', 'cancelled')->count(),
            'total_spent' => Payment::where('user_id', $user->id)
                ->where('status', 'completed')
                ->sum('amount'),
            'completion_rate' => $allEnrollments->count() > 0 
                ? round(($allEnrollments->where('status', 'completed')->count() / $allEnrollments->count()) * 100, 2)
                : 0
        ];

        return response()->json([
            'success' => true,
            'data' => $enrollments->items(),
            'stats' => $stats,
            'pagination' => [
                'current_page' => $enrollments->currentPage(),
                'last_page' => $enrollments->lastPage(),
                'per_page' => $enrollments->perPage(),
                'total' => $enrollments->total(),
                'from' => $enrollments->firstItem(),
                'to' => $enrollments->lastItem(),
            ]
        ]);
    }

    private function canCancelEnrollment(CourseEnrollment $enrollment): bool
    {
        // Can only cancel pending or active enrollments
        if (!in_array($enrollment->status, ['pending', 'active'])) {
            return false;
        }

        // Check cancellation deadline (48 hours before start)
        $cancellationDeadline = $enrollment->course->start_date->subHours(48);
        if (now() > $cancellationDeadline) {
            return false;
        }

        return true;
    }
}