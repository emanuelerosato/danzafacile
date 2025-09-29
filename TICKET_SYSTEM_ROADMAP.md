# 🎫 TICKET SYSTEM - ROADMAP & AUDIT

**Progetto:** Scuola di Danza Management System
**Data Audit:** 29 Settembre 2025
**Status:** Admin Ticket System - **MANCANTE** 🔴

---

## 📋 AUDIT COMPLETO SISTEMA TICKET

### ✅ COSA ESISTE GIÀ

| Componente | Status | Path | Note |
|------------|--------|------|------|
| **Model Ticket** | ✅ Completo | `/app/Models/Ticket.php` | 142 righe, scopes, attributes, relationships |
| **Model TicketResponse** | ✅ Completo | `/app/Models/TicketResponse.php` | Relationship con Ticket e User |
| **Migration Tickets** | ✅ Completo | `database/migrations/2025_09_12_210748_create_tickets_table.php` | Campi: title, description, status, priority, category, user_id, assigned_to, closed_at |
| **Migration Responses** | ✅ Completo | `database/migrations/2025_09_12_210817_create_ticket_responses_table.php` | Campi: ticket_id, user_id, message |
| **Controller Student** | ✅ Completo | `/app/Http/Controllers/Student/TicketController.php` | 205 righe - CRUD completo |
| **Controller SuperAdmin** | ✅ Completo | `/app/Http/Controllers/SuperAdmin/HelpdeskController.php` | Gestione helpdesk completa |
| **Views Student** | ✅ Complete | `resources/views/student/tickets/` | index.blade.php, create.blade.php, show.blade.php, partials/ |
| **Views SuperAdmin** | ✅ Complete | `resources/views/super-admin/helpdesk/` | index.blade.php, show.blade.php |
| **Rotte Student** | ✅ 7 rotte | `routes/web.php` | index, create, store, show, reply, stats, recent |
| **Rotte SuperAdmin** | ✅ 6 rotte | `routes/web.php` | show, update, destroy, close, reopen, reply |
| **Sidebar Link Student** | ✅ Presente | `resources/views/components/sidebar.blade.php:165-167` | Con icona "chat" |
| **Sidebar Link SuperAdmin** | ✅ Presente | `resources/views/components/sidebar.blade.php:43-51` | Con badge unread tickets |

---

### ❌ COSA MANCA (CRITICO)

| Componente | Status | Impatto | Priorità |
|------------|--------|---------|----------|
| **Controller Admin** | ❌ **ASSENTE** | 🔴 Admin non può gestire ticket | P0 - CRITICO |
| **Views Admin** | ❌ **ASSENTI** | 🔴 Nessuna UI per admin | P0 - CRITICO |
| **Rotte Admin** | ❌ **ASSENTI** | 🔴 Nessun endpoint | P0 - CRITICO |
| **Link Sidebar Admin** | ❌ **ASSENTE** | 🔴 Nessun accesso visibile | P0 - CRITICO |
| **Stats Dashboard Admin** | ❌ **ASSENTI** | 🟡 Mancanza visibilità | P1 - ALTA |
| **Widget Dashboard Admin** | ❌ **ASSENTE** | 🟡 Mancanza monitoring | P1 - ALTA |
| **Alpine.js Components** | ❌ **ASSENTI** | 🟢 UX migliorabile | P2 - MEDIA |
| **Notifiche Email** | ❌ **ASSENTI** | 🟢 Comunicazione asincrona | P3 - BASSA |

---

## 🎯 TABELLA DI MARCIA SVILUPPO

### **FASE 1: Admin Ticket System (CRITICO)** 🔴

**Tempo stimato:** 3-4 ore
**Priorità:** P0 - BLOCCA OPERATIVITÀ ADMIN

#### 1.1 Controller Admin (`AdminTicketController.php`)

**Path:** `/app/Http/Controllers/Admin/AdminTicketController.php`

