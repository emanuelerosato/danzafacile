<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * Enum per i ruoli utente
     */
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_INSTRUCTOR = 'instructor';
    const ROLE_STUDENT = 'student';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'school_id',
        'role',
        'first_name',
        'last_name',
        'phone',
        'codice_fiscale',
        'date_of_birth',
        'address',
        'emergency_contact',
        'medical_notes',
        'profile_image_path',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'date_of_birth' => 'date',
            'active' => 'boolean',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene la scuola di appartenenza dell'utente
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Ottiene tutte le iscrizioni ai corsi dell'utente
     */
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    /**
     * Alias per courseEnrollments (for API consistency)
     */
    public function enrollments(): HasMany
    {
        return $this->courseEnrollments();
    }

    /**
     * Ottiene tutti i pagamenti dell'utente
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Ottiene tutti i documenti dell'utente
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Ottiene tutti i media items caricati dall'utente
     */
    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class);
    }

    /**
     * Ottiene tutti i ruoli staff dell'utente
     */
    public function staffRoles(): HasMany
    {
        return $this->hasMany(StaffRole::class);
    }

    /**
     * Ottiene le presenze dell'utente
     */
    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Ottiene tutte le registrazioni agli eventi dell'utente
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Ottiene tutti i record di presenza dell'utente
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Ottiene i record di presenza segnati da questo utente (se è un Admin/Instructor)
     */
    public function markedAttendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class, 'marked_by_user_id');
    }

    // SCOPES

    /**
     * Filtra gli utenti per ruolo
     */
    public function scopeByRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    /**
     * Filtra solo gli utenti attivi
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * Filtra solo gli amministratori
     */
    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_ADMIN);
    }

    /**
     * Filtra solo gli utenti non amministratori
     */
    public function scopeUsers(Builder $query): Builder
    {
        return $query->where('role', '!=', self::ROLE_ADMIN);
    }

    /**
     * Filtra solo gli istruttori
     */
    public function scopeInstructors(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_INSTRUCTOR);
    }

    /**
     * Filtra solo gli studenti
     */
    public function scopeStudents(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_STUDENT);
    }

    // ACCESSORS

    /**
     * Ottiene il nome completo dell'utente
     */
    public function getFullNameAttribute(): string
    {
        if ($this->first_name && $this->last_name) {
            return $this->first_name . ' ' . $this->last_name;
        }
        
        return $this->name ?? '';
    }

    /**
     * Ottiene l'URL completo dell'immagine del profilo
     */
    public function getProfileImageUrlAttribute(): ?string
    {
        if (!$this->profile_image_path) {
            return null;
        }

        return asset('storage/' . $this->profile_image_path);
    }

    // MUTATORS

    /**
     * Imposta il telefono formattato
     */
    public function setPhoneAttribute($value): void
    {
        // Rimuove tutti i caratteri non numerici tranne il +
        $this->attributes['phone'] = $value ? preg_replace('/[^\d+]/', '', $value) : null;
    }

    /**
     * Imposta il ruolo con validazione
     */
    public function setRoleAttribute($value): void
    {
        $allowedRoles = ['super_admin', 'admin', 'user']; // Match database enum
        $this->attributes['role'] = in_array($value, $allowedRoles) ? $value : 'user';
    }

    // HELPER METHODS

    /**
     * Verifica se l'utente è super amministratore
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Verifica se l'utente è amministratore
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Verifica se l'utente è istruttore (admin role in database)
     */
    public function isInstructor(): bool
    {
        return $this->role === 'admin'; // Instructor is admin role in current schema
    }

    /**
     * Verifica se l'utente è studente (user role in database)
     */
    public function isStudent(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Verifica se l'utente può amministrare (Super Admin o Admin)
     */
    public function canAdministrate(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /**
     * Ottiene tutti i ruoli disponibili
     */
    public static function getAllRoles(): array
    {
        return ['super_admin', 'admin', 'user'];
    }

    /**
     * Ottiene il nome leggibile del ruolo
     */
    public function getRoleNameAttribute(): string
    {
        return match($this->role) {
            'super_admin' => 'Super Administrator',
            'admin' => 'Administrator',
            'user' => 'Student',
            default => 'Student'
        };
    }
}
