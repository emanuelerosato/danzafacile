# 🚀 Deployment VPS - Scuola di Danza

**Branch:** `deploy/vps-setup`
**Status:** In Sviluppo - Brainstorming

---

## 📋 Architettura Finale

### **Stack Tecnologico:**
```
┌─────────────────────────────────────────────┐
│ ARUBA                                       │
│ • Dominio: scuoladidanza.it                │
│ • Email: admin@scuoladidanza.it            │
│ • DNS Management                            │
│ • SMTP Server                               │
│ Costo: €12/anno (€1/mese)                  │
└─────────────────────────────────────────────┘
                    ↓ DNS Record A
┌─────────────────────────────────────────────┐
│ DIGITALOCEAN DROPLET VPS                   │
│                                             │
│ • Ubuntu 22.04 LTS                         │
│ • Nginx (Web Server)                       │
│ • PHP 8.2-FPM                              │
│ • MySQL 8.0                                │
│ • Redis 7.0                                │
│ • Let's Encrypt SSL                        │
│                                             │
│ Specs: 1vCPU, 1GB RAM, 25GB SSD           │
│ Costo: $6/mese (€5.50/mese)                │
└─────────────────────────────────────────────┘
                    ↑ API REST
┌─────────────────────────────────────────────┐
│ FLUTTER APP (Futuro)                       │
│ • iOS / Android                            │
│ • Sanctum Authentication                   │
└─────────────────────────────────────────────┘
```

**COSTO TOTALE: ~€6.50/mese**

---

## 📁 Struttura

```
deployment/
├── README.md              # Questo file
├── scripts/               # Script bash automatici
│   ├── setup-server.sh   # Setup iniziale VPS
│   ├── deploy-first-time.sh  # Primo deploy
│   ├── deploy.sh         # Deploy aggiornamenti
│   ├── backup.sh         # Backup automatico
│   ├── monitor.sh        # Health check
│   └── update-system.sh  # Update sicurezza
├── config/               # Configurazioni
│   └── .env.production.template
└── docs/                # Documentazione
    └── GUIDA_COMPLETA.md
```

Per iniziare: leggi `docs/GUIDA_COMPLETA.md`
