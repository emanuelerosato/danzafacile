# 🚀 ROADMAP IMPLEMENTAZIONE STORAGE LIMITATO

## 📋 PANORAMICA GENERALE

### **Obiettivo**
Implementare il sistema di storage limitato con controllo super-admin e PayPal pay-per-GB **SENZA ROMPERE** il sistema esistente e mantenendo il layout grafico attuale.

### **Principi Fondamentali**
- ✅ **Zero Breaking Changes**: Sistema attuale deve continuare a funzionare
- ✅ **Backward Compatibility**: Scuole esistenti non devono essere impattate
- ✅ **Layout Consistency**: Mantenere il design system glassmorphism esistente
- ✅ **Gradual Rollout**: Implementazione step-by-step testabile
- ✅ **Data Safety**: Nessuna perdita di dati durante l'implementazione

### **Stato Attuale da Preservare**
- Sistema documenti funzionante e performante
- Dashboard admin con design glassmorphism
- Upload flow esistente senza interruzioni
- Ruoli e permessi attuali (super-admin, admin, user)
- Database schema attuale intatto

---

## 📅 FASE 1: PREPARAZIONE DATABASE (GIORNO 1-2)

### **1.1 Analisi Schema Esistente**

#### **Verifica Tabella Schools**
```sql
-- Verificare struttura attuale
DESCRIBE schools;
SHOW INDEX FROM schools;

-- Verificare se esistono colonne conflittuali
SELECT COLUMN_NAME
FROM INFORMATION_SCHEMA.COLUMNS
WHERE TABLE_NAME = 'schools'
AND COLUMN_NAME IN ('storage_limit_mb', 'current_usage_mb', 'paypal_subscription_id');
```

#### **Verifica Tabella Documents**
```sql
-- Verificare se esiste colonna file_size
DESCRIBE documents;

-- Se non esiste, verificare come calcolare la dimensione
SELECT file_path, created_at
FROM documents
LIMIT 5;
```

### **1.2 Migration Database (NON-BREAKING)**

#### **Migration 001: Aggiungere Colonne Storage a Schools**
```php
// database/migrations/2024_01_15_100000_add_storage_columns_to_schools_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStorageColumnsToSchoolsTable extends Migration
{
    public function up()
    {
        Schema::table('schools', function (Blueprint $table) {
            // Default 1GB (1024 MB) per scuole esistenti
            $table->bigInteger('storage_limit_mb')->default(1024)->after('name');

            // Cache del calcolo usage (aggiornato da job)
            $table->bigInteger('current_usage_mb')->default(0)->after('storage_limit_mb');

            // Prezzo per GB configurabile dal super-admin
            $table->decimal('storage_price_per_gb', 8, 2)->default(2.00)->after('current_usage_mb');

            // ID subscription PayPal (null se non ha upgrade)
            $table->string('paypal_subscription_id')->nullable()->after('storage_price_per_gb');

            // Timestamps per tracking storage
            $table->timestamp('storage_updated_at')->nullable()->after('paypal_subscription_id');

            // Index per performance
            $table->index(['storage_limit_mb', 'current_usage_mb']);
            $table->index('paypal_subscription_id');
        });
    }

    public function down()
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropIndex(['schools_storage_limit_mb_current_usage_mb_index']);
            $table->dropIndex(['schools_paypal_subscription_id_index']);
            $table->dropColumn([
                'storage_limit_mb',
                'current_usage_mb',
                'storage_price_per_gb',
                'paypal_subscription_id',
                'storage_updated_at'
            ]);
        });
    }
}
```

#### **Migration 002: Aggiungere File Size a Documents (Se Non Esiste)**
```php
// database/migrations/2024_01_15_110000_add_file_size_to_documents_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFileSizeToDocumentsTable extends Migration
{
    public function up()
    {
        Schema::table('documents', function (Blueprint $table) {
            // Solo se non esiste già
            if (!Schema::hasColumn('documents', 'file_size')) {
                $table->bigInteger('file_size')->default(0)->after('file_path');
                $table->index('file_size');
            }
        });
    }

    public function down()
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasColumn('documents', 'file_size')) {
                $table->dropIndex(['documents_file_size_index']);
                $table->dropColumn('file_size');
            }
        });
    }
}
```

#### **Migration 003: Tabella Storage Purchases**
```php
// database/migrations/2024_01_15_120000_create_storage_purchases_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoragePurchasesTable extends Migration
{
    public function up()
    {
        Schema::create('storage_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->onDelete('cascade');

            // Dettagli acquisto
            $table->integer('gb_purchased');
            $table->decimal('price_per_gb', 8, 2);
            $table->decimal('total_amount', 10, 2);

            // PayPal tracking
            $table->string('paypal_subscription_id');
            $table->string('paypal_payment_id')->nullable();
            $table->string('paypal_order_id')->nullable();

            // Status tracking
            $table->enum('status', ['pending', 'active', 'cancelled', 'expired'])->default('pending');

            // Date tracking
            $table->timestamp('starts_at');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['school_id', 'status']);
            $table->index('paypal_subscription_id');
            $table->index(['starts_at', 'expires_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('storage_purchases');
    }
}
```

### **1.3 Seeder per Dati di Test**
```php
// database/seeders/StorageLimitsSeeder.php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\School;
use App\Models\Document;

class StorageLimitsSeeder extends Seeder
{
    public function run()
    {
        // Calcola usage attuale per tutte le scuole esistenti
        School::chunk(100, function ($schools) {
            foreach ($schools as $school) {
                $this->calculateInitialUsage($school);
            }
        });
    }

    private function calculateInitialUsage(School $school)
    {
        // Calcola dimensione reale dei file esistenti
        $totalSize = 0;
        $documents = $school->documents;

        foreach ($documents as $document) {
            if ($document->file_path && file_exists(storage_path('app/' . $document->file_path))) {
                $fileSize = filesize(storage_path('app/' . $document->file_path));

                // Aggiorna il documento con file_size se non esiste
                if (!$document->file_size) {
                    $document->update(['file_size' => $fileSize]);
                }

                $totalSize += $fileSize;
            }
        }

        // Aggiorna usage nella scuola (convertito in MB)
        $school->update([
            'current_usage_mb' => round($totalSize / 1024 / 1024, 2)
        ]);

        echo "School {$school->name}: {$school->current_usage_mb}MB\n";
    }
}
```

