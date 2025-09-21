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

    <!-- Success/Error Alerts -->
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-green-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-red-800 font-medium">Si sono verificati degli errori:</p>
                    <ul class="list-disc list-inside text-red-700 mt-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

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
              x-data="{ activeTab: 'basic', imagePreview: null }">

            @if ($errors->any())
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    <h4 class="font-bold">Errori di validazione:</h4>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
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
                            @php
                                $studentsCount = $course->enrollments()->where('status', 'active')->count();
                            @endphp
                            Studenti ({{ $studentsCount }})
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
                    <div :class="{ 'hidden': activeTab !== 'basic' }" class="space-y-6">
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
                                            <option value="beginner" {{ old('level', $course->level ?? '') === 'beginner' ? 'selected' : '' }}>Principiante</option>
                                            <option value="intermediate" {{ old('level', $course->level ?? '') === 'intermediate' ? 'selected' : '' }}>Intermedio</option>
                                            <option value="advanced" {{ old('level', $course->level ?? '') === 'advanced' ? 'selected' : '' }}>Avanzato</option>
                                            <option value="professional" {{ old('level', $course->level ?? '') === 'professional' ? 'selected' : '' }}>Professionale</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                                        <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="draft" {{ old('status', $course->status) === 'draft' ? 'selected' : '' }}>Bozza</option>
                                            <option value="published" {{ old('status', $course->status) === 'published' ? 'selected' : '' }}>Pubblicato</option>
                                            <option value="archived" {{ old('status', $course->status) === 'archived' ? 'selected' : '' }}>Archiviato</option>
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
                                        <p class="mt-1 text-xs text-gray-500">Attualmente iscritti: {{ $course->enrollments()->where('status', 'active')->count() }} studenti</p>
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

                                <!-- Date Fields -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Inizio *</label>
                                        <input type="date" name="start_date"
                                               value="{{ old('start_date', $course->start_date ? $course->start_date->format('Y-m-d') : '') }}"
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                        @error('start_date')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Fine</label>
                                        <input type="date" name="end_date"
                                               value="{{ old('end_date', $course->end_date ? $course->end_date->format('Y-m-d') : '') }}"
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                        @error('end_date')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details Tab -->
                    <div :class="{ 'hidden': activeTab !== 'details' }" class="space-y-6">
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
                    <div :class="{ 'hidden': activeTab !== 'students' }" class="space-y-6">
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
                        @php
                            $activeEnrollments = $course->enrollments()->with('user')->where('status', 'active')->get();
                        @endphp
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse ($activeEnrollments as $enrollment)
                                <!-- DEBUG: User ID {{ $enrollment->user->id ?? 'NULL' }} - {{ $enrollment->user->name ?? 'NULL' }} - Status: {{ $enrollment->status }} -->
                                @php
                                    Log::info('🎨 RENDERING STUDENT CARD', [
                                        'user_id' => $enrollment->user_id,
                                        'user_name' => $enrollment->user->name ?? 'NULL',
                                        'enrollment_id' => $enrollment->id,
                                        'has_user_object' => $enrollment->user ? 'YES' : 'NO'
                                    ]);
                                @endphp
                                @if($enrollment->user)
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
                                        <div class="relative" x-data="{ open: false }" @click.stop="console.log('Dropdown container clicked')">
                                            <button type="button" @click.stop="console.log('Button clicked, open was:', open); open = !open; console.log('Button clicked, open now:', open);" class="p-1 text-gray-400 hover:text-gray-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                </svg>
                                            </button>
                                            <div x-show="open" @click.away="open = false" @click.stop x-transition
                                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                                <div class="py-1">
                                                    <button type="button" @click.stop.prevent="openStudentDetailsModal({{ $enrollment->user->id }}, '{{ $enrollment->user->name }}', '{{ $enrollment->user->email }}', '{{ $enrollment->user->phone ?? 'N/A' }}', '{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}', '{{ $enrollment->status }}', '{{ $enrollment->payment_status }}')" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        </svg>
                                                        Vedi Dettagli
                                                    </button>
                                                    <button type="button" @click.stop.prevent="openContactModal({{ $enrollment->user->id }}, '{{ $enrollment->user->name }}', '{{ $enrollment->user->email }}')" class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                        </svg>
                                                        Contatta
                                                    </button>
                                                    <div @click.stop.prevent>
                                                        <!-- DEBUG FORM: User ID {{ $enrollment->user->id }} - Form ID: remove-form-{{ $enrollment->user->id }} -->
                                                        @php
                                                            Log::info('🔧 GENERATING REMOVE FORM', [
                                                                'user_id' => $enrollment->user_id,
                                                                'user_name' => $enrollment->user->name ?? 'NULL',
                                                                'enrollment_id' => $enrollment->id
                                                            ]);
                                                        @endphp
                                                        @php
                                                            try {
                                                                if (!$enrollment->user) {
                                                                    throw new \Exception('User relationship is null for enrollment ' . $enrollment->id);
                                                                }
                                                                if (!$enrollment->user->id) {
                                                                    throw new \Exception('User ID is null for enrollment ' . $enrollment->id);
                                                                }
                                                                $destroyRoute = route('admin.courses.students.destroy', [$course, $enrollment->user]);
                                                            } catch (\Exception $e) {
                                                                $destroyRoute = '#ERROR-' . $enrollment->user_id;
                                                                Log::error('🔥 Route generation failed for user', [
                                                                    'enrollment_id' => $enrollment->id,
                                                                    'user_id' => $enrollment->user_id,
                                                                    'user_object' => $enrollment->user ? 'EXISTS' : 'NULL',
                                                                    'user_object_id' => $enrollment->user ? $enrollment->user->id : 'N/A',
                                                                    'course_id' => $course->id,
                                                                    'error' => $e->getMessage()
                                                                ]);
                                                            }
                                                        @endphp
                                                        @php
                                                            Log::info('✅ FORM GENERATION COMPLETED', [
                                                                'user_id' => $enrollment->user_id,
                                                                'destroy_route' => $destroyRoute
                                                            ]);
                                                        @endphp
                                                        <form id="remove-form-{{ $enrollment->user_id }}" action="{{ $destroyRoute }}" method="POST">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" @click.stop.prevent="removeStudentConfirm($el, {{ $enrollment->user_id }}, '{{ $enrollment->user->name ?? 'Unknown' }}')" class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                                Rimuovi dal Corso
                                                            </button>
                                                        </form>
                                                    </div>
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
                                @endif
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
                    <div :class="{ 'hidden': activeTab !== 'schedule' }" class="space-y-6">
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
                                                    <p>{{ $slot['location'] ?? $course->location ?? 'Sede' }} • {{ $course->enrollments()->where('status', 'active')->count() }} studenti iscritti</p>
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
                                <div class="flex items-center justify-between">
                                    <h3 class="text-lg font-semibold text-gray-900">Modifica Orari</h3>
                                    <button type="button" onclick="addScheduleSlot()"
                                            class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 text-sm font-medium">
                                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Aggiungi Orario
                                    </button>
                                </div>

                                <div id="schedule-container" class="space-y-4">
                                    @php
                                        $scheduleData = old('schedule_slots', $course->schedule_data ?? []);
                                        if (empty($scheduleData)) {
                                            $scheduleData = [['day' => '', 'start_time' => '', 'end_time' => '', 'location' => '']];
                                        }
                                    @endphp


                                    @foreach($scheduleData as $index => $slot)
                                        <div class="schedule-slot bg-gray-50 hover:bg-gray-100 rounded-lg p-4 border border-gray-200 transition-all duration-200">
                                            <div class="flex items-center justify-between mb-3">
                                                <h4 class="font-medium text-gray-900">Orario {{ $index + 1 }}</h4>
                                                @if($index > 0)
                                                    <button type="button" onclick="removeScheduleSlot(this)"
                                                            class="text-red-600 hover:text-red-800 text-sm">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Rimuovi
                                                    </button>
                                                @endif
                                            </div>

                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Giorno della settimana *</label>
                                                    <select name="schedule_slots[{{ $index }}][day]"
                                                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                                        <option value="">Seleziona giorno</option>
                                                        <option value="Lunedì" {{ ($slot['day'] ?? '') === 'Lunedì' ? 'selected' : '' }}>Lunedì</option>
                                                        <option value="Martedì" {{ ($slot['day'] ?? '') === 'Martedì' ? 'selected' : '' }}>Martedì</option>
                                                        <option value="Mercoledì" {{ ($slot['day'] ?? '') === 'Mercoledì' ? 'selected' : '' }}>Mercoledì</option>
                                                        <option value="Giovedì" {{ ($slot['day'] ?? '') === 'Giovedì' ? 'selected' : '' }}>Giovedì</option>
                                                        <option value="Venerdì" {{ ($slot['day'] ?? '') === 'Venerdì' ? 'selected' : '' }}>Venerdì</option>
                                                        <option value="Sabato" {{ ($slot['day'] ?? '') === 'Sabato' ? 'selected' : '' }}>Sabato</option>
                                                        <option value="Domenica" {{ ($slot['day'] ?? '') === 'Domenica' ? 'selected' : '' }}>Domenica</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Sala/Ubicazione</label>
                                                    <select name="schedule_slots[{{ $index }}][location]"
                                                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                                            data-selected-value="{{ $slot['location'] ?? '' }}">
                                                        <!-- Options will be populated by JavaScript -->
                                                    </select>

                                                    {{-- Quick room management buttons --}}
                                                    <div class="flex gap-1 mt-1">
                                                        <button type="button" onclick="openRoomManager()"
                                                                class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                            </svg>
                                                            Gestisci Sale
                                                        </button>
                                                    </div>

                                                    {{-- Hint for users --}}
                                                    <small class="text-xs text-gray-500 mt-1 block">Aggiungi nuove sale tramite "Gestisci Sale"</small>
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4 mt-4">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Orario Inizio *</label>
                                                    <input type="time" name="schedule_slots[{{ $index }}][start_time]"
                                                           value="{{ isset($slot['start_time']) ? substr($slot['start_time'], 0, 5) : '' }}"
                                                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                                </div>
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-600 mb-1">Orario Fine *</label>
                                                    <input type="time" name="schedule_slots[{{ $index }}][end_time]"
                                                           value="{{ isset($slot['end_time']) ? substr($slot['end_time'], 0, 5) : '' }}"
                                                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                                </div>
                                            </div>

                                            <div class="mt-3 text-xs text-gray-500">
                                                <span class="font-medium">Durata: </span>
                                                <span class="duration-display">Seleziona orari per calcolare la durata</span>
                                            </div>
                                        </div>
                                    @endforeach
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
                    </div>

                    <!-- Pricing Tab -->
                    <div :class="{ 'hidden': activeTab !== 'pricing' }" class="space-y-6">
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
                                            <p class="text-xs text-green-600 mt-1">{{ $course->enrollments()->where('status', 'active')->count() }} studenti paganti</p>
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
                                            $monthlyRevenue = $activeStudents * ($course->monthly_price ?? $course->price ?? 0);
                                            $enrollmentFeeRevenue = 2 * ($course->enrollment_fee ?? 0);
                                            $totalRevenue = $monthlyRevenue + $enrollmentFeeRevenue;
                                        @endphp
                                        <p>Studenti: {{ $activeStudents }} × €{{ number_format($course->monthly_price ?? $course->price ?? 0, 2, ',', '.') }} = <strong>€{{ number_format($monthlyRevenue, 0, ',', '.') }}</strong></p>
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
                                            value="{{ old('monthly_price', $course->monthly_price ?? $course->price ?? '') }}"
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
                        <strong>{{ $course->enrollments()->where('status', 'active')->count() }} studenti</strong> sono attualmente iscritti a questo corso.
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

    <!-- Student Details Modal -->
    <x-modal name="student-details" maxWidth="2xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Dettagli Studente</h3>
                <button @click="$dispatch('close-modal', 'student-details')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <div class="space-y-6">
                <!-- Student Info Header -->
                <div class="flex items-center space-x-4 p-4 bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg border border-rose-100">
                    <div id="student-avatar" class="w-16 h-16 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-lg shadow-lg">
                    </div>
                    <div>
                        <h4 id="student-name" class="text-xl font-semibold text-gray-900"></h4>
                        <p id="student-email" class="text-gray-600"></p>
                    </div>
                </div>

                <!-- Details Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h5 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Informazioni Contatto
                        </h5>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Email:</span>
                                <span id="details-email" class="text-gray-900 bg-white px-2 py-1 rounded text-xs"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Telefono:</span>
                                <span id="details-phone" class="text-gray-900 bg-white px-2 py-1 rounded text-xs"></span>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <h5 class="font-semibold text-gray-900 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Iscrizione al Corso
                        </h5>
                        <div class="space-y-3 text-sm">
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Data Iscrizione:</span>
                                <span id="details-enrollment-date" class="text-gray-900 bg-white px-2 py-1 rounded text-xs"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Stato:</span>
                                <span id="details-status" class="text-gray-900 bg-white px-2 py-1 rounded text-xs"></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-gray-600 font-medium">Pagamento:</span>
                                <span id="details-payment-status" class="text-gray-900 bg-white px-2 py-1 rounded text-xs"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Statistics Card -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg border border-blue-100">
                    <h5 class="font-semibold text-gray-900 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Statistiche Presenza
                    </h5>
                    <div class="grid grid-cols-3 gap-4 text-center">
                        <div class="bg-white p-4 rounded-lg border border-blue-200">
                            <div class="text-3xl font-bold text-blue-600 mb-1">-</div>
                            <div class="text-sm text-gray-600 font-medium">Presenze</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-orange-200">
                            <div class="text-3xl font-bold text-orange-600 mb-1">-</div>
                            <div class="text-sm text-gray-600 font-medium">Assenze</div>
                        </div>
                        <div class="bg-white p-4 rounded-lg border border-green-200">
                            <div class="text-3xl font-bold text-green-600 mb-1">-</div>
                            <div class="text-sm text-gray-600 font-medium">% Presenza</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                <button @click="$dispatch('close-modal', 'student-details')" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Chiudi
                </button>
                <button type="button" onclick="openContactModalFromDetails()" class="px-4 py-2 text-sm font-medium text-white bg-rose-600 border border-transparent rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                    Contatta Studente
                </button>
            </div>
        </div>
    </x-modal>

    <!-- Contact Student Modal -->
    <x-modal name="contact-student" maxWidth="lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Contatta Studente</h3>
                <button @click="$dispatch('close-modal', 'contact-student')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="contact-form" action="#" method="POST">
                @csrf
                <input type="hidden" id="contact-student-id" name="student_id">

                <div class="space-y-6">
                    <!-- Student Info Header -->
                    <div class="flex items-center space-x-3 p-4 bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg border border-rose-100">
                        <div id="contact-avatar" class="w-12 h-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold shadow-lg">
                        </div>
                        <div>
                            <h4 id="contact-student-name" class="font-semibold text-gray-900"></h4>
                            <p id="contact-student-email" class="text-sm text-gray-600"></p>
                        </div>
                    </div>

                    <!-- Form Fields -->
                    <div>
                        <label for="contact-subject" class="block text-sm font-medium text-gray-700 mb-2">Oggetto *</label>
                        <select id="contact-subject" name="subject" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 text-sm" required>
                            <option value="">Seleziona oggetto</option>
                            <option value="attendance">Presenza/Assenza</option>
                            <option value="payment">Pagamento</option>
                            <option value="performance">Prestazioni</option>
                            <option value="general">Comunicazione Generale</option>
                            <option value="other">Altro</option>
                        </select>
                    </div>

                    <div>
                        <label for="contact-message" class="block text-sm font-medium text-gray-700 mb-2">Messaggio *</label>
                        <textarea id="contact-message" name="message" rows="6" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 text-sm" placeholder="Scrivi il tuo messaggio..." required></textarea>
                        <p class="text-xs text-gray-500 mt-1">Descrivi chiaramente il motivo del contatto e eventuali azioni richieste.</p>
                    </div>

                    <!-- Options -->
                    <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                        <div class="flex items-center">
                            <input type="checkbox" id="send-email" name="send_email" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500" checked>
                            <label for="send-email" class="ml-3 text-sm text-gray-700 font-medium">
                                Invia anche via email
                                <span class="text-gray-500 font-normal block text-xs">Il messaggio sarà inviato anche all'indirizzo email dello studente</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6 pt-4 border-t border-gray-200">
                    <button @click="$dispatch('close-modal', 'contact-student')" type="button" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Annulla
                    </button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-rose-600 border border-transparent rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Invia Messaggio
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Add Student Modal -->
    <x-modal name="add-student" maxWidth="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Aggiungi Studente al Corso</h3>
                <button @click="$dispatch('close-modal', 'add-student')"
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form action="{{ route('admin.courses.students.store', $course) }}" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Seleziona Studente
                    </label>
                    <select name="user_id" required
                            class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                        <option value="">Seleziona uno studente...</option>
                        @php
                            $availableStudents = \App\Models\User::where('role', 'user')
                                ->whereNotIn('id', $course->students->pluck('id'))
                                ->orderBy('name')
                                ->get();
                        @endphp
                        @foreach($availableStudents as $student)
                            <option value="{{ $student->id }}">{{ $student->name }} ({{ $student->email }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Stato Iscrizione
                    </label>
                    <select name="status" required
                            class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                        <option value="active" selected>Attiva</option>
                        <option value="pending">In Attesa</option>
                        <option value="cancelled">Annullata</option>
                        <option value="completed">Completata</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Stato Pagamento
                    </label>
                    <select name="payment_status" required
                            class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                        <option value="pending">In Sospeso</option>
                        <option value="paid">Pagato</option>
                        <option value="refunded">Rimborsato</option>
                    </select>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Note (opzionale)
                    </label>
                    <textarea name="notes" rows="3"
                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                              placeholder="Note aggiuntive per l'iscrizione..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <button @click="$dispatch('close-modal', 'add-student')" type="button"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Annulla
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        Aggiungi Studente
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

<script>
console.log('🔄 COURSE EDIT JS v2.3 - ENHANCED DEBUGGING SYSTEM');

// Global error handler to catch any JavaScript errors
window.addEventListener('error', function(e) {
    console.error('🚨 JavaScript Error:', e.error);
    console.error('Message:', e.message);
    console.error('Filename:', e.filename);
    console.error('Line:', e.lineno);
});

// Capture unhandled promise rejections
window.addEventListener('unhandledrejection', function(e) {
    console.error('🚨 Unhandled Promise Rejection:', e.reason);
});

// Available rooms data from server (global so it can be updated)
let availableRooms = @json($availableRooms);
window.availableRooms = availableRooms;

// Function to generate room options HTML
function generateRoomOptions() {
    let options = '<option value="">Seleziona sala</option>';
    availableRooms.forEach(room => {
        options += `<option value="${room}">${room}</option>`;
    });
    return options;
}

// Custom location system removed - use only database-managed rooms

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

// Schedule management functions
let scheduleSlotIndex = {{ count($scheduleData ?? []) }};

function addScheduleSlot() {
    const container = document.getElementById('schedule-container');
    const slotHtml = `
        <div class="schedule-slot bg-gray-50 rounded-lg p-4 border border-gray-200">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Orario ${scheduleSlotIndex + 1}</h4>
                <button type="button" onclick="removeScheduleSlot(this)"
                        class="text-red-600 hover:text-red-800 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Rimuovi
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Giorno della settimana *</label>
                    <select name="schedule_slots[${scheduleSlotIndex}][day]" onchange="updateSlotNumbers()"
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
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
                    <select name="schedule_slots[${scheduleSlotIndex}][location]"
                            class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                        ${generateRoomOptions()}
                    </select>

                    <div class="flex gap-1 mt-1">
                        <button type="button" onclick="openRoomManager()"
                                class="text-xs text-blue-600 hover:text-blue-800 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            Gestisci Sale
                        </button>
                        <small class="text-xs text-gray-500">Aggiungi nuove sale tramite il modal</small>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mt-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Orario Inizio *</label>
                    <input type="time" name="schedule_slots[${scheduleSlotIndex}][start_time]"
                           onchange="calculateDuration(this)"
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Orario Fine *</label>
                    <input type="time" name="schedule_slots[${scheduleSlotIndex}][end_time]"
                           onchange="calculateDuration(this)"
                           class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                </div>
            </div>

            <div class="mt-3 text-xs text-gray-500">
                <span class="font-medium">Durata: </span>
                <span class="duration-display">Seleziona orari per calcolare la durata</span>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', slotHtml);
    scheduleSlotIndex++;
    updateSlotNumbers();
}

function removeScheduleSlot(button) {
    const slot = button.closest('.schedule-slot');
    slot.remove();
    updateSlotNumbers();
}

function updateSlotNumbers() {
    const slots = document.querySelectorAll('.schedule-slot');
    slots.forEach((slot, index) => {
        const title = slot.querySelector('h4');
        const daySelect = slot.querySelector('select[name*="[day]"]');
        const selectedDay = daySelect.value;

        if (selectedDay) {
            title.textContent = `${selectedDay} - Orario ${index + 1}`;
        } else {
            title.textContent = `Orario ${index + 1}`;
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

function calculateDuration(input) {
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

// Initialize duration calculation for existing slots
document.addEventListener('DOMContentLoaded', function() {
    const timeInputs = document.querySelectorAll('input[type="time"]');
    timeInputs.forEach(input => {
        input.addEventListener('change', function() {
            calculateDuration(this);
        });
        // Calculate on page load
        calculateDuration(input);
    });

    const daySelects = document.querySelectorAll('select[name*="[day]"]');
    daySelects.forEach(select => {
        select.addEventListener('change', function() {
            updateSlotNumbers();
        });
    });

    updateSlotNumbers();

    // Initialize ALL location selects (existing + new) with JavaScript-generated options
    initializeAllLocationDropdowns();

    // Debug form submission with comprehensive error checking
    const form = document.querySelector('form');
    if (form) {
        console.log('🎯 Form found, adding event listeners...', form);

        // High priority listener to catch submit early
        form.addEventListener('submit', function(e) {
            console.log('🔥 HIGH PRIORITY SUBMIT EVENT TRIGGERED!');
            console.log('Event:', e);
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
        }, { capture: true });

        // Normal priority listener
        form.addEventListener('submit', function(e) {
            console.log('🔥 NORMAL PRIORITY SUBMIT EVENT TRIGGERED!');
            console.log('Event:', e);
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);

            const formData = new FormData(form);
            console.log('📋 All form data:');
            for (let [key, value] of formData.entries()) {
                console.log(key, '=', value);
            }

            // Check specifically for pricing fields
            console.log('💰 Pricing fields check:');
            console.log('monthly_price:', formData.get('monthly_price'));
            console.log('enrollment_fee:', formData.get('enrollment_fee'));
            console.log('single_lesson_price:', formData.get('single_lesson_price'));
            console.log('trial_price:', formData.get('trial_price'));
            console.log('price_application:', formData.get('price_application'));

            // Check if form submission is being prevented
            console.log('Is default prevented?', e.defaultPrevented);

            // Check form validity (HTML5 validation)
            console.log('Form validity:', form.checkValidity());
            if (!form.checkValidity()) {
                console.error('❌ Form validation failed! Invalid fields:');
                const invalidFields = form.querySelectorAll(':invalid');
                invalidFields.forEach(field => {
                    console.error('- Invalid field:', field.name, field.value, field.validationMessage);
                });
            }
        });

        // Add click listener to submit buttons specifically
        const submitButtons = form.querySelectorAll('button[type="submit"]');
        submitButtons.forEach((btn, index) => {
            console.log(`🎯 Submit button ${index} found:`, btn);
            console.log('Button text:', btn.textContent.trim());
            console.log('Button classes:', btn.className);
            console.log('Button disabled:', btn.disabled);
            console.log('Button offsetParent:', btn.offsetParent);
            console.log('Button style display:', window.getComputedStyle(btn).display);
            console.log('Button style visibility:', window.getComputedStyle(btn).visibility);
            console.log('Button style pointer-events:', window.getComputedStyle(btn).pointerEvents);

            // Add multiple types of event listeners
            btn.addEventListener('click', function(e) {
                console.log(`🖱️ CLICK EVENT Submit button ${index} clicked!`, btn);
                console.log('Event target:', e.target);
                console.log('Event currentTarget:', e.currentTarget);
                console.log('Button value:', btn.value);
                console.log('Button name:', btn.name);
            }, true); // Capture phase

            btn.addEventListener('click', function(e) {
                console.log(`🖱️ BUBBLE EVENT Submit button ${index} clicked!`, btn);
            }, false); // Bubble phase

            btn.addEventListener('mousedown', function(e) {
                console.log(`🖱️ MOUSEDOWN Submit button ${index}!`);
            });

            btn.addEventListener('mouseup', function(e) {
                console.log(`🖱️ MOUSEUP Submit button ${index}!`);
            });
        });

        // Global click detection for debugging
        document.addEventListener('click', function(e) {
            if (e.target.matches('button[type="submit"]')) {
                console.log(`🌍 GLOBAL CLICK DETECTED on submit button:`, e.target);
                console.log('Target text:', e.target.textContent.trim());

                // Check form validity when button is clicked
                console.log('🔍 Debug target element:');
                console.log('- Target tag:', e.target.tagName);
                console.log('- Target type:', e.target.type);
                console.log('- Target parent:', e.target.parentElement);
                console.log('- Target parent tag:', e.target.parentElement?.tagName);

                // Try multiple ways to find the form
                const form1 = e.target.closest('form');
                const form2 = e.target.form;
                const form3 = document.querySelector('form');

                // Count ALL forms in the page
                const allForms = document.querySelectorAll('form');
                console.log('🔍 Form search results:');
                console.log('- closest("form"):', form1);
                console.log('- target.form:', form2);
                console.log('- document.querySelector("form"):', form3);
                console.log('- Total forms in page:', allForms.length);
                allForms.forEach((f, i) => {
                    console.log(`  Form ${i}: action="${f.action}" method="${f.method}"`);
                });

                // Find the course edit form specifically (Form 1 from debug)
                const courseForm = Array.from(allForms).find(f => {
                    const isCoursePath = f.action.includes('/admin/courses/41') && f.method.toLowerCase() === 'post';
                    const isNotLogout = !f.action.includes('/logout');
                    const isNotStudents = !f.action.includes('/students');
                    const isNotGet = f.method.toLowerCase() !== 'get';
                    console.log(`Checking form: ${f.action} - isCoursePath:${isCoursePath} isNotLogout:${isNotLogout} isNotStudents:${isNotStudents} isNotGet:${isNotGet}`);
                    return isCoursePath && isNotLogout && isNotStudents && isNotGet;
                });

                console.log('🎯 Course form found:', courseForm);

                const form = form1 || form2 || courseForm || form3;
                if (form) {
                    console.log('🔍 Form validity check:');
                    console.log('- Form valid:', form.checkValidity());
                    console.log('- Form action:', form.action);
                    console.log('- Form method:', form.method);

                    if (!form.checkValidity()) {
                        console.error('❌ Form validation failed! Invalid fields:');
                        const invalidFields = form.querySelectorAll(':invalid');
                        invalidFields.forEach(field => {
                            console.error(`- Invalid: ${field.name} = "${field.value}" (${field.validationMessage})`);
                        });

                        // Prevent form submission if invalid
                        e.preventDefault();
                        console.log('🚫 Form submission prevented due to validation errors');
                        return false;
                    } else {
                        console.log('✅ Form is valid, forcing manual submission...');

                        // Force manual form submission
                        e.preventDefault(); // Prevent default to control submission

                        // Add the button's name and value to the form
                        const hiddenAction = document.createElement('input');
                        hiddenAction.type = 'hidden';
                        hiddenAction.name = e.target.name || 'action';
                        hiddenAction.value = e.target.value || 'update';
                        form.appendChild(hiddenAction);

                        console.log('🚀 Manually submitting form...');

                        // Show success message and reload after submission
                        const submitBtn = e.target;
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';

                        // Submit form
                        form.submit();

                        // Reload page after a short delay to show data was saved
                        setTimeout(() => {
                            window.location.reload();
                        }, 1000);

                        return false;
                    }
                } else {
                    console.error('❌ No form found for submit button');
                }
            }
        }, true);

    } else {
        console.error('❌ Form not found!');
    }
});

// Enhanced schedule management functions
function validateTimeSlots() {
    const slots = document.querySelectorAll('.schedule-slot');
    const conflicts = [];

    // Clear previous conflict indicators
    slots.forEach(slot => {
        const conflictIndicator = slot.querySelector('.conflict-indicator');
        if (conflictIndicator) {
            conflictIndicator.remove();
        }
        slot.classList.remove('border-red-300', 'bg-red-50');
    });

    // Check for conflicts between slots
    for (let i = 0; i < slots.length; i++) {
        for (let j = i + 1; j < slots.length; j++) {
            const conflict = checkSlotConflict(slots[i], slots[j]);
            if (conflict) {
                conflicts.push({ slot1: slots[i], slot2: slots[j], message: conflict });
            }
        }
    }

    // Display conflicts
    conflicts.forEach(conflict => {
        showConflictIndicator(conflict.slot1, conflict.message);
        showConflictIndicator(conflict.slot2, conflict.message);
    });

    return conflicts.length === 0;
}

function checkSlotConflict(slot1, slot2) {
    const day1 = slot1.querySelector('select[name*="[day]"]').value;
    const day2 = slot2.querySelector('select[name*="[day]"]').value;

    if (!day1 || !day2 || day1 !== day2) {
        return null; // No conflict if different days or empty
    }

    const start1 = slot1.querySelector('input[name*="[start_time]"]').value;
    const end1 = slot1.querySelector('input[name*="[end_time]"]').value;
    const start2 = slot2.querySelector('input[name*="[start_time]"]').value;
    const end2 = slot2.querySelector('input[name*="[end_time]"]').value;

    if (!start1 || !end1 || !start2 || !end2) {
        return null; // Can't check without complete times
    }

    // Check for time overlap
    if (clientTimesOverlap(start1, end1, start2, end2)) {
        const location1 = slot1.querySelector('select[name*="[location]"]').value;
        const location2 = slot2.querySelector('select[name*="[location]"]').value;

        if (location1 && location2 && location1 === location2) {
            return `Conflitto: stessa location "${location1}" il ${day1} dalle ${start1}-${end1} e ${start2}-${end2}`;
        } else {
            return `Attenzione: orari sovrapposti il ${day1} dalle ${start1}-${end1} e ${start2}-${end2}`;
        }
    }

    return null;
}

function clientTimesOverlap(start1, end1, start2, end2) {
    const startTime1 = new Date(`2000-01-01T${start1}:00`);
    const endTime1 = new Date(`2000-01-01T${end1}:00`);
    const startTime2 = new Date(`2000-01-01T${start2}:00`);
    const endTime2 = new Date(`2000-01-01T${end2}:00`);

    // Check for overlap: start1 < end2 && start2 < end1
    return startTime1 < endTime2 && startTime2 < endTime1;
}

function showConflictIndicator(slot, message) {
    slot.classList.add('border-red-300', 'bg-red-50');

    if (!slot.querySelector('.conflict-indicator')) {
        const indicator = document.createElement('div');
        indicator.className = 'conflict-indicator mt-2 p-2 bg-red-100 border border-red-300 rounded text-red-700 text-sm';
        indicator.innerHTML = `<i class="fas fa-exclamation-triangle mr-1"></i> ${message}`;
        slot.appendChild(indicator);
    }
}

// Enhanced event handling with delegation
document.addEventListener('DOMContentLoaded', function() {
    const scheduleContainer = document.getElementById('schedule-container');

    // Use event delegation for better performance and dynamic content handling
    scheduleContainer.addEventListener('change', function(e) {
        if (e.target.type === 'time') {
            calculateDuration(e.target);
            setTimeout(validateTimeSlots, 100); // Small delay to ensure DOM is updated
        } else if (e.target.matches('select[name*="[day]"]')) {
            updateSlotNumbers();
            setTimeout(validateTimeSlots, 100);
        } else if (e.target.matches('select[name*="[location]"]')) {
            setTimeout(validateTimeSlots, 100);
        }
    });

    // Form submission validation - TEMPORARILY DISABLED FOR TESTING
    // const form = document.querySelector('form');
    // if (form) {
    //     form.addEventListener('submit', function(e) {
    //         // Only validate time slots if we're in the schedule tab
    //         const scheduleTab = document.querySelector('[x-show="activeTab === \'schedule\'"]');
    //         const isScheduleTabActive = scheduleTab && !scheduleTab.hidden;

    //         if (isScheduleTabActive && !validateTimeSlots()) {
    //             e.preventDefault();
    //             alert('Ci sono conflitti negli orari. Risolvi i conflitti prima di salvare.');
    //             return false;
    //         }
    //     });
    // }

    // Initialize validation for existing slots
    setTimeout(validateTimeSlots, 500);

    // Add UX enhancements
    initializeUXEnhancements();
});

// Initialize all location dropdowns with current room data
function initializeAllLocationDropdowns() {
    console.log('🏗️ Initializing all location dropdowns...');
    console.log('📋 Current availableRooms:', availableRooms);

    const locationSelects = document.querySelectorAll('select[name*="[location]"]');
    console.log(`🔍 Found ${locationSelects.length} location dropdowns to update`);

    locationSelects.forEach((select, index) => {
        // Get the selected value (either current value or from data attribute for initial load)
        const selectedValue = select.value || select.getAttribute('data-selected-value');
        console.log(`🎯 Dropdown ${index}: selectedValue="${selectedValue}"`);

        // Populate with current room options
        const newOptionsHtml = generateRoomOptions();
        console.log(`🔄 Dropdown ${index}: Setting new options HTML`);
        select.innerHTML = newOptionsHtml;

        // Restore the selected value
        if (selectedValue) {
            if (availableRooms.includes(selectedValue)) {
                // This is a managed room, select it directly
                select.value = selectedValue;
                console.log(`✅ Dropdown ${index}: Restored managed room "${selectedValue}"`);
            } else if (selectedValue) {
                // Keep the value as-is for now (legacy data)
                // New rooms will be added through the modal system
                select.value = selectedValue;
                console.log(`⚠️ Dropdown ${index}: Keeping legacy value "${selectedValue}"`);
            }
        } else {
            console.log(`📭 Dropdown ${index}: No value to restore`);
        }
    });

    console.log(`✅ Initialized ${locationSelects.length} location dropdowns`);
}

// UX Enhancement functions
function initializeUXEnhancements() {
    // Add helpful tooltips and visual improvements
    addTimeSlotTooltips();
    enhanceSlotVisuals();
    addKeyboardNavigation();
}

function addTimeSlotTooltips() {
    const scheduleContainer = document.getElementById('schedule-container');

    // Add helpful tooltips to time inputs
    scheduleContainer.addEventListener('focus', function(e) {
        if (e.target.type === 'time') {
            if (!e.target.title) {
                e.target.title = 'Formato: HH:MM (es. 09:30)';
            }
        }
    }, true);
}

function enhanceSlotVisuals() {
    // Add visual feedback when slots are being edited
    const scheduleContainer = document.getElementById('schedule-container');

    scheduleContainer.addEventListener('focus', function(e) {
        if (e.target.closest('.schedule-slot')) {
            const slot = e.target.closest('.schedule-slot');
            slot.classList.add('ring-2', 'ring-rose-300', 'border-rose-300');
        }
    }, true);

    scheduleContainer.addEventListener('blur', function(e) {
        if (e.target.closest('.schedule-slot')) {
            const slot = e.target.closest('.schedule-slot');
            slot.classList.remove('ring-2', 'ring-rose-300', 'border-rose-300');
        }
    }, true);
}

function addKeyboardNavigation() {
    // Add keyboard shortcuts for better accessibility
    document.addEventListener('keydown', function(e) {
        if (e.ctrlKey || e.metaKey) {
            switch(e.key) {
                case 'n':
                    if (document.activeElement.closest('#schedule-container')) {
                        e.preventDefault();
                        addScheduleSlot();
                    }
                    break;
                case 'd':
                    if (document.activeElement.closest('.schedule-slot')) {
                        e.preventDefault();
                        const removeBtn = document.activeElement.closest('.schedule-slot').querySelector('button[onclick*="removeScheduleSlot"]');
                        if (removeBtn) {
                            removeScheduleSlot(removeBtn);
                        }
                    }
                    break;
            }
        }
    });
}

// Enhanced addScheduleSlot function with UX improvements
const originalAddScheduleSlot = window.addScheduleSlot;
window.addScheduleSlot = function() {
    const result = originalAddScheduleSlot();

    // Focus on the new slot's first input for better UX
    setTimeout(() => {
        const newSlot = document.querySelector('.schedule-slot:last-child');
        if (newSlot) {
            const firstInput = newSlot.querySelector('select, input');
            if (firstInput) {
                firstInput.focus();
            }

            // Add a subtle animation
            newSlot.style.opacity = '0';
            newSlot.style.transform = 'translateY(10px)';
            setTimeout(() => {
                newSlot.style.transition = 'all 0.3s ease';
                newSlot.style.opacity = '1';
                newSlot.style.transform = 'translateY(0)';
            }, 10);
        }
    }, 100);

    return result;
};

// Add visual feedback for form validation
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        // Add visual feedback on form submission
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Salvando...';

                // Re-enable after 5 seconds in case of issues
                setTimeout(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Salva Modifiche';
                }, 5000);
            }
        });
    }
});

// Modal Management Functions
let currentStudentData = {};

function openStudentDetailsModal(id, name, email, phone, enrollmentDate, status, paymentStatus) {
    currentStudentData = { id, name, email, phone, enrollmentDate, status, paymentStatus };

    // Set avatar initials
    const avatar = document.getElementById('student-avatar');
    const initials = getInitials(name);
    avatar.textContent = initials;

    // Set basic info
    document.getElementById('student-name').textContent = name;
    document.getElementById('student-email').textContent = email;
    document.getElementById('details-email').textContent = email;
    document.getElementById('details-phone').textContent = phone;
    document.getElementById('details-enrollment-date').textContent = enrollmentDate;
    document.getElementById('details-status').textContent = getStatusLabel(status);
    document.getElementById('details-payment-status').textContent = getPaymentStatusLabel(paymentStatus);

    // Show modal using Laravel modal system
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'student-details' }));
}

function openContactModal(id, name, email) {
    currentStudentData = { id, name, email };

    // Set avatar initials
    const avatar = document.getElementById('contact-avatar');
    const initials = getInitials(name);
    avatar.textContent = initials;

    // Set contact info
    document.getElementById('contact-student-id').value = id;
    document.getElementById('contact-student-name').textContent = name;
    document.getElementById('contact-student-email').textContent = email;

    // Reset form
    document.getElementById('contact-form').reset();
    document.getElementById('contact-student-id').value = id;
    document.getElementById('send-email').checked = true;

    // Show modal using Laravel modal system
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'contact-student' }));
}

function openContactModalFromDetails() {
    // Close details modal and open contact modal
    window.dispatchEvent(new CustomEvent('close-modal', { detail: 'student-details' }));
    setTimeout(() => {
        openContactModal(currentStudentData.id, currentStudentData.name, currentStudentData.email);
    }, 100);
}

function getInitials(name) {
    const nameParts = name.split(' ');
    const firstInitial = nameParts[0] ? nameParts[0].charAt(0).toUpperCase() : '';
    const lastInitial = nameParts[1] ? nameParts[1].charAt(0).toUpperCase() : '';
    return firstInitial + lastInitial;
}

function getStatusLabel(status) {
    const statusLabels = {
        'active': 'Attiva',
        'pending': 'In Attesa',
        'cancelled': 'Annullata',
        'completed': 'Completata',
        'suspended': 'Sospesa'
    };
    return statusLabels[status] || status;
}

function getPaymentStatusLabel(paymentStatus) {
    const paymentLabels = {
        'paid': 'Pagato',
        'pending': 'In Sospeso',
        'refunded': 'Rimborsato'
    };
    return paymentLabels[paymentStatus] || paymentStatus;
}

function removeStudentConfirm(buttonElement, studentId, studentName) {
    console.log(`🔍 CHECKING FORM FOR: ${studentName} (ID: ${studentId})`);

    // The form is in an Alpine.js x-show dropdown that might not be in DOM
    // Method 1: Check if form is already in DOM (dropdown is open)
    let formElement = document.getElementById(`remove-form-${studentId}`);
    console.log(`Method 1 - getElementById result:`, formElement);

    if (!formElement) {
        // Method 2: Search in button's parent hierarchy (the form should be a sibling)
        let current = buttonElement.parentElement;
        while (current && current !== document) {
            const form = current.querySelector(`#remove-form-${studentId}`);
            if (form) {
                formElement = form;
                console.log(`Method 2 - Found form via querySelector in parent:`, formElement);
                break;
            }
            current = current.parentElement;
        }
    }

    if (!formElement) {
        // Method 3: Search all forms for matching action URL
        const allForms = Array.from(document.querySelectorAll('form'));
        formElement = allForms.find(form =>
            form.action && form.action.includes(`/students/${studentId}`)
        );
        console.log(`Method 3 - Found form by action URL:`, formElement);
    }

    if (!formElement) {
        // Method 4: The form exists in HTML but Alpine.js hides it
        // Let's try to find the parent container and force show the dropdown
        console.log(`Method 4 - Trying to access hidden Alpine.js form...`);

        // Find the dropdown container
        let dropdownContainer = buttonElement.closest('[x-data*="open"]');
        if (dropdownContainer) {
            console.log('Found dropdown container:', dropdownContainer);

            // Try to access Alpine data and force open
            if (dropdownContainer._x_dataStack && dropdownContainer._x_dataStack[0]) {
                const alpineData = dropdownContainer._x_dataStack[0];
                if (alpineData.open !== undefined) {
                    console.log('Setting Alpine open to true temporarily...');
                    const wasOpen = alpineData.open;
                    alpineData.open = true;

                    // Force Alpine.js to re-render by triggering the reactive system
                    if (window.Alpine && window.Alpine.nextTick) {
                        window.Alpine.nextTick(() => {
                            setTimeout(() => {
                                formElement = document.getElementById(`remove-form-${studentId}`);
                                console.log('Method 4 - Form found after Alpine nextTick:', formElement);

                                if (formElement) {
                                    processFormSubmission(formElement, studentId, studentName);
                                } else {
                                    fallbackRemoval(studentId, studentName);
                                }

                                // Restore original state
                                alpineData.open = wasOpen;
                            }, 50);
                        });
                    } else {
                        // Fallback without Alpine.nextTick
                        setTimeout(() => {
                            formElement = document.getElementById(`remove-form-${studentId}`);
                            console.log('Method 4 - Form found after forcing Alpine open (no nextTick):', formElement);

                            if (formElement) {
                                processFormSubmission(formElement, studentId, studentName);
                            } else {
                                fallbackRemoval(studentId, studentName);
                            }

                            // Restore original state
                            alpineData.open = wasOpen;
                        }, 100);
                    }
                    return; // Exit here, continuation is in setTimeout
                }
            }
        }
    }

    if (!formElement) {
        console.error(`❌ FORM NOT FOUND for student ${studentId} using all methods`);
        console.log('🔄 Trying fallback removal with temporary form...');
        fallbackRemoval(studentId, studentName);
        return;
    }

    // If we found the form, process it
    processFormSubmission(formElement, studentId, studentName);
}

