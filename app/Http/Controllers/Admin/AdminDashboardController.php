<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\Payment;
use App\Models\Document;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    /**
     * Display Admin dashboard for specific school
     */
    public function index()
    {
        return $this->dashboard();
    }

    /**
     * Display Admin dashboard for specific school (alternative route)
     */
    public function dashboard()
    {
        $user = auth()->user();
        $school = $user->school;

        if (!$school) {
            abort(403, 'Account amministratore non associato a nessuna scuola.');
        }

        // Statistics for the school
        $stats = [
            'students_total' => $school->users()->where('role', User::ROLE_STUDENT)->count(),
            'students_active' => $school->users()->where('role', User::ROLE_STUDENT)->where('active', true)->count(),
            'instructors_total' => $school->users()->where('role', User::ROLE_INSTRUCTOR)->count(),
            'courses_total' => $school->courses()->count(),
            'courses_active' => $school->courses()->where('active', true)->count(),
            'enrollments_total' => CourseEnrollment::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count(),
            'enrollments_this_month' => CourseEnrollment::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->whereMonth('enrollment_date', now()->month)->count(),
            'revenue_total' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'completed')->sum('amount'),
            'revenue_this_month' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'completed')
              ->whereMonth('payment_date', now()->month)->sum('amount'),
            'documents_pending' => Document::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'pending')->count(),
        ];

        // Recent activities
        $recent_enrollments = CourseEnrollment::with(['user', 'course'])
            ->whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->latest('enrollment_date')
            ->take(5)
            ->get();

        $recent_payments = Payment::with('user')
            ->whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->latest('payment_date')
            ->take(5)
            ->get();

        $pending_documents = Document::with('user')
            ->whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $upcoming_courses = Course::where('school_id', $school->id)
            ->where('active', true)
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'school', 
            'stats', 
            'recent_enrollments', 
            'recent_payments', 
            'pending_documents', 
            'upcoming_courses'
        ));
    }

    /**
     * Get dashboard statistics for AJAX requests
     */
    public function getStats(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;
        $period = $request->get('period', 'month'); // week, month, year

        $dateFilter = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        $stats = [
            'new_students' => $school->users()
                ->where('role', User::ROLE_STUDENT)
                ->where('created_at', '>=', $dateFilter)
                ->count(),
            
            'new_enrollments' => CourseEnrollment::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('enrollment_date', '>=', $dateFilter)->count(),
            
            'revenue' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'completed')
              ->where('payment_date', '>=', $dateFilter)
              ->sum('amount'),
            
            'course_completion_rate' => $this->getCourseCompletionRate($school, $dateFilter),
        ];

        return response()->json($stats);
    }

    /**
     * Calculate course completion rate
     */
    private function getCourseCompletionRate($school, $dateFilter)
    {
        $totalCourses = Course::where('school_id', $school->id)
            ->where('end_date', '<=', now())
            ->where('start_date', '>=', $dateFilter)
            ->count();

        if ($totalCourses === 0) {
            return 0;
        }

        // This is a simplified calculation - you might want to implement
        // more sophisticated logic based on actual attendance or completion criteria
        $completedCourses = Course::where('school_id', $school->id)
            ->where('end_date', '<=', now())
            ->where('start_date', '>=', $dateFilter)
            ->whereHas('enrollments')
            ->count();

        return round(($completedCourses / $totalCourses) * 100, 2);
    }

    /**
     * Export dashboard report
     */
    public function exportReport(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;
        $period = $request->get('period', 'month');

        $filename = "school_report_{$school->name}_{$period}_" . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($school, $period) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, ['Report Scuola di Danza - ' . $school->name]);
            fputcsv($file, ['Generato il: ' . now()->format('d/m/Y H:i')]);
            fputcsv($file, ['Periodo: ' . ucfirst($period)]);
            fputcsv($file, []);
            
            // Statistics
            fputcsv($file, ['Statistiche Generali']);
            fputcsv($file, ['Studenti Totali', $school->users()->where('role', User::ROLE_STUDENT)->count()]);
            fputcsv($file, ['Istruttori', $school->users()->where('role', User::ROLE_INSTRUCTOR)->count()]);
            fputcsv($file, ['Corsi Attivi', $school->courses()->where('active', true)->count()]);
            fputcsv($file, ['Iscrizioni Totali', CourseEnrollment::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count()]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}