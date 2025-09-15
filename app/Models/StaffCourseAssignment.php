<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class StaffCourseAssignment extends Model
{
    use HasFactory;

    /**
     * Assignment type constants
     */
    const TYPE_PRIMARY_INSTRUCTOR = 'primary_instructor';
    const TYPE_ASSISTANT_INSTRUCTOR = 'assistant_instructor';
    const TYPE_SUBSTITUTE = 'substitute';
    const TYPE_COORDINATOR = 'coordinator';

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_COMPLETED = 'completed';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'staff_id',
        'course_id',
        'assignment_type',
        'start_date',
        'end_date',
        'status',
        'rate_override',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'rate_override' => 'decimal:2',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene lo staff member assegnato
     */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Ottiene il corso assegnato
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    // SCOPES

    /**
     * Filtra per tipo di assegnazione
     */
    public function scopeByAssignmentType(Builder $query, string $type): Builder
    {
        return $query->where('assignment_type', $type);
    }

    /**
     * Filtra per status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Filtra solo assegnazioni attive
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Filtra solo primary instructors
     */
    public function scopePrimaryInstructors(Builder $query): Builder
    {
        return $query->where('assignment_type', self::TYPE_PRIMARY_INSTRUCTOR);
    }

    /**
     * Filtra per corso
     */
    public function scopeByCourse(Builder $query, int $courseId): Builder
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Filtra per staff
     */
    public function scopeByStaff(Builder $query, int $staffId): Builder
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Filtra assegnazioni attive in un periodo
     */
    public function scopeActiveDuring(Builder $query, $date): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
                    ->where('start_date', '<=', $date)
                    ->where(function($q) use ($date) {
                        $q->whereNull('end_date')
                          ->orWhere('end_date', '>=', $date);
                    });
    }

    // ACCESSORS

    /**
     * Verifica se l'assegnazione è attiva
     */
    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Verifica se è un primary instructor
     */
    public function getIsPrimaryInstructorAttribute(): bool
    {
        return $this->assignment_type === self::TYPE_PRIMARY_INSTRUCTOR;
    }

    /**
     * Ottiene la tariffa effettiva (override o base)
     */
    public function getEffectiveRateAttribute(): ?float
    {
        return $this->rate_override ?? $this->staff->hourly_rate;
    }

    /**
     * Ottiene il badge del tipo di assegnazione
     */
    public function getAssignmentTypeBadgeAttribute(): string
    {
        return match($this->assignment_type) {
            self::TYPE_PRIMARY_INSTRUCTOR => 'bg-blue-100 text-blue-800',
            self::TYPE_ASSISTANT_INSTRUCTOR => 'bg-green-100 text-green-800',
            self::TYPE_SUBSTITUTE => 'bg-orange-100 text-orange-800',
            self::TYPE_COORDINATOR => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Ottiene il badge dello status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_INACTIVE => 'bg-gray-100 text-gray-800',
            self::STATUS_COMPLETED => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // HELPER METHODS

    /**
     * Ottiene tutti i tipi di assegnazione disponibili
     */
    public static function getAvailableAssignmentTypes(): array
    {
        return [
            self::TYPE_PRIMARY_INSTRUCTOR => 'Istruttore Principale',
            self::TYPE_ASSISTANT_INSTRUCTOR => 'Istruttore Assistente',
            self::TYPE_SUBSTITUTE => 'Sostituto',
            self::TYPE_COORDINATOR => 'Coordinatore',
        ];
    }

    /**
     * Ottiene tutti gli status disponibili
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_ACTIVE => 'Attivo',
            self::STATUS_INACTIVE => 'Inattivo',
            self::STATUS_COMPLETED => 'Completato',
        ];
    }

    /**
     * Verifica se l'assegnazione è valida nel periodo
     */
    public function isValidDuring($date): bool
    {
        $checkDate = is_string($date) ? \Carbon\Carbon::parse($date) : $date;

        return $this->start_date <= $checkDate &&
               ($this->end_date === null || $this->end_date >= $checkDate) &&
               $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Calcola la durata dell'assegnazione in giorni
     */
    public function getDurationInDays(): ?int
    {
        if (!$this->end_date) {
            return null; // Assegnazione a tempo indeterminato
        }

        return $this->start_date->diffInDays($this->end_date);
    }

    /**
     * Calcola il guadagno totale stimato per l'assegnazione
     */
    public function getEstimatedTotalEarnings(): float
    {
        $rate = $this->effective_rate;
        if (!$rate) {
            return 0;
        }

        // Calcola le ore settimanali del corso
        $weeklyHours = $this->course->duration_hours ?? 0;

        // Se c'è una data di fine, calcola il numero di settimane
        if ($this->end_date) {
            $weeks = $this->start_date->diffInWeeks($this->end_date);
            return $rate * $weeklyHours * $weeks;
        }

        // Se non c'è data di fine, restituisce il guadagno settimanale
        return $rate * $weeklyHours;
    }
}
