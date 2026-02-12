# 🔍 AUDIT COMPLETO: `/admin/events/create`

**Data Audit:** 2026-02-12
**Pagina Analizzata:** https://www.danzafacile.it/admin/events/create
**Team:** 4 Senior Experts (Laravel Backend Dev, Frontend Specialist, Code Reviewer, QA Tester)

---

## 📊 EXECUTIVE SUMMARY

### Stato Generale
- **Security:** ✅ PASSED (Multi-tenant isolation eccellente)
- **Functionality:** ⚠️ ISSUES FOUND (8 bugs backend, 17 bugs frontend)
- **Code Quality:** ⚠️ NEEDS IMPROVEMENT (Duplication, missing Form Requests)
- **Test Coverage:** ❌ MISSING (0% coverage su Event creation)

### Metriche Bug
| Severity | Backend | Frontend | Total |
|----------|---------|----------|-------|
| 🔴 **CRITICAL** | 5 | 2 | **7** |
| 🟡 **HIGH** | 3 | 4 | **7** |
| 🟢 **MEDIUM** | 8 | 6 | **14** |
| 🔵 **LOW** | 9 | 5 | **14** |
| **TOTAL** | **25** | **17** | **42** |

### Raccomandazione
**Status:** ⚠️ **DEPLOY BLOCCATO** fino a fix di 7 bug CRITICAL

---

## 🚨 BUG CRITICI (P0 - FIX IMMEDIATO)

### 1. **BACKEND: Discrepanza Cast 'price' vs 'price_students'**
**File:** `app/Models/Event.php` line 51
**Severity:** 🔴 CRITICAL

**Problema:**
```php
// Model Event.php - line 51
protected $casts = [
    'price' => 'decimal:2',  // ❌ Colonna NON esiste più nel DB!
    'price_students' => 'decimal:2',
    'price_guests' => 'decimal:2',
];
```

La migration `2025_11_30_210801_add_dual_pricing_to_events_table.php` ha rinominato `price` → `price_students`, ma il Model ancora casta `price`.

**Impatto:**
- Laravel tenta di castare una colonna inesistente
- `$event->price` ritorna sempre `null`
- Potenziali errori silenti in produzione

**Fix:**
```php
protected $casts = [
    // REMOVE: 'price' => 'decimal:2',
    'price_students' => 'decimal:2',
    'price_guests' => 'decimal:2',
];
```

**Effort:** 1 minuto
**Risk:** NONE (fix sicuro)

---

### 2. **BACKEND: Field Name Mismatch in Validation**
**File:** `app/Http/Controllers/Admin/AdminEventController.php` lines 86, 115, 217, 242
**Severity:** 🔴 CRITICAL

**Problema:**
```php
// store() method - line 86
'price' => 'nullable|numeric|min:0',  // ❌ Valida 'price' ma NON esiste più

// Line 115
$validated['price'] = $validated['price'] ?? 0.00;  // ❌ Imposta campo inesistente
```

**Impatto CRITICO:**
- Il valore `price` inserito dall'utente viene VALIDATO ma MAI SALVATO
- `price_students` nel DB rimane sempre `0.00` (default)
- **TUTTI gli eventi creati sono GRATUITI** anche se l'admin inserisce un prezzo
- Bug SILENZIOSO → nessun errore mostrato all'utente

**Fix:**
```php
// Cambio validation key
'price_students' => 'nullable|numeric|min:0|max:999999.99',
'price_guests' => 'nullable|numeric|min:0|max:999999.99',

// Line 115
$validated['price_students'] = $validated['price_students'] ?? 0.00;
$validated['price_guests'] = $validated['price_guests'] ?? 0.00;
```

**Effort:** 5 minuti
**Risk:** NONE (mappatura diretta)

---

### 3. **BACKEND: Mass Assignment Vulnerability su school_id**
**File:** `app/Models/Event.php` line 16
**Severity:** 🔴 CRITICAL SECURITY

**Problema:**
```php
protected $fillable = [
    'school_id',  // ❌ SECURITY RISK: Attacker può modificare school_id!
    'name',
    // ...
];
```

