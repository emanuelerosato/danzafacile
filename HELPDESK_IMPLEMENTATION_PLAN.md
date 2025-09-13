# 📋 **PIANO OPERATIVO COMPLETO - SISTEMA HELPDESK/MESSAGGI**

## 🎯 **OBIETTIVO FINALE**
Implementare un sistema completo di Helpdesk/Messaggi per il Super Admin, con gestione ticket, risposte, allegati e interfaccia moderna.

---

## 🏗️ **FASE 1: ARCHITETTURA E DATABASE** ✅ *Completata*

### ✅ **Database Design** 
- **Tabella `tickets`**: ID, title, description, status, priority, category, user_id, assigned_to, closed_at
- **Tabella `ticket_responses`**: ID, ticket_id, user_id, message, attachments, is_internal
- **Relazioni**: User ↔ Tickets, Tickets ↔ TicketResponses
- **Indici**: Ottimizzati per performance su status, user_id, priority

### ✅ **Modelli Eloquent**
- **Ticket Model**: Relationships, scopes, accessors per UI
- **TicketResponse Model**: Gestione allegati, relazioni, helper methods

### 📝 **Schema Database Dettagliato**

```sql
-- Tabella Tickets
CREATE TABLE tickets (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    status ENUM('open', 'pending', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high', 'critical') DEFAULT 'medium',
    category VARCHAR(255) NULL,
    user_id BIGINT NOT NULL,
    assigned_to BIGINT NULL,
    closed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_status_created (status, created_at),
    INDEX idx_user_status (user_id, status),
    INDEX idx_priority (priority)
);

-- Tabella Ticket Responses
CREATE TABLE ticket_responses (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    ticket_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    message TEXT NOT NULL,
    attachments JSON NULL,
    is_internal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_ticket_created (ticket_id, created_at),
    INDEX idx_user (user_id)
);
```

---

## 🎨 **FASE 2: INTERFACCIA UTENTE** *(In Progress)*

### 📱 **UI/UX Requirements**
```
📊 Dashboard Ticket:
├── 📈 Statistiche (Aperti/In Sospeso/Chiusi/Priorità Alta)
├── 🔍 Filtri Avanzati (Status/Priorità/Data/Categoria)
├── 📋 Lista Ticket (Tabella responsive con paginazione)
└── 🎯 Quick Actions (Risposta rapida/Chiudi/Assegna)

📄 Dettaglio Ticket:
├── 📝 Header (Titolo/Status/Priorità/Info utente)
├── 💬 Timeline Conversazioni (Cronologico)
├── ✍️ Form Risposta (WYSIWYG editor)
├── 📎 Upload Allegati (Solo Super Admin)
└── 🔧 Azioni (Chiudi/Riapri/Cambia Status/Priorità)
```

### 🎨 **Design System**
- **Colori**: Match con esistente (Rose/Pink/Purple gradient)
- **Typography**: Tailwind CSS + Inter font
- **Components**: Cards, badges, forms, modals
- **Icons**: Emoji + SVG per azioni
- **Responsive**: Mobile-first approach

### 🎨 **Color Palette Helpdesk**
```css
/* Status Colors */
.status-open { @apply bg-green-100 text-green-800 border-green-200; }
.status-pending { @apply bg-yellow-100 text-yellow-800 border-yellow-200; }
.status-closed { @apply bg-gray-100 text-gray-800 border-gray-200; }

/* Priority Colors */
.priority-critical { @apply bg-red-100 text-red-800 border-red-200; }
.priority-high { @apply bg-orange-100 text-orange-800 border-orange-200; }
.priority-medium { @apply bg-yellow-100 text-yellow-800 border-yellow-200; }
.priority-low { @apply bg-green-100 text-green-800 border-green-200; }

/* Gradient Backgrounds */
.helpdesk-gradient { @apply bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50; }
.helpdesk-card { @apply bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20; }
```

---

## ⚙️ **FASE 3: BACKEND IMPLEMENTATION**

### 🚀 **Controller Architecture**
```php
HelpdeskController:
├── index()        → Lista ticket con filtri/paginazione
├── show($id)      → Dettagli ticket + risposte
├── store()        → Crea nuovo ticket
├── update($id)    → Aggiorna ticket (status/priorità)
├── destroy($id)   → Elimina ticket
├── reply($id)     → Aggiungi risposta
├── close($id)     → Chiudi ticket
└── reopen($id)    → Riapri ticket
```

