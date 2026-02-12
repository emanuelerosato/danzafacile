<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Nuovo Evento
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione nuovo evento della tua scuola
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
        <li class="text-gray-900 font-medium">Nuovo Evento</li>
    </x-slot>



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Crea Nuovo Evento</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Crea un nuovo evento per la tua scuola
                </p>
            </div>
            <a href="{{ route('admin.events.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Torna agli Eventi
            </a>
        </div>

        <!-- Autosave Draft Notification -->
        <div x-data="{ showDraftAlert: false }"
             x-init="showDraftAlert = localStorage.getItem('event-draft') !== null"
             x-show="showDraftAlert"
             x-transition
             class="mb-4 bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-center justify-between">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-blue-800">
                    <strong>Bozza trovata!</strong> Hai una bozza salvata automaticamente. Vuoi ripristinarla?
                </p>
            </div>
            <div class="flex space-x-2">
                <button type="button"
                        @click="loadDraft(); showDraftAlert = false"
                        class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-md transition-colors">
                    Ripristina
                </button>
                <button type="button"
                        @click="localStorage.removeItem('event-draft'); showDraftAlert = false"
                        class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-700 text-sm rounded-md transition-colors">
                    Ignora
                </button>
            </div>
        </div>

        <!-- Autosave Status Badge -->
        <div x-data="{ showSaved: false }"
             @draft-saved.window="showSaved = true; setTimeout(() => showSaved = false, 2000)"
             x-show="showSaved"
             x-transition
             class="mb-4">
            <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex items-center">
                <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm text-green-800">Bozza salvata automaticamente</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <form action="{{ route('admin.events.store') }}"
                  method="POST"
                  id="createEventForm"
                  class="p-6"
                  enctype="multipart/form-data"
                  x-data="{
                      ...eventFormValidation(),
                      ...autosaveManager(),
                      ...imageUploadManager(),
                      submitting: false,
                      // Character counters
                      nameChars: 0,
                      descriptionChars: 0,
                      locationChars: 0
                  }"
                  x-init="loadDraft()"
                  @input.debounce.1000ms="saveDraft()"
                  @submit="if(validateDates($event)) { submitting = true; clearDraft() }">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Informazioni Base -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Informazioni Base</h3>

                        <!-- Nome -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nome Evento <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   maxlength="255"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('name') border-red-500 @enderror"
                                   value="{{ old('name') }}"
                                   x-model="nameChars"
                                   @input="nameChars = $event.target.value"
                                   aria-label="Nome evento"
                                   aria-describedby="name-help name-counter"
                                   aria-required="true"
                                   required>
                            <div class="flex items-center justify-between mt-1">
                                <p id="name-help" class="text-xs text-gray-500">
                                    Inserisci un nome descrittivo per l'evento
                                </p>
                                <p id="name-counter" class="text-xs" aria-live="polite"
                                   :class="{
                                       'text-gray-600': nameChars.length < 204,
                                       'text-yellow-600': nameChars.length >= 204 && nameChars.length < 242,
                                       'text-red-600': nameChars.length >= 242
                                   }">
                                    <span x-text="nameChars.length || 0"></span>/255 caratteri
                                </p>
                            </div>
                            @error('name')
                                <p id="name-error" role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descrizione -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Descrizione
                            </label>
                            <textarea id="description"
                                      name="description"
                                      rows="6"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('description') border-red-500 @enderror"
                                      x-model="descriptionChars"
                                      @input="descriptionChars = $event.target.value"
                                      aria-label="Descrizione evento"
                                      aria-describedby="description-help"
                                      placeholder="Inserisci una descrizione completa dell'evento...">{{ old('description') }}</textarea>
                            <div class="flex items-center justify-between mt-1">
                                <p id="description-help" class="text-xs text-gray-500">
                                    Descrivi l'evento in dettaglio. Puoi usare la formattazione di base.
                                </p>
                                <p class="text-xs text-gray-600" aria-live="polite">
                                    <span x-text="descriptionChars.length || 0"></span> caratteri
                                </p>
                            </div>
                            @error('description')
                                <p role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tipo -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">
                                Tipo Evento *
                            </label>
                            <select id="type" name="type"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('type') border-red-500 @enderror" required>
                                <option value="">Seleziona tipo...</option>
                                @foreach($eventTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('type') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Localizzazione -->
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700 mb-1">
                                Luogo
                            </label>
                            <input type="text"
                                   id="location"
                                   name="location"
                                   maxlength="255"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('location') border-red-500 @enderror"
                                   value="{{ old('location') }}"
                                   x-model="locationChars"
                                   @input="locationChars = $event.target.value"
                                   aria-label="Luogo evento"
                                   aria-describedby="location-help location-counter"
                                   placeholder="es. Auditorium Scuola, Via Roma 123">
                            <div class="flex items-center justify-between mt-1">
                                <p id="location-help" class="text-xs text-gray-500">
                                    Indirizzo o nome della location
                                </p>
                                <p id="location-counter" class="text-xs" aria-live="polite"
                                   :class="{
                                       'text-gray-600': locationChars.length < 204,
                                       'text-yellow-600': locationChars.length >= 204 && locationChars.length < 242,
                                       'text-red-600': locationChars.length >= 242
                                   }">
                                    <span x-text="locationChars.length || 0"></span>/255 caratteri
                                </p>
                            </div>
                            @error('location')
                                <p role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Date e Configurazione -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Date e Configurazione</h3>

                        <!-- Data Inizio -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Data e Ora Inizio *
                            </label>
                            <input type="datetime-local" id="start_date" name="start_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('start_date') border-red-500 @enderror"
                                   value="{{ old('start_date') }}" required>
                            @error('start_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Data Fine -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Data e Ora Fine *
                            </label>
                            <input type="datetime-local" id="end_date" name="end_date"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('end_date') border-red-500 @enderror"
                                   value="{{ old('end_date') }}" required>
                            @error('end_date')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Numero Max Partecipanti -->
                        <div>
                            <label for="max_participants" class="block text-sm font-medium text-gray-700 mb-1">
                                Numero Massimo Partecipanti
                            </label>
                            <input type="number" id="max_participants" name="max_participants" min="1"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('max_participants') border-red-500 @enderror"
                                   value="{{ old('max_participants') }}"
                                   placeholder="Lascia vuoto per illimitato">
                            @error('max_participants')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Prezzo -->
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 mb-1">
                                Prezzo (€)
                            </label>
                            <input type="number" id="price" name="price" min="0" step="0.01"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('price') border-red-500 @enderror"
                                   value="{{ old('price') }}"
                                   placeholder="0.00 per evento gratuito">
                            @error('price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Data Limite Registrazione -->
                        <div x-data="{ requiresRegistration: {{ old('requires_registration', 1) ? 'true' : 'false' }} }">
                            <div class="flex items-center mb-2">
                                <input type="checkbox" id="requires_registration" name="requires_registration" value="1"
                                       class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded"
                                       :checked="requiresRegistration"
                                       @change="requiresRegistration = $event.target.checked">
                                <label for="requires_registration" class="ml-2 text-sm font-medium text-gray-700">
                                    Richiede Registrazione
                                </label>
                            </div>

                            <div x-show="requiresRegistration" x-transition>
                                <label for="registration_deadline" class="block text-sm font-medium text-gray-700 mb-1">
                                    Scadenza Registrazione
                                </label>
                                <input type="datetime-local" id="registration_deadline" name="registration_deadline"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('registration_deadline') border-red-500 @enderror"
                                       value="{{ old('registration_deadline') }}">
                                @error('registration_deadline')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Requisiti -->
                <div class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Requisiti (Opzionale)</h3>
                    <div x-data="{ requirements: {{ json_encode(old('requirements', [])) }} }">
                        <div class="space-y-2" id="requirements-container">
                            <template x-for="(requirement, index) in requirements" :key="index">
                                <div class="flex items-center space-x-2">
                                    <input type="text" :name="`requirements[${index}]`" x-model="requirements[index]"
                                           class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                           placeholder="Inserisci un requisito...">
                                    <button type="button" @click="requirements.splice(index, 1)"
                                            class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-md transition-colors duration-200">
                                        <i class="fas fa-trash text-sm"></i>
                                    </button>
                                </div>
                            </template>
                        </div>
                        <button type="button" @click="requirements.push('')"
                                class="mt-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>
                            Aggiungi Requisito
                        </button>
                    </div>
                </div>

                <!-- Media e Collegamenti -->
                <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Locandina Evento -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Locandina Evento</h3>
                        <div>
                            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-image mr-1 text-rose-500"></i>
                                Carica Immagine Locandina
                            </label>
                            <input type="file"
                                   id="image"
                                   name="image"
                                   accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('image') border-red-500 @enderror"
                                   @change="handleImageUpload($event)"
                                   aria-label="Carica immagine locandina"
                                   aria-describedby="image-help">
                            <p id="image-help" class="mt-1 text-xs text-gray-500">
                                Formati supportati: JPG, PNG, GIF, WEBP. Max 5MB
                            </p>
                            @error('image')
                                <p role="alert" class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <!-- Upload Error Message -->
                            <div x-show="imageError" x-transition class="mt-2">
                                <p class="text-sm text-red-600" role="alert">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    <span x-text="imageError"></span>
                                </p>
                            </div>

                            <!-- Image Preview with Details -->
                            <div x-show="imagePreview" x-transition class="mt-4">
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <img :src="imagePreview"
                                         alt="Anteprima locandina"
                                         class="max-w-full h-48 object-cover rounded-lg mx-auto shadow-md">
                                    <div class="mt-3 flex items-center justify-between text-xs text-gray-600">
                                        <span>
                                            <i class="fas fa-file-image mr-1"></i>
                                            <span x-text="imageFileName"></span>
                                        </span>
                                        <span>
                                            <i class="fas fa-weight mr-1"></i>
                                            <span x-text="imageFileSize"></span>
                                        </span>
                                    </div>
                                    <button type="button"
                                            @click="clearImageUpload()"
                                            class="mt-2 w-full px-3 py-1 bg-red-50 hover:bg-red-100 text-red-700 text-sm rounded-md transition-colors">
                                        <i class="fas fa-times mr-1"></i>
                                        Rimuovi immagine
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Collegamenti -->
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2 mb-4">Collegamenti Esterni</h3>

                        <!-- Link Sito Esterno -->
                        <div class="mb-4">
                            <label for="external_link" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-link mr-1 text-blue-500"></i>
                                Link Sito Esterno
                            </label>
                            <input type="url" id="external_link" name="external_link"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('external_link') border-red-500 @enderror"
                                   value="{{ old('external_link') }}"
                                   placeholder="https://esempio.com">
                            <p class="mt-1 text-xs text-gray-500">
                                Link a sito web esterno relativo all'evento
                            </p>
                            @error('external_link')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Link Pagina Social -->
                        <div>
                            <label for="social_link" class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-share-alt mr-1 text-purple-500"></i>
                                Link Pagina Social
                            </label>
                            <input type="url" id="social_link" name="social_link"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('social_link') border-red-500 @enderror"
                                   value="{{ old('social_link') }}"
                                   placeholder="https://facebook.com/evento">
                            <p class="mt-1 text-xs text-gray-500">
                                Link a evento/pagina su Facebook, Instagram, etc.
                            </p>
                            @error('social_link')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- SEO Metadata (Eventi Pubblici) -->
                <div class="mt-6" x-data="{ showSeo: {{ old('is_public', true) ? 'true' : 'false' }} }">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-medium text-gray-900">SEO & Social Media</h3>
                        <button type="button"
                                @click="showSeo = !showSeo"
                                class="text-sm text-gray-600 hover:text-gray-900">
                            <i class="fas" :class="showSeo ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                            <span x-text="showSeo ? 'Nascondi' : 'Mostra'"></span>
                        </button>
                    </div>

                    <div x-show="showSeo" x-transition class="space-y-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                        <p class="text-sm text-gray-600 mb-4">
                            <i class="fas fa-info-circle mr-1"></i>
                            Personalizza come l'evento apparirà quando condiviso sui social media (Facebook, Instagram, WhatsApp, etc.)
                        </p>

                        <!-- OG Title -->
                        <div x-data="{ ogTitleChars: '' }">
                            <label for="seo_og_title" class="block text-sm font-medium text-gray-700 mb-1">
                                Titolo Social (Open Graph)
                            </label>
                            <input type="text"
                                   id="seo_og_title"
                                   name="seo_og_title"
                                   maxlength="100"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   value="{{ old('seo_og_title') }}"
                                   x-model="ogTitleChars"
                                   @input="ogTitleChars = $event.target.value"
                                   placeholder="Lascia vuoto per usare il nome evento">
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-xs text-gray-500">
                                    Titolo ottimizzato per condivisioni social (lascia vuoto per default)
                                </p>
                                <p class="text-xs text-gray-600" aria-live="polite">
                                    <span x-text="ogTitleChars.length || 0"></span>/100 caratteri
                                </p>
                            </div>
                        </div>

                        <!-- OG Description -->
                        <div x-data="{ ogDescChars: '' }">
                            <label for="seo_og_description" class="block text-sm font-medium text-gray-700 mb-1">
                                Descrizione Social (Open Graph)
                            </label>
                            <textarea id="seo_og_description"
                                      name="seo_og_description"
                                      rows="3"
                                      maxlength="200"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                      x-model="ogDescChars"
                                      @input="ogDescChars = $event.target.value"
                                      placeholder="Lascia vuoto per usare la descrizione evento">{{ old('seo_og_description') }}</textarea>
                            <div class="flex items-center justify-between mt-1">
                                <p class="text-xs text-gray-500">
                                    Descrizione breve per anteprima social
                                </p>
                                <p class="text-xs" aria-live="polite"
                                   :class="{
                                       'text-gray-600': ogDescChars.length < 160,
                                       'text-yellow-600': ogDescChars.length >= 160 && ogDescChars.length < 190,
                                       'text-red-600': ogDescChars.length >= 190
                                   }">
                                    <span x-text="ogDescChars.length || 0"></span>/200 caratteri
                                </p>
                            </div>
                        </div>

                        <!-- Social Preview Card -->
                        <div class="mt-4 p-4 bg-white rounded-lg border border-gray-300">
                            <p class="text-xs font-medium text-gray-700 mb-3">
                                <i class="fas fa-eye mr-1"></i>
                                Anteprima Social Media
                            </p>
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <!-- Preview Image -->
                                <div class="bg-gray-100 h-48 flex items-center justify-center"
                                     :class="{ 'hidden': !imagePreview }">
                                    <img x-show="imagePreview"
                                         :src="imagePreview"
                                         alt="Social preview"
                                         class="w-full h-full object-cover">
                                    <div x-show="!imagePreview" class="text-gray-400 text-center">
                                        <i class="fas fa-image fa-3x mb-2"></i>
                                        <p class="text-sm">Nessuna immagine</p>
                                    </div>
                                </div>
                                <!-- Preview Content -->
                                <div class="p-3 bg-white">
                                    <p class="text-xs text-gray-500 uppercase mb-1">danzafacile.it</p>
                                    <p class="text-sm font-semibold text-gray-900 line-clamp-2"
                                       x-text="document.getElementById('seo_og_title')?.value || document.getElementById('name')?.value || 'Nome Evento'">
                                    </p>
                                    <p class="text-xs text-gray-600 mt-1 line-clamp-2"
                                       x-text="document.getElementById('seo_og_description')?.value || document.getElementById('description')?.value || 'Descrizione evento...'">
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Impostazioni Visibilità -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_public" name="is_public" value="1"
                               class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded"
                               {{ old('is_public', true) ? 'checked' : '' }}
                               @change="showSeo = $event.target.checked"
                               aria-label="Evento pubblico"
                               aria-describedby="is-public-help">
                        <label for="is_public" class="ml-2 text-sm font-medium text-gray-700">
                            Evento Pubblico
                        </label>
                        <p id="is-public-help" class="ml-2 text-xs text-gray-500">(visibile a tutti)</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="active" name="active" value="1"
                               class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded"
                               {{ old('active', true) ? 'checked' : '' }}
                               aria-label="Evento attivo"
                               aria-describedby="active-help">
                        <label for="active" class="ml-2 text-sm font-medium text-gray-700">
                            Evento Attivo
                        </label>
                        <p id="active-help" class="ml-2 text-xs text-gray-500">(disponibile per registrazioni)</p>
                    </div>
                </div>

                <!-- Pulsanti Azione -->
                <div class="mt-8 flex items-center justify-end space-x-3 border-t pt-6">
                    <a href="{{ route('admin.events.index') }}"
                       :class="{ 'pointer-events-none opacity-50': submitting }"
                       class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        Annulla
                    </a>
                    <button type="submit"
                            :disabled="submitting"
                            :class="{ 'opacity-50 cursor-not-allowed': submitting }"
                            class="px-6 py-2 bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700 text-white rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                        <span x-show="!submitting">
                            <i class="fas fa-save mr-2"></i>
                            Crea Evento
                        </span>
                        <span x-show="submitting">
                            <i class="fas fa-spinner fa-spin mr-2"></i>
                            Creazione in corso...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script nonce="@cspNonce">
document.addEventListener('DOMContentLoaded', function() {
    // Auto-update end date when start date changes
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');

    startDateInput.addEventListener('change', function() {
        if (this.value && !endDateInput.value) {
            const startDate = new Date(this.value);
            startDate.setHours(startDate.getHours() + 2); // Add 2 hours by default
            const endDateTime = startDate.toISOString().slice(0, 16);
            endDateInput.value = endDateTime;
        }
    });

    // Auto-update registration deadline when start date changes
    const registrationDeadlineInput = document.getElementById('registration_deadline');

    startDateInput.addEventListener('change', function() {
        if (this.value && document.getElementById('requires_registration').checked && !registrationDeadlineInput.value) {
            const startDate = new Date(this.value);
            startDate.setDate(startDate.getDate() - 1); // 1 day before event
            const deadlineDateTime = startDate.toISOString().slice(0, 16);
            registrationDeadlineInput.value = deadlineDateTime;
        }
    });
});

/**
 * Alpine.js Component: Event Form Date Validation
 * Valida date e orari del form evento
 */
function eventFormValidation() {
    return {
        validateDates(event) {
            const startDate = document.getElementById('start_date').value;
            const endDate = document.getElementById('end_date').value;
            const registrationDeadline = document.getElementById('registration_deadline').value;
            const requiresRegistration = document.querySelector('[name="requires_registration"]').checked;

            // Validation 1: end_date >= start_date
            if (startDate && endDate && new Date(endDate) < new Date(startDate)) {
                event.preventDefault();
                alert('La data di fine deve essere successiva o uguale alla data di inizio.');
                return false;
            }

            // Validation 2: registration_deadline < start_date (se richiesta registrazione)
            if (requiresRegistration && registrationDeadline && startDate) {
                if (new Date(registrationDeadline) >= new Date(startDate)) {
                    event.preventDefault();
                    alert('La scadenza registrazione deve essere precedente alla data di inizio evento.');
                    return false;
                }

                // Validation 3: registration_deadline >= today
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (new Date(registrationDeadline) < today) {
                    event.preventDefault();
                    alert('La scadenza registrazione non può essere nel passato.');
                    return false;
                }
            }

            // Validation 4: start_date >= today (solo per create, non edit)
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (startDate && new Date(startDate) < today) {
                event.preventDefault();
                alert('La data di inizio deve essere oggi o nel futuro.');
                return false;
            }

            return true;
        }
    }
}

/**
 * Alpine.js Component: Autosave Manager
 * Gestisce il salvataggio automatico della bozza in localStorage
 */
function autosaveManager() {
    return {
        saveDraft() {
            try {
                const formData = {
                    name: document.getElementById('name')?.value || '',
                    description: document.getElementById('description')?.value || '',
                    type: document.getElementById('type')?.value || '',
                    location: document.getElementById('location')?.value || '',
                    start_date: document.getElementById('start_date')?.value || '',
                    end_date: document.getElementById('end_date')?.value || '',
                    max_participants: document.getElementById('max_participants')?.value || '',
                    price: document.getElementById('price')?.value || '',
                    requires_registration: document.getElementById('requires_registration')?.checked || false,
                    registration_deadline: document.getElementById('registration_deadline')?.value || '',
                    external_link: document.getElementById('external_link')?.value || '',
                    social_link: document.getElementById('social_link')?.value || '',
                    is_public: document.getElementById('is_public')?.checked || false,
                    active: document.getElementById('active')?.checked || false,
                    seo_og_title: document.getElementById('seo_og_title')?.value || '',
                    seo_og_description: document.getElementById('seo_og_description')?.value || '',
                    timestamp: new Date().toISOString()
                };

                localStorage.setItem('event-draft', JSON.stringify(formData));

                // Dispatch evento custom per mostrare badge "salvato"
                window.dispatchEvent(new CustomEvent('draft-saved'));
            } catch (error) {
                console.error('Errore salvataggio bozza:', error);
            }
        },

        loadDraft() {
            try {
                const draft = localStorage.getItem('event-draft');
                if (!draft) return;

                const formData = JSON.parse(draft);

                // Popola campi form con dati bozza
                if (formData.name) document.getElementById('name').value = formData.name;
                if (formData.description) document.getElementById('description').value = formData.description;
                if (formData.type) document.getElementById('type').value = formData.type;
                if (formData.location) document.getElementById('location').value = formData.location;
                if (formData.start_date) document.getElementById('start_date').value = formData.start_date;
                if (formData.end_date) document.getElementById('end_date').value = formData.end_date;
                if (formData.max_participants) document.getElementById('max_participants').value = formData.max_participants;
                if (formData.price) document.getElementById('price').value = formData.price;
                if (formData.registration_deadline) document.getElementById('registration_deadline').value = formData.registration_deadline;
                if (formData.external_link) document.getElementById('external_link').value = formData.external_link;
                if (formData.social_link) document.getElementById('social_link').value = formData.social_link;
                if (formData.seo_og_title) document.getElementById('seo_og_title').value = formData.seo_og_title;
                if (formData.seo_og_description) document.getElementById('seo_og_description').value = formData.seo_og_description;

                // Checkboxes
                document.getElementById('requires_registration').checked = formData.requires_registration;
                document.getElementById('is_public').checked = formData.is_public;
                document.getElementById('active').checked = formData.active;

                // Aggiorna character counters
                this.nameChars = formData.name;
                this.descriptionChars = formData.description;
                this.locationChars = formData.location;

                console.log('Bozza ripristinata con successo');
            } catch (error) {
                console.error('Errore caricamento bozza:', error);
            }
        },

        clearDraft() {
            localStorage.removeItem('event-draft');
        }
    }
}

/**
 * Alpine.js Component: Image Upload Manager
 * Gestisce validazione e preview upload immagine
 */
function imageUploadManager() {
    return {
        imagePreview: null,
        imageFileName: '',
        imageFileSize: '',
        imageError: null,

        handleImageUpload(event) {
            const file = event.target.files[0];
            this.imageError = null;

            if (!file) {
                this.clearImageUpload();
                return;
            }

            // Validazione tipo file
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                this.imageError = 'Formato file non valido. Usa JPG, PNG, GIF o WebP.';
                event.target.value = ''; // Reset input
                return;
            }

            // Validazione dimensione (max 5MB)
            const maxSize = 5 * 1024 * 1024; // 5MB in bytes
            if (file.size > maxSize) {
                this.imageError = 'File troppo grande. Dimensione massima: 5MB.';
                event.target.value = ''; // Reset input
                return;
            }

            // File valido - mostra preview
            this.imageFileName = file.name;
            this.imageFileSize = this.formatFileSize(file.size);

            const reader = new FileReader();
            reader.onload = (e) => {
                this.imagePreview = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        clearImageUpload() {
            this.imagePreview = null;
            this.imageFileName = '';
            this.imageFileSize = '';
            this.imageError = null;
            document.getElementById('image').value = '';
        },

        formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
    }
}
</script>
</x-app-layout>
