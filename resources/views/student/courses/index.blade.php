<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Corsi Disponibili
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Scopri tutti i corsi offerti dalla nostra scuola
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">Filtri attivi</p>
                    <p class="text-xs text-gray-500" x-data x-text="document.querySelectorAll('input[type=checkbox]:checked, select option:checked').length + ' filtri'">2 filtri</p>
                </div>
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
        <li class="text-gray-900 font-medium">Corsi Disponibili</li>
    </x-slot>

    <div class="space-y-6" x-data="{ viewMode: 'grid', showFilters: false }">
        <!-- Search and Filters -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div class="flex flex-col sm:flex-row items-start sm:items-center space-y-4 sm:space-y-0 sm:space-x-4">
                    <!-- Search -->
                    <div class="relative">
                        <input type="search" placeholder="Cerca corsi..." 
                               class="pl-10 pr-4 py-2 text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 w-64">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Quick Filters -->
                    <div class="flex items-center space-x-3">
                        <button @click="showFilters = !showFilters" 
                                :class="showFilters ? 'bg-rose-100 text-rose-700' : 'bg-gray-100 text-gray-700'"
                                class="px-3 py-2 text-sm font-medium rounded-lg hover:bg-rose-100 hover:text-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                            </svg>
                            Filtri
                        </button>

                        <!-- Posti disponibili toggle -->
                        <label class="inline-flex items-center">
                            <input type="checkbox" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <span class="ml-2 text-sm text-gray-700">Solo posti disponibili</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- View Mode Toggle -->
                    <div class="flex items-center bg-gray-100 rounded-lg p-1">
                        <button @click="viewMode = 'grid'" 
                                :class="viewMode === 'grid' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                class="p-1.5 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                            </svg>
                        </button>
                        <button @click="viewMode = 'list'" 
                                :class="viewMode === 'list' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                class="p-1.5 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Sort -->
                    <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                        <option>Ordina per</option>
                        <option>Nome A-Z</option>
                        <option>Nome Z-A</option>
                        <option>Prezzo crescente</option>
                        <option>Prezzo decrescente</option>
                        <option>Posti disponibili</option>
                        <option>Più recenti</option>
                    </select>
                </div>
            </div>

            <!-- Advanced Filters (Collapsible) -->
            <div x-show="showFilters" x-transition class="mt-6 pt-4 border-t border-gray-200">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Dance Type -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo di Danza</label>
                        <div class="space-y-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded" checked>
                                <span class="ml-2 text-sm text-gray-700">Danza Classica</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Danza Moderna</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded" checked>
                                <span class="ml-2 text-sm text-gray-700">Hip Hop</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Jazz Dance</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                <span class="ml-2 text-sm text-gray-700">Contemporanea</span>
                            </label>
                        </div>
                    </div>

                    <!-- Level -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Livello</label>
                        <select class="w-full text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                            <option>Tutti i livelli</option>
                            <option>Principiante</option>
                            <option selected>Intermedio</option>
                            <option>Avanzato</option>
                            <option>Professionale</option>
                        </select>
                    </div>

                    <!-- Price Range -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fascia di Prezzo (€)</label>
                        <div class="space-y-2">
                            <div class="flex items-center space-x-2">
                                <input type="number" placeholder="Min" class="flex-1 text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                                <span class="text-gray-500">-</span>
                                <input type="number" placeholder="Max" class="flex-1 text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                            </div>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Giorni della Settimana</label>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'] as $day)
                                <label class="inline-flex items-center">
                                    <input type="checkbox" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                    <span class="ml-1 text-sm text-gray-700">{{ $day }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <button class="text-sm text-gray-500 hover:text-gray-700">Cancella tutti i filtri</button>
                    <div class="flex items-center space-x-3">
                        <button @click="showFilters = false" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                            Chiudi
                        </button>
                        <button class="px-4 py-2 text-sm text-white bg-rose-600 rounded-lg hover:bg-rose-700">
                            Applica Filtri
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Results Summary -->
        <div class="flex items-center justify-between text-sm text-gray-600">
            <span>Mostrando 12 corsi di 24 totali</span>
            <span>Filtrati per: Danza Classica, Hip Hop, Livello Intermedio</span>
        </div>

        <!-- Courses Grid -->
        <div x-show="viewMode === 'grid'">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @php
                    $courses = [
                        [
                            'id' => 1,
                            'name' => 'Danza Classica Intermedio',
                            'instructor' => 'Prof. Martina Rossi',
                            'description' => 'Perfezionamento della tecnica classica con focus su adagio e allegro. Corso rivolto a studenti con esperienza di base.',
                            'level' => 'Intermedio',
                            'price' => 85,
                            'schedule' => 'Mar/Gio 16:00-17:30',
                            'duration' => 90,
                            'students' => 28,
                            'max_students' => 30,
                            'available_spots' => 2,
                            'status' => 'available',
                            'image' => '/images/courses/classical.jpg',
                            'rating' => 4.8,
                            'is_enrolled' => false
                        ],
                        [
                            'id' => 2,
                            'name' => 'Hip Hop Avanzato',
                            'instructor' => 'Prof. Marco Bianchi',
                            'description' => 'Stili urban e street dance con coreografie moderne. Ideale per chi ha già esperienza nell\'hip hop.',
                            'level' => 'Avanzato',
                            'price' => 75,
                            'schedule' => 'Lun/Mer/Ven 18:30-19:30',
                            'duration' => 60,
                            'students' => 24,
                            'max_students' => 25,
                            'available_spots' => 1,
                            'status' => 'available',
                            'image' => '/images/courses/hiphop.jpg',
                            'rating' => 4.9,
                            'is_enrolled' => true
                        ],
                        [
                            'id' => 3,
                            'name' => 'Danza Moderna Principianti',
                            'instructor' => 'Prof. Elena Verdi',
                            'description' => 'Introduzione alla danza moderna con focus su tecnica Graham e movimento espressivo.',
                            'level' => 'Principiante',
                            'price' => 70,
                            'schedule' => 'Mar/Gio 19:00-20:00',
                            'duration' => 60,
                            'students' => 15,
                            'max_students' => 20,
                            'available_spots' => 5,
                            'status' => 'available',
                            'image' => '/images/courses/modern.jpg',
                            'rating' => 4.6,
                            'is_enrolled' => false
                        ],
                        [
                            'id' => 4,
                            'name' => 'Jazz Dance Intermedio',
                            'instructor' => 'Prof. Sofia Rossi',
                            'description' => 'Energia e dinamismo del jazz con coreografie coinvolgenti su musiche contemporanee.',
                            'level' => 'Intermedio',
                            'price' => 80,
                            'schedule' => 'Lun/Ven 17:00-18:30',
                            'duration' => 90,
                            'students' => 18,
                            'max_students' => 22,
                            'available_spots' => 4,
                            'status' => 'available',
                            'image' => '/images/courses/jazz.jpg',
                            'rating' => 4.7,
                            'is_enrolled' => false
                        ],
                        [
                            'id' => 5,
                            'name' => 'Danza Contemporanea',
                            'instructor' => 'Prof. Elena Conti',
                            'description' => 'Espressione corporea e movimento fluido che unisce tecnica classica e moderna.',
                            'level' => 'Intermedio',
                            'price' => 95,
                            'schedule' => 'Ven 19:00-20:30',
                            'duration' => 90,
                            'students' => 16,
                            'max_students' => 18,
                            'available_spots' => 2,
                            'status' => 'available',
                            'image' => '/images/courses/contemporary.jpg',
                            'rating' => 4.8,
                            'is_enrolled' => true
                        ],
                        [
                            'id' => 6,
                            'name' => 'Balletto Classico Avanzato',
                            'instructor' => 'Prof. Anna Maria Bianchi',
                            'description' => 'Tecnica avanzata con studio di variazioni dal repertorio classico e preparazione alle punte.',
                            'level' => 'Avanzato',
                            'price' => 120,
                            'schedule' => 'Lun/Mer/Ven 15:00-16:30',
                            'duration' => 90,
                            'students' => 15,
                            'max_students' => 15,
                            'available_spots' => 0,
                            'status' => 'full',
                            'image' => '/images/courses/ballet.jpg',
                            'rating' => 4.9,
                            'is_enrolled' => false
                        ]
                    ];
                @endphp

                @foreach ($courses as $course)
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <!-- Course Image -->
                        <div class="relative h-48 bg-gradient-to-r from-rose-400 to-purple-500">
                            <div class="absolute inset-0 bg-black/20"></div>
                            <div class="absolute top-4 left-4">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium text-white bg-black/30 backdrop-blur-sm">
                                    {{ $course['level'] }}
                                </span>
                            </div>
                            <div class="absolute top-4 right-4">
                                @if ($course['is_enrolled'])
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Iscritto
                                    </span>
                                @elseif ($course['status'] === 'full')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Al completo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ $course['available_spots'] }} posti
                                    </span>
                                @endif
                            </div>
                            <div class="absolute bottom-4 left-4 right-4">
                                <h3 class="text-lg font-bold text-white mb-1">{{ $course['name'] }}</h3>
                                <p class="text-sm text-white/90">{{ $course['instructor'] }}</p>
                            </div>
                        </div>

                        <!-- Course Content -->
                        <div class="p-6">
                            <div class="mb-4">
                                <p class="text-sm text-gray-600 line-clamp-2">{{ $course['description'] }}</p>
                            </div>

                            <!-- Course Details -->
                            <div class="space-y-2 mb-4">
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $course['schedule'] }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{ $course['duration'] }} minuti per lezione
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    {{ $course['students'] }}/{{ $course['max_students'] }} studenti
                                </div>
                            </div>

                            <!-- Rating and Price -->
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="flex items-center">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <svg class="w-4 h-4 {{ $i <= floor($course['rating']) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                        @endfor
                                    </div>
                                    <span class="text-sm text-gray-600 ml-1">{{ $course['rating'] }}</span>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-gray-900">€{{ $course['price'] }}</div>
                                    <div class="text-sm text-gray-500">al mese</div>
                                </div>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('student.courses.show', $course['id']) }}" 
                                   class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center transition-colors">
                                    Dettagli
                                </a>
                                
                                @if ($course['is_enrolled'])
                                    <button class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg cursor-not-allowed">
                                        Già Iscritto
                                    </button>
                                @elseif ($course['status'] === 'full')
                                    <button class="flex-1 px-4 py-2 text-sm font-medium text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                                        Al Completo
                                    </button>
                                @else
                                    <button @click="$dispatch('open-modal', 'enroll-{{ $course['id'] }}')" 
                                            class="flex-1 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 transition-all">
                                        Iscriviti
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Courses List -->
        <div x-show="viewMode === 'list'">
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="divide-y divide-gray-200">
                    @foreach ($courses as $course)
                        <div class="p-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center space-x-6">
                                <!-- Course Image -->
                                <div class="flex-shrink-0">
                                    <div class="w-20 h-20 bg-gradient-to-r from-rose-400 to-purple-500 rounded-xl flex items-center justify-center text-white font-bold text-lg">
                                        {{ strtoupper(substr($course['name'], 0, 2)) }}
                                    </div>
                                </div>

                                <!-- Course Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">{{ $course['name'] }}</h3>
                                            <p class="text-sm text-gray-600">{{ $course['instructor'] }}</p>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            @if ($course['is_enrolled'])
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Iscritto
                                                </span>
                                            @elseif ($course['status'] === 'full')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Al completo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    {{ $course['available_spots'] }} posti
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $course['level'] }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <p class="mt-2 text-sm text-gray-600">{{ $course['description'] }}</p>
                                    
                                    <div class="mt-3 flex items-center justify-between">
                                        <div class="flex items-center space-x-4 text-sm text-gray-500">
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                {{ $course['schedule'] }}
                                            </span>
                                            <span class="flex items-center">
                                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                {{ $course['duration'] }} min
                                            </span>
                                            <div class="flex items-center">
                                                @for ($i = 1; $i <= 5; $i++)
                                                    <svg class="w-3 h-3 {{ $i <= floor($course['rating']) ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                    </svg>
                                                @endfor
                                                <span class="ml-1">{{ $course['rating'] }}</span>
                                            </div>
                                        </div>
                                        
                                        <div class="flex items-center space-x-4">
                                            <div class="text-right">
                                                <div class="text-lg font-bold text-gray-900">€{{ $course['price'] }}</div>
                                                <div class="text-xs text-gray-500">al mese</div>
                                            </div>
                                            
                                            <div class="flex items-center space-x-2">
                                                <a href="{{ route('student.courses.show', $course['id']) }}" 
                                                   class="px-3 py-1.5 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                                                    Dettagli
                                                </a>
                                                
                                                @if ($course['is_enrolled'])
                                                    <button class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg cursor-not-allowed">
                                                        Iscritto
                                                    </button>
                                                @elseif ($course['status'] === 'full')
                                                    <button class="px-3 py-1.5 text-sm font-medium text-gray-400 bg-gray-200 rounded-lg cursor-not-allowed">
                                                        Al Completo
                                                    </button>
                                                @else
                                                    <button @click="$dispatch('open-modal', 'enroll-{{ $course['id'] }}')" 
                                                            class="px-3 py-1.5 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                                                        Iscriviti
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex items-center justify-center">
            <nav class="flex items-center space-x-2">
                <button class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-white rounded-lg">
                    Precedente
                </button>
                <button class="px-3 py-2 text-sm bg-rose-600 text-white rounded-lg">1</button>
                <button class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-white rounded-lg">2</button>
                <button class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-white rounded-lg">3</button>
                <button class="px-3 py-2 text-sm text-gray-500 hover:text-gray-700 hover:bg-white rounded-lg">
                    Successivo
                </button>
            </nav>
        </div>
    </div>

    <!-- Enrollment Modal (Sample for first course) -->
    <x-modal name="enroll-1" maxWidth="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Iscriviti al Corso</h3>
                <button @click="$dispatch('close-modal', 'enroll-1')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <div class="bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg p-4 border border-rose-200">
                    <h4 class="font-semibold text-gray-900">Danza Classica Intermedio</h4>
                    <p class="text-sm text-gray-600">Prof. Martina Rossi • Mar/Gio 16:00-17:30</p>
                    <p class="text-lg font-bold text-rose-600 mt-2">€85,00 al mese</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="inline-flex items-center">
                        <input type="checkbox" class="form-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                        <span class="ml-2 text-sm text-gray-900">Richiedi lezione di prova gratuita</span>
                    </label>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modalità di Pagamento</label>
                    <div class="space-y-2">
                        <label class="inline-flex items-center">
                            <input type="radio" name="payment_method" value="monthly" class="form-radio h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300" checked>
                            <span class="ml-2 text-sm text-gray-900">Pagamento mensile</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="payment_method" value="quarterly" class="form-radio h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300">
                            <span class="ml-2 text-sm text-gray-900">Pagamento trimestrale (-5%)</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note aggiuntive</label>
                    <textarea rows="3" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                              placeholder="Eventuali richieste o informazioni aggiuntive..."></textarea>
                </div>
            </div>
            
            <div class="flex items-center justify-end space-x-3 mt-6">
                <button @click="$dispatch('close-modal', 'enroll-1')" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Annulla
                </button>
                <button class="px-4 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                    Conferma Iscrizione
                </button>
            </div>
        </div>
    </x-modal>
</x-app-layout>