<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

// Models
use App\Models\User;
use App\Models\Course;
use App\Models\Event;
use App\Models\Staff;
use App\Models\Payment;
use App\Models\Attendance;
use App\Models\Enrollment;
use App\Models\Document;
use App\Models\MediaGallery;
use App\Exports\ReportsExport;

class ReportsController extends Controller
{
    /**
     * Display the reports dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', 'month'); // day, week, month, year
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        // Calcola le metriche principali
        $metrics = $this->calculateMetrics($startDate, $endDate);

        return view('admin.reports.index', compact('metrics', 'period'));
    }

    /**
     * API endpoint per i dati dei grafici
     */
    public function chartsData(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $chartType = $request->get('type', 'overview');

        $data = match($chartType) {
            'overview' => $this->getOverviewData($period),
            'students' => $this->getStudentsData($period),
            'courses' => $this->getCoursesData($period),
            'payments' => $this->getPaymentsData($period),
            'attendance' => $this->getAttendanceData($period),
            'staff' => $this->getStaffData($period),
            default => $this->getOverviewData($period)
        };

        return response()->json($data);
    }

    /**
     * Esporta report in formato PDF
     */
    public function exportPdf(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        $metrics = $this->calculateMetrics($startDate, $endDate);
        $reportData = [
            'metrics' => $metrics,
            'period' => $period,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'generatedAt' => Carbon::now()
        ];

        // Genera il PDF
        $pdf = Pdf::loadView('admin.reports.pdf.report', $reportData);
        $pdf->setPaper('A4', 'portrait');

        $filename = 'report-analytics-' . $period . '-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Esporta report in formato Excel
     */
    public function exportExcel(Request $request)
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);
        $endDate = Carbon::now();

        $metrics = $this->calculateMetrics($startDate, $endDate);

