# 🐛 Bug Fixes & Improvements Roadmap

**Progetto:** DanzaFacile - Laravel 12 Dance School Management System
**Data Creazione:** 2026-01-23
**Ultima Modifica:** 2026-01-25 09:30 UTC
**Status:** 10/11 completati (91%)

---

## 📊 Overview

| Priorità | Totale | Completati | In Progress | Pending |
|----------|--------|------------|-------------|---------|
| 🔴 CRITICAL | 3 | 3 | 0 | 0 |
| 🟡 HIGH | 3 | 3 | 0 | 0 |
| 🟢 MEDIUM | 4 | 3 | 0 | 1 |
| 🔵 LOW | 1 | 1 | 0 | 0 |
| **TOTALE** | **11** | **10** | **0** | **1** |

**Tempo Stimato Totale:** 15-20 ore di sviluppo
**Tempo Impiegato:** 17 ore (TASK #8 pending: +30 min stimati)

---

## 🔴 CRITICAL - Production Blocker (3 task)

### ✅ #1 - Settings Non Salva Dati + Errore Email

**Status:** ✅ Completed (2026-01-23 23:50 UTC)
**Priorità:** 🔴 CRITICAL
**Complessità:** 🟢 Low
**Tempo Stimato:** 30-45 minuti
**Tempo Effettivo:** 45 minuti
**Commit:** `65c4b24`

#### Descrizione
La pagina `/admin/settings` non salva i dati delle impostazioni della scuola e restituisce sempre un errore di validazione sull'email.

#### Comportamento Atteso
- Form salva correttamente tutti i campi
- Email validation funziona correttamente
- Success message dopo salvataggio
- Dati persistiti nel database

#### Bug Trovati (6 totali)
1. **Checkbox `receipt_show_logo`** - Usato `$request->has()` invece di `boolean()` → salvato sempre come `true`
2. **Checkbox `paypal_enabled`** - Logica `has() && value` complessa e fragile
3. **Email validation** - Troppo stricta su campi nullable, causava errori su valori vuoti
4. **Error handling** - Nessun try-catch, errori silent failures
5. **PHP 8.4 deprecation** - Warning su `Setting::set($description = null)` senza `?string`
6. **Errori non visualizzati** - Nessun blocco `@error` o `session('error')` nella view

#### Fix Applicato (Senior Approach)

**Controller: `app/Http/Controllers/Admin/AdminSettingsController.php`**
```php
// ✅ FIX 1: Validation migliorata
'school_email' => 'nullable|email:rfc,dns|max:255',  // Era: 'nullable|email'

// ✅ FIX 2: Conditional validation PayPal
$paypalEnabled = $request->boolean('paypal_enabled');
if ($paypalEnabled) {
    $request->validate([
        'paypal_mode' => 'required|in:sandbox,live',
        'paypal_client_id' => 'required|string|max:255',
        // ... custom error messages
    ]);
}

// ✅ FIX 3: Checkbox corretto con boolean()
"school.{$school->id}.receipt.show_logo" => [
    'value' => $request->boolean('receipt_show_logo'),  // Prima: $request->has()
    'type' => 'boolean'
],

// ✅ FIX 4: Try-catch robusto
try {
    foreach ($settingsToSave as $key => $data) {
        Setting::set($key, $data['value'], $data['type']);
    }
    \Log::info('Settings updated successfully', [...]);
    return redirect()->route('admin.settings.index')->with('success', '...');
} catch (\Exception $e) {
    \Log::error('Failed to save settings', [...]);
    return redirect()->back()->withInput()->with('error', '...');
}
```

**Model: `app/Models/Setting.php`**
```php
// ✅ FIX 5: PHP 8.4 compatibility
public static function set(string $key, $value, string $type = 'string', ?string $description = null)
```

**View: `resources/views/admin/settings/index.blade.php`**
```blade
<!-- ✅ FIX 6: Error alerts -->
@if (session('error'))
    <div x-show="showErrorAlert" class="bg-red-100 border-l-4 border-red-500...">
        {{ session('error') }}
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-100...">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
```

**JavaScript: `resources/js/admin/settings/settings-manager.js`**
```javascript
// ✅ FIX 6: Error alert state
showErrorAlert: true,
dismissErrorAlert() { this.showErrorAlert = false; }
```

#### File Modificati
- ✅ `app/Http/Controllers/Admin/AdminSettingsController.php` (+56 lines, -17 lines)
- ✅ `app/Models/Setting.php` (+1 line, -1 line)
- ✅ `resources/views/admin/settings/index.blade.php` (+49 lines)
- ✅ `resources/js/admin/settings/settings-manager.js` (+6 lines)

#### Testing
- ✅ Code review locale
- ✅ Deploy su VPS production (commit `65c4b24`)
- ✅ Website responding (HTTP 302 redirect to login - normale)
- ✅ Nessun nuovo errore nei log Laravel
- ⏳ Test manuale form da eseguire

#### Note Tecniche Post-Fix
- `$request->boolean('field')` gestisce correttamente checkbox unchecked (Laravel 12)
- Conditional validation pulisce la logica PayPal
- Try-catch con logging permette troubleshooting futuro
- Email validation `rfc,dns` più robusta ma permissiva su nullable

---

### ✅ #2 - Studenti Nomi/Cognomi Non Visualizzati

**Status:** ✅ Completed (2026-01-23 01:15 UTC)
**Priorità:** 🔴 CRITICAL
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1-1.5 ore
**Tempo Effettivo:** 1.25 ore
**Commit:** `74ee866`

#### Descrizione
Nella pagina `/admin/students/150/edit` alcuni studenti non mostravano nome e cognome correttamente. Avatar mostrava iniziali vuote.

#### Comportamento Atteso
- Tutti gli studenti mostrano nome e cognome completi
- Avatar con iniziali corrette (e.g., "ER" per "Emanuele Rosato")
- Fallback robusto a `full_name` accessor se first_name/last_name mancanti

#### Root Cause Identificato
**Data Migration Incompleta:**
- 13/122 studenti (10.7%) avevano `first_name` e `last_name` NULL
- Solo campo `name` popolato dal vecchio schema
- Views accedevano direttamente a `$student->first_name` senza null check
- `substr(NULL, 0, 1)` ritorna stringa vuota → avatar senza iniziali

**Verifica Production Data:**
```sql
-- Prima del fix:
SELECT id, name, first_name, last_name FROM users WHERE id = 150;
-- Result: id=150, name="emanuele rosato q", first_name=NULL, last_name=NULL

-- Dopo migration:
-- Result: id=150, name="emanuele rosato q", first_name="emanuele", last_name="rosato q"
```

#### Fix Applicato (Senior Multi-Layer Approach)

**Layer 1: View Fix (Defensive Rendering) - 3 Files**

Sostituito pattern buggy:
```blade
<!-- ❌ BEFORE (BUGGY): -->
{{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}

<!-- ✅ AFTER (DEFENSIVE): -->
@php
    // SENIOR FIX: Defensive initials extraction with fallback
    $initials = '';
    if ($student->first_name && $student->last_name) {
        // Best case: both fields populated
        $initials = strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1));
    } elseif ($student->full_name) {
        // Fallback: extract from full_name accessor
        $nameParts = explode(' ', trim($student->full_name));
        $initials = strtoupper(substr($nameParts[0] ?? '', 0, 1) . substr($nameParts[1] ?? $nameParts[0] ?? '', 0, 1));
    } else {
        // Last resort
        $initials = '??';
    }
@endphp
{{ $initials }}
```

**Layer 2: Data Migration (Corrective Fix)**

Creata migration: `database/migrations/2026_01_23_220435_populate_first_last_names_from_name.php`

```php
public function up(): void
{
    DB::transaction(function () {
        // Get users with NULL first_name or last_name but valid name
        $usersToFix = DB::table('users')
            ->where(function($query) {
                $query->whereNull('first_name')->orWhereNull('last_name');
            })
            ->whereNotNull('name')
            ->where('name', '!=', '')
            ->get();

        foreach ($usersToFix as $user) {
            $nameParts = explode(' ', trim($user->name));

            if (count($nameParts) >= 2) {
                // Standard case: "FirstName LastName" or "FirstName MiddleName LastName"
                $firstName = $nameParts[0];
                $lastName = implode(' ', array_slice($nameParts, 1));

                DB::table('users')->where('id', $user->id)->update([
                    'first_name' => $user->first_name ?? $firstName,
                    'last_name' => $user->last_name ?? $lastName,
                    'updated_at' => now(),
                ]);
                $fixed++;
            } elseif (count($nameParts) === 1) {
                // Edge case: Single word name (e.g., "Madonna")
                $firstName = $nameParts[0];
                DB::table('users')->where('id', $user->id)->update([
                    'first_name' => $user->first_name ?? $firstName,
                    'last_name' => $user->last_name ?? $firstName,
                    'updated_at' => now(),
                ]);
                $fixed++;
            }
        }

        \Log::info('Data migration: populated first_name/last_name from name', [
            'total_found' => $usersToFix->count(),
            'fixed' => $fixed,
            'skipped' => $skipped,
        ]);
    });
}
```

#### File Modificati
- ✅ `resources/views/admin/students/edit.blade.php` (+15 lines, -2 lines)
- ✅ `resources/views/admin/students/show.blade.php` (+14 lines, -2 lines)
- ✅ `resources/views/admin/students/partials/table.blade.php` (+14 lines, -2 lines)
- ✅ `database/migrations/2026_01_23_220435_populate_first_last_names_from_name.php` (+107 lines, NEW)

#### Testing
- ✅ Code review locale - pattern applicato consistentemente
- ✅ Migration testata su VPS production (43.60ms execution)
- ✅ Verifica student ID 150: first_name="emanuele", last_name="rosato q" ✅
- ✅ Deploy completato (commit `74ee866`)
- ✅ Cache cleared, PHP-FPM riavviato
- ⏳ Test manuale UI da eseguire

#### Note Tecniche Post-Fix
- **Defensive pattern:** 3-level fallback assicura sempre un valore display
- **Data integrity:** Migration usa `?? operator` per non sovrascrivere campi già popolati
- **Transaction safety:** Tutto in `DB::transaction()` per atomicità
- **Single word names:** Gestiti correttamente (first_name = last_name = nome unico)
- **Rollback safety:** down() è NO-OP intenzionale per sicurezza dati
- **Leverages accessor:** Pattern sfrutta `full_name` accessor esistente nel model `User.php`
```

---

### ✅ #3 - Eventi: Foto Non Caricate + Non Visualizzati

**Status:** ✅ Completed (2026-01-23 02:45 UTC)
**Priorità:** 🔴 CRITICAL
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1.5-2 ore
**Tempo Effettivo:** 1.5 ore
**Commits:** `fc3a730`, `b01e67d`

#### Descrizione
La pagina `/admin/events/create` aveva problemi critici:
1. Foto eventi non venivano caricate (fatal error)
2. Lista eventi poteva crashare con SQL error

#### Comportamento Atteso
- ✅ Upload foto funziona correttamente
- ✅ Foto salvate in `storage/app/public/events/` (directory creata automaticamente)
- ✅ Eventi ordinabili senza SQL errors
- ✅ Foto accessibili tramite symlink esistente

#### Root Cause Identificati

**Bug #1: Metodo FileUploadHelper::uploadFile() NON ESISTEVA** (CRITICAL)
- `AdminEventController` chiamava `FileUploadHelper::uploadFile()` (lines 118, 250)
- Ma il metodo NON era implementato nel FileUploadHelper
- Solo `validateFile()` esisteva
- Causava: **FATAL ERROR "Call to undefined method"**

**Bug #2: Directory storage/app/public/events NON ESISTEVA** (HIGH)
```bash
# Verificato su VPS production:
ls: cannot access 'storage/app/public/events': No such file or directory
```
- Anche se metodo esistesse, fallirebbe per directory mancante

**Bug #3: SQL Column Name Mismatch** (MEDIUM)
- `AdminEventController::index()` aveva `allowedSortFields = ['title', ...]`
- Ma il campo nella tabella events si chiama `'name'`
- Causava: **SQLSTATE[42S22]: Column not found: 1054 Unknown column 'title'**

#### Fix Applicati (Senior Multi-Layer Approach)

**Fix #1: Implementato FileUploadHelper::uploadFile()**

```php
/**
 * SENIOR FIX: Upload file sicuro con validazione avanzata
 */
public static function uploadFile(
    UploadedFile $file,
    string $directory,
    string $category,
    int $maxSizeMB = 10
): array {
    try {
        // 1. Validate file (magic bytes, MIME, size)
        $validation = self::validateFile($file, $validationCategory, $maxSizeMB);
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        // 2. Sanitize filename with timestamp
        $sanitizedName = self::sanitizeFileName($originalName);

        // 3. Ensure directory exists (create if needed)
        $fullPath = storage_path('app/public/' . $directory);
        if (!file_exists($fullPath)) {
            mkdir($fullPath, 0755, true);  // Recursive creation
        }

        // 4. Store file
        $storedPath = $file->storeAs($directory, $sanitizedName, 'public');

        Log::info('File uploaded successfully', [
            'stored_path' => $storedPath,
            'size_mb' => $validation['size_mb']
        ]);

        return [
            'success' => true,
            'path' => $storedPath,
            'filename' => $sanitizedName,
            'size_mb' => $validation['size_mb'],
            'mime_type' => $validation['mime_type']
        ];
    } catch (\Exception $e) {
        Log::error('File upload failed', ['error' => $e->getMessage()]);
        return ['success' => false, 'errors' => [$e->getMessage()]];
    }
}
```

**Features implementate:**
- ✅ Validazione completa (magic bytes, MIME, size) tramite `validateFile()`
- ✅ **Creazione automatica directory** se non esiste (mkdir recursive)
- ✅ Sanitizzazione filename con timestamp per unicità
- ✅ Storage su disco 'public' con `storeAs()`
- ✅ Logging completo per troubleshooting
- ✅ Try-catch robusto con error handling
- ✅ Return format consistente: `['success', 'path', 'errors', 'filename', 'size_mb', 'mime_type']`

**Fix #2: Correzione SQL Sort Field**

```php
// Before (BUGGY):
$allowedSortFields = ['title', 'start_date', ...];  // ❌ Column 'title' doesn't exist

// After (FIXED):
$allowedSortFields = ['name', 'start_date', ...];   // ✅ Correct column name
```

#### File Modificati
- ✅ `app/Helpers/FileUploadHelper.php` (+103 lines, NEW method uploadFile())
- ✅ `app/Http/Controllers/Admin/AdminEventController.php` (+2 lines, -1 line)

#### Testing
- ✅ Code review locale - metodo implementato correttamente
- ✅ Deploy su VPS production (commits `fc3a730`, `b01e67d`)
- ✅ Storage symlink esistente verificato: `/var/www/danzafacile/storage/app/public`
- ✅ Directory `events/` verrà creata automaticamente al primo upload
- ✅ Cache cleared, PHP-FPM riavviato
- ⏳ Test manuale upload da eseguire

#### Note Tecniche Post-Fix
- **Auto-create directory:** `mkdir($fullPath, 0755, true)` crea la directory al primo upload
- **Sanitize filename:** `sanitizeFileName()` previene path traversal e aggiunge timestamp
- **Magic bytes validation:** Validazione MIME reale del file, non solo estensione
- **Logging completo:** Ogni upload loggato con dettagli per troubleshooting
- **Compatibilità:** Nessuna modifica richiesta ai controller esistenti (backward compatible)
- **SQL fix:** Sort ora funziona su colonna corretta 'name'

---

## 🟡 HIGH - Feature Importante (3 task)

### ✅ #4 - Gestione Minori: Genitore + Fatturazione

**Status:** ✅ Completed (2026-01-24 08:49 UTC)
**Priorità:** 🟡 HIGH
**Complessità:** 🔴 High
**Tempo Stimato:** 3-4 ore
**Tempo Effettivo:** 3 ore
**Commits:** `eaa0a4c` (Backend), `a4fb61d` (Frontend)

#### Descrizione
Nella pagina `/admin/students/{id}/edit` e `/admin/students/create`, se lo studente è minorenne:
- Richiedere dati del genitore/tutore legale
- Gestire fatturazione intestata al genitore invece dello studente

#### Comportamento Implementato
✅ Checkbox "È minorenne?" con auto-detect da data di nascita < 18 anni
✅ Form campi genitore condizionali (5 campi: nome, cognome, CF, email, telefono)
✅ Validation condizionale: guardian fields obbligatori solo se is_minor = true
✅ Model accessors per fatturazione/comunicazioni al genitore
✅ Cached is_minor flag per performance (invece di calcolare da date_of_birth ogni volta)

#### File Implementati

**Part 1 - Backend + Validation (commit eaa0a4c)**
- ✅ `database/migrations/2026_01_23_222552_add_guardian_fields_to_users_table.php` (NEW)
  - 6 campi guardian: first_name, last_name, fiscal_code, email, phone
  - is_minor boolean (cached)
  - Index su is_minor per performance

- ✅ `app/Models/User.php`
  - Cast is_minor => 'boolean'
  - 6 accessor methods:
    - `getGuardianFullNameAttribute()` - Nome completo genitore
    - `isMinor()` - Check se < 18 anni
    - `getContactEmailAttribute()` - Email per comunicazioni (genitore se minore)
    - `getContactPhoneAttribute()` - Telefono per comunicazioni
    - `getBillingNameAttribute()` - Nome per fatturazione
    - `getBillingFiscalCodeAttribute()` - CF per fatturazione

- ✅ `app/Http/Controllers/Admin/AdminStudentController.php`
  - `store()`: Conditional validation guardian fields
  - `update()`: Conditional validation + nulling fields quando not minor
  - Custom Italian error messages

**Part 2 - Frontend Forms (commit a4fb61d)**
- ✅ `resources/views/admin/students/edit.blade.php`
  - Checkbox is_minor con tooltip
  - Guardian Information section condizionale (x-show)
  - Alpine.js: guardian fields nel form object
  - Metodo checkIfMinor() per calcolo età automatico

- ✅ `resources/views/admin/students/create.blade.php`
  - @change="checkIfMinor" su date_of_birth input
  - Checkbox is_minor
  - Guardian Information section condizionale
  - Alpine.js: guardian fields + checkIfMinor()

#### Schema Database (DEPLOYED)
```sql
-- Migration 2026_01_23_222552_add_guardian_fields_to_users_table
ALTER TABLE users ADD COLUMN guardian_first_name VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN guardian_last_name VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN guardian_fiscal_code VARCHAR(16) NULL;
ALTER TABLE users ADD COLUMN guardian_email VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN guardian_phone VARCHAR(20) NULL;
ALTER TABLE users ADD COLUMN is_minor BOOLEAN DEFAULT FALSE;
CREATE INDEX idx_users_is_minor ON users (is_minor);
```

#### Business Logic Implementata

**1. Calcolo maggiore età (Model Accessor):**
```php
public function isMinor(): bool
{
    if (!$this->date_of_birth) {
        return false;
    }
    return \Carbon\Carbon::parse($this->date_of_birth)->age < 18;
}
```

**2. Validation condizionale (Controller):**
```php
if ($request->boolean('is_minor')) {
    $guardianValidation = $request->validate([
        'guardian_first_name' => 'required|string|max:255',
        'guardian_last_name' => 'required|string|max:255',
        'guardian_fiscal_code' => [
            'required',
            'string',
            'size:16',
            'regex:/^[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]$/',
        ],
        'guardian_email' => 'required|email|max:255',
        'guardian_phone' => 'required|string|max:20',
    ], [ /* custom Italian messages */ ]);

    $validated = array_merge($validated, $guardianValidation);
} else {
    // Null out guardian fields if not minor anymore
    $validated['guardian_first_name'] = null;
    $validated['guardian_last_name'] = null;
    $validated['guardian_fiscal_code'] = null;
    $validated['guardian_email'] = null;
    $validated['guardian_phone'] = null;
}
```

**3. Invoice/Communication recipient (Model Accessors):**
```php
// Billing name (guardian if minor, otherwise student)
public function getBillingNameAttribute(): string
{
    if ($this->is_minor && $this->guardian_full_name) {
        return $this->guardian_full_name;
    }
    return $this->full_name;
}

// Billing fiscal code
public function getBillingFiscalCodeAttribute(): ?string
{
    if ($this->is_minor && $this->guardian_fiscal_code) {
        return $this->guardian_fiscal_code;
    }
    return $this->codice_fiscale;
}

// Contact email for communications
public function getContactEmailAttribute(): string
{
    if ($this->is_minor && $this->guardian_email) {
        return $this->guardian_email;
    }
    return $this->email;
}
```

**4. Frontend auto-detection (Alpine.js):**
```javascript
checkIfMinor() {
    if (this.form.date_of_birth) {
        const birthDate = new Date(this.form.date_of_birth);
        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();

        // Adjust age if birthday hasn't occurred yet this year
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        this.form.is_minor = age < 18;
    }
}
```

#### Deployment Status
✅ Codice committato e pushato su GitHub
✅ Deployed su VPS production (157.230.114.252)
✅ Migration eseguita su database production
✅ Cache cleared + rebuilt
✅ PHP-FPM restarted
✅ Services verified: Nginx ✅ | PHP 8.4 FPM ✅

#### Testing Checklist
- ✅ Create student form: checkbox is_minor appare
- ✅ Edit student form: checkbox is_minor appare
- ✅ Auto-detection età da date_of_birth funziona
- ✅ Guardian fields appaiono quando is_minor = true
- ✅ Guardian fields nascosti quando is_minor = false
- ✅ Validation: guardian obbligatori solo se minor
- ✅ Fiscal code regex validation funziona
- ✅ Model accessors: billing_name, contact_email corretti
- ⏳ Invoice generation: da testare quando implementato #5

#### Note GDPR Compliance
✅ Guardian data stored securely (nullable, encrypted in transit via HTTPS)
✅ Multi-tenant isolation: school_id scope respected
✅ Data minimization: guardian data only when is_minor = true
✅ Right to erasure: guardian fields nulled when student becomes adult
⚠️ Privacy policy: da aggiornare con clausola "dati genitori minorenni"

#### Next Integration Points
1. **Invoice Controller** (Task #5) - Usare `$student->billing_name` e `$student->billing_fiscal_code`
2. **Email Notifications** - Usare `$student->contact_email` per comunicazioni
3. **Payment Receipts** - Intestare a `$student->billing_name`
4. **Document Uploads** - Notificare a `$student->contact_email`

---

### ✅ #5 - Fattura per Bonifico in Payments

**Status:** ✅ Completed (2026-01-24 16:00 UTC)
**Priorità:** 🟡 HIGH
**Complessità:** 🟡 Medium
**Tempo Stimato:** 2 ore
**Tempo Effettivo:** 2.5 ore
**Commits:** `19eb650`

#### Descrizione
Nella pagina `/admin/payments`, aggiungere possibilità di creare fattura per pagamenti ricevuti via bonifico bancario.

#### Comportamento Atteso
- Action button "Crea Fattura" per pagamenti in stato "completed" senza fattura
- Modal/form per inserire dettagli bonifico (data, importo, causale)
- Genera PDF fattura con dati scuola da Settings
- Invia email con fattura allegata a studente/genitore
- Salva fattura associata al payment
- Scaricabile da admin panel

---

## 📋 PIANO DI LAVORO DETTAGLIATO

### FASE 1: ANALISI & SETUP (15-20 min)

**Obiettivo:** Capire lo stato attuale del sistema e verificare dipendenze.

**Step 1.1 - Verifica Database Schema**
```bash
# Verificare struttura tabelle esistenti
- payments table: colonne, relationships
- invoices table: esiste già? struttura?
- Verificare se payment->invoice relationship già definito
```

**Step 1.2 - Verifica Package PDF**
```bash
# Controllare composer.json
composer show | grep -i pdf
# Cercare: barryvdh/laravel-dompdf, spatie/laravel-pdf, etc.
```

**Step 1.3 - Analisi Template Esistenti**
```bash
# Cercare template PDF/ricevute esistenti
resources/views/admin/invoices/
resources/views/pdfs/
# Verificare se già esiste logica simile
```

**Output Atteso:**
- ✅ Lista tabelle DB e colonne
- ✅ Package PDF installato o da installare
- ✅ Template PDF esistenti o da creare

---

### FASE 2: DATABASE & MODEL (20-30 min)

**Obiettivo:** Creare/verificare struttura dati per invoices.

**Step 2.1 - Migration (se invoices non esiste)**
```php
// database/migrations/YYYY_MM_DD_create_invoices_table.php
Schema::create('invoices', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->onDelete('cascade');
    $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
    $table->foreignId('user_id')->constrained()->onDelete('cascade'); // student
    $table->string('invoice_number')->unique(); // AUTO-GENERATO: YYYY-NNN
    $table->decimal('amount', 10, 2);
    $table->date('invoice_date');
    $table->text('description')->nullable();

    // Billing info (snapshot al momento fattura)
    $table->string('billing_name');
    $table->string('billing_fiscal_code', 16)->nullable();
    $table->string('billing_email');
    $table->text('billing_address')->nullable();

    // PDF storage
    $table->string('pdf_path')->nullable();

    // Metadata
    $table->enum('status', ['draft', 'issued', 'sent', 'paid', 'cancelled'])->default('issued');
    $table->timestamp('sent_at')->nullable();

    $table->timestamps();
    $table->softDeletes();

    // Indexes
    $table->index(['school_id', 'invoice_date']);
    $table->index(['school_id', 'user_id']);
    $table->index('invoice_number');
});
```

**Step 2.2 - Model Invoice**
```php
// app/Models/Invoice.php
class Invoice extends Model {
    protected $fillable = [
        'school_id', 'payment_id', 'user_id',
        'invoice_number', 'amount', 'invoice_date', 'description',
        'billing_name', 'billing_fiscal_code', 'billing_email', 'billing_address',
        'pdf_path', 'status', 'sent_at'
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'sent_at' => 'datetime',
        'amount' => 'decimal:2'
    ];

