# TASK #11 - Sistema Quota Storage Gallerie + Acquisto Spazio

**Status:** ⏸️ Pending
**Priorità:** 🔵 LOW
**Complessità:** 🔴 High
**Tempo Stimato:** 4-5 ore (con FASE 7 Super Admin inclusa)
**Data Inizio:** -
**Data Fine:** -

---

## 📊 PROGRESS TRACKING

### ✅ Completato
- [x] Creazione piano implementazione dettagliato (1300+ righe)
- [x] Analisi schema database esistente
- [x] Design architettura completa

### 🔄 In Progress
- [ ] Nessuna task attualmente in corso

### ⏸️ Pending
- [ ] FASE 1: Database Migration (40-50 min)
- [ ] FASE 2: Service Layer (50-60 min)
- [ ] FASE 3: Controller Modifications (30-40 min)
- [ ] FASE 4: UI Components (40-50 min)
- [ ] FASE 5: Routes & Integration (10-15 min)
- [ ] FASE 6: Testing & Edge Cases (30-40 min)
- [ ] FASE 7: Super Admin Storage Management (40-50 min)
- [ ] Deploy su VPS Production

### 📝 Note Implementazione
_Spazio per note durante l'implementazione..._

---

## 🎯 OBIETTIVO

Implementare sistema completo di quota storage per gallerie:
- Limitare spazio totale per scuola (default: 5GB base)
- Mostrare usage corrente con UI intuitiva
- Sistema acquisto spazio aggiuntivo (4 piani)
- Bloccare upload se quota superata
- Cache intelligente per performance

### Comportamento Atteso
- Dashboard mostra widget "Spazio Gallerie: 2.3GB / 5GB (46%)" con progress bar
- Warning giallo quando raggiunge 80%
- Alert rosso e blocco upload a 100%
- Link "Acquista Spazio" per upgrade
- Scadenza quota per piani temporanei (1 anno)

---

## 🏗️ ARCHITETTURA

### Schema Database

**Nuove colonne in `schools` table:**
```sql
storage_quota_gb INT DEFAULT 5
storage_used_bytes BIGINT DEFAULT 0
storage_cache_updated_at TIMESTAMP NULL
storage_quota_expires_at TIMESTAMP NULL
storage_unlimited BOOLEAN DEFAULT false
```

### Componenti Principali

1. **StorageQuotaService** (NEW)
   - Cache management (TTL 5 min)
   - Quota calculations
   - Upload validation
   - Purchase handling

2. **BillingController** (NEW)
   - Storage upgrade page
   - Payment processing
   - Plan management

3. **MediaGalleryController** (MODIFY)
   - Pre-upload quota check
   - Post-upload usage increment
   - Post-delete usage decrement

4. **School Model** (MODIFY)
   - 7 nuovi accessors/helpers
   - Quota expiration logic

---

## 📋 PIANO IMPLEMENTAZIONE DETTAGLIATO

---

### FASE 1: ANALISI & PROGETTAZIONE DATABASE (40-50 min)

#### 1.1 Analisi Schema Esistente

**File da analizzare:**
- `database/migrations/2024_09_08_000001_create_schools_table.php` - Struttura scuole
- `database/migrations/2024_09_08_000008_create_media_items_table.php` - Struttura media

**Informazioni raccolte:**
- ✅ Tabella `media_items` ha già `file_size` (bigInteger)
- ❌ Tabella `schools` NON ha campi storage quota
- ✅ `media_items` ha `school_id` per multi-tenant filtering

#### 1.2 Design Nuove Colonne Storage Quota

**Colonne da aggiungere a `schools`:**

```php
// database/migrations/2026_01_24_HHMMSS_add_storage_quota_to_schools.php
Schema::table('schools', function (Blueprint $table) {
    // Quota base in GB (default: 5GB per tutte le scuole)
    $table->integer('storage_quota_gb')
          ->default(5)
          ->comment('Storage quota totale in GB');

    // Spazio utilizzato in bytes (calcolato real-time ma cachato)
    $table->bigInteger('storage_used_bytes')
          ->default(0)
          ->comment('Spazio utilizzato in bytes (cache)');

    // Timestamp ultimo aggiornamento cache
    $table->timestamp('storage_cache_updated_at')
          ->nullable()
          ->comment('Ultimo aggiornamento cache storage');

    // Scadenza quota aggiuntiva (NULL = illimitato/permanente)
    $table->timestamp('storage_quota_expires_at')
          ->nullable()
          ->comment('Scadenza GB aggiuntivi (NULL = permanente)');

    // Flag per disabilitare limite (per scuole premium/illimitate)
    $table->boolean('storage_unlimited')
          ->default(false)
          ->comment('TRUE = storage illimitato');

    // Indici per performance
    $table->index('storage_unlimited');
});
```

**Rationale:**
- `storage_used_bytes`: Cache per evitare SUM() query pesanti su ogni request
- `storage_cache_updated_at`: Timestamp per invalidare cache (es: ogni 5 min)
- `storage_quota_expires_at`: Per gestire upgrade temporanei (es: +10GB per 1 anno)
- `storage_unlimited`: Per scuole VIP/unlimited (evita check quota)

#### 1.3 Migration Completa

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->integer('storage_quota_gb')->default(5);
            $table->bigInteger('storage_used_bytes')->default(0);
            $table->timestamp('storage_cache_updated_at')->nullable();
            $table->timestamp('storage_quota_expires_at')->nullable();
            $table->boolean('storage_unlimited')->default(false);

            $table->index('storage_unlimited');
        });

        // Calcola storage_used_bytes per scuole esistenti
        // ATTENZIONE: Può essere lento se molti media_items
        $this->calculateInitialStorageUsage();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropIndex(['storage_unlimited']);
            $table->dropColumn([
                'storage_quota_gb',
                'storage_used_bytes',
                'storage_cache_updated_at',
                'storage_quota_expires_at',
                'storage_unlimited'
            ]);
        });
    }

    /**
     * Calcola storage usage per scuole esistenti
     */
    private function calculateInitialStorageUsage(): void
    {
        $schools = DB::table('schools')->get();

        foreach ($schools as $school) {
            $totalBytes = DB::table('media_items')
                ->where('school_id', $school->id)
                ->sum('file_size');

            DB::table('schools')
                ->where('id', $school->id)
                ->update([
                    'storage_used_bytes' => $totalBytes ?? 0,
                    'storage_cache_updated_at' => now()
                ]);
        }

        \Log::info('Storage quota migration: Calculated initial usage for ' . $schools->count() . ' schools');
    }
};
```

#### 1.4 Aggiornamento Model School

```php
// app/Models/School.php

// Aggiungi a $fillable
protected $fillable = [
    // ... existing fields ...
    'storage_quota_gb',
    'storage_used_bytes',
    'storage_cache_updated_at',
    'storage_quota_expires_at',
    'storage_unlimited',
];

// Aggiungi a $casts
protected $casts = [
    // ... existing casts ...
    'storage_cache_updated_at' => 'datetime',
    'storage_quota_expires_at' => 'datetime',
    'storage_unlimited' => 'boolean',
];

/**
 * Ottieni quota totale in bytes
 */
public function getStorageQuotaBytesAttribute(): int
{
    return $this->storage_quota_gb * 1024 * 1024 * 1024; // GB -> bytes
}

/**
 * Ottieni percentuale uso storage
 */
public function getStorageUsagePercentAttribute(): float
{
    if ($this->storage_unlimited) {
        return 0; // Unlimited = sempre 0%
    }

    if ($this->storage_quota_bytes === 0) {
        return 100; // No quota = pieno
    }

    return round(($this->storage_used_bytes / $this->storage_quota_bytes) * 100, 2);
}

/**
 * Ottieni storage rimanente in bytes
 */
public function getStorageRemainingBytesAttribute(): int
{
    if ($this->storage_unlimited) {
        return PHP_INT_MAX; // Unlimited
    }

    $remaining = $this->storage_quota_bytes - $this->storage_used_bytes;
    return max(0, $remaining); // Never negative
}

/**
 * Check se quota è scaduta (per upgrade temporanei)
 */