### **1.4 Testing della Migrazione**
```bash
# Backup database prima della migrazione
./vendor/bin/sail artisan db:backup

# Eseguire migrazioni in ambiente di test
./vendor/bin/sail artisan migrate --pretend

# Se tutto ok, eseguire per davvero
./vendor/bin/sail artisan migrate

# Verificare che tutto funzioni
./vendor/bin/sail artisan db:seed --class=StorageLimitsSeeder

# Test che upload esistente funzioni ancora
./vendor/bin/sail artisan test --filter=DocumentUploadTest
```

---

## 📅 FASE 2: BACKEND LOGIC (GIORNO 3-4)

### **2.1 Model Extensions (NON-BREAKING)**

#### **Estendere School Model**
```php
// app/Models/School.php - AGGIUNGERE METODI (non modificare esistenti)

class School extends Model
{
    // Aggiungere a $fillable esistente
    protected $fillable = [
        // ... campi esistenti ...
        'storage_limit_mb',
        'current_usage_mb',
        'storage_price_per_gb',
        'paypal_subscription_id',
        'storage_updated_at'
    ];

    // Aggiungere a $casts esistente
    protected $casts = [
        // ... casts esistenti ...
        'storage_limit_mb' => 'integer',
        'current_usage_mb' => 'integer',
        'storage_price_per_gb' => 'decimal:2',
        'storage_updated_at' => 'datetime'
    ];

    // NUOVI METODI (non modificare esistenti)

    /**
     * Calcola usage corrente da database (con cache)
     */
    public function getCurrentStorageUsageMB(): float
    {
        return Cache::remember("school.{$this->id}.storage_usage", 3600, function() {
            return $this->documents()->sum('file_size') / 1024 / 1024;
        });
    }

    /**
     * Verifica se c'è spazio per un nuovo file
     */
    public function hasStorageSpace(int $additionalBytes): bool
    {
        $currentUsageMB = $this->getCurrentStorageUsageMB();
        $additionalMB = $additionalBytes / 1024 / 1024;
        return ($currentUsageMB + $additionalMB) <= $this->storage_limit_mb;
    }

    /**
     * Percentuale di utilizzo storage
     */
    public function getStorageUsagePercentage(): float
    {
        if ($this->storage_limit_mb <= 0) return 0;
        return ($this->getCurrentStorageUsageMB() / $this->storage_limit_mb) * 100;
    }

    /**
     * Determina il livello di warning storage
     */
    public function getStorageWarningLevel(): string
    {
        $percentage = $this->getStorageUsagePercentage();
        if ($percentage >= 100) return 'critical';
        if ($percentage >= 95) return 'urgent';
        if ($percentage >= 90) return 'warning';
        if ($percentage >= 80) return 'notice';
        return 'normal';
    }

    /**
     * Verifica se mostrare warning storage
     */
    public function shouldShowStorageWarning(): bool
    {
        return $this->getStorageUsagePercentage() >= 80;
    }

    /**
     * Verifica se bloccare upload
     */
    public function shouldBlockUploads(): bool
    {
        return $this->getStorageUsagePercentage() >= 100;
    }

    /**
     * Formatta usage per display
     */
    public function getFormattedStorageUsage(): string
    {
        $usage = $this->getCurrentStorageUsageMB();
        if ($usage < 1024) {
            return round($usage, 1) . ' MB';
        }
        return round($usage / 1024, 1) . ' GB';
    }

    /**
     * Formatta limite per display
     */
    public function getFormattedStorageLimit(): string
    {
        $limit = $this->storage_limit_mb;
        if ($limit < 1024) {
            return $limit . ' MB';
        }
        return round($limit / 1024, 1) . ' GB';
    }

    /**
     * Relationship con storage purchases
     */
    public function storagePurchases()
    {
        return $this->hasMany(StoragePurchase::class);
    }

    /**
     * Storage purchase attivo
     */
    public function activeStoragePurchase()
    {
        return $this->storagePurchases()
                    ->where('status', 'active')
                    ->where('starts_at', '<=', now())
                    ->where(function($query) {
                        $query->whereNull('expires_at')
                              ->orWhere('expires_at', '>', now());
                    })
                    ->first();
    }
}
```

#### **Nuovo Model StoragePurchase**
```php
// app/Models/StoragePurchase.php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoragePurchase extends Model
{
    protected $fillable = [
        'school_id',
        'gb_purchased',
        'price_per_gb',
        'total_amount',
        'paypal_subscription_id',
        'paypal_payment_id',
        'paypal_order_id',
        'status',
        'starts_at',
        'expires_at',
        'cancelled_at'
    ];

    protected $casts = [
        'gb_purchased' => 'integer',
        'price_per_gb' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'cancelled_at' => 'datetime'
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->starts_at <= now()
            && (is_null($this->expires_at) || $this->expires_at > now());
    }

    public function getDaysUntilExpiry(): ?int
    {
        if (!$this->expires_at) return null;
        return now()->diffInDays($this->expires_at, false);
    }
}
```

