<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Payment extends Model
{
    use HasFactory;

    /**
     * Enum per i metodi di pagamento
     */
    const METHOD_CASH = 'cash';
    const METHOD_CREDIT_CARD = 'credit_card';
    const METHOD_DEBIT_CARD = 'debit_card';
    const METHOD_BANK_TRANSFER = 'bank_transfer';
    const METHOD_PAYPAL = 'paypal';
    const METHOD_STRIPE = 'stripe';
    const METHOD_ONLINE = 'online';
    const METHOD_CHECK = 'check';

    /**
     * Enum per lo status del pagamento
     */
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_PROCESSING = 'processing';
    const STATUS_PARTIAL = 'partial';

    /**
     * Enum per i tipi di pagamento
     */
    const TYPE_COURSE_ENROLLMENT = 'course_enrollment';
    const TYPE_EVENT_REGISTRATION = 'event_registration';
    const TYPE_MEMBERSHIP_FEE = 'membership_fee';
    const TYPE_MATERIAL = 'material';
    const TYPE_OTHER = 'other';

    /**
     * Enum per le frequenze di rata
     */
    const INSTALLMENT_MONTHLY = 'monthly';
    const INSTALLMENT_QUARTERLY = 'quarterly';
    const INSTALLMENT_BIANNUAL = 'biannual';
    const INSTALLMENT_ANNUAL = 'annual';
    const INSTALLMENT_CUSTOM = 'custom';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'school_id',
        'course_id',
        'event_id',
        'amount',
        'currency',
        'payment_method',
        'transaction_id',
        'status',
        'payment_type',
        'payment_date',
        'due_date',
        'notes',
        'receipt_number',
        'is_installment',
        'parent_payment_id',
        'installment_number',
        'total_installments',
        'installment_frequency',
        'tax_amount',
        'discount_amount',
        'net_amount',
        'reference_number',
        'payment_gateway_fee',
        'refund_reason',
        'processed_by_user_id',
        'gateway_response',
        'receipt_sent_at',
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
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'payment_gateway_fee' => 'decimal:2',
            'payment_date' => 'datetime',
            'due_date' => 'datetime',
            'receipt_sent_at' => 'datetime',
            'is_installment' => 'boolean',
            'installment_number' => 'integer',
            'total_installments' => 'integer',
            'gateway_response' => 'array',
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

    /**
     * Ottiene l'evento per cui è stato effettuato il pagamento
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Ottiene l'utente che ha elaborato il pagamento
     */
    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by_user_id');
    }

    /**
     * Ottiene il pagamento principale (per le rate)
     */
    public function parentPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'parent_payment_id');
    }

    /**
     * Ottiene tutte le rate collegate a questo pagamento
     */
    public function installments(): HasMany
    {
        return $this->hasMany(Payment::class, 'parent_payment_id');
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
     * Filtra i pagamenti per evento
     */
    public function scopeByEvent(Builder $query, int $eventId): Builder
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Filtra i pagamenti per tipo
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('payment_type', $type);
    }

    /**
     * Filtra solo le rate principali (non installments)
     */
    public function scopeMainPayments(Builder $query): Builder
    {
        return $query->whereNull('parent_payment_id');
    }

    /**
     * Filtra solo le rate (installments)
     */
    public function scopeInstallments(Builder $query): Builder
    {
        return $query->whereNotNull('parent_payment_id');
    }

    /**
     * Filtra i pagamenti scaduti
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('due_date', '<', now())
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL]);
    }

    /**
     * Filtra i pagamenti in scadenza
     */
    public function scopeDueSoon(Builder $query, int $days = 7): Builder
    {
        return $query->whereBetween('due_date', [now(), now()->addDays($days)])
                    ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PARTIAL]);
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

    /**
     * Verifica se il pagamento è stato rimborsato
     */
    public function getIsRefundedAttribute(): bool
    {
        return $this->status === self::STATUS_REFUNDED;
    }

    /**
     * Verifica se il pagamento è scaduto
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() &&
               in_array($this->status, [self::STATUS_PENDING, self::STATUS_PARTIAL]);
    }

    /**
     * Ottiene l'importo formattato con valuta e simbolo
     */
    public function getFormattedFullAmountAttribute(): string
    {
        $symbol = $this->currency === 'EUR' ? '€' : $this->currency;
        $amount = number_format($this->amount, 2, ',', '.');
        return "{$amount} {$symbol}";
    }

    /**
     * Ottiene il nome leggibile del tipo di pagamento
     */
    public function getPaymentTypeNameAttribute(): string
    {
        return match($this->payment_type) {
            self::TYPE_COURSE_ENROLLMENT => 'Iscrizione Corso',
            self::TYPE_EVENT_REGISTRATION => 'Registrazione Evento',
            self::TYPE_MEMBERSHIP_FEE => 'Quota Associativa',
            self::TYPE_MATERIAL => 'Materiale',
            self::TYPE_OTHER => 'Altro',
            default => 'Non Specificato'
        };
    }

    /**
     * Ottiene il nome leggibile del metodo di pagamento
     */
    public function getPaymentMethodNameAttribute(): string
    {
        return match($this->payment_method) {
            self::METHOD_CASH => 'Contanti',
            self::METHOD_CREDIT_CARD => 'Carta di Credito',
            self::METHOD_DEBIT_CARD => 'Carta di Debito',
            self::METHOD_BANK_TRANSFER => 'Bonifico Bancario',
            self::METHOD_PAYPAL => 'PayPal',
            self::METHOD_STRIPE => 'Stripe',
            self::METHOD_ONLINE => 'Pagamento Online',
            self::METHOD_CHECK => 'Assegno',
            default => 'Non Specificato'
        };
    }

    /**
     * Ottiene il nome leggibile dello status
     */
    public function getStatusNameAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'In Attesa',
            self::STATUS_COMPLETED => 'Completato',
            self::STATUS_FAILED => 'Fallito',
            self::STATUS_REFUNDED => 'Rimborsato',
            self::STATUS_CANCELLED => 'Annullato',
            self::STATUS_PROCESSING => 'In Elaborazione',
            self::STATUS_PARTIAL => 'Parziale',
            default => 'Sconosciuto'
        };
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
            self::METHOD_DEBIT_CARD,
            self::METHOD_BANK_TRANSFER,
            self::METHOD_PAYPAL,
            self::METHOD_STRIPE,
            self::METHOD_ONLINE,
            self::METHOD_CHECK
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
            self::STATUS_REFUNDED,
            self::STATUS_CANCELLED,
            self::STATUS_PROCESSING,
            self::STATUS_PARTIAL
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
            self::METHOD_DEBIT_CARD => 'Carta di Debito',
            self::METHOD_BANK_TRANSFER => 'Bonifico Bancario',
            self::METHOD_PAYPAL => 'PayPal',
            self::METHOD_STRIPE => 'Stripe',
            self::METHOD_ONLINE => 'Pagamento Online',
            self::METHOD_CHECK => 'Assegno',
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
            self::STATUS_CANCELLED => 'Annullato',
            self::STATUS_PROCESSING => 'In Elaborazione',
            self::STATUS_PARTIAL => 'Parziale',
        ];
    }

    /**
     * Ottiene tutti i tipi di pagamento disponibili
     */
    public static function getAvailableTypes(): array
    {
        return [
            self::TYPE_COURSE_ENROLLMENT => 'Iscrizione Corso',
            self::TYPE_EVENT_REGISTRATION => 'Registrazione Evento',
            self::TYPE_MEMBERSHIP_FEE => 'Quota Associativa',
            self::TYPE_MATERIAL => 'Materiale',
            self::TYPE_OTHER => 'Altro',
        ];
    }

    /**
     * Ottiene le frequenze di rata disponibili
     */
    public static function getAvailableInstallmentFrequencies(): array
    {
        return [
            self::INSTALLMENT_MONTHLY => 'Mensile',
            self::INSTALLMENT_QUARTERLY => 'Trimestrale',
            self::INSTALLMENT_BIANNUAL => 'Semestrale',
            self::INSTALLMENT_ANNUAL => 'Annuale',
            self::INSTALLMENT_CUSTOM => 'Personalizzato',
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

    /**
     * Genera un numero di ricevuta univoco
     */
    public function generateReceiptNumber(): string
    {
        if ($this->receipt_number) {
            return $this->receipt_number;
        }

        $year = now()->year;
        $schoolPrefix = strtoupper(substr($this->school->name, 0, 3));
        $sequence = str_pad($this->id, 6, '0', STR_PAD_LEFT);

        $this->receipt_number = "{$schoolPrefix}-{$year}-{$sequence}";
        $this->save();

        return $this->receipt_number;
    }

    /**
     * Crea le rate per un pagamento rateale
     */
    public function createInstallments(int $numberOfInstallments, string $frequency = self::INSTALLMENT_MONTHLY): array
    {
        if ($this->is_installment || $this->parent_payment_id) {
            throw new \Exception('Impossibile creare rate per un pagamento che è già una rata.');
        }

        $installmentAmount = round($this->amount / $numberOfInstallments, 2);
        $installments = [];

        for ($i = 1; $i <= $numberOfInstallments; $i++) {
            $dueDate = $this->calculateInstallmentDueDate($i, $frequency);

            // Aggiusta l'ultimo importo per compensare eventuali arrotondamenti
            $amount = ($i === $numberOfInstallments) ?
                $this->amount - (($numberOfInstallments - 1) * $installmentAmount) :
                $installmentAmount;

            $installment = self::create([
                'user_id' => $this->user_id,
                'school_id' => $this->school_id,
                'course_id' => $this->course_id,
                'event_id' => $this->event_id,
                'parent_payment_id' => $this->id,
                'amount' => $amount,
                'currency' => $this->currency,
                'payment_method' => $this->payment_method,
                'payment_type' => $this->payment_type,
                'status' => self::STATUS_PENDING,
                'due_date' => $dueDate,
                'is_installment' => true,
                'installment_number' => $i,
                'total_installments' => $numberOfInstallments,
                'installment_frequency' => $frequency,
                'notes' => "Rata {$i} di {$numberOfInstallments}",
            ]);

            $installments[] = $installment;
        }

        // Aggiorna il pagamento principale
        $this->update([
            'status' => self::STATUS_PARTIAL,
            'total_installments' => $numberOfInstallments,
            'installment_frequency' => $frequency,
        ]);

        return $installments;
    }

    /**
     * Calcola la data di scadenza per una rata
     */
    private function calculateInstallmentDueDate(int $installmentNumber, string $frequency): \Carbon\Carbon
    {
        $baseDate = $this->due_date ?? $this->payment_date ?? now();

        return match($frequency) {
            self::INSTALLMENT_MONTHLY => $baseDate->copy()->addMonths($installmentNumber - 1),
            self::INSTALLMENT_QUARTERLY => $baseDate->copy()->addMonths(($installmentNumber - 1) * 3),
            self::INSTALLMENT_BIANNUAL => $baseDate->copy()->addMonths(($installmentNumber - 1) * 6),
            self::INSTALLMENT_ANNUAL => $baseDate->copy()->addYears($installmentNumber - 1),
            default => $baseDate->copy()->addDays($installmentNumber * 30),
        };
    }

    /**
     * Ottiene il totale pagato per tutte le rate
     */
    public function getTotalPaidForInstallments(): float
    {
        if (!$this->is_installment && !$this->installments->count()) {
            return $this->status === self::STATUS_COMPLETED ? $this->amount : 0;
        }

        return $this->installments()
            ->where('status', self::STATUS_COMPLETED)
            ->sum('amount');
    }

    /**
     * Ottiene il saldo rimanente
     */
    public function getRemainingBalance(): float
    {
        if ($this->is_installment || $this->parent_payment_id) {
            return $this->status === self::STATUS_COMPLETED ? 0 : $this->amount;
        }

        return $this->amount - $this->getTotalPaidForInstallments();
    }

    /**
     * Verifica se tutte le rate sono state pagate
     */
    public function areAllInstallmentsPaid(): bool
    {
        if (!$this->installments->count()) {
            return false;
        }

        return $this->installments()
            ->where('status', '!=', self::STATUS_COMPLETED)
            ->count() === 0;
    }

    /**
     * Marca tutte le rate come completate
     */
    public function completeAllInstallments(): bool
    {
        $this->installments()
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING])
            ->update([
                'status' => self::STATUS_COMPLETED,
                'payment_date' => now(),
            ]);

        // Aggiorna il pagamento principale
        $this->update(['status' => self::STATUS_COMPLETED]);

        return true;
    }

    /**
     * Ottiene la prossima rata in scadenza
     */
    public function getNextDueInstallment()
    {
        return $this->installments()
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_PROCESSING])
            ->orderBy('due_date')
            ->first();
    }

    /**
     * Auto-scope per filtrare per scuola (multi-tenant security)
     */
    protected static function booted(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            $user = Auth::user();
            if ($user && !$user->isSuperAdmin() && $user->school_id) {
                $builder->where('school_id', $user->school_id);
            }
        });

        // Auto-assign school_id when creating
        static::creating(function (Payment $payment) {
            $user = Auth::user();
            if ($user && !$user->isSuperAdmin() && $user->school_id && !$payment->school_id) {
                $payment->school_id = $user->school_id;
            }
        });
    }
}