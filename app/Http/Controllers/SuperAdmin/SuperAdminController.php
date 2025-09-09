<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Models\Course;
use App\Models\Payment;
use App\Models\Document;
use Illuminate\Http\Request;

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
        return view('super-admin.settings');
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
            'maintenance_mode' => 'boolean',
        ]);

        // Logic to update system settings
        // This could be implemented using a Settings model or configuration files

        return redirect()->back()->with('success', 'Impostazioni sistema aggiornate con successo.');
    }

    /**
     * System logs and activity monitor
     */
    public function logs(Request $request)
    {
        // This would typically integrate with Laravel's logging system
        // or a custom activity log package like spatie/laravel-activitylog
        
        return view('super-admin.logs');
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

        return view('super-admin.reports', compact('data', 'type', 'period'));
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
            'by_level' => Course::selectRaw('level, count(*) as count')->groupBy('level')->pluck('count', 'level'),
        ];
    }
}