### 🛣️ **Routes Planning**
```php
// Super Admin Helpdesk Routes
Route::middleware(['auth', 'role:super_admin'])->prefix('super-admin')->name('super-admin.')->group(function () {
    Route::prefix('helpdesk')->name('helpdesk.')->group(function () {
        Route::get('/', [HelpdeskController::class, 'index'])->name('index');
        Route::get('/create', [HelpdeskController::class, 'create'])->name('create');
        Route::post('/', [HelpdeskController::class, 'store'])->name('store');
        Route::get('/{ticket}', [HelpdeskController::class, 'show'])->name('show');
        Route::put('/{ticket}', [HelpdeskController::class, 'update'])->name('update');
        Route::delete('/{ticket}', [HelpdeskController::class, 'destroy'])->name('destroy');
        Route::post('/{ticket}/reply', [HelpdeskController::class, 'reply'])->name('reply');
        Route::patch('/{ticket}/close', [HelpdeskController::class, 'close'])->name('close');
        Route::patch('/{ticket}/reopen', [HelpdeskController::class, 'reopen'])->name('reopen');
        Route::get('/export/{format}', [HelpdeskController::class, 'export'])->name('export');
    });
});
```

### 🎛️ **Controller Methods Specifications**

```php
class HelpdeskController extends Controller
{
    /**
     * Display ticket list with filters and pagination
     */
    public function index(Request $request)
    {
        // Filters: status, priority, category, date_from, date_to, search
        // Pagination: 25 per page
        // Stats: total, open, pending, closed, high_priority
        // Sorting: created_at DESC default
    }

    /**
     * Show ticket details with response timeline
     */
    public function show(Ticket $ticket)
    {
        // Load: user, assignedTo, responses.user
        // Order responses: created_at ASC
        // Mark as viewed by Super Admin
    }

    /**
     * Store new ticket response with file upload
     */
    public function reply(Request $request, Ticket $ticket)
    {
        // Validation: message required, files optional
        // File upload: images only, max 5MB each, max 3 files
        // Auto-change status to 'pending' if was 'open'
        // Send notification to ticket creator
    }

    /**
     * Update ticket status and priority
     */
    public function update(Request $request, Ticket $ticket)
    {
        // Validation: status, priority, assigned_to
        // Log status changes in responses as system message
        // Auto-set closed_at when status = 'closed'
    }

    /**
     * Close ticket with optional final message
     */
    public function close(Request $request, Ticket $ticket)
    {
        // Set status = 'closed', closed_at = now()
        // Optional final message from Super Admin
        // Send closure notification to user
    }

    /**
     * Reopen closed ticket
     */
    public function reopen(Ticket $ticket)
    {
        // Set status = 'open', closed_at = null
        // Add system message about reopening
        // Send notification to user
    }
}
```

---

## 📁 **FASE 4: FILE STRUCTURE**

### 📂 **Views Organization**
```
resources/views/super-admin/helpdesk/
├── index.blade.php          → Lista ticket + filtri + stats
├── show.blade.php           → Dettaglio ticket + timeline
├── create.blade.php         → Form creazione ticket
├── partials/
│   ├── ticket-card.blade.php    → Card singolo ticket
│   ├── response-item.blade.php  → Elemento risposta
│   ├── reply-form.blade.php     → Form per rispondere
│   ├── filters.blade.php        → Form filtri avanzati
│   ├── stats-cards.blade.php    → Cards statistiche
│   └── ticket-actions.blade.php → Azioni rapide (chiudi/riapri)
└── components/
    ├── status-badge.blade.php   → Badge status colorato
    ├── priority-badge.blade.php → Badge priorità
    ├── attachment-list.blade.php → Lista allegati
    ├── user-avatar.blade.php    → Avatar utente
    └── timeline-item.blade.php  → Elemento timeline
```

### 🖼️ **Assets & Storage Structure**
```
storage/app/public/
├── helpdesk/
│   ├── attachments/
│   │   ├── 2025/09/12/          → Organizzazione per data
│   │   └── thumbnails/          → Miniature generate automaticamente
│   └── exports/
│       ├── tickets_export_YYYYMMDD.csv
│       └── reports/
```

### 🎨 **Frontend Assets**
```
resources/js/
├── helpdesk.js                  → Alpine.js components
└── components/
    ├── ticket-filters.js        → Gestione filtri avanzati
    ├── file-upload.js          → Drag & drop upload
    └── ticket-actions.js       → Quick actions

resources/css/
└── helpdesk.css                 → Stili specifici Helpdesk
```

---

## 🔧 **FASE 5: FEATURES AVANZATE**

### 📎 **Sistema Allegati** *(Solo Super Admin)*
- **Upload**: Drag & drop + click to browse
- **Formati**: JPG, PNG, GIF, PDF (max 5MB each)
- **Validazione**: Mime type, dimensione, virus scan
- **Storage**: Organizzato per data `/helpdesk/attachments/YYYY/MM/DD/`
- **Thumbnails**: Auto-generate per immagini (150x150px)
- **Preview**: Inline nelle conversazioni + lightbox
- **Download**: Sicuro con autorizzazione + log accessi

