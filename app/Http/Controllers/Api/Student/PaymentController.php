<?php

namespace App\Http\Controllers\Api\Student;

use App\Http\Controllers\API\BaseApiController;
use App\Models\Payment;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PaymentController extends BaseApiController
{
    /**
     * Get user's payment history with pagination and filtering
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Payment::where('user_id', $user->id)
            ->with(['course:id,name', 'event:id,name'])
            ->orderBy('created_at', 'desc');

        // Filtering by status
        if ($request->has('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filtering by payment type
        if ($request->has('payment_type')) {
            $query->where('payment_type', $request->get('payment_type'));
        }

        // Date range filtering
        if ($request->has('from_date')) {
            $query->where('payment_date', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date')) {
            $query->where('payment_date', '<=', $request->get('to_date'));
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $payments = $query->paginate($perPage);

        // Transform payments for mobile
        $paymentsData = $payments->getCollection()->map(function($payment) {
            return [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'formatted_amount' => '€ ' . number_format($payment->amount, 2),
                'currency' => $payment->currency,
                'status' => $payment->status,
                'status_name' => $payment->status_name,
                'payment_type' => $payment->payment_type,
                'payment_type_name' => $payment->payment_type_name,
                'payment_method' => $payment->payment_method,
                'payment_method_name' => $payment->payment_method_name,
                'payment_date' => $payment->payment_date?->toISOString(),
                'due_date' => $payment->due_date?->toISOString(),
                'transaction_id' => $payment->transaction_id,
                'receipt_number' => $payment->receipt_number,
                'notes' => $payment->notes,
                'course' => $payment->course ? [
                    'id' => $payment->course->id,
                    'name' => $payment->course->name
                ] : null,
                'event' => $payment->event ? [
                    'id' => $payment->event->id,
                    'name' => $payment->event->name
                ] : null,
                'can_pay_now' => in_array($payment->status, ['pending', 'failed']),
                'is_overdue' => $payment->due_date && $payment->due_date->isPast() && in_array($payment->status, ['pending', 'processing']),
                'created_at' => $payment->created_at->toISOString(),
                'updated_at' => $payment->updated_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'payments' => $paymentsData,
                'pagination' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                    'from' => $payments->firstItem(),
                    'to' => $payments->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Get payment statistics for the user
     */
    public function statistics(Request $request): JsonResponse
    {
        $user = $request->user();

        // Basic statistics
        $stats = [
            'total_spent' => $user->payments()->where('status', 'completed')->sum('amount'),
            'pending_amount' => $user->payments()->whereIn('status', ['pending', 'processing'])->sum('amount'),
            'overdue_count' => $user->payments()->where('due_date', '<', now())->whereIn('status', ['pending', 'processing'])->count(),
            'this_month_spent' => $user->payments()->where('status', 'completed')->whereMonth('payment_date', now()->month)->sum('amount'),
            'total_payments' => $user->payments()->count(),
        ];

        // Status breakdown
        $statusStats = $user->payments()
            ->selectRaw('status, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $paymentStatusStats = [
            'completed' => $statusStats['completed']->count ?? 0,
            'pending' => $statusStats['pending']->count ?? 0,
            'failed' => $statusStats['failed']->count ?? 0,
            'processing' => $statusStats['processing']->count ?? 0,
            'cancelled' => $statusStats['cancelled']->count ?? 0,
            'refunded' => $statusStats['refunded']->count ?? 0,
        ];

        // Payment method breakdown
        $methodStats = $user->payments()
            ->where('status', 'completed')
            ->selectRaw('payment_method, COUNT(*) as count, SUM(amount) as total_amount')
            ->groupBy('payment_method')
            ->get();

        // Recent activity (last 30 days)
        $recentActivity = $user->payments()
            ->where('created_at', '>=', now()->subDays(30))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->limit(7)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'overview' => $stats,
                'status_breakdown' => $paymentStatusStats,
                'payment_methods' => $methodStats,
                'recent_activity' => $recentActivity
            ]
        ]);
    }

    /**
     * Get specific payment details
     */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        // Verify the payment belongs to the authenticated user
        if ($payment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found'
            ], 404);
        }

        $payment->load(['course', 'event']);

        $paymentData = [
            'id' => $payment->id,
            'amount' => $payment->amount,
            'formatted_amount' => '€ ' . number_format($payment->amount, 2),
            'currency' => $payment->currency,
            'status' => $payment->status,
            'status_name' => $payment->status_name,
            'payment_type' => $payment->payment_type,
            'payment_type_name' => $payment->payment_type_name,
            'payment_method' => $payment->payment_method,
            'payment_method_name' => $payment->payment_method_name,
            'payment_date' => $payment->payment_date?->toISOString(),
            'due_date' => $payment->due_date?->toISOString(),
            'transaction_id' => $payment->transaction_id,
            'receipt_number' => $payment->receipt_number,
            'notes' => $payment->notes,
            'gateway_response' => $payment->gateway_response,
            'course' => $payment->course ? [
                'id' => $payment->course->id,
                'name' => $payment->course->name,
                'instructor' => $payment->course->instructor,
                'schedule' => $payment->course->schedule,
                'location' => $payment->course->location,
            ] : null,
            'event' => $payment->event ? [
                'id' => $payment->event->id,
                'name' => $payment->event->name,
                'event_date' => $payment->event->event_date,
                'location' => $payment->event->location,
            ] : null,
            'can_pay_now' => in_array($payment->status, ['pending', 'failed']),
            'can_refund' => $payment->status === 'completed' && $payment->payment_date >= now()->subDays(30),
            'is_overdue' => $payment->due_date && $payment->due_date->isPast() && in_array($payment->status, ['pending', 'processing']),
            'created_at' => $payment->created_at->toISOString(),
            'updated_at' => $payment->updated_at->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'payment' => $paymentData
            ]
        ]);
    }

    /**
     * Create PayPal payment for mobile
     */
    public function createPayPalPayment(Request $request, Payment $payment): JsonResponse
    {
        // Verify the payment belongs to the authenticated user
        if ($payment->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorizzato ad accedere a questo pagamento.'
            ], 403);
        }

        // Verify payment is in pending status
        if ($payment->status !== Payment::STATUS_PENDING) {
            return response()->json([
                'success' => false,
                'message' => 'Questo pagamento non può essere elaborato.'
            ], 400);
        }

        try {
            // Create PayPal payment data (same structure as web controller)
            $paypalData = [
                'intent' => 'sale',
                'payer' => [
                    'payment_method' => 'paypal'
                ],
                'redirect_urls' => [
                    'return_url' => config('app.url') . '/api/mobile/v1/student/payments/' . $payment->id . '/paypal/success',
                    'cancel_url' => config('app.url') . '/api/mobile/v1/student/payments/' . $payment->id . '/paypal/cancel'
                ],
                'transactions' => [[
                    'amount' => [
                        'total' => number_format($payment->amount, 2, '.', ''),
                        'currency' => $payment->currency
                    ],
                    'description' => $payment->course
                        ? "Pagamento corso: {$payment->course->name}"
                        : "Pagamento: {$payment->payment_type_name}",
                    'invoice_number' => $payment->receipt_number ?? "PAY-{$payment->id}",
                    'item_list' => [
                        'items' => [[
                            'name' => $payment->course ? $payment->course->name : $payment->payment_type_name,
                            'sku' => "COURSE-{$payment->course_id}",
                            'price' => number_format($payment->amount, 2, '.', ''),
                            'currency' => $payment->currency,
                            'quantity' => 1
                        ]]
                    ]
                ]]
            ];

            // For demo purposes, create a mock PayPal approval URL
            // In production, you would use PayPal SDK here
            $approvalUrl = $this->createMockPayPalUrl($payment);

            // Update payment status to processing
            $payment->update([
                'status' => Payment::STATUS_PROCESSING,
                'payment_method' => Payment::METHOD_PAYPAL,
                'notes' => 'PayPal payment initiated from mobile app'
            ]);

            Log::info('Mobile PayPal payment initiated', [
                'payment_id' => $payment->id,
                'user_id' => $request->user()->id,
                'amount' => $payment->amount
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'approval_url' => $approvalUrl,
                    'payment_id' => $payment->id,
                    'paypal_data' => $paypalData
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile PayPal payment creation failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante la creazione del pagamento PayPal.'
            ], 500);
        }
    }

    /**
     * Handle successful PayPal payment from mobile
     */
    public function paypalSuccess(Request $request, Payment $payment): JsonResponse
    {
        try {
            // In production, you would verify the payment with PayPal API
            $payerId = $request->get('PayerID');
            $paymentId = $request->get('paymentId');

            if (!$payerId || !$paymentId) {
                throw new \Exception('Missing PayPal parameters');
            }

            // Mark payment as completed
            $payment->update([
                'status' => Payment::STATUS_COMPLETED,
                'payment_date' => now(),
                'transaction_id' => $paymentId,
                'gateway_response' => [
                    'payer_id' => $payerId,
                    'payment_id' => $paymentId,
                    'completed_at' => now()->toISOString(),
                    'source' => 'mobile_app'
                ]
            ]);

            // Generate receipt number if not exists
            if (!$payment->receipt_number) {
                $payment->generateReceiptNumber();
            }

            // Send confirmation email
            try {
                Mail::to($payment->user->email)->send(new PaymentConfirmationMail($payment));
                Log::info('Mobile payment confirmation email sent', [
                    'payment_id' => $payment->id,
                    'user_email' => $payment->user->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send mobile payment confirmation email', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('Mobile PayPal payment completed', [
                'payment_id' => $payment->id,
                'transaction_id' => $paymentId,
                'amount' => $payment->amount
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pagamento completato con successo!',
                'data' => [
                    'payment' => [
                        'id' => $payment->id,
                        'status' => $payment->status,
                        'receipt_number' => $payment->receipt_number,
                        'transaction_id' => $payment->transaction_id,
                        'amount' => $payment->amount
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Mobile PayPal payment completion failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            $payment->update(['status' => Payment::STATUS_FAILED]);

            return response()->json([
                'success' => false,
                'message' => 'Errore durante il completamento del pagamento.'
            ], 500);
        }
    }

    /**
     * Handle cancelled PayPal payment from mobile
     */
    public function paypalCancel(Request $request, Payment $payment): JsonResponse
    {
        // Reset payment status to pending
        $payment->update([
            'status' => Payment::STATUS_PENDING,
            'notes' => 'PayPal payment cancelled by user from mobile app'
        ]);

        Log::info('Mobile PayPal payment cancelled', [
            'payment_id' => $payment->id,
            'user_id' => $request->user()->id
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pagamento PayPal annullato.',
            'data' => [
                'payment' => [
                    'id' => $payment->id,
                    'status' => $payment->status
                ]
            ]
        ]);
    }

    /**
     * Get payment status for mobile app
     */
    public function getPaymentStatus(Payment $payment): JsonResponse
    {
        // Verify the payment belongs to the authenticated user
        if ($payment->user_id !== request()->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorizzato.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'payment' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                    'status_name' => $payment->status_name,
                    'amount' => $payment->amount,
                    'formatted_amount' => $payment->formatted_amount,
                    'payment_date' => $payment->payment_date?->toISOString(),
                    'receipt_number' => $payment->receipt_number,
                    'transaction_id' => $payment->transaction_id,
                    'can_pay_now' => in_array($payment->status, ['pending', 'failed']),
                ]
            ]
        ]);
    }

    /**
     * Get upcoming payments (due soon)
     */
    public function upcoming(Request $request): JsonResponse
    {
        $user = $request->user();
        $days = $request->get('days', 7); // Next 7 days by default

        $upcomingPayments = $user->payments()
            ->whereIn('status', ['pending', 'processing'])
            ->where('due_date', '<=', now()->addDays($days))
            ->where('due_date', '>=', now())
            ->with(['course:id,name', 'event:id,name'])
            ->orderBy('due_date', 'asc')
            ->get();

        $paymentsData = $upcomingPayments->map(function($payment) {
            return [
                'id' => $payment->id,
                'amount' => $payment->amount,
                'formatted_amount' => '€ ' . number_format($payment->amount, 2),
                'due_date' => $payment->due_date->toISOString(),
                'days_until_due' => now()->diffInDays($payment->due_date, false),
                'status' => $payment->status,
                'payment_type_name' => $payment->payment_type_name,
                'course' => $payment->course ? [
                    'id' => $payment->course->id,
                    'name' => $payment->course->name
                ] : null,
                'event' => $payment->event ? [
                    'id' => $payment->event->id,
                    'name' => $payment->event->name
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'upcoming_payments' => $paymentsData,
                'total_amount' => $upcomingPayments->sum('amount'),
                'count' => $upcomingPayments->count()
            ]
        ]);
    }

    /**
     * Create mock PayPal URL for demo purposes
     */
    private function createMockPayPalUrl(Payment $payment): string
    {
        // In production, replace with actual PayPal SDK integration
        $mockPayPalId = 'PAYID-' . strtoupper(uniqid());

        return "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token={$mockPayPalId}&payment_id={$payment->id}&mobile=1";
    }
}