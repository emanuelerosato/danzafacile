# API Coverage Report per Flutter App
**Data Generazione:** 2025-10-02
**Branch:** feature/refactoring-phase-1
**Stato:** Analisi Completa

---

## 📊 Executive Summary

**Copertura Totale:** ~75%
**API Pronte:** 13 controller API attivi
**API Mancanti:** 4 aree principali (Tickets, Documents, Galleries, Rooms)
**Rate Limiting:** ✅ Implementato (3 livelli)
**Autenticazione:** ✅ Laravel Sanctum

---

## ✅ API IMPLEMENTATE E PRONTE

### 🔐 **Autenticazione (AuthController)**
**Endpoint:** `/api/mobile/v1/auth/*`
**Status:** ✅ **COMPLETO**

- ✅ POST `/login` - Login utente
- ✅ POST `/register` - Registrazione nuovo utente
- ✅ POST `/logout` - Logout
- ✅ GET `/me` - Dati utente autenticato
- ✅ PUT `/profile` - Aggiorna profilo
- ✅ PUT `/password` - Cambia password
- ✅ POST `/forgot-password` - Reset password
- ✅ POST `/reset-password` - Conferma reset password

**Rate Limiting:** 10 req/min (public), 60 req/min (auth)

---

### 👨‍💼 **Admin APIs**

#### **1. Dashboard (AdminController)**
**Endpoint:** `/api/mobile/v1/admin/*`
**Status:** ✅ **COMPLETO**

- ✅ GET `/dashboard` - Stats dashboard
- ✅ GET `/analytics` - Analisi avanzate
- ✅ GET `/notifications` - Notifiche admin
- ✅ POST `/notifications/{id}/mark-read` - Segna notifica letta

#### **2. Corsi (AdminCourseController)**
**Endpoint:** `/api/mobile/v1/admin/courses`
**Status:** ✅ **COMPLETO**

- ✅ GET `/` - Lista corsi
- ✅ POST `/` - Crea corso
- ✅ GET `/{course}` - Dettaglio corso
- ✅ PUT `/{course}` - Aggiorna corso
- ✅ DELETE `/{course}` - Elimina corso
- ✅ POST `/{course}/toggle-status` - Attiva/disattiva
- ✅ POST `/{course}/duplicate` - Duplica corso
- ✅ GET `/statistics` - Statistiche corsi

#### **3. Studenti (AdminStudentController)**
**Endpoint:** `/api/mobile/v1/admin/students`
**Status:** ✅ **COMPLETO**

- ✅ GET `/` - Lista studenti
- ✅ POST `/` - Crea studente
- ✅ GET `/{student}` - Dettaglio studente
- ✅ PUT `/{student}` - Aggiorna studente
- ✅ DELETE `/{student}` - Elimina studente
- ✅ POST `/{student}/activate` - Attiva studente
- ✅ POST `/{student}/deactivate` - Disattiva studente
- ✅ GET `/{student}/enrollments` - Iscrizioni studente
- ✅ GET `/{student}/payments` - Pagamenti studente
- ✅ GET `/statistics` - Statistiche studenti
- ✅ POST `/{student}/reset-password` - Reset password (rate limited 5/min)

---

### 👨‍🎓 **Student APIs**

#### **1. Profilo (StudentProfileController)**
**Endpoint:** `/api/mobile/v1/student/profile`
**Status:** ✅ **COMPLETO**

- ✅ GET `/` - Visualizza profilo
- ✅ PUT `/` - Aggiorna profilo
- ✅ PUT `/password` - Cambia password
- ✅ PUT `/email` - Cambia email
- ✅ GET `/dashboard` - Dashboard studente
- ✅ GET|PUT `/preferences` - Preferenze

#### **2. Corsi (StudentCourseController)**
**Endpoint:** `/api/mobile/v1/student/courses`
**Status:** ✅ **COMPLETO**

- ✅ GET `/` - Sfoglia corsi disponibili
- ✅ GET `/{course}` - Dettaglio corso
- ✅ GET `/enrolled/me` - Corsi a cui sono iscritto
- ✅ GET `/recommendations` - Corsi raccomandati
- ✅ GET `/categories` - Categorie corsi

#### **3. Iscrizioni (EnrollmentController)**
**Endpoint:** `/api/mobile/v1/student/enrollments`
**Status:** ✅ **COMPLETO**