**Metodi richiesti:**
```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminTicketController extends Controller
{
    /**
     * Display a listing of school's tickets
     * Filter by: status, priority, category, date_range, search
     * Pagination: 15 per page
     * Stats: total, open, pending, closed, high_priority
     */
    public function index(Request $request)
    {
        // TODO: Implementare
        // - Filtri avanzati
        // - Stats cards
        // - Paginazione
        // - Scope by school
    }

    /**
     * Display the specified ticket
     * Load: responses, user, assignedTo
     * Security: Check school ownership
     */
    public function show(Ticket $ticket)
    {
        // TODO: Implementare
        // - Check authorization (school)
        // - Load relationships
        // - Timeline risposte
    }

    /**
     * Update ticket (status, priority, assigned_to)
     * Allow: status, priority, assigned_to
     */
    public function update(Request $request, Ticket $ticket)
    {
        // TODO: Implementare
        // - Validation
        // - Update fields
        // - Log activity
    }

    /**
     * Add admin reply to ticket
     * Auto-update status to 'pending'
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // TODO: Implementare
        // - Validation message
        // - Create response
        // - Update ticket status
        // - Send notification (future)
    }

    /**
     * Close ticket
     * Set status='closed', closed_at=now()
     */
    public function close(Ticket $ticket)
    {
        // TODO: Implementare
        // - Check if already closed
        // - Update status and closed_at
    }

    /**
     * Reopen closed ticket
     * Set status='pending', closed_at=null
     */
    public function reopen(Ticket $ticket)
    {
        // TODO: Implementare
        // - Check if closed
        // - Update status
    }

    /**
     * Assign ticket to staff member
     * Update assigned_to field
     */
    public function assign(Request $request, Ticket $ticket)
    {
        // TODO: Implementare
        // - Validation user_id
        // - Check user is staff
        // - Update assigned_to
    }

    /**
     * Bulk actions on multiple tickets
     * Actions: close, reopen, delete, assign
     */
    public function bulkActions(Request $request)
    {
        // TODO: Implementare
        // - Validation action + ticket_ids
        // - Loop tickets
        // - Apply action
    }

    /**
     * Get ticket statistics for dashboard
     * Return: total, open, pending, closed, high_priority, avg_response_time
     */
    public function getStats()
    {
        // TODO: Implementare
        // - Query stats by school
        // - Calculate metrics
        // - Return JSON
    }

    /**
     * Get recent tickets for dashboard widget
     * Limit: 5 tickets
     */
    public function getRecent(int $limit = 5)
    {
        // TODO: Implementare
        // - Query recent by school
        // - Load relationships
        // - Return JSON
    }
}
```

#### 1.2 Routes Admin

**Path:** `routes/web.php` - Sezione Admin

```php
// Admin Ticket Management
Route::prefix('admin/tickets')->name('admin.tickets.')->middleware(['auth', 'role:admin'])->group(function() {
    Route::get('/', [AdminTicketController::class, 'index'])->name('index');
    Route::get('/stats', [AdminTicketController::class, 'getStats'])->name('stats');
    Route::get('/recent', [AdminTicketController::class, 'getRecent'])->name('recent');
    Route::get('/{ticket}', [AdminTicketController::class, 'show'])->name('show');
    Route::patch('/{ticket}', [AdminTicketController::class, 'update'])->name('update');
    Route::post('/{ticket}/reply', [AdminTicketController::class, 'reply'])->name('reply');
    Route::patch('/{ticket}/close', [AdminTicketController::class, 'close'])->name('close');
    Route::patch('/{ticket}/reopen', [AdminTicketController::class, 'reopen'])->name('reopen');
    Route::patch('/{ticket}/assign', [AdminTicketController::class, 'assign'])->name('assign');
    Route::post('/bulk-action', [AdminTicketController::class, 'bulkActions'])->name('bulk-action');
});
```

#### 1.3 Views Admin

##### **View: index.blade.php**

**Path:** `resources/views/admin/tickets/index.blade.php`

