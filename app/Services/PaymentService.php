<?php

namespace App\Services;

use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\EventPayment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Crea un record di pagamento per una registrazione evento
     *
     * @param EventRegistration $registration
     * @param string $paymentMethod Metodo di pagamento (paypal, stripe, onsite, free)
     * @return EventPayment
     */
    public function createPayment(EventRegistration $registration, string $paymentMethod = 'paypal'): EventPayment
    {
        $event = $registration->event;
        $user = $registration->user;

        // Calcola il prezzo per l'utente specifico
        $amount = $event->getPriceForUser($user);

        $payment = EventPayment::create([
            'event_id' => $event->id,
            'event_registration_id' => $registration->id,
            'user_id' => $user->id,
            'school_id' => $event->school_id,
            'amount' => $amount,
            'currency' => 'EUR',
            'status' => 'pending',
            'payment_method' => $paymentMethod,
            'payer_email' => $user->email,
            'payer_name' => $user->name,
        ]);

        Log::info('Payment record created', [
            'payment_id' => $payment->id,
            'event_id' => $event->id,
            'user_id' => $user->id,
            'amount' => $amount,
            'payment_method' => $paymentMethod,
        ]);

        return $payment;
    }

    /**
     * Segna un pagamento come completato
     *
     * @param EventPayment $payment
     * @param string $transactionId ID transazione dal gateway
     * @param array $gatewayResponse Risposta completa dal gateway di pagamento
     * @return void
     */
    public function completePayment(EventPayment $payment, string $transactionId, array $gatewayResponse = []): void
    {
        DB::transaction(function () use ($payment, $transactionId, $gatewayResponse) {
            // Segna il pagamento come completato
            $payment->markAsPaid($transactionId, $gatewayResponse);

            // Aggiorna lo stato della registrazione a confermato
            $payment->eventRegistration->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
            ]);

            Log::info('Payment completed successfully', [
                'payment_id' => $payment->id,
                'transaction_id' => $transactionId,
                'registration_id' => $payment->event_registration_id,
            ]);
        });
    }

    /**
     * Segna un pagamento come fallito
     *
     * @param EventPayment $payment
     * @param array $errorResponse Risposta di errore dal gateway
     * @return void
     */
    public function failPayment(EventPayment $payment, array $errorResponse = []): void
    {
        $payment->update([
            'status' => 'failed',
            'payment_gateway_response' => $errorResponse,
        ]);

        // Aggiorna lo stato della registrazione
        $payment->eventRegistration->update([
            'status' => 'payment_failed',
        ]);

        Log::warning('Payment failed', [
            'payment_id' => $payment->id,
            'registration_id' => $payment->event_registration_id,
            'error' => $errorResponse,
        ]);
    }

    /**
     * Rimborsa un pagamento
     * NOTA: L'integrazione completa con PayPal API sarà implementata in Phase 6
     *
     * @param EventPayment $payment
     * @return bool
     */
    public function refundPayment(EventPayment $payment): bool
    {
        // TODO: Integrare con PayPal API in Phase 6 per rimborso reale
        // Per ora segniamo solo il pagamento come rimborsato

        if (!$payment->isCompleted()) {
            Log::warning('Attempted to refund non-completed payment', [
                'payment_id' => $payment->id,
                'status' => $payment->status,
            ]);
            return false;
        }

        return DB::transaction(function () use ($payment) {
            // Segna il pagamento come rimborsato
            $payment->markAsRefunded();

            // Aggiorna lo stato della registrazione a cancellato
            $payment->eventRegistration->update([
                'status' => 'cancelled',
            ]);

            Log::info('Payment refunded', [
                'payment_id' => $payment->id,
                'registration_id' => $payment->event_registration_id,
                'amount' => $payment->amount,
            ]);

            return true;
        });
    }

    /**
     * Ottiene il totale pagamenti per un evento
     *
     * @param Event $event
     * @return array
     */
    public function getEventPaymentStats(Event $event): array
    {
        $payments = EventPayment::forEvent($event->id)->get();

        return [
            'total_amount' => $payments->where('status', 'completed')->sum('amount'),
            'total_payments' => $payments->count(),
            'completed_payments' => $payments->where('status', 'completed')->count(),
            'pending_payments' => $payments->where('status', 'pending')->count(),
            'failed_payments' => $payments->where('status', 'failed')->count(),
            'refunded_payments' => $payments->where('status', 'refunded')->count(),
            'refunded_amount' => $payments->where('status', 'refunded')->sum('amount'),
        ];
    }

    /**
     * Verifica se un utente ha già pagato per un evento
     *
     * @param User $user
     * @param Event $event
     * @return bool
     */
    public function hasUserPaidForEvent(User $user, Event $event): bool
    {
        return EventPayment::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Crea un pagamento gratuito (per eventi senza costo)
     *
     * @param EventRegistration $registration
     * @return EventPayment
     */
    public function createFreePayment(EventRegistration $registration): EventPayment
    {
        $payment = $this->createPayment($registration, 'free');

        // Segna immediatamente come completato con transazione free
        $payment->markAsPaid('FREE-' . time(), [
            'type' => 'free',
            'note' => 'Free event - no payment required',
        ]);

        // Conferma la registrazione
        $registration->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        Log::info('Free payment created and completed', [
            'payment_id' => $payment->id,
            'registration_id' => $registration->id,
        ]);

        return $payment;
    }
}
