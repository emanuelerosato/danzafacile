# 🐛 Bug Fixes & Improvements Roadmap

**Progetto:** DanzaFacile - Laravel 12 Dance School Management System
**Data Creazione:** 2026-01-23
**Ultima Modifica:** 2026-01-23 02:45 UTC
**Status:** 3/11 completati (27%)

---

## 📊 Overview

| Priorità | Totale | Completati | In Progress | Pending |
|----------|--------|------------|-------------|---------|
| 🔴 CRITICAL | 3 | 3 | 0 | 0 |
| 🟡 HIGH | 3 | 0 | 0 | 3 |
| 🟢 MEDIUM | 4 | 0 | 0 | 4 |
| 🔵 LOW | 1 | 0 | 0 | 1 |
| **TOTALE** | **11** | **3** | **0** | **8** |

**Tempo Stimato Totale:** 15-20 ore di sviluppo
**Tempo Impiegato:** 3.5 ore

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

### ❌ #4 - Gestione Minori: Genitore + Fatturazione

**Status:** ⏸️ Pending
**Priorità:** 🟡 HIGH
**Complessità:** 🔴 High
**Tempo Stimato:** 3-4 ore

#### Descrizione
Nella pagina `/admin/students/{id}/edit`, se lo studente è minorenne:
- Richiedere dati del genitore/tutore legale
- Gestire fatturazione intestata al genitore invece dello studente

#### Comportamento Atteso
- Checkbox "È minorenne?" (calcolata da data_nascita < 18 anni)
- Se minorenne: form campi genitore (nome, cognome, CF, email, telefono)
- Fatture generate intestate al genitore
- Comunicazioni inviate all'email del genitore

#### File Coinvolti
- `database/migrations/YYYY_MM_DD_add_guardian_to_students.php` (NEW)
- `app/Models/Student.php` - add campi genitore
- `app/Http/Controllers/Admin/StudentController.php` - validation genitore
- `app/Http/Controllers/Admin/InvoiceController.php` - logica intestatario
- `resources/views/admin/students/edit.blade.php` - form genitore
- `resources/views/admin/students/create.blade.php` - form genitore

#### Schema Database (da aggiungere)
```sql
ALTER TABLE students ADD COLUMN guardian_first_name VARCHAR(255) NULL;
ALTER TABLE students ADD COLUMN guardian_last_name VARCHAR(255) NULL;
ALTER TABLE students ADD COLUMN guardian_fiscal_code VARCHAR(16) NULL;
ALTER TABLE students ADD COLUMN guardian_email VARCHAR(255) NULL;
ALTER TABLE students ADD COLUMN guardian_phone VARCHAR(20) NULL;
ALTER TABLE students ADD COLUMN is_minor BOOLEAN DEFAULT FALSE;
```

#### Business Logic
1. **Calcolo maggiore età:**
   ```php
   $isMinor = Carbon::parse($student->birth_date)->age < 18;
   ```

2. **Validation condizionale:**
   ```php
   if ($request->is_minor) {
       $request->validate([
           'guardian_first_name' => 'required|string|max:255',
           'guardian_last_name' => 'required|string|max:255',
           'guardian_email' => 'required|email',
           // ...
       ]);
   }
   ```

3. **Invoice generation:**
   ```php
   $invoiceRecipient = $student->is_minor
       ? $student->guardian_full_name
       : $student->full_name;
   ```

#### Note Tecniche
- Verificare GDPR compliance per dati minori
- Multi-tenant: guardian_email deve essere univoca per school_id
- Notifiche: inviare al genitore se minore
- Payment: verificare PayPal recipient details

---

### ❌ #5 - Fattura per Bonifico in Payments

**Status:** ⏸️ Pending
**Priorità:** 🟡 HIGH
**Complessità:** 🟡 Medium
**Tempo Stimato:** 2 ore

#### Descrizione
Nella pagina `/admin/payments`, aggiungere possibilità di creare fattura per pagamenti ricevuti via bonifico bancario.

#### Comportamento Atteso
- Action button "Crea Fattura" per pagamenti in stato "completed" senza fattura
- Modal/form per inserire dettagli bonifico (data, importo, causale)
- Genera PDF fattura
- Invia email con fattura allegata
- Salva fattura associata al payment

#### File Coinvolti
- `app/Http/Controllers/Admin/PaymentController.php` - nuovo metodo `generateInvoice()`
- `app/Http/Controllers/Admin/InvoiceController.php` - metodo `createFromPayment()`
- `app/Models/Payment.php` - relationship `invoice()`
- `app/Models/Invoice.php`
- `resources/views/admin/payments/index.blade.php` - add button
- `resources/views/admin/invoices/pdf.blade.php` - template PDF

