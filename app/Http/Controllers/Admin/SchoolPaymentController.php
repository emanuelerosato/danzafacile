<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Models\Payment;
use App\Models\User;
use App\Models\Course;
use Illuminate\Http\Request;

class SchoolPaymentController extends Controller
{
    /**
     * Display a listing of payments for the admin's school
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;

        $query = Payment::with(['user', 'course'])
            ->whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            });

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('user', function($subq) use ($search) {
                    $subq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('transaction_id', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by payment method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        // Filter by course
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->get('course_id'));
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->get('date_to'));
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(15);

        // Statistics
        $stats = [
            'total_amount' => $query->where('status', 'completed')->sum('amount'),
            'pending_amount' => $query->where('status', 'pending')->sum('amount'),
            'this_month' => $query->where('status', 'completed')
                                  ->whereMonth('payment_date', now()->month)
                                  ->sum('amount'),
        ];

        // Get courses for filter dropdown
        $courses = Course::where('school_id', $school->id)
                        ->orderBy('name')
                        ->get();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.payments.partials.table', compact('payments'))->render(),
                'pagination' => $payments->links()->render(),
                'stats' => $stats
            ]);
        }

        return view('admin.payments.index', compact('payments', 'courses', 'stats'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create()
    {
        $user = auth()->user();
        $school = $user->school;

        $students = User::where('school_id', $school->id)
                       ->where('role', User::ROLE_STUDENT)
                       ->where('active', true)
                       ->orderBy('name')
                       ->get();

        $courses = Course::where('school_id', $school->id)
                        ->where('active', true)
                        ->orderBy('name')
                        ->get();

        return view('admin.payments.create', compact('students', 'courses'));
    }

    /**
     * Store a newly created payment
     */
    public function store(StorePaymentRequest $request)
    {
        // Ensure the user belongs to admin's school
        $student = User::findOrFail($request->user_id);
        if ($student->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $payment = Payment::create($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pagamento registrato con successo.',
                'payment' => $payment->load(['user', 'course'])
            ]);
        }

