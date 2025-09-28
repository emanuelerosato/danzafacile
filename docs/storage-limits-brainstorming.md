# 🚀 STORAGE LIMITATO: Super-Admin Controlled + PayPal Pay-per-GB

## 🎯 STRATEGIA SCELTA

Implementare un sistema di **storage limitato** dove:
- **Super-admin** stabilisce lo spazio storage per ogni scuola dalla sua dashboard
- **Admin scuola** riceve notifiche al raggiungimento delle soglie (80%, 90%, 95%)
- **Upload bloccato al 100%** - Admin deve fare **richiesta esplicita** di upgrade
- **Pagamenti per GB** tramite PayPal con pricing configurabile dal super-admin

---

## 📋 MODELLO DI BUSINESS

### **Pay-per-GB con Controllo Super-Admin**
```
🆓 BASE: 1GB gratuito per ogni scuola (configurabile da super-admin)
💰 EXTRA: €2/GB aggiuntivo al mese (prezzo configurabile da super-admin)
📈 BULK: Sconti automatici per volumi (>50GB = €1.50/GB)
🎯 ADMIN CONTROL: Super-admin può modificare limiti e prezzi per ogni scuola
💳 PAYPAL: Tutti i pagamenti tramite PayPal Subscriptions
```

### **Vantaggi del Modello Scelto:**
- **Flessibilità**: Super-admin controlla tutto senza limitazioni predefinite
- **Semplicità**: Un solo sistema di pricing senza piani complessi
- **Scalabilità**: Pricing cresce naturalmente con l'uso
- **Controllo**: Nessun customer support - tutto gestito da super-admin
- **Fairness**: Si paga solo quello che si usa

---

## 🔍 STATO ATTUALE

### ✅ Cosa Abbiamo Già:
- Sistema upload documenti funzionante
- Gestione multi-tenant (schools) con isolamento dati
- Ruoli differenziati (super-admin, admin, user)
- Storage su filesystem privato sicuro
- Dashboard admin con statistiche base

### ❌ Cosa Dobbiamo Implementare:
- Tracking del consumo storage per scuola
- Limiti e controlli pre-upload
- Sistema PayPal integrato
- Dashboard super-admin per gestire limiti
- Interface admin per richiedere upgrade
- Notifiche automatiche limite raggiunto

---

## 🛠️ IMPLEMENTAZIONE TECNICA

### **Database Changes**
```sql
-- Aggiungere colonne alla tabella schools
ALTER TABLE schools ADD COLUMN storage_limit_mb BIGINT DEFAULT 1024; -- 1GB default
ALTER TABLE schools ADD COLUMN current_usage_mb BIGINT DEFAULT 0;
ALTER TABLE schools ADD COLUMN paypal_subscription_id VARCHAR(255) NULL;
ALTER TABLE schools ADD COLUMN storage_price_per_gb DECIMAL(8,2) DEFAULT 2.00;

-- Nuova tabella per storico storage purchases
CREATE TABLE storage_purchases (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id BIGINT UNSIGNED NOT NULL,
    gb_purchased INT NOT NULL,
    price_per_gb DECIMAL(8,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    paypal_subscription_id VARCHAR(255) NOT NULL,
    paypal_payment_id VARCHAR(255) NULL,
    status ENUM('pending', 'active', 'cancelled', 'expired') DEFAULT 'pending',
    starts_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
);
```

### **PayPal Integration**
```php
// Service per gestire PayPal subscriptions
class PayPalStorageService {
    public function createStorageSubscription(School $school, int $additionalGB) {
        $pricePerGB = $school->storage_price_per_gb ?? 2.00;
        $monthlyAmount = $additionalGB * $pricePerGB;

        return $this->paypal->createSubscription([
            'plan_id' => $this->createStoragePlan($additionalGB, $monthlyAmount),
            'custom_id' => 'school-storage-' . $school->id,
            'application_context' => [
                'brand_name' => 'Scuola Danza Platform',
                'locale' => 'it-IT',
                'return_url' => route('admin.storage.success'),
                'cancel_url' => route('admin.storage.upgrade'),
            ],
            'subscriber' => [
                'email_address' => $school->admin->email,
                'name' => [
                    'given_name' => $school->admin->first_name,
                    'surname' => $school->admin->last_name
                ]
            ]
        ]);
    }

    public function handleWebhook($eventType, $data) {
        switch($eventType) {
            case 'BILLING.SUBSCRIPTION.ACTIVATED':
                $this->activateStorageUpgrade($data);
                break;
            case 'BILLING.SUBSCRIPTION.CANCELLED':
                $this->handleCancellation($data);
                break;
            case 'PAYMENT.SALE.COMPLETED':
                $this->recordPayment($data);
                break;
        }
    }
}
```

