# GUIDA.md - Tracciamento Modifiche Progetto

Questo file traccia tutte le modifiche importanti apportate al progetto **Scuola di Danza**.

---

## 📝 Log Modifiche

### 2025-09-09 - Sistema Backend Completo Implementato

**Cosa è stato fatto:**
- ✅ **Database completo**: 8 migrazioni per tutte le entità (schools, users, courses, enrollments, payments, documents, media_galleries, media_items)
- ✅ **Modelli Eloquent**: 8 modelli con relazioni complete, scopes, accessors, mutators
- ✅ **Controller RESTful**: 13 controller organizzati per ruolo (SuperAdmin, Admin, Student, Shared)
- ✅ **Sistema autenticazione**: Laravel Breeze integrato con middleware custom per ruoli
- ✅ **Template Blade**: Layout responsive con dashboard role-based, componenti riutilizzabili
- ✅ **Docker ambiente**: Sistema completamente funzionante con Sail (MySQL, Redis, Mailpit, etc.)
- ✅ **Dati di test**: Seeder con scuola, admin, studenti e corsi di esempio

**File principali creati:**
- **Migrazioni**: `database/migrations/2024_09_08_*` (8 file)
- **Modelli**: `app/Models/` (School, Course, CourseEnrollment, Payment, Document, MediaGallery, MediaItem, User esteso)
- **Controller**: `app/Http/Controllers/` (3 cartelle: SuperAdmin, Admin, Student, Shared)
- **Middleware**: `RoleMiddleware.php`, `SchoolOwnershipMiddleware.php`
- **Rotte**: `routes/web.php` (60+ rotte organizzate per ruolo)
- **Template**: `resources/views/` (layout app/guest, dashboard role-based, componenti)
- **Seeder**: `DatabaseSeeder.php` con dati completi

**Dettagli tecnici:**
- **Stack**: Laravel 12 + PHP 8.2 + MySQL + Redis + Tailwind CSS + Alpine.js + Vite
- **Architettura**: Role-based (super_admin, admin, user) con isolamento dati per scuola
- **Sicurezza**: Middleware per ruoli e ownership, validazione form, CSRF protection
- **UI/UX**: Design moderno tema "scuola di danza", responsive, sidebar collassabile
- **Performance**: Indici database, eager loading relazioni, asset optimization

**Sistema operativo:**
- URL: http://localhost:8089
- Super Admin: superadmin@scuoladanza.it / password
- Admin: admin@eleganza.it / password  
- Studenti: studente1@example.com to studente5@example.com / password

**Status:** ✅ **COMPLETATO** - Backend completamente funzionante
**Commit:** *Da eseguire*

---

### 2025-09-08 - Inizializzazione Progetto

**Cosa è stato fatto:**
- ✅ Creazione file `CLAUDE.md` con configurazione completa per Claude Code
- ✅ Aggiornamento `CLAUDE.md` con sezioni specifiche per workflow Git/GitHub
- ✅ Creazione file `guida.md` per tracciamento modifiche

**File modificati/creati:**
- `CLAUDE.md` (creato e migliorato)
- `guida.md` (creato)

**Dettagli tecnici:**
- Configurato workflow obbligatorio per Git: pull → modifica → commit → push
- Definiti ruoli utente: Super Admin, Admin, User
- Specificato stack tecnologico: Laravel 12 + Vite + Docker Sail
- Repository GitHub: https://github.com/emanuelerosato/scuoladidanza

**Status:** ✅ Completato
**Commit:** *Da eseguire*

---

## 🎯 Prossimi Task

- [ ] Inizializzazione repository Git (se necessario)
- [ ] Prima commit e push su GitHub
- [ ] Setup ambiente sviluppo con Sail
- [ ] Configurazione database e migrazioni iniziali

---

## 📋 Template per Nuove Modifiche

```
### YYYY-MM-DD - [Titolo Modifica]

**Cosa è stato fatto:**
- ✅ [Descrizione 1]
- ✅ [Descrizione 2]

**File modificati/creati:**
- `path/file.ext` (azione)

**Dettagli tecnici:**
- [Dettagli importanti]

**Status:** ✅ Completato / 🔄 In corso / ❌ Fallito
**Commit:** [Hash commit o messaggio]
```

---

## 🔗 Link Utili

- **Repository:** https://github.com/emanuelerosato/scuoladidanza
- **Laravel Docs:** https://laravel.com/docs/12.x
- **Docker Sail:** https://laravel.com/docs/12.x/sail