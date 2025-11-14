<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Helpers\QueryHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSchoolRequest;
use App\Http\Requests\UpdateSchoolRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SchoolController extends Controller
{
    /**
     * Display a listing of schools with search and filters
     */
    public function index(Request $request)
    {
        $query = School::with(['users' => function($q) {
            $q->where('role', User::ROLE_ADMIN);
        }]);

        // Search functionality
        // SECURITY: Sanitize LIKE input to prevent SQL wildcard injection
        if ($request->filled('search')) {
            $search = QueryHelper::sanitizeLikeInput($request->get('search'));
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->get('status') === 'active';
            $query->where('active', $status);
        }

        // Filter by city
        if ($request->filled('city')) {
            $query->where('city', $request->get('city'));
        }

        $schools = $query->orderBy('name')->paginate(15);
        $cities = School::distinct()->pluck('city');

        if ($request->ajax()) {
            return response()->json([
                'html' => view('super-admin.schools.partials.table', compact('schools'))->render(),
                'pagination' => $schools->links()->render()
            ]);
        }

        return view('super-admin.schools.index', compact('schools', 'cities'));
    }

    /**
     * Show the form for creating a new school
     */
    public function create()
    {
        return view('super-admin.schools.create');
    }

    /**
     * Store a newly created school in storage
     */
    public function store(StoreSchoolRequest $request)
    {
        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo_path')) {
            $data['logo_path'] = $request->file('logo_path')->store('schools/logos', 'public');
        }

        $school = School::create($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Scuola creata con successo.',
                'school' => $school->load('users')
            ]);
        }

        return redirect()->route('super-admin.schools.index')
                        ->with('success', 'Scuola creata con successo.');
    }

    /**
     * Display the specified school with detailed information
     */
    public function show(School $school)
    {
        try {
            // Load basic relationships first
            $school->load(['users']);

            // Calculate stats with safe fallbacks
            $stats = [
                'admins_count' => $school->users()->where('role', User::ROLE_ADMIN)->count() ?? 0,
                'instructors_count' => $school->users()->where('role', User::ROLE_INSTRUCTOR)->count() ?? 0,
                'students_count' => $school->users()->where('role', User::ROLE_STUDENT)->count() ?? 0,
                'courses_count' => 0,
                'active_courses' => 0,
                'total_revenue' => 0,
                'monthly_revenue' => 0,
            ];

            // Try to load courses if they exist
            try {
                $stats['courses_count'] = $school->courses()->count() ?? 0;
                $stats['active_courses'] = $school->courses()->where('active', true)->count() ?? 0;
            } catch (\Exception $e) {
                // Courses relationship might not exist yet
                $stats['courses_count'] = 0;
                $stats['active_courses'] = 0;
            }

            // Try to load payments if they exist
            try {
                $stats['total_revenue'] = $school->payments()->where('status', 'completed')->sum('amount') ?? 0;
                $stats['monthly_revenue'] = $school->payments()
                                              ->where('status', 'completed')
                                              ->whereMonth('payment_date', now()->month)
                                              ->sum('amount') ?? 0;
            } catch (\Exception $e) {
                // Payments relationship might not exist yet
                $stats['total_revenue'] = 0;
                $stats['monthly_revenue'] = 0;
            }

            return view('super-admin.schools.show', compact('school', 'stats'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errore durante il caricamento dei dettagli della scuola.'
                ], 500);
            }

            return redirect()->route('super-admin.schools.index')
                            ->with('error', 'Errore durante il caricamento dei dettagli della scuola.');
        }
    }

    /**
     * Show the form for editing the specified school
     */
    public function edit(School $school)
    {
        return view('super-admin.schools.edit', compact('school'));
    }

    /**
     * Update the specified school in storage
     */
    public function update(UpdateSchoolRequest $request, School $school)
    {
        $data = $request->validated();

        // Handle logo upload
        if ($request->hasFile('logo_path')) {
            // Delete old logo if exists
            if ($school->logo_path) {
                Storage::disk('public')->delete($school->logo_path);
            }
            $data['logo_path'] = $request->file('logo_path')->store('schools/logos', 'public');
        }

        $school->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Scuola aggiornata con successo.',
                'school' => $school->fresh()->load('users')
            ]);
        }

        return redirect()->route('super-admin.schools.show', $school)
                        ->with('success', 'Scuola aggiornata con successo.');
    }

    /**
     * Remove the specified school from storage
     */
    public function destroy(School $school)
    {
        // Check if school has users or courses
        if ($school->users()->count() > 0 || $school->courses()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Impossibile eliminare la scuola. Contiene utenti o corsi associati.'
            ], 422);
        }

        // Delete logo if exists
        if ($school->logo_path) {
            Storage::disk('public')->delete($school->logo_path);
        }

        $school->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Scuola eliminata con successo.'
            ]);
        }

        return redirect()->route('super-admin.schools.index')
                        ->with('success', 'Scuola eliminata con successo.');
    }

    /**
     * Activate/deactivate school
     */
    public function toggleStatus(School $school)
    {
        $school->update(['active' => !$school->active]);

        $status = $school->active ? 'attivata' : 'disattivata';
        $message = "Scuola {$status} con successo.";

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'status' => $school->active
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Alias for toggleStatus to match route naming
     */
    public function toggleActive(School $school)
    {
        return $this->toggleStatus($school);
    }

    /**
     * Bulk actions for multiple schools
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'school_ids' => 'required|array',
            'school_ids.*' => 'exists:schools,id'
        ]);

        $schoolIds = $request->get('school_ids');
        $action = $request->get('action');

        switch ($action) {
            case 'activate':
                School::whereIn('id', $schoolIds)->update(['active' => true]);
                $message = 'Scuole attivate con successo.';
                break;

            case 'deactivate':
                School::whereIn('id', $schoolIds)->update(['active' => false]);
                $message = 'Scuole disattivate con successo.';
                break;

            case 'delete':
                $schools = School::whereIn('id', $schoolIds)->get();
                foreach ($schools as $school) {
                    if ($school->users()->count() === 0 && $school->courses()->count() === 0) {
                        if ($school->logo_path) {
                            Storage::disk('public')->delete($school->logo_path);
                        }
                        $school->delete();
                    }
                }
                $message = 'Scuole eliminate con successo (quelle senza utenti o corsi).';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Export schools data to CSV
     */
    public function export(Request $request)
    {
        $schools = School::with('users')->get();

        $filename = 'schools_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($schools) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'ID', 'Nome', 'Indirizzo', 'Città', 'CAP', 'Telefono', 
                'Email', 'Sito Web', 'Attiva', 'Amministratori', 'Studenti', 'Data Creazione'
            ]);

            foreach ($schools as $school) {
                fputcsv($file, [
                    $school->id,
                    $school->name,
                    $school->address,
                    $school->city,
                    $school->postal_code,
                    $school->phone,
                    $school->email,
                    $school->website,
                    $school->active ? 'Sì' : 'No',
                    $school->users()->where('role', User::ROLE_ADMIN)->count(),
                    $school->users()->where('role', User::ROLE_STUDENT)->count(),
                    $school->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export all schools data to CSV
     */
    public function exportAll(Request $request)
    {
        $query = School::with('users');
        
        // Apply same filters as index page
        // SECURITY: Sanitize LIKE input to prevent SQL wildcard injection
        if ($request->filled('search')) {
            $search = QueryHelper::sanitizeLikeInput($request->get('search'));
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->get('status') === 'active';
            $query->where('active', $status);
        }

        if ($request->filled('city')) {
            $query->where('city', $request->get('city'));
        }

        $schools = $query->orderBy('name')->get();

        $filename = 'schools_all_export_' . now()->format('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($schools) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'ID', 'Nome', 'Indirizzo', 'Città', 'CAP', 'Telefono', 
                'Email', 'Sito Web', 'Attiva', 'Amministratori', 'Studenti', 'Data Creazione'
            ]);

            foreach ($schools as $school) {
                fputcsv($file, [
                    $school->id,
                    $school->name,
                    $school->address,
                    $school->city,
                    $school->postal_code,
                    $school->phone,
                    $school->email,
                    $school->website,
                    $school->active ? 'Sì' : 'No',
                    $school->users()->where('role', User::ROLE_ADMIN)->count(),
                    $school->users()->where('role', User::ROLE_STUDENT)->count(),
                    $school->created_at->format('d/m/Y H:i')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Suspend a school
     */
    public function suspend(Request $request, School $school)
    {
        $request->validate([
            'suspension_reason' => 'nullable|string|max:255',
            'suspension_notes' => 'nullable|string|max:1000',
        ]);

        $school->update([
            'active' => false,
            'notes' => $request->suspension_notes
                ? ($school->notes ? $school->notes . "\n\n[SOSPENSIONE " . now()->format('d/m/Y H:i') . "]\nMotivo: " . $request->suspension_reason . "\n" . $request->suspension_notes : "[SOSPENSIONE " . now()->format('d/m/Y H:i') . "]\nMotivo: " . $request->suspension_reason . "\n" . $request->suspension_notes)
                : $school->notes
        ]);

        return redirect()
            ->route('super-admin.schools.show', $school)
            ->with('success', "La scuola \"{$school->name}\" è stata sospesa con successo.");
    }

    /**
     * Activate a school
     */
    public function activate(Request $request, School $school)
    {
        $request->validate([
            'activation_notes' => 'nullable|string|max:1000',
        ]);

        $school->update([
            'active' => true,
            'notes' => $request->activation_notes
                ? ($school->notes ? $school->notes . "\n\n[RIATTIVAZIONE " . now()->format('d/m/Y H:i') . "]\n" . $request->activation_notes : "[RIATTIVAZIONE " . now()->format('d/m/Y H:i') . "]\n" . $request->activation_notes)
                : $school->notes
        ]);

        return redirect()
            ->route('super-admin.schools.show', $school)
            ->with('success', "La scuola \"{$school->name}\" è stata riattivata con successo.");
    }
}