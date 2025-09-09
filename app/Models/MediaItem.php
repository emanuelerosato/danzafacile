<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MediaItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'gallery_id',
        'user_id',
        'file_path',
        'file_type',
        'file_size',
        'title',
        'description',
        'order',
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
            'order' => 'integer',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene la galleria a cui appartiene il media item
     */
    public function mediaGallery(): BelongsTo
    {
        return $this->belongsTo(MediaGallery::class, 'gallery_id');
    }

    /**
     * Ottiene l'utente che ha caricato il media item
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // SCOPES

    /**
     * Ordina i media items per l'ordine specificato
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order')->orderBy('created_at');
    }

    /**
     * Filtra solo le immagini
     */
    public function scopeImages(Builder $query): Builder
    {
        return $query->where('file_type', 'LIKE', 'image/%');
    }

    /**
     * Filtra solo i video
     */
    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('file_type', 'LIKE', 'video/%');
    }

    /**
     * Filtra per galleria
     */
    public function scopeByGallery(Builder $query, int $galleryId): Builder
    {
        return $query->where('gallery_id', $galleryId);
    }

    /**
     * Filtra per utente
     */
    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Filtra per tipo di file specifico
     */
    public function scopeByFileType(Builder $query, string $fileType): Builder
    {
        return $query->where('file_type', $fileType);
    }

    // ACCESSORS

    /**
     * Ottiene l'URL completo del file
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
     * Verifica se il media item è un'immagine
     */
    public function getIsImageAttribute(): bool
    {
        return str_starts_with($this->file_type, 'image/');
    }

    /**
     * Verifica se il media item è un video
     */
    public function getIsVideoAttribute(): bool
    {
        return str_starts_with($this->file_type, 'video/');
    }

    /**
     * Ottiene l'URL per la thumbnail (per le immagini)
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->is_image) {
            return null;
        }

        // Assumendo che le thumbnail siano salvate in una cartella specifica
        $thumbnailPath = str_replace('/media/', '/media/thumbnails/', $this->file_path);
        
        if (file_exists(storage_path('app/public/' . $thumbnailPath))) {
            return asset('storage/' . $thumbnailPath);
        }

        return $this->file_url;
    }

    /**
     * Ottiene l'estensione del file
     */
    public function getFileExtensionAttribute(): string
    {
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    /**
     * Ottiene il nome del file senza estensione
     */
    public function getFileNameAttribute(): string
    {
        return pathinfo($this->file_path, PATHINFO_FILENAME);
    }

    /**
     * Ottiene l'icona basata sul tipo di file
     */
    public function getFileIconAttribute(): string
    {
        if ($this->is_image) {
            return 'fas fa-image';
        }

        if ($this->is_video) {
            return 'fas fa-video';
        }

        return match($this->file_extension) {
            'pdf' => 'fas fa-file-pdf',
            'doc', 'docx' => 'fas fa-file-word',
            'xls', 'xlsx' => 'fas fa-file-excel',
            'ppt', 'pptx' => 'fas fa-file-powerpoint',
            'zip', 'rar', '7z' => 'fas fa-file-archive',
            'mp3', 'wav', 'flac' => 'fas fa-music',
            default => 'fas fa-file'
        };
    }

    // MUTATORS

    /**
     * Imposta il tipo di file in minuscolo
     */
    public function setFileTypeAttribute($value): void
    {
        $this->attributes['file_type'] = $value ? strtolower($value) : null;
    }

    /**
     * Valida che l'ordine sia un numero positivo
     */
    public function setOrderAttribute($value): void
    {
        $this->attributes['order'] = max(0, (int) $value);
    }

    /**
     * Valida che la dimensione del file sia positiva
     */
    public function setFileSizeAttribute($value): void
    {
        $this->attributes['file_size'] = max(0, (int) $value);
    }

    // HELPER METHODS

    /**
     * Ottiene il prossimo numero di ordine disponibile per la galleria
     */
    public static function getNextOrderForGallery(int $galleryId): int
    {
        $maxOrder = static::where('gallery_id', $galleryId)->max('order');
        return ($maxOrder ?? 0) + 1;
    }

    /**
     * Sposta il media item in una posizione specifica
     */
    public function moveToPosition(int $newOrder): bool
    {
        $currentOrder = $this->order;
        
        if ($currentOrder === $newOrder) {
            return true;
        }

        // Sposta gli altri elementi
        if ($newOrder > $currentOrder) {
            // Sposta verso il basso
            static::where('gallery_id', $this->gallery_id)
                  ->where('order', '>', $currentOrder)
                  ->where('order', '<=', $newOrder)
                  ->decrement('order');
        } else {
            // Sposta verso l'alto
            static::where('gallery_id', $this->gallery_id)
                  ->where('order', '>=', $newOrder)
                  ->where('order', '<', $currentOrder)
                  ->increment('order');
        }

        $this->order = $newOrder;
        return $this->save();
    }

    /**
     * Ottiene il media item precedente nella galleria
     */
    public function getPreviousItem(): ?static
    {
        return static::where('gallery_id', $this->gallery_id)
                    ->where('order', '<', $this->order)
                    ->orderBy('order', 'desc')
                    ->first();
    }

    /**
     * Ottiene il media item successivo nella galleria
     */
    public function getNextItem(): ?static
    {
        return static::where('gallery_id', $this->gallery_id)
                    ->where('order', '>', $this->order)
                    ->orderBy('order')
                    ->first();
    }

    /**
     * Verifica se il media item può essere eliminato dall'utente specificato
     */
    public function canBeDeletedBy(User $user): bool
    {
        // L'utente può eliminare i propri media o se è admin della scuola
        return $this->user_id === $user->id || 
               ($user->isAdmin() && $user->school_id === $this->mediaGallery->school_id);
    }

    /**
     * Ottiene informazioni sulla durata per i video (se disponibile)
     */
    public function getVideoDuration(): ?string
    {
        // Questa funzione richiederebbe l'integrazione con una libreria 
        // per leggere i metadati video come getid3 o ffprobe
        // Per ora restituisce null, può essere implementata in seguito
        return null;
    }

    /**
     * Ottiene le dimensioni dell'immagine (larghezza x altezza)
     */
    public function getImageDimensions(): ?array
    {
        if (!$this->is_image) {
            return null;
        }

        $fullPath = storage_path('app/public/' . $this->file_path);
        
        if (!file_exists($fullPath)) {
            return null;
        }

        $imageSize = getimagesize($fullPath);
        
        if ($imageSize === false) {
            return null;
        }

        return [
            'width' => $imageSize[0],
            'height' => $imageSize[1]
        ];
    }
}