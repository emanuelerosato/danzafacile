# 🚀 Ripristino Rapido Automatico - Danza Facile

**Tempo stimato**: 10-15 minuti (automatico)
**Difficoltà**: Facile (tutto automatizzato)

---

## 📋 Prerequisiti

### Nuovo Server
- Ubuntu 22.04 o 24.04
- Accesso root SSH
- Connessione internet

### Software Necessario (lo script controlla automaticamente)
```bash
# Lo script ti dirà se manca qualcosa, ma idealmente:
apt update && apt install -y \
    mysql-server \
    nginx \
    php8.4 php8.4-fpm php8.4-mysql \
    composer \
    rclone \
    curl
```

---

## 🆘 Procedura Ripristino Rapido

### Step 1: Scarica Script (2 minuti)

```bash
# Connettiti al nuovo server
ssh root@NUOVO_SERVER_IP

# Scarica script da GitHub
wget https://raw.githubusercontent.com/emanuelerosato/danzafacile/main/deployment/scripts/restore-automatic.sh

# Rendi eseguibile
chmod +x restore-automatic.sh
```

### Step 2: Esegui Script (8-10 minuti)

```bash
# Lancia ripristino automatico
./restore-automatic.sh
```

**Lo script ti chiederà:**

1. **Sovrascrivere installazione esistente?**
   - Se server nuovo: automatico
   - Se server esistente: scrivi `SI` per confermare

2. **Da dove ripristinare?**
   ```
   1) Backup LOCALE (/var/backups/danzafacile)
   2) Google Drive (richiede rclone configurato)

   Scegli (1 o 2): 2  ← scegli Google Drive
   ```

3. **Procedere con questo backup?**
   ```
   Backup trovato: 20251115_144438
   Files trovati:
   ✓ Database: 27K
   ✓ Files utenti: 4.0K
   ✓ .env: 1.3K
   ✓ Nginx: 1.6K
   ✓ SSL: 690B
   ✓ Laravel: 1.4M

   Procedere con questo backup? (s/n): s
   ```

4. **Password root MySQL**
   ```
   Password root MySQL: ********
   ```

### Step 3: Verifica (2 minuti)

Lo script al termine mostra:

```
════════════════════════════════════════════════════════════
  ✅ RIPRISTINO COMPLETATO CON SUCCESSO!
════════════════════════════════════════════════════════════

📊 Statistiche:
   • Tempo totale: 9 minuti e 32 secondi
   • Database: 47 tabelle ripristinate
   • Codice Laravel: ✓ ripristinato
   • Files utenti: ✓ ripristinati
   • Configurazione: ✓ ripristinata

🔧 Servizi:
   • Nginx: ✓ RUNNING
   • PHP-FPM: ✓ RUNNING
   • MySQL: ✓ RUNNING
   • Redis: ✓ RUNNING

🌐 Verifica applicazione:
   • URL: https://www.danzafacile.it
   • Login: https://www.danzafacile.it/login

✓ Website online! HTTP 200

Ripristino completato in 9 minuti e 32 secondi!
════════════════════════════════════════════════════════════
```

---

## ✅ Test Post-Ripristino

### Verifiche Automatiche

```bash
# 1. Servizi attivi
systemctl status nginx php8.4-fpm mysql redis-server

# 2. Website online
curl -I https://www.danzafacile.it

# 3. Database popolato
mysql -u danzafacile -p danzafacile -e "SELECT COUNT(*) FROM users;"
```

### Test Manuale

1. **Apri browser**: https://www.danzafacile.it
2. **Login admin**:
   - Email: `admin@danzafacile.it`
   - Password: (quella nel file .env ripristinato)
3. **Verifica dashboard**: Studenti, corsi, pagamenti visibili
4. **Verifica upload**: Foto gallerie caricate

---

## 🎯 Cosa Fa lo Script Automaticamente

### STEP 1: Verifica Installazione
- Controlla se esiste installazione precedente
- Chiede conferma se deve sovrascrivere

### STEP 2: Selezione Backup
- Locale (`/var/backups/danzafacile`) o Google Drive
- Scarica da Google Drive se necessario

### STEP 3: Ricerca Backup
- Trova backup più recente automaticamente
- Mostra timestamp e dimensioni files
- Chiede conferma prima di procedere

### STEP 4: Credenziali Database
- Legge credenziali dal file `.env` backup
- Estrae DB_DATABASE, DB_USERNAME, DB_PASSWORD

### STEP 5: Ripristino Database
- Crea database MySQL
- Crea utente con permessi
- Ripristina dump completo
- Verifica tabelle ripristinate

### STEP 6: Ripristino Codice Laravel
- Estrae codice Laravel completo
- Ripristina file `.env`
- Ripristina files utenti uploaded
- Installa dipendenze Composer
- Configura permessi corretti
- Ottimizza cache Laravel

### STEP 7: Ripristino Nginx
- Ripristina configurazione Nginx
- Crea symlink sites-enabled
- Testa configurazione
- Riavvia Nginx

### STEP 8: Ripristino SSL
- Ripristina certificati Let's Encrypt
- Riavvia Nginx per applicare SSL

### STEP 9: Verifica Finale
- Mostra statistiche ripristino
- Verifica servizi attivi
- Testa connessione website
- Salva log completo

---

## 📊 Cosa Viene Ripristinato