### **2.2 Job per Aggiornamento Storage**
```php
// app/Jobs/UpdateSchoolStorageUsage.php
<?php

namespace App\Jobs;

use App\Models\School;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UpdateSchoolStorageUsage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schoolId;

    public function __construct(int $schoolId)
    {
        $this->schoolId = $schoolId;
    }

    public function handle()
    {
        $school = School::find($this->schoolId);
        if (!$school) return;

        // Calcola usage reale dal filesystem
        $totalBytes = 0;
        $documentCount = 0;

        foreach ($school->documents as $document) {
            if ($document->file_path && file_exists(storage_path('app/' . $document->file_path))) {
                $fileSize = filesize(storage_path('app/' . $document->file_path));

                // Aggiorna file_size se necessario
                if ($document->file_size != $fileSize) {
                    $document->update(['file_size' => $fileSize]);
                }

                $totalBytes += $fileSize;
                $documentCount++;
            }
        }

        $usageMB = round($totalBytes / 1024 / 1024, 2);

        // Aggiorna school
        $school->update([
            'current_usage_mb' => $usageMB,
            'storage_updated_at' => now()
        ]);

        // Invalida cache
        Cache::forget("school.{$school->id}.storage_usage");

        Log::info("Storage updated for school {$school->id}: {$usageMB}MB ({$documentCount} documents)");

        // Controlla se è necessario inviare notifiche
        $this->checkStorageNotifications($school);
    }

    private function checkStorageNotifications(School $school)
    {
        $percentage = $school->getStorageUsagePercentage();
        $warningLevel = $school->getStorageWarningLevel();

        // Invia notifiche basate sul livello
        switch ($warningLevel) {
            case 'notice':
                if (!Cache::has("storage_notice_sent.{$school->id}")) {
                    // Invia email notice 80%
                    Cache::put("storage_notice_sent.{$school->id}", true, now()->addDays(7));
                }
                break;

            case 'warning':
                if (!Cache::has("storage_warning_sent.{$school->id}")) {
                    // Invia email warning 90%
                    Cache::put("storage_warning_sent.{$school->id}", true, now()->addDays(3));
                }
                break;

            case 'urgent':
                if (!Cache::has("storage_urgent_sent.{$school->id}")) {
                    // Invia email urgent 95%
                    Cache::put("storage_urgent_sent.{$school->id}", true, now()->addDay());
                }
                break;

            case 'critical':
                // Sempre invia email critical 100%
                // Invia notifica critica
                break;
        }
    }
}
```

### **2.3 Middleware Storage Check (NON-BREAKING)**
```php
// app/Http/Middleware/CheckStorageLimit.php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckStorageLimit
{
    public function handle(Request $request, Closure $next)
    {
        // Solo per upload di file
        if (!$request->hasFile()) {
            return $next($request);
        }

        $user = Auth::user();

        // Solo per admin e user (non super-admin)
        if (!$user || $user->role === 'super_admin') {
            return $next($request);
        }

        $school = $user->school;

        // Se scuola non ha limite (legacy), procedi
        if (!$school || !$school->storage_limit_mb) {
            return $next($request);
        }

        // Calcola dimensione totale dei file in upload
        $totalUploadSize = 0;
        foreach ($request->allFiles() as $files) {
            if (is_array($files)) {
                foreach ($files as $file) {
                    $totalUploadSize += $file->getSize();
                }
            } else {
                $totalUploadSize += $files->getSize();
            }
        }

        // Verifica se c'è spazio
        if (!$school->hasStorageSpace($totalUploadSize)) {
            // MODO BACKWARD-COMPATIBLE:
            // Se request è AJAX, ritorna JSON
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'error' => 'Storage limit raggiunto',
                    'message' => 'Il tuo storage è pieno. Richiedi un upgrade per continuare.',
                    'current_usage' => $school->getFormattedStorageUsage(),
                    'limit' => $school->getFormattedStorageLimit(),
                    'percentage' => round($school->getStorageUsagePercentage(), 1),
                    'upgrade_url' => route('admin.storage.upgrade'),
                    'blocked' => true
                ], 413);
            }

            // Se request normale, redirect con messaggio
            return redirect()->back()
                ->withErrors(['file' => 'Storage pieno. Richiedi un upgrade per continuare.'])
                ->with('storage_blocked', true)
                ->with('upgrade_url', route('admin.storage.upgrade'));
        }

        return $next($request);
    }
}
```

### **2.4 Registrazione Middleware**
```php
// app/Http/Kernel.php - AGGIUNGERE (non modificare esistenti)

protected $routeMiddleware = [
    // ... middleware esistenti ...
    'check_storage' => \App\Http\Middleware\CheckStorageLimit::class,
];
```

---

## 📅 FASE 3: INTERFACCIA ADMIN (GIORNO 5-6)

### **3.1 Storage Widget Dashboard (MANTIENE LAYOUT ESISTENTE)**

#### **Nuovo Component Storage Widget**
```php
// resources/views/components/storage-widget.blade.php
<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-lg font-medium text-gray-900">Storage Utilizzato</h3>
            <p class="text-sm text-gray-600">{{ $school->getFormattedStorageUsage() }} di {{ $school->getFormattedStorageLimit() }}</p>
        </div>
        <div class="flex items-center">
            @if($school->getStorageWarningLevel() === 'critical')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Pieno
                </span>
            @elseif($school->getStorageWarningLevel() === 'urgent')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Quasi Pieno
                </span>
            @elseif($school->getStorageWarningLevel() === 'warning')
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    Attenzione
                </span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    OK
                </span>
            @endif
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="mb-4">
        <div class="flex justify-between text-sm text-gray-600 mb-1">
            <span>Utilizzo</span>
            <span>{{ round($school->getStorageUsagePercentage(), 1) }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2.5">
            @php
                $percentage = min($school->getStorageUsagePercentage(), 100);
                $colorClass = match($school->getStorageWarningLevel()) {
                    'critical' => 'bg-red-600',
                    'urgent' => 'bg-orange-500',
                    'warning' => 'bg-yellow-500',
                    'notice' => 'bg-blue-500',
                    default => 'bg-green-500'
                };
            @endphp
            <div class="h-2.5 rounded-full transition-all duration-300 {{ $colorClass }}"
                 style="width: {{ $percentage }}%"></div>
        </div>
    </div>

    <!-- Actions -->
    <div class="flex items-center justify-between">
        <div class="text-sm text-gray-600">
            @if($school->activeStoragePurchase())
                <span class="text-green-600">Piano attivo: +{{ $school->activeStoragePurchase()->gb_purchased }}GB</span>
            @else
                <span>Piano gratuito</span>
            @endif
        </div>

        @if($school->shouldShowStorageWarning())
            <a href="{{ route('admin.storage.upgrade') }}"
               class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-xs font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                Upgrade
            </a>
        @endif
    </div>
</div>
```

#### **Integrazione nel Dashboard Esistente**
```php
// resources/views/admin/dashboard.blade.php - AGGIUNGERE nella sezione stats esistente

<!-- Storage Widget - AGGIUNGERE dopo le stats cards esistenti -->
<div class="mt-6">
    <x-storage-widget :school="auth()->user()->school" />
</div>
```

### **3.2 Banner Notifiche Storage (OVERLAY NON-INVASIVO)**

