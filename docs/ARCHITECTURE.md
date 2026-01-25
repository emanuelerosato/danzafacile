# 🏛️ DanzaFacile - System Architecture

**Versione:** 1.0.0
**Ultima modifica:** 2026-01-25
**Autore:** DanzaFacile Development Team
**Status:** ✅ Production Ready

---

## 📋 Table of Contents

1. [System Overview](#-system-overview)
2. [Technology Stack](#-technology-stack)
3. [Architecture Layers](#-architecture-layers)
4. [Design Patterns](#-design-patterns)
5. [Architecture Decision Records (ADR)](#-architecture-decision-records-adr)
6. [Data Flow](#-data-flow)
7. [External Integrations](#-external-integrations)
8. [Security Architecture](#-security-architecture)
9. [Scalability & Performance](#-scalability--performance)
10. [Future Evolution](#-future-evolution)

---

## 🎯 System Overview

DanzaFacile è un **sistema gestionale SaaS multi-tenant** per scuole di danza, costruito con architettura **monolitica modulare** su stack Laravel.

### High-Level Architecture

```
┌────────────────────────────────────────────────────────────────┐
│                        FRONTEND LAYER                          │
├────────────────────────────────────────────────────────────────┤
│  Blade Templates + Alpine.js + Tailwind CSS v4               │
│  └─ Server-Side Rendering (SSR) per SEO                       │
└────────────────────────────────────────────────────────────────┘
                           ▲
                           │ HTTP/HTTPS
                           ▼
┌────────────────────────────────────────────────────────────────┐
│                      APPLICATION LAYER                         │
├────────────────────────────────────────────────────────────────┤
│  Laravel 12 Framework                                          │
│  ├─ Controllers (MVC)                                          │
│  ├─ Middleware (Auth, CORS, CSP, Multi-Tenant)                │
│  ├─ Policies (Authorization)                                   │
│  ├─ Requests (Validation)                                      │
│  └─ Routes (Web + API REST)                                    │
└────────────────────────────────────────────────────────────────┘
                           ▲
                           │ Dependency Injection
                           ▼
┌────────────────────────────────────────────────────────────────┐
│                       SERVICE LAYER                            │
├────────────────────────────────────────────────────────────────┤
│  Business Logic Services                                       │
│  ├─ PaymentService (pagamenti e fatturazione)                 │
│  ├─ StorageQuotaService (gestione storage)                    │
│  ├─ FirebasePushService (notifiche push)                      │
│  ├─ PayPalService (integrazione PayPal)                       │
│  ├─ InvoiceService (generazione PDF)                          │
│  └─ ... (11 servizi totali)                                   │
└────────────────────────────────────────────────────────────────┘
                           ▲
                           │ Eloquent ORM
                           ▼
┌────────────────────────────────────────────────────────────────┐
│                        DATA LAYER                              │
├────────────────────────────────────────────────────────────────┤
│  Eloquent Models (30+ models)                                  │
│  ├─ Global Scopes (Multi-Tenant Isolation)                    │
│  ├─ Relationships (BelongsTo, HasMany, etc.)                  │
│  ├─ Accessors/Mutators                                         │
│  └─ Events & Observers                                         │
└────────────────────────────────────────────────────────────────┘
                           ▲
                           │ MySQL Driver
                           ▼
┌────────────────────────────────────────────────────────────────┐
│                     PERSISTENCE LAYER                          │
├────────────────────────────────────────────────────────────────┤
│  MySQL 8.4.7 (Relational Database)                             │
│  Redis 7.0 (Cache + Sessions + Queues)                        │
│  File Storage (Local + S3-compatible future)                  │
└────────────────────────────────────────────────────────────────┘
                           ▲
                           │ External APIs
                           ▼
┌────────────────────────────────────────────────────────────────┐
│                   EXTERNAL INTEGRATIONS                        │
├────────────────────────────────────────────────────────────────┤
│  Firebase Cloud Messaging (Push Notifications)                │
│  PayPal API v2 (Payment Gateway)                              │
│  SMTP (Email Notifications)                                    │
│  Google reCAPTCHA (Spam Protection)                           │
└────────────────────────────────────────────────────────────────┘
```

### System Characteristics

| Caratteristica | Valore |
|----------------|--------|
| **Architecture Type** | Monolithic Modular (Laravel MVC + Service Layer) |
| **Multi-Tenancy** | Single Database, Row-Level Isolation via `school_id` |
| **Authentication** | Laravel Sanctum (Token-Based) |
| **Authorization** | Laravel Policies (Role-Based) |
| **Frontend** | Server-Side Rendering (Blade + Alpine.js) |
| **API** | RESTful API (/api/mobile/v1/*) |
| **Database** | MySQL 8.4.7 (Relational) |
| **Cache** | Redis 7.0 |
| **Queue** | Laravel Queue Worker (systemd service) |
| **File Storage** | Local Filesystem (future S3) |

---

## 🛠️ Technology Stack

### Backend Core

| Tecnologia | Versione | Scopo |
|------------|----------|-------|
| **PHP** | 8.2+ | Runtime language |
| **Laravel** | 12.x | Framework MVC |
| **Laravel Sanctum** | 4.2+ | API Authentication |
| **MySQL** | 8.4.7 | Primary database |
| **Redis** | 7.0 | Cache, Sessions, Queues |
| **Nginx** | 1.28.0 | Web server (production) |
| **PHP-FPM** | 8.4.11 | Process manager (production) |

### Frontend Stack

| Tecnologia | Versione | Scopo |
|------------|----------|-------|
| **Blade Templates** | Laravel 12 | Server-side templating |
| **Alpine.js** | 3.x | Reactive UI components |
| **Tailwind CSS** | v4 | Utility-first CSS |
| **Vite** | 6.x | Build tool & HMR |
| **Heroicons** | - | SVG icon library |

### Core Packages (Composer)

```json
{
  "kreait/laravel-firebase": "^6.1",           // Firebase integration
  "srmklive/paypal": "^3.0",                   // PayPal API v2
  "barryvdh/laravel-dompdf": "^3.1",          // PDF generation
  "intervention/image": "^3.11",               // Image processing
  "simplesoftwareio/simple-qrcode": "^4.2",   // QR code generation
  "maatwebsite/excel": "^3.1",                // Excel export
  "google/recaptcha": "^1.3"                   // reCAPTCHA validation
}
```

### External Services

| Servizio | Provider | Scopo |
|----------|----------|-------|
| **Push Notifications** | Firebase Cloud Messaging | Mobile push |
| **Payment Gateway** | PayPal API v2 | Pagamenti online |
| **Email** | SMTP (configurable) | Transactional emails |
| **SSL/TLS** | Let's Encrypt | HTTPS encryption |
| **DNS** | DigitalOcean DNS | Domain management |

---

## 🏗️ Architecture Layers

### Layer 1: Presentation Layer (Frontend)

**Responsabilità:** Renderizzare UI, gestire interazioni utente, validazione client-side

**Tecnologie:**
- **Blade Templates**: SSR (Server-Side Rendering)
- **Alpine.js**: Reactive components (dropdown, modals, forms)
- **Tailwind CSS**: Styling
- **Vite**: Asset bundling & hot reload

**Pattern:**
- Component-based design (Blade components)
- Design system centralizzato (`CLAUDE.md` - sezione design system)
- Responsive mobile-first

**Esempio:**
```blade
<x-app-layout>
    <x-slot name="header">
        <h2>Dashboard</h2>
    </x-slot>

    <div x-data="{ open: false }">
        <!-- Alpine.js reactive component -->
    </div>
</x-app-layout>
```

### Layer 2: Application Layer (Controllers)

**Responsabilità:** Routing, request handling, response formatting, authorization

**Namespace Structure:**
```
app/Http/Controllers/
├── Admin/                      # Admin school controllers
│   ├── AdminStudentController.php
│   ├── AdminPaymentController.php
│   ├── AdminEventController.php
│   └── ...
├── SuperAdmin/                 # Super admin controllers
│   ├── SuperAdminSchoolController.php
│   └── SuperAdminSchoolStorageController.php
├── Student/                    # Student portal controllers
│   ├── StudentDashboardController.php
│   └── StudentTicketController.php
├── Api/                        # REST API controllers
│   ├── Mobile/
│   │   └── v1/
│   │       ├── AuthController.php
│   │       ├── Student/
│   │       │   ├── CourseController.php
│   │       │   └── LessonController.php
│   │       └── Notifications/
│   │           └── NotificationController.php
└── Public/                     # Public-facing controllers
    └── EventController.php
```

**Pattern:**
- **Resource Controllers**: CRUD operations standard
- **Policy Authorization**: `$this->authorize('view', $model)`
- **Form Request Validation**: `$request->validated()`
- **Service Injection**: Constructor dependency injection

**Esempio:**
```php
class AdminPaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService
    ) {
        $this->middleware(['auth', 'role:admin']);
    }

    public function store(PaymentRequest $request)
    {
        $this->authorize('create', Payment::class);

        $payment = $this->paymentService->processPayment(
            $request->validated()
        );

        return redirect()->back()->with('success', 'Pagamento registrato');
    }
}
```

### Layer 3: Service Layer (Business Logic)

**Responsabilità:** Business logic, orchestrazione, integrazione external APIs

**Services Map:**

| Service | File | Scopo |
|---------|------|-------|
| **PaymentService** | `app/Services/PaymentService.php` | Pagamenti e rimborsi |
| **StorageQuotaService** | `app/Services/StorageQuotaService.php` | Gestione quote storage |
| **FirebasePushService** | `app/Services/FirebasePushService.php` | Push notifications |
| **PayPalService** | `app/Services/PayPalService.php` | Integrazione PayPal API |
| **InvoiceService** | `app/Services/InvoiceService.php` | Generazione PDF fatture |
| **QRCodeService** | `app/Services/QRCodeService.php` | Generazione QR codes |
| **NotificationService** | `app/Services/NotificationService.php` | Email & push orchestration |
| **FileUploadService** | `app/Services/FileUploadService.php` | Upload file e validazione |
| **GuestRegistrationService** | `app/Services/GuestRegistrationService.php` | Registrazione ospiti eventi |
| **CacheService** | `app/Services/CacheService.php` | Cache management |
| **DatabaseOptimizationService** | `app/Services/DatabaseOptimizationService.php` | DB optimization |

**Pattern:**
- **Dependency Injection**: Services ricevono dependencies via constructor
- **Single Responsibility**: Ogni service ha uno scopo specifico
- **Stateless**: Services non mantengono state tra richieste
- **Exception Handling**: Catch & log errors, ritorna bool o object

**Esempio:**
```php
class StorageQuotaService
{
    public function canUpload(School $school, int $fileSizeBytes): bool
    {
        if ($school->storage_unlimited) {
            return true;
        }

        $currentUsage = $this->getUsage($school);
        $newTotal = $currentUsage + $fileSizeBytes;

        return $newTotal <= $school->storage_quota_bytes;
    }

    public function getUsage(School $school, bool $forceRefresh = false): int
    {
        // Implementazione con cache...
    }
}
```

### Layer 4: Data Layer (Models)

**Responsabilità:** Data access, relationships, scopes, business rules

**Model Organization:**

```
app/Models/
├── User.php                    # Core user model
├── School.php                  # Tenant root entity
├── Course.php                  # Corsi
├── Payment.php                 # Pagamenti
├── Event.php                   # Eventi
├── MediaItem.php               # Media galleries
├── Attendance.php              # Presenze
├── Document.php                # Documenti
└── Traits/
    └── HasSchoolScope.php      # Multi-tenant trait
```

**Pattern:**
- **Global Scopes**: Multi-tenant isolation automatica
- **Eloquent Relationships**: BelongsTo, HasMany, ManyToMany
- **Accessors/Mutators**: Business logic su attributi
- **Query Scopes**: Filtri riutilizzabili
- **Model Events**: Observers per side effects

**Esempio:**
```php
class Payment extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'school_id', 'amount', 'status'];

    protected static function booted(): void
    {
        // Global scope multi-tenant
        static::addGlobalScope('school', function (Builder $builder) {
            if (auth()->check() && auth()->user()->school_id) {
                $builder->where('school_id', auth()->user()->school_id);
            }
        });
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // Scopes
    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', self::STATUS_COMPLETED);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return '€ ' . number_format($this->amount, 2, ',', '.');
    }
}
```

### Layer 5: Persistence Layer (Database)

**Responsabilità:** Data storage, indexing, transactions

**Database Schema Overview:**

| Tabella | Records (avg) | Scopo |
|---------|---------------|-------|
| `schools` | 10-100 | Root tenant entity |
| `users` | 1K-10K | Utenti (admin + studenti) |
| `courses` | 100-1K | Corsi offerti |
| `course_enrollments` | 1K-10K | Iscrizioni studenti |
| `payments` | 5K-50K | Pagamenti |
| `events` | 100-1K | Eventi pubblici |
| `event_registrations` | 1K-10K | Registrazioni eventi |
| `media_items` | 10K-100K | File multimedia |
| `attendances` | 50K-500K | Presenze lezioni |
| `documents` | 1K-10K | Documenti utenti |

**Indexing Strategy:**
```sql
-- Composite indexes per multi-tenant queries
CREATE INDEX idx_school_created ON table_name (school_id, created_at);
CREATE INDEX idx_school_status ON table_name (school_id, status);

-- Foreign keys con cascade
ALTER TABLE table_name
    ADD CONSTRAINT fk_school
    FOREIGN KEY (school_id) REFERENCES schools(id)
    ON DELETE CASCADE;
```

**Cache Layer (Redis):**
```
Redis Structure:
├── Cache (Laravel cache)
├── Sessions (user sessions)
├── Queues (job queues)
└── Custom Keys
    ├── storage_usage:{school_id}
    ├── dashboard_stats:{school_id}
    └── notification_preferences:{user_id}
```

---

## 🎨 Design Patterns

### 1. Repository Pattern (Limited Use)

**Quando:** Queries complesse, riutilizzabili

**Dove:** Principalmente tramite Eloquent Query Scopes

**Esempio:**
```php
// Scope in Model (preferito)
public function scopeOverdue(Builder $query): void
{
    $query->where('due_date', '<', now())
          ->where('status', '!=', self::STATUS_COMPLETED);
}

// Usage
$overduePayments = Payment::overdue()->get();
```

### 2. Service Layer Pattern (Heavily Used)

**Quando:** Business logic complessa, integrazione external APIs

**Dove:** `app/Services/` (11 servizi)

**Esempio:** Vedi [Layer 3: Service Layer](#layer-3-service-layer-business-logic)

### 3. Policy Pattern (Authorization)

**Quando:** Verifica permission su risorse

**Dove:** `app/Policies/`

**Esempio:**
```php
class PaymentPolicy
{
    public function view(User $user, Payment $payment): bool
    {
        // Super admin vede tutto
        if ($user->role === 'super_admin') {
            return true;
        }

        // Admin/Student vede solo propria scuola
        return $user->school_id === $payment->school_id;
    }
}
```

### 4. Observer Pattern (Events)

**Quando:** Side effects su model events (created, updated, deleted)

**Dove:** `app/Observers/`

**Esempio:**
```php
class PaymentObserver
{
    public function created(Payment $payment): void
    {
        // Send email receipt
        Mail::to($payment->user)->send(new PaymentConfirmation($payment));

        // Log activity
        Log::info('Payment created', ['payment_id' => $payment->id]);
    }
}
```

### 5. Factory Pattern (Testing)

**Quando:** Generazione dati test

**Dove:** `database/factories/`

**Esempio:**
```php
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'school_id' => School::factory(),
            'user_id' => User::factory(),
            'amount' => $this->faker->randomFloat(2, 10, 1000),
            'status' => Payment::STATUS_COMPLETED,
        ];
    }
}
```

---

## 📜 Architecture Decision Records (ADR)

### ADR-001: Why Laravel Instead of Microservices?

**Decision:** Usare Laravel monolith invece di microservices architecture

**Context:**
- Team piccolo (1-2 developer)
- Dominio ben definito (gestione scuole danza)
- Deploy semplice richiesto
- Budget limitato

**Decision:**
Implementare architettura monolitica modulare con Laravel, organizzando codice in layer chiari (Controller → Service → Model) invece di microservices.

**Consequences:**
- ✅ **Pro:**
  - Deploy semplice (single VPS)
  - Development velocity alta
  - Debugging facile
  - Transaction ACID native
  - Costo server basso

- ❌ **Contro:**
  - Scalabilità verticale limitata
  - Impossibile scalare componenti singoli
  - Deployment all-or-nothing

**Status:** ✅ Accepted

**Alternative Considered:**
- Microservices (rejected: complessità eccessiva)
- Serverless (rejected: cold starts, costi imprevedibili)

---

### ADR-002: Why Multi-Tenant Single Database?

**Decision:** Usare single-database multi-tenancy con row-level isolation via `school_id`

**Context:**
- 10-100 scuole previste (non migliaia)
- Data isolation critica per sicurezza
- Backup semplificato richiesto
- Costi database contenuti

**Decision:**
Implementare multi-tenancy con:
- Single database MySQL
- Row-level isolation via `school_id` column
- Global scopes su tutti i modelli tenant
- Middleware per automatic context setup

**Consequences:**
- ✅ **Pro:**
  - Setup semplice
  - Backup centralizzato
  - Cross-school analytics facile (super admin)
  - Costi database bassi
  - Migrazioni schema centralized

- ❌ **Contro:**
  - Risk di data leak (mitigato con global scopes)
  - Impossibile customizzare schema per singola scuola
  - Query overhead (+1 WHERE clause)

**Status:** ✅ Accepted

**Alternative Considered:**
- Database per tenant (rejected: complessità backup, costi)
- Schema per tenant (rejected: migration hell)

---

### ADR-003: Why Laravel Sanctum Instead of Passport?

**Decision:** Usare Laravel Sanctum per API authentication

**Context:**
- API REST per Flutter mobile app
- Token-based auth richiesto
- NO OAuth2 third-party needed
- Semplicità setup priorità

**Decision:**
Laravel Sanctum con Personal Access Tokens (PAT) invece di OAuth2 Passport.

**Consequences:**
- ✅ **Pro:**
  - Setup veloce (5 minuti)
  - Token management built-in
  - Revoke tokens facile
  - Middleware semplice
  - Nessun OAuth complexity

- ❌ **Contro:**
  - NO OAuth2 support (non necessario per ora)
  - Meno features enterprise

**Status:** ✅ Accepted

**Alternative Considered:**
- Laravel Passport (rejected: overhead OAuth2 non necessario)
- JWT custom (rejected: reinventare la ruota)

---

### ADR-004: Why Firebase for Push Notifications?

**Decision:** Usare Firebase Cloud Messaging (FCM) per push notifications

**Context:**
- Flutter app richiede push notifications
- Cross-platform (iOS + Android) support necessario
- Free tier generoso
- Laravel integration available

**Decision:**
Firebase Cloud Messaging con `kreait/laravel-firebase` package.

**Consequences:**
- ✅ **Pro:**
  - Free tier generoso (unlimited messages)
  - Cross-platform support
  - Laravel integration stabile
  - Topic messaging per broadcast
  - Analytics built-in

- ❌ **Contro:**
  - Vendor lock-in Google
  - Richiede Firebase project setup

**Status:** ✅ Accepted

**Alternative Considered:**
- OneSignal (rejected: costi dopo 10K users)
- Pusher (rejected: costoso)
- Custom solution (rejected: complessità infrastruttura)

---

### ADR-005: Why PayPal Over Stripe?

**Decision:** Usare PayPal come primary payment gateway

**Context:**
- Mercato italiano richiede PayPal
- Studenti preferiscono PayPal (no carta richiesta)
- Integration Laravel disponibile

**Decision:**
PayPal API v2 con `srmklive/paypal` package come primary gateway.

**Consequences:**
- ✅ **Pro:**
  - Diffusione alta in Italia
  - Nessuna carta richiesta (PayPal balance)
  - Laravel package maturo
  - Sandbox testing

- ❌ **Contro:**
  - Fee più alte di Stripe (2.9% + €0.35 vs 1.5% + €0.25)
  - UX meno smooth (redirect required)

**Status:** ✅ Accepted

**Future:** Aggiungere Stripe come opzione alternativa (dual gateway)

**Alternative Considered:**
- Stripe (valido, ma meno diffuso in Italia)
- Nexi (complessità integration)

---

### ADR-006: Why Server-Side Rendering (Blade)?

**Decision:** Usare Blade templates (SSR) invece di SPA framework

**Context:**
- SEO importante per landing pages pubbliche
- Team familiare con Blade
- NO complex state management needed
- Performance priority

**Decision:**
Server-Side Rendering con Blade templates + Alpine.js per interattività.

**Consequences:**
- ✅ **Pro:**
  - SEO excellent (pre-rendered HTML)
  - Time-To-First-Byte basso
  - NO JavaScript bundle overhead
  - Development velocity alta
  - Cache full-page facile

- ❌ **Contro:**
  - Page reloads su navigation
  - Meno "app-like" feel
  - State management manuale

**Status:** ✅ Accepted

**Alternative Considered:**
- Vue.js SPA (rejected: SEO complesso, overhead)
- React SPA (rejected: team non familiare)
- Inertia.js (considerato per future)

---

## 📊 Data Flow

### Request → Response Flow

```
1. HTTP Request
   │
   ├─ nginx:80/443 (production)
   │  └─ SSL termination
   │
   ▼
2. Laravel Entry Point
   │
   ├─ public/index.php
   ├─ bootstrap/app.php
   │
   ▼
3. Middleware Stack
   │
   ├─ EncryptCookies
   ├─ VerifyCsrfToken
   ├─ Authenticate
   ├─ SchoolScopeMiddleware  ← MULTI-TENANT
   ├─ SecurityHeaders         ← CSP, HSTS
   │
   ▼
4. Router
   │
   ├─ routes/web.php
   ├─ routes/api.php
   │
   ▼
5. Controller
   │
   ├─ Authorization (Policy)
   ├─ Validation (FormRequest)
   │
   ▼
6. Service Layer (if needed)
   │
   ├─ Business Logic
   ├─ External API calls
   │
   ▼
7. Model Layer
   │
   ├─ Eloquent Query
   ├─ Global Scopes applied
   │
   ▼
8. Database
   │
   ├─ MySQL query execution
   │
   ▼
9. Response
   │
   ├─ Blade View rendering (SSR)
   ├─ OR JSON response (API)
   │
   ▼
10. Browser
```

### Payment Processing Flow

```
User: Studente effettua pagamento evento

1. Student clicks "Paga con PayPal"
   │
   ▼
2. PaymentController::showPaymentForm()
   ├─ Verifica evento pubblico disponibile
   ├─ Calcola importo
   └─ Render payment form
   │
   ▼
3. JavaScript: PayPal Buttons SDK
   ├─ createOrder → POST /pagamenti/paypal/create-order
   │  ├─ PaymentController::createPayPalOrder()
   │  ├─ PayPalService::createOrder($amount, $schoolId)
   │  └─ Return PayPal order_id
   │
   ▼
4. User approves on PayPal
   │
   ▼
5. onApprove → Redirect to /pagamenti/success?token=...
   ├─ PaymentController::success()
   ├─ PayPalService::executePayment($token)
   ├─ PaymentService::recordPayment(...)
   │  ├─ Create Payment record
   │  ├─ Create EventPayment record
   │  ├─ Update EventRegistration status
   │
   ▼
6. Email confirmation
   ├─ Mail::to($user)->send(PaymentConfirmation)
   │
   ▼
7. Success page + receipt
```

### Push Notification Flow

```
System: Cron job ogni 15 minuti

1. php artisan notifications:send-lesson-reminders
   │
   ▼
2. NotificationService::sendLessonReminders()
   ├─ Query lessons starting in 1 hour
   ├─ Filter students with notifications enabled
   │
   ▼
3. For each student:
   ├─ FirebasePushService::sendNotification(
   │    $fcmToken,
   │    'Lezione tra 1 ora',
   │    'Corso: ...'
   │  )
   │  ├─ Firebase Admin SDK
   │  └─ FCM API call
   │
   ▼
4. NotificationLog::create() per tracking
   │
   ▼
5. Mobile device riceve push
```

---

## 🔌 External Integrations

### Firebase Cloud Messaging

**Purpose:** Push notifications iOS + Android

**Package:** `kreait/laravel-firebase` v6.1

**Authentication:** Service account JSON credentials

**Flow:**
```
Laravel App → Firebase Admin SDK → FCM API → Mobile Device
```

**Configuration:**
```php
// config/firebase.php
'credentials' => env('FIREBASE_CREDENTIALS'),
'database_url' => env('FIREBASE_DATABASE_URL'),
```

**Usage:**
```php
$service = app(FirebasePushService::class);
$service->sendNotification($fcmToken, $title, $body, $data);
```

### PayPal API v2

**Purpose:** Payment processing

**Package:** `srmklive/paypal` v3.0

**Authentication:** Client ID + Secret (OAuth2)

**Endpoints Used:**
- `POST /v2/checkout/orders` - Create order
- `POST /v2/checkout/orders/{id}/capture` - Capture payment
- `POST /v2/payments/captures/{id}/refund` - Refund

**Flow:**
```
User → PayPal Buttons SDK → PayPal API → Webhook → Laravel App
```

**Configuration:**
```php
// config/paypal.php
'client_id' => env('PAYPAL_CLIENT_ID'),
'secret' => env('PAYPAL_SECRET'),
'mode' => env('PAYPAL_MODE', 'sandbox'),
```

### SMTP Email

**Purpose:** Transactional emails (receipts, notifications, password reset)

**Protocol:** SMTP

**Configuration:**
```php
// .env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
```

**Templates:** Blade email templates in `resources/views/emails/`

---

## 🔒 Security Architecture

### Security Layers

```
1. Network Layer (VPS)
   ├─ Firewall (ufw)
   │  ├─ Allow: 80, 443, 22 (SSH)
   │  └─ Deny: All other ports
   └─ Fail2Ban (brute-force protection)

2. Transport Layer
   ├─ SSL/TLS 1.2+ (Let's Encrypt)
   ├─ HSTS enabled (max-age=31536000)
   └─ Automatic certificate renewal

3. Application Layer
   ├─ Content Security Policy (CSP)
   ├─ X-Frame-Options: SAMEORIGIN
   ├─ X-Content-Type-Options: nosniff
   ├─ Referrer-Policy: strict-origin-when-cross-origin
   └─ Permissions-Policy

4. Authentication Layer
   ├─ Laravel Sanctum (API tokens)
   ├─ Bcrypt password hashing
   ├─ Rate limiting (login attempts)
   └─ Session management (Redis)

5. Authorization Layer
   ├─ Role-Based Access Control (RBAC)
   ├─ Laravel Policies
   └─ Multi-Tenant Isolation (global scopes)

6. Data Layer
   ├─ SQL injection prevention (Eloquent ORM)
   ├─ XSS prevention (Blade escaping)
   ├─ CSRF protection (middleware)
   └─ Input validation (Form Requests)
```

### Content Security Policy (CSP)

**File:** `app/Http/Middleware/SecurityHeaders.php`

**Policy:**
```php
$nonce = base64_encode(random_bytes(16));

$csp = [
    "default-src 'self'",
    "script-src 'self' 'nonce-{$nonce}' 'unsafe-eval' https://www.paypal.com",
    "style-src 'self' 'unsafe-inline'",
    "img-src 'self' data: https:",
    "font-src 'self' data:",
    "connect-src 'self' https://www.paypal.com",
    "frame-src https://www.paypal.com",
];

header("Content-Security-Policy: " . implode('; ', $csp));
```

**Grade:** A (92/100) - [SSL Labs Report](https://www.ssllabs.com/ssltest/)

---

## 🚀 Scalability & Performance

### Current Capacity

| Metric | Current | Target (1 year) |
|--------|---------|-----------------|
| **Schools** | 5-10 | 50-100 |
| **Total Users** | 100-500 | 5K-10K |
| **API Requests/day** | 1K-5K | 50K-100K |
| **Database Size** | <1GB | 5-10GB |
| **Storage (media)** | 5-10GB | 100-500GB |

### Optimization Strategies

#### 1. Database Optimization

```php
// Composite indexes per multi-tenant
Schema::table('payments', function (Blueprint $table) {
    $table->index(['school_id', 'status', 'created_at']);
    $table->index(['school_id', 'user_id']);
});

// Eager loading per N+1 prevention
$payments = Payment::with(['user', 'school', 'course'])->get();
```

#### 2. Redis Caching

```php
// Cache dashboard stats (5 minuti)
Cache::remember("dashboard_stats_{$schoolId}", 300, function () use ($schoolId) {
    return [
        'total_students' => User::where('school_id', $schoolId)->count(),
        'total_payments' => Payment::where('school_id', $schoolId)->sum('amount'),
        // ...
    ];
});
```

#### 3. Queue Workers

```bash
# systemd service for queue worker
[Service]
ExecStart=/usr/bin/php /var/www/danzafacile/artisan queue:work --tries=3
Restart=always
```

**Jobs Queued:**
- Email sending
- Push notifications
- PDF generation
- Image processing

#### 4. Asset Optimization

```javascript
// vite.config.js
export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['alpinejs'],
                },
            },
        },
    },
});
```

### Future Scalability Plan

#### Phase 1: Vertical Scaling (6 months)

- ✅ Upgrade VPS: 2GB → 4GB RAM
- ✅ MySQL optimization (query cache, buffer pool)
- ✅ Redis persistent storage

#### Phase 2: Horizontal Scaling (1 year)

- ⏳ Load balancer (Nginx)
- ⏳ Multiple app servers (PHP-FPM pool)
- ⏳ Database read replicas
- ⏳ CDN for static assets

#### Phase 3: Cloud Migration (2 years)

- ⏳ AWS/DigitalOcean managed services
- ⏳ S3-compatible object storage (media)
- ⏳ RDS managed database
- ⏳ ElastiCache (Redis cluster)

---

## 🔮 Future Evolution

### Roadmap Architetturale

#### Q1 2026: API Expansion

- [ ] GraphQL API (alternative to REST)
- [ ] API versioning strategy (v2)
- [ ] Webhook system for third-party integrations
- [ ] Public API for partner schools

#### Q2 2026: Mobile App Maturity

- [ ] Flutter app v1.0 release
- [ ] Offline-first architecture (local DB sync)
- [ ] Real-time features (WebSockets/Pusher)

#### Q3 2026: Analytics & Reporting

- [ ] Data warehouse (separate DB for analytics)
- [ ] Business Intelligence dashboard
- [ ] Custom report builder
- [ ] Export API (CSV, Excel, PDF)

#### Q4 2026: AI/ML Features

- [ ] Predictive analytics (student churn)
- [ ] Smart scheduling recommendations
- [ ] Automated payment reminders (ML-optimized timing)
- [ ] Chatbot support (student FAQ)

### Technology Evolution

| Area | Current | Future (2 years) |
|------|---------|------------------|
| **Monolith → Modular** | Monolith | Modular Monolith (DDD modules) |
| **Cache** | Redis single | Redis Cluster |
| **Database** | MySQL single | MySQL Primary + Read Replicas |
| **Storage** | Local filesystem | S3-compatible object storage |
| **Search** | MySQL LIKE | Meilisearch/Elasticsearch |
| **Real-time** | Polling | Laravel Echo + Pusher |

---

## 📚 References

### Internal Documentation

- [MULTI_TENANT_GUIDE.md](MULTI_TENANT_GUIDE.md) - Multi-tenancy implementation
- [SERVICES_MAP.md](SERVICES_MAP.md) - Service layer mapping
- [docs/security/SECURITY_AUDIT_REPORT_2025-11-22.md](security/SECURITY_AUDIT_REPORT_2025-11-22.md) - Security audit

### External Resources

- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Laravel Best Practices](https://github.com/alexeymezenin/laravel-best-practices)
- [Domain-Driven Design](https://martinfowler.com/bliki/DomainDrivenDesign.html)

---

**Versione:** 1.0.0
**Ultimo aggiornamento:** 2026-01-25
**Maintainer:** DanzaFacile Development Team
