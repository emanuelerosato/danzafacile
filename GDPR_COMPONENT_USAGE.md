# GDPR Consent Checkbox Component - Guida d'Uso

## Panoramica

Il componente `<x-gdpr-consent-checkbox>` è un componente Blade riutilizzabile che gestisce i consensi GDPR in modo standardizzato e conforme alle normative.

## File Creati

- **Component:** `/resources/views/components/gdpr-consent-checkbox.blade.php`
- **Privacy Policy:** `/resources/views/privacy-policy.blade.php`
- **Cookie Policy:** `/resources/views/cookie-policy.blade.php`
- **Routes:** Configurate in `/routes/web.php`

## Utilizzo Base

### 1. Privacy Policy (Obbligatorio)

```blade
<x-gdpr-consent-checkbox
    name="privacy"
    type="privacy"
    :required="true"
/>
```

**Output:**
```
☑ Ho letto e accetto l'Informativa Privacy *
```

### 2. Cookie Consent

```blade
<x-gdpr-consent-checkbox
    name="cookies"
    type="cookies"
    :required="false"
/>
```

**Output:**
```
☐ Accetto l'utilizzo dei cookie come descritto nella Cookie Policy
```

### 3. Terms & Conditions

```blade
<x-gdpr-consent-checkbox
    name="terms"
    type="terms"
    :required="true"
/>
```

**Output:**
```
☑ Accetto i Termini e Condizioni del Servizio *
```

### 4. Marketing Consent

```blade
<x-gdpr-consent-checkbox
    name="marketing"
    type="marketing"
    :required="false"
/>
```

**Output:**
```
☐ Acconsento all'invio di comunicazioni commerciali e promozionali
Puoi revocare il consenso in qualsiasi momento dalle impostazioni del tuo profilo.
```

### 5. Newsletter Subscription

```blade
<x-gdpr-consent-checkbox
    name="newsletter"
    type="newsletter"
    :required="false"
/>
```

**Output:**
```
☐ Desidero ricevere la newsletter con aggiornamenti e novità
Puoi revocare il consenso in qualsiasi momento dalle impostazioni del tuo profilo.
```

## Parametri Disponibili

| Parametro | Tipo | Default | Descrizione |
|-----------|------|---------|-------------|
| `name` | string | 'consent' | Nome del campo input |
| `type` | string | 'privacy' | Tipo di consenso: privacy, cookies, terms, marketing, newsletter |
| `required` | boolean | false | Se il consenso è obbligatorio |
| `checked` | boolean | false | Stato iniziale della checkbox |
| `id` | string | auto-generated | ID univoco dell'elemento |
| `error` | string | null | Messaggio di errore personalizzato |

## Esempi di Form Completi

### Form Registrazione Evento Pubblico

```blade
<form method="POST" action="{{ route('public.events.register', $event) }}">
    @csrf

    <!-- Dati Utente -->
    <div class="space-y-4">
        <x-form-input name="name" label="Nome e Cognome" required />
        <x-form-input name="email" type="email" label="Email" required />
        <x-form-input name="phone" label="Telefono" required />
    </div>

    <!-- GDPR Consents -->
    <div class="mt-6 space-y-3 border-t border-gray-200 pt-6">
        <h3 class="font-semibold text-gray-900 mb-3">Consensi Privacy</h3>

        <!-- Privacy Policy - OBBLIGATORIO -->
        <x-gdpr-consent-checkbox
            name="privacy_consent"
            type="privacy"
            :required="true"
        />

        <!-- Marketing - OPZIONALE -->
        <x-gdpr-consent-checkbox
            name="marketing_consent"
            type="marketing"
            :required="false"
        />

        <!-- Newsletter - OPZIONALE -->
        <x-gdpr-consent-checkbox
            name="newsletter_consent"
            type="newsletter"
            :required="false"
        />
    </div>

    <!-- reCAPTCHA (se abilitato) -->
    @if(config('services.recaptcha.enabled'))
        <input type="hidden" name="g-recaptcha-response" id="recaptcha-token">
    @endif

    <!-- Submit -->
    <button type="submit" class="mt-6 w-full bg-gradient-to-r from-rose-500 to-purple-600 text-white py-3 rounded-lg">
        Conferma Iscrizione
    </button>
</form>
```

### Validazione Backend

```php
public function register(Request $request, Event $event)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'phone' => 'required|string|max:20',
        'privacy_consent' => 'required|accepted', // OBBLIGATORIO
        'marketing_consent' => 'nullable|boolean',
        'newsletter_consent' => 'nullable|boolean',
        'g-recaptcha-response' => ['required', new \App\Rules\Recaptcha],
    ], [
        'privacy_consent.required' => 'Devi accettare l\'Informativa Privacy per proseguire.',
        'privacy_consent.accepted' => 'Devi accettare l\'Informativa Privacy per proseguire.',
    ]);

    // Processa iscrizione...
}
```

