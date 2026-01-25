# CLAUDE.md

Istruzioni per Claude Code (claude.ai/code) su come lavorare con DanzaFacile.

**Ultimo aggiornamento:** 2026-01-25
**Versione:** 2.1.0
**Status:** ✅ PRODUCTION READY

---

## 📋 Panoramica Progetto

**DanzaFacile** è un sistema completo di gestione per scuole di danza con:
- **Backend Laravel 12** (PHP 8.4) - 100% production-ready
- **Flutter Mobile App** (in sviluppo Week 2)
- **Push Notifications** (Firebase Cloud Messaging)
- **API REST** completa per integrazione mobile

### 🎯 Status Attuale

| Componente | Status | URL/Info |
|-----------|--------|----------|
| **Backend Web** | ✅ Production | https://www.danzafacile.it |
| **API REST** | ✅ Production | https://www.danzafacile.it/api/mobile/v1/* |
| **VPS Server** | ✅ Live | 157.230.114.252 |
| **SSL/TLS** | ✅ Active | Let's Encrypt auto-renewal |
| **Firebase** | ✅ Connected | Push notifications ready |
| **Flutter App** | 🚧 Week 2 | In development |

---

## 🏗️ Architettura Sistema

### Backend Stack (Production)
- **Framework:** Laravel 12
- **PHP:** 8.4.11
- **Database:** MySQL 8.4.7
- **Cache:** Redis 7.0
- **Web Server:** Nginx 1.28.0
- **SSL:** Let's Encrypt (auto-renewal)
- **Queue:** Laravel Queue Worker (systemd)
- **Push:** Firebase Admin SDK (kreait/firebase-php 7.23.0)

### Frontend Stack
- **Build Tool:** Vite con plugin Laravel
- **CSS Framework:** Tailwind CSS v4
- **JavaScript:** Alpine.js (reactive components)
- **Icons:** Heroicons SVG
- **Charts:** Chart.js

### Mobile App Stack
- **Framework:** Flutter 3.x
- **State Management:** Provider
- **HTTP Client:** Dio
- **Push:** Firebase Cloud Messaging
- **Storage:** Shared Preferences

---

## 📚 Architecture Documentation (IMPORTANTE - LEGGERE PRIMA DI SVILUPPARE)

**Creati il 2026-01-25** per risolvere context management in AI coding.

### 🎯 File Core da Consultare

Questi 3 file contengono le **decisioni architetturali** e i **pattern fondamentali** del sistema. **DEVI consultarli** quando lavori su feature complesse o multi-tenant.

#### 1. `docs/ARCHITECTURE.md` (900+ righe) 🏛️

**Quando leggere:**
- ✅ Prima di decisioni architetturali major (nuova integrazione, tecnologia, pattern)
- ✅ Per capire il "WHY" dietro le scelte tecnologiche
- ✅ Quando aggiungi external integrations (Firebase, PayPal, etc.)
- ✅ Per problemi di performance/scalability

**Contiene:**
- System architecture overview (5 layers)
- Technology stack completo
- 6 Architecture Decision Records (ADR) - LEGGI QUESTI per capire scelte
- Design patterns utilizzati (Service, Repository, Policy, etc.)
- Data flow diagrams
- External integrations details
- Security architecture (CSP, HSTS, SSL/TLS)

**Path:** `docs/ARCHITECTURE.md`

---

#### 2. `docs/MULTI_TENANT_GUIDE.md` (650+ righe) 🏢

**Quando leggere (OBBLIGATORIO):**
- ✅ **SEMPRE** quando crei nuova migration
- ✅ **SEMPRE** quando crei nuovo Model
- ✅ **SEMPRE** per feature che coinvolgono dati scuole
- ✅ Per bug isolation (admin vede dati altre scuole)
- ✅ Per query cross-school

