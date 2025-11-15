# 🆘 Disaster Recovery - Danza Facile

**Server**: 157.230.114.252 (danzafacile)
**Data Configurazione**: 2025-11-15
**Tempo Ripristino Stimato**: 15-30 minuti
**Status**: ✅ Backup completo attivo

---

## 📋 Cosa Viene Backuppato

### ✅ Backup COMPLETO (ogni 3 ore)

Il sistema esegue backup completi che includono:

| Componente | Descrizione | Dimensione ~  | Critico |
|------------|-------------|---------------|---------|
| **Database MySQL** | Tutti i dati (studenti, corsi, pagamenti, documenti) | 27 KB | ✅ CRITICO |
| **Files Utenti** | Upload (documenti, foto, gallerie) | 4 KB | ✅ CRITICO |
| **File .env** | Configurazione completa (password, API keys) | 1.3 KB | ✅ CRITICO |
| **Nginx Config** | Configurazione web server | 1.6 KB | ⚠️ IMPORTANTE |
| **SSL Certificates** | Certificati HTTPS Let's Encrypt | 690 B | ⚠️ IMPORTANTE |
| **Codice Laravel** | App, routes, controllers, views, migrations | 1.4 MB | ⚠️ IMPORTANTE* |

**\*Nota**: Il codice Laravel è anche su GitHub, ma il backup include eventuali modifiche non committate.

### 📅 Frequenza Backup

- **Ogni 3 ore**: 00:00, 03:00, 06:00, 09:00, 12:00, 15:00, 18:00, 21:00
- **8 backup al giorno**
- **Retention**: 7 giorni (56 backup totali disponibili)
- **Perdita dati massima**: 3 ore
- **Storage**: Locale + Google Drive (ridondanza)

---

## ☁️ Dove Sono i Backup

### 1. **Server Locale**
```bash
/var/backups/danzafacile/
```

**Esempio contenuto:**
```
db_20251115_144438.sql.gz        # Database compresso
files_20251115_144438.tar.gz     # Files utenti
env_20251115_144438.txt          # Configurazione .env
nginx_20251115_144438.conf       # Nginx config
ssl_20251115_144438.tar.gz       # Certificati SSL
laravel_20251115_144438.tar.gz   # Codice Laravel
```

### 2. **Google Drive** (Backup remoto sicuro)
```
Google Drive → danzafacile-backups/
```

**Accesso:**
- Via rclone sul server
- Via web interface Google Drive
- Tutti i file sono sincronizzati automaticamente

---

## 🔄 Procedura Disaster Recovery

### Scenario: Server Completamente Distrutto

**Tempo necessario**: 15-30 minuti
**Prerequisiti**: Nuovo server Ubuntu 22.04/24.04, accesso root

### Step 1: Setup Nuovo Server (5 minuti)

```bash
# 1. Connettiti al nuovo server
ssh root@NUOVO_IP

# 2. Aggiorna sistema
apt update && apt upgrade -y

# 3. Installa dipendenze base
apt install -y nginx mysql-server php8.4 php8.4-fpm php8.4-mysql \
    php8.4-curl php8.4-mbstring php8.4-xml php8.4-zip \
    redis-server composer rclone unzip git
```

### Step 2: Recupera Backup da Google Drive (3 minuti)

```bash
# 1. Configura rclone per Google Drive
rclone config
# Segui wizard per configurare Google Drive (nome: gdrive)

# 2. Scarica backup più recente
mkdir -p /var/backups/danzafacile
rclone copy gdrive:danzafacile-backups /var/backups/danzafacile

# 3. Trova backup più recente
cd /var/backups/danzafacile
ls -lht | head -10

# Identifica timestamp più recente (es: 20251115_144438)
export BACKUP_DATE=20251115_144438
```

### Step 3: Ripristina Database (2 minuti)

```bash
# 1. Crea database
mysql -u root -p
CREATE DATABASE danzafacile;
CREATE USER 'danzafacile'@'localhost' IDENTIFIED BY 'PASSWORD_DAL_ENV';
GRANT ALL PRIVILEGES ON danzafacile.* TO 'danzafacile'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# 2. Ripristina database
gunzip < db_${BACKUP_DATE}.sql.gz | mysql -u root -p danzafacile

# 3. Verifica
mysql -u danzafacile -p danzafacile -e "SHOW TABLES;"
```

