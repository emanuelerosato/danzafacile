<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifica Corso
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione modifica della tua scuola
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
        <li class="text-gray-900 font-medium">Modifica {{ $course->name ?? 'Corso' }}</li>
    </x-slot>


        {{-- Header duplicato rimosso --}}

    <div class="space-y-6">
        <!-- Course Status Alert -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-sm font-medium text-blue-900">Corso {{ $course->active ? 'Attivo' : 'Inattivo' }} con {{ $course->enrollments()->where('status', 'attiva')->count() }} Studenti Iscritti</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        Le modifiche agli orari e ai prezzi potrebbero influenzare gli studenti già iscritti. 
                        Ti consigliamo di comunicare i cambiamenti con almeno 7 giorni di anticipo.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.courses.update', $course ?? 1) }}" method="POST" enctype="multipart/form-data" 
              x-data="{ activeTab: 'basic', imagePreview: '/images/courses/danza-classica.jpg' }">
            @csrf
            @method('PUT')

            <!-- Form Sections -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="-mb-px flex space-x-8 px-6">
                        <button type="button" @click="activeTab = 'basic'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'basic', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'basic' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Informazioni Base
                        </button>
                        <button type="button" @click="activeTab = 'details'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'details', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'details' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Dettagli
                        </button>
                        <button type="button" @click="activeTab = 'students'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'students', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'students' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            Studenti ({{ $course->enrollments()->where('status', 'attiva')->count() }})
                        </button>
                        <button type="button" @click="activeTab = 'schedule'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'schedule', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'schedule' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Orari
                        </button>
                        <button type="button" @click="activeTab = 'pricing'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'pricing', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'pricing' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Prezzi
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Basic Information Tab -->
                    <div x-show="activeTab === 'basic'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                            <!-- Image Upload -->
                            <div class="lg:col-span-1">
                                <div class="text-center">
                                    <div class="mb-4">
                                        <img x-show="imagePreview" x-bind:src="imagePreview" class="mx-auto h-40 w-full rounded-2xl object-cover">
                                        <div x-show="!imagePreview" class="mx-auto h-40 w-full bg-gradient-to-r from-rose-100 to-purple-100 rounded-2xl flex items-center justify-center border-2 border-dashed border-rose-300">
                                            <div class="text-center">
                                                <svg class="mx-auto h-12 w-12 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <p class="mt-2 text-sm text-rose-600 font-medium">Immagine Corso</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2">
                                        <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-rose-50 text-rose-600 text-sm font-medium rounded-lg hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 border border-rose-200">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            Cambia Immagine
                                            <input type="file" name="image" accept="image/*" class="sr-only" 
                                                   @change="if ($event.target.files[0]) { 
                                                       const reader = new FileReader();
                                                       reader.onload = (e) => imagePreview = e.target.result;
                                                       reader.readAsDataURL($event.target.files[0]);
                                                   }">
                                        </label>
                                        <p class="text-xs text-gray-500">PNG, JPG fino a 5MB</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Basic Form Fields -->
                            <div class="lg:col-span-2 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-form-input 
                                            label="Nome Corso *"
                                            name="name"
                                            type="text"
                                            value="{{ old('name', $course->name) }}"
                                            placeholder="es. Danza Classica Intermedio"
                                            required />
                                    </div>
                                    <div>
                                        <x-form-input 
                                            label="Codice Corso"
                                            name="code"
                                            type="text"
                                            value="{{ $course->code ?? '' }}"
                                            readonly />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo di Danza *</label>
                                        <select name="dance_type" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="">Seleziona tipo</option>
                                            <option value="classica" {{ old('dance_type', $course->dance_type ?? '') === 'classica' ? 'selected' : '' }}>Danza Classica</option>
                                            <option value="moderna" {{ old('dance_type', $course->dance_type ?? '') === 'moderna' ? 'selected' : '' }}>Danza Moderna</option>
                                            <option value="contemporanea" {{ old('dance_type', $course->dance_type ?? '') === 'contemporanea' ? 'selected' : '' }}>Danza Contemporanea</option>
                                            <option value="hip_hop" {{ old('dance_type', $course->dance_type ?? '') === 'hip_hop' ? 'selected' : '' }}>Hip Hop</option>
                                            <option value="jazz" {{ old('dance_type', $course->dance_type ?? '') === 'jazz' ? 'selected' : '' }}>Jazz Dance</option>
                                            <option value="latino" {{ old('dance_type', $course->dance_type ?? '') === 'latino' ? 'selected' : '' }}>Danze Latine</option>
                                            <option value="bollywood" {{ old('dance_type', $course->dance_type ?? '') === 'bollywood' ? 'selected' : '' }}>Bollywood</option>
                                            <option value="altro" {{ old('dance_type', $course->dance_type ?? '') === 'altro' ? 'selected' : '' }}>Altro</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Livello *</label>
                                        <select name="level" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="">Seleziona livello</option>
                                            <option value="principiante" {{ old('level', $course->level ?? '') === 'principiante' ? 'selected' : '' }}>Principiante</option>
                                            <option value="base" {{ old('level', $course->level ?? '') === 'base' ? 'selected' : '' }}>Base</option>
                                            <option value="intermedio" {{ old('level', $course->level ?? '') === 'intermedio' ? 'selected' : '' }}>Intermedio</option>
                                            <option value="avanzato" {{ old('level', $course->level ?? '') === 'avanzato' ? 'selected' : '' }}>Avanzato</option>
                                            <option value="professionale" {{ old('level', $course->level ?? '') === 'professionale' ? 'selected' : '' }}>Professionale</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                                        <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="draft" {{ old('status', $course->active ? 'published' : 'draft') === 'draft' ? 'selected' : '' }}>Bozza</option>
                                            <option value="published" {{ old('status', $course->active ? 'published' : 'draft') === 'published' ? 'selected' : '' }}>Pubblicato</option>
                                            <option value="archived" {{ old('status', $course->active ? 'published' : 'draft') === 'archived' ? 'selected' : '' }}>Archiviato</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Età Minima</label>
                                        <input type="number" name="min_age" min="3" max="99" value="{{ old('min_age', $course->min_age ?? '') }}" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Età Massima</label>
                                        <input type="number" name="max_age" min="3" max="99" value="{{ old('max_age', $course->max_age ?? '') }}" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Posti Totali *</label>
                                        <input type="number" name="max_students" min="1" max="100" value="{{ old('max_students', $course->max_students ?? '') }}" required
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                        <p class="mt-1 text-xs text-gray-500">Attualmente iscritti: {{ $course->enrollments()->where('status', 'attiva')->count() }} studenti</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Istruttore</label>
                                        <select name="instructor_id" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="">Seleziona istruttore</option>
                                            @foreach($instructors ?? [] as $instructor)
                                                <option value="{{ $instructor->id }}" {{ old('instructor_id', $course->instructor_id ?? '') == $instructor->id ? 'selected' : '' }}>{{ $instructor->user->name ?? $instructor->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Tab -->
                    <div x-show="activeTab === 'details'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Course Description -->
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrizione Breve</label>
                                    <textarea name="short_description" rows="3"
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">{{ old('short_description', $course->short_description ?? '') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrizione Completa</label>
                                    <textarea name="description" rows="6"
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">{{ old('description', $course->description ?? '') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisiti</label>
                                    <textarea name="prerequisites" rows="4"
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">{{ old('prerequisites', $course->prerequisites ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Requirements & Equipment -->
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Abbigliamento Richiesto</label>
                                    <div class="space-y-2">
                                        @php $equipment = old('equipment', $course->equipment ?? []); @endphp
                                        @if(is_array($equipment) && count($equipment) > 0)
                                            @foreach($equipment as $item)
                                                <input type="text" name="equipment[]" value="{{ $item }}"
                                                       class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            @endforeach
                                        @else
                                            <input type="text" name="equipment[]" value=""
                                                   class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500" placeholder="Inserisci abbigliamento richiesto">
                                        @endif
                                        <button type="button" onclick="addEquipmentField()" class="text-sm text-rose-600 hover:text-rose-800">+ Aggiungi elemento</button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Obiettivi del Corso</label>
                                    <div class="space-y-2">
                                        @php $objectives = old('objectives', $course->objectives ?? []); @endphp
                                        @if(is_array($objectives) && count($objectives) > 0)
                                            @foreach($objectives as $objective)
                                                <input type="text" name="objectives[]" value="{{ $objective }}"
                                                       class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            @endforeach
                                        @else
                                            <input type="text" name="objectives[]" value=""
                                                   class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500" placeholder="Inserisci obiettivo del corso">
                                        @endif
                                        <button type="button" onclick="addObjectiveField()" class="text-sm text-rose-600 hover:text-rose-800">+ Aggiungi obiettivo</button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Note Aggiuntive</label>
                                    <textarea name="notes" rows="4"
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">{{ old('notes', $course->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Students Tab -->
                    <div x-show="activeTab === 'students'" class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Gestione Studenti Iscritti</h3>
                            <div class="flex space-x-3">
                                <button @click="$dispatch('open-modal', 'add-student')" type="button"
                                        class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-medium">
                                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Aggiungi Studente
                                </button>
                                <button type="button" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm font-medium">
                                    Invia Comunicazione
                                </button>
                            </div>
                        </div>

                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-yellow-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-900">Attenzione</h4>
                                    <p class="text-sm text-yellow-800">
                                        La rimozione di studenti dal corso cancellerà la loro iscrizione e interromperà i pagamenti ricorrenti.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Students Management Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse ($course->enrollments()->with('user')->where('status', 'attiva')->get() as $enrollment)
                                <div class="bg-white p-4 rounded-lg border border-gray-200 hover:border-rose-300 transition-colors">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                                            <div class="w-10 h-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($enrollment->user->name, 0, 1) . substr(explode(' ', $enrollment->user->name)[1] ?? '', 0, 1)) }}
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-900">{{ $enrollment->user->name }}</h4>
                                                <p class="text-xs text-gray-500">{{ $enrollment->user->age ?? 'N/A' }} anni</p>
                                            </div>
                                        </div>
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="p-1 text-gray-400 hover:text-gray-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" x-transition
                                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                                <div class="py-1">
                                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        Vedi Dettagli
                                                    </button>
                                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                        </svg>
                                                        Contatta
                                                    </button>
                                                    <button type="button" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Rimuovi dal Corso
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Iscritto:</span>
                                            <span class="text-gray-900">{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Presenze:</span>
                                            <span class="font-medium text-gray-600">
                                                N/A
                                            </span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span class="text-gray-600">Pagamenti:</span>
                                            <span class="font-medium
                                                {{ $enrollment->payment_status === 'pagato' ? 'text-green-600' :
                                                   ($enrollment->payment_status === 'in_attesa' ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ ucfirst($enrollment->payment_status ?? 'N/A') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full text-center py-8">
                                    <div class="text-gray-500">
                                        <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">Nessuno studente iscritto</h3>
                                        <p class="text-gray-500">Inizia ad aggiungere studenti al corso</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Schedule Tab -->
                    <div x-show="activeTab === 'schedule'" class="space-y-6">
                        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-orange-900">Modifica degli Orari</h4>
                                    <p class="text-sm text-orange-800">
                                        Le modifiche agli orari saranno comunicate automaticamente a tutti gli studenti iscritti via email.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Current Schedule Display -->
                            <div class="space-y-4">
                                <h3 class="text-lg font-semibold text-gray-900">Orario Attuale</h3>
                                
                                <div class="space-y-4">
                                    @if($course->schedule_data && is_array($course->schedule_data))
                                        @foreach($course->schedule_data as $index => $slot)
                                            <div class="p-4 bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg border border-rose-200">
                                                <div class="flex items-center space-x-4 mb-2">
                                                    <div class="w-12 h-12 bg-rose-500 rounded-lg flex items-center justify-center text-white">
                                                        <span class="font-bold text-sm">{{ strtoupper(substr($slot['day'] ?? '', 0, 3)) }}</span>
                                                    </div>
                                                    <div>
                                                        <h4 class="font-semibold text-gray-900">{{ $slot['day'] ?? 'N/A' }}</h4>
                                                        <p class="text-gray-600">{{ $slot['start_time'] ?? '' }} - {{ $slot['end_time'] ?? '' }}</p>
                                                    </div>
                                                </div>
                                                <div class="text-sm text-gray-600">
                                                    <p>{{ $slot['location'] ?? $course->location ?? 'Sede' }} • {{ $course->enrollments()->where('status', 'attiva')->count() }} studenti iscritti</p>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="p-4 bg-gray-50 rounded-lg border border-gray-200 text-center">
                                            <p class="text-gray-500">Nessun orario configurato</p>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Schedule Modification Form -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900">Modifica Orari</h3>
                                
                                <div class="space-y-4">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Orario del Corso (JSON)</label>
                                        <textarea name="schedule" rows="6"
                                                  class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 font-mono text-sm"
                                                  placeholder='Esempio: [{"day":"Lunedì","start_time":"19:00","end_time":"20:30","location":"Sala A"}]'>{{ old('schedule', $course->schedule ? json_encode($course->schedule, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '') }}</textarea>
                                        <p class="mt-1 text-xs text-gray-500">Modifica l'orario in formato JSON. Ogni slot deve contenere: day, start_time, end_time, location</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Tab -->
                    <div x-show="activeTab === 'pricing'" class="space-y-6">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-red-900">Attenzione - Modifica Prezzi</h4>
                                    <p class="text-sm text-red-800">
                                        Le modifiche ai prezzi influenzeranno i pagamenti futuri degli studenti già iscritti. 
                                        Valuta attentamente l'impatto prima di confermare le modifiche.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Current Pricing -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900">Prezzi Attuali</h3>
                                
                                <div class="space-y-4">
                                    <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                        <div class="text-center">
                                            <div class="text-xl md:text-2xl font-bold text-green-800">{{ $course->formatted_price ?? '€0,00' }}</div>
                                            <p class="text-sm text-green-600">Quota mensile</p>
                                            <p class="text-xs text-green-600 mt-1">{{ $course->enrollments()->where('status', 'attiva')->count() }} studenti paganti</p>
                                        </div>
                                    </div>
                                    
                                    <div class="grid grid-cols-2 gap-4">
                                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                                            <div class="font-bold text-gray-800">€{{ number_format($course->enrollment_fee ?? 0, 2, ',', '.') }}</div>
                                            <p class="text-xs text-gray-600">Quota iscrizione</p>
                                        </div>
                                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                                            <div class="font-bold text-gray-800">€{{ number_format($course->single_lesson_price ?? 0, 2, ',', '.') }}</div>
                                            <p class="text-xs text-gray-600">Lezione singola</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="p-4 bg-blue-50 rounded-lg border border-blue-200">
                                    <h4 class="font-medium text-blue-900 mb-2">Ricavi Mensili</h4>
                                    <div class="text-sm text-blue-800 space-y-1">
                                        @php
                                            $activeStudents = $course->enrollments()->where('status', 'attiva')->count();
                                            $monthlyRevenue = $activeStudents * ($course->price ?? 0);
                                            $enrollmentFeeRevenue = 2 * ($course->enrollment_fee ?? 0);
                                            $totalRevenue = $monthlyRevenue + $enrollmentFeeRevenue;
                                        @endphp
                                        <p>Studenti: {{ $activeStudents }} × €{{ number_format($course->price ?? 0, 2, ',', '.') }} = <strong>€{{ number_format($monthlyRevenue, 0, ',', '.') }}</strong></p>
                                        <p>Nuove iscrizioni (media): 2 × €{{ number_format($course->enrollment_fee ?? 0, 2, ',', '.') }} = <strong>€{{ number_format($enrollmentFeeRevenue, 0, ',', '.') }}</strong></p>
                                        <p>Totale mensile: <strong>€{{ number_format($totalRevenue, 0, ',', '.') }}</strong></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Price Modification -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900">Modifica Prezzi</h3>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-form-input 
                                            label="Prezzo Mensile (€) *"
                                            name="monthly_price"
                                            type="number"
                                            step="0.01"
                                            value="{{ old('monthly_price', $course->price ?? '') }}"
                                            required />
                                    </div>
                                    <div>
                                        <x-form-input 
                                            label="Quota Iscrizione (€)"
                                            name="enrollment_fee"
                                            type="number"
                                            step="0.01"
                                            value="{{ old('enrollment_fee', $course->enrollment_fee ?? '') }}" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-form-input 
                                            label="Prezzo Lezione Singola (€)"
                                            name="single_lesson_price"
                                            type="number"
                                            step="0.01"
                                            value="{{ old('single_lesson_price', $course->single_lesson_price ?? '') }}" />
                                    </div>
                                    <div>
                                        <x-form-input 
                                            label="Prezzo Prova Gratuita (€)"
                                            name="trial_price"
                                            type="number"
                                            step="0.01"
                                            value="{{ old('trial_price', $course->trial_price ?? '') }}" />
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Applica Modifiche</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="radio" name="price_application" value="new_students" id="new_students" checked
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300">
                                            <label for="new_students" class="ml-2 text-sm text-gray-900">
                                                Solo per nuovi studenti
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="price_application" value="all_students" id="all_students"
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300">
                                            <label for="all_students" class="ml-2 text-sm text-gray-900">
                                                Tutti gli studenti (a partire dal prossimo pagamento)
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="radio" name="price_application" value="immediate" id="immediate"
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300">
                                            <label for="immediate" class="ml-2 text-sm text-gray-900">
                                                Applicazione immediata (prossima fatturazione)
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Applicazione</label>
                                    <input type="date" name="price_effective_date" value="{{ old('price_effective_date', now()->addDays(7)->format('Y-m-d')) }}"
                                           class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                    <p class="mt-1 text-xs text-gray-500">Gli studenti saranno avvisati con almeno 7 giorni di anticipo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between bg-white rounded-2xl shadow-lg border border-gray-200 p-6">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.courses.show', $course ?? 1) }}" 
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                        Annulla
                    </a>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                    <button type="submit" name="action" value="draft" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                        Salva come Bozza
                    </button>
                    <button type="submit" name="action" value="update" 
                            class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Salva Modifiche
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Deactivate Course Modal -->
    <x-modal name="deactivate-course" maxWidth="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Disattiva Corso</h3>
                <button @click="$dispatch('close-modal', 'deactivate-course')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-orange-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-center text-gray-700 mb-4">
                    Sei sicuro di voler disattivare <strong>"{{ $course->name ?? 'questo corso' }}"</strong>?
                </p>
                <p class="text-sm text-gray-500 text-center mb-4">
                    Il corso non sarà più visibile per le nuove iscrizioni, ma gli studenti già iscritti potranno continuare a frequentare.
                </p>
                
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
                    <p class="text-sm text-yellow-800">
                        <strong>{{ $course->enrollments()->where('status', 'attiva')->count() }} studenti</strong> sono attualmente iscritti a questo corso.
                    </p>
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Motivo della disattivazione
                </label>
                <select class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option>Fine del periodo del corso</option>
                    <option>Ristrutturazione programma</option>
                    <option>Mancanza di istruttore</option>
                    <option>Insufficienti iscrizioni</option>
                    <option>Altro</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Note per gli studenti
                </label>
                <textarea rows="3" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500" 
                          placeholder="Messaggio che sarà inviato agli studenti iscritti..."></textarea>
            </div>
            
            <div class="flex items-center justify-end space-x-3">
                <button @click="$dispatch('close-modal', 'deactivate-course')" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Annulla
                </button>
                <button class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    Disattiva Corso
                </button>
            </div>
        </div>
    </x-modal>

<script>
function addEquipmentField() {
    const container = event.target.parentElement;
    const newField = document.createElement('input');
    newField.type = 'text';
    newField.name = 'equipment[]';
    newField.className = 'w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500';
    newField.placeholder = 'Inserisci abbigliamento richiesto';
    container.insertBefore(newField, event.target);
}

function addObjectiveField() {
    const container = event.target.parentElement;
    const newField = document.createElement('input');
    newField.type = 'text';
    newField.name = 'objectives[]';
    newField.className = 'w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500';
    newField.placeholder = 'Inserisci obiettivo del corso';
    container.insertBefore(newField, event.target);
}
</script>
</x-app-layout>
