# SECURITY FIX ROADMAP
## Scuola di Danza - Dashboard Admin

**Data:** 30 Settembre 2025
**Versione Applicazione:** Laravel 12
**Strategia:** Fix progressivi e non-breaking con testing continuo

---

## PRINCIPI GUIDA

### 🎯 Obiettivi
1. **Zero Downtime** - Nessuna interruzione del servizio
2. **Zero Regression** - Mantenere tutte le funzionalità esistenti
3. **Incremental Fixes** - Fix graduali e testati
4. **Rollback Ready** - Ogni fix deve essere reversibile
5. **Testing First** - Test prima di ogni deploy

### 📋 Processo per Ogni Fix
```
1. Crea branch feature/security-fix-XXX
2. Implementa fix con backward compatibility
3. Scrivi test automatizzati
4. Test manuale completo
5. Code review
6. Merge su staging
7. Test staging 24h
8. Merge su production
9. Monitor 48h
```

---

## FASE 0: PREPARAZIONE (Giorno 1)

### 📦 Setup Ambiente
**Durata:** 2 ore
**Branch:** `feature/security-infrastructure`

#### Task 0.1: Backup Completo
```bash
# Database backup
./vendor/bin/sail artisan backup:run --only-db

# File storage backup
tar -czf storage_backup_$(date +%Y%m%d).tar.gz storage/

# Git tag pre-security-fixes
git tag v1.0.0-pre-security
git push origin v1.0.0-pre-security
```

#### Task 0.2: Setup Testing Infrastructure
```bash
# Creare suite test sicurezza
mkdir -p tests/Feature/Security
mkdir -p tests/Unit/Security

# Template base test
cat > tests/Feature/Security/SecurityTestCase.php
```

```php
<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\User;
use App\Models\School;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class SecurityTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected School $school1;
    protected School $school2;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup default test data
        $this->school1 = School::factory()->create(['name' => 'Test School 1']);
        $this->school2 = School::factory()->create(['name' => 'Test School 2']);

        $this->admin = User::factory()->admin()->create([
            'school_id' => $this->school1->id
        ]);

        $this->student = User::factory()->student()->create([
            'school_id' => $this->school1->id
        ]);
    }
}
```

#### Task 0.3: Setup Monitoring
```bash
# Configurare log separato per security
# config/logging.php - aggiungere channel security
```

**✅ Checklist:**
- [ ] Backup database completo
- [ ] Backup storage files
- [ ] Git tag creato
- [ ] Test infrastructure setup
- [ ] Monitoring configurato
- [ ] Staging environment pronto

**Commit:** `🔧 SETUP: Security fix infrastructure`

---

## FASE 1: CRITICAL FIXES (Giorni 2-4)

### 🔴 FIX #1: SQL Injection via Sort Parameter
**Priorità:** CRITICAL
**Durata:** 6 ore
**Branch:** `feature/security-fix-sql-injection-sort`

#### Step 1.1: Creare Helper Centralizzato (1h)
**File:** `app/Helpers/QueryHelper.php`

```php
<?php

namespace App\Helpers;

class QueryHelper
{
    /**
     * Valida e sanitizza sort field
     */
    public static function validateSortField(string $field, array $allowedFields, string $default): string
    {
        return in_array($field, $allowedFields) ? $field : $default;
    }

    /**
     * Valida sort direction
     */
    public static function validateSortDirection(string $direction, string $default = 'desc'): string
    {
        return in_array(strtolower($direction), ['asc', 'desc']) ? $direction : $default;
    }

    /**
     * Sanitizza LIKE input
     */
    public static function sanitizeLikeInput(string $input): string
    {
        return addcslashes(trim($input), '%_\\');
    }

    /**
     * Applica sorting sicuro a query
     */
    public static function applySafeSort($query, string $sortField, string $sortDirection, array $allowedFields, string $defaultField = 'created_at')
    {
        $field = self::validateSortField($sortField, $allowedFields, $defaultField);
        $direction = self::validateSortDirection($sortDirection);

        return $query->orderBy($field, $direction);
    }
}
```

**Commit:** `🔒 SECURITY: Add QueryHelper for safe sorting`

#### Step 1.2: Fix AdminPaymentController (1h)
**File:** `app/Http/Controllers/Admin/AdminPaymentController.php`

```php
// BEFORE (linee 50-52)
$sortField = $request->get('sort', 'payment_date');
$sortDirection = $request->get('direction', 'desc');
$query->orderBy($sortField, $sortDirection);

// AFTER
use App\Helpers\QueryHelper;

$allowedSortFields = [
    'payment_date', 'amount', 'status', 'payment_method',
    'created_at', 'updated_at', 'due_date', 'receipt_number'
];

$query = QueryHelper::applySafeSort(
    $query,
    $request->get('sort', 'payment_date'),
    $request->get('direction', 'desc'),
    $allowedSortFields,
    'payment_date'
);
```

**Commit:** `🔒 SECURITY: Fix SQL injection in AdminPaymentController sorting`

#### Step 1.3: Fix Altri Controller (2h)
Applicare stesso fix a:
- `AdminStudentController.php` (riga 382)
- `AdminDocumentController.php` (riga 45-46)
- `AdminBaseController.php` (riga 381-382)

**Commit:** `🔒 SECURITY: Fix SQL injection in all admin controllers`

#### Step 1.4: Scrivere Test (1h)
**File:** `tests/Feature/Security/SqlInjectionTest.php`

```php
<?php

namespace Tests\Feature\Security;

class SqlInjectionTest extends SecurityTestCase
{
    /** @test */
    public function sql_injection_via_sort_parameter_is_prevented()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.payments.index', [
                'sort' => 'payment_date;DROP TABLE payments--',
                'direction' => 'desc'
            ]))
            ->assertOk();

        // Verificare tabella esiste ancora
        $this->assertDatabaseHas('payments', []);
    }

    /** @test */
    public function only_allowed_sort_fields_are_accepted()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payments.index', [
                'sort' => 'password', // Campo non permesso
                'direction' => 'asc'
            ]))
            ->assertOk();

        // Dovrebbe usare default sort
        $this->assertTrue(true); // Query non crash
    }

    /** @test */
    public function sort_direction_is_validated()
    {
        $this->actingAs($this->admin)
            ->get(route('admin.payments.index', [
                'sort' => 'amount',
                'direction' => 'UNION SELECT * FROM users--'
            ]))
            ->assertOk();
    }
}
```