function processFormSubmission(formElement, studentId, studentName) {
    console.log(`✅ FORM FOUND for student ${studentId}:`, formElement);

    if (confirm(`Sei sicuro di voler rimuovere ${studentName} dal corso?\n\nQuesta azione non può essere annullata.`)) {
        console.log(`Removing student ${studentName} (ID: ${studentId})`);
        console.log('Submitting form:', formElement);
        console.log('Form action:', formElement.action);
        console.log('Form method:', formElement.method);

        // Submit the found form
        try {
            formElement.submit();
        } catch (error) {
            console.error('Form submission failed:', error);

            // Fallback: create temporary form and submit
            console.log('Trying temporary form fallback...');
            const tempForm = document.createElement('form');
            tempForm.method = 'POST';
            tempForm.action = `/admin/courses/41/students/${studentId}`;
            tempForm.style.display = 'none';

            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken;
                tempForm.appendChild(csrfInput);
            }

            // Add method override for DELETE
            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            tempForm.appendChild(methodInput);

            // Add form to DOM and submit
            document.body.appendChild(tempForm);
            tempForm.submit();
        }

        // Try multiple methods to find the form
        if (buttonElement && typeof buttonElement.closest === 'function') {
            console.log('Trying closest() method...');
            form = buttonElement.closest('form');
        }

        if (!form && buttonElement && buttonElement.parentNode) {
            console.log('Trying parentNode traversal...');
            let parent = buttonElement.parentNode;
            while (parent && parent.tagName !== 'FORM' && parent !== document.body) {
                parent = parent.parentNode;
            }
            if (parent && parent.tagName === 'FORM') {
                form = parent;
            }
        }

        if (!form) {
            console.log('Trying by ID...');
            form = document.getElementById(`remove-form-${studentId}`);
        }

        if (form) {
            console.log('Form found, submitting...', form);
            console.log('Form action:', form.action);
            console.log('Form method:', form.method);
            console.log('Form ID:', form.id);

            // Verify this is the correct form for student removal
            if (form.action.includes('/students/') || form.id.includes('remove-form')) {
                console.log('Verified: This is a student removal form');
                form.submit();
                return; // Exit function after successful submission
            } else {
                console.warn('Warning: This appears to be the wrong form!');
                console.log('Expected: URL should contain /students/ or ID should contain remove-form');
                console.log('Falling back to ID search...');

                const correctForm = document.getElementById(`remove-form-${studentId}`);
                if (correctForm) {
                    console.log('Found correct form by ID:', correctForm);
                    correctForm.submit();
                    return; // Exit function after successful submission
                } else {
                    console.error('Could not find the correct student removal form');
                    // Don't return here - continue to fetch fallback
                    form = null; // Reset form so we go to fetch fallback
                }
            }
        }

        // If we reach here, no form was found or wrong form was detected
        // Last resort: try to submit via fetch
        if (!form || form.action.includes('/admin/courses/41')) {
            console.error(`Form not found or wrong form detected for student ${studentId}`);
            console.log('All forms in document:', document.querySelectorAll('form'));
            console.log('All forms with remove-form ID:', document.querySelectorAll('form[id*="remove-form"]'));
            console.log('All forms with student ID:', document.querySelectorAll(`form[id="remove-form-${studentId}"]`));
            console.log('Button parent elements:', buttonElement.parentNode, buttonElement.parentNode.parentNode);

            console.log('Trying direct fetch submission...');

            // Build the URL properly
            const baseUrl = '{{ route("admin.courses.students.destroy", [$course->id, "PLACEHOLDER"]) }}';
            const formAction = baseUrl.replace('PLACEHOLDER', studentId);

            console.log('Fetch URL:', formAction);

            fetch(formAction, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
            }).then(response => {
                if (response.ok) {
                    console.log('Student removed successfully via fetch');
                    location.reload();
                } else {
                    console.error('Failed to remove student via fetch, status:', response.status);

                    if (response.status === 403) {
                        alert('⚠️ Permessi insufficienti per rimuovere questo studente.\n\nPossibili cause:\n- Lo studente non è più iscritto al corso\n- Non hai i permessi per questa operazione\n\nRicarica la pagina per aggiornare la lista studenti.');
                        location.reload();
                    } else if (response.status === 404) {
                        alert('⚠️ Studente non trovato.\n\nLo studente potrebbe essere già stato rimosso da un altro utente.\nRicarica la pagina per aggiornare la lista.');
                        location.reload();
                    } else {
                        response.text().then(text => {
                            console.error('Response:', text);
                            alert('❌ Errore durante la rimozione dello studente.\n\nErrore: ' + response.status + '\nConsulta la console per maggiori dettagli.');
                        });
                    }
                }
            }).catch(error => {
                console.error('Error removing student:', error);
                alert('❌ Errore di connessione durante la rimozione dello studente.\n\nControlla la connessione internet e riprova.');
            });
        }
    }
}

