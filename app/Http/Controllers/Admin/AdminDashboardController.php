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
        $recentEnrollments = CourseEnrollment::with(['user', 'course'])
            ->whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->latest('enrollment_date')
            ->take(5)
            ->get();

        $recentPayments = Payment::with('user')
            ->whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->latest('payment_date')
            ->take(5)
            ->get();

        $pendingDocuments = Document::with('user')
            ->whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $upcomingCourses = Course::where('school_id', $school->id)
            ->where('active', true)
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // Quick stats for dashboard cards
        $quickStats = [
            [
                'title' => 'Studenti Totali',
                'value' => number_format($stats['students_total']),
                'icon' => 'fas fa-user-graduate',
                'color' => 'blue',
                'subtitle' => 'Attivi: ' . number_format($stats['students_active']),
                'change' => $stats['students_active'] > 0 ? '+' . number_format(($stats['students_active'] / max($stats['students_total'], 1)) * 100, 1) . '%' : '0%'
            ],
            [
                'title' => 'Corsi Attivi',
                'value' => number_format($stats['courses_active']),
                'icon' => 'fas fa-book-open',
                'color' => 'green',
                'subtitle' => 'Totali: ' . number_format($stats['courses_total']),
                'change' => $stats['courses_total'] > 0 ? '+' . number_format(($stats['courses_active'] / max($stats['courses_total'], 1)) * 100, 1) . '%' : '0%'
            ],
            [
                'title' => 'Fatturato Totale',
                'value' => '€' . number_format($stats['revenue_total'], 2),
                'icon' => 'fas fa-euro-sign',
                'color' => 'purple',
                'subtitle' => 'Questo mese: €' . number_format($stats['revenue_this_month'], 2),
                'change' => $stats['revenue_this_month'] > 0 ? '+€' . number_format($stats['revenue_this_month'], 2) : '€0'
            ],
            [
                'title' => 'Documenti Pending',
                'value' => number_format($stats['documents_pending']),
                'icon' => 'fas fa-file-alt',
                'color' => 'orange',
                'subtitle' => 'Da approvare',
                'change' => $stats['documents_pending'] > 0 ? 'Attenzione' : 'Tutto OK'
            ]
        ];

        return view('admin.dashboard', compact(
            'school',
            'stats',
            'quickStats',
            'recentEnrollments',
            'recentPayments',
            'pendingDocuments',
            'upcomingCourses'
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