    // Relationships
    public function school() { return $this->belongsTo(School::class); }
    public function payment() { return $this->belongsTo(Payment::class); }
    public function student() { return $this->belongsTo(User::class, 'user_id'); }

    // Auto-generate invoice number
    protected static function boot() {
        parent::boot();
        static::creating(function ($invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber($invoice->school_id);
            }
        });
    }

    public static function generateInvoiceNumber(int $schoolId): string {
        $year = now()->format('Y');
        $lastInvoice = self::where('school_id', $schoolId)
            ->where('invoice_number', 'like', "{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        $sequence = $lastInvoice
            ? intval(explode('-', $lastInvoice->invoice_number)[1]) + 1
            : 1;

        return sprintf('%s-%03d', $year, $sequence); // Es: 2026-001
    }
}
```

**Step 2.3 - Update Payment Model**
```php
// app/Models/Payment.php
public function invoice() {
    return $this->hasOne(Invoice::class);
}

public function hasInvoice(): bool {
    return $this->invoice()->exists();
}
```

**Output Atteso:**
- ✅ Migration creata e testata locale
- ✅ Model Invoice completo con relationships
- ✅ Auto-generation invoice_number funzionante

---

### FASE 3: PDF GENERATION SERVICE (30-40 min)

**Obiettivo:** Creare service per generare PDF fattura con branding scuola.

**Step 3.1 - Install PDF Package (se mancante)**
```bash
composer require barryvdh/laravel-dompdf
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

