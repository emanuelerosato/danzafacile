<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Staff extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The \"booted\" method of the model.
     */
    protected static function booted(): void
    {
        // Global scope per multi-tenant security
        static::addGlobalScope('school', function (Builder $builder) {
            if (auth()->check() && auth()->user()->school_id) {
                $builder->where('school_id', auth()->user()->school_id);
            }
        });
    }

    /**
     * Role constants
     */
    const ROLE_INSTRUCTOR = 'instructor';
    const ROLE_COORDINATOR = 'coordinator';
    const ROLE_ADMIN_ASSISTANT = 'admin_assistant';
    const ROLE_RECEPTIONIST = 'receptionist';
    const ROLE_CLEANER = 'cleaner';
    const ROLE_MAINTENANCE = 'maintenance';

    /**
     * Department constants
     */
    const DEPT_DANCE = 'dance';
    const DEPT_ADMINISTRATION = 'administration';
    const DEPT_MAINTENANCE = 'maintenance';
    const DEPT_FRONT_DESK = 'front_desk';

    /**
     * Employment type constants
     */
    const EMPLOYMENT_FULL_TIME = 'full_time';
    const EMPLOYMENT_PART_TIME = 'part_time';
    const EMPLOYMENT_CONTRACT = 'contract';
    const EMPLOYMENT_VOLUNTEER = 'volunteer';

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';
    const STATUS_ON_LEAVE = 'on_leave';
    const STATUS_TERMINATED = 'terminated';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'school_id',
        'user_id',
        'employee_id',
        'role',
        'department',
        'employment_type',
        'status',
        'title',
        'date_of_birth',
        'phone',
        'emergency_contact_name',
        'emergency_contact_phone',
        'address',
        'qualifications',
        'certifications',
        'specializations',
        'years_experience',
        'hire_date',
        'termination_date',
        'hourly_rate',
        'monthly_salary',
        'payment_method',
        'bank_account',
        'tax_id',
        'availability',
        'max_hours_per_week',
        'can_substitute',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'hire_date' => 'date',
            'termination_date' => 'date',
            'hourly_rate' => 'decimal:2',
            'monthly_salary' => 'decimal:2',
            'availability' => 'array',
            'years_experience' => 'integer',
            'max_hours_per_week' => 'integer',
            'can_substitute' => 'boolean',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene la scuola a cui appartiene lo staff
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Ottiene l'utente associato al staff member
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ottiene le assegnazioni ai corsi
     */
    public function courseAssignments(): HasMany
    {
        return $this->hasMany(StaffCourseAssignment::class);
    }

    /**
     * Ottiene le assegnazioni ai corsi attive
     */
    public function activeCourseAssignments(): HasMany
    {
        return $this->courseAssignments()->where('status', 'active');
    }

    /**
     * Ottiene i corsi attraverso le assegnazioni
     */
    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'staff_course_assignments')
                    ->withPivot(['assignment_type', 'start_date', 'end_date', 'status', 'rate_override', 'notes'])
                    ->withTimestamps();
    }

    /**
     * Ottiene i corsi attivi
     */
    public function activeCourses(): BelongsToMany
    {
        return $this->courses()->wherePivot('status', 'active');
    }

    // SCOPES

    /**
     * Filtra per ruolo
     */
    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Filtra per dipartimento
     */
    public function scopeByDepartment(Builder $query, string $department): Builder
    {
        return $query->where('department', $department);
    }

    /**
     * Filtra per tipo di impiego
     */
    public function scopeByEmploymentType(Builder $query, string $type): Builder
    {
        return $query->where('employment_type', $type);
    }

    /**
     * Filtra per status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Filtra solo staff attivo
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Filtra solo instructors
     */
    public function scopeInstructors(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_INSTRUCTOR);
    }

    /**
     * Filtra staff che può fare sostituzioni
     */
    public function scopeCanSubstitute(Builder $query): Builder
    {
        return $query->where('can_substitute', true);
    }

    /**
     * Filtra per specializzazione
     */
    public function scopeBySpecialization(Builder $query, string $specialization): Builder
    {
        return $query->where('specializations', 'LIKE', "%{$specialization}%");
    }

    /**
     * Filtra per disponibilità in un giorno specifico
     */
    public function scopeAvailableOnDay(Builder $query, string $day): Builder
    {
        return $query->whereJsonContains('availability', $day);
    }

    // ACCESSORS

    /**
     * Ottiene il nome completo del staff member
     */
    public function getFullNameAttribute(): string
    {
        $title = $this->title ? $this->title . ' ' : '';
        return $title . $this->user->name;
    }

    /**
     * Ottiene l'età
     */
    public function getAgeAttribute(): ?int
    {
        return $this->date_of_birth ? $this->date_of_birth->diffInYears(now()) : null;
    }

    /**
     * Ottiene il salario formattato
     */
    public function getFormattedHourlyRateAttribute(): string
    {
        return $this->hourly_rate ? '€' . number_format($this->hourly_rate, 2) . '/h' : 'Non specificato';
    }

    /**
     * Ottiene il salario mensile formattato
     */
    public function getFormattedMonthlySalaryAttribute(): string
    {
        return $this->monthly_salary ? '€' . number_format($this->monthly_salary, 2) . '/mese' : 'Non specificato';
    }

    /**
     * Verifica se è un instructor
     */
    public function getIsInstructorAttribute(): bool
    {
        return $this->role === self::ROLE_INSTRUCTOR;
    }

    /**
     * Verifica se è disponibile oggi
     */
    public function getIsAvailableTodayAttribute(): bool
    {
        $today = now()->format('l'); // Monday, Tuesday, etc.
        return in_array(strtolower($today), $this->availability ?? []);
    }

    /**
     * Ottiene il numero di corsi assegnati
     */
    public function getActiveCourseCountAttribute(): int
    {
        return $this->activeCourseAssignments()->count();
    }

    /**
     * Ottiene il badge di status
     */
    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            self::STATUS_ACTIVE => 'bg-green-100 text-green-800',
            self::STATUS_INACTIVE => 'bg-gray-100 text-gray-800',
            self::STATUS_ON_LEAVE => 'bg-yellow-100 text-yellow-800',
            self::STATUS_TERMINATED => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Ottiene il badge del ruolo
     */
    public function getRoleBadgeAttribute(): string
    {
        return match($this->role) {
            self::ROLE_INSTRUCTOR => 'bg-blue-100 text-blue-800',
            self::ROLE_COORDINATOR => 'bg-purple-100 text-purple-800',
            self::ROLE_ADMIN_ASSISTANT => 'bg-indigo-100 text-indigo-800',
            self::ROLE_RECEPTIONIST => 'bg-pink-100 text-pink-800',
            self::ROLE_CLEANER => 'bg-orange-100 text-orange-800',
            self::ROLE_MAINTENANCE => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    // HELPER METHODS

    /**
     * Ottiene tutti i ruoli disponibili
     */
    public static function getAvailableRoles(): array
    {
        return [
            self::ROLE_INSTRUCTOR => 'Istruttore',
            self::ROLE_COORDINATOR => 'Coordinatore',
            self::ROLE_ADMIN_ASSISTANT => 'Assistente Amministrativo',
            self::ROLE_RECEPTIONIST => 'Receptionist',
            self::ROLE_CLEANER => 'Addetto Pulizie',
            self::ROLE_MAINTENANCE => 'Manutentore',
        ];
    }

    /**
     * Ottiene tutti i dipartimenti disponibili
     */
    public static function getAvailableDepartments(): array
    {
        return [
            self::DEPT_DANCE => 'Danza',
            self::DEPT_ADMINISTRATION => 'Amministrazione',
            self::DEPT_MAINTENANCE => 'Manutenzione',
            self::DEPT_FRONT_DESK => 'Reception',
        ];
    }

    /**
     * Ottiene tutti i tipi di impiego disponibili
     */
    public static function getAvailableEmploymentTypes(): array
    {
        return [
            self::EMPLOYMENT_FULL_TIME => 'Tempo Pieno',
            self::EMPLOYMENT_PART_TIME => 'Tempo Parziale',
            self::EMPLOYMENT_CONTRACT => 'Contratto',
            self::EMPLOYMENT_VOLUNTEER => 'Volontario',
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
            self::STATUS_ON_LEAVE => 'In Congedo',
            self::STATUS_TERMINATED => 'Licenziato',
        ];
    }

    /**
     * Genera un employee ID unico per la scuola
     */
    public static function generateEmployeeId(int $schoolId): string
    {
        $prefix = 'EMP';
        $number = static::where('school_id', $schoolId)->count() + 1;
        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Verifica se può essere assegnato a un corso
     */
    public function canBeAssignedToCourse(Course $course): bool
    {
        // Verifica che sia un istruttore attivo
        if (!$this->is_instructor || $this->status !== self::STATUS_ACTIVE) {
            return false;
        }

        // Verifica che non sia già assegnato come primary instructor
        $existingAssignment = $this->courseAssignments()
            ->where('course_id', $course->id)
            ->where('assignment_type', 'primary_instructor')
            ->where('status', 'active')
            ->exists();

        return !$existingAssignment;
    }

    /**
     * Ottiene le ore settimanali attuali
     */
    public function getCurrentWeeklyHours(): float
    {
        // Calcola le ore basate sui corsi assegnati
        return $this->activeCourseAssignments()
            ->join('courses', 'staff_course_assignments.course_id', '=', 'courses.id')
            ->sum('courses.duration_hours') ?? 0;
    }

    /**
     * Verifica se può accettare più ore
     */
    public function canAcceptMoreHours(float $additionalHours = 0): bool
    {
        if (!$this->max_hours_per_week) {
            return true;
        }

        $currentHours = $this->getCurrentWeeklyHours();
        return ($currentHours + $additionalHours) <= $this->max_hours_per_week;
    }

    /**
     * Ottiene il guadagno stimato settimanale
     */
    public function getEstimatedWeeklyEarnings(): float
    {
        if (!$this->hourly_rate) {
            return 0;
        }

        return $this->getCurrentWeeklyHours() * $this->hourly_rate;
    }
}
