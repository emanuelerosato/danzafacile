<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Mail\PaymentConfirmationMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    /**
     * Create PayPal payment for student
     */
    public function createPayPalPayment(Request $request, Payment $payment): JsonResponse
    {
        // Verify the payment belongs to the authenticated user
        if ($payment->user_id !== Auth::id()) {
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
            // Create PayPal payment data
            $paypalData = [
                'intent' => 'sale',
                'payer' => [
                    'payment_method' => 'paypal'
                ],
                'redirect_urls' => [
                    'return_url' => route('student.payment.paypal.success', $payment->id),
                    'cancel_url' => route('student.payment.paypal.cancel', $payment->id)
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

            // For demo purposes, we'll create a mock PayPal approval URL
            // In production, you would use PayPal SDK here
            $approvalUrl = $this->createMockPayPalUrl($payment);

            // Update payment status to processing
            $payment->update([
                'status' => Payment::STATUS_PROCESSING,
                'payment_method' => Payment::METHOD_PAYPAL,
                'notes' => 'PayPal payment initiated by student'
            ]);

            Log::info('PayPal payment initiated', [
                'payment_id' => $payment->id,
                'user_id' => Auth::id(),
                'amount' => $payment->amount
            ]);

            return response()->json([
                'success' => true,
                'approval_url' => $approvalUrl,
                'payment_id' => $payment->id
            ]);

        } catch (\Exception $e) {
            Log::error('PayPal payment creation failed', [
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
     * Handle successful PayPal payment
     */
    public function paypalSuccess(Request $request, Payment $payment): RedirectResponse
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
                    'completed_at' => now()->toISOString()
                ]
            ]);

            // Generate receipt number if not exists
            if (!$payment->receipt_number) {
                $payment->generateReceiptNumber();
            }

            // Send confirmation email
            try {
                Mail::to($payment->user->email)->send(new PaymentConfirmationMail($payment));
                Log::info('Payment confirmation email sent', [
                    'payment_id' => $payment->id,
                    'user_email' => $payment->user->email
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to send payment confirmation email', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage()
                ]);
            }

            Log::info('PayPal payment completed', [
                'payment_id' => $payment->id,
                'transaction_id' => $paymentId,
                'amount' => $payment->amount
            ]);

            return redirect()->route('student.my-courses')
                ->with('success', 'Pagamento completato con successo! Ricevuta: ' . $payment->receipt_number);

        } catch (\Exception $e) {
            Log::error('PayPal payment completion failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            $payment->update(['status' => Payment::STATUS_FAILED]);

            return redirect()->route('student.my-courses')
                ->with('error', 'Errore durante il completamento del pagamento.');
        }
    }

    /**
     * Handle cancelled PayPal payment
     */
    public function paypalCancel(Request $request, Payment $payment): RedirectResponse
    {
        // Reset payment status to pending
        $payment->update([
            'status' => Payment::STATUS_PENDING,
            'notes' => 'PayPal payment cancelled by user'
        ]);

        Log::info('PayPal payment cancelled', [
            'payment_id' => $payment->id,
            'user_id' => Auth::id()
        ]);

        return redirect()->route('student.my-courses')
            ->with('warning', 'Pagamento PayPal annullato.');
    }

    /**
     * Download payment receipt
     */
    public function downloadReceipt(Payment $payment)
    {
        // Verify the payment belongs to the authenticated user
        if ($payment->user_id !== Auth::id()) {
            abort(403, 'Non autorizzato ad accedere a questa ricevuta.');
        }

        // Verify payment is completed
        if ($payment->status !== Payment::STATUS_COMPLETED) {
            abort(400, 'Ricevuta non disponibile per questo pagamento.');
        }

        // For now, redirect to admin receipt (could be customized for students)
        return redirect()->route('admin.payments.receipt', $payment->id);
    }

    /**
     * Create mock PayPal URL for demo purposes
     */
    private function createMockPayPalUrl(Payment $payment): string
    {
        // In production, replace with actual PayPal SDK integration
        $baseUrl = config('app.url');
        $mockPayPalId = 'PAYID-' . strtoupper(uniqid());

        return "https://www.sandbox.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token={$mockPayPalId}&payment_id={$payment->id}";
    }

    /**
     * Get payment status for AJAX requests
     */
    public function getPaymentStatus(Payment $payment): JsonResponse
    {
        // Verify the payment belongs to the authenticated user
        if ($payment->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorizzato.'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'status_name' => $payment->status_name,
            'amount' => $payment->formatted_amount,
            'payment_date' => $payment->payment_date?->format('d/m/Y H:i'),
            'receipt_number' => $payment->receipt_number
        ]);
    }
}