**Attack Vector:**
```bash
POST /admin/events/store
{
  "name": "Evento Fake",
  "school_id": 999,  # ❌ Scuola di qualcun altro!
  "type": "workshop"
}
```

**Impatto SECURITY:**
- Attacker può creare eventi in scuole NON sue
- **VIOLA multi-tenant isolation** a livello mass assignment
- Anche se controller imposta `$validated['school_id'] = $this->school->id`, il fillable permette override

**Fix:**
```php
protected $fillable = [
    // REMOVE: 'school_id',  // ❌ SECURITY RISK
    'name',
    'description',
    // ... altri campi ...
];

// In controller store():
$event = new Event($validated);
$event->school_id = $this->school->id;  // Set manualmente DOPO fillable
$event->save();
```

**Effort:** 10 minuti
**Risk:** LOW (il global scope già protegge, ma defense-in-depth)

---

### 4. **BACKEND: NULL Pointer Exception Risk su $this->school**
**File:** `app/Http/Controllers/Admin/AdminEventController.php` lines 21, 111, 153, 184
**Severity:** 🔴 CRITICAL

**Problema:**
```php
// store() method - line 111
$validated['school_id'] = $this->school->id;  // ❌ E se $this->school è NULL?
```

**Scenario:**
- Se middleware fallisce e `$this->school` è `null`
- **Fatal Error:** `Trying to get property 'id' of null`
- L'applicazione crasherebbe completamente

**Fix:**
```php
// All'inizio di ogni metodo che usa $this->school
if (!$this->school) {
    abort(403, 'Nessuna scuola associata al tuo account.');
}
```

**Effort:** 15 minuti (aggiungere a tutti i metodi)
**Risk:** NONE (defensive programming)

---

### 5. **BACKEND: Missing DB Transaction**
**File:** `app/Http/Controllers/Admin/AdminEventController.php` lines 76-145
**Severity:** 🔴 CRITICAL

**Problema:**
```php
public function store(Request $request)
{
    // File upload (line 118-131) - potrebbe fallire
    if ($request->hasFile('image')) {
        $result = FileUploadHelper::uploadFile(...);
        if ($result['success']) {
            $validated['image_path'] = $result['path'];
        }
    }

    $event = Event::create($validated);  // ❌ NO transaction!
    $this->clearSchoolCache();
}
```

**Scenario di fallimento:**
1. File upload SUCCESS → file salvato su disk
2. `Event::create()` FAILS (DB error) → exception
3. **File rimane su disk ma evento NON creato** → file orfano
4. **Cache già cleared ma niente in DB** → inconsistenza

**Fix:**
```php
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

public function store(Request $request)
{
    try {
        DB::beginTransaction();

        // Validation...

        if ($request->hasFile('image')) {
            // Upload file DENTRO transaction
            $result = FileUploadHelper::uploadFile(...);
            if (!$result['success']) {
                throw new \Exception(implode(' ', $result['errors']));
            }
            $validated['image_path'] = $result['path'];
        }

        $event = Event::create($validated);
        $this->clearSchoolCache();

        DB::commit();

        return redirect()->route('admin.events.show', $event)
            ->with('success', 'Evento creato con successo.');

    } catch (\Exception $e) {
        DB::rollBack();

        // Cleanup uploaded file if exists
        if (isset($validated['image_path'])) {
            Storage::disk('public')->delete($validated['image_path']);
        }

        Log::error('Event creation failed', [
            'error' => $e->getMessage(),
            'school_id' => $this->school->id ?? null,
            'user_id' => auth()->id()
        ]);

        return back()
            ->withErrors(['error' => 'Errore durante la creazione dell\'evento.'])
            ->withInput();
    }
}
```

**Effort:** 30 minuti
**Risk:** LOW (transazioni standard)

---

### 6. **FRONTEND: CSP Violation - Inline Event Handler**
**File:** `resources/views/admin/events/create.blade.php` line 231
**Severity:** 🔴 CRITICAL

**Problema:**
```blade
<input type="file" ... onchange="previewImage(event)">  <!-- ❌ CSP violation -->
```

**Impatto CRITICO:**
- Content Security Policy blocca inline event handlers
- **Preview immagine NON funziona** in produzione
- Console error: `Refused to execute inline event handler`

