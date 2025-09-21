<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'address',
        'city',
        'postal_code',
        'phone',
        'email',
        'website',
        'logo_path',
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
            'active' => 'boolean',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene tutti gli utenti associati alla scuola
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Ottiene tutti i corsi offerti dalla scuola
     */
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    /**
     * Ottiene tutti i pagamenti ricevuti dalla scuola
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Ottiene tutte le sale della scuola
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(SchoolRoom::class);
    }

    /**
     * Ottiene tutti i documenti della scuola
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Ottiene tutte le gallerie media della scuola
     */
    public function mediaGalleries(): HasMany
    {
        return $this->hasMany(MediaGallery::class);
    }

    /**
     * Ottiene tutti gli eventi organizzati dalla scuola
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Ottiene tutti i record di presenza della scuola
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Ottiene tutti gli amministratori della scuola
     */
    public function admins(): HasMany
    {
        return $this->users()->where('role', User::ROLE_ADMIN);
    }

    /**
     * Ottiene tutti gli studenti della scuola
     */
    public function students(): HasMany
    {
        return $this->users()->where('role', User::ROLE_STUDENT);
    }

    /**
     * Ottiene tutti gli istruttori della scuola
     */
    public function instructors(): HasMany
    {
        return $this->users()->where('role', User::ROLE_INSTRUCTOR);
    }

    /**
     * Ottiene tutte le iscrizioni ai corsi della scuola
     */
    public function courseEnrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    // SCOPES

    /**
     * Filtra solo le scuole attive
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    // ACCESSORS

    /**
     * Ottiene l'URL completo del logo della scuola
     */
    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }

        return asset('storage/' . $this->logo_path);
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
     * Imposta l'email in minuscolo
     */
    public function setEmailAttribute($value): void
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
    }

    // HELPER METHODS

    /**
     * Ottiene il numero totale di studenti iscritti
     */
    public function getTotalStudentsCount(): int
    {
        return $this->students()->count();
    }

    /**
     * Ottiene il numero totale di corsi attivi
     */
    public function getActiveCoursesCount(): int
    {
        return $this->courses()->active()->count();
    }

    /**
     * Ottiene il fatturato totale della scuola
     */
    public function getTotalRevenue(): float
    {
        return $this->payments()->completed()->sum('amount');
    }
}