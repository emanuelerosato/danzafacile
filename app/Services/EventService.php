<?php

namespace App\Services;

use App\Models\Event;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class EventService
{
    /**
     * Gestisce l'upload dell'immagine evento con compressione e thumbnails.
     *
     * Crea 3 versioni dell'immagine:
     * - original: dimensione originale
     * - medium: 800x600 (per listing/cards)
     * - thumb: 200x150 (per thumbnails)
     *
     * @param UploadedFile $file
     * @param int|null $eventId ID evento per organizzare in directory
     * @return array Paths delle immagini create: ['original' => '...', 'medium' => '...', 'thumb' => '...']
     */
    public function handleImageUpload(UploadedFile $file, ?int $eventId = null): array
    {
        // Genera nome file univoco
        $filename = Str::random(40) . '.jpg';
        $basePath = $eventId ? "events/{$eventId}" : 'events/temp';

        // Path completi
        $paths = [
            'original' => "{$basePath}/original/{$filename}",
            'medium' => "{$basePath}/medium/{$filename}",
            'thumb' => "{$basePath}/thumb/{$filename}",
        ];

        // Inizializza Intervention Image con GD driver
        $manager = new ImageManager(new Driver());

        try {
            // Leggi immagine originale
            $image = $manager->read($file->getRealPath());

            // 1. ORIGINAL - Salva con compressione 85%
            $originalImage = clone $image;
            $encodedOriginal = $originalImage->toJpeg(quality: 85);
            Storage::disk('public')->put($paths['original'], (string) $encodedOriginal);

            // 2. MEDIUM - Resize 800x600 mantenendo aspect ratio
            $mediumImage = clone $image;
            $mediumImage->scale(width: 800, height: 600);
            $encodedMedium = $mediumImage->toJpeg(quality: 85);
            Storage::disk('public')->put($paths['medium'], (string) $encodedMedium);

            // 3. THUMB - Resize 200x150 mantenendo aspect ratio
            $thumbImage = clone $image;
            $thumbImage->scale(width: 200, height: 150);
            $encodedThumb = $thumbImage->toJpeg(quality: 85);
            Storage::disk('public')->put($paths['thumb'], (string) $encodedThumb);

            return $paths;

        } catch (\Exception $e) {
            // Cleanup in caso di errore
            foreach ($paths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            throw new \RuntimeException('Errore durante l\'upload dell\'immagine: ' . $e->getMessage());
        }
    }

    /**
     * Elimina tutte le versioni di un'immagine evento.
     *
     * @param string $imagePath Path dell'immagine original (o qualsiasi versione)
     * @return void
     */
    public function deleteEventImage(string $imagePath): void
    {
        // Estrai base path e filename
        $pathParts = explode('/', $imagePath);
        $filename = array_pop($pathParts);

        // Rimuovi 'original', 'medium' o 'thumb' dal path
        $basePath = implode('/', array_filter($pathParts, fn($part) => !in_array($part, ['original', 'medium', 'thumb'])));

        // Path delle versioni
        $versions = [
            "{$basePath}/original/{$filename}",
            "{$basePath}/medium/{$filename}",
            "{$basePath}/thumb/{$filename}",
        ];

        // Elimina tutte le versioni
        foreach ($versions as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Cleanup directory vuote
        $directories = [
            "{$basePath}/original",
            "{$basePath}/medium",
            "{$basePath}/thumb",
            $basePath,
        ];

        foreach ($directories as $dir) {
            if (Storage::disk('public')->exists($dir) && empty(Storage::disk('public')->files($dir))) {
                Storage::disk('public')->deleteDirectory($dir);
            }
        }
    }

    /**
     * Genera uno slug univoco per l'evento con gestione caratteri italiani.
     *
     * Translittera caratteri accentati italiani (à, è, ì, ò, ù, ç) prima dello slugging.
     *
     * @param string $name Nome evento
     * @param int|null $ignoreId ID da ignorare per update
     * @return string Slug univoco
     */
    public function generateEventSlug(string $name, ?int $ignoreId = null): string
    {
        // Normalizzazione caratteri italiani PRIMA di Str::slug()
        $normalized = $this->normalizeItalianChars($name);

        // Genera slug base
        $slug = Str::slug($normalized);
        $originalSlug = $slug;
        $counter = 1;

        // Verifica unicità (senza global scope per slug univoco globale)
        while ($this->slugExists($slug, $ignoreId)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Normalizza caratteri accentati italiani per slug corretti.
     *
     * @param string $string Stringa da normalizzare
     * @return string Stringa normalizzata
     */
    private function normalizeItalianChars(string $string): string
    {
        $replacements = [
            // Vocali accentate minuscole
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a', 'ä' => 'a', 'å' => 'a',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ç' => 'c',

            // Vocali accentate maiuscole
            'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
            'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
            'Ç' => 'C',
        ];

        return strtr($string, $replacements);
    }

    /**
     * Verifica se uno slug esiste già.
     *
     * @param string $slug Slug da verificare
     * @param int|null $ignoreId ID evento da ignorare
     * @return bool
     */
    private function slugExists(string $slug, ?int $ignoreId = null): bool
    {
        $query = Event::withoutGlobalScope('school')->where('slug', $slug);

        if ($ignoreId) {
            $query->where('id', '!=', $ignoreId);
        }

        return $query->exists();
    }

    /**
     * Invalida cache eventi per una scuola specifica usando Redis tags.
     *
     * Usa cache tags per invalidazione granulare:
     * - school:{school_id}: tutti i dati della scuola
     * - events: tutti gli eventi
     * - events:public: solo eventi pubblici
     *
     * @param int $schoolId ID scuola
     * @param bool $publicOnly Se true, invalida solo cache eventi pubblici
     * @return void
     */
    public function clearEventCache(int $schoolId, bool $publicOnly = false): void
    {
        // Tags da invalidare
        $tags = ["school:{$schoolId}"];

        if ($publicOnly) {
            $tags[] = 'events:public';
        } else {
            $tags[] = 'events';
        }

        // Flush cache con tags
        Cache::tags($tags)->flush();

        // Invalida anche cache generica scuola (per backward compatibility)
        Cache::forget("school_{$schoolId}");
        Cache::forget("school_events_{$schoolId}");
        Cache::forget("school_public_events_{$schoolId}");
    }

    /**
     * Prepara i dati validati per la creazione/update di un evento.
     *
     * Gestisce:
     * - Default values
     * - Slug generation se necessario
     * - Normalizzazione dati
     *
     * @param array $validated Dati validati
     * @param int|null $eventId ID evento per update
     * @return array Dati preparati
     */
    public function prepareEventData(array $validated, ?int $eventId = null): array
    {
        // Default values per campi booleani
        $validated['requires_registration'] = $validated['requires_registration'] ?? false;
        $validated['is_public'] = $validated['is_public'] ?? true;
        $validated['active'] = $validated['active'] ?? true;
        $validated['requires_payment'] = $validated['requires_payment'] ?? false;
        $validated['qr_checkin_enabled'] = $validated['qr_checkin_enabled'] ?? false;

        // Default values per prezzi
        $validated['price_students'] = $validated['price_students'] ?? 0.00;
        $validated['price_guests'] = $validated['price_guests'] ?? 0.00;

        // Genera slug se non presente
        if (empty($validated['slug']) && !empty($validated['name'])) {
            $validated['slug'] = $this->generateEventSlug($validated['name'], $eventId);
        }

        // Normalizza requirements array
        if (isset($validated['requirements']) && is_array($validated['requirements'])) {
            // Rimuovi elementi vuoti
            $validated['requirements'] = array_filter($validated['requirements'], fn($req) => !empty($req));
            // Reset array keys
            $validated['requirements'] = array_values($validated['requirements']);
        }

        return $validated;
    }

    /**
     * Ottiene statistiche per un evento.
     *
     * @param Event $event Evento
     * @return array Statistiche
     */
    public function getEventStats(Event $event): array
    {
        return [
            'total_registrations' => $event->registrations()->active()->count(),
            'confirmed_registrations' => $event->registrations()->confirmed()->count(),
            'waitlist_count' => $event->registrations()->waitlist()->count(),
            'available_spots' => $event->available_spots,
            'total_revenue' => $event->payments()->where('status', 'completed')->sum('amount') ??
                             ($event->registrations()->confirmed()->count() * ($event->price_students ?? 0)),
            'attendance_count' => $event->attendance()->where('status', 'present')->count(),
            'no_show_count' => $event->registrations()->confirmed()->count() -
                              $event->attendance()->where('status', 'present')->count(),
            'guest_registrations' => $event->registrations()
                ->whereHas('user', fn($q) => $q->where('is_guest', true))
                ->count(),
        ];
    }

    /**
     * Verifica se un evento può essere eliminato.
     *
     * Business rule: eventi con registrazioni confermate non possono essere eliminati
     *
     * @param Event $event Evento
     * @return array ['can_delete' => bool, 'reason' => string|null]
     */
    public function canDeleteEvent(Event $event): array
    {
        $confirmedRegistrations = $event->registrations()->confirmed()->count();

        if ($confirmedRegistrations > 0) {
            return [
                'can_delete' => false,
                'reason' => "L'evento ha {$confirmedRegistrations} registrazioni confermate."
            ];
        }

        return ['can_delete' => true, 'reason' => null];
    }
}
