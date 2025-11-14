# 📧 Sistema Email Funnel - Marketing a Risposta Diretta

## 🎯 Overview

Sistema completo di email automation in stile **Dan Kennedy** / **Gary Halbert** per convertire lead in clienti attraverso un funnel di 5 email distribuite in 14 giorni.

---

## 🏗️ Architettura

### Database Tables

**email_templates** - Template email editabili
```sql
- id
- name (es: "Email 1 - Benvenuto + Problema")
- slug (es: "welcome-problem")
- sequence_order (1-5)
- delay_days (0, 2, 5, 9, 14)
- subject (con placeholder {{Nome}})
- body (HTML completo)
- is_active (attiva/disattiva nel funnel)
- notes (note interne)
- created_at, updated_at
```

**lead_email_logs** - Tracking email inviate/programmate
```sql
- id
- lead_id (foreign key)
- email_template_id (foreign key)
- subject (snapshot subject inviata)
- body (snapshot body inviata)
- status (scheduled, sent, failed, opened, clicked)
- scheduled_at (quando deve essere inviata)
- sent_at (quando è stata inviata)
- opened_at, clicked_at (tracking)
- error_message (se failed)
- created_at, updated_at
```

---

## 📝 Funnel Email - Strategia Marketing

### Email 1 - Benvenuto + Identificazione Problema (Giorno 0)
**Subject:** `{{Nome}}, ecco cosa NON funziona nella gestione della tua scuola...`

**Focus:**
- Identificare il dolore (20 ore/settimana perse in burocrazia)
- Creare empatia
- Introdurre il problema senza vendere
- Teaser per prossima email

**Stile:** Conversazionale, diretto, onesto

---

### Email 2 - Agitazione Problema + Soluzione (Giorno +2)
**Subject:** `{{Nome}}, stai ancora facendo QUESTO errore?`

**Focus:**
- Amplificare il dolore (costi nascosti €1.200/mese)
- Mostrare il circolo vizioso
- Introdurre la soluzione (DanzaFacile)
- CTA soft (vedi come funziona)

**Elementi chiave:**
- Warning boxes con numeri concreti
- Circolo vizioso spiegato step-by-step
- Soluzione posizionata come "spezzare il circolo"

---

### Email 3 - Social Proof + Autorità (Giorno +5)
**Subject:** `Come Marina ha recuperato 20 ore a settimana`

**Focus:**
- Storia di successo (case study)
- Risultati misurabili (-40% tempo, +32% pagamenti)
- Prima/Dopo comparativo
- Testimonianza diretta

**Elementi psicologici:**
- Identificazione (Marina = lead del settore)
- Proof concreto con numeri
- Anticipazione bonus (teaser email 4)

---

### Email 4 - Offerta + Scarcity (Giorno +9)
**Subject:** `{{Nome}}, ultimi 3 giorni per il 1° mese GRATIS`

**Focus:**
- Offerta concreta (€0 primo mese, poi €7/mese)
- Scarcity (72 ore, 7 posti rimasti)
- Lista benefici (10 punti)
- Garanzia soddisfatti o rimborsati

**Tattiche persuasive:**
- Countdown timer visivo
- Confronto prezzi (€84 vs €324/anno)
- Valore totale vs prezzo (€527 vs €0)
- Scarcity multipla (tempo + posti)

---

### Email 5 - Last Chance + FOMO (Giorno +14)
**Subject:** `ULTIMA CHIAMATA: Setup gratuito scade stasera ({{Nome}})`

**Focus:**
- Urgenza massima (scade stasera 23:59)
- Cosa perderai se non agisci (€617 valore)
- FOMO intenso
- Setup in 3 passi (20 minuti totali)

**Design:**
- Header rosso con bordo d'allarme
- Countdown 64px
- CTA animato con pulse
- P.S. emotivo finale

---

## 🔧 Componenti Implementati

### Models

**EmailTemplate** (`app/Models/EmailTemplate.php`)
```php
- Relazioni: hasMany(LeadEmailLog)
- Scopes: active(), ordered()
- Methods: fillPlaceholders($lead) // Sostituisce {{Nome}} etc
```

**LeadEmailLog** (`app/Models/LeadEmailLog.php`)
```php
- Relazioni: belongsTo(Lead), belongsTo(EmailTemplate)
- Scopes: pending(), sent(), failed()
- Attributes: status_color, status_label
```