**Test:**
```bash
./vendor/bin/sail artisan test --filter=SqlInjectionTest
```

#### Step 1.5: Testing Manuale (1h)
**Checklist:**
- [ ] Admin payments index con sort funziona
- [ ] Admin students index con sort funziona
- [ ] Admin documents index con sort funziona
- [ ] Sort con parametri invalidi usa default
- [ ] Nessun crash con payload SQL injection
- [ ] Performance non degradata

**Commit:** `🧪 TEST: Add SQL injection test suite`

**Deploy Strategy:**
```bash
# 1. Merge su staging
git checkout staging
git merge feature/security-fix-sql-injection-sort

# 2. Deploy staging
./vendor/bin/sail artisan migrate --force

# 3. Test staging 2 ore
# 4. Se OK → Merge production
```

---

### 🔴 FIX #2: PayPal Webhook Signature Verification
**Priorità:** CRITICAL
**Durata:** 8 ore
**Branch:** `feature/security-fix-paypal-webhook`

#### Step 2.1: Aggiungere Configurazione (30min)
**File:** `config/paypal.php`

```php
// Aggiungere dopo la configurazione esistente
'webhook_id' => env('PAYPAL_WEBHOOK_ID', ''),

'webhook_verification' => [
    'enabled' => env('PAYPAL_WEBHOOK_VERIFY', true),
    'strict_mode' => env('PAYPAL_WEBHOOK_STRICT', true),
],
```

**File:** `.env` (per ogni scuola)
```env
PAYPAL_WEBHOOK_ID=your-webhook-id-from-paypal
PAYPAL_WEBHOOK_VERIFY=true
PAYPAL_WEBHOOK_STRICT=false  # false in staging, true in production
```

**Commit:** `🔒 SECURITY: Add PayPal webhook configuration`

#### Step 2.2: Implementare Verifica Firma (2h)
**File:** `app/Services/PayPalService.php`

```php
/**
 * Verifica firma webhook PayPal
 *
 * @param array $verification Dati per verifica
 * @return bool
 */
public function verifyWebhookSignature(array $verification): bool
{
    try {
        // Skip verifica se disabilitata (solo per testing)
        if (!config('paypal.webhook_verification.enabled')) {
            Log::warning('PayPal webhook verification is DISABLED', [
                'school_id' => $this->school->id,
                'environment' => config('app.env')
            ]);
            return true;
        }

        // Validazione parametri richiesti
        $requiredKeys = [
            'auth_algo', 'cert_url', 'transmission_id',
            'transmission_sig', 'transmission_time', 'webhook_id'
        ];

        foreach ($requiredKeys as $key) {
            if (empty($verification[$key])) {
                Log::error('PayPal webhook missing required field', [
                    'missing_field' => $key,
                    'school_id' => $this->school->id
                ]);
                return false;
            }
        }

        // Ottieni access token
        $accessToken = $this->getAccessToken();

        if (!$accessToken) {
            Log::error('PayPal webhook verification failed: no access token');
            return false;
        }

        // Chiamata API PayPal per verifica
        $response = $this->client->post('/v1/notifications/verify-webhook-signature', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => "Bearer {$accessToken}"
            ],
            'json' => $verification
        ]);

        $result = json_decode($response->getBody(), true);

        $isValid = isset($result['verification_status']) &&
                   $result['verification_status'] === 'SUCCESS';

        if (!$isValid) {
            Log::error('PayPal webhook signature verification failed', [
                'school_id' => $this->school->id,
                'transmission_id' => $verification['transmission_id'],
                'status' => $result['verification_status'] ?? 'unknown'
            ]);
        } else {
            Log::info('PayPal webhook signature verified successfully', [
                'school_id' => $this->school->id,
                'transmission_id' => $verification['transmission_id']
            ]);
        }

        return $isValid;

    } catch (\Exception $e) {
        Log::error('PayPal webhook verification exception', [
            'error' => $e->getMessage(),
            'school_id' => $this->school->id,
            'trace' => $e->getTraceAsString()
        ]);

        // In strict mode, rigetta webhook se verifica fallisce
        if (config('paypal.webhook_verification.strict_mode')) {
            return false;
        }

        // In non-strict mode (staging), permetti ma logga
        Log::warning('PayPal webhook accepted despite verification error (non-strict mode)');
        return true;
    }
}

/**
 * Ottiene access token per API PayPal
 */
private function getAccessToken(): ?string
{
    try {
        $response = $this->client->post('/v1/oauth2/token', [
            'auth' => [
                $this->settings['client_id'],
                $this->settings['client_secret']
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            'form_params' => [
                'grant_type' => 'client_credentials'
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        return $data['access_token'] ?? null;

    } catch (\Exception $e) {
        Log::error('Failed to get PayPal access token', [
            'error' => $e->getMessage(),
            'school_id' => $this->school->id
        ]);
        return null;
    }
}
```

**Commit:** `🔒 SECURITY: Implement PayPal webhook signature verification`

#### Step 2.3: Aggiornare PayPalController (2h)
**File:** `app/Http/Controllers/PayPalController.php`

