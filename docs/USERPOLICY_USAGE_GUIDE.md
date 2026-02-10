# UserPolicy - Guida all'Utilizzo

**Data creazione:** 2026-02-10
**Versione:** 1.0.0
**Status:** ✅ PRODUCTION READY

---

## Panoramica

La `UserPolicy` è stata implementata per centralizzare la logica di autorizzazione delle operazioni sugli studenti (User model), seguendo le best practices Laravel.

**Approccio CONSERVATIVO - Defense in Depth:**
- ✅ Middleware `SchoolOwnership` rimane attivo (primo layer)
- ✅ Metodi `verifyResourceOwnership()` nei controller rimangono attivi
- ✅ Policy aggiunge un layer aggiuntivo di autorizzazione
- ✅ NON sostituisce i check esistenti, li complementa

---

## File Creati/Modificati

### 1. `/app/Policies/UserPolicy.php` (NUOVO)
Policy completa per autorizzazione operazioni su User/Student.

**Metodi implementati:**
- `viewAny(User $user)` - Visualizzare lista studenti
- `view(User $user, User $student)` - Visualizzare singolo studente
- `create(User $user)` - Creare nuovo studente
- `update(User $user, User $student)` - Aggiornare studente
- `delete(User $user, User $student)` - Eliminare studente (soft-delete)
- `restore(User $user, User $student)` - Ripristinare studente eliminato
- `forceDelete(User $user, User $student)` - Eliminazione permanente (solo Super Admin)

### 2. `/app/Providers/AppServiceProvider.php` (MODIFICATO)
Registrata la policy nel provider.

```php
protected $policies = [
    User::class => UserPolicy::class,
];
```

---

## Regole di Autorizzazione Multi-Tenant

### Super Admin
- ✅ Accesso completo a tutte le operazioni
- ✅ Può gestire studenti di tutte le scuole
- ✅ Unico ruolo con permesso di `forceDelete`

### Admin Scuola
- ✅ Può vedere lista studenti (`viewAny`)
- ✅ Può creare studenti nella propria scuola
- ✅ Può visualizzare/modificare/eliminare studenti della propria scuola
- ❌ NON può accedere a studenti di altre scuole
- ❌ NON può fare `forceDelete` (solo soft-delete)

### Student
- ✅ Può visualizzare il proprio profilo (`view` - solo se $student->id === $user->id)
- ✅ Può aggiornare il proprio profilo (`update` - partial updates)
- ❌ NON può vedere lista altri studenti
- ❌ NON può creare altri studenti
- ❌ NON può eliminare il proprio account

---

## Come Usare la Policy nei Controller

### Esempio 1: Check Autorizzazione con `authorize()`

```php
// AdminStudentController.php

public function show(User $student)
{
    // Metodo 1: authorize() - throw 403 se fallisce
    $this->authorize('view', $student);

    // Se arriviamo qui, l'utente è autorizzato
    return view('admin.students.show', compact('student'));
}

public function update(Request $request, User $student)
{
    $this->authorize('update', $student);

    $validated = $request->validate([...]);
    $student->update($validated);

    return redirect()->route('admin.students.show', $student);
}
```

### Esempio 2: Check Condizionale con `Gate::allows()`

```php
use Illuminate\Support\Facades\Gate;

public function index()
{
    // Metodo 2: Gate::allows() - ritorna bool, NON throw exception
    if (!Gate::allows('viewAny', User::class)) {
        return redirect()->route('dashboard')
            ->with('error', 'Non hai i permessi per visualizzare gli studenti.');
    }

    $students = User::where('role', 'student')
        ->where('school_id', auth()->user()->school_id)
        ->paginate(15);

    return view('admin.students.index', compact('students'));
}
```

### Esempio 3: Check nelle Blade Views

