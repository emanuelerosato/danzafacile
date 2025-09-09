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

## Note aggiuntive

- **Separazione ruoli:** Rispetta sempre Super Admin, Admin e User
- **Modularità:** Mantieni codice pronto per futura integrazione Flutter
- **Lingua:** Tutti gli output devono essere in italiano
- **Documentazione:** Ogni modifica importante va documentata in `guida.md`