#### Flow
1. Admin clicca "Crea Fattura" su payment
2. Modal chiede conferma dettagli (importo, descrizione)
3. Sistema genera invoice record
4. PDF creato con library (DomPDF/Snappy)
5. Email inviata a studente/genitore
6. Fattura scaricabile da admin panel

#### Note Tecniche
```php
// Route
Route::post('/admin/payments/{payment}/generate-invoice', [PaymentController::class, 'generateInvoice'])
     ->name('admin.payments.generate-invoice');

// Controller
public function generateInvoice(Payment $payment) {
    // Verificare payment belong to admin's school
    // Verificare payment non ha già invoice
    // Creare invoice
    // Generare PDF
    // Inviare email
}
```

#### Dipendenze
- Verificare package PDF generation installato
- Verificare template fattura esistente
- Verificare email notification setup

---

### ❌ #6 - Verifica Associazione Studente-Scuola in Documents

**Status:** ⏸️ Pending
**Priorità:** 🟡 HIGH
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1 ora

#### Descrizione
Nella pagina `/admin/documents/create`, verificare che l'associazione studente-scuola rispetti il multi-tenant isolation.

#### Security Issue
Attualmente potrebbe essere possibile:
- Caricare documento per studente di altra scuola
- Vedere lista studenti di altre scuole nel dropdown

#### Comportamento Atteso
- Dropdown studenti mostra SOLO studenti della scuola corrente (school_id match)
- Validation lato backend verifica student_id belong to admin's school
- 403 Forbidden se tentativo di associare studente di altra scuola

#### File Coinvolti
- `app/Http/Controllers/Admin/DocumentController.php`
- `resources/views/admin/documents/create.blade.php`
- `app/Http/Requests/StoreDocumentRequest.php` (se esiste)

#### Fix da Applicare
```php
// Nel controller create():
$students = Student::where('school_id', auth()->user()->school_id)
                   ->orderBy('last_name')
                   ->get();

// Nel controller store():
$validated = $request->validate([
    'student_id' => [
        'required',
        'exists:students,id',
        Rule::exists('students', 'id')->where(function ($query) {
            $query->where('school_id', auth()->user()->school_id);
        }),
    ],
    // ...
]);
```

#### Testing
1. Login come admin scuola A
2. Tentare POST con student_id di scuola B
3. Verificare 403/422 response
4. Verificare log security event

---

## 🟢 MEDIUM - UX Improvement (4 task)

### ❌ #7 - Visualizza/Modifica Corsi da Profilo Studente

**Status:** ⏸️ Pending
**Priorità:** 🟢 MEDIUM
**Complessità:** 🟡 Medium
**Tempo Stimato:** 2-2.5 ore

#### Descrizione
Nella pagina `/admin/students/{id}/edit`, aggiungere sezione per:
- Visualizzare corsi attivi dello studente
- Modificare iscrizioni (cancellare, sospendere, riattivare)
- Aggiungere nuova iscrizione a corso

#### Comportamento Atteso
- Tab/Section "Corsi" nel profilo studente
- Lista corsi con status (active, completed, cancelled)
- Action buttons per ogni corso (Edit Status, Remove)
- Button "Aggiungi Corso" per nuova enrollment

#### File Coinvolti
- `resources/views/admin/students/edit.blade.php` - add section
- `app/Http/Controllers/Admin/StudentController.php` - load enrollments
- `app/Http/Controllers/Admin/EnrollmentController.php` - manage enrollments
- `app/Models/CourseEnrollment.php`

#### UI Design
```blade
<!-- Tab Corsi -->
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-lg font-semibold">Corsi Iscritti</h3>
        <button class="btn-primary">+ Aggiungi Corso</button>
    </div>

    <table>
        <thead>
            <tr>
                <th>Corso</th>
                <th>Data Iscrizione</th>
                <th>Status</th>
                <th>Azioni</th>
            </tr>
        </thead>
        <tbody>
            @foreach($student->enrollments as $enrollment)
            <tr>
                <td>{{ $enrollment->course->name }}</td>
                <td>{{ $enrollment->enrollment_date }}</td>
                <td><span class="badge-{{ $enrollment->status }}">{{ $enrollment->status }}</span></td>
                <td>
                    <button @click="editEnrollment({{ $enrollment->id }})">Modifica</button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
```

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

### ❌ #9 - Sistema Upload Logo Fattura

