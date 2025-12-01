# 🎯 ROADMAP: Sistema Eventi Pubblici con Guest Users

**Progetto:** DanzaFacile - Sistema Gestione Eventi Pubblici
**Versione:** 1.0.0
**Data Inizio:** [DA COMPILARE]
**Team Lead:** [DA COMPILARE]
**Durata Stimata:** 6-8 settimane

---

## 📋 Executive Summary

### Obiettivo
Implementare sistema completo di gestione eventi pubblici con:
- Registrazioni guest (utenti non iscritti alla scuola)
- Pagamenti differenziati (studenti vs guest)
- Landing page personalizzabili per scuola
- Magic link access per guest
- Sistema QR code check-in
- Email automation
- Cleanup automatico guest dopo 6 mesi

### Requisiti Funzionali Chiave
- ✅ Eventi pubblici accessibili senza login
- ✅ Prezzi duali: studenti della scuola / guest esterni
- ✅ Pagamento online obbligatorio prima iscrizione
- ✅ Magic link per accesso guest (niente password)
- ✅ QR code per check-in evento
- ✅ Landing page personalizzabile per ogni scuola
- ✅ Condivisione social con link brevi
- ✅ GDPR compliant (consensi espliciti)
- ✅ reCAPTCHA per anti-spam (gratuito)
- ✅ Email automatiche (conferma, reminder, thank you, feedback)
- ✅ Cleanup automatico guest dopo 6 mesi dall'ultimo evento

### Vincoli Tecnici
- ⚠️ ZERO breaking changes al sistema esistente
- ⚠️ Compatibilità backward con eventi privati attuali
- ⚠️ App Flutter mostra SOLO eventi della scuola dello studente
- ⚠️ Guest NON possono accedere all'app mobile

---

## 📊 Progress Tracker

### Fase 0: Preparazione (Settimana 1) ✅ COMPLETATA
- [x] 0.1 Audit Privacy Policy & GDPR
- [x] 0.2 Setup Environment & Tools

### Fase 1: Database (Settimana 1-2) ✅ COMPLETATA
- [x] 1.1 Migration: Guest fields in users table
- [x] 1.2 Migration: Dual pricing in events table
- [x] 1.3 Migration: Event payments table
- [x] 1.4 Migration: GDPR consents table
- [x] 1.5 Migration: QR code in registrations

### Fase 2: Backend Core (Settimana 2-3) ✅ COMPLETATA
- [x] 2.1 Model: User - Guest methods
- [x] 2.2 Model: Event - Pricing methods
- [x] 2.3 Model: EventPayment
- [x] 2.4 Model: GdprConsent
- [x] 2.5 Service: GuestRegistrationService
- [x] 2.6 Service: PaymentService
- [x] 2.7 Service: QRCodeService

### Fase 3: Controllers & Routes (Settimana 3-4)
- [ ] 3.1 Controller: PublicEventController
- [ ] 3.2 Controller: GuestDashboardController
- [ ] 3.3 Controller: PaymentController
- [ ] 3.4 Controller: QRCheckinController
- [ ] 3.5 Routes: Public + Guest

### Fase 4: Frontend Views (Settimana 4-5)
- [ ] 4.1 Landing page evento pubblico
- [ ] 4.2 Form registrazione guest
- [ ] 4.3 Guest dashboard (magic link)
- [ ] 4.4 Payment flow
- [ ] 4.5 Admin: gestione prezzi evento
- [ ] 4.6 Admin: QR scanner check-in

### Fase 5: Email System (Settimana 5)
- [ ] 5.1 Email: Conferma registrazione
- [ ] 5.2 Email: Reminder 1 giorno prima
- [ ] 5.3 Email: Thank you post evento
- [ ] 5.4 Email: Richiesta feedback
- [ ] 5.5 Email: Notifica admin nuova iscrizione

### Fase 6: Pagamenti PayPal (Settimana 5-6)
- [ ] 6.1 Setup PayPal SDK
- [ ] 6.2 Payment flow integration
- [ ] 6.3 Webhook listener
- [ ] 6.4 Refund system
- [ ] 6.5 Testing sandbox

### Fase 7: Automazioni (Settimana 6)
- [ ] 7.1 Command: Cleanup guest automatico
- [ ] 7.2 Job: Email reminder scheduler
- [ ] 7.3 Job: Email feedback post-evento
- [ ] 7.4 Observer: Event lifecycle hooks

### Fase 8: Admin Features (Settimana 6-7)
- [ ] 8.1 Dashboard eventi pubblici
- [ ] 8.2 Landing page customization
- [ ] 8.3 QR code scanner UI
- [ ] 8.4 Report iscrizioni guest
- [ ] 8.5 Export data guest

### Fase 9: Testing & Deploy (Settimana 7-8)
- [ ] 9.1 Unit tests
- [ ] 9.2 Feature tests
- [ ] 9.3 Integration tests PayPal
- [ ] 9.4 User acceptance testing
- [ ] 9.5 Deploy staging
- [ ] 9.6 Deploy production

---

# 🔧 FASE 0: Preparazione & Analisi

## 0.1 Audit Privacy Policy & GDPR

**Owner:** Legal/Compliance + Dev Lead
**Durata:** 2-3 giorni

### Tasks

#### ☐ Verifica Privacy Policy Esistente

**File da verificare:**
```
/resources/views/privacy-policy.blade.php (se esiste)
/public/privacy-policy.pdf (se esiste)
```

**Checklist GDPR Compliance:**
- [ ] Identità e contatti del Titolare del trattamento
- [ ] Base giuridica del trattamento
- [ ] Finalità del trattamento dati
- [ ] Categorie di dati personali trattati
- [ ] Destinatari dei dati
- [ ] Periodo di conservazione
- [ ] Diritti dell'interessato (accesso, rettifica, cancellazione)
- [ ] Diritto di revocare il consenso
- [ ] Diritto di proporre reclamo
- [ ] Informazioni su trasferimenti dati extra-UE
- [ ] Cookie policy

**Azioni da Fare:**
```bash
# Se privacy policy non esiste, creare:
php artisan make:controller PrivacyPolicyController

# Creare route
Route::get('/privacy-policy', [PrivacyPolicyController::class, 'show'])->name('privacy.policy');
Route::get('/cookie-policy', [PrivacyPolicyController::class, 'cookies'])->name('privacy.cookies');
```

#### ☐ Creare Template Consensi Riutilizzabili

**File:** `resources/views/components/gdpr-consent-checkbox.blade.php`

```blade
@props([
    'type' => 'privacy',
    'required' => true,
    'checked' => false,
    'name' => null
])

@php
$consentTexts = [
    'privacy' => 'Ho letto e accetto la <a href="'.route('privacy.policy').'" target="_blank" class="text-blue-600 underline">Privacy Policy</a>',
    'marketing' => 'Acconsento all\'invio di comunicazioni promozionali e newsletter',
    'terms' => 'Accetto i <a href="'.route('terms.conditions').'" target="_blank" class="text-blue-600 underline">Termini e Condizioni</a>',
];

$inputName = $name ?? 'consent_' . $type;
@endphp

<div class="flex items-start">
    <input
        type="checkbox"
        id="{{ $inputName }}"
        name="{{ $inputName }}"
        value="1"
        {{ $required ? 'required' : '' }}
        {{ $checked ? 'checked' : '' }}
        class="mt-1 h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded"
    >
    <label for="{{ $inputName }}" class="ml-2 text-sm text-gray-700">
        {!! $consentTexts[$type] !!}
        @if($required)
            <span class="text-red-500">*</span>
        @endif
    </label>
</div>
@error($inputName)
    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
@enderror
```

**Output:**
- [ ] Privacy policy verificata/creata
- [ ] Cookie policy verificata/creata
- [ ] Component consensi GDPR creato
- [ ] Routes privacy/cookies configurate

---

## 0.2 Setup Environment & Tools

**Owner:** DevOps + Backend Dev
**Durata:** 1-2 giorni

### ☐ Git Branch Strategy

```bash
# Crea feature branch
git checkout -b feature/public-events-system

# Crea branch sviluppo
git checkout -b develop/public-events-system
```

**Workflow:**
```
feature/public-events-system (main feature branch)
  ├── develop/database-migrations
  ├── develop/backend-services
  ├── develop/frontend-landing
  ├── develop/payment-integration
  └── develop/email-system
```

### ☐ Installazione Dipendenze

**Composer packages:**
```bash
# PayPal SDK
composer require paypal/rest-api-sdk-php

# QR Code generator
composer require simplesoftwareio/simple-qrcode

# reCAPTCHA validation
composer require google/recaptcha
```

**NPM packages (frontend):**
```bash
npm install --save-dev qrcode.react
```

### ☐ Environment Variables

**File:** `.env`

Aggiungi le seguenti variabili:

```env
# PayPal Configuration
PAYPAL_MODE=sandbox  # sandbox o live
PAYPAL_SANDBOX_CLIENT_ID=your_sandbox_client_id
PAYPAL_SANDBOX_SECRET=your_sandbox_secret
PAYPAL_LIVE_CLIENT_ID=your_live_client_id
PAYPAL_LIVE_SECRET=your_live_secret

# reCAPTCHA
RECAPTCHA_SITE_KEY=your_recaptcha_site_key
RECAPTCHA_SECRET_KEY=your_recaptcha_secret_key

# Guest System
GUEST_TOKEN_EXPIRY_DAYS=180  # 6 mesi
GUEST_CLEANUP_ENABLED=true
GUEST_CLEANUP_AFTER_DAYS=180

# Email Settings
MAIL_FROM_ADDRESS=noreply@danzafacile.it
MAIL_FROM_NAME="DanzaFacile"

# Public Events
PUBLIC_EVENTS_ENABLED=true
PUBLIC_EVENTS_REQUIRE_VERIFICATION=false
```

**File:** `config/services.php`

Aggiungi configurazione PayPal:

```php
'paypal' => [
    'mode' => env('PAYPAL_MODE', 'sandbox'),
    'sandbox' => [
        'client_id' => env('PAYPAL_SANDBOX_CLIENT_ID'),
        'secret' => env('PAYPAL_SANDBOX_SECRET'),
    ],
    'live' => [
        'client_id' => env('PAYPAL_LIVE_CLIENT_ID'),
        'secret' => env('PAYPAL_LIVE_SECRET'),
    ],
],

'recaptcha' => [
    'site_key' => env('RECAPTCHA_SITE_KEY'),
    'secret_key' => env('RECAPTCHA_SECRET_KEY'),
],
```

### ☐ Setup PayPal Sandbox

**Steps:**

1. **Crea account PayPal Developer:**
   - Vai su https://developer.paypal.com
   - Login/Registrati
   - Dashboard → My Apps & Credentials

2. **Crea App Sandbox:**
   - Click "Create App"
   - Nome: "DanzaFacile Events"
   - Tipo: Merchant
   - Salva Client ID e Secret

3. **Crea Account Test:**
   - Dashboard → Sandbox → Accounts
   - Crea Business Account (per ricevere pagamenti)
   - Crea Personal Account (per testare pagamenti)
   - Annota email e password

4. **Configura .env:**
   ```env
   PAYPAL_MODE=sandbox
   PAYPAL_SANDBOX_CLIENT_ID=AeB...xyz
   PAYPAL_SANDBOX_SECRET=ELx...abc
   ```

### ☐ Setup Google reCAPTCHA v2

**Steps:**

1. **Registra sito:**
   - Vai su https://www.google.com/recaptcha/admin
   - Click "+" per nuovo sito
   - Label: DanzaFacile
   - Tipo: reCAPTCHA v2 → "I'm not a robot" Checkbox
   - Domini: `danzafacile.it`, `localhost` (per testing)

2. **Ottieni chiavi:**
   - Site Key (pubblica, va nel frontend)
   - Secret Key (privata, va nel backend)

3. **Configura .env:**
   ```env
   RECAPTCHA_SITE_KEY=6Lc...xyz
   RECAPTCHA_SECRET_KEY=6Lc...abc
   ```

### ☐ Validation Rule reCAPTCHA

**File:** `app/Rules/Recaptcha.php`

```bash
php artisan make:rule Recaptcha
```

