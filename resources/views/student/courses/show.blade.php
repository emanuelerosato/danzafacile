<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettagli Corso
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Tutte le informazioni sul corso
                </p>
            </div>
            <div class="flex items-center space-x-3">
                @if (!$isEnrolled ?? false)
                    <button @click="$dispatch('open-modal', 'enroll-course')" 
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Iscriviti al Corso
                    </button>
                @else
                    <button disabled 
                            class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg cursor-not-allowed">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Già Iscritto
                    </button>
                @endif
                <button class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                    Salva nei Preferiti
                </button>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('student.courses.index') }}" class="text-gray-500 hover:text-gray-700">Corsi Disponibili</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Danza Classica Intermedio</li>
    </x-slot>

    <div class="space-y-6">
        <!-- Course Hero Section -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <!-- Hero Background -->
            <div class="relative h-64 bg-gradient-to-r from-rose-400 via-purple-500 to-violet-600">
                <div class="absolute inset-0 bg-black/30"></div>
                <div class="absolute inset-0 bg-[url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="white" opacity="0.1"/><circle cx="75" cy="75" r="1" fill="white" opacity="0.1"/><circle cx="50" cy="10" r="0.5" fill="white" opacity="0.05"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>')] opacity-20"></div>
                
                <div class="absolute top-6 left-6">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white bg-white/20 backdrop-blur-sm">
                        Intermedio
                    </span>
                </div>
                
                <div class="absolute top-6 right-6 flex items-center space-x-2">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium text-white bg-blue-500/80 backdrop-blur-sm">
                        2 posti disponibili
                    </span>
                    <div class="flex items-center text-white">
                        @for ($i = 1; $i <= 5; $i++)
                            <svg class="w-4 h-4 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                        <span class="ml-1 text-sm">4.8</span>
                    </div>
                </div>
                
                <div class="absolute bottom-6 left-6 right-6">
                    <h1 class="text-3xl font-bold text-white mb-2">Danza Classica Intermedio</h1>
                    <p class="text-white/90 mb-4">Perfezionamento della tecnica classica con focus su adagio e allegro per studenti con esperienza di base</p>
                    <div class="flex items-center space-x-6 text-white/80">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Prof. Martina Rossi
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Mar/Gio 16:00-17:30
                        </div>
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            €85/mese
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Course Info Tabs -->
        <div x-data="{ activeTab: 'overview' }" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <button @click="activeTab = 'overview'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'overview', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'overview' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Panoramica
                    </button>
                    <button @click="activeTab = 'instructor'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'instructor', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'instructor' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Istruttore
                    </button>
                    <button @click="activeTab = 'program'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'program', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'program' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Programma
                    </button>
                    <button @click="activeTab = 'reviews'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'reviews', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'reviews' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Recensioni (156)
                    </button>
                    <button @click="activeTab = 'gallery'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'gallery', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'gallery' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Galleria
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Course Details -->
                        <div class="lg:col-span-2 space-y-6">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900 mb-3">Descrizione del Corso</h3>
                                <div class="prose prose-gray max-w-none">
                                    <p class="text-gray-700">
                                        Il corso di Danza Classica Intermedio è rivolto a studenti che hanno già acquisito le basi della danza classica 
                                        e desiderano perfezionare la tecnica e l'espressività. Durante le lezioni verranno approfonditi i movimenti fondamentali, 
                                        la postura, l'equilibrio e la coordinazione.
                                    </p>
                                    <p class="text-gray-700 mt-3">
                                        Il programma include esercizi alla sbarra, al centro, salti e variazioni coreografiche. Gli studenti impareranno 
                                        anche i principi dell'interpretazione artistica e della musicalità, elementi essenziali per una formazione completa.
                                    </p>
                                    <p class="text-gray-700 mt-3">
                                        Al termine del corso, gli allievi avranno consolidato la tecnica intermedia e saranno pronti per affrontare 
                                        coreografie più complesse e partecipare ai saggi di fine anno.
                                    </p>
                                </div>
                            </div>

                            <!-- Prerequisites & Requirements -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-3">Prerequisiti</h4>
                                    <ul class="space-y-2 text-sm text-gray-600">
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Conoscenza delle 5 posizioni di base
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Esercizi base alla sbarra
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Almeno 1 anno di esperienza
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Età consigliata: 12-16 anni
                                        </li>
                                    </ul>
                                </div>
                                
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-3">Abbigliamento Richiesto</h4>
                                    <ul class="space-y-2 text-sm text-gray-600">
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-rose-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Body nero o colori scuri
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-rose-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Collant rosa o neri
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-rose-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Scarpette da danza classica
                                        </li>
                                        <li class="flex items-center">
                                            <svg class="w-4 h-4 text-rose-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                            </svg>
                                            Capelli raccolti
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <!-- Course Objectives -->
                            <div>
                                <h4 class="font-semibold text-gray-900 mb-3">Obiettivi del Corso</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="flex items-start space-x-3 p-3 bg-rose-50 rounded-lg">
                                        <div class="w-8 h-8 bg-rose-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-gray-900">Miglioramento Tecnico</h5>
                                            <p class="text-sm text-gray-600">Perfezionamento della tecnica di base e introduzione di elementi più complessi</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start space-x-3 p-3 bg-purple-50 rounded-lg">
                                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12 3v9M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-gray-900">Musicalità</h5>
                                            <p class="text-sm text-gray-600">Sviluppo del senso musicale e dell'interpretazione artistica</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-gray-900">Coordinazione</h5>
                                            <p class="text-sm text-gray-600">Miglioramento dell'equilibrio, coordinazione e controllo del movimento</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3"/>
                                            </svg>
                                        </div>
                                        <div>
                                            <h5 class="font-medium text-gray-900">Espressività</h5>
                                            <p class="text-sm text-gray-600">Sviluppo dell'espressività corporea e della presenza scenica</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="lg:col-span-1 space-y-6">
                            <!-- Course Quick Info -->
                            <div class="bg-gradient-to-br from-rose-50 to-purple-50 rounded-xl p-6 border border-rose-200">
                                <h4 class="font-semibold text-gray-900 mb-4">Informazioni Corso</h4>
                                <div class="space-y-3">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Livello:</span>
                                        <span class="font-medium text-gray-900">Intermedio</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Durata:</span>
                                        <span class="font-medium text-gray-900">8 mesi</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Lezioni totali:</span>
                                        <span class="font-medium text-gray-900">64 lezioni</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Durata lezione:</span>
                                        <span class="font-medium text-gray-900">90 minuti</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Sala:</span>
                                        <span class="font-medium text-gray-900">Sala A</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Iscritti:</span>
                                        <span class="font-medium text-gray-900">28/30</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Inizio corso:</span>
                                        <span class="font-medium text-gray-900">15 Settembre</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Schedule -->
                            <div class="bg-white rounded-xl p-6 border border-gray-200">
                                <h4 class="font-semibold text-gray-900 mb-4">Orario Lezioni</h4>
                                <div class="space-y-3">
                                    <div class="flex items-center p-3 bg-rose-50 rounded-lg border border-rose-200">
                                        <div class="w-10 h-10 bg-rose-500 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">
                                            MAR
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">Martedì</p>
                                            <p class="text-sm text-gray-600">16:00 - 17:30</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                                        <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center text-white font-bold text-sm mr-3">
                                            GIO
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">Giovedì</p>
                                            <p class="text-sm text-gray-600">16:00 - 17:30</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pricing Options -->
                            <div class="bg-white rounded-xl p-6 border border-gray-200">
                                <h4 class="font-semibold text-gray-900 mb-4">Opzioni di Pagamento</h4>
                                <div class="space-y-3">
                                    <div class="p-3 border border-gray-200 rounded-lg">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-medium text-gray-900">Mensile</span>
                                            <span class="text-lg font-bold text-gray-900">€85</span>
                                        </div>
                                        <p class="text-sm text-gray-500">Pagamento mensile</p>
                                    </div>
                                    <div class="p-3 border border-green-200 rounded-lg bg-green-50">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-medium text-gray-900">Trimestrale</span>
                                            <div class="text-right">
                                                <span class="text-lg font-bold text-green-700">€242</span>
                                                <span class="ml-1 text-xs text-green-600 bg-green-200 px-2 py-1 rounded-full">-5%</span>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500">€80.67 al mese</p>
                                    </div>
                                    <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="font-medium text-gray-900">Annuale</span>
                                            <div class="text-right">
                                                <span class="text-lg font-bold text-blue-700">€918</span>
                                                <span class="ml-1 text-xs text-blue-600 bg-blue-200 px-2 py-1 rounded-full">-10%</span>
                                            </div>
                                        </div>
                                        <p class="text-sm text-gray-500">€76.50 al mese</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Info -->
                            <div class="bg-yellow-50 rounded-xl p-6 border border-yellow-200">
                                <h4 class="font-semibold text-gray-900 mb-3">Informazioni Importanti</h4>
                                <ul class="space-y-2 text-sm text-gray-700">
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-yellow-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Lezione di prova gratuita disponibile
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-yellow-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Partecipazione al saggio di fine anno inclusa
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-yellow-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Possibilità di recupero lezioni perse
                                    </li>
                                    <li class="flex items-start">
                                        <svg class="w-4 h-4 text-yellow-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Spogliatoi e armadietti disponibili
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instructor Tab -->
                <div x-show="activeTab === 'instructor'" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Instructor Profile -->
                        <div class="lg:col-span-1">
                            <div class="bg-gradient-to-br from-rose-50 to-purple-50 rounded-xl p-6 border border-rose-200 text-center">
                                <div class="w-32 h-32 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full mx-auto flex items-center justify-center text-white font-bold text-4xl mb-4">
                                    MR
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Prof.ssa Martina Rossi</h3>
                                <p class="text-gray-600 mb-4">Insegnante di Danza Classica</p>
                                <div class="flex items-center justify-center mb-4">
                                    @for ($i = 1; $i <= 5; $i++)
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                    <span class="ml-2 text-sm text-gray-600">4.9 (124 recensioni)</span>
                                </div>
                                <button class="w-full px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 transition-colors">
                                    Contatta l'Istruttore
                                </button>
                            </div>
                        </div>

                        <!-- Instructor Details -->
                        <div class="lg:col-span-2 space-y-6">
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-3">Chi è Martina Rossi</h4>
                                <div class="prose prose-gray max-w-none">
                                    <p class="text-gray-700">
                                        Martina Rossi è una ballerina professionista diplomata presso l'Accademia Teatro alla Scala di Milano. 
                                        Con oltre 15 anni di esperienza nell'insegnamento, ha formato centinaia di giovani danzatori, 
                                        molti dei quali hanno continuato la loro carriera in compagnie prestigiose.
                                    </p>
                                    <p class="text-gray-700 mt-3">
                                        La sua metodologia di insegnamento unisce la rigorosa tecnica classica con un approccio moderno e coinvolgente, 
                                        sempre attenta alle esigenze individuali di ogni allievo. È specializzata nel metodo Vaganova e 
                                        nell'insegnamento per giovani dai 10 ai 18 anni.
                                    </p>
                                </div>
                            </div>

                            <!-- Qualifications -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-3">Qualifiche e Formazione</h4>
                                <div class="space-y-3">
                                    <div class="flex items-start p-3 bg-white rounded-lg border border-gray-200">
                                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14v7"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h5 class="font-medium text-gray-900">Diploma in Danza Classica</h5>
                                            <p class="text-sm text-gray-600">Accademia Teatro alla Scala, Milano (2005)</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start p-3 bg-white rounded-lg border border-gray-200">
                                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h5 class="font-medium text-gray-900">Certificazione Metodo Vaganova</h5>
                                            <p class="text-sm text-gray-600">Accademia Vaganova, San Pietroburgo (2008)</p>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-start p-3 bg-white rounded-lg border border-gray-200">
                                        <div class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <h5 class="font-medium text-gray-900">Master in Pedagogia della Danza</h5>
                                            <p class="text-sm text-gray-600">Università Statale di Milano (2012)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Experience -->
                            <div>
                                <h4 class="text-lg font-semibold text-gray-900 mb-3">Esperienza Professionale</h4>
                                <div class="space-y-4">
                                    <div class="border-l-4 border-rose-500 pl-4">
                                        <div class="flex items-center justify-between mb-1">
                                            <h5 class="font-medium text-gray-900">Prima Ballerina</h5>
                                            <span class="text-sm text-gray-500">2005-2010</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Corpo di Ballo del Teatro San Carlo, Napoli</p>
                                        <p class="text-sm text-gray-500 mt-1">Ruoli principali in: Il Lago dei Cigni, Giselle, Don Chisciotte</p>
                                    </div>
                                    
                                    <div class="border-l-4 border-purple-500 pl-4">
                                        <div class="flex items-center justify-between mb-1">
                                            <h5 class="font-medium text-gray-900">Insegnante Senior</h5>
                                            <span class="text-sm text-gray-500">2010-presente</span>
                                        </div>
                                        <p class="text-sm text-gray-600">Scuola di Danza Milano</p>
                                        <p class="text-sm text-gray-500 mt-1">Responsabile del dipartimento di danza classica per livelli intermedio e avanzato</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Program Tab -->
                <div x-show="activeTab === 'program'">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Programma del Corso</h3>
                            <p class="text-gray-600 mb-6">Il corso è strutturato in 8 mesi con una progressione didattica attentamente pianificata</p>
                        </div>

                        <!-- Monthly Program -->
                        <div class="space-y-4">
                            @php
                                $months = [
                                    ['title' => 'Settembre - Ottobre', 'focus' => 'Revisione e consolidamento', 'topics' => [
                                        'Revisione delle posizioni base', 
                                        'Port de bras e coordinazione', 
                                        'Esercizi alla sbarra: pliés, tendus, dégagés',
                                        'Primi salti: échappés sautés, soubresauts'
                                    ]],
                                    ['title' => 'Novembre - Dicembre', 'focus' => 'Sviluppo tecnico', 'topics' => [
                                        'Rond de jambe en l\'air', 
                                        'Développés e extensions', 
                                        'Pirouettes en dehors',
                                        'Assemblés e sissones'
                                    ]],
                                    ['title' => 'Gennaio - Febbraio', 'focus' => 'Adagio e controllo', 'topics' => [
                                        'Adagio al centro', 
                                        'Equilibri e arabesques', 
                                        'Grand battement en cloche',
                                        'Preparazione al grand jeté'
                                    ]],
                                    ['title' => 'Marzo - Aprile', 'focus' => 'Allegro e dinamismo', 'topics' => [
                                        'Salti composti', 
                                        'Échappés battus', 
                                        'Tours chainés',
                                        'Coreografie brevi'
                                    ]]
                                ];
                            @endphp

                            @foreach ($months as $index => $month)
                                <div class="bg-white rounded-xl p-6 border border-gray-200">
                                    <div class="flex items-center mb-4">
                                        <div class="w-10 h-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm mr-4">
                                            {{ $index + 1 }}
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-900">{{ $month['title'] }}</h4>
                                            <p class="text-sm text-gray-600">Focus: {{ $month['focus'] }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach ($month['topics'] as $topic)
                                            <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                                                <svg class="w-4 h-4 text-rose-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span class="text-sm text-gray-700">{{ $topic }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Reviews Tab -->
                <div x-show="activeTab === 'reviews'">
                    <div class="space-y-6">
                        <!-- Reviews Summary -->
                        <div class="bg-gradient-to-br from-rose-50 to-purple-50 rounded-xl p-6 border border-rose-200">
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div class="text-center">
                                    <div class="text-4xl font-bold text-gray-900 mb-1">4.8</div>
                                    <div class="flex items-center justify-center mb-2">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <p class="text-sm text-gray-600">156 recensioni totali</p>
                                </div>
                                <div class="md:col-span-2">
                                    <div class="space-y-2">
                                        @for ($star = 5; $star >= 1; $star--)
                                            <div class="flex items-center space-x-3">
                                                <span class="text-sm text-gray-600 w-8">{{ $star }} ★</span>
                                                <div class="flex-1 h-2 bg-gray-200 rounded-full">
                                                    <div class="h-2 bg-yellow-400 rounded-full" style="width: {{ $star == 5 ? '75%' : ($star == 4 ? '20%' : '3%') }}"></div>
                                                </div>
                                                <span class="text-sm text-gray-600 w-8">{{ $star == 5 ? '117' : ($star == 4 ? '31' : '5') }}</span>
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Individual Reviews -->
                        <div class="space-y-4">
                            @php
                                $reviews = [
                                    ['name' => 'Sofia M.', 'rating' => 5, 'date' => '2 settimane fa', 'text' => 'Prof.ssa Martina è fantastica! Il corso è ben strutturato e le lezioni sono sempre stimolanti. Ho migliorato moltissimo la mia tecnica.'],
                                    ['name' => 'Giulia R.', 'rating' => 5, 'date' => '1 mese fa', 'text' => 'Ambiente accogliente e professionale. Le correzioni sono sempre costruttive e motivanti. Consigliatissimo!'],
                                    ['name' => 'Marco V.', 'rating' => 4, 'date' => '2 mesi fa', 'text' => 'Ottima scuola, insegnanti preparati. Forse un po\' impegnativo per chi ha appena iniziato, ma perfetto per il livello intermedio.']
                                ];
                            @endphp

                            @foreach ($reviews as $review)
                                <div class="bg-white p-6 rounded-xl border border-gray-200">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-10 h-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($review['name'], 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ $review['name'] }}</p>
                                                <div class="flex items-center">
                                                    @for ($i = 1; $i <= 5; $i++)
                                                        <svg class="w-4 h-4 {{ $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                        </svg>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        <span class="text-sm text-gray-500">{{ $review['date'] }}</span>
                                    </div>
                                    <p class="text-gray-700">{{ $review['text'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        <!-- Load More Reviews -->
                        <div class="text-center">
                            <button class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                Carica altre recensioni
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Gallery Tab -->
                <div x-show="activeTab === 'gallery'">
                    <div class="space-y-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-3">Galleria del Corso</h3>
                            <p class="text-gray-600 mb-6">Foto delle lezioni, saggi e momenti speciali del corso</p>
                        </div>

                        <!-- Photo Grid -->
                        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                            @for ($i = 1; $i <= 12; $i++)
                                <div class="relative aspect-square bg-gradient-to-br from-rose-300 to-purple-400 rounded-xl overflow-hidden cursor-pointer hover:shadow-lg transition-shadow group">
                                    <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors"></div>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="w-12 h-12 bg-white/20 rounded-full flex items-center justify-center text-white">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/60 to-transparent">
                                        <p class="text-white text-sm font-medium">Lezione {{ $i }}</p>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Courses -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Altri Corsi che Potrebbero Interessarti</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @php
                    $relatedCourses = [
                        ['name' => 'Danza Classica Avanzato', 'instructor' => 'Prof. Anna Bianchi', 'price' => 120, 'level' => 'Avanzato'],
                        ['name' => 'Danza Contemporanea', 'instructor' => 'Prof. Elena Conti', 'price' => 95, 'level' => 'Intermedio'],
                        ['name' => 'Tecnica delle Punte', 'instructor' => 'Prof. Martina Rossi', 'price' => 110, 'level' => 'Avanzato']
                    ];
                @endphp

                @foreach ($relatedCourses as $course)
                    <div class="bg-white p-4 rounded-xl border border-gray-200 hover:shadow-lg transition-shadow">
                        <div class="h-32 bg-gradient-to-r from-rose-300 to-purple-400 rounded-lg mb-4"></div>
                        <h4 class="font-semibold text-gray-900 mb-1">{{ $course['name'] }}</h4>
                        <p class="text-sm text-gray-600 mb-2">{{ $course['instructor'] }}</p>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-500">{{ $course['level'] }}</span>
                            <span class="font-bold text-gray-900">€{{ $course['price'] }}</span>
                        </div>
                        <button class="w-full mt-3 px-4 py-2 text-sm font-medium text-rose-600 border border-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                            Scopri di più
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Enrollment Modal -->
    <x-modal name="enroll-course" maxWidth="lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Iscriviti al Corso</h3>
                <button @click="$dispatch('close-modal', 'enroll-course')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form class="space-y-6">
                <!-- Course Summary -->
                <div class="bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg p-4 border border-rose-200">
                    <h4 class="font-semibold text-gray-900 mb-2">Danza Classica Intermedio</h4>
                    <div class="grid grid-cols-2 gap-4 text-sm text-gray-600">
                        <div>Prof. Martina Rossi</div>
                        <div>Mar/Gio 16:00-17:30</div>
                        <div>Livello Intermedio</div>
                        <div>8 mesi di corso</div>
                    </div>
                </div>

                <!-- Payment Options -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-3">Modalità di Pagamento</label>
                    <div class="space-y-3" x-data="{ paymentMode: 'monthly' }">
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" x-model="paymentMode" value="monthly" class="form-radio h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">Pagamento Mensile</span>
                                    <span class="font-bold text-gray-900">€85/mese</span>
                                </div>
                                <p class="text-sm text-gray-600">Paghi ogni mese, maggiore flessibilità</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-3 border border-green-200 rounded-lg cursor-pointer hover:bg-green-50">
                            <input type="radio" x-model="paymentMode" value="quarterly" class="form-radio h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">Pagamento Trimestrale</span>
                                    <div>
                                        <span class="font-bold text-green-700">€242</span>
                                        <span class="ml-1 text-xs text-green-600 bg-green-200 px-2 py-1 rounded-full">-5%</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">€80.67 al mese - Risparmia €12.50</p>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-3 border border-blue-200 rounded-lg cursor-pointer hover:bg-blue-50">
                            <input type="radio" x-model="paymentMode" value="yearly" class="form-radio h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300">
                            <div class="ml-3 flex-1">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium text-gray-900">Pagamento Annuale</span>
                                    <div>
                                        <span class="font-bold text-blue-700">€918</span>
                                        <span class="ml-1 text-xs text-blue-600 bg-blue-200 px-2 py-1 rounded-full">-10%</span>
                                    </div>
                                </div>
                                <p class="text-sm text-gray-600">€76.50 al mese - Risparmia €102</p>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Additional Options -->
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="trial_lesson" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                        <label for="trial_lesson" class="ml-2 text-sm text-gray-900">
                            Richiedi lezione di prova gratuita prima dell'iscrizione
                        </label>
                    </div>
                    
                    <div class="flex items-center">
                        <input type="checkbox" id="newsletter" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded" checked>
                        <label for="newsletter" class="ml-2 text-sm text-gray-900">
                            Ricevi aggiornamenti su eventi e nuovi corsi
                        </label>
                    </div>
                </div>

                <!-- Terms and Conditions -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-center mb-2">
                        <input type="checkbox" id="terms" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded" required>
                        <label for="terms" class="ml-2 text-sm font-medium text-gray-900">
                            Accetto i termini e le condizioni
                        </label>
                    </div>
                    <p class="text-xs text-gray-600">
                        Leggi i <a href="#" class="text-rose-600 hover:text-rose-700">termini di servizio</a> e la 
                        <a href="#" class="text-rose-600 hover:text-rose-700">politica di cancellazione</a>.
                    </p>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <button type="button" @click="$dispatch('close-modal', 'enroll-course')" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Annulla
                    </button>
                    <button type="submit" 
                            class="px-6 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        Conferma Iscrizione
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
</x-app-layout>