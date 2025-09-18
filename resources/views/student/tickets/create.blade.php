<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Nuovo Messaggio
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Invia una richiesta di assistenza o comunicazione alla scuola
                </p>
            </div>
            <a href="{{ route('student.tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Torna ai Messaggi
            </a>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('student.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('student.tickets.index') }}" class="text-gray-500 hover:text-gray-700">Messaggi</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Nuovo Messaggio</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

    <!-- Help Section -->
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">Suggerimenti per il tuo messaggio</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <ul class="list-disc pl-5 space-y-1">
                        <li>Scegli una categoria appropriata per il tuo messaggio</li>
                        <li>Fornisci più dettagli possibili nella descrizione</li>
                        <li>Indica la priorità in base all'urgenza della tua richiesta</li>
                        <li>Ti risponderemo al più presto tramite email e nell'area messaggi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Ticket Form -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
        <form action="{{ route('student.tickets.store') }}" method="POST" id="create-ticket-form">
            @csrf

            <div class="space-y-6">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Oggetto del Messaggio <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           name="title"
                           id="title"
                           value="{{ old('title') }}"
                           placeholder="Es: Problema con il pagamento del corso di danza moderna"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                           required>
                    @error('title')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category and Priority Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            Categoria <span class="text-red-500">*</span>
                        </label>
                        <select name="category"
                                id="category"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                required>
                            <option value="">Seleziona una categoria</option>
                            @foreach($categories as $value => $label)
                                <option value="{{ $value }}" {{ old('category') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Priority -->
                    <div>
                        <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">
                            Priorità <span class="text-red-500">*</span>
                        </label>
                        <select name="priority"
                                id="priority"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                required>
                            <option value="">Seleziona una priorità</option>
                            @foreach($priorities as $value => $label)
                                <option value="{{ $value }}" {{ old('priority') === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('priority')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                        Descrizione del Problema <span class="text-red-500">*</span>
                    </label>
                    <textarea name="description"
                              id="description"
                              rows="6"
                              placeholder="Descrivi dettagliatamente il tuo problema o la tua richiesta. Includi tutte le informazioni che ritieni utili per aiutarci a comprenderti meglio..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 resize-none"
                              required>{{ old('description') }}</textarea>
                    <div class="mt-1 flex justify-between text-sm text-gray-500">
                        <span>Fornisci il maggior numero di dettagli possibile</span>
                        <span id="char-count">0/2000</span>
                    </div>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('student.tickets.index') }}"
                       class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-3 px-6 rounded-lg transition-colors duration-200">
                        Annulla
                    </a>
                    <button type="submit"
                            class="bg-gradient-to-r from-rose-600 to-purple-600 hover:from-rose-700 hover:to-purple-700 text-white font-semibold py-3 px-6 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-1">
                        <svg class="w-5 h-5 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        Invia Messaggio
                    </button>
                </div>
            </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Character count for description
        const descriptionTextarea = document.getElementById('description');
        const charCount = document.getElementById('char-count');

        function updateCharCount() {
            const length = descriptionTextarea.value.length;
            charCount.textContent = `${length}/2000`;

            if (length > 1800) {
                charCount.classList.add('text-yellow-600');
            } else if (length > 1950) {
                charCount.classList.remove('text-yellow-600');
                charCount.classList.add('text-red-600');
            } else {
                charCount.classList.remove('text-yellow-600', 'text-red-600');
            }
        }

        descriptionTextarea.addEventListener('input', updateCharCount);
        updateCharCount(); // Initial count

        // Form submission with loading state
        const form = document.getElementById('create-ticket-form');
        const submitButton = form.querySelector('button[type="submit"]');

        form.addEventListener('submit', function() {
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Invio in corso...
            `;
        });
    });
    </script>
    @endpush
</x-app-layout>