### **Storage Calculation & Middleware**
```php
// Middleware per controllare storage prima upload
class CheckStorageLimit {
    public function handle($request, $next) {
        if ($request->hasFile('file')) {
            $school = auth()->user()->school;
            $fileSize = $request->file('file')->getSize();

            if (!$school->hasStorageSpace($fileSize)) {
                return response()->json([
                    'error' => 'Storage limit raggiunto - Upload bloccato',
                    'message' => 'Il tuo storage è pieno. Richiedi un upgrade per continuare.',
                    'current_usage' => $school->getCurrentUsageMB(),
                    'limit' => $school->storage_limit_mb,
                    'upgrade_url' => route('admin.storage.upgrade'),
                    'blocked' => true
                ], 413);
            }
        }
        return $next($request);
    }
}

// Model methods per School
class School extends Model {
    public function getCurrentUsageMB(): float {
        return Cache::remember("school.{$this->id}.storage_usage", 3600, function() {
            return $this->documents()->sum('file_size') / 1024 / 1024;
        });
    }

    public function hasStorageSpace(int $additionalBytes): bool {
        $currentUsageMB = $this->getCurrentUsageMB();
        $additionalMB = $additionalBytes / 1024 / 1024;
        return ($currentUsageMB + $additionalMB) <= $this->storage_limit_mb;
    }

    public function getStorageUsagePercentage(): float {
        return ($this->getCurrentUsageMB() / $this->storage_limit_mb) * 100;
    }

    public function shouldShowStorageWarning(): bool {
        return $this->getStorageUsagePercentage() >= 80;
    }

    public function shouldBlockUploads(): bool {
        return $this->getStorageUsagePercentage() >= 100;
    }

    public function getStorageWarningLevel(): string {
        $percentage = $this->getStorageUsagePercentage();
        if ($percentage >= 100) return 'critical';
        if ($percentage >= 95) return 'urgent';
        if ($percentage >= 90) return 'warning';
        if ($percentage >= 80) return 'notice';
        return 'normal';
    }
}
```

---

## 🚨 FLUSSO NOTIFICHE E UPGRADE

### **Soglie di Notifica (Richiesta Esplicita)**
```
📊 SOGLIE AUTOMATICHE:

🟢 0-79%: Normale
├── Nessuna notifica
└── Upload permesso

🟡 80-89%: Notice
├── Email automatica: "Storage al 80% - Considera un upgrade"
├── Banner giallo dashboard: "Storage quasi pieno"
└── Upload ancora permesso

🟠 90-94%: Warning
├── Email automatica: "Storage al 90% - Upgrade consigliato"
├── Banner arancione dashboard: "Storage in esaurimento"
├── Popup occasionale: "Vuoi richiedere più spazio?"
└── Upload ancora permesso

🔴 95-99%: Urgent
├── Email automatica: "Storage al 95% - Upgrade urgente"
├── Banner rosso fisso: "Storage quasi pieno - Richiedi upgrade"
├── Popup ad ogni login: "Solo 5% storage rimasto"
└── Upload ancora permesso

⛔ 100%: Critical - UPLOAD BLOCCATO
├── Email immediata: "Storage pieno - Upload bloccato"
├── Banner rosso lampeggiante: "STORAGE PIENO"
├── Redirect automatico a pagina upgrade
├── Tutti gli upload bloccati con messaggio chiaro
└── Admin deve fare richiesta esplicita upgrade
```

### **Processo Upgrade Esplicito**
```
🔄 FLUSSO UPGRADE:

1. 📱 Admin clicca "Richiedi Upgrade Storage"
2. 📋 Form selezione:
   ├── "Quanti GB aggiuntivi vuoi?" (slider: 1-50GB)
   ├── Preview pricing: "5GB × €2/mese = €10/mese"
   ├── "Totale mensile: €10 + IVA"
   └── Checkbox: "Accetto i termini di servizio"

3. 💳 Redirect PayPal:
   ├── PayPal checkout con dettagli subscription
   ├── Admin conferma pagamento su PayPal
   └── Return URL success/cancel

4. ✅ Post-Payment:
   ├── Webhook PayPal attiva nuovo limite
   ├── Email conferma: "Storage aumentato a XGB"
   ├── Dashboard aggiornata immediatamente
   └── Upload sbloccati automaticamente

5. 📧 Follow-up:
   ├── Email ricevuta PayPal
   ├── Prima fattura PayPal a fine mese
   └── Reminder renewal 7 giorni prima scadenza
```

---

## 📅 ROADMAP IMPLEMENTAZIONE

### **SETTIMANA 1: Database & Core Logic**
```
🎯 OBIETTIVO: Sistema base storage tracking
📅 TIMELINE: 5 giorni lavorativi

🔧 TASKS:
├── [2 giorni] Database migration e setup
│   ├── Aggiungere colonne storage a schools table
│   ├── Creare storage_purchases table
│   ├── Aggiungere indexes per performance
│   └── Seeder per dati di test
├── [2 giorni] Storage calculation logic
│   ├── Background job UpdateStorageUsage
│   ├── School model methods per usage tracking
│   ├── Cache Redis per performance
│   └── Middleware CheckStorageLimit
└── [1 giorno] Dashboard widget basic
    ├── Storage usage progress bar
    ├── Current usage vs limit display
    ├── Integration su admin dashboard
    └── Mobile responsive
```

