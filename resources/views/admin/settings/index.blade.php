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

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
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
                    </div>
                @endif

                <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
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
                                    class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-lg text-white bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 shadow-sm transition-all duration-200 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Salva Impostazioni
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>