function fallbackRemoval(studentId, studentName) {
    console.log('🔄 NEW FALLBACK REMOVAL METHOD v2.0 - Using temporary form method...');

    if (confirm(`Sei sicuro di voler rimuovere ${studentName} dal corso?\n\nQuesta azione non può essere annullata.`)) {
        // Create a temporary form and submit it (most reliable method)
        const tempForm = document.createElement('form');
        tempForm.method = 'POST';
        tempForm.action = `/admin/courses/41/students/${studentId}`;
        tempForm.style.display = 'none';

        // Add CSRF token
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfToken;
            tempForm.appendChild(csrfInput);
        }

        // Add method override for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        tempForm.appendChild(methodInput);

        // Add form to DOM and submit
        document.body.appendChild(tempForm);
        console.log('Submitting temporary form:', tempForm);
        console.log('Form action:', tempForm.action);
        tempForm.submit();
    }
}

// Handle contact form submission
document.addEventListener('DOMContentLoaded', function() {
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(contactForm);
            const studentId = formData.get('student_id');
            const subject = formData.get('subject');
            const message = formData.get('message');
            const sendEmail = formData.get('send_email');

            if (!subject || !message) {
                alert('Per favore compila tutti i campi obbligatori.');
                return;
            }

            // Here you would normally send the data to your backend
            console.log('Contact form submitted:', {
                studentId,
                subject,
                message,
                sendEmail: !!sendEmail
            });

            // For now, just show success message
            alert('Messaggio inviato con successo!\n\n' +
                  'Studente: ' + currentStudentData.name + '\n' +
                  'Oggetto: ' + subject + '\n' +
                  'Messaggio: ' + message.substring(0, 50) + '...');

            // Close modal using Laravel modal system
            window.dispatchEvent(new CustomEvent('close-modal', { detail: 'contact-student' }));
        });
    }
});