```php
<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements Rule
{
    public function passes($attribute, $value)
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $value,
            'remoteip' => request()->ip()
        ]);

        return $response->json('success');
    }

    public function message()
    {
        return 'La verifica reCAPTCHA è fallita. Riprova.';
    }
}
```

**Uso:**
```php
$request->validate([
    'g-recaptcha-response' => ['required', new \App\Rules\Recaptcha],
]);
```

### ☐ Setup Email Testing

**Opzione A - Mailpit (già configurato in Sail):**
```bash
# Accedi a http://localhost:8025 per vedere email
# Nessuna configurazione extra necessaria
```

**Opzione B - Mailtrap:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

### ☐ Database Backup Pre-Migration

```bash
# Backup database prima delle migration
php artisan db:backup

# O manualmente:
./vendor/bin/sail mysql
> CREATE DATABASE danzafacile_backup;
> exit

mysqldump -u sail -p danzafacile > backup_$(date +%Y%m%d).sql
```

---

**Output Fase 0:**
- [x] Git branch creato
- [x] Dipendenze installate
- [x] PayPal sandbox configurato
- [x] reCAPTCHA configurato
- [x] Email testing pronto
- [x] Backup database fatto
- [x] Privacy policy verificata/creata

---

# 🗄️ FASE 1: Database Schema

## 1.1 Migration: Guest Fields in Users Table

**File:** `database/migrations/YYYY_MM_DD_add_guest_fields_to_users_table.php`

```bash
php artisan make:migration add_guest_fields_to_users_table --table=users
```

**Codice Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Guest user identification
            $table->boolean('is_guest')->default(false)->after('school_id');
            $table->string('guest_token', 64)->nullable()->unique()->after('is_guest');
            $table->timestamp('guest_token_expires_at')->nullable()->after('guest_token');

            // Guest contact info
            $table->string('guest_phone', 20)->nullable()->after('phone');

            // Archive system
            $table->boolean('is_archived')->default(false)->after('guest_token_expires_at');
            $table->timestamp('archived_at')->nullable()->after('is_archived');
            $table->string('archive_reason')->nullable()->after('archived_at');

            // Indexes for performance
            $table->index('is_guest');
            $table->index('guest_token');
            $table->index(['is_guest', 'is_archived']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_guest']);
            $table->dropIndex(['guest_token']);
            $table->dropIndex(['is_guest', 'is_archived']);

            $table->dropColumn([
                'is_guest',
                'guest_token',
                'guest_token_expires_at',
                'guest_phone',
                'is_archived',
                'archived_at',
                'archive_reason'
            ]);
        });
    }
};
```

**Testing:**

```bash
# Run migration
php artisan migrate

# Verify columns
php artisan tinker
>>> DB::select("SHOW COLUMNS FROM users WHERE Field LIKE '%guest%'");

# Rollback test
php artisan migrate:rollback --step=1
php artisan migrate
```

**✅ Checklist:**
- [ ] Migration creata
- [ ] Migration eseguita senza errori
- [ ] Colonne verificate in database
- [ ] Indexes creati correttamente
- [ ] Rollback testato e funzionante

---

## 1.2 Migration: Dual Pricing in Events Table

**File:** `database/migrations/YYYY_MM_DD_add_dual_pricing_to_events_table.php`

```bash
php artisan make:migration add_dual_pricing_to_events_table --table=events
```

**Codice Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Rename existing price → price_students
            // (will do data migration separately)

            // Add new pricing fields
            $table->decimal('price_students', 10, 2)->default(0.00)->after('price');
            $table->decimal('price_guests', 10, 2)->default(0.00)->after('price_students');

            // Payment settings
            $table->boolean('requires_payment')->default(false)->after('price_guests');
            $table->enum('payment_method', ['paypal', 'stripe', 'onsite', 'free'])->default('free')->after('requires_payment');

            // Landing page settings
            $table->string('slug')->unique()->nullable()->after('name');
            $table->text('landing_description')->nullable()->after('description');
            $table->string('landing_cta_text', 100)->default('Iscriviti Ora')->after('landing_description');

            // Check-in QR
            $table->boolean('qr_checkin_enabled')->default(true)->after('landing_cta_text');

            // Indexes
            $table->index('slug');
        });

        // Data migration: copy price → price_students
        DB::statement('UPDATE events SET price_students = price');

        // Drop old price column
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Restore price column
            $table->decimal('price', 10, 2)->default(0.00)->after('max_participants');
        });

        // Restore data: price_students → price
        DB::statement('UPDATE events SET price = price_students');

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex(['slug']);

            $table->dropColumn([
                'price_students',
                'price_guests',
                'requires_payment',
                'payment_method',
                'slug',
                'landing_description',
                'landing_cta_text',
                'qr_checkin_enabled'
            ]);
        });
    }
};
```

**Testing:**

```bash
# Run migration
php artisan migrate

# Verify data migration
php artisan tinker
>>> \App\Models\Event::first()->only(['price_students', 'price_guests']);

# Test rollback
php artisan migrate:rollback --step=1
php artisan migrate
```

**✅ Checklist:**
- [ ] Migration creata
- [ ] Data migration testata (price → price_students)
- [ ] Migration eseguita senza errori
- [ ] Slug index creato
- [ ] Eventi esistenti non rotti
- [ ] Rollback funzionante

---

## 1.3 Migration: Event Payments Table

**File:** `database/migrations/YYYY_MM_DD_create_event_payments_table.php`

```bash
php artisan make:migration create_event_payments_table
```

**Codice Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('event_registration_id')->nullable()->constrained('event_registrations')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('school_id')->constrained('schools')->cascadeOnDelete();

            // Payment info
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EUR');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('payment_method', ['paypal', 'stripe', 'onsite', 'free'])->default('paypal');

            // External payment gateway
            $table->string('transaction_id')->nullable()->unique();
            $table->text('payment_gateway_response')->nullable(); // JSON response

            // Metadata
            $table->string('payer_email')->nullable();
            $table->string('payer_name')->nullable();

            // Timestamps
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['event_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index('transaction_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_payments');
    }
};
```

**Testing:**

```bash
php artisan migrate

# Test foreign keys
php artisan tinker
>>> DB::table('event_payments')->insert([
    'event_id' => 1,
    'user_id' => 1,
    'school_id' => 1,
    'amount' => 25.00,
    'status' => 'pending'
]);
>>> DB::table('event_payments')->count();
```

**✅ Checklist:**
- [ ] Tabella creata
- [ ] Foreign keys funzionanti
- [ ] Indexes creati
- [ ] Test insert riuscito
- [ ] Rollback testato

---

## 1.4 Migration: GDPR Consents Table

**File:** `database/migrations/YYYY_MM_DD_create_gdpr_consents_table.php`

```bash
php artisan make:migration create_gdpr_consents_table
```

**Codice Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gdpr_consents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Consent type: privacy, marketing, terms, cookies
            $table->string('consent_type', 50);
            $table->boolean('consented')->default(false);

            // Tracking for legal compliance
            $table->string('ip_address', 45)->nullable(); // IPv6 support
            $table->text('user_agent')->nullable();
            $table->timestamp('consented_at')->nullable();

            // Versioning (per future updates privacy policy)
            $table->string('policy_version', 20)->default('1.0');

            $table->timestamps();

            // Indexes
            $table->index(['user_id', 'consent_type']);
            $table->index('consent_type');
            $table->index('consented_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gdpr_consents');
    }
};
```

**✅ Checklist:**
- [ ] Tabella creata
- [ ] Indexes configurati
- [ ] Campo policy_version per versioning

---

## 1.5 Migration: QR Code in Event Registrations

**File:** `database/migrations/YYYY_MM_DD_add_qr_code_to_event_registrations_table.php`

```bash
php artisan make:migration add_qr_code_to_event_registrations_table --table=event_registrations
```

**Codice Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            // QR code for check-in
            $table->string('qr_code_token', 64)->unique()->nullable()->after('additional_info');

            // Check-in tracking
            $table->timestamp('checked_in_at')->nullable()->after('qr_code_token');
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete()->after('checked_in_at');

            // Indexes
            $table->index('qr_code_token');
            $table->index('checked_in_at');
        });
    }

    public function down(): void
    {
        Schema::table('event_registrations', function (Blueprint $table) {
            $table->dropIndex(['qr_code_token']);
            $table->dropIndex(['checked_in_at']);

            $table->dropColumn([
                'qr_code_token',
                'checked_in_at',
                'checked_in_by'
            ]);
        });
    }
};
```

**✅ Checklist:**
- [ ] Colonne aggiunte
- [ ] Unique constraint su qr_code_token
- [ ] Foreign key checked_in_by configurata

---

**Output Fase 1:**
- [x] Tutte le migration create
- [x] Migration eseguite su database locale
- [x] Rollback testati
- [x] Data migration verificata
- [x] Foreign keys funzionanti
- [x] Indexes ottimizzati

---

# 🔧 FASE 2: Backend Core - Models & Services

## 2.1 Model: User - Guest Methods

**File:** `app/Models/User.php`

Aggiungi al model User esistente:

```php
/**
 * Check if user is a guest (external to school)
 */
public function isGuest(): bool
{
    return $this->is_guest;
}

/**
 * Check if user is a regular student
 */
public function isStudent(): bool
{
    return !$this->is_guest && $this->school_id !== null;
}

/**
 * Generate magic link token for guest access
 *
 * @return string The generated token
 */
public function generateGuestToken(): string
{
    $this->guest_token = Str::random(64);
    $this->guest_token_expires_at = now()->addDays(config('app.guest_token_expiry_days', 180));
    $this->save();

    return $this->guest_token;
}

/**
 * Find user by guest token
 *
 * @param string $token
 * @return self|null
 */
public static function findByGuestToken(string $token): ?self
{
    return self::where('guest_token', $token)
               ->where('guest_token_expires_at', '>', now())
               ->where('is_archived', false)
               ->first();
}

/**
 * Archive guest user (soft delete for GDPR)
 *
 * @param string $reason
 * @return void
 */
public function archive(string $reason = 'auto_cleanup'): void
{
    $this->is_archived = true;
    $this->archived_at = now();
    $this->archive_reason = $reason;
    $this->save();
}

/**
 * Unarchive guest user
 */
public function unarchive(): void
{
    $this->is_archived = false;
    $this->archived_at = null;
    $this->archive_reason = null;
    $this->save();
}

/**
 * Get GDPR consents
 */
public function consents()
{
    return $this->hasMany(GdprConsent::class);
}

/**
 * Check if user has specific consent
 *
 * @param string $type privacy|marketing|terms
 * @return bool
 */
public function hasConsent(string $type): bool
{
    return $this->consents()
                ->where('consent_type', $type)
                ->where('consented', true)
                ->exists();
}

/**
 * Scope: Only guest users
 */
public function scopeGuests(Builder $query): Builder
{
    return $query->where('is_guest', true);
}

/**
 * Scope: Only students
 */
public function scopeStudents(Builder $query): Builder
{
    return $query->where('is_guest', false)->whereNotNull('school_id');
}

/**
 * Scope: Exclude archived users
 */
public function scopeNotArchived(Builder $query): Builder
{
    return $query->where('is_archived', false);
}

/**
 * Scope: Guest users eligible for cleanup
 * (no future events, last event > X days ago)
 */
public function scopeEligibleForCleanup(Builder $query, int $daysInactive = 180): Builder
{
    $cutoffDate = now()->subDays($daysInactive);

    return $query->guests()
                 ->notArchived()
                 ->whereDoesntHave('eventRegistrations', function($q) {
                     $q->whereHas('event', function($e) {
                         $e->where('end_date', '>', now());
                     });
                 })
                 ->where('updated_at', '<', $cutoffDate);
}
```

**Aggiungi a $fillable:**

```php
protected $fillable = [
    // ... existing ...
    'is_guest',
    'guest_token',
    'guest_token_expires_at',
    'guest_phone',
    'is_archived',
    'archived_at',
    'archive_reason',
];
```

**Aggiungi a $casts:**

```php
protected $casts = [
    // ... existing ...
    'is_guest' => 'boolean',
    'guest_token_expires_at' => 'datetime',
    'is_archived' => 'boolean',
    'archived_at' => 'datetime',
];
```

**Testing:**

```php
// In tinker:
$guest = User::factory()->create(['is_guest' => true]);
$token = $guest->generateGuestToken();
$found = User::findByGuestToken($token);
// $found->id === $guest->id
```

**✅ Checklist:**
- [ ] Metodi guest aggiunti a User model
- [ ] Scopes configurati
- [ ] Fillable/casts aggiornati
- [ ] Testing manuale in tinker

---

## 2.2 Model: Event - Pricing & Landing Methods

**File:** `app/Models/Event.php`

Aggiungi al model Event esistente:

```php
/**
 * Get price for specific user type
 *
 * @param User|null $user
 * @return float
 */
