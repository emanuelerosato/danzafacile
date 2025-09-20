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
    const LEVEL_BEGINNER = 'beginner';
    const LEVEL_INTERMEDIATE = 'intermediate';
    const LEVEL_ADVANCED = 'advanced';
    const LEVEL_PROFESSIONAL = 'professional';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'name',
        'code',
        'description',
        'short_description',
        'dance_type',
        'level',
        'difficulty_level',
        'min_age',
        'max_age',
        'prerequisites',
        'equipment',
        'objectives',
        'notes',
        'duration_weeks',
        'max_students',
        'price',
        'monthly_price',
        'enrollment_fee',
        'single_lesson_price',
        'trial_price',
        'price_application',
        'start_date',
        'end_date',
        'schedule',
        'location',
        'instructor_id',
        'active',
        'status',
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
            'equipment' => 'array',
            'objectives' => 'array',
            'start_date' => 'date',
            'end_date' => 'date',
            'price' => 'decimal:2',
            'monthly_price' => 'decimal:2',
            'enrollment_fee' => 'decimal:2',
            'single_lesson_price' => 'decimal:2',
            'trial_price' => 'decimal:2',
            'active' => 'boolean',
            'max_students' => 'integer',
            'min_age' => 'integer',
            'max_age' => 'integer',
            'duration_weeks' => 'integer',
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
     * Alias per courseEnrollments per compatibilità
     */
    public function enrollments(): HasMany
    {
        return $this->courseEnrollments();
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
     * Ottiene tutti i record di presenza per questo corso
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class);
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
                                 ->where('status', 'active')
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
        // Mapping da italiano a inglese
        $levelMapping = [
            'principiante' => self::LEVEL_BEGINNER,
            'intermedio' => self::LEVEL_INTERMEDIATE,
            'avanzato' => self::LEVEL_ADVANCED,
            'professionale' => self::LEVEL_PROFESSIONAL,
            'Principiante' => self::LEVEL_BEGINNER,
            'Intermedio' => self::LEVEL_INTERMEDIATE,
            'Avanzato' => self::LEVEL_ADVANCED,
            'Professionale' => self::LEVEL_PROFESSIONAL,
            // Valori inglesi (già corretti)
            self::LEVEL_BEGINNER => self::LEVEL_BEGINNER,
            self::LEVEL_INTERMEDIATE => self::LEVEL_INTERMEDIATE,
            self::LEVEL_ADVANCED => self::LEVEL_ADVANCED,
            self::LEVEL_PROFESSIONAL => self::LEVEL_PROFESSIONAL,
        ];

        $this->attributes['level'] = $levelMapping[$value] ?? self::LEVEL_BEGINNER;
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
                    ->where('status', 'active')
                    ->count();
    }

    /**
     * Verifica se un utente è iscritto al corso
     */
    public function hasStudent(User $user): bool
    {
        return $this->courseEnrollments()
                    ->where('user_id', $user->id)
                    ->where('status', 'active')
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

    /**
     * Ottiene i dati dell'orario decodificati e corretti
     */
    public function getScheduleDataAttribute()
    {
        if (!$this->schedule) {
            return null;
        }

        $scheduleData = null;

        if (is_array($this->schedule)) {
            $scheduleData = $this->schedule;
        } elseif (is_string($this->schedule)) {
            $scheduleData = json_decode($this->schedule, true);
        }

        // Fix encoding issues and format times
        if ($scheduleData && is_array($scheduleData)) {
            foreach ($scheduleData as &$slot) {
                if (isset($slot['day'])) {
                    // Ensure UTF-8 encoding
                    $slot['day'] = mb_convert_encoding($slot['day'], 'UTF-8', 'auto');

                    // Fix common encoding issues with Italian accented characters
                    $encodingFixes = [
                        'LunedÃ¬' => 'Lunedì',
                        'MartedÃ¬' => 'Martedì',
                        'MercoledÃ¬' => 'Mercoledì',
                        'GiovedÃ¬' => 'Giovedì',
                        'VenerdÃ¬' => 'Venerdì',
                        'SabatoÃ¬' => 'Sabato',
                        'DomenicaÃ¬' => 'Domenica',
                        // Additional variations
                        'Luned%C3%AC' => 'Lunedì',
                        'Marted%C3%AC' => 'Martedì',
                        'Mercoled%C3%AC' => 'Mercoledì',
                        'Gioved%C3%AC' => 'Giovedì',
                        'Venerd%C3%AC' => 'Venerdì'
                    ];

                    foreach ($encodingFixes as $broken => $fixed) {
                        $slot['day'] = str_replace($broken, $fixed, $slot['day']);
                    }

                    // Normalize day names (in case they come without accents)
                    $dayNormalizations = [
                        'Lunedi' => 'Lunedì',
                        'Martedi' => 'Martedì',
                        'Mercoledi' => 'Mercoledì',
                        'Giovedi' => 'Giovedì',
                        'Venerdi' => 'Venerdì'
                    ];

                    if (isset($dayNormalizations[$slot['day']])) {
                        $slot['day'] = $dayNormalizations[$slot['day']];
                    }
                }

                // Ensure UTF-8 encoding for location
                if (isset($slot['location'])) {
                    $slot['location'] = mb_convert_encoding($slot['location'], 'UTF-8', 'auto');
                }

                // Format time to HH:MM (remove seconds)
                if (isset($slot['start_time'])) {
                    $slot['start_time'] = substr($slot['start_time'], 0, 5);
                }
                if (isset($slot['end_time'])) {
                    $slot['end_time'] = substr($slot['end_time'], 0, 5);
                }
            }
        }

        return $scheduleData;
    }
}