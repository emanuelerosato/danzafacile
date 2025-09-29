@vite('resources/js/admin/settings/settings-manager.js')

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Impostazioni Scuola
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Configura i dati della scuola e le impostazioni per le ricevute
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Impostazioni</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8" x-data="settingsManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                @if (session('success'))
                    <div x-show="showSuccessAlert"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-95"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-95"
                         class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                        <div class="flex items-center justify-between">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium">{{ session('success') }}</p>
                                </div>
                            </div>
                            <button @click="dismissAlert()" type="button" class="ml-4 flex-shrink-0 inline-flex text-green-500 hover:text-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 rounded-lg p-1.5 transition-colors duration-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Key Statistics -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-stats-card
                        title="Configurazione"
                        :value="$stats['configured_settings'] . '/' . $stats['total_settings']"
                        :subtitle="'Impostazioni completate'"
                        icon="cog"
                        color="blue"
                        :change="null"
                    />

                    <x-stats-card
                        title="Stato PayPal"
                        :value="$stats['paypal_status'] === 'active' ? 'Attivo' : 'Disattivo'"
                        :subtitle="$stats['paypal_status'] === 'active' ? 'Pagamenti online abilitati' : 'Configura PayPal'"
                        icon="credit-card"
                        :color="$stats['paypal_status'] === 'active' ? 'green' : 'gray'"
                        :change="null"
                    />

                    <x-stats-card
                        title="Ricevute"
                        :value="$stats['receipt_configured'] ? 'Configurate' : 'Da configurare'"
                        :subtitle="'Template personalizzato'"
                        icon="document"
                        :color="$stats['receipt_configured'] ? 'green' : 'yellow'"
                        :change="null"
                    />

                    <x-stats-card
                        title="Dati Fiscali"
                        :value="!empty($settings['school_vat_number']) || !empty($settings['school_tax_code']) ? 'Completi' : 'Incompleti'"
                        :subtitle="'P.IVA e Cod. Fiscale'"
                        icon="shield-check"
                        :color="!empty($settings['school_vat_number']) || !empty($settings['school_tax_code']) ? 'green' : 'red'"
                        :change="null"
                    />
                </div>

                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6" @submit="handleSubmit()">
                    @csrf

                    <!-- Informazioni Generali -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Informazioni Generali
                            </h3>
                            <p class="text-sm text-gray-600">Dati principali della scuola</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="school_name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Nome Scuola <span class="text-red-500">*</span>
                                </label>
                                <input type="text" name="school_name" id="school_name" value="{{ old('school_name', $settings['school_name']) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                       required>
                                @error('school_name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="school_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Principale
                                </label>
                                <input type="email" name="school_email" id="school_email" value="{{ old('school_email', $settings['school_email']) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('school_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="school_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Telefono
                                </label>
                                <input type="text" name="school_phone" id="school_phone" value="{{ old('school_phone', $settings['school_phone']) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('school_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="school_website" class="block text-sm font-medium text-gray-700 mb-2">
                                    Sito Web
                                </label>
                                <input type="url" name="school_website" id="school_website" value="{{ old('school_website', $settings['school_website']) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                       placeholder="https://www.example.com">
                                @error('school_website')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label for="school_address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Indirizzo
                                </label>
                                <textarea name="school_address" id="school_address" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">{{ old('school_address', $settings['school_address']) }}</textarea>
                                @error('school_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="school_city" class="block text-sm font-medium text-gray-700 mb-2">
                                    Città
                                </label>
                                <input type="text" name="school_city" id="school_city" value="{{ old('school_city', $settings['school_city']) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('school_city')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="school_postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                                    CAP
                                </label>
                                <input type="text" name="school_postal_code" id="school_postal_code" value="{{ old('school_postal_code', $settings['school_postal_code']) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('school_postal_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Dati Fiscali -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Dati Fiscali
                            </h3>
                            <p class="text-sm text-gray-600">Informazioni necessarie per le ricevute</p>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="school_vat_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    Partita IVA
                                </label>
                                <input type="text" name="school_vat_number" id="school_vat_number" value="{{ old('school_vat_number', $settings['school_vat_number']) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                       placeholder="IT00000000000">
                                @error('school_vat_number')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="school_tax_code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Codice Fiscale
                                </label>
                                <input type="text" name="school_tax_code" id="school_tax_code" value="{{ old('school_tax_code', $settings['school_tax_code']) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                @error('school_tax_code')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Configurazione PayPal -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Pagamenti PayPal
                            </h3>
                            <p class="text-sm text-gray-600">Configura l'integrazione PayPal per i pagamenti online</p>
                        </div>

                        <div class="space-y-6">
                            <div class="flex items-center">
                                <div class="flex items-center h-5">
                                    <input type="hidden" name="paypal_enabled" value="0">
                                    <input type="checkbox" name="paypal_enabled" id="paypal_enabled" value="1"
                                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                                           {{ old('paypal_enabled', $settings['paypal_enabled'] ?? false) ? 'checked' : '' }}
                                           @change="togglePayPalSettings()"
                                           x-model="paypalEnabled">
                                </div>
                                <div class="ml-3 text-sm">
                                    <label for="paypal_enabled" class="font-medium text-gray-700">
                                        Abilita pagamenti PayPal
                                    </label>
                                    <p class="text-gray-500">Permetti agli studenti di pagare tramite PayPal</p>
                                </div>
                            </div>

                            <div x-show="showPayPalSettings"
                                 x-transition:enter="transition ease-out duration-300"
                                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                                 x-transition:enter-end="opacity-100 transform translate-y-0"
                                 x-transition:leave="transition ease-in duration-200"
                                 x-transition:leave-start="opacity-100 transform translate-y-0"
                                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                                 class="space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="paypal_mode" class="block text-sm font-medium text-gray-700 mb-2">
                                            Modalità PayPal <span class="text-red-500">*</span>
                                        </label>
                                        <select name="paypal_mode" id="paypal_mode"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                            <option value="sandbox" {{ old('paypal_mode', $settings['paypal_mode'] ?? 'sandbox') === 'sandbox' ? 'selected' : '' }}>
                                                Sandbox (Test)
                                            </option>
                                            <option value="live" {{ old('paypal_mode', $settings['paypal_mode'] ?? 'sandbox') === 'live' ? 'selected' : '' }}>
                                                Live (Produzione)
                                            </option>
                                        </select>
                                        @error('paypal_mode')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-xs text-gray-500">Usa Sandbox per i test, Live per i pagamenti reali</p>
                                    </div>

                                    <div>
                                        <label for="paypal_currency" class="block text-sm font-medium text-gray-700 mb-2">
                                            Valuta <span class="text-red-500">*</span>
                                        </label>
                                        <select name="paypal_currency" id="paypal_currency"
                                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                            <option value="EUR" {{ old('paypal_currency', $settings['paypal_currency'] ?? 'EUR') === 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                            <option value="USD" {{ old('paypal_currency', $settings['paypal_currency'] ?? 'EUR') === 'USD' ? 'selected' : '' }}>USD - Dollaro USA</option>
                                            <option value="GBP" {{ old('paypal_currency', $settings['paypal_currency'] ?? 'EUR') === 'GBP' ? 'selected' : '' }}>GBP - Sterlina</option>
                                        </select>
                                        @error('paypal_currency')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                <div>
                                    <label for="paypal_client_id" class="block text-sm font-medium text-gray-700 mb-2">
                                        PayPal Client ID <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="paypal_client_id" id="paypal_client_id"
                                           value="{{ old('paypal_client_id', $settings['paypal_client_id'] ?? '') }}"
                                           placeholder="Es. AYSq3RDGsmBLJE-otTkBtM-jBRd1TCQwFf9RGfwddNXWz0uFU9ztymylOhRS"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                    @error('paypal_client_id')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Ottenibile dalla PayPal Developer Console</p>
                                </div>

                                <div>
                                    <label for="paypal_client_secret" class="block text-sm font-medium text-gray-700 mb-2">
                                        PayPal Client Secret <span class="text-red-500">*</span>
                                    </label>
                                    <input type="password" name="paypal_client_secret" id="paypal_client_secret"
                                           value="{{ old('paypal_client_secret', $settings['paypal_client_secret'] ?? '') }}"
                                           placeholder="Es. EGnHDxD_qRPdaLdZz8iCr8N7_MzF-YHPTkjs6NKYQvQSBngp4PTTVWkPZRbL"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                    @error('paypal_client_secret')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                    <p class="mt-1 text-xs text-gray-500">Mantieni questo valore privato e sicuro</p>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="paypal_fee_percentage" class="block text-sm font-medium text-gray-700 mb-2">
                                            Commissione PayPal (%)
                                        </label>
                                        <input type="number" name="paypal_fee_percentage" id="paypal_fee_percentage" step="0.01" min="0" max="100"
                                               value="{{ old('paypal_fee_percentage', $settings['paypal_fee_percentage'] ?? '3.4') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                        @error('paypal_fee_percentage')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-xs text-gray-500">Percentuale commissioni PayPal (default: 3.4%)</p>
                                    </div>

                                    <div>
                                        <label for="paypal_fixed_fee" class="block text-sm font-medium text-gray-700 mb-2">
                                            Commissione Fissa (€)
                                        </label>
                                        <input type="number" name="paypal_fixed_fee" id="paypal_fixed_fee" step="0.01" min="0"
                                               value="{{ old('paypal_fixed_fee', $settings['paypal_fixed_fee'] ?? '0.35') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors duration-200">
                                        @error('paypal_fixed_fee')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                        <p class="mt-1 text-xs text-gray-500">Commissione fissa per transazione (default: €0.35)</p>
                                    </div>
                                </div>

                                <div>
                                    <label for="paypal_webhook_url" class="block text-sm font-medium text-gray-700 mb-2">
                                        Webhook URL (Solo lettura)
                                    </label>
                                    <input type="text" id="paypal_webhook_url" readonly
                                           value="{{ url('/webhook/paypal') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                                    <p class="mt-1 text-xs text-gray-500">Configura questo URL nella PayPal Developer Console per ricevere notifiche dei pagamenti</p>
                                </div>

                                <!-- PayPal Test Info -->
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h3 class="text-sm font-medium text-blue-800">Come configurare PayPal</h3>
                                            <div class="mt-2 text-sm text-blue-700">
                                                <ol class="list-decimal list-inside space-y-1">
                                                    <li>Vai su <a href="https://developer.paypal.com" target="_blank" class="underline">developer.paypal.com</a></li>
                                                    <li>Crea una nuova App PayPal</li>
                                                    <li>Copia Client ID e Client Secret qui</li>
                                                    <li>Configura il Webhook URL nella console PayPal</li>
                                                    <li>Attiva gli eventi: PAYMENT.CAPTURE.COMPLETED, PAYMENT.CAPTURE.DENIED</li>
                                                </ol>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Configurazione Ricevute -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Configurazione Ricevute
                            </h3>
                            <p class="text-sm text-gray-600">Personalizza l'aspetto delle ricevute generate</p>
                        </div>

                        <div class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="receipt_logo_url" class="block text-sm font-medium text-gray-700 mb-2">
                                        URL Logo
                                    </label>
                                    <input type="url" name="receipt_logo_url" id="receipt_logo_url" value="{{ old('receipt_logo_url', $settings['receipt_logo_url']) }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                           placeholder="https://www.example.com/logo.png">
                                    @error('receipt_logo_url')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center">
                                    <div class="flex items-center h-5">
                                        <input type="checkbox" name="receipt_show_logo" id="receipt_show_logo" value="1"
                                               class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded"
                                               {{ old('receipt_show_logo', $settings['receipt_show_logo']) ? 'checked' : '' }}>
                                    </div>
                                    <div class="ml-3 text-sm">
                                        <label for="receipt_show_logo" class="font-medium text-gray-700">
                                            Mostra logo nelle ricevute
                                        </label>
                                        <p class="text-gray-500">Se selezionato, il logo apparirà nelle ricevute generate</p>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="receipt_header_text" class="block text-sm font-medium text-gray-700 mb-2">
                                    Testo Header Ricevuta
                                </label>
                                <textarea name="receipt_header_text" id="receipt_header_text" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                          placeholder="Testo che apparirà nella parte superiore della ricevuta">{{ old('receipt_header_text', $settings['receipt_header_text']) }}</textarea>
                                @error('receipt_header_text')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="receipt_footer_text" class="block text-sm font-medium text-gray-700 mb-2">
                                    Testo Footer Ricevuta
                                </label>
                                <textarea name="receipt_footer_text" id="receipt_footer_text" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                          placeholder="Testo che apparirà nella parte inferiore della ricevuta">{{ old('receipt_footer_text', $settings['receipt_footer_text']) }}</textarea>
                                @error('receipt_footer_text')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="receipt_notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Note Aggiuntive
                                </label>
                                <textarea name="receipt_notes" id="receipt_notes" rows="3"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                          placeholder="Note che appariranno nelle ricevute">{{ old('receipt_notes', $settings['receipt_notes']) }}</textarea>
                                @error('receipt_notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Impostazioni Pagamento -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                                Impostazioni Pagamento
                            </h3>
                            <p class="text-sm text-gray-600">Termini di pagamento e coordinate bancarie</p>
                        </div>

                        <div class="space-y-6">
                            <div>
                                <label for="payment_terms" class="block text-sm font-medium text-gray-700 mb-2">
                                    Termini di Pagamento
                                </label>
                                <textarea name="payment_terms" id="payment_terms" rows="2"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                          placeholder="Pagamento da effettuare entro 30 giorni">{{ old('payment_terms', $settings['payment_terms']) }}</textarea>
                                @error('payment_terms')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="payment_bank_details" class="block text-sm font-medium text-gray-700 mb-2">
                                    Coordinate Bancarie
                                </label>
                                <textarea name="payment_bank_details" id="payment_bank_details" rows="4"
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                          placeholder="Banca: Nome Banca&#10;IBAN: IT00 0000 0000 0000 0000 0000 000&#10;BIC/SWIFT: XXXXXXXXXXXX">{{ old('payment_bank_details', $settings['payment_bank_details']) }}</textarea>
                                @error('payment_bank_details')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Pulsanti Azione -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3">
                            <a href="{{ route('admin.dashboard') }}"
                               class="inline-flex justify-center items-center px-6 py-3 border border-gray-300 shadow-sm text-base font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Annulla
                            </a>

                            <button type="submit"
                                    :disabled="isSubmitting"
                                    class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 shadow-sm transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                                <svg x-show="!isSubmitting" class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg x-show="isSubmitting" class="w-5 h-5 mr-2 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="isSubmitting ? 'Salvataggio...' : 'Salva Impostazioni'"></span>
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>