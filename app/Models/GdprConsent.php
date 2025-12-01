<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class GdprConsent extends Model
{
    use HasFactory;

    /**
     * Indica che il model non usa i timestamp standard di Laravel.
     * Usiamo consented_at e revoked_at invece di created_at e updated_at.
     */
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'event_registration_id',
        'consent_type',
        'consented',
        'ip_address',
        'user_agent',
        'consented_at',
        'revoked_at',
        'policy_version',
    ];

    protected $casts = [
        'consented' => 'boolean',
        'consented_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    // RELATIONSHIPS

    /**
     * Ottiene l'utente associato al consenso
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ottiene la registrazione evento associata (opzionale)
     */
    public function eventRegistration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class);
    }

    // CONSENT MANAGEMENT

    /**
     * Revoca il consenso
     *
     * @return void
     */
    public function revoke(): void
    {
        $this->update([
            'consented' => false,
            'revoked_at' => now(),
        ]);

        \Log::info('GDPR consent revoked', [
            'consent_id' => $this->id,
            'user_id' => $this->user_id,
            'consent_type' => $this->consent_type,
        ]);
    }

    /**
     * Verifica se il consenso è stato revocato
     */
    public function isRevoked(): bool
    {
        return !$this->consented || $this->revoked_at !== null;
    }

    /**
     * Verifica se il consenso è attivo
     */
    public function isActive(): bool
    {
        return $this->consented && $this->revoked_at === null;
    }

    // SCOPES

    /**
     * Filtra i consensi per utente
     *
     * @param Builder $query
     * @param int $userId
     * @return Builder
     */
    public function scopeForUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtra i consensi per tipo
     *
     * @param Builder $query
     * @param string $type
     * @return Builder
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('consent_type', $type);
    }

    /**
     * Filtra solo i consensi attivi
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('consented', true)
                    ->whereNull('revoked_at');
    }

    /**
     * Filtra solo i consensi revocati
     */
    public function scopeRevoked(Builder $query): Builder
    {
        return $query->where('consented', false)
                    ->orWhereNotNull('revoked_at');
    }

    // STATIC FACTORY

    /**
     * Registra un nuovo consenso GDPR
     *
     * @param int $userId ID dell'utente
     * @param string $consentType Tipo di consenso (privacy, marketing, cookies, terms, newsletter)
     * @param bool $consented Se l'utente ha dato il consenso
     * @param int|null $eventRegistrationId ID registrazione evento (opzionale)
     * @param string|null $policyVersion Versione della policy
     * @return self
     */
    public static function record(
        int $userId,
        string $consentType,
        bool $consented,
        ?int $eventRegistrationId = null,
        ?string $policyVersion = null
    ): self {
        // Ottiene IP e User Agent dalla richiesta corrente
        $ipAddress = request()->ip();
        $userAgent = request()->userAgent();

        // Usa la versione della policy dal config o default a '1.0'
        $policyVersion = $policyVersion ?? config('app.privacy_policy_version', '1.0');

        $consent = static::create([
            'user_id' => $userId,
            'event_registration_id' => $eventRegistrationId,
            'consent_type' => $consentType,
            'consented' => $consented,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'consented_at' => now(),
            'revoked_at' => null,
            'policy_version' => $policyVersion,
        ]);

        \Log::info('GDPR consent recorded', [
            'consent_id' => $consent->id,
            'user_id' => $userId,
            'consent_type' => $consentType,
            'consented' => $consented,
            'policy_version' => $policyVersion,
        ]);

        return $consent;
    }
}