public function getPriceForUser(?User $user = null): float
{
    // Guest or no user → guest price
    if (!$user || $user->isGuest()) {
        return (float) $this->price_guests;
    }

    // Student of THIS school → student price
    if ($user->isStudent() && $user->school_id === $this->school_id) {
        return (float) $this->price_students;
    }

    // Student of ANOTHER school → guest price
    return (float) $this->price_guests;
}

/**
 * Check if user needs to pay for this event
 *
 * @param User|null $user
 * @return bool
 */
public function requiresPaymentForUser(?User $user = null): bool
{
    if (!$this->requires_payment) {
        return false;
    }

    return $this->getPriceForUser($user) > 0;
}

/**
 * Generate unique slug for this event
 *
 * @param string $name
 * @param int $schoolId
 * @return string
 */
public static function generateSlug(string $name, int $schoolId): string
{
    $slug = Str::slug($name);
    $originalSlug = $slug;
    $counter = 1;

    while (self::where('slug', $slug)
              ->where('school_id', $schoolId)
              ->exists()) {
        $slug = $originalSlug . '-' . $counter;
        $counter++;
    }

    return $slug;
}

/**
 * Get public landing page URL
 *
 * @return string
 */
public function getLandingUrlAttribute(): string
{
    return route('public.events.show', [
        'school' => $this->school->slug ?? $this->school_id,
        'event' => $this->slug
    ]);
}

/**
 * Get short URL for social sharing
 *
 * @return string
 */
public function getShortUrlAttribute(): string
{
    return url('/e/' . Str::limit($this->slug, 15, ''));
}

/**
 * Get QR code for event (for posters)
 *
 * @return string Base64 encoded QR
 */
public function getQrCodeAttribute(): string
{
    return \QrCode::format('svg')
                  ->size(300)
                  ->generate($this->landing_url);
}

/**
 * Scope: Public events only
 */
public function scopePublicEvents(Builder $query): Builder
{
    return $query->where('is_public', true)->where('active', true);
}

/**
 * Scope: Events with payment required
 */
public function scopeRequiresPayment(Builder $query): Builder
{
    return $query->where('requires_payment', true);
}

/**
 * Scope: Free events
 */
public function scopeFree(Builder $query): Builder
{
    return $query->where('price_students', 0)
                 ->where('price_guests', 0);
}

/**
 * Get guest registrations
 */
public function guestRegistrations()
{
    return $this->registrations()->whereHas('user', function($q) {
        $q->where('is_guest', true);
    });
}

/**
 * Get student registrations
 */
public function studentRegistrations()
{
    return $this->registrations()->whereHas('user', function($q) {
        $q->where('is_guest', false);
    });
}

/**
 * Get expected revenue
 */
public function getExpectedRevenueAttribute(): float
{
    $guestCount = $this->guestRegistrations()->count();
    $studentCount = $this->studentRegistrations()->count();

    return ($guestCount * $this->price_guests) +
           ($studentCount * $this->price_students);
}

/**
 * Relationship: Event payments
 */
public function eventPayments()
{
    return $this->hasMany(EventPayment::class);
}
```

**Aggiungi a $fillable:**

```php
protected $fillable = [
    // ... existing ...
    'price_students',
    'price_guests',
    'requires_payment',
    'payment_method',
    'slug',
    'landing_description',
    'landing_cta_text',
    'qr_checkin_enabled',
];
```

**Aggiungi a $casts:**

```php
protected $casts = [
    // ... existing ...
    'price_students' => 'decimal:2',
    'price_guests' => 'decimal:2',
    'requires_payment' => 'boolean',
    'qr_checkin_enabled' => 'boolean',
];
```

**Aggiungi a $appends (opzionale):**

```php
protected $appends = [
    'landing_url',
    'short_url',
    'expected_revenue',
];
```

**Observer: Auto-generate slug on create**

**File:** `app/Observers/EventObserver.php`

```bash
php artisan make:observer EventObserver --model=Event
```

```php
<?php

namespace App\Observers;

use App\Models\Event;

class EventObserver
{
    public function creating(Event $event): void
    {
        if (empty($event->slug)) {
            $event->slug = Event::generateSlug($event->name, $event->school_id);
        }
    }

    public function updating(Event $event): void
    {
        // If name changed and slug not manually set
        if ($event->isDirty('name') && !$event->isDirty('slug')) {
            $event->slug = Event::generateSlug($event->name, $event->school_id);
        }
    }
}
```

**Register Observer in** `app/Providers/EventServiceProvider.php`:

```php
use App\Models\Event;
use App\Observers\EventObserver;

public function boot(): void
{
    Event::observe(EventObserver::class);
}
```

**✅ Checklist:**
- [ ] Pricing methods aggiunti
- [ ] Slug auto-generation configurato
- [ ] Observer registrato
- [ ] Scopes pubblici/privati funzionanti
- [ ] Testing pricing logic

---

## 2.3 Model: EventPayment

**File:** `app/Models/EventPayment.php`

```bash
php artisan make:model EventPayment
```

**Codice Completo:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EventPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_registration_id',
        'user_id',
        'school_id',
        'amount',
        'currency',
        'status',
        'payment_method',
        'transaction_id',
        'payment_gateway_response',
        'payer_email',
        'payer_name',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'payment_gateway_response' => 'array',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function eventRegistration()
    {
        return $this->belongsTo(EventRegistration::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    // Scopes
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', 'failed');
    }

    public function scopeRefunded(Builder $query): Builder
    {
        return $query->where('status', 'refunded');
    }

    public function scopePaypal(Builder $query): Builder
    {
        return $query->where('payment_method', 'paypal');
    }

    // Methods
    public function markAsCompleted(string $transactionId, array $gatewayResponse = []): void
    {
        $this->update([
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'payment_gateway_response' => $gatewayResponse,
            'paid_at' => now(),
        ]);
    }

    public function markAsFailed(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => 'failed',
            'payment_gateway_response' => $gatewayResponse,
        ]);
    }

    public function markAsRefunded(array $gatewayResponse = []): void
    {
        $this->update([
            'status' => 'refunded',
            'payment_gateway_response' => $gatewayResponse,
        ]);
    }

    /**
     * Check if payment is successful
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if payment can be refunded
     */
    public function canBeRefunded(): bool
    {
        return $this->status === 'completed' &&
               $this->paid_at &&
               $this->paid_at->diffInDays(now()) <= 60; // PayPal refund limit
    }
}
```

**✅ Checklist:**
- [ ] Model creato
- [ ] Relationships configurate
- [ ] Scopes per status
- [ ] Metodi helper per status transitions

---

## 2.4 Model: GdprConsent

**File:** `app/Models/GdprConsent.php`

```bash
php artisan make:model GdprConsent
```

**Codice Completo:**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GdprConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'consent_type',
        'consented',
        'ip_address',
        'user_agent',
        'consented_at',
        'policy_version',
    ];

    protected $casts = [
        'consented' => 'boolean',
        'consented_at' => 'datetime',
    ];

    // Consent types constants
    const TYPE_PRIVACY = 'privacy';
    const TYPE_MARKETING = 'marketing';
    const TYPE_TERMS = 'terms';
    const TYPE_COOKIES = 'cookies';

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePrivacy(Builder $query): Builder
    {
        return $query->where('consent_type', self::TYPE_PRIVACY);
    }

    public function scopeMarketing(Builder $query): Builder
    {
        return $query->where('consent_type', self::TYPE_MARKETING);
    }

    public function scopeTerms(Builder $query): Builder
    {
        return $query->where('consent_type', self::TYPE_TERMS);
    }

    public function scopeConsented(Builder $query): Builder
    {
        return $query->where('consented', true);
    }

    // Static helper to record consent
    public static function recordConsent(
        int $userId,
        string $consentType,
        bool $consented,
        string $policyVersion = '1.0'
    ): self {
        return self::create([
            'user_id' => $userId,
            'consent_type' => $consentType,
            'consented' => $consented,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'consented_at' => $consented ? now() : null,
            'policy_version' => $policyVersion,
        ]);
    }

    /**
     * Get latest consent for user and type
     */
    public static function getLatestConsent(int $userId, string $consentType): ?self
    {
        return self::where('user_id', $userId)
                   ->where('consent_type', $consentType)
                   ->latest('consented_at')
                   ->first();
    }
}
```

**✅ Checklist:**
- [ ] Model creato
- [ ] Constanti per consent types
- [ ] Helper per registrare consensi
- [ ] Tracking IP e user agent

---

## 2.5 Service: GuestRegistrationService

**File:** `app/Services/GuestRegistrationService.php`

```bash
mkdir -p app/Services
touch app/Services/GuestRegistrationService.php
```

**Codice Completo:**

```php
<?php

namespace App\Services;

