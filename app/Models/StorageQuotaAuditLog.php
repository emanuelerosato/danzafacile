<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TASK #11 FASE 7: Storage Quota Audit Log
 *
 * Traccia tutte le modifiche manuali alla quota storage
 * effettuate da Super Admin
 */
class StorageQuotaAuditLog extends Model
{
    use HasFactory;

    protected $table = 'storage_quota_audit_log';

    protected $fillable = [
        'school_id',
        'super_admin_id',
        'action',
        'old_quota_gb',
        'old_unlimited',
        'old_expires_at',
        'new_quota_gb',
        'new_unlimited',
        'new_expires_at',
        'admin_note',
    ];

    protected function casts(): array
    {
        return [
            'old_unlimited' => 'boolean',
            'new_unlimited' => 'boolean',
            'old_expires_at' => 'datetime',
            'new_expires_at' => 'datetime',
        ];
    }

    // RELAZIONI

    /**
     * Scuola a cui si riferisce la modifica
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Super Admin che ha effettuato la modifica
     */
    public function superAdmin(): BelongsTo
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    // ACCESSORS

    /**
     * Ottiene la descrizione human-readable dell'azione
     */
    public function getActionDescriptionAttribute(): string
    {
        return match ($this->action) {
            'add_quota' => "Aggiunti {$this->getQuotaDiff()}GB ({$this->old_quota_gb}GB → {$this->new_quota_gb}GB)",
            'set_unlimited' => "Storage illimitato abilitato",
            'reset_to_base' => "Reset a quota base ({$this->old_quota_gb}GB → {$this->new_quota_gb}GB)",
            default => "Azione sconosciuta: {$this->action}",
        };
    }

    /**
     * Ottiene la differenza in GB tra old e new quota
     */
    public function getQuotaDiff(): int
    {
        return $this->new_quota_gb - $this->old_quota_gb;
    }

    /**
     * Ottiene il badge color per l'azione
     */
    public function getActionBadgeColorAttribute(): string
    {
        return match ($this->action) {
            'add_quota' => 'bg-green-100 text-green-800',
            'set_unlimited' => 'bg-purple-100 text-purple-800',
            'reset_to_base' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }
}