```blade
{{-- Solo se può creare studenti --}}
@can('create', App\Models\User::class)
    <a href="{{ route('admin.students.create') }}" class="btn btn-primary">
        Nuovo Studente
    </a>
@endcan

{{-- Solo se può modificare questo specifico studente --}}
@can('update', $student)
    <a href="{{ route('admin.students.edit', $student) }}" class="btn btn-secondary">
        Modifica
    </a>
@endcan

{{-- Solo se può eliminare questo studente --}}
@can('delete', $student)
    <form method="POST" action="{{ route('admin.students.destroy', $student) }}">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Elimina</button>
    </form>
@endcan
```

### Esempio 4: Check in API Controllers

```php
// Api/StudentController.php

public function update(Request $request, User $student)
{
    // Check autorizzazione
    if (!Gate::allows('update', $student)) {
        return response()->json([
            'success' => false,
            'message' => 'Non hai i permessi per modificare questo studente.',
            'error' => 'Forbidden'
        ], 403);
    }

    $validated = $request->validate([...]);
    $student->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Studente aggiornato con successo.',
        'data' => $student
    ]);
}
```

---

## Defense in Depth - Layers di Sicurezza

**IMPORTANTE:** La Policy NON sostituisce i check esistenti. Stack completo di sicurezza:

### Layer 1: Middleware `SchoolOwnership`
```php
// routes/web.php
Route::middleware(['auth', 'school.ownership'])->group(function () {
    Route::resource('admin/students', AdminStudentController::class);
});
```
Verifica che l'utente appartenga alla scuola corretta prima di raggiungere il controller.

### Layer 2: Manual Check in Controller
```php
public function update(Request $request, User $student)
{
    // Existing manual check
    $this->verifyResourceOwnership($student, 'Studente');

    // ... rest of method
}
```
Verifica ownership prima di processare la richiesta.

### Layer 3: Policy Authorization (NUOVO)
```php
public function update(Request $request, User $student)
{
    // NEW: Policy check (opzionale, add alongside existing checks)
    $this->authorize('update', $student);

    // Existing manual check (keep this for now)
    $this->verifyResourceOwnership($student, 'Studente');

    // ... rest of method
}
```
Layer aggiuntivo, centralizzato, riutilizzabile.

### Layer 4: Database Constraints
```sql
-- Foreign keys con ON DELETE CASCADE
-- Unique indexes scoped per school_id
```

---

## Migrazione Graduale del Codice Esistente

**Approccio raccomandato:**

### Fase 1: Nuovo Codice (IMMEDIATE)
- ✅ Usa Policy in tutti i nuovi controller/metodi
- ✅ Usa `@can` directive in tutte le nuove Blade views

### Fase 2: Codice Esistente (INCREMENTALE)
- ⏳ Aggiungi Policy checks ACCANTO a quelli esistenti
- ⏳ NON rimuovere check manuali per ora
- ⏳ Testa approfonditamente

### Fase 3: Cleanup (FUTURO)
- 🔄 Dopo 2-3 mesi di testing, considera di rimuovere check manuali duplicati
- 🔄 Mantieni comunque middleware `SchoolOwnership` (sempre)

---

## Testing della Policy

### Test Manuale

```bash
# SSH su local/VPS
php artisan tinker

# Test Super Admin
$superAdmin = User::where('role', 'super_admin')->first();
$student = User::where('role', 'student')->first();
Gate::forUser($superAdmin)->allows('delete', $student); // true

# Test Admin - stesso school
$admin = User::where('role', 'admin')->first();
$studentSameSchool = User::where('role', 'student')->where('school_id', $admin->school_id)->first();
Gate::forUser($admin)->allows('update', $studentSameSchool); // true

# Test Admin - diverso school
$studentOtherSchool = User::where('role', 'student')->where('school_id', '!=', $admin->school_id)->first();
Gate::forUser($admin)->allows('update', $studentOtherSchool); // false

# Test Student - proprio profilo
$student = User::where('role', 'student')->first();
Gate::forUser($student)->allows('view', $student); // true

# Test Student - profilo altrui
$otherStudent = User::where('role', 'student')->where('id', '!=', $student->id)->first();
Gate::forUser($student)->allows('view', $otherStudent); // false
```

### Test Automatici (TODO)

