<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Mobile API Controllers (Step 2 - Flutter Integration)
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Admin\AdminController;
use App\Http\Controllers\API\Admin\CourseController as AdminCourseController;
use App\Http\Controllers\API\Admin\StudentController as AdminStudentController;
use App\Http\Controllers\API\Student\ProfileController as StudentProfileController;
use App\Http\Controllers\API\Student\CourseController as StudentCourseController;
use App\Http\Controllers\API\Student\EnrollmentController;

// Legacy Web Controllers (Step 1 - Maintained for compatibility)
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\SchoolController as SuperAdminSchoolController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EnrollmentController as WebEnrollmentController;
use App\Http\Controllers\Admin\SchoolPaymentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentCourseController as WebStudentCourseController;
use App\Http\Controllers\Student\ProfileController;
use App\Http\Controllers\Shared\DocumentController;
use App\Http\Controllers\Shared\MediaItemController;

// Public API routes
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// API v1 routes
Route::prefix('v1')->middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {

    // User info endpoint
    Route::get('/user', function (Request $request) {
        return response()->json([
            'user' => $request->user()->load('school'),
            'permissions' => [
                'is_super_admin' => $request->user()->isSuperAdmin(),
                'is_admin' => $request->user()->isAdmin(),
                'is_instructor' => $request->user()->isInstructor(),
                'is_student' => $request->user()->isStudent(),
            ]
        ]);
    });

    // SUPER ADMIN API ROUTES
    Route::middleware(['role:super_admin'])->prefix('super-admin')->group(function () {
        
        // Dashboard statistics
        Route::get('/dashboard/stats', [SuperAdminController::class, 'getStats']);
        Route::get('/reports', [SuperAdminController::class, 'reportsApi']);
        
        // Schools API
        Route::apiResource('schools', SuperAdminSchoolController::class);
        Route::post('schools/{school}/toggle-status', [SuperAdminSchoolController::class, 'toggleStatus']);
        Route::post('schools/bulk-action', [SuperAdminSchoolController::class, 'bulkAction']);
        
        // Users API
        Route::apiResource('users', SuperAdminUserController::class);
        Route::post('users/{user}/toggle-status', [SuperAdminUserController::class, 'toggleStatus']);
        Route::post('users/bulk-action', [SuperAdminUserController::class, 'bulkAction']);
        Route::post('users/{user}/impersonate', [SuperAdminUserController::class, 'impersonate']);
    });

    // ADMIN API ROUTES
    Route::middleware(['role:admin', 'school.ownership'])->prefix('admin')->group(function () {
        
        // Dashboard
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'getStats']);
        
        // Courses API
        Route::apiResource('courses', CourseController::class);
        Route::post('courses/{course}/toggle-status', [CourseController::class, 'toggleStatus']);
        Route::post('courses/{course}/duplicate', [CourseController::class, 'duplicate']);
        Route::get('courses/statistics', [CourseController::class, 'getStatistics']);
        Route::post('courses/bulk-action', [CourseController::class, 'bulkAction']);
        
        // Enrollments API
        Route::apiResource('enrollments', EnrollmentController::class);
        Route::post('enrollments/{enrollment}/cancel', [EnrollmentController::class, 'cancel']);
        Route::post('enrollments/{enrollment}/reactivate', [EnrollmentController::class, 'reactivate']);
        Route::post('enrollments/bulk-action', [EnrollmentController::class, 'bulkAction']);
        Route::get('enrollments/statistics', [EnrollmentController::class, 'getStatistics']);
        
        // Payments API
        Route::apiResource('payments', SchoolPaymentController::class);
        Route::post('payments/{payment}/mark-completed', [SchoolPaymentController::class, 'markCompleted']);
        Route::post('payments/{payment}/refund', [SchoolPaymentController::class, 'refund']);
        Route::post('payments/bulk-action', [SchoolPaymentController::class, 'bulkAction']);
        Route::get('payments/statistics', [SchoolPaymentController::class, 'getStatistics']);
    });

    // STUDENT API ROUTES
    Route::middleware('role:student')->prefix('student')->group(function () {
        
        // Dashboard
        Route::get('/dashboard/stats', [StudentDashboardController::class, 'getStats']);
        Route::get('/dashboard/progress', [StudentDashboardController::class, 'getProgress']);
        Route::get('/dashboard/upcoming-activities', [StudentDashboardController::class, 'getUpcomingActivities']);
        Route::get('/dashboard/quick-actions', [StudentDashboardController::class, 'quickActions']);
        
        // Courses
        Route::get('courses', [StudentCourseController::class, 'index']);
        Route::get('courses/{course}', [StudentCourseController::class, 'show']);
        Route::post('courses/{course}/enroll', [StudentCourseController::class, 'enroll']);
        Route::delete('courses/{course}/cancel-enrollment', [StudentCourseController::class, 'cancelEnrollment']);
        Route::get('courses/available-slots', [StudentCourseController::class, 'getAvailableSlots']);
        
        // Profile
        Route::get('profile', [ProfileController::class, 'show']);
        Route::put('profile', [ProfileController::class, 'update']);
        Route::put('profile/password', [ProfileController::class, 'updatePassword']);
        Route::post('profile/image', [ProfileController::class, 'updateImage']);
        Route::delete('profile/image', [ProfileController::class, 'removeImage']);
    });

    // SHARED API ROUTES
    
    // Documents API (access controlled within controller)
    Route::apiResource('documents', DocumentController::class);
    Route::post('documents/{document}/approve', [DocumentController::class, 'approve']);
    Route::post('documents/{document}/reject', [DocumentController::class, 'reject']);
    Route::post('documents/bulk-action', [DocumentController::class, 'bulkAction']);
    
    // Media API (access controlled within controller)
    Route::apiResource('media', MediaItemController::class);
    Route::get('media/{mediaItem}/view', [MediaItemController::class, 'view']);
    Route::get('galleries/{gallery}/media', [MediaItemController::class, 'getByGallery']);
    Route::post('media/bulk-action', [MediaItemController::class, 'bulkAction']);
    Route::get('media/statistics', [MediaItemController::class, 'getStatistics']);

    // General utility endpoints
    Route::get('/schools', function () {
        $user = auth()->user();
        
        if ($user->isSuperAdmin()) {
            return response()->json(App\Models\School::orderBy('name')->get());
        } elseif ($user->isAdmin()) {
            return response()->json([$user->school]);
        }
        
        return response()->json([]);
    });
    
    Route::get('/courses', function () {
        $user = auth()->user();
        $query = App\Models\Course::query();
        
        if ($user->isAdmin()) {
            $query->where('school_id', $user->school_id);
        } elseif ($user->isStudent()) {
            $query->where('school_id', $user->school_id)
                  ->where('active', true);
        } elseif (!$user->isSuperAdmin()) {
            return response()->json([]);
        }
        
        return response()->json($query->with('instructor')->orderBy('name')->get());
    });
    
    Route::get('/users', function () {
        $user = auth()->user();
        $query = App\Models\User::query();
        
        if ($user->isSuperAdmin()) {
            $query->where('role', '!=', App\Models\User::ROLE_SUPER_ADMIN);
        } elseif ($user->isAdmin()) {
            $query->where('school_id', $user->school_id)
                  ->where('role', '!=', App\Models\User::ROLE_SUPER_ADMIN);
        } else {
            return response()->json([]);
        }
        
        return response()->json($query->orderBy('name')->get());
    });
});