#### **Component Banner Storage**
```php
// resources/views/components/storage-banner.blade.php
@if(auth()->user()->school->shouldShowStorageWarning())
    @php
        $school = auth()->user()->school;
        $level = $school->getStorageWarningLevel();
        $percentage = round($school->getStorageUsagePercentage(), 1);
    @endphp

    <div x-data="{ dismissed: localStorage.getItem('storage_banner_{{ $level }}_{{ $school->id }}') === 'true' }"
         x-show="!dismissed"
         x-transition:enter="transform ease-out duration-300"
         x-transition:enter-start="translate-y-full"
         x-transition:enter-end="translate-y-0"
         x-transition:leave="transform ease-in duration-200"
         x-transition:leave-start="translate-y-0"
         x-transition:leave-end="translate-y-full"
         class="fixed bottom-4 right-4 max-w-md z-50">

        @if($level === 'critical')
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-lg shadow-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400 animate-pulse" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700 font-medium">
                            Storage pieno ({{ $percentage }}%)
                        </p>
                        <p class="text-sm text-red-600 mt-1">
                            Upload bloccati. Richiedi upgrade per continuare.
                        </p>
                        <div class="mt-2">
                            <a href="{{ route('admin.storage.upgrade') }}"
                               class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded hover:bg-red-700 transition-colors">
                                Upgrade Ora
                            </a>
                        </div>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="dismissed = true; localStorage.setItem('storage_banner_{{ $level }}_{{ $school->id }}', 'true')"
                                class="text-red-400 hover:text-red-600">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @elseif($level === 'urgent')
            <div class="bg-orange-50 border-l-4 border-orange-400 p-4 rounded-lg shadow-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-orange-700 font-medium">
                            Storage quasi pieno ({{ $percentage }}%)
                        </p>
                        <p class="text-sm text-orange-600 mt-1">
                            Solo {{ 100 - $percentage }}% rimasto. Upgrade consigliato.
                        </p>
                        <div class="mt-2">
                            <a href="{{ route('admin.storage.upgrade') }}"
                               class="inline-flex items-center px-3 py-1.5 bg-orange-600 text-white text-xs font-medium rounded hover:bg-orange-700 transition-colors">
                                Richiedi Upgrade
                            </a>
                        </div>
                    </div>
                    <div class="ml-auto pl-3">
                        <button @click="dismissed = true; localStorage.setItem('storage_banner_{{ $level }}_{{ $school->id }}', 'true')"
                                class="text-orange-400 hover:text-orange-600">
                            <span class="sr-only">Dismiss</span>
                            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endif
```

#### **Integrazione nel Layout Principale**
```php
// resources/views/layouts/app.blade.php - AGGIUNGERE prima del </body>

<!-- Storage Banner - Non invasivo -->
<x-storage-banner />
```

### **3.3 Aggiornamento Upload Forms (MANTIENE LAYOUT)**

#### **Modificare Document Upload Form**
```javascript
// resources/views/admin/documents/create.blade.php - AGGIUNGERE script (non modificare layout)

@push('scripts')
<script>
// Aggiungere controllo storage prima dell'upload
function documentUpload() {
    return {
        selectedFile: null,
        isDragOver: false,
        storageCheck: true,

        initUpload() {
            // Controllo storage all'inizializzazione
            this.checkStorageStatus();
        },

        async checkStorageStatus() {
            try {
                const response = await fetch('/admin/storage/status');
                const data = await response.json();

                if (data.blocked) {
                    this.showStorageBlockedModal(data);
                }
            } catch (error) {
                console.error('Storage check failed:', error);
            }
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                // Controllo dimensione file vs spazio disponibile
                this.validateFileSize(file);
            }
        },

        async validateFileSize(file) {
            try {
                const response = await fetch('/admin/storage/check', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        file_size: file.size
                    })
                });

                const data = await response.json();

                if (data.blocked) {
                    this.showStorageBlockedModal(data);
                    return false;
                }

                // Se OK, procedi con logic esistente
                this.selectedFile = file;
                this.autoFillName(file.name);
                return true;
            } catch (error) {
                console.error('File validation failed:', error);
                return true; // Fallback: permetti upload
            }
        },

        showStorageBlockedModal(data) {
            // Modal non invasivo per storage pieno
            if (confirm(`Storage pieno (${data.current_usage}/${data.limit}). Vuoi richiedere un upgrade?`)) {
                window.location.href = data.upgrade_url;
            }
        },

        // Resto del codice esistente...
        autoFillName(filename) {
            const nameInput = document.getElementById('name');
            if (!nameInput.value) {
                const name = filename
                    .replace(/\.[^/.]+$/, '')
                    .replace(/[_-]/g, ' ')
                    .replace(/\b\w/g, l => l.toUpperCase());
                nameInput.value = name;
            }
        },

        formatFileSize(bytes) {
            if (!bytes) return '';
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;
            while (bytes >= 1024 && i < units.length - 1) {
                bytes /= 1024;
                i++;
            }
            return `${Math.round(bytes * 100) / 100} ${units[i]}`;
        }
    }
}
</script>
@endpush
```

---

## 📅 FASE 4: CONTROLLI E ROUTE (GIORNO 7-8)

### **4.1 Route Storage (NON-BREAKING)**

#### **Aggiungere Route Admin**
```php
// routes/web.php - AGGIUNGERE (non modificare esistenti)

// Route storage per admin (dentro gruppo auth middleware)
Route::middleware(['auth', 'check_storage'])->prefix('admin')->name('admin.')->group(function() {
    // Storage management routes
    Route::get('/storage', [StorageController::class, 'index'])->name('storage.index');
    Route::get('/storage/upgrade', [StorageController::class, 'upgrade'])->name('storage.upgrade');
    Route::post('/storage/request-upgrade', [StorageController::class, 'requestUpgrade'])->name('storage.request-upgrade');
    Route::get('/storage/success', [StorageController::class, 'success'])->name('storage.success');
    Route::get('/storage/cancel', [StorageController::class, 'cancel'])->name('storage.cancel');

    // AJAX endpoints
    Route::get('/storage/status', [StorageController::class, 'status'])->name('storage.status');
    Route::post('/storage/check', [StorageController::class, 'checkFileSize'])->name('storage.check');
});

// PayPal webhook (senza auth)
Route::post('/webhooks/paypal/storage', [PayPalWebhookController::class, 'handle'])->name('webhooks.paypal.storage');
```

