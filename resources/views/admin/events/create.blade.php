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

        <div class="bg-white rounded-lg shadow">
            <form action="{{ route('admin.events.store') }}"
                  method="POST"
                  id="createEventForm"
                  class="p-6"
                  enctype="multipart/form-data"
                  x-data="{ ...eventFormValidation(), submitting: false }"
                  @submit="if(validateDates($event)) { submitting = true }">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Informazioni Base -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900 border-b pb-2">Informazioni Base</h3>

                        <!-- Nome -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Nome Evento *
                            </label>
                            <input type="text" id="name" name="name"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('name') border-red-500 @enderror"
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Descrizione -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                                Descrizione
                            </label>
                            <textarea id="description" name="description" rows="4"
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('description') border-red-500 @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                            <input type="text" id="location" name="location"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('location') border-red-500 @enderror"
                                   value="{{ old('location') }}">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
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
                            <input type="file" id="image" name="image" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('image') border-red-500 @enderror"
                                   onchange="window.previewImage && window.previewImage(event)">
                            <p class="mt-1 text-xs text-gray-500">
                                Formati supportati: JPG, PNG, GIF, WEBP. Max 5MB
                            </p>
                            @error('image')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror

                            <!-- Image Preview -->
                            <div id="imagePreview" class="mt-3 hidden">
                                <img id="imagePreviewImg" src="" alt="Preview" class="max-w-full h-48 object-cover rounded-lg border border-gray-300">
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

                <!-- Impostazioni Visibilità -->
                <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="is_public" name="is_public" value="1"
                               class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded" {{ old('is_public', true) ? 'checked' : '' }}>
                        <label for="is_public" class="ml-2 text-sm font-medium text-gray-700">
                            Evento Pubblico
                        </label>
                        <p class="ml-2 text-xs text-gray-500">(visibile a tutti)</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" id="active" name="active" value="1"
                               class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded" {{ old('active', true) ? 'checked' : '' }}>
                        <label for="active" class="ml-2 text-sm font-medium text-gray-700">
                            Evento Attivo
                        </label>
                        <p class="ml-2 text-xs text-gray-500">(disponibile per registrazioni)</p>
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

// Image preview function
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('imagePreviewImg');

    if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    } else {
        preview.classList.add('hidden');
        previewImg.src = '';
    }
}

// Event form date validation
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
</script>
</x-app-layout>
