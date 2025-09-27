<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\School;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class PayPalController extends Controller
{
    /**
     * Crea un pagamento PayPal
     */
    public function createPayment(Request $request)
    {
        try {
            $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'course_id' => 'required|exists:courses,id',
                'description' => 'nullable|string|max:255',
            ]);

            $user = Auth::user();
            $school = $user->school ?? School::find($request->school_id);

            if (!$school) {
                return response()->json([
                    'success' => false,
                    'message' => 'Scuola non trovata'
                ], 404);
            }

            // Inizializza service PayPal per la scuola
            $paypalService = PayPalService::forSchool($school);

            if (!$paypalService->isEnabled()) {
                return response()->json([
                    'success' => false,
                    'message' => 'PayPal non è abilitato per questa scuola'
                ], 400);
            }

            // Crea record di pagamento nel database
            $payment = Payment::create([
                'user_id' => $user->id,
                'school_id' => $school->id,
                'course_id' => $request->course_id,
                'amount' => $request->amount,
                'currency' => $paypalService->getSettings()['currency'],
                'status' => 'pending',
                'payment_method' => Payment::METHOD_PAYPAL,
                'description' => $request->description ?? 'Pagamento corso',
            ]);

            // Calcola commissioni PayPal
            $fees = $paypalService->calculateFees($request->amount);

            // Prepara dati per PayPal
            $paymentData = [
                'amount' => $request->amount,
                'description' => $request->description ?? 'Pagamento corso',
                'payment_id' => $payment->id,
                'user_id' => $user->id,
                'course_id' => $request->course_id,
                'item_name' => $request->description ?? 'Corso di danza',
                'payer_email' => $user->email,
                'payer_first_name' => $user->name,
                'payer_last_name' => '',
            ];

            // Crea pagamento PayPal
            $paypalResponse = $paypalService->createPayment($paymentData);

            // Salva PayPal payment ID
            $payment->update([
                'external_id' => $paypalResponse['id'],
                'fees_amount' => $fees['total_fees'],
                'net_amount' => $fees['net_amount'],
            ]);

            // Ottieni URL di approvazione
            $approvalUrl = $paypalService->getApprovalUrl($paypalResponse);

            if (!$approvalUrl) {
                throw new Exception('URL di approvazione PayPal non trovato');
            }

            return response()->json([
                'success' => true,
                'message' => 'Pagamento PayPal creato con successo',
                'data' => [
                    'payment_id' => $payment->id,
                    'paypal_payment_id' => $paypalResponse['id'],
                    'approval_url' => $approvalUrl,
                    'fees' => $fees,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error creating PayPal payment:', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id(),
                'request_data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nella creazione del pagamento: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Gestisce il successo del pagamento PayPal
     */
    public function paymentSuccess(Request $request)
    {
        try {
            $paymentId = $request->get('paymentId');
            $payerId = $request->get('PayerID');

            if (!$paymentId || !$payerId) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'Parametri di pagamento mancanti');
            }

            // Trova il pagamento nel database
            $payment = Payment::where('external_id', $paymentId)->first();

            if (!$payment) {
                return redirect()->route('student.dashboard')
                    ->with('error', 'Pagamento non trovato');
            }

            // Inizializza service PayPal
            $paypalService = PayPalService::forSchool($payment->school);

            // Esegui il pagamento
            $executionResult = $paypalService->executePayment($paymentId, $payerId);

            if ($executionResult['state'] === 'approved') {
                // Aggiorna stato pagamento
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'paypal_payer_id' => $payerId,
                    'transaction_data' => json_encode($executionResult),
                ]);

                Log::info('PayPal payment completed successfully', [
                    'payment_id' => $payment->id,
                    'paypal_payment_id' => $paymentId,
                    'user_id' => $payment->user_id,
                ]);

                return redirect()->route('student.dashboard')
                    ->with('success', 'Pagamento completato con successo!');
            } else {
                $payment->update([
                    'status' => 'failed',
                    'transaction_data' => json_encode($executionResult),
                ]);

                return redirect()->route('student.dashboard')
                    ->with('error', 'Pagamento non riuscito');
            }

        } catch (Exception $e) {
            Log::error('Error processing PayPal payment success:', [
                'error' => $e->getMessage(),
                'paymentId' => $request->get('paymentId'),
                'PayerID' => $request->get('PayerID'),
            ]);

            return redirect()->route('student.dashboard')
                ->with('error', 'Errore nel completamento del pagamento');
        }
    }

    /**
     * Gestisce la cancellazione del pagamento PayPal
     */
    public function paymentCancel(Request $request)
    {
        $paymentId = $request->get('paymentId');

        if ($paymentId) {
            $payment = Payment::where('external_id', $paymentId)->first();
            if ($payment) {
                $payment->update(['status' => 'cancelled']);

                Log::info('PayPal payment cancelled', [
                    'payment_id' => $payment->id,
                    'paypal_payment_id' => $paymentId,
                ]);
            }
        }

        return redirect()->route('student.dashboard')
            ->with('warning', 'Pagamento annullato');
    }

    /**
     * Gestisce i webhook PayPal
     */
    public function webhook(Request $request)
    {
        try {
            $headers = $request->header();
            $body = $request->getContent();
            $data = json_decode($body, true);

            Log::info('PayPal webhook received', [
                'event_type' => $data['event_type'] ?? 'unknown',
                'headers' => $headers,
            ]);

            if (!isset($data['event_type'])) {
                return response('Invalid webhook data', 400);
            }

            // Gestisce diversi tipi di eventi PayPal
            switch ($data['event_type']) {
                case 'PAYMENT.SALE.COMPLETED':
                    $this->handlePaymentCompleted($data);
                    break;

                case 'PAYMENT.SALE.DENIED':
                case 'PAYMENT.SALE.REFUNDED':
                    $this->handlePaymentFailed($data);
                    break;

                default:
                    Log::info('PayPal webhook event not handled', [
                        'event_type' => $data['event_type']
                    ]);
            }

            return response('OK', 200);

        } catch (Exception $e) {
            Log::error('Error processing PayPal webhook:', [
                'error' => $e->getMessage(),
                'request_body' => $request->getContent(),
            ]);

            return response('Webhook processing error', 500);
        }
    }

    /**
     * Gestisce il completamento del pagamento via webhook
     */
    private function handlePaymentCompleted(array $data)
    {
        if (!isset($data['resource']['parent_payment'])) {
            return;
        }

        $paymentId = $data['resource']['parent_payment'];
        $payment = Payment::where('external_id', $paymentId)->first();

        if ($payment && $payment->status === 'pending') {
            $payment->update([
                'status' => 'completed',
                'paid_at' => now(),
                'webhook_data' => json_encode($data),
            ]);

            Log::info('Payment completed via webhook', [
                'payment_id' => $payment->id,
                'paypal_payment_id' => $paymentId,
            ]);
        }
    }

    /**
     * Gestisce il fallimento del pagamento via webhook
     */
    private function handlePaymentFailed(array $data)
    {
        if (!isset($data['resource']['parent_payment'])) {
            return;
        }

        $paymentId = $data['resource']['parent_payment'];
        $payment = Payment::where('external_id', $paymentId)->first();

        if ($payment && $payment->status !== 'failed') {
            $payment->update([
                'status' => 'failed',
                'webhook_data' => json_encode($data),
            ]);

            Log::info('Payment failed via webhook', [
                'payment_id' => $payment->id,
                'paypal_payment_id' => $paymentId,
                'event_type' => $data['event_type'],
            ]);
        }
    }

    /**
     * Ottiene lo stato di un pagamento PayPal
     */
    public function getPaymentStatus(Request $request, $paymentId)
    {
        try {
            $payment = Payment::where('external_id', $paymentId)->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pagamento non trovato'
                ], 404);
            }

            // Verifica che l'utente possa accedere a questo pagamento
            if (Auth::id() !== $payment->user_id && !Auth::user()->isAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accesso negato'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $payment->id,
                    'status' => $payment->status,
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'paid_at' => $payment->paid_at,
                    'created_at' => $payment->created_at,
                ]
            ]);

        } catch (Exception $e) {
            Log::error('Error getting payment status:', [
                'error' => $e->getMessage(),
                'payment_id' => $paymentId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Errore nel recupero dello stato del pagamento'
            ], 500);
        }
    }
}