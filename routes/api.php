<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\SchoolController as SuperAdminSchoolController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\SchoolPaymentController;
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentCourseController;
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
        Route::get('/reports', [SuperAdminController::class, 'reports']);
        
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

// Mobile API routes (if needed for future mobile app)
Route::prefix('mobile/v1')->middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    
    // Mobile-specific endpoints would go here
    Route::get('/dashboard', function (Request $request) {
        $user = $request->user();
        
        // Return mobile-optimized dashboard data
        return response()->json([
            'user' => $user->only(['id', 'name', 'email', 'role']),
            'quick_stats' => [
                'active_enrollments' => $user->courseEnrollments()->where('status', 'active')->count(),
                'pending_payments' => $user->payments()->where('status', 'pending')->count(),
                'upcoming_classes' => 5, // This would be calculated based on enrollment schedule
            ]
        ]);
    });
    
    Route::get('/notifications', function (Request $request) {
        // Return user notifications
        return response()->json([
            'notifications' => [],
            'unread_count' => 0
        ]);
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