use App\Models\User;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\GdprConsent;
use App\Mail\GuestRegistrationConfirmation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class GuestRegistrationService
{
    /**
     * Register a guest user for an event
     *
     * @param Event $event
     * @param array $guestData ['name', 'email', 'phone', 'consents']
     * @return array ['user' => User, 'registration' => EventRegistration, 'magic_link' => string]
     * @throws \Exception
     */
    public function registerGuest(Event $event, array $guestData): array
    {
        return DB::transaction(function () use ($event, $guestData) {
            // 1. Find or create guest user
            $user = $this->findOrCreateGuestUser($guestData);

            // 2. Validate availability
            if ($event->is_full) {
                throw new \Exception('Evento al completo. Nessun posto disponibile.');
            }

            // 3. Check for duplicate registration
            $existingRegistration = EventRegistration::where('event_id', $event->id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingRegistration) {
                throw new \Exception('Sei già iscritto a questo evento.');
            }

            // 4. Create registration
            $registration = EventRegistration::create([
                'event_id' => $event->id,
                'user_id' => $user->id,
                'school_id' => $event->school_id,
                'status' => 'registered',
                'registration_date' => now(),
                'qr_code_token' => Str::random(64),
            ]);

            // 5. Record GDPR consents
            $this->recordConsents($user, $guestData['consents'] ?? []);

            // 6. Generate magic link
            $magicLink = $this->generateMagicLink($user);

            // 7. Send confirmation email
            $this->sendConfirmationEmail($user, $event, $registration, $magicLink);

            return [
                'user' => $user,
                'registration' => $registration->load('event'),
                'magic_link' => $magicLink,
            ];
        });
    }

    /**
     * Find existing guest or create new one
     */
    private function findOrCreateGuestUser(array $guestData): User
    {
        // Find by email
        $user = User::where('email', $guestData['email'])
                    ->where('is_guest', true)
                    ->first();

        if ($user) {
            // Update existing guest data
            $user->update([
                'name' => $guestData['name'],
                'guest_phone' => $guestData['phone'] ?? null,
            ]);

            return $user;
        }

        // Create new guest
        return User::create([
            'name' => $guestData['name'],
            'email' => $guestData['email'],
            'guest_phone' => $guestData['phone'] ?? null,
            'password' => bcrypt(Str::random(32)), // Random password (not used)
            'is_guest' => true,
            'school_id' => null, // Guest not attached to school
            'role' => 'user',
        ]);
    }

    /**
     * Record GDPR consents
     */
    private function recordConsents(User $user, array $consents): void
    {
        $consentTypes = ['privacy', 'marketing', 'terms'];

        foreach ($consentTypes as $type) {
            if (isset($consents[$type])) {
                GdprConsent::recordConsent(
                    $user->id,
                    $type,
                    (bool) $consents[$type]
                );
            }
        }
    }

    /**
     * Generate magic link for guest access
     */
    private function generateMagicLink(User $user): string
    {
        $token = $user->generateGuestToken();

        return route('guest.dashboard', ['token' => $token]);
    }

    /**
     * Send confirmation email to guest
     */
    private function sendConfirmationEmail(
        User $user,
        Event $event,
        EventRegistration $registration,
        string $magicLink
    ): void {
        Mail::to($user->email)->send(
            new GuestRegistrationConfirmation($user, $event, $registration, $magicLink)
        );
    }

    /**
     * Cancel guest registration
     */
    public function cancelRegistration(EventRegistration $registration): void
    {
        DB::transaction(function () use ($registration) {
            // Mark registration as cancelled
            $registration->update([
                'status' => 'cancelled',
            ]);

            // If payment exists, mark for refund
            $payment = $registration->payment;
            if ($payment && $payment->isCompleted() && $payment->canBeRefunded()) {
                // Will be handled by admin manually or via PaymentService
                $payment->update(['status' => 'refund_requested']);
            }
        });
    }
}
```

**✅ Checklist:**
- [ ] Service creato
- [ ] Logica registrazione guest completa
- [ ] GDPR consents tracking
- [ ] Magic link generation
- [ ] Email confirmation

---

## 2.6 Service: PaymentService

**File:** `app/Services/PaymentService.php`

```php
<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use App\Models\EventPayment;
use App\Models\EventRegistration;
use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment as PayPalPayment;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Api\PaymentExecution;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    private ApiContext $apiContext;

    public function __construct()
    {
        $this->setupPayPal();
    }

    /**
     * Setup PayPal API context
     */
    private function setupPayPal(): void
    {
        $mode = config('services.paypal.mode', 'sandbox');
        $credentials = config("services.paypal.{$mode}");

        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                $credentials['client_id'],
                $credentials['secret']
            )
        );

        $this->apiContext->setConfig([
            'mode' => $mode,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path('logs/paypal.log'),
            'log.LogLevel' => 'DEBUG',
        ]);
    }

    /**
     * Create PayPal payment for event registration
     *
     * @param Event $event
     * @param User $user
     * @param EventRegistration|null $registration
     * @return array ['payment' => EventPayment, 'approval_url' => string]
     * @throws \Exception
     */
    public function createPayment(Event $event, User $user, ?EventRegistration $registration = null): array
    {
        return DB::transaction(function () use ($event, $user, $registration) {
            // 1. Calculate amount
            $amount = $event->getPriceForUser($user);

            if ($amount <= 0) {
                throw new \Exception('Evento gratuito. Nessun pagamento richiesto.');
            }

            // 2. Create EventPayment record
            $eventPayment = EventPayment::create([
                'event_id' => $event->id,
                'event_registration_id' => $registration?->id,
                'user_id' => $user->id,
                'school_id' => $event->school_id,
                'amount' => $amount,
                'currency' => 'EUR',
                'status' => 'pending',
                'payment_method' => 'paypal',
                'payer_email' => $user->email,
                'payer_name' => $user->name,
            ]);

            // 3. Create PayPal payment
            $payer = new Payer();
            $payer->setPaymentMethod('paypal');

            $amountObj = new Amount();
            $amountObj->setCurrency('EUR')
                      ->setTotal($amount);

            $transaction = new Transaction();
            $transaction->setAmount($amountObj)
                        ->setDescription("Evento: {$event->name}")
                        ->setInvoiceNumber("EVT-{$event->id}-{$eventPayment->id}");

            $redirectUrls = new RedirectUrls();
            $redirectUrls->setReturnUrl(route('payment.success', ['payment' => $eventPayment->id]))
                         ->setCancelUrl(route('payment.cancel', ['payment' => $eventPayment->id]));

            $payment = new PayPalPayment();
            $payment->setIntent('sale')
                    ->setPayer($payer)
                    ->setRedirectUrls($redirectUrls)
                    ->setTransactions([$transaction]);

            // 4. Execute PayPal API call
            try {
                $payment->create($this->apiContext);

                // Get approval URL
                $approvalUrl = $payment->getApprovalLink();

                // Save PayPal payment ID
                $eventPayment->update([
                    'transaction_id' => $payment->getId(),
                    'payment_gateway_response' => $payment->toArray(),
                ]);

                return [
                    'payment' => $eventPayment,
                    'approval_url' => $approvalUrl,
                ];

            } catch (\Exception $e) {
                $eventPayment->markAsFailed([
                    'error' => $e->getMessage(),
                ]);

                throw new \Exception('Errore nella creazione del pagamento: ' . $e->getMessage());
            }
        });
    }

    /**
     * Execute PayPal payment after user approval
     *
     * @param string $paymentId PayPal payment ID
     * @param string $payerId PayPal payer ID
     * @return EventPayment
     * @throws \Exception
     */
    public function executePayment(string $paymentId, string $payerId): EventPayment
    {
        return DB::transaction(function () use ($paymentId, $payerId) {
            // 1. Find our payment record
            $eventPayment = EventPayment::where('transaction_id', $paymentId)
                                        ->where('status', 'pending')
                                        ->firstOrFail();

            // 2. Get PayPal payment
            $payment = PayPalPayment::get($paymentId, $this->apiContext);

            // 3. Execute payment
            $execution = new PaymentExecution();
            $execution->setPayerId($payerId);

            try {
                $result = $payment->execute($execution, $this->apiContext);

                // 4. Verify payment state
                if ($result->getState() === 'approved') {
                    $eventPayment->markAsCompleted(
                        $paymentId,
                        $result->toArray()
                    );

                    // 5. Confirm registration
                    if ($eventPayment->eventRegistration) {
                        $eventPayment->eventRegistration->update([
                            'status' => 'confirmed',
                        ]);
                    }

                    return $eventPayment;
                } else {
                    throw new \Exception('Pagamento non approvato da PayPal.');
                }

            } catch (\Exception $e) {
                $eventPayment->markAsFailed([
                    'error' => $e->getMessage(),
                ]);

                throw new \Exception('Errore nell\'esecuzione del pagamento: ' . $e->getMessage());
            }
        });
    }

    /**
     * Refund PayPal payment
     *
     * @param EventPayment $eventPayment
     * @param float|null $amount Full refund if null
     * @return bool
     */
    public function refundPayment(EventPayment $eventPayment, ?float $amount = null): bool
    {
        if (!$eventPayment->canBeRefunded()) {
            throw new \Exception('Pagamento non rimborsabile.');
        }

        // PayPal refund API implementation
        // (requires PayPal Sale ID from original transaction)
        // This is a simplified version - full implementation needed

        try {
            DB::transaction(function () use ($eventPayment) {
                $eventPayment->markAsRefunded([
                    'refunded_at' => now(),
                ]);

                // Cancel registration
                if ($eventPayment->eventRegistration) {
                    $eventPayment->eventRegistration->update([
                        'status' => 'cancelled',
                    ]);
                }
            });

            return true;
        } catch (\Exception $e) {
            \Log::error('PayPal refund failed: ' . $e->getMessage());
            return false;
        }
    }
}
```

**✅ Checklist:**
- [ ] PayPal SDK integrato
- [ ] Create payment implementato
- [ ] Execute payment implementato
- [ ] Refund implementato
- [ ] Error handling completo

---

## 2.7 Service: QRCodeService

**File:** `app/Services/QRCodeService.php`

```php
<?php

namespace App\Services;

use App\Models\EventRegistration;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Str;

class QRCodeService
{
    /**
     * Generate QR code for event registration
     *
     * @param EventRegistration $registration
     * @param string $format 'svg'|'png'
     * @param int $size
     * @return string
     */
    public function generateQRCode(
        EventRegistration $registration,
        string $format = 'svg',
        int $size = 300
    ): string {
        // Ensure registration has QR token
        if (empty($registration->qr_code_token)) {
            $registration->update([
                'qr_code_token' => Str::random(64),
            ]);
        }

        $qrData = $this->generateQRData($registration);

        return QrCode::format($format)
                     ->size($size)
                     ->errorCorrection('H')
                     ->generate($qrData);
    }

    /**
     * Generate QR code data (URL or JSON)
     *
     * @param EventRegistration $registration
     * @return string
     */
    private function generateQRData(EventRegistration $registration): string
    {
        // Option A: URL to check-in endpoint
        return route('checkin.verify', [
            'token' => $registration->qr_code_token
        ]);

        // Option B: JSON payload (for app-based scanning)
        // return json_encode([
        //     'event_id' => $registration->event_id,
        //     'registration_id' => $registration->id,
        //     'token' => $registration->qr_code_token,
        //     'timestamp' => now()->timestamp,
        // ]);
    }

    /**
     * Validate QR code token and check-in user
     *
     * @param string $token
     * @param int|null $staffUserId
     * @return array ['success' => bool, 'message' => string, 'registration' => EventRegistration|null]
     */
    public function validateAndCheckin(string $token, ?int $staffUserId = null): array
    {
        $registration = EventRegistration::where('qr_code_token', $token)
                                         ->with(['event', 'user'])
                                         ->first();

        if (!$registration) {
            return [
                'success' => false,
                'message' => 'QR Code non valido.',
                'registration' => null,
            ];
        }

        // Check if already checked in
        if ($registration->checked_in_at) {
            return [
                'success' => false,
                'message' => 'Utente già registrato in ingresso.',
                'registration' => $registration,
            ];
        }

        // Check if event is today
        $eventDate = $registration->event->start_date;
        if (!$eventDate->isToday() && !$eventDate->isFuture()) {
            return [
                'success' => false,
                'message' => 'Evento già concluso.',
                'registration' => $registration,
            ];
        }

        // Perform check-in
        $registration->update([
            'checked_in_at' => now(),
            'checked_in_by' => $staffUserId,
        ]);

        return [
            'success' => true,
            'message' => "Check-in effettuato per {$registration->user->name}",
            'registration' => $registration->fresh(),
        ];
    }

