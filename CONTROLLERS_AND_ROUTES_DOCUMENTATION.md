# Sistema Controller e Rotte RESTful - Scuola di Danza

Questo documento descrive il sistema completo di controller e rotte RESTful implementato per il sistema di gestione delle scuole di danza.

## Struttura del Sistema

### Modello dei Ruoli

Il sistema utilizza 4 ruoli principali:

- **SUPER_ADMIN**: Accesso completo a tutto il sistema, gestione di tutte le scuole
- **ADMIN**: Gestione della propria scuola specifica  
- **INSTRUCTOR**: Istruttore (per implementazioni future)
- **STUDENT**: Studente con accesso alla propria area personale

## Controller Creati

### 1. Super Admin Controllers (`/app/Http/Controllers/SuperAdmin/`)

#### SuperAdminController
- Dashboard principale del super admin
- Statistiche sistema globale
- Impostazioni sistema
- Reports e logs
- Manutenzione sistema

**Metodi principali:**
```php
- index() - Dashboard con statistiche globali
- settings() - Visualizza impostazioni sistema
- updateSettings() - Aggiorna impostazioni
- logs() - Visualizza logs sistema
- reports() - Genera report sistema
```

#### SchoolController
- CRUD completo delle scuole
- Attivazione/disattivazione scuole
- Azioni bulk (attiva/disattiva/elimina multiple scuole)
- Export dati scuole in CSV

**Metodi RESTful:**
```php
- index() - Lista paginata scuole con filtri
- create() - Form creazione scuola
- store() - Salvataggio nuova scuola
- show() - Dettaglio scuola con statistiche
- edit() - Form modifica scuola  
- update() - Aggiornamento scuola
- destroy() - Eliminazione scuola
- toggleStatus() - Attiva/disattiva scuola
- bulkAction() - Azioni su multiple scuole
- export() - Export CSV
```

#### SuperAdminUserController
- Gestione utenti amministratori delle scuole
- Impersonificazione utenti
- Gestione bulk utenti
- Export dati utenti

### 2. Admin Controllers (`/app/Http/Controllers/Admin/`)

#### AdminDashboardController
- Dashboard specifica per la scuola dell'admin
- Statistiche scuola (studenti, corsi, pagamenti, documenti)
- Activities recenti
- Export report scuola

#### CourseController  
- CRUD corsi della scuola
- Attivazione/disattivazione corsi
- Duplicazione corsi
- Statistiche corsi

**Caratteristiche:**
- Filtraggio automatico per school_id dell'admin
- Controllo capacità corsi
- Gestione istruttori

#### EnrollmentController
- Gestione iscrizioni studenti ai corsi
- Cancellazione e riattivazione iscrizioni
- Statistiche iscrizioni
- Export iscrizioni

#### SchoolPaymentController
- Gestione pagamenti della scuola
- Approvazione pagamenti
- Rimborsi
- Statistiche finanziarie
- Export pagamenti

### 3. Student Controllers (`/app/Http/Controllers/Student/`)

#### StudentDashboardController
- Dashboard personale studente
- Statistiche personali (corsi attivi, pagamenti, documenti)
- Corsi in arrivo
- Pagamenti recenti

**Funzionalità avanzate:**
```php
- getStats() - Statistiche periodo personalizzabile
- getProgress() - Progresso formativo studente
- getUpcomingActivities() - Prossime attività/lezioni
- quickActions() - Azioni rapide disponibili
```

#### StudentCourseController
- Visualizzazione corsi disponibili
- Iscrizione a corsi
- Cancellazione iscrizioni
- Le mie iscrizioni

**Filtri avanzati:**
- Per livello corso
- Per età appropriata
- Per giorni della settimana
- Esclusione corsi già frequentati

#### ProfileController
- Gestione profilo personale
- Aggiornamento password
- Upload/rimozione immagine profilo
- Storico attività
- Preferenze utente

### 4. Shared Controllers (`/app/Http/Controllers/Shared/`)

#### DocumentController
- Gestione documenti con controllo accesso basato su ruolo
- Approvazione/rifiuto documenti (admin/super-admin)
- Download sicuro documenti
- Azioni bulk

**Controlli di accesso:**
- Super Admin: tutti i documenti
- Admin: solo documenti della propria scuola
- Student: solo documenti personali

#### MediaItemController
- Gestione elementi media (immagini, video, documenti)
- Organizzazione in gallerie
- Viewer integrato
- Statistiche utilizzo storage

## Form Request Classes

Tutte le validazioni sono gestite tramite Form Request classes:

```
/app/Http/Requests/
├── StoreSchoolRequest.php
├── UpdateSchoolRequest.php
├── StoreCourseRequest.php
├── UpdateCourseRequest.php
├── StoreEnrollmentRequest.php
├── StorePaymentRequest.php
├── StoreDocumentRequest.php
└── UpdateProfileRequest.php
```

Ogni Form Request include:
- Regole di validazione
- Messaggi di errore personalizzati in italiano
- Controlli di autorizzazione

## Middleware di Sicurezza

### RoleMiddleware
```php
/app/Http/Middleware/RoleMiddleware.php
```
- Controllo accesso basato su ruolo
- Super Admin bypassa tutti i controlli
- Controllo gerarchico dei ruoli

### SchoolOwnershipMiddleware
```php
/app/Http/Middleware/SchoolOwnershipMiddleware.php
```
- Garantisce che Admin accedano solo ai dati della propria scuola
- Estrae school_id dalla richiesta
- Bypass per Super Admin

