<?php

namespace App\Services;

use App\Models\School;
use App\Models\MediaItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * TASK #11: Storage Quota Service
 *
 * Gestisce quota storage gallerie:
 * - Calcolo spazio utilizzato
 * - Verifica quota disponibile
 * - Aggiornamento cache
 * - Upgrade/Purchase spazio aggiuntivo
 */
class StorageQuotaService
{
    /**
     * Durata cache usage (5 minuti)
     */
    const CACHE_TTL_SECONDS = 300;

    /**
     * Threshold warning (80%)
     */
    const WARNING_THRESHOLD = 80;

    /**
     * Calcola storage utilizzato per una scuola (real-time)
     *
     * @param School $school
     * @return int Bytes utilizzati
     */
    public function calculateUsage(School $school): int
    {
        $totalBytes = MediaItem::whereHas('mediaGallery', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })->sum('file_size');

        return (int) ($totalBytes ?? 0);
    }

    /**
     * Aggiorna cache storage_used_bytes per una scuola
     *
     * @param School $school
     * @return int Bytes utilizzati (aggiornato)
     */
    public function updateCache(School $school): int
    {
        $totalBytes = $this->calculateUsage($school);

        $school->update([
            'storage_used_bytes' => $totalBytes,
            'storage_cache_updated_at' => now()
        ]);

        Log::info('Storage cache updated', [
            'school_id' => $school->id,
            'school_name' => $school->name,
            'storage_used_bytes' => $totalBytes,
            'storage_used_gb' => round($totalBytes / 1024 / 1024 / 1024, 2)
        ]);

        return $totalBytes;
    }

    /**
     * Ottieni storage utilizzato (con cache)
     *
     * Se cache è vecchia (> CACHE_TTL_SECONDS), ricalcola
     *
     * @param School $school
     * @param bool $forceRefresh Force ricalcolo (ignora cache)
     * @return int Bytes utilizzati
     */
    public function getUsage(School $school, bool $forceRefresh = false): int
    {
        // Force refresh
        if ($forceRefresh) {
            return $this->updateCache($school);
        }

        // Check cache age
        $cacheAge = $school->storage_cache_updated_at
            ? now()->diffInSeconds($school->storage_cache_updated_at)
            : self::CACHE_TTL_SECONDS + 1;

        // Cache troppo vecchia -> refresh
        if ($cacheAge > self::CACHE_TTL_SECONDS) {
            return $this->updateCache($school);
        }

        // Return cached value
        return $school->storage_used_bytes;
    }

    /**
     * Check se scuola può uploadare file di dimensione specifica
     *
     * @param School $school
     * @param int $fileSizeBytes Dimensione file da uploadare
     * @return bool TRUE se può uploadare, FALSE se quota superata
     */
    public function canUpload(School $school, int $fileSizeBytes): bool
    {
        // Storage illimitato -> sempre TRUE
        if ($school->storage_unlimited) {
            return true;
        }

        // Check quota scaduta (per upgrade temporanei)
        if ($school->hasExpiredQuota()) {
            $this->handleExpiredQuota($school);
        }

        // Ottieni usage corrente (con cache)
        $currentUsage = $this->getUsage($school);

        // Calcola nuovo totale dopo upload
        $newTotal = $currentUsage + $fileSizeBytes;

        // Check se supera quota
        $canUpload = $newTotal <= $school->storage_quota_bytes;

        if (!$canUpload) {
            Log::warning('Storage quota exceeded', [
                'school_id' => $school->id,
                'school_name' => $school->name,
                'current_usage_gb' => round($currentUsage / 1024 / 1024 / 1024, 2),
                'file_size_mb' => round($fileSizeBytes / 1024 / 1024, 2),
                'quota_gb' => $school->storage_quota_gb,
                'would_exceed_by_mb' => round(($newTotal - $school->storage_quota_bytes) / 1024 / 1024, 2)
            ]);
        }

        return $canUpload;
    }

    /**
     * Incrementa storage utilizzato dopo upload
     *
     * @param School $school
     * @param int $fileSizeBytes
     */
    public function incrementUsage(School $school, int $fileSizeBytes): void
    {
        $school->increment('storage_used_bytes', $fileSizeBytes);
        $school->update(['storage_cache_updated_at' => now()]);

        // Log se raggiunge warning threshold
        $school->refresh();
        if ($school->isStorageWarning()) {
            Log::warning('Storage warning threshold reached', [
                'school_id' => $school->id,
                'school_name' => $school->name,
                'usage_percent' => $school->storage_usage_percentage
            ]);
        }
    }

    /**
     * Decrementa storage utilizzato dopo delete
     *
     * @param School $school
     * @param int $fileSizeBytes
     */
    public function decrementUsage(School $school, int $fileSizeBytes): void
    {
        $school->decrement('storage_used_bytes', $fileSizeBytes);
        $school->update(['storage_cache_updated_at' => now()]);
    }

    /**
     * Gestione quota scaduta
     *
     * Se quota aggiuntiva è scaduta, reset a quota base (5GB)
     *
     * @param School $school
     */
    private function handleExpiredQuota(School $school): void
    {
        Log::info('Storage quota expired, resetting to base quota', [
            'school_id' => $school->id,
            'school_name' => $school->name,
            'old_quota_gb' => $school->storage_quota_gb,
            'new_quota_gb' => 5 // Base quota
        ]);

        $school->update([
            'storage_quota_gb' => 5, // Reset to base
            'storage_quota_expires_at' => null
        ]);
    }

    /**
     * Acquista GB aggiuntivi (permanenti)
     *
     * @param School $school
     * @param int $additionalGB GB da aggiungere
     * @param bool $temporary Se TRUE, scade dopo 1 anno
     * @return bool Success
     */
    public function purchaseAdditionalStorage(School $school, int $additionalGB, bool $temporary = false): bool
    {
        try {
            $oldQuota = $school->storage_quota_gb;
            $newQuota = $oldQuota + $additionalGB;

            $updateData = [
                'storage_quota_gb' => $newQuota,
            ];

            // Se upgrade temporaneo, imposta scadenza
            if ($temporary) {
                $updateData['storage_quota_expires_at'] = now()->addYear();
            }

            $school->update($updateData);

            Log::info('Additional storage purchased', [
                'school_id' => $school->id,
                'school_name' => $school->name,
                'old_quota_gb' => $oldQuota,
                'new_quota_gb' => $newQuota,
                'additional_gb' => $additionalGB,
                'temporary' => $temporary,
                'expires_at' => $temporary ? now()->addYear() : null
            ]);

            return true;

        } catch (\Exception $e) {
            Log::error('Failed to purchase additional storage', [
                'school_id' => $school->id,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Abilita storage illimitato
     *
     * @param School $school
     */
    public function enableUnlimited(School $school): void
    {
        $school->update(['storage_unlimited' => true]);

        Log::info('Unlimited storage enabled', [
            'school_id' => $school->id,
            'school_name' => $school->name
        ]);
    }

    /**
     * Formatta bytes in formato human-readable
     *
     * @param int $bytes
     * @param int $precision
     * @return string Es: "2.35 GB"
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Ottieni info complete storage per dashboard
     *
     * @param School $school
     * @return array
     */
    public function getStorageInfo(School $school): array
    {
        $usedBytes = $this->getUsage($school);
        $quotaBytes = $school->storage_quota_bytes;
        $remainingBytes = $school->storage_remaining_bytes;
        $usagePercent = $school->storage_usage_percentage;

        return [
            'unlimited' => $school->storage_unlimited,
            'used_bytes' => $usedBytes,
            'used_formatted' => $this->formatBytes($usedBytes),
            'quota_gb' => $school->storage_quota_gb,
            'quota_bytes' => $quotaBytes,
            'quota_formatted' => $this->formatBytes($quotaBytes),
            'remaining_bytes' => $remainingBytes,
            'remaining_formatted' => $this->formatBytes($remainingBytes),
            'usage_percent' => $usagePercent,
            'is_warning' => $school->isStorageWarning(),
            'is_full' => $school->isStorageFull(),
            'expires_at' => $school->storage_quota_expires_at,
            'has_expired' => $school->hasExpiredQuota(),
        ];
    }
}