- ✅ POST `/` - Nuova iscrizione
- ✅ GET `/{enrollment}` - Dettaglio iscrizione
- ✅ POST `/{enrollment}/cancel` - Cancella iscrizione
- ✅ GET `/history` - Storico iscrizioni

#### **4. Pagamenti (PaymentController)**
**Endpoint:** `/api/mobile/v1/student/payments`
**Status:** ✅ **COMPLETO**

- ✅ GET `/` - Lista pagamenti
- ✅ GET `/{payment}` - Dettaglio pagamento
- ✅ GET `/statistics` - Statistiche pagamenti
- ✅ GET `/upcoming` - Pagamenti in scadenza
- ✅ GET `/{payment}/status` - Stato pagamento
- ✅ POST `/{payment}/paypal` - Crea pagamento PayPal
- ✅ GET `/{payment}/paypal/success` - Callback successo
- ✅ GET `/{payment}/paypal/cancel` - Callback cancellazione

---

### 🔄 **Shared APIs (Tutti gli utenti autenticati)**

#### **1. Eventi (EventController)**
**Endpoint:** `/api/mobile/v1/events`
**Status:** ✅ **COMPLETO**

- ✅ GET `/` - Lista eventi
- ✅ GET `/{event}` - Dettaglio evento
- ✅ GET `/categories` - Categorie eventi
- ✅ POST `/{event}/register` - Registrati evento
- ✅ DELETE `/{event}/cancel` - Cancella registrazione
- ✅ GET `/my-events` - I miei eventi

#### **2. Presenze (AttendanceController)**
**Endpoint:** `/api/mobile/v1/attendance`
**Status:** ✅ **COMPLETO**

- ✅ GET `/my-attendance` - Le mie presenze
- ✅ GET `/my-stats` - Statistiche presenze
- ✅ GET `/upcoming-sessions` - Sessioni future
- ✅ POST `/check-in` - Check-in manuale
- ✅ POST `/qr-code` - Genera QR code
- ✅ POST `/qr-check-in` - Check-in via QR (admin only)

#### **3. Staff (StaffController)**
**Endpoint:** `/api/mobile/v1/staff` (Admin only)
**Status:** ✅ **COMPLETO**

- ✅ GET `/` - Lista staff
- ✅ POST `/` - Crea staff
- ✅ GET `/{staff}` - Dettaglio staff
- ✅ PUT `/{staff}` - Aggiorna staff
- ✅ DELETE `/{staff}` - Elimina staff
- ✅ POST `/{staff}/toggle-status` - Attiva/disattiva
- ✅ GET `/{staff}/schedule` - Orario staff
- ✅ GET `/statistics` - Statistiche staff

#### **4. Analytics (AnalyticsController)**
**Endpoint:** `/api/mobile/v1/analytics`
**Status:** ✅ **COMPLETO**

- ✅ GET `/dashboard` - Dashboard analytics
- ✅ GET `/attendance` - Analisi presenze
- ✅ GET `/revenue` - Analisi ricavi (admin only)
- ✅ GET `/export` - Export dati (admin only)

---

## ❌ API MANCANTI - DA IMPLEMENTARE

### 🎫 **1. TICKETS API** ⚠️ **CRITICO**
**Controller:** `TicketController` - **NON ESISTE**
**Funzionalità Web:** ✅ Implementata in `AdminTicketController` e `StudentTicketController`

#### **Endpoint Necessari:**

**Admin:**
```
GET    /api/mobile/v1/admin/tickets           - Lista tickets (ricevuti + inviati)
POST   /api/mobile/v1/admin/tickets           - Crea ticket (a SuperAdmin)
GET    /api/mobile/v1/admin/tickets/{id}      - Dettaglio ticket
PUT    /api/mobile/v1/admin/tickets/{id}      - Aggiorna ticket
POST   /api/mobile/v1/admin/tickets/{id}/reply - Rispondi ticket
POST   /api/mobile/v1/admin/tickets/{id}/close - Chiudi ticket
GET    /api/mobile/v1/admin/tickets/statistics - Stats tickets
```

**Student:**
```
GET    /api/mobile/v1/student/tickets          - Lista tickets
POST   /api/mobile/v1/student/tickets          - Crea ticket
GET    /api/mobile/v1/student/tickets/{id}     - Dettaglio ticket
POST   /api/mobile/v1/student/tickets/{id}/reply - Rispondi ticket
```

**Priorità:** 🔴 **ALTA** (sistema di supporto essenziale)

---