| Componente | Fonte | Dimensione | Status |
|------------|-------|------------|--------|
| Database MySQL | `db_*.sql.gz` | ~27 KB | ✅ Automatico |
| Files Utenti | `files_*.tar.gz` | ~4 KB | ✅ Automatico |
| File .env | `env_*.txt` | ~1.3 KB | ✅ Automatico |
| Nginx Config | `nginx_*.conf` | ~1.6 KB | ✅ Automatico |
| SSL Certificates | `ssl_*.tar.gz` | ~690 B | ✅ Automatico |
| Codice Laravel | `laravel_*.tar.gz` | ~1.4 MB | ✅ Automatico |
| Dipendenze Composer | - | ~50 MB | ✅ Automatico |
| Cache Laravel | - | - | ✅ Automatico |
| Permessi Files | - | - | ✅ Automatico |

---

## 🔧 Opzioni Avanzate

### Ripristino da Backup Specifico

Se non vuoi l'ultimo backup ma uno specifico:

```bash
# Modifica script temporaneamente
nano restore-automatic.sh

# Cerca questa riga (circa linea 140):
LATEST_DB=$(ls -t "$BACKUP_DIR"/db_*.sql.gz 2>/dev/null | head -1)

# Sostituisci con timestamp specifico:
BACKUP_DATE="20251115_120000"  # Usa backup specifico
```

### Solo Database (Ripristino Parziale)

Se vuoi ripristinare solo il database:

```bash
# Trova backup
cd /var/backups/danzafacile
LATEST=$(ls -t db_*.sql.gz | head -1)

# Ripristina
gunzip < $LATEST | mysql -u root -p danzafacile

# Verifica
mysql -u danzafacile -p danzafacile -e "SHOW TABLES;"
```

### Solo Files Utenti

```bash
# Trova backup
cd /var/backups/danzafacile
LATEST=$(ls -t files_*.tar.gz | head -1)

# Ripristina
tar -xzf $LATEST -C /var/www/danzafacile/storage/app/

# Permessi
chown -R www-data:www-data /var/www/danzafacile/storage
```

---

## ⚠️ Troubleshooting

### Errore: MySQL Password Incorretta

```bash
# Resetta password root MySQL
sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY 'nuova_password';
FLUSH PRIVILEGES;
EXIT;

# Riprova script
./restore-automatic.sh
```

### Errore: rclone non configurato

```bash
# Configura Google Drive
rclone config

# Segui wizard:
# - Nuovo remote: gdrive
# - Tipo: Google Drive
# - Autentica via browser

# Testa
rclone ls gdrive:danzafacile-backups
```

### Errore: Nginx configurazione invalida

```bash
# Testa config
nginx -t

# Vedi errori dettagliati
nginx -T

# Ripristina config di default
cp /etc/nginx/sites-available/default /etc/nginx/sites-enabled/
systemctl restart nginx
```

### Website non raggiungibile dopo ripristino

```bash
# 1. Verifica servizi
systemctl status nginx php8.4-fpm mysql

# 2. Verifica logs
tail -f /var/www/danzafacile/storage/logs/laravel.log
tail -f /var/log/nginx/error.log

# 3. Verifica DNS
nslookup danzafacile.it

# 4. Verifica firewall
ufw status
ufw allow 80/tcp
ufw allow 443/tcp
```

---

## 📝 Log Ripristino

Ogni esecuzione dello script crea un log dettagliato:

```bash
# Log salvato automaticamente in:
/var/log/restore-YYYYMMDD_HHMMSS.log

# Visualizza ultimo log
ls -lt /var/log/restore-*.log | head -1

# Leggi log
tail -100 /var/log/restore-20251115_153045.log
```

---

## 🎯 Confronto: Manuale vs Automatico

| Operazione | Manuale | Automatico |
|------------|---------|------------|
| Setup server | 5 min | 5 min |
| Scarica backup | 3 min | **Automatico** |
| Trova backup più recente | Manuale | **Automatico** |
| Leggi credenziali .env | Manuale | **Automatico** |
| Crea database | 2 min | **Automatico** |
| Ripristina database | 2 min | **Automatico** |
| Estrai codice Laravel | 3 min | **Automatico** |
| Ripristina .env | 1 min | **Automatico** |
| Ripristina files | 1 min | **Automatico** |
| Installa Composer | 3 min | **Automatico** |
| Permessi files | 2 min | **Automatico** |
| Ottimizza Laravel | 1 min | **Automatico** |
| Ripristina Nginx | 3 min | **Automatico** |
| Ripristina SSL | 2 min | **Automatico** |
| Verifica servizi | 2 min | **Automatico** |
| **TOTALE** | **30-35 min** | **10-15 min** |

---

## ✅ Checklist Finale

Dopo il ripristino automatico verifica:

- [ ] Tutti i servizi sono `active (running)`
- [ ] Website risponde HTTP 200/302
- [ ] Login admin funziona
- [ ] Dashboard mostra dati corretti
- [ ] Studenti/Corsi/Pagamenti visibili
- [ ] Upload files funzionano
- [ ] Email funzionanti
- [ ] SSL certificate valido (HTTPS)

---

## 📞 Supporto

**Se lo script fallisce:**

1. Leggi log dettagliato: `/var/log/restore-*.log`
2. Verifica errori specifici nel log
3. Consulta sezione Troubleshooting
4. Contatta: info@danzafacile.it

**Log utili per debugging:**
```bash
# Script restore
tail -100 /var/log/restore-*.log

# Laravel
tail -100 /var/www/danzafacile/storage/logs/laravel.log

# Nginx
tail -100 /var/log/nginx/error.log

# MySQL
tail -100 /var/log/mysql/error.log
```

---

**Ultima modifica**: 2025-11-15
**Versione Script**: 1.0.0
**Testato**: No (richiede test su server reale)
**Status**: ✅ Pronto per uso
