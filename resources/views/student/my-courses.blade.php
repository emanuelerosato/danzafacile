<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    I Miei Corsi
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Tutti i corsi a cui sei iscritto
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('student.courses.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    Scopri Altri Corsi
                </a>
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
        <li class="text-gray-900 font-medium">I Miei Corsi</li>
    </x-slot>

    <div class="space-y-6">
        <!-- Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-stats-card 
                title="Corsi Attivi"
                :value="3"
                icon="academic-cap"
                color="rose"
                subtitle="In corso"
            />
            
            <x-stats-card 
                title="Ore Totali"
                :value="72"
                icon="clock"
                color="purple"
                subtitle="Questo mese"
            />
            
            <x-stats-card 
                title="Presenza Media"
                :value="'94%'"
                icon="check-circle"
                color="green"
                subtitle="Eccellente"
            />
            
            <x-stats-card 
                title="Prossimo Pagamento"
                :value="'€255'"
                icon="currency-dollar"
                color="blue"
                subtitle="15 Ottobre 2024"
            />
        </div>

        <!-- Current Courses -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">Corsi Attualmente Frequentati</h3>
                <span class="text-sm text-gray-500">3 corsi attivi</span>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @php
                    $myCourses = [
                        [
                            'id' => 1,
                            'name' => 'Danza Classica Intermedio',
                            'instructor' => 'Prof. Martina Rossi',
                            'schedule' => 'Mar/Gio 16:00-17:30',
                            'enrollment_date' => '2024-09-01',
                            'progress' => 85,
                            'attendance' => 95,
                            'next_lesson' => '2024-09-12 16:00:00',
                            'total_lessons' => 32,
                            'completed_lessons' => 8,
                            'price' => 85,
                            'payment_status' => 'paid',
                            'next_payment' => '2024-10-01',
                            'notes' => 'Ottimi progressi nella tecnica'
                        ],
                        [
                            'id' => 2,
                            'name' => 'Hip Hop Avanzato',
                            'instructor' => 'Prof. Marco Bianchi',
                            'schedule' => 'Lun/Mer/Ven 18:30-19:30',
                            'enrollment_date' => '2024-08-15',
                            'progress' => 92,
                            'attendance' => 88,
                            'next_lesson' => '2024-09-13 18:30:00',
                            'total_lessons' => 48,
                            'completed_lessons' => 15,
                            'price' => 75,
                            'payment_status' => 'paid',
                            'next_payment' => '2024-10-15',
                            'notes' => 'Eccellente interpretazione'
                        ],
                        [
                            'id' => 3,
                            'name' => 'Danza Contemporanea',
                            'instructor' => 'Prof. Elena Conti',
                            'schedule' => 'Ven 19:00-20:30',
                            'enrollment_date' => '2024-09-05',
                            'progress' => 78,
                            'attendance' => 100,
                            'next_lesson' => '2024-09-13 19:00:00',
                            'total_lessons' => 32,
                            'completed_lessons' => 4,
                            'price' => 95,
                            'payment_status' => 'pending',
                            'next_payment' => '2024-10-05',
                            'notes' => 'Lavora sulla fluidità del movimento'
                        ]
                    ];
                @endphp

                @foreach ($myCourses as $course)
                    <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow">
                        <!-- Course Header -->
                        <div class="relative h-32 bg-gradient-to-r from-rose-400 via-purple-500 to-violet-600">
                            <div class="absolute inset-0 bg-black/20"></div>
                            <div class="absolute top-3 right-3">
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium text-white 
                                    {{ $course['payment_status'] === 'paid' ? 'bg-green-500/80' : 'bg-orange-500/80' }}">
                                    {{ $course['payment_status'] === 'paid' ? 'Pagato' : 'In scadenza' }}
                                </span>
                            </div>
                            <div class="absolute bottom-3 left-3 right-3 text-white">
                                <h4 class="font-bold text-lg">{{ $course['name'] }}</h4>
                                <p class="text-sm text-white/90">{{ $course['instructor'] }}</p>
                            </div>
                        </div>

                        <!-- Course Content -->
                        <div class="p-4 space-y-4">
                            <!-- Progress Section -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-700">Progresso</span>
                                    <span class="text-sm text-gray-600">{{ $course['completed_lessons'] }}/{{ $course['total_lessons'] }} lezioni</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gradient-to-r from-rose-400 to-purple-500 h-2 rounded-full transition-all duration-500" 
                                         style="width: {{ ($course['completed_lessons'] / $course['total_lessons']) * 100 }}%"></div>
                                </div>
                                <div class="flex items-center justify-between mt-1 text-xs text-gray-500">
                                    <span>Valutazione: {{ $course['progress'] }}%</span>
                                    <span>Presenze: {{ $course['attendance'] }}%</span>
                                </div>
                            </div>

                            <!-- Schedule Info -->
                            <div class="space-y-2">
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
                                    Prossima: {{ date('d/m/Y H:i', strtotime($course['next_lesson'])) }}
                                </div>
                                <div class="flex items-center text-sm text-gray-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    €{{ $course['price'] }}/mese - Prossimo: {{ date('d/m/Y', strtotime($course['next_payment'])) }}
                                </div>
                            </div>

                            <!-- Teacher's Notes -->
                            @if ($course['notes'])
                                <div class="p-3 bg-blue-50 rounded-lg border border-blue-200">
                                    <div class="flex items-start">
                                        <svg class="w-4 h-4 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-medium text-blue-900">Nota dell'istruttore:</p>
                                            <p class="text-sm text-blue-800">{{ $course['notes'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Action Buttons -->
                            <div class="flex space-x-2 pt-2">
                                <a href="{{ route('student.courses.show', $course['id']) }}" 
                                   class="flex-1 px-3 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 text-center transition-colors">
                                    Dettagli
                                </a>
                                <button @click="$dispatch('open-modal', 'schedule-{{ $course['id'] }}')" 
                                        class="flex-1 px-3 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 transition-colors">
                                    Orario
                                </button>
                                <div class="relative" x-data="{ open: false }">
                                    <button @click="open = !open" 
                                            class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </button>
                                    
                                    <div x-show="open" @click.away="open = false" x-transition
                                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                                        <div class="py-1">
                                            <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                </svg>
                                                Registro Presenze
                                            </button>
                                            <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                Materiali Didattici
                                            </button>
                                            <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                Contatta Istruttore
                                            </button>
                                            <div class="border-t border-gray-100"></div>
                                            <button @click="$dispatch('open-modal', 'suspend-{{ $course['id'] }}')" 
                                                    class="flex items-center w-full px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">
                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                Sospendi Iscrizione
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Weekly Schedule Overview -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Il Mio Programma Settimanale</h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-7 gap-4">
                @php
                    $weekDays = ['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'];
                    $schedule = [
                        'Lunedì' => [['course' => 'Hip Hop Avanzato', 'time' => '18:30-19:30', 'instructor' => 'Prof. Marco Bianchi', 'color' => 'purple']],
                        'Martedì' => [['course' => 'Danza Classica Intermedio', 'time' => '16:00-17:30', 'instructor' => 'Prof. Martina Rossi', 'color' => 'rose']],
                        'Mercoledì' => [['course' => 'Hip Hop Avanzato', 'time' => '18:30-19:30', 'instructor' => 'Prof. Marco Bianchi', 'color' => 'purple']],
                        'Giovedì' => [['course' => 'Danza Classica Intermedio', 'time' => '16:00-17:30', 'instructor' => 'Prof. Martina Rossi', 'color' => 'rose']],
                        'Venerdì' => [
                            ['course' => 'Hip Hop Avanzato', 'time' => '18:30-19:30', 'instructor' => 'Prof. Marco Bianchi', 'color' => 'purple'],
                            ['course' => 'Danza Contemporanea', 'time' => '19:00-20:30', 'instructor' => 'Prof. Elena Conti', 'color' => 'blue']
                        ],
                        'Sabato' => [],
                        'Domenica' => []
                    ];
                @endphp

                @foreach ($weekDays as $day)
                    <div class="bg-white rounded-lg border border-gray-200 p-4 min-h-[200px]">
                        <h4 class="font-semibold text-gray-900 mb-3 text-center">{{ $day }}</h4>
                        <div class="space-y-2">
                            @if (isset($schedule[$day]) && count($schedule[$day]) > 0)
                                @foreach ($schedule[$day] as $lesson)
                                    <div class="p-3 bg-{{ $lesson['color'] }}-50 border border-{{ $lesson['color'] }}-200 rounded-lg">
                                        <div class="text-xs font-semibold text-{{ $lesson['color'] }}-800 mb-1">{{ $lesson['time'] }}</div>
                                        <div class="text-sm font-medium text-gray-900">{{ $lesson['course'] }}</div>
                                        <div class="text-xs text-gray-600">{{ $lesson['instructor'] }}</div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center text-gray-400 text-sm py-8">
                                    <svg class="w-8 h-8 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-2.172a1 1 0 01-.707-.293l-2.414-2.414a1 1 0 00-.707-.293H8"/>
                                    </svg>
                                    Nessuna lezione
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Progress Chart -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">I Miei Progressi</h3>
                <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option>Ultimi 3 mesi</option>
                    <option>Ultimi 6 mesi</option>
                    <option>Anno corrente</option>
                </select>
            </div>
            
            <div class="h-80">
                <canvas id="progressChart"></canvas>
            </div>
        </div>

        <!-- Recent Achievements -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Riconoscimenti Recenti</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="flex items-center p-4 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
                    <div class="flex-shrink-0 w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">Studentessa del Mese</h4>
                        <p class="text-sm text-gray-600">Eccellente dedizione e miglioramento</p>
                        <p class="text-xs text-gray-500">Settembre 2024</p>
                    </div>
                </div>
                
                <div class="flex items-center p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                    <div class="flex-shrink-0 w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">Presenza Perfetta</h4>
                        <p class="text-sm text-gray-600">100% di presenze per 3 mesi consecutivi</p>
                        <p class="text-xs text-gray-500">Agosto 2024</p>
                    </div>
                </div>
                
                <div class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-violet-50 rounded-lg border border-purple-200">
                    <div class="flex-shrink-0 w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h4 class="font-semibold text-gray-900">Primo Spettacolo</h4>
                        <p class="text-sm text-gray-600">Partecipazione al saggio di primavera</p>
                        <p class="text-xs text-gray-500">Giugno 2024</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @foreach ($myCourses as $course)
        <!-- Schedule Modal -->
        <x-modal name="schedule-{{ $course['id'] }}" maxWidth="lg">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Orario - {{ $course['name'] }}</h3>
                    <button @click="$dispatch('close-modal', 'schedule-{{ $course['id'] }}')" 
                            class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="mb-6">
                    <h4 class="font-medium text-gray-900 mb-3">Prossime Lezioni</h4>
                    <div class="space-y-3">
                        @for ($i = 0; $i < 5; $i++)
                            <div class="flex items-center p-3 {{ $i === 0 ? 'bg-rose-50 border-rose-200' : 'bg-gray-50 border-gray-200' }} rounded-lg border">
                                <div class="w-12 h-12 {{ $i === 0 ? 'bg-rose-500' : 'bg-gray-400' }} rounded-lg flex items-center justify-center text-white font-bold text-sm mr-4">
                                    {{ date('d', strtotime($course['next_lesson'] . ' +' . ($i * 2) . ' days')) }}
                                </div>
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">
                                        {{ date('l, d F Y', strtotime($course['next_lesson'] . ' +' . ($i * 2) . ' days')) }}
                                    </p>
                                    <p class="text-sm text-gray-600">{{ $course['schedule'] }} • {{ $course['instructor'] }}</p>
                                </div>
                                @if ($i === 0)
                                    <span class="px-2 py-1 bg-rose-100 text-rose-800 text-xs font-medium rounded-full">Prossima</span>
                                @endif
                            </div>
                        @endfor
                    </div>
                </div>
                
                <div class="flex items-center justify-end">
                    <button @click="$dispatch('close-modal', 'schedule-{{ $course['id'] }}')" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Chiudi
                    </button>
                </div>
            </div>
        </x-modal>

        <!-- Suspend Course Modal -->
        <x-modal name="suspend-{{ $course['id'] }}" maxWidth="md">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Sospendi Iscrizione</h3>
                    <button @click="$dispatch('close-modal', 'suspend-{{ $course['id'] }}')" 
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
                        Sei sicuro di voler sospendere l'iscrizione a <strong>{{ $course['name'] }}</strong>?
                    </p>
                    <p class="text-sm text-gray-500 text-center">
                        Potrai riattivare l'iscrizione in qualsiasi momento.
                    </p>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Motivo della sospensione</label>
                    <select class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                        <option>Impegni personali</option>
                        <option>Problemi di salute</option>
                        <option>Problemi economici</option>
                        <option>Cambio di orari</option>
                        <option>Altro</option>
                    </select>
                </div>
                
                <div class="flex items-center justify-end space-x-3">
                    <button @click="$dispatch('close-modal', 'suspend-{{ $course['id'] }}')" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Annulla
                    </button>
                    <button class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        Sospendi Iscrizione
                    </button>
                </div>
            </div>
        </x-modal>
    @endforeach

    @push('scripts')
    <script>
        // Progress Chart
        const progressCtx = document.getElementById('progressChart').getContext('2d');
        new Chart(progressCtx, {
            type: 'line',
            data: {
                labels: ['Luglio', 'Agosto', 'Settembre'],
                datasets: [{
                    label: 'Danza Classica',
                    data: [78, 82, 85],
                    borderColor: 'rgb(244, 63, 94)',
                    backgroundColor: 'rgba(244, 63, 94, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Hip Hop',
                    data: [85, 88, 92],
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Contemporanea',
                    data: [70, 75, 78],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        min: 60,
                        max: 100,
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>