// DEBUG: Check all remove forms on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔍 DEBUG: Page loaded, checking all remove forms...');

    const allRemoveForms = document.querySelectorAll('form[id*="remove-form"]');
    console.log(`Found ${allRemoveForms.length} remove forms:`, allRemoveForms);

    allRemoveForms.forEach(form => {
        const formId = form.id;
        const studentId = formId.replace('remove-form-', '');
        console.log(`✅ Form found: ${formId} for student ID: ${studentId}`);
    });

    // Debug forms (removed Andrea Conti specific checks since he was successfully removed)
});

// ========================================
// ROOM MANAGEMENT SYSTEM
// ========================================

// Function to open the room manager modal
function openRoomManager() {
    console.log('🏢 Opening Room Manager...');
    loadRoomsList();
    window.dispatchEvent(new CustomEvent('open-modal', { detail: 'room-manager' }));
}

// Global room management data
let roomsData = [];
let editingRoomId = null;

// Load all rooms for the current school
async function loadRoomsList() {
    try {
        console.log('📥 Loading rooms list...');
        const response = await fetch('/admin/rooms', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            const responseData = await response.json();
            console.log('✅ API Response:', responseData);

            // Modal MUST use detailed_rooms (full objects), not just room names
            if (responseData.detailed_rooms && Array.isArray(responseData.detailed_rooms)) {
                roomsData = responseData.detailed_rooms;
                console.log('🔍 Using detailed_rooms array (REQUIRED for modal)');
            } else if (Array.isArray(responseData)) {
                // Fallback: if it's a direct array, assume it contains full objects
                roomsData = responseData;
                console.log('🔍 Using direct array response as fallback');
            } else {
                roomsData = [];
                console.error('❌ Modal requires detailed_rooms array, but not found in response:', responseData);
            }

            console.log('✅ Rooms data processed:', roomsData);
            console.log('🔍 RoomsData type:', typeof roomsData, 'Length:', roomsData.length);
            renderRoomsList();
        } else {
            console.error('❌ Failed to load rooms:', response.status);
            showNotification('Errore nel caricamento delle sale', 'error');
        }
    } catch (error) {
        console.error('❌ Error loading rooms:', error);
        showNotification('Errore di connessione', 'error');
    }
}

