<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MediaGallery extends Model
{
    use HasFactory;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        // Global scope per multi-tenant security - TEMPORANEAMENTE DISABILITATO
        // static::addGlobalScope('school', function (Builder $builder) {
        //     if (auth()->check() && auth()->user()->school_id) {
        //         $builder->where('school_id', auth()->user()->school_id);
        //     }
        // });
    }

    /**
     * Enum per i tipi di galleria
     */
    const TYPE_PHOTOS = 'foto';
    const TYPE_VIDEOS = 'video';
    const TYPE_MIXED = 'misto';
    const TYPE_PERFORMANCES = 'spettacoli';
    const TYPE_LESSONS = 'lezioni';
    const TYPE_EVENTS = 'eventi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'school_id',
        'course_id',
        'created_by',
        'title',
        'description',
        'type',
        'is_public',
        'is_featured',
        'cover_image_id',
        'settings',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'is_public' => 'boolean',
            'is_featured' => 'boolean',
            'settings' => 'array',
        ];
    }

    // RELAZIONI

    /**
     * Ottiene la scuola proprietaria della galleria
     */
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Ottiene il corso associato alla galleria (opzionale)
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Ottiene l'utente che ha creato la galleria
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Ottiene l'immagine di copertina specifica
     */
    public function coverImage(): BelongsTo
    {
        return $this->belongsTo(MediaItem::class, 'cover_image_id');
    }

    /**
     * Ottiene tutti i media items della galleria
     */
    public function mediaItems(): HasMany
    {
        return $this->hasMany(MediaItem::class, 'gallery_id')->ordered();
    }

    /**
     * Ottiene solo le foto della galleria
     */
    public function photos(): HasMany
    {
        return $this->mediaItems()->where('file_type', 'LIKE', 'image/%');
    }

    /**
     * Ottiene solo i video della galleria
     */
    public function videos(): HasMany
    {
        return $this->mediaItems()->where('file_type', 'LIKE', 'video/%');
    }

    // SCOPES

    /**
     * Filtra le gallerie per tipo
     */
    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Filtra solo le gallerie di foto
     */
    public function scopePhotos(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PHOTOS);
    }

    /**
     * Filtra solo le gallerie di video
     */
    public function scopeVideos(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_VIDEOS);
    }

    /**
     * Filtra solo le gallerie miste
     */
    public function scopeMixed(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MIXED);
    }

    /**
     * Filtra le gallerie per scuola
     */
    public function scopeBySchool(Builder $query, int $schoolId): Builder
    {
        return $query->where('school_id', $schoolId);
    }

    /**
     * Filtra le gallerie per corso
     */
    public function scopeByCourse(Builder $query, int $courseId): Builder
    {
        return $query->where('course_id', $courseId);
    }

    /**
     * Filtra le gallerie che hanno almeno un media item
     */
    public function scopeWithMedia(Builder $query): Builder
    {
        return $query->has('mediaItems');
    }

    /**
     * Filtra le gallerie per spettacoli
     */
    public function scopePerformances(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_PERFORMANCES);
    }

    /**
     * Filtra le gallerie per lezioni
     */
    public function scopeLessons(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_LESSONS);
    }

    /**
     * Filtra le gallerie per eventi
     */
    public function scopeEvents(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_EVENTS);
    }

    /**
     * Filtra solo le gallerie pubbliche
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Filtra solo le gallerie private
     */
    public function scopePrivate(Builder $query): Builder
    {
        return $query->where('is_public', false);
    }

    /**
     * Filtra solo le gallerie in evidenza
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Filtra le gallerie per creatore
     */
    public function scopeByCreator(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope ottimizzato per la dashboard admin con eager loading
     */
    public function scopeForDashboard(Builder $query): Builder
    {
        return $query->with([
            'course:id,name',
            'createdBy:id,name',
            'coverImage:id,gallery_id,file_path,thumbnail_url,type'
        ])
        ->withCount('mediaItems')
        ->latest('created_at');
    }

    /**
     * Scope ottimizzato per gallerie pubbliche
     */
    public function scopePublicWithPreview(Builder $query): Builder
    {
        return $query->where('is_public', true)
                    ->with([
                        'coverImage:id,gallery_id,file_path,thumbnail_url,type',
                        'mediaItems' => function($q) {
                            $q->select('id', 'gallery_id', 'type', 'file_path', 'thumbnail_url', 'title')
                              ->ordered()
                              ->limit(4);
                        }
                    ])
                    ->withCount('mediaItems');
    }

    /**
     * Scope ottimizzato per ricerca veloce
     */
    public function scopeSearchOptimized(Builder $query, string $searchTerm): Builder
    {
        return $query->select('id', 'title', 'description', 'type', 'created_at', 'cover_image_id')
                    ->where(function($q) use ($searchTerm) {
                        $q->where('title', 'LIKE', "%{$searchTerm}%")
                          ->orWhere('description', 'LIKE', "%{$searchTerm}%");
                    });
    }

    // MUTATORS

    /**
     * Imposta il tipo con validazione
     */
    public function setTypeAttribute($value): void
    {
        $allowedTypes = [
            self::TYPE_PHOTOS,
            self::TYPE_VIDEOS,
            self::TYPE_MIXED,
            self::TYPE_PERFORMANCES,
            self::TYPE_LESSONS,
            self::TYPE_EVENTS
        ];
        
        $this->attributes['type'] = in_array($value, $allowedTypes) ? $value : self::TYPE_MIXED;
    }

    // ACCESSORS

    /**
     * Ottiene il numero totale di media items
     */
    public function getTotalMediaCountAttribute(): int
    {
        return $this->mediaItems()->count();
    }

    /**
     * Ottiene il numero totale di foto
     */
    public function getPhotosCountAttribute(): int
    {
        return $this->photos()->count();
    }

    /**
     * Ottiene il numero totale di video
     */
    public function getVideosCountAttribute(): int
    {
        return $this->videos()->count();
    }

    /**
     * Ottiene la foto di copertina (primo media item)
     */
    public function getCoverImageAttribute(): ?MediaItem
    {
        return $this->mediaItems()->first();
    }

    /**
     * Verifica se la galleria è vuota
     */
    public function getIsEmptyAttribute(): bool
    {
        return $this->total_media_count === 0;
    }

    /**
     * Ottiene l'URL della foto di copertina
     */
    public function getCoverImageUrlAttribute(): ?string
    {
        $coverImage = $this->cover_image;
        return $coverImage ? $coverImage->file_url : null;
    }

    // HELPER METHODS

    /**
     * Ottiene tutti i tipi disponibili
     */
    public static function getAvailableTypes(): array
    {
        return cache()->remember('media_gallery_types', 3600, function () {
            return [
                self::TYPE_PHOTOS => 'Foto',
                self::TYPE_VIDEOS => 'Video',
                self::TYPE_MIXED => 'Misto',
                self::TYPE_PERFORMANCES => 'Spettacoli',
                self::TYPE_LESSONS => 'Lezioni',
                self::TYPE_EVENTS => 'Eventi',
            ];
        });
    }

    /**
     * Verifica se la galleria può contenere foto
     */
    public function canContainPhotos(): bool
    {
        return in_array($this->type, [
            self::TYPE_PHOTOS,
            self::TYPE_MIXED,
            self::TYPE_PERFORMANCES,
            self::TYPE_LESSONS,
            self::TYPE_EVENTS
        ]);
    }

    /**
     * Verifica se la galleria può contenere video
     */
    public function canContainVideos(): bool
    {
        return in_array($this->type, [
            self::TYPE_VIDEOS,
            self::TYPE_MIXED,
            self::TYPE_PERFORMANCES,
            self::TYPE_LESSONS,
            self::TYPE_EVENTS
        ]);
    }

    /**
     * Ottiene gli ultimi media items aggiunti
     */
    public function getRecentMediaItems(int $limit = 5)
    {
        return $this->mediaItems()
                    ->latest()
                    ->limit($limit)
                    ->get();
    }

    /**
     * Ottiene la dimensione totale di tutti i media
     */
    public function getTotalSize(): int
    {
        return $this->mediaItems()->sum('file_size');
    }

    /**
     * Ottiene la dimensione totale formattata
     */
    public function getFormattedTotalSize(): string
    {
        $bytes = $this->getTotalSize();
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;

        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}