        return redirect()->route('admin.payments.index')
                        ->with('success', 'Pagamento registrato con successo.');
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment)
    {
        // Check authorization
        if ($payment->user->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $payment->load(['user', 'course']);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment
     */
    public function edit(Payment $payment)
    {
        // Check authorization
        if ($payment->user->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $user = auth()->user();
        $school = $user->school;

        $students = User::where('school_id', $school->id)
                       ->where('role', User::ROLE_STUDENT)
                       ->where('active', true)
                       ->orderBy('name')
                       ->get();

        $courses = Course::where('school_id', $school->id)
                        ->orderBy('name')
                        ->get();

        return view('admin.payments.edit', compact('payment', 'students', 'courses'));
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment)
    {
        // Check authorization
        if ($payment->user->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:cash,card,bank_transfer,paypal',
            'transaction_id' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:payment_date',
            'status' => 'required|in:pending,completed,failed,refunded',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment->update($request->validated());

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pagamento aggiornato con successo.',
                'payment' => $payment->fresh()->load(['user', 'course'])
            ]);
        }

        return redirect()->route('admin.payments.show', $payment)
                        ->with('success', 'Pagamento aggiornato con successo.');
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment)
    {
        // Check authorization
        if ($payment->user->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $payment->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pagamento eliminato con successo.'
            ]);
        }

        return redirect()->route('admin.payments.index')
                        ->with('success', 'Pagamento eliminato con successo.');
    }

    /**
     * Mark payment as completed
     */
    public function markCompleted(Payment $payment)
    {
        // Check authorization
        if ($payment->user->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        $payment->update([
            'status' => 'completed',
            'payment_date' => now()
        ]);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pagamento contrassegnato come completato.',
                'payment' => $payment->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Pagamento contrassegnato come completato.');
    }

    /**
     * Process refund
     */
    public function refund(Payment $payment)
    {
        // Check authorization
        if ($payment->user->school_id !== auth()->user()->school_id) {
            abort(403, 'Non autorizzato');
        }

        if ($payment->status !== 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Solo i pagamenti completati possono essere rimborsati.'
            ], 422);
        }

        $payment->update(['status' => 'refunded']);

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Rimborso elaborato con successo.',
                'payment' => $payment->fresh()
            ]);
        }

        return redirect()->back()->with('success', 'Rimborso elaborato con successo.');
    }

    /**
     * Get payment statistics for dashboard
     */
    public function getStatistics(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;
        $period = $request->get('period', 'month');

        $dateFilter = match($period) {
            'week' => now()->subWeek(),
            'month' => now()->subMonth(),
            'year' => now()->subYear(),
            default => now()->subMonth()
        };

        $stats = [
            'total_revenue' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'completed')
              ->where('payment_date', '>=', $dateFilter)
              ->sum('amount'),
            
            'pending_payments' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'pending')
              ->where('payment_date', '>=', $dateFilter)
              ->sum('amount'),
            
            'payment_methods' => Payment::whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            })->where('status', 'completed')
              ->where('payment_date', '>=', $dateFilter)
              ->selectRaw('payment_method, sum(amount) as total')
              ->groupBy('payment_method')
              ->pluck('total', 'payment_method'),
              
            'monthly_trend' => $this->getMonthlyTrend($school->id)
        ];

        return response()->json($stats);
    }

    /**
     * Bulk actions for multiple payments
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:mark_completed,mark_pending,delete',
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'exists:payments,id'
        ]);

        $user = auth()->user();
        $paymentIds = $request->get('payment_ids');
        $action = $request->get('action');

        // Ensure payments belong to admin's school
        $payments = Payment::whereIn('id', $paymentIds)
                          ->whereHas('user', function($q) use ($user) {
                              $q->where('school_id', $user->school_id);
                          })
                          ->get();

        switch ($action) {
            case 'mark_completed':
                $payments->each(function($payment) {
                    $payment->update([
                        'status' => 'completed',
                        'payment_date' => $payment->payment_date ?? now()
                    ]);
                });
                $message = 'Pagamenti contrassegnati come completati.';
                break;

            case 'mark_pending':
                $payments->each(function($payment) {
                    $payment->update(['status' => 'pending']);
                });
                $message = 'Pagamenti contrassegnati come in attesa.';
                break;

            case 'delete':
                $payments->each(function($payment) {
                    $payment->delete();
                });
                $message = 'Pagamenti eliminati con successo.';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }

    /**
     * Export payments to CSV
     */
    public function export(Request $request)
    {
        $user = auth()->user();
        $school = $user->school;

        $query = Payment::with(['user', 'course'])
            ->whereHas('user', function($q) use ($school) {
                $q->where('school_id', $school->id);
            });

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $filename = 'payments_' . $school->name . '_' . now()->format('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            // Header
            fputcsv($file, [
                'ID', 'Studente', 'Email', 'Corso', 'Importo', 'Metodo Pagamento', 
                'ID Transazione', 'Data Pagamento', 'Stato', 'Note'
            ]);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->user->full_name,
                    $payment->user->email,
                    $payment->course ? $payment->course->name : 'N/A',
                    number_format($payment->amount, 2, ',', '.') . ' €',
                    ucfirst(str_replace('_', ' ', $payment->payment_method)),
                    $payment->transaction_id,
                    $payment->payment_date ? $payment->payment_date->format('d/m/Y') : '',
                    ucfirst($payment->status),
                    $payment->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get monthly revenue trend
     */
    private function getMonthlyTrend($schoolId)
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = [
                'month' => $date->format('M Y'),
                'revenue' => Payment::whereHas('user', function($q) use ($schoolId) {
                    $q->where('school_id', $schoolId);
                })->where('status', 'completed')
                  ->whereMonth('payment_date', $date->month)
                  ->whereYear('payment_date', $date->year)
                  ->sum('amount')
            ];
        }

        return $months;
    }
}