**Contiene:**
- School-based data isolation strategy
- HasSchoolScope trait implementation
- Global scopes pattern
- Migration checklist (14 items) - USA QUESTA
- 5 usage patterns (automatic, explicit, bypass, creating, service)
- Best practices OBBLIGATORIE
- Common pitfalls & troubleshooting
- Testing multi-tenant isolation

**Path:** `docs/MULTI_TENANT_GUIDE.md`

**⚠️ CRITICAL:** Violazione multi-tenant = SECURITY BREACH. Leggi SEMPRE questo file prima di toccare database.

---

#### 3. `docs/SERVICES_MAP.md` (800+ righe) 🗺️

**Quando leggere:**
- ✅ Prima di creare nuovo service (verifica se esiste già!)
- ✅ Per business logic complessa (check pattern esistenti)
- ✅ Per capire quale service usare per una funzionalità
- ✅ Prima di refactoring controller → service

**Contiene:**
- 11 servizi mappati con dettagli completi:
  - StorageQuotaService, PaymentService, FirebasePushService
  - PayPalService, InvoiceService, NotificationService
  - QRCodeService, FileUploadService, GuestRegistrationService
  - CacheService, DatabaseOptimizationService
- Key methods & signatures per ogni service
- Dependencies graph
- Usage examples concreti (payment flow, media upload, push)
- Service creation template
- "When to create service" checklist

**Path:** `docs/SERVICES_MAP.md`

---

### 📋 Quick Reference per Task Comuni

```bash
# NUOVA FEATURE CON DATABASE
1. Leggi: docs/MULTI_TENANT_GUIDE.md (Migration Checklist)
2. Verifica: docs/SERVICES_MAP.md (se serve nuovo service)
3. Implementa con global scope + school_id

# BUG ISOLATION (admin vede dati altre scuole)
1. Leggi: docs/MULTI_TENANT_GUIDE.md (Common Pitfalls section)
2. Fix: Aggiungi global scope o filtra school_id
3. Test: Verifica con 2+ scuole

# NUOVA INTEGRAZIONE (es: Stripe, Twillio)
1. Leggi: docs/ARCHITECTURE.md (ADR section)
2. Documenta: Crea nuovo ADR per decisione
3. Implementa: Segui pattern PayPalService/FirebasePushService

# REFACTORING BUSINESS LOGIC
1. Leggi: docs/SERVICES_MAP.md (Service creation guide)
2. Verifica: Service esiste già? Evita duplicazione
3. Crea: Usa template da SERVICES_MAP.md

# MAJOR ARCHITECTURAL DECISION
1. Leggi: docs/ARCHITECTURE.md (ADR section completa)
2. Valuta: Pro/contro alternativa
3. Documenta: Aggiungi nuovo ADR in ARCHITECTURE.md
```

---

### 🎯 Quando NON Serve Leggere

❌ **NON leggere** per:
- Simple CRUD operations (usa Resource Controller)
- UI-only changes (styling, layout)
- Bug fix minori non multi-tenant
- Feature già completamente documentata

✅ **Principio:** Se tocchi database o business logic → leggi docs rilevanti

---

### 🔄 Workflow Consigliato

```
1. Ricevi task → Analizza tipo (feature/bug/refactoring)
2. Identifica docs rilevanti (usa Quick Reference sopra)
3. Leggi SOLO sezioni rilevanti (non interi file)
4. Implementa seguendo pattern documentati
5. Se major change → Aggiorna docs corrispondenti
6. Commit con reference a docs consultati
```

---

## 👥 Ruoli e Permessi

### Super Admin
- Controllo completo sistema
- Gestione scuole (CRUD, attivazione, licenze)
- Gestione utenti globale
- Analytics e reports globali
- **NON** interagisce con singole scuole

### Admin Scuola
- Gestione completa della propria scuola
- Studenti, corsi, iscrizioni, pagamenti
- Presenze, eventi, documenti, media
- Analytics scuola-specific
- **NO** accesso ad altre scuole (multi-tenant strict)