## Gestione Errori

Il componente supporta automaticamente la visualizzazione degli errori di validazione Laravel:

```blade
<!-- Errore automatico da Laravel validation -->
<x-gdpr-consent-checkbox
    name="privacy_consent"
    type="privacy"
    :required="true"
/>

<!-- Errore personalizzato -->
<x-gdpr-consent-checkbox
    name="privacy_consent"
    type="privacy"
    :required="true"
    error="Devi accettare la privacy policy"
/>
```

## Features Incluse

### 1. Validazione Client-Side
- Previene l'invio del form se checkbox obbligatorie non sono spuntate
- Mostra messaggio di errore con Toast notification
- Highlight visivo della checkbox mancante

### 2. Link External
- Tutti i link a policy si aprono in nuova tab (`target="_blank"`)
- Attributo `rel="noopener noreferrer"` per sicurezza

### 3. Responsive Design
- Layout ottimizzato per mobile e desktop
- Testo leggibile su tutti i dispositivi

### 4. Accessibilità
- Label associate correttamente agli input
- Focus states ben definiti
- Supporto screen readers

## Routes Disponibili

```php
Route::get('/privacy-policy', ...)->name('privacy-policy');
Route::get('/cookie-policy', ...)->name('cookie-policy');
```

**URL:**
- Privacy Policy: `https://tuodominio.com/privacy-policy`
- Cookie Policy: `https://tuodominio.com/cookie-policy`

## Personalizzazione

### Modifica Testi

Per modificare i testi predefiniti, edita il componente:

```php
// resources/views/components/gdpr-consent-checkbox.blade.php

$consentTexts = [
    'privacy' => [
        'label' => 'Il tuo testo personalizzato',
        'linkText' => 'Privacy Policy',
        'linkRoute' => 'privacy-policy',
        'required' => true,
    ],
    // ...
];
```

### Aggiungere Nuovi Tipi

```php
$consentTexts = [
    // ... tipi esistenti ...

    'custom_consent' => [
        'label' => 'Accetto il mio consenso personalizzato',
        'linkText' => 'Maggiori info',
        'linkRoute' => 'custom-page',
        'required' => false,
    ],
];
```

Utilizzo:
```blade
<x-gdpr-consent-checkbox
    name="custom"
    type="custom_consent"
/>
```

## Best Practices

1. **Privacy Consent**: Sempre obbligatorio per iscrizioni e registrazioni
2. **Marketing Consent**: Sempre opzionale, con possibilità di revoca
3. **Cookie Consent**: Gestire con cookie banner separato per UX migliore
4. **Terms Consent**: Obbligatorio solo per creazione account

## Compliance GDPR

Il componente è progettato per essere conforme a:
- **GDPR** (Regolamento UE 2016/679)
- **ePrivacy Directive**
- **Cookie Law Italiana**

Assicurati di:
- ✅ Memorizzare timestamp dei consensi
- ✅ Permettere revoca consensi
- ✅ Fornire export dati personali
- ✅ Implementare diritto all'oblio

## Testing

### Test Manuale

1. Visita: `http://localhost:8089/privacy-policy`
2. Verifica contenuto e layout
3. Testa link esterni
4. Verifica responsive design

### Test Componente

```blade
<!-- resources/views/test-gdpr.blade.php -->
<x-guest-layout>
    <div class="space-y-6">
        <h2 class="text-2xl font-bold">Test GDPR Components</h2>

        <x-gdpr-consent-checkbox name="test1" type="privacy" :required="true" />
        <x-gdpr-consent-checkbox name="test2" type="cookies" />
        <x-gdpr-consent-checkbox name="test3" type="marketing" />
        <x-gdpr-consent-checkbox name="test4" type="newsletter" />
    </div>
</x-guest-layout>
```

## Troubleshooting

### Checkbox non visualizzata
```bash
php artisan view:clear
php artisan config:clear
```

### Route non trovata
```bash
php artisan route:clear
php artisan route:list --path=privacy
```

### Errori di validazione non mostrati
Verifica che il form abbia `@csrf` e che il name corrisponda alla validazione backend.

## Supporto

Per domande o problemi:
- Consulta `/resources/views/components/gdpr-consent-checkbox.blade.php`
- Verifica `/routes/web.php` per le route
- Controlla Laravel logs: `storage/logs/laravel.log`
