<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

/**
 * File Upload Helper per validazione sicura dei file
 *
 * Fornisce validazione avanzata con:
 * - Magic bytes verification (real file type)
 * - MIME type validation
 * - Size limits
 * - Extension whitelist
 */
class FileUploadHelper
{
    /**
     * MIME types permessi per categoria
     */
    private const ALLOWED_MIMES = [
        'images' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ],
        'documents' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ],
        'videos' => [
            'video/mp4',
            'video/mpeg',
            'video/quicktime',
            'video/x-msvideo'
        ]
    ];

    /**
     * Magic bytes signatures per tipo file
     */
    private const MAGIC_BYTES = [
        'image/jpeg' => [
            ['offset' => 0, 'bytes' => [0xFF, 0xD8, 0xFF]]
        ],
        'image/png' => [
            ['offset' => 0, 'bytes' => [0x89, 0x50, 0x4E, 0x47, 0x0D, 0x0A, 0x1A, 0x0A]]
        ],
        'image/gif' => [
            ['offset' => 0, 'bytes' => [0x47, 0x49, 0x46, 0x38, 0x37, 0x61]], // GIF87a
            ['offset' => 0, 'bytes' => [0x47, 0x49, 0x46, 0x38, 0x39, 0x61]]  // GIF89a
        ],
        'application/pdf' => [
            ['offset' => 0, 'bytes' => [0x25, 0x50, 0x44, 0x46]] // %PDF
        ],
        'video/mp4' => [
            ['offset' => 4, 'bytes' => [0x66, 0x74, 0x79, 0x70]] // ftyp
        ]
    ];

    /**
     * Valida un file caricato con controlli avanzati
     *
     * @param UploadedFile $file File da validare
     * @param string $category Categoria (images, documents, videos)
     * @param int $maxSizeMB Dimensione massima in MB
     * @return array ['valid' => bool, 'errors' => array]
     */
    public static function validateFile(UploadedFile $file, string $category, int $maxSizeMB = 10): array
    {
        $errors = [];

        // 1. Check dimensione file
        $maxSizeBytes = $maxSizeMB * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            $errors[] = "File troppo grande. Massimo {$maxSizeMB}MB consentiti.";
        }

        // 2. Check MIME type dichiarato
        $clientMimeType = $file->getMimeType();
        $allowedMimes = self::ALLOWED_MIMES[$category] ?? [];

        if (!in_array($clientMimeType, $allowedMimes)) {
            $errors[] = "Tipo file non permesso. MIME type: {$clientMimeType}";
            Log::warning('File upload rejected: invalid MIME type', [
                'mime_type' => $clientMimeType,
                'category' => $category,
                'original_name' => $file->getClientOriginalName()
            ]);
        }

        // 3. Check MIME type reale (via finfo)
        try {
            $realMimeType = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $file->getRealPath());

            if ($realMimeType !== $clientMimeType) {
                $errors[] = "Il tipo file dichiarato non corrisponde al contenuto reale.";
                Log::warning('File upload rejected: MIME type mismatch', [
                    'declared' => $clientMimeType,
                    'real' => $realMimeType,
                    'original_name' => $file->getClientOriginalName()
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error detecting real MIME type', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
        }

        // 4. Verifica magic bytes per tipi critici (images, pdf)
        if ($category === 'images' || $clientMimeType === 'application/pdf') {
            if (!self::validateMagicBytes($file, $clientMimeType)) {
                $errors[] = "Il file non sembra essere un {$category} valido (magic bytes check failed).";
                Log::warning('File upload rejected: magic bytes validation failed', [
                    'mime_type' => $clientMimeType,
                    'original_name' => $file->getClientOriginalName()
                ]);
            }
        }

        // 5. Check estensione file
        $extension = strtolower($file->getClientOriginalExtension());
        $validExtensions = self::getValidExtensions($category);

        if (!in_array($extension, $validExtensions)) {
            $errors[] = "Estensione file non valida: .{$extension}";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $clientMimeType,
            'size_mb' => round($file->getSize() / 1024 / 1024, 2)
        ];
    }

    /**
     * Valida magic bytes del file
     *
     * @param UploadedFile $file
     * @param string $expectedMimeType
     * @return bool
     */
    private static function validateMagicBytes(UploadedFile $file, string $expectedMimeType): bool
    {
        if (!isset(self::MAGIC_BYTES[$expectedMimeType])) {
            return true; // Nessuna signature definita, skip check
        }

        try {
            $handle = fopen($file->getRealPath(), 'rb');
            if (!$handle) {
                return false;
            }

            $signatures = self::MAGIC_BYTES[$expectedMimeType];

            foreach ($signatures as $signature) {
                $offset = $signature['offset'];
                $expectedBytes = $signature['bytes'];

                fseek($handle, $offset);
                $actualBytes = array_values(unpack('C*', fread($handle, count($expectedBytes))));

                if ($actualBytes === $expectedBytes) {
                    fclose($handle);
                    return true;
                }
            }

            fclose($handle);
            return false;

        } catch (\Exception $e) {
            Log::error('Error validating magic bytes', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName()
            ]);
            return false;
        }
    }

    /**
     * Ottiene estensioni valide per categoria
     *
     * @param string $category
     * @return array
     */
    private static function getValidExtensions(string $category): array
    {
        return match($category) {
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'documents' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'videos' => ['mp4', 'mpeg', 'mov', 'avi'],
            default => []
        };
    }

    /**
     * Genera nome file sicuro (sanitizzato)
     *
     * @param string $originalName
     * @return string
     */
    public static function sanitizeFileName(string $originalName): string
    {
        // Rimuovi path traversal
        $name = basename($originalName);

        // Rimuovi caratteri pericolosi
        $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);

        // Limita lunghezza
        $name = substr($name, 0, 200);

        // Aggiungi timestamp per unicità
        $extension = pathinfo($name, PATHINFO_EXTENSION);
        $basename = pathinfo($name, PATHINFO_FILENAME);

        return $basename . '_' . time() . '.' . $extension;
    }

    /**
     * Valida che il path di destinazione sia sicuro
     *
     * @param string $path
     * @param string $allowedBasePath
     * @return bool
     */
    public static function isPathSafe(string $path, string $allowedBasePath): bool
    {
        // Risolvi path assoluto
        $realPath = realpath(dirname($path));
        $realBasePath = realpath($allowedBasePath);

        // Verifica che il path sia dentro il base path permesso
        if ($realPath === false || $realBasePath === false) {
            return false;
        }

        return str_starts_with($realPath, $realBasePath);
    }

    /**
     * Ottiene category da MIME type
     *
     * @param string $mimeType
     * @return string|null
     */
    public static function getCategoryFromMimeType(string $mimeType): ?string
    {
        foreach (self::ALLOWED_MIMES as $category => $mimes) {
            if (in_array($mimeType, $mimes)) {
                return $category;
            }
        }
        return null;
    }

    /**
     * SENIOR FIX: Upload file sicuro con validazione avanzata
     *
     * Gestisce upload completo di:
     * - Validazione file (magic bytes, MIME, size)
     * - Creazione directory se non esiste
     * - Nome file sanitizzato con timestamp
     * - Storage su disco 'public'
     *
     * @param UploadedFile $file File da uploadare
     * @param string $directory Directory di destinazione (es: 'events', 'documents')
     * @param string $category Categoria per validazione ('image', 'documents', 'videos')
     * @param int $maxSizeMB Dimensione massima in MB
     * @return array ['success' => bool, 'path' => string|null, 'errors' => array]
     */
    public static function uploadFile(
        UploadedFile $file,
        string $directory,
        string $category,
        int $maxSizeMB = 10
    ): array {
        try {
            // Map category to validation category
            $validationCategory = match($category) {
                'image' => 'images',
                'document' => 'documents',
                'video' => 'videos',
                default => 'images'
            };

            // 1. Validate file
            $validation = self::validateFile($file, $validationCategory, $maxSizeMB);

            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'path' => null,
                    'errors' => $validation['errors']
                ];
            }

            // 2. Sanitize filename
            $originalName = $file->getClientOriginalName();
            $sanitizedName = self::sanitizeFileName($originalName);

            // 3. Ensure directory exists (create if needed)
            $fullPath = storage_path('app/public/' . $directory);
            if (!file_exists($fullPath)) {
                if (!mkdir($fullPath, 0755, true)) {
                    Log::error('Failed to create upload directory', [
                        'directory' => $fullPath
                    ]);
                    return [
                        'success' => false,
                        'path' => null,
                        'errors' => ['Impossibile creare la directory di upload.']
                    ];
                }
                Log::info('Created upload directory', ['directory' => $fullPath]);
            }

            // 4. Store file
            $storedPath = $file->storeAs($directory, $sanitizedName, 'public');

            if (!$storedPath) {
                return [
                    'success' => false,
                    'path' => null,
                    'errors' => ['Errore durante il salvataggio del file.']
                ];
            }

            Log::info('File uploaded successfully', [
                'original_name' => $originalName,
                'stored_path' => $storedPath,
                'size_mb' => $validation['size_mb'],
                'mime_type' => $validation['mime_type']
            ]);

            return [
                'success' => true,
                'path' => $storedPath,
                'errors' => [],
                'filename' => $sanitizedName,
                'size_mb' => $validation['size_mb'],
                'mime_type' => $validation['mime_type']
            ];

        } catch (\Exception $e) {
            Log::error('File upload failed with exception', [
                'error' => $e->getMessage(),
                'file' => $file->getClientOriginalName(),
                'directory' => $directory
            ]);

            return [
                'success' => false,
                'path' => null,
                'errors' => ['Errore imprevisto durante l\'upload: ' . $e->getMessage()]
            ];
        }
    }
}