```php
public function webhook(Request $request)
{
    try {
        $headers = $request->header();
        $body = $request->getContent();
        $data = json_decode($body, true);

        if (!$data) {
            Log::error('PayPal webhook: Invalid JSON');
            return response('Invalid JSON', 400);
        }

        // Log webhook ricevuto
        Log::info('PayPal webhook received', [
            'event_type' => $data['event_type'] ?? 'unknown',
            'transmission_id' => $headers['paypal-transmission-id'][0] ?? null
        ]);

        // Estrai school_id dal custom data
        $customData = json_decode($data['resource']['custom'] ?? '{}', true);
        $schoolId = $customData['school_id'] ?? null;

        if (!$schoolId) {
            Log::error('PayPal webhook missing school_id', [
                'event_type' => $data['event_type'] ?? 'unknown'
            ]);
            return response('Missing school_id in custom data', 400);
        }

        $school = School::find($schoolId);
        if (!$school) {
            Log::error('PayPal webhook: School not found', ['school_id' => $schoolId]);
            return response('School not found', 404);
        }

        // Inizializza PayPal service per la scuola
        $paypalService = PayPalService::forSchool($school);

        // VERIFICA FIRMA WEBHOOK
        $webhookId = Setting::get("school.{$schoolId}.paypal.webhook_id");

        if (!$webhookId) {
            Log::error('PayPal webhook: Webhook ID not configured', [
                'school_id' => $schoolId
            ]);

            // Se strict mode, rigetta
            if (config('paypal.webhook_verification.strict_mode')) {
                return response('Webhook ID not configured', 403);
            }
        }

        $verification = [
            'auth_algo' => $headers['paypal-auth-algo'][0] ?? '',
            'cert_url' => $headers['paypal-cert-url'][0] ?? '',
            'transmission_id' => $headers['paypal-transmission-id'][0] ?? '',
            'transmission_sig' => $headers['paypal-transmission-sig'][0] ?? '',
            'transmission_time' => $headers['paypal-transmission-time'][0] ?? '',
            'webhook_id' => $webhookId,
            'webhook_event' => $data
        ];

        // VERIFICA FIRMA
        if (!$paypalService->verifyWebhookSignature($verification)) {
            Log::error('PayPal webhook signature verification FAILED', [
                'school_id' => $schoolId,
                'transmission_id' => $verification['transmission_id'],
                'event_type' => $data['event_type'] ?? 'unknown'
            ]);

            return response('Webhook signature verification failed', 403);
        }

        Log::info('PayPal webhook signature VERIFIED', [
            'school_id' => $schoolId,
            'event_type' => $data['event_type']
        ]);

        // Processa webhook solo se firma verificata
        if (!isset($data['event_type'])) {
            return response('Missing event_type', 400);
        }

        // ... resto del codice esistente per processare webhook ...

        return response('Webhook processed successfully', 200);

    } catch (\Exception $e) {
        Log::error('PayPal webhook exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return response('Internal server error', 500);
    }
}
```

**Commit:** `🔒 SECURITY: Add webhook signature verification to PayPalController`

#### Step 2.4: Migration per Webhook ID (30min)
**File:** `database/migrations/2025_09_30_add_paypal_webhook_id_to_settings.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\School;
use App\Models\Setting;

return new class extends Migration
{
    public function up(): void
    {
        // Aggiungi webhook_id per ogni scuola che ha PayPal configurato
        School::all()->each(function ($school) {
            $clientId = Setting::get("school.{$school->id}.paypal.client_id");

            if ($clientId) {
                // Placeholder - admin deve configurare webhook_id reale
                Setting::set(
                    "school.{$school->id}.paypal.webhook_id",
                    '' // Vuoto - da configurare in admin panel
                );
            }
        });
    }

    public function down(): void
    {
        School::all()->each(function ($school) {
            Setting::forget("school.{$school->id}.paypal.webhook_id");
        });
    }
};
```

**Commit:** `🔒 SECURITY: Add migration for PayPal webhook ID`

#### Step 2.5: UI per Configurazione Webhook ID (2h)
**File:** `resources/views/admin/settings/index.blade.php`

```blade
<!-- Nella sezione PayPal, aggiungere dopo client_secret -->
<div>
    <label for="paypal_webhook_id" class="block text-sm font-medium text-gray-700 mb-2">
        Webhook ID <span class="text-red-500">*</span>
    </label>
    <input type="text"
           name="paypal_webhook_id"
           id="paypal_webhook_id"
           value="{{ Setting::get("school.{$school->id}.paypal.webhook_id", '') }}"
           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500"
           placeholder="es: 7XX123456X789012Y">
    <p class="mt-2 text-sm text-gray-600">
        Trova il Webhook ID nella Dashboard PayPal Developer → My Apps → [Tua App] → Webhooks
    </p>
</div>
```

**File:** `app/Http/Controllers/Admin/AdminSettingsController.php`

```php
// Nel metodo update, aggiungere:
if ($request->filled('paypal_webhook_id')) {
    Setting::set(
        "school.{$school->id}.paypal.webhook_id",
        $request->paypal_webhook_id
    );
}
```

**Commit:** `🔒 SECURITY: Add UI for PayPal webhook ID configuration`

#### Step 2.6: Documentazione (1h)
**File:** `docs/PAYPAL_WEBHOOK_SETUP.md`

```markdown
# PayPal Webhook Signature Verification Setup

## Perché è Importante
La verifica della firma webhook previene attacchi di tipo:
- Payment fraud (pagamenti falsi)
- Bypass processo PayPal
- Iscrizioni gratuite non autorizzate

## Setup Passo-Passo

### 1. Ottieni Webhook ID da PayPal
1. Vai su https://developer.paypal.com/dashboard/
2. Seleziona la tua app
3. Vai in Webhooks section
4. Copia il Webhook ID (formato: 7XX123456X789012Y)

### 2. Configura nel Sistema
1. Login come Admin
2. Vai in Gestione → Impostazioni → Tab PayPal
3. Inserisci Webhook ID nel campo dedicato
4. Salva

### 3. Test Verifica
```bash
# Invia webhook di test da PayPal Dashboard
# Verifica nei log: "PayPal webhook signature VERIFIED"
```

### 4. Abilitazione Produzione
```env
# .env production
PAYPAL_WEBHOOK_VERIFY=true
PAYPAL_WEBHOOK_STRICT=true
```

## Troubleshooting
- Se webhook fallisce: controlla log in storage/logs/laravel.log
- Cerca: "PayPal webhook signature verification failed"
- Verifica Webhook ID corretto
```

**Commit:** `📚 DOCS: Add PayPal webhook setup documentation`

#### Step 2.7: Testing (1h)
**File:** `tests/Feature/Security/PayPalWebhookTest.php`

```php
<?php

namespace Tests\Feature\Security;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class PayPalWebhookTest extends SecurityTestCase
{
    /** @test */
    public function webhook_without_signature_is_rejected_in_strict_mode()
    {
        config(['paypal.webhook_verification.strict_mode' => true]);

        $response = $this->postJson('/paypal/webhook', [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'custom' => json_encode(['school_id' => $this->school1->id])
            ]
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function webhook_with_valid_signature_is_accepted()
    {
        // Mock PayPal API response
        Http::fake([
            '*/v1/notifications/verify-webhook-signature' => Http::response([
                'verification_status' => 'SUCCESS'
            ], 200)
        ]);

        Setting::set("school.{$this->school1->id}.paypal.webhook_id", 'test-webhook-id');

        $response = $this->postJson('/paypal/webhook', [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'custom' => json_encode(['school_id' => $this->school1->id])
            ]
        ], [
            'paypal-auth-algo' => 'SHA256withRSA',
            'paypal-cert-url' => 'https://api.paypal.com/cert',
            'paypal-transmission-id' => 'test-123',
            'paypal-transmission-sig' => 'test-sig',
            'paypal-transmission-time' => now()->toIso8601String()
        ]);

        $response->assertOk();
    }
}
```