### Studente
- Visualizzazione corsi disponibili
- Iscrizioni self-service
- Pagamenti online (PayPal)
- Tracking presenze personali
- Gestione documenti personali
- Accesso eventi e gallerie

---

## 🌍 Ambienti di Lavoro

### 1️⃣ Locale (Sviluppo)

**Setup con Docker Sail:**
```bash
# Directory
cd /Users/emanuele/Sites/scuoladanza

# Servizi disponibili
- Laravel App: http://localhost:8089
- MySQL: localhost:3307 (user: sail, pass: password)
- phpMyAdmin: http://localhost:8090
- Redis: localhost:6380
- Meilisearch: http://localhost:7701
- Mailpit: http://localhost:8026 (SMTP: 1026)
```

**Quando usare:**
- Sviluppo nuove feature
- Testing prima del deploy
- Debug problemi complessi
- Modifiche database schema

### 2️⃣ Production (VPS)

**Server Details:**
```bash
# SSH Access
ssh root@157.230.114.252

# Directory
cd /var/www/danzafacile

# Stack
- Domain: https://www.danzafacile.it
- Nginx: 1.28.0
- PHP-FPM: 8.4.11
- MySQL: 8.4.7 (localhost:3306)
- Redis: 7.0 (localhost:6379)
```

**Quando usare:**
- Testing feature in ambiente reale
- Verifiche security/performance
- Troubleshooting production issues
- Deploy finale

---

## 📦 Repository GitHub

### Backend (danzafacile)
```bash
# URL
https://github.com/emanuelerosato/danzafacile.git

# Branch Strategy
main          # Production (protected)
feature/*     # Nuove feature
fix/*         # Bug fixes
security/*    # Security patches
```

### Flutter App (danzafacile-app)
```bash
# URL
https://github.com/emanuelerosato/danzafacile-app.git

# Branch Strategy
main          # Production releases
develop       # Development
feature/*     # Nuove feature
```

---

## 🔄 Workflow Git (OBBLIGATORIO)

### Prima di Iniziare Qualsiasi Task

```bash
# 1. Sincronizza repository locale
git pull origin main

# 2. Verifica stato
git status

# 3. Verifica branch corrente
git branch
```

### Dopo Modifiche Significative

```bash
# 1. Verifica modifiche
git status
git diff

# 2. Stage modifiche
git add .

# 3. Commit con messaggio descrittivo
git commit -m "🎯 TIPO: Descrizione breve

## Dettaglio
- Cosa è stato modificato
- Perché è stato modificato
- Impatto sulle altre parti

🤖 Generated with [Claude Code](https://claude.com/claude-code)
Co-Authored-By: Claude <noreply@anthropic.com>"

# 4. Push su GitHub
git push origin main
```

### Conventional Commits (Preferiti)

```bash
✨ feat:      Nuova feature
🐛 fix:       Bug fix
📚 docs:      Documentazione
💄 style:     Styling/UI
♻️ refactor:  Refactoring codice
⚡️ perf:      Performance improvement
✅ test:      Testing
🔧 chore:     Maintenance
🔒 security:  Security fix
🚀 deploy:    Deployment
```

### IMPORTANTE - Claude DEVE:
1. ✅ Verificare `git status` prima di iniziare
2. ✅ Committare dopo ogni modifica significativa
3. ✅ Pushare su GitHub automaticamente
4. ✅ Aggiornare docs/ se necessario
5. ❌ NON committare file sensibili (.env, credentials)

---

## 🚀 Deploy Workflow

### Deploy su VPS Production

```bash
# 1. SSH su VPS
ssh root@157.230.114.252

# 2. Navigate to app
cd /var/www/danzafacile

# 3. Pull latest code
git pull origin main

# 4. Update dependencies (se composer.json modificato)
composer install --no-dev --optimize-autoloader

# 5. Run migrations (se ci sono)
php artisan migrate --force

# 6. Clear & rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 7. Restart services
systemctl restart php8.4-fpm
systemctl reload nginx

# 8. Verify deployment
curl -I https://www.danzafacile.it
```

