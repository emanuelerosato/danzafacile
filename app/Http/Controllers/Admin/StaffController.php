<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Staff;
use App\Models\StaffCourseAssignment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Staff::with(['user:id,name,email', 'school:id,name'])
                     ->withCount(['activeCourseAssignments']);

        // Filtri
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            })->orWhere('employee_id', 'LIKE', "%{$search}%");
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('department')) {
            $query->where('department', $request->department);
        }

        if ($request->filled('employment_type')) {
            $query->where('employment_type', $request->employment_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $staff = $query->latest()->paginate(15);

        // Stats per header
        $stats = [
            'total' => Staff::count(),
            'active' => Staff::where('status', 'active')->count(),
            'instructors' => Staff::where('role', 'instructor')->count(),
            'on_leave' => Staff::where('status', 'on_leave')->count(),
        ];

        return view('admin.staff.index', compact('staff', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Staff::getAvailableRoles();
        $departments = Staff::getAvailableDepartments();
        $employmentTypes = Staff::getAvailableEmploymentTypes();
        $statuses = Staff::getAvailableStatuses();

        return view('admin.staff.create', compact('roles', 'departments', 'employmentTypes', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // User Information
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',

            // Staff Information
            'role' => 'required|in:' . implode(',', array_keys(Staff::getAvailableRoles())),
            'department' => 'nullable|in:' . implode(',', array_keys(Staff::getAvailableDepartments())),
            'employment_type' => 'required|in:' . implode(',', array_keys(Staff::getAvailableEmploymentTypes())),
            'status' => 'required|in:' . implode(',', array_keys(Staff::getAvailableStatuses())),

            // Personal Information
            'title' => 'nullable|string|max:10',
            'date_of_birth' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',

            // Professional Information
            'qualifications' => 'nullable|string|max:1000',
            'certifications' => 'nullable|string|max:1000',
            'specializations' => 'nullable|string|max:500',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'hire_date' => 'nullable|date',

            // Financial Information
            'hourly_rate' => 'nullable|numeric|min:0|max:999.99',
            'monthly_salary' => 'nullable|numeric|min:0|max:99999.99',
            'payment_method' => 'nullable|in:bank_transfer,cash,check',
            'bank_account' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:20',

            // Availability
            'availability' => 'nullable|array',
            'max_hours_per_week' => 'nullable|integer|min:1|max:80',
            'can_substitute' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Errori di validazione',
                    'errors' => $validator->errors()
                ], 422);
            }

            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Crea l'utente
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin', // Staff members sono admin della scuola
            'school_id' => Auth::user()->school_id,
            'email_verified_at' => now(),
        ]);

        // Genera employee ID
        $employeeId = Staff::generateEmployeeId(Auth::user()->school_id);

        // Crea il record staff
        $staff = Staff::create([
            'school_id' => Auth::user()->school_id,
            'user_id' => $user->id,
            'employee_id' => $employeeId,
            'role' => $request->role,
            'department' => $request->department,
            'employment_type' => $request->employment_type,
            'status' => $request->status,
            'title' => $request->title,
            'date_of_birth' => $request->date_of_birth,
            'phone' => $request->phone,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'address' => $request->address,
            'qualifications' => $request->qualifications,
            'certifications' => $request->certifications,
            'specializations' => $request->specializations,
            'years_experience' => $request->years_experience,
            'hire_date' => $request->hire_date ?: now(),
            'hourly_rate' => $request->hourly_rate,
            'monthly_salary' => $request->monthly_salary,
            'payment_method' => $request->payment_method ?: 'bank_transfer',
            'bank_account' => $request->bank_account,
            'tax_id' => $request->tax_id,
            'availability' => $request->availability ?: [],
            'max_hours_per_week' => $request->max_hours_per_week,
            'can_substitute' => $request->boolean('can_substitute'),
            'notes' => $request->notes,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Staff member creato con successo!',
                'data' => $staff->load('user'),
                'redirect' => route('admin.staff.show', $staff)
            ], 201);
        }

        return redirect()->route('admin.staff.show', $staff)
                        ->with('success', 'Staff member creato con successo!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Staff $staff)
    {
        $staff->load([
            'user:id,name,email,created_at',
            'school:id,name',
            'courseAssignments.course:id,name,start_date,end_date',
            'courseAssignments' => function($query) {
                $query->with('course:id,name,start_date,end_date')
                      ->latest();
            }
        ]);

        // Statistiche
        $stats = [
            'active_courses' => $staff->activeCourseAssignments()->count(),
            'total_assignments' => $staff->courseAssignments()->count(),
            'weekly_hours' => $staff->getCurrentWeeklyHours(),
            'weekly_earnings' => $staff->getEstimatedWeeklyEarnings(),
        ];

        return view('admin.staff.show', compact('staff', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Staff $staff)
    {
        $staff->load('user:id,name,email');

        $roles = Staff::getAvailableRoles();
        $departments = Staff::getAvailableDepartments();
        $employmentTypes = Staff::getAvailableEmploymentTypes();
        $statuses = Staff::getAvailableStatuses();

        return view('admin.staff.edit', compact('staff', 'roles', 'departments', 'employmentTypes', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Staff $staff)
    {
        $validator = Validator::make($request->all(), [
            // User Information
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($staff->user_id)
            ],

            // Staff Information
            'role' => 'required|in:' . implode(',', array_keys(Staff::getAvailableRoles())),
            'department' => 'nullable|in:' . implode(',', array_keys(Staff::getAvailableDepartments())),
            'employment_type' => 'required|in:' . implode(',', array_keys(Staff::getAvailableEmploymentTypes())),
            'status' => 'required|in:' . implode(',', array_keys(Staff::getAvailableStatuses())),

            // Personal Information
            'title' => 'nullable|string|max:10',
            'date_of_birth' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',

            // Professional Information
            'qualifications' => 'nullable|string|max:1000',
            'certifications' => 'nullable|string|max:1000',
            'specializations' => 'nullable|string|max:500',
            'years_experience' => 'nullable|integer|min:0|max:50',
            'hire_date' => 'nullable|date',
            'termination_date' => 'nullable|date|after:hire_date',

            // Financial Information
            'hourly_rate' => 'nullable|numeric|min:0|max:999.99',
            'monthly_salary' => 'nullable|numeric|min:0|max:99999.99',
            'payment_method' => 'nullable|in:bank_transfer,cash,check',
            'bank_account' => 'nullable|string|max:50',
            'tax_id' => 'nullable|string|max:20',

            // Availability
            'availability' => 'nullable|array',
            'max_hours_per_week' => 'nullable|integer|min:1|max:80',
            'can_substitute' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->withInput();
        }

        // Aggiorna l'utente
        $staff->user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        // Aggiorna il record staff
        $staff->update([
            'role' => $request->role,
            'department' => $request->department,
            'employment_type' => $request->employment_type,
            'status' => $request->status,
            'title' => $request->title,
            'date_of_birth' => $request->date_of_birth,
            'phone' => $request->phone,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'address' => $request->address,
            'qualifications' => $request->qualifications,
            'certifications' => $request->certifications,
            'specializations' => $request->specializations,
            'years_experience' => $request->years_experience,
            'hire_date' => $request->hire_date,
            'termination_date' => $request->termination_date,
            'hourly_rate' => $request->hourly_rate,
            'monthly_salary' => $request->monthly_salary,
            'payment_method' => $request->payment_method,
            'bank_account' => $request->bank_account,
            'tax_id' => $request->tax_id,
            'availability' => $request->availability ?: [],
            'max_hours_per_week' => $request->max_hours_per_week,
            'can_substitute' => $request->boolean('can_substitute'),
            'notes' => $request->notes,
        ]);

        return redirect()->route('admin.staff.show', $staff)
                        ->with('success', 'Staff member aggiornato con successo!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Staff $staff)
    {
        // Verifica che non ci siano assegnazioni attive
        if ($staff->activeCourseAssignments()->count() > 0) {
            return redirect()->back()
                           ->with('error', 'Impossibile eliminare: lo staff ha assegnazioni attive ai corsi.');
        }

        $name = $staff->user->name;

        // Elimina lo staff (soft delete)
        $staff->delete();

        return redirect()->route('admin.staff.index')
                        ->with('success', "Staff member {$name} eliminato con successo.");
    }

    /**
     * Assign staff to course
     */
    public function assignToCourse(Request $request, Staff $staff)
    {
        $validator = Validator::make($request->all(), [
            'course_id' => 'required|exists:courses,id',
            'assignment_type' => 'required|in:' . implode(',', array_keys(StaffCourseAssignment::getAvailableAssignmentTypes())),
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'rate_override' => 'nullable|numeric|min:0|max:999.99',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errore di validazione',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verifica che lo staff possa essere assegnato
        $course = Course::find($request->course_id);
        if (!$staff->canBeAssignedToCourse($course)) {
            return response()->json([
                'success' => false,
                'message' => 'Lo staff non può essere assegnato a questo corso'
            ], 422);
        }

        // Crea l'assegnazione
        $assignment = StaffCourseAssignment::create([
            'staff_id' => $staff->id,
            'course_id' => $request->course_id,
            'assignment_type' => $request->assignment_type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'status' => 'active',
            'rate_override' => $request->rate_override,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Staff assegnato al corso con successo!',
            'assignment' => $assignment->load('course:id,name')
        ]);
    }

    /**
     * Remove staff from course
     */
    public function removeFromCourse(Staff $staff, StaffCourseAssignment $assignment)
    {
        // Verifica che l'assegnazione appartenga al staff
        if ($assignment->staff_id !== $staff->id) {
            abort(404);
        }

        $assignment->update(['status' => 'completed', 'end_date' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Staff rimosso dal corso con successo!'
        ]);
    }

    /**
     * Toggle staff status
     */
    public function toggleStatus(Staff $staff)
    {
        $newStatus = $staff->status === 'active' ? 'inactive' : 'active';
        $staff->update(['status' => $newStatus]);

        return redirect()->back()
                        ->with('success', "Status aggiornato a: " . Staff::getAvailableStatuses()[$newStatus]);
    }

    /**
     * Get available courses for assignment
     */
    public function getAvailableCourses(Staff $staff)
    {
        $courses = Course::where('school_id', Auth::user()->school_id)
                        ->select('id', 'name', 'start_date', 'end_date')
                        ->get();

        return response()->json($courses);
    }

    /**
     * Bulk actions
     */
    public function bulkAction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'action' => 'required|in:activate,deactivate,delete',
            'staff_ids' => 'required|array|min:1',
            'staff_ids.*' => 'exists:staff,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                           ->withErrors($validator)
                           ->with('error', 'Errore nei dati forniti');
        }

        $staffMembers = Staff::whereIn('id', $request->staff_ids);
        $count = $staffMembers->count();

        switch ($request->action) {
            case 'activate':
                $staffMembers->update(['status' => 'active']);
                $message = "{$count} staff members attivati";
                break;

            case 'deactivate':
                $staffMembers->update(['status' => 'inactive']);
                $message = "{$count} staff members disattivati";
                break;

            case 'delete':
                // Verifica che non ci siano assegnazioni attive
                $withActiveAssignments = $staffMembers->whereHas('activeCourseAssignments')->count();
                if ($withActiveAssignments > 0) {
                    return redirect()->back()
                                   ->with('error', "Impossibile eliminare {$withActiveAssignments} staff con assegnazioni attive");
                }

                $staffMembers->delete();
                $message = "{$count} staff members eliminati";
                break;
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Validate email uniqueness for AJAX requests
     */
    public function validateEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'exclude_id' => 'nullable|integer|exists:staff,id'
        ]);

        $email = $request->input('email');
        $excludeId = $request->input('exclude_id');

        // Check if email exists in users table (for staff accounts)
        $query = \App\Models\User::where('email', $email);

        if ($excludeId) {
            // If editing, exclude current staff's user
            $currentStaff = Staff::find($excludeId);
            if ($currentStaff && $currentStaff->user_id) {
                $query->where('id', '!=', $currentStaff->user_id);
            }
        }

        $emailExists = $query->exists();

        return response()->json([
            'unique' => !$emailExists,
            'message' => $emailExists ? 'Email già in uso' : 'Email disponibile'
        ]);
    }
}
