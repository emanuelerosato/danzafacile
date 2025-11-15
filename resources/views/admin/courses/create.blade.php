<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Crea Nuovo Corso
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione nuovo corso della tua scuola
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
        <li class="text-gray-900 font-medium">Nuovo Corso</li>
    </x-slot>



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Nuovo Corso
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Crea un nuovo corso per la tua scuola
            </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
            <a href="{{ route('admin.courses.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m0 7h18"/>
                </svg>
                Torna alla Lista
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-lg p-6">
        <!-- Dual-Layer Validation Component -->
        <x-form-validation :rules="[
            'name' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'instructor_id' => 'nullable|exists:users,id',
            'level' => 'required|in:beginner,intermediate,advanced',
            'price' => 'required|numeric|min:0|max:999.99',
            'max_students' => 'nullable|integer|min:1|max:100',
            'start_date' => 'required|date|after:today',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'duration_weeks' => 'nullable|integer|min:1|max:52',
            'schedule' => 'nullable|string|max:255'
        ]" />

        <form id="courseForm" action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome Corso *
                    </label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        placeholder="Es. Danza Classica Livello Base"
                        required
                        maxlength="255"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent" />
                    @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="instructor_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Istruttore
                    </label>
                    <select name="instructor_id" id="instructor_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleziona istruttore</option>
                        @if(isset($instructors))
                            @foreach($instructors as $instructor)
                                <option value="{{ $instructor->id }}" {{ old('instructor_id') == $instructor->id ? 'selected' : '' }}>
                                    {{ $instructor->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                    @error('instructor_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Description -->
            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Descrizione *
                </label>
                <textarea
                    name="description"
                    id="description"
                    placeholder="Descrivi il corso, obiettivi e contenuti..."
                    required
                    maxlength="1000"
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Level and Price -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                        Livello *
                    </label>
                    <select name="level" id="level" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleziona livello</option>
                        <option value="beginner" {{ old('level') == 'beginner' ? 'selected' : '' }}>Principiante</option>
                        <option value="intermediate" {{ old('level') == 'intermediate' ? 'selected' : '' }}>Intermedio</option>
                        <option value="advanced" {{ old('level') == 'advanced' ? 'selected' : '' }}>Avanzato</option>
                    </select>
                    @error('level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-2">
                        Prezzo Mensile (€) *
                    </label>
                    <input type="number" name="price" id="price" step="0.01" min="0" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="0.00"
                           value="{{ old('price') }}">
                    @error('price')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="max_students" class="block text-sm font-medium text-gray-700 mb-2">
                        Max Studenti
                    </label>
                    <input type="number" name="max_students" id="max_students" min="1"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="20"
                           value="{{ old('max_students') }}">
                    @error('max_students')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Schedule -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Data Inizio *
                    </label>
                    <input type="date" name="start_date" id="start_date" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('start_date') }}">
                    @error('start_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">
                        Data Fine
                    </label>
                    <input type="date" name="end_date" id="end_date"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           value="{{ old('end_date') }}">
                    @error('end_date')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Location and Notes -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                        Ubicazione
                    </label>
                    <input type="text" name="location" id="location"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Es. Sala A, Studio 1"
                           value="{{ old('location') }}">
                    @error('location')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="active" class="flex items-center">
                        <input type="checkbox" name="active" id="active" value="1"
                               class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                               {{ old('active', '1') ? 'checked' : '' }}>
                        <span class="ml-2 text-sm text-gray-700">Corso attivo</span>
                    </label>
                </div>
            </div>

            <!-- Duration -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="duration_weeks" class="block text-sm font-medium text-gray-700 mb-2">
                        Durata (settimane)
                    </label>
                    <input type="number" name="duration_weeks" id="duration_weeks" min="1" max="52"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="12"
                           value="{{ old('duration_weeks', 12) }}">
                    @error('duration_weeks')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Schedule Section -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700">
                            Orari delle Lezioni
                        </label>
                        <button type="button" onclick="addScheduleSlotCreate()"
                                class="px-3 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Aggiungi Orario
                        </button>
                    </div>

                    <div id="schedule-container-create" class="space-y-3">
                        <!-- Default slot -->
                        <div class="schedule-slot bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="font-medium text-gray-900">Orario 1</h4>
                                <button type="button" onclick="removeScheduleSlotCreate(this)" class="text-red-600 hover:text-red-800 text-sm" style="display: none;">
                                    Rimuovi
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Giorno della settimana *</label>
                                    <select name="schedule_slots[0][day]" required onchange="updateSlotNumbersCreate()"
                                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Seleziona giorno</option>
                                        <option value="Lunedì">Lunedì</option>
                                        <option value="Martedì">Martedì</option>
                                        <option value="Mercoledì">Mercoledì</option>
                                        <option value="Giovedì">Giovedì</option>
                                        <option value="Venerdì">Venerdì</option>
                                        <option value="Sabato">Sabato</option>
                                        <option value="Domenica">Domenica</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Sala/Ubicazione</label>
                                    <select name="schedule_slots[0][location]"
                                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Seleziona sala</option>
                                        <option value="Sala A">Sala A</option>
                                        <option value="Sala B">Sala B</option>
                                        <option value="Sala C">Sala C</option>
                                        <option value="Sala Principale">Sala Principale</option>
                                        <option value="Studio 1">Studio 1</option>
                                        <option value="Studio 2">Studio 2</option>
                                        <option value="Palestra">Palestra</option>
                                        <option value="Aula Magna">Aula Magna</option>
                                        <option value="Altro">Altro</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mt-4">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Orario Inizio *</label>
                                    <input type="time" name="schedule_slots[0][start_time]" required
                                           onchange="calculateDurationCreate(this)"
                                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Orario Fine *</label>
                                    <input type="time" name="schedule_slots[0][end_time]" required
                                           onchange="calculateDurationCreate(this)"
                                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                                </div>
                            </div>

                            <div class="mt-3 text-xs text-gray-500">
                                <span class="font-medium">Durata: </span>
                                <span class="duration-display">Seleziona orari per calcolare la durata</span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <div>
                                <p class="text-sm text-blue-800 font-medium">Suggerimento</p>
                                <p class="text-xs text-blue-700">Puoi aggiungere più orari per lo stesso corso se si svolge in giorni diversi della settimana.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.courses.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Annulla
                </a>
                <x-loading-button
                    type="submit"
                    variant="primary"
                    icon="plus"
                    loading-text="Creando...">
                    Crea Corso
                </x-loading-button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
// Initialize dual-layer validation when page loads
document.addEventListener('DOMContentLoaded', function() {
    FormValidator.init('#courseForm');
});

// Schedule management functions for create page
let scheduleSlotIndexCreate = 1;

function addScheduleSlotCreate() {
    const container = document.getElementById('schedule-container-create');
    const slotHtml = `
        <div class="schedule-slot bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Orario ${scheduleSlotIndexCreate + 1}</h4>
                <button type="button" onclick="removeScheduleSlotCreate(this)"
                        class="text-red-600 hover:text-red-800 text-sm">
                    Rimuovi
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Giorno della settimana *</label>
                    <select name="schedule_slots[${scheduleSlotIndexCreate}][day]" required onchange="updateSlotNumbersCreate()"
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleziona giorno</option>
                        <option value="Lunedì">Lunedì</option>
                        <option value="Martedì">Martedì</option>
                        <option value="Mercoledì">Mercoledì</option>
                        <option value="Giovedì">Giovedì</option>
                        <option value="Venerdì">Venerdì</option>
                        <option value="Sabato">Sabato</option>
                        <option value="Domenica">Domenica</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Sala/Ubicazione</label>
                    <select name="schedule_slots[${scheduleSlotIndexCreate}][location]"
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Seleziona sala</option>
                        <option value="Sala A">Sala A</option>
                        <option value="Sala B">Sala B</option>
                        <option value="Sala C">Sala C</option>
                        <option value="Sala Principale">Sala Principale</option>
                        <option value="Studio 1">Studio 1</option>
                        <option value="Studio 2">Studio 2</option>
                        <option value="Palestra">Palestra</option>
                        <option value="Aula Magna">Aula Magna</option>
                        <option value="Altro">Altro</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Orario Inizio *</label>
                    <input type="time" name="schedule_slots[${scheduleSlotIndexCreate}][start_time]" required
                           onchange="calculateDurationCreate(this)"
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Orario Fine *</label>
                    <input type="time" name="schedule_slots[${scheduleSlotIndexCreate}][end_time]" required
                           onchange="calculateDurationCreate(this)"
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <div class="mt-3 text-xs text-gray-500">
                <span class="font-medium">Durata: </span>
                <span class="duration-display">Seleziona orari per calcolare la durata</span>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', slotHtml);
    scheduleSlotIndexCreate++;
    updateSlotNumbersCreate();
}

function removeScheduleSlotCreate(button) {
    const slot = button.closest('.schedule-slot');
    slot.remove();
    updateSlotNumbersCreate();
}

function updateSlotNumbersCreate() {
    const slots = document.querySelectorAll('#schedule-container-create .schedule-slot');
    slots.forEach((slot, index) => {
        const title = slot.querySelector('h4');
        const daySelect = slot.querySelector('select[name*="[day]"]');
        const selectedDay = daySelect.value;
        const removeButton = slot.querySelector('button[onclick*="removeScheduleSlotCreate"]');

        if (selectedDay) {
            title.textContent = `${selectedDay} - Orario ${index + 1}`;
        } else {
            title.textContent = `Orario ${index + 1}`;
        }

        // Show/hide remove button
        if (removeButton) {
            removeButton.style.display = slots.length > 1 ? 'block' : 'none';
        }

        // Update field names
        const inputs = slot.querySelectorAll('input, select');
        inputs.forEach(input => {
            const nameAttr = input.getAttribute('name');
            if (nameAttr && nameAttr.includes('schedule_slots[')) {
                const newName = nameAttr.replace(/schedule_slots\[\d+\]/, `schedule_slots[${index}]`);
                input.setAttribute('name', newName);
            }
        });
    });
}

function calculateDurationCreate(input) {
    const slot = input.closest('.schedule-slot');
    const startTime = slot.querySelector('input[name*="[start_time]"]').value;
    const endTime = slot.querySelector('input[name*="[end_time]"]').value;
    const durationDisplay = slot.querySelector('.duration-display');

    if (startTime && endTime) {
        const start = new Date(`2000-01-01T${startTime}:00`);
        const end = new Date(`2000-01-01T${endTime}:00`);

        if (end > start) {
            const diff = end - start;
            const hours = Math.floor(diff / (1000 * 60 * 60));
            const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));

            let durationText = '';
            if (hours > 0) {
                durationText += `${hours}h `;
            }
            if (minutes > 0) {
                durationText += `${minutes}min`;
            }

            durationDisplay.textContent = durationText || '0min';
            durationDisplay.className = 'duration-display text-green-600 font-medium';
        } else {
            durationDisplay.textContent = 'Orario fine deve essere dopo orario inizio';
            durationDisplay.className = 'duration-display text-red-600';
        }
    } else {
        durationDisplay.textContent = 'Seleziona orari per calcolare la durata';
        durationDisplay.className = 'duration-display';
    }
}
</script>
@endpush
</x-app-layout>