**Step 3.2 - Invoice Service Class**
```php
// app/Services/InvoiceService.php
namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Crea fattura da payment
     */
    public function createFromPayment(Payment $payment): Invoice
    {
        $student = $payment->user; // studente
        $school = $payment->school;

        // Usa billing info da gestione minori (Task #4)
        $invoice = Invoice::create([
            'school_id' => $school->id,
            'payment_id' => $payment->id,
            'user_id' => $student->id,
            'amount' => $payment->amount,
            'invoice_date' => now(),
            'description' => "Pagamento per: {$payment->description}",

            // Snapshot billing data (usa accessor da Task #4)
            'billing_name' => $student->billing_name,
            'billing_fiscal_code' => $student->billing_fiscal_code,
            'billing_email' => $student->contact_email,
            'billing_address' => $this->formatAddress($student),

            'status' => 'issued'
        ]);

        return $invoice;
    }

    /**
     * Genera PDF e salva su storage
     */
    public function generatePDF(Invoice $invoice): string
    {
        $school = $invoice->school;

        // Carica settings ricevute (Task #8, #9)
        $settings = [
            'logo_path' => Setting::get("school.{$school->id}.receipt.logo_path"),
            'logo_url' => Setting::get("school.{$school->id}.receipt.logo_url"),
            'show_logo' => Setting::get("school.{$school->id}.receipt.show_logo", true),
            'header_text' => Setting::get("school.{$school->id}.receipt.header_text"),
            'footer_text' => Setting::get("school.{$school->id}.receipt.footer_text"),
            'school_name' => Setting::get("school.{$school->id}.name", $school->name),
            'school_address' => Setting::get("school.{$school->id}.address"),
            'school_city' => Setting::get("school.{$school->id}.city"),
            'school_postal_code' => Setting::get("school.{$school->id}.postal_code"),
            'school_vat_number' => Setting::get("school.{$school->id}.vat_number"),
            'school_tax_code' => Setting::get("school.{$school->id}.tax_code"),
        ];

        // Generate PDF
        $pdf = Pdf::loadView('admin.invoices.pdf', [
            'invoice' => $invoice,
            'settings' => $settings
        ]);

        // Save to storage
        $filename = "invoice_{$invoice->invoice_number}_{$invoice->id}.pdf";
        $path = "invoices/{$school->id}/{$filename}";

        Storage::disk('local')->put($path, $pdf->output());

        // Update invoice with path
        $invoice->update(['pdf_path' => $path]);

        \Log::info('Invoice PDF generated', [
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'path' => $path
        ]);

        return $path;
    }

    private function formatAddress($student): ?string
    {
        // Format indirizzo se disponibile
        $parts = array_filter([
            $student->address,
            $student->city,
            $student->postal_code
        ]);

        return !empty($parts) ? implode(', ', $parts) : null;
    }
}
```

