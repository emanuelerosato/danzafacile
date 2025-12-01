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
        try {
            $payload = $request->all();

            Log::info('PayPal webhook received', [
                'event_type' => $payload['event_type'] ?? 'unknown',
                'payload' => $payload,
            ]);

            // Estrai school_id dal campo custom nel payload
            $customData = null;
            if (isset($payload['resource']['custom'])) {
                $customData = json_decode($payload['resource']['custom'], true);
            }

            if (!$customData || !isset($customData['school_id'])) {
                Log::warning('PayPal webhook missing school_id in custom data', [
                    'payload' => $payload
                ]);
                return response()->json(['status' => 'error', 'message' => 'Missing school data'], 400);
            }

            // Ottieni la scuola
            $school = \App\Models\School::find($customData['school_id']);
            if (!$school) {
                Log::error('PayPal webhook - school not found', [
                    'school_id' => $customData['school_id']
                ]);
                return response()->json(['status' => 'error', 'message' => 'School not found'], 400);
            }

            // Verifica webhook signature con PayPalService
            $paypalService = \App\Services\PayPalService::forSchool($school);

            $headers = [
                'paypal-auth-algo' => $request->header('paypal-auth-algo') ? [$request->header('paypal-auth-algo')] : [],
                'paypal-cert-url' => $request->header('paypal-cert-url') ? [$request->header('paypal-cert-url')] : [],
                'paypal-transmission-id' => $request->header('paypal-transmission-id') ? [$request->header('paypal-transmission-id')] : [],
                'paypal-transmission-sig' => $request->header('paypal-transmission-sig') ? [$request->header('paypal-transmission-sig')] : [],
                'paypal-transmission-time' => $request->header('paypal-transmission-time') ? [$request->header('paypal-transmission-time')] : [],
            ];

            $isVerified = $paypalService->verifyWebhook($headers, $request->getContent());

            if (!$isVerified) {
                Log::error('PayPal webhook signature verification failed', [
                    'school_id' => $school->id,
                    'event_type' => $payload['event_type'] ?? 'unknown'
                ]);
                return response()->json(['status' => 'error', 'message' => 'Signature verification failed'], 400);
            }

            // Processa solo eventi di pagamento completato
            $eventType = $payload['event_type'] ?? '';
            if ($eventType === 'PAYMENT.SALE.COMPLETED' || $eventType === 'PAYMENT.CAPTURE.COMPLETED') {

                // Estrai transaction ID
                $transactionId = $payload['resource']['id'] ?? null;
                if (!$transactionId) {
                    Log::warning('PayPal webhook missing transaction ID', [
                        'payload' => $payload
                    ]);
                    return response()->json(['status' => 'error', 'message' => 'Missing transaction ID'], 400);
                }

                // Trova il payment
                $payment = EventPayment::where('transaction_id', $transactionId)->first();
                if (!$payment) {
                    Log::warning('PayPal webhook - payment not found', [
                        'transaction_id' => $transactionId
                    ]);
                    return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
                }

                // Verifica che il payment non sia già completato
                if ($payment->isCompleted()) {
                    Log::info('PayPal webhook - payment already completed', [
                        'payment_id' => $payment->id,
                        'transaction_id' => $transactionId
                    ]);
                    return response()->json(['status' => 'ok', 'message' => 'Payment already completed'], 200);
                }

                // Completa il pagamento
                $this->paymentService->completePayment($payment, $transactionId, [
                    'webhook_event' => $payload,
                    'completed_via' => 'webhook',
                    'event_type' => $eventType
                ]);

                Log::info('PayPal webhook - payment completed successfully', [
                    'payment_id' => $payment->id,
                    'transaction_id' => $transactionId,
                    'event_type' => $eventType
                ]);
            }

            return response()->json(['status' => 'success'], 200);

        } catch (\Exception $e) {
            Log::error('PayPal webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json(['status' => 'error', 'message' => 'Webhook processing failed'], 500);
        }
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
            // Find payment by transaction_id
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

            // Esegui il pagamento PayPal tramite PayPalService
            $school = $payment->event->school;
            $paypalService = \App\Services\PayPalService::forSchool($school);

            // Execute payment con PayPal
            $result = $paypalService->executePayment($paymentId, $payerId);

            // Complete payment con i dati reali da PayPal
            $this->paymentService->completePayment($payment, $paymentId, [
                'payer_id' => $payerId,
                'token' => $token,
                'completed_via' => 'success_callback',
                'paypal_response' => $result,
            ]);

            Log::info('Payment completed via success callback', [
                'payment_id' => $payment->id,
                'transaction_id' => $paymentId,
                'paypal_status' => $result['state'] ?? 'unknown',
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

    /**
     * Create PayPal order via API (for PayPal Buttons SDK)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPayPalOrder(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_id' => 'required|integer|exists:event_payments,id',
                'event_id' => 'required|integer|exists:events,id',
                'amount' => 'required|numeric|min:0.01',
            ]);

            // Trova il payment esistente
            $payment = EventPayment::findOrFail($validated['payment_id']);
            $event = $payment->event;
            $school = $event->school;

            // Verifica che l'importo corrisponda
            if (abs($payment->amount - $validated['amount']) > 0.01) {
                Log::warning('Payment amount mismatch in createPayPalOrder', [
                    'payment_id' => $payment->id,
                    'expected_amount' => $payment->amount,
                    'received_amount' => $validated['amount'],
                ]);
                return response()->json([
                    'error' => 'Importo non valido'
                ], 400);
            }

            // Crea ordine PayPal tramite PayPalService
            $paypalService = \App\Services\PayPalService::forSchool($school);

            $paymentData = [
                'amount' => $payment->amount,
                'description' => "Iscrizione evento: {$event->name}",
                'item_name' => $event->name,
                'payer_email' => $payment->payer_email,
                'payer_first_name' => explode(' ', $payment->payer_name)[0] ?? '',
                'payer_last_name' => explode(' ', $payment->payer_name, 2)[1] ?? '',
                'user_id' => $payment->user_id,
                'course_id' => null,
                'payment_id' => $payment->id,
            ];

            $paypalResponse = $paypalService->createPayment($paymentData);

            // Salva transaction_id nel payment
            $payment->update([
                'transaction_id' => $paypalResponse['id'] ?? null,
            ]);

            Log::info('PayPal order created successfully', [
                'payment_id' => $payment->id,
                'paypal_order_id' => $paypalResponse['id'] ?? null,
            ]);

            return response()->json([
                'order_id' => $paypalResponse['id'] ?? null,
                'status' => 'success',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Dati non validi',
                'details' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating PayPal order', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Errore durante la creazione dell\'ordine PayPal',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
