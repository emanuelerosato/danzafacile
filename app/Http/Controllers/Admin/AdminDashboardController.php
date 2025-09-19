<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\Payment;
use App\Models\Document;
use App\Models\Event;
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
            'students_total' => $school->users()->where('role', 'user')->count(),
            'students_active' => $school->users()->where('role', 'user')->where('active', true)->count(),
            'instructors_total' => $school->users()->where('role', 'admin')->count(),
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

        $upcomingEvents = Event::where('school_id', $school->id)
            ->where('start_date', '>', now())
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // Analytics data for charts
        $enrollmentTrends = CourseEnrollment::whereHas('course', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })
        ->selectRaw('MONTH(enrollment_date) as month, COUNT(*) as count')
        ->whereYear('enrollment_date', now()->year)
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        // Course distribution - get enrollment count per course
        $courseDistribution = Course::where('school_id', $school->id)
            ->withCount(['enrollments' => function($q) {
                $q->where('status', 'active');
            }])
            ->get()
            ->filter(function($course) {
                return $course->enrollments_count > 0;
            });

        $analytics = [
            'monthly_revenue' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'completed')
            ->selectRaw('MONTH(payment_date) as month, SUM(amount) as revenue')
            ->whereYear('payment_date', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(function($item) {
                return [
                    'month' => now()->month($item->month)->format('M'),
                    'revenue' => (float) $item->revenue
                ];
            }),

            'enrollment_trends' => $enrollmentTrends->isNotEmpty() ? [
                'labels' => $enrollmentTrends->map(function($item) {
                    return now()->month($item->month)->format('M');
                })->toArray(),
                'values' => $enrollmentTrends->pluck('count')->toArray(),
                'label' => 'Nuove Iscrizioni'
            ] : [
                'labels' => ['Nessun dato'],
                'values' => [0],
                'label' => 'Nuove Iscrizioni'
            ],

            'course_distribution' => $courseDistribution->isNotEmpty() ? [
                'labels' => $courseDistribution->pluck('name')->toArray(),
                'values' => $courseDistribution->pluck('enrollments_count')->toArray(),
                'label' => 'Studenti per Corso'
            ] : [
                'labels' => ['Nessun dato'],
                'values' => [0],
                'label' => 'Studenti per Corso'
            ]
        ];

        // Calculate percentage changes compared to last month
        $lastMonthStudents = $school->users()->where('role', 'user')
            ->whereMonth('created_at', now()->subMonth()->month)->count();
        $lastMonthRevenue = Payment::whereHas('user', function($q) use ($school) {
            $q->where('school_id', $school->id);
        })->where('status', 'completed')
          ->whereMonth('payment_date', now()->subMonth()->month)->sum('amount');

        $studentsChange = $lastMonthStudents > 0 ?
            round((($stats['students_total'] - $lastMonthStudents) / $lastMonthStudents) * 100, 1) : 0;
        $revenueChange = $lastMonthRevenue > 0 ?
            round((($stats['revenue_this_month'] - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1) : 0;

        // Quick stats for dashboard cards - formato compatibile con template
        $quickStats = [
            'total_students' => $stats['students_total'],
            'active_students' => $stats['students_active'],
            'total_courses' => $stats['courses_total'],
            'active_courses' => $stats['courses_active'],
            'monthly_revenue' => $stats['revenue_this_month'],
            'upcoming_events' => $upcomingEvents->count(),
            'total_events' => Event::where('school_id', $school->id)->count(),
            'pending_payments' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'pending')->count(),

            // Dynamic change percentages
            'students_change' => abs($studentsChange),
            'students_change_type' => $studentsChange >= 0 ? 'increase' : 'decrease',
            'courses_change' => 0, // No course changes to calculate yet
            'courses_change_type' => 'neutral',
            'revenue_change' => abs($revenueChange),
            'revenue_change_type' => $revenueChange >= 0 ? 'increase' : 'decrease',
            'events_change' => 0, // No event changes to calculate yet
            'events_change_type' => 'neutral'
        ];

        // Add recent enrollments to variable name expected by template
        $recent_enrollments = $recentEnrollments;

        return view('admin.dashboard', compact(
            'school',
            'stats',
            'quickStats',
            'recent_enrollments',
            'recentEnrollments',
            'recentPayments',
            'pendingDocuments',
            'upcomingCourses',
            'upcomingEvents',
            'analytics'
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
                ->where('role', 'user')
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
            fputcsv($file, ['Studenti Totali', $school->users()->where('role', 'user')->count()]);
            fputcsv($file, ['Istruttori', $school->users()->where('role', 'admin')->count()]);
            fputcsv($file, ['Corsi Attivi', $school->courses()->where('active', true)->count()]);
            fputcsv($file, ['Iscrizioni Totali', CourseEnrollment::whereHas('course', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->count()]);
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}