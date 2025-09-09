<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Course extends Model
{
    use HasFactory;

    /**
     * Enum per i livelli del corso
     */
    const LEVEL_BEGINNER = 'principiante';
    const LEVEL_INTERMEDIATE = 'intermedio';
    const LEVEL_ADVANCED = 'avanzato';
    const LEVEL_PROFESSIONAL = 'professionale';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'description',
        'level',
        'max_students',
        'price',
        'start_date',
        'end_date',
        'schedule',
        'instructor_id',
        'active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'schedule' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
            'active' => 'boolean',
            'max_students' => 'integer',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene la scuola che offre il corso
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Ottiene l'istruttore del corso
     */
    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    /**
     * Ottiene tutte le iscrizioni al corso
     */
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Ottiene tutti i pagamenti relativi al corso
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Ottiene tutti i documenti del corso
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Ottiene tutte le gallerie media del corso
     */
    public function mediaGalleries(): HasMany
    {
        return $this->hasMany(MediaGallery::class);
    }

    /**
     * Ottiene tutti gli studenti iscritti al corso
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_enrollments')
                    ->withPivot('enrollment_date', 'status', 'payment_status')
                    ->withTimestamps();
    }

    // SCOPES

    /**
     * Filtra i corsi per livello
     */
    public function scopeByLevel(Builder $query, string $level): Builder
    {
        return $query->where('level', $level);
    }

    /**
     * Filtra solo i corsi attivi
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Filtra i corsi per scuola
     */
    public function scopeBySchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Filtra i corsi che devono ancora iniziare
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', now());
    }

    /**
     * Filtra i corsi attualmente in corso
     */
    public function scopeCurrent(Builder $query): Builder
    {
        return $query->where('start_date', '<=', now())
                    ->where('end_date', '>=', now());
    }

    /**
     * Filtra i corsi terminati
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('end_date', '<', now());
    }

    /**
     * Filtra i corsi per istruttore
     */
    public function scopeByInstructor(Builder $query, int $instructorId): Builder
    {
        return $query->where('instructor_id', $instructorId);
    }

    // ACCESSORS

    /**
     * Verifica se il corso è aperto alle iscrizioni
     */
    public function getIsEnrollableAttribute(): bool
    {
        if (!$this->active) {
            return false;
        }

        // Controlla se il corso non è ancora iniziato
        if ($this->start_date && $this->start_date->isPast()) {
            return false;
        }

        // Controlla se ci sono posti disponibili
        return $this->remaining_spots > 0;
    }

    /**
     * Ottiene il numero di posti rimanenti
     */
    public function getRemaininingSpotsAttribute(): int
    {
        $enrolledStudents = $this->courseEnrollments()
                                 ->where('status', 'attiva')
                                 ->count();

        return max(0, $this->max_students - $enrolledStudents);
    }

    /**
     * Ottiene il prezzo formattato
     */
    public function getFormattedPriceAttribute(): string
    {
        return '€ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Verifica se il corso è iniziato
     */
    public function getIsStartedAttribute(): bool
    {
        return $this->start_date && $this->start_date->isPast();
    }

    /**
     * Verifica se il corso è terminato
     */
    public function getIsCompletedAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    /**
     * Ottiene la durata del corso in giorni
     */
    public function getDurationInDaysAttribute(): ?int
    {
        if (!$this->start_date || !$this->end_date) {
            return null;
        }

        return $this->start_date->diffInDays($this->end_date);
    }

    // MUTATORS

    /**
     * Imposta il livello con validazione
     */
    public function setLevelAttribute($value): void
    {
        $allowedLevels = [
            self::LEVEL_BEGINNER,
            self::LEVEL_INTERMEDIATE,
            self::LEVEL_ADVANCED,
            self::LEVEL_PROFESSIONAL
        ];
        
        $this->attributes['level'] = in_array($value, $allowedLevels) ? $value : self::LEVEL_BEGINNER;
    }

    /**
     * Valida che max_students sia un numero positivo
     */
    public function setMaxStudentsAttribute($value): void
    {
        $this->attributes['max_students'] = max(1, (int) $value);
    }

    /**
     * Valida che il prezzo sia positivo
     */
    public function setPriceAttribute($value): void
    {
        $this->attributes['price'] = max(0, (float) $value);
    }

    // HELPER METHODS

    /**
     * Ottiene tutti i livelli disponibili
     */
    public static function getAvailableLevels(): array
    {
        return [
            self::LEVEL_BEGINNER => 'Principiante',
            self::LEVEL_INTERMEDIATE => 'Intermedio',
            self::LEVEL_ADVANCED => 'Avanzato',
            self::LEVEL_PROFESSIONAL => 'Professionale',
        ];
    }

    /**
     * Ottiene il numero totale di studenti iscritti
     */
    public function getEnrolledStudentsCount(): int
    {
        return $this->courseEnrollments()
                    ->where('status', 'attiva')
                    ->count();
    }

    /**
     * Verifica se un utente è iscritto al corso
     */
    public function hasStudent(User $user): bool
    {
        return $this->courseEnrollments()
                    ->where('user_id', $user->id)
                    ->where('status', 'attiva')
                    ->exists();
    }

    /**
     * Ottiene il fatturato generato dal corso
     */
    public function getTotalRevenue(): float
    {
        return $this->payments()
                    ->where('status', 'completato')
                    ->sum('amount');
    }
}