```php
// tests/Feature/Policies/UserPolicyTest.php

public function test_admin_can_update_student_in_same_school()
{
    $admin = User::factory()->admin()->create(['school_id' => 1]);
    $student = User::factory()->student()->create(['school_id' => 1]);

    $this->actingAs($admin);

    $this->assertTrue(Gate::allows('update', $student));
}

public function test_admin_cannot_update_student_in_different_school()
{
    $admin = User::factory()->admin()->create(['school_id' => 1]);
    $student = User::factory()->student()->create(['school_id' => 2]);

    $this->actingAs($admin);

    $this->assertFalse(Gate::allows('update', $student));
}
```

---

## Best Practices

### ✅ DO

1. **Usa `authorize()` per operazioni critiche:**
   ```php
   $this->authorize('delete', $student); // Throw 403 se fallisce
   ```

2. **Usa `Gate::allows()` per check condizionali:**
   ```php
   if (Gate::allows('update', $student)) {
       // Show edit button
   }
   ```

3. **Usa `@can` directive nelle view:**
   ```blade
   @can('delete', $student)
       <button>Delete</button>
   @endcan
   ```

4. **Testa sempre in locale prima di deploy:**
   ```bash
   # Verifica che policy funzioni per tutti i ruoli
   php artisan tinker
   ```

### ❌ DON'T

1. **NON rimuovere middleware esistenti:**
   ```php
   // ❌ WRONG
   Route::get('/students/{student}', ...); // No middleware!

   // ✅ CORRECT
   Route::middleware(['auth', 'school.ownership'])->get('/students/{student}', ...);
   ```

2. **NON fare solo check in Policy senza middleware:**
   Defense in depth = multipli layer di sicurezza.

3. **NON modificare Policy senza test approfonditi:**
   Impatta autorizzazioni di TUTTI gli studenti.

---

## Troubleshooting

### Policy non funziona / sempre returns false

**Check 1:** Policy registrata?
```bash
php artisan route:list | grep students
# Verifica che route esistano

php artisan tinker
> app(\Illuminate\Contracts\Auth\Access\Gate::class)->policies();
# Verifica che User::class => UserPolicy::class sia presente
```

**Check 2:** Utente autenticato?
```php
// In controller
dd(auth()->check(), auth()->user());
```

**Check 3:** Parametri corretti?
```php
// Per model-specific (view, update, delete)
$this->authorize('update', $student); // Passa $student

// Per class-level (viewAny, create)
$this->authorize('viewAny', User::class); // Passa User::class
```

### 403 Forbidden improvvisi

**Causa probabile:** Policy aggiunta ma logica errata.

**Debug:**
```php
// Aggiungi log in Policy
public function update(User $user, User $student): bool
{
    \Log::info('UserPolicy::update check', [
        'user_id' => $user->id,
        'user_role' => $user->role,
        'user_school_id' => $user->school_id,
        'student_id' => $student->id,
        'student_school_id' => $student->school_id,
        'result' => $this->belongsToSameSchool($user, $student)
    ]);

    // ... existing logic
}
```

Poi verifica i log:
```bash
tail -f storage/logs/laravel.log
```

---

## Prossimi Step (Opzionale)

1. ✅ **Implementare CoursePolicy** per autorizzazione operazioni su corsi
2. ✅ **Implementare EnrollmentPolicy** per iscrizioni
3. ✅ **Implementare PaymentPolicy** per pagamenti
4. ✅ **Scrivere test automatici** (Feature tests per tutte le policy)
5. ✅ **Migrare controller esistenti** (gradualmente)

---

## References

- Laravel 12 Authorization: https://laravel.com/docs/12.x/authorization
- Multi-Tenant Architecture: `/docs/MULTI_TENANT_GUIDE.md` (TODO)
- Security Best Practices: `/docs/security/SECURITY_AUDIT_REPORT_2025-11-22.md`

---

**Maintainer:** Emanuele Rosato
**Last updated:** 2026-02-10
**Version:** 1.0.0