### Quick Deploy (solo code changes)

```bash
ssh root@157.230.114.252 "cd /var/www/danzafacile && git pull && php artisan optimize && systemctl restart php8.4-fpm"
```

### Rollback (se qualcosa va male)

```bash
# SSH su VPS
cd /var/www/danzafacile

# Rollback all'ultimo commit funzionante
git log --oneline -5  # trova commit hash
git reset --hard <commit-hash>

# Restart services
systemctl restart php8.4-fpm nginx
```

---

## 📚 Documentazione (Nuova Struttura)

```
/
├── README.md                   # Getting started
├── CLAUDE.md                   # Questo file
│
└── docs/
    ├── README.md               # Indice completo documentazione
    │
    ├── api/                    # API Documentation
    │   ├── API_ENDPOINTS.md
    │   └── FLUTTER_API_DOCUMENTATION.md
    │
    ├── security/               # Security Reports & Guides
    │   ├── SECURITY_AUDIT_REPORT_2025-11-22.md
    │   ├── SECURITY_FIXES_APPLIED.md
    │   ├── CSP_FIX_APPLIED.md
    │   ├── SSL_TLS_AUDIT_REPORT.md
    │   └── ...
    │
    ├── deployment/             # Deploy Guides
    │   ├── DEPLOYMENT.md
    │   └── VPS_BACKEND_CHANGES.md
    │
    ├── flutter/                # Flutter App Docs
    │   ├── FLUTTER_APP_STRATEGY.md
    │   └── FLUTTER_PROJECT_INIT.md
    │
    └── [general docs]
        ├── PUSH_NOTIFICATIONS_GUIDE.md
        ├── PRE_PRODUCTION_CHECKLIST.md
        ├── guida.md (guida italiana completa)
        └── ...
```

### Come Navigare Docs

```bash
# Indice completo
cat docs/README.md

# API reference
cat docs/api/API_ENDPOINTS.md

# Security audit latest
cat docs/security/SECURITY_AUDIT_REPORT_2025-11-22.md

# Deploy guide
cat docs/deployment/DEPLOYMENT.md

# Push notifications
cat docs/PUSH_NOTIFICATIONS_GUIDE.md
```

---

## 🔔 Firebase & Push Notifications

### Setup Locale

```bash
# 1. Credentials file location (NON committare!)
storage/app/firebase/firebase-credentials.json

# 2. .env configuration
FIREBASE_CREDENTIALS=storage/app/firebase/firebase-credentials.json
FIREBASE_DATABASE_URL=https://danzafacile-default-rtdb.firebaseio.com
```

### Setup VPS (già configurato)

```bash
# Verify Firebase connected
ssh root@157.230.114.252
cd /var/www/danzafacile
php artisan tinker
> $service = app(App\Services\FirebasePushService::class);
> $service->testConnection(); // true = OK
```

### Testing Push Notifications

```bash
# Test cron job manually
php artisan notifications:send-lesson-reminders

# Check notification logs
php artisan tinker
> \App\Models\NotificationLog::latest()->take(5)->get();
```

### API Endpoints Push

```
POST   /api/mobile/v1/notifications/fcm-token       # Register device
DELETE /api/mobile/v1/notifications/fcm-token       # Unregister device
GET    /api/mobile/v1/notifications/preferences     # Get user preferences
PUT    /api/mobile/v1/notifications/preferences     # Update preferences
```

**Full guide:** `docs/PUSH_NOTIFICATIONS_GUIDE.md` (29KB, 1,045 righe)

---

## 🛠️ Comandi Sviluppo

### Setup Iniziale Ambiente Locale

