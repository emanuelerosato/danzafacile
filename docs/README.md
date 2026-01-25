# 📚 DanzaFacile - Documentation

Documentazione completa del progetto DanzaFacile, organizzata per categoria.

**Totale documenti:** 35 file organizzati in 8 categorie
**Ultimo aggiornamento:** 2026-01-25

---

## 📖 Indice Rapido

| Categoria | File | Descrizione |
|-----------|------|-------------|
| 🏛️ **Architecture** | 3 file | Architettura sistema e decisioni |
| 📄 **Root** | 3 file | Guide principali e checklist |
| 🔌 **API** | 3 file | Documentazione API REST |
| 🚀 **Deployment** | 2 file | Guide deploy e VPS |
| 🔒 **Security** | 6 file | Audit e fix security |
| 📱 **Flutter** | 5 file | Progress app mobile |
| 🔔 **Push Notifications** | 4 file | Sistema notifiche |
| ⚙️ **Features** | 5 file | Feature specifiche |
| 📋 **Tasks** | 2 file + archive | Bug tracking e implementation |

---

## 🏛️ Architecture Documentation (NEW!)

### Core Architecture Guides

- **[ARCHITECTURE.md](ARCHITECTURE.md)** (72KB) - 🏛️ **System Architecture Overview**
  - Technology stack completo
  - Layered architecture (5 layers)
  - Design patterns utilizzati
  - 6 Architecture Decision Records (ADR)
  - Data flow diagrams
  - External integrations
  - Security & scalability

- **[SERVICES_MAP.md](SERVICES_MAP.md)** (60KB) - 🗺️ **Service Layer Mapping**
  - 11 servizi business logic mappati
  - Key methods & signatures
  - Dependencies graph
  - Usage examples
  - Service creation guide

- **[MULTI_TENANT_GUIDE.md](MULTI_TENANT_GUIDE.md)** (48KB) - 🏢 **Multi-Tenant Architecture**
  - School-based data isolation
  - HasSchoolScope trait implementation
  - Best practices & patterns
  - Migration checklist
  - Testing isolation
  - Common pitfalls & troubleshooting

**Why these docs?**
Questi 3 file risolvono il problema di **context management** per AI coding assistants, fornendo architettura HIGH-LEVEL invece di massive function mapping (2,350 righe vs 20K+ mapping file).

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

## 📋 Tasks & Roadmaps (tasks/)

### Active Tasks
- **[BUG_FIXES_ROADMAP.md](tasks/BUG_FIXES_ROADMAP.md)** (60KB) - ✅ 11/11 Completed (100%)
- **[TASK_11_STORAGE_QUOTA_IMPLEMENTATION.md](tasks/TASK_11_STORAGE_QUOTA_IMPLEMENTATION.md)** (104KB) - ✅ Completed & Deployed

**Status:** Tutti i task completati al 100%
**Last Update:** 2026-01-25

### Archive
Legacy task files (STAFF_*.md) sono in `archive/` per riferimento storico.

---

## 🔍 Quick Reference

### Per Sviluppatori

#### Architecture & Patterns
```bash
# System architecture overview
cat docs/ARCHITECTURE.md

# Service layer mapping
cat docs/SERVICES_MAP.md

# Multi-tenant implementation
cat docs/MULTI_TENANT_GUIDE.md

# Istruzioni Claude AI
cat ../CLAUDE.md

# Getting started
cat ../README.md
```

#### API Development
```bash
# API reference
cat docs/api/FLUTTER_API_DOCUMENTATION.md

# Push notifications
cat docs/push-notifications/PUSH_NOTIFICATIONS_GUIDE.md
```

### Per Deploy
```bash
# Deploy guide
cat docs/deployment/DEPLOYMENT.md

# Security audit
cat docs/security/SECURITY_AUDIT_REPORT_2025-11-22.md

# CSP configuration
cat docs/security/CSP_FIX_APPLIED.md
```

### Per Flutter Development
```bash
# App strategy
cat docs/flutter/FLUTTER_APP_STRATEGY.md

# API endpoints
cat docs/api/FLUTTER_API_DOCUMENTATION.md

# Firebase setup
cat docs/push-notifications/FIREBASE_SETUP_GUIDE.md
```

---

## 📊 Struttura Completa

```
docs/
├── README.md (questo file - 6.9KB)
│
├── ARCHITECTURE.md (72KB) ⭐ NEW
├── SERVICES_MAP.md (60KB) ⭐ NEW
├── MULTI_TENANT_GUIDE.md (48KB) ⭐ NEW
│
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
├── features/ (5 file - 118KB)
│   ├── EMAIL-FUNNEL-SYSTEM.md
│   ├── PAYMENTS_ANALYSIS.md
│   ├── galleries-audit-report.md
│   ├── storage-implementation-roadmap.md
│   └── storage-limits-brainstorming.md
│
├── tasks/ (2 file + README)
│   ├── README.md
│   ├── BUG_FIXES_ROADMAP.md (60KB)
│   └── TASK_11_STORAGE_QUOTA_IMPLEMENTATION.md (104KB)
│
└── archive/ (6 file - legacy)
    ├── STAFF_AJAX_JSON_FIX.md
    ├── STAFF_CHECKBOX_FIX_FINALE.md
    ├── STAFF_CHECKBOX_SELECTION_FIX.md
    ├── STAFF_DELETE_FIX_EVIDENZE.md
    ├── STAFF_MODULE_FIXES_2025-12-06.md
    └── STAFF_SHOW_500_FIX.md
```

**Totale:** 35 file, ~700KB di documentazione organizzata

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

## 🎯 Documentation Philosophy

### Design Principles

1. **High-Level Over Low-Level**: Documentare DECISIONI e ARCHITETTURA, non singole funzioni
2. **Context-Aware**: Fornire contesto sufficiente senza drowning in details
3. **Maintenance-Friendly**: Documentation che invecchia bene (95% accuracy dopo 6+ mesi)
4. **Developer-Centric**: Rispondere a "WHY" prima di "WHAT" e "HOW"
5. **AI-Friendly**: Ottimizzato per AI coding assistants (Claude Code, GitHub Copilot)

### Documentation Layers

```
Layer 1: ARCHITECTURE.md        → WHY decisions were made (ADR style)
Layer 2: SERVICES_MAP.md        → WHAT services exist & WHERE to use them
Layer 3: MULTI_TENANT_GUIDE.md  → HOW to implement features correctly
Layer 4: Feature-specific docs  → DEEP DIVE on specific domains
```

### Update Frequency

| Doc Type | Update Frequency | Owner |
|----------|------------------|-------|
| Architecture core (3 files) | 6-12 months | Tech Lead |
| Feature-specific | Per feature release | Feature developer |
| API docs | Per API change | Backend team |
| Tasks/Roadmaps | Weekly | Project Manager |

---

**Versione:** 3.0.0 (Restructured with Architecture docs)
**Ultimo aggiornamento:** 2026-01-25
**Maintainer:** Claude Code AI + DanzaFacile Team