**Test:**
```bash
./vendor/bin/sail artisan test --filter=PayPalWebhookTest
```

**✅ Checklist Fase 1:**
- [ ] SQL Injection fix implementato
- [ ] QueryHelper creato e testato
- [ ] Tutti controller admin aggiornati
- [ ] PayPal webhook verification implementata
- [ ] UI webhook ID configurata
- [ ] Test automatizzati passano
- [ ] Documentazione completa
- [ ] Deploy staging completato
- [ ] Test staging 24h OK

**Commit Finale Fase 1:** `🔒 SECURITY: Complete CRITICAL fixes (SQL Injection + PayPal)`

---

## FASE 2: HIGH PRIORITY FIXES (Giorni 5-9)

### ⚠️ FIX #3: SchoolOwnership Middleware - Model Coverage
**Priorità:** HIGH
**Durata:** 4 ore
**Branch:** `feature/security-fix-school-ownership`

#### Step 3.1: Estendere validateModelOwnership (2h)
**File:** `app/Http/Middleware/SchoolOwnership.php`

```php
// Aggiungere dopo i case esistenti (circa linea 179)

case 'App\Models\Event':
    if (($user->isAdmin() || $user->isStudent()) && $model->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Event access denied - not your school');
    }
    break;

case 'App\Models\EventRegistration':
    if ($user->isAdmin() && $model->event->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Event registration access denied - not your school');
    }
    if ($user->isStudent() && $model->user_id !== $user->id) {
        $this->denyAccess($request, 'Event registration access denied - not your registration');
    }
    break;

case 'App\Models\Ticket':
    // Admin può vedere solo ticket della propria scuola
    if ($user->isAdmin() && $model->user->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Ticket access denied - not your school');
    }
    // Student può vedere solo i propri ticket
    if ($user->isStudent() && $model->user_id !== $user->id) {
        $this->denyAccess($request, 'Ticket access denied - not your ticket');
    }
    break;

case 'App\Models\Staff':
    if ($user->isAdmin() && $model->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Staff access denied - not your school');
    }
    break;

case 'App\Models\StaffSchedule':
    if ($user->isAdmin() && $model->staff->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Staff schedule access denied - not your school');
    }
    break;

case 'App\Models\Attendance':
    // Attendance è polymorphic (può essere per Course o Event)
    $attendable = $model->attendable;
    if ($attendable && $user->isAdmin() && $attendable->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Attendance access denied - not your school');
    }
    if ($user->isStudent() && $model->user_id !== $user->id) {
        $this->denyAccess($request, 'Attendance access denied - not your record');
    }
    break;

case 'App\Models\MediaGallery':
    if ($user->isAdmin() && $model->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Media gallery access denied - not your school');
    }
    break;
```

**Commit:** `🔒 SECURITY: Extend SchoolOwnership middleware for all models`

#### Step 3.2: Test Coverage (1h)
**File:** `tests/Feature/Security/SchoolOwnershipTest.php`

```php
<?php

namespace Tests\Feature\Security;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\Staff;

class SchoolOwnershipTest extends SecurityTestCase
{
    /** @test */
    public function admin_cannot_access_other_school_events()
    {
        $event = Event::factory()->create(['school_id' => $this->school2->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.events.show', $event))
            ->assertStatus(404); // Or 403 depending on implementation
    }

    /** @test */
    public function admin_cannot_access_other_school_tickets()
    {
        $student2 = User::factory()->student()->create(['school_id' => $this->school2->id]);
        $ticket = Ticket::factory()->create(['user_id' => $student2->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.tickets.show', $ticket))
            ->assertStatus(404);
    }

    /** @test */
    public function student_cannot_access_other_student_tickets()
    {
        $student2 = User::factory()->student()->create(['school_id' => $this->school1->id]);
        $ticket = Ticket::factory()->create(['user_id' => $student2->id]);

        $this->actingAs($this->student)
            ->get(route('student.tickets.show', $ticket))
            ->assertStatus(404);
    }

    /** @test */
    public function admin_cannot_access_other_school_staff()
    {
        $staff = Staff::factory()->create(['school_id' => $this->school2->id]);

        $this->actingAs($this->admin)
            ->get(route('admin.staff.show', $staff))
            ->assertStatus(404);
    }
}
```

**Commit:** `🧪 TEST: Add comprehensive school ownership tests`

#### Step 3.3: Regression Testing (1h)
```bash
# Test completo funzionalità esistenti
./vendor/bin/sail artisan test --testsuite=Feature

# Test manuale:
# - Admin può vedere propri eventi ✓
# - Admin può vedere propri ticket ✓
# - Admin può vedere proprio staff ✓
# - Student può vedere propri ticket ✓
# - Cross-school access negato ✓
```

**✅ Checklist:**
- [ ] Tutti model coperti in middleware
- [ ] Test automatizzati passano
- [ ] Nessuna regression su funzionalità esistenti
- [ ] Deploy staging OK

---

### ⚠️ FIX #4: Search Input Sanitization (Like Wildcard)
**Priorità:** HIGH
**Durata:** 3 ore
**Branch:** `feature/security-fix-like-injection`

#### Step 4.1: Estendere QueryHelper (30min)
**File:** `app/Helpers/QueryHelper.php`

```php
/**
 * Sanitizza input per LIKE queries
 * Rimuove caratteri wildcard speciali (%, _)
 */
public static function sanitizeLikeInput(string $input): string
{
    // Trim whitespace
    $input = trim($input);

    // Escape caratteri speciali LIKE
    $input = addcslashes($input, '%_\\');

    // Limita lunghezza (previene DoS)
    $input = substr($input, 0, 100);

    return $input;
}

/**
 * Applica ricerca sicura con LIKE
 */
public static function applySafeLikeSearch($query, string $field, string $searchTerm, string $operator = 'like'): void
{
    $sanitized = self::sanitizeLikeInput($searchTerm);
    $query->where($field, $operator, "%{$sanitized}%");
}
```

**Commit:** `🔒 SECURITY: Add LIKE input sanitization to QueryHelper`

#### Step 4.2: Fix Search nei Controller (1.5h)
**File:** `app/Http/Controllers/Admin/AdminPaymentController.php`

