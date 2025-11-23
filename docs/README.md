# 📚 DanzaFacile - Documentation

Documentazione completa del progetto DanzaFacile, organizzata per categoria.

**Totale documenti:** 28 file organizzati in 6 categorie
**Ultimo aggiornamento:** 2025-11-23

---

## 📖 Indice Rapido

| Categoria | File | Descrizione |
|-----------|------|-------------|
| 📄 **Root** | 3 file | Guide principali e checklist |
| 🔌 **API** | 3 file | Documentazione API REST |
| 🚀 **Deployment** | 2 file | Guide deploy e VPS |
| 🔒 **Security** | 6 file | Audit e fix security |
| 📱 **Flutter** | 5 file | Progress app mobile |
| 🔔 **Push Notifications** | 4 file | Sistema notifiche |
| ⚙️ **Features** | 5 file | Feature specifiche |

---

## 📄 Documentazione Root

### Guide Principali
- **[guida.md](guida.md)** (37KB) - 📚 Guida completa progetto in italiano
- **[PRE_PRODUCTION_CHECKLIST.md](PRE_PRODUCTION_CHECKLIST.md)** (10KB) - ✅ Checklist pre-produzione

---

## 🔌 API Documentation (api/)

### Endpoint Reference
- **[API_ENDPOINTS.md](api/API_ENDPOINTS.md)** (8.5KB) - Lista endpoint backend
- **[API_ENDPOINTS_REFERENCE.md](api/API_ENDPOINTS_REFERENCE.md)** (10KB) - Reference rapida API
- **[FLUTTER_API_DOCUMENTATION.md](api/FLUTTER_API_DOCUMENTATION.md)** (21KB) - API per Flutter app

**Base URL Production:** `https://www.danzafacile.it/api/mobile/v1`

---

## 🚀 Deployment (deployment/)

### Guide Deploy VPS
- **[DEPLOYMENT.md](deployment/DEPLOYMENT.md)** (11KB) - Guida deploy completa VPS
- **[VPS_BACKEND_CHANGES.md](deployment/VPS_BACKEND_CHANGES.md)** (5.6KB) - Change log backend

**VPS Production:** `ssh root@157.230.114.252`
**Directory:** `/var/www/danzafacile`

---

## 🔒 Security (security/)

### Audit & Reports
- **[SECURITY_AUDIT_REPORT_2025-11-22.md](security/SECURITY_AUDIT_REPORT_2025-11-22.md)** (22KB) - ⭐ Audit completo (LATEST)
- **[SECURITY_FIXES_APPLIED.md](security/SECURITY_FIXES_APPLIED.md)** (8.1KB) - Fix applicati
- **[CSP_FIX_APPLIED.md](security/CSP_FIX_APPLIED.md)** (4.8KB) - Content Security Policy fix

### SSL/TLS
- **[SSL_TLS_AUDIT_REPORT.md](security/SSL_TLS_AUDIT_REPORT.md)** (20KB) - Audit SSL/TLS completo
- **[SSL_IMPLEMENTATION_SUMMARY.md](security/SSL_IMPLEMENTATION_SUMMARY.md)** (10KB) - Implementazione SSL
- **[SSL_EMAIL_NOTIFICATIONS.md](security/SSL_EMAIL_NOTIFICATIONS.md)** (14KB) - Email auto-renewal

**Security Grade:** A (92/100) - CSP + HSTS + Headers
**SSL:** Let's Encrypt (auto-renewal attivo)

---

## 📱 Flutter App (flutter/)

### Strategy & Planning
- **[FLUTTER_APP_STRATEGY.md](flutter/FLUTTER_APP_STRATEGY.md)** (15KB) - Strategia sviluppo app
- **[FLUTTER_PROJECT_INIT.md](flutter/FLUTTER_PROJECT_INIT.md)** (17KB) - Setup iniziale progetto

### Progress Tracking
- **[FLUTTER_WEEK2_PROGRESS.md](flutter/FLUTTER_WEEK2_PROGRESS.md)** (10KB) - Progress Week 2
- **[FLUTTER_WEEK2_DAY1_FINAL.md](flutter/FLUTTER_WEEK2_DAY1_FINAL.md)** (20KB) - Day 1 Week 2 report
- **[WEEK_1_COMPLETE_SUMMARY.md](flutter/WEEK_1_COMPLETE_SUMMARY.md)** (11KB) - Summary Week 1

**Status:** 🚧 In Development (Week 2)
**Repository:** `https://github.com/emanuelerosato/danzafacile-app.git`

---

## 🔔 Push Notifications (push-notifications/)