**Fix:**
```blade
<!-- Rimuovere inline handler -->
<input type="file" id="image" name="image"
       accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
       class="..."
       @change="previewImage($event)">  <!-- Alpine.js binding -->

<!-- Nella sezione script con nonce -->
<script nonce="@cspNonce">
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('imagePreviewImg');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
        previewImg.src = '';
    }
}
</script>
```

**Effort:** 5 minuti
**Risk:** NONE (solo spostamento handler)

---

### 7. **FRONTEND: Design System Violation - Glassmorphism**
**File:** `resources/views/admin/events/create.blade.php` line 43
**Severity:** 🔴 CRITICAL (per CLAUDE.md compliance)

**Problema:**
```blade
<div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
```

**Violazione CLAUDE.md:**
```markdown
❌ MAI
1. NO Glassmorphism (`backdrop-blur`, `bg-white/80`)
```

**Fix:**
```blade
<div class="bg-white rounded-lg shadow">
```

**Effort:** 2 minuti
**Risk:** NONE (solo CSS)

---

## 🟡 BUG ALTA PRIORITÀ (P1 - THIS WEEK)

### 8. **BACKEND: Missing Validation for Fillable Fields**
**File:** `AdminEventController.php` lines 78-109
**Severity:** 🟡 HIGH

**Problema:**
Questi campi sono fillable nel Model ma NON validati nel controller:
- `short_description`
- `landing_description`
- `landing_cta_text`
- `qr_checkin_enabled`
- `payment_method`
- `requires_payment`
- `additional_info`
- `slug`

**Impatto:**
- Attacker potrebbe iniettare dati non validati via mass assignment
- Nessun controllo su lunghezza, tipo, formato
- Potenziale XSS, data corruption

**Fix:**
```php
// Aggiungere validation rules:
'short_description' => 'nullable|string|max:500',
'landing_description' => 'nullable|string|max:2000',
'landing_cta_text' => 'nullable|string|max:100',
'qr_checkin_enabled' => 'boolean',
'payment_method' => 'nullable|in:cash,card,bank_transfer,paypal',
'requires_payment' => 'boolean',
'additional_info' => 'nullable|string|max:1000',
'slug' => 'nullable|string|max:255|unique:events,slug',
```

**Effort:** 10 minuti

---

### 9. **BACKEND: Incorrect Date Validation Logic**
**File:** `AdminEventController.php` lines 88, 219
**Severity:** 🟡 HIGH

**Problema:**
```php
'registration_deadline' => 'nullable|date|before:start_date',
```

**Business Logic Error:**
- Nessuna validazione che deadline sia nel FUTURO
- Potrebbe essere nel passato e passa la validation!

**Scenario:**
```
start_date = 2025-03-15 18:00
registration_deadline = 2024-01-01 00:00  ✅ PASSA (before start_date)
```

**Fix:**
```php
'registration_deadline' => 'nullable|date|after_or_equal:today|before:start_date',
```

**Effort:** 2 minuti

---

### 10. **BACKEND: N+1 Query in CSV Export**
**File:** `AdminEventController.php` lines 484-500
**Severity:** 🟡 HIGH

**Problema:**
```php
$data = $events->map(function ($event) {
    return [
        $event->registrations()->active()->count(),  // ❌ N+1 query!
    ];
});
```

**Impatto:**
- Se 100 eventi → **101 queries** (1 + 100)
- Export CSV lento/timeout con molti eventi

**Fix:**
```php
$events = $events->withCount(['registrations as active_registrations_count' => function($q) {
    $q->active();
}]);

$data = $events->map(function ($event) {
    return [
        $event->active_registrations_count,  // ✅ No query!
    ];
});
```

**Effort:** 5 minuti

---

### 11. **FRONTEND: Alpine.js Expression Error**
**File:** `create.blade.php` line 169
**Severity:** 🟡 HIGH

**Problema:**
```blade
<div x-data="{ requiresRegistration: {{ old('requires_registration', 'true') === 'true' ? 'true' : 'false' }} }">
```

**Issue:**
- `old('requires_registration')` ritorna `"1"` (string) quando checkbox checked
- Comparazione con `'true'` fallisce
- Dopo validation error, checkbox si resetta in modo errato