public function hasExpiredQuota(): bool
{
    if (!$this->storage_quota_expires_at) {
        return false; // Quota permanente
    }

    return $this->storage_quota_expires_at->isPast();
}

/**
 * Check se storage è pieno (>= 100%)
 */
public function isStorageFull(): bool
{
    if ($this->storage_unlimited) {
        return false;
    }

    return $this->storage_used_bytes >= $this->storage_quota_bytes;
}

/**
 * Check se storage è quasi pieno (>= 80%)
 */
public function isStorageWarning(): bool
{
    if ($this->storage_unlimited) {
        return false;
    }

    return $this->storage_usage_percent >= 80;
}
```

**✅ Checklist FASE 1:**
- [ ] Migration creata e testata localmente
- [ ] School model aggiornato con fillable/casts
- [ ] 7 accessors/helpers implementati
- [ ] Test calcolo initial storage usage
- [ ] Verify indici database creati

---

### FASE 2: SERVICE LAYER - StorageQuotaService (50-60 min)

#### 2.1 Service Completo

```php
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
        $totalBytes = MediaItem::where('school_id', $school->id)
            ->sum('file_size');

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
                'usage_percent' => $school->storage_usage_percent
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
        $usagePercent = $school->storage_usage_percent;

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
```

**✅ Checklist FASE 2:**
- [ ] StorageQuotaService creato
- [ ] 10 metodi pubblici implementati
- [ ] Cache logic testato (TTL 5 min)
- [ ] Logging completo su tutti gli eventi
- [ ] Test calculateUsage con DB reale

---

### FASE 3: CONTROLLER MODIFICATIONS (30-40 min)

#### 3.1 MediaGalleryController - Quota Check Before Upload

**Modifiche necessarie:**

```php
// app/Http/Controllers/Admin/MediaGalleryController.php

use App\Services\StorageQuotaService;

class MediaGalleryController extends AdminBaseController
{
    protected StorageQuotaService $storageQuotaService;

    public function __construct(StorageQuotaService $storageQuotaService)
    {
        $this->storageQuotaService = $storageQuotaService;
    }

    /**
     * Upload media to gallery
     *
     * TASK #11: Aggiunto check quota storage
     */
    public function uploadMedia(UploadMediaGalleryRequest $request)
    {
        $this->setupContext();

        // TASK #11: Check storage quota PRIMA di validare upload
        if (!$this->checkStorageQuota($request)) {
            return redirect()->back()
                ->with('error', 'Quota storage superata! Libera spazio o acquista GB aggiuntivi.')
                ->with('show_upgrade_modal', true); // Flag per mostrare modal upgrade
        }

        // ... existing upload logic ...

        // TASK #11: Incrementa usage dopo upload successful
        $uploadedFileSize = $request->file('file')->getSize();
        $this->storageQuotaService->incrementUsage($this->school, $uploadedFileSize);

        return redirect()->back()->with('success', 'Media caricato con successo!');
    }

    /**
     * Delete media from gallery
     *
     * TASK #11: Decrementa usage dopo delete
     */
    public function deleteMedia(MediaItem $media)
    {
        $this->setupContext();

        // Multi-tenant authorization
        if ($media->school_id !== $this->school->id) {
            abort(403);
        }

        $fileSize = $media->file_size;

        // Delete file
        Storage::disk('public')->delete($media->file_path);

        // Delete DB record
        $media->delete();

        // TASK #11: Decrementa storage usage
        $this->storageQuotaService->decrementUsage($this->school, $fileSize);

        return redirect()->back()->with('success', 'Media eliminato con successo!');
    }

    /**
     * TASK #11: Check storage quota before upload
     *
     * @param UploadMediaGalleryRequest $request
     * @return bool TRUE se può uploadare, FALSE se quota superata
     */
    private function checkStorageQuota(UploadMediaGalleryRequest $request): bool
    {
        // Get uploaded file size
        $file = $request->file('file');
        if (!$file) {
            return true; // No file = no check
        }

        $fileSizeBytes = $file->getSize();

        // Check quota
        return $this->storageQuotaService->canUpload($this->school, $fileSizeBytes);
    }
}
```

#### 3.2 UploadMediaGalleryRequest - Validation con Quota

```php
// app/Http/Requests/UploadMediaGalleryRequest.php

use App\Services\StorageQuotaService;

class UploadMediaGalleryRequest extends FormRequest
{
    /**
     * Get the validation rules
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:jpeg,jpg,png,gif,mp4,mov,avi',
                'max:102400', // 100MB max per file
                // TASK #11: Custom rule per quota check
                function ($attribute, $value, $fail) {
                    $school = $this->user()->currentSchool();
                    $storageQuotaService = app(StorageQuotaService::class);

                    if (!$storageQuotaService->canUpload($school, $value->getSize())) {
                        $storageInfo = $storageQuotaService->getStorageInfo($school);
                        $fail("Quota storage superata! Utilizzo: {$storageInfo['used_formatted']} / {$storageInfo['quota_formatted']}. Libera spazio o acquista GB aggiuntivi.");
                    }
                },
            ],
            'gallery_id' => 'required|exists:media_galleries,id',
        ];
    }
}
```

#### 3.3 BillingController - Storage Upgrade Handling (NEW)

**File nuovo da creare:**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * TASK #11: Billing Controller - Storage Upgrade
 */
class BillingController extends AdminBaseController
{
    protected StorageQuotaService $storageQuotaService;

    public function __construct(StorageQuotaService $storageQuotaService)
    {
        $this->storageQuotaService = $storageQuotaService;
    }

    /**
     * Show storage upgrade page
     */
    public function storage()
    {
        $this->setupContext();

        $storageInfo = $this->storageQuotaService->getStorageInfo($this->school);

        // Pricing plans
        $plans = [
            [
                'name' => 'Piano Base',
                'gb' => 5,
                'price' => 0,
                'type' => 'base',
                'features' => ['5GB storage', 'Supporto email'],
            ],
            [
                'name' => 'Piano Plus',
                'gb' => 20,
                'price' => 9.99,
                'type' => 'monthly',
                'features' => ['20GB storage', 'Supporto prioritario', 'Backup automatico'],
            ],
            [
                'name' => 'Piano Pro',
                'gb' => 50,
                'price' => 19.99,
                'type' => 'monthly',
                'features' => ['50GB storage', 'Supporto 24/7', 'Backup automatico', 'Logo personalizzato'],
            ],
            [
                'name' => 'Piano Unlimited',
                'gb' => null, // Unlimited
                'price' => 49.99,
                'type' => 'monthly',
                'features' => ['Storage illimitato', 'Supporto dedicato', 'Backup automatico', 'Tutti i premium features'],
            ],
        ];

        return view('admin.billing.storage', compact('storageInfo', 'plans'));
    }

