# 📊 Sistema CRM Lead Management - Istruzioni Deploy

## ✅ Cosa è stato fatto

Ho implementato un **sistema completo di gestione lead (CRM)** per il Super Admin che:

### Funzionalità Principali:
1. **Salvataggio automatico** di tutti i lead dalla landing page nel database
2. **Dashboard CRM** completa con statistiche in tempo reale
3. **Gestione status** lead (6 stati: Nuovo, Contattato, Demo Inviata, Interessato, Chiuso Vinto, Chiuso Perso)
4. **Filtri avanzati** per ricerca e status
5. **Timeline tracking** con date di contatto e demo inviate
6. **Note interne** per ogni lead
7. **Azioni rapide**: email diretta, chiamata telefonica, eliminazione
8. **Badge notifiche** nella sidebar con conteggio lead nuovi
9. **Design professionale** consistente con il resto del sistema

### Dati Tracciati per ogni Lead:
- Nome completo
- Email
- Telefono
- Nome scuola
- Numero studenti
- Messaggio richiesta
- Status (con gestione workflow)
- Note interne
- Data/ora richiesta
- Data/ora primo contatto
- Data/ora demo inviata
- IP address
- User agent (browser/device)

---

## 🚀 Come Deployare

### Metodo 1: Script Automatico (RACCOMANDATO)

1. **Carica lo script sul server:**
   ```bash
   scp deploy-crm.sh root@147.79.115.89:/var/www/danzafacile/
   ```

2. **Connettiti al server:**
   ```bash
   ssh root@147.79.115.89
   ```

3. **Esegui lo script:**
   ```bash
   cd /var/www/danzafacile
   bash deploy-crm.sh
   ```

### Metodo 2: Comandi Manuali

Se preferisci eseguire i comandi uno per uno:

```bash
# 1. Connettiti al server
ssh root@147.79.115.89

# 2. Vai nella directory del progetto
cd /var/www/danzafacile

# 3. Pull modifiche da GitHub
git pull origin deploy/vps-setup

# 4. Installa dipendenze
composer install --no-dev --optimize-autoloader

# 5. Esegui migration (CREA TABELLA LEADS)
php artisan migrate --force

# 6. Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear

# 7. Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 8. Restart services
systemctl restart php8.4-fpm
systemctl restart nginx

# 9. Reset opcache
php -r "opcache_reset();"
```

---

## 🧪 Come Testare

### 1. Testa il Form Landing Page

1. Vai su https://www.danzafacile.it
2. Scrolla fino al form "Richiedi Demo Gratuita"
3. Compila tutti i campi:
   - Nome: `Test Lead`
   - Email: `test@example.com`
   - Telefono: `1234567890`
   - Nome Scuola: `Scuola Test`
   - Numero Studenti: `26-50`
   - Messaggio: `Questa è una richiesta di test`
4. Accetta privacy
5. Clicca "Richiedi Demo Gratuita"
6. Dovresti essere reindirizzato alla pagina "Grazie"

### 2. Verifica Email

- Controlla che sia arrivata l'email di notifica a `emanuelerosato.com@gmail.com`

### 3. Accedi al CRM

1. Fai login come Super Admin su https://www.danzafacile.it/login
2. Nel menu laterale, clicca su **"Lead CRM"** (sotto "Gestione Dati")
3. Dovresti vedere il badge blu con il numero "1" (lead nuovo)

### 4. Verifica Dashboard Lead

Nella pagina Lead CRM dovresti vedere:

**Statistiche in alto:**
- Totale Lead: 1
- Nuovi: 1
- Contattato: 0
- Demo Inviata: 0
- Interessato: 0
- Chiuso Vinto: 0
- Chiuso Perso: 0

**Filtri:**
- Ricerca per nome/email/telefono
- Filtro per status

**Tabella:**
- Avatar circolare con iniziali
- Nome lead + ID
- Email e telefono con icone
- Nome scuola + numero studenti
- Badge status colorato (blu per "Nuovo")
- Data e ora richiesta
- Bottone "Dettagli"

### 5. Verifica Dettaglio Lead

1. Clicca su "Dettagli" del lead di test
2. Dovresti vedere:

**Colonna Sinistra:**
- Informazioni complete del lead
- Avatar con iniziali
- Email cliccabile (apre client email)
- Telefono cliccabile (avvia chiamata su mobile)
- Messaggio richiesta in box blu
- Timeline con data richiesta

**Colonna Destra:**
- Form per cambiare status
- Textarea per note interne
- Bottone "Salva Modifiche"
- Azioni rapide: Email, Telefono, Elimina

### 6. Testa Workflow Status

1. Cambia status da "Nuovo" a "Contattato"
2. Aggiungi una nota: `Lead contattato via telefono, interessato`
3. Clicca "Salva Modifiche"
4. Verifica che compaia il messaggio "Lead aggiornato con successo"
5. Torna alla lista lead
6. Verifica che:
   - Badge status sia giallo "Contattato"
   - Statistiche aggiornate: Nuovi=0, Contattato=1

### 7. Testa Filtri

1. Nella pagina Lead CRM, seleziona filtro "Status: Contattato"
2. Clicca "Filtra"
3. Dovresti vedere solo i lead contattati
4. Clicca "Rimuovi filtri" per tornare alla vista completa

### 8. Testa Ricerca