// Render the rooms list in the modal
function renderRoomsList() {
    console.log('🎨 Rendering rooms list, roomsData:', roomsData);

    const container = document.getElementById('rooms-list-container');
    if (!container) {
        console.error('❌ Rooms list container not found');
        return;
    }

    if (roomsData.length === 0) {
        console.log('📭 No rooms to display');
        container.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="text-gray-500 mb-4">Nessuna sala configurata</p>
                <button onclick="showAddRoomForm()" class="text-rose-600 hover:text-rose-800 font-medium">
                    + Aggiungi la prima sala
                </button>
            </div>
        `;
        return;
    }

    const roomsHtml = roomsData.map(room => {
        console.log('🏠 Rendering room:', room);

        // Safe access to room properties
        const roomName = room.name || 'Nome non disponibile';
        const roomDescription = room.description || 'Nessuna descrizione';
        const roomCapacity = room.capacity || 'Non specificata';
        const roomActive = room.active !== undefined ? room.active : true;
        const roomId = room.id || 0;

        return `
            <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h4 class="font-medium text-gray-900">${escapeHtml(roomName)}</h4>
                        <p class="text-sm text-gray-600 mt-1">${escapeHtml(roomDescription)}</p>
                        <div class="flex gap-4 mt-2 text-xs text-gray-500">
                            <span>Capacità: ${roomCapacity}</span>
                            <span class="flex items-center">
                                <div class="w-2 h-2 rounded-full ${roomActive ? 'bg-green-500' : 'bg-gray-400'} mr-1"></div>
                                ${roomActive ? 'Attiva' : 'Disattivata'}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-2 ml-4">
                        <button onclick="showEditRoomForm(${roomId})" class="text-blue-600 hover:text-blue-800 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                        <button onclick="deleteRoom(${roomId}, '${escapeHtml(roomName)}')" class="text-red-600 hover:text-red-800 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }).join('');

    container.innerHTML = roomsHtml;
}

