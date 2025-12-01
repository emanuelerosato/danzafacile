<?php

namespace App\Http\Controllers;

use App\Models\EventPayment;
use App\Models\EventRegistration;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PaymentService $paymentService
    ) {}

    /**
     * Handle PayPal webhook notifications
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request)
    {
        // TODO Phase 6: Implement PayPal webhook verification
        // For now, just log the webhook payload

        Log::info('PayPal webhook received', [
            'payload' => $request->all(),
        ]);

        // Verify webhook signature (Phase 6)
        // Extract transaction details
        // Find payment by transaction_id
        // Call PaymentService::completePayment()

        return response()->json(['status' => 'received'], 200);
    }

    /**
     * Handle PayPal payment success callback
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function success(Request $request)
    {
        $paymentId = $request->get('paymentId');
        $payerId = $request->get('PayerID');
        $token = $request->get('token');

        if (!$paymentId || !$payerId) {
            return redirect()->route('home')->withErrors([
                'payment' => 'Dati pagamento mancanti.'
            ]);
        }

        try {
            // TODO Phase 6: Execute PayPal payment
            // For now, simulate payment completion

            // Find payment by transaction_id or token
            $payment = EventPayment::where('transaction_id', $paymentId)->first();

            if (!$payment) {
                Log::warning('Payment not found in success callback', [
                    'payment_id' => $paymentId,
                    'payer_id' => $payerId,
                ]);

                return redirect()->route('home')->withErrors([
                    'payment' => 'Pagamento non trovato.'
                ]);
            }

            if ($payment->isCompleted()) {
                // Already processed
                return redirect()->route('public.events.registration.success', [
                    'slug' => $payment->event->slug,
                    'registration' => $payment->event_registration_id,
                ]);
            }

            // Complete payment
            $this->paymentService->completePayment($payment, $paymentId, [
                'payer_id' => $payerId,
                'token' => $token,
                'completed_via' => 'success_callback',
            ]);

            Log::info('Payment completed via success callback', [
                'payment_id' => $payment->id,
                'transaction_id' => $paymentId,
            ]);

            return redirect()->route('public.events.registration.success', [
                'slug' => $payment->event->slug,
                'registration' => $payment->event_registration_id,
            ])->with('success', 'Pagamento completato con successo!');

        } catch (\Exception $e) {
            Log::error('Payment success callback failed', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('home')->withErrors([
                'payment' => 'Errore durante il completamento del pagamento. Contatta il supporto.'
            ]);
        }
    }

    /**
     * Handle PayPal payment cancel callback
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function cancel(Request $request)
    {
        $token = $request->get('token');

        Log::info('Payment cancelled by user', [
            'token' => $token,
            'ip' => $request->ip(),
        ]);

        return redirect()->route('home')->with('info', 'Pagamento annullato. Puoi ritentare in qualsiasi momento dalla tua email di conferma.');
    }
}
