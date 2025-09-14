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
use App\Http\Controllers\Admin\AdminCourseController as WebAdminCourseController;
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
        Route::apiResource('schools', SuperAdminSchoolController::class)->names([
            'index' => 'api.super-admin.schools.index',
            'store' => 'api.super-admin.schools.store',
            'show' => 'api.super-admin.schools.show',
            'update' => 'api.super-admin.schools.update',
            'destroy' => 'api.super-admin.schools.destroy'
        ]);
        Route::post('schools/{school}/toggle-status', [SuperAdminSchoolController::class, 'toggleStatus'])->name('api.super-admin.schools.toggle-status');
        Route::post('schools/bulk-action', [SuperAdminSchoolController::class, 'bulkAction'])->name('api.super-admin.schools.bulk-action');

        // Users API
        Route::apiResource('users', SuperAdminUserController::class)->names([
            'index' => 'api.super-admin.users.index',
            'store' => 'api.super-admin.users.store',
            'show' => 'api.super-admin.users.show',
            'update' => 'api.super-admin.users.update',
            'destroy' => 'api.super-admin.users.destroy'
        ]);
        Route::post('users/{user}/toggle-status', [SuperAdminUserController::class, 'toggleStatus'])->name('api.super-admin.users.toggle-status');
        Route::post('users/bulk-action', [SuperAdminUserController::class, 'bulkAction'])->name('api.super-admin.users.bulk-action');
        Route::post('users/{user}/impersonate', [SuperAdminUserController::class, 'impersonate'])->name('api.super-admin.users.impersonate');
    });

    // ADMIN API ROUTES
    Route::middleware(['role:admin', 'school.ownership'])->prefix('admin')->group(function () {
        
        // Dashboard
        Route::get('/dashboard/stats', [AdminDashboardController::class, 'getStats']);
        
        // Courses API
        Route::apiResource('courses', WebAdminCourseController::class)->names([
            'index' => 'api.admin.courses.index',
            'store' => 'api.admin.courses.store',
            'show' => 'api.admin.courses.show',
            'update' => 'api.admin.courses.update',
            'destroy' => 'api.admin.courses.destroy'
        ]);
        Route::post('courses/{course}/toggle-status', [WebAdminCourseController::class, 'toggleStatus'])->name('api.admin.courses.toggle-status');
        Route::post('courses/{course}/duplicate', [WebAdminCourseController::class, 'duplicate'])->name('api.admin.courses.duplicate');
        Route::get('courses/statistics', [WebAdminCourseController::class, 'getStatistics'])->name('api.admin.courses.statistics');
        Route::post('courses/bulk-action', [WebAdminCourseController::class, 'bulkAction'])->name('api.admin.courses.bulk-action');
        
        // Enrollments API
        Route::apiResource('enrollments', EnrollmentController::class)->names([
            'index' => 'api.admin.enrollments.index',
            'store' => 'api.admin.enrollments.store',
            'show' => 'api.admin.enrollments.show',
            'update' => 'api.admin.enrollments.update',
            'destroy' => 'api.admin.enrollments.destroy'
        ]);
        Route::post('enrollments/{enrollment}/cancel', [EnrollmentController::class, 'cancel'])->name('api.admin.enrollments.cancel');
        Route::post('enrollments/{enrollment}/reactivate', [EnrollmentController::class, 'reactivate'])->name('api.admin.enrollments.reactivate');
        Route::post('enrollments/bulk-action', [EnrollmentController::class, 'bulkAction'])->name('api.admin.enrollments.bulk-action');
        Route::get('enrollments/statistics', [EnrollmentController::class, 'getStatistics'])->name('api.admin.enrollments.statistics');
        
        // Payments API
        Route::apiResource('payments', SchoolPaymentController::class)->names([
            'index' => 'api.admin.payments.index',
            'store' => 'api.admin.payments.store',
            'show' => 'api.admin.payments.show',
            'update' => 'api.admin.payments.update',
            'destroy' => 'api.admin.payments.destroy'
        ]);
        Route::post('payments/{payment}/mark-completed', [SchoolPaymentController::class, 'markCompleted'])->name('api.admin.payments.mark-completed');
        Route::post('payments/{payment}/refund', [SchoolPaymentController::class, 'refund'])->name('api.admin.payments.refund');
        Route::post('payments/bulk-action', [SchoolPaymentController::class, 'bulkAction'])->name('api.admin.payments.bulk-action');
        Route::get('payments/statistics', [SchoolPaymentController::class, 'getStatistics'])->name('api.admin.payments.statistics');
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
    Route::apiResource('documents', DocumentController::class)->names([
        'index' => 'api.documents.index',
        'store' => 'api.documents.store',
        'show' => 'api.documents.show',
        'update' => 'api.documents.update',
        'destroy' => 'api.documents.destroy'
    ]);
    Route::post('documents/{document}/approve', [DocumentController::class, 'approve'])->name('api.documents.approve');
    Route::post('documents/{document}/reject', [DocumentController::class, 'reject'])->name('api.documents.reject');
    Route::post('documents/bulk-action', [DocumentController::class, 'bulkAction'])->name('api.documents.bulk-action');

    // Media API (access controlled within controller)
    Route::apiResource('media', MediaItemController::class)->names([
        'index' => 'api.media.index',
        'store' => 'api.media.store',
        'show' => 'api.media.show',
        'update' => 'api.media.update',
        'destroy' => 'api.media.destroy'
    ]);
    Route::get('media/{mediaItem}/view', [MediaItemController::class, 'view'])->name('api.media.view');
    Route::get('galleries/{gallery}/media', [MediaItemController::class, 'getByGallery'])->name('api.media.by-gallery');
    Route::post('media/bulk-action', [MediaItemController::class, 'bulkAction'])->name('api.media.bulk-action');
    Route::get('media/statistics', [MediaItemController::class, 'getStatistics'])->name('api.media.statistics');

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
            Route::apiResource('courses', AdminCourseController::class)->names([
                'index' => 'api.mobile.admin.courses.index',
                'store' => 'api.mobile.admin.courses.store',
                'show' => 'api.mobile.admin.courses.show',
                'update' => 'api.mobile.admin.courses.update',
                'destroy' => 'api.mobile.admin.courses.destroy'
            ]);
            Route::post('courses/{course}/toggle-status', [AdminCourseController::class, 'toggleStatus'])->name('api.mobile.admin.courses.toggle-status');
            Route::post('courses/{course}/duplicate', [AdminCourseController::class, 'duplicate'])->name('api.mobile.admin.courses.duplicate');
            Route::get('courses/statistics', [AdminCourseController::class, 'getStatistics'])->name('api.mobile.admin.courses.statistics');
            
            // Students Management
            Route::apiResource('students', AdminStudentController::class)->names([
                'index' => 'api.mobile.admin.students.index',
                'store' => 'api.mobile.admin.students.store',
                'show' => 'api.mobile.admin.students.show',
                'update' => 'api.mobile.admin.students.update',
                'destroy' => 'api.mobile.admin.students.destroy'
            ]);
            Route::post('students/{student}/activate', [AdminStudentController::class, 'activate'])->name('api.mobile.admin.students.activate');
            Route::post('students/{student}/deactivate', [AdminStudentController::class, 'deactivate'])->name('api.mobile.admin.students.deactivate');
            Route::get('students/{student}/enrollments', [AdminStudentController::class, 'enrollments'])->name('api.mobile.admin.students.enrollments');
            Route::get('students/{student}/payments', [AdminStudentController::class, 'payments'])->name('api.mobile.admin.students.payments');
            Route::post('students/{student}/reset-password', [AdminStudentController::class, 'resetPassword'])->name('api.mobile.admin.students.reset-password');
            Route::get('students/statistics', [AdminStudentController::class, 'statistics'])->name('api.mobile.admin.students.statistics');
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
            Route::post('/enrollments', [EnrollmentController::class, 'store'])->name('api.mobile.student.enrollments.store');
            Route::get('/enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('api.mobile.student.enrollments.show');
            Route::post('/enrollments/{enrollment}/cancel', [EnrollmentController::class, 'cancel'])->name('api.mobile.student.enrollments.cancel');
            Route::get('/enrollments/history', [EnrollmentController::class, 'history'])->name('api.mobile.student.enrollments.history');
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