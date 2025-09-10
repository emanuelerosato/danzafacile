<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * Enum per i metodi di pagamento
     */
    const METHOD_CASH = 'cash';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';

    /**
     * Enum per lo status del pagamento
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'school_id',
        'course_id',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'status',
        'payment_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'payment_date' => 'datetime',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene l'utente che ha effettuato il pagamento
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ottiene la scuola che ha ricevuto il pagamento
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Ottiene il corso per cui è stato effettuato il pagamento
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // SCOPES

    /**
     * Filtra i pagamenti per status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Filtra solo i pagamenti completati
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Filtra solo i pagamenti in attesa
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Filtra solo i pagamenti in elaborazione
     */
    public function scopeProcessing(Builder $query): Builder
    {
        return $query->where('status', 'processing');
    }

    /**
     * Filtra solo i pagamenti falliti
     */
    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FAILED);
    }

    /**
     * Filtra solo i pagamenti rimborsati
     */
    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REFUNDED);
    }

    /**
     * Filtra i pagamenti per utente
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtra i pagamenti per scuola
     */
    public function scopeBySchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Filtra i pagamenti per corso
     */
    public function scopeByCourse(Builder $query, int $courseId): Builder
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Filtra i pagamenti per metodo di pagamento
     */
    public function scopeByPaymentMethod(Builder $query, string $method): Builder
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Filtra i pagamenti per periodo
     */
    public function scopeInDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    /**
     * Filtra i pagamenti dell'ultimo mese
     */
    public function scopeLastMonth(Builder $query): Builder
    {
        return $query->whereBetween('payment_date', [
            now()->subMonth()->startOfMonth(),
            now()->subMonth()->endOfMonth()
        ]);
    }

    /**
     * Filtra i pagamenti di questo mese
     */
    public function scopeThisMonth(Builder $query): Builder
    {
        return $query->whereBetween('payment_date', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    // ACCESSORS

    /**
     * Ottiene l'importo formattato con valuta
     */
    public function getFormattedAmountAttribute(): string
    {
        $symbol = $this->currency === 'EUR' ? '€' : $this->currency;
        return $symbol . ' ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Verifica se il pagamento è completato
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    /**
     * Verifica se il pagamento è in attesa
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica se il pagamento è fallito
     */
    public function getIsFailedAttribute(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    // MUTATORS

    /**
     * Imposta il metodo di pagamento con validazione
     */
    public function setPaymentMethodAttribute($value): void
    {
        $allowedMethods = [
            self::METHOD_CASH,
            self::METHOD_CREDIT_CARD,
            self::METHOD_BANK_TRANSFER
        ];
        
        $this->attributes['payment_method'] = in_array($value, $allowedMethods) ? $value : self::METHOD_CASH;
    }

    /**
     * Imposta lo status con validazione
     */
    public function setStatusAttribute($value): void
    {
        $allowedStatuses = [
            self::STATUS_PENDING,
            self::STATUS_COMPLETED,
            self::STATUS_FAILED,
            self::STATUS_REFUNDED
        ];
        
        $this->attributes['status'] = in_array($value, $allowedStatuses) ? $value : self::STATUS_PENDING;
    }

    /**
     * Imposta la valuta in maiuscolo
     */
    public function setCurrencyAttribute($value): void
    {
        $this->attributes['currency'] = $value ? strtoupper($value) : 'EUR';
    }

    /**
     * Valida che l'importo sia positivo
     */
    public function setAmountAttribute($value): void
    {
        $this->attributes['amount'] = max(0, (float) $value);
    }

    // HELPER METHODS

    /**
     * Ottiene tutti i metodi di pagamento disponibili
     */
    public static function getAvailablePaymentMethods(): array
    {
        return [
            self::METHOD_CASH => 'Contanti',
            self::METHOD_CREDIT_CARD => 'Carta di Credito',
            self::METHOD_BANK_TRANSFER => 'Bonifico Bancario',
        ];
    }

    /**
     * Ottiene tutti gli status disponibili
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'In Attesa',
            self::STATUS_COMPLETED => 'Completato',
            self::STATUS_FAILED => 'Fallito',
            self::STATUS_REFUNDED => 'Rimborsato',
        ];
    }

    /**
     * Completa il pagamento
     */
    public function complete(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        $this->payment_date = now();
        return $this->save();
    }

    /**
     * Annulla il pagamento
     */
    public function cancel(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Marca il pagamento come fallito
     */
    public function fail(): bool
    {
        $this->status = self::STATUS_FAILED;
        return $this->save();
    }

    /**
     * Rimborsa il pagamento
     */
    public function refund(): bool
    {
        $this->status = self::STATUS_REFUNDED;
        return $this->save();
    }

    /**
     * Verifica se il pagamento può essere rimborsato
     */
    public function canBeRefunded(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}