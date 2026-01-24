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
        'storage_quota_gb',
        'storage_used_bytes',
        'storage_cache_updated_at',
        'storage_quota_expires_at',
        'storage_unlimited',
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
            'storage_unlimited' => 'boolean',
            'storage_cache_updated_at' => 'datetime',
            'storage_quota_expires_at' => 'datetime',
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

    /**
     * TASK #11: Ottiene lo storage utilizzato in GB (formattato)
     */
    public function getStorageUsedGbAttribute(): float
    {
        return round($this->storage_used_bytes / (1024 ** 3), 2);
    }

    /**
     * TASK #11: Ottiene la percentuale di storage utilizzato
     */
    public function getStorageUsagePercentageAttribute(): float
    {
        if ($this->storage_unlimited) {
            return 0.0;
        }

        if ($this->storage_quota_gb <= 0) {
            return 100.0;
        }

        $percentage = ($this->storage_used_gb / $this->storage_quota_gb) * 100;
        return round(min($percentage, 100), 2);
    }

    /**
     * TASK #11: Ottiene lo storage rimanente in GB
     */
    public function getStorageRemainingGbAttribute(): float
    {
        if ($this->storage_unlimited) {
            return PHP_FLOAT_MAX;
        }

        $remaining = $this->storage_quota_gb - $this->storage_used_gb;
        return round(max($remaining, 0), 2);
    }

    /**
     * TASK #11: Verifica se la quota storage è scaduta
     */
    public function getIsStorageQuotaExpiredAttribute(): bool
    {
        if (!$this->storage_quota_expires_at) {
            return false;
        }

        return $this->storage_quota_expires_at->isPast();
    }

    /**
     * TASK #11: Ottiene la quota storage in bytes
     */
    public function getStorageQuotaBytesAttribute(): int
    {
        if ($this->storage_unlimited) {
            return PHP_INT_MAX;
        }

        return $this->storage_quota_gb * (1024 ** 3);
    }

    /**
     * TASK #11: Ottiene lo storage rimanente in bytes
     */
    public function getStorageRemainingBytesAttribute(): int
    {
        if ($this->storage_unlimited) {
            return PHP_INT_MAX;
        }

        return max(0, $this->storage_quota_bytes - $this->storage_used_bytes);
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

    // TASK #11: STORAGE QUOTA METHODS

    /**
     * Verifica se la scuola ha spazio storage disponibile
     *
     * @param int $requiredBytes Byte richiesti per upload
     * @return bool
     */
    public function hasStorageAvailable(int $requiredBytes = 0): bool
    {
        // Storage illimitato
        if ($this->storage_unlimited) {
            return true;
        }

        // Quota scaduta - ritorna a base 5GB
        if ($this->is_storage_quota_expired) {
            $effectiveQuotaBytes = 5 * (1024 ** 3); // 5GB in bytes
        } else {
            $effectiveQuotaBytes = $this->storage_quota_gb * (1024 ** 3);
        }

        return ($this->storage_used_bytes + $requiredBytes) <= $effectiveQuotaBytes;
    }

    /**
     * Ottiene lo storage disponibile in bytes
     *
     * @return int
     */
    public function getAvailableStorageBytes(): int
    {
        if ($this->storage_unlimited) {
            return PHP_INT_MAX;
        }

        $effectiveQuotaGb = $this->is_storage_quota_expired ? 5 : $this->storage_quota_gb;
        $quotaBytes = $effectiveQuotaGb * (1024 ** 3);

        return max(0, $quotaBytes - $this->storage_used_bytes);
    }

    /**
     * Aggiorna il totale storage utilizzato ricalcolando dalla media_galleries
     *
     * @return void
     */
    public function recalculateStorageUsed(): void
    {
        $totalBytes = $this->mediaGalleries()
            ->sum('file_size');

        $this->update([
            'storage_used_bytes' => $totalBytes,
            'storage_cache_updated_at' => now(),
        ]);
    }

    /**
     * Verifica se il cache dello storage necessita refresh (TTL 5 minuti)
     *
     * @return bool
     */
    public function shouldRefreshStorageCache(): bool
    {
        if (!$this->storage_cache_updated_at) {
            return true;
        }

        return $this->storage_cache_updated_at->diffInMinutes(now()) >= 5;
    }

    /**
     * Ottiene la quota effettiva in GB (considera scadenza)
     *
     * @return int
     */
    public function getEffectiveQuotaGb(): int
    {
        if ($this->storage_unlimited) {
            return PHP_INT_MAX;
        }

        return $this->is_storage_quota_expired ? 5 : $this->storage_quota_gb;
    }

    /**
     * Verifica se la quota è scaduta (alias per accessor)
     *
     * @return bool
     */
    public function hasExpiredQuota(): bool
    {
        return $this->is_storage_quota_expired;
    }

    /**
     * Verifica se lo storage è pieno (100%)
     *
     * @return bool
     */
    public function isStorageFull(): bool
    {
        if ($this->storage_unlimited) {
            return false;
        }

        return $this->storage_used_bytes >= $this->storage_quota_bytes;
    }

    /**
     * Verifica se lo storage è in warning (>= 80%)
     *
     * @return bool
     */
    public function isStorageWarning(): bool
    {
        if ($this->storage_unlimited) {
            return false;
        }

        return $this->storage_usage_percentage >= 80;
    }
}