### 📄 **2. DOCUMENTS API** ⚠️ **IMPORTANTE**
**Controller:** `DocumentController` - **DISABILITATO** (commentato in api.php)
**Funzionalità Web:** ✅ Implementata in `AdminDocumentController` e `StudentDocumentController`

#### **Endpoint Necessari:**

**Admin:**
```
GET    /api/mobile/v1/admin/documents           - Lista documenti
POST   /api/mobile/v1/admin/documents           - Carica documento
GET    /api/mobile/v1/admin/documents/{id}      - Dettaglio documento
PUT    /api/mobile/v1/admin/documents/{id}      - Aggiorna documento
DELETE /api/mobile/v1/admin/documents/{id}      - Elimina documento
GET    /api/mobile/v1/admin/documents/{id}/download - Download documento
POST   /api/mobile/v1/admin/documents/{id}/approve  - Approva documento
POST   /api/mobile/v1/admin/documents/{id}/reject   - Rifiuta documento
POST   /api/mobile/v1/admin/documents/bulk-action   - Azioni multiple
```

**Student:**
```
GET    /api/mobile/v1/student/documents         - Lista documenti
POST   /api/mobile/v1/student/documents         - Carica documento
GET    /api/mobile/v1/student/documents/{id}    - Dettaglio documento
GET    /api/mobile/v1/student/documents/{id}/download - Download documento
```

**Priorità:** 🟡 **MEDIA** (utile ma non bloccante)

---

### 🖼️ **3. GALLERIES API** ⚠️ **IMPORTANTE**
**Controller:** `GalleryController` - **NON ESISTE**
**Funzionalità Web:** ✅ Implementata in `MediaGalleryController`

#### **Endpoint Necessari:**

**Admin:**
```
GET    /api/mobile/v1/admin/galleries           - Lista gallerie
POST   /api/mobile/v1/admin/galleries           - Crea galleria
GET    /api/mobile/v1/admin/galleries/{id}      - Dettaglio galleria
PUT    /api/mobile/v1/admin/galleries/{id}      - Aggiorna galleria
DELETE /api/mobile/v1/admin/galleries/{id}      - Elimina galleria
POST   /api/mobile/v1/admin/galleries/{id}/upload - Carica media
POST   /api/mobile/v1/admin/galleries/{id}/external-link - Link esterno
GET    /api/mobile/v1/admin/galleries/{id}/media - Lista media
PUT    /api/mobile/v1/admin/galleries/{id}/media/{mediaId} - Aggiorna media
DELETE /api/mobile/v1/admin/galleries/{id}/media/{mediaId} - Elimina media
POST   /api/mobile/v1/admin/galleries/{id}/cover-image - Imposta copertina
```

**Student:**
```
GET    /api/mobile/v1/student/galleries         - Lista gallerie pubbliche
GET    /api/mobile/v1/student/galleries/{id}    - Dettaglio galleria
GET    /api/mobile/v1/student/galleries/{id}/media - Lista media
```

**Priorità:** 🟡 **MEDIA** (feature visual importante)

---

### 🏢 **4. ROOMS API** ⚠️ **OPZIONALE**
**Controller:** `RoomController` - **NON ESISTE**
**Funzionalità Web:** ✅ Implementata in `AdminRoomController`

#### **Endpoint Necessari:**

**Admin:**
```
GET    /api/mobile/v1/admin/rooms               - Lista aule
POST   /api/mobile/v1/admin/rooms               - Crea aula
GET    /api/mobile/v1/admin/rooms/{id}          - Dettaglio aula
PUT    /api/mobile/v1/admin/rooms/{id}          - Aggiorna aula
DELETE /api/mobile/v1/admin/rooms/{id}          - Elimina aula
GET    /api/mobile/v1/admin/rooms/{id}/availability - Disponibilità aula
```

**Student:**
```
GET    /api/mobile/v1/student/rooms/{id}        - Visualizza aula corso
```

**Priorità:** 🟢 **BASSA** (nice to have)

---

### 📊 **5. REPORTS API** ⚠️ **OPZIONALE**
**Controller:** `ReportController` - **NON ESISTE** (partial in SuperAdminController)
**Funzionalità Web:** ✅ Parzialmente implementata

#### **Endpoint Necessari:**

**Admin:**
```
GET    /api/mobile/v1/admin/reports/payments    - Report pagamenti
GET    /api/mobile/v1/admin/reports/enrollments - Report iscrizioni
GET    /api/mobile/v1/admin/reports/attendance  - Report presenze
GET    /api/mobile/v1/admin/reports/revenue     - Report ricavi
POST   /api/mobile/v1/admin/reports/export      - Export report
```