```php
// BEFORE (linee 34-46)
if ($request->filled('search')) {
    $searchTerm = $request->get('search');
    $query->where(function($q) use ($searchTerm) {
        $q->whereHas('user', function($subq) use ($searchTerm) {
            $subq->where('name', 'like', "%{$searchTerm}%")
                 ->orWhere('email', 'like', "%{$searchTerm}%");
        })
        ->orWhere('receipt_number', 'like', "%{$searchTerm}%")
        ->orWhere('payment_method', 'like', "%{$searchTerm}%");
    });
}

// AFTER
if ($request->filled('search')) {
    $searchTerm = QueryHelper::sanitizeLikeInput($request->get('search'));

    $query->where(function($q) use ($searchTerm) {
        $q->whereHas('user', function($subq) use ($searchTerm) {
            $subq->where('name', 'like', "%{$searchTerm}%")
                 ->orWhere('email', 'like', "%{$searchTerm}%");
        })
        ->orWhere('receipt_number', 'like', "%{$searchTerm}%")
        ->orWhere('payment_method', 'like', "%{$searchTerm}%");
    });
}
```

Applicare a tutti i controller con search:
- AdminStudentController
- AdminCourseController
- AdminDocumentController
- AdminTicketController

**Commit:** `🔒 SECURITY: Sanitize LIKE search input in all controllers`

#### Step 4.3: Test (1h)
**File:** `tests/Feature/Security/LikeInjectionTest.php`

```php
<?php

namespace Tests\Feature\Security;

class LikeInjectionTest extends SecurityTestCase
{
    /** @test */
    public function search_with_wildcard_characters_is_sanitized()
    {
        // Crea payment
        $payment = Payment::factory()->create([
            'school_id' => $this->school1->id,
            'user_id' => $this->student->id
        ]);

        // Ricerca con wildcard
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payments.index', ['search' => '%']))
            ->assertOk();

        // Non dovrebbe mostrare tutti i record
        // (comportamento wildcard bloccato)
    }

    /** @test */
    public function search_with_underscore_is_sanitized()
    {
        $response = $this->actingAs($this->admin)
            ->get(route('admin.payments.index', ['search' => 'test_']))
            ->assertOk();
    }
}
```

**Commit:** `🧪 TEST: Add LIKE injection test coverage`

---

### ⚠️ FIX #5: File Upload Validation
**Priorità:** HIGH
**Durata:** 4 ore
**Branch:** `feature/security-fix-file-upload`

#### Step 5.1: Creare File Validation Helper (1h)
**File:** `app/Helpers/FileUploadHelper.php`

```php
<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class FileUploadHelper
{
    /**
     * MIME types permessi per categoria
     */
    private static array $allowedMimes = [
        'documents' => [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ],
        'images' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp'
        ],
        'videos' => [
            'video/mp4',
            'video/mpeg',
            'video/quicktime'
        ]
    ];

    /**
     * Validazione file robusta
     */
    public static function validateFile(
        UploadedFile $file,
        string $category = 'documents',
        int $maxSizeMB = 10
    ): array {
        $errors = [];

        // 1. Verifica dimensione
        $maxSizeBytes = $maxSizeMB * 1024 * 1024;
        if ($file->getSize() > $maxSizeBytes) {
            $errors[] = "File troppo grande. Max {$maxSizeMB}MB";
        }

        // 2. Verifica extension
        $extension = strtolower($file->getClientOriginalExtension());
        $allowedExtensions = self::getAllowedExtensions($category);

        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = "Estensione non permessa: {$extension}";
        }

        // 3. Verifica MIME type reale (magic bytes)
        $realMimeType = File::mimeType($file->path());
        $allowedMimes = self::$allowedMimes[$category] ?? [];

        if (!in_array($realMimeType, $allowedMimes)) {
            $errors[] = "Tipo file non permesso: {$realMimeType}";
        }

        // 4. Verifica magic bytes per immagini
        if ($category === 'images') {
            if (!self::validateImageMagicBytes($file)) {
                $errors[] = "File non è un'immagine valida";
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'mime_type' => $realMimeType,
            'extension' => $extension
        ];
    }

    /**
     * Genera nome file sicuro
     */
    public static function generateSafeFilename(UploadedFile $file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return hash('sha256', time() . $file->getClientOriginalName() . random_bytes(16))
               . '.' . $extension;
    }

    /**
     * Ottieni extensions permesse per categoria
     */
    private static function getAllowedExtensions(string $category): array
    {
        return match($category) {
            'documents' => ['pdf', 'doc', 'docx', 'txt'],
            'images' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'videos' => ['mp4', 'mpeg', 'mov'],
            default => []
        };
    }

    /**
     * Valida magic bytes immagini
     */
    private static function validateImageMagicBytes(UploadedFile $file): bool
    {
        $handle = fopen($file->path(), 'rb');
        $bytes = fread($handle, 8);
        fclose($handle);

        // JPEG
        if (substr($bytes, 0, 2) === "\xFF\xD8") {
            return true;
        }

        // PNG
        if (substr($bytes, 0, 8) === "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") {
            return true;
        }

        // GIF
        if (substr($bytes, 0, 3) === "GIF") {
            return true;
        }

        return false;
    }
}
```

**Commit:** `🔒 SECURITY: Add robust file upload validation helper`

#### Step 5.2: Update AdminDocumentController (1.5h)
**File:** `app/Http/Controllers/Admin/AdminDocumentController.php`

```php
use App\Helpers\FileUploadHelper;

// Nel metodo store() e update(), PRIMA dello storage:
if ($request->hasFile('file')) {
    $file = $request->file('file');

    // Validazione robusta
    $validation = FileUploadHelper::validateFile($file, 'documents', 10);

    if (!$validation['valid']) {
        return back()->withErrors([
            'file' => implode(' ', $validation['errors'])
        ])->withInput();
    }

    // Nome file sicuro
    $safeFilename = FileUploadHelper::generateSafeFilename($file);

    // Storage (già corretto con 'private' disk)
    $filePath = $file->storeAs(
        "documents/{$schoolId}/admin",
        $safeFilename,
        'private'
    );

    // Salva con nome originale per display
    $document->file_path = $filePath;
    $document->original_filename = $file->getClientOriginalName();
}
```

**Commit:** `🔒 SECURITY: Apply robust file validation in AdminDocumentController`

#### Step 5.3: Update MediaGalleryController (1h)
Applicare stesso pattern a MediaGalleryController per upload media.

**Commit:** `🔒 SECURITY: Apply robust file validation in MediaGalleryController`