    /**
     * Generate downloadable QR code (for email attachments)
     *
     * @param EventRegistration $registration
     * @return string Base64 encoded PNG
     */
    public function generateDownloadableQR(EventRegistration $registration): string
    {
        $png = $this->generateQRCode($registration, 'png', 400);

        return base64_encode($png);
    }
}
```

**✅ Checklist:**
- [ ] QR code generation implementato
- [ ] Validation e check-in implementati
- [ ] Token security verificato
- [ ] Downloadable QR per email

---

**Output Fase 2:**
- [x] User model esteso con guest methods
- [x] Event model esteso con pricing
- [x] EventPayment model creato
- [x] GdprConsent model creato
- [x] GuestRegistrationService implementato
- [x] PaymentService con PayPal integrato
- [x] QRCodeService implementato

---

# 🎛️ FASE 3: Controllers & Routes

## 3.1 Controller: PublicEventController

**File:** `app/Http/Controllers/PublicEventController.php`

```bash
php artisan make:controller PublicEventController
```

**Codice Completo:**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\School;
use App\Services\GuestRegistrationService;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Rules\Recaptcha;

class PublicEventController extends Controller
{
    protected GuestRegistrationService $guestService;
    protected PaymentService $paymentService;

    public function __construct(
        GuestRegistrationService $guestService,
        PaymentService $paymentService
    ) {
        $this->guestService = $guestService;
        $this->paymentService = $paymentService;
    }

    /**
     * Show public landing page for event
     *
     * @param string $schoolSlug
     * @param string $eventSlug
     */
    public function show(string $schoolSlug, string $eventSlug)
    {
        $school = School::where('slug', $schoolSlug)->firstOrFail();

        $event = Event::where('school_id', $school->id)
                      ->where('slug', $eventSlug)
                      ->where('is_public', true)
                      ->where('active', true)
                      ->with(['school', 'registrations'])
                      ->firstOrFail();

        // Calculate stats
        $stats = [
            'total_registrations' => $event->registrations()->active()->count(),
            'available_spots' => $event->available_spots,
            'is_full' => $event->is_full,
            'registration_status' => $event->registration_status,
        ];

        return view('public.events.show', compact('event', 'school', 'stats'));
    }

    /**
     * Show registration form
     */
    public function registerForm(string $schoolSlug, string $eventSlug)
    {
        $school = School::where('slug', $schoolSlug)->firstOrFail();

        $event = Event::where('school_id', $school->id)
                      ->where('slug', $eventSlug)
                      ->where('is_public', true)
                      ->where('active', true)
                      ->firstOrFail();

        if ($event->is_full) {
            return redirect()
                ->route('public.events.show', [$schoolSlug, $eventSlug])
                ->with('error', 'Evento al completo.');
        }

        if ($event->registration_status === 'closed') {
            return redirect()
                ->route('public.events.show', [$schoolSlug, $eventSlug])
                ->with('error', 'Le iscrizioni sono chiuse.');
        }

        return view('public.events.register', compact('event', 'school'));
    }

    /**
     * Process guest registration
     */
    public function register(Request $request, string $schoolSlug, string $eventSlug)
    {
        $school = School::where('slug', $schoolSlug)->firstOrFail();

        $event = Event::where('school_id', $school->id)
                      ->where('slug', $eventSlug)
                      ->where('is_public', true)
                      ->where('active', true)
                      ->firstOrFail();

        // Validation
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'consent_privacy' => 'required|accepted',
            'consent_marketing' => 'nullable|boolean',
            'consent_terms' => 'required|accepted',
            'g-recaptcha-response' => ['required', new Recaptcha],
        ]);

        try {
            // Register guest
            $result = $this->guestService->registerGuest($event, [
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'consents' => [
                    'privacy' => true,
                    'marketing' => $validated['consent_marketing'] ?? false,
                    'terms' => true,
                ],
            ]);

            $user = $result['user'];
            $registration = $result['registration'];
            $magicLink = $result['magic_link'];

            // If event requires payment, redirect to payment
            if ($event->requiresPaymentForUser($user)) {
                $payment = $this->paymentService->createPayment($event, $user, $registration);

                return redirect($payment['approval_url']);
            }

            // Free event - confirm registration
            $registration->update(['status' => 'confirmed']);

            return view('public.events.success', compact('event', 'registration', 'magicLink'));

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * List all public events for a school
     */
    public function schoolEvents(string $schoolSlug)
    {
        $school = School::where('slug', $schoolSlug)->firstOrFail();

        $events = Event::where('school_id', $school->id)
                       ->where('is_public', true)
                       ->where('active', true)
                       ->upcoming()
                       ->with(['registrations'])
                       ->orderBy('start_date', 'asc')
                       ->paginate(12);

        return view('public.events.index', compact('school', 'events'));
    }

    /**
     * Short URL redirect (for social sharing)
     */
    public function shortUrl(string $slug)
    {
        $event = Event::where('slug', $slug)
                      ->where('is_public', true)
                      ->where('active', true)
                      ->firstOrFail();

        return redirect()->route('public.events.show', [
            'school' => $event->school->slug,
            'event' => $event->slug
        ]);
    }
}
```

**✅ Checklist:**
- [ ] Controller creato
- [ ] Landing page method
- [ ] Registration form method
- [ ] Guest registration processing
- [ ] Payment redirect implementato
- [ ] reCAPTCHA validation

---

## 3.2 Controller: GuestDashboardController

**File:** `app/Http/Controllers/GuestDashboardController.php`

```bash
php artisan make:controller GuestDashboardController
```

**Codice Completo:**

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\EventRegistration;
use App\Services\QRCodeService;
use Illuminate\Http\Request;