        $filename = 'report-analytics-' . $period . '-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(
            new ReportsExport($metrics, $period, $startDate, $endDate),
            $filename
        );
    }

    /**
     * Calcola le metriche principali
     */
    private function calculateMetrics(Carbon $startDate, Carbon $endDate): array
    {
        $schoolId = auth()->user()->admin_school_id;

        return [
            // Studenti
            'students' => [
                'total' => User::where('role', 'student')->count(),
                'new' => User::where('role', 'student')
                    ->whereBetween('created_at', [$startDate, $endDate])
                    ->count(),
                'active' => User::where('role', 'student')
                    ->where('active', true)
                    ->count(),
            ],

            // Corsi
            'courses' => [
                'total' => Course::where('school_id', $schoolId)->count(),
                'active' => Course::where('school_id', $schoolId)
                    ->where('active', true)
                    ->count(),
                'capacity_usage' => $this->getCourseCapacityUsage($schoolId),
            ],

            // Eventi
            'events' => [
                'total' => Event::where('school_id', $schoolId)->count(),
                'upcoming' => Event::where('school_id', $schoolId)
                    ->where('event_date', '>=', Carbon::now())
                    ->count(),
                'this_period' => Event::where('school_id', $schoolId)
                    ->whereBetween('event_date', [$startDate, $endDate])
                    ->count(),
            ],

            // Staff
            'staff' => [
                'total' => Staff::where('school_id', $schoolId)->count(),
                'active' => Staff::where('school_id', $schoolId)
                    ->where('status', 'active')
                    ->count(),
                'instructors' => Staff::where('school_id', $schoolId)
                    ->where('role', 'instructor')
                    ->count(),
            ],

            // Pagamenti
            'payments' => [
                'total_amount' => Payment::where('school_id', $schoolId)
                    ->where('status', 'completed')
                    ->sum('amount'),
                'this_period_amount' => Payment::where('school_id', $schoolId)
                    ->where('status', 'completed')
                    ->whereBetween('payment_date', [$startDate, $endDate])
                    ->sum('amount'),
                'pending_amount' => Payment::where('school_id', $schoolId)
                    ->where('status', 'pending')
                    ->sum('amount'),
                'count' => Payment::where('school_id', $schoolId)->count(),
            ],

            // Presenze
            'attendance' => [
                'total' => Attendance::whereHas('user', function($q) use ($schoolId) {
                    $q->whereHas('enrollments', function($eq) use ($schoolId) {
                        $eq->whereHas('course', function($cq) use ($schoolId) {
                            $cq->where('school_id', $schoolId);
                        });
                    });
                })->count(),
                'this_period' => Attendance::whereHas('user', function($q) use ($schoolId) {
                    $q->whereHas('enrollments', function($eq) use ($schoolId) {
                        $eq->whereHas('course', function($cq) use ($schoolId) {
                            $cq->where('school_id', $schoolId);
                        });
                    });
                })->whereBetween('date', [$startDate, $endDate])->count(),
                'rate' => $this->getAttendanceRate($schoolId, $startDate, $endDate),
            ],

            // Documenti
            'documents' => [
                'total' => Document::where('school_id', $schoolId)->count(),
                'pending_approval' => Document::where('school_id', $schoolId)
                    ->where('status', 'pending')
                    ->count(),
                'approved' => Document::where('school_id', $schoolId)
                    ->where('status', 'approved')
                    ->count(),
            ],

            // Gallerie
            'galleries' => [
                'total' => MediaGallery::where('school_id', $schoolId)->count(),
                'total_media' => MediaGallery::where('school_id', $schoolId)
                    ->withCount('mediaItems')
                    ->get()
                    ->sum('media_items_count'),
            ],
        ];
    }

    /**
     * Ottiene i dati per il grafico overview
     */
    private function getOverviewData(string $period): array
    {
        $schoolId = auth()->user()->admin_school_id;
        $dates = $this->generateDateRange($period);

        $studentsData = [];
        $paymentsData = [];
        $attendanceData = [];

        foreach ($dates as $date) {
            $startOfPeriod = $date['start'];
            $endOfPeriod = $date['end'];

            // Nuovi studenti per periodo
            $studentsData[] = User::where('role', 'student')
                ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                ->count();

            // Pagamenti per periodo
            $paymentsData[] = Payment::where('school_id', $schoolId)
                ->where('status', 'completed')
                ->whereBetween('payment_date', [$startOfPeriod, $endOfPeriod])
                ->sum('amount');

            // Presenze per periodo
            $attendanceData[] = Attendance::whereHas('user', function($q) use ($schoolId) {
                $q->whereHas('enrollments', function($eq) use ($schoolId) {
                    $eq->whereHas('course', function($cq) use ($schoolId) {
                        $cq->where('school_id', $schoolId);
                    });
                });
            })->whereBetween('date', [$startOfPeriod, $endOfPeriod])->count();
        }

        return [
            'labels' => array_map(fn($date) => $date['label'], $dates),
            'datasets' => [
                [
                    'label' => 'Nuovi Studenti',
                    'data' => $studentsData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ],
                [
                    'label' => 'Incassi (€)',
                    'data' => $paymentsData,
                    'borderColor' => 'rgb(16, 185, 129)',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
                [
                    'label' => 'Presenze',
                    'data' => $attendanceData,
                    'borderColor' => 'rgb(245, 158, 11)',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ]
            ]
        ];
    }

    /**
     * Ottiene i dati per il grafico studenti
     */
    private function getStudentsData(string $period): array
    {
        $dates = $this->generateDateRange($period);

        $newStudents = [];
        $activeStudents = [];

        foreach ($dates as $date) {
            $newStudents[] = User::where('role', 'student')
                ->whereBetween('created_at', [$date['start'], $date['end']])
                ->count();

            $activeStudents[] = User::where('role', 'student')
                ->where('active', true)
                ->where('created_at', '<=', $date['end'])
                ->count();
        }

        return [
            'labels' => array_map(fn($date) => $date['label'], $dates),
            'datasets' => [
                [
                    'label' => 'Nuovi Studenti',
                    'data' => $newStudents,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                ],
                [
                    'label' => 'Studenti Attivi Totali',
                    'data' => $activeStudents,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                ]
            ]
        ];
    }

    /**
     * Ottiene i dati per il grafico corsi
     */
    private function getCoursesData(string $period): array
    {
        $schoolId = auth()->user()->admin_school_id;

        $courses = Course::where('school_id', $schoolId)
            ->withCount('enrollments')
            ->get();

        return [
            'labels' => $courses->pluck('name')->toArray(),
            'datasets' => [
                [
                    'label' => 'Iscrizioni per Corso',
                    'data' => $courses->pluck('enrollments_count')->toArray(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                    ],
                ]
            ]
        ];
    }

    /**
     * Ottiene i dati per il grafico pagamenti
     */
    private function getPaymentsData(string $period): array
    {
        $schoolId = auth()->user()->admin_school_id;
        $dates = $this->generateDateRange($period);

        $completedPayments = [];
        $pendingPayments = [];

        foreach ($dates as $date) {
            $completed = Payment::where('school_id', $schoolId)
                ->where('status', 'completed')
                ->whereBetween('payment_date', [$date['start'], $date['end']])
                ->sum('amount');

            $pending = Payment::where('school_id', $schoolId)
                ->where('status', 'pending')
                ->whereBetween('due_date', [$date['start'], $date['end']])
                ->sum('amount');

            $completedPayments[] = $completed;
            $pendingPayments[] = $pending;
        }

        return [
            'labels' => array_map(fn($date) => $date['label'], $dates),
            'datasets' => [
                [
                    'label' => 'Pagamenti Completati (€)',
                    'data' => $completedPayments,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                ],
                [
                    'label' => 'Pagamenti Pendenti (€)',
                    'data' => $pendingPayments,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.8)',
                ]
            ]
        ];
    }

    /**
     * Ottiene i dati per il grafico presenze
     */
    private function getAttendanceData(string $period): array
    {
        $schoolId = auth()->user()->admin_school_id;
        $dates = $this->generateDateRange($period);

        $attendanceData = [];
        $rates = [];

        foreach ($dates as $date) {
            $attendance = Attendance::whereHas('user', function($q) use ($schoolId) {
                $q->whereHas('enrollments', function($eq) use ($schoolId) {
                    $eq->whereHas('course', function($cq) use ($schoolId) {
                        $cq->where('school_id', $schoolId);
                    });
                });
            })->whereBetween('date', [$date['start'], $date['end']])->count();

            $attendanceData[] = $attendance;
            $rates[] = $this->getAttendanceRate($schoolId, $date['start'], $date['end']);
        }

        return [
            'labels' => array_map(fn($date) => $date['label'], $dates),
            'datasets' => [
                [
                    'label' => 'Presenze Totali',
                    'data' => $attendanceData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                ],
                [
                    'label' => 'Tasso di Presenza (%)',
                    'data' => $rates,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'yAxisID' => 'y1',
                ]
            ]
        ];
    }

    /**
     * Ottiene i dati per il grafico staff
     */
    private function getStaffData(string $period): array
    {
        $schoolId = auth()->user()->admin_school_id;

        $staffByRole = Staff::where('school_id', $schoolId)
            ->select('role', DB::raw('count(*) as count'))
            ->groupBy('role')
            ->get();

        return [
            'labels' => $staffByRole->pluck('role')->map(fn($role) =>
                Staff::getAvailableRoles()[$role] ?? $role
            )->toArray(),
            'datasets' => [
                [
                    'label' => 'Staff per Ruolo',
                    'data' => $staffByRole->pluck('count')->toArray(),
                    'backgroundColor' => [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                    ],
                ]
            ]
        ];
    }

    /**
     * Helper: Ottiene la data di inizio per il periodo
     */
    private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'day' => Carbon::now()->subDays(7),
            'week' => Carbon::now()->subWeeks(12),
            'month' => Carbon::now()->subMonths(12),
            'year' => Carbon::now()->subYears(5),
            default => Carbon::now()->subMonths(12),
        };
    }

    /**
     * Helper: Genera un range di date per il periodo
     */
    private function generateDateRange(string $period): array
    {
        $dates = [];
        $end = Carbon::now();

        switch ($period) {
            case 'day':
                for ($i = 6; $i >= 0; $i--) {
                    $date = $end->copy()->subDays($i);
                    $dates[] = [
                        'start' => $date->copy()->startOfDay(),
                        'end' => $date->copy()->endOfDay(),
                        'label' => $date->format('d/m')
                    ];
                }
                break;

            case 'week':
                for ($i = 11; $i >= 0; $i--) {
                    $date = $end->copy()->subWeeks($i);
                    $dates[] = [
                        'start' => $date->copy()->startOfWeek(),
                        'end' => $date->copy()->endOfWeek(),
                        'label' => $date->format('W/Y')
                    ];
                }
                break;

            case 'month':
                for ($i = 11; $i >= 0; $i--) {
                    $date = $end->copy()->subMonths($i);
                    $dates[] = [
                        'start' => $date->copy()->startOfMonth(),
                        'end' => $date->copy()->endOfMonth(),
                        'label' => $date->format('M Y')
                    ];
                }
                break;

            case 'year':
                for ($i = 4; $i >= 0; $i--) {
                    $date = $end->copy()->subYears($i);
                    $dates[] = [
                        'start' => $date->copy()->startOfYear(),
                        'end' => $date->copy()->endOfYear(),
                        'label' => $date->format('Y')
                    ];
                }
                break;
        }

        return $dates;
    }

    /**
     * Helper: Calcola l'utilizzo della capacità dei corsi
     */
    private function getCourseCapacityUsage(int $schoolId): float
    {
        $courses = Course::where('school_id', $schoolId)
            ->withCount('enrollments')
            ->get();

        if ($courses->isEmpty()) {
            return 0;
        }

        $totalCapacity = $courses->sum('max_students');
        $totalEnrolled = $courses->sum('enrollments_count');

        return $totalCapacity > 0 ? round(($totalEnrolled / $totalCapacity) * 100, 2) : 0;
    }

    /**
     * Helper: Calcola il tasso di presenza
     */
    private function getAttendanceRate(int $schoolId, Carbon $startDate, Carbon $endDate): float
    {
        // Calcola presenze totali nel periodo
        $totalAttendances = Attendance::whereHas('user', function($q) use ($schoolId) {
            $q->whereHas('enrollments', function($eq) use ($schoolId) {
                $eq->whereHas('course', function($cq) use ($schoolId) {
                    $cq->where('school_id', $schoolId);
                });
            });
        })->whereBetween('date', [$startDate, $endDate])->count();

        // Stima delle lezioni totali possibili (approssimazione)
        $totalStudents = User::where('role', 'student')->count();
        $activeCourses = Course::where('school_id', $schoolId)->where('active', true)->count();
        $daysInPeriod = $startDate->diffInDays($endDate);

        $estimatedLessons = $totalStudents * $activeCourses * ($daysInPeriod / 7) * 2; // 2 lezioni a settimana

        return $estimatedLessons > 0 ? round(($totalAttendances / $estimatedLessons) * 100, 2) : 0;
    }
}