    /**
     * Purchase additional storage
     */
    public function purchaseStorage(Request $request)
    {
        $this->setupContext();

        $request->validate([
            'plan_type' => 'required|in:plus,pro,unlimited',
            'payment_method' => 'required|in:paypal,stripe',
        ]);

        $planType = $request->input('plan_type');

        // Map plan to GB
        $gbMap = [
            'plus' => 20,
            'pro' => 50,
            'unlimited' => null,
        ];

        try {
            if ($planType === 'unlimited') {
                // Enable unlimited storage
                $this->storageQuotaService->enableUnlimited($this->school);

                Log::info('Unlimited storage purchased', [
                    'school_id' => $this->school->id,
                    'admin_id' => auth()->id(),
                    'payment_method' => $request->input('payment_method')
                ]);

                return redirect()->back()
                    ->with('success', 'Storage illimitato attivato con successo!');

            } else {
                // Purchase additional GB
                $newQuotaGB = $gbMap[$planType];
                $additionalGB = $newQuotaGB - $this->school->storage_quota_gb;

                if ($additionalGB <= 0) {
                    return redirect()->back()
                        ->with('error', 'Hai già questo piano o superiore.');
                }

                $this->storageQuotaService->purchaseAdditionalStorage(
                    $this->school,
                    $additionalGB,
                    true // Temporary = scade dopo 1 anno
                );

                Log::info('Additional storage purchased', [
                    'school_id' => $this->school->id,
                    'admin_id' => auth()->id(),
                    'plan_type' => $planType,
                    'additional_gb' => $additionalGB,
                    'new_quota_gb' => $newQuotaGB,
                    'payment_method' => $request->input('payment_method')
                ]);

                return redirect()->back()
                    ->with('success', "Piano {$planType} attivato! +{$additionalGB}GB aggiunti.");
            }

        } catch (\Exception $e) {
            Log::error('Storage purchase failed', [
                'school_id' => $this->school->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('error', 'Errore durante l\'acquisto. Riprova o contatta il supporto.');
        }
    }
}
```

**✅ Checklist FASE 3:**
- [ ] MediaGalleryController modificato (constructor injection)
- [ ] checkStorageQuota() method aggiunto
- [ ] incrementUsage() chiamato dopo upload
- [ ] decrementUsage() chiamato dopo delete
- [ ] UploadMediaGalleryRequest custom rule aggiunto
- [ ] BillingController creato
- [ ] storage() method implementato
- [ ] purchaseStorage() method implementato

---

### FASE 4: UI COMPONENTS (40-50 min)

#### 4.1 Dashboard - Storage Usage Widget

**Modifiche a `resources/views/admin/dashboard.blade.php`:**

Aggiungere widget nella griglia dashboard:

```blade
{{-- TASK #11: Storage Usage Card --}}
@php
    $storageInfo = app(App\Services\StorageQuotaService::class)->getStorageInfo($school);
@endphp

<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-semibold text-gray-900">Spazio Gallerie</h3>

        @if($storageInfo['unlimited'])
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L11 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c-.25.78.74 1.43 1.403.926l.07-.07a1.99 1.99 0 012.83 0l.07.07c.662.504 1.652-.145 1.403-.926l-.818-2.552a1.99 1.99 0 00-1.13-1.13l-2.552-.818a1 1 0 00-.926 1.403l.07.07a1.99 1.99 0 000 2.83l-.07.07a1 1 0 00-.926 1.403z"/>
                </svg>
                Illimitato
            </span>
        @else
            @if($storageInfo['is_full'])
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Pieno
                </span>
            @elseif($storageInfo['is_warning'])
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Attenzione
                </span>
            @endif
        @endif
    </div>

    @if($storageInfo['unlimited'])
        <p class="text-sm text-gray-600 mb-2">
            <span class="font-semibold text-gray-900">{{ $storageInfo['used_formatted'] }}</span> utilizzati
        </p>
        <p class="text-xs text-gray-500">Storage illimitato attivo</p>
    @else
        <div class="space-y-3">
            <div>
                <div class="flex items-center justify-between text-sm mb-1">
                    <span class="text-gray-600">Utilizzo:</span>
                    <span class="font-semibold text-gray-900">
                        {{ $storageInfo['used_formatted'] }} / {{ $storageInfo['quota_formatted'] }}
                    </span>
                </div>

                {{-- Progress bar --}}
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div class="h-2.5 rounded-full transition-all duration-300
                        @if($storageInfo['is_full']) bg-red-600
                        @elseif($storageInfo['is_warning']) bg-yellow-500
                        @else bg-green-500
                        @endif"
                         style="width: {{ min($storageInfo['usage_percent'], 100) }}%">
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-1">
                    {{ $storageInfo['usage_percent'] }}% utilizzato
                    @if($storageInfo['expires_at'])
                        • Scade il {{ $storageInfo['expires_at']->format('d/m/Y') }}
                    @endif
                </p>
            </div>

            @if($storageInfo['is_warning'] || $storageInfo['is_full'])
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm text-yellow-700">
                                @if($storageInfo['is_full'])
                                    Spazio esaurito! Non puoi caricare nuovi media.
                                @else
                                    Stai raggiungendo il limite. Considera di acquistare spazio aggiuntivo.
                                @endif
                            </p>
                            <a href="{{ route('admin.billing.storage') }}"
                               class="inline-flex items-center mt-2 text-sm font-medium text-yellow-700 hover:text-yellow-600">
                                Acquista Spazio
                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    @endif
</div>
```

#### 4.2 Billing Page - Storage Upgrade

**File nuovo: `resources/views/admin/billing/storage.blade.php`**

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Storage
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestisci lo spazio disponibile per le tue gallerie
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Storage</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                {{-- Current Usage Card --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Utilizzo Corrente</h3>

                    @if($storageInfo['unlimited'])
                        <div class="text-center py-8">
                            <svg class="w-16 h-16 mx-auto text-purple-500 mb-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L11 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c-.25.78.74 1.43 1.403.926l.07-.07a1.99 1.99 0 012.83 0l.07.07c.662.504 1.652-.145 1.403-.926l-.818-2.552a1.99 1.99 0 00-1.13-1.13l-2.552-.818a1 1 0 00-.926 1.403l.07.07a1.99 1.99 0 000 2.83l-.07.07a1 1 0 00-.926 1.403z"/>
                            </svg>
                            <p class="text-xl font-semibold text-gray-900">Storage Illimitato Attivo</p>
                            <p class="text-sm text-gray-600 mt-2">{{ $storageInfo['used_formatted'] }} utilizzati</p>
                        </div>
                    @else
                        <div class="grid md:grid-cols-3 gap-6 mb-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Utilizzato</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $storageInfo['used_formatted'] }}</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Quota Totale</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $storageInfo['quota_gb'] }} GB</p>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <p class="text-sm text-gray-600 mb-1">Disponibile</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $storageInfo['remaining_formatted'] }}</p>
                            </div>
                        </div>

                        {{-- Progress Bar --}}
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="text-gray-600">Utilizzo Storage</span>
                                <span class="font-semibold text-gray-900">{{ $storageInfo['usage_percent'] }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-4">
                                <div class="h-4 rounded-full transition-all duration-300
                                    @if($storageInfo['is_full']) bg-red-600
                                    @elseif($storageInfo['is_warning']) bg-yellow-500
                                    @else bg-green-500
                                    @endif"
                                     style="width: {{ min($storageInfo['usage_percent'], 100) }}%">
                                </div>
                            </div>
                        </div>

                        @if($storageInfo['expires_at'])
                            <p class="text-sm text-gray-600 mt-2">
                                <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                Quota aggiuntiva scade il <strong>{{ $storageInfo['expires_at']->format('d/m/Y') }}</strong>
                            </p>
                        @endif
                    @endif
                </div>

                {{-- Pricing Plans --}}
                @unless($storageInfo['unlimited'])
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Piani Disponibili</h3>

                        <div class="grid md:grid-cols-4 gap-6">
                            @foreach($plans as $plan)
                                <div class="border rounded-lg p-6
                                    @if($plan['type'] === 'base') bg-gray-50
                                    @elseif($plan['name'] === 'Piano Pro') border-purple-500 border-2 relative
                                    @endif">

                                    @if($plan['name'] === 'Piano Pro')
                                        <span class="absolute top-0 right-0 bg-purple-500 text-white text-xs font-bold px-3 py-1 rounded-bl-lg rounded-tr-lg">
                                            Popolare
                                        </span>
                                    @endif

                                    <h4 class="text-lg font-semibold text-gray-900 mb-2">{{ $plan['name'] }}</h4>

                                    <div class="mb-4">
                                        @if($plan['gb'])
                                            <p class="text-3xl font-bold text-gray-900">{{ $plan['gb'] }} GB</p>
                                        @else
                                            <p class="text-3xl font-bold text-purple-600">Illimitato</p>
                                        @endif

                                        <p class="text-sm text-gray-600 mt-1">
                                            @if($plan['price'] > 0)
                                                €{{ $plan['price'] }}/mese
                                            @else
                                                Gratis
                                            @endif
                                        </p>
                                    </div>

                                    <ul class="space-y-2 mb-6">
                                        @foreach($plan['features'] as $feature)
                                            <li class="flex items-start text-sm text-gray-700">
                                                <svg class="w-5 h-5 text-green-500 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                                {{ $feature }}
                                            </li>
                                        @endforeach
                                    </ul>

                                    @if($plan['type'] === 'base')
                                        <button disabled class="w-full px-4 py-2 bg-gray-300 text-gray-600 rounded-lg cursor-not-allowed">
                                            Piano Corrente
                                        </button>
                                    @else
                                        <form action="{{ route('admin.billing.purchase-storage') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="plan_type" value="{{ strtolower(str_replace('Piano ', '', $plan['name'])) }}">
                                            <input type="hidden" name="payment_method" value="paypal">

                                            <button type="submit"
                                                    class="w-full px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200 transform hover:scale-105">
                                                Acquista Ora
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endunless

            </div>
        </div>
    </div>
</x-app-layout>
```

**✅ Checklist FASE 4:**
- [ ] Dashboard widget implementato
- [ ] Progress bar colori corretti (verde/giallo/rosso)
- [ ] Storage billing page creata
- [ ] 4 pricing plans visualizzati
- [ ] Form purchase storage funzionante
- [ ] UI responsive testata

---

### FASE 5: ROUTES & INTEGRATION (10-15 min)

#### 5.1 Routes Web

**Modifiche a `routes/web.php`:**

```php
// Dentro il gruppo Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(...)

Route::prefix('billing')->name('billing.')->group(function () {
    Route::get('/storage', [App\Http\Controllers\Admin\BillingController::class, 'storage'])
        ->name('storage');

    Route::post('/purchase-storage', [App\Http\Controllers\Admin\BillingController::class, 'purchaseStorage'])
        ->name('purchase-storage');
});
```

**✅ Checklist FASE 5:**
- [ ] Routes billing aggiunte
- [ ] Route names corretti
- [ ] Middleware auth applicato
- [ ] Test routing con artisan route:list

---

### FASE 6: TESTING & EDGE CASES (30-40 min)

#### Test Cases da Verificare:

**1. Upload con Quota Disponibile**
- [ ] Upload file piccolo (1MB) con quota 5GB e usage 1GB → SUCCESS
- [ ] Upload file grande (100MB) con quota 5GB e usage 4.9GB → SUCCESS
- [ ] Verifica incremento storage_used_bytes corretto

**2. Upload con Quota Superata**
- [ ] Upload file (500MB) con quota 5GB e usage 4.8GB → BLOCKED (supererebbe quota)
- [ ] Upload file (1MB) con quota 5GB e usage 5GB → BLOCKED (quota piena)
- [ ] Verifica messaggio errore corretto
- [ ] Verifica viene mostrato link "Acquista Spazio"

**3. Delete Media**
- [ ] Delete media 10MB → usage decrementa di 10MB
- [ ] Verifica cache aggiornata dopo delete
- [ ] Verifica file fisico eliminato da storage

**4. Cache Storage Usage**
- [ ] Primo accesso: calcola real-time (query DB)
- [ ] Accessi successivi < 5min: usa cache (no query)
- [ ] Accessi > 5min: ricalcola (aggiorna cache)
- [ ] Force refresh funziona correttamente

**5. Upgrade Storage**
- [ ] Acquisto Piano Plus (20GB) → quota passa da 5GB a 20GB
- [ ] Acquisto Piano Pro (50GB) → quota passa da 5GB a 50GB
- [ ] Acquisto Unlimited → flag unlimited=TRUE
- [ ] Verifica scadenza upgrade temporaneo (storage_quota_expires_at)

**6. Storage Illimitato**
- [ ] Upload con unlimited=TRUE → sempre permesso (no check)
- [ ] Dashboard mostra "Illimitato" invece di progress bar
- [ ] Verifica badge "Illimitato" visualizzato

**7. Quota Scaduta**
- [ ] Upgrade temporaneo scaduto → reset a quota base (5GB)
- [ ] Se usage > base quota dopo scadenza → blocco upload
- [ ] Log evento scadenza quota

**8. Edge Cases**
- [ ] Upload multipli concorrenti → race condition su cache
- [ ] File molto grandi (1GB+) → timeout/memory
- [ ] Delete durante upload → consistency storage_used_bytes
- [ ] Scuola senza media → usage = 0 (corretto)

**9. Performance**
- [ ] Cache hit rate > 95% in condizioni normali
- [ ] Calcolo usage < 100ms per scuole con < 1000 media
- [ ] Dashboard load time < 500ms con storage widget
- [ ] Query N+1 non presenti

**10. Security**
- [ ] Multi-tenant isolation: scuola A non vede usage scuola B
- [ ] Authorization: solo admin scuola può acquistare storage
- [ ] Validation: file size negativo → error
- [ ] SQL injection: file_size manipolato → no impact

**✅ Checklist FASE 6:**
- [ ] Tutti i 10 test cases passano
- [ ] Edge cases gestiti correttamente
- [ ] Performance accettabile
- [ ] Security verificata

---

### FASE 7: SUPER ADMIN STORAGE MANAGEMENT (40-50 min)

#### 7.1 Obiettivo FASE 7

Implementare dashboard e funzionalità per **Super Admin** per gestire manualmente le quote storage delle scuole:
- Visualizzare overview storage di tutte le scuole
- Assegnare GB extra a scuola specifica
- Abilitare/disabilitare storage unlimited
- Impostare scadenze quota personalizzate
- Audit trail completo delle modifiche

**Casi d'uso:**
- Super Admin vuole regalare 20GB extra a scuola VIP (gratis, permanente)
- Super Admin vuole abilitare unlimited per scuola premium
- Super Admin vuole dare 10GB temporanei (1 anno) per testing
- Super Admin vuole vedere quali scuole stanno per finire spazio

---

#### 7.2 Flusso Completo User Journey

**STEP 1: Accesso Dashboard Super Admin Storage**

1. Super Admin fa login con credenziali super admin
2. Naviga a `/super-admin/schools/storage` (nuovo link in sidebar)
3. Sistema verifica permessi: `if (!auth()->user()->is_super_admin) abort(403)`
4. Controller carica lista tutte le scuole con info storage
5. View renderizza tabella con:
   - Nome scuola
   - Storage utilizzato (human-readable)
   - Quota totale (GB)
   - Percentuale uso (progress bar colorata)
   - Status (badge: OK/Warning/Full/Unlimited)
   - Azioni (bottone "Gestisci")

**STEP 2: Click su "Gestisci" per Scuola**

1. Super Admin clicca bottone "Gestisci" nella row della scuola
2. JavaScript apre modal `manageStorageModal` con Alpine.js
3. Modal mostra:
   - Header: "Gestisci Storage - [Nome Scuola]"
   - Info corrente:
     - Quota attuale: 5 GB
     - Utilizzo: 3.2 GB (64%)
     - Status: Normale/Unlimited
     - Scadenza: -/[Data se temporaneo]
   - Form gestione (vedi STEP 3)

**STEP 3: Form Gestione Storage**

Modal contiene form con 3 opzioni (radio buttons):

**Opzione A: Aggiungi GB**
```html
<input type="radio" name="action" value="add_quota" checked>
<label>Aggiungi GB aggiuntivi</label>

<input type="number" name="additional_gb" min="1" max="1000" value="10">
<label>GB da aggiungere (es: 10)</label>

<select name="duration">
  <option value="permanent">Permanente</option>
  <option value="1_year">1 anno</option>
  <option value="6_months">6 mesi</option>
  <option value="3_months">3 mesi</option>
  <option value="custom">Data personalizzata</option>
</select>

<input type="date" name="custom_expiry_date" class="hidden" x-show="duration === 'custom'">
```

**Opzione B: Imposta Unlimited**
```html
<input type="radio" name="action" value="set_unlimited">
<label>Abilita Storage Illimitato</label>

<p class="text-sm text-gray-600">
  La scuola avrà storage illimitato senza limiti di quota.
  Non verranno effettuati controlli di spazio.
</p>
```

**Opzione C: Reset a Quota Base**
```html
<input type="radio" name="action" value="reset_to_base">
<label>Reset a Quota Base (5GB)</label>

<p class="text-sm text-yellow-600">
  ATTENZIONE: Se la scuola sta usando più di 5GB, gli upload verranno bloccati.
  Storage utilizzato corrente: 3.2GB
</p>
```

**Campo Note (opzionale):**
```html
<textarea name="admin_note" rows="3" placeholder="Motivazione (opzionale, per audit log)">
</textarea>
```

**STEP 4: Validation Frontend (JavaScript)**

Prima di submit, JavaScript valida:
- Se action="add_quota": additional_gb > 0
- Se action="add_quota" + duration="custom": custom_expiry_date deve essere futura
- Se action="reset_to_base" + current_usage > 5GB: mostra warning confirmation

```javascript
function validateForm() {
    const action = document.querySelector('input[name="action"]:checked').value;

    if (action === 'add_quota') {
        const gb = parseInt(document.querySelector('[name="additional_gb"]').value);
        if (gb <= 0 || gb > 1000) {
            alert('GB devono essere tra 1 e 1000');
            return false;
        }

        const duration = document.querySelector('[name="duration"]').value;
        if (duration === 'custom') {
            const date = document.querySelector('[name="custom_expiry_date"]').value;
            if (new Date(date) <= new Date()) {
                alert('Data scadenza deve essere futura');
                return false;
            }
        }
    }

    if (action === 'reset_to_base') {
        const currentUsageGB = parseFloat(document.getElementById('current-usage-gb').textContent);
        if (currentUsageGB > 5) {
            return confirm(`ATTENZIONE: La scuola sta usando ${currentUsageGB}GB. Resettare a 5GB bloccherà gli upload. Continuare?`);
        }
    }

    return true;
}
```

**STEP 5: Submit Form → Backend**

1. Form viene inviato tramite POST a `/super-admin/schools/{school}/storage/update`
2. Request arriva a `SuperAdminSchoolStorageController@update`
3. Controller esegue validazione backend (vedi 7.3)
4. Controller aggiorna database school record
5. Controller crea audit log entry
6. Controller invia notification email a school admin (opzionale)
7. Controller ritorna redirect con messaggio success
8. Frontend chiude modal e aggiorna riga tabella con nuovi dati

**STEP 6: Feedback Visivo**

Dopo update success:
- Modal si chiude automaticamente
- Toast notification verde: "Storage aggiornato con successo per [Nome Scuola]"
- Riga tabella si aggiorna in real-time (Alpine.js) con nuovi valori:
  - Quota: 5GB → 25GB
  - Status: Normal → Normal (se ancora sotto 80%)
  - Scadenza: - → 25/01/2027 (se temporaneo)
- Badge "Modificato da Super Admin" (piccolo, temporaneo, scompare dopo 5 sec)

**STEP 7: Audit Trail**

Sistema registra in `storage_quota_audit_log` (nuova tabella):
```sql
INSERT INTO storage_quota_audit_log (
    school_id,
    super_admin_id,
    action,
    old_quota_gb,
    new_quota_gb,
    old_unlimited,
    new_unlimited,
    old_expires_at,
    new_expires_at,
    admin_note,
    created_at
) VALUES (
    1,                    -- school_id
    1,                    -- super_admin_id (auth()->id())
    'add_quota',          -- action
    5,                    -- old_quota_gb
    25,                   -- new_quota_gb
    false,                -- old_unlimited
    false,                -- new_unlimited
    NULL,                 -- old_expires_at
    '2027-01-25',         -- new_expires_at
    'Upgrade per cliente VIP', -- admin_note
    NOW()
);
```

---

#### 7.3 Controller: SuperAdminSchoolStorageController

**File nuovo:** `app/Http/Controllers/SuperAdmin/SuperAdminSchoolStorageController.php`

```php
<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\StorageQuotaAuditLog;
use App\Services\StorageQuotaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * TASK #11 - FASE 7: Super Admin Storage Management
 *
 * Gestisce modifiche manuali alle quote storage scuole da parte Super Admin
 */
class SuperAdminSchoolStorageController extends Controller
{
    protected StorageQuotaService $storageQuotaService;

    public function __construct(StorageQuotaService $storageQuotaService)
    {
        // CRITICAL: Middleware super admin required
        $this->middleware(['auth', 'super_admin']);
        $this->storageQuotaService = $storageQuotaService;
    }

    /**
     * Dashboard storage overview tutte le scuole
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Carica tutte le scuole con storage info
        $schools = School::query()
            ->orderBy('storage_usage_percent', 'desc') // Più piene per prime
            ->get()
            ->map(function ($school) {
                $storageInfo = $this->storageQuotaService->getStorageInfo($school);

                return [
                    'id' => $school->id,
                    'name' => $school->name,
                    'storage_info' => $storageInfo,
                    'created_at' => $school->created_at,
                    'status_color' => $this->getStatusColor($storageInfo),
                ];
            });

        // Statistiche globali
        $totalSchools = $schools->count();
        $unlimitedSchools = $schools->where('storage_info.unlimited', true)->count();
        $warningSchools = $schools->where('storage_info.is_warning', true)->count();
        $fullSchools = $schools->where('storage_info.is_full', true)->count();

        $globalStats = [
            'total_schools' => $totalSchools,
            'unlimited_schools' => $unlimitedSchools,
            'warning_schools' => $warningSchools,
            'full_schools' => $fullSchools,
            'normal_schools' => $totalSchools - $unlimitedSchools - $warningSchools - $fullSchools,
        ];

        return view('super-admin.schools.storage.index', compact('schools', 'globalStats'));
    }

    /**
     * Update storage quota per scuola specifica
     *
     * @param Request $request
     * @param School $school
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, School $school)
    {
        // Validation
        $validated = $request->validate([
            'action' => 'required|in:add_quota,set_unlimited,reset_to_base',
            'additional_gb' => 'required_if:action,add_quota|integer|min:1|max:1000',
            'duration' => 'required_if:action,add_quota|in:permanent,1_year,6_months,3_months,custom',
            'custom_expiry_date' => 'required_if:duration,custom|date|after:today',
            'admin_note' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Salva stato precedente per audit log
            $oldState = [
                'quota_gb' => $school->storage_quota_gb,
                'unlimited' => $school->storage_unlimited,
                'expires_at' => $school->storage_quota_expires_at,
            ];

            $action = $validated['action'];
            $newState = [];

            // Esegui azione
            switch ($action) {
                case 'add_quota':
                    $additionalGB = $validated['additional_gb'];
                    $newQuota = $school->storage_quota_gb + $additionalGB;

                    // Calcola scadenza
                    $expiresAt = $this->calculateExpiryDate($validated['duration'], $validated['custom_expiry_date'] ?? null);

                    $school->update([
                        'storage_quota_gb' => $newQuota,
                        'storage_quota_expires_at' => $expiresAt,
                        'storage_unlimited' => false, // Reset unlimited se era attivo
                    ]);

                    $newState = [
                        'quota_gb' => $newQuota,
                        'unlimited' => false,
                        'expires_at' => $expiresAt,
                    ];

                    $successMessage = "Aggiunti {$additionalGB}GB a {$school->name}. Nuova quota: {$newQuota}GB";
                    break;

                case 'set_unlimited':
                    $school->update([
                        'storage_unlimited' => true,
                        'storage_quota_expires_at' => null, // Unlimited non scade
                    ]);

                    $newState = [
                        'quota_gb' => $school->storage_quota_gb, // Unchanged
                        'unlimited' => true,
                        'expires_at' => null,
                    ];

                    $successMessage = "Storage illimitato abilitato per {$school->name}";
                    break;

                case 'reset_to_base':
                    $school->update([
                        'storage_quota_gb' => 5, // Base quota
                        'storage_unlimited' => false,
                        'storage_quota_expires_at' => null,
                    ]);

                    $newState = [
                        'quota_gb' => 5,
                        'unlimited' => false,
                        'expires_at' => null,
                    ];

                    // Warning se scuola sta usando più di 5GB
                    if ($school->storage_used_bytes > (5 * 1024 * 1024 * 1024)) {
                        $usedGB = round($school->storage_used_bytes / 1024 / 1024 / 1024, 2);
                        $successMessage = "Quota reset a 5GB per {$school->name}. ATTENZIONE: La scuola sta usando {$usedGB}GB. Upload bloccati!";
                    } else {
                        $successMessage = "Quota reset a 5GB per {$school->name}";
                    }
                    break;
            }

            // Crea audit log
            $this->createAuditLog($school, $action, $oldState, $newState, $validated['admin_note'] ?? null);

            // Log evento
            Log::info('Super Admin updated school storage quota', [
                'super_admin_id' => auth()->id(),
                'super_admin_email' => auth()->user()->email,
                'school_id' => $school->id,
                'school_name' => $school->name,
                'action' => $action,
                'old_state' => $oldState,
                'new_state' => $newState,
                'admin_note' => $validated['admin_note'] ?? '-',
            ]);

            DB::commit();

            return redirect()->back()->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to update school storage quota', [
                'super_admin_id' => auth()->id(),
                'school_id' => $school->id,
                'action' => $action,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect()->back()->with('error', 'Errore durante aggiornamento quota storage. Riprova o contatta supporto tecnico.');
        }
    }

    /**
     * Calcola data scadenza quota in base a duration selezionata
     *
     * @param string $duration
     * @param string|null $customDate
     * @return \Carbon\Carbon|null
     */
    private function calculateExpiryDate(string $duration, ?string $customDate): ?Carbon
    {
        return match ($duration) {
            'permanent' => null,
            '1_year' => now()->addYear(),
            '6_months' => now()->addMonths(6),
            '3_months' => now()->addMonths(3),
            'custom' => Carbon::parse($customDate),
            default => null,
        };
    }

    /**
     * Crea audit log entry
     *
     * @param School $school
     * @param string $action
     * @param array $oldState
     * @param array $newState
     * @param string|null $adminNote
     * @return void
     */
    private function createAuditLog(School $school, string $action, array $oldState, array $newState, ?string $adminNote): void
    {
        StorageQuotaAuditLog::create([
            'school_id' => $school->id,
            'super_admin_id' => auth()->id(),
            'action' => $action,
            'old_quota_gb' => $oldState['quota_gb'],
            'new_quota_gb' => $newState['quota_gb'],
            'old_unlimited' => $oldState['unlimited'],
            'new_unlimited' => $newState['unlimited'],
            'old_expires_at' => $oldState['expires_at'],
            'new_expires_at' => $newState['expires_at'],
            'admin_note' => $adminNote,
        ]);
    }

    /**
     * Determina colore status badge in base a storage info
     *
     * @param array $storageInfo
     * @return string
     */
    private function getStatusColor(array $storageInfo): string
    {
        if ($storageInfo['unlimited']) {
            return 'purple'; // Unlimited
        }

        if ($storageInfo['is_full']) {
            return 'red'; // Full (100%)
        }

        if ($storageInfo['is_warning']) {
            return 'yellow'; // Warning (>= 80%)
        }

        return 'green'; // Normal (< 80%)
    }

    /**
     * Visualizza audit log per scuola specifica
     *
     * @param School $school
     * @return \Illuminate\View\View
     */
    public function auditLog(School $school)
    {
        $auditLogs = StorageQuotaAuditLog::where('school_id', $school->id)
            ->with('superAdmin')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('super-admin.schools.storage.audit-log', compact('school', 'auditLogs'));
    }
}
```

---

#### 7.4 Model: StorageQuotaAuditLog

**File nuovo:** `app/Models/StorageQuotaAuditLog.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * TASK #11 - FASE 7: Audit Log Storage Quota Changes
 *
 * Traccia tutte le modifiche manuali alle quote storage effettuate da Super Admin
 */
class StorageQuotaAuditLog extends Model
{
    protected $table = 'storage_quota_audit_log';

    protected $fillable = [
        'school_id',
        'super_admin_id',
        'action',
        'old_quota_gb',
        'new_quota_gb',
        'old_unlimited',
        'new_unlimited',
        'old_expires_at',
        'new_expires_at',
        'admin_note',
    ];

    protected $casts = [
        'old_unlimited' => 'boolean',
        'new_unlimited' => 'boolean',
        'old_expires_at' => 'datetime',
        'new_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Scuola a cui si riferisce il log
     */
    public function school()
    {
        return $this->belongsTo(School::class);
    }

    /**
     * Super Admin che ha effettuato la modifica
     */
    public function superAdmin()
    {
        return $this->belongsTo(User::class, 'super_admin_id');
    }

    /**
     * Ottieni descrizione human-readable dell'azione
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
     * Calcola differenza GB aggiunti/rimossi
     */
    private function getQuotaDiff(): int
    {
        return abs($this->new_quota_gb - $this->old_quota_gb);
    }
}
```

---

#### 7.5 Migration: storage_quota_audit_log Table

**File nuovo:** `database/migrations/2026_01_24_HHMMSS_create_storage_quota_audit_log_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('storage_quota_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');
            $table->foreignId('super_admin_id')->constrained('users')->onDelete('cascade');

            // Tipo azione
            $table->enum('action', ['add_quota', 'set_unlimited', 'reset_to_base']);

            // Stato precedente
            $table->integer('old_quota_gb');
            $table->boolean('old_unlimited')->default(false);
            $table->timestamp('old_expires_at')->nullable();

            // Nuovo stato
            $table->integer('new_quota_gb');
            $table->boolean('new_unlimited')->default(false);
            $table->timestamp('new_expires_at')->nullable();

            // Note admin (opzionale)
            $table->text('admin_note')->nullable();

            $table->timestamps();

            // Indici per performance
            $table->index(['school_id', 'created_at']);
            $table->index('super_admin_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_quota_audit_log');
    }
};
```

---

#### 7.6 View: Dashboard Storage Super Admin

**File nuovo:** `resources/views/super-admin/schools/storage/index.blade.php`

```blade
<x-super-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Storage Scuole
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Panoramica e gestione quote storage per tutte le scuole
                </p>
            </div>
        </div>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Statistiche Globali --}}
            <div class="grid md:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Totale Scuole</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $globalStats['total_schools'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Normali</p>
                            <p class="text-2xl font-bold text-green-700">{{ $globalStats['normal_schools'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Warning (>80%)</p>
                            <p class="text-2xl font-bold text-yellow-700">{{ $globalStats['warning_schools'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Pieno (100%)</p>
                            <p class="text-2xl font-bold text-red-700">{{ $globalStats['full_schools'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L11 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c-.25.78.74 1.43 1.403.926l.07-.07a1.99 1.99 0 012.83 0l.07.07c.662.504 1.652-.145 1.403-.926l-.818-2.552a1.99 1.99 0 00-1.13-1.13l-2.552-.818a1 1 0 00-.926 1.403l.07.07a1.99 1.99 0 000 2.83l-.07.07a1 1 0 00-.926 1.403z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Unlimited</p>
                            <p class="text-2xl font-bold text-purple-700">{{ $globalStats['unlimited_schools'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabella Scuole --}}
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Scuola
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Storage Utilizzato
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Quota Totale
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Utilizzo
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Scadenza
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Azioni
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($schools as $school)
                            <tr class="hover:bg-gray-50" x-data="{ showModal: false }">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $school['name'] }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ $school['id'] }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $school['storage_info']['used_formatted'] }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($school['storage_info']['unlimited'])
                                        <span class="text-purple-600 font-semibold">Illimitato</span>
                                    @else
                                        {{ $school['storage_info']['quota_gb'] }} GB
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($school['storage_info']['unlimited'])
                                        <span class="text-sm text-gray-500">-</span>
                                    @else
                                        <div class="w-full bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full transition-all duration-300
                                                @if($school['status_color'] === 'red') bg-red-600
                                                @elseif($school['status_color'] === 'yellow') bg-yellow-500
                                                @else bg-green-500
                                                @endif"
                                                 style="width: {{ min($school['storage_info']['usage_percent'], 100) }}%">
                                            </div>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $school['storage_info']['usage_percent'] }}%
                                        </p>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($school['status_color'] === 'purple') bg-purple-100 text-purple-800
                                        @elseif($school['status_color'] === 'red') bg-red-100 text-red-800
                                        @elseif($school['status_color'] === 'yellow') bg-yellow-100 text-yellow-800
                                        @else bg-green-100 text-green-800
                                        @endif">
                                        @if($school['storage_info']['unlimited'])
                                            Unlimited
                                        @elseif($school['storage_info']['is_full'])
                                            Pieno
                                        @elseif($school['storage_info']['is_warning'])
                                            Warning
                                        @else
                                            Normale
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    @if($school['storage_info']['expires_at'])
                                        {{ $school['storage_info']['expires_at']->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <button @click="showModal = true"
                                            class="inline-flex items-center px-3 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                                        </svg>
                                        Gestisci
                                    </button>

                                    <a href="{{ route('super-admin.schools.storage.audit-log', $school['id']) }}"
                                       class="ml-2 text-gray-600 hover:text-gray-900">
                                        <svg class="w-5 h-5 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                    </a>

                                    {{-- Modal Gestione Storage --}}
                                    <div x-show="showModal"
                                         x-cloak
                                         class="fixed inset-0 z-50 overflow-y-auto"
                                         x-transition:enter="ease-out duration-300"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0">

                                        <div class="flex items-center justify-center min-h-screen px-4">
                                            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>

                                            <div class="bg-white rounded-lg overflow-hidden shadow-xl transform transition-all sm:max-w-lg sm:w-full z-50"
                                                 @click.away="showModal = false">

                                                <form action="{{ route('super-admin.schools.storage.update', $school['id']) }}" method="POST">
                                                    @csrf

                                                    <div class="bg-white px-6 py-4">
                                                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                                                            Gestisci Storage - {{ $school['name'] }}
                                                        </h3>

                                                        {{-- Info Corrente --}}
                                                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                                                            <div class="grid grid-cols-2 gap-4 text-sm">
                                                                <div>
                                                                    <p class="text-gray-600">Quota Attuale:</p>
                                                                    <p class="font-semibold text-gray-900">
                                                                        @if($school['storage_info']['unlimited'])
                                                                            Illimitato
                                                                        @else
                                                                            {{ $school['storage_info']['quota_gb'] }} GB
                                                                        @endif
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <p class="text-gray-600">Utilizzo:</p>
                                                                    <p class="font-semibold text-gray-900">
                                                                        {{ $school['storage_info']['used_formatted'] }}
                                                                        @unless($school['storage_info']['unlimited'])
                                                                            ({{ $school['storage_info']['usage_percent'] }}%)
                                                                        @endunless
                                                                    </p>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Form Opzioni --}}
                                                        <div class="space-y-4" x-data="{ action: 'add_quota', duration: 'permanent' }">

                                                            {{-- Opzione A: Aggiungi GB --}}
                                                            <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                                                                   :class="action === 'add_quota' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                                                <input type="radio" name="action" value="add_quota"
                                                                       x-model="action" class="mt-1" checked>
                                                                <div class="ml-3 flex-1">
                                                                    <p class="font-medium text-gray-900">Aggiungi GB Aggiuntivi</p>

                                                                    <div class="mt-3 space-y-3" x-show="action === 'add_quota'">
                                                                        <div>
                                                                            <label class="block text-sm text-gray-700 mb-1">GB da aggiungere:</label>
                                                                            <input type="number" name="additional_gb" min="1" max="1000" value="10"
                                                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                                        </div>

                                                                        <div>
                                                                            <label class="block text-sm text-gray-700 mb-1">Durata:</label>
                                                                            <select name="duration" x-model="duration"
                                                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                                                <option value="permanent">Permanente</option>
                                                                                <option value="1_year">1 anno</option>
                                                                                <option value="6_months">6 mesi</option>
                                                                                <option value="3_months">3 mesi</option>
                                                                                <option value="custom">Data personalizzata</option>
                                                                            </select>
                                                                        </div>

                                                                        <div x-show="duration === 'custom'">
                                                                            <label class="block text-sm text-gray-700 mb-1">Data scadenza:</label>
                                                                            <input type="date" name="custom_expiry_date"
                                                                                   min="{{ now()->addDay()->format('Y-m-d') }}"
                                                                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </label>

                                                            {{-- Opzione B: Unlimited --}}
                                                            <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                                                                   :class="action === 'set_unlimited' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                                                <input type="radio" name="action" value="set_unlimited"
                                                                       x-model="action" class="mt-1">
                                                                <div class="ml-3">
                                                                    <p class="font-medium text-gray-900">Abilita Storage Illimitato</p>
                                                                    <p class="text-sm text-gray-600 mt-1">
                                                                        La scuola avrà storage illimitato permanente.
                                                                    </p>
                                                                </div>
                                                            </label>

                                                            {{-- Opzione C: Reset --}}
                                                            <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50"
                                                                   :class="action === 'reset_to_base' ? 'border-purple-500 bg-purple-50' : 'border-gray-200'">
                                                                <input type="radio" name="action" value="reset_to_base"
                                                                       x-model="action" class="mt-1">
                                                                <div class="ml-3">
                                                                    <p class="font-medium text-gray-900">Reset a Quota Base (5GB)</p>
                                                                    @if(($school['storage_info']['used_bytes'] / 1024 / 1024 / 1024) > 5)
                                                                        <p class="text-sm text-yellow-600 mt-1 font-semibold">
                                                                            ⚠️ ATTENZIONE: La scuola sta usando più di 5GB. Upload verranno bloccati!
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                            </label>

                                                            {{-- Note Admin --}}
                                                            <div>
                                                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                                                    Motivazione (opzionale):
                                                                </label>
                                                                <textarea name="admin_note" rows="3"
                                                                          placeholder="Es: Upgrade per cliente VIP, Testing, ecc..."
                                                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Footer Modal --}}
                                                    <div class="bg-gray-50 px-6 py-4 flex justify-end space-x-3">
                                                        <button type="button" @click="showModal = false"
                                                                class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                                                            Annulla
                                                        </button>
                                                        <button type="submit"
                                                                class="px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-lg hover:from-rose-600 hover:to-purple-700">
                                                            Conferma Modifica
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</x-super-admin-layout>
```

---

#### 7.7 Routes Super Admin

**Modifiche a `routes/web.php`:**

```php
// Super Admin Routes (existing group, add storage routes)
Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'super_admin'])->group(function () {

    // ... existing super admin routes ...

    // TASK #11 - FASE 7: Storage Management
    Route::prefix('schools/storage')->name('schools.storage.')->group(function () {
        Route::get('/', [App\Http\Controllers\SuperAdmin\SuperAdminSchoolStorageController::class, 'index'])
            ->name('index');

        Route::post('/{school}/update', [App\Http\Controllers\SuperAdmin\SuperAdminSchoolStorageController::class, 'update'])
            ->name('update');

        Route::get('/{school}/audit-log', [App\Http\Controllers\SuperAdmin\SuperAdminSchoolStorageController::class, 'auditLog'])
            ->name('audit-log');
    });
});
```

---

#### 7.8 Test Cases FASE 7

**Test da eseguire:**

**1. Authorization**
- [ ] Non-super-admin tenta accedere → 403 Forbidden
- [ ] Super admin accede → dashboard visibile

**2. Dashboard Overview**
- [ ] Statistiche globali corrette (totale, normal, warning, full, unlimited)
- [ ] Tabella mostra tutte le scuole ordinate per usage% desc
- [ ] Progress bar colori corretti (verde/giallo/rosso)
- [ ] Badge status corretti

**3. Aggiungi GB**
- [ ] Aggiungi 10GB permanente → quota passa da 5 a 15GB, expires_at=NULL
- [ ] Aggiungi 20GB con scadenza 1 anno → expires_at = now()+1year
- [ ] Aggiungi 5GB con data custom → expires_at = data selezionata
- [ ] Verifica audit log creato correttamente

**4. Set Unlimited**
- [ ] Abilita unlimited → storage_unlimited=TRUE, expires_at=NULL
- [ ] Dashboard mostra badge "Unlimited" viola
- [ ] Upload non controlla quota

**5. Reset to Base**
- [ ] Reset scuola con usage 3GB → quota=5GB (ok)
- [ ] Reset scuola con usage 7GB → warning mostrato, upload bloccati dopo reset

**6. Validation**
- [ ] GB negativi → validation error
- [ ] GB > 1000 → validation error
- [ ] Data custom passata → validation error
- [ ] Action non valida → validation error

**7. Audit Log**
- [ ] Ogni modifica crea entry in storage_quota_audit_log
- [ ] Audit log mostra: admin, data, old state, new state, note
- [ ] Pagina audit log funziona e mostra history

**8. Edge Cases**
- [ ] Modifiche concurrent (2 super admin modificano stessa scuola) → race condition?
- [ ] Scuola eliminata dopo apertura modal → error handling
- [ ] Network error durante submit → rollback transaction

**9. Performance**
- [ ] Dashboard con 100+ scuole carica < 1sec
- [ ] Query N+1 non presenti
- [ ] Pagination audit log funziona

**10. Security**
- [ ] Super admin A non può vedere super admin B's email in logs
- [ ] SQL injection su admin_note → escaped
- [ ] XSS su admin_note → sanitized

**✅ Checklist FASE 7:**
- [ ] Controller SuperAdminSchoolStorageController creato
- [ ] Model StorageQuotaAuditLog creato
- [ ] Migration audit log table creata
- [ ] Dashboard view implementata
- [ ] Modal gestione storage funzionante
- [ ] Routes configurate
- [ ] Middleware super_admin applicato
- [ ] Tutti i 10 test cases passano
- [ ] Audit log traccia tutte le modifiche
- [ ] UI responsive e intuitiva

---

## 📦 FILE COINVOLTI (RIEPILOGO COMPLETO)

### Database
- **NEW:** `database/migrations/2026_01_24_HHMMSS_add_storage_quota_to_schools.php`
- **NEW (FASE 7):** `database/migrations/2026_01_24_HHMMSS_create_storage_quota_audit_log_table.php`

### Models
- **MODIFY:** `app/Models/School.php`
  - Aggiunti 5 campi a $fillable
  - Aggiunti 3 campi a $casts
  - Aggiunti 7 accessors/helpers

- **NEW (FASE 7):** `app/Models/StorageQuotaAuditLog.php`
  - Audit trail modifiche quota
  - Relationships: school(), superAdmin()
  - Accessor: action_description

### Services
- **NEW:** `app/Services/StorageQuotaService.php` (300+ lines)
  - 10 metodi pubblici
  - Cache management
  - Logging completo

### Controllers
- **MODIFY:** `app/Http/Controllers/Admin/MediaGalleryController.php`
  - Constructor injection StorageQuotaService
  - Pre-upload quota check
  - Post-upload/delete usage update

- **NEW:** `app/Http/Controllers/Admin/BillingController.php`
  - storage() method
  - purchaseStorage() method

### Requests
- **MODIFY:** `app/Http/Requests/UploadMediaGalleryRequest.php`
  - Custom validation rule per quota

### Views
- **MODIFY:** `resources/views/admin/dashboard.blade.php`
  - Storage usage widget

- **NEW:** `resources/views/admin/billing/storage.blade.php`
  - Storage upgrade page
  - 4 pricing plans

### Routes
- **MODIFY:** `routes/web.php`
  - 2 nuove routes billing

---

## 💰 PRICING MODEL FINALE

| Piano | Storage | Prezzo | Tipo | Scadenza |
|-------|---------|--------|------|----------|
| **Base** | 5 GB | Gratis | Incluso | Permanente |
| **Plus** | 20 GB | €9.99/mese | Abbonamento | Rinnovo mensile |
| **Pro** | 50 GB | €19.99/mese | Abbonamento | Rinnovo mensile |
| **Unlimited** | ∞ | €49.99/mese | Abbonamento | Rinnovo mensile |

**Note:**
- Base: 5GB inclusi per tutte le scuole (gratis)
- Upgrade temporaneo: +GB per 1 anno (one-time payment)
- Abbonamento mensile: rinnovo automatico tramite PayPal
- Unlimited: flag `storage_unlimited=TRUE`, no check quota

---

## 📊 LOGGING & MONITORING

### Eventi Loggati

```php
// 1. Upload bloccato per quota
Log::warning('Storage quota exceeded', [
    'school_id' => $school->id,
    'current_usage_gb' => X,
    'file_size_mb' => Y,
    'quota_gb' => Z
]);

// 2. Warning threshold (80%)
Log::warning('Storage warning threshold reached', [
    'school_id' => $school->id,
    'usage_percent' => 85
]);

// 3. Purchase storage
Log::info('Additional storage purchased', [
    'school_id' => $school->id,
    'plan_type' => 'pro',
    'additional_gb' => 45,
    'payment_method' => 'paypal'
]);

// 4. Quota expired
Log::info('Storage quota expired, resetting to base', [
    'school_id' => $school->id,
    'old_quota_gb' => 50,
    'new_quota_gb' => 5
]);

// 5. Cache update
Log::info('Storage cache updated', [
    'school_id' => $school->id,
    'storage_used_gb' => 3.45
]);
```

---

## 🚀 DEPLOY CHECKLIST

### Pre-Deploy
- [ ] Test locale completo (tutti i 10 test cases)
- [ ] Code review service e controllers
- [ ] Verify migration SQL sintatticamente corretto
- [ ] Check performance calcolo usage con DB reale
- [ ] Verify UI responsive (mobile/tablet/desktop)
- [ ] Git status pulito (no uncommitted changes)

### Deploy Steps
1. [ ] Commit code: `git add . && git commit -m "✨ feat: TASK #11 - Sistema quota storage gallerie"`
2. [ ] Push GitHub: `git push origin main`
3. [ ] SSH VPS: `ssh root@157.230.114.252`
4. [ ] Pull code: `cd /var/www/danzafacile && git pull`
5. [ ] **BACKUP DATABASE:** `mysqldump danzafacile > backup_pre_task11_$(date +%Y%m%d_%H%M%S).sql`
6. [ ] Run migration: `php artisan migrate --force`
7. [ ] Verify migration output (controllare righe processate)
8. [ ] Check DB manualmente: `mysql -e "DESCRIBE schools;" danzafacile`
9. [ ] Clear caches: `php artisan optimize:clear && php artisan optimize`
10. [ ] Restart services: `systemctl restart php8.4-fpm nginx`
11. [ ] Test produzione: Upload media, check dashboard widget
12. [ ] Monitor logs: `tail -f storage/logs/laravel.log`

### Post-Deploy
- [ ] Test upgrade flow con scuola reale
- [ ] Verify quota check funziona correttamente
- [ ] Monitor performance (query time, cache hit rate)
- [ ] Update `docs/BUG_FIXES_ROADMAP.md`: Task #11 ✅ COMPLETED
- [ ] Update questo file: Status = COMPLETED

### Rollback Plan (se necessario)
```bash
# 1. SSH su VPS
ssh root@157.230.114.252
cd /var/www/danzafacile

# 2. Restore database
mysql danzafacile < backup_pre_task11_YYYYMMDD_HHMMSS.sql

# 3. Rollback code
git reset --hard HEAD~1

# 4. Clear caches
php artisan optimize:clear

# 5. Restart services
systemctl restart php8.4-fpm nginx

# 6. Verify rollback
curl -I https://www.danzafacile.it
tail -50 storage/logs/laravel.log
```

---

## ⏱️ STIMA TEMPO FINALE

| Fase | Tempo Stimato | Tempo Effettivo | Note |
|------|---------------|-----------------|------|
| **FASE 1** | 40-50 min | - | Migration + Model |
| **FASE 2** | 50-60 min | - | Service Layer |
| **FASE 3** | 30-40 min | - | Controllers |
| **FASE 4** | 40-50 min | - | UI Components |
| **FASE 5** | 10-15 min | - | Routes |
| **FASE 6** | 30-40 min | - | Testing |
| **Deploy** | 15-20 min | - | Production deploy |
| **TOTALE** | **215-275 min** | - | **3.5 - 4.5 ore** |

**Stima Finale:** **3-4 ore** (come da roadmap originale) ✅

---

## 📝 NOTE IMPLEMENTAZIONE

_Usare questa sezione per note durante l'implementazione:_

### Problemi Riscontrati
-

### Soluzioni Applicate
-

### Modifiche al Piano Originale
-

### Performance Metrics
-

---

**File:** `docs/TASK_11_STORAGE_QUOTA_IMPLEMENTATION.md`
**Versione:** 1.0.0
**Ultimo aggiornamento:** 2026-01-25
**Maintainer:** Claude Code AI Assistant
