<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'short_description',
        'type',
        'start_date',
        'end_date',
        'location',
        'max_participants',
        'price',
        'price_students',
        'price_guests',
        'requires_payment',
        'payment_method',
        'slug',
        'landing_description',
        'landing_cta_text',
        'qr_checkin_enabled',
        'requires_registration',
        'registration_deadline',
        'requirements',
        'image_path',
        'external_link',
        'social_link',
        'active',
        'is_public',
        'additional_info'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'requirements' => 'array',
        'additional_info' => 'array',
        'price_students' => 'decimal:2',
        'price_guests' => 'decimal:2',
        'requires_registration' => 'boolean',
        'requires_payment' => 'boolean',
        'qr_checkin_enabled' => 'boolean',
        'active' => 'boolean',
        'is_public' => 'boolean'
    ];

    // Automatically filter by school for non-super-admin users
    protected static function booted(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            $user = Auth::user();
            if ($user && !$user->isSuperAdmin() && $user->school_id) {
                $builder->where('school_id', $user->school_id);
            }
        });

        // Automatically set school_id when creating
        static::creating(function (Event $event) {
            $user = Auth::user();
            if ($user && !$user->isSuperAdmin() && $user->school_id && !$event->school_id) {
                $event->school_id = $user->school_id;
            }

            // Auto-generate slug if not provided
            if (empty($event->slug) && !empty($event->name)) {
                $event->slug = static::generateUniqueSlug($event->name);
            }
        });

        // Update slug when name changes
        static::updating(function (Event $event) {
            if ($event->isDirty('name') && empty($event->slug)) {
                $event->slug = static::generateUniqueSlug($event->name, $event->id);
            }
        });
    }

    // Relationships
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function attendance()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'event_id');
    }

    public function eventPayments()
    {
        return $this->hasMany(\App\Models\EventPayment::class);
    }

    public function attendanceRecords()
    {
        return $this->hasMany(Attendance::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('start_date', '>', now());
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    // Accessors & Mutators
    public function getIsUpcomingAttribute(): bool
    {
        return $this->start_date > now();
    }

    public function getIsOngoingAttribute(): bool
    {
        return $this->start_date <= now() && $this->end_date >= now();
    }

    public function getCurrentRegistrationsCountAttribute(): int
    {
        return $this->registrations()
            ->whereIn('status', ['registered', 'confirmed'])
            ->count();
    }

    public function getAvailableSpotsAttribute(): ?int
    {
        if (!$this->max_participants) {
            return null;
        }
        return max(0, $this->max_participants - $this->current_registrations_count);
    }

    public function getIsFullAttribute(): bool
    {
        if (!$this->max_participants) {
            return false;
        }
        return $this->current_registrations_count >= $this->max_participants;
    }

    public function getRegistrationStatusAttribute(): string
    {
        if (!$this->requires_registration) {
            return 'not_required';
        }

        if ($this->registration_deadline && $this->registration_deadline < now()) {
            return 'closed';
        }

        if ($this->is_full) {
            return 'full';
        }

        return 'open';
    }

    // PRICING METHODS

    /**
     * Ottiene il prezzo per un utente specifico
     * Ritorna price_students se l'utente è uno studente della scuola, altrimenti price_guests
     *
     * @param User|null $user
     * @return float
     */
    public function getPriceForUser(?User $user = null): float
    {
        // Se non c'è dual pricing, usa il prezzo standard
        if ($this->price_students === null && $this->price_guests === null) {
            return (float) ($this->price ?? 0);
        }

        // Se l'utente non è fornito o è guest, usa il prezzo guest
        if (!$user || $user->isGuest()) {
            return (float) ($this->price_guests ?? $this->price ?? 0);
        }

        // Se l'utente è uno studente della stessa scuola, usa il prezzo studenti
        if ($user->school_id === $this->school_id) {
            return (float) ($this->price_students ?? $this->price ?? 0);
        }

        // Altrimenti usa il prezzo guest
        return (float) ($this->price_guests ?? $this->price ?? 0);
    }

    /**
     * Verifica se l'evento richiede un pagamento
     */
    public function requiresPayment(): bool
    {
        return (bool) $this->requires_payment && $this->getPriceForUser() > 0;
    }

    /**
     * Verifica se l'evento ha prezzi differenziati
     */
    public function hasGuestPricing(): bool
    {
        return $this->price_students !== null && $this->price_guests !== null;
    }

    /**
     * Ottiene il prezzo formattato per un utente
     *
     * @param User|null $user
     * @return string
     */
    public function getFormattedPrice(?User $user = null): string
    {
        $price = $this->getPriceForUser($user);
        return '€' . number_format($price, 2, ',', '.');
    }

    /**
     * Verifica se l'evento è pubblico (accessibile ai guest)
     */
    public function isPublic(): bool
    {
        return (bool) $this->is_public;
    }

    /**
     * Ottiene l'URL della landing page pubblica
     */
    public function getLandingUrl(): string
    {
        return route('events.public.show', $this->slug);
    }

    /**
     * Ottiene la valuta (default EUR)
     */
    public function getCurrencyAttribute(): string
    {
        return 'EUR';
    }

    /**
     * Ottiene l'URL dell'immagine dell'evento
     * Usa image_path se presente, altrimenti custom_image_url da additional_info
     */
    public function getImageUrlAttribute(): ?string
    {
        // Se c'è image_path (upload locale), usa quello
        if ($this->image_path) {
            return asset('storage/' . $this->image_path);
        }

        // Altrimenti usa custom_image_url da additional_info
        $customization = $this->additional_info['landing_customization'] ?? [];
        return $customization['custom_image_url'] ?? null;
    }

    // SLUG MANAGEMENT

    /**
     * Genera uno slug univoco per l'evento
     *
     * @param string $name
     * @param int|null $ignoreId ID da ignorare per update
     * @return string
     */
    protected static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::slugExists($slug, $ignoreId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Verifica se uno slug esiste già
     *
     * @param string $slug
     * @param int|null $ignoreId
     * @return bool
     */
    protected static function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = static::withoutGlobalScope('school')->where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }
}