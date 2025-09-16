<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
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
     * Enum per le categorie dei documenti
     */
    const CATEGORY_GENERAL = 'general';
    const CATEGORY_MEDICAL = 'medical';
    const CATEGORY_CONTRACT = 'contract';
    const CATEGORY_IDENTIFICATION = 'identification';
    const CATEGORY_OTHER = 'other';

    /**
     * Enum per lo status del documento
     */
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'user_id',
        'course_id',
        'name',
        'file_path',
        'file_type',
        'file_size',
        'category',
        'status',
        'uploaded_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_public' => 'boolean',
            'requires_approval' => 'boolean',
            'metadata' => 'array',
            'approved_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene l'utente che ha caricato il documento
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Ottiene l'utente che ha approvato il documento
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Ottiene l'utente che ha caricato il documento (alias di uploadedBy per compatibilità)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Ottiene la scuola a cui appartiene il documento
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }


    // SCOPES

    /**
     * Filtra i documenti per categoria
     */
    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * Filtra i documenti per status
     */
    public function scopeByStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Filtra solo i documenti approvati
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Filtra solo i documenti in attesa
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Filtra solo i documenti rifiutati
     */
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }


    /**
     * Filtra i documenti per utente che li ha caricati
     */
    public function scopeByUploader(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtra i documenti pubblici
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Filtra i documenti privati
     */
    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('is_public', false);
    }

    /**
     * Filtra i documenti che richiedono approvazione
     */
    public function scopeRequiringApproval(Builder $query): Builder
    {
        return $query->where('requires_approval', true);
    }

    /**
     * Filtra i documenti scaduti
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('expires_at', '<', now());
    }

    /**
     * Filtra i documenti non scaduti
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        return $query->where(function($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>=', now());
        });
    }

    /**
     * Filtra i documenti per scuola
     */
    public function scopeBySchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Filtra i documenti per MIME type
     */
    public function scopeByMimeType(Builder $query, string $mimeType): Builder
    {
        return $query->where('mime_type', $mimeType);
    }

    /**
     * Filtra solo i documenti immagine
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Filtra solo i documenti PDF
     */
    public function scopePdfs(Builder $query): Builder
    {
        return $query->where('mime_type', 'application/pdf');
    }

    /**
     * Ordina per data di creazione più recente
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderBy('created_at', 'desc');
    }

    // ACCESSORS

    /**
     * Ottiene l'URL completo del documento
     */
    public function getFileUrlAttribute(): ?string
    {
        if (!$this->file_path) {
            return null;
        }

        return asset('storage/' . $this->file_path);
    }

    /**
     * Ottiene la dimensione del file formattata
     */
    public function getFormattedSizeAttribute(): string
    {
        if (!$this->file_size) {
            return '0 B';
        }

        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Verifica se il documento è un'immagine
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /**
     * Verifica se il documento è un PDF
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Verifica se il documento è scaduto
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Verifica se il documento richiede approvazione
     */
    public function getRequiresApprovalAttribute(): bool
    {
        return (bool) $this->attributes['requires_approval'];
    }

    /**
     * Verifica se il documento è approvato
     */
    public function getIsApprovedAttribute(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    /**
     * Verifica se il documento è in attesa
     */
    public function getIsPendingAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Ottiene l'icona basata sul MIME type
     */
    public function getFileIconAttribute(): string
    {
        if ($this->is_image) {
            return 'fas fa-image';
        }

        if ($this->is_pdf) {
            return 'fas fa-file-pdf';
        }

        return match($this->mime_type) {
            'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fas fa-file-word',
            'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fas fa-file-excel',
            'application/vnd.ms-powerpoint', 'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'fas fa-file-powerpoint',
            'application/zip', 'application/x-rar-compressed', 'application/x-7z-compressed' => 'fas fa-file-archive',
            'text/plain' => 'fas fa-file-alt',
            default => 'fas fa-file'
        };
    }

    /**
     * Ottiene il nome della categoria localizzato
     */
    public function getCategoryNameAttribute(): string
    {
        $categories = self::getAvailableCategories();
        return $categories[$this->category] ?? 'Generale';
    }

    /**
     * Ottiene il nome dello status localizzato
     */
    public function getStatusNameAttribute(): string
    {
        $statuses = self::getAvailableStatuses();
        return $statuses[$this->status] ?? 'In Attesa';
    }

    /**
     * Ottiene la classe CSS per lo status
     */
    public function getStatusClassAttribute(): string
    {
        return match($this->status) {
            self::STATUS_APPROVED => 'bg-green-100 text-green-800',
            self::STATUS_REJECTED => 'bg-red-100 text-red-800',
            default => 'bg-yellow-100 text-yellow-800'
        };
    }

    // MUTATORS


    // HELPER METHODS

    /**
     * Ottiene tutte le categorie disponibili
     */
    public static function getAvailableCategories(): array
    {
        return [
            self::CATEGORY_GENERAL => 'Generale',
            self::CATEGORY_MEDICAL => 'Certificato Medico',
            self::CATEGORY_CONTRACT => 'Contratto/Accordo',
            self::CATEGORY_IDENTIFICATION => 'Documento di Identità',
            self::CATEGORY_OTHER => 'Altro',
        ];
    }

    /**
     * Ottiene tutti gli status disponibili
     */
    public static function getAvailableStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'In Attesa',
            self::STATUS_APPROVED => 'Approvato',
            self::STATUS_REJECTED => 'Rifiutato',
        ];
    }

    /**
     * Approva il documento
     */
    public function approve(?User $approver = null): bool
    {
        $this->status = self::STATUS_APPROVED;
        $this->uploaded_at = now();
        return $this->save();
    }

    /**
     * Rifiuta il documento
     */
    public function reject(?string $reason = null, ?User $rejector = null): bool
    {
        $this->status = self::STATUS_REJECTED;
        return $this->save();
    }


    /**
     * Verifica se il documento può essere eliminato
     */
    public function canBeDeleted(): bool
    {
        return auth()->user()?->can('delete', $this) ?? false;
    }

    /**
     * Verifica se il documento può essere scaricato
     */
    public function canBeDownloaded(): bool
    {
        return auth()->user()?->can('view', $this) ?? false;
    }

    /**
     * Ottiene il percorso completo del file
     */
    public function getFullFilePathAttribute(): string
    {
        return storage_path('app/' . $this->file_path);
    }

    /**
     * Verifica se il file esiste fisicamente
     */
    public function fileExists(): bool
    {
        return file_exists($this->full_file_path);
    }
}