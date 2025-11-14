# 🚀 Guida Rapida Deploy VPS

## Costi Mensili
- Aruba: €1/mese (€12/anno dominio + email)
- DigitalOcean VPS: €5.50/mese
- **TOTALE: €6.50/mese**

## Setup (30 minuti)

### 1. Acquista Servizi

**Aruba (dominio + email):**
1. Vai su aruba.it
2. Registra dominio + "Posta Elettronica Hosting"
3. Crea casella: admin@tuodominio.it
4. Annota password email

**DigitalOcean (VPS):**
1. Vai su digitalocean.com
2. Crea Droplet: Ubuntu 22.04, Basic $6/mese
3. Scegli datacenter Frankfurt/Amsterdam
4. Annota IP e password root (via email)

### 2. Configura DNS su Aruba

Pannello Aruba → DNS Management:
```
Tipo: A    Nome: @     Valore: IP_VPS    TTL: 3600
Tipo: A    Nome: www   Valore: IP_VPS    TTL: 3600
```
Aspetta 1-2 ore per propagazione.

### 3. Setup VPS

```bash
# Connetti SSH
ssh root@IP_VPS

# Download e esegui setup (15 min)
wget https://raw.githubusercontent.com/emanuelerosato/danzafacile/deploy/vps-setup/deployment/scripts/setup-server.sh
chmod +x setup-server.sh
./setup-server.sh

# Configura database MySQL
mysql -u root
CREATE DATABASE danzafacile;
CREATE USER 'danzafacile'@'localhost' IDENTIFIED BY 'PASSWORD_FORTE';
GRANT ALL ON danzafacile.* TO 'danzafacile'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 4. Deploy Applicazione

```bash
# Download e esegui deploy (10 min)
wget https://raw.githubusercontent.com/emanuelerosato/danzafacile/deploy/vps-setup/deployment/scripts/deploy-first-time.sh
chmod +x deploy-first-time.sh
./deploy-first-time.sh
```

Lo script chiederà:
- Dominio
- Password database
- Email e password Aruba

### 5. ✅ Sito Online!

Vai su: `https://tuodominio.it`

## Deploy Aggiornamenti

```bash
# Sul tuo Mac
git push origin main

# Sul VPS
ssh root@IP_VPS
cd /var/www/danzafacile/deployment/scripts
./deploy.sh
```

## Script Disponibili

| Script | Uso |
|--------|-----|
| `setup-server.sh` | Setup iniziale VPS (1x) |
| `deploy-first-time.sh` | Primo deploy (1x) |
| `deploy.sh` | Deploy aggiornamenti |
| `backup.sh` | Backup manuale |
| `monitor.sh` | Verifica sistema |
| `update-system.sh` | Update sicurezza |

## Troubleshooting

**Sito lento:**
```bash
systemctl restart nginx php8.2-fpm
```

**Errore 500:**
```bash
tail -50 /var/www/danzafacile/storage/logs/laravel.log
```

**Email non partono:**
Verifica credenziali Aruba in `.env`

---

Per guida completa: vedi `DEPLOY_VPS_SETUP.md`
