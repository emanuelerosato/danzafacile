# CLAUDE.md

Questo file fornisce indicazioni a Claude Code (claude.ai/code) su come lavorare con il codice in questo repository.

---

## Panoramica del progetto

Si tratta di un’applicazione Laravel 12 (**scuoladidanza** – scuola di danza) basata su **PHP 8.2** con **Vite** per la compilazione degli asset frontend.  
Il progetto utilizza **Docker via Laravel Sail** per gestire l’ambiente di sviluppo e include i seguenti servizi:

- **MySQL** – Database (porta `3307` locale)
- **Redis** – Cache e sessioni (porta `6380` locale)
- **Meilisearch** – Motore di ricerca (porta `7701` locale)
- **Mailpit** – Test email (SMTP `1026`, Web UI `8026`)
- **Selenium** – Automazione dei browser per testing
- **Laravel App** – Server applicazione PHP (porta `8089` con Sail)

---

## Ruoli e Permessi

- **Super Admin:** controlla tutto il sistema. Può creare scuole, gestire licenze e assegnare Admin a ciascuna scuola. Non interagisce con le singole scuole degli Admin.  
- **Admin:** gestisce la propria scuola: corsi, utenti/studenti, pagamenti, documenti, media e operazioni interne. Non ha accesso ad altre scuole.  
- **User:** utenti finali/studenti. Possono iscriversi ai corsi, consultare gallerie, eventi e pagare online. Possono gestire il proprio profilo e documenti.  

---

## Obiettivo del progetto

Sviluppare un **Sistema di Gestione per Scuola di Danza**.  
In futuro il backend Laravel 12 potrà essere integrato con un frontend Flutter, ma per ora il lavoro è esclusivamente sul backend web.

---

## Stack tecnologico

- Laravel 12
- Sail
- MySQL
- Redis
- Blade
- Vite

---

## Modalità di lavoro

- **Vibe coding:** genera codice, struttura file, crea controller, rotte, template Blade, modelli e migrazioni.  
- Esegui comandi Artisan quando necessario.  
- Testa sempre tutto localmente su `http://localhost:8089/`.  
- Scrivi codice chiaro, commentato e pronto per modifiche future.  
- Usa solo agenti interni al progetto; Claude non deve usare servizi esterni se non indicato.
- **IMPORTANTE:** Traccia TUTTE le modifiche importanti nel file `guida.md`
- **IMPORTANTE:** Dopo ogni modifica significativa, esegui git commit e push su GitHub  

---

## Comandi di sviluppo

### Setup ambiente
```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
```

### Workflow sviluppo
```bash
composer run dev      # Laravel app + queue + logs + Vite
php artisan serve     # Laravel dev server (senza Docker)
npm run dev           # Vite dev server (hot reload frontend)
npm run build         # Build produzione assets
```

### Docker con Sail
```bash
./vendor/bin/sail up -d         # Avvia ambiente Docker
./vendor/bin/sail down          # Arresta ambiente Docker
./vendor/bin/sail artisan       # Esegui comandi Artisan dentro il container
./vendor/bin/sail composer      # Esegui Composer dentro il container
./vendor/bin/sail npm run dev   # Esegui Vite dentro il container
```

### Testing
```bash
composer run test
php artisan test
vendor/bin/phpunit
vendor/bin/phpunit tests/Unit
vendor/bin/phpunit tests/Feature
```

### Qualità codice
```bash
vendor/bin/pint
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Database
```bash
php artisan migrate
php artisan migrate:fresh
php artisan db:seed
```

### Git e GitHub
```bash
git status                    # Verifica stato modifiche
git add .                     # Aggiungi tutte le modifiche
git commit -m "messaggio"     # Commit con messaggio descrittivo
git push origin main          # Push su GitHub
git pull origin main          # Sincronizza da GitHub
---

## Architettura

### Backend
- **Framework:** Laravel 12 (PHP 8.2)
- **Models:** `app/Models/` (include User)
- **Controllers:** `app/Http/Controllers/`
- **Routes:** `routes/web.php`, `routes/console.php`
- **Migrations:** `database/migrations/`

### Frontend
- **Build Tool:** Vite con plugin Laravel
- **CSS Framework:** Tailwind CSS v4
- **Assets:** `resources/`
- **Build Config:** `vite.config.js`

### Servizi & Infrastruttura
- **Laravel App:** porta 8089 (Sail)
- **MySQL:** localhost:3307
- **phpMyAdmin:** http://localhost:8090 (user: sail, password: password)
- **Redis:** localhost:6379
- **Meilisearch:** http://localhost:7700
- **Mailpit:** SMTP localhost:1026, Web UI http://localhost:8026
- **Selenium:** browser automation

---

## Gestione Git e GitHub

**Repository:** https://github.com/emanuelerosato/scuoladidanza

