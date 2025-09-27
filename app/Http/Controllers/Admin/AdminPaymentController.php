<?php

namespace App\Http\Controllers\Admin;

use App\Models\Payment;
use App\Models\User;
use App\Models\Course;
use App\Models\Event;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class AdminPaymentController extends AdminBaseController
{
    /**
     * Display a listing of payments
     */
    public function index(Request $request): View|JsonResponse
    {
        $query = Payment::with(['user', 'course', 'event', 'processedBy'])
            ->where('school_id', $this->school->id);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply search
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->whereHas('user', function($subq) use ($searchTerm) {
                    $subq->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('email', 'like', "%{$searchTerm}%")
                         ->orWhere('first_name', 'like', "%{$searchTerm}%")
                         ->orWhere('last_name', 'like', "%{$searchTerm}%");
                })
                ->orWhere('transaction_id', 'like', "%{$searchTerm}%")
                ->orWhere('receipt_number', 'like', "%{$searchTerm}%")
                ->orWhere('reference_number', 'like', "%{$searchTerm}%");
            });
        }

        // Apply sorting
        $sortField = $request->get('sort', 'payment_date');
        $sortDirection = $request->get('direction', 'desc');
        $query->orderBy($sortField, $sortDirection);

        $payments = $query->paginate($request->get('per_page', 20));

        // Calculate statistics
        $stats = $this->calculatePaymentStats($request);

        // Get filter options
        $filterOptions = $this->getFilterOptions();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => [
                    'payments' => $payments,
                    'stats' => $stats,
                    'filter_options' => $filterOptions
                ]
            ]);
        }

        return view('admin.payments.index', compact('payments', 'stats', 'filterOptions'));
    }

    /**
     * Show the form for creating a new payment
     */
    public function create(Request $request): View
    {
        $students = User::where('school_id', $this->school->id)
            ->where('role', 'user')
            ->where('active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $courses = Course::where('school_id', $this->school->id)
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $events = Event::where('school_id', $this->school->id)
            ->where('active', true)
            ->orderBy('start_date', 'desc')
            ->get();

        // Pre-select based on URL parameters
        $preselected = [
            'user_id' => $request->get('user_id'),
            'course_id' => $request->get('course_id'),
            'event_id' => $request->get('event_id'),
            'payment_type' => $request->get('type', 'course_enrollment'),
            'amount' => $request->get('amount'),
        ];

        return view('admin.payments.create', compact(
            'students', 'courses', 'events', 'preselected'
        ));
    }

    /**
     * Store a newly created payment
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'event_id' => 'nullable|exists:events,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:' . implode(',', array_keys(Payment::getAvailablePaymentMethods())),
            'payment_type' => 'required|in:' . implode(',', array_keys(Payment::getAvailableTypes())),
            'transaction_id' => 'nullable|string|max:255|unique:payments,transaction_id',
            'reference_number' => 'nullable|string|max:255',
            'payment_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:payment_date',
            'notes' => 'nullable|string|max:1000',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_gateway_fee' => 'nullable|numeric|min:0',
            'create_installments' => 'nullable|boolean',
            'installments_count' => 'nullable|integer|min:2|max:12',
            'installment_frequency' => 'nullable|in:' . implode(',', array_keys(Payment::getAvailableInstallmentFrequencies())),
        ]);

        // Ensure student belongs to this school
        $student = User::findOrFail($validated['user_id']);
        if ($student->school_id !== $this->school->id) {
            return $this->jsonResponse(false, 'Student does not belong to your school.', [], 403);
        }

        // Ensure course/event belongs to this school if specified
        if (isset($validated['course_id'])) {
            $course = Course::findOrFail($validated['course_id']);
            if ($course->school_id !== $this->school->id) {
                return $this->jsonResponse(false, 'Course does not belong to your school.', [], 403);
            }
        }

        if (isset($validated['event_id'])) {
            $event = Event::findOrFail($validated['event_id']);
            if ($event->school_id !== $this->school->id) {
                return $this->jsonResponse(false, 'Event does not belong to your school.', [], 403);
            }
        }

        try {
            DB::beginTransaction();

            // Calculate net amount
            $taxAmount = $validated['tax_amount'] ?? 0;
            $discountAmount = $validated['discount_amount'] ?? 0;
            $gatewayFee = $validated['payment_gateway_fee'] ?? 0;
            $netAmount = $validated['amount'] - $discountAmount + $taxAmount + $gatewayFee;

            $paymentData = array_merge($validated, [
                'school_id' => $this->school->id,
                'processed_by_user_id' => $this->user->id,
                'net_amount' => $netAmount,
                'status' => Payment::STATUS_PENDING,
                'currency' => 'EUR',
            ]);

            $payment = Payment::create($paymentData);

            // Generate receipt number
            $payment->generateReceiptNumber();

            // Create installments if requested
            if ($request->get('create_installments') && $request->get('installments_count') > 1) {
                $payment->createInstallments(
                    $request->get('installments_count'),
                    $request->get('installment_frequency', Payment::INSTALLMENT_MONTHLY)
                );
            }

            DB::commit();
            $this->clearSchoolCache();

            $message = 'Payment created successfully.' .
                ($request->get('create_installments') ? ' Installments have been created.' : '');

            if ($request->ajax()) {
                return $this->jsonResponse(true, $message, [
                    'payment' => $payment->load(['user', 'course', 'event', 'installments'])
                ]);
            }

            return redirect()->route('admin.payments.show', $payment)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment creation failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return $this->jsonResponse(false, 'Failed to create payment. Please try again.');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to create payment. Please try again.');
        }
    }

    /**
     * Display the specified payment
     */
    public function show(Payment $payment): View
    {
        $this->authorizePayment($payment);

        $payment->load([
            'user', 'course', 'event', 'processedBy',
            'installments' => function($query) {
                $query->orderBy('installment_number');
            },
            'parentPayment'
        ]);

        return view('admin.payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified payment
     */
    public function edit(Payment $payment): View
    {
        $this->authorizePayment($payment);

        $students = User::where('school_id', $this->school->id)
            ->where('role', 'user')
            ->where('active', true)
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        $courses = Course::where('school_id', $this->school->id)
            ->orderBy('name')
            ->get();

        $events = Event::where('school_id', $this->school->id)
            ->orderBy('start_date', 'desc')
            ->get();

        return view('admin.payments.edit', compact('payment', 'students', 'courses', 'events'));
    }

    /**
     * Update the specified payment
     */
    public function update(Request $request, Payment $payment): JsonResponse|RedirectResponse
    {
        $this->authorizePayment($payment);

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'nullable|exists:courses,id',
            'event_id' => 'nullable|exists:events,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|in:' . implode(',', array_keys(Payment::getAvailablePaymentMethods())),
            'payment_type' => 'required|in:' . implode(',', array_keys(Payment::getAvailableTypes())),
            'transaction_id' => 'nullable|string|max:255|unique:payments,transaction_id,' . $payment->id,
            'reference_number' => 'nullable|string|max:255',
            'status' => 'required|in:' . implode(',', array_keys(Payment::getAvailableStatuses())),
            'payment_date' => 'nullable|date',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string|max:1000',
            'tax_amount' => 'nullable|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'payment_gateway_fee' => 'nullable|numeric|min:0',
            'refund_reason' => 'nullable|string|max:500',
        ]);

        // Security checks
        $student = User::findOrFail($validated['user_id']);
        if ($student->school_id !== $this->school->id) {
            return $this->jsonResponse(false, 'Student does not belong to your school.', [], 403);
        }

        try {
            // Calculate net amount
            $taxAmount = $validated['tax_amount'] ?? 0;
            $discountAmount = $validated['discount_amount'] ?? 0;
            $gatewayFee = $validated['payment_gateway_fee'] ?? 0;
            $netAmount = $validated['amount'] - $discountAmount + $taxAmount + $gatewayFee;

            $updateData = array_merge($validated, [
                'processed_by_user_id' => $this->user->id,
                'net_amount' => $netAmount,
            ]);

            $payment->update($updateData);
            $this->clearSchoolCache();

            if ($request->ajax()) {
                return $this->jsonResponse(true, 'Payment updated successfully.', [
                    'payment' => $payment->fresh()->load(['user', 'course', 'event'])
                ]);
            }

            return redirect()->route('admin.payments.show', $payment)
                ->with('success', 'Payment updated successfully.');

        } catch (\Exception $e) {
            Log::error('Payment update failed: ' . $e->getMessage());

            if ($request->ajax()) {
                return $this->jsonResponse(false, 'Failed to update payment. Please try again.');
            }

            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to update payment. Please try again.');
        }
    }

    /**
     * Remove the specified payment
     */
    public function destroy(Payment $payment): JsonResponse|RedirectResponse
    {
        $this->authorizePayment($payment);

        try {
            DB::beginTransaction();

            // Delete installments if this is a parent payment
            if ($payment->installments()->count()) {
                $payment->installments()->delete();
            }

            $payment->delete();

            DB::commit();
            $this->clearSchoolCache();

            if (request()->ajax()) {
                return $this->jsonResponse(true, 'Payment deleted successfully.');
            }

            return redirect()->route('admin.payments.index')
                ->with('success', 'Payment deleted successfully.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Payment deletion failed: ' . $e->getMessage());

            if (request()->ajax()) {
                return $this->jsonResponse(false, 'Failed to delete payment. Please try again.');
            }

            return redirect()->back()
                ->with('error', 'Failed to delete payment. Please try again.');
        }
    }

    /**
     * Mark payment as completed
     */
    public function markCompleted(Payment $payment): JsonResponse
    {
        $this->authorizePayment($payment);

        try {
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'payment_date' => now(),
                'processed_by_user_id' => $this->user->id,
            ]);

            $this->clearSchoolCache();

            return $this->jsonResponse(true, 'Payment marked as completed.', [
                'payment' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Payment completion failed: ' . $e->getMessage());
            return $this->jsonResponse(false, 'Failed to mark payment as completed.');
        }
    }

    /**
     * Process refund
     */
    public function refund(Request $request, Payment $payment): JsonResponse
    {
        Log::info('Refund request received', ['payment_id' => $payment->id, 'status' => $payment->status]);

        $this->authorizePayment($payment);

        $request->validate([
            'refund_reason' => 'required|string|max:500'
        ]);

        Log::info('Checking if payment can be refunded', ['payment_id' => $payment->id, 'canBeRefunded' => $payment->canBeRefunded()]);

        if (!$payment->canBeRefunded()) {
            Log::warning('Payment cannot be refunded', ['payment_id' => $payment->id, 'status' => $payment->status]);
            return $this->jsonResponse(false, 'Payment cannot be refunded.', [], 422);
        }

        try {
            Log::info('Processing refund', ['payment_id' => $payment->id, 'reason' => $request->get('refund_reason')]);

            $payment->update([
                'status' => Payment::STATUS_REFUNDED,
                'refund_reason' => $request->get('refund_reason'),
                'processed_by_user_id' => $this->user->id,
            ]);

            Log::info('Refund processed successfully', ['payment_id' => $payment->id, 'new_status' => $payment->fresh()->status]);

            $this->clearSchoolCache();

            return $this->jsonResponse(true, 'Refund processed successfully.', [
                'payment' => $payment->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Payment refund failed: ' . $e->getMessage());
            return $this->jsonResponse(false, 'Failed to process refund.');
        }
    }

    /**
     * Generate PDF receipt
     */
    public function generateReceipt(Payment $payment)
    {
        $this->authorizePayment($payment);

        $payment->load(['user', 'course', 'event', 'school']);

        // Generate receipt number if not exists
        if (!$payment->receipt_number) {
            $payment->generateReceiptNumber();
        }

        // Get school-specific settings for receipt
        $settings = [
            'school_name' => Setting::get("school.{$this->school->id}.name", $this->school->name),
            'school_address' => Setting::get("school.{$this->school->id}.address", $this->school->address ?? ''),
            'school_city' => Setting::get("school.{$this->school->id}.city", $this->school->city ?? ''),
            'school_postal_code' => Setting::get("school.{$this->school->id}.postal_code", $this->school->postal_code ?? ''),
            'school_phone' => Setting::get("school.{$this->school->id}.phone", $this->school->phone ?? ''),
            'school_email' => Setting::get("school.{$this->school->id}.email", $this->school->email ?? ''),
            'school_website' => Setting::get("school.{$this->school->id}.website", $this->school->website ?? ''),
            'vat_number' => Setting::get("school.{$this->school->id}.vat_number", ''),
            'tax_code' => Setting::get("school.{$this->school->id}.tax_code", ''),
            'receipt_header_text' => Setting::get("school.{$this->school->id}.receipt.header_text", ''),
            'receipt_footer_text' => Setting::get("school.{$this->school->id}.receipt.footer_text", ''),
            'receipt_logo_path' => Setting::get("school.{$this->school->id}.receipt.logo_path", $this->school->logo_path ?? ''),
            'payment_terms' => Setting::get("school.{$this->school->id}.payment.terms", ''),
            'bank_name' => Setting::get("school.{$this->school->id}.bank.name", ''),
            'bank_iban' => Setting::get("school.{$this->school->id}.bank.iban", ''),
            'bank_swift' => Setting::get("school.{$this->school->id}.bank.swift", ''),
        ];


        $data = [
            'payment' => $payment,
            'school' => $this->school,
            'settings' => $settings,
            'generated_at' => now(),
            'generated_by' => $this->user
        ];

        $pdf = PDF::loadView('admin.payments.receipt', $data);

        // Update receipt sent timestamp
        $payment->update(['receipt_sent_at' => now()]);

        $filename = "receipt_{$payment->receipt_number}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Send receipt via email
     */
    public function sendReceipt(Payment $payment): JsonResponse
    {
        $this->authorizePayment($payment);

        try {
            $payment->load(['user', 'school']);

            // Get school-specific settings for receipt
            $settings = [
                'school_name' => Setting::get("school.{$this->school->id}.name", $this->school->name),
                'school_address' => Setting::get("school.{$this->school->id}.address", $this->school->address ?? ''),
                'school_city' => Setting::get("school.{$this->school->id}.city", $this->school->city ?? ''),
                'school_postal_code' => Setting::get("school.{$this->school->id}.postal_code", $this->school->postal_code ?? ''),
                'school_phone' => Setting::get("school.{$this->school->id}.phone", $this->school->phone ?? ''),
                'school_email' => Setting::get("school.{$this->school->id}.email", $this->school->email ?? ''),
                'school_website' => Setting::get("school.{$this->school->id}.website", $this->school->website ?? ''),
                'vat_number' => Setting::get("school.{$this->school->id}.vat_number", ''),
                'tax_code' => Setting::get("school.{$this->school->id}.tax_code", ''),
                'receipt_header_text' => Setting::get("school.{$this->school->id}.receipt.header_text", ''),
                'receipt_footer_text' => Setting::get("school.{$this->school->id}.receipt.footer_text", ''),
                'receipt_logo_path' => Setting::get("school.{$this->school->id}.receipt.logo_path", $this->school->logo_path ?? ''),
                'payment_terms' => Setting::get("school.{$this->school->id}.payment.terms", ''),
                'bank_name' => Setting::get("school.{$this->school->id}.bank.name", ''),
                'bank_iban' => Setting::get("school.{$this->school->id}.bank.iban", ''),
                'bank_swift' => Setting::get("school.{$this->school->id}.bank.swift", ''),
            ];

            // Generate PDF
            $pdf = PDF::loadView('admin.payments.receipt', [
                'payment' => $payment,
                'school' => $this->school,
                'settings' => $settings,
                'generated_at' => now(),
                'generated_by' => $this->user
            ]);

            // Send email (implement your mail logic here)
            // Mail::to($payment->user->email)->send(new PaymentReceiptMail($payment, $pdf->output()));

            $payment->update(['receipt_sent_at' => now()]);

            return $this->jsonResponse(true, 'Receipt sent successfully.');

        } catch (\Exception $e) {
            Log::error('Receipt sending failed: ' . $e->getMessage());
            return $this->jsonResponse(false, 'Failed to send receipt.');
        }
    }

    /**
     * Handle bulk actions
     */
    public function bulkAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:mark_completed,mark_pending,delete,send_receipts',
            'payment_ids' => 'required|array',
            'payment_ids.*' => 'integer|exists:payments,id'
        ]);

        $payments = Payment::whereIn('id', $validated['payment_ids'])
            ->where('school_id', $this->school->id)
            ->get();

        if ($payments->isEmpty()) {
            return $this->jsonResponse(false, 'No valid payments found.');
        }

        try {
            DB::beginTransaction();

            $count = 0;
            foreach ($payments as $payment) {
                switch ($validated['action']) {
                    case 'mark_completed':
                        if (in_array($payment->status, [Payment::STATUS_PENDING, Payment::STATUS_PROCESSING])) {
                            $payment->update([
                                'status' => Payment::STATUS_COMPLETED,
                                'payment_date' => $payment->payment_date ?? now(),
                                'processed_by_user_id' => $this->user->id,
                            ]);
                            $count++;
                        }
                        break;

                    case 'mark_pending':
                        if ($payment->status !== Payment::STATUS_COMPLETED) {
                            $payment->update(['status' => Payment::STATUS_PENDING]);
                            $count++;
                        }
                        break;

                    case 'delete':
                        $payment->delete();
                        $count++;
                        break;

                    case 'send_receipts':
                        // Implement receipt sending logic
                        $payment->update(['receipt_sent_at' => now()]);
                        $count++;
                        break;
                }
            }

            DB::commit();
            $this->clearSchoolCache();

            $actionName = match($validated['action']) {
                'mark_completed' => 'marked as completed',
                'mark_pending' => 'marked as pending',
                'delete' => 'deleted',
                'send_receipts' => 'receipts sent',
                default => 'processed'
            };

            return $this->jsonResponse(true, "{$count} payments {$actionName} successfully.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Bulk action failed: ' . $e->getMessage());
            return $this->jsonResponse(false, 'Bulk action failed. Please try again.');
        }
    }

    /**
     * Export payments to CSV
     */
    public function export(Request $request)
    {
        $query = Payment::with(['user', 'course', 'event'])
            ->where('school_id', $this->school->id);

        $this->applyFilters($query, $request);

        $payments = $query->orderBy('payment_date', 'desc')->get();

        $headers = [
            'ID', 'Student', 'Email', 'Type', 'Course/Event', 'Amount', 'Method',
            'Status', 'Payment Date', 'Due Date', 'Receipt Number', 'Transaction ID', 'Notes'
        ];

        $data = $payments->map(function ($payment) {
            return [
                $payment->id,
                $payment->user->full_name ?? $payment->user->name,
                $payment->user->email,
                $payment->payment_type_name,
                $payment->course?->name ?? $payment->event?->name ?? 'N/A',
                $payment->formatted_full_amount,
                $payment->payment_method_name,
                $payment->status_name,
                $payment->payment_date?->format('d/m/Y') ?? 'N/A',
                $payment->due_date?->format('d/m/Y') ?? 'N/A',
                $payment->receipt_number ?? 'N/A',
                $payment->transaction_id ?? 'N/A',
                $payment->notes ?? ''
            ];
        })->toArray();

        $filename = 'payments_' . $this->school->name . '_' . now()->format('Y-m-d') . '.csv';

        return $this->exportToCsv($data, $headers, $filename);
    }

    /**
     * Get payment statistics
     */
    public function getStats(Request $request): JsonResponse
    {
        $stats = $this->calculatePaymentStats($request);
        return $this->jsonResponse(true, '', $stats);
    }

    /**
     * Get payment statistics (for AJAX calls)
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->calculatePaymentStats($request);
        return $this->jsonResponse(true, 'Statistics retrieved successfully', $stats);
    }

    /**
     * Get overdue payments
     */
    public function getOverdue(Request $request): JsonResponse
    {
        $overduePayments = Payment::with(['user', 'course', 'event'])
            ->where('school_id', $this->school->id)
            ->overdue()
            ->orderBy('due_date')
            ->paginate($request->get('per_page', 15));

        return $this->jsonResponse(true, '', [
            'payments' => $overduePayments
        ]);
    }

    /**
     * Apply search to query
     */
    protected function applySearch($query, string $searchTerm)
    {
        return $query->where(function($q) use ($searchTerm) {
            $q->whereHas('user', function($subq) use ($searchTerm) {
                $subq->where('name', 'like', "%{$searchTerm}%")
                     ->orWhere('email', 'like', "%{$searchTerm}%");
            })
            ->orWhere('transaction_id', 'like', "%{$searchTerm}%")
            ->orWhere('receipt_number', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->get('payment_method'));
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->get('payment_type'));
        }

        if ($request->filled('course_id')) {
            $query->where('course_id', $request->get('course_id'));
        }

        if ($request->filled('event_id')) {
            $query->where('event_id', $request->get('event_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('payment_date', '>=', $request->get('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('payment_date', '<=', $request->get('date_to'));
        }

        if ($request->filled('is_installment')) {
            $query->where('is_installment', $request->get('is_installment') === 'true');
        }

        if ($request->filled('overdue')) {
            $query->overdue();
        }
    }

    /**
     * Calculate payment statistics
     */
    private function calculatePaymentStats(Request $request): array
    {
        $baseQuery = Payment::where('school_id', $this->school->id);
        $this->applyFilters($baseQuery, $request);

        return [
            'total_payments' => (clone $baseQuery)->count(),
            'completed_payments' => (clone $baseQuery)->completed()->count(),
            'pending_payments' => (clone $baseQuery)->pending()->count(),
            'overdue_payments' => (clone $baseQuery)->overdue()->count(),
            'total_amount' => (clone $baseQuery)->sum('amount'),
            'completed_amount' => (clone $baseQuery)->completed()->sum('amount'),
            'pending_amount' => (clone $baseQuery)->pending()->sum('amount'),
            'this_month_revenue' => (clone $baseQuery)->completed()
                ->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->sum('amount'),
            'installment_payments' => (clone $baseQuery)->installments()->count(),
            'main_payments' => (clone $baseQuery)->mainPayments()->count(),
        ];
    }

    /**
     * Get filter options
     */
    private function getFilterOptions(): array
    {
        return [
            'statuses' => Payment::getAvailableStatuses(),
            'methods' => Payment::getAvailablePaymentMethods(),
            'types' => Payment::getAvailableTypes(),
            'courses' => Course::where('school_id', $this->school->id)->pluck('name', 'id'),
            'events' => Event::where('school_id', $this->school->id)->pluck('name', 'id'),
        ];
    }

    /**
     * Authorize payment access
     */
    private function authorizePayment(Payment $payment): void
    {
        if ($payment->school_id !== $this->school->id) {
            abort(403, 'Unauthorized access to payment.');
        }
    }
}