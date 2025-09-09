# 🚀 DEPLOYMENT GUIDE - SCUOLA DI DANZA

**Versione:** 1.0  
**Sistema:** Laravel 12 + Docker + MySQL + Redis  
**Ambiente:** Production Ready  

---

## 📋 **INDICE**

1. [Requisiti Sistema](#requisiti-sistema)
2. [Preparazione Server](#preparazione-server)
3. [Configurazione Database](#configurazione-database)
4. [Deploy Applicazione](#deploy-applicazione)
5. [Configurazione SSL](#configurazione-ssl)
6. [Monitoring & Backup](#monitoring--backup)
7. [Manutenzione](#manutenzione)
8. [Troubleshooting](#troubleshooting)

---

## 🖥️ **REQUISITI SISTEMA**

### **Server Minimo**
- **OS:** Ubuntu 22.04 LTS / CentOS 8+ / Debian 11+
- **RAM:** 4GB (raccomandati 8GB)
- **CPU:** 2 vCPU (raccomandati 4 vCPU)
- **Storage:** 50GB SSD (raccomandati 100GB)
- **Network:** 1Gbps connessione

### **Software Richiesto**
```bash
# Docker & Docker Compose
Docker Engine 24.0+
Docker Compose 2.20+

# SSL Certificate
Let's Encrypt / Certbot
nginx-proxy + acme-companion

# Database
MySQL 8.0+
Redis 7.0+

# Monitoring (opzionale)
Prometheus + Grafana
```

### **Ports da Aprire**
```bash
# Web Traffic
80/tcp   (HTTP)
443/tcp  (HTTPS)

# SSH Management
22/tcp   (SSH)

# Internal Services (localhost only)
3306/tcp (MySQL)
6379/tcp (Redis)
9090/tcp (Prometheus)
3000/tcp (Grafana)
```

---

## ⚙️ **PREPARAZIONE SERVER**

### **1. Update Sistema**
```bash
# Ubuntu/Debian
sudo apt update && sudo apt upgrade -y
sudo apt install curl git unzip software-properties-common

# CentOS/RHEL
sudo yum update -y
sudo yum install curl git unzip
```

### **2. Installare Docker**
```bash
# Installare Docker Engine
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Aggiungere user al gruppo docker
sudo usermod -aG docker $USER

# Installare Docker Compose
sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
sudo chmod +x /usr/local/bin/docker-compose

# Verificare installazione
docker --version
docker-compose --version
```

### **3. Configurare Firewall**
```bash
# UFW (Ubuntu)
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

# Firewalld (CentOS)
sudo firewall-cmd --permanent --add-service=ssh
sudo firewall-cmd --permanent --add-service=http
sudo firewall-cmd --permanent --add-service=https
sudo firewall-cmd --reload
```

### **4. Creare Directory di Deploy**
```bash
# Creare struttura directory
sudo mkdir -p /opt/scuoladanza-production
sudo chown $USER:$USER /opt/scuoladanza-production
cd /opt/scuoladanza-production

# Creare directory per i dati
mkdir -p {storage,backups,logs,ssl}
```

---

## 🗄️ **CONFIGURAZIONE DATABASE**

### **1. Clone Repository**
```bash
cd /opt/scuoladanza-production
git clone https://github.com/emanuelerosato/scuoladanza.git .
git checkout main
```

### **2. Configurare Environment**
```bash
# Copiare template produzione
cp .env.production.example .env.production

# Editare configurazione
nano .env.production
```

### **3. Variabili Essenziali**
```bash
# Application
APP_NAME="Scuola di Danza"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Sicurezza
APP_KEY=                    # Generare con: php artisan key:generate
APP_CIPHER=AES-256-CBC
SESSION_SECURE_COOKIES=true
SANCTUM_STATEFUL_DOMAINS=your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=scuoladanza_production
DB_USERNAME=scuoladanza_user
DB_PASSWORD=STRONG_PASSWORD_HERE
DB_ROOT_PASSWORD=ROOT_PASSWORD_HERE

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=REDIS_PASSWORD_HERE

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.your-provider.com
MAIL_PORT=587
MAIL_USERNAME=noreply@your-domain.com
MAIL_PASSWORD=EMAIL_PASSWORD_HERE
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@your-domain.com

# SSL
APP_DOMAIN=your-domain.com
LETSENCRYPT_EMAIL=admin@your-domain.com

# Backup
AWS_ACCESS_KEY_ID=your_aws_key
AWS_SECRET_ACCESS_KEY=your_aws_secret
AWS_BUCKET=scuoladanza-backups
BACKUP_NOTIFICATION_MAIL=admin@your-domain.com

# Health Check
HEALTH_CHECK_SECRET=your-health-check-secret

# Monitoring
GRAFANA_ADMIN_PASSWORD=strong_grafana_password
```

---

## 🚀 **DEPLOY APPLICAZIONE**

### **1. Build & Deploy**
```bash
cd /opt/scuoladanza-production

# Avviare servizi base
docker-compose -f docker-compose.prod.yml up -d mysql redis

# Attendere che i servizi siano pronti (2-3 minuti)
docker-compose -f docker-compose.prod.yml logs -f mysql

# Avviare applicazione
docker-compose -f docker-compose.prod.yml up -d app

# Verificare status
docker-compose -f docker-compose.prod.yml ps
```

### **2. Configurazione Iniziale**
```bash
# Accedere al container dell'app
docker-compose -f docker-compose.prod.yml exec app bash

# Generare application key
php artisan key:generate

# Eseguire migrazioni
php artisan migrate --force

# Seedare dati iniziali (opzionale)
php artisan db:seed

# Cache ottimizzazioni
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Uscire dal container
exit
```

### **3. Verificare Installazione**
```bash
# Test health check
curl http://localhost/health/simple

# Dovrebbe restituire:
# {"status":"ok","timestamp":"2025-09-09T10:30:00.000000Z","environment":"production"}

# Test dettagliato (con secret)
curl "http://localhost/health/detailed?secret=your-health-check-secret"
```

---

## 🔒 **CONFIGURAZIONE SSL**

### **1. Avviare Proxy e SSL**
```bash
cd /opt/scuoladanza-production

# Configurare domain nell'app
docker-compose -f docker-compose.prod.yml exec app bash
echo "VIRTUAL_HOST=your-domain.com" >> .env.production
echo "LETSENCRYPT_HOST=your-domain.com" >> .env.production
echo "LETSENCRYPT_EMAIL=admin@your-domain.com" >> .env.production
exit

# Riavviare app con nuove variabili
docker-compose -f docker-compose.prod.yml restart app

# Avviare nginx-proxy e Let's Encrypt
docker-compose -f docker-compose.prod.yml up -d nginx-proxy letsencrypt
```

### **2. Verificare SSL**
```bash
# Test SSL certificate
curl -I https://your-domain.com

# Verificare rating SSL
# https://www.ssllabs.com/ssltest/analyze.html?d=your-domain.com
```

---

## 📊 **MONITORING & BACKUP**

### **1. Configurare Backup Automatico**
```bash
# Creare cron job per backup
crontab -e

# Aggiungere: Backup ogni notte alle 2:00
0 2 * * * cd /opt/scuoladanza-production && docker-compose -f docker-compose.prod.yml run --rm backup

# Backup settimanale completo ogni domenica alle 3:00
0 3 * * 0 cd /opt/scuoladanza-production && docker-compose -f docker-compose.prod.yml run --rm backup
```

### **2. Avviare Monitoring (Opzionale)**
```bash
cd /opt/scuoladanza-production

# Avviare Prometheus e Grafana
docker-compose -f docker-compose.prod.yml --profile monitoring up -d

# Accedere a Grafana
# http://your-server-ip:3000
# Username: admin
# Password: [GRAFANA_ADMIN_PASSWORD from .env]
```

### **3. Configurare Log Rotation**
```bash
sudo nano /etc/logrotate.d/scuoladanza

# Contenuto:
/opt/scuoladanza-production/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        docker-compose -f /opt/scuoladanza-production/docker-compose.prod.yml restart app
    endscript
}
```

---

## 🔄 **MANUTENZIONE**

### **1. Update Applicazione**
```bash
cd /opt/scuoladanza-production

# Backup before update
docker-compose -f docker-compose.prod.yml run --rm backup

# Pull latest code
git pull origin main

# Update containers
docker-compose -f docker-compose.prod.yml pull
docker-compose -f docker-compose.prod.yml up -d

# Run migrations if needed
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force

# Clear caches
docker-compose -f docker-compose.prod.yml exec app php artisan optimize:clear
docker-compose -f docker-compose.prod.yml exec app php artisan config:cache

# Security audit
docker-compose -f docker-compose.prod.yml exec app php artisan security:audit
```

### **2. Monitoring Comandi Utili**
```bash
# Status containers
docker-compose -f docker-compose.prod.yml ps

# View logs
docker-compose -f docker-compose.prod.yml logs -f app
docker-compose -f docker-compose.prod.yml logs -f mysql

# Resource usage
docker stats

# Database backup manuale
docker-compose -f docker-compose.prod.yml run --rm backup

# Security audit
docker-compose -f docker-compose.prod.yml exec app php artisan security:audit --report
```

### **3. Performance Optimization**
```bash
# Optimize production
docker-compose -f docker-compose.prod.yml exec app php artisan optimize:for-production

# Clear old logs (older than 30 days)
find /opt/scuoladanza-production/logs -name "*.log" -mtime +30 -delete

# Clear old backups (older than 30 days)
find /opt/scuoladanza-production/backups -name "*.sql*" -mtime +30 -delete
```

---

## 🛠️ **TROUBLESHOOTING**

### **Common Issues**

#### **1. Container Non Parte**
```bash
# Check logs
docker-compose -f docker-compose.prod.yml logs app

# Check system resources
free -h
df -h

# Restart services
docker-compose -f docker-compose.prod.yml restart
```

#### **2. Database Connection Error**
```bash
# Check MySQL status
docker-compose -f docker-compose.prod.yml logs mysql

# Test database connection
docker-compose -f docker-compose.prod.yml exec mysql mysql -u root -p

# Reset database container
docker-compose -f docker-compose.prod.yml down mysql
docker volume rm scuoladanza_mysql_data
docker-compose -f docker-compose.prod.yml up -d mysql
```

#### **3. SSL Certificate Issues**
```bash
# Check nginx-proxy logs
docker-compose -f docker-compose.prod.yml logs nginx-proxy letsencrypt

# Force certificate renewal
docker-compose -f docker-compose.prod.yml exec letsencrypt /app/force_renew

# Check certificate expiration
echo | openssl s_client -connect your-domain.com:443 2>/dev/null | openssl x509 -noout -dates
```

#### **4. Performance Issues**
```bash
# Check resource usage
docker stats

# Check slow queries
docker-compose -f docker-compose.prod.yml exec mysql mysql -e "SHOW PROCESSLIST;"

# Optimize database
docker-compose -f docker-compose.prod.yml exec app php artisan optimize:for-production
```

### **Emergency Procedures**

#### **1. Maintenance Mode**
```bash
# Enable maintenance mode
docker-compose -f docker-compose.prod.yml exec app php artisan down --secret=your-emergency-secret

# Disable maintenance mode
docker-compose -f docker-compose.prod.yml exec app php artisan up
```

#### **2. Restore from Backup**
```bash
# Stop application
docker-compose -f docker-compose.prod.yml stop app

# Restore database
docker-compose -f docker-compose.prod.yml exec mysql mysql -u root -p scuoladanza_production < /var/backups/backup_YYYYMMDD_HHMMSS.sql

# Start application
docker-compose -f docker-compose.prod.yml start app
```

---

## 📞 **SUPPORTO**

**Repository:** https://github.com/emanuelerosato/scuoladanza  
**Issues:** https://github.com/emanuelerosato/scuoladanza/issues  
**Documentazione:** `/docs/` directory  

### **Log Files Locations**
- **Application:** `/opt/scuoladanza-production/logs/laravel.log`
- **Nginx:** `/var/log/nginx/access.log`, `/var/log/nginx/error.log`
- **MySQL:** `/var/log/mysql/error.log`, `/var/log/mysql/slow-query.log`
- **Redis:** `docker logs scuoladanza_redis`

### **Health Check URLs**
- **Simple:** `https://your-domain.com/health/simple`
- **Detailed:** `https://your-domain.com/health/detailed?secret=your-secret`
- **Metrics:** `https://your-domain.com/health/metrics?secret=your-secret`

---

**📝 Ultima modifica:** 2025-09-09  
**👨‍💻 Versione:** Production Ready v1.0