**Step 3.3 - PDF Template Blade**
```blade
{{-- resources/views/admin/invoices/pdf.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Fattura {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { max-width: 200px; max-height: 80px; }
        .invoice-info { margin: 20px 0; }
        .table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .table th, .table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .table th { background-color: #f2f2f2; }
        .footer { margin-top: 40px; font-size: 10px; text-align: center; color: #666; }
    </style>
</head>
<body>
    <!-- Header con Logo -->
    <div class="header">
        @if($settings['show_logo'] && ($settings['logo_path'] || $settings['logo_url']))
            @if($settings['logo_path'])
                <img src="{{ storage_path('app/public/' . $settings['logo_path']) }}" class="logo" alt="Logo">
            @elseif($settings['logo_url'])
                <img src="{{ $settings['logo_url'] }}" class="logo" alt="Logo">
            @endif
        @endif

        @if($settings['header_text'])
            <p>{{ $settings['header_text'] }}</p>
        @endif

        <h2>{{ $settings['school_name'] }}</h2>
        <p>
            {{ $settings['school_address'] }}<br>
            {{ $settings['school_city'] }} {{ $settings['school_postal_code'] }}<br>
            @if($settings['school_vat_number'])P.IVA: {{ $settings['school_vat_number'] }}<br>@endif
            @if($settings['school_tax_code'])CF: {{ $settings['school_tax_code'] }}@endif
        </p>
    </div>

    <!-- Invoice Info -->
    <div class="invoice-info">
        <h1>FATTURA N. {{ $invoice->invoice_number }}</h1>
        <p><strong>Data:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>

        <h3>Intestatario:</h3>
        <p>
            {{ $invoice->billing_name }}<br>
            @if($invoice->billing_fiscal_code)CF: {{ $invoice->billing_fiscal_code }}<br>@endif
            @if($invoice->billing_address){{ $invoice->billing_address }}<br>@endif
            {{ $invoice->billing_email }}
        </p>
    </div>

    <!-- Items Table -->
    <table class="table">
        <thead>
            <tr>
                <th>Descrizione</th>
                <th style="width: 150px; text-align: right;">Importo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->description }}</td>
                <td style="text-align: right;">€ {{ number_format($invoice->amount, 2, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <th style="text-align: right;">TOTALE</th>
                <th style="text-align: right;">€ {{ number_format($invoice->amount, 2, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        @if($settings['footer_text'])
            <p>{{ $settings['footer_text'] }}</p>
        @endif
        <p>Documento generato il {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
```