class GuestDashboardController extends Controller
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Magic link access to guest dashboard
     *
     * @param string $token
     */
    public function access(string $token)
    {
        $user = User::findByGuestToken($token);

        if (!$user) {
            abort(403, 'Link non valido o scaduto.');
        }

        // Log user in temporarily for this session
        auth()->login($user);

        return redirect()->route('guest.dashboard');
    }

    /**
     * Show guest dashboard
     */
    public function dashboard()
    {
        $user = auth()->user();

        if (!$user || !$user->isGuest()) {
            abort(403, 'Accesso negato.');
        }

        // Get upcoming events
        $upcomingRegistrations = $user->eventRegistrations()
            ->with(['event.school'])
            ->whereHas('event', function($q) {
                $q->where('end_date', '>=', now());
            })
            ->whereIn('status', ['registered', 'confirmed'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get past events
        $pastRegistrations = $user->eventRegistrations()
            ->with(['event.school'])
            ->whereHas('event', function($q) {
                $q->where('end_date', '<', now());
            })
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Generate QR codes for upcoming events
        foreach ($upcomingRegistrations as $registration) {
            $registration->qr_code_svg = $this->qrCodeService->generateQRCode($registration, 'svg', 250);
        }

        return view('guest.dashboard', compact('user', 'upcomingRegistrations', 'pastRegistrations'));
    }

    /**
     * Show single registration details
     */
    public function showRegistration(EventRegistration $registration)
    {
        $user = auth()->user();

        if (!$user || $registration->user_id !== $user->id) {
            abort(403, 'Accesso negato.');
        }

        $qrCode = $this->qrCodeService->generateQRCode($registration, 'svg', 400);

        return view('guest.registration-details', compact('registration', 'qrCode'));
    }

    /**
     * Download QR code ticket
     */
    public function downloadTicket(EventRegistration $registration)
    {
        $user = auth()->user();

        if (!$user || $registration->user_id !== $user->id) {
            abort(403, 'Accesso negato.');
        }

        $qrCodePng = $this->qrCodeService->generateQRCode($registration, 'png', 600);

        $filename = "ticket-{$registration->event->slug}-{$registration->id}.png";

        return response($qrCodePng)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Update guest profile
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        if (!$user || !$user->isGuest()) {
            abort(403, 'Accesso negato.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ]);

        $user->update([
            'name' => $validated['name'],
            'guest_phone' => $validated['phone'] ?? null,
        ]);

        return redirect()
            ->route('guest.dashboard')
            ->with('success', 'Profilo aggiornato con successo.');
    }

    /**
     * Logout guest
     */
    public function logout()
    {
        auth()->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home')
            ->with('success', 'Logout effettuato.');
    }
}
```

**✅ Checklist:**
- [ ] Magic link authentication
- [ ] Dashboard con eventi futuri/passati
- [ ] QR code display
- [ ] Download ticket
- [ ] Profile update

---

## 3.3 Controller: PaymentController

**File:** `app/Http/Controllers/PaymentController.php`

```bash
php artisan make:controller PaymentController
```

**Codice Completo:**

```php
<?php

namespace App\Http\Controllers;

use App\Models\EventPayment;
use App\Services\PaymentService;
use App\Mail\PaymentConfirmation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * PayPal payment success callback
     */
    public function success(Request $request, EventPayment $payment)
    {
        try {
            $paymentId = $request->get('paymentId');
            $payerId = $request->get('PayerID');

            if (!$paymentId || !$payerId) {
                throw new \Exception('Parametri pagamento mancanti.');
            }

            // Execute payment
            $completedPayment = $this->paymentService->executePayment($paymentId, $payerId);

            // Send confirmation email
            $user = $completedPayment->user;
            $event = $completedPayment->event;
            $registration = $completedPayment->eventRegistration;

            if ($user && $event) {
                Mail::to($user->email)->send(
                    new PaymentConfirmation($user, $event, $completedPayment, $registration)
                );
            }

            return view('payment.success', compact('completedPayment', 'event', 'registration'));

        } catch (\Exception $e) {
            \Log::error('Payment execution failed: ' . $e->getMessage());

            return redirect()
                ->route('payment.failed')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * PayPal payment cancel callback
     */
    public function cancel(EventPayment $payment)
    {
        $payment->update(['status' => 'cancelled']);

        return view('payment.cancelled', compact('payment'));
    }

    /**
     * Payment failed page
     */
    public function failed()
    {
        return view('payment.failed');
    }

    /**
     * Show payment details
     */
    public function show(EventPayment $payment)
    {
        // Only allow owner or admin to view
        $user = auth()->user();

        if (!$user || ($payment->user_id !== $user->id && !$user->isAdmin())) {
            abort(403, 'Accesso negato.');
        }

        return view('payment.show', compact('payment'));
    }
}
```

**✅ Checklist:**
- [ ] PayPal success callback
- [ ] PayPal cancel callback
- [ ] Payment confirmation email
- [ ] Payment details page

---

## 3.4 Controller: QRCheckinController

**File:** `app/Http/Controllers/Admin/QRCheckinController.php`

```bash
php artisan make:controller Admin/QRCheckinController
```

**Codice Completo:**

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Models\Event;
use App\Services\QRCodeService;
use Illuminate\Http\Request;

class QRCheckinController extends AdminBaseController
{
    protected QRCodeService $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        parent::__construct();
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Show QR scanner page for event
     */
    public function scanner(Event $event)
    {
        // Ensure event belongs to current school
        if ($event->school_id !== $this->school->id) {
            abort(404, 'Evento non trovato.');
        }

        $stats = [
            'total_registrations' => $event->registrations()->confirmed()->count(),
            'checked_in' => $event->registrations()->whereNotNull('checked_in_at')->count(),
            'not_checked_in' => $event->registrations()->confirmed()->whereNull('checked_in_at')->count(),
        ];

        return view('admin.events.qr-scanner', compact('event', 'stats'));
    }

    /**
     * Validate QR code (AJAX)
     */
    public function validate(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token mancante.',
            ], 400);
        }

        $result = $this->qrCodeService->validateAndCheckin($token, auth()->id());

        $status = $result['success'] ? 200 : 422;

        return response()->json($result, $status);
    }

    /**
     * Manual check-in by registration ID
     */
    public function manualCheckin(Request $request, Event $event)
    {
        $request->validate([
            'registration_id' => 'required|integer|exists:event_registrations,id',
        ]);

        $registration = $event->registrations()
            ->where('id', $request->registration_id)
            ->firstOrFail();

        if ($registration->checked_in_at) {
            return $this->jsonResponse(false, 'Utente già registrato in ingresso.', [], 422);
        }

        $registration->update([
            'checked_in_at' => now(),
            'checked_in_by' => auth()->id(),
        ]);

        return $this->jsonResponse(true, 'Check-in effettuato.', [
            'registration' => $registration->fresh()->load('user'),
        ]);
    }

    /**
     * Get check-in list for event (AJAX)
     */
    public function checkinList(Event $event)
    {
        if ($event->school_id !== $this->school->id) {
            abort(404);
        }

        $registrations = $event->registrations()
            ->with('user')
            ->confirmed()
            ->orderBy('checked_in_at', 'desc')
            ->get();

        return response()->json([
            'registrations' => $registrations->map(function($reg) {
                return [
                    'id' => $reg->id,
                    'user_name' => $reg->user->name,
                    'user_email' => $reg->user->email,
                    'checked_in' => !is_null($reg->checked_in_at),
                    'checked_in_at' => $reg->checked_in_at?->format('H:i'),
                    'is_guest' => $reg->user->isGuest(),
                ];
            }),
        ]);
    }
}
```

**✅ Checklist:**
- [ ] QR scanner page
- [ ] QR validation AJAX endpoint
- [ ] Manual check-in
- [ ] Check-in list API

---

## 3.5 Routes: Public + Guest + Admin

**File:** `routes/web.php`

Aggiungi le seguenti routes:

```php
<?php

use App\Http\Controllers\PublicEventController;
use App\Http\Controllers\GuestDashboardController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Admin\QRCheckinController;

// ========================================
// PUBLIC EVENTS (no auth required)
// ========================================
Route::prefix('scuole')->name('public.')->group(function () {

    // Short URL for social sharing
    Route::get('/e/{slug}', [PublicEventController::class, 'shortUrl'])->name('events.short');

    // School public events listing
    Route::get('/{school}/eventi', [PublicEventController::class, 'schoolEvents'])->name('events.index');

    // Event landing page
    Route::get('/{school}/eventi/{event}', [PublicEventController::class, 'show'])->name('events.show');

    // Registration
    Route::get('/{school}/eventi/{event}/iscriviti', [PublicEventController::class, 'registerForm'])->name('events.register-form');
    Route::post('/{school}/eventi/{event}/iscriviti', [PublicEventController::class, 'register'])->name('events.register');
});

// ========================================
// GUEST DASHBOARD (magic link auth)
// ========================================
Route::prefix('guest')->name('guest.')->group(function () {

    // Magic link access (no auth middleware)
    Route::get('/accedi/{token}', [GuestDashboardController::class, 'access'])->name('access');

    // Protected guest routes
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [GuestDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/registrazione/{registration}', [GuestDashboardController::class, 'showRegistration'])->name('registration.show');
        Route::get('/biglietto/{registration}/download', [GuestDashboardController::class, 'downloadTicket'])->name('ticket.download');
        Route::post('/profilo/aggiorna', [GuestDashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/logout', [GuestDashboardController::class, 'logout'])->name('logout');
    });
});

// ========================================
// PAYMENT (PayPal callbacks)
// ========================================
Route::prefix('pagamento')->name('payment.')->group(function () {
    Route::get('/successo/{payment}', [PaymentController::class, 'success'])->name('success');
    Route::get('/annullato/{payment}', [PaymentController::class, 'cancel'])->name('cancel');
    Route::get('/fallito', [PaymentController::class, 'failed'])->name('failed');
    Route::get('/dettaglio/{payment}', [PaymentController::class, 'show'])->name('show')->middleware('auth');
});

// ========================================
// ADMIN QR CHECK-IN
// ========================================
Route::prefix('admin/eventi')->middleware(['auth', 'role:admin'])->name('admin.events.')->group(function () {
    Route::get('/{event}/qr-scanner', [QRCheckinController::class, 'scanner'])->name('qr-scanner');
    Route::post('/qr/valida', [QRCheckinController::class, 'validate'])->name('qr.validate');
    Route::post('/{event}/checkin-manuale', [QRCheckinController::class, 'manualCheckin'])->name('checkin.manual');
    Route::get('/{event}/checkin-list', [QRCheckinController::class, 'checkinList'])->name('checkin.list');
});

// Public QR verification (optional - for web-based check-in)
Route::get('/checkin/{token}', function($token) {
    $qrService = app(\App\Services\QRCodeService::class);
    $result = $qrService->validateAndCheckin($token);
    return view('checkin.result', $result);
})->name('checkin.verify');
```

**Route List Summary:**

**Public Routes:**
- `GET /scuole/e/{slug}` - Short URL redirect
- `GET /scuole/{school}/eventi` - School events list
- `GET /scuole/{school}/eventi/{event}` - Event landing page
- `GET /scuole/{school}/eventi/{event}/iscriviti` - Registration form
- `POST /scuole/{school}/eventi/{event}/iscriviti` - Process registration

**Guest Routes:**
- `GET /guest/accedi/{token}` - Magic link access
- `GET /guest/dashboard` - Guest dashboard
- `GET /guest/registrazione/{registration}` - Registration details
- `GET /guest/biglietto/{registration}/download` - Download ticket
- `POST /guest/profilo/aggiorna` - Update profile
- `POST /guest/logout` - Logout

**Payment Routes:**
- `GET /pagamento/successo/{payment}` - PayPal success
- `GET /pagamento/annullato/{payment}` - PayPal cancel
- `GET /pagamento/fallito` - Payment failed
- `GET /pagamento/dettaglio/{payment}` - Payment details

**Admin Routes:**
- `GET /admin/eventi/{event}/qr-scanner` - QR scanner page
- `POST /admin/eventi/qr/valida` - Validate QR (AJAX)
- `POST /admin/eventi/{event}/checkin-manuale` - Manual check-in
- `GET /admin/eventi/{event}/checkin-list` - Check-in list API

**✅ Checklist:**
- [ ] Public routes create
- [ ] Guest routes create
- [ ] Payment routes configurate
- [ ] Admin QR routes configurate
- [ ] Middleware applicati correttamente
- [ ] Route names consistenti

---

**Output Fase 3:**
- [x] PublicEventController implementato
- [x] GuestDashboardController implementato
- [x] PaymentController implementato
- [x] QRCheckinController implementato
- [x] Tutte le routes configurate

---

# 🎨 FASE 4: Frontend Views

## 4.1 Landing Page Evento Pubblico

**File:** `resources/views/public/events/show.blade.php`

**Struttura View:**

```blade
@extends('layouts.guest')

@section('title', $event->name . ' - ' . $school->name)

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50">
    <!-- Hero Section con immagine evento -->
    <div class="relative h-96 bg-cover bg-center" style="background-image: url('{{ $event->image_url ?? '/default-event.jpg' }}')">
        <div class="absolute inset-0 bg-black/50"></div>
        <div class="relative h-full flex items-center justify-center text-white">
            <div class="text-center">
                <h1 class="text-5xl font-bold mb-4">{{ $event->name }}</h1>
                <p class="text-xl">{{ $school->name }}</p>
                <p class="mt-4">
                    <span class="inline-block bg-white/20 px-4 py-2 rounded-lg">
                        {{ $event->start_date->format('d M Y - H:i') }}
                    </span>
                </p>
            </div>
        </div>
    </div>

    <!-- Event Details -->
    <div class="max-w-4xl mx-auto px-4 py-12">
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold mb-4">Dettagli Evento</h2>
                <div class="space-y-3">
                    <div><strong>Data:</strong> {{ $event->start_date->format('d/m/Y H:i') }}</div>
                    <div><strong>Luogo:</strong> {{ $event->location }}</div>
                    <div><strong>Tipo:</strong> {{ ucfirst($event->type) }}</div>
                    @if($event->price_guests > 0)
                        <div><strong>Prezzo:</strong> €{{ number_format($event->price_guests, 2) }}</div>
                    @else
                        <div><strong>Prezzo:</strong> Gratuito</div>
                    @endif
                </div>
            </div>

            <!-- CTA -->
            <div class="bg-white rounded-lg shadow p-6">
                <h3 class="text-xl font-bold mb-4">Posti Disponibili</h3>
                @if($stats['is_full'])
                    <p class="text-red-600 font-bold">Evento al completo</p>
                @else
                    <p class="mb-4">Ancora {{ $stats['available_spots'] ?? 'illimitati' }} posti disponibili</p>
                    <a href="{{ route('public.events.register-form', [$school->slug, $event->slug]) }}"
                       class="block w-full text-center bg-gradient-to-r from-rose-500 to-purple-600 text-white py-3 rounded-lg font-bold hover:from-rose-600 hover:to-purple-700">
                        {{ $event->landing_cta_text }}
                    </a>
                @endif
            </div>
        </div>

        <!-- Description -->
        <div class="mt-8 bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-bold mb-4">Descrizione</h2>
            <div class="prose max-w-none">
                {!! nl2br(e($event->landing_description ?? $event->description)) !!}
            </div>
        </div>

        <!-- Social Share -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-600 mb-2">Condividi:</p>
            <div class="flex justify-center space-x-4">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($event->landing_url) }}"
                   target="_blank" class="text-blue-600 hover:text-blue-800">Facebook</a>
                <a href="https://twitter.com/intent/tweet?url={{ urlencode($event->landing_url) }}&text={{ urlencode($event->name) }}"
                   target="_blank" class="text-blue-400 hover:text-blue-600">Twitter</a>
                <a href="https://wa.me/?text={{ urlencode($event->name . ' - ' . $event->landing_url) }}"
                   target="_blank" class="text-green-600 hover:text-green-800">WhatsApp</a>
            </div>
        </div>
    </div>
</div>
@endsection
```

**✅ Checklist:**
- [ ] Hero section con immagine
- [ ] Event details card
- [ ] CTA iscrizione
- [ ] Social sharing buttons
- [ ] Responsive design

---

## 4.2 Form Registrazione Guest

**File:** `resources/views/public/events/register.blade.php`

**Elementi Chiave:**
- Form con campi: nome, email, telefono
- Checkbox GDPR (privacy, marketing, terms)
- reCAPTCHA v2 widget
- Validazione client-side
- Design consistente con landing page

**JavaScript reCAPTCHA:**

```blade
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<form method="POST" action="{{ route('public.events.register', [$school->slug, $event->slug]) }}">
    @csrf

    <!-- Nome -->
    <input type="text" name="name" required>

    <!-- Email -->
    <input type="email" name="email" required>

    <!-- Telefono -->
    <input type="tel" name="phone">

    <!-- GDPR Consents -->
    <x-gdpr-consent-checkbox type="privacy" required />
    <x-gdpr-consent-checkbox type="marketing" />
    <x-gdpr-consent-checkbox type="terms" required />

    <!-- reCAPTCHA -->
    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>

    <button type="submit">Iscriviti</button>
</form>
```

---

## 4.3 Guest Dashboard

**File:** `resources/views/guest/dashboard.blade.php`

**Sezioni:**
1. Benvenuto con nome utente
2. Lista eventi futuri con QR code inline
3. Lista eventi passati
4. Link per aggiornare profilo
5. Logout button

**QR Code Display:**

```blade
@foreach($upcomingRegistrations as $registration)
<div class="bg-white rounded-lg shadow p-6">
    <h3>{{ $registration->event->name }}</h3>
    <p>{{ $registration->event->start_date->format('d/m/Y H:i') }}</p>

    <!-- QR Code SVG -->
    <div class="mt-4">
        {!! $registration->qr_code_svg !!}
    </div>

    <!-- Download button -->
    <a href="{{ route('guest.ticket.download', $registration) }}"
       class="btn btn-primary mt-2">
        Scarica Biglietto
    </a>
</div>
@endforeach
```

---

## 4.4 Payment Flow Views

**Success:** `resources/views/payment/success.blade.php`
- Conferma pagamento completato
- Dettagli evento
- Magic link per dashboard
- Istruzioni per salvare email

**Cancelled:** `resources/views/payment/cancelled.blade.php`
- Messaggio pagamento annullato
- Possibilità di riprovare

**Failed:** `resources/views/payment/failed.blade.php`
- Errore pagamento
- Contatti supporto

---

## 4.5 Admin: Gestione Prezzi Evento

**Modifica:** `resources/views/admin/events/create.blade.php` e `edit.blade.php`

Aggiungi sezione prezzi duali:

```blade
<!-- Sezione Prezzi -->
<div class="bg-white rounded-lg shadow p-6">
    <h3 class="text-lg font-bold mb-4">Prezzi</h3>

    <div class="grid md:grid-cols-2 gap-4">
        <!-- Prezzo Studenti -->
        <div>
            <label>Prezzo Studenti Scuola</label>
            <input type="number" step="0.01" name="price_students" value="{{ old('price_students', $event->price_students ?? 0) }}">
            <p class="text-sm text-gray-500">Per studenti iscritti alla tua scuola</p>
        </div>

        <!-- Prezzo Guest -->
        <div>
            <label>Prezzo Guest</label>
            <input type="number" step="0.01" name="price_guests" value="{{ old('price_guests', $event->price_guests ?? 0) }}">
            <p class="text-sm text-gray-500">Per partecipanti esterni</p>
        </div>
    </div>

    <!-- Requires Payment -->
    <div class="mt-4">
        <label class="flex items-center">
            <input type="checkbox" name="requires_payment" value="1" {{ old('requires_payment', $event->requires_payment ?? false) ? 'checked' : '' }}>
            <span class="ml-2">Richiede Pagamento</span>
        </label>
    </div>

    <!-- Payment Method -->
    <div class="mt-4">
        <label>Metodo Pagamento</label>
        <select name="payment_method">
            <option value="free">Gratuito</option>
            <option value="paypal">PayPal</option>
            <option value="onsite">In Sede</option>
        </select>
    </div>
</div>

<!-- Sezione Landing Page -->
<div class="bg-white rounded-lg shadow p-6 mt-6">
    <h3 class="text-lg font-bold mb-4">Landing Page</h3>

    <div class="mb-4">
        <label>Descrizione Landing</label>
        <textarea name="landing_description" rows="5">{{ old('landing_description', $event->landing_description ?? '') }}</textarea>
    </div>

    <div>
        <label>Testo Call-to-Action</label>
        <input type="text" name="landing_cta_text" value="{{ old('landing_cta_text', $event->landing_cta_text ?? 'Iscriviti Ora') }}">
    </div>

    <div class="mt-4">
        <label class="flex items-center">
            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $event->is_public ?? true) ? 'checked' : '' }}>
            <span class="ml-2">Evento Pubblico</span>
        </label>
        <p class="text-sm text-gray-500 mt-1">Se disabilitato, solo studenti della scuola possono iscriversi</p>
    </div>
</div>
```

---

## 4.6 Admin: QR Scanner Check-in

**File:** `resources/views/admin/events/qr-scanner.blade.php`

**Funzionalità:**
- Webcam access per scanner QR
- Lista check-in in tempo reale
- Stats in tempo reale
- Manual check-in fallback

**JavaScript QR Scanner:**

```blade
<script src="https://cdn.jsdelivr.net/npm/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<script>
const html5QrCode = new Html5Qrcode("qr-reader");

html5QrCode.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    onScanSuccess,
    onScanError
);

function onScanSuccess(decodedText) {
    // Send AJAX to validate
    fetch('{{ route("admin.events.qr.validate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ token: decodedText })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message);
            refreshCheckinList();
        } else {
            showError(data.message);
        }
    });
}
</script>

<!-- Stats Cards -->
<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <p class="text-sm text-gray-600">Totale Iscritti</p>
        <p class="text-3xl font-bold" id="total-registrations">{{ $stats['total_registrations'] }}</p>
    </div>
    <div class="bg-green-100 rounded-lg shadow p-4">
        <p class="text-sm text-gray-600">Check-in Effettuati</p>
        <p class="text-3xl font-bold text-green-700" id="checked-in">{{ $stats['checked_in'] }}</p>
    </div>
    <div class="bg-yellow-100 rounded-lg shadow p-4">
        <p class="text-sm text-gray-600">Mancanti</p>
        <p class="text-3xl font-bold text-yellow-700" id="not-checked-in">{{ $stats['not_checked_in'] }}</p>
    </div>
</div>

<!-- QR Scanner -->
<div id="qr-reader" class="w-full max-w-md mx-auto"></div>

<!-- Check-in List -->
<div id="checkin-list" class="mt-6"></div>
```

---

**Output Fase 4:**
- [x] Landing page evento pubblico
- [x] Form registrazione guest con GDPR
- [x] Guest dashboard con QR codes
- [x] Payment success/cancel/failed pages
- [x] Admin pricing form
- [x] Admin QR scanner UI

---

# 📧 FASE 5: Sistema Email

## 5.1 Email: Conferma Registrazione

**File:** `app/Mail/GuestRegistrationConfirmation.php`

```bash
php artisan make:mail GuestRegistrationConfirmation
```

**Codice:**

```php
<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Event;
use App\Models\EventRegistration;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GuestRegistrationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public Event $event;
    public EventRegistration $registration;
    public string $magicLink;

    public function __construct(User $user, Event $event, EventRegistration $registration, string $magicLink)
    {
        $this->user = $user;
        $this->event = $event;
        $this->registration = $registration;
        $this->magicLink = $magicLink;
    }

    public function build()
    {
        return $this->subject("Conferma iscrizione: {$this->event->name}")
                    ->markdown('emails.guest-registration-confirmation')
                    ->with([
                        'userName' => $this->user->name,
                        'eventName' => $this->event->name,
                        'eventDate' => $this->event->start_date->format('d/m/Y H:i'),
                        'eventLocation' => $this->event->location,
                        'magicLink' => $this->magicLink,
                        'schoolName' => $this->event->school->name,
                    ]);
    }
}
```

**Template:** `resources/views/emails/guest-registration-confirmation.blade.php`

```blade
@component('mail::message')
# Ciao {{ $userName }},

Grazie per esserti iscritto all'evento **{{ $eventName }}**.

## Dettagli Evento
- **Data:** {{ $eventDate }}
- **Luogo:** {{ $eventLocation }}
- **Scuola:** {{ $schoolName }}

## Accesso Dashboard

Accedi alla tua area personale per visualizzare il biglietto QR code:

@component('mail::button', ['url' => $magicLink])
Accedi alla Dashboard
@endcomponent

**Importante:** Salva questo link per accedere in futuro ai tuoi eventi.

Ci vediamo presto!

{{ config('app.name') }}
@endcomponent
```

---

## 5.2 Email: Reminder 1 Giorno Prima

**File:** `app/Mail/EventReminder.php`

**Invio automatico:** Schedulato via Laravel Scheduler (Fase 7)

```php
public function build()
{
    return $this->subject("Promemoria: {$this->event->name} domani!")
                ->markdown('emails.event-reminder')
                ->attachData($this->qrCodePng, 'biglietto-qr.png', [
                    'mime' => 'image/png',
                ]);
}
```

---

## 5.3 Email: Thank You Post-Evento

**File:** `app/Mail/EventThankYou.php`

**Invio automatico:** 1 giorno dopo evento (Scheduler)

---

## 5.4 Email: Richiesta Feedback

**File:** `app/Mail/EventFeedbackRequest.php`

**Invio automatico:** 3 giorni dopo evento

Include link a form feedback (opzionale):

```blade
@component('mail::button', ['url' => route('feedback.create', $registration->id)])
Lascia il tuo Feedback
@endcomponent
```

---

## 5.5 Email: Notifica Admin Nuova Iscrizione

**File:** `app/Mail/AdminNewRegistrationNotification.php`

Inviato a admin scuola quando arriva nuova registrazione guest:

```php
Mail::to($event->school->admin_email)->send(
    new AdminNewRegistrationNotification($registration)
);
```

---

**Output Fase 5:**
- [x] 5 email templates creati
- [x] Mailable classes configurate
- [x] QR code attachment nelle email
- [x] Magic link incluso
- [x] Email queueing ready

---

# 💳 FASE 6: Integrazione PayPal Completa

## 6.1 Setup PayPal SDK

**Già fatto in Fase 0 e Fase 2 (PaymentService)**

Verifica:
- [ ] Composer package installato
- [ ] Config in `config/services.php`
- [ ] .env variables configurate
- [ ] Sandbox account testato

---

## 6.2 Payment Flow Integration

**Test Checklist:**

```bash
# Test payment creation
php artisan tinker
>>> $event = Event::first();
>>> $user = User::factory()->create(['is_guest' => true]);
>>> $service = app(\App\Services\PaymentService::class);
>>> $payment = $service->createPayment($event, $user);
>>> $payment['approval_url']; // Copy this URL
```

Aprire URL in browser → Login PayPal sandbox → Approva → Redirect a success URL

---

## 6.3 Webhook Listener (Opzionale)

Per notifiche asincrone PayPal:

**File:** `app/Http/Controllers/PayPalWebhookController.php`

```php
public function handle(Request $request)
{
    $event = $request->input('event_type');

    switch ($event) {
        case 'PAYMENT.SALE.COMPLETED':
            // Handle successful payment
            break;
        case 'PAYMENT.SALE.REFUNDED':
            // Handle refund
            break;
    }
}
```

**Route:**
```php
Route::post('/webhooks/paypal', [PayPalWebhookController::class, 'handle']);
```

**Configure in PayPal Dashboard:**
`https://danzafacile.it/webhooks/paypal`

---

## 6.4 Refund System

**Admin Interface:**

In `admin/events/{event}/registrations` aggiungere button "Rimborsa":

```blade
@if($registration->payment && $registration->payment->canBeRefunded())
    <form method="POST" action="{{ route('admin.payments.refund', $registration->payment) }}">
        @csrf
        <button type="submit" onclick="return confirm('Confermi il rimborso?')">
            Rimborsa Pagamento
        </button>
    </form>
@endif
```

**Controller:**

```php
public function refund(EventPayment $payment)
{
    $service = app(\App\Services\PaymentService::class);

    if ($service->refundPayment($payment)) {
        return back()->with('success', 'Rimborso effettuato.');
    }

    return back()->with('error', 'Errore rimborso.');
}
```

---

## 6.5 Testing Sandbox

**Test Plan:**

1. **Pagamento Successo:**
   - Crea evento a pagamento
   - Registrazione guest
   - PayPal redirect
   - Login sandbox personal account
   - Approva pagamento
   - Verifica redirect success
   - Verifica email conferma
   - Verifica status registration = 'confirmed'

2. **Pagamento Annullato:**
   - Inizio flow
   - Click "Cancel" su PayPal
   - Verifica redirect cancel
   - Verifica status payment = 'cancelled'

3. **Rimborso:**
   - Pagamento completato
   - Admin richiede rimborso
   - Verifica email notifica
   - Verifica status = 'refunded'

---

**Output Fase 6:**
- [x] PayPal SDK integrato
- [x] Payment flow testato
- [x] Webhooks configurati (opzionale)
- [x] Refund system funzionante
- [x] Sandbox testing completo

---

# ⚙️ FASE 7: Automazioni & Scheduler

## 7.1 Command: Cleanup Guest Automatico

**File:** `app/Console/Commands/CleanupInactiveGuests.php`

```bash
php artisan make:command CleanupInactiveGuests
```

```php
<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class CleanupInactiveGuests extends Command
{
    protected $signature = 'guests:cleanup {--days=180}';
    protected $description = 'Archive inactive guest users';

    public function handle()
    {
        $days = $this->option('days');

        $guests = User::eligibleForCleanup($days)->get();

        $this->info("Found {$guests->count()} inactive guests");

        foreach ($guests as $guest) {
            $guest->archive('auto_cleanup_' . now()->format('Y-m-d'));
            $this->line("Archived: {$guest->email}");
        }

        $this->info('Cleanup completed.');
    }
}
```

**Test:**

```bash
php artisan guests:cleanup --days=180
```

---

## 7.2 Job: Email Reminder Scheduler

**File:** `app/Jobs/SendEventReminders.php`

```bash
php artisan make:job SendEventReminders
```

```php
<?php

namespace App\Jobs;

use App\Models\Event;
use App\Mail\EventReminder;
use Illuminate\Support\Facades\Mail;

class SendEventReminders
{
    public function handle()
    {
        // Eventi che iniziano domani
        $events = Event::where('start_date', '>=', now()->addDay()->startOfDay())
                       ->where('start_date', '<=', now()->addDay()->endOfDay())
                       ->with(['registrations.user'])
                       ->get();

        foreach ($events as $event) {
            foreach ($event->registrations()->confirmed()->get() as $registration) {
                Mail::to($registration->user->email)->send(
                    new EventReminder($registration->user, $event, $registration)
                );
            }
        }
    }
}
```

---

## 7.3 Job: Email Feedback Post-Evento

**File:** `app/Jobs/SendEventFeedbackRequests.php`

```php
public function handle()
{
    // Eventi conclusi 3 giorni fa
    $events = Event::where('end_date', '>=', now()->subDays(3)->startOfDay())
                   ->where('end_date', '<=', now()->subDays(3)->endOfDay())
                   ->get();

    foreach ($events as $event) {
        foreach ($event->registrations()->confirmed()->get() as $registration) {
            Mail::to($registration->user->email)->send(
                new EventFeedbackRequest($registration->user, $event, $registration)
            );
        }
    }
}
```

---

## 7.4 Schedule Configuration

**File:** `app/Console/Kernel.php`

```php
protected function schedule(Schedule $schedule)
{
    // Cleanup guests ogni giorno alle 3am
    $schedule->command('guests:cleanup --days=180')
             ->daily()
             ->at('03:00');

    // Email reminder ogni giorno alle 10am
    $schedule->job(new \App\Jobs\SendEventReminders)
             ->daily()
             ->at('10:00');

    // Email thank you ogni giorno alle 11am
    $schedule->job(new \App\Jobs\SendEventThankYou)
             ->daily()
             ->at('11:00');

    // Email feedback ogni giorno alle 12pm
    $schedule->job(new \App\Jobs\SendEventFeedbackRequests)
             ->daily()
             ->at('12:00');
}
```

**Attivare Scheduler:**

```bash
# Crontab entry
* * * * * cd /path/to/danzafacile && php artisan schedule:run >> /dev/null 2>&1
```

---

**Output Fase 7:**
- [x] Cleanup command implementato
- [x] Email jobs schedulati
- [x] Scheduler configurato
- [x] Crontab attivato

---

# 🛡️ FASE 8: Admin Features

## 8.1 Dashboard Eventi Pubblici

**Aggiungi a:** `resources/views/admin/events/index.blade.php`

**Nuova sezione:**

```blade
<!-- Public Events Stats -->
<div class="bg-blue-100 rounded-lg shadow p-6 mb-6">
    <h3 class="text-lg font-bold mb-4">Eventi Pubblici</h3>
    <div class="grid grid-cols-4 gap-4">
        <div>
            <p class="text-sm text-gray-600">Eventi Pubblici Attivi</p>
            <p class="text-2xl font-bold">{{ $stats['public_events'] ?? 0 }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Iscrizioni Guest</p>
            <p class="text-2xl font-bold">{{ $stats['guest_registrations'] ?? 0 }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Pagamenti Ricevuti</p>
            <p class="text-2xl font-bold">€{{ number_format($stats['total_revenue'] ?? 0, 2) }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-600">Check-in Oggi</p>
            <p class="text-2xl font-bold">{{ $stats['checkins_today'] ?? 0 }}</p>
        </div>
    </div>
</div>
```

---

## 8.2 Landing Page Customization

Nella edit evento, permettere preview live:

```blade
<!-- Preview Landing -->
<div class="bg-white rounded-lg shadow p-6">
    <h3>Preview Landing Page</h3>
    <iframe src="{{ route('public.events.show', [$school->slug, $event->slug]) }}"
            class="w-full h-96 border rounded"></iframe>
    <a href="{{ route('public.events.show', [$school->slug, $event->slug]) }}"
       target="_blank" class="btn btn-secondary mt-2">
        Apri in Nuova Tab
    </a>
</div>

<!-- Copy Share Link -->
<div class="bg-white rounded-lg shadow p-6 mt-4">
    <h3>Link Condivisione</h3>
    <div class="flex items-center space-x-2">
        <input type="text"
               value="{{ route('public.events.show', [$school->slug, $event->slug]) }}"
               id="share-link"
               readonly
               class="flex-1 px-4 py-2 border rounded">
        <button onclick="copyShareLink()" class="btn btn-primary">Copia</button>
    </div>
</div>

<script>
function copyShareLink() {
    document.getElementById('share-link').select();
    document.execCommand('copy');
    alert('Link copiato!');
}
</script>
```

---

## 8.3 Report Iscrizioni Guest

**Route:**

```php
Route::get('/admin/events/{event}/guest-report', [AdminEventController::class, 'guestReport'])->name('admin.events.guest-report');
```

**Controller Method:**

```php
public function guestReport(Event $event)
{
    $guestRegistrations = $event->guestRegistrations()
        ->with(['user', 'payment'])
        ->get();

    $stats = [
        'total_guests' => $guestRegistrations->count(),
        'confirmed' => $guestRegistrations->where('status', 'confirmed')->count(),
        'total_revenue' => $event->eventPayments()->completed()->sum('amount'),
        'checked_in' => $guestRegistrations->whereNotNull('checked_in_at')->count(),
    ];

    return view('admin.events.guest-report', compact('event', 'guestRegistrations', 'stats'));
}
```

---

## 8.4 Export Data Guest

**CSV Export:**

```php
public function exportGuests(Event $event)
{
    $guests = $event->guestRegistrations()->with('user')->get();

    $csv = "Nome,Email,Telefono,Data Iscrizione,Status,Check-in\n";

    foreach ($guests as $registration) {
        $csv .= sprintf(
            "%s,%s,%s,%s,%s,%s\n",
            $registration->user->name,
            $registration->user->email,
            $registration->user->guest_phone ?? '',
            $registration->created_at->format('d/m/Y H:i'),
            $registration->status,
            $registration->checked_in_at ? 'Sì' : 'No'
        );
    }

    return response($csv)
        ->header('Content-Type', 'text/csv')
        ->header('Content-Disposition', "attachment; filename=guest-{$event->slug}.csv");
}
```

---

**Output Fase 8:**
- [x] Dashboard public events
- [x] Landing customization UI
- [x] Guest report
- [x] CSV export

---

# ✅ FASE 9: Testing & Deploy

## 9.1 Unit Tests

**File:** `tests/Unit/UserGuestMethodsTest.php`

```php
<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;

class UserGuestMethodsTest extends TestCase
{
    public function test_is_guest_returns_true_for_guest_users()
    {
        $guest = User::factory()->create(['is_guest' => true]);
        $this->assertTrue($guest->isGuest());
    }

    public function test_generate_guest_token_creates_valid_token()
    {
        $guest = User::factory()->create(['is_guest' => true]);
        $token = $guest->generateGuestToken();

        $this->assertNotNull($token);
        $this->assertEquals(64, strlen($token));
        $this->assertNotNull($guest->fresh()->guest_token_expires_at);
    }

    public function test_find_by_guest_token_returns_correct_user()
    {
        $guest = User::factory()->create(['is_guest' => true]);
        $token = $guest->generateGuestToken();

        $found = User::findByGuestToken($token);

        $this->assertEquals($guest->id, $found->id);
    }
}
```

**Run:**
```bash
php artisan test --filter=UserGuestMethodsTest
```

---

## 9.2 Feature Tests

**File:** `tests/Feature/PublicEventRegistrationTest.php`

```php
public function test_guest_can_register_for_free_event()
{
    $school = School::factory()->create(['slug' => 'test-school']);
    $event = Event::factory()->create([
        'school_id' => $school->id,
        'slug' => 'test-event',
        'is_public' => true,
        'price_guests' => 0,
        'requires_payment' => false,
    ]);

    $response = $this->post(route('public.events.register', [$school->slug, $event->slug]), [
        'name' => 'Test Guest',
        'email' => 'guest@test.com',
        'phone' => '1234567890',
        'consent_privacy' => true,
        'consent_terms' => true,
        'g-recaptcha-response' => 'test-token',
    ]);

    $response->assertSuccessful();
    $this->assertDatabaseHas('users', [
        'email' => 'guest@test.com',
        'is_guest' => true,
    ]);
    $this->assertDatabaseHas('event_registrations', [
        'event_id' => $event->id,
        'status' => 'confirmed',
    ]);
}
```

---

## 9.3 Integration Tests PayPal

**Sandbox Test Checklist:**

- [ ] Create payment returns approval URL
- [ ] Execute payment marks as completed
- [ ] Registration confirmed after payment
- [ ] Email sent after payment
- [ ] Refund changes status correctly

---

## 9.4 User Acceptance Testing

**UAT Checklist:**

**Guest User Flow:**
- [ ] Landing page carica correttamente
- [ ] Form registrazione validazione funziona
- [ ] reCAPTCHA validation funziona
- [ ] Email conferma ricevuta con magic link
- [ ] Magic link accede a dashboard
- [ ] QR code visibile in dashboard
- [ ] Download ticket funziona
- [ ] Profilo aggiornabile

**Payment Flow:**
- [ ] Redirect a PayPal funziona
- [ ] Payment approval redirect back
- [ ] Confirmation email ricevuta
- [ ] Transaction salvata correttamente

**Admin Flow:**
- [ ] Creazione evento pubblico funziona
- [ ] Prezzi duali salvati correttamente
- [ ] Landing page preview funziona
- [ ] QR scanner funziona
- [ ] Check-in registrato correttamente
- [ ] Guest report accessibile
- [ ] Export CSV funziona

---

## 9.5 Deploy Staging

**Checklist:**

```bash
# 1. Merge feature branch
git checkout main
git merge feature/public-events-system

# 2. Run migrations on staging
ssh user@staging-server
cd /var/www/danzafacile
php artisan migrate --force

# 3. Clear caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 4. Compile assets
npm run build

# 5. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

# 6. Test on staging
curl https://staging.danzafacile.it/scuole/test-school/eventi
```

---

## 9.6 Deploy Production

**Pre-Deploy Checklist:**

- [ ] Tutti i test passano
- [ ] UAT completato
- [ ] Staging funzionante
- [ ] Backup database fatto
- [ ] PayPal LIVE credentials configurate
- [ ] reCAPTCHA production domain configurato
- [ ] Email SMTP production configurato

**Deploy Steps:**

```bash
# 1. Backup database
php artisan db:backup

# 2. Enable maintenance mode
php artisan down --message="Aggiornamento sistema in corso"

# 3. Pull latest code
git pull origin main

# 4. Install dependencies
composer install --no-dev --optimize-autoloader
npm ci && npm run build

# 5. Run migrations
php artisan migrate --force

# 6. Clear & cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 7. Restart services
sudo systemctl restart php8.2-fpm nginx

# 8. Test critical paths
curl https://www.danzafacile.it/health

# 9. Disable maintenance mode
php artisan up
```

**Post-Deploy Verification:**

- [ ] Homepage carica
- [ ] Admin login funziona
- [ ] Creazione evento pubblico funziona
- [ ] Landing page pubblica accessibile
- [ ] Guest registration funziona
- [ ] PayPal payment test (small amount)
- [ ] QR scanner funziona
- [ ] Email inviate correttamente
- [ ] Scheduler attivo (check logs)

---

**Output Fase 9:**
- [x] Unit tests scritti e passati
- [x] Feature tests implementati
- [x] PayPal integration testata
- [x] UAT completato
- [x] Staging deploy riuscito
- [x] Production deploy completato

---

# 🎉 COMPLETAMENTO ROADMAP

## Riepilogo Sistema Implementato

### Database:
- ✅ 5 migrations per guest users, prezzi duali, payments, GDPR, QR codes

### Backend:
- ✅ 4 Models estesi/creati (User, Event, EventPayment, GdprConsent)
- ✅ 3 Services (GuestRegistrationService, PaymentService, QRCodeService)
- ✅ 4 Controllers (PublicEventController, GuestDashboardController, PaymentController, QRCheckinController)
- ✅ Tutte le routes configurate

### Frontend:
- ✅ Landing page evento pubblico
- ✅ Form registrazione con GDPR e reCAPTCHA
- ✅ Guest dashboard con QR codes
- ✅ Payment flow completo
- ✅ Admin pricing e landing customization
- ✅ QR scanner interface

### Email:
- ✅ 5 email templates (conferma, reminder, thank you, feedback, admin notification)

### Automazioni:
- ✅ Guest cleanup automatico
- ✅ Email scheduler jobs
- ✅ Crontab configurato

### Payment:
- ✅ PayPal SDK integrato
- ✅ Create/Execute payment
- ✅ Refund system
- ✅ Webhooks (opzionale)

### Testing & Deploy:
- ✅ Unit tests
- ✅ Feature tests
- ✅ UAT
- ✅ Staging deploy
- ✅ Production deploy

---

## Metriche di Successo

**KPIs da Monitorare:**

1. **Guest Registrations:** N° iscrizioni guest per evento
2. **Conversion Rate:** % registrazioni completate vs visite landing
3. **Payment Success Rate:** % pagamenti completati vs iniziati
4. **QR Check-in Rate:** % check-in effettivi vs registrazioni
5. **Email Open Rate:** % apertura email conferma/reminder
6. **Guest Retention:** % guest che si iscrivono a più eventi

**Monitoring:**

```sql
-- Registrazioni guest ultimo mese
SELECT COUNT(*) FROM event_registrations
WHERE user_id IN (SELECT id FROM users WHERE is_guest = 1)
AND created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH);

-- Tasso conversione pagamenti
SELECT
    (SELECT COUNT(*) FROM event_payments WHERE status = 'completed') * 100.0 /
    (SELECT COUNT(*) FROM event_payments) AS conversion_rate;

-- Eventi pubblici più popolari
SELECT e.name, COUNT(er.id) AS registrations
FROM events e
LEFT JOIN event_registrations er ON e.id = er.event_id
WHERE e.is_public = 1
GROUP BY e.id
ORDER BY registrations DESC
LIMIT 10;
```

---

## Manutenzione Post-Launch

**Tasks Settimanali:**
- [ ] Monitorare logs errori PayPal
- [ ] Verificare email queue
- [ ] Controllare guest cleanup logs

**Tasks Mensili:**
- [ ] Review guest analytics
- [ ] Verificare GDPR consents compliance
- [ ] Update privacy policy se necessario
- [ ] Backup database eventi pubblici

**Tasks Trimestrali:**
- [ ] Review pricing strategy
- [ ] A/B testing landing pages
- [ ] Survey feedback guest users

---

## 🔥 SISTEMA PRONTO PER PRODUZIONE

**Congratulazioni! Il sistema eventi pubblici è completo e pronto per essere utilizzato.**

**Prossimi Step:**
1. Formare team admin sull'uso
2. Creare primo evento pubblico di test
3. Promuovere su social media
4. Raccogliere feedback utenti
5. Iterare e migliorare

---

**Fine Roadmap** ✅
