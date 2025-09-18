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
        <div class="flex items-center space-x-3">
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
        <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- Basic Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nome Corso *
                    </label>
                    <input type="text" name="name" id="name" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Es. Danza Classica Livello Base"
                           value="{{ old('name') }}">
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
                <textarea name="description" id="description" rows="4" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                          placeholder="Descrivi il corso, obiettivi e contenuti...">{{ old('description') }}</textarea>
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

                <div>
                    <label for="schedule" class="block text-sm font-medium text-gray-700 mb-2">
                        Programma/Orari
                    </label>
                    <input type="text" name="schedule" id="schedule"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Es. Lun-Mer-Ven 18:00-19:30"
                           value="{{ old('schedule') }}">
                    @error('schedule')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.courses.index') }}"
                   class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                    Annulla
                </a>
                <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Crea Corso
                </button>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