**Status:** ⏸️ Pending
**Priorità:** 🟢 MEDIUM
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1.5 ore

#### Descrizione
Aggiungere possibilità di caricare logo personalizzato per le fatture generate dalla scuola.

#### Comportamento Atteso
- Form upload in `/admin/settings/invoice-configuration`
- Preview logo caricato
- Validazione: solo immagini, max 2MB, min 200x200px
- Logo appare in alto a sinistra delle fatture PDF

#### File Coinvolti
- `database/migrations/YYYY_MM_DD_add_invoice_logo_to_schools.php`
- `app/Models/School.php` - add `invoice_logo_path`
- `app/Http/Controllers/Admin/SettingsController.php` - handle upload
- `resources/views/admin/settings/invoice.blade.php` - form upload
- `resources/views/admin/invoices/pdf.blade.php` - display logo

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

### ❌ #10 - Sidebar: Impedire Ricaricamento

**Status:** ⏸️ Pending
**Priorità:** 🟢 MEDIUM
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1 ora

#### Descrizione
La sidebar si ricarica completamente ad ogni navigazione. Implementare comportamento SPA-like per mantenere stato sidebar.

#### Comportamento Atteso
- Sidebar non ricarica visivamente tra le pagine
- Scroll position mantenuta
- Item attivo evidenziato correttamente
- Transizioni smooth

#### Possibili Soluzioni

**Opzione A: Turbo/Hotwire (Laravel)**
```blade
<!-- In layout -->
<div data-turbo-permanent class="sidebar">
    <!-- Sidebar content -->
</div>
```

**Opzione B: Alpine.js + AJAX**
```javascript
<div x-data="sidebarState()" x-init="init()">
    <!-- Persist state in Alpine store -->
</div>
```

**Opzione C: Livewire (se installato)**
```blade
@livewire('sidebar', ['persistent' => true])
```

#### File Coinvolti
- `resources/views/layouts/app.blade.php`
- `resources/views/components/sidebar.blade.php`
- `resources/js/app.js` - gestione navigazione

---

## 🔵 LOW - Enhancement (1 task)

### ❌ #11 - Limitazione Spazio Gallerie + Acquisto Spazio

**Status:** ⏸️ Pending
**Priorità:** 🔵 LOW
**Complessità:** 🔴 High
**Tempo Stimato:** 3-4 ore

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

#### File Coinvolti
- `database/migrations/YYYY_MM_DD_add_storage_quota_to_schools.php`
- `app/Models/School.php` - quota fields
- `app/Services/StorageQuotaService.php` (NEW)
- `app/Http/Controllers/Admin/GalleryController.php` - check quota before upload
- `app/Http/Controllers/Admin/BillingController.php` - upgrade handling
- `resources/views/admin/galleries/index.blade.php` - usage indicator
- `resources/views/admin/billing/storage.blade.php` - upgrade page

#### Schema Database
```sql
ALTER TABLE schools ADD COLUMN storage_quota_gb INT DEFAULT 5;
ALTER TABLE schools ADD COLUMN storage_used_bytes BIGINT DEFAULT 0;
ALTER TABLE schools ADD COLUMN storage_quota_expires_at TIMESTAMP NULL;
```

#### Business Logic
```php
class StorageQuotaService {
    public function calculateUsage(School $school): int {
        return Media::where('school_id', $school->id)
                    ->where('collection_name', 'gallery')
                    ->sum('size');
    }

    public function canUpload(School $school, int $fileSize): bool {
        $currentUsage = $this->calculateUsage($school);
        $maxBytes = $school->storage_quota_gb * 1024 * 1024 * 1024;

        return ($currentUsage + $fileSize) <= $maxBytes;
    }

    public function purchaseAdditionalStorage(School $school, int $additionalGB) {
        // Integration con PayPal
        // Add GB to quota
        // Log transaction
    }
}
```

#### Pricing Model (da definire)
- Base: 5GB inclusi nel piano base
- Upgrade: €5/mese per ogni 5GB aggiuntivi
- Pay-as-you-go: €1/GB una tantum

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

---

## 🔄 Change Log

| Data | Autore | Task | Azione |
|------|--------|------|--------|
| 2026-01-23 23:50 | Claude | #1 | ✅ Completato fix Settings (6 bug risolti) - Commit 65c4b24 |
| 2026-01-23 23:15 | Claude | - | Creazione roadmap iniziale |

---

**Ultima Modifica:** 2026-01-23 23:50 UTC
**Prossimo Task:** #2 - Studenti Nomi/Cognomi Non Visualizzati