**Priorità:** 🟢 **BASSA** (analytics già coperto)

---

## 🔒 Security Features ✅

### **Rate Limiting Implementato:**
- ✅ **api-public:** 10 req/min per IP (login, register, webhooks)
- ✅ **api-auth:** 60 req/min per user (API autenticate)
- ✅ **api-sensitive:** 5 req/min per user (operazioni critiche)

### **Middleware Attivi:**
- ✅ `auth:sanctum` - Autenticazione token
- ✅ `throttle` - Rate limiting
- ✅ `role:admin|student|super_admin` - Controllo ruoli
- ✅ `school.ownership` - Multi-tenancy

---

## 📋 PIANO DI LAVORO per Flutter

### **FASE 1 - MVP (Minimo Funzionante)** 🔴 PRIORITÀ ALTA
**Obiettivo:** App funzionante con feature base

**API da Implementare:**
1. ✅ Autenticazione - **GIÀ PRONTA**
2. ✅ Corsi (Student) - **GIÀ PRONTA**
3. ✅ Iscrizioni - **GIÀ PRONTA**
4. ✅ Pagamenti - **GIÀ PRONTA**
5. ✅ Profilo - **GIÀ PRONTA**
6. ❌ **Tickets API** - **DA CREARE** (critico per supporto)

**Stima Lavoro FASE 1:** 1-2 giorni (solo Tickets API)

---

### **FASE 2 - Feature Complete** 🟡 PRIORITÀ MEDIA
**Obiettivo:** Tutte le feature principali

**API da Implementare:**
1. ❌ **Documents API** - Upload/download documenti
2. ❌ **Galleries API** - Visualizzazione foto/video
3. ✅ Eventi - **GIÀ PRONTA**
4. ✅ Presenze - **GIÀ PRONTA**

**Stima Lavoro FASE 2:** 2-3 giorni

---

### **FASE 3 - Nice to Have** 🟢 PRIORITÀ BASSA
**Obiettivo:** Completezza totale

**API da Implementare:**
1. ❌ **Rooms API** - Gestione aule
2. ❌ **Reports API** - Report avanzati

**Stima Lavoro FASE 3:** 1-2 giorni

---

## 🎯 RACCOMANDAZIONI

### **PUOI INIZIARE SUBITO CON:**
✅ Autenticazione (login, register, logout)
✅ Profilo studente
✅ Browse corsi
✅ Iscrizioni corsi
✅ Pagamenti PayPal
✅ Eventi e registrazioni
✅ Presenze (check-in, QR code)
✅ Dashboard analytics

### **DEVI IMPLEMENTARE PRIMA DI PRODUCTION:**
🔴 **Tickets API** (supporto clienti)
🟡 **Documents API** (gestione documenti)
🟡 **Galleries API** (media gallery)

### **OPZIONALE (Post-Launch):**
🟢 Rooms API
🟢 Reports API avanzati

---

## 📊 STATISTICHE FINALI

**Controller API Esistenti:** 13
**Endpoint Totali Implementati:** ~80
**Copertura Funzionalità:** 75%
**API Mancanti Critiche:** 1 (Tickets)
**API Mancanti Importanti:** 2 (Documents, Galleries)
**API Mancanti Opzionali:** 2 (Rooms, Reports)

---

## ✅ CONCLUSIONE

**VERDETTO:** 🟢 **PUOI INIZIARE LO SVILUPPO FLUTTER**

**CONDIZIONI:**
1. **MVP possibile SUBITO** con API esistenti (auth, corsi, pagamenti, eventi)
2. **Implementare Tickets API** prima del rilascio (1-2 giorni lavoro)
3. **Documents e Galleries API** consigliati per release 1.0 (2-3 giorni)
4. **Rooms e Reports** possono aspettare release successive

**STIMA TEMPO TOTALE per API Complete:**
- FASE 1 (MVP): **READY** ✅
- FASE 2 (Tickets): 1-2 giorni 🔴
- FASE 3 (Documents + Galleries): 2-3 giorni 🟡
- **TOTALE: 3-5 giorni** per copertura 100%

---

**NEXT STEPS:**
1. Creare `TicketController` API
2. Creare `DocumentController` API
3. Creare `GalleryController` API
4. Testing completo con Postman/Insomnia
5. Generare documentazione OpenAPI/Swagger