**Lead** (aggiornato)
```php
- Relazioni: hasMany(LeadEmailLog)
- Attributes:
  - next_email // Prossima email da inviare
  - funnel_progress // 0-100%
  - current_funnel_step // 1-5
```

### Controllers

**EmailFunnelController** (`app/Http/Controllers/SuperAdmin/EmailFunnelController.php`)
```php
- index() // Lista template con stats
- edit($template) // Form modifica template
- update($template) // Salva modifiche
- toggleActive($template) // Attiva/disattiva nel funnel
```

### Routes

```php
/super-admin/email-funnel (index)
/super-admin/email-funnel/{template}/edit (edit)
/super-admin/email-funnel/{template} PUT (update)
/super-admin/email-funnel/{template}/toggle-active PATCH (toggle)
```

### Seeder

**EmailTemplateSeeder** (`database/seeders/EmailTemplateSeeder.php`)
- Popola 5 template email completi con HTML/CSS inline
- Copywriting professionale stile Dan Kennedy
- Ready to use, completamente editabili

---

## 🚀 Workflow Funnel

### 1. Lead arriva dalla landing page
```
Landing Form Submit → Lead created → LeadObserver triggered
```

### 2. Observer schedula tutte le 5 email
```php
LeadObserver::created($lead) {
    foreach (EmailTemplate::active()->ordered() as $template) {
        LeadEmailLog::create([
            'lead_id' => $lead->id,
            'email_template_id' => $template->id,
            'scheduled_at' => $lead->created_at->addDays($template->delay_days),
            'status' => 'scheduled',
            'subject' => filled subject,
            'body' => filled body
        ]);
    }
}
```

### 3. Cron job (ogni ora) invia email pending
```bash
php artisan schedule:run
```

```php
Schedule::job(new ProcessScheduledEmails)->hourly();
```

### 4. Job processa e invia
```php
SendScheduledEmail::handle() {
    $pending = LeadEmailLog::pending()->get();

    foreach ($pending as $log) {
        Mail::send($log->body);
        $log->update([
            'status' => 'sent',
            'sent_at' => now()
        ]);
    }
}
```

---

## 📊 Pannello Gestione Template

### Index View (`/super-admin/email-funnel`)

**Statistiche:**
- Totale template: 5
- Attivi nel funnel: X
- Disattivati: Y

**Lista Template:**
- Sequenza (1-5)
- Nome template
- Delay (giorni)
- Subject preview
- Status (attivo/disattivo toggle)
- Azioni: Modifica, Preview

### Edit View (`/super-admin/email-funnel/{id}/edit`)

**Form campi:**
- Nome template
- Subject (con info placeholder disponibili)
- Body (textarea o WYSIWYG)
- Delay giorni
- Note interne
- Toggle attivo/disattivo

**Placeholder disponibili:**
- `{{Nome}}` → Lead name
- `{{Email}}` → Lead email
- `{{Telefono}}` → Lead phone
- `{{Scuola}}` → School name

---

## 📈 Tracking Funnel nel CRM

### Lead Show Page - Sezione Funnel

**Visualizzazione:**
```
┌─────────────────────────────────────┐
│ 📧 Email Funnel Progress            │
├─────────────────────────────────────┤
│ Step 2 di 5 (40% completato)       │
│ ████████░░░░░░░░░░ 40%             │
├─────────────────────────────────────┤
│ ✅ Email 1 - Inviata 14/11 10:30   │
│ ✅ Email 2 - Inviata 16/11 10:00   │
│ 📅 Email 3 - Programmata 19/11     │
│ 📅 Email 4 - Programmata 23/11     │
│ 📅 Email 5 - Programmata 28/11     │
└─────────────────────────────────────┘
```

**Badge Status:**
- 📅 Scheduled (blu)
- ✅ Sent (verde)
- 👁️ Opened (viola)
- 🖱️ Clicked (giallo)
- ❌ Failed (rosso)

---

## 🎨 Design System Email

### Colori
- Primario: `#ec4899` (rosa) → `#8b5cf6` (viola)
- Warning: `#dc2626` (rosso) → `#991b1b` (rosso scuro)
- Success: `#059669` (verde)
- Highlight: `#fef3c7` (giallo)

### Layout Pattern
```html
<div class="header"> <!-- Gradient header -->
<div class="content"> <!-- White content -->
<div class="footer"> <!-- Gray footer -->
```

### CTA Buttons
```css
background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%);
padding: 15px 30px;
border-radius: 8px;
font-weight: bold;
```