**Fix:**
```blade
<div x-data="{ requiresRegistration: {{ old('requires_registration', 1) ? 'true' : 'false' }} }">
```

**Effort:** 2 minuti

---

### 12. **FRONTEND: Missing Client-Side Date Validation**
**File:** `create.blade.php` form
**Severity:** 🟡 HIGH

**Problema:**
Nessuna validation client-side per:
- `end_date >= start_date`
- `registration_deadline < start_date`
- `start_date >= today`

**Impatto:**
- Submit inutili al server
- Bad UX (errore solo dopo submit)

**Fix:**
```javascript
<script nonce="@cspNonce">
function eventFormValidation() {
    return {
        validateDates(event) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;

            if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                event.preventDefault();
                alert('La data di fine deve essere successiva alla data di inizio.');
                return false;
            }

            // ... altre validations
            return true;
        }
    }
}
</script>

<form @submit="validateDates($event)" ...>
```

**Effort:** 30 minuti

---

### 13. **FRONTEND: Checkbox Value Inconsistency**
**File:** `create.blade.php` lines 291-306
**Severity:** 🟡 HIGH

**Problema:**
- Checkbox `value="1"` nel DOM
- Alpine.js `requiresRegistration` è boolean
- Controller si aspetta boolean
- Discrepanza tra HTML/Alpine/Backend

**Fix:**
```blade
<div x-data="{ requiresRegistration: {{ old('requires_registration', true) ? 'true' : 'false' }} }">
    <input type="checkbox"
           id="requires_registration"
           name="requires_registration"
           value="1"
           :checked="requiresRegistration"
           @change="requiresRegistration = $event.target.checked">
</div>
```

**Effort:** 10 minuti

---

### 14. **FRONTEND: Missing Loading State on Submit**
**File:** `create.blade.php` form
**Severity:** 🟡 HIGH

**Problema:**
- Nessun loading state durante submit
- Possibili submit duplicati con doppio click

**Fix:**
```blade
<form x-data="{ submitting: false }" @submit="submitting = true" ...>
    <button type="submit"
            :disabled="submitting"
            :class="{ 'opacity-50 cursor-not-allowed': submitting }">
        <span x-show="!submitting">Crea Evento</span>
        <span x-show="submitting">Creazione in corso...</span>
    </button>
</form>
```

**Effort:** 10 minuti

---

## 🟢 BUG MEDIA PRIORITÀ (P2 - NEXT SPRINT)

### 15-28. Altri Bug (14 totali)
- Inconsistent default values
- Missing validation boolean checkboxes
- Weak file validation (duplicata)
- Missing index su registration_deadline
- Race condition slug generation
- Inconsistent error handling
- Security: missing MIME validation image preview
- Cache stampede risk
- Missing Form Request classes
- Magic numbers hardcoded
- Missing PHPDoc documentation
- Inconsistent naming conventions
- Missing `maxlength` attributes
- Missing file size client validation
- Duplicate page title
- Missing gradient background
- Missing ARIA attributes
- Focus management requirements
- Responsive header overflow

*Dettagli completi nel report esteso degli agenti*

---

## 🔐 SECURITY AUDIT RESULT

### ✅ PASSED
1. **Multi-tenant Isolation:** ✅ EXCELLENT (global scope + middleware + manual checks)
2. **CSRF Protection:** ✅ PRESENT
3. **XSS Prevention:** ✅ BLADE AUTO-ESCAPE
4. **SQL Injection:** ✅ ELOQUENT ORM
5. **File Upload:** ✅ FILEUPLOADHELPER VALIDATION