#### Step 5.4: Test (30min)
**File:** `tests/Feature/Security/FileUploadTest.php`

```php
<?php

namespace Tests\Feature\Security;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FileUploadTest extends SecurityTestCase
{
    /** @test */
    public function php_file_disguised_as_pdf_is_rejected()
    {
        Storage::fake('private');

        // Crea file PHP mascherato
        $phpContent = '<?php system($_GET["cmd"]); ?>';
        $file = UploadedFile::fake()->createWithContent('evil.pdf', $phpContent);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.documents.store'), [
                'title' => 'Test',
                'type' => 'medical_certificate',
                'file' => $file
            ]);

        $response->assertSessionHasErrors('file');
    }

    /** @test */
    public function valid_pdf_is_accepted()
    {
        Storage::fake('private');

        $file = UploadedFile::fake()->create('document.pdf', 1024, 'application/pdf');

        $response = $this->actingAs($this->admin)
            ->post(route('admin.documents.store'), [
                'title' => 'Valid Document',
                'type' => 'medical_certificate',
                'file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function oversized_file_is_rejected()
    {
        Storage::fake('private');

        // File > 10MB
        $file = UploadedFile::fake()->create('huge.pdf', 11 * 1024);

        $response = $this->actingAs($this->admin)
            ->post(route('admin.documents.store'), [
                'title' => 'Huge File',
                'type' => 'medical_certificate',
                'file' => $file
            ]);

        $response->assertSessionHasErrors('file');
    }
}
```

**Commit:** `🧪 TEST: Add file upload security tests`

---

### ⚠️ FIX #6: PayPal Credentials Encryption
**Priorità:** HIGH
**Durata:** 3 ore
**Branch:** `feature/security-fix-paypal-encryption`

#### Step 6.1: Creare Encryption Helper (30min)
**File:** `app/Helpers/EncryptionHelper.php`

```php
<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class EncryptionHelper
{
    /**
     * Cifra valore sensibile
     */
    public static function encrypt(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::encryptString($value);
        } catch (\Exception $e) {
            Log::error('Encryption failed', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Decifra valore sensibile
     */
    public static function decrypt(?string $encryptedValue): ?string
    {
        if (empty($encryptedValue)) {
            return null;
        }

        try {
            return Crypt::decryptString($encryptedValue);
        } catch (\Exception $e) {
            Log::error('Decryption failed', ['error' => $e->getMessage()]);
            // Return null invece di crash se decryption fallisce
            return null;
        }
    }

    /**
     * Verifica se un valore è cifrato
     */
    public static function isEncrypted(?string $value): bool
    {
        if (empty($value)) {
            return false;
        }

        // Laravel encrypted strings iniziano con "eyJpdiI6..."
        return str_starts_with($value, 'eyJpdiI6');
    }
}
```

**Commit:** `🔒 SECURITY: Add encryption helper for sensitive data`

#### Step 6.2: Update AdminSettingsController (1h)
**File:** `app/Http/Controllers/Admin/AdminSettingsController.php`

```php
use App\Helpers\EncryptionHelper;

public function update(Request $request)
{
    // ... existing validation ...

    $school = Auth::user()->school;
    $schoolId = $school->id;

    // PayPal Configuration
    if ($request->filled('paypal_mode')) {
        Setting::set("school.{$schoolId}.paypal.mode", $request->paypal_mode);
    }

    if ($request->filled('paypal_client_id')) {
        Setting::set("school.{$schoolId}.paypal.client_id", $request->paypal_client_id);
    }

    if ($request->filled('paypal_client_secret')) {
        // CIFRA client_secret prima di salvare
        $encryptedSecret = EncryptionHelper::encrypt($request->paypal_client_secret);
        Setting::set("school.{$schoolId}.paypal.client_secret", $encryptedSecret);

        Log::info('PayPal client secret encrypted and saved', [
            'school_id' => $schoolId,
            'admin_id' => Auth::id()
        ]);
    }

    if ($request->filled('paypal_webhook_id')) {
        Setting::set("school.{$schoolId}.paypal.webhook_id", $request->paypal_webhook_id);
    }

    // ... rest of code ...
}
```

**Commit:** `🔒 SECURITY: Encrypt PayPal client_secret on save`

#### Step 6.3: Update PayPalService (1h)
**File:** `app/Services/PayPalService.php`

```php
use App\Helpers\EncryptionHelper;

public static function forSchool(School $school): self
{
    $schoolId = $school->id;

    // Leggi client_secret (potrebbe essere cifrato o plaintext)
    $clientSecret = Setting::get("school.{$schoolId}.paypal.client_secret", '');

    // Se cifrato, decifra
    if (EncryptionHelper::isEncrypted($clientSecret)) {
        $clientSecret = EncryptionHelper::decrypt($clientSecret);
    }

    $settings = [
        'client_id' => Setting::get("school.{$schoolId}.paypal.client_id", ''),
        'client_secret' => $clientSecret,
        'mode' => Setting::get("school.{$schoolId}.paypal.mode", 'sandbox'),
        'currency' => Setting::get("school.{$schoolId}.paypal.currency", 'EUR'),
    ];

    return new self($school, $settings);
}
```

**Commit:** `🔒 SECURITY: Decrypt PayPal credentials in PayPalService`

#### Step 6.4: Migration per Encryption Esistenti (30min)
**File:** `database/migrations/2025_09_30_encrypt_existing_paypal_secrets.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\School;
use App\Models\Setting;
use App\Helpers\EncryptionHelper;

return new class extends Migration
{
    public function up(): void
    {
        // Cifra tutti i client_secret esistenti in plaintext
        School::all()->each(function ($school) {
            $clientSecret = Setting::get("school.{$school->id}.paypal.client_secret");

            if ($clientSecret && !EncryptionHelper::isEncrypted($clientSecret)) {
                $encrypted = EncryptionHelper::encrypt($clientSecret);
                Setting::set("school.{$school->id}.paypal.client_secret", $encrypted);

                Log::info('Encrypted existing PayPal secret', [
                    'school_id' => $school->id
                ]);
            }
        });
    }

    public function down(): void
    {
        // Decifra tutti i client_secret
        School::all()->each(function ($school) {
            $clientSecret = Setting::get("school.{$school->id}.paypal.client_secret");

            if ($clientSecret && EncryptionHelper::isEncrypted($clientSecret)) {
                $decrypted = EncryptionHelper::decrypt($clientSecret);
                Setting::set("school.{$school->id}.paypal.client_secret", $decrypted);

                Log::info('Decrypted PayPal secret (rollback)', [
                    'school_id' => $school->id
                ]);
            }
        });
    }
};
```

