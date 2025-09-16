@extends('layouts.app')
    @section('content')
        <div class="flex items-center justify-between">
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
    @endsection

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.courses.index') }}" class="text-gray-500 hover:text-gray-700">Corsi</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Nuovo Corso</li>
    @endsection

    <div class="space-y-6">
        <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data" 
              x-data="{ activeTab: 'basic', imagePreview: null, duration: 8, lessons_per_week: 2, totalLessons: 32 }"
              x-init="$watch('duration', value => totalLessons = value * 4 * lessons_per_week);
                      $watch('lessons_per_week', value => totalLessons = duration * 4 * value)">
            @csrf

            <!-- Progress Steps -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Progresso Creazione</h3>
                    <span class="text-sm text-gray-500">4 sezioni</span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div :class="activeTab === 'basic' ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-600'" 
                             class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">1</div>
                        <span class="ml-2 text-sm" :class="activeTab === 'basic' ? 'text-rose-600 font-medium' : 'text-gray-500'">Info Base</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded-full">
                        <div :class="['details', 'schedule', 'pricing'].indexOf(activeTab) >= 0 ? 'w-1/3' : 'w-0'" 
                             class="h-1 bg-rose-600 rounded-full transition-all duration-300"></div>
                    </div>
                    <div class="flex items-center">
                        <div :class="['details', 'schedule', 'pricing'].indexOf(activeTab) >= 0 ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-600'" 
                             class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">2</div>
                        <span class="ml-2 text-sm" :class="['details', 'schedule', 'pricing'].indexOf(activeTab) >= 0 ? 'text-rose-600 font-medium' : 'text-gray-500'">Dettagli</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded-full">
                        <div :class="['schedule', 'pricing'].indexOf(activeTab) >= 0 ? 'w-2/3' : 'w-0'" 
                             class="h-1 bg-rose-600 rounded-full transition-all duration-300"></div>
                    </div>
                    <div class="flex items-center">
                        <div :class="['schedule', 'pricing'].indexOf(activeTab) >= 0 ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-600'" 
                             class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">3</div>
                        <span class="ml-2 text-sm" :class="['schedule', 'pricing'].indexOf(activeTab) >= 0 ? 'text-rose-600 font-medium' : 'text-gray-500'">Orari</span>
                    </div>
                    <div class="flex-1 h-1 bg-gray-200 rounded-full">
                        <div :class="activeTab === 'pricing' ? 'w-full' : 'w-0'" 
                             class="h-1 bg-rose-600 rounded-full transition-all duration-300"></div>
                    </div>
                    <div class="flex items-center">
                        <div :class="activeTab === 'pricing' ? 'bg-rose-600 text-white' : 'bg-gray-200 text-gray-600'" 
                             class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium">4</div>
                        <span class="ml-2 text-sm" :class="activeTab === 'pricing' ? 'text-rose-600 font-medium' : 'text-gray-500'">Prezzi</span>
                    </div>
                </div>
            </div>

            <!-- Form Content -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
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
                            Dettagli e Contenuti
                        </button>
                        <button type="button" @click="activeTab = 'schedule'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'schedule', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'schedule' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Orari e Programma
                        </button>
                        <button type="button" @click="activeTab = 'pricing'" 
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'pricing', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'pricing' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            Prezzi e Politiche
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
                                        <div x-show="!imagePreview" class="mx-auto h-40 w-full bg-gradient-to-r from-rose-100 to-purple-100 rounded-2xl flex items-center justify-center border-2 border-dashed border-rose-300">
                                            <div class="text-center">
                                                <svg class="mx-auto h-12 w-12 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <p class="mt-2 text-sm text-rose-600 font-medium">Immagine Corso</p>
                                            </div>
                                        </div>
                                        <img x-show="imagePreview" x-bind:src="imagePreview" class="mx-auto h-40 w-full rounded-2xl object-cover">
                                    </div>
                                    <div>
                                        <label class="cursor-pointer inline-flex items-center px-4 py-2 bg-rose-50 text-rose-600 text-sm font-medium rounded-lg hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 border border-rose-200">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                                            </svg>
                                            Carica Immagine
                                            <input type="file" name="image" accept="image/*" class="sr-only" 
                                                   @change="if ($event.target.files[0]) { 
                                                       const reader = new FileReader();
                                                       reader.onload = (e) => imagePreview = e.target.result;
                                                       reader.readAsDataURL($event.target.files[0]);
                                                   }">
                                        </label>
                                        <p class="mt-2 text-xs text-gray-500">
                                            PNG, JPG fino a 5MB<br>
                                            Dimensioni consigliate: 400x300px
                                        </p>
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
                                            placeholder="es. Danza Classica Intermedio"
                                            required />
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Codice Corso</label>
                                        <input type="text" name="code" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="Auto-generato se vuoto">
                                        <p class="mt-1 text-xs text-gray-500">Lasciare vuoto per generazione automatica</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo di Danza *</label>
                                        <select name="dance_type" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="">Seleziona tipo</option>
                                            <option value="classica">Danza Classica</option>
                                            <option value="moderna">Danza Moderna</option>
                                            <option value="contemporanea">Danza Contemporanea</option>
                                            <option value="hip_hop">Hip Hop</option>
                                            <option value="jazz">Jazz Dance</option>
                                            <option value="latino">Danze Latine</option>
                                            <option value="bollywood">Bollywood</option>
                                            <option value="altro">Altro</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Livello *</label>
                                        <select name="level" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="">Seleziona livello</option>
                                            <option value="principiante">Principiante</option>
                                            <option value="base">Base</option>
                                            <option value="intermedio">Intermedio</option>
                                            <option value="avanzato">Avanzato</option>
                                            <option value="professionale">Professionale</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                                        <select name="status" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="draft">Bozza</option>
                                            <option value="published" selected>Pubblicato</option>
                                            <option value="archived">Archiviato</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Età Minima</label>
                                        <input type="number" name="min_age" min="3" max="99" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. 12">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Età Massima</label>
                                        <input type="number" name="max_age" min="3" max="99" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. 16">
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Posti Disponibili *</label>
                                        <input type="number" name="max_students" min="1" max="100" required
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. 25">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Istruttore</label>
                                        <select name="instructor_id" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="">Seleziona istruttore</option>
                                            <option value="1">Martina Rossi</option>
                                            <option value="2">Marco Bianchi</option>
                                            <option value="3">Elena Verdi</option>
                                            <option value="4">Giuseppe Romano</option>
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
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                              placeholder="Una breve descrizione del corso (massimo 200 caratteri)"></textarea>
                                    <p class="mt-1 text-xs text-gray-500">Utilizzata nelle anteprime e nei risultati di ricerca</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrizione Completa</label>
                                    <textarea name="description" rows="6" 
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                              placeholder="Descrizione dettagliata del corso, obiettivi, metodologia..."></textarea>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Prerequisiti</label>
                                    <textarea name="prerequisites" rows="4" 
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                              placeholder="Competenze o esperienze richieste per partecipare al corso"></textarea>
                                </div>
                            </div>

                            <!-- Requirements & Equipment -->
                            <div class="space-y-6">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Abbigliamento Richiesto</label>
                                    <div class="space-y-2">
                                        <input type="text" name="equipment[]" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. Body nero">
                                        <input type="text" name="equipment[]" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. Collant rosa">
                                        <input type="text" name="equipment[]" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. Scarpette da danza">
                                        <button type="button" class="mt-2 text-sm text-rose-600 hover:text-rose-700">
                                            + Aggiungi altro
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Obiettivi del Corso</label>
                                    <div class="space-y-2">
                                        <input type="text" name="objectives[]" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. Migliorare la tecnica di base">
                                        <input type="text" name="objectives[]" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. Sviluppare coordinazione ed equilibrio">
                                        <input type="text" name="objectives[]" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. Imparare variazioni coreografiche">
                                        <button type="button" class="mt-2 text-sm text-rose-600 hover:text-rose-700">
                                            + Aggiungi obiettivo
                                        </button>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Note Aggiuntive</label>
                                    <textarea name="notes" rows="4" 
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                              placeholder="Informazioni aggiuntive, regole speciali, comunicazioni importanti..."></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schedule Tab -->
                    <div x-show="activeTab === 'schedule'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Timing -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                    Programma e Durata
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Inizio *</label>
                                        <input type="date" name="start_date" required
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Fine</label>
                                        <input type="date" name="end_date" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                        <p class="mt-1 text-xs text-gray-500">Lasciare vuoto per corso continuativo</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Durata (mesi)</label>
                                        <input type="number" x-model="duration" name="duration_months" min="1" max="24" 
                                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                               placeholder="es. 8">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Lezioni/Settimana</label>
                                        <select x-model="lessons_per_week" name="lessons_per_week" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            <option value="1">1 lezione</option>
                                            <option value="2" selected>2 lezioni</option>
                                            <option value="3">3 lezioni</option>
                                            <option value="4">4 lezioni</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Durata Singola Lezione</label>
                                    <select name="lesson_duration" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                        <option value="60">60 minuti</option>
                                        <option value="75">75 minuti</option>
                                        <option value="90" selected>90 minuti</option>
                                        <option value="120">120 minuti</option>
                                    </select>
                                </div>

                                <div class="p-4 bg-rose-50 rounded-lg border border-rose-200">
                                    <h4 class="font-medium text-rose-900 mb-2">Riepilogo</h4>
                                    <div class="text-sm text-rose-800 space-y-1">
                                        <p>Durata totale: <span x-text="duration"></span> mesi</p>
                                        <p>Lezioni totali: <span x-text="totalLessons"></span></p>
                                        <p>Frequenza: <span x-text="lessons_per_week"></span> lezioni/settimana</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Weekly Schedule -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                    Orario Settimanale
                                </h3>

                                <div class="space-y-4">
                                    <!-- First Lesson -->
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="font-medium text-gray-900">Lezione 1</h4>
                                            <button type="button" class="text-red-600 hover:text-red-700 text-sm">Rimuovi</button>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Giorno</label>
                                                <select name="schedule[0][day]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                                    <option value="">Seleziona giorno</option>
                                                    <option value="monday">Lunedì</option>
                                                    <option value="tuesday">Martedì</option>
                                                    <option value="wednesday">Mercoledì</option>
                                                    <option value="thursday">Giovedì</option>
                                                    <option value="friday">Venerdì</option>
                                                    <option value="saturday">Sabato</option>
                                                    <option value="sunday">Domenica</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Orario Inizio</label>
                                                <input type="time" name="schedule[0][start_time]" 
                                                       class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Orario Fine</label>
                                                <input type="time" name="schedule[0][end_time]" 
                                                       class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Sala</label>
                                            <select name="schedule[0][room]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                                <option value="">Seleziona sala</option>
                                                <option value="sala_a">Sala A</option>
                                                <option value="sala_b">Sala B</option>
                                                <option value="sala_c">Sala C</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Second Lesson -->
                                    <div class="p-4 bg-gray-50 rounded-lg border border-gray-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <h4 class="font-medium text-gray-900">Lezione 2</h4>
                                            <button type="button" class="text-red-600 hover:text-red-700 text-sm">Rimuovi</button>
                                        </div>
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Giorno</label>
                                                <select name="schedule[1][day]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                                    <option value="">Seleziona giorno</option>
                                                    <option value="monday">Lunedì</option>
                                                    <option value="tuesday">Martedì</option>
                                                    <option value="wednesday">Mercoledì</option>
                                                    <option value="thursday">Giovedì</option>
                                                    <option value="friday">Venerdì</option>
                                                    <option value="saturday">Sabato</option>
                                                    <option value="sunday">Domenica</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Orario Inizio</label>
                                                <input type="time" name="schedule[1][start_time]" 
                                                       class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-gray-600 mb-1">Orario Fine</label>
                                                <input type="time" name="schedule[1][end_time]" 
                                                       class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <label class="block text-xs font-medium text-gray-600 mb-1">Sala</label>
                                            <select name="schedule[1][room]" class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                                <option value="">Seleziona sala</option>
                                                <option value="sala_a">Sala A</option>
                                                <option value="sala_b">Sala B</option>
                                                <option value="sala_c">Sala C</option>
                                            </select>
                                        </div>
                                    </div>

                                    <button type="button" class="w-full p-3 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-rose-300 hover:text-rose-600 transition-colors">
                                        <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                        Aggiungi Lezione
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pricing Tab -->
                    <div x-show="activeTab === 'pricing'" class="space-y-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                            <!-- Pricing Structure -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                    Struttura Prezzi
                                </h3>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-form-input 
                                            label="Prezzo Mensile (€) *"
                                            name="monthly_price"
                                            type="number"
                                            step="0.01"
                                            placeholder="85.00"
                                            required />
                                    </div>
                                    <div>
                                        <x-form-input 
                                            label="Quota Iscrizione (€)"
                                            name="enrollment_fee"
                                            type="number"
                                            step="0.01"
                                            placeholder="25.00" />
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <x-form-input 
                                            label="Prezzo Lezione Singola (€)"
                                            name="single_lesson_price"
                                            type="number"
                                            step="0.01"
                                            placeholder="15.00" />
                                    </div>
                                    <div>
                                        <x-form-input 
                                            label="Prezzo Prova Gratuita (€)"
                                            name="trial_price"
                                            type="number"
                                            step="0.01"
                                            value="0.00"
                                            placeholder="0.00" />
                                    </div>
                                </div>

                                <!-- Discounts -->
                                <div>
                                    <h4 class="font-medium text-gray-900 mb-3">Sconti e Promozioni</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="family_discount" id="family_discount" 
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                            <label for="family_discount" class="ml-2 text-sm text-gray-900">
                                                Sconto famiglia (secondo figlio -10%)
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" name="student_discount" id="student_discount" 
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                            <label for="student_discount" class="ml-2 text-sm text-gray-900">
                                                Sconto studenti (-15% con tessera)
                                            </label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" name="early_bird" id="early_bird" 
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                            <label for="early_bird" class="ml-2 text-sm text-gray-900">
                                                Early bird (-20% primo mese se iscrizione entro 15 giorni)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Policies -->
                            <div class="space-y-6">
                                <h3 class="text-lg font-semibold text-gray-900 border-b border-gray-200 pb-2">
                                    Politiche del Corso
                                </h3>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Politica di Cancellazione</label>
                                    <select name="cancellation_policy" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                        <option value="flexible">Flessibile - Cancellazione fino a 24h prima</option>
                                        <option value="moderate">Moderata - Cancellazione fino a 48h prima</option>
                                        <option value="strict">Rigida - Nessuna cancellazione</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Politica Recuperi</label>
                                    <select name="makeup_policy" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                        <option value="allowed">Recuperi consentiti entro il mese</option>
                                        <option value="limited">Massimo 2 recuperi per trimestre</option>
                                        <option value="no_makeup">Nessun recupero consentito</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Modalità di Pagamento</label>
                                    <div class="space-y-2">
                                        <div class="flex items-center">
                                            <input type="checkbox" name="payment_methods[]" value="monthly" id="monthly_payment" checked
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                            <label for="monthly_payment" class="ml-2 text-sm text-gray-900">Pagamento mensile</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" name="payment_methods[]" value="quarterly" id="quarterly_payment" 
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                            <label for="quarterly_payment" class="ml-2 text-sm text-gray-900">Pagamento trimestrale (-5%)</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input type="checkbox" name="payment_methods[]" value="full" id="full_payment" 
                                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                            <label for="full_payment" class="ml-2 text-sm text-gray-900">Pagamento annuale (-10%)</label>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Note sui Pagamenti</label>
                                    <textarea name="payment_notes" rows="4" 
                                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                              placeholder="Informazioni aggiuntive sui pagamenti, scadenze, modalità..."></textarea>
                                </div>

                                <!-- Summary -->
                                <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                                    <h4 class="font-medium text-green-900 mb-2">Riepilogo Economico</h4>
                                    <div class="text-sm text-green-800 space-y-1">
                                        <p>Ricavo mensile stimato (25 studenti): <strong>€2,125</strong></p>
                                        <p>Ricavo annuale stimato: <strong>€17,000</strong></p>
                                        <p>Prezzo per lezione: <strong>€10.62</strong></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center space-x-4">
                    <button type="button" @click="if (activeTab === 'details') activeTab = 'basic'; 
                                                  else if (activeTab === 'schedule') activeTab = 'details';
                                                  else if (activeTab === 'pricing') activeTab = 'schedule';"
                            :disabled="activeTab === 'basic'"
                            :class="activeTab === 'basic' ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-200'"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Indietro
                    </button>
                    
                    <button type="button" @click="if (activeTab === 'basic') activeTab = 'details'; 
                                                  else if (activeTab === 'details') activeTab = 'schedule';
                                                  else if (activeTab === 'schedule') activeTab = 'pricing';"
                            x-show="activeTab !== 'pricing'"
                            class="px-4 py-2 text-sm font-medium text-rose-600 bg-rose-50 border border-rose-200 rounded-lg hover:bg-rose-100 focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all duration-200">
                        Avanti
                        <svg class="w-4 h-4 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </button>
                </div>

                <div class="flex items-center space-x-3">
                    <button type="submit" name="action" value="draft" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
                        Salva come Bozza
                    </button>
                    <button type="submit" name="action" value="publish" 
                            class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Pubblica Corso
                    </button>
                </div>
            </div>
        </form>
    </div>
@endsection