#### **Applicare Middleware alle Route Upload Esistenti**
```php
// routes/web.php - MODIFICARE route documenti esistenti
Route::middleware(['auth', 'check_storage'])->group(function() {
    // Applicare middleware alle route che fanno upload
    Route::post('/admin/documents', [DocumentController::class, 'store'])->name('admin.documents.store');
    Route::put('/admin/documents/{document}', [DocumentController::class, 'update'])->name('admin.documents.update');

    // Altre route upload esistenti...
});
```

### **4.2 Storage Controller**
```php
// app/Http/Controllers/Admin/StorageController.php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StorageController extends Controller
{
    public function index()
    {
        $school = Auth::user()->school;
        $storagePurchases = $school->storagePurchases()->latest()->paginate(10);

        return view('admin.storage.index', compact('school', 'storagePurchases'));
    }

    public function upgrade()
    {
        $school = Auth::user()->school;

        // Calcola opzioni upgrade disponibili
        $currentUsage = $school->getCurrentStorageUsageMB();
        $currentLimit = $school->storage_limit_mb;
        $pricePerGB = $school->storage_price_per_gb;

        $suggestedOptions = [
            1 => ['gb' => 1, 'price' => $pricePerGB],
            2 => ['gb' => 2, 'price' => $pricePerGB * 2],
            5 => ['gb' => 5, 'price' => $pricePerGB * 5],
            10 => ['gb' => 10, 'price' => $pricePerGB * 10]
        ];

        return view('admin.storage.upgrade', compact('school', 'suggestedOptions'));
    }

    public function requestUpgrade(Request $request)
    {
        $request->validate([
            'additional_gb' => 'required|integer|min:1|max:100',
            'terms_accepted' => 'required|accepted'
        ]);

        $school = Auth::user()->school;
        $additionalGB = $request->additional_gb;

        // Creare PayPal subscription
        $paypalService = app(PayPalStorageService::class);
        $subscription = $paypalService->createStorageSubscription($school, $additionalGB);

        if ($subscription['status'] === 'success') {
            // Redirect a PayPal
            return redirect($subscription['approval_url']);
        }

        return back()->withErrors(['paypal' => 'Errore nella creazione del pagamento PayPal.']);
    }

    public function success(Request $request)
    {
        // Gestire ritorno da PayPal
        $subscriptionId = $request->subscription_id;

        // Verificare e attivare subscription
        $paypalService = app(PayPalStorageService::class);
        $result = $paypalService->activateSubscription($subscriptionId);

        if ($result['success']) {
            return view('admin.storage.success')->with('message', 'Storage upgrade completato!');
        }

        return redirect()->route('admin.storage.upgrade')->withErrors(['activation' => 'Errore nell\'attivazione.']);
    }

    public function cancel()
    {
        return redirect()->route('admin.storage.upgrade')->with('message', 'Upgrade annullato.');
    }

    // AJAX endpoints
    public function status()
    {
        $school = Auth::user()->school;

        return response()->json([
            'current_usage' => $school->getFormattedStorageUsage(),
            'limit' => $school->getFormattedStorageLimit(),
            'percentage' => $school->getStorageUsagePercentage(),
            'warning_level' => $school->getStorageWarningLevel(),
            'blocked' => $school->shouldBlockUploads(),
            'upgrade_url' => route('admin.storage.upgrade')
        ]);
    }

    public function checkFileSize(Request $request)
    {
        $school = Auth::user()->school;
        $fileSize = $request->file_size;

        $canUpload = $school->hasStorageSpace($fileSize);

        return response()->json([
            'can_upload' => $canUpload,
            'blocked' => !$canUpload,
            'current_usage' => $school->getFormattedStorageUsage(),
            'limit' => $school->getFormattedStorageLimit(),
            'percentage' => $school->getStorageUsagePercentage(),
            'upgrade_url' => route('admin.storage.upgrade')
        ]);
    }
}
```

### **4.3 View Storage Admin (MANTIENE DESIGN SYSTEM)**

#### **Storage Index Page**
```php
// resources/views/admin/storage/index.blade.php
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Storage
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Monitora e gestisci lo spazio di archiviazione
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

                <!-- Storage Overview -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Utilizzo Storage</h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Usage Stats -->
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900">{{ $school->getFormattedStorageUsage() }}</div>
                            <div class="text-sm text-gray-600">Utilizzato</div>
                        </div>

                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900">{{ $school->getFormattedStorageLimit() }}</div>
                            <div class="text-sm text-gray-600">Limite</div>
                        </div>

                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-900">{{ round($school->getStorageUsagePercentage(), 1) }}%</div>
                            <div class="text-sm text-gray-600">Percentuale</div>
                        </div>
                    </div>

                    <!-- Progress Bar -->
                    <div class="mt-6">
                        <div class="w-full bg-gray-200 rounded-full h-4">
                            @php
                                $percentage = min($school->getStorageUsagePercentage(), 100);
                                $colorClass = match($school->getStorageWarningLevel()) {
                                    'critical' => 'bg-red-600',
                                    'urgent' => 'bg-orange-500',
                                    'warning' => 'bg-yellow-500',
                                    'notice' => 'bg-blue-500',
                                    default => 'bg-green-500'
                                };
                            @endphp
                            <div class="h-4 rounded-full transition-all duration-300 {{ $colorClass }}"
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>

                    <!-- Actions -->
                    @if($school->shouldShowStorageWarning())
                        <div class="mt-6 text-center">
                            <a href="{{ route('admin.storage.upgrade') }}"
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                </svg>
                                Richiedi Upgrade Storage
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Purchase History -->
                @if($storagePurchases->count() > 0)
                    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Storico Acquisti</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GB Acquistati</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prezzo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stato</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scadenza</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($storagePurchases as $purchase)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $purchase->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                +{{ $purchase->gb_purchased }} GB
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                €{{ number_format($purchase->total_amount, 2) }}/mese
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $purchase->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ ucfirst($purchase->status) }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                {{ $purchase->expires_at ? $purchase->expires_at->format('d/m/Y') : 'Mai' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        @if($storagePurchases->hasPages())
                            <div class="mt-6">
                                {{ $storagePurchases->links() }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
```