### Setup & Implementation
- **[PUSH_NOTIFICATIONS_GUIDE.md](push-notifications/PUSH_NOTIFICATIONS_GUIDE.md)** (29KB) - ⭐ Guida completa
- **[FIREBASE_SETUP_GUIDE.md](push-notifications/FIREBASE_SETUP_GUIDE.md)** (6.3KB) - Setup Firebase
- **[PUSH_NOTIFICATIONS_IMPLEMENTATION_PLAN.md](push-notifications/PUSH_NOTIFICATIONS_IMPLEMENTATION_PLAN.md)** (42KB) - Piano implementazione
- **[PUSH_NOTIFICATIONS_PROGRESS.md](push-notifications/PUSH_NOTIFICATIONS_PROGRESS.md)** (17KB) - Progress tracking

**Status:** ✅ Implementato e funzionante
**Firebase:** kreait/firebase-php 7.23.0
**Cron Job:** Check ogni 15 minuti

---

## ⚙️ Features (features/)

### Feature Specifiche
- **[EMAIL-FUNNEL-SYSTEM.md](features/EMAIL-FUNNEL-SYSTEM.md)** (11KB) - Sistema email marketing
- **[PAYMENTS_ANALYSIS.md](features/PAYMENTS_ANALYSIS.md)** (13KB) - Analisi pagamenti
- **[galleries-audit-report.md](features/galleries-audit-report.md)** (11KB) - Audit gallerie media
- **[storage-implementation-roadmap.md](features/storage-implementation-roadmap.md)** (69KB) - Roadmap storage system
- **[storage-limits-brainstorming.md](features/storage-limits-brainstorming.md)** (14KB) - Limiti storage

---

## 🔍 Quick Reference

### Per Sviluppatori
```bash
# Istruzioni Claude AI
cat ../CLAUDE.md

# Getting started
cat ../README.md

# API reference
cat api/FLUTTER_API_DOCUMENTATION.md

# Push notifications
cat push-notifications/PUSH_NOTIFICATIONS_GUIDE.md
```

### Per Deploy
```bash
# Deploy guide
cat deployment/DEPLOYMENT.md

# Security audit
cat security/SECURITY_AUDIT_REPORT_2025-11-22.md

# CSP configuration
cat security/CSP_FIX_APPLIED.md
```

### Per Flutter Development
```bash
# App strategy
cat flutter/FLUTTER_APP_STRATEGY.md

# API endpoints
cat api/FLUTTER_API_DOCUMENTATION.md

# Firebase setup
cat push-notifications/FIREBASE_SETUP_GUIDE.md
```

---

## 📊 Struttura Completa

```
docs/
├── README.md (questo file)
├── guida.md (37KB - guida completa IT)
├── PRE_PRODUCTION_CHECKLIST.md (10KB)
│
├── api/ (3 file - 40KB)
│   ├── API_ENDPOINTS.md
│   ├── API_ENDPOINTS_REFERENCE.md
│   └── FLUTTER_API_DOCUMENTATION.md
│
├── deployment/ (2 file - 17KB)
│   ├── DEPLOYMENT.md
│   └── VPS_BACKEND_CHANGES.md
│
├── security/ (6 file - 79KB)
│   ├── SECURITY_AUDIT_REPORT_2025-11-22.md (LATEST)
│   ├── SECURITY_FIXES_APPLIED.md
│   ├── CSP_FIX_APPLIED.md
│   ├── SSL_TLS_AUDIT_REPORT.md
│   ├── SSL_IMPLEMENTATION_SUMMARY.md
│   └── SSL_EMAIL_NOTIFICATIONS.md
│
├── flutter/ (5 file - 73KB)
│   ├── FLUTTER_APP_STRATEGY.md
│   ├── FLUTTER_PROJECT_INIT.md
│   ├── FLUTTER_WEEK2_PROGRESS.md
│   ├── FLUTTER_WEEK2_DAY1_FINAL.md
│   └── WEEK_1_COMPLETE_SUMMARY.md
│
├── push-notifications/ (4 file - 94KB)
│   ├── PUSH_NOTIFICATIONS_GUIDE.md (guida principale)
│   ├── FIREBASE_SETUP_GUIDE.md
│   ├── PUSH_NOTIFICATIONS_IMPLEMENTATION_PLAN.md
│   └── PUSH_NOTIFICATIONS_PROGRESS.md
│
└── features/ (5 file - 118KB)
    ├── EMAIL-FUNNEL-SYSTEM.md
    ├── PAYMENTS_ANALYSIS.md
    ├── galleries-audit-report.md
    ├── storage-implementation-roadmap.md
    └── storage-limits-brainstorming.md
```

**Totale:** 28 file, ~500KB di documentazione organizzata

---

## 🔗 Links Esterni

### Repository
- Backend: https://github.com/emanuelerosato/danzafacile.git
- Flutter: https://github.com/emanuelerosato/danzafacile-app.git

### Production
- Web: https://www.danzafacile.it
- API: https://www.danzafacile.it/api/mobile/v1

### VPS
- SSH: `ssh root@157.230.114.252`
- Directory: `/var/www/danzafacile`

---

**Versione:** 2.0.0
**Ultimo aggiornamento:** 2025-11-23
**Maintainer:** Claude Code AI
