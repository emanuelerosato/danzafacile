<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

// Controllers Super Admin
use App\Http\Controllers\SuperAdmin\SuperAdminController;
use App\Http\Controllers\SuperAdmin\SchoolController;
use App\Http\Controllers\SuperAdmin\SuperAdminUserController;
use App\Http\Controllers\SuperAdmin\HelpdeskController;
use App\Http\Controllers\SuperAdmin\SuperAdminHelpController;

// Controllers Admin
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminCourseController;
use App\Http\Controllers\Admin\AdminEventController;
use App\Http\Controllers\Admin\AdminAttendanceController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\AdminPaymentController;
use App\Http\Controllers\Admin\AdminDocumentController;
use App\Http\Controllers\Admin\SchoolUserController;
use App\Http\Controllers\Admin\MediaGalleryController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\EventRegistrationController;
use App\Http\Controllers\Admin\StaffScheduleController;

// Controllers Student
use App\Http\Controllers\Student\StudentDashboardController;
use App\Http\Controllers\Student\StudentCourseController;
use App\Http\Controllers\Student\TicketController;

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
        Route::get('/reports-api', [SuperAdminController::class, 'reportsApi'])->name('reports-api');
        Route::get('/settings', [SuperAdminController::class, 'settings'])->name('settings');
        Route::post('/settings', [SuperAdminController::class, 'updateSettings'])->name('settings.update');
        Route::get('/logs', [SuperAdminController::class, 'logs'])->name('logs');
        
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
        
        // Helpdesk/Messaggi management
        Route::prefix('helpdesk')->name('helpdesk.')->group(function () {
            Route::get('/', [HelpdeskController::class, 'index'])->name('index');
            Route::get('/{ticket}', [HelpdeskController::class, 'show'])->name('show');
            Route::put('/{ticket}', [HelpdeskController::class, 'update'])->name('update');
            Route::delete('/{ticket}', [HelpdeskController::class, 'destroy'])->name('destroy');
            Route::post('/{ticket}/reply', [HelpdeskController::class, 'reply'])->name('reply');
            Route::patch('/{ticket}/close', [HelpdeskController::class, 'close'])->name('close');
            Route::patch('/{ticket}/reopen', [HelpdeskController::class, 'reopen'])->name('reopen');
            Route::get('/export/{format}', [HelpdeskController::class, 'export'])->name('export');
        });
        
        // Help/Guide system
        Route::get('/help', [SuperAdminHelpController::class, 'index'])->name('help');
    });
    
    // ADMIN ROUTES
    Route::middleware(['role:admin', 'school.ownership'])->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/stats', [AdminDashboardController::class, 'stats'])->name('stats');
        Route::get('/export/{type}', [AdminDashboardController::class, 'export'])->name('export');
        
        // Courses management
        Route::resource('courses', AdminCourseController::class);
        Route::patch('courses/{course}/toggle-active', [AdminCourseController::class, 'toggleActive'])->name('courses.toggle-active');
        Route::post('courses/bulk-action', [AdminCourseController::class, 'bulkAction'])->name('courses.bulk-action');
        Route::get('courses-export', [AdminCourseController::class, 'export'])->name('courses.export');

        // Events management
        Route::resource('events', AdminEventController::class);
        Route::patch('events/{event}/toggle-active', [AdminEventController::class, 'toggleActive'])->name('events.toggle-active');
        Route::post('events/bulk-action', [AdminEventController::class, 'bulkAction'])->name('events.bulk-action');
        Route::get('events-export', [AdminEventController::class, 'export'])->name('events.export');
        Route::post('events/{event}/register-user', [AdminEventController::class, 'registerUser'])->name('events.register-user');

        // Event Registrations management
        Route::prefix('event-registrations')->name('event-registrations.')->group(function () {
            Route::get('/', [EventRegistrationController::class, 'index'])->name('index');
            Route::get('/event/{event}', [EventRegistrationController::class, 'byEvent'])->name('by-event');
            Route::get('/{registration}', [EventRegistrationController::class, 'show'])->name('show');
            Route::post('/', [EventRegistrationController::class, 'store'])->name('store');
            Route::patch('/{registration}/status', [EventRegistrationController::class, 'updateStatus'])->name('update-status');
            Route::post('/bulk-update', [EventRegistrationController::class, 'bulkUpdate'])->name('bulk-update');
            Route::delete('/{registration}', [EventRegistrationController::class, 'destroy'])->name('destroy');
            Route::get('/export', [EventRegistrationController::class, 'export'])->name('export');
        });

        // Attendance management
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AdminAttendanceController::class, 'index'])->name('index');
            Route::get('/course/{course}', [AdminAttendanceController::class, 'courseAttendance'])->name('course');
            Route::get('/event/{event}', [AdminAttendanceController::class, 'eventAttendance'])->name('event');
            Route::post('/mark', [AdminAttendanceController::class, 'mark'])->name('mark');
            Route::post('/bulk-mark', [AdminAttendanceController::class, 'bulkMark'])->name('bulk-mark');
            Route::get('/user/{user}/stats', [AdminAttendanceController::class, 'userStats'])->name('user-stats');
            Route::get('/export', [AdminAttendanceController::class, 'export'])->name('export');
            Route::delete('/{attendance}', [AdminAttendanceController::class, 'destroy'])->name('destroy');
        });
        
        // Enrollments management
        Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('enrollments/{enrollment}', [EnrollmentController::class, 'show'])->name('enrollments.show');
        Route::patch('enrollments/{enrollment}/status', [EnrollmentController::class, 'updateStatus'])->name('enrollments.update-status');
        Route::post('enrollments/bulk-action', [EnrollmentController::class, 'bulkAction'])->name('enrollments.bulk-action');
        Route::get('enrollments/export', [EnrollmentController::class, 'export'])->name('enrollments.export');
        
        // Payments management
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [AdminPaymentController::class, 'index'])->name('index');
            Route::get('/create', [AdminPaymentController::class, 'create'])->name('create');
            Route::post('/', [AdminPaymentController::class, 'store'])->name('store');
            Route::get('/{payment}', [AdminPaymentController::class, 'show'])->name('show');
            Route::get('/{payment}/edit', [AdminPaymentController::class, 'edit'])->name('edit');
            Route::put('/{payment}', [AdminPaymentController::class, 'update'])->name('update');
            Route::delete('/{payment}', [AdminPaymentController::class, 'destroy'])->name('destroy');

            // Payment actions
            Route::post('/{payment}/mark-completed', [AdminPaymentController::class, 'markCompleted'])->name('mark-completed');
            Route::post('/{payment}/refund', [AdminPaymentController::class, 'refund'])->name('refund');
            Route::get('/{payment}/receipt', [AdminPaymentController::class, 'generateReceipt'])->name('receipt');
            Route::post('/{payment}/send-receipt', [AdminPaymentController::class, 'sendReceipt'])->name('send-receipt');

            // Bulk operations
            Route::post('/bulk-action', [AdminPaymentController::class, 'bulkAction'])->name('bulk-action');
            Route::get('/export', [AdminPaymentController::class, 'export'])->name('export');

            // Statistics and reports
            Route::get('/stats', [AdminPaymentController::class, 'getStats'])->name('stats');
            Route::get('/overdue', [AdminPaymentController::class, 'getOverdue'])->name('overdue');
        });
        
        // School Users management
        Route::resource('users', SchoolUserController::class)->except(['create', 'store']);
        Route::patch('users/{user}/toggle-active', [SchoolUserController::class, 'toggleActive'])->name('users.toggle-active');
        Route::post('users/bulk-action', [SchoolUserController::class, 'bulkAction'])->name('users.bulk-action');

        // Students management
        Route::resource('students', AdminStudentController::class);
        Route::patch('students/{student}/toggle-active', [AdminStudentController::class, 'toggleActive'])->name('students.toggle-active');
        Route::post('students/bulk-action', [AdminStudentController::class, 'bulkAction'])->name('students.bulk-action');
        Route::get('students-export', [AdminStudentController::class, 'export'])->name('students.export');

        // Documents management
        Route::resource('documents', AdminDocumentController::class);
        Route::get('documents/{document}/download', [AdminDocumentController::class, 'download'])->name('documents.download');
        Route::post('documents/{document}/approve', [AdminDocumentController::class, 'approve'])->name('documents.approve');
        Route::post('documents/{document}/reject', [AdminDocumentController::class, 'reject'])->name('documents.reject');
        Route::post('documents/bulk-action', [AdminDocumentController::class, 'bulkAction'])->name('documents.bulk-action');

        // Gallery management
        Route::resource('galleries', MediaGalleryController::class);
        Route::post('galleries/{gallery}/upload', [MediaGalleryController::class, 'uploadMedia'])->name('galleries.upload');
        Route::post('galleries/{gallery}/external-link', [MediaGalleryController::class, 'addExternalLink'])->name('galleries.external-link');
        Route::patch('galleries/{gallery}/media/{mediaItem}', [MediaGalleryController::class, 'updateMediaItem'])->name('galleries.media.update');
        Route::delete('galleries/{gallery}/media/{mediaItem}', [MediaGalleryController::class, 'deleteMediaItem'])->name('galleries.media.delete');
        Route::post('galleries/{gallery}/cover-image', [MediaGalleryController::class, 'setCoverImage'])->name('galleries.cover-image');

        // Staff management
        Route::resource('staff', StaffController::class);
        Route::patch('staff/{staff}/toggle-active', [StaffController::class, 'toggleActive'])->name('staff.toggle-active');
        Route::post('staff/bulk-action', [StaffController::class, 'bulkAction'])->name('staff.bulk-action');
        Route::get('staff-export', [StaffController::class, 'export'])->name('staff.export');
        Route::post('staff/{staff}/assign-course', [StaffController::class, 'assignToCourse'])->name('staff.assign-course');
        Route::delete('staff/{staff}/assignments/{assignment}', [StaffController::class, 'removeAssignment'])->name('staff.remove-assignment');

        // Schedules management
        Route::prefix('schedules')->name('schedules.')->group(function () {
            Route::get('/', [ScheduleController::class, 'index'])->name('index');
            Route::get('/manage', [ScheduleController::class, 'manage'])->name('manage');
            Route::get('/course/{course}', [ScheduleController::class, 'show'])->name('show');
            Route::put('/course/{course}', [ScheduleController::class, 'updateCourseSchedule'])->name('update-course');
            Route::get('/export', [ScheduleController::class, 'export'])->name('export');
        });

        // Staff Schedules management
        Route::prefix('staff-schedules')->name('staff-schedules.')->group(function () {
            Route::get('/', [StaffScheduleController::class, 'index'])->name('index');
            Route::get('/create', [StaffScheduleController::class, 'create'])->name('create');
            Route::post('/', [StaffScheduleController::class, 'store'])->name('store');
            Route::get('/{staffSchedule}', [StaffScheduleController::class, 'show'])->name('show');
            Route::get('/{staffSchedule}/edit', [StaffScheduleController::class, 'edit'])->name('edit');
            Route::put('/{staffSchedule}', [StaffScheduleController::class, 'update'])->name('update');
            Route::delete('/{staffSchedule}', [StaffScheduleController::class, 'destroy'])->name('destroy');

            // Actions
            Route::patch('/{staffSchedule}/confirm', [StaffScheduleController::class, 'confirm'])->name('confirm');
            Route::patch('/{staffSchedule}/complete', [StaffScheduleController::class, 'complete'])->name('complete');
            Route::patch('/{staffSchedule}/cancel', [StaffScheduleController::class, 'cancel'])->name('cancel');
            Route::patch('/{staffSchedule}/no-show', [StaffScheduleController::class, 'markNoShow'])->name('no-show');

            // Views
            Route::get('/calendar/view', [StaffScheduleController::class, 'calendar'])->name('calendar');
            Route::get('/export', [StaffScheduleController::class, 'export'])->name('export');
        });

        // Reports and Analytics
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportsController::class, 'index'])->name('index');
            Route::get('/charts-data', [ReportsController::class, 'chartsData'])->name('charts-data');
            Route::get('/export-pdf', [ReportsController::class, 'exportPdf'])->name('export-pdf');
            Route::get('/export-excel', [ReportsController::class, 'exportExcel'])->name('export-excel');
        });
    });
    
    // STUDENT ROUTES
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/progress', [StudentDashboardController::class, 'getProgress'])->name('progress');
        Route::get('/activity', [StudentDashboardController::class, 'getUpcomingActivities'])->name('activity');
        
        // Available courses and enrollments
        Route::get('courses', [StudentCourseController::class, 'index'])->name('courses.index');
        Route::get('courses/{course}', [StudentCourseController::class, 'show'])->name('courses.show');
        Route::post('courses/{course}/enroll', [StudentCourseController::class, 'enroll'])->name('courses.enroll');
        Route::delete('enrollments/{enrollment}', [StudentCourseController::class, 'cancelEnrollment'])->name('enrollments.cancel');
        
        // My enrollments
        Route::get('my-courses', [StudentCourseController::class, 'myEnrollments'])->name('my-courses');
        Route::get('my-courses/{enrollment}', [StudentCourseController::class, 'showEnrollment'])->name('my-courses.show');

        // Tickets/Messages (Helpdesk)
        Route::prefix('tickets')->name('tickets.')->group(function () {
            Route::get('/', [TicketController::class, 'index'])->name('index');
            Route::get('/create', [TicketController::class, 'create'])->name('create');
            Route::post('/', [TicketController::class, 'store'])->name('store');
            Route::get('/{ticket}', [TicketController::class, 'show'])->name('show');
            Route::post('/{ticket}/reply', [TicketController::class, 'reply'])->name('reply');
            Route::get('/stats', [TicketController::class, 'getStats'])->name('stats');
            Route::get('/recent', [TicketController::class, 'getRecent'])->name('recent');
        });
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
