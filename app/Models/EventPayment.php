<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class EventPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_registration_id',
        'user_id',
        'school_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'payment_gateway_response',
        'payer_email',
        'payer_name',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_gateway_response' => 'array',
    ];

    // RELATIONSHIPS

    /**
     * Ottiene l'evento associato al pagamento
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Ottiene la registrazione all'evento associata
     */
    public function eventRegistration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class);
    }

    /**
     * Ottiene l'utente che ha effettuato il pagamento
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ottiene la scuola associata al pagamento
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // STATUS METHODS

    /**
     * Verifica se il pagamento è in attesa
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Verifica se il pagamento è completato
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Verifica se il pagamento è fallito
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Verifica se il pagamento è stato rimborsato
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    /**
     * Segna il pagamento come completato
     *
     * @param string $transactionId ID transazione dal gateway
     * @param array $gatewayResponse Risposta completa dal gateway
     * @return void
     */
    public function markAsPaid(string $transactionId, array $gatewayResponse = []): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'payment_gateway_response' => $gatewayResponse,
            'paid_at' => now(),
        ]);

        \Log::info('Payment marked as paid', [
            'payment_id' => $this->id,
            'transaction_id' => $transactionId,
            'amount' => $this->amount,
        ]);
    }

    /**
     * Segna il pagamento come rimborsato
     *
     * @return void
     */
    public function markAsRefunded(): void
    {
        $this->update([
            'status' => 'refunded',
        ]);

        \Log::info('Payment marked as refunded', [
            'payment_id' => $this->id,
            'transaction_id' => $this->transaction_id,
            'amount' => $this->amount,
        ]);
    }

    // SCOPES

    /**
     * Filtra solo i pagamenti completati
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * Filtra solo i pagamenti in attesa
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    /**
     * Filtra i pagamenti per evento
     *
     * @param Builder $query
     * @param int $eventId
     * @return Builder
     */
    public function scopeForEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    // ACCESSORS

    /**
     * Ottiene l'importo formattato con valuta
     */
    public function getFormattedAmountAttribute(): string
    {
        return '€' . number_format($this->amount, 2, ',', '.');
    }
}
