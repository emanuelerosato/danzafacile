<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Document;
use App\Models\CourseEnrollment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use App\Exports\SuperAdminReportExport;

class SuperAdminController extends Controller
{
    /**
     * Display Super Admin dashboard with system overview
     */
    public function index()
    {
        $stats = [
            'schools_total' => School::count(),
            'schools_active' => School::where('active', true)->count(),
            'users_total' => User::count(),
            'admins_total' => User::where('role', User::ROLE_ADMIN)->count(),
            'students_total' => User::where('role', User::ROLE_STUDENT)->count(),
            'courses_total' => Course::count(),
            'courses_active' => Course::where('active', true)->count(),
            'payments_total' => Payment::sum('amount'),
            'payments_month' => Payment::whereMonth('payment_date', now()->month)->sum('amount'),
            'documents_pending' => Document::where('status', 'pending')->count(),
        ];

        $recent_schools = School::latest()->take(5)->get();
        $recent_users = User::latest()->take(10)->get();
        $pending_documents = Document::where('status', 'pending')->with('user')->latest()->take(5)->get();

        return view('super-admin.dashboard', compact('stats', 'recent_schools', 'recent_users', 'pending_documents'));
    }

    /**
     * System settings and configuration
     */
    public function settings()
    {
        // Get current settings from database or use defaults
        $currentSettings = [
            'app_name' => \App\Models\Setting::get('app_name', config('app.name')),
            'app_description' => \App\Models\Setting::get('app_description', 'Sistema di gestione per scuole di danza'),
            'contact_email' => \App\Models\Setting::get('contact_email', 'info@danzafacile.it'),
            'contact_phone' => \App\Models\Setting::get('contact_phone', '+39 123 456 7890'),
            'timezone' => \App\Models\Setting::get('timezone', 'Europe/Rome'),
            'default_language' => \App\Models\Setting::get('default_language', 'it'),
            'maintenance_mode' => \App\Models\Setting::get('maintenance_mode', false),
            'maintenance_message' => \App\Models\Setting::get('maintenance_message', 'Il sistema è temporaneamente in manutenzione. Riprova più tardi.'),
            
            // Email settings
            'email_enabled' => \App\Models\Setting::get('email_enabled', true),
            'smtp_host' => \App\Models\Setting::get('smtp_host', 'smtp.mailtrap.io'),
            'smtp_port' => \App\Models\Setting::get('smtp_port', 587),
            'smtp_username' => \App\Models\Setting::get('smtp_username', ''),
            'smtp_encryption' => \App\Models\Setting::get('smtp_encryption', 'tls'),
            'mail_from_name' => \App\Models\Setting::get('mail_from_name', 'Scuola di Danza'),
            'mail_from_address' => \App\Models\Setting::get('mail_from_address', 'noreply@danzafacile.it'),
            
            // Security settings
            'session_timeout' => \App\Models\Setting::get('session_timeout', 120),
            'max_login_attempts' => \App\Models\Setting::get('max_login_attempts', 5),
            'lockout_duration' => \App\Models\Setting::get('lockout_duration', 15),
            'password_min_length' => \App\Models\Setting::get('password_min_length', 8),
            'password_expiry_days' => \App\Models\Setting::get('password_expiry_days', 90),
            'require_uppercase' => \App\Models\Setting::get('require_uppercase', true),
            'require_lowercase' => \App\Models\Setting::get('require_lowercase', true),
            'require_numbers' => \App\Models\Setting::get('require_numbers', true),
            'require_symbols' => \App\Models\Setting::get('require_symbols', false),
            'enable_2fa' => \App\Models\Setting::get('enable_2fa', false),
            'force_2fa_admin' => \App\Models\Setting::get('force_2fa_admin', false),
            'force_2fa_superadmin' => \App\Models\Setting::get('force_2fa_superadmin', true),
        ];
        
        return view('super-admin.settings', compact('currentSettings'));
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request)
    {
        $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'contact_email' => 'required|email',
            'contact_phone' => 'nullable|string|max:20',
            'timezone' => 'required|string',
            'default_language' => 'required|string',
            'maintenance_mode' => 'sometimes|boolean',
            'maintenance_message' => 'nullable|string|max:1000',
            
            // Email settings
            'email_enabled' => 'sometimes|boolean',
            'smtp_host' => 'nullable|string|max:255',
            'smtp_port' => 'nullable|integer|min:1|max:65535',
            'smtp_username' => 'nullable|string|max:255',
            'smtp_password' => 'nullable|string|max:255',
            'smtp_encryption' => 'nullable|in:tls,ssl',
            'mail_from_name' => 'nullable|string|max:255',
            'mail_from_address' => 'nullable|email|max:255',
            
            // Security settings
            'session_timeout' => 'sometimes|integer|min:5|max:1440',
            'max_login_attempts' => 'sometimes|integer|min:1|max:10',
            'lockout_duration' => 'sometimes|integer|min:1|max:60',
            'password_min_length' => 'sometimes|integer|min:6|max:20',
            'password_expiry_days' => 'sometimes|integer|min:0|max:365',
            'require_uppercase' => 'sometimes|boolean',
            'require_lowercase' => 'sometimes|boolean',
            'require_numbers' => 'sometimes|boolean',
            'require_symbols' => 'sometimes|boolean',
            'enable_2fa' => 'sometimes|boolean',
            'force_2fa_admin' => 'sometimes|boolean',
            'force_2fa_superadmin' => 'sometimes|boolean',
        ]);

        try {
            // Save all settings to database
            foreach ($request->all() as $key => $value) {
                if ($key === '_token' || $key === '_method') {
                    continue;
                }
                
                // Determine type and save setting
                $type = 'string';
                if (is_bool($value) || in_array($key, ['maintenance_mode', 'email_enabled', 'require_uppercase', 'require_lowercase', 'require_numbers', 'require_symbols', 'enable_2fa', 'force_2fa_admin', 'force_2fa_superadmin'])) {
                    $type = 'boolean';
                    $value = (bool) $value;
                } elseif (is_numeric($value) && in_array($key, ['smtp_port', 'session_timeout', 'max_login_attempts', 'lockout_duration', 'password_min_length', 'password_expiry_days'])) {
                    $type = 'integer';
                    $value = (int) $value;
                }
                
                \App\Models\Setting::set($key, $value, $type);
            }
            
            // Clear config cache to reflect changes
            \Artisan::call('config:clear');

            // Clear app settings cache to reflect sidebar changes immediately
            \App\View\Composers\AppSettingsComposer::clearCache();

            return redirect()->back()->with('success', 'Impostazioni sistema aggiornate con successo.');
            
        } catch (\Exception $e) {
            \Log::error('Settings update failed', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return redirect()->back()
                ->with('error', 'Errore durante l\'aggiornamento delle impostazioni.')
                ->withInput();
        }
    }

    /**
     * System logs and activity monitor
     */
    public function logs(Request $request)
    {
        try {
            $level = $request->get('level', 'all');
            $date = $request->get('date');
            $search = $request->get('search');
            $perPage = $request->get('per_page', 50);
            
            // Get logs from Laravel log file
            $logs = $this->parseLogFile($level, $date, $search);
            
            // Paginate results
            $currentPage = $request->get('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            $paginatedLogs = array_slice($logs, $offset, $perPage);
            
            // Create pagination info
            $totalLogs = count($logs);
            $pagination = [
                'current_page' => $currentPage,
                'per_page' => $perPage,
                'total' => $totalLogs,
                'last_page' => ceil($totalLogs / $perPage),
                'from' => $offset + 1,
                'to' => min($offset + $perPage, $totalLogs)
            ];
            
            // Get log statistics
            $stats = $this->getLogStats($logs);
            
            return view('super-admin.logs', compact('paginatedLogs', 'pagination', 'stats', 'level', 'date', 'search'));
            
        } catch (\Exception $e) {
            \Log::error('Error loading system logs', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            
            return view('super-admin.logs', [
                'paginatedLogs' => [],
                'pagination' => ['current_page' => 1, 'per_page' => 50, 'total' => 0, 'last_page' => 1, 'from' => 0, 'to' => 0],
                'stats' => ['total' => 0, 'error' => 0, 'warning' => 0, 'info' => 0, 'debug' => 0],
                'error' => 'Errore durante il caricamento dei log di sistema.',
                'level' => $request->get('level', 'all'),
                'date' => $request->get('date'),
                'search' => $request->get('search')
            ]);
        }
    }
    
    /**
     * Parse Laravel log file
     */
    private function parseLogFile($level = 'all', $date = null, $search = null)
    {
        $logPath = storage_path('logs/laravel.log');
        $logs = [];
        
        if (!file_exists($logPath)) {
            return $logs;
        }
        
        $content = file_get_contents($logPath);
        $lines = explode("\n", $content);
        
        $currentLog = null;
        
        foreach ($lines as $line) {
            if (empty(trim($line))) continue;
            
            // Check if line starts with timestamp pattern [YYYY-MM-DD HH:MM:SS]
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(\w+)\.(\w+):\s+(.*)$/', $line, $matches)) {
                // Save previous log if exists
                if ($currentLog) {
                    $logs[] = $currentLog;
                }
                
                // Start new log entry
                $currentLog = [
                    'datetime' => $matches[1],
                    'environment' => $matches[2],
                    'level' => strtolower($matches[3]),
                    'message' => $matches[4],
                    'context' => '',
                    'formatted_time' => \Carbon\Carbon::parse($matches[1])->format('d/m/Y H:i:s'),
                    'time_ago' => \Carbon\Carbon::parse($matches[1])->diffForHumans(),
                    'level_color' => $this->getLogLevelColor($matches[3])
                ];
            } else {
                // Continuation of previous log (stack trace, context, etc.)
                if ($currentLog) {
                    $currentLog['context'] .= $line . "\n";
                }
            }
        }
        
        // Don't forget the last log
        if ($currentLog) {
            $logs[] = $currentLog;
        }
        
        // Sort by datetime descending (newest first)
        usort($logs, function($a, $b) {
            return strtotime($b['datetime']) - strtotime($a['datetime']);
        });
        
        // Apply filters
        if ($level !== 'all') {
            $logs = array_filter($logs, function($log) use ($level) {
                return $log['level'] === $level;
            });
        }
        
        if ($date) {
            $filterDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            $logs = array_filter($logs, function($log) use ($filterDate) {
                return strpos($log['datetime'], $filterDate) === 0;
            });
        }
        
        if ($search) {
            $logs = array_filter($logs, function($log) use ($search) {
                return stripos($log['message'], $search) !== false || 
                       stripos($log['context'], $search) !== false;
            });
        }
        
        return array_values($logs); // Reindex array
    }
    
    /**
     * Get log level color for UI
     */
    private function getLogLevelColor($level)
    {
        $colors = [
            'emergency' => 'bg-red-100 text-red-800 border-red-200',
            'alert' => 'bg-red-100 text-red-800 border-red-200',
            'critical' => 'bg-red-100 text-red-800 border-red-200',
            'error' => 'bg-red-50 text-red-700 border-red-200',
            'warning' => 'bg-yellow-50 text-yellow-700 border-yellow-200',
            'notice' => 'bg-blue-50 text-blue-700 border-blue-200',
            'info' => 'bg-blue-50 text-blue-600 border-blue-200',
            'debug' => 'bg-gray-50 text-gray-600 border-gray-200'
        ];
        
        return $colors[strtolower($level)] ?? 'bg-gray-50 text-gray-600 border-gray-200';
    }
    
    /**
     * Get log statistics
     */
    private function getLogStats($logs)
    {
        $stats = [
            'total' => count($logs),
            'error' => 0,
            'warning' => 0,
            'info' => 0,
            'debug' => 0,
            'other' => 0
        ];
        
        foreach ($logs as $log) {
            switch ($log['level']) {
                case 'error':
                case 'emergency':
                case 'alert':
                case 'critical':
                    $stats['error']++;
                    break;
                case 'warning':
                    $stats['warning']++;
                    break;
                case 'info':
                case 'notice':
                    $stats['info']++;
                    break;
                case 'debug':
                    $stats['debug']++;
                    break;
                default:
                    $stats['other']++;
            }
        }
        
        return $stats;
    }

    /**
     * Database backup and maintenance
     */
    public function maintenance()
    {
        return view('super-admin.maintenance');
    }

    /**
     * Generate system reports
     */
    public function reports(Request $request)
    {
        $type = $request->get('type', 'schools');
        $period = $request->get('period', 'month');

        $data = [];

        switch ($type) {
            case 'schools':
                $data = $this->getSchoolsReport($period);
                break;
            case 'users':
                $data = $this->getUsersReport($period);
                break;
            case 'payments':
                $data = $this->getPaymentsReport($period);
                break;
            case 'courses':
                $data = $this->getCoursesReport($period);
                break;
        }

        // Get chart data for all reports
        $chartData = $this->getChartData($period);

        return view('super-admin.reports', compact('data', 'type', 'period', 'chartData'));
    }

    /**
     * API endpoint for filtered reports data
     */
    public function reportsApi(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'type' => 'required|in:overview,schools,users,payments,courses',
                'period' => 'sometimes|in:week,month,quarter,year,custom',
                'date_from' => 'sometimes|date',
                'date_to' => 'sometimes|date|after_or_equal:date_from'
            ]);

            $type = $request->get('type', 'overview');
            $period = $request->get('period', 'month');
            $dateFrom = $request->get('date_from');
            $dateTo = $request->get('date_to');

            // Handle custom date range validation
            if ($period === 'custom') {
                if (!$dateFrom || !$dateTo) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Date range is required for custom period',
                        'error_code' => 'MISSING_DATE_RANGE'
                    ], 400);
                }
                $customPeriod = ['from' => $dateFrom, 'to' => $dateTo];
            } else {
                $customPeriod = null;
            }

            // Get filtered report data with error handling
            try {
                $data = match($type) {
                    'schools' => $this->getSchoolsReportFiltered($period, $customPeriod),
                    'users' => $this->getUsersReportFiltered($period, $customPeriod), 
                    'payments' => $this->getPaymentsReportFiltered($period, $customPeriod),
                    'courses' => $this->getCoursesReportFiltered($period, $customPeriod),
                    default => $this->getOverviewReportFiltered($period, $customPeriod)
                };
            } catch (\Exception $e) {
                \Log::error('Report data generation failed', [
                    'type' => $type,
                    'period' => $period,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Errore durante la generazione dei dati del report',
                    'error_code' => 'DATA_GENERATION_ERROR'
                ], 500);
            }

            // Get updated chart data with error handling
            try {
                $chartData = $this->getChartDataFiltered($period, $customPeriod);
            } catch (\Exception $e) {
                \Log::error('Chart data generation failed', [
                    'type' => $type,
                    'period' => $period,
                    'error' => $e->getMessage()
                ]);

                // Use empty chart data as fallback
                $chartData = [
                    'revenue_trends' => ['labels' => [], 'data' => []],
                    'user_distribution' => ['labels' => [], 'data' => []],
                    'school_performance' => ['labels' => [], 'students' => [], 'courses' => []],
                    'course_difficulty' => ['labels' => [], 'data' => []]
                ];
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'report_data' => $data,
                    'chart_data' => $chartData,
                    'filters' => [
                        'type' => $type,
                        'period' => $period,
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                    ],
                    'generated_at' => now()->toISOString()
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Parametri di filtro non validi',
                'errors' => $e->errors(),
                'error_code' => 'VALIDATION_ERROR'
            ], 422);

        } catch (\Exception $e) {
            \Log::error('Reports API unexpected error', [
                'request' => $request->all(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore interno del server. Riprova più tardi.',
                'error_code' => 'INTERNAL_SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Get filtered chart data
     */
    private function getChartDataFiltered($period, $customPeriod = null)
    {
        // Use custom period if provided, otherwise use standard period
        $actualPeriod = $customPeriod ? 'custom' : $period;
        
        return [
            'revenue_trends' => $this->getRevenueTrendsDataFiltered($actualPeriod, $customPeriod),
            'user_distribution' => $this->getUserDistributionDataFiltered($actualPeriod, $customPeriod),
            'school_performance' => $this->getSchoolPerformanceDataFiltered($actualPeriod, $customPeriod),
            'course_difficulty' => $this->getCourseDifficultyDataFiltered($actualPeriod, $customPeriod)
        ];
    }

    /**
     * Get filtered revenue trends
     */
    private function getRevenueTrendsDataFiltered($period, $customPeriod = null)
    {
        $query = Payment::where('status', 'completed');

        if ($customPeriod) {
            $query->whereBetween('payment_date', [$customPeriod['from'], $customPeriod['to']]);
        } else {
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subMonths(3),
                'year' => now()->subYear(),
                default => now()->subMonth()
            };
            $query->where('payment_date', '>=', $startDate);
        }

        if ($period === 'year' && !$customPeriod) {
            $revenues = $query->selectRaw('MONTH(payment_date) as month, YEAR(payment_date) as year, SUM(amount) as total')
                ->groupBy('month', 'year')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $monthNames = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
            $labels = $revenues->pluck('month')->map(fn($m) => $monthNames[$m - 1])->toArray();
            $data = $revenues->pluck('total')->map(fn($t) => floatval($t))->toArray();
        } else {
            $revenues = $query->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get();

            $labels = $revenues->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))->toArray();
            $data = $revenues->pluck('total')->map(fn($t) => floatval($t))->toArray();
        }

        return compact('labels', 'data');
    }

    /**
     * Get filtered user distribution
     */
    private function getUserDistributionDataFiltered($period, $customPeriod = null)
    {
        $query = User::query();

        if ($customPeriod) {
            $query->whereBetween('created_at', [$customPeriod['from'], $customPeriod['to']]);
        } elseif ($period !== 'year') {
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subMonths(3),
                default => now()->subMonth()
            };
            $query->where('created_at', '>=', $startDate);
        }

        $students = (clone $query)->where('role', User::ROLE_STUDENT)->count();
        $admins = (clone $query)->where('role', User::ROLE_ADMIN)->count();
        $instructors = (clone $query)->where('role', User::ROLE_INSTRUCTOR)->count();

        return [
            'labels' => ['Studenti', 'Admin', 'Istruttori'],
            'data' => [$students, $admins, $instructors]
        ];
    }

    /**
     * Get filtered school performance
     */
    private function getSchoolPerformanceDataFiltered($period, $customPeriod = null)
    {
        $query = School::withCount(['users', 'courses'])->where('active', true);

        if ($customPeriod) {
            $query->whereBetween('created_at', [$customPeriod['from'], $customPeriod['to']]);
        } elseif ($period !== 'year') {
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subMonths(3),
                default => now()->subMonth()
            };
            $query->where('created_at', '>=', $startDate);
        }

        $schools = $query->limit(5)->get();

        $labels = [];
        $studentsData = [];
        $coursesData = [];

        foreach ($schools as $school) {
            $labels[] = substr($school->name, 0, 15) . (strlen($school->name) > 15 ? '...' : '');
            $studentsData[] = $school->users_count;
            $coursesData[] = $school->courses_count;
        }

        return [
            'labels' => $labels,
            'students' => $studentsData,
            'courses' => $coursesData
        ];
    }

    /**
     * Get filtered course difficulty distribution
     */
    private function getCourseDifficultyDataFiltered($period, $customPeriod = null)
    {
        $query = Course::where('active', true);

        if ($customPeriod) {
            $query->whereBetween('created_at', [$customPeriod['from'], $customPeriod['to']]);
        } elseif ($period !== 'year') {
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subMonths(3),
                default => now()->subMonth()
            };
            $query->where('created_at', '>=', $startDate);
        }

        $difficulties = $query->selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->get();

        $labels = [];
        $data = [];

        $difficultyLabels = [
            'beginner' => 'Principiante',
            'intermediate' => 'Intermedio',
            'advanced' => 'Avanzato'
        ];

        foreach ($difficulties as $difficulty) {
            $label = $difficultyLabels[$difficulty->level] ?? ucfirst($difficulty->level);
            $labels[] = $label;
            $data[] = $difficulty->count;
        }

        return compact('labels', 'data');
    }

    /**
     * Get filtered reports for each type
     */
    private function getSchoolsReportFiltered($period, $customPeriod = null)
    {
        $query = School::query();
        
        if ($customPeriod) {
            $query->whereBetween('created_at', [$customPeriod['from'], $customPeriod['to']]);
        } elseif ($period !== 'all') {
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subMonths(3),
                'year' => now()->subYear(),
                default => now()->subMonth()
            };
            $query->where('created_at', '>=', $startDate);
        }

        return [
            'total' => $query->count(),
            'active' => (clone $query)->where('active', true)->count(),
            'inactive' => (clone $query)->where('active', false)->count(),
            'new_this_period' => $query->count(),
        ];
    }

    private function getUsersReportFiltered($period, $customPeriod = null)
    {
        $query = User::query();
        
        if ($customPeriod) {
            $query->whereBetween('created_at', [$customPeriod['from'], $customPeriod['to']]);
        } elseif ($period !== 'all') {
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subMonths(3),
                'year' => now()->subYear(),
                default => now()->subMonth()
            };
            $query->where('created_at', '>=', $startDate);
        }

        return [
            'total' => $query->count(),
            'admins' => (clone $query)->where('role', User::ROLE_ADMIN)->count(),
            'instructors' => (clone $query)->where('role', User::ROLE_INSTRUCTOR)->count(),
            'students' => (clone $query)->where('role', User::ROLE_STUDENT)->count(),
            'active' => (clone $query)->where('active', true)->count(),
        ];
    }

    private function getPaymentsReportFiltered($period, $customPeriod = null)
    {
        $query = Payment::query();
        
        if ($customPeriod) {
            $query->whereBetween('payment_date', [$customPeriod['from'], $customPeriod['to']]);
        } elseif ($period !== 'all') {
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subMonths(3),
                'year' => now()->subYear(),
                default => now()->subMonth()
            };
            $query->where('payment_date', '>=', $startDate);
        }

        return [
            'total_amount' => $query->sum('amount'),
            'total_count' => $query->count(),
            'completed' => (clone $query)->where('status', 'completed')->sum('amount'),
            'pending' => (clone $query)->where('status', 'pending')->sum('amount'),
        ];
    }

    private function getCoursesReportFiltered($period, $customPeriod = null)
    {
        $query = Course::query();
        
        if ($customPeriod) {
            $query->whereBetween('created_at', [$customPeriod['from'], $customPeriod['to']]);
        } elseif ($period !== 'all') {
            $startDate = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'quarter' => now()->subMonths(3),
                'year' => now()->subYear(),
                default => now()->subMonth()
            };
            $query->where('created_at', '>=', $startDate);
        }

        return [
            'total' => $query->count(),
            'active' => (clone $query)->where('active', true)->count(),
            'inactive' => (clone $query)->where('active', false)->count(),
            'by_level' => $query->selectRaw('level, count(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level'),
        ];
    }

    private function getOverviewReportFiltered($period, $customPeriod = null)
    {
        return [
            'schools' => $this->getSchoolsReportFiltered($period, $customPeriod),
            'users' => $this->getUsersReportFiltered($period, $customPeriod),
            'payments' => $this->getPaymentsReportFiltered($period, $customPeriod),
            'courses' => $this->getCoursesReportFiltered($period, $customPeriod),
        ];
    }

    /**
     * Get real chart data from database
     */
    private function getChartData($period)
    {
        // Revenue trends data
        $revenueTrends = $this->getRevenueTrendsData($period);
        
        // User distribution data 
        $userDistribution = $this->getUserDistributionData();
        
        // School performance data
        $schoolPerformance = $this->getSchoolPerformanceData();
        
        // Course difficulty distribution
        $courseDifficulty = $this->getCourseDifficultyData();

        return [
            'revenue_trends' => $revenueTrends,
            'user_distribution' => $userDistribution,
            'school_performance' => $schoolPerformance,
            'course_difficulty' => $courseDifficulty
        ];
    }

    /**
     * Get revenue trends data based on period
     */
    private function getRevenueTrendsData($period)
    {
        $startDate = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'quarter' => now()->subMonths(3),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        $revenues = Payment::where('status', 'completed')
            ->where('payment_date', '>=', $startDate)
            ->selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $data = [];

        if ($period === 'year') {
            // Group by month for yearly view
            $monthlyData = Payment::where('status', 'completed')
                ->where('payment_date', '>=', $startDate)
                ->selectRaw('MONTH(payment_date) as month, YEAR(payment_date) as year, SUM(amount) as total')
                ->groupBy('month', 'year')
                ->orderBy('year')
                ->orderBy('month')
                ->get();

            $monthNames = ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'];
            
            foreach ($monthlyData as $item) {
                $labels[] = $monthNames[$item->month - 1];
                $data[] = floatval($item->total);
            }
        } else {
            // Daily data for shorter periods
            foreach ($revenues as $revenue) {
                $labels[] = \Carbon\Carbon::parse($revenue->date)->format('d/m');
                $data[] = floatval($revenue->total);
            }
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get user distribution data
     */
    private function getUserDistributionData()
    {
        $students = User::where('role', User::ROLE_STUDENT)->count();
        $admins = User::where('role', User::ROLE_ADMIN)->count();
        $instructors = User::where('role', User::ROLE_INSTRUCTOR)->count();

        return [
            'labels' => ['Studenti', 'Admin', 'Istruttori'],
            'data' => [$students, $admins, $instructors]
        ];
    }

    /**
     * Get school performance data
     */
    private function getSchoolPerformanceData()
    {
        $schools = School::withCount(['users', 'courses'])
            ->where('active', true)
            ->limit(5)
            ->get();

        $labels = [];
        $studentsData = [];
        $coursesData = [];

        foreach ($schools as $school) {
            $labels[] = substr($school->name, 0, 15) . (strlen($school->name) > 15 ? '...' : '');
            $studentsData[] = $school->users_count;
            $coursesData[] = $school->courses_count;
        }

        return [
            'labels' => $labels,
            'students' => $studentsData,
            'courses' => $coursesData
        ];
    }

    /**
     * Get course difficulty distribution data
     */
    private function getCourseDifficultyData()
    {
        $difficulties = Course::selectRaw('level, COUNT(*) as count')
            ->where('active', true)
            ->groupBy('level')
            ->get();

        $labels = [];
        $data = [];

        $difficultyLabels = [
            'beginner' => 'Principiante',
            'intermediate' => 'Intermedio', 
            'advanced' => 'Avanzato'
        ];

        foreach ($difficulties as $difficulty) {
            $label = $difficultyLabels[$difficulty->level] ?? ucfirst($difficulty->level);
            $labels[] = $label;
            $data[] = $difficulty->count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    /**
     * Get dashboard statistics for API
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = [
            'schools' => [
                'total' => School::count(),
                'active' => School::where('active', true)->count(),
                'inactive' => School::where('active', false)->count(),
            ],
            'users' => [
                'total' => User::count(),
                'admins' => User::where('role', User::ROLE_ADMIN)->count(),
                'students' => User::where('role', User::ROLE_STUDENT)->count(),
                'active' => User::where('active', true)->count(),
            ],
            'courses' => [
                'total' => Course::count(),
                'active' => Course::where('active', true)->count(),
                'inactive' => Course::where('active', false)->count(),
            ],
            'payments' => [
                'total_amount' => Payment::where('status', 'completed')->sum('amount'),
                'total_count' => Payment::count(),
                'completed' => Payment::where('status', 'completed')->count(),
                'pending' => Payment::where('status', 'pending')->count(),
            ],
            'enrollments' => [
                'total' => CourseEnrollment::count(),
                'active' => CourseEnrollment::where('status', 'active')->count(),
                'completed' => CourseEnrollment::where('status', 'completed')->count(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Export reports in PDF or Excel format
     */
    public function export(Request $request, $format)
    {
        try {
            $request->validate([
                'type' => 'required|in:schools,users,payments,courses,overview',
                'period' => 'sometimes|in:week,month,year,all'
            ]);

            $type = $request->get('type', 'overview');
            $period = $request->get('period', 'month');

            // Validate format
            if (!in_array(strtolower($format), ['pdf', 'excel'])) {
                return redirect()->back()->with('error', 'Formato export non supportato. Utilizzare PDF o Excel.');
            }

            // Get report data based on type with error handling
            try {
                $data = match($type) {
                    'schools' => $this->getSchoolsReportForExport($period),
                    'users' => $this->getUsersReportForExport($period),
                    'payments' => $this->getPaymentsReportForExport($period),
                    'courses' => $this->getCoursesReportForExport($period),
                    default => $this->getOverviewReportForExport($period)
                };
            } catch (\Exception $e) {
                \Log::error('Export data generation failed', [
                    'type' => $type,
                    'period' => $period,
                    'format' => $format,
                    'error' => $e->getMessage()
                ]);

                return redirect()->back()->with('error', 'Errore durante la generazione dei dati per l\'export. Riprova più tardi.');
            }

            // Validate data exists
            if (empty($data) || (isset($data['items']) && empty($data['items']))) {
                return redirect()->back()->with('warning', 'Nessun dato disponibile per il periodo selezionato.');
            }

            $filename = "report_{$type}_{$period}_" . now()->format('Y-m-d');

            try {
                if (strtolower($format) === 'pdf') {
                    return $this->exportPDF($data, $type, $filename);
                } elseif (strtolower($format) === 'excel') {
                    return $this->exportExcel($data, $type, $filename);
                }
            } catch (\Exception $e) {
                \Log::error('Export file generation failed', [
                    'type' => $type,
                    'period' => $period,
                    'format' => $format,
                    'filename' => $filename,
                    'error' => $e->getMessage()
                ]);

                return redirect()->back()->with('error', "Errore durante la generazione del file {$format}. Riprova più tardi.");
            }

            return redirect()->back()->with('error', 'Formato export non supportato');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->with('error', 'Parametri export non validi.');

        } catch (\Exception $e) {
            \Log::error('Export unexpected error', [
                'format' => $format,
                'request' => $request->all(),
                'error' => $e->getMessage()
            ]);

            return redirect()->back()->with('error', 'Errore interno durante l\'export. Contatta il supporto tecnico.');
        }
    }

    /**
     * Export data as PDF
     */
    private function exportPDF($data, $type, $filename)
    {
        $pdf = Pdf::loadView('super-admin.exports.pdf', [
            'data' => $data,
            'type' => $type,
            'generated_at' => now()->format('d/m/Y H:i')
        ]);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Export data as Excel
     */
    private function exportExcel($data, $type, $filename)
    {
        return Excel::download(new SuperAdminReportExport($data, $type), $filename . '.xlsx');
    }

    /**
     * Get schools report data for export
     */
    private function getSchoolsReportForExport($period)
    {
        $query = School::with(['users', 'courses']);
        
        if ($period !== 'all') {
            $date = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
            };
            $query->where('created_at', '>=', $date);
        }

        $schools = $query->get();

        return [
            'title' => 'Report Scuole',
            'period' => ucfirst($period),
            'summary' => [
                'total' => $schools->count(),
                'active' => $schools->where('active', true)->count(),
                'inactive' => $schools->where('active', false)->count(),
            ],
            'items' => $schools->map(function($school) {
                return [
                    'name' => $school->name,
                    'email' => $school->email,
                    'phone' => $school->phone,
                    'address' => $school->address,
                    'active' => $school->active ? 'Sì' : 'No',
                    'users_count' => $school->users->count(),
                    'courses_count' => $school->courses->count(),
                    'created_at' => $school->created_at->format('d/m/Y'),
                ];
            })
        ];
    }

    /**
     * Get users report data for export
     */
    private function getUsersReportForExport($period)
    {
        $query = User::with('school');
        
        if ($period !== 'all') {
            $date = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(), 
                'year' => now()->subYear(),
            };
            $query->where('created_at', '>=', $date);
        }

        $users = $query->get();

        return [
            'title' => 'Report Utenti',
            'period' => ucfirst($period),
            'summary' => [
                'total' => $users->count(),
                'admins' => $users->where('role', User::ROLE_ADMIN)->count(),
                'students' => $users->where('role', User::ROLE_STUDENT)->count(),
                'active' => $users->where('active', true)->count(),
            ],
            'items' => $users->map(function($user) {
                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => ucfirst($user->role),
                    'school' => $user->school ? $user->school->name : 'N/A',
                    'active' => $user->active ? 'Sì' : 'No',
                    'email_verified' => $user->email_verified_at ? 'Sì' : 'No',
                    'created_at' => $user->created_at->format('d/m/Y'),
                ];
            })
        ];
    }

    /**
     * Get payments report data for export  
     */
    private function getPaymentsReportForExport($period)
    {
        $query = Payment::with(['user', 'course']);
        
        if ($period !== 'all') {
            $date = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
            };
            $query->where('payment_date', '>=', $date);
        }

        $payments = $query->get();

        return [
            'title' => 'Report Pagamenti',
            'period' => ucfirst($period),
            'summary' => [
                'total_amount' => '€ ' . number_format($payments->sum('amount'), 2, ',', '.'),
                'total_count' => $payments->count(),
                'completed' => $payments->where('status', 'completed')->count(),
                'pending' => $payments->where('status', 'pending')->count(),
            ],
            'items' => $payments->map(function($payment) {
                return [
                    'user' => $payment->user ? $payment->user->name : 'N/A',
                    'course' => $payment->course ? $payment->course->name : 'N/A',
                    'amount' => '€ ' . number_format($payment->amount, 2, ',', '.'),
                    'status' => ucfirst($payment->status),
                    'payment_method' => $payment->payment_method ?? 'N/A',
                    'payment_date' => $payment->payment_date ? $payment->payment_date->format('d/m/Y') : 'N/A',
                    'created_at' => $payment->created_at->format('d/m/Y'),
                ];
            })
        ];
    }

    /**
     * Get courses report data for export
     */
    private function getCoursesReportForExport($period)
    {
        $query = Course::with(['school', 'enrollments']);
        
        if ($period !== 'all') {
            $date = match($period) {
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
            };
            $query->where('created_at', '>=', $date);
        }

        $courses = $query->get();

        return [
            'title' => 'Report Corsi',
            'period' => ucfirst($period),
            'summary' => [
                'total' => $courses->count(),
                'active' => $courses->where('active', true)->count(),
                'inactive' => $courses->where('active', false)->count(),
                'total_enrollments' => $courses->sum(fn($course) => $course->enrollments->count()),
            ],
            'items' => $courses->map(function($course) {
                return [
                    'title' => $course->name,
                    'school' => $course->school ? $course->school->name : 'N/A',
                    'description' => substr($course->description, 0, 100) . '...',
                    'level' => ucfirst($course->level),
                    'price' => '€ ' . number_format($course->price, 2, ',', '.'),
                    'capacity' => $course->max_students,
                    'enrolled_count' => $course->enrollments->count(),
                    'active' => $course->active ? 'Sì' : 'No',
                    'created_at' => $course->created_at->format('d/m/Y'),
                ];
            })
        ];
    }

    /**
     * Get overview report data for export
     */
    private function getOverviewReportForExport($period)
    {
        return [
            'title' => 'Report Generale Sistema',
            'period' => ucfirst($period),
            'summary' => [
                'schools_total' => School::count(),
                'schools_active' => School::where('active', true)->count(),
                'users_total' => User::count(),
                'users_active' => User::where('active', true)->count(),
                'courses_total' => Course::count(),
                'courses_active' => Course::where('active', true)->count(),
                'payments_total' => '€ ' . number_format(Payment::where('status', 'completed')->sum('amount'), 2, ',', '.'),
                'enrollments_total' => CourseEnrollment::count(),
            ],
            'items' => [] // Overview doesn't have detailed items
        ];
    }

    /**
     * Get schools statistics report
     */
    private function getSchoolsReport($period)
    {
        // Implementation for schools report
        return [
            'total' => School::count(),
            'active' => School::where('active', true)->count(),
            'inactive' => School::where('active', false)->count(),
            'new_this_period' => School::where('created_at', '>=', now()->subMonth())->count(),
        ];
    }

    /**
     * Get users statistics report
     */
    private function getUsersReport($period)
    {
        return [
            'total' => User::count(),
            'admins' => User::where('role', User::ROLE_ADMIN)->count(),
            'instructors' => User::where('role', User::ROLE_INSTRUCTOR)->count(),
            'students' => User::where('role', User::ROLE_STUDENT)->count(),
            'active' => User::where('active', true)->count(),
        ];
    }

    /**
     * Get payments statistics report
     */
    private function getPaymentsReport($period)
    {
        $query = Payment::query();
        
        switch ($period) {
            case 'week':
                $query->where('payment_date', '>=', now()->subWeek());
                break;
            case 'month':
                $query->where('payment_date', '>=', now()->subMonth());
                break;
            case 'year':
                $query->where('payment_date', '>=', now()->subYear());
                break;
        }

        return [
            'total_amount' => $query->sum('amount'),
            'total_count' => $query->count(),
            'completed' => $query->where('status', 'completed')->sum('amount'),
            'pending' => $query->where('status', 'pending')->sum('amount'),
        ];
    }

    /**
     * Get courses statistics report
     */
    private function getCoursesReport($period)
    {
        return [
            'total' => Course::count(),
            'active' => Course::where('active', true)->count(),
            'inactive' => Course::where('active', false)->count(),
            'by_level' => Course::selectRaw('level, count(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level'),
        ];
    }

    // API METHODS FOR MOBILE/EXTERNAL ACCESS

    /**
     * Get dashboard statistics for API
     */
    public function getStats(Request $request): JsonResponse
    {
        $stats = [
            'system_overview' => [
                'schools_total' => School::count(),
                'schools_active' => School::where('active', true)->count(),
                'schools_inactive' => School::where('active', false)->count(),
                'users_total' => User::count(),
                'admins_total' => User::where('role', User::ROLE_ADMIN)->count(),
                'instructors_total' => User::where('role', User::ROLE_INSTRUCTOR)->count(),
                'students_total' => User::where('role', User::ROLE_STUDENT)->count(),
                'courses_total' => Course::count(),
                'courses_active' => Course::where('active', true)->count(),
                'enrollments_total' => CourseEnrollment::count(),
                'enrollments_active' => CourseEnrollment::where('status', 'active')->count(),
            ],
            'financial_overview' => [
                'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
                'monthly_revenue' => Payment::where('status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('amount'),
                'pending_payments' => Payment::where('status', 'pending')->sum('amount'),
                'average_course_price' => Course::avg('price'),
            ],
            'recent_activity' => [
                'new_schools_this_month' => School::whereMonth('created_at', now()->month)->count(),
                'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
                'new_enrollments_this_week' => CourseEnrollment::where('created_at', '>=', now()->subWeek())->count(),
                'pending_documents' => Document::where('status', 'pending')->count(),
            ],
            'growth_metrics' => [
                'school_growth_rate' => $this->calculateGrowthRate('schools'),
                'user_growth_rate' => $this->calculateGrowthRate('users'),
                'revenue_growth_rate' => $this->calculateRevenueGrowthRate(),
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Calculate growth rate for given entity
     */
    private function calculateGrowthRate($entity): float
    {
        $model = match($entity) {
            'schools' => School::class,
            'users' => User::class,
            default => School::class
        };

        $currentMonth = $model::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $previousMonth = $model::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        if ($previousMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
    }

    /**
     * Calculate revenue growth rate
     */
    private function calculateRevenueGrowthRate(): float
    {
        $currentMonth = Payment::where('status', 'completed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('amount');

        $previousMonth = Payment::where('status', 'completed')
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('amount');

        if ($previousMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $previousMonth) / $previousMonth) * 100, 2);
    }

    /**
     * Get detailed analytics data
     */
    public function analytics(Request $request): JsonResponse
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);

        // Revenue trends
        $revenueTrends = Payment::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Enrollment trends
        $enrollmentTrends = CourseEnrollment::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // School performance
        $schoolPerformance = School::with('courses')
            ->get()
            ->map(function ($school) {
                return [
                    'school_id' => $school->id,
                    'school_name' => $school->name,
                    'total_courses' => $school->courses->count(),
                    'active_courses' => $school->courses->where('active', true)->count(),
                    'total_students' => $school->users()->where('role', User::ROLE_STUDENT)->count(),
                    'total_revenue' => $school->payments()->where('status', 'completed')->sum('amount'),
                ];
            });

        // User distribution by role
        $userDistribution = User::selectRaw('role, COUNT(*) as count')
            ->groupBy('role')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->role => $item->count];
            });

        // Course difficulty distribution
        $courseDifficulty = Course::selectRaw('level, COUNT(*) as count')
            ->groupBy('level')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->level => $item->count];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'period' => $period,
                'revenue_trends' => $revenueTrends,
                'enrollment_trends' => $enrollmentTrends,
                'school_performance' => $schoolPerformance,
                'user_distribution' => $userDistribution,
                'course_difficulty_distribution' => $courseDifficulty,
            ]
        ]);
    }


    /**
     * Get overview report
     */
    private function getOverviewReport($period): array
    {
        return [
            'system_health' => [
                'total_schools' => School::count(),
                'active_schools' => School::where('active', true)->count(),
                'total_users' => User::count(),
                'active_users' => User::where('active', true)->count(),
                'total_courses' => Course::count(),
                'active_courses' => Course::where('active', true)->count(),
            ],
            'business_metrics' => [
                'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
                'total_enrollments' => CourseEnrollment::count(),
                'active_enrollments' => CourseEnrollment::where('status', 'active')->count(),
                'average_revenue_per_school' => School::count() > 0 
                    ? round(Payment::where('status', 'completed')->sum('amount') / School::count(), 2) 
                    : 0,
            ]
        ];
    }
}