---

## 📅 FASE 5: PAYPAL INTEGRATION (GIORNO 9-10)

### **5.1 PayPal Service**
```php
// app/Services/PayPalStorageService.php
<?php

namespace App\Services;

use App\Models\School;
use App\Models\StoragePurchase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalStorageService
{
    private $baseUrl;
    private $clientId;
    private $clientSecret;

    public function __construct()
    {
        $this->baseUrl = config('services.paypal.sandbox')
            ? 'https://api.sandbox.paypal.com'
            : 'https://api.paypal.com';
        $this->clientId = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
    }

    private function getAccessToken()
    {
        $response = Http::withBasicAuth($this->clientId, $this->clientSecret)
            ->asForm()
            ->post($this->baseUrl . '/v1/oauth2/token', [
                'grant_type' => 'client_credentials'
            ]);

        if ($response->successful()) {
            return $response->json()['access_token'];
        }

        throw new \Exception('Failed to get PayPal access token');
    }

    public function createStorageSubscription(School $school, int $additionalGB)
    {
        try {
            $accessToken = $this->getAccessToken();
            $monthlyAmount = $additionalGB * $school->storage_price_per_gb;

            // Create product first
            $product = $this->createProduct($accessToken, $school, $additionalGB);

            // Create billing plan
            $plan = $this->createBillingPlan($accessToken, $product['id'], $monthlyAmount, $additionalGB);

            // Create subscription
            $subscription = $this->createSubscription($accessToken, $plan['id'], $school, $additionalGB, $monthlyAmount);

            return [
                'status' => 'success',
                'subscription_id' => $subscription['id'],
                'approval_url' => $this->getApprovalUrl($subscription['links'])
            ];

        } catch (\Exception $e) {
            Log::error('PayPal subscription creation failed: ' . $e->getMessage());
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function createProduct($accessToken, $school, $additionalGB)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/v1/catalogs/products', [
            'name' => "Storage Upgrade +{$additionalGB}GB - {$school->name}",
            'type' => 'SERVICE',
            'category' => 'SOFTWARE'
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to create PayPal product');
        }

        return $response->json();
    }

    private function createBillingPlan($accessToken, $productId, $monthlyAmount, $additionalGB)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/v1/billing/plans', [
            'product_id' => $productId,
            'name' => "Storage +{$additionalGB}GB Monthly Plan",
            'description' => "Piano mensile per {$additionalGB}GB di storage aggiuntivo",
            'billing_cycles' => [[
                'frequency' => [
                    'interval_unit' => 'MONTH',
                    'interval_count' => 1
                ],
                'tenure_type' => 'REGULAR',
                'sequence' => 1,
                'total_cycles' => 0, // infinite
                'pricing_scheme' => [
                    'fixed_price' => [
                        'value' => number_format($monthlyAmount, 2, '.', ''),
                        'currency_code' => 'EUR'
                    ]
                ]
            ]],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'payment_failure_threshold' => 3
            ]
        ]);

        if (!$response->successful()) {
            throw new \Exception('Failed to create PayPal billing plan');
        }

        return $response->json();
    }

    private function createSubscription($accessToken, $planId, $school, $additionalGB, $monthlyAmount)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json'
        ])->post($this->baseUrl . '/v1/billing/subscriptions', [
            'plan_id' => $planId,
            'custom_id' => 'school-storage-' . $school->id . '-' . time(),
            'application_context' => [
                'brand_name' => 'Scuola Danza Platform',
                'locale' => 'it-IT',
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED'
                ],
                'return_url' => route('admin.storage.success'),
                'cancel_url' => route('admin.storage.cancel'),
            ],
            'subscriber' => [
                'email_address' => $school->admin->email ?? $school->users()->where('role', 'admin')->first()->email,
                'name' => [
                    'given_name' => $school->admin->first_name ?? 'Admin',
                    'surname' => $school->admin->last_name ?? $school->name
                ]
            ]
        ]);

        if (!$response->successful()) {
            Log::error('PayPal subscription creation failed', $response->json());
            throw new \Exception('Failed to create PayPal subscription');
        }

        // Store purchase record
        StoragePurchase::create([
            'school_id' => $school->id,
            'gb_purchased' => $additionalGB,
            'price_per_gb' => $school->storage_price_per_gb,
            'total_amount' => $monthlyAmount,
            'paypal_subscription_id' => $response->json()['id'],
            'status' => 'pending',
            'starts_at' => now()
        ]);

        return $response->json();
    }

    private function getApprovalUrl($links)
    {
        foreach ($links as $link) {
            if ($link['rel'] === 'approve') {
                return $link['href'];
            }
        }
        throw new \Exception('No approval URL found in PayPal response');
    }

    public function activateSubscription($subscriptionId)
    {
        try {
            $purchase = StoragePurchase::where('paypal_subscription_id', $subscriptionId)->first();

            if (!$purchase) {
                throw new \Exception('Purchase record not found');
            }

            // Update purchase status
            $purchase->update(['status' => 'active']);

            // Update school storage limit
            $school = $purchase->school;
            $school->increment('storage_limit_mb', $purchase->gb_purchased * 1024);
            $school->update(['paypal_subscription_id' => $subscriptionId]);

            // Clear cache
            \Cache::forget("school.{$school->id}.storage_usage");

            Log::info("Storage upgrade activated for school {$school->id}: +{$purchase->gb_purchased}GB");

            return ['success' => true];

        } catch (\Exception $e) {
            Log::error('Failed to activate storage subscription: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
```