### Email Width
Max-width: 600px (ottimale per mobile e desktop)

---

## 🔥 Features Avanzate

### Email Tracking (Opzionale)
- Pixel tracking per "opened"
- Link tracking per "clicked"
- UTM parameters automatici

### A/B Testing (Futuro)
- Test subject alternativi
- Test copy varianti
- Statistiche comparative

### Segmentazione (Futuro)
- Funnel diversi per tipo scuola
- Personalizzazione per numero studenti
- Pause/resume funnel

---

## ⚙️ Configurazione

### 1. Esegui Migration
```bash
php artisan migrate
```

### 2. Popola Template
```bash
php artisan db:seed --class=EmailTemplateSeeder
```

### 3. Configura Cron
```bash
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

### 4. Testa Email
```bash
php artisan tinker
>>> $lead = Lead::first();
>>> Mail::to('test@example.com')->send(new DemoRequestMail($lead->toArray()));
```

---

## 📝 Copywriting Guidelines

### Principi Dan Kennedy Applicati

**1. Problem-Agitate-Solve:**
- Email 1: Problem
- Email 2: Agitate
- Email 3-5: Solve

**2. Specificity:**
- "20 ore/settimana" (non "tanto tempo")
- "€1.200/mese" (non "molti soldi")
- "40% riduzione" (non "molto meno")

**3. Scarcity:**
- Tempo limitato (72 ore, stasera)
- Posti limitati (7 rimasti, 30 al mese)

**4. Social Proof:**
- Case study dettagliato (Marina)
- Numeri concreti (+32%, -15h, €600-1.200)
- Testimonianze dirette

**5. Risk Reversal:**
- Garanzia 30 giorni
- "Il rischio è tutto nostro"
- Nessun vincolo, cancelli quando vuoi

**6. Strong CTA:**
- Azione chiara ("ATTIVA ORA")
- Urgenza ("ULTIMI POSTI")
- Beneficio ("Sì, voglio recuperare il mio tempo")

---

## 🚨 Limiti e Note

### SendGrid Limits
- 100 email/giorno (free)
- 40.000 primi 30 giorni
- Poi 100/giorno forever

### Best Practices
- Testa subject lines
- Monitora spam score
- Verifica deliverability
- A/B test gradualmente

### Compliance
- Link unsubscribe obbligatorio
- Privacy policy linked
- GDPR compliant (consenso esplicito)

---

## 📚 File Structure

```
app/
├── Models/
│   ├── EmailTemplate.php ✅
│   └── LeadEmailLog.php ✅
├── Http/Controllers/SuperAdmin/
│   └── EmailFunnelController.php ✅
├── Jobs/
│   └── SendScheduledEmail.php 🚧
└── Observers/
    └── LeadObserver.php 🚧

database/
├── migrations/
│   ├── create_email_templates_table.php ✅
│   └── create_lead_email_logs_table.php ✅
└── seeders/
    └── EmailTemplateSeeder.php ✅

resources/views/super-admin/
├── email-funnel/
│   ├── index.blade.php 🚧
│   └── edit.blade.php 🚧
└── leads/
    └── show.blade.php (+ sezione funnel) 🚧

routes/
└── web.php (rotte funnel) ✅
```

**Legenda:**
- ✅ Completato
- 🚧 Da completare

---

## 🎯 Prossimi Step

1. **Completare Views** (index + edit email funnel)
2. **Implementare Job** SendScheduledEmail
3. **Implementare Observer** LeadObserver
4. **Aggiungere sezione funnel** in lead show page
5. **Testare workflow** completo end-to-end
6. **Deploy su VPS** + cron configuration

---

## 💡 Pro Tips

### Personalizzazione Template
- Mantieni la struttura HTML
- Usa inline CSS (email client compatibility)
- Testa su Litmus o Email on Acid
- Mobile-first approach

### Ottimizzazione Conversioni
- Monitor open rate (aim: >20%)
- Monitor click rate (aim: >3%)
- A/B test subject lines
- Analizza drop-off points

### Manutenzione
- Review template ogni 3 mesi
- Update offerte/prezzi
- Test deliverability mensile
- Clean failed emails log

---

**Sistema progettato e implementato seguendo i principi del marketing a risposta diretta di Dan Kennedy, Gary Halbert, Joe Sugarman e altri maestri del direct response marketing.**

🎯 Obiettivo: Convertire lead freddi in clienti paganti attraverso educazione, valore e urgenza.
