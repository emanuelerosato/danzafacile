<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Crea Nuovo Turno
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Aggiungi un nuovo turno per un membro dello staff
                </p>
            </div>
            <a href="{{ route('admin.staff-schedules.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Torna alla Lista
            </a>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.staff-schedules.index') }}" class="text-gray-500 hover:text-gray-700">Turni Staff</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Crea Turno</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                <!-- Form -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
            <form id="staff-schedule-form" method="POST" action="{{ route('admin.staff-schedules.store') }}" class="p-6 space-y-6">
                @csrf

                @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex">
                            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">Errori nel form:</h3>
                                <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Staff Selection and Title -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="staff_id" class="block text-sm font-medium text-gray-700 mb-2">Staff Member *</label>
                        <select id="staff_id" name="staff_id" required
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('staff_id') border-red-300 @enderror">
                            <option value="">Seleziona staff member</option>
                            @foreach($staff as $member)
                                <option value="{{ $member->id }}" {{ old('staff_id') == $member->id ? 'selected' : '' }}>
                                    {{ $member->full_name }} - {{ $member->email }}
                                </option>
                            @endforeach
                        </select>
                        @error('staff_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Titolo Turno *</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required
                               placeholder="es. Lezione Danza Classica"
                               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('title') border-red-300 @enderror">
                        @error('title')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Type and Date -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipo Turno *</label>
                        <select id="type" name="type" required
                                class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('type') border-red-300 @enderror">
                            <option value="">Seleziona tipo</option>
                            <option value="course" {{ old('type') == 'course' ? 'selected' : '' }}>Corso</option>
                            <option value="event" {{ old('type') == 'event' ? 'selected' : '' }}>Evento</option>
                            <option value="administrative" {{ old('type') == 'administrative' ? 'selected' : '' }}>Amministrativo</option>
                            <option value="maintenance" {{ old('type') == 'maintenance' ? 'selected' : '' }}>Manutenzione</option>
                            <option value="other" {{ old('type') == 'other' ? 'selected' : '' }}>Altro</option>
                        </select>
                        @error('type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-2">Data *</label>
                        <input type="date" id="date" name="date" value="{{ old('date') }}" required
                               min="{{ date('Y-m-d') }}"
                               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('date') border-red-300 @enderror">
                        @error('date')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Time Range -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label for="start_time" class="block text-sm font-medium text-gray-700 mb-2">Ora Inizio *</label>
                        <input type="time" id="start_time" name="start_time" value="{{ old('start_time') }}" required
                               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('start_time') border-red-300 @enderror">
                        @error('start_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="end_time" class="block text-sm font-medium text-gray-700 mb-2">Ora Fine *</label>
                        <input type="time" id="end_time" name="end_time" value="{{ old('end_time') }}" required
                               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('end_time') border-red-300 @enderror">
                        @error('end_time')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Luogo</label>
                        <input type="text" id="location" name="location" value="{{ old('location') }}"
                               placeholder="es. Sala A, Studio 1"
                               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('location') border-red-300 @enderror">
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Financial Info -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-2">Tariffa Oraria (€)</label>
                        <input type="number" id="hourly_rate" name="hourly_rate" value="{{ old('hourly_rate') }}"
                               step="0.01" min="0" placeholder="es. 25.00"
                               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('hourly_rate') border-red-300 @enderror">
                        @error('hourly_rate')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="max_hours" class="block text-sm font-medium text-gray-700 mb-2">Ore Massime</label>
                        <input type="number" id="max_hours" name="max_hours" value="{{ old('max_hours') }}"
                               min="1" placeholder="es. 8"
                               class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('max_hours') border-red-300 @enderror">
                        @error('max_hours')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Descrizione</label>
                    <textarea id="description" name="description" rows="3"
                              placeholder="Descrizione dettagliata del turno..."
                              class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Requirements -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Requisiti Specifici</label>
                    <div id="requirements-container">
                        @if(old('requirements'))
                            @foreach(old('requirements') as $index => $requirement)
                                <div class="flex items-center space-x-2 mb-2 requirement-item">
                                    <input type="text" name="requirements[]" value="{{ $requirement }}"
                                           placeholder="es. Certificazione First Aid"
                                           class="flex-1 rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    <button type="button" onclick="removeRequirement(this)"
                                            class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        @else
                            <div class="flex items-center space-x-2 mb-2 requirement-item">
                                <input type="text" name="requirements[]" placeholder="es. Certificazione First Aid"
                                       class="flex-1 rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                <button type="button" onclick="removeRequirement(this)"
                                        class="px-3 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        @endif
                    </div>
                    <button type="button" id="add-requirement-btn"
                            class="mt-2 inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Aggiungi Requisito
                    </button>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Note Aggiuntive</label>
                    <textarea id="notes" name="notes" rows="3"
                              placeholder="Note per questo turno..."
                              class="w-full rounded-lg border-gray-300 focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('notes') border-red-300 @enderror">{{ old('notes') }}</textarea>
                    @error('notes')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Overlap Warning -->
                <div id="overlap-warning" class="hidden bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Attenzione</h3>
                            <p class="mt-1 text-sm text-yellow-700">Verifica che non ci siano sovrapposizioni con altri turni del membro dello staff selezionato.</p>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.staff-schedules.index') }}"
                       class="inline-flex items-center px-6 py-3 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        Annulla
                    </a>
                    <button type="submit"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        Crea Turno
                    </button>
                </div>
            </form>
                </div>
            </div>
        </div>
    </div>

    @vite('resources/js/admin/staff-schedules.js')
</x-app-layout>