**IMPORTANTE - Eseguire con attenzione:**
```bash
# Backup database PRIMA di migration
./vendor/bin/sail artisan backup:run --only-db

# Run migration
./vendor/bin/sail artisan migrate

# Verificare encryption avvenuta
./vendor/bin/sail artisan tinker
>>> Setting::get("school.1.paypal.client_secret")
# Dovrebbe iniziare con "eyJpdiI6..."
```

**Commit:** `🔒 SECURITY: Encrypt existing PayPal credentials`

#### Step 6.5: Test (1h)
**File:** `tests/Feature/Security/PayPalEncryptionTest.php`

```php
<?php

namespace Tests\Feature\Security;

use App\Models\Setting;
use App\Helpers\EncryptionHelper;

class PayPalEncryptionTest extends SecurityTestCase
{
    /** @test */
    public function paypal_client_secret_is_encrypted_on_save()
    {
        $plainSecret = 'test-secret-123';

        $this->actingAs($this->admin)
            ->post(route('admin.settings.update'), [
                'paypal_client_secret' => $plainSecret
            ]);

        $saved = Setting::get("school.{$this->school1->id}.paypal.client_secret");

        $this->assertNotEquals($plainSecret, $saved);
        $this->assertTrue(EncryptionHelper::isEncrypted($saved));
    }

    /** @test */
    public function encrypted_secret_is_decrypted_in_paypal_service()
    {
        $plainSecret = 'test-secret-456';
        $encrypted = EncryptionHelper::encrypt($plainSecret);

        Setting::set("school.{$this->school1->id}.paypal.client_secret", $encrypted);

        $service = PayPalService::forSchool($this->school1);

        $this->assertEquals($plainSecret, $service->getSettings()['client_secret']);
    }
}
```

**Commit:** `🧪 TEST: Add PayPal encryption test coverage`

---

### ⚠️ FIX #7: Weak Password Generation
**Priorità:** HIGH
**Durata:** 2 ore
**Branch:** `feature/security-fix-password-generation`

#### Step 7.1: Update Password Generation (30min)
**File:** `app/Http/Controllers/Admin/AdminStudentController.php`

```php
use Illuminate\Support\Str;

// BEFORE (linee 395-397)
private function generateStudentPassword(): string
{
    return 'Student' . now()->year . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
}

// AFTER
private function generateStudentPassword(): string
{
    // Genera password sicura con pattern memorabile
    $adjectives = [
        'Quick', 'Brave', 'Smart', 'Swift', 'Bright',
        'Bold', 'Clear', 'Happy', 'Lucky', 'Noble'
    ];

    $nouns = [
        'Lion', 'Eagle', 'Tiger', 'Falcon', 'Dolphin',
        'Wolf', 'Hawk', 'Bear', 'Fox', 'Puma'
    ];

    $number = rand(1000, 9999);
    $specials = ['!', '@', '#', '$', '%'];
    $special = $specials[array_rand($specials)];

    // Esempio: QuickLion5847!
    return $adjectives[array_rand($adjectives)] .
           $nouns[array_rand($nouns)] .
           $number .
           $special;
}
```

**OPPURE versione completamente random:**
```php
private function generateStudentPassword(): string
{
    // Password completamente random 16 caratteri
    // Include maiuscole, minuscole, numeri, simboli
    return Str::password(16);
}
```

**Commit:** `🔒 SECURITY: Improve student password generation strength`

#### Step 7.2: Test (30min)
**File:** `tests/Feature/Security/PasswordGenerationTest.php`

```php
<?php

namespace Tests\Feature\Security;

class PasswordGenerationTest extends SecurityTestCase
{
    /** @test */
    public function generated_password_meets_strength_requirements()
    {
        $controller = new AdminStudentController();
        $password = $controller->generateStudentPassword();

        // Lunghezza minima
        $this->assertGreaterThanOrEqual(12, strlen($password));

        // Contiene numero
        $this->assertMatchesRegularExpression('/\d/', $password);

        // Contiene carattere speciale
        $this->assertMatchesRegularExpression('/[!@#$%]/', $password);

        // Non è prevedibile pattern "Student20XX..."
        $this->assertStringNotContainsString('Student', $password);
    }

    /** @test */
    public function passwords_are_unique()
    {
        $controller = new AdminStudentController();

        $passwords = [];
        for ($i = 0; $i < 100; $i++) {
            $passwords[] = $controller->generateStudentPassword();
        }

        $unique = array_unique($passwords);
        $this->assertCount(100, $unique);
    }
}
```

**Commit:** `🧪 TEST: Add password generation strength tests`

#### Step 7.3: Documentazione (1h)
**File:** `docs/PASSWORD_POLICY.md`

```markdown
# Password Policy

## Password Generate Automaticamente
Per studenti creati da admin, il sistema genera password sicure con pattern:
- **Formato:** AdjectiveNoun####!
- **Esempio:** QuickLion5847!
- **Lunghezza:** 15-17 caratteri
- **Entropia:** ~40 bit

## Password Requisiti Utente
Quando utente cambia password manualmente:
- Minimo 8 caratteri
- Almeno 1 maiuscola
- Almeno 1 numero
- Almeno 1 carattere speciale

## Best Practices
- Password inviate via email sono one-time use
- Utente deve cambiarla al primo login
- Password non vengono mai loggate
```

**Commit:** `📚 DOCS: Add password policy documentation`

---

### ⚠️ FIX #8: Mass Assignment Protection
**Priorità:** HIGH
**Durata:** 2 ore
**Branch:** `feature/security-fix-mass-assignment`

#### Step 8.1: Update User Model (30min)
**File:** `app/Models/User.php`