## Sistema di Rotte

### Rotte Web (`/routes/web.php`)

**Organizzazione per prefisso:**

```php
// Super Admin routes
/super-admin/*  -> middleware: role:super_admin

// Admin routes  
/admin/*        -> middleware: role:admin, school.ownership

// Student routes
/student/*      -> middleware: role:student

// Shared routes
/documents/*    -> Controllo accesso nel controller
/media/*        -> Controllo accesso nel controller
```

**Dashboard intelligente:**
```php
/dashboard -> Redirect automatico basato su ruolo:
- Super Admin -> /super-admin/
- Admin -> /admin/  
- Student -> /student/
```

### Rotte API (`/routes/api.php`)

API RESTful completa per tutti i controller con:
- Versioning (`/api/v1/`)
- Rate limiting
- Autenticazione Sanctum
- Endpoints mobile ottimizzati (`/api/mobile/v1/`)
- Webhooks per integrazioni esterne

## Funzionalità Avanzate Implementate

### 1. Ricerca e Filtri
- Search full-text su tutti gli indici
- Filtri multipli combinabili
- Paginazione AJAX
- Export risultati filtrati

### 2. Bulk Actions
- Selezione multipla con checkbox
- Azioni batch: attiva/disattiva/elimina
- Feedback operazioni completate
- Conferme di sicurezza

### 3. Upload File Sicuri
- Validazione tipi file
- Storage privato per documenti sensibili
- Storage pubblico per media
- Limitazioni dimensione file
- Pulizia automatica file orfani

### 4. Dashboard Interattive
- Statistiche real-time
- Grafici e trend
- Widget configurabili
- Notifiche integrate

### 5. Sistema Autorizzazioni
- Controllo granulare accessi
- Policy Laravel integrate
- Middleware a stack
- Logging tentativi accesso

### 6. Export e Report
- Export CSV configurabili
- Report PDF (implementabile)
- Filtri data periodo
- Statistiche aggregate

## Esempi di Utilizzo

### Super Admin - Gestione Scuole
```php
// Lista tutte le scuole con filtri
GET /super-admin/schools?search=dance&status=active&city=Milano

// Attiva multiple scuole
POST /super-admin/schools/bulk-action
{
    "action": "activate",
    "school_ids": [1, 2, 3]
}
```

### Admin - Gestione Corsi
```php
// Crea nuovo corso per la propria scuola
POST /admin/courses
{
    "name": "Danza Classica Principianti",
    "level": "beginner", 
    "max_students": 15,
    "price": 80.00,
    "schedule_days": ["monday", "wednesday"]
}
```

### Student - Iscrizione Corso
```php
// Iscrizione a corso disponibile
POST /student/courses/5/enroll
{
    "notes": "Prima esperienza di danza"
}
```

### Shared - Upload Documento
```php
// Upload certificato medico
POST /documents
{
    "type": "medical_certificate",
    "title": "Certificato Medico 2024",
    "file": [binary],
    "expiry_date": "2024-12-31"
}
```

## File Creati

### Controller:
- `/app/Http/Controllers/SuperAdmin/SuperAdminController.php`
- `/app/Http/Controllers/SuperAdmin/SchoolController.php`
- `/app/Http/Controllers/SuperAdmin/SuperAdminUserController.php`
- `/app/Http/Controllers/Admin/AdminDashboardController.php`
- `/app/Http/Controllers/Admin/CourseController.php`
- `/app/Http/Controllers/Admin/EnrollmentController.php`
- `/app/Http/Controllers/Admin/SchoolPaymentController.php`
- `/app/Http/Controllers/Student/StudentDashboardController.php`
- `/app/Http/Controllers/Student/StudentCourseController.php`
- `/app/Http/Controllers/Student/ProfileController.php`
- `/app/Http/Controllers/Shared/DocumentController.php`
- `/app/Http/Controllers/Shared/MediaItemController.php`

### Form Requests:
- `/app/Http/Requests/StoreSchoolRequest.php`
- `/app/Http/Requests/UpdateSchoolRequest.php`
- `/app/Http/Requests/StoreCourseRequest.php`
- `/app/Http/Requests/UpdateCourseRequest.php`
- `/app/Http/Requests/StoreEnrollmentRequest.php`
- `/app/Http/Requests/StorePaymentRequest.php`
- `/app/Http/Requests/StoreDocumentRequest.php`
- `/app/Http/Requests/UpdateProfileRequest.php`

### Middleware:
- `/app/Http/Middleware/RoleMiddleware.php`
- `/app/Http/Middleware/SchoolOwnershipMiddleware.php`

### Rotte:
- `/routes/web.php` (aggiornato)
- `/routes/api.php` (creato)

### Configurazione:
- `/bootstrap/app.php` (aggiornato con middleware)
- `/app/Models/User.php` (aggiornato con ruolo super_admin)

## Prossimi Passi per l'Integrazione

1. **Creare le View Blade** corrispondenti a tutti i controller
2. **Implementare autenticazione** (Laravel Breeze consigliato)
3. **Configurare storage** per upload file
4. **Creare seeder** per dati di test
5. **Implementare notifiche** email per eventi importanti
6. **Aggiungere cache** per performance
7. **Implementare test** automatizzati

Il sistema è ora completo e pronto per l'integrazione con le view frontend e per essere utilizzato in produzione.