```bash
# 1. Clone repository
git clone https://github.com/emanuelerosato/danzafacile.git
cd danzafacile

# 2. Install dependencies
composer install
npm install

# 3. Environment setup
cp .env.example .env
php artisan key:generate

# 4. Start Docker services
./vendor/bin/sail up -d

# 5. Run migrations
./vendor/bin/sail artisan migrate --seed

# 6. Build frontend
npm run dev
```

### Development Workflow

```bash
# Start all services (raccomandato)
composer run dev
# Avvia: Laravel + Queue Worker + Logs + Vite hot reload

# OR manual start
./vendor/bin/sail up -d      # Start Docker
./vendor/bin/sail npm run dev # Start Vite
```

### Database

```bash
# Fresh migration + seed
php artisan migrate:fresh --seed

# Specific seeder
php artisan db:seed --class=TestSchoolSeeder

# Create test data for push notifications
php artisan db:seed --class=PushNotificationTestSeeder
```

### Queue Worker (locale)

```bash
# Start queue worker
php artisan queue:work

# With verbose output
php artisan queue:work --verbose

# Process 10 jobs then stop
php artisan queue:work --max-jobs=10
```

### Cache Management

```bash
# Clear all caches
php artisan optimize:clear

# Clear specific caches
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# Rebuild production caches (before deploy)
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### Code Quality

```bash
# Format code (Laravel Pint)
./vendor/bin/pint

# Check code style (no changes)
./vendor/bin/pint --test

# Specific files
./vendor/bin/pint app/Http/Controllers
```

### Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Feature
php artisan test --testsuite=Unit

# Run specific test file
php artisan test tests/Feature/Api/AuthTest.php

# With coverage
php artisan test --coverage
```

---

## 🔒 Security Best Practices

### File Sensibili (NON committare MAI)

```bash
.env
.env.production
storage/app/firebase/firebase-credentials.json
auth.json
*.pem
*.key
```

### Headers Security (già implementati)

Il middleware `SecurityHeaders.php` applica automaticamente:
- ✅ Content-Security-Policy (CSP) - Grade A
- ✅ Strict-Transport-Security (HSTS)
- ✅ X-Frame-Options: SAMEORIGIN
- ✅ X-Content-Type-Options: nosniff
- ✅ Referrer-Policy
- ✅ Permissions-Policy

**Dettagli:** `docs/security/CSP_FIX_APPLIED.md`

### CSP Nonce Usage (Blade)

```blade
<!-- Scripts require nonce -->
<script nonce="@cspNonce">
    // Your inline JavaScript
</script>

<!-- Alpine.js requires unsafe-eval (già configurato) -->
<div x-data="{ open: false }">
    <!-- Alpine expressions work automatically -->
</div>

<!-- Inline styles work (unsafe-inline configurato) -->
<div style="color: red;">Text</div>
```

### API Security

```bash
# Rate limiting (già configurato)
- Public endpoints: 10 req/min
- Authenticated: 60 req/min
- Sensitive ops: 5 req/min

# Authentication
- Laravel Sanctum tokens
- Token expiration: 24h
```

---

## 🎨 Sistema di Design (OBBLIGATORIO)

### Layout Pattern Standard

**TUTTE le pagine DEVONO usare questo pattern:**

```blade
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Titolo Pagina
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Descrizione breve della funzionalità
                </p>
            </div>
            <!-- Action buttons se necessari -->
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Pagina Corrente</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <!-- Contenuto pagina -->
            </div>
        </div>
    </div>
</x-app-layout>
```

### Palette Colori (OBBLIGATORIA)

```css
/* Colori Primari */
bg-gradient-to-r from-rose-500 to-purple-600  /* Primary buttons */
bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50  /* Background */
bg-white rounded-lg shadow  /* Cards */

/* Colori Stato */
bg-green-100 text-green-800 border-green-200  /* Success */
bg-yellow-100 text-yellow-800 border-yellow-200  /* Warning */
bg-red-100 text-red-800 border-red-200  /* Error */
bg-blue-100 text-blue-800 border-blue-200  /* Info */
bg-gray-100 text-gray-800 border-gray-200  /* Neutral */

/* Typography */
font-semibold text-xl text-gray-800 leading-tight  /* Headers */
text-sm text-gray-600 mt-1  /* Subtitles */
text-gray-900  /* Body */
text-gray-600  /* Muted */
```

