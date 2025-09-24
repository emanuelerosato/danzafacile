<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseEnrollment extends Model
{
    use HasFactory;

    /**
     * Enum per lo status dell'iscrizione
     */
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_COMPLETED = 'completed';

    /**
     * Enum per lo status del pagamento
     */
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_REFUNDED = 'refunded';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'course_id',
        'user_id',
        'enrollment_date',
        'status',
        'payment_status',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'enrollment_date' => 'date',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene il corso a cui l'utente è iscritto
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Ottiene l'utente iscritto al corso
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ottiene tutti i pagamenti relativi a questa iscrizione
     * Relazione personalizzata che filtra per course_id, user_id e tipo
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'user_id', 'user_id')
                    ->whereColumn('payments.course_id', 'course_enrollments.course_id')
                    ->where('payment_type', Payment::TYPE_COURSE_ENROLLMENT);
    }

    // SCOPES

    /**
     * Filtra le iscrizioni per status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Filtra le iscrizioni per status del pagamento
     */
    public function scopeByPaymentStatus(Builder $query, string $paymentStatus): Builder
    {
        return $query->where('payment_status', $paymentStatus);
    }

    /**
     * Filtra solo le iscrizioni in attesa
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Filtra solo le iscrizioni attive
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Filtra solo le iscrizioni sospese
     */
    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', 'suspended');
    }

    /**
     * Filtra solo le iscrizioni annullate
     */
    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    /**
     * Filtra solo le iscrizioni completate
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Filtra le iscrizioni per utente
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtra le iscrizioni per corso
     */
    public function scopeByCourse(Builder $query, int $courseId): Builder
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Filtra le iscrizioni con pagamenti in sospeso
     */
    public function scopePaymentPending(Builder $query): Builder
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PENDING);
    }

    /**
     * Filtra le iscrizioni completamente pagate
     */
    public function scopePaymentCompleted(Builder $query): Builder
    {
        return $query->where('payment_status', self::PAYMENT_STATUS_PAID);
    }

    // MUTATORS

    /**
     * Imposta lo status con validazione
     */
    public function setStatusAttribute($value): void
    {
        $allowedStatuses = [
            self::STATUS_PENDING,
            self::STATUS_ACTIVE,
            self::STATUS_CANCELLED,
            self::STATUS_COMPLETED
        ];
        
        $this->attributes['status'] = in_array($value, $allowedStatuses) ? $value : self::STATUS_PENDING;
    }

    /**
     * Imposta il payment_status con validazione
     */
    public function setPaymentStatusAttribute($value): void
    {
        $allowedStatuses = [
            self::PAYMENT_STATUS_PENDING,
            self::PAYMENT_STATUS_PAID,
            self::PAYMENT_STATUS_REFUNDED
        ];
        
        $this->attributes['payment_status'] = in_array($value, $allowedStatuses) ? $value : self::PAYMENT_STATUS_PENDING;
    }

    // HELPER METHODS

    /**
     * Ottiene tutti gli status disponibili
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'In Attesa',
            self::STATUS_ACTIVE => 'Attiva',
            self::STATUS_CANCELLED => 'Annullata',
            self::STATUS_COMPLETED => 'Completata',
        ];
    }

    /**
     * Ottiene tutti i payment status disponibili
     */
    public static function getAvailablePaymentStatuses(): array
    {
        return [
            self::PAYMENT_STATUS_PENDING => 'In Attesa',
            self::PAYMENT_STATUS_PAID => 'Pagato',
            self::PAYMENT_STATUS_REFUNDED => 'Rimborsato',
        ];
    }

    /**
     * Verifica se l'iscrizione è attiva
     */
    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Verifica se l'iscrizione è in attesa
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Verifica se l'iscrizione è stata annullata
     */
    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    /**
     * Verifica se il pagamento è completato
     */
    public function isPaymentCompleted(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PAID;
    }

    /**
     * Verifica se il pagamento è in attesa
     */
    public function isPaymentPending(): bool
    {
        return $this->payment_status === self::PAYMENT_STATUS_PENDING;
    }

    /**
     * Attiva l'iscrizione
     */
    public function activate(): bool
    {
        $this->status = self::STATUS_ACTIVE;
        return $this->save();
    }

    /**
     * Sospende l'iscrizione
     */
    public function suspend(): bool
    {
        $this->status = 'suspended';
        return $this->save();
    }

    /**
     * Annulla l'iscrizione
     */
    public function cancel(): bool
    {
        $this->status = self::STATUS_CANCELLED;
        return $this->save();
    }

    /**
     * Completa l'iscrizione
     */
    public function complete(): bool
    {
        $this->status = self::STATUS_COMPLETED;
        return $this->save();
    }
}