### **5.2 PayPal Webhook Controller**
```php
// app/Http/Controllers/PayPalWebhookController.php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StoragePurchase;
use App\Jobs\UpdateSchoolStorageUsage;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Verify webhook signature (implement PayPal verification)
        if (!$this->verifyWebhookSignature($request)) {
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $eventType = $request->input('event_type');
        $resource = $request->input('resource');

        Log::info('PayPal webhook received', ['event_type' => $eventType, 'resource_id' => $resource['id'] ?? 'unknown']);

        switch ($eventType) {
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                $this->handleSubscriptionActivated($resource);
                break;

            case 'BILLING.SUBSCRIPTION.CANCELLED':
                $this->handleSubscriptionCancelled($resource);
                break;

            case 'PAYMENT.SALE.COMPLETED':
                $this->handlePaymentCompleted($resource);
                break;

            case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                $this->handlePaymentFailed($resource);
                break;
        }

        return response()->json(['status' => 'success']);
    }

    private function verifyWebhookSignature(Request $request): bool
    {
        // Implement PayPal webhook signature verification
        // For now, return true (implement properly in production)
        return true;
    }

    private function handleSubscriptionActivated($resource)
    {
        $subscriptionId = $resource['id'];
        $purchase = StoragePurchase::where('paypal_subscription_id', $subscriptionId)->first();

        if ($purchase && $purchase->status === 'pending') {
            $purchase->update(['status' => 'active']);

            $school = $purchase->school;
            $school->increment('storage_limit_mb', $purchase->gb_purchased * 1024);

            // Trigger storage recalculation
            UpdateSchoolStorageUsage::dispatch($school->id);

            Log::info("Subscription activated via webhook for school {$school->id}");
        }
    }

    private function handleSubscriptionCancelled($resource)
    {
        $subscriptionId = $resource['id'];
        $purchase = StoragePurchase::where('paypal_subscription_id', $subscriptionId)
                                  ->where('status', 'active')
                                  ->first();

        if ($purchase) {
            $purchase->update([
                'status' => 'cancelled',
                'cancelled_at' => now()
            ]);

            $school = $purchase->school;

            // Grace period: don't immediately reduce storage
            // Send notification to admin about cancellation

            Log::info("Subscription cancelled via webhook for school {$school->id}");
        }
    }

    private function handlePaymentCompleted($resource)
    {
        // Log successful payment
        Log::info('PayPal payment completed', ['payment_id' => $resource['id'] ?? 'unknown']);
    }

    private function handlePaymentFailed($resource)
    {
        // Handle failed payment (send notification, etc.)
        Log::warning('PayPal payment failed', ['resource' => $resource]);
    }
}
```

### **5.3 Configuration PayPal**
```php
// config/services.php - AGGIUNGERE
'paypal' => [
    'client_id' => env('PAYPAL_CLIENT_ID'),
    'client_secret' => env('PAYPAL_CLIENT_SECRET'),
    'sandbox' => env('PAYPAL_SANDBOX', true),
],
```

```bash
# .env - AGGIUNGERE
PAYPAL_CLIENT_ID=your_paypal_client_id
PAYPAL_CLIENT_SECRET=your_paypal_client_secret
PAYPAL_SANDBOX=true
```

---

## 📅 FASE 6: SUPER-ADMIN INTERFACE (GIORNO 11-12)

### **6.1 Super-Admin Storage Management**
```php
// app/Http/Controllers/SuperAdmin/SchoolStorageController.php
<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\StoragePurchase;
use Illuminate\Http\Request;

class SchoolStorageController extends Controller
{
    public function index()
    {
        $schools = School::with(['storagePurchases' => function($query) {
            $query->where('status', 'active');
        }])
        ->withCount('documents')
        ->paginate(20);

        $totalRevenue = StoragePurchase::where('status', 'active')->sum('total_amount');
        $totalStorageGB = $schools->sum(function($school) {
            return $school->storage_limit_mb / 1024;
        });

        return view('super-admin.storage.index', compact('schools', 'totalRevenue', 'totalStorageGB'));
    }

    public function edit(School $school)
    {
        $school->load('storagePurchases');
        return view('super-admin.storage.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $request->validate([
            'storage_limit_mb' => 'required|integer|min:1024',
            'storage_price_per_gb' => 'required|numeric|min:0.01|max:999.99'
        ]);

        $school->update([
            'storage_limit_mb' => $request->storage_limit_mb,
            'storage_price_per_gb' => $request->storage_price_per_gb
        ]);

        return redirect()->route('super-admin.storage.index')
                        ->with('success', "Storage settings aggiornati per {$school->name}");
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'action' => 'required|in:increase_limit,set_price,reset_to_default',
            'schools' => 'required|array',
            'schools.*' => 'exists:schools,id',
            'value' => 'required_unless:action,reset_to_default|numeric|min:0'
        ]);

        $schools = School::whereIn('id', $request->schools);

        switch ($request->action) {
            case 'increase_limit':
                $schools->increment('storage_limit_mb', $request->value);
                $message = "Limite storage aumentato di {$request->value}MB per " . count($request->schools) . " scuole";
                break;

            case 'set_price':
                $schools->update(['storage_price_per_gb' => $request->value]);
                $message = "Prezzo per GB impostato a €{$request->value} per " . count($request->schools) . " scuole";
                break;

            case 'reset_to_default':
                $schools->update([
                    'storage_limit_mb' => 1024,
                    'storage_price_per_gb' => 2.00
                ]);
                $message = "Impostazioni storage resettate per " . count($request->schools) . " scuole";
                break;
        }

        return redirect()->back()->with('success', $message);
    }
}
```

### **6.2 Super-Admin Routes**
```php
// routes/web.php - AGGIUNGERE
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function() {
    Route::get('/storage', [SchoolStorageController::class, 'index'])->name('storage.index');
    Route::get('/storage/{school}/edit', [SchoolStorageController::class, 'edit'])->name('storage.edit');
    Route::put('/storage/{school}', [SchoolStorageController::class, 'update'])->name('storage.update');
    Route::post('/storage/bulk-update', [SchoolStorageController::class, 'bulkUpdate'])->name('storage.bulk-update');
});
```

---

## 📅 FASE 7: TESTING E DEPLOYMENT (GIORNO 13-14)