### Componenti Standard

#### Primary Button
```blade
<button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
    <svg class="w-4 h-4 mr-2"><!-- Icon --></svg>
    Azione
</button>
```

#### Stats Card
```blade
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center">
        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
            <svg class="w-6 h-6 text-white"><!-- Icon --></svg>
        </div>
        <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Metrica</p>
            <p class="text-2xl font-bold text-gray-900">Valore</p>
        </div>
    </div>
</div>
```

#### Form Input
```blade
<div>
    <label for="field" class="block text-sm font-medium text-gray-700 mb-2">
        Label <span class="text-red-500">*</span>
    </label>
    <input type="text" name="field" id="field"
           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
</div>
```

#### Status Badge
```blade
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
    Attivo
</span>
```

### REGOLE OBBLIGATORIE

#### ✅ SEMPRE
1. Layout: `<x-app-layout>` con header e breadcrumb
2. Background: `bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50`
3. Container: `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`
4. Cards: `bg-white rounded-lg shadow`
5. Spacing: `space-y-6` tra sezioni

#### ❌ MAI
1. NO Glassmorphism (`backdrop-blur`, `bg-white/80`)
2. NO Layout custom diversi dal pattern
3. NO Colori al di fuori della palette
4. NO Mix di pattern diversi nella stessa pagina

### Esempi di Riferimento
- `resources/views/admin/dashboard.blade.php`
- `resources/views/admin/students/index.blade.php`
- `resources/views/student/tickets/index.blade.php`

---

## 📱 API REST per Flutter

### Base URL

```
Production: https://www.danzafacile.it/api/mobile/v1
Local Dev:  http://localhost:8089/api/mobile/v1
```

### Authentication

```bash
# Login
POST /auth/login
Content-Type: application/json
{
  "email": "user@example.com",
  "password": "password"
}

# Response
{
  "success": true,
  "token": "1|...",
  "user": { ... }
}

# Use token in subsequent requests
Authorization: Bearer 1|...
```

### Main Endpoints

```
# Auth
POST   /auth/login
POST   /auth/logout
GET    /auth/user

# Student - Lessons
GET    /student/lessons/upcoming?days=7
GET    /student/lessons/{id}
GET    /student/lessons/by-date/{date}

# Student - Courses
GET    /student/courses
GET    /student/courses/{id}

# Notifications
POST   /notifications/fcm-token
DELETE /notifications/fcm-token
GET    /notifications/preferences
PUT    /notifications/preferences
```

**Full API Reference:** `docs/api/FLUTTER_API_DOCUMENTATION.md`

### Test Account

```
Email: studente1@test.pushnotif.local
Password: password
School: [TEST] Scuola Push Notifications (ID: 4)
```

---

## 🔧 Troubleshooting

### Backend non risponde (VPS)

```bash
# Check services status
ssh root@157.230.114.252
systemctl status nginx
systemctl status php8.4-fpm
systemctl status mysql

# Check logs
tail -100 /var/www/danzafacile/storage/logs/laravel.log
tail -50 /var/log/nginx/error.log

# Restart services
systemctl restart php8.4-fpm nginx
```

### Queue worker non funziona

```bash
# VPS - Check service
systemctl status laravel-worker
systemctl restart laravel-worker

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Cache problems

```bash
# Clear tutto
php artisan optimize:clear

# Rebuild caches
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Firebase connection issues

```bash
# Verify credentials file exists
ls -la storage/app/firebase/

# Test connection
php artisan tinker
> $service = app(App\Services\FirebasePushService::class);
> $service->testConnection();
```