### ⚠️ WARNINGS
6. **Mass Assignment:** ⚠️ school_id in fillable (BUG #3)
7. **Rate Limiting:** ⚠️ Missing throttle on POST /events
8. **Authorization Policy:** ⚠️ No EventPolicy (manual checks only)

### ❌ FAILED
Nessuno.

---

## 📋 ROADMAP DI FIX

### 🔴 **SPRINT 0 (IMMEDIATE - Before Next Deploy)**
**Blockers da fixare OGGI:**

| # | Bug | File | Effort | Risk |
|---|-----|------|--------|------|
| 1 | Cast 'price' inesistente | Event.php:51 | 1 min | NONE |
| 2 | Validation 'price' mismatch | AdminEventController.php:86,115 | 5 min | NONE |
| 3 | Mass assignment school_id | Event.php:16 | 10 min | LOW |
| 4 | NULL check $this->school | AdminEventController.php (tutti) | 15 min | NONE |
| 5 | DB Transaction | AdminEventController.php:76-145 | 30 min | LOW |
| 6 | CSP violation onchange | create.blade.php:231 | 5 min | NONE |
| 7 | Glassmorphism removal | create.blade.php:43 | 2 min | NONE |

**TOTAL EFFORT:** ~70 minuti (1.5 ore)

**Action Items:**
1. ✅ Creare branch `fix/events-create-critical-bugs`
2. ✅ Applicare fix #1-7 in ordine
3. ✅ Test manuale pagina `/admin/events/create`
4. ✅ Test creazione evento con prezzo > 0
5. ✅ Test upload immagine (verificare CSP OK)
6. ✅ Deploy su production
7. ✅ Smoke test production

---

### 🟡 **SPRINT 1 (THIS WEEK)**
**High priority bugs:**

| # | Bug | File | Effort |
|---|-----|------|--------|
| 8 | Missing validation fillable | AdminEventController.php:78 | 10 min |
| 9 | Date validation logic | AdminEventController.php:88 | 2 min |
| 10 | N+1 query CSV export | AdminEventController.php:484 | 5 min |
| 11 | Alpine.js expression error | create.blade.php:169 | 2 min |
| 12 | Client-side date validation | create.blade.php | 30 min |
| 13 | Checkbox inconsistency | create.blade.php:291 | 10 min |
| 14 | Missing loading state | create.blade.php | 10 min |

**TOTAL EFFORT:** ~70 minuti (1.5 ore)

---

### 🟢 **SPRINT 2 (NEXT 2 WEEKS)**
**Refactoring & Best Practices:**

1. **Creare Form Request Classes** (30 min)
   - `StoreEventRequest.php`
   - `UpdateEventRequest.php`

2. **Creare EventPolicy** (45 min)
   - Centralizzare authorization logic
   - Usare `$this->authorize('create', Event::class)`

3. **Estrarre Event Type Enum** (20 min)
   - `app/Enums/EventType.php`
   - Eliminare magic strings

4. **Creare EventImageService** (1 ora)
   - Gestione upload immagini
   - Eliminare duplicazione store/update

5. **Aggiungere Rate Limiting** (15 min)
   - `throttle:10,1` su POST /events

6. **Fix tutti i bug MEDIUM** (3 ore)

**TOTAL EFFORT:** ~6 ore

---

### 🔵 **BACKLOG**
**Testing & Documentation:**

1. **Creare Test Suite** (8 ore)
   - `tests/Unit/Models/EventTest.php`
   - `tests/Feature/Admin/EventControllerTest.php`
   - Target: 80% code coverage

2. **Documentazione API** (2 ore)
   - PHPDoc su tutti i metodi
   - Swagger/OpenAPI specs

3. **Performance Optimization** (4 ore)
   - Cache locking (stampede prevention)
   - Database indexes
   - Query optimization

**TOTAL EFFORT:** ~14 ore

---

## 📈 TESTING PLAN

### Test Coverage Attuale
- ❌ **0%** - Nessun test per Event creation

### Test Coverage Target
- ✅ **80%** - Sprint 2

### Test Suite Proposta

#### Unit Tests (`tests/Unit/Models/EventTest.php`)
```php
- it_auto_sets_school_id_on_creation()
- it_generates_unique_slug_from_name()
- it_filters_events_by_school_for_non_super_admin()
- it_casts_dates_correctly()
- it_casts_prices_as_decimals()
```

#### Feature Tests (`tests/Feature/Admin/EventControllerTest.php`)
```php
- admin_can_create_event_for_their_school()
- admin_cannot_create_event_with_past_start_date()
- admin_cannot_view_events_from_other_schools()
- event_requires_name_type_and_dates()
- end_date_must_be_after_start_date()
- registration_deadline_must_be_before_start_date()
- image_upload_validates_file_type_and_size()
- price_is_saved_correctly_in_database()  // ← Test BUG #2
```

---

## 📁 FILES TO MODIFY

### Backend (3 files)
1. `/app/Models/Event.php`
   - Line 51: Remove cast 'price'
   - Line 16: Remove 'school_id' from fillable

2. `/app/Http/Controllers/Admin/AdminEventController.php`
   - Lines 86, 115: Change 'price' → 'price_students'
   - Lines 217, 242: Same fix in update()
   - All methods: Add null check `$this->school`
   - Lines 76-145: Wrap in DB::transaction()
   - Lines 78-109: Add validation fillable fields
   - Line 88: Fix date validation logic
   - Line 484: Fix N+1 query with withCount()

3. `/app/Http/Controllers/Admin/AdminBaseController.php`
   - Add helper method `ensureSchoolContext()`

### Frontend (1 file)
4. `/resources/views/admin/events/create.blade.php`
   - Line 231: Remove inline onchange, use @change
   - Line 43: Remove glassmorphism classes
   - Line 169: Fix Alpine.js expression
   - Add client-side date validation
   - Fix checkbox binding
   - Add loading state on submit
   - Add maxlength attributes
   - Fix missing gradient background
   - Remove duplicate title

---

## 🎯 SUCCESS CRITERIA

### SPRINT 0 (Immediate)
- ✅ Tutti i 7 bug CRITICAL fixati
- ✅ Deploy production senza errors
- ✅ Eventi creati con prezzo corretto salvato
- ✅ Preview immagine funzionante
- ✅ Design system compliance

### SPRINT 1 (This Week)
- ✅ Tutti i 7 bug HIGH fixati
- ✅ Validation completa backend + frontend
- ✅ UX migliorata (loading states, client validation)

### SPRINT 2 (Next 2 Weeks)
- ✅ Code refactored (Form Requests, Services)
- ✅ EventPolicy implementata
- ✅ Test coverage 80%
- ✅ Performance ottimizzata

---

## 📞 CONTACTS & REFERENCES

**Laravel Backend Dev Report:** [Dettagli completi analisi backend]
**Frontend Specialist Report:** [Dettagli completi analisi frontend]
**Code Reviewer Report:** [Dettagli completi code review]
**QA Tester Report:** [Dettagli completi testing scenarios]

**Generated:** 2026-02-12
**Version:** 1.0
**Status:** ⚠️ DEPLOY BLOCKED - Fix Critical Bugs First

---

## 🚀 QUICK START FIX GUIDE

```bash
# 1. Crea branch fix
git checkout -b fix/events-create-critical-bugs

# 2. Fix Model Event.php
# Rimuovi line 51: 'price' => 'decimal:2',
# Rimuovi 'school_id' dal fillable

# 3. Fix Controller AdminEventController.php
# Sostituisci 'price' con 'price_students' in validation
# Aggiungi null checks
# Aggiungi DB::transaction()

# 4. Fix View create.blade.php
# Rimuovi onchange inline
# Fix glassmorphism
# Fix Alpine.js expressions

# 5. Test locale
php artisan migrate:fresh --seed
npm run dev
./vendor/bin/sail up -d

# Testa: http://localhost:8089/admin/events/create

# 6. Commit & Push
git add .
git commit -m "🐛 FIX: 7 critical bugs in events/create

- Remove deprecated 'price' cast from Event model
- Fix validation field name mismatch (price → price_students)
- Remove school_id from fillable (security)
- Add null checks for \$this->school
- Add DB transaction to store()
- Fix CSP violation (inline onchange)
- Remove glassmorphism (design system compliance)

🤖 Generated with Claude Code
Co-Authored-By: Claude <noreply@anthropic.com>"

git push origin fix/events-create-critical-bugs

# 7. Deploy production
ssh root@157.230.114.252
cd /var/www/danzafacile
git pull origin fix/events-create-critical-bugs
php artisan migrate
php artisan optimize
systemctl restart php8.4-fpm

# 8. Smoke test
curl -I https://www.danzafacile.it/admin/events/create
```

---

**END OF REPORT**