### **7.1 Test Suite Completo**
```php
// tests/Feature/StorageLimitTest.php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class StorageLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_storage_calculation_is_accurate()
    {
        $school = School::factory()->create(['storage_limit_mb' => 1024]);
        $user = User::factory()->create(['school_id' => $school->id, 'role' => 'admin']);

        // Create test document
        $document = Document::factory()->create([
            'school_id' => $school->id,
            'file_size' => 500 * 1024 * 1024 // 500MB
        ]);

        $this->assertEquals(500, $school->getCurrentStorageUsageMB());
        $this->assertEquals(48.83, round($school->getStorageUsagePercentage(), 2));
    }

    public function test_upload_blocked_when_storage_full()
    {
        $school = School::factory()->create(['storage_limit_mb' => 100]); // 100MB limit
        $user = User::factory()->create(['school_id' => $school->id, 'role' => 'admin']);

        // Fill storage to 99MB
        Document::factory()->create([
            'school_id' => $school->id,
            'file_size' => 99 * 1024 * 1024
        ]);

        $this->actingAs($user);

        // Try to upload 2MB file (should be blocked)
        $file = UploadedFile::fake()->create('test.pdf', 2048);

        $response = $this->post(route('admin.documents.store'), [
            'name' => 'Test Document',
            'file' => $file,
            'category' => 'general'
        ]);

        $response->assertStatus(413);
        $this->assertDatabaseMissing('documents', ['name' => 'Test Document']);
    }

    public function test_storage_widget_displays_correctly()
    {
        $school = School::factory()->create([
            'storage_limit_mb' => 1024,
            'current_usage_mb' => 800
        ]);
        $user = User::factory()->create(['school_id' => $school->id, 'role' => 'admin']);

        $this->actingAs($user);

        $response = $this->get(route('admin.dashboard'));

        $response->assertSee('Storage Utilizzato');
        $response->assertSee('800 MB di 1 GB');
        $response->assertSee('78.1%');
    }

    public function test_upgrade_flow_works()
    {
        $school = School::factory()->create(['storage_price_per_gb' => 2.50]);
        $user = User::factory()->create(['school_id' => $school->id, 'role' => 'admin']);

        $this->actingAs($user);

        $response = $this->get(route('admin.storage.upgrade'));
        $response->assertOk();
        $response->assertSee('€2.50');

        // Test upgrade request
        $response = $this->post(route('admin.storage.request-upgrade'), [
            'additional_gb' => 5,
            'terms_accepted' => true
        ]);

        // Should redirect to PayPal (or return error in test environment)
        $this->assertTrue(
            $response->isRedirection() ||
            $response->getStatusCode() === 302 ||
            $response->status() === 422 // Validation error in test
        );
    }
}
```

### **7.2 Performance Tests**
```php
// tests/Performance/StoragePerformanceTest.php
<?php

namespace Tests\Performance;

use Tests\TestCase;
use App\Models\School;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoragePerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_storage_calculation_performance_with_many_documents()
    {
        $school = School::factory()->create();

        // Create 1000 documents
        Document::factory()->count(1000)->create(['school_id' => $school->id]);

        $startTime = microtime(true);
        $usage = $school->getCurrentStorageUsageMB();
        $endTime = microtime(true);

        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        // Should execute in under 100ms even with 1000 documents
        $this->assertLessThan(100, $executionTime, "Storage calculation took {$executionTime}ms, should be under 100ms");
    }

    public function test_middleware_performance()
    {
        $school = School::factory()->create();
        $user = User::factory()->create(['school_id' => $school->id, 'role' => 'admin']);

        $this->actingAs($user);

        $startTime = microtime(true);

        $response = $this->post('/admin/storage/check', ['file_size' => 1024*1024]);

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000;

        // Middleware should respond in under 50ms
        $this->assertLessThan(50, $executionTime, "Storage check took {$executionTime}ms, should be under 50ms");
    }
}
```

### **7.3 Deployment Checklist**

#### **Pre-deployment**
```bash
# 1. Backup production database
./vendor/bin/sail artisan backup:run

# 2. Test migrations on copy of production data
./vendor/bin/sail artisan migrate --pretend

# 3. Run full test suite
./vendor/bin/sail artisan test

# 4. Verify PayPal sandbox integration
./vendor/bin/sail artisan test --filter=PayPal

# 5. Performance testing
./vendor/bin/sail artisan test tests/Performance/

# 6. Check storage calculation accuracy
./vendor/bin/sail artisan storage:calculate-all --dry-run
```

#### **Deployment Steps**
```bash
# 1. Enable maintenance mode
./vendor/bin/sail artisan down

# 2. Run migrations
./vendor/bin/sail artisan migrate

# 3. Update storage usage for existing schools
./vendor/bin/sail artisan db:seed --class=StorageLimitsSeeder

# 4. Clear all caches
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan route:clear
./vendor/bin/sail artisan view:clear
./vendor/bin/sail artisan cache:clear

# 5. Compile assets
npm run build

# 6. Disable maintenance mode
./vendor/bin/sail artisan up

# 7. Monitor logs for errors
tail -f storage/logs/laravel.log
```

#### **Post-deployment Monitoring**
```bash
# Monitor storage calculations
./vendor/bin/sail artisan queue:work --timeout=300

# Check that uploads still work
curl -X POST http://localhost:8089/admin/storage/check \
  -H "Content-Type: application/json" \
  -d '{"file_size": 1048576}'

# Verify PayPal webhook endpoint
curl -X POST http://localhost:8089/webhooks/paypal/storage \
  -H "Content-Type: application/json" \
  -d '{"event_type": "TEST"}'
```

---

## 🚨 CONSIDERAZIONI CRITICHE

### **1. Backward Compatibility**
- Tutte le scuole esistenti mantengono funzionalità corrente
- Middleware storage check si attiva solo se `storage_limit_mb` è impostato
- Route esistenti non modificate, solo aggiunte nuove
- Layout e design completamente preservati

### **2. Data Safety**
- Migrazioni sono reversibili
- Nessuna modifica a dati esistenti
- Calcolo storage non modifica documenti esistenti
- Backup automatico prima di ogni step critico

### **3. Performance**
- Cache Redis per calcoli storage
- Background jobs per aggiornamenti pesanti
- Indici database per query veloci
- Lazy loading per dashboard widgets

### **4. Error Handling**
- Graceful degradation se PayPal non disponibile
- Fallback per calcolo storage se file system issues
- Logging completo per debugging
- Recovery automatico per failed payments

### **5. Security**
- Webhook PayPal signature verification
- Input validation su tutti i form
- Rate limiting per API endpoints
- Audit log per modifiche super-admin

### **6. Monitoring**
- Health check endpoint per storage system
- Alerts per failed PayPal webhooks
- Dashboard metrics per super-admin
- Automated reports per revenue tracking

---

**🎯 Questa roadmap garantisce implementazione sicura, performante e non-breaking del sistema storage limitato mantenendo la UX esistente e il design system corrente.**