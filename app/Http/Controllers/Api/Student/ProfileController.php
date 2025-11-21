<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
        $this->middleware('role:student');
    }

    public function show(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $user->load([
            'school:id,name,email,phone,address',
            'enrollments.course:id,name,instructor,schedule,start_date,end_date',
            'payments.course:id,name'
        ]);

        // Calculate profile stats
        $stats = [
            'total_courses' => $user->enrollments->count(),
            'active_courses' => $user->enrollments->where('status', 'active')->count(),
            'completed_courses' => $user->enrollments->where('status', 'completed')->count(),
            'total_paid' => $user->payments->where('status', 'completed')->sum('amount'),
            'pending_payments' => $user->payments->where('status', 'pending')->sum('amount'),
            'member_since' => $user->created_at->format('Y-m-d'),
            'profile_completion' => $this->calculateProfileCompletion($user)
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'date_of_birth' => $user->date_of_birth,
                    'address' => $user->address,
                    'emergency_contact' => $user->emergency_contact,
                    'medical_notes' => $user->medical_notes,
                    'active' => $user->active,
                    'email_verified_at' => $user->email_verified_at,
                    'created_at' => $user->created_at,
                    'school' => $user->school
                ],
                'stats' => $stats,
                'recent_enrollments' => $user->enrollments()
                    ->with('course:id,name,instructor')
                    ->orderBy('created_at', 'desc')
                    ->limit(3)
                    ->get()
            ]
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before:today',
            'address' => 'nullable|string|max:500',
            'emergency_contact' => 'nullable|string|max:255',
            'medical_notes' => 'nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth,
                'address' => $user->address,
                'emergency_contact' => $user->emergency_contact,
                'medical_notes' => $user->medical_notes,
                'profile_completion' => $this->calculateProfileCompletion($user)
            ]
        ]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password is incorrect']
                ]
            ], 422);
        }

        $user->update([
            'password' => Hash::make($validated['password'])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password updated successfully'
        ]);
    }

    public function updateEmail(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id)
            ],
            'current_password' => 'required|string',
        ]);

        // Verify current password
        if (!Hash::check($validated['current_password'], $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
                'errors' => [
                    'current_password' => ['The current password is incorrect']
                ]
            ], 422);
        }

        $user->update([
            'email' => $validated['email'],
            'email_verified_at' => null // Reset email verification
        ]);

        // Here you could send email verification
        // event(new Registered($user));

        return response()->json([
            'success' => true,
            'message' => 'Email updated successfully. Please verify your new email address.',
            'data' => [
                'email' => $user->email,
                'email_verified' => false
            ]
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get upcoming classes
        $upcomingClasses = $user->enrollments()
            ->with('course:id,name,instructor,schedule')
            ->where('status', 'active')
            ->get()
            ->map(function ($enrollment) {
                return [
                    'id' => $enrollment->id,
                    'course' => $enrollment->course,
                    'enrollment_date' => $enrollment->enrollment_date,
                    'status' => $enrollment->status,
                    'next_class' => $this->getNextClassDate($enrollment->course->schedule)
                ];
            })
            ->sortBy('next_class')
            ->values();

        // Get recent payments
        $recentPayments = $user->payments()
            ->with('course:id,name')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get notifications (upcoming classes, payment reminders, etc.)
        $notifications = $this->getStudentNotifications($user);

        // Dashboard stats
        $stats = [
            'active_courses' => $user->enrollments->where('status', 'active')->count(),
            'completed_courses' => $user->enrollments->where('status', 'completed')->count(),
            'total_payments' => $user->payments->where('status', 'completed')->sum('amount'),
            'pending_payments' => $user->payments->where('status', 'pending')->sum('amount'),
            'attendance_rate' => 95, // Mock data - would come from attendance tracking
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'student_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'school' => $user->school->name ?? 'Unknown School'
                ],
                'stats' => $stats,
                'upcoming_classes' => $upcomingClasses,
                'recent_payments' => $recentPayments,
                'notifications' => $notifications
            ]
        ]);
    }

    public function preferences(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($request->isMethod('GET')) {
            // Get current preferences (mock data - would be stored in user_preferences table)
            $preferences = [
                'email_notifications' => true,
                'sms_notifications' => false,
                'class_reminders' => true,
                'payment_reminders' => true,
                'marketing_emails' => false,
                'language' => 'it',
                'timezone' => 'Europe/Rome'
            ];

            return response()->json([
                'success' => true,
                'data' => $preferences
            ]);
        }

        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            $validated = $request->validate([
                'email_notifications' => 'boolean',
                'sms_notifications' => 'boolean',
                'class_reminders' => 'boolean',
                'payment_reminders' => 'boolean',
                'marketing_emails' => 'boolean',
                'language' => 'string|in:it,en,es,fr',
                'timezone' => 'string'
            ]);

            // Here you would update user preferences in database
            // UserPreference::updateOrCreate(['user_id' => $user->id], $validated);

            return response()->json([
                'success' => true,
                'message' => 'Preferences updated successfully',
                'data' => $validated
            ]);
        }
    }

    private function calculateProfileCompletion($user): int
    {
        $fields = ['name', 'email', 'phone', 'date_of_birth', 'address', 'emergency_contact'];
        $completedFields = 0;

        foreach ($fields as $field) {
            if (!empty($user->$field)) {
                $completedFields++;
            }
        }

        return round(($completedFields / count($fields)) * 100);
    }

    private function getNextClassDate($schedule): ?string
    {
        // Mock implementation - would parse actual schedule
        // For now, just return next Monday
        return now()->next('Monday')->format('Y-m-d H:i:s');
    }

    private function getStudentNotifications($user): array
    {
        $notifications = [];

        // Check for upcoming payment due dates
        $pendingPayments = $user->payments()->where('status', 'pending')->count();
        if ($pendingPayments > 0) {
            $notifications[] = [
                'type' => 'payment',
                'title' => 'Payment Due',
                'message' => "You have {$pendingPayments} pending payment(s)",
                'priority' => 'high',
                'created_at' => now()
            ];
        }

        // Check for classes starting soon
        $upcomingEnrollments = $user->enrollments()
            ->with('course')
            ->where('status', 'active')
            ->whereHas('course', function($query) {
                $query->where('start_date', '>', now())
                      ->where('start_date', '<=', now()->addDays(7));
            })
            ->count();

        if ($upcomingEnrollments > 0) {
            $notifications[] = [
                'type' => 'class',
                'title' => 'Classes Starting Soon',
                'message' => "You have {$upcomingEnrollments} class(es) starting this week",
                'priority' => 'medium',
                'created_at' => now()
            ];
        }

        return $notifications;
    }
}