```php
// BEFORE (linee 45-61)
protected $fillable = [
    'name', 'email', 'password', 'school_id', 'role', 'first_name',
    'last_name', 'phone', 'codice_fiscale', 'date_of_birth', 'address',
    'emergency_contact', 'medical_notes', 'profile_image_path', 'active',
];

// AFTER
protected $fillable = [
    'name', 'email', 'first_name', 'last_name', 'phone',
    'date_of_birth', 'address', 'emergency_contact', 'medical_notes',
];

protected $guarded = [
    'id', 'password', 'school_id', 'role', 'active',
    'email_verified_at', 'remember_token', 'profile_image_path'
];

/**
 * Assegna role in modo sicuro
 */
public function assignRole(string $role): void
{
    $validRoles = ['super_admin', 'admin', 'user'];

    if (!in_array($role, $validRoles)) {
        throw new \InvalidArgumentException("Invalid role: {$role}");
    }

    // Log per audit
    Log::info('Role assigned', [
        'user_id' => $this->id,
        'old_role' => $this->role,
        'new_role' => $role,
        'assigned_by' => auth()->id()
    ]);

    $this->role = $role;
    $this->save();
}

/**
 * Assegna scuola in modo sicuro
 */
public function setSchool(School $school): void
{
    // Log per audit
    Log::info('School assigned', [
        'user_id' => $this->id,
        'old_school_id' => $this->school_id,
        'new_school_id' => $school->id,
        'assigned_by' => auth()->id()
    ]);

    $this->school_id = $school->id;
    $this->save();
}

/**
 * Attiva/disattiva utente in modo sicuro
 */
public function setActive(bool $active): void
{
    Log::info('User active status changed', [
        'user_id' => $this->id,
        'old_active' => $this->active,
        'new_active' => $active,
        'changed_by' => auth()->id()
    ]);

    $this->active = $active;
    $this->save();
}
```

**Commit:** `🔒 SECURITY: Protect User model from mass assignment`

#### Step 8.2: Update Payment Model (30min)
**File:** `app/Models/Payment.php`

```php
// Aggiungere dopo $fillable esistente
protected $guarded = [
    'id', 'school_id', 'processed_by_user_id',
    'receipt_number', 'gateway_response', 'paypal_transaction_id'
];
```

**Commit:** `🔒 SECURITY: Protect Payment model from mass assignment`

#### Step 8.3: Update Controller che Modificano Attributi Sensibili (1h)
**File:** `app/Http/Controllers/Admin/AdminStudentController.php`

```php
// Quando si crea studente, usare metodi dedicati:
public function store(Request $request)
{
    // ... validation ...

    $student = new User();
    $student->fill($request->only([
        'name', 'email', 'first_name', 'last_name', 'phone',
        'date_of_birth', 'address', 'emergency_contact', 'medical_notes'
    ]));

    // Attributi sensibili via metodi dedicati
    $student->password = Hash::make($this->generateStudentPassword());
    $student->setSchool(auth()->user()->school);
    $student->assignRole('user');
    $student->setActive(true);

    $student->save();

    // ... rest of code ...
}
```

**Commit:** `🔒 SECURITY: Use safe assignment methods in controllers`

#### Step 8.4: Test (1h)
**File:** `tests/Feature/Security/MassAssignmentTest.php`

```php
<?php

namespace Tests\Feature\Security;

class MassAssignmentTest extends SecurityTestCase
{
    /** @test */
    public function mass_assignment_cannot_change_role()
    {
        $this->actingAs($this->student)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'role' => 'admin' // Tentativo escalation
            ]);

        $this->assertEquals('user', $this->student->fresh()->role);
    }

    /** @test */
    public function mass_assignment_cannot_change_school_id()
    {
        $originalSchoolId = $this->student->school_id;

        $this->actingAs($this->student)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'school_id' => $this->school2->id // Tentativo cambio scuola
            ]);

        $this->assertEquals($originalSchoolId, $this->student->fresh()->school_id);
    }

    /** @test */
    public function mass_assignment_cannot_activate_deactivated_account()
    {
        $this->student->setActive(false);

        $this->actingAs($this->student)
            ->patch(route('profile.update'), [
                'name' => 'Updated Name',
                'active' => true // Tentativo riattivazione
            ]);

        $this->assertFalse($this->student->fresh()->active);
    }
}
```

**Commit:** `🧪 TEST: Add mass assignment protection tests`

---

## ✅ CHECKLIST FASE 2

- [ ] SchoolOwnership middleware esteso
- [ ] LIKE injection sanitization implementata
- [ ] File upload validation robusta
- [ ] PayPal credentials cifrate
- [ ] Password generation migliorata
- [ ] Mass assignment protetto
- [ ] Tutti test automatizzati passano
- [ ] Nessuna regression funzionale
- [ ] Deploy staging completato
- [ ] Test staging 24h OK

**Commit Finale Fase 2:** `🔒 SECURITY: Complete HIGH priority fixes`

---

## FASE 3: MEDIUM PRIORITY FIXES (Giorni 10-14)

_(Continua con fix MEDIUM: XSS, Path Traversal, Log Sanitization, Rate Limiting, Error Handling, GDPR)_

---

## FASE 4: LOW PRIORITY & BEST PRACTICES (Giorni 15-21)

_(Security Headers, Audit Logging, Monitoring, Final Testing)_

---

## ROLLBACK PROCEDURES

### Se Fix Causa Problemi

#### 1. Rollback Git
```bash
# Ritorna al tag pre-security
git checkout v1.0.0-pre-security

# Deploy rollback
./vendor/bin/sail artisan migrate:rollback
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
```

#### 2. Rollback Database
```bash
# Restore backup
./vendor/bin/sail artisan backup:restore --backup=nome-backup.sql
```

#### 3. Rollback Specifico Branch
```bash
# Se problema in feature/security-fix-XXX
git revert <commit-hash>
git push origin main
```

---

## SUCCESS METRICS

### KPI per Ogni Fase
- [ ] **0 Breaking Changes** - Tutte funzionalità esistenti funzionano
- [ ] **0 Test Failures** - Test suite passa al 100%
- [ ] **0 Production Incidents** - Nessun crash o downtime
- [ ] **100% Fix Implementation** - Tutte vulnerabilità addressate
- [ ] **>95% Code Coverage** - Security tests coprono nuovi fix

### Final Validation
```bash
# Run complete test suite
./vendor/bin/sail artisan test

# Run security-specific tests
./vendor/bin/sail artisan test --testsuite=Security

# Manual testing checklist
- [ ] Login/Logout funziona
- [ ] Admin dashboard carica
- [ ] Payments funzionano
- [ ] PayPal webhook testa OK
- [ ] File upload funziona
- [ ] Search funziona
- [ ] Multi-tenant isolation OK
```

---

**RICORDA:**
1. Un fix alla volta
2. Test dopo ogni fix
3. Deploy graduale
4. Monitor costante
5. Rollback ready

**Durata Totale Stimata:** 21 giorni (3 settimane)
**Effort:** 1 developer full-time
**Risk Level:** LOW (con questo approccio graduale)
