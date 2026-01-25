# 🏢 Multi-Tenant Architecture Guide

**Versione:** 1.0.0
**Ultima modifica:** 2026-01-25
**Autore:** DanzaFacile Development Team
**Status:** ✅ Production Ready

---

## 📋 Table of Contents

1. [Overview](#-overview)
2. [Architecture](#-architecture)
3. [Implementation Details](#-implementation-details)
4. [Usage Patterns](#-usage-patterns)
5. [Best Practices](#-best-practices)
6. [Migration Checklist](#-migration-checklist)
7. [Testing Multi-Tenant Isolation](#-testing-multi-tenant-isolation)
8. [Common Pitfalls](#-common-pitfalls)
9. [Troubleshooting](#-troubleshooting)

---

## 🎯 Overview

DanzaFacile implementa un'architettura **multi-tenant** basata su **school-based data isolation**. Ogni scuola di danza opera in un ambiente completamente isolato, garantendo:

- ✅ **Data Security**: Nessuna scuola può accedere ai dati di un'altra
- ✅ **Automatic Filtering**: Global scopes applicano filtri automaticamente
- ✅ **Developer Safety**: Pattern standard riducono errori di isolation
- ✅ **Scalability**: Supporto per centinaia di scuole sullo stesso database

### Ruoli e Tenant Scope

| Ruolo | Tenant Scope | Accesso Dati |
|-------|--------------|--------------|
| **Super Admin** | Global | Tutte le scuole |
| **Admin** | School | Solo propria scuola |
| **Studente** | School | Solo propria scuola |

---

## 🏗️ Architecture

### Data Isolation Model

```
┌─────────────────────────────────────────────────────────┐
│                     DATABASE LAYER                       │
├─────────────────────────────────────────────────────────┤
│  Table: users                                            │
│  ├─ id, name, email, school_id                          │
│  └─ school_id = 1 → School A                            │
│                                                          │
│  Table: courses                                          │
│  ├─ id, name, school_id                                 │
│  └─ school_id = 1 → School A                            │
│                                                          │
│  Table: payments                                         │
│  ├─ id, amount, user_id, school_id                      │
│  └─ school_id = 1 → School A                            │
└─────────────────────────────────────────────────────────┘
                         ▲
                         │ Automatic Filtering
                         │
┌─────────────────────────────────────────────────────────┐
│                    MIDDLEWARE LAYER                      │
├─────────────────────────────────────────────────────────┤
│  SchoolScopeMiddleware                                   │
│  └─ Sets session('current_school_id')                   │
│  └─ Binds app('current_school_id')                      │
└─────────────────────────────────────────────────────────┘
                         ▲
                         │
┌─────────────────────────────────────────────────────────┐
│                     MODEL LAYER                          │
├─────────────────────────────────────────────────────────┤
│  HasSchoolScope Trait                                    │
│  └─ Global Scope: WHERE school_id = current_school_id   │
│                                                          │
│  Models using trait:                                     │
│  ├─ Payment                                              │
│  ├─ Course                                               │
│  ├─ Event                                                │
│  ├─ MediaItem                                            │
│  ├─ Staff                                                │
│  ├─ Document                                             │
│  └─ ... (10+ models)                                     │
└─────────────────────────────────────────────────────────┘
```

---

## 🔧 Implementation Details

### 1. HasSchoolScope Trait

**File:** `app/Models/Traits/HasSchoolScope.php`

Il trait principale che gestisce l'isolation multi-tenant:

```php
<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Builder;

trait HasSchoolScope
{
    /**
     * Boot del trait - aggiunge global scope automatico
     */
    protected static function bootHasSchoolScope(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            // Legge school_id dal container o session
            $currentSchoolId = app()->bound('current_school_id')
                ? app('current_school_id')
                : session('current_school_id');

            if ($currentSchoolId) {
                $builder->where($builder->getModel()->getTable() . '.school_id', $currentSchoolId);
            }
        });
    }

    /**
     * Scope per filtrare manualmente una scuola specifica
     */
    public function scopeForSchool(Builder $query, int $schoolId): Builder
    {
        return $query->where($this->getTable() . '.school_id', $schoolId);
    }

    /**
     * Scope per DISABILITARE filtro multi-tenant (admin use only)
     */
    public function scopeWithoutSchoolScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('school');
    }

    /**
     * Helper per ottenere school_id corrente
     */
    public static function getCurrentSchoolId(): ?int
    {
        return app()->bound('current_school_id')
            ? app('current_school_id')
            : session('current_school_id');
    }

    /**
     * Crea un nuovo record per la scuola corrente
     */
    public static function createForCurrentSchool(array $attributes = []): static
    {
        $schoolId = static::getCurrentSchoolId();

        if ($schoolId && !isset($attributes['school_id'])) {
            $attributes['school_id'] = $schoolId;
        }

        return static::create($attributes);
    }
}
```

### 2. SchoolScopeMiddleware

**File:** `app/Http/Middleware/SchoolScopeMiddleware.php`

Middleware che imposta il contesto della scuola corrente:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SchoolScopeMiddleware
{
    /**
     * Imposta il context della scuola per le query
     */
    public function handle(Request $request, Closure $next): mixed
    {
        // Solo per utenti autenticati
        if (Auth::check()) {
            $user = Auth::user();

            // Solo per utenti con school_id (esclusi super_admin)
            if ($user->school_id && $user->role !== 'super_admin') {
                // Imposta school_id nella session
                session(['current_school_id' => $user->school_id]);

                // Binding nel service container
                app()->instance('current_school_id', $user->school_id);
            }
        }

        return $next($request);
    }
}
```

### 3. Models Implementazione

#### Esempio: Payment Model

**File:** `app/Models/Payment.php` (linee 792-798)

```php
protected static function booted(): void
{
    // Global scope per multi-tenant security
    static::addGlobalScope('school', function (Builder $builder) {
        if (auth()->check() && auth()->user()->school_id) {
            $builder->where('school_id', auth()->user()->school_id);
        }
    });
}
```

#### Modelli con Multi-Tenant Scope (Attivi)

| Model | File | Scope Type |
|-------|------|------------|
| Payment | `app/Models/Payment.php` | Direct global scope |
| MediaItem | `app/Models/MediaItem.php` | Direct global scope |
| Staff | `app/Models/Staff.php` | Direct global scope |
| StaffRole | `app/Models/StaffRole.php` | Direct global scope |
| Event | `app/Models/Event.php` | Direct global scope |
| EventRegistration | `app/Models/EventRegistration.php` | Direct global scope |
| Document | `app/Models/Document.php` | Direct global scope |
| Attendance | `app/Models/Attendance.php` | Direct global scope |
| StaffSchedule | `app/Models/StaffSchedule.php` | Direct global scope |

#### Modelli con Scope Disabilitato (Temporaneamente)

| Model | File | Motivo |
|-------|------|--------|
| Course | `app/Models/Course.php` | Problemi ricorsione - da riattivare |
| User | `app/Models/User.php` | Gestione manuale school_id |

### 4. Modelli Globali (NO Multi-Tenant)

Alcuni modelli NON hanno scope multi-tenant perché sono globali:

| Model | Motivo |
|-------|--------|
| School | Root entity (definisce il tenant) |
| StorageQuotaAuditLog | Super admin logging (cross-school) |
| FcmToken | Notification system (cross-school) |

---

## 💡 Usage Patterns

### Pattern 1: Automatic Filtering (Recommended)

**Quando usare:** 99% dei casi - lascia che il global scope filtri automaticamente

```php
// ✅ CORRETTO - Global scope applica automaticamente filtro
$courses = Course::all(); // WHERE school_id = current_school_id

// ✅ CORRETTO - Anche con query complesse
$payments = Payment::where('status', 'completed')
    ->where('amount', '>', 100)
    ->get(); // WHERE school_id = current_school_id AND status = 'completed' ...
```

### Pattern 2: Explicit School Filtering

**Quando usare:** Quando serve specificare una scuola diversa (admin only)

```php
// ✅ CORRETTO - Filtraggio esplicito per scuola specifica
$school = School::find(5);
$courses = Course::forSchool($school->id)->get();

// ✅ CORRETTO - Con relazioni
$students = User::where('role', 'student')
    ->where('school_id', $school->id)
    ->get();
```

### Pattern 3: Bypass Multi-Tenant (Super Admin Only)

**Quando usare:** Super Admin che deve vedere dati di tutte le scuole

```php
// ✅ CORRETTO - Super Admin bypass scope
if (auth()->user()->role === 'super_admin') {
    $allPayments = Payment::withoutSchoolScope()->get();

    // Con filtri aggiuntivi
    $allCourses = Course::withoutSchoolScope()
        ->where('active', true)
        ->get();
}

// ❌ SBAGLIATO - Bypass senza verifica ruolo
$payments = Payment::withoutSchoolScope()->get(); // SECURITY RISK!
```

### Pattern 4: Creating Records

**Quando usare:** Creazione nuovi record (automatic school_id)

```php
// ✅ CORRETTO - school_id aggiunto automaticamente dal middleware/trait
$payment = Payment::create([
    'user_id' => $user->id,
    'amount' => 100,
    'status' => Payment::STATUS_PENDING,
    // school_id NON serve - aggiunto automaticamente
]);

// ✅ ALTERNATIVA - Esplicito (defensive programming)
$payment = Payment::create([
    'user_id' => $user->id,
    'school_id' => auth()->user()->school_id, // Esplicito
    'amount' => 100,
    'status' => Payment::STATUS_PENDING,
]);

// ✅ USANDO TRAIT HELPER
$payment = Payment::createForCurrentSchool([
    'user_id' => $user->id,
    'amount' => 100,
]);
```

### Pattern 5: Service Layer

**Quando usare:** Business logic isolata con dependency injection

```php
// ✅ CORRETTO - Service riceve School come parametro
class StorageQuotaService
{
    public function getUsage(School $school): int
    {
        // Filtra esplicitamente usando school_id
        $totalBytes = MediaItem::whereHas('mediaGallery', function ($query) use ($school) {
            $query->where('school_id', $school->id);
        })->sum('file_size');

        return (int) ($totalBytes ?? 0);
    }
}

// Uso nel controller
$storageService = app(StorageQuotaService::class);
$usage = $storageService->getUsage(auth()->user()->school);
```

---

## ✅ Best Practices

### 1. SEMPRE Includere school_id nelle Migration

```php
// ✅ CORRETTO
Schema::create('new_table', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->timestamps();

    // Index per performance multi-tenant queries
    $table->index(['school_id', 'created_at']);
});

// ❌ SBAGLIATO - Manca school_id
Schema::create('new_table', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Dove va school_id???
    $table->timestamps();
});
```

### 2. SEMPRE Aggiungere Global Scope ai Model

```php
// ✅ CORRETTO - Model con global scope
class NewModel extends Model
{
    use HasFactory;

    protected $fillable = ['school_id', 'name', 'description'];

    protected static function booted(): void
    {
        static::addGlobalScope('school', function (Builder $builder) {
            if (auth()->check() && auth()->user()->school_id) {
                $builder->where('school_id', auth()->user()->school_id);
            }
        });
    }
}

// ❌ SBAGLIATO - Model senza scope
class NewModel extends Model
{
    protected $fillable = ['school_id', 'name'];
    // Manca il global scope - SECURITY RISK!
}
```

### 3. SEMPRE Testare con Multiple Schools

```php
// ✅ CORRETTO - Test con 2+ scuole
public function test_school_isolation(): void
{
    $school1 = School::factory()->create();
    $school2 = School::factory()->create();

    $admin1 = User::factory()->create(['school_id' => $school1->id, 'role' => 'admin']);
    $admin2 = User::factory()->create(['school_id' => $school2->id, 'role' => 'admin']);

    // Login admin school 1
    $this->actingAs($admin1);
    $courses1 = Course::all();

    // Login admin school 2
    $this->actingAs($admin2);
    $courses2 = Course::all();

    // Verifica isolation
    $this->assertNotEquals($courses1->pluck('id'), $courses2->pluck('id'));
}
```

### 4. SEMPRE Validare school_id in Update

```php
// ✅ CORRETTO - Verifica ownership prima di update
public function update(Request $request, Course $course)
{
    // Laravel policy verifica automaticamente school_id
    $this->authorize('update', $course);

    $course->update($request->validated());
    return redirect()->back()->with('success', 'Corso aggiornato');
}

// ❌ SBAGLIATO - Nessuna verifica ownership
public function update(Request $request, $courseId)
{
    $course = Course::withoutSchoolScope()->find($courseId); // BYPASS SCOPE!
    $course->update($request->all()); // Può modificare corsi di altre scuole!
    return redirect()->back();
}
```

### 5. SEMPRE Filtrare Relazioni Cross-School

```php
// ✅ CORRETTO - Filtrare relazioni
public function course(): BelongsTo
{
    return $this->belongsTo(Course::class)
        ->where('school_id', $this->school_id); // Explicit filter
}

// ✅ ALTERNATIVA - Usando global scope (automatico)
public function course(): BelongsTo
{
    return $this->belongsTo(Course::class);
    // Global scope applica automaticamente WHERE school_id
}
```

---

## 📝 Migration Checklist

Quando crei una **nuova feature** che richiede nuove tabelle/modelli:

### Database Migration

- [ ] Aggiunto campo `school_id` nella migration
- [ ] Aggiunto foreign key constraint: `->foreignId('school_id')->constrained()->onDelete('cascade')`
- [ ] Aggiunto index composito: `->index(['school_id', 'created_at'])`
- [ ] Testato rollback migration

### Model Setup

- [ ] Aggiunto `'school_id'` in `$fillable` array
- [ ] Aggiunto global scope nel metodo `booted()`
- [ ] Aggiunto scope helper `scopeForSchool()` e `scopeWithoutSchoolScope()`
- [ ] Verificato relazioni con altri modelli multi-tenant

### Controller Logic

- [ ] Usato `auth()->user()->school_id` per ottenere contesto
- [ ] Verificato authorization con Policy (es: `$this->authorize('view', $model)`)
- [ ] Testato CRUD operations con utenti di scuole diverse
- [ ] Verificato nessun leak di dati tra scuole

### Service Layer (se applicabile)

- [ ] Service riceve `School` come parametro (dependency injection)
- [ ] Query esplicite usano `where('school_id', $school->id)`
- [ ] Nessun uso di `withoutSchoolScope()` senza autorizzazione
- [ ] Logging include `school_id` per audit trail

### Testing

- [ ] Test con almeno 2 scuole diverse
- [ ] Test isolation: Admin School A non vede dati School B
- [ ] Test Super Admin può vedere dati di tutte le scuole
- [ ] Test create/update/delete con ownership verification
- [ ] Test performance con 100+ records per scuola

### Documentation

- [ ] Aggiornato `SERVICES_MAP.md` se creato nuovo service
- [ ] Aggiornato `ARCHITECTURE.md` se pattern architetturale nuovo
- [ ] Commentato codice complesso con riferimenti multi-tenant

---

## 🧪 Testing Multi-Tenant Isolation

### Test Case 1: Query Isolation

```php
/** @test */
public function admin_can_only_see_own_school_courses()
{
    $school1 = School::factory()->create(['name' => 'School A']);
    $school2 = School::factory()->create(['name' => 'School B']);

    $admin1 = User::factory()->create(['school_id' => $school1->id, 'role' => 'admin']);
    $admin2 = User::factory()->create(['school_id' => $school2->id, 'role' => 'admin']);

    Course::factory()->create(['school_id' => $school1->id, 'name' => 'Corso A']);
    Course::factory()->create(['school_id' => $school2->id, 'name' => 'Corso B']);

    // Login admin school 1
    $this->actingAs($admin1);
    $courses = Course::all();

    $this->assertCount(1, $courses);
    $this->assertEquals('Corso A', $courses->first()->name);
}
```

### Test Case 2: Create Isolation

```php
/** @test */
public function new_records_are_created_with_correct_school_id()
{
    $school = School::factory()->create();
    $admin = User::factory()->create(['school_id' => $school->id, 'role' => 'admin']);

    $this->actingAs($admin);

    $payment = Payment::create([
        'user_id' => $admin->id,
        'amount' => 100,
        'status' => Payment::STATUS_PENDING,
        // school_id NON specificato - deve essere auto-filled
    ]);

    $this->assertEquals($school->id, $payment->school_id);
}
```

### Test Case 3: Cross-School Access Prevention

```php
/** @test */
public function admin_cannot_update_other_school_records()
{
    $school1 = School::factory()->create();
    $school2 = School::factory()->create();

    $admin1 = User::factory()->create(['school_id' => $school1->id, 'role' => 'admin']);
    $course2 = Course::factory()->create(['school_id' => $school2->id]);

    $this->actingAs($admin1);

    // Tentativo di update corso di school2 fallisce
    $response = $this->put(route('admin.courses.update', $course2), [
        'name' => 'Hacked Course',
    ]);

    $response->assertForbidden(); // Policy blocca
}
```

---

## ⚠️ Common Pitfalls

### Pitfall 1: Dimenticare school_id in Migration

```php
// ❌ SBAGLIATO
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained();
    $table->string('filename');
    $table->timestamps();
    // Manca school_id!
});

// ✅ CORRETTO
Schema::create('documents', function (Blueprint $table) {
    $table->id();
    $table->foreignId('school_id')->constrained()->onDelete('cascade');
    $table->foreignId('user_id')->constrained();
    $table->string('filename');
    $table->timestamps();

    $table->index(['school_id', 'created_at']);
});
```

### Pitfall 2: Usare withoutSchoolScope() Senza Autorizzazione

```php
// ❌ SBAGLIATO - Bypass scope senza check
public function index()
{
    $allCourses = Course::withoutSchoolScope()->get(); // TUTTI i corsi!
    return view('courses.index', compact('allCourses'));
}

// ✅ CORRETTO - Verifica ruolo prima di bypass
public function index()
{
    if (auth()->user()->role === 'super_admin') {
        $courses = Course::withoutSchoolScope()->get();
    } else {
        $courses = Course::all(); // Global scope applica filtro
    }

    return view('courses.index', compact('courses'));
}
```

### Pitfall 3: Query Raw SQL Senza Filtro

```php
// ❌ SBAGLIATO - Raw query bypassa global scope
$results = DB::select('SELECT * FROM courses WHERE active = 1');
// Restituisce corsi di TUTTE le scuole!

// ✅ CORRETTO - Filtraggio esplicito
$schoolId = auth()->user()->school_id;
$results = DB::select(
    'SELECT * FROM courses WHERE active = 1 AND school_id = ?',
    [$schoolId]
);

// ✅ MIGLIORE - Usa Eloquent
$results = Course::where('active', true)->get();
// Global scope applica automaticamente school_id
```

### Pitfall 4: Relazioni Senza Filtro

```php
// ❌ SBAGLIATO - Relazione senza school_id filter
public function payments()
{
    return $this->hasMany(Payment::class);
    // Può restituire pagamenti di altre scuole se user_id coincide!
}

// ✅ CORRETTO - Filtraggio esplicito
public function payments()
{
    return $this->hasMany(Payment::class)
        ->where('school_id', $this->school_id);
}

// ✅ MIGLIORE - Global scope fa il lavoro
public function payments()
{
    return $this->hasMany(Payment::class);
    // Payment model ha global scope, filtra automaticamente
}
```

---

## 🔍 Troubleshooting

### Problema: "Vedo dati di altre scuole"

**Diagnosi:**
```php
// Debug query per vedere SQL generato
DB::enableQueryLog();
$courses = Course::all();
dd(DB::getQueryLog());

// Verifica se global scope è attivo
dd(Course::getGlobalScopes());
```

**Soluzioni:**
1. Verifica che il model abbia global scope nel metodo `booted()`
2. Controlla che middleware `SchoolScopeMiddleware` sia attivo
3. Verifica che `auth()->user()->school_id` sia popolato

### Problema: "Global scope causa errori in seeder/factory"

**Diagnosi:**
Global scope cerca `auth()->user()` ma in seeder/factory non c'è utente autenticato.

**Soluzione:**
```php
// ✅ In seeder/factory, disabilita scope temporaneamente
Course::withoutSchoolScope()->create([
    'school_id' => 1,
    'name' => 'Test Course',
]);

// ✅ OPPURE usa factory con school
Course::factory()->create(['school_id' => $school->id]);
```

### Problema: "Performance lente con molti record"

**Diagnosi:**
Query multi-tenant senza index.

**Soluzione:**
```php
// Aggiungi index compositi in migration
$table->index(['school_id', 'created_at']);
$table->index(['school_id', 'status']);

// Verifica EXPLAIN
DB::listen(function ($query) {
    dump($query->sql, $query->bindings, $query->time);
});
```

---

## 📚 References

### Files da Studiare

| File | Descrizione |
|------|-------------|
| `app/Models/Traits/HasSchoolScope.php` | Trait multi-tenant principale |
| `app/Http/Middleware/SchoolScopeMiddleware.php` | Middleware context setup |
| `app/Models/Payment.php` | Esempio model con global scope |
| `app/Services/StorageQuotaService.php` | Esempio service layer con school parameter |

### Related Documentation

- [ARCHITECTURE.md](ARCHITECTURE.md) - Decisioni architetturali
- [SERVICES_MAP.md](SERVICES_MAP.md) - Mappa servizi multi-tenant
- [Laravel Global Scopes](https://laravel.com/docs/11.x/eloquent#global-scopes) - Documentazione ufficiale

---

**Versione:** 1.0.0
**Ultimo aggiornamento:** 2026-01-25
**Maintainer:** DanzaFacile Development Team