### Workflow Git obbligatorio:
1. **Prima di iniziare:** `git pull origin main` per sincronizzare
2. **Dopo ogni modifica importante:** 
   - Aggiorna `guida.md` con le modifiche effettuate
   - `git add .`
   - `git commit -m "descrizione modifiche"`
   - `git push origin main`
3. **Claude deve SEMPRE:**
   - Verificare lo stato Git prima di iniziare (`git status`)
   - Committare e pushare automaticamente dopo modifiche significative
   - Tracciare tutto nel file `guida.md`

---

## Sistema di Design e Stile Grafico

### **Layout Pattern Standardizzato**
Tutte le pagine devono seguire rigorosamente questo pattern di layout:

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

### **Palette Colori**
```css
/* Colori Primari */
- Rose: from-rose-500 to-purple-600 (bottoni principali)
- Background: bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50
- Cards: bg-white con rounded-lg shadow

/* Colori Stato */
- Successo: bg-green-100 text-green-800 border-green-200
- Warning: bg-yellow-100 text-yellow-800 border-yellow-200
- Errore: bg-red-100 text-red-800 border-red-200
- Info: bg-blue-100 text-blue-800 border-blue-200
- Neutro: bg-gray-100 text-gray-800 border-gray-200

/* Typography */
- Headers: font-semibold text-xl text-gray-800 leading-tight
- Subtitle: text-sm text-gray-600 mt-1
- Body: text-gray-900
- Muted: text-gray-600
```

### **Componenti Standardizzati**

#### **Stats Cards**
```blade
<div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center">
        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
            <!-- SVG Icon -->
        </div>
        <div class="ml-4">
            <p class="text-sm font-medium text-gray-600">Label</p>
            <p class="text-2xl font-bold text-gray-900">Valore</p>
        </div>
    </div>
</div>
```

#### **Action Buttons**
```blade
<!-- Primary Button -->
<button class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
    <svg class="w-4 h-4 mr-2"><!-- Icon --></svg>
    Testo Button
</button>

<!-- Secondary Button -->
<button class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
    Testo Button
</button>
```

#### **Form Elements**
```blade
<!-- Input Field -->
<div>
    <label for="field" class="block text-sm font-medium text-gray-700 mb-2">
        Label <span class="text-red-500">*</span>
    </label>
    <input type="text" name="field" id="field"
           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
</div>

<!-- Select Field -->
<select class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
    <option>Opzione</option>
</select>
```

#### **Status Badges**
```blade
<!-- Status Success -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
    Attivo
</span>

<!-- Status Warning -->
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
    In Attesa
</span>
```

### **Container e Spacing**
```css
/* Container principale */
.max-w-7xl mx-auto px-4 sm:px-6 lg:px-8

/* Spacing verticale */
.space-y-6 (tra sezioni principali)
.space-y-4 (tra elementi correlati)
.space-y-2 (tra elementi piccoli)

/* Padding standard */
.p-6 (cards principali)
.p-4 (elementi secondari)
.py-8 (container principale)
```

### **Responsive Grid**
```blade
<!-- Stats Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

<!-- Form Grid -->
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">

<!-- Content Grid -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
```

### **Icons Standard**
Usare sempre SVG Heroicons con:
- `w-4 h-4` per icons nei bottoni
- `w-6 h-6` per icons nelle stats cards
- `w-5 h-5` per icons nei navigation items
- `stroke-width="2"` standard
- Colori: `text-white` (in bottoni), `currentColor` (inherit)

### **Regole di Consistenza**

#### **OBBLIGATORIO:**
1. **Layout**: Sempre usare il pattern `<x-app-layout>` con header e breadcrumb separati
2. **Background**: Sempre `bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50`
3. **Container**: Sempre `max-w-7xl mx-auto px-4 sm:px-6 lg:px-8`
4. **Cards**: Sempre `bg-white rounded-lg shadow`
5. **Spacing**: Sempre `space-y-6` tra sezioni principali

#### **VIETATO:**
1. **NO Glassmorphism**: Non usare `backdrop-blur`, `bg-white/80`, `border-white/20`
2. **NO Layout custom**: Non inventare strutture diverse dal pattern standard
3. **NO Colori casuali**: Usare solo la palette definita
4. **NO Mixed patterns**: Non mescolare stili diversi nella stessa pagina

### **Esempi di Riferimento**
- **Admin Dashboard**: `/resources/views/admin/dashboard.blade.php`
- **Admin Students**: `/resources/views/admin/students/index.blade.php`
- **Student Tickets**: `/resources/views/student/tickets/index.blade.php`

---

## Note aggiuntive

- **Separazione ruoli:** Rispetta sempre Super Admin, Admin e User
- **Modularità:** Mantieni codice pronto per futura integrazione Flutter
- **Lingua:** Tutti gli output devono essere in italiano
- **Documentazione:** Ogni modifica importante va documentata in `guida.md`
- **Design System:** SEMPRE seguire le regole di design sopra definite

