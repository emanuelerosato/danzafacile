<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// Controllers Super Admin
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\SchoolController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;

// Controllers Admin
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\SchoolPaymentController;
use App\Http\Controllers\Admin\SchoolUserController;

// Controllers Student
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentCourseController;

// Controllers Shared
use App\Http\Controllers\Shared\DocumentController;
use App\Http\Controllers\Shared\MediaItemController;

// Home page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Authentication routes
require __DIR__.'/auth.php';

// CSRF Token refresh endpoint
Route::get('/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token()
    ]);
})->middleware('auth');

// Dashboard redirect based on role
Route::get('/dashboard', function () {
    $user = auth()->user();
    
    if ($user->isSuperAdmin()) {
        return redirect()->route('super-admin.dashboard');
    } elseif ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('student.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Authenticated routes
Route::middleware('auth')->group(function () {
    
    // SUPER ADMIN ROUTES
    Route::middleware('role:super_admin')->prefix('super-admin')->name('super-admin.')->group(function () {
        Route::get('/dashboard', [SuperAdminController::class, 'index'])->name('dashboard');
        Route::get('/stats', [SuperAdminController::class, 'stats'])->name('stats');
        Route::get('/export/{type}', [SuperAdminController::class, 'export'])->name('export');
        Route::get('/reports', [SuperAdminController::class, 'reports'])->name('reports');
        Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
        
        // Schools management
        Route::get('schools/export', [SchoolController::class, 'exportAll'])->name('schools.export-all');
        Route::resource('schools', SchoolController::class);
        Route::patch('schools/{school}/toggle-active', [SchoolController::class, 'toggleActive'])->name('schools.toggle-active');
        Route::post('schools/bulk-action', [SchoolController::class, 'bulkAction'])->name('schools.bulk-action');
        Route::get('schools/{school}/export', [SchoolController::class, 'export'])->name('schools.export');
        
        // Super Admin Users management
        Route::get('users/export', [SuperAdminUserController::class, 'export'])->name('users.export');
        Route::resource('users', SuperAdminUserController::class);
        Route::patch('users/{user}/toggle-active', [SuperAdminUserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('users/bulk-action', [SuperAdminUserController::class, 'bulkAction'])->name('users.bulk-action');
        Route::post('users/{user}/impersonate', [SuperAdminUserController::class, 'impersonate'])->name('users.impersonate');
    });
    
    // ADMIN ROUTES
    Route::middleware(['role:admin', 'school.ownership'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/stats', [AdminDashboardController::class, 'stats'])->name('stats');
        Route::get('/export/{type}', [AdminDashboardController::class, 'export'])->name('export');
        
        // Courses management
        Route::resource('courses', CourseController::class);
        Route::patch('courses/{course}/toggle-active', [CourseController::class, 'toggleActive'])->name('courses.toggle-active');
        Route::post('courses/bulk-action', [CourseController::class, 'bulkAction'])->name('courses.bulk-action');
        Route::get('courses/{course}/students', [CourseController::class, 'students'])->name('courses.students');
        Route::get('courses/{course}/export', [CourseController::class, 'export'])->name('courses.export');
        
        // Enrollments management
        Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
        Route::patch('enrollments/{enrollment}/status', [EnrollmentController::class, 'updateStatus'])->name('enrollments.update-status');
        Route::post('enrollments/bulk-action', [EnrollmentController::class, 'bulkAction'])->name('enrollments.bulk-action');
        Route::get('enrollments/export', [EnrollmentController::class, 'export'])->name('enrollments.export');
        
        // Payments management
        Route::get('payments', [SchoolPaymentController::class, 'index'])->name('payments.index');
        Route::get('payments/{payment}', [SchoolPaymentController::class, 'show'])->name('payments.show');
        Route::post('payments/{payment}/refund', [SchoolPaymentController::class, 'refund'])->name('payments.refund');
        Route::get('payments/export', [SchoolPaymentController::class, 'export'])->name('payments.export');
        
        // School Users management
        Route::resource('users', SchoolUserController::class)->except(['create', 'store']);
        Route::patch('users/{user}/toggle-active', [SchoolUserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('users/bulk-action', [SchoolUserController::class, 'bulkAction'])->name('users.bulk-action');
    });
    
    // STUDENT ROUTES
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/progress', [StudentDashboardController::class, 'progress'])->name('progress');
        Route::get('/activity', [StudentDashboardController::class, 'activity'])->name('activity');
        
        // Available courses and enrollments
        Route::get('courses', [StudentCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/{course}', [StudentCourseController::class, 'show'])->name('courses.show');
        Route::post('courses/{course}/enroll', [StudentCourseController::class, 'enroll'])->name('courses.enroll');
        Route::delete('enrollments/{enrollment}', [StudentCourseController::class, 'cancelEnrollment'])->name('enrollments.cancel');
        
        // My enrollments
        Route::get('my-courses', [StudentCourseController::class, 'myCourses'])->name('my-courses');
        Route::get('my-courses/{enrollment}', [StudentCourseController::class, 'showMyCourse'])->name('my-courses.show');
    });
    
    // SHARED ROUTES (for all authenticated users)
    
    // Profile management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Documents management
    Route::resource('documents', DocumentController::class);
    Route::post('documents/{document}/approve', [DocumentController::class, 'approve'])->name('documents.approve');
    Route::post('documents/{document}/reject', [DocumentController::class, 'reject'])->name('documents.reject');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    
    // Media management
    Route::resource('media', MediaItemController::class);
    Route::post('media/{mediaItem}/move', [MediaItemController::class, 'move'])->name('media.move');
    Route::get('media/{mediaItem}/download', [MediaItemController::class, 'download'])->name('media.download');
});
