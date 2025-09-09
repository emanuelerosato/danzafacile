<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

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
        'date_of_birth',
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
        $allowedRoles = [self::ROLE_SUPER_ADMIN, self::ROLE_ADMIN, 'user'];
        $this->attributes['role'] = in_array($value, $allowedRoles) ? $value : 'user';
    }

    // HELPER METHODS

    /**
     * Verifica se l'utente è super amministratore
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    /**
     * Verifica se l'utente è amministratore
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Verifica se l'utente è istruttore (not implemented - no instructor role in DB)
     */
    public function isInstructor(): bool
    {
        return false; // No instructor role in current DB schema
    }

    /**
     * Verifica se l'utente è studente (user)
     */
    public function isStudent(): bool
    {
        return $this->role === 'user';
    }
}
