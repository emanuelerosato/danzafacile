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
            'uploaded_at' => 'datetime',
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
     * Filtra i documenti pubblici (placeholder - nessuna logica di visibilità)
     */
    public function scopePublic(Builder $query): Builder
    {
        // Tutti i documenti sono considerati privati nella struttura attuale
        return $query->whereRaw('1 = 0'); // Restituisce sempre zero documenti
    }

    /**
     * Filtra i documenti privati (placeholder - nessuna logica di visibilità)
     */
    public function scopePrivate(Builder $query): Builder
    {
        // Tutti i documenti sono considerati privati nella struttura attuale
        return $query; // Restituisce tutti i documenti
    }

    /**
     * Filtra i documenti che richiedono approvazione (tutti richiedono approvazione)
     */
    public function scopeRequiringApproval(Builder $query): Builder
    {
        // Tutti i documenti richiedono approvazione nella struttura attuale
        return $query; // Restituisce tutti i documenti
    }

    /**
     * Filtra i documenti scaduti (placeholder - nessuna logica di scadenza)
     */
    public function scopeExpired(Builder $query): Builder
    {
        // Nessun documento ha scadenza nella struttura attuale
        return $query->whereRaw('1 = 0'); // Restituisce sempre zero documenti
    }

    /**
     * Filtra i documenti non scaduti (placeholder - nessuna logica di scadenza)
     */
    public function scopeNotExpired(Builder $query): Builder
    {
        // Tutti i documenti sono considerati non scaduti
        return $query; // Restituisce tutti i documenti
    }

    /**
     * Filtra i documenti per scuola
     */
    public function scopeBySchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Filtra i documenti per tipo di file (usa file_type invece di mime_type)
     */
    public function scopeByFileType(Builder $query, string $fileType): Builder
    {
        return $query->where('file_type', $fileType);
    }

    /**
     * Filtra solo i documenti immagine (usa file_type)
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->whereIn('file_type', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Filtra solo i documenti PDF (usa file_type)
     */
    public function scopePdfs(Builder $query): Builder
    {
        return $query->where('file_type', 'pdf');
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
     * Verifica se il documento è un'immagine (usa file_type)
     */
    public function getIsImageAttribute(): bool
    {
        return in_array(strtolower($this->file_type ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Verifica se il documento è un PDF (usa file_type)
     */
    public function getIsPdfAttribute(): bool
    {
        return strtolower($this->file_type ?? '') === 'pdf';
    }

    /**
     * Verifica se il documento è scaduto (sempre false - nessuna logica di scadenza)
     */
    public function getIsExpiredAttribute(): bool
    {
        return false; // Nessun documento ha scadenza nella struttura attuale
    }

    /**
     * Verifica se il documento richiede approvazione (sempre true)
     */
    public function getRequiresApprovalAttribute(): bool
    {
        return true; // Tutti i documenti richiedono approvazione nella struttura attuale
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
     * Ottiene l'icona basata sul tipo di file
     */
    public function getFileIconAttribute(): string
    {
        if ($this->is_image) {
            return 'fas fa-image';
        }

        if ($this->is_pdf) {
            return 'fas fa-file-pdf';
        }

        return match(strtolower($this->file_type ?? '')) {
            'doc', 'docx' => 'fas fa-file-word',
            'xls', 'xlsx' => 'fas fa-file-excel',
            'ppt', 'pptx' => 'fas fa-file-powerpoint',
            'zip', 'rar', '7z' => 'fas fa-file-archive',
            'txt' => 'fas fa-file-alt',
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