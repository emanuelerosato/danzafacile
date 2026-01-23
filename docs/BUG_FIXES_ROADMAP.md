# 🐛 Bug Fixes & Improvements Roadmap

**Progetto:** DanzaFacile - Laravel 12 Dance School Management System
**Data Creazione:** 2026-01-23
**Ultima Modifica:** 2026-01-23
**Status:** 0/11 completati (0%)

---

## 📊 Overview

| Priorità | Totale | Completati | In Progress | Pending |
|----------|--------|------------|-------------|---------|
| 🔴 CRITICAL | 3 | 0 | 0 | 3 |
| 🟡 HIGH | 3 | 0 | 0 | 3 |
| 🟢 MEDIUM | 4 | 0 | 0 | 4 |
| 🔵 LOW | 1 | 0 | 0 | 1 |
| **TOTALE** | **11** | **0** | **0** | **11** |

**Tempo Stimato Totale:** 15-20 ore di sviluppo

---

## 🔴 CRITICAL - Production Blocker (3 task)

### ❌ #1 - Settings Non Salva Dati + Errore Email

**Status:** ⏸️ Pending
**Priorità:** 🔴 CRITICAL
**Complessità:** 🟢 Low
**Tempo Stimato:** 30-45 minuti

#### Descrizione
La pagina `/admin/settings` non salva i dati delle impostazioni della scuola e restituisce sempre un errore di validazione sull'email.

#### Comportamento Atteso
- Form salva correttamente tutti i campi
- Email validation funziona correttamente
- Success message dopo salvataggio
- Dati persistiti nel database

#### File Coinvolti
- `routes/web.php` - route `admin.settings.*`
- `app/Http/Controllers/Admin/SettingsController.php` (da verificare se esiste)
- `resources/views/admin/settings/index.blade.php`
- `resources/views/admin/settings/edit.blade.php`
- `app/Models/SchoolSetting.php` (da verificare struttura)

#### Indagine Necessaria
1. Verificare esistenza controller e route
2. Controllare validation rules
3. Verificare schema database `school_settings` table
4. Testare form submission con browser DevTools
5. Controllare Laravel logs per errori specifici

#### Note Tecniche
- Probabilmente validation rule troppo restrittiva su email
- Possibile mismatch tra nomi campi form e database
- Verificare CSRF token

---

### ❌ #2 - Studenti Nomi/Cognomi Non Visualizzati

**Status:** ⏸️ Pending
**Priorità:** 🔴 CRITICAL
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1-1.5 ore

#### Descrizione
Nella pagina `/admin/students/150/edit` alcuni studenti non mostrano nome e cognome correttamente.

#### Comportamento Atteso
- Tutti gli studenti mostrano nome e cognome completi
- Nessun campo vuoto o null
- Dati caricati correttamente da database

#### File Coinvolti
- `app/Http/Controllers/Admin/StudentController.php` - metodo `edit()`
- `app/Models/Student.php` - relationships e accessors
- `resources/views/admin/students/edit.blade.php`
- Database: `students` table

#### Indagine Necessaria
1. Query database per verificare dati raw studente ID 150
2. Controllare eager loading nel controller (`->with()`)
3. Verificare accessors/mutators in Student model
4. Testare con più student_id per capire pattern
5. Verificare se è problema N+1 query o dati mancanti

#### Possibili Cause
- **N+1 Query Problem:** Relazioni non eager loaded
- **Data Corruption:** Alcuni studenti hanno campi NULL
- **Multi-tenant Scope Issue:** Query filtra erroneamente studenti

#### Note Tecniche
```php
// Verificare nel controller:
$student = Student::with(['school', 'user', 'enrollments'])->find($id);

// Verificare nel model:
protected $appends = ['full_name'];
public function getFullNameAttribute() {
    return $this->first_name . ' ' . $this->last_name;
}
```

---

### ❌ #3 - Eventi: Foto Non Caricate + Non Visualizzati

**Status:** ⏸️ Pending
**Priorità:** 🔴 CRITICAL
**Complessità:** 🟡 Medium
**Tempo Stimato:** 1.5-2 ore

#### Descrizione
La pagina `/admin/events/create` ha problemi:
1. Le foto caricate non vengono salvate
2. Gli eventi creati non vengono visualizzati nella lista

#### Comportamento Atteso
- Upload foto funziona correttamente
- Foto salvate in `storage/app/public/events/`
- Eventi visualizzati immediatamente dopo creazione
- Foto accessibili tramite symlink

#### File Coinvolti
- `app/Http/Controllers/Admin/EventController.php`
- `app/Models/Event.php`
- `resources/views/admin/events/create.blade.php`
- `resources/views/admin/events/index.blade.php`
- `config/filesystems.php`

#### Indagine Necessaria
1. Verificare storage symlink: `php artisan storage:link`
2. Controllare permissions su `storage/app/public/`
3. Verificare validation rules per file upload
4. Controllare query scope nella index (filtri attivi?)
5. Verificare salvataggio path foto nel database

#### Possibili Cause
**Foto non caricate:**
- Storage symlink mancante
- Permission denied su directory storage
- Validation fallisce silenziosamente
- Path salvato errato in database

**Eventi non visualizzati:**
- Query scope filtra eventi appena creati
- Cache problema
- Multi-tenant scope esclude eventi nuovi
- Redirect errato dopo store()

#### Note Tecniche
```php
// Nel controller EventController@store:
if ($request->hasFile('photo')) {
    $path = $request->file('photo')->store('events', 'public');
    $event->photo_path = $path;
}

// Verificare nella index query:
Event::where('school_id', auth()->user()->school_id)
     ->orderBy('event_date', 'desc')
     ->paginate(20);
```

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
| 2026-01-23 | Claude | - | Creazione roadmap iniziale |

---

**Ultima Modifica:** 2026-01-23 23:45 UTC
**Prossimo Task:** #1 - Settings Non Salva Dati