**Struttura:**
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2>Gestione Ticket</h2>
                <p>Gestisci le richieste di supporto degli studenti</p>
            </div>
            <div>
                <!-- Actions: Refresh, Export -->
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <!-- Dashboard > Ticket -->
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-stats-card title="Totale Ticket" :value="$stats['total']" />
                    <x-stats-card title="Aperti" :value="$stats['open']" color="green" />
                    <x-stats-card title="In Attesa" :value="$stats['pending']" color="yellow" />
                    <x-stats-card title="Chiusi" :value="$stats['closed']" color="gray" />
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-6">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Search, Status, Priority, Category, Date Range -->
                    </form>
                </div>

                <!-- Tickets Table -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b">
                        <h3>Lista Ticket</h3>
                        <!-- Bulk Actions Button -->
                    </div>
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead>
                            <tr>
                                <th><input type="checkbox" id="selectAll"></th>
                                <th>ID</th>
                                <th>Studente</th>
                                <th>Oggetto</th>
                                <th>Categoria</th>
                                <th>Priorità</th>
                                <th>Stato</th>
                                <th>Creato</th>
                                <th>Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tickets as $ticket)
                            <tr>
                                <!-- Ticket row data -->
                                <td>
                                    <!-- Dropdown actions: View, Reply, Close, Assign -->
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Pagination -->
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
```

##### **View: show.blade.php**

**Path:** `resources/views/admin/tickets/show.blade.php`

**Struttura:**
```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2>Ticket #{{ $ticket->id }} - {{ $ticket->title }}</h2>
                <p>{{ $ticket->user->full_name }} - {{ $ticket->formatted_created_at }}</p>
            </div>
            <div class="flex space-x-3">
                <!-- Actions: Close, Reopen, Assign, Change Priority -->
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <!-- Dashboard > Ticket > #ID -->
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Main Content (2/3) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Ticket Details Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3>Dettagli Ticket</h3>
                        <div class="mt-4">
                            <p class="text-gray-900">{{ $ticket->description }}</p>
                        </div>
                    </div>

                    <!-- Responses Timeline -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3>Conversazione</h3>
                        <div class="mt-4 space-y-4">
                            @foreach($ticket->responses as $response)
                            <div class="flex items-start space-x-3">
                                <!-- Avatar -->
                                <div class="flex-1">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-medium">{{ $response->user->name }}</span>
                                            <span class="text-sm text-gray-500">{{ $response->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-gray-900">{{ $response->message }}</p>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Reply Form -->
                    @if($ticket->status !== 'closed')
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3>Rispondi al Ticket</h3>
                        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}">
                            @csrf
                            <textarea name="message" rows="4" class="w-full" required></textarea>
                            <button type="submit" class="mt-4 btn-primary">Invia Risposta</button>
                        </form>
                    </div>
                    @endif

                </div>

                <!-- Sidebar (1/3) -->
                <div class="space-y-6">

                    <!-- Ticket Info Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3>Informazioni</h3>
                        <dl class="mt-4 space-y-3">
                            <div>
                                <dt class="text-sm text-gray-500">Stato</dt>
                                <dd>
                                    <span class="badge {{ $ticket->status_color }}">
                                        {{ $ticket->status }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Priorità</dt>
                                <dd>
                                    <span class="badge {{ $ticket->priority_color }}">
                                        {{ $ticket->priority }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Categoria</dt>
                                <dd>{{ $ticket->category }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Assegnato a</dt>
                                <dd>{{ $ticket->assignedTo->name ?? 'Non assegnato' }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Quick Actions Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3>Azioni Rapide</h3>
                        <div class="mt-4 space-y-2">
                            @if($ticket->status !== 'closed')
                            <form method="POST" action="{{ route('admin.tickets.close', $ticket) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full btn-secondary">
                                    Chiudi Ticket
                                </button>
                            </form>
                            @else
                            <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="w-full btn-primary">
                                    Riapri Ticket
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>

                    <!-- Student Info Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3>Studente</h3>
                        <div class="mt-4">
                            <p class="font-medium">{{ $ticket->user->full_name }}</p>
                            <p class="text-sm text-gray-500">{{ $ticket->user->email }}</p>
                            <a href="{{ route('admin.students.show', $ticket->user) }}" class="text-rose-600 text-sm mt-2 inline-block">
                                Vedi Profilo →
                            </a>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>
```

#### 1.4 Sidebar Link Admin

**Path:** `resources/views/components/sidebar.blade.php`

**Inserire dopo la sezione "Analytics" (riga ~138):**

```blade
<x-nav-group title="Supporto" icon="support">
    <x-nav-item href="{{ route('admin.tickets.index') }}" :active="request()->routeIs('admin.tickets.*')" icon="chat">
        Ticket
        @php
            $openTickets = \App\Models\Ticket::whereHas('user', function($q) {
                $q->where('school_id', Auth::user()->school_id);
            })->whereIn('status', ['open', 'pending'])->count();
        @endphp
        @if($openTickets > 0)
        <span class="ml-auto bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $openTickets }}</span>
        @endif
    </x-nav-item>
</x-nav-group>
```

---

### **FASE 2: Dashboard Integration (ALTA)** 🟡

**Tempo stimato:** 1-2 ore
**Priorità:** P1 - VISIBILITÀ OPERAZIONI

#### 2.1 Dashboard Admin - Stats Widget

**Path:** `resources/views/admin/dashboard.blade.php`

**Aggiungere dopo Stats Cards esistenti:**

```blade
<!-- Ticket Stats Card (in grid con altre stats) -->
<x-stats-card
    title="Ticket Aperti"
    :value="$quickStats['open_tickets'] ?? 0"
    :subtitle="($quickStats['urgent_tickets'] ?? 0) . ' urgenti'"
    icon="chat"
    color="orange"
    :change="$quickStats['tickets_change'] ?? 0"
    :changeType="$quickStats['tickets_change_type'] ?? 'neutral'"
/>
```

#### 2.2 Dashboard Admin - Recent Tickets Widget

**Aggiungere sezione dedicata:**

```blade
<!-- Recent Tickets Widget -->
<div class="bg-white rounded-lg shadow">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <h3 class="text-lg font-semibold text-gray-900">Ticket Recenti</h3>
        <a href="{{ route('admin.tickets.index') }}" class="text-sm text-rose-600 hover:text-rose-700">
            Vedi tutti →
        </a>
    </div>
    <div class="p-6">
        <div class="space-y-4">
            @forelse($recentTickets ?? [] as $ticket)
            <div class="flex items-center justify-between border-b border-gray-100 pb-4">
                <div class="flex-1">
                    <a href="{{ route('admin.tickets.show', $ticket) }}" class="text-sm font-medium text-gray-900 hover:text-rose-600">
                        #{{ $ticket->id }} - {{ Str::limit($ticket->title, 50) }}
                    </a>
                    <p class="text-xs text-gray-500 mt-1">
                        {{ $ticket->user->full_name }} - {{ $ticket->time_ago }}
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <span class="badge {{ $ticket->status_color }}">{{ $ticket->status }}</span>
                    <span class="badge {{ $ticket->priority_color }}">{{ $ticket->priority }}</span>
                </div>
            </div>
            @empty
            <p class="text-sm text-gray-500 text-center py-4">Nessun ticket recente</p>
            @endforelse
        </div>
    </div>
</div>
```

#### 2.3 Controller Dashboard Update

**Path:** `app/Http/Controllers/Admin/AdminDashboardController.php`

**Aggiungere nel metodo `index()`:**

```php
// Ticket Stats
$quickStats['open_tickets'] = Ticket::whereHas('user', function($q) use ($school) {
    $q->where('school_id', $school->id);
})->whereIn('status', ['open', 'pending'])->count();

$quickStats['urgent_tickets'] = Ticket::whereHas('user', function($q) use ($school) {
    $q->where('school_id', $school->id);
})->whereIn('status', ['open', 'pending'])
  ->whereIn('priority', ['high', 'critical'])->count();

// Recent Tickets
$recentTickets = Ticket::whereHas('user', function($q) use ($school) {
    $q->where('school_id', $school->id);
})->with('user')->latest()->limit(5)->get();

return view('admin.dashboard', compact('quickStats', 'recentTickets', ...));
```

---

### **FASE 3: Refactoring & UX (MEDIA)** 🟢

**Tempo stimato:** 2-3 ore
**Priorità:** P2 - MIGLIORAMENTO UX

#### 3.1 Alpine.js Component

**Path:** `resources/js/admin/tickets/ticket-manager.js`

```javascript
window.ticketManager = function() {
    return {
        selectedTickets: [],
        showBulkModal: false,
        filters: {
            status: '',
            priority: '',
            category: '',
            search: ''
        },

        init() {
            console.log('[TicketManager] Initialized');
        },

        toggleSelection(ticketId) {
            const index = this.selectedTickets.indexOf(ticketId);
            if (index > -1) {
                this.selectedTickets.splice(index, 1);
            } else {
                this.selectedTickets.push(ticketId);
            }
        },

        selectAll() {
            const checkboxes = document.querySelectorAll('.ticket-checkbox');
            const allSelected = this.selectedTickets.length === checkboxes.length;

            if (allSelected) {
                this.selectedTickets = [];
            } else {
                this.selectedTickets = Array.from(checkboxes).map(cb => parseInt(cb.value));
            }
        },

        openBulkModal() {
            if (this.selectedTickets.length === 0) {
                alert('Seleziona almeno un ticket');
                return;
            }
            this.showBulkModal = true;
        },

        closeBulkModal() {
            this.showBulkModal = false;
        },

        async bulkAction(action) {
            try {
                const response = await fetch('/admin/tickets/bulk-action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        action: action,
                        ticket_ids: this.selectedTickets
                    })
                });

                const data = await response.json();

                if (data.success) {
                    location.reload();
                } else {
                    alert('Errore: ' + data.message);
                }
            } catch (error) {
                console.error('Bulk action error:', error);
                alert('Errore durante l\'operazione');
            }
        },

        applyFilters() {
            const params = new URLSearchParams();

            Object.entries(this.filters).forEach(([key, value]) => {
                if (value) params.append(key, value);
            });

            window.location.href = `/admin/tickets?${params.toString()}`;
        },

        resetFilters() {
            window.location.href = '/admin/tickets';
        }
    };
};
```

#### 3.2 Design System Alignment

**Checklist:**
- [ ] Stats Cards con stile standard
- [ ] Tabella responsive come payments/students
- [ ] Filtri inline con design uniforme
- [ ] Badge colori consistenti (status/priority)
- [ ] Modal style uniformi
- [ ] Transizioni Alpine.js smooth
- [ ] Loading states
- [ ] Empty states

#### 3.3 Notifiche & Email (Opzionale - P3)

**Notifiche Real-time:**
- [ ] Badge count in sidebar (già implementato)
- [ ] Sound notification (opzionale)
- [ ] Browser notification API (opzionale)

**Email Notifications:**
- [ ] Mail `TicketCreatedMail` → Admin
- [ ] Mail `TicketRepliedMail` → Student
- [ ] Mail `TicketClosedMail` → Student
- [ ] Queue jobs per invio asincrono

---

## 📊 PIANO IMPLEMENTAZIONE SPRINT

### **SPRINT 1: Core Functionality** (1 giorno)

**Giorno 1 - Mattina (4h):**
- [x] Audit completo sistema
- [x] Creazione TICKET_SYSTEM_ROADMAP.md
- [ ] Controller Admin completo (2h)
- [ ] Routes Admin (30min)

**Giorno 1 - Pomeriggio (4h):**
- [ ] View index.blade.php (2h)
- [ ] View show.blade.php (1h 30min)
- [ ] Sidebar link (30min)
- [ ] Test funzionalità base

---

### **SPRINT 2: Dashboard Integration** (Mezza giornata)

**Giorno 2 - Mattina (4h):**
- [ ] Dashboard stats widget (1h)
- [ ] Dashboard recent tickets widget (1h)
- [ ] Controller dashboard update (30min)
- [ ] Test integrazione dashboard
- [ ] Fix bugs eventuali (1h 30min)

---

### **SPRINT 3: Polish & UX** (1 giorno)

**Giorno 3 - Mattina (4h):**
- [ ] Alpine.js ticket-manager.js (2h)
- [ ] Design system alignment (1h 30min)
- [ ] Responsive testing (30min)

**Giorno 3 - Pomeriggio (4h):**
- [ ] Testing completo (2h)
- [ ] Bug fixing (1h)
- [ ] Documentazione aggiornamento (30min)
- [ ] Deploy & verifica produzione (30min)

---

## ✅ CHECKLIST COMPLETAMENTO

### Controller & Backend
- [ ] AdminTicketController creato
- [ ] Tutti i metodi implementati (index, show, update, reply, close, reopen, assign, bulkActions, getStats, getRecent)
- [ ] Routes registrate in web.php
- [ ] Middleware auth + role:admin applicato
- [ ] Policy per authorization (opzionale)

### Views Admin
- [ ] index.blade.php completo
- [ ] show.blade.php completo
- [ ] Modals (bulk actions, assign)
- [ ] Partials (filters, table row)

### Dashboard Integration
- [ ] Stats card ticket in dashboard
- [ ] Widget recent tickets
- [ ] Controller dashboard aggiornato
- [ ] Sidebar link con badge

### UX & Polish
- [ ] Alpine.js component
- [ ] Design system aligned
- [ ] Responsive design
- [ ] Loading states
- [ ] Empty states
- [ ] Transitions smooth

### Testing
- [ ] CRUD ticket funzionante
- [ ] Filtri funzionanti
- [ ] Bulk actions funzionanti
- [ ] Reply funzionante
- [ ] Close/Reopen funzionante
- [ ] Assign funzionante
- [ ] Dashboard widgets funzionanti
- [ ] Responsive mobile OK

### Documentation
- [ ] README aggiornato
- [ ] CLAUDE.md aggiornato
- [ ] guida.md aggiornato
- [ ] Commit messages dettagliati

---

## 🚀 COMANDI UTILI

### Test Database
```bash
# Verifica tabelle ticket esistenti
./vendor/bin/sail artisan db:table tickets
./vendor/bin/sail artisan db:table ticket_responses

# Conta ticket nel database
./vendor/bin/sail artisan tinker
>>> Ticket::count()
>>> Ticket::where('status', 'open')->count()
```

### Test Routes
```bash
# Lista tutte le rotte ticket
./vendor/bin/sail artisan route:list --path=ticket

# Test route admin
./vendor/bin/sail artisan route:list --path=admin/ticket
```

### Build Assets
```bash
# Build Alpine.js components
./vendor/bin/sail npm run build

# Dev mode con hot reload
./vendor/bin/sail npm run dev
```

---

## 📝 NOTE IMPLEMENTAZIONE

### Security Considerations
1. **Authorization:** Ogni admin deve vedere SOLO i ticket degli studenti della sua scuola
2. **Validation:** Sanitizzare sempre input utente (titolo, messaggio)
3. **Rate Limiting:** Limitare creazione ticket (max 10/ora per utente)
4. **XSS Prevention:** Escape output in views
5. **CSRF Protection:** Token in tutti i form

### Performance Optimization
1. **Eager Loading:** Sempre caricare user, responses, assignedTo con `with()`
2. **Pagination:** Max 15 ticket per pagina
3. **Caching:** Cache stats dashboard (5 min)
4. **Indexes:** Verificare index su `status`, `priority`, `created_at`
5. **Query Optimization:** Evitare N+1 problems

### Best Practices
1. **Code Reusability:** Componenti Blade riutilizzabili (ticket-card, status-badge)
2. **Consistent Naming:** Seguire convenzioni Laravel
3. **Error Handling:** Try-catch in controller actions critiche
4. **Logging:** Log azioni importanti (close, assign)
5. **Testing:** Scrivere feature tests per CRUD

---

## 🔗 RIFERIMENTI UTILI

### File da consultare durante sviluppo:
- **Student Controller:** `/app/Http/Controllers/Student/TicketController.php` (esempio implementazione)
- **SuperAdmin Controller:** `/app/Http/Controllers/SuperAdmin/HelpdeskController.php` (gestione helpdesk)
- **Model Ticket:** `/app/Models/Ticket.php` (scopes, attributes, relationships)
- **Views Student:** `resources/views/student/tickets/` (esempio UI)
- **Sidebar:** `resources/views/components/sidebar.blade.php` (navigation)

### Design System Reference:
- **CLAUDE.md:** Linee guida design system
- **Stats Card:** Payments, Students, Courses (esempio implementazione)
- **Tables:** Payments index (tabella responsive con actions)
- **Filters:** Payments index (filtri inline)

---

## 📊 METRICHE SUCCESSO

### KPI da monitorare dopo deploy:
- [ ] Tempo medio risposta admin < 24h
- [ ] % ticket chiusi entro 48h > 80%
- [ ] % ticket assegnati > 70%
- [ ] Soddisfazione utenti (feedback post-chiusura)
- [ ] Nessun ticket "perso" (non visto)

---

**Documento creato:** 29 Settembre 2025
**Ultima modifica:** 29 Settembre 2025
**Status:** READY TO IMPLEMENT
**Prossimo step:** SPRINT 1 - Controller + Routes + Views base