// Show the add room form
function showAddRoomForm() {
    editingRoomId = null;
    document.getElementById('room-form-title').textContent = 'Aggiungi Nuova Sala';
    document.getElementById('room-form').reset();
    document.getElementById('room-active').checked = true;
    document.getElementById('room-form-container').classList.remove('hidden');
}

// Show the edit room form
function showEditRoomForm(roomId) {
    const room = roomsData.find(r => r.id === roomId);
    if (!room) {
        console.error('❌ Room not found:', roomId);
        return;
    }

    editingRoomId = roomId;
    document.getElementById('room-form-title').textContent = 'Modifica Sala';
    document.getElementById('room-name').value = room.name;
    document.getElementById('room-description').value = room.description || '';
    document.getElementById('room-capacity').value = room.capacity || '';
    document.getElementById('room-active').checked = room.active;
    document.getElementById('room-form-container').classList.remove('hidden');
}

// Hide the room form
function hideRoomForm() {
    document.getElementById('room-form-container').classList.add('hidden');
    editingRoomId = null;
}

// Save room (create or update)
async function saveRoom(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const roomData = {
        name: formData.get('name'),
        description: formData.get('description'),
        capacity: formData.get('capacity'),
        active: formData.has('active')
    };

    console.log('💾 Saving room:', roomData);

    try {
        const url = editingRoomId ? `/admin/rooms/${editingRoomId}` : '/admin/rooms';
        const method = editingRoomId ? 'PUT' : 'POST';

        const response = await fetch(url, {
            method: method,
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(roomData)
        });

        if (response.ok) {
            const result = await response.json();
            console.log('✅ Room saved successfully:', result);

            showNotification(
                editingRoomId ? 'Sala aggiornata con successo' : 'Sala creata con successo',
                'success'
            );

            hideRoomForm();
            await loadRoomsList();
            await refreshRoomDropdowns();
        } else {
            const error = await response.json();
            console.error('❌ Failed to save room:', error);
            showNotification(error.message || 'Errore nel salvataggio della sala', 'error');
        }
    } catch (error) {
        console.error('❌ Error saving room:', error);
        showNotification('Errore di connessione', 'error');
    }
}

