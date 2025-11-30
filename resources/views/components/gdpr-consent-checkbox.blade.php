@props([
    'name' => 'consent',
    'type' => 'privacy', // privacy, marketing, terms, cookies, newsletter
    'required' => false,
    'checked' => false,
    'id' => null,
    'error' => null,
])

@php
    $checkboxId = $id ?? 'gdpr_' . $name . '_' . uniqid();

    // Testi predefiniti per ogni tipo di consenso
    $consentTexts = [
        'privacy' => [
            'label' => 'Ho letto e accetto l\'',
            'linkText' => 'Informativa Privacy',
            'linkRoute' => 'privacy-policy',
            'required' => true,
        ],
        'cookies' => [
            'label' => 'Accetto l\'utilizzo dei cookie come descritto nella ',
            'linkText' => 'Cookie Policy',
            'linkRoute' => 'cookie-policy',
            'required' => false,
        ],
        'terms' => [
            'label' => 'Accetto i ',
            'linkText' => 'Termini e Condizioni del Servizio',
            'linkRoute' => 'terms-conditions', // Da creare se necessario
            'required' => true,
        ],
        'marketing' => [
            'label' => 'Acconsento all\'invio di comunicazioni commerciali e promozionali',
            'linkText' => 'Maggiori informazioni',
            'linkRoute' => 'privacy-policy',
            'required' => false,
        ],
        'newsletter' => [
            'label' => 'Desidero ricevere la newsletter con aggiornamenti e novità',
            'linkText' => 'Info newsletter',
            'linkRoute' => 'privacy-policy',
            'required' => false,
        ],
    ];

    $config = $consentTexts[$type] ?? $consentTexts['privacy'];
    $isRequired = $required || $config['required'];
@endphp

<div class="flex items-start" x-data="{ checked: {{ $checked ? 'true' : 'false' }} }">
    <div class="flex items-center h-5">
        <input
            type="checkbox"
            name="{{ $name }}"
            id="{{ $checkboxId }}"
            value="1"
            x-model="checked"
            @if($isRequired) required @endif
            @if($checked) checked @endif
            class="w-4 h-4 text-rose-600 bg-gray-100 border-gray-300 rounded focus:ring-rose-500 focus:ring-2 transition-colors duration-200
                   @error($name) border-red-500 @enderror"
        >
    </div>
    <div class="ml-3 text-sm">
        <label for="{{ $checkboxId }}" class="font-medium text-gray-700 select-none cursor-pointer">
            {{ $config['label'] }}
            <a href="{{ route($config['linkRoute']) }}"
               target="_blank"
               rel="noopener noreferrer"
               class="text-rose-600 hover:text-rose-800 underline font-semibold transition-colors duration-200">
                {{ $config['linkText'] }}
            </a>
            @if($isRequired)
                <span class="text-red-500 font-bold ml-1">*</span>
            @endif
        </label>

        @if($type === 'marketing' || $type === 'newsletter')
            <p class="text-xs text-gray-500 mt-1">
                Puoi revocare il consenso in qualsiasi momento dalle impostazioni del tuo profilo.
            </p>
        @endif

        @if($type === 'privacy' || $type === 'terms')
            <p class="text-xs text-gray-500 mt-1">
                Il consenso è obbligatorio per utilizzare il servizio.
            </p>
        @endif

        <!-- Error Message -->
        @if($error || $errors->has($name))
            <p class="text-red-500 text-xs mt-1 flex items-center">
                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                {{ $error ?? $errors->first($name) }}
            </p>
        @enderror
    </div>
</div>

@push('scripts')
<script nonce="@cspNonce">
    // Validazione client-side per GDPR checkbox
    document.addEventListener('DOMContentLoaded', function() {
        const checkbox = document.getElementById('{{ $checkboxId }}');
        if (checkbox && checkbox.hasAttribute('required')) {
            const form = checkbox.closest('form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    if (!checkbox.checked) {
                        e.preventDefault();
                        checkbox.focus();

                        // Mostra errore visivo
                        checkbox.classList.add('border-red-500', 'ring-2', 'ring-red-500');

                        // Mostra toast error
                        if (window.Toast) {
                            Toast.error('Devi accettare {{ strtolower($config["linkText"]) }} per continuare.');
                        } else {
                            alert('Devi accettare {{ strtolower($config["linkText"]) }} per continuare.');
                        }

                        // Rimuovi classe errore dopo 3 secondi
                        setTimeout(() => {
                            checkbox.classList.remove('border-red-500', 'ring-2', 'ring-red-500');
                        }, 3000);
                    }
                });
            }
        }
    });
</script>
@endpush