### 🔔 **Notifiche & Alerts System**
```php
// Notification Events
- TicketCreated         → Notify Super Admin
- TicketResponseAdded   → Notify ticket creator
- TicketStatusChanged   → Notify all participants
- TicketClosed          → Notify ticket creator
- TicketReopened        → Notify Super Admin

// UI Notifications
- Badge counter in sidebar (open tickets)
- Toast notifications for actions
- Real-time updates (polling ogni 30s)
- Email notifications (optional)
```

### 📊 **Analytics & Reports**
```php
// Metrics to Track
- Average response time (Super Admin)
- Average resolution time
- Tickets by category/priority distribution
- User satisfaction (optional rating)
- Monthly/weekly trends

// Export Capabilities
- CSV export (filtered data)
- PDF report generation
- Excel format with charts
- Email scheduled reports
```

### 🤖 **Automation Features**
- **Auto-assignment**: Basato su categoria
- **Auto-close**: Ticket inattivi da 7+ giorni
- **Escalation**: Alta priorità senza risposta da 24h
- **Templates**: Risposte predefinite comuni
- **Tags**: Sistema etichette per categorizzazione

---

## 🧪 **FASE 6: TESTING STRATEGY**

### ✅ **Unit Tests**
```php
// Model Tests
- TicketTest: relationships, scopes, accessors
- TicketResponseTest: file handling, validation
- UserTest: helpdesk permissions

// Controller Tests  
- HelpdeskControllerTest: CRUD operations, authorization
- FileUploadTest: validation, storage, security
- NotificationTest: email sending, real-time updates
```

### 🎭 **Feature Tests**
```php
// User Journey Tests
- CreateTicketFlowTest: Form → validation → storage
- ResponseWorkflowTest: Reply → status change → notification
- FileUploadFlowTest: Upload → validation → display
- PermissionTest: Super Admin only access
```

### 🚀 **E2E Tests**
```php
// Browser Tests (Laravel Dusk)
- FullTicketLifecycleTest: Create → respond → close
- ResponsiveUITest: Mobile/tablet/desktop layouts
- PerformanceTest: 1000+ tickets loading
- SecurityTest: XSS, CSRF, file upload attacks
```

### 🔍 **Testing Data & Scenarios**

```php
// Test Scenarios
1. Happy Path: Ticket creation → response → resolution
2. Edge Cases: Empty messages, invalid files, concurrent updates
3. Error Handling: Network failures, storage issues, validation errors
4. Performance: Bulk operations, large files, many responses
5. Security: Unauthorized access, malicious uploads, XSS attempts

// Test Data Factory
TicketFactory: Various priorities, statuses, categories
UserFactory: Super Admin, Admin, Student roles
ResponseFactory: With/without attachments, internal/public
```

---

## ⏱️ **TIMELINE IMPLEMENTAZIONE DETTAGLIATO**

| **Fase** | **Durata** | **Tasks** | **Deliverable** |
|----------|------------|-----------|-----------------|
| **Setup Base** | 15 min | Controller + Routes base | CRUD scaffolding |
| **Lista Ticket** | 45 min | Index view + filtri + stats + pagination | Dashboard funzionale |
| **Dettaglio Ticket** | 60 min | Show view + timeline + response form | Visualizzazione completa |
| **Sistema Allegati** | 45 min | Upload + validation + display | Gestione file |
| **Actions Avanzate** | 30 min | Close/reopen + status change | Workflow completo |
| **UI Polish** | 30 min | Styling + responsive + UX | Interfaccia finale |
| **Testing** | 30 min | E2E + debugging + fixes | Sistema testato |
| **Documentation** | 15 min | Code comments + README | Documentazione |
| **TOTALE** | **~4 ore** | **8 fasi** | **Sistema Helpdesk Production-Ready** |

---

## 🚦 **PRIORITÀ IMPLEMENTAZIONE**

### 🔴 **CRITICAL (P0)** - *Funzionalità Core* [2 ore]
1. ✅ Database + Models (completato)
2. 🚧 Controller base con CRUD
3. 🚧 Lista ticket con filtri essenziali
4. 🚧 Dettaglio ticket con risposte
5. 🚧 Form risposta semplice (solo testo)
6. 🚧 Cambio status (aperto/chiuso)

### 🟡 **HIGH (P1)** - *User Experience* [1.5 ore]
1. Upload allegati immagini
2. Filtri avanzati + ricerca full-text
3. Statistiche dashboard
4. UI responsive + mobile-friendly
5. Notifiche toast per azioni

### 🟢 **MEDIUM (P2)** - *Nice to Have* [30 min]
1. Export CSV dei ticket
2. Analytics avanzate
3. Templates risposte
4. Auto-refresh real-time

---

## 🔒 **SECURITY CHECKLIST**