// Mobile API routes for Flutter apps
Route::prefix('mobile/v1')->middleware('throttle:120,1')->group(function () {
    
    // Authentication endpoints (public)
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
    
    // Protected mobile endpoints
    Route::middleware('auth:sanctum')->group(function () {
        
        // Auth user endpoints
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('/auth/password', [AuthController::class, 'updatePassword']);
        
        // ADMIN MOBILE ROUTES
        Route::middleware('role:admin')->prefix('admin')->group(function () {
            
            // Dashboard
            Route::get('/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/analytics', [AdminController::class, 'analytics']);
            Route::get('/notifications', [AdminController::class, 'notifications']);
            Route::post('/notifications/{id}/mark-read', [AdminController::class, 'markNotificationRead']);
            
            // Courses Management
            Route::apiResource('courses', AdminCourseController::class);
            Route::post('courses/{course}/toggle-status', [AdminCourseController::class, 'toggleStatus']);
            Route::post('courses/{course}/duplicate', [AdminCourseController::class, 'duplicate']);
            Route::get('courses/statistics', [AdminCourseController::class, 'getStatistics']);
            
            // Students Management
            Route::apiResource('students', AdminStudentController::class);
            Route::post('students/{student}/activate', [AdminStudentController::class, 'activate']);
            Route::post('students/{student}/deactivate', [AdminStudentController::class, 'deactivate']);
            Route::get('students/{student}/enrollments', [AdminStudentController::class, 'enrollments']);
            Route::get('students/{student}/payments', [AdminStudentController::class, 'payments']);
            Route::post('students/{student}/reset-password', [AdminStudentController::class, 'resetPassword']);
            Route::get('students/statistics', [AdminStudentController::class, 'statistics']);
        });
        
        // STUDENT MOBILE ROUTES
        Route::middleware('role:student')->prefix('student')->group(function () {
            
            // Profile Management
            Route::get('/profile', [StudentProfileController::class, 'show']);
            Route::put('/profile', [StudentProfileController::class, 'update']);
            Route::put('/profile/password', [StudentProfileController::class, 'updatePassword']);
            Route::put('/profile/email', [StudentProfileController::class, 'updateEmail']);
            Route::get('/profile/dashboard', [StudentProfileController::class, 'dashboard']);
            Route::match(['GET', 'PUT', 'PATCH'], '/profile/preferences', [StudentProfileController::class, 'preferences']);
            
            // Course Browsing
            Route::get('/courses', [StudentCourseController::class, 'index']);
            Route::get('/courses/{course}', [StudentCourseController::class, 'show']);
            Route::get('/courses/enrolled/me', [StudentCourseController::class, 'enrolled']);
            Route::get('/courses/recommendations', [StudentCourseController::class, 'recommendations']);
            Route::get('/courses/categories', [StudentCourseController::class, 'categories']);
            
            // Enrollment Management
            Route::post('/enrollments', [EnrollmentController::class, 'store']);
            Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show']);
            Route::post('/enrollments/{enrollment}/cancel', [EnrollmentController::class, 'cancel']);
            Route::get('/enrollments/history', [EnrollmentController::class, 'history']);
        });
        
        // Quick mobile dashboard for any authenticated user
        Route::get('/dashboard-quick', function (Request $request) {
            $user = $request->user();
            
            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user->only(['id', 'name', 'email', 'role']),
                    'school' => $user->school ? $user->school->only(['id', 'name']) : null,
                    'quick_stats' => [
                        'active_enrollments' => $user->courseEnrollments()->where('status', 'active')->count(),
                        'pending_payments' => $user->payments()->where('status', 'pending')->count(),
                        'total_courses' => $user->role === 'student' 
                            ? $user->courseEnrollments()->count()
                            : ($user->role === 'admin' ? $user->school->courses()->count() : 0),
                    ]
                ]
            ]);
        });
        
        Route::get('/notifications', function (Request $request) {
            $user = $request->user();
            $notifications = [];
            
            if ($user->isStudent()) {
                // Student notifications
                $pendingPayments = $user->payments()->where('status', 'pending')->count();
                if ($pendingPayments > 0) {
                    $notifications[] = [
                        'id' => 'payment_' . $user->id,
                        'type' => 'payment',
                        'title' => 'Pending Payments',
                        'message' => "You have {$pendingPayments} pending payment(s)",
                        'priority' => 'high',
                        'created_at' => now()
                    ];
                }
            } elseif ($user->isAdmin()) {
                // Admin notifications
                $newEnrollments = $user->school->courseEnrollments()
                    ->where('created_at', '>', now()->subDays(7))
                    ->count();
                if ($newEnrollments > 0) {
                    $notifications[] = [
                        'id' => 'enrollment_' . $user->school_id,
                        'type' => 'enrollment',
                        'title' => 'New Enrollments',
                        'message' => "{$newEnrollments} new enrollment(s) this week",
                        'priority' => 'medium',
                        'created_at' => now()
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'data' => [
                    'notifications' => $notifications,
                    'unread_count' => count($notifications)
                ]
            ]);
        });
    });
});

// Webhook endpoints for external integrations
Route::prefix('webhooks')->middleware('throttle:30,1')->group(function () {
    
    // Payment gateway webhooks
    Route::post('/payment/stripe', function (Request $request) {
        // Handle Stripe webhook
        return response()->json(['status' => 'ok']);
    });
    
    Route::post('/payment/paypal', function (Request $request) {
        // Handle PayPal webhook
        return response()->json(['status' => 'ok']);
    });
});