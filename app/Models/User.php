<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Global scope per multi-tenant security - TEMPORANEAMENTE DISABILITATO
        // static::addGlobalScope('school', function (Builder $builder) {
        //     if (auth()->check() && auth()->user()->school_id && auth()->user()->role !== self::ROLE_SUPER_ADMIN) {
        //         $builder->where('school_id', auth()->user()->school_id);
        //     }
        // });
    }

    /**
     * Enum per i ruoli utente
     */
    const ROLE_SUPER_ADMIN = 'super_admin';
    const ROLE_ADMIN = 'admin';
    const ROLE_INSTRUCTOR = 'instructor';
    const ROLE_STUDENT = 'student';

    /**
     * SECURITY: Mass Assignment Protection
     *
     * Using $guarded instead of $fillable for stronger protection.
     * Sensitive fields are explicitly blocked from mass assignment.
     * Use safe methods like assignRole(), activateUser() instead.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',                    // Never allow mass assignment of ID
        'role',                  // Use assignRole() method instead
        'email_verified_at',     // Use markEmailAsVerified() instead
        'remember_token',        // Laravel internal field
    ];

    /**
     * DEPRECATED: Fillable array replaced by $guarded for better security
     * Keep commented for reference during migration
     *
     * protected $fillable = [
     *     'name', 'email', 'password', 'school_id', 'role', 'first_name',
     *     'last_name', 'phone', 'codice_fiscale', 'date_of_birth', 'address',
     *     'emergency_contact', 'medical_notes', 'profile_image_path', 'active',
     * ];
     */

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
            'is_guest' => 'boolean',
            'guest_token_expires_at' => 'datetime',
            'is_archived' => 'boolean',
            'archived_at' => 'datetime',
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
     * Ottiene il record staff dell'utente (nuova struttura)
     */
    public function staff(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Staff::class);
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

    /**
     * Ottiene tutti i pagamenti eventi dell'utente
     */
    public function eventPayments(): HasMany
    {
        return $this->hasMany(\App\Models\EventPayment::class);
    }

    /**
     * Ottiene tutti i consensi GDPR dell'utente
     */
    public function gdprConsents(): HasMany
    {
        return $this->hasMany(\App\Models\GdprConsent::class);
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

    /**
     * Filtra solo gli utenti guest
     */
    public function scopeGuests(Builder $query): Builder
    {
        return $query->where('is_guest', true);
    }

    /**
     * Filtra gli utenti guest con token scaduto
     */
    public function scopeExpiredGuests(Builder $query): Builder
    {
        return $query->where('is_guest', true)
                    ->where('guest_token_expires_at', '<', now());
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

    // SECURITY: SAFE METHODS FOR SENSITIVE FIELDS

    /**
     * Safely assign role to user (prevents mass assignment privilege escalation)
     *
     * SECURITY: Only use this method to change user roles.
     * Never allow role to be set via request->all() or mass assignment.
     *
     * @param string $role Role to assign (super_admin, admin, user)
     * @param User|null $authorizedBy User authorizing this change (for audit)
     * @return bool True if role was changed
     */
    public function assignRole(string $role, ?User $authorizedBy = null): bool
    {
        $allowedRoles = ['super_admin', 'admin', 'user'];

        if (!in_array($role, $allowedRoles)) {
            \Log::warning('Attempted to assign invalid role', [
                'user_id' => $this->id,
                'invalid_role' => $role,
                'authorized_by' => $authorizedBy?->id
            ]);
            return false;
        }

        // Only super_admin can assign super_admin role
        if ($role === 'super_admin' && !$authorizedBy?->isSuperAdmin()) {
            \Log::critical('Unauthorized super_admin role assignment attempt', [
                'user_id' => $this->id,
                'attempted_by' => $authorizedBy?->id
            ]);
            return false;
        }

        $oldRole = $this->role;
        $this->role = $role;
        $this->save();

        \Log::info('User role changed', [
            'user_id' => $this->id,
            'old_role' => $oldRole,
            'new_role' => $role,
            'authorized_by' => $authorizedBy?->id
        ]);

        return true;
    }

    /**
     * Safely activate/deactivate user account
     *
     * @param bool $active
     * @param User|null $authorizedBy
     * @return bool
     */
    public function setActiveStatus(bool $active, ?User $authorizedBy = null): bool
    {
        $this->active = $active;
        $this->save();

        \Log::info('User active status changed', [
            'user_id' => $this->id,
            'active' => $active,
            'authorized_by' => $authorizedBy?->id
        ]);

        return true;
    }

    /**
     * Mark email as verified (prevents mass assignment bypass)
     *
     * @return bool
     */
    public function markEmailAsVerified(): bool
    {
        if ($this->email_verified_at !== null) {
            return false; // Already verified
        }

        $this->email_verified_at = now();
        $this->save();

        return true;
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
        return $this->role === 'student' || $this->role === 'user';
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

    // GUEST USER METHODS

    /**
     * Verifica se l'utente è un guest
     */
    public function isGuest(): bool
    {
        return (bool) $this->is_guest;
    }

    /**
     * Genera un token per l'autenticazione guest (magic link)
     *
     * @param int $expirationDays Giorni di validità del token
     * @return string Il token generato
     */
    public function generateGuestToken(int $expirationDays = 180): string
    {
        $this->guest_token = Str::random(64);
        $this->guest_token_expires_at = now()->addDays($expirationDays);
        $this->save();

        \Log::info('Guest token generated', [
            'user_id' => $this->id,
            'expires_at' => $this->guest_token_expires_at,
        ]);

        return $this->guest_token;
    }

    /**
     * Verifica se l'utente guest ha un token valido
     */
    public function hasValidGuestToken(): bool
    {
        return $this->is_guest
            && !empty($this->guest_token)
            && $this->guest_token_expires_at
            && $this->guest_token_expires_at->isFuture();
    }

    /**
     * Ottiene il link di login magico per l'utente guest
     */
    public function getMagicLoginLink(): string
    {
        if (!$this->hasValidGuestToken()) {
            $this->generateGuestToken();
        }

        return route('guest.login', ['token' => $this->guest_token]);
    }

    /**
     * Archivia un utente guest (per GDPR compliance)
     *
     * @param string $reason Motivo dell'archiviazione
     * @return void
     */
    public function archiveGuest(string $reason = 'auto_cleanup'): void
    {
        if (!$this->is_guest) {
            \Log::warning('Attempted to archive non-guest user', [
                'user_id' => $this->id,
            ]);
            return;
        }

        $this->is_archived = true;
        $this->archived_at = now();
        $this->archive_reason = $reason;
        $this->guest_token = null; // Invalida il token
        $this->save();

        \Log::info('Guest user archived', [
            'user_id' => $this->id,
            'reason' => $reason,
        ]);
    }
}