### 🛡️ **Access Control**
- ✅ **Route Protection**: Middleware `role:super_admin`
- ✅ **Model Authorization**: Policy-based permissions
- ✅ **View Guards**: `@can` directives in Blade
- ✅ **API Security**: CSRF token validation

### 🔐 **Data Protection**
- ✅ **Input Validation**: Request validation classes
- ✅ **XSS Prevention**: Escaped output, HTML purifier
- ✅ **SQL Injection**: Eloquent ORM protection
- ✅ **Mass Assignment**: Fillable properties

### 📁 **File Upload Security**
```php
// File Upload Validation Rules
'attachments.*' => [
    'required',
    'file',
    'mimes:jpg,jpeg,png,gif,pdf',
    'max:5120', // 5MB max
    'dimensions:max_width=4000,max_height=4000'
];

// Storage Security
- Files stored outside web root
- Random filename generation
- Mime type validation server-side
- Virus scanning integration (optional)
- Access log per download
```

### 🕒 **Rate Limiting**
```php
// Throttling Rules
Route::middleware('throttle:30,1')->group(function () {
    // Helpdesk routes limited to 30 requests per minute
});

// Specific Limits
- Ticket creation: 5 per hour per user
- File uploads: 10 per hour per user
- Response posting: 20 per hour per user
```

---

## 📚 **DEPENDENCIES & REQUIREMENTS**

### 🔧 **Laravel Packages**
```php
// Required Packages
- "intervention/image": "^2.7" // Image processing
- "spatie/laravel-permission": "^5.0" // Advanced permissions (optional)
- "barryvdh/laravel-dompdf": "^2.0" // PDF generation
- "maatwebsite/excel": "^3.1" // Excel export

// Development Packages  
- "laravel/dusk": "^7.0" // Browser testing
- "phpunit/phpunit": "^10.0" // Unit testing
```

### 🌐 **Frontend Dependencies**
```json
{
    "devDependencies": {
        "alpinejs": "^3.12.0",
        "tailwindcss": "^3.3.0",
        "@tailwindcss/forms": "^0.5.0",
        "dropzone": "^6.0.0",
        "sortablejs": "^1.15.0"
    }
}
```

### 🗄️ **Database Requirements**
- MySQL 8.0+ (JSON column support)
- Redis (optional, per caching + sessions)
- Full-text search indexes
- Foreign key constraints enabled

---

## 🎯 **SUCCESS METRICS**

### 📈 **Performance KPIs**
- Lista ticket carica in < 200ms (100 ticket)
- Upload file completo in < 3s (5MB)
- Response time API < 100ms (95th percentile)
- UI responsiva su mobile (< 3s First Paint)

### 👥 **User Experience KPIs**
- 0 bug critici in produzione
- 100% funzionalità testate e funzionanti
- UI consistent con design system esistente
- Mobile-friendly (responsive design)

### 🔒 **Security KPIs**
- 0 vulnerabilità note
- 100% richieste autenticate/autorizzate
- File upload sicuri (validation + virus scan)
- Audit trail completo per tutte le azioni

---

## 🚀 **DEPLOYMENT CHECKLIST**

### ✅ **Pre-Deployment**
- [ ] Migrations tested su database staging
- [ ] All tests passing (Unit + Feature + E2E)
- [ ] Code review completato
- [ ] Security audit passato
- [ ] Performance benchmarks validated

### 🔄 **Deployment Steps**
1. **Database**: Run migrations (`php artisan migrate`)
2. **Storage**: Setup directory permissions (`storage/app/public/helpdesk/`)
3. **Cache**: Clear application cache (`php artisan cache:clear`)
4. **Config**: Update environment variables
5. **Assets**: Compile production assets (`npm run build`)

### 📊 **Post-Deployment**
- [ ] Health checks passed
- [ ] Smoke tests successful
- [ ] Monitoring alerts configured
- [ ] Documentation updated
- [ ] Team training completed

---

## 📖 **ADDITIONAL RESOURCES**

### 📝 **Documentation Links**
- [Laravel File Upload Best Practices](https://laravel.com/docs/filesystem)
- [Tailwind CSS Components](https://tailwindui.com/components)
- [Alpine.js Documentation](https://alpinejs.dev/)
- [Laravel Testing Guide](https://laravel.com/docs/testing)

### 🛠️ **Development Tools**
- **API Testing**: Postman collection per endpoints
- **Database**: MySQL Workbench per schema design
- **UI/UX**: Figma mockups per reference
- **Code Quality**: PHP CS Fixer + PHPStan

---

> **📌 NOTA**: Questo piano è un documento vivente che verrà aggiornato durante l'implementazione con feedback e miglioramenti scoperti durante lo sviluppo.

---

*Creato il: 12 Settembre 2025*  
*Versione: 1.0*  
*Progetto: Sistema Scuola di Danza - Helpdesk Module*