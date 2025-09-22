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

    <!-- Main Container -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Success/Error Alerts -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <p class="text-green-800">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
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
                    <h3 class="text-sm font-medium text-blue-900">Corso {{ $course->status === 'active' ? 'Attivo' : 'Inattivo' }} con {{ $course->enrollments()->where('status', 'active')->count() }} Studenti Iscritti</h3>
                    <p class="text-sm text-blue-700 mt-1">
                        Le modifiche agli orari e ai prezzi potrebbero influenzare gli studenti già iscritti.
                        Ti consigliamo di comunicare i cambiamenti con almeno 7 giorni di anticipo.
                    </p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.courses.update', $course) }}" method="POST" enctype="multipart/form-data"
              x-data="{
                  activeTab: 'basic',
                  imagePreview: '{{ $course->image ? Storage::url($course->image) : null }}'
              }">
            @csrf
            @method('PUT')

            @php
                // Prepare data for the form
                $scheduleData = old('schedule_slots', $course->schedule_data ?? []);
                if (empty($scheduleData)) {
                    $scheduleData = [['day' => '', 'start_time' => '', 'end_time' => '', 'room_id' => '']];
                }
                $equipment = old('equipment', $course->equipment ?? []);
                $objectives = old('objectives', $course->objectives ?? []);
            @endphp

            <!-- Form Sections -->
            <div class="bg-white rounded-2xl shadow-lg border border-gray-200">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200 bg-gray-50">
                    <nav class="-mb-px flex space-x-8 px-6 overflow-x-auto">
                        <button type="button" @click="activeTab = 'basic'"
                                :class="{ 'border-rose-500 text-rose-600 bg-white': activeTab === 'basic', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'basic' }"
                                class="whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200 min-w-0 flex items-center">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Informazioni Base
                        </button>
                        <button type="button" @click="activeTab = 'details'"
                                :class="{ 'border-rose-500 text-rose-600 bg-white': activeTab === 'details', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'details' }"
                                class="whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200 min-w-0 flex items-center">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Dettagli
                        </button>
                        <button type="button" @click="activeTab = 'students'"
                                :class="{ 'border-rose-500 text-rose-600 bg-white': activeTab === 'students', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'students' }"
                                class="whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200 min-w-0 flex items-center">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                            @php
                                $studentsCount = $course->enrollments()->where('status', 'active')->count();
                            @endphp
                            Studenti ({{ $studentsCount }})
                        </button>
                        <button type="button" @click="activeTab = 'schedule'"
                                :class="{ 'border-rose-500 text-rose-600 bg-white': activeTab === 'schedule', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'schedule' }"
                                class="whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200 min-w-0 flex items-center">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Orari
                        </button>
                        <button type="button" @click="activeTab = 'pricing'"
                                :class="{ 'border-rose-500 text-rose-600 bg-white': activeTab === 'pricing', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'pricing' }"
                                class="whitespace-nowrap py-4 px-3 border-b-2 font-medium text-sm rounded-t-lg transition-all duration-200 min-w-0 flex items-center">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Prezzi
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6 sm:p-8">
                    <!-- Basic Information Tab -->
                    <div :class="{ 'hidden': activeTab !== 'basic' }" class="space-y-6">
                        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
                            <!-- Image Upload -->
                            <div class="xl:col-span-1">
                                <div class="text-center">
                                    <div class="mb-4">
                                        <img x-show="imagePreview" x-bind:src="imagePreview" class="mx-auto h-40 w-full rounded-2xl object-cover shadow-lg border border-gray-200">
                                        <div x-show="!imagePreview" class="mx-auto h-40 w-full bg-gradient-to-r from-rose-100 to-purple-100 rounded-2xl flex items-center justify-center border-2 border-dashed border-rose-300 hover:border-rose-400 transition-colors">
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
                            <div class="xl:col-span-2 space-y-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Nome Corso *</label>
                                        <input type="text" name="name" value="{{ old('name', $course->name) }}"
                                               placeholder="es. Danza Classica Intermedio" required
                                               class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Codice Corso</label>
                                        <input type="text" name="code" value="{{ $course->code ?? '' }}" readonly
                                               class="w-full px-4 py-3 border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo di Danza *</label>
                                        <select name="dance_type" class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
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
                                        <select name="level" class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                            <option value="">Seleziona livello</option>
                                            <option value="beginner" {{ old('level', $course->level ?? '') === 'beginner' ? 'selected' : '' }}>Principiante</option>
                                            <option value="intermediate" {{ old('level', $course->level ?? '') === 'intermediate' ? 'selected' : '' }}>Intermedio</option>
                                            <option value="advanced" {{ old('level', $course->level ?? '') === 'advanced' ? 'selected' : '' }}>Avanzato</option>
                                            <option value="professional" {{ old('level', $course->level ?? '') === 'professional' ? 'selected' : '' }}>Professionale</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                                        <select name="status" class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
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
                                               class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Età Massima</label>
                                        <input type="number" name="max_age" min="3" max="99" value="{{ old('max_age', $course->max_age ?? '') }}"
                                               class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Posti Totali *</label>
                                        <input type="number" name="max_students" min="1" max="100" value="{{ old('max_students', $course->max_students ?? '') }}" required
                                               class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                        <p class="mt-1 text-xs text-gray-500">Attualmente iscritti: {{ $course->enrollments()->where('status', 'active')->count() }} studenti</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Istruttore</label>
                                        <select name="instructor_id" class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
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
                                               class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                        @error('start_date')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Fine</label>
                                        <input type="date" name="end_date"
                                               value="{{ old('end_date', $course->end_date ? $course->end_date->format('Y-m-d') : '') }}"
                                               class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
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
                                              class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors resize-vertical">{{ old('short_description', $course->short_description ?? '') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrizione Completa</label>
                                    <textarea name="description" rows="6"
                                              class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors resize-vertical">{{ old('description', $course->description ?? '') }}</textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisiti</label>
                                    <textarea name="prerequisites" rows="4"
                                              class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors resize-vertical">{{ old('prerequisites', $course->prerequisites ?? '') }}</textarea>
                                </div>
                            </div>

                            <!-- Requirements & Equipment -->
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Abbigliamento Richiesto</label>
                                    <div class="space-y-2">
                                        @if(is_array($equipment) && count($equipment) > 0)
                                            @foreach($equipment as $item)
                                                <input type="text" name="equipment[]" value="{{ $item }}"
                                                       class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                            @endforeach
                                        @else
                                            <input type="text" name="equipment[]" value=""
                                                   class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors" placeholder="Inserisci abbigliamento richiesto">
                                        @endif
                                        <button type="button" onclick="addEquipmentField()" class="text-sm text-rose-600 hover:text-rose-800">+ Aggiungi elemento</button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Obiettivi del Corso</label>
                                    <div class="space-y-2">
                                        @if(is_array($objectives) && count($objectives) > 0)
                                            @foreach($objectives as $objective)
                                                <input type="text" name="objectives[]" value="{{ $objective }}"
                                                       class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                            @endforeach
                                        @else
                                            <input type="text" name="objectives[]" value=""
                                                   class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors" placeholder="Inserisci obiettivo del corso">
                                        @endif
                                        <button type="button" onclick="addObjectiveField()" class="text-sm text-rose-600 hover:text-rose-800">+ Aggiungi obiettivo</button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Note Aggiuntive</label>
                                    <textarea name="notes" rows="4"
                                              class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors resize-vertical">{{ old('notes', $course->notes ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Students Tab -->
                    <div :class="{ 'hidden': activeTab !== 'students' }" class="space-y-6">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">Gestione Studenti Iscritti</h3>
                            <div class="flex space-x-3">
                                <button type="button"
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

                        <!-- Students List -->
                        @php
                            $activeEnrollments = $course->enrollments()->with('user')->where('status', 'active')->get();
                        @endphp
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse ($activeEnrollments as $enrollment)
                                <div class="bg-gray-50 rounded-lg p-4 border">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="font-medium text-gray-900">{{ $enrollment->user->name ?? 'N/A' }}</h4>
                                            <p class="text-sm text-gray-600">{{ $enrollment->user->email ?? 'N/A' }}</p>
                                            <p class="text-xs text-gray-500">Iscritto: {{ $enrollment->created_at->format('d/m/Y') }}</p>
                                        </div>
                                        <div class="flex space-x-1">
                                            <button type="button" class="text-blue-600 hover:text-blue-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                                </svg>
                                            </button>
                                            <button type="button" class="text-red-600 hover:text-red-800">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-span-full text-center py-8">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuno studente iscritto</h3>
                                    <p class="mt-1 text-sm text-gray-500">Inizia aggiungendo il primo studente al corso.</p>
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
                                    <h4 class="text-sm font-medium text-orange-900">Attenzione agli Orari</h4>
                                    <p class="text-sm text-orange-800">
                                        Le modifiche agli orari possono influenzare studenti già iscritti. Assicurati di comunicarlo con almeno 7 giorni di anticipo.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div id="schedule-container" class="space-y-4">
                            @if(!empty($scheduleData))
                                @foreach($scheduleData as $index => $slot)
                                    <div class="bg-gray-50 rounded-lg p-4 border">
                                        <div class="flex items-center justify-between mb-4">
                                            <h4 class="font-medium text-gray-900">
                                                {{ $slot['day'] ? $slot['day'] . ' - ' : '' }}Orario {{ $index + 1 }}
                                            </h4>
                                            @if($index > 0)
                                                <button type="button" onclick="removeScheduleSlot(this)" class="text-red-600 hover:text-red-800">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Giorno</label>
                                                <select name="schedule_slots[{{ $index }}][day]" class="w-full px-3 py-2 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                                    <option value="">Seleziona giorno</option>
                                                    @foreach(['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'] as $day)
                                                        <option value="{{ $day }}" {{ ($slot['day'] ?? '') == $day ? 'selected' : '' }}>{{ $day }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Ora Inizio</label>
                                                <input type="time" name="schedule_slots[{{ $index }}][start_time]"
                                                       value="{{ $slot['start_time'] ?? '' }}"
                                                       class="w-full px-3 py-2 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors"
                                                       onchange="calculateDuration(this)">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Ora Fine</label>
                                                <input type="time" name="schedule_slots[{{ $index }}][end_time]"
                                                       value="{{ $slot['end_time'] ?? '' }}"
                                                       class="w-full px-3 py-2 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors"
                                                       onchange="calculateDuration(this)">
                                            </div>

                                            <div>
                                                <label class="block text-sm font-medium text-gray-700 mb-1">Durata</label>
                                                <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-600">
                                                    <span class="duration-display">{{ $slot['duration'] ?? '--' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Sala</label>
                                            <select name="schedule_slots[{{ $index }}][room_id]" class="w-full px-3 py-2 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                                <option value="">Seleziona una sala</option>
                                                @foreach($availableRooms as $roomIndex => $roomName)
                                                    <option value="{{ $roomIndex }}" {{ ($slot['room_id'] ?? '') == $roomIndex ? 'selected' : '' }}>
                                                        {{ $roomName }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <button type="button" id="add-schedule-slot" class="w-full px-4 py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-rose-300 hover:text-rose-600 transition-colors" onclick="addScheduleSlot()">
                            <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            Aggiungi Nuovo Orario
                        </button>
                    </div>

                    <!-- Pricing Tab -->
                    <div :class="{ 'hidden': activeTab !== 'pricing' }" class="space-y-6">
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <div>
                                    <h4 class="text-sm font-medium text-red-900">Attenzione ai Prezzi</h4>
                                    <p class="text-sm text-red-800">
                                        Le modifiche ai prezzi potrebbero influenzare gli studenti con pagamenti ricorrenti attivi.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prezzo Mensile € *</label>
                                    <input type="number" name="monthly_price" step="0.01" min="0"
                                           value="{{ old('monthly_price', $course->monthly_price ?? '') }}"
                                           class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prezzo Trimestrale €</label>
                                    <input type="number" name="quarterly_price" step="0.01" min="0"
                                           value="{{ old('quarterly_price', $course->quarterly_price ?? '') }}"
                                           class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prezzo Annuale €</label>
                                    <input type="number" name="yearly_price" step="0.01" min="0"
                                           value="{{ old('yearly_price', $course->yearly_price ?? '') }}"
                                           class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                </div>
                            </div>

                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Quota di Iscrizione €</label>
                                    <input type="number" name="enrollment_fee" step="0.01" min="0"
                                           value="{{ old('enrollment_fee', $course->enrollment_fee ?? '') }}"
                                           class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Lezione Singola €</label>
                                    <input type="number" name="single_lesson_price" step="0.01" min="0"
                                           value="{{ old('single_lesson_price', $course->single_lesson_price ?? '') }}"
                                           class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Pacchetto 10 Lezioni €</label>
                                    <input type="number" name="package_10_price" step="0.01" min="0"
                                           value="{{ old('package_10_price', $course->package_10_price ?? '') }}"
                                           class="w-full px-4 py-3 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-between pt-6 border-t border-gray-200 gap-4">
                    <div class="flex space-x-3 order-2 sm:order-1">
                        <a href="{{ route('admin.courses.index') }}"
                           class="px-6 py-3 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Annulla
                        </a>
                    </div>
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 order-1 sm:order-2 w-full sm:w-auto">
                        <button type="submit" name="submit_action" value="draft"
                                class="px-6 py-3 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                            Salva come Bozza
                        </button>
                        <button type="submit" name="submit_action" value="update"
                                class="px-8 py-3 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Salva Modifiche
                        </button>
                    </div>
                </div>
            </div>
        </form>
        </div>
    </div>

    {{-- Include CSS and JS --}}
    @push('styles')
        @vite('resources/css/admin/courses/course-edit.css')
    @endpush

    @push('scripts')
        @vite('resources/js/admin/courses/course-edit.js')
        <script>
            // Pass data to JavaScript modules
            window.availableRooms = @json(collect($availableRooms)->mapWithKeys(function($room, $index) { return [$index => $room]; })->toArray());
            window.scheduleSlotIndex = {{ count($scheduleData ?? []) }};

            // Legacy functions for onclick handlers (will be deprecated)
            function deleteCourse() {
                if (confirm('Sei sicuro di voler eliminare questo corso? Questa azione non può essere annullata.')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.courses.destroy", $course) }}';
                    form.innerHTML = '@csrf @method("DELETE")';
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            function removeMedia(mediaId) {
                if (confirm('Sei sicuro di voler rimuovere questo media?')) {
                    // Implementation for media removal
                    console.log('Remove media:', mediaId);
                }
            }

            function addEquipmentField() {
                const container = document.querySelector('div:has(> input[name="equipment[]"])').parentElement;
                if (container) {
                    const newInput = document.createElement('input');
                    newInput.type = 'text';
                    newInput.name = 'equipment[]';
                    newInput.value = '';
                    newInput.className = 'w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500';
                    newInput.placeholder = 'Inserisci abbigliamento richiesto';
                    container.insertBefore(newInput, container.lastElementChild);
                }
            }

            function addObjectiveField() {
                const container = document.querySelector('div:has(> input[name="objectives[]"])').parentElement;
                if (container) {
                    const newInput = document.createElement('input');
                    newInput.type = 'text';
                    newInput.name = 'objectives[]';
                    newInput.value = '';
                    newInput.className = 'w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500';
                    newInput.placeholder = 'Inserisci obiettivo del corso';
                    container.insertBefore(newInput, container.lastElementChild);
                }
            }

            function removeScheduleSlot(button) {
                button.closest('.bg-gray-50').remove();
            }

            function addScheduleSlot() {
                const container = document.getElementById('schedule-container');
                const index = window.scheduleSlotIndex++;

                const slotHtml = `
                    <div class="bg-gray-50 rounded-lg p-4 border">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-medium text-gray-900">Orario ${index + 1}</h4>
                            <button type="button" onclick="removeScheduleSlot(this)" class="text-red-600 hover:text-red-800">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Giorno</label>
                                <select name="schedule_slots[${index}][day]" class="w-full px-3 py-2 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
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
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ora Inizio</label>
                                <input type="time" name="schedule_slots[${index}][start_time]" class="w-full px-3 py-2 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors" onchange="calculateDuration(this)">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Ora Fine</label>
                                <input type="time" name="schedule_slots[${index}][end_time]" class="w-full px-3 py-2 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors" onchange="calculateDuration(this)">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Durata</label>
                                <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm text-gray-600">
                                    <span class="duration-display">--</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Sala</label>
                            <select name="schedule_slots[${index}][room_id]" class="w-full px-3 py-2 border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 transition-colors">
                                <option value="">Seleziona una sala</option>
                                ${Object.entries(window.availableRooms || {}).map(([key, value]) => `<option value="${key}">${value}</option>`).join('')}
                            </select>
                        </div>
                    </div>
                `;

                container.insertAdjacentHTML('beforeend', slotHtml);
            }

            function calculateDuration(input) {
                const slot = input.closest('.bg-gray-50');
                const startTime = slot.querySelector('input[name*="[start_time]"]').value;
                const endTime = slot.querySelector('input[name*="[end_time]"]').value;
                const durationDisplay = slot.querySelector('.duration-display');

                if (startTime && endTime) {
                    const start = new Date(`2000-01-01T${startTime}:00`);
                    const end = new Date(`2000-01-01T${endTime}:00`);
                    const diffMs = end - start;

                    if (diffMs > 0) {
                        const hours = Math.floor(diffMs / (1000 * 60 * 60));
                        const minutes = Math.floor((diffMs % (1000 * 60 * 60)) / (1000 * 60));
                        durationDisplay.textContent = `${hours}h ${minutes}m`;
                    } else {
                        durationDisplay.textContent = '--';
                    }
                } else {
                    durationDisplay.textContent = '--';
                }
            }
        </script>
    @endpush
</x-app-layout>
