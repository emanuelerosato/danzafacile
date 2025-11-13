# 🚀 Deployment VPS - Scuola di Danza

**Branch:** `deploy/vps-setup`
**Status:** ✅ Ready for Production

---

## 📋 Architettura Finale

### **Stack Tecnologico:**
```
┌─────────────────────────────────────────────┐
│ ARUBA (€12/anno = €1/mese)                 │
│ • Dominio .it (es: danzafacile.it)        │
│ • 5 caselle email professionali            │
│ • DNS Management                            │
│ • SMTP Server                               │
└─────────────────────────────────────────────┘
                    ↓ DNS Record A
┌─────────────────────────────────────────────┐
│ DIGITALOCEAN VPS (€5.50/mese)              │
│ • Ubuntu 22.04 LTS / 25.10                 │
│ • Nginx (Web Server)                       │
│ • PHP 8.2-8.3 (auto-detect)                │
│ • MySQL 8.0-9.0                            │
│ • Redis 7.0                                │
│ • Let's Encrypt SSL                        │
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

## ✅ Compatibilità

Gli script sono compatibili con:

- ✅ **Ubuntu 25.10** (testato) - Rileva automaticamente PHP 8.3
- ✅ **Ubuntu 24.04 LTS** - Supporto PHP 8.2/8.3  
- ✅ **Ubuntu 22.04 LTS** - Richiede PPA ondrej/php per PHP 8.2

**Auto-detection PHP:** Gli script rilevano automaticamente la versione PHP disponibile nei repository e configurano tutto di conseguenza.

---

## 📁 Struttura

```
deployment/
├── README.md              # Questo file
├── scripts/               # Script bash automatici
│   ├── setup-server.sh   # Setup iniziale VPS (✅ Ubuntu 25.10)
│   ├── deploy-first-time.sh  # Primo deploy (✅ Ubuntu 25.10)
│   ├── deploy.sh         # Deploy aggiornamenti (✅ Ubuntu 25.10)
│   ├── backup.sh         # Backup automatico
│   ├── monitor.sh        # Health check
│   └── update-system.sh  # Update sicurezza
├── config/               # Configurazioni
│   └── .env.production.template
└── docs/                # Documentazione
    └── GUIDA_RAPIDA.md
```

---

## 🚀 Quick Start

### **Sul VPS (Ubuntu 25.10):**

```bash
# Setup VPS (15 min)
wget https://raw.githubusercontent.com/emanuelerosato/scuoladidanza/deploy/vps-setup/deployment/scripts/setup-server.sh
chmod +x setup-server.sh
./setup-server.sh

# Configura database
mysql -u root
CREATE DATABASE scuoladidanza;
CREATE USER 'scuoladidanza'@'localhost' IDENTIFIED BY 'PASSWORD_FORTE';
GRANT ALL ON scuoladidanza.* TO 'scuoladidanza'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Deploy app (10 min)
wget https://raw.githubusercontent.com/emanuelerosato/scuoladidanza/deploy/vps-setup/deployment/scripts/deploy-first-time.sh
chmod +x deploy-first-time.sh
./deploy-first-time.sh
```

**✅ Sito online!** `https://tuodominio.it`

---

Per guida completa: leggi `docs/GUIDA_RAPIDA.md`
