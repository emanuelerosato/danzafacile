<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    /**
     * Enum per le categorie dei documenti
     */
    const CATEGORY_MEDICAL = 'certificato_medico';
    const CATEGORY_IDENTITY = 'documento_identita';
    const CATEGORY_ENROLLMENT = 'iscrizione';
    const CATEGORY_INSURANCE = 'assicurazione';
    const CATEGORY_PAYMENT = 'ricevuta_pagamento';
    const CATEGORY_AUTHORIZATION = 'autorizzazione';
    const CATEGORY_PHOTO = 'autorizzazione_foto';
    const CATEGORY_OTHER = 'altro';

    /**
     * Enum per lo status del documento
     */
    const STATUS_PENDING = 'in_attesa';
    const STATUS_APPROVED = 'approvato';
    const STATUS_REJECTED = 'rifiutato';
    const STATUS_EXPIRED = 'scaduto';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'school_id',
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
     * Ottiene l'utente proprietario del documento
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Ottiene la scuola a cui appartiene il documento
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Ottiene il corso a cui è associato il documento (opzionale)
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
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
     * Filtra solo i documenti scaduti
     */
    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_EXPIRED);
    }

    /**
     * Filtra i documenti per utente
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtra i documenti per scuola
     */
    public function scopeBySchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Filtra i documenti per corso
     */
    public function scopeByCourse(Builder $query, int $courseId): Builder
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Filtra i documenti per tipo di file
     */
    public function scopeByFileType(Builder $query, string $fileType): Builder
    {
        return $query->where('file_type', $fileType);
    }

    /**
     * Filtra solo i documenti immagine
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->whereIn('file_type', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Filtra solo i documenti PDF
     */
    public function scopePdfs(Builder $query): Builder
    {
        return $query->where('file_type', 'pdf');
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
        return in_array($this->file_type, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Verifica se il documento è un PDF
     */
    public function getIsPdfAttribute(): bool
    {
        return $this->file_type === 'pdf';
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

        return match($this->file_type) {
            'doc', 'docx' => 'fas fa-file-word',
            'xls', 'xlsx' => 'fas fa-file-excel',
            'ppt', 'pptx' => 'fas fa-file-powerpoint',
            'zip', 'rar', '7z' => 'fas fa-file-archive',
            default => 'fas fa-file'
        };
    }

    // MUTATORS

    /**
     * Imposta la categoria con validazione
     */
    public function setCategoryAttribute($value): void
    {
        $allowedCategories = [
            self::CATEGORY_MEDICAL,
            self::CATEGORY_IDENTITY,
            self::CATEGORY_ENROLLMENT,
            self::CATEGORY_INSURANCE,
            self::CATEGORY_PAYMENT,
            self::CATEGORY_AUTHORIZATION,
            self::CATEGORY_PHOTO,
            self::CATEGORY_OTHER
        ];
        
        $this->attributes['category'] = in_array($value, $allowedCategories) ? $value : self::CATEGORY_OTHER;
    }

    /**
     * Imposta lo status con validazione
     */
    public function setStatusAttribute($value): void
    {
        $allowedStatuses = [
            self::STATUS_PENDING,
            self::STATUS_APPROVED,
            self::STATUS_REJECTED,
            self::STATUS_EXPIRED
        ];
        
        $this->attributes['status'] = in_array($value, $allowedStatuses) ? $value : self::STATUS_PENDING;
    }

    /**
     * Imposta il tipo di file in minuscolo
     */
    public function setFileTypeAttribute($value): void
    {
        $this->attributes['file_type'] = $value ? strtolower($value) : null;
    }

    // HELPER METHODS

    /**
     * Ottiene tutte le categorie disponibili
     */
    public static function getAvailableCategories(): array
    {
        return [
            self::CATEGORY_MEDICAL => 'Certificato Medico',
            self::CATEGORY_IDENTITY => 'Documento d\'Identità',
            self::CATEGORY_ENROLLMENT => 'Iscrizione',
            self::CATEGORY_INSURANCE => 'Assicurazione',
            self::CATEGORY_PAYMENT => 'Ricevuta Pagamento',
            self::CATEGORY_AUTHORIZATION => 'Autorizzazione',
            self::CATEGORY_PHOTO => 'Autorizzazione Foto',
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
            self::STATUS_EXPIRED => 'Scaduto',
        ];
    }

    /**
     * Approva il documento
     */
    public function approve(): bool
    {
        $this->status = self::STATUS_APPROVED;
        return $this->save();
    }

    /**
     * Rifiuta il documento
     */
    public function reject(): bool
    {
        $this->status = self::STATUS_REJECTED;
        return $this->save();
    }

    /**
     * Marca il documento come scaduto
     */
    public function markAsExpired(): bool
    {
        $this->status = self::STATUS_EXPIRED;
        return $this->save();
    }

    /**
     * Verifica se il documento può essere cancellato
     */
    public function canBeDeleted(): bool
    {
        return $this->status !== self::STATUS_APPROVED;
    }

    /**
     * Verifica se il documento richiede approvazione
     */
    public function requiresApproval(): bool
    {
        return in_array($this->category, [
            self::CATEGORY_MEDICAL,
            self::CATEGORY_IDENTITY,
            self::CATEGORY_INSURANCE
        ]);
    }
}