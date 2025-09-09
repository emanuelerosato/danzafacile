<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use App\Models\MediaItem;
use App\Models\Document;

class FileUploadService
{
    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10MB
    private const IMAGE_MAX_SIZE = 5 * 1024 * 1024; // 5MB
    private const DOCUMENT_MAX_SIZE = 20 * 1024 * 1024; // 20MB

    private const ALLOWED_IMAGE_TYPES = ['jpeg', 'png', 'jpg', 'gif', 'webp'];
    private const ALLOWED_DOCUMENT_TYPES = ['pdf', 'doc', 'docx', 'txt', 'rtf'];
    private const ALLOWED_VIDEO_TYPES = ['mp4', 'mov', 'avi', 'wmv'];

    /**
     * Upload and process profile image
     */
    public function uploadProfileImage(UploadedFile $file, int $userId): string
    {
        $this->validateImage($file);

        $filename = $this->generateFileName($file, 'profile');
        $path = "profiles/{$userId}/{$filename}";

        // Resize and optimize image
        $this->processAndStoreImage($file, $path, 300, 300);

        return $path;
    }

    /**
     * Upload school logo
     */
    public function uploadSchoolLogo(UploadedFile $file, int $schoolId): string
    {
        $this->validateImage($file);

        $filename = $this->generateFileName($file, 'logo');
        $path = "schools/{$schoolId}/logo/{$filename}";

        // Resize and optimize logo
        $this->processAndStoreImage($file, $path, 400, 200);

        return $path;
    }

    /**
     * Upload document with metadata
     */
    public function uploadDocument(UploadedFile $file, int $userId, int $schoolId, array $metadata = []): Document
    {
        $this->validateDocument($file);

        $filename = $this->generateFileName($file, 'doc');
        $path = "documents/{$schoolId}/{$userId}/{$filename}";

        Storage::disk('private')->put($path, file_get_contents($file));

        return Document::create([
            'user_id' => $userId,
            'school_id' => $schoolId,
            'title' => $metadata['title'] ?? $file->getClientOriginalName(),
            'description' => $metadata['description'] ?? null,
            'file_path' => $path,
            'file_name' => $filename,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'status' => 'pending',
        ]);
    }

    /**
     * Upload media item for gallery
     */
    public function uploadMediaItem(UploadedFile $file, int $galleryId, array $metadata = []): MediaItem
    {
        $type = $this->determineMediaType($file);
        $this->validateMediaFile($file, $type);

        $filename = $this->generateFileName($file, 'media');
        $path = "media/{$galleryId}/{$filename}";

        if ($type === 'image') {
            $this->processAndStoreImage($file, $path, 1920, 1080);
            
            // Create thumbnail
            $thumbnailPath = "media/{$galleryId}/thumbs/{$filename}";
            $this->processAndStoreImage($file, $thumbnailPath, 300, 200);
        } else {
            Storage::disk('public')->put($path, file_get_contents($file));
            $thumbnailPath = null;
        }

        return MediaItem::create([
            'media_gallery_id' => $galleryId,
            'title' => $metadata['title'] ?? $file->getClientOriginalName(),
            'description' => $metadata['description'] ?? null,
            'file_path' => $path,
            'thumbnail_path' => $thumbnailPath,
            'file_name' => $filename,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'type' => $type,
            'active' => true,
        ]);
    }

    /**
     * Delete file and cleanup
     */
    public function deleteFile(string $path, string $disk = 'public'): bool
    {
        if (Storage::disk($disk)->exists($path)) {
            return Storage::disk($disk)->delete($path);
        }
        return false;
    }

    /**
     * Get secure download URL for private files
     */
    public function getSecureUrl(string $path, int $expiresInMinutes = 60): string
    {
        return Storage::disk('private')->temporaryUrl($path, now()->addMinutes($expiresInMinutes));
    }

    // PRIVATE METHODS

    private function validateImage(UploadedFile $file): void
    {
        if ($file->getSize() > self::IMAGE_MAX_SIZE) {
            throw new \InvalidArgumentException('Image size exceeds maximum allowed size.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_IMAGE_TYPES)) {
            throw new \InvalidArgumentException('Invalid image type. Allowed: ' . implode(', ', self::ALLOWED_IMAGE_TYPES));
        }

        // Check if it's a valid image
        if (!getimagesize($file->path())) {
            throw new \InvalidArgumentException('File is not a valid image.');
        }
    }

    private function validateDocument(UploadedFile $file): void
    {
        if ($file->getSize() > self::DOCUMENT_MAX_SIZE) {
            throw new \InvalidArgumentException('Document size exceeds maximum allowed size.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, self::ALLOWED_DOCUMENT_TYPES)) {
            throw new \InvalidArgumentException('Invalid document type. Allowed: ' . implode(', ', self::ALLOWED_DOCUMENT_TYPES));
        }
    }

    private function validateMediaFile(UploadedFile $file, string $type): void
    {
        $maxSize = $type === 'image' ? self::IMAGE_MAX_SIZE : self::MAX_FILE_SIZE;
        
        if ($file->getSize() > $maxSize) {
            throw new \InvalidArgumentException('File size exceeds maximum allowed size.');
        }

        $extension = strtolower($file->getClientOriginalExtension());
        $allowedTypes = $type === 'image' ? self::ALLOWED_IMAGE_TYPES : 
                       ($type === 'video' ? self::ALLOWED_VIDEO_TYPES : self::ALLOWED_DOCUMENT_TYPES);

        if (!in_array($extension, $allowedTypes)) {
            throw new \InvalidArgumentException('Invalid file type for ' . $type . '. Allowed: ' . implode(', ', $allowedTypes));
        }
    }

    private function determineMediaType(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        
        if (in_array($extension, self::ALLOWED_IMAGE_TYPES)) {
            return 'image';
        } elseif (in_array($extension, self::ALLOWED_VIDEO_TYPES)) {
            return 'video';
        } else {
            return 'document';
        }
    }

    private function generateFileName(UploadedFile $file, string $prefix = ''): string
    {
        $extension = $file->getClientOriginalExtension();
        $timestamp = now()->format('Y-m-d_H-i-s');
        $random = Str::random(8);
        
        return $prefix . '_' . $timestamp . '_' . $random . '.' . $extension;
    }

    private function processAndStoreImage(UploadedFile $file, string $path, int $maxWidth, int $maxHeight): void
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($file->path());

        // Resize maintaining aspect ratio
        $image->scale(width: $maxWidth, height: $maxHeight);

        // Optimize for web
        $quality = $file->getClientOriginalExtension() === 'png' ? null : 85;

        Storage::disk('public')->put($path, $image->encode(quality: $quality));
    }
}