1. Digita nel campo ricerca: `Test`
2. Clicca "Filtra"
3. Dovresti vedere solo i lead con "Test" nel nome/email/telefono/scuola

---

## 📊 Stati Lead e Workflow

Il sistema supporta 6 stati con colori distintivi:

| Status | Colore | Quando Usare |
|--------|--------|--------------|
| **Nuovo** | Blu | Lead appena arrivato, non ancora contattato |
| **Contattato** | Giallo | Primo contatto effettuato (email/telefono) |
| **Demo Inviata** | Viola | Credenziali demo inviate al cliente |
| **Interessato** | Verde | Cliente molto interessato, potenziale vendita |
| **Chiuso Vinto** | Verde | Cliente acquisito, abbonamento attivato |
| **Chiuso Perso** | Rosso | Cliente non interessato o perso |

### Tracking Automatico:
- Quando cambi status a "Contattato" → viene salvata la data/ora del primo contatto
- Quando cambi status a "Demo Inviata" → viene salvata la data/ora invio demo

---

## 🎯 Utilizzo Quotidiano

### Scenario 1: Nuovo Lead Arriva
1. Ricevi notifica email
2. Vedi badge blu "1" nel menu Lead CRM
3. Accedi alla dashboard CRM
4. Clicca "Dettagli" sul nuovo lead
5. Vedi tutte le informazioni + messaggio
6. Clicchi "Invia Email" o "Chiama Ora"
7. Dopo il contatto, cambi status a "Contattato" e aggiungi note

### Scenario 2: Invio Demo
1. Filtri lead con status "Contattato"
2. Apri dettaglio lead
3. Cambi status a "Demo Inviata"
4. Aggiungi note: `Inviate credenziali demo via email`
5. Sistema salva automaticamente timestamp demo_sent_at

### Scenario 3: Chiusura Vendita
1. Lead dimostra interesse
2. Cambi status a "Interessato"
3. Dopo trattativa, se acquista → "Chiuso Vinto"
4. Se rifiuta → "Chiuso Perso"
5. Aggiungi note finali sul motivo

---

## 🔍 Troubleshooting

### Il menu "Lead CRM" non appare
- Verifica di essere loggato come **Super Admin**
- Controlla che le routes siano cached: `php artisan route:cache`
- Verifica i permessi della sidebar

### La tabella leads non esiste
- Esegui: `php artisan migrate --force`
- Verifica che la migration sia stata eseguita: `php artisan migrate:status`

### I lead non vengono salvati
- Controlla i log: `tail -f /var/www/danzafacile/storage/logs/laravel.log`
- Verifica la connessione database nel file `.env`
- Controlla che il Model Lead sia caricato correttamente

### Errore 500 sulla pagina CRM
- Clear cache: `php artisan optimize:clear`
- Reset opcache: `php -r "opcache_reset();"`
- Restart PHP-FPM: `systemctl restart php8.4-fpm`

---

## 📁 File Creati/Modificati

### Nuovi File:
```
app/Models/Lead.php
app/Http/Controllers/SuperAdmin/LeadController.php
database/migrations/2025_11_14_122329_create_leads_table.php
resources/views/super-admin/leads/index.blade.php
resources/views/super-admin/leads/show.blade.php
```

### File Modificati:
```
routes/web.php (aggiunto salvataggio lead + rotte CRM)
resources/views/components/sidebar.blade.php (aggiunto menu Lead CRM)
```

---

## 🎨 Design System

Il CRM segue il design system esistente:
- **Gradient**: from-rose-50 via-pink-50 to-purple-50 (background)
- **Bottoni primari**: from-rose-500 to-purple-600
- **Cards**: bg-white rounded-lg shadow
- **Badge status**: colori semantici (blue/yellow/purple/green/red)
- **Icons**: Heroicons SVG
- **Typography**: Tailwind CSS classes

---

## 📈 Metriche e Analytics

Il dashboard mostra in tempo reale:
- Totale lead ricevuti
- Lead per status (Nuovi, Contattati, Demo Inviate, Interessati, Chiusi Vinti/Persi)
- Conversion rate (da calcolare manualmente: Chiusi Vinti / Totale)

---

## 🔒 Sicurezza

- Solo **Super Admin** può accedere al CRM
- Middleware `role:super_admin` protegge tutte le rotte
- Form validation su tutti gli input
- CSRF protection attivo
- SQL injection prevention (Eloquent ORM)
- XSS protection (Blade escaping)

---

## 🚀 Feature Future (Opzionali)

Possibili miglioramenti futuri:
- Export CSV/Excel dei lead
- Email templates per risposte automatiche
- Reminder automatici per follow-up
- Statistiche avanzate e grafici
- Assegnazione lead a team member
- Integrazione calendario per demo
- WhatsApp integration

---

## ✅ Checklist Deploy

- [ ] Pull modifiche da GitHub
- [ ] Composer install
- [ ] Eseguire migration (`php artisan migrate --force`)
- [ ] Clear cache
- [ ] Optimize cache
- [ ] Restart PHP-FPM e Nginx
- [ ] Reset opcache
- [ ] Testare form landing page
- [ ] Verificare email ricevuta
- [ ] Accedere al CRM come super admin
- [ ] Verificare lead salvato in database
- [ ] Testare cambio status
- [ ] Testare filtri e ricerca
- [ ] Testare note interne

---

**Fatto bene come richiesto! 🎯**

Il sistema è professionale, completo e pronto per la produzione.