### Step 4: Ripristina Codice Laravel (5 minuti)

```bash
# 1. Crea directory applicazione
mkdir -p /var/www/danzafacile

# 2. Estrai codice Laravel
tar -xzf laravel_${BACKUP_DATE}.tar.gz -C /var/www/danzafacile

# 3. Ripristina .env
cp env_${BACKUP_DATE}.txt /var/www/danzafacile/.env

# 4. Ripristina files utenti
tar -xzf files_${BACKUP_DATE}.tar.gz -C /var/www/danzafacile/storage/app/

# 5. Installa dipendenze PHP
cd /var/www/danzafacile
composer install --no-dev --optimize-autoloader

# 6. Permessi corretti
chown -R www-data:www-data /var/www/danzafacile
chmod -R 755 /var/www/danzafacile
chmod -R 775 /var/www/danzafacile/storage
chmod -R 775 /var/www/danzafacile/bootstrap/cache
```

### Step 5: Ripristina Nginx (3 minuti)

```bash
# 1. Ripristina configurazione
cp nginx_${BACKUP_DATE}.conf /etc/nginx/sites-available/danzafacile

# 2. Crea symlink
ln -s /etc/nginx/sites-available/danzafacile /etc/nginx/sites-enabled/

# 3. Rimuovi default se presente
rm /etc/nginx/sites-enabled/default

# 4. Test configurazione
nginx -t

# 5. Riavvia Nginx
systemctl restart nginx
```

### Step 6: Ripristina SSL (2 minuti)

```bash
# 1. Installa Certbot
apt install -y certbot python3-certbot-nginx

# 2. Estrai certificati backup
tar -xzf ssl_${BACKUP_DATE}.tar.gz -C /etc/letsencrypt/

# 3. Oppure rigenera (se backup SSL fallito)
certbot --nginx -d danzafacile.it -d www.danzafacile.it

# 4. Test auto-renewal
certbot renew --dry-run
```

### Step 7: Finalizzazione (5 minuti)

```bash
# 1. Ottimizza Laravel
cd /var/www/danzafacile
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Migrazione database (se necessario)
php artisan migrate --force

# 3. Riavvia tutti i servizi
systemctl restart nginx
systemctl restart php8.4-fpm
systemctl restart mysql
systemctl restart redis-server

# 4. Test applicazione
curl -I https://www.danzafacile.it
# Dovrebbe rispondere HTTP/2 200
```

### Step 8: Aggiorna DNS (se IP cambiato)

```bash
# Se il nuovo server ha IP diverso, aggiorna DNS:
# - Vai su provider dominio (Aruba/altro)
# - Modifica record A per danzafacile.it → NUOVO_IP
# - Modifica record A per www.danzafacile.it → NUOVO_IP
# - Attendi propagazione DNS (5-30 minuti)
```

---

## ✅ Verifica Post-Ripristino

### Checklist Completa

```bash
# 1. Verifica servizi attivi
systemctl status nginx php8.4-fpm mysql redis-server

# 2. Verifica database
mysql -u danzafacile -p danzafacile -e "SELECT COUNT(*) FROM users;"

# 3. Verifica applicazione web
curl https://www.danzafacile.it
curl https://www.danzafacile.it/login

# 4. Verifica files upload
ls -lh /var/www/danzafacile/storage/app/public/

# 5. Verifica SSL
openssl s_client -connect www.danzafacile.it:443 -servername www.danzafacile.it

# 6. Test login admin
# Browser: https://www.danzafacile.it/login
# Email: admin@danzafacile.it
# Password: (dal file .env ripristinato)

# 7. Verifica email funzionanti
cd /var/www/danzafacile
php artisan tinker
Mail::raw('Test', function($msg) { $msg->to('admin@danzafacile.it')->subject('Test'); });
```

### ✅ Sistema Ripristinato Se:

- [ ] Tutti i servizi sono `active (running)`
- [ ] Database contiene dati (users, courses, students)
- [ ] Website risponde HTTP 200
- [ ] Login admin funziona
- [ ] Files upload sono presenti
- [ ] SSL certificate valido
- [ ] Email funzionanti

---

## 📞 Supporto Emergenza

### Contatti

- **Email**: info@danzafacile.it
- **Admin**: admin@danzafacile.it
- **GitHub**: https://github.com/emanuelerosato/scuoladidanza

### Risorse Utili

```bash
# Documentazione server
/root/MANUTENZIONE_SERVER.md

# Logs applicazione
tail -f /var/www/danzafacile/storage/logs/laravel.log

# Logs Nginx
tail -f /var/log/nginx/error.log
tail -f /var/log/nginx/access.log

# Logs MySQL
tail -f /var/log/mysql/error.log

# Logs PHP-FPM
tail -f /var/log/php8.4-fpm.log
```

---

## 🧪 Test Disaster Recovery

### Test Periodico Consigliato (Trimestrale)

**Perché testare?**
Un backup non testato è un backup non affidabile. Test regolari garantiscono che il ripristino funzioni realmente.

### Test Rapido (10 minuti)

```bash
# 1. Scarica backup più recente
rclone copy gdrive:danzafacile-backups /tmp/test-backup
cd /tmp/test-backup

# 2. Verifica integrità file
gunzip -t db_*.sql.gz  # Test database
tar -tzf laravel_*.tar.gz > /dev/null  # Test Laravel
tar -tzf ssl_*.tar.gz > /dev/null  # Test SSL

# 3. Test ripristino database in ambiente test
mysql -u root -p
CREATE DATABASE test_recovery;
EXIT;
gunzip < db_*.sql.gz | mysql -u root -p test_recovery
mysql -u root -p test_recovery -e "SHOW TABLES;"

# 4. Cleanup
mysql -u root -p -e "DROP DATABASE test_recovery;"
rm -rf /tmp/test-backup
```

---

## 📊 Metriche Backup

### Statistiche Correnti

```bash
# Spazio totale backup
du -sh /var/backups/danzafacile/

# Numero file backup
find /var/backups/danzafacile/ -type f | wc -l

# Backup più vecchio
ls -lt /var/backups/danzafacile/ | tail -1

# Backup più recente
ls -lt /var/backups/danzafacile/ | head -2

# Spazio Google Drive
rclone size gdrive:danzafacile-backups
```

### Retention Policy

- **Locale**: 7 giorni (168 ore)
- **Google Drive**: Illimitato (finché spazio disponibile)
- **Totale backup**: ~56 copie (8/giorno × 7 giorni)
- **Ridondanza**: 2 copie (locale + cloud)

---

## 🔐 Sicurezza Backup

### Protezione File

```bash
# Permessi corretti backup
chmod 600 /var/backups/danzafacile/env_*.txt  # File .env sensibile
chmod 644 /var/backups/danzafacile/db_*.sql.gz
chmod 644 /var/backups/danzafacile/*.tar.gz
```

### Accesso Limitato

- Solo `root` può leggere backup locali
- Google Drive protetto con autenticazione OAuth2
- File `.env` contiene password in chiaro (attenzione!)

### Best Practice

1. **Mai committare .env su GitHub** ✅ (già configurato)
2. **Backup Google Drive privato** ✅ (configurato)
3. **Password database complesse** ✅ (verificare)
4. **Rotazione credenziali trimestrale** ⚠️ (da implementare)

---

## 📝 Log Backup

### Visualizzare Report Backup

```bash
# Log backup completo
tail -f /var/log/backup-email.log

# Ultimi 3 backup
grep "BACKUP COMPLETO" /var/log/backup-email.log | tail -3

# Errori backup
grep "ERROR" /var/log/backup-email.log

# Successi backup
grep "SUCCESS" /var/log/backup-email.log | tail -10
```

---

**Ultima modifica**: 2025-11-15
**Versione**: 1.0.0
**Status**: ✅ Backup completo attivo ogni 3 ore
**Testato**: Sì (15/11/2025)