// Delete room
async function deleteRoom(roomId, roomName) {
    if (!confirm(`Sei sicuro di voler eliminare la sala "${roomName}"?\n\nQuesta azione non può essere annullata.`)) {
        return;
    }

    console.log('🗑️ Deleting room:', roomId);

    try {
        const response = await fetch(`/admin/rooms/${roomId}`, {
            method: 'DELETE',
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        });

        if (response.ok) {
            console.log('✅ Room deleted successfully');
            showNotification('Sala eliminata con successo', 'success');
            await loadRoomsList();
            await refreshRoomDropdowns();
        } else {
            const error = await response.json();
            console.error('❌ Failed to delete room:', error);
            showNotification(error.message || 'Errore nell\'eliminazione della sala', 'error');
        }
    } catch (error) {
        console.error('❌ Error deleting room:', error);
        showNotification('Errore di connessione', 'error');
    }
}

// Refresh all room dropdowns on the page
async function refreshRoomDropdowns() {
    console.log('🔄 Refreshing room dropdowns...');

    try {
        // SIMPLIFIED APPROACH: Use roomsData from modal (already loaded and working)
        if (roomsData && Array.isArray(roomsData)) {
            // Extract room names from the detailed room objects
            const roomNames = roomsData.map(room => room.name).sort();
            console.log('📦 Extracted room names from modal data:', roomNames);

            // Update both local and global availableRooms variables
            availableRooms = roomNames;
            window.availableRooms = roomNames;
            console.log('🔄 Updated availableRooms variable:', availableRooms);

            // Use the same initialization logic for consistency
            initializeAllLocationDropdowns();

            console.log('✅ Room dropdowns refreshed via roomsData!');
        } else {
            console.warn('⚠️ roomsData not available for dropdown refresh');
        }
    } catch (error) {
        console.error('❌ Error refreshing dropdowns:', error);
    }
}