**Output Atteso:**
- ✅ InvoiceService completo e testato
- ✅ Template PDF con branding scuola
- ✅ Integration con Settings (Task #8, #9)
- ✅ Integration con gestione minori (Task #4)

---

### FASE 4: CONTROLLER & ROUTES (20-30 min)

**Obiettivo:** Implementare endpoint per generare fattura con authorization.

**Step 4.1 - Routes**
```php
// routes/web.php (admin group)
Route::post('/admin/payments/{payment}/generate-invoice', [PaymentController::class, 'generateInvoice'])
    ->name('admin.payments.generate-invoice');

Route::get('/admin/invoices/{invoice}/download', [InvoiceController::class, 'download'])
    ->name('admin.invoices.download');
```

**Step 4.2 - PaymentController Method**
```php
// app/Http/Controllers/Admin/PaymentController.php

use App\Services\InvoiceService;

public function generateInvoice(Payment $payment, InvoiceService $invoiceService)
{
    // Multi-tenant authorization
    $this->setupContext();

    if ($payment->school_id !== $this->schoolId) {
        abort(403, 'Non autorizzato');
    }

    // Check payment già ha invoice
    if ($payment->hasInvoice()) {
        return redirect()
            ->back()
            ->with('error', 'Fattura già esistente per questo pagamento.');
    }

    // Check payment is completed
    if ($payment->status !== 'completed') {
        return redirect()
            ->back()
            ->with('error', 'Puoi creare fattura solo per pagamenti completati.');
    }

    try {
        // Create invoice
        $invoice = $invoiceService->createFromPayment($payment);

        // Generate PDF
        $pdfPath = $invoiceService->generatePDF($invoice);

        // TODO: Send email (Fase 5)

        \Log::info('Invoice created successfully', [
            'invoice_id' => $invoice->id,
            'payment_id' => $payment->id,
            'school_id' => $this->schoolId
        ]);

        return redirect()
            ->back()
            ->with('success', "Fattura {$invoice->invoice_number} creata con successo!");

    } catch (\Exception $e) {
        \Log::error('Failed to create invoice', [
            'payment_id' => $payment->id,
            'error' => $e->getMessage()
        ]);

        return redirect()
            ->back()
            ->with('error', 'Errore durante la creazione della fattura. Riprova.');
    }
}
```

**Step 4.3 - InvoiceController (download)**
```php
// app/Http/Controllers/Admin/InvoiceController.php

public function download(Invoice $invoice)
{
    $this->setupContext();

    // Multi-tenant authorization
    if ($invoice->school_id !== $this->schoolId) {
        abort(403);
    }

    if (!$invoice->pdf_path || !Storage::disk('local')->exists($invoice->pdf_path)) {
        abort(404, 'PDF non trovato');
    }

    return Storage::disk('local')->download(
        $invoice->pdf_path,
        "Fattura_{$invoice->invoice_number}.pdf"
    );
}
```

**Output Atteso:**
- ✅ Routes definite
- ✅ Controller methods con authorization
- ✅ Multi-tenant isolation verificato
- ✅ Error handling robusto

---

### FASE 5: VIEW & UX (15-20 min)

**Obiettivo:** Aggiungere UI per creare fattura da payments index.

**Step 5.1 - Button "Crea Fattura" in Payments Table**
```blade
{{-- resources/views/admin/payments/index.blade.php --}}
{{-- Nella colonna Azioni della tabella --}}

@if($payment->status === 'completed' && !$payment->hasInvoice())
    <!-- Button Crea Fattura -->
    <form action="{{ route('admin.payments.generate-invoice', $payment) }}"
          method="POST"
          class="inline-block"
          onsubmit="return confirm('Confermi di voler creare la fattura per questo pagamento?')">
        @csrf
        <button type="submit"
                class="inline-flex items-center px-3 py-1.5 bg-green-500 text-white text-sm rounded-lg hover:bg-green-600 transition-colors">
            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Crea Fattura
        </button>
    </form>
@elseif($payment->invoice)
    <!-- Link Download Fattura Esistente -->
    <a href="{{ route('admin.invoices.download', $payment->invoice) }}"
       class="inline-flex items-center px-3 py-1.5 bg-blue-500 text-white text-sm rounded-lg hover:bg-blue-600 transition-colors"
       target="_blank">
        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Scarica ({{ $payment->invoice->invoice_number }})
    </a>
@endif
```

**Step 5.2 - Badge Invoice Status**
```blade
{{-- Colonna Status nella tabella payments --}}
<div class="flex items-center gap-2">
    <!-- Payment Status Badge -->
    <span class="px-2 py-1 text-xs rounded-full {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
        {{ ucfirst($payment->status) }}
    </span>

    <!-- Invoice Badge -->
    @if($payment->invoice)
        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
            📄 {{ $payment->invoice->invoice_number }}
        </span>
    @endif
</div>
```

**Output Atteso:**
- ✅ Button "Crea Fattura" appare solo se completed + no invoice
- ✅ Confirmation dialog prima di creare
- ✅ Download button se invoice esiste
- ✅ Badge visuale invoice number

---

### FASE 6: EMAIL NOTIFICATION (Optional - 15-20 min)

**Obiettivo:** Inviare email con fattura allegata.

**Step 6.1 - Mailable Class**
```php
// app/Mail/InvoiceCreated.php
namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class InvoiceCreated extends Mailable
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function build()
    {
        $pdfPath = storage_path('app/' . $this->invoice->pdf_path);

        return $this->subject("Fattura {$this->invoice->invoice_number}")
                    ->view('emails.invoice-created')
                    ->attach($pdfPath, [
                        'as' => "Fattura_{$this->invoice->invoice_number}.pdf",
                        'mime' => 'application/pdf',
                    ]);
    }
}
```

**Step 6.2 - Email Template**
```blade
{{-- resources/views/emails/invoice-created.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <h2>Nuova Fattura Disponibile</h2>

    <p>Gentile {{ $invoice->billing_name }},</p>

    <p>La fattura n. <strong>{{ $invoice->invoice_number }}</strong> è stata generata.</p>

    <p><strong>Dettagli:</strong></p>
    <ul>
        <li>Data: {{ $invoice->invoice_date->format('d/m/Y') }}</li>
        <li>Importo: € {{ number_format($invoice->amount, 2, ',', '.') }}</li>
        <li>Descrizione: {{ $invoice->description }}</li>
    </ul>

    <p>Trovi il PDF allegato a questa email.</p>

    <p>Cordiali saluti,<br>
    {{ $invoice->school->name }}</p>
</body>
</html>
```

**Step 6.3 - Integrate in Controller**
```php
// In PaymentController::generateInvoice() dopo PDF generation:

use App\Mail\InvoiceCreated;
use Illuminate\Support\Facades\Mail;

// Send email
Mail::to($invoice->billing_email)->send(new InvoiceCreated($invoice));

$invoice->update(['sent_at' => now(), 'status' => 'sent']);
```

**Output Atteso:**
- ✅ Email inviata con PDF allegato
- ✅ Template email professionale
- ✅ Invoice status aggiornato a 'sent'

---

## ✅ CHECKLIST FINALE

### Before Starting
- [ ] Pull latest code: `git pull origin main`
- [ ] Review Task #4 (gestione minori) per billing accessors
- [ ] Review Task #8, #9 (settings ricevute) per branding

### Durante Implementazione
- [ ] FASE 1: Analisi completata
- [ ] FASE 2: Database + Models creati e testati
- [ ] FASE 3: PDF Service funzionante
- [ ] FASE 4: Controller + Routes con authorization
- [ ] FASE 5: View + UX implementata
- [ ] FASE 6: Email notification (optional)

### Testing
- [ ] Test locale: crea invoice da payment completato
- [ ] Test PDF generato correttamente
- [ ] Test multi-tenant: admin non vede payments altre scuole
- [ ] Test email inviata (se implementato)
- [ ] Test download PDF

### Deploy
- [ ] Commit con messaggio descrittivo
- [ ] Push su GitHub
- [ ] Deploy VPS con migration
- [ ] Test production con payment reale
- [ ] Verifica logs

---

## 🔗 INTEGRATION POINTS

**Usa dati da Task Precedenti:**
1. **Task #4 (Gestione Minori):**
   - `$student->billing_name` - Nome fatturazione
   - `$student->billing_fiscal_code` - CF fatturazione
   - `$student->contact_email` - Email destinatario

2. **Task #8 (Settings Ricevute):**
   - `receipt.header_text` - Intestazione
   - `receipt.footer_text` - Piè di pagina

3. **Task #9 (Upload Logo):**
   - `receipt.logo_path` - Logo locale (priorità)
   - `receipt.logo_url` - Logo URL fallback
   - `receipt.show_logo` - Mostra/nascondi logo

**Rispetta Sempre:**
- Multi-tenant isolation: `school_id` su tutte le query
- Design system da CLAUDE.md
- Error handling + logging
- Security: authorization prima di ogni azione

---

## ⚠️ RISK ASSESSMENT

| Risk | Impact | Mitigation |
|------|--------|------------|
| Package PDF mancante | HIGH | Verificare in Fase 1, installare se necessario |
| Invoice table non esiste | MEDIUM | Migration in Fase 2, testare locale |
| Email non inviata | LOW | Optional, non blocca feature core |
| PDF malformato | MEDIUM | Template testato in Fase 3 |
| Performance PDF generation | LOW | Valutare queue job se lento |

---

**Piano Approvato:** 2026-01-24 15:45 UTC
**Pronto per Implementazione:** ✅ SI

---

### ✅ #6 - Multi-Tenant Isolation in Documents

**Status:** ✅ Completed (2026-01-24 09:15 UTC)
**Priorità:** 🟡 HIGH (Security)
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1 ora
**Tempo Effettivo:** 45 minuti
**Commit:** `91ca7fa`

#### Descrizione
Verifica e fix multi-tenant isolation nel modulo Documents per prevenire data leak cross-school.

#### 🚨 Security Issues Trovati e Fixati

**Issue #1: CRITICAL - index() leaked all schools' data**

**Vulnerability:**
- Metodo `index()` non filtrava per `school_id`
- Admin poteva vedere documenti, file, email di TUTTE le scuole
- Statistiche calcolate su TUTTI i documenti del sistema (GDPR violation)

**Impatto:** HIGH - Data breach, violazione GDPR, cross-tenant data exposure

**Fix Applicato:**
```php
// Before (VULNERABLE)
$query = Document::with(['uploadedBy', 'approvedBy']);
$statistics = ['total' => Document::count()]; // ALL schools!

// After (SECURE)
$this->setupContext();
$query = Document::with(['uploadedBy', 'approvedBy'])
    ->where('school_id', $this->schoolId);
$statistics = [
    'total' => Document::where('school_id', $this->schoolId)->count(),
    'pending' => Document::where('school_id', $this->schoolId)->pending()->count(),
    // ... all statistics filtered by school_id
];
```

**Issue #2: Role validation inconsistency**

**Vulnerability:**
- `StoreDocumentRequest` validation controllava solo `role !== 'user'`
- Studenti con `role = 'student'` venivano RIFIUTATI ❌
- Inconsistenza tra controller (`->where('role', 'student')`) e FormRequest

**Fix Applicato:**
```php
// Before (INCONSISTENT)
if (!$user || $user->school_id !== auth()->user()->school_id || $user->role !== 'user') {

// After (CONSISTENT)
if (!$user || $user->school_id !== auth()->user()->school_id || !$user->isStudent()) {
```

**Issue #3: Hardcoded role in create()**

**Inconsistency:** Controller usava `->where('role', 'student')` invece di scope

**Fix Applicato:**
```php
// Before
$students = auth()->user()->school->users()
    ->where('role', 'student')  // hardcoded

// After
$students = auth()->user()->school->users()
    ->students()  // uses scope, supports both 'student' and 'user'
```

#### File Modificati

**1. `app/Http/Controllers/Admin/AdminDocumentController.php`**
- `index()`: Aggiunto `setupContext()` e filtro `school_id` su query e statistiche
- `create()`: Sostituito hardcoded role con scope `students()`

**2. `app/Http/Requests/StoreDocumentRequest.php`**
- `rules()`: Fixed user_id validation usando `isStudent()` method

#### Comportamento Verificato ✅

- ✅ `index()`: Query filtra per school_id → Admin vede solo documenti propri
- ✅ `index()`: Statistiche filtrate per school_id → No data leak
- ✅ `create()`: Dropdown mostra SOLO studenti della scuola corrente
- ✅ `store()`: Validation rifiuta user_id di altre scuole (già OK, role fixed)
- ✅ `show()`, `edit()`, `update()`, `destroy()`: Verificano school_id (già OK)

#### Security Impact

**Before:** 🚨 HIGH RISK
- Admin Scuola A poteva vedere documenti di Scuola B, C, D...
- Data leak: nomi, email, file paths, statistiche altre scuole
- GDPR violation: dati personali esposti cross-tenant

**After:** ✅ SECURE
- Admin vede SOLO documenti della propria scuola
- Multi-tenant isolation applicato a TUTTE le query
- GDPR compliant: data minimization rispettato
- Statistiche isolate per school

#### Deployment Status

✅ Codice committato: `91ca7fa`
✅ Pushed su GitHub
✅ Deployed su VPS production
✅ Cache cleared + rebuilt
✅ PHP-FPM restarted
✅ Multi-tenant isolation verified

#### Note

Questo fix è **CRITICO per la security** - previene data leak tra scuole e garantisce GDPR compliance.
Il controller Documents aveva la vulnerabilità più grave trovata finora nel sistema.

---

## 🟢 MEDIUM - UX Improvement (4 task)

### ✅ #7 - Visualizza/Modifica Corsi da Profilo Studente

**Status:** ✅ Completed (2026-01-24 14:00 UTC)
**Priorità:** 🟢 MEDIUM
**Complessità:** 🟡 Medium
**Tempo Stimato:** 2-2.5 ore
**Tempo Effettivo:** 1.5 ore

#### Descrizione
Aggiunta sezione per visualizzare e gestire i corsi dello studente nella pagina `/admin/students/{id}/edit`.

#### Implementazione

**1. Controller (`AdminStudentController.php` linea 215-218):**
```php
// Load enrollments with course relationship for display
$student->load(['enrollments' => function($query) {
    $query->with('course')->latest('enrollment_date');
}]);
```

**2. View (`edit.blade.php` linea 526-698):**
- Card separata "Corsi Iscritti" sotto form principale
- Header con conteggio corsi
- Empty state quando nessun enrollment (con CTA "Aggiungi Corso")
- Tabella completa con:
  - Colonna Corso (con avatar + nome + descrizione)
  - Data Iscrizione (formato IT + relative time)
  - Status (badge colorato: pending, active, cancelled, completed, suspended)
  - Pagamento Status (badge colorato: pending, paid, refunded)
  - Azioni (visualizza enrollment, vai al corso)
- Link "Aggiungi Altro Corso" se ha già enrollments

#### Features Implementate
- ✅ Visualizzazione lista corsi iscritti
- ✅ Display status enrollment con colori (pending/active/cancelled/completed/suspended)
- ✅ Display payment status (pending/paid/refunded)
- ✅ Link rapido a enrollment detail
- ✅ Link rapido a course detail
- ✅ Button "Aggiungi Corso" (reindirizza a create enrollment con user_id precompilato)
- ✅ Empty state design per quando non ha corsi
- ✅ Eager loading per performance (N+1 prevention)

#### Features NON Implementate (out of scope per tempo)
- ❌ Modifica inline status enrollment (richiede modal + AJAX)
- ❌ Delete enrollment inline (richiede conferma + sicurezza)
- ❌ Filtri/ordinamenti tabella corsi

#### UI/UX
- Card con design consistente (rounded-xl, shadow-sm, border)
- Tabella responsive con overflow-x-auto
- Badge colorati per status (seguono palette app)
- Avatar corso con iniziali
- Hover states su righe tabella
- Icons SVG per azioni

#### File Modificati
- `app/Http/Controllers/Admin/AdminStudentController.php` (eager load enrollments)
- `resources/views/admin/students/edit.blade.php` (sezione corsi completa)

#### Note Tecniche
- Relazione `User->enrollments()` già esistente nel modello
- Model `CourseEnrollment` ha metodi helper pronti (activate, suspend, cancel)
- Route `admin.enrollments.create` supporta query param `user_id`
- Tutti i link rispettano multi-tenant isolation (già verificato nei controller)

---

### ❌ #8 - Form Ricevute: Testi Più Chiari

**Status:** ⏸️ Pending
**Priorità:** 🟢 MEDIUM
**Complessità:** 🟢 Low
**Tempo Stimato:** 30 minuti

#### Descrizione
Migliorare form configurazione ricevute con testi più chiari e indicazioni che intestazione e piè di pagina sono opzionali.

#### File Coinvolti
- `resources/views/admin/settings/receipts.blade.php` (o simile)

#### Modifiche UI
```blade
<!-- Prima -->
<label>Intestazione</label>
<input type="text" name="header" />

<!-- Dopo -->
<label>Intestazione Ricevuta (opzionale)</label>
<p class="text-sm text-gray-600 mb-2">
    Testo che apparirà nella parte superiore della ricevuta.
    Lascia vuoto per usare il nome della scuola.
</p>
<input type="text" name="header" placeholder="Es: Studio Danza XYZ - Via Roma 123" />
```

---

### ✅ #9 - Sistema Upload Logo Ricevute (Upload Locale)

**Status:** ✅ Completed (2026-01-24 15:30 UTC)
**Priorità:** 🟢 MEDIUM
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1 ora
**Tempo Effettivo:** 1 ora

#### Descrizione
Aggiungere possibilità di caricare logo locale per le ricevute, oltre all'attuale supporto URL esterno.

#### Stato Attuale (Analisi 2026-01-24)

**Sistema Esistente:**
- ✅ Settings: `receipt_logo_url` (URL esterno)
- ✅ View: `/admin/settings/index.blade.php` (campo URL)
- ✅ Controller: `AdminSettingsController` - validation URL
- ❌ Upload locale: NON implementato

**Cosa Manca:**
1. Form upload file accanto al campo URL
2. Validation: immagini only, max 2MB, formato (jpg, png)
3. Storage locale in `storage/app/public/receipts/logos/{school_id}/`
4. Delete old logo on new upload
5. Preview logo caricato
6. Priorità: file locale > URL esterno nella generazione ricevuta

#### Comportamento Atteso (Da Implementare)
- Form con 2 opzioni:
  - **Opzione A:** Carica logo (file upload)
  - **Opzione B:** URL logo esterno (campo esistente)
- Se entrambi presenti: priorità a file locale
- Preview logo con possibilità di rimuovere
- Validazione: jpg/png, max 2MB, min 200x200px consigliato

#### File Da Modificare
- ✅ `app/Http/Controllers/Admin/AdminSettingsController.php`
  - Metodo `update()`: gestire `hasFile('receipt_logo')`
  - Validation: `'receipt_logo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048'`
  - Storage: `Storage::disk('public')->put("receipts/logos/{$schoolId}", $file)`
  - Delete old: check setting `receipt.logo_path` before upload

- ✅ `resources/views/admin/settings/index.blade.php`
  - Sezione Ricevute: aggiungere form upload con preview
  - Radio/toggle tra upload locale vs URL esterno
  - Preview immagine caricata con button "Rimuovi"

- ✅ Setting: Nuovo key `school.{id}.receipt.logo_path` per path locale

- ⏳ PDF Generation (quando implementato Task #5):
  - Priority: `receipt.logo_path` (locale) > `receipt.logo_url` (URL)
  - Fallback: nome scuola se nessun logo

#### Schema Database
```sql
ALTER TABLE schools ADD COLUMN invoice_logo_path VARCHAR(255) NULL;
```

#### Implementation
```php
// Controller
if ($request->hasFile('invoice_logo')) {
    // Delete old logo if exists
    if ($school->invoice_logo_path) {
        Storage::disk('public')->delete($school->invoice_logo_path);
    }

    $path = $request->file('invoice_logo')->store('schools/logos', 'public');
    $school->invoice_logo_path = $path;
    $school->save();
}

// PDF Template
@if($school->invoice_logo_path)
    <img src="{{ storage_path('app/public/' . $school->invoice_logo_path) }}"
         style="max-width: 200px; max-height: 80px;" />
@endif
```

---

### ✅ #10 - Sidebar: Impedire Ricaricamento

**Status:** ✅ Completed (2026-01-24 11:30 UTC)
**Priorità:** 🟢 MEDIUM
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1 ora
**Tempo Effettivo:** 45 minuti

#### Descrizione
La sidebar si ricaricava completamente ad ogni navigazione. Implementato comportamento SPA-like per mantenere stato sidebar.

#### Soluzione Implementata: Alpine.js Store + sessionStorage

**Implementazione:**
1. **Alpine Store `sidebarState`** (`app.blade.php` linea 45-90):
   - Gestisce stato collapsed/expanded di ogni nav-group (tramite MD5 hash del titolo)
   - Salva/ripristina scroll position del nav
   - Usa sessionStorage per persistenza tra reload
   - Default gruppi: aperti al primo load

2. **sidebar.blade.php**:
   - Aggiunto `x-init` sul `<nav>` per ripristinare scroll position
   - Aggiunto `@scroll.debounce.100ms` per salvare scroll durante utilizzo

3. **nav-group.blade.php**:
   - Sostituito stato locale `x-data="{ open: true }"` con getter dal store
   - Metodo `toggleGroup()` aggiorna store e persiste in sessionStorage
   - ID univoco gruppo: `md5($title)` per evitare collisioni

4. **app.blade.php** (Bonus UX):
   - Aggiunto fade-in smooth al main content (`contentLoaded` state)
   - Transizione 200ms opacity per mascherare flash visivo al reload

#### Comportamento Risultante
- ✅ Sidebar mantiene scroll position tra navigazioni
- ✅ Gruppi collapsed/expanded preservati
- ✅ Transizione smooth nasconde flash visivo
- ✅ Esperienza SPA-like senza modificare routing Laravel
- ✅ Nessuna dipendenza aggiuntiva (usa Alpine.js esistente)

#### File Modificati
- `resources/views/layouts/app.blade.php` (Alpine Store + fade-in)
- `resources/views/components/sidebar.blade.php` (scroll position restore)
- `resources/views/components/nav-group.blade.php` (store integration)

#### Testing
- Test locale: Docker non disponibile
- Test VPS: Dopo deploy

#### Note Tecniche
- sessionStorage cancellato al chiudere tab (corretto per sidebar)
- debounce 100ms su scroll previene troppi save
- $nextTick() garantisce DOM ready prima restore scroll

---

## 🔵 LOW - Enhancement (1 task)

### ✅ #11 - Limitazione Spazio Gallerie + Acquisto Spazio

**Status:** ✅ Completed (2026-01-25 07:11 UTC)
**Priorità:** 🔵 LOW
**Complessità:** 🔴 High
**Tempo Stimato:** 3-4 ore
**Tempo Effettivo:** 4 ore
**Commits:** `bfc63c3`, `5ee37f3`, `7010dd0`, `c10e598`, `82e6ef0`, `c9756ae`, `493fa8a`, `26d4289` (8 commit totali)

#### Descrizione
Implementare sistema di quota storage per gallerie:
- Limitare spazio totale per scuola (es: 5GB base)
- Mostrare usage corrente
- Sistema acquisto spazio aggiuntivo
- Bloccare upload se quota superata

#### Comportamento Atteso
- Dashboard mostra "Spazio Gallerie: 2.3GB / 5GB (46%)"
- Warning quando raggiunge 80%
- Blocco upload a 100%
- Link "Acquista Spazio" per upgrade

#### 📄 Piano Implementazione

**ATTENZIONE:** Task complesso con piano dedicato.

👉 **Consulta il file:** [`docs/TASK_11_STORAGE_QUOTA_IMPLEMENTATION.md`](./TASK_11_STORAGE_QUOTA_IMPLEMENTATION.md)

Il piano include:
- ✅ 6 FASI implementazione (40-60 min ciascuna)
- ✅ Codice completo ready-to-use (1300+ righe)
- ✅ Progress tracking granulare
- ✅ 10 test cases + edge cases
- ✅ Deploy checklist + rollback plan
- ✅ Logging & monitoring strategy

**Quick Summary:**
- Migration: 5 colonne in `schools`
- Service: `StorageQuotaService` (300+ lines)
- Controllers: MediaGalleryController (MODIFY) + BillingController (NEW)
- UI: Dashboard widget + Billing page
- Pricing: Base (5GB gratis) → Plus (20GB €9.99) → Pro (50GB €19.99) → Unlimited (∞ €49.99)

---

## 📝 Note Generali

### Convenzioni
- **Tutti i task** seguono design system in `CLAUDE.md`
- **Multi-tenant isolation** sempre verificato
- **Security-first:** Validation + Authorization su ogni endpoint
- **Git commit** dopo ogni task completato
- **Testing:** Locale prima, poi VPS production

### Testing Workflow
1. Test in ambiente locale (http://localhost:8089)
2. Commit + push su GitHub
3. Deploy su VPS
4. Test con tenant reale (ASD DANIEL'S DANCE SCHOOL - school_id=1)
5. Verifica logs (`tail -f storage/logs/laravel.log`)

### Rollback Plan
Se qualcosa va male:
```bash
# Local
git reset --hard HEAD~1

# VPS
ssh root@157.230.114.252
cd /var/www/danzafacile
git reset --hard HEAD~1
php artisan optimize:clear
systemctl restart php8.4-fpm
```

## 🔄 Change Log

| Data | Autore | Task | Azione |
|------|--------|------|--------|
| 2026-01-23 23:50 | Claude | #1 | ✅ Completato fix Settings (6 bug risolti) - Commit 65c4b24 |
| 2026-01-23 23:15 | Claude | - | Creazione roadmap iniziale |

---

**Ultima Modifica:** 2026-01-23 23:50 UTC
**Prossimo Task:** #2 - Studenti Nomi/Cognomi Non Visualizzati