### **SETTIMANA 2: Super-Admin Interface**
```
🎯 OBIETTIVO: Controllo completo per super-admin
📅 TIMELINE: 5 giorni lavorativi

🎨 SUPER-ADMIN DASHBOARD:
├── [2 giorni] Schools management interface
│   ├── Lista tutte le scuole con usage
│   ├── Modify storage limits per scuola
│   ├── Set custom pricing per scuola
│   └── Bulk operations per multiple schools
├── [2 giorni] Analytics e reporting
│   ├── Revenue dashboard
│   ├── Storage usage trends
│   ├── Schools over limit alerts
│   └── PayPal transaction history
└── [1 giorno] Configuration settings
    ├── Default storage limit per new schools
    ├── Default pricing per GB
    ├── Email templates configuration
    └── PayPal settings management
```

### **SETTIMANA 3: PayPal Integration**
```
🎯 OBIETTIVO: Pagamenti automatici funzionanti
📅 TIMELINE: 5 giorni lavorativi

💳 PAYPAL IMPLEMENTATION:
├── [2 giorni] Core PayPal integration
│   ├── PayPal business account setup
│   ├── PayPal SDK PHP integration
│   ├── Subscription creation logic
│   └── Webhook endpoint setup
├── [2 giorni] Admin upgrade interface
│   ├── Storage upgrade request form
│   ├── PayPal checkout integration
│   ├── Success/failure handling
│   └── Payment history display
└── [1 giorno] Automated workflows
    ├── Post-payment storage activation
    ├── Failed payment handling
    ├── Subscription cancellation logic
    └── Email notifications
```

### **SETTIMANA 4: Testing & Launch**
```
🎯 OBIETTIVO: Sistema production-ready
📅 TIMELINE: 5 giorni lavorativi

🧪 TESTING & OPTIMIZATION:
├── [2 giorni] Comprehensive testing
│   ├── Unit tests per storage calculations
│   ├── Integration tests PayPal flow
│   ├── Load testing con large files
│   └── Security audit upload process
├── [1 giorno] Performance optimization
│   ├── Query optimization per storage calculations
│   ├── Cache strategy refinement
│   ├── Background job scheduling
│   └── Database indexing review
├── [1 giorno] User experience polish
│   ├── Error messages improvement
│   ├── Loading states per PayPal flow
│   ├── Mobile responsiveness check
│   └── Accessibility review
└── [1 giorno] Documentation & launch prep
    ├── Admin user guide
    ├── Super-admin documentation
    ├── Deployment checklist
    └── Monitoring setup
```

---

## 📊 PROIEZIONI FINANZIARIE

### **Scenario Realistico (3 anni)**
```
Year 1: Foundation Building
├── 150 schools total (100 stay in 1GB free, 50 need extra storage)
├── Average overage: 3GB/school at €2/GB = €6/mese per paying school
├── Monthly Revenue: €300 (50 schools × €6)
├── Annual Revenue: €3,600
└── Development Investment: €15,000

Year 2: Growth & Adoption
├── 400 schools total (250 free, 150 need extra storage)
├── Average overage: 4GB/school at €2/GB = €8/mese per paying school
├── Monthly Revenue: €1,200 (150 schools × €8)
├── Annual Revenue: €14,400
└── Break-even achieved Q2

Year 3: Optimization & Scale
├── 800 schools total (400 free, 400 need extra storage)
├── Average overage: 5GB/school at €2/GB = €10/mese per paying school
├── Monthly Revenue: €4,000 (400 schools × €10)
├── Annual Revenue: €48,000
└── Profit margin: 85% (low operational costs)
```

### **Key Success Metrics**
```
📈 TRACKING:
├── Storage Usage Growth: Month-over-month per school
├── Conversion Rate: Free → Paid storage (target: 30-40%)
├── Revenue Per School: Average monthly overage spending
├── PayPal Success Rate: Payment completion rate (target: >95%)
├── Customer Satisfaction: Support tickets related to storage
└── System Performance: Storage calculation response times
```

---

## 🔧 IMMEDIATE NEXT STEPS

### **Technical Preparation**
1. **Database Design**: Finalize schema e create migrations
2. **PayPal Account**: Setup business account e get API credentials
3. **Development Environment**: Configure PayPal sandbox per testing
4. **Performance Planning**: Design caching strategy per storage calculations

### **Business Preparation**
1. **Pricing Strategy**: Validate €2/GB pricing con market research
2. **Legal Review**: Terms of service updates per billing
3. **Customer Communication**: Prepare announcement per existing users
4. **Support Training**: Team preparation per billing questions

### **Success Criteria**
- ✅ Storage calculation accurate entro 1MB
- ✅ PayPal integration success rate >95%
- ✅ Dashboard response time <500ms
- ✅ Zero data loss durante implementation
- ✅ Smooth user experience per upgrade flow

---

**🎯 Il focus è su semplicità, controllo super-admin, e fair pricing con PayPal come foundation per crescita sostenibile.**