// Utility functions
function escapeHtml(text) {
    if (!text || typeof text !== 'string') {
        return text || '';
    }

    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, function(m) { return map[m]; });
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg text-white max-w-sm transition-all duration-300 transform translate-x-full`;

    // Set color based on type
    if (type === 'success') {
        notification.classList.add('bg-green-500');
    } else if (type === 'error') {
        notification.classList.add('bg-red-500');
    } else {
        notification.classList.add('bg-blue-500');
    }

    notification.innerHTML = `
        <div class="flex items-center justify-between">
            <span class="text-sm font-medium">${message}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-3 text-white hover:text-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 10);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentElement) {
                notification.remove();
            }
        }, 300);
    }, 5000);
}
</script>

<!-- Room Management Modal -->
<x-modal name="room-manager" maxWidth="3xl">
    <div class="p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Gestione Sale</h3>
            <button @click="$dispatch('close-modal', 'room-manager')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Add Room Button -->
        <div class="mb-6">
            <button onclick="showAddRoomForm()"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-rose-600 border border-transparent rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Aggiungi Sala
            </button>
        </div>

        <!-- Room Form (Hidden by default) -->
        <div id="room-form-container" class="hidden mb-6 bg-gray-50 rounded-lg border border-gray-200 p-4">
            <h4 id="room-form-title" class="font-medium text-gray-900 mb-4">Aggiungi Nuova Sala</h4>
            <form id="room-form" onsubmit="saveRoom(event)">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="room-name" class="block text-sm font-medium text-gray-700 mb-1">Nome Sala *</label>
                        <input type="text" id="room-name" name="name" required
                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                               placeholder="es. Sala A, Studio Principale">
                    </div>
                    <div>
                        <label for="room-capacity" class="block text-sm font-medium text-gray-700 mb-1">Capacità</label>
                        <input type="number" id="room-capacity" name="capacity" min="1"
                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                               placeholder="Numero massimo di persone">
                    </div>
                </div>
                <div class="mt-4">
                    <label for="room-description" class="block text-sm font-medium text-gray-700 mb-1">Descrizione</label>
                    <textarea id="room-description" name="description" rows="2"
                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                              placeholder="Descrizione della sala (opzionale)"></textarea>
                </div>
                <div class="mt-4 flex items-center">
                    <input type="checkbox" id="room-active" name="active" checked
                           class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                    <label for="room-active" class="ml-2 text-sm text-gray-700">Sala attiva</label>
                </div>
                <div class="flex justify-end space-x-3 mt-4 pt-4 border-t border-gray-200">
                    <button type="button" onclick="hideRoomForm()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200">
                        Annulla
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-rose-600 border border-transparent rounded-lg hover:bg-rose-700">
                        Salva Sala
                    </button>
                </div>
            </form>
        </div>

        <!-- Rooms List -->
        <div id="rooms-list-container" class="space-y-3">
            <!-- Rooms will be loaded here via JavaScript -->
            <div class="text-center py-8">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-rose-500 mx-auto mb-4"></div>
                <p class="text-gray-500">Caricamento sale...</p>
            </div>
        </div>
    </div>
</x-modal>

</x-app-layout>
