# 🗺️ Services Map - DanzaFacile

**Versione:** 1.0.0
**Ultima modifica:** 2026-01-25
**Autore:** DanzaFacile Development Team
**Status:** ✅ Production Ready

---

## 📋 Table of Contents

1. [Overview](#-overview)
2. [Service Layer Architecture](#-service-layer-architecture)
3. [Core Services](#-core-services)
4. [Service Dependencies](#-service-dependencies)
5. [Usage Examples](#-usage-examples)
6. [Creating New Services](#-creating-new-services)

---

## 🎯 Overview

DanzaFacile implementa un **Service Layer Pattern** per separare business logic dai controller. Attualmente il sistema conta **11 servizi** organizzati per dominio funzionale.

### Services Overview

| Service | LOC | Purpose | Status |
|---------|-----|---------|--------|
| **StorageQuotaService** | 309 | Gestione quote storage scuole | ✅ Production |
| **PaymentService** | 250+ | Business logic pagamenti | ✅ Production |
| **FirebasePushService** | 280+ | Push notifications FCM | ✅ Production |
| **PayPalService** | 350+ | Integrazione PayPal API v2 | ✅ Production |
| **InvoiceService** | 200+ | Generazione PDF fatture | ✅ Production |
| **NotificationService** | 180+ | Orchestrazione notifiche | ✅ Production |
| **QRCodeService** | 120+ | Generazione QR codes | ✅ Production |
| **FileUploadService** | 150+ | Upload e validazione file | ✅ Production |
| **GuestRegistrationService** | 100+ | Registrazione ospiti eventi | ✅ Production |
| **CacheService** | 80+ | Cache management helper | ✅ Production |
| **DatabaseOptimizationService** | 60+ | Query optimization utilities | ✅ Production |

**Total:** ~2,200 lines of business logic code

---

## 🏗️ Service Layer Architecture

### Design Principles

```
┌─────────────────────────────────────────────────────────┐
│                    CONTROLLER LAYER                     │
├─────────────────────────────────────────────────────────┤
│  - HTTP request/response handling                       │
│  - Routing & authorization                              │
│  - Input validation (FormRequest)                       │
│  - Minimal business logic                               │
└─────────────────────────────────────────────────────────┘
                        ▼ Dependency Injection
┌─────────────────────────────────────────────────────────┐
│                     SERVICE LAYER                       │
├─────────────────────────────────────────────────────────┤
│  - Complex business logic                               │
│  - External API integrations                            │
│  - Multi-step operations                                │
│  - Data transformations                                 │
│  - Error handling & logging                             │
└─────────────────────────────────────────────────────────┘
                        ▼ Eloquent ORM
┌─────────────────────────────────────────────────────────┐
│                      MODEL LAYER                        │
├─────────────────────────────────────────────────────────┤
│  - Database access                                       │
│  - Relationships                                         │
│  - Scopes & query builders                              │
└─────────────────────────────────────────────────────────┘
```

### Service Characteristics

✅ **MUST:**
- Stateless (no instance state between requests)
- Single Responsibility (one domain per service)
- Dependency Injection (constructor injection)
- Exception handling with logging
- Return typed values (bool, object, array)

❌ **MUST NOT:**
- Direct HTTP access ($_GET, $_POST)
- Echo/print output
- Exit/die statements
- Session management (let controllers handle)

---

## 🔧 Core Services

### 1. StorageQuotaService

**Path:** `app/Services/StorageQuotaService.php`

**Purpose:** Gestione quote storage per gallerie media delle scuole

**Key Methods:**

```php
class StorageQuotaService
{
    /**
     * Calcola storage utilizzato (real-time query)
     */
    public function calculateUsage(School $school): int

    /**
     * Aggiorna cache storage_used_bytes
     */
    public function updateCache(School $school): int

    /**
     * Ottieni usage con cache (5 min TTL)
     */
    public function getUsage(School $school, bool $forceRefresh = false): int

    /**
     * Verifica se scuola può uploadare file
     */
    public function canUpload(School $school, int $fileSizeBytes): bool

    /**
     * Incrementa/decrementa usage dopo upload/delete
     */
    public function incrementUsage(School $school, int $fileSizeBytes): void
    public function decrementUsage(School $school, int $fileSizeBytes): void

    /**
     * Acquisto GB aggiuntivi
     */
    public function purchaseAdditionalStorage(
        School $school,
        int $additionalGB,
        bool $temporary = false
    ): bool

    /**
     * Abilita storage illimitato
     */
    public function enableUnlimited(School $school): void

    /**
     * Ottieni info complete per dashboard
     */
    public function getStorageInfo(School $school): array
}
```

**Dependencies:**
- `School` model
- `MediaItem` model
- `Log` facade
- `Cache` facade (implicit via model caching)

**Used By:**
- `MediaGalleryController::store()` - Verifica quota prima upload
- `MediaGalleryController::destroy()` - Decrementa usage dopo delete
- `SuperAdminSchoolStorageController::index()` - Dashboard storage super admin
- `AdminDashboardController::index()` - Widget storage admin

**Example Usage:**
```php
$storageService = app(StorageQuotaService::class);

// Check se può uploadare
if (!$storageService->canUpload($school, $fileSize)) {
    return redirect()->back()->with('error', 'Quota storage esaurita');
}

// Upload file...
Storage::put($path, $file);

// Update usage
$storageService->incrementUsage($school, $fileSize);
```

---

### 2. PaymentService

**Path:** `app/Services/PaymentService.php`

**Purpose:** Business logic gestione pagamenti eventi

**Key Methods:**

```php
class PaymentService
{
    /**
     * Crea record pagamento per registrazione evento
     */
    public function createPayment(
        EventRegistration $registration,
        string $paymentMethod = 'paypal'
    ): EventPayment

    /**
     * Completa pagamento e conferma registrazione
     */
    public function completePayment(
        EventPayment $payment,
        string $transactionId,
        array $gatewayResponse = []
    ): void

    /**
     * Rimborsa pagamento via PayPal API
     */
    public function refundPayment(
        EventPayment $payment,
        string $reason = ''
    ): bool

    /**
     * Gestisce pagamenti gratuiti (eventi free)
     */
    public function processFreePayment(EventRegistration $registration): EventPayment
}
```

**Dependencies:**
- `EventPayment` model
- `EventRegistration` model
- `Event` model
- `User` model
- `PayPalService` (per refund)
- `Mail` facade
- `DB` facade (transactions)
- `Log` facade

**Used By:**
- `PaymentController::createPayPalOrder()` - Creazione ordine PayPal
- `PaymentController::success()` - Conferma pagamento
- `AdminEventController::refund()` - Rimborso admin

**Example Usage:**
```php
$paymentService = app(PaymentService::class);

// Crea pagamento
$payment = $paymentService->createPayment($registration, 'paypal');

// Dopo conferma PayPal
$paymentService->completePayment($payment, $transactionId, $response);

// Rimborso
if ($paymentService->refundPayment($payment, 'Evento cancellato')) {
    // Success
}
```

---

### 3. FirebasePushService

**Path:** `app/Services/FirebasePushService.php`

**Purpose:** Invio push notifications tramite Firebase Cloud Messaging

**Key Methods:**

```php
class FirebasePushService
{
    /**
     * Invia notifica a singolo utente (tutti i suoi device)
     */
    public function sendToUser(
        int $userId,
        string $title,
        string $body,
        array $data = [],
        ?int $lessonId = null
    ): array // ['success' => bool, 'sent_count' => int, 'failed_tokens' => array]

    /**
     * Invia notifica multicast (multipli token)
     */
    public function sendMulticast(
        array $tokens,
        string $title,
        string $body,
        array $data = [],
        ?int $userId = null,
        ?int $lessonId = null
    ): array

    /**
     * Invia a topic Firebase
     */
    public function sendToTopic(
        string $topic,
        string $title,
        string $body,
        array $data = []
    ): bool

    /**
     * Test connessione Firebase
     */
    public function testConnection(): bool

    /**
     * Rimuove token non validi
     */
    public function removeInvalidTokens(array $invalidTokens): int
}
```

**Dependencies:**
- `FcmToken` model
- `NotificationLog` model
- `kreait/firebase-php` package
- `Log` facade

**Used By:**
- `NotificationService::sendLessonReminders()` - Reminder lezioni
- `Api\Mobile\v1\NotificationController::register()` - Registrazione token
- `Console\Commands\SendLessonReminders` - Cron job

**Example Usage:**
```php
$firebase = app(FirebasePushService::class);

// Invia a singolo utente
$result = $firebase->sendToUser(
    userId: $user->id,
    title: 'Lezione tra 1 ora',
    body: "Corso: {$lesson->course->name}",
    data: ['lesson_id' => $lesson->id]
);

// Invia a topic (broadcast)
$firebase->sendToTopic(
    topic: 'school_' . $schoolId,
    title: 'Nuovo evento!',
    body: 'Evento aperto alle iscrizioni'
);
```

---

### 4. PayPalService

**Path:** `app/Services/PayPalService.php`

**Purpose:** Integrazione PayPal API v2 (payments & refunds)

**Key Methods:**

```php
class PayPalService
{
    /**
     * Crea ordine PayPal
     */
    public function createOrder(
        float $amount,
        int $schoolId,
        array $customData = []
    ): array // ['id' => 'order_id', ...]

    /**
     * Cattura pagamento dopo approval
     */
    public function executePayment(string $token): array

    /**
     * Rimborsa pagamento
     */
    public function refundPayment(
        string $captureId,
        float $amount,
        string $reason = ''
    ): array

    /**
     * Ottieni dettagli ordine
     */
    public function getOrderDetails(string $orderId): array

    /**
     * Verifica webhook signature
     */
    public function verifyWebhookSignature(
        array $headers,
        string $payload,
        string $webhookId
    ): bool
}
```

**Dependencies:**
- `srmklive/paypal` package
- `Log` facade

**Used By:**
- `PaymentController::createPayPalOrder()` - Checkout flow
- `PaymentController::success()` - Conferma pagamento
- `PaymentController::webhook()` - IPN handler
- `PaymentService::refundPayment()` - Refund orchestration

**Example Usage:**
```php
$paypal = app(PayPalService::class);

// Crea ordine
$order = $paypal->createOrder(
    amount: 50.00,
    schoolId: $school->id,
    customData: ['event_id' => $event->id]
);

// Cattura dopo approval
$capture = $paypal->executePayment($token);

// Rimborso
$refund = $paypal->refundPayment(
    captureId: $payment->transaction_id,
    amount: 50.00,
    reason: 'Evento cancellato'
);
```

---

### 5. InvoiceService

**Path:** `app/Services/InvoiceService.php`

**Purpose:** Generazione PDF fatture e ricevute

**Key Methods:**

```php
class InvoiceService
{
    /**
     * Genera PDF fattura
     */
    public function generateInvoicePDF(Invoice $invoice): string // PDF path

    /**
     * Genera PDF ricevuta pagamento
     */
    public function generateReceiptPDF(Payment $payment): string // PDF path

    /**
     * Invia fattura via email
     */
    public function sendInvoiceEmail(Invoice $invoice): bool

    /**
     * Calcola totali fattura (subtotal, tax, total)
     */
    public function calculateTotals(Invoice $invoice): array
}
```

**Dependencies:**
- `Invoice` model
- `Payment` model
- `barryvdh/laravel-dompdf` package
- `Mail` facade
- `Storage` facade

**Used By:**
- `AdminInvoiceController::download()` - Download PDF
- `AdminInvoiceController::email()` - Invio email
- `AdminPaymentController::generateReceipt()` - Ricevuta pagamento

**Example Usage:**
```php
$invoiceService = app(InvoiceService::class);

// Genera PDF
$pdfPath = $invoiceService->generateInvoicePDF($invoice);

// Download
return response()->download($pdfPath);

// Invia email
$invoiceService->sendInvoiceEmail($invoice);
```

---

### 6. NotificationService

**Path:** `app/Services/NotificationService.php`

**Purpose:** Orchestrazione notifiche (email + push)

**Key Methods:**

```php
class NotificationService
{
    /**
     * Invia reminder lezioni (cron job)
     */
    public function sendLessonReminders(): array // Stats

    /**
     * Notifica nuova registrazione evento
     */
    public function notifyEventRegistration(EventRegistration $registration): void

    /**
     * Notifica pagamento completato
     */
    public function notifyPaymentCompleted(Payment $payment): void

    /**
     * Notifica documento approvato/rifiutato
     */
    public function notifyDocumentStatus(Document $document): void
}
```

**Dependencies:**
- `FirebasePushService`
- `Mail` facade
- `NotificationPreference` model
- `Lesson` model
- `User` model

**Used By:**
- `Console\Commands\SendLessonReminders` - Cron job
- `PaymentController::success()` - Conferma pagamento
- `AdminDocumentController::approve()` - Approvazione documenti

**Example Usage:**
```php
$notificationService = app(NotificationService::class);

// Cron job reminder
$stats = $notificationService->sendLessonReminders();
// ['sent' => 15, 'failed' => 0, 'skipped' => 3]

// Notifica manuale
$notificationService->notifyPaymentCompleted($payment);
```

---

### 7. QRCodeService

**Path:** `app/Services/QRCodeService.php`

**Purpose:** Generazione QR codes per presenze e check-in

**Key Methods:**

```php
class QRCodeService
{
    /**
     * Genera QR code per lezione
     */
    public function generateLessonQR(Lesson $lesson): string // SVG

    /**
     * Genera QR code per evento
     */
    public function generateEventQR(Event $event): string // SVG

    /**
     * Decodifica QR code check-in
     */
    public function decodeCheckInQR(string $qrData): array // ['type', 'id']

    /**
     * Verifica validità QR code
     */
    public function validateQR(string $qrData, string $expectedType): bool
}
```

**Dependencies:**
- `simplesoftwareio/simple-qrcode` package
- `Lesson` model
- `Event` model

**Used By:**
- `AdminAttendanceController::show()` - Display QR lezione
- `AdminEventController::show()` - Display QR evento
- `Api\Mobile\v1\AttendanceController::checkIn()` - Scan QR mobile

**Example Usage:**
```php
$qrService = app(QRCodeService::class);

// Genera QR lezione
$qrSvg = $qrService->generateLessonQR($lesson);

// In view
<div class="qr-code">
    {!! $qrSvg !!}
</div>

// Decode scan
$decoded = $qrService->decodeCheckInQR($scannedData);
// ['type' => 'lesson', 'id' => 123]
```

---

### 8. FileUploadService

**Path:** `app/Services/FileUploadService.php`

**Purpose:** Upload file con validazione e processing

**Key Methods:**

```php
class FileUploadService
{
    /**
     * Upload file con validazione
     */
    public function upload(
        UploadedFile $file,
        string $directory,
        array $validationRules = []
    ): array // ['path' => '...', 'size' => 12345, 'mime' => '...']

    /**
     * Upload immagine con resize
     */
    public function uploadImage(
        UploadedFile $file,
        string $directory,
        int $maxWidth = 1920,
        int $maxHeight = 1080
    ): array

    /**
     * Valida file prima upload
     */
    public function validateFile(
        UploadedFile $file,
        array $rules
    ): bool // throws ValidationException

    /**
     * Elimina file e cleanup
     */
    public function deleteFile(string $path): bool
}
```

**Dependencies:**
- `intervention/image` package
- `Storage` facade
- `Validator` facade

**Used By:**
- `MediaGalleryController::store()` - Upload media
- `AdminEventController::store()` - Upload event image
- `StudentController::uploadDocument()` - Upload documenti

**Example Usage:**
```php
$fileService = app(FileUploadService::class);

// Upload generico
$result = $fileService->upload(
    file: $request->file('document'),
    directory: 'documents/' . $schoolId,
    validationRules: ['max:5120', 'mimes:pdf,doc,docx']
);

// Upload immagine con resize
$result = $fileService->uploadImage(
    file: $request->file('photo'),
    directory: 'events/' . $schoolId,
    maxWidth: 1920,
    maxHeight: 1080
);

// $result = ['path' => 'events/1/photo.jpg', 'size' => 234567, ...]
```

---

### 9. GuestRegistrationService

**Path:** `app/Services/GuestRegistrationService.php`

**Purpose:** Registrazione ospiti a eventi pubblici (senza account)

**Key Methods:**

```php
class GuestRegistrationService
{
    /**
     * Registra ospite a evento
     */
    public function registerGuest(
        Event $event,
        array $guestData
    ): EventRegistration

    /**
     * Crea account temporaneo per ospite
     */
    public function createGuestAccount(array $data): User

    /**
     * Verifica se email ospite già registrata
     */
    public function isGuestAlreadyRegistered(
        Event $event,
        string $email
    ): bool

    /**
     * Converti ospite in utente registrato
     */
    public function convertGuestToUser(User $guest, string $password): User
}
```

**Dependencies:**
- `Event` model
- `EventRegistration` model
- `User` model
- `Hash` facade

**Used By:**
- `EventController::registerGuest()` - Registrazione pubblica
- `AdminEventController::guestList()` - Lista ospiti admin

**Example Usage:**
```php
$guestService = app(GuestRegistrationService::class);

// Registra ospite
$registration = $guestService->registerGuest(
    event: $event,
    guestData: [
        'name' => 'Mario Rossi',
        'email' => 'mario@example.com',
        'phone' => '1234567890',
    ]
);

// Converti in user registrato (dopo evento)
$user = $guestService->convertGuestToUser($guest, 'password123');
```

---

### 10. CacheService

**Path:** `app/Services/CacheService.php`

**Purpose:** Helper per cache management

**Key Methods:**

```php
class CacheService
{
    /**
     * Cache dashboard stats per scuola
     */
    public function cacheDashboardStats(
        School $school,
        int $ttl = 300
    ): array

    /**
     * Invalida cache scuola
     */
    public function invalidateSchoolCache(School $school): void

    /**
     * Cache query results con key dinamica
     */
    public function cacheQuery(
        string $key,
        callable $callback,
        int $ttl = 3600
    ): mixed

    /**
     * Flush cache per tag
     */
    public function flushTag(string $tag): bool
}
```

**Dependencies:**
- `Cache` facade
- `School` model

**Used By:**
- `AdminDashboardController` - Cache stats
- `StorageQuotaService` - Cache usage
- `Api\*` controllers - Cache API responses

**Example Usage:**
```php
$cacheService = app(CacheService::class);

// Cache dashboard stats
$stats = $cacheService->cacheDashboardStats($school, ttl: 300);

// Cache query custom
$courses = $cacheService->cacheQuery(
    key: "school_{$schoolId}_courses",
    callback: fn() => Course::where('active', true)->get(),
    ttl: 3600
);
```

---

### 11. DatabaseOptimizationService

**Path:** `app/Services/DatabaseOptimizationService.php`

**Purpose:** Query optimization e N+1 prevention utilities

**Key Methods:**

```php
class DatabaseOptimizationService
{
    /**
     * Eager load relationships per prevenire N+1
     */
    public function eagerLoadRelations(
        $query,
        array $relations
    ): $query

    /**
     * Analizza query per N+1 problems
     */
    public function analyzeQueryPerformance(callable $callback): array

    /**
     * Ottimizza query con chunk
     */
    public function chunkQuery(
        $query,
        int $chunkSize,
        callable $callback
    ): void

    /**
     * Crea index su tabella
     */
    public function createIndex(
        string $table,
        array $columns,
        string $name = null
    ): bool
}
```

**Dependencies:**
- `DB` facade
- `Schema` facade
- `Log` facade

**Used By:**
- Development/Debugging (manual use)
- Performance optimization tasks
- Database migrations

**Example Usage:**
```php
$dbService = app(DatabaseOptimizationService::class);

// Analyze N+1
$stats = $dbService->analyzeQueryPerformance(function() {
    $payments = Payment::all();
    foreach($payments as $payment) {
        echo $payment->user->name; // N+1?
    }
});

// $stats = ['queries' => 101, 'time' => 1.234, 'warnings' => ['N+1 detected']]
```

---

## 🔗 Service Dependencies

### Dependency Graph

```
PaymentService
├─ depends on → PayPalService
├─ depends on → InvoiceService (future)
└─ depends on → NotificationService

NotificationService
├─ depends on → FirebasePushService
└─ depends on → Mail facade

MediaGalleryController
├─ depends on → StorageQuotaService
└─ depends on → FileUploadService

FileUploadService
└─ depends on → StorageQuotaService (future integration)

GuestRegistrationService
└─ depends on → PaymentService (per eventi a pagamento)
```

### External Package Dependencies

| Service | Package | Version | Purpose |
|---------|---------|---------|---------|
| FirebasePushService | kreait/laravel-firebase | ^6.1 | FCM integration |
| PayPalService | srmklive/paypal | ^3.0 | PayPal API v2 |
| InvoiceService | barryvdh/laravel-dompdf | ^3.1 | PDF generation |
| FileUploadService | intervention/image | ^3.11 | Image processing |
| QRCodeService | simplesoftwareio/simple-qrcode | ^4.2 | QR generation |

---

## 💡 Usage Examples

### Example 1: Complete Payment Flow

```php
// 1. User invia form pagamento
public function store(PaymentRequest $request)
{
    $paypalService = app(PayPalService::class);
    $paymentService = app(PaymentService::class);

    // Crea ordine PayPal
    $order = $paypalService->createOrder(
        amount: $request->amount,
        schoolId: auth()->user()->school_id,
        customData: ['event_id' => $request->event_id]
    );

    // Crea record pagamento
    $payment = $paymentService->createPayment(
        registration: $registration,
        paymentMethod: 'paypal'
    );

    return response()->json(['order_id' => $order['id']]);
}

// 2. Callback dopo approval PayPal
public function success(Request $request)
{
    $paypalService = app(PayPalService::class);
    $paymentService = app(PaymentService::class);
    $notificationService = app(NotificationService::class);

    // Cattura pagamento
    $capture = $paypalService->executePayment($request->token);

    // Completa payment record
    $paymentService->completePayment(
        payment: $payment,
        transactionId: $capture['id'],
        gatewayResponse: $capture
    );

    // Notifica utente
    $notificationService->notifyPaymentCompleted($payment);

    return redirect()->route('events.show', $event)->with('success', 'Pagamento completato');
}
```

### Example 2: Media Upload with Storage Quota

```php
public function store(Request $request)
{
    $storageService = app(StorageQuotaService::class);
    $fileService = app(FileUploadService::class);
    $school = auth()->user()->school;

    // Valida quota PRIMA dell'upload
    $fileSize = $request->file('photo')->getSize();
    if (!$storageService->canUpload($school, $fileSize)) {
        return redirect()->back()->with('error', 'Quota storage esaurita. Acquista spazio aggiuntivo.');
    }

    // Upload file
    $result = $fileService->uploadImage(
        file: $request->file('photo'),
        directory: "galleries/{$school->id}",
        maxWidth: 1920
    );

    // Salva in database
    $mediaItem = MediaItem::create([
        'media_gallery_id' => $request->gallery_id,
        'filename' => basename($result['path']),
        'file_path' => $result['path'],
        'file_size' => $result['size'],
        'mime_type' => $result['mime'],
    ]);

    // Aggiorna usage
    $storageService->incrementUsage($school, $fileSize);

    return redirect()->back()->with('success', 'Foto caricata');
}
```

### Example 3: Push Notification Flow

```php
public function sendLessonReminders()
{
    $firebase = app(FirebasePushService::class);
    $cacheService = app(CacheService::class);

    // Cache query risultati (15 min)
    $lessons = $cacheService->cacheQuery(
        key: 'upcoming_lessons_' . now()->format('Y-m-d-H'),
        callback: fn() => Lesson::with(['course', 'enrollments.user'])
            ->whereBetween('start_time', [now()->addMinutes(50), now()->addMinutes(70)])
            ->get(),
        ttl: 900
    );

    $stats = ['sent' => 0, 'failed' => 0];

    foreach ($lessons as $lesson) {
        foreach ($lesson->enrollments as $enrollment) {
            $user = $enrollment->user;

            // Check preference notifiche
            if (!$user->notificationPreference?->lesson_reminders) {
                continue;
            }

            // Invia push
            $result = $firebase->sendToUser(
                userId: $user->id,
                title: 'Lezione tra 1 ora',
                body: "Corso: {$lesson->course->name}",
                data: [
                    'lesson_id' => $lesson->id,
                    'course_id' => $lesson->course_id,
                    'start_time' => $lesson->start_time->toISOString(),
                ]
            );

            $result['success'] ? $stats['sent']++ : $stats['failed']++;
        }
    }

    Log::info('Lesson reminders sent', $stats);
    return $stats;
}
```

---

## 🛠️ Creating New Services

### Service Template

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

/**
 * NewFeatureService
 *
 * Purpose: Descrizione chiara dello scopo del servizio
 *
 * Usage:
 * $service = app(NewFeatureService::class);
 * $result = $service->methodName($param);
 */
class NewFeatureService
{
    /**
     * Constructor - dependency injection
     */
    public function __construct(
        private OtherService $otherService,
    ) {}

    /**
     * Main method description
     *
     * @param Model $model
     * @param array $data
     * @return bool|object Success status or result object
     * @throws \Exception On critical errors
     */
    public function mainMethod($model, array $data)
    {
        try {
            DB::beginTransaction();

            // Business logic here

            DB::commit();

            Log::info('Operation completed', [
                'model_id' => $model->id,
                'data' => $data,
            ]);

            return true;

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Operation failed', [
                'model_id' => $model->id,
                'error' => $e->getMessage(),
            ]);

            throw $e; // OR return false, dipende dal use case
        }
    }

    /**
     * Helper method (private if internal only)
     */
    private function helperMethod($param)
    {
        // Implementation
    }
}
```

### Service Checklist

Quando crei un nuovo service:

- [ ] **Nome:** Suffisso `Service` (es: `PaymentService`, non `PaymentHelper`)
- [ ] **Location:** `app/Services/` directory
- [ ] **Constructor:** Dependency injection per altri servizi/packages
- [ ] **Stateless:** NO instance variables che mantengono state
- [ ] **Typed:** Return types chiari (bool, object, array)
- [ ] **Exceptions:** Try-catch con logging
- [ ] **Logging:** Log::info per success, Log::error per failures
- [ ] **Transactions:** DB::transaction() per operazioni multi-step
- [ ] **Documentation:** PHPDoc con `@param`, `@return`, `@throws`
- [ ] **Tests:** Unit test per ogni metodo pubblico
- [ ] **SERVICES_MAP.md:** Aggiorna questa documentazione!

### When to Create a Service

✅ **Create service quando:**
- Business logic complessa (>20 righe)
- Integrazione external API
- Riutilizzo logic in multipli controller
- Operazioni multi-step che richiedono transaction
- Heavy processing (image, PDF, export)

❌ **NON creare service per:**
- Simple CRUD (usa Resource Controller + Model)
- Single database query (usa Model scope)
- Formatting/presentation logic (usa View o Accessor)
- One-time utility functions (usa Helper file)

---

## 📚 References

### Internal Documentation

- [ARCHITECTURE.md](ARCHITECTURE.md) - System architecture overview
- [MULTI_TENANT_GUIDE.md](MULTI_TENANT_GUIDE.md) - Multi-tenant patterns
- [docs/api/](api/) - API documentation

### External Resources

- [Laravel Service Container](https://laravel.com/docs/12.x/container)
- [Dependency Injection](https://laravel.com/docs/12.x/providers)
- [Service Layer Pattern](https://martinfowler.com/eaaCatalog/serviceLayer.html)

---

**Versione:** 1.0.0
**Ultimo aggiornamento:** 2026-01-25
**Maintainer:** DanzaFacile Development Team