### CSP blocking scripts/styles

```blade
<!-- Scripts need nonce -->
<script nonce="@cspNonce">
    // Code here
</script>

<!-- Alpine.js works (unsafe-eval enabled) -->
<div x-data="{ open: false }">OK</div>

<!-- Inline styles work (unsafe-inline enabled) -->
<div style="color: red;">OK</div>
```

---

## 📊 Monitoring & Logs

### Laravel Logs (VPS)

```bash
# Real-time logs
tail -f /var/www/danzafacile/storage/logs/laravel.log

# Last 100 lines
tail -100 /var/www/danzafacile/storage/logs/laravel.log

# Search for errors
grep -i "error" /var/www/danzafacile/storage/logs/laravel.log
```

### Nginx Logs (VPS)

```bash
# Access log
tail -f /var/log/nginx/access.log

# Error log
tail -f /var/log/nginx/error.log
```

### Queue Logs (VPS)

```bash
# Laravel queue worker service logs
journalctl -u laravel-worker -f

# Last 50 entries
journalctl -u laravel-worker -n 50
```

---

## 🎯 Best Practices per Claude

### Prima di Scrivere Codice

1. ✅ `git pull origin main` - Sincronizza sempre
2. ✅ Leggi documentazione in `docs/` se esiste
3. ✅ Verifica esempi esistenti simili
4. ✅ Controlla design system per UI

### Durante lo Sviluppo

1. ✅ Segui design pattern esistenti
2. ✅ Usa componenti standardizzati
3. ✅ Commenta codice complesso
4. ✅ Rispetta separazione ruoli (Super Admin/Admin/User)
5. ✅ Mantieni multi-tenant isolation

### Dopo Modifiche

1. ✅ Test locale prima di commit
2. ✅ `git add . && git commit -m "..."`
3. ✅ `git push origin main`
4. ✅ Aggiorna docs/ se necessario
5. ✅ Test su VPS production se critiche

### Lingua

- ✅ Codice: Inglese (variabili, funzioni, classi)
- ✅ Commenti: Italiano
- ✅ UI: Italiano
- ✅ Documentazione: Italiano
- ✅ Git commits: Italiano (con emoji)

---

## 📞 Quick Reference

### Repository URLs
- Backend: https://github.com/emanuelerosato/danzafacile.git
- Flutter: https://github.com/emanuelerosato/danzafacile-app.git

### Production URLs
- Web: https://www.danzafacile.it
- API: https://www.danzafacile.it/api/mobile/v1

### VPS Access
- SSH: `ssh root@157.230.114.252`
- App Directory: `/var/www/danzafacile`

### Documentation
- Index: `docs/README.md`
- API: `docs/api/FLUTTER_API_DOCUMENTATION.md`
- Deploy: `docs/deployment/DEPLOYMENT.md`
- Security: `docs/security/SECURITY_AUDIT_REPORT_2025-11-22.md`
- Push: `docs/PUSH_NOTIFICATIONS_GUIDE.md`

### Test Accounts
- Email: `studente1@test.pushnotif.local`
- Password: `password`
- School: `[TEST] Scuola Push Notifications`

---

## ✅ Pre-Flight Checklist

Prima di ogni sessione di lavoro, Claude dovrebbe:

- [ ] `git pull origin main`
- [ ] `git status` (verificare no file uncommitted)
- [ ] Verificare obiettivo task è chiaro
- [ ] Controllare docs/ per info esistenti
- [ ] Pianificare modifiche necessarie

Dopo ogni modifica significativa:

- [ ] Test locale funziona
- [ ] Codice formattato (`./vendor/bin/pint`)
- [ ] Commit con messaggio descrittivo
- [ ] Push su GitHub
- [ ] Aggiornare docs/ se necessario

---

**Versione:** 2.0.0
**Ultimo aggiornamento:** 2025-11-23
**Maintainer:** Claude Code AI Assistant
