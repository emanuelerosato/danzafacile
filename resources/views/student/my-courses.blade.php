<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    I Miei Corsi
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Programma, iscrizioni e progressi dei tuoi corsi di danza
                </p>
            </div>
            <a href="{{ route('student.courses.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Iscriviti a Nuovi Corsi
            </a>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('student.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">I Miei Corsi</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-rose-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Corsi Attivi</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['active_courses'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Ore Settimanali</p>
                                <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_hours_per_week'], 1) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Lezioni Completate</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $stats['completed_classes'] }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Prossima Lezione</p>
                                <p class="text-sm font-bold text-gray-900">
                                    @if($stats['next_class'])
                                        {{ $stats['next_class']['date']->format('d/m') }}
                                    @else
                                        Nessuna
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs -->
                <div class="bg-white rounded-lg shadow" x-data="{ activeTab: 'schedule' }">
                    <div class="border-b border-gray-200">
                        <nav class="flex space-x-8 px-6" aria-label="Tabs">
                            <button @click="activeTab = 'schedule'"
                                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                                    :class="activeTab === 'schedule' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Programma Settimanale
                                </div>
                            </button>

                            <button @click="activeTab = 'enrollments'"
                                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                                    :class="activeTab === 'enrollments' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Le Mie Iscrizioni
                                </div>
                            </button>

                            <button @click="activeTab = 'progress'"
                                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                                    :class="activeTab === 'progress' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Progressi & Statistiche
                                </div>
                            </button>

                            <button @click="activeTab = 'payments'"
                                    class="py-4 px-1 border-b-2 font-medium text-sm transition-colors duration-200"
                                    :class="activeTab === 'payments' ? 'border-rose-500 text-rose-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'">
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                    Pagamenti & Fatture
                                </div>
                            </button>
                        </nav>
                    </div>

                    <!-- Tab Content -->
                    <div class="p-6">
                        <!-- Schedule Tab -->
                        <div x-show="activeTab === 'schedule'" x-transition>
                            @if($stats['active_courses'] > 0)
                                <div class="space-y-6">
                                    <!-- Weekly Schedule Grid -->
                                    <div class="grid grid-cols-1 lg:grid-cols-7 gap-4">
                                        @foreach(['monday' => 'Lunedì', 'tuesday' => 'Martedì', 'wednesday' => 'Mercoledì', 'thursday' => 'Giovedì', 'friday' => 'Venerdì', 'saturday' => 'Sabato', 'sunday' => 'Domenica'] as $dayKey => $dayName)
                                            <div class="border border-gray-200 rounded-lg p-4 min-h-[200px]">
                                                <h4 class="font-medium text-gray-900 mb-3 text-center">{{ $dayName }}</h4>
                                                <div class="space-y-2">
                                                    @if(isset($weeklySchedule[$dayKey]) && count($weeklySchedule[$dayKey]) > 0)
                                                        @foreach($weeklySchedule[$dayKey] as $class)
                                                            <div class="bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg p-3 border border-rose-200">
                                                                <p class="font-medium text-sm text-gray-900">{{ $class['course']->name }}</p>
                                                                <p class="text-xs text-gray-600">{{ $class['instructor'] }}</p>
                                                                @if(isset($class['times']['start']) && isset($class['times']['end']))
                                                                    <p class="text-xs text-gray-500 mt-1">
                                                                        {{ $class['times']['start'] }} - {{ $class['times']['end'] }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    @else
                                                        <p class="text-sm text-gray-400 text-center italic">Nessuna lezione</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Upcoming Events -->
                                    @if(count($upcomingEvents) > 0)
                                        <div class="bg-gray-50 rounded-lg p-6">
                                            <h3 class="text-lg font-medium text-gray-900 mb-4">Prossime Lezioni</h3>
                                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                                @foreach($upcomingEvents as $event)
                                                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                                                        <h4 class="font-medium text-gray-900">{{ $event['course']->name }}</h4>
                                                        <p class="text-sm text-gray-600">{{ $event['instructor'] }}</p>
                                                        <p class="text-sm text-gray-500 mt-2">
                                                            {{ $event['date']->format('d/m/Y - l') }}
                                                        </p>
                                                        @if($event['course']->location)
                                                            <p class="text-xs text-gray-400">{{ $event['course']->location }}</p>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun corso attivo</h3>
                                    <p class="text-gray-600 mb-4">Non sei ancora iscritto a nessun corso. Inizia il tuo percorso di danza!</p>
                                    <a href="{{ route('student.courses.index') }}"
                                       class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-colors duration-200">
                                        Esplora i Corsi
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Enrollments Tab -->
                        <div x-show="activeTab === 'enrollments'" x-transition>
                            @if($enrollments->count() > 0)
                                <div class="space-y-6">
                                    <!-- Status Filter -->
                                    <div class="flex flex-wrap gap-2">
                                        <a href="{{ route('student.my-courses') }}"
                                           class="px-3 py-1 rounded-full text-sm font-medium {{ !request('status') ? 'bg-rose-100 text-rose-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                            Tutti ({{ $enrollmentStats['active'] + $enrollmentStats['completed'] + $enrollmentStats['cancelled'] + $enrollmentStats['pending'] }})
                                        </a>
                                        <a href="{{ route('student.my-courses', ['status' => 'active']) }}"
                                           class="px-3 py-1 rounded-full text-sm font-medium {{ request('status') === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                            Attivi ({{ $enrollmentStats['active'] }})
                                        </a>
                                        <a href="{{ route('student.my-courses', ['status' => 'completed']) }}"
                                           class="px-3 py-1 rounded-full text-sm font-medium {{ request('status') === 'completed' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                            Completati ({{ $enrollmentStats['completed'] }})
                                        </a>
                                        <a href="{{ route('student.my-courses', ['status' => 'pending']) }}"
                                           class="px-3 py-1 rounded-full text-sm font-medium {{ request('status') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                            In Attesa ({{ $enrollmentStats['pending'] }})
                                        </a>
                                        <a href="{{ route('student.my-courses', ['status' => 'cancelled']) }}"
                                           class="px-3 py-1 rounded-full text-sm font-medium {{ request('status') === 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                                            Cancellati ({{ $enrollmentStats['cancelled'] }})
                                        </a>
                                    </div>

                                    <!-- Enrollments List -->
                                    <div class="space-y-4">
                                        @foreach($enrollments as $enrollment)
                                            <div class="bg-white border border-gray-200 rounded-lg p-6 hover:shadow-md transition-shadow duration-200">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex-1">
                                                        <h3 class="text-lg font-medium text-gray-900">{{ $enrollment->course->name }}</h3>
                                                        <p class="text-sm text-gray-600 mt-1">{{ $enrollment->course->instructor ? $enrollment->course->instructor->name : 'Istruttore TBD' }}</p>
                                                        <p class="text-sm text-gray-500 mt-1">Iscritto il {{ $enrollment->enrollment_date->format('d/m/Y') }}</p>

                                                        @if($enrollment->course->location)
                                                            <p class="text-sm text-gray-500 mt-1">
                                                                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                                </svg>
                                                                {{ $enrollment->course->location }}
                                                            </p>
                                                        @endif

                                                        @if($enrollment->notes)
                                                            <p class="text-sm text-gray-600 mt-2 italic">{{ $enrollment->notes }}</p>
                                                        @endif
                                                    </div>

                                                    <div class="ml-6 flex flex-col items-end space-y-2">
                                                        @php
                                                            $statusColors = [
                                                                'active' => 'bg-green-100 text-green-800',
                                                                'pending' => 'bg-yellow-100 text-yellow-800',
                                                                'completed' => 'bg-blue-100 text-blue-800',
                                                                'cancelled' => 'bg-red-100 text-red-800'
                                                            ];
                                                            $statusLabels = [
                                                                'active' => 'Attivo',
                                                                'pending' => 'In Attesa',
                                                                'completed' => 'Completato',
                                                                'cancelled' => 'Cancellato'
                                                            ];
                                                        @endphp
                                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$enrollment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                            {{ $statusLabels[$enrollment->status] ?? ucfirst($enrollment->status) }}
                                                        </span>

                                                        <div class="flex space-x-2">
                                                            <a href="{{ route('student.courses.show', $enrollment->course) }}"
                                                               class="text-sm text-rose-600 hover:text-rose-700 font-medium">
                                                                Dettagli
                                                            </a>
                                                            @if($enrollment->status === 'active')
                                                                <button class="text-sm text-gray-400 hover:text-gray-600 font-medium">
                                                                    Gestisci
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <!-- Pagination -->
                                    @if($enrollments->hasPages())
                                        <div class="flex justify-center">
                                            {{ $enrollments->links() }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-12">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nessuna iscrizione</h3>
                                    <p class="text-gray-600 mb-4">Non hai ancora iscrizioni ai corsi.</p>
                                    <a href="{{ route('student.courses.index') }}"
                                       class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-colors duration-200">
                                        Esplora i Corsi
                                    </a>
                                </div>
                            @endif
                        </div>

                        <!-- Progress Tab -->
                        <div x-show="activeTab === 'progress'" x-transition>
                            <div class="space-y-6">
                                <!-- Course Progress -->
                                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                    @if($activeEnrollments->count() > 0)
                                        @foreach($activeEnrollments as $enrollment)
                                            <div class="bg-white border border-gray-200 rounded-lg p-6">
                                                <h4 class="font-medium text-gray-900 mb-4">{{ $enrollment->course->name }}</h4>

                                                @php
                                                    $courseStarted = $enrollment->course->start_date <= now();
                                                    $totalWeeks = $enrollment->course->duration_weeks ?? 12;
                                                    $progressPercentage = $courseStarted ? min(100, (now()->diffInWeeks($enrollment->course->start_date) / $totalWeeks) * 100) : 0;
                                                @endphp

                                                <div class="mb-4">
                                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                                        <span>Progresso del corso</span>
                                                        <span>{{ number_format($progressPercentage, 0) }}%</span>
                                                    </div>
                                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                                        <div class="bg-gradient-to-r from-rose-500 to-purple-600 h-2 rounded-full transition-all duration-300"
                                                             style="width: {{ $progressPercentage }}%"></div>
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-2 gap-4 text-sm">
                                                    <div>
                                                        <p class="text-gray-500">Inizio corso</p>
                                                        <p class="font-medium">{{ $enrollment->course->start_date->format('d/m/Y') }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-gray-500">Fine corso</p>
                                                        <p class="font-medium">{{ $enrollment->course->end_date->format('d/m/Y') }}</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-gray-500">Durata</p>
                                                        <p class="font-medium">{{ $enrollment->course->duration_weeks ?? 12 }} settimane</p>
                                                    </div>
                                                    <div>
                                                        <p class="text-gray-500">Livello</p>
                                                        <p class="font-medium">{{ ucfirst($enrollment->course->level) }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="col-span-2 text-center py-8">
                                            <p class="text-gray-500">Nessun corso attivo per mostrare i progressi</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Overall Statistics -->
                                <div class="bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg p-6 border border-rose-200">
                                    <h3 class="text-lg font-medium text-gray-900 mb-4">Statistiche Generali</h3>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                        <div class="text-center">
                                            <p class="text-2xl font-bold text-rose-600">{{ $enrollmentStats['active'] + $enrollmentStats['completed'] }}</p>
                                            <p class="text-sm text-gray-600">Corsi Totali</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-2xl font-bold text-green-600">{{ $enrollmentStats['completed'] }}</p>
                                            <p class="text-sm text-gray-600">Completati</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-2xl font-bold text-purple-600">{{ number_format($stats['total_hours_per_week'] * 4, 0) }}</p>
                                            <p class="text-sm text-gray-600">Ore Mensili</p>
                                        </div>
                                        <div class="text-center">
                                            <p class="text-2xl font-bold text-blue-600">{{ $stats['completed_classes'] }}</p>
                                            <p class="text-sm text-gray-600">Lezioni Fatte</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Payments Tab -->
                        <div x-show="activeTab === 'payments'" x-transition>
                            <div class="space-y-6">
                                <!-- Payment Statistics Cards -->
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                    <div class="bg-white rounded-lg shadow p-6">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-500">Totale Speso</p>
                                                <p class="text-2xl font-bold text-gray-900">€ {{ number_format($paymentStats['total_spent'], 2) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-lg shadow p-6">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-500">In Attesa</p>
                                                <p class="text-2xl font-bold text-gray-900">€ {{ number_format($paymentStats['pending_amount'], 2) }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-lg shadow p-6">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-500">Scaduti</p>
                                                <p class="text-2xl font-bold text-gray-900">{{ $paymentStats['overdue_count'] }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="bg-white rounded-lg shadow p-6">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                            </div>
                                            <div class="ml-4">
                                                <p class="text-sm font-medium text-gray-500">Questo Mese</p>
                                                <p class="text-2xl font-bold text-gray-900">€ {{ number_format($paymentStats['this_month_spent'], 2) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Upcoming Payments -->
                                @if($upcomingPayments->count() > 0)
                                    <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-lg border border-orange-200 p-6">
                                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                                            <svg class="w-5 h-5 inline mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                            </svg>
                                            Pagamenti in Scadenza
                                        </h3>
                                        <div class="space-y-3">
                                            @foreach($upcomingPayments as $payment)
                                                <div class="bg-white rounded-lg p-4 border border-orange-200">
                                                    <div class="flex items-center justify-between">
                                                        <div>
                                                            <h4 class="font-medium text-gray-900">
                                                                {{ $payment->course ? $payment->course->name : $payment->payment_type_name }}
                                                            </h4>
                                                            <p class="text-sm text-gray-600">Scadenza: {{ $payment->due_date->format('d/m/Y') }}</p>
                                                        </div>
                                                        <div class="text-right">
                                                            <p class="font-bold text-gray-900">€ {{ number_format($payment->amount, 2) }}</p>
                                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                                                {{ $payment->status_name }}
                                                            </span>
                                                            @if($payment->status === 'pending')
                                                                <button onclick="payWithPayPal({{ $payment->id }})"
                                                                        class="mt-2 inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                                                                        <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.03-.026.06-.04.09-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9L7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81a4.154 4.154 0 0 1 .607.541c.013-.03.026-.06.04-.09.983-5.05 4.349-6.797 8.647-6.797h2.19c.524 0 .968-.382 1.05-.9L23.722.901C23.64.382 23.192 0 22.668 0h-7.46c-2.57 0-4.578.543-5.69 1.81z"/>
                                                                    </svg>
                                                                    PayPal
                                                                </button>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <!-- Recent Payments -->
                                <div class="bg-white rounded-lg shadow">
                                    <div class="px-6 py-4 border-b border-gray-200">
                                        <div class="flex items-center justify-between">
                                            <h3 class="text-lg font-medium text-gray-900">Ultimi Pagamenti</h3>
                                            <div class="flex space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Completati: {{ $paymentStatusStats['completed'] }}
                                                </span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    In Attesa: {{ $paymentStatusStats['pending'] }}
                                                </span>
                                                @if($paymentStatusStats['failed'] > 0)
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                        Falliti: {{ $paymentStatusStats['failed'] }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="p-6">
                                        @if($userPayments->count() > 0)
                                            <div class="space-y-4">
                                                @foreach($userPayments as $payment)
                                                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200"
                                                         data-payment-id="{{ $payment->id }}"
                                                         data-payment-status="{{ $payment->status }}">
                                                        <div class="flex items-start justify-between">
                                                            <div class="flex-1">
                                                                <h4 class="font-medium text-gray-900">
                                                                    {{ $payment->course ? $payment->course->name : $payment->payment_type_name }}
                                                                </h4>
                                                                <p class="text-sm text-gray-600 mt-1">
                                                                    {{ $payment->payment_method_name }}
                                                                    @if($payment->payment_date)
                                                                        - {{ $payment->payment_date->format('d/m/Y') }}
                                                                    @endif
                                                                </p>
                                                                @if($payment->notes)
                                                                    <p class="text-sm text-gray-500 mt-1">{{ $payment->notes }}</p>
                                                                @endif
                                                                @if($payment->receipt_number)
                                                                    <p class="text-xs text-gray-400 mt-1">Ricevuta: {{ $payment->receipt_number }}</p>
                                                                @endif
                                                            </div>
                                                            <div class="ml-6 text-right">
                                                                <p class="font-bold text-gray-900">€ {{ number_format($payment->amount, 2) }}</p>
                                                                @php
                                                                    $statusColors = [
                                                                        'completed' => 'bg-green-100 text-green-800',
                                                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                                                        'failed' => 'bg-red-100 text-red-800',
                                                                        'refunded' => 'bg-purple-100 text-purple-800',
                                                                        'cancelled' => 'bg-gray-100 text-gray-800',
                                                                        'processing' => 'bg-blue-100 text-blue-800'
                                                                    ];
                                                                @endphp
                                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$payment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                                                    {{ $payment->status_name }}
                                                                </span>
                                                                @if($payment->due_date && $payment->status === 'pending')
                                                                    <p class="text-xs text-gray-500 mt-1">
                                                                        Scadenza: {{ $payment->due_date->format('d/m/Y') }}
                                                                    </p>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="text-center py-12">
                                                <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                                </svg>
                                                <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun pagamento</h3>
                                                <p class="text-gray-600 mb-4">Non hai ancora effettuato pagamenti.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // PayPal payment function
        async function payWithPayPal(paymentId) {
            const button = event.target;
            const originalText = button.innerHTML;

            try {
                // Disable button and show loading state
                button.disabled = true;
                button.innerHTML = '<svg class="w-3 h-3 mr-1 animate-spin" fill="currentColor" viewBox="0 0 24 24"><path d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>Elaborazione...';

                // Create PayPal payment
                const response = await fetch(`/student/payments/${paymentId}/paypal`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });

                const data = await response.json();

                if (data.success && data.approval_url) {
                    // Redirect to PayPal for approval
                    window.location.href = data.approval_url;
                } else {
                    // Show error message
                    alert(data.message || 'Errore durante la creazione del pagamento PayPal');

                    // Restore button state
                    button.disabled = false;
                    button.innerHTML = originalText;
                }

            } catch (error) {
                console.error('PayPal payment error:', error);
                alert('Errore di rete. Riprova più tardi.');

                // Restore button state
                button.disabled = false;
                button.innerHTML = originalText;
            }
        }

        // Add PayPal buttons to all pending payments in the history
        document.addEventListener('DOMContentLoaded', function() {
            // Add PayPal buttons for pending payments in the main list
            const pendingPayments = document.querySelectorAll('[data-payment-status="pending"]');

            pendingPayments.forEach(payment => {
                const paymentId = payment.getAttribute('data-payment-id');
                if (paymentId && !payment.querySelector('.paypal-button')) {
                    const actionsDiv = payment.querySelector('.payment-actions') ||
                                      payment.appendChild(document.createElement('div'));

                    actionsDiv.classList.add('payment-actions', 'mt-2');

                    const paypalButton = document.createElement('button');
                    paypalButton.className = 'paypal-button inline-flex items-center px-3 py-1 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200';
                    paypalButton.onclick = () => payWithPayPal(paymentId);
                    paypalButton.innerHTML = `
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81 1.01 1.15 1.304 2.42 1.012 4.287-.023.143-.047.288-.077.437-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9l-1.12 7.106zm14.146-14.42a3.35 3.35 0 0 0-.607-.541c-.013.03-.026.06-.04.09-.983 5.05-4.349 6.797-8.647 6.797h-2.19c-.524 0-.968.382-1.05.9L7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944.901C5.026.382 5.474 0 5.998 0h7.46c2.57 0 4.578.543 5.69 1.81a4.154 4.154 0 0 1 .607.541c.013-.03.026-.06.04-.09.983-5.05 4.349-6.797 8.647-6.797h2.19c.524 0 .968-.382 1.05-.9L23.722.901C23.64.382 23.192 0 22.668 0h-7.46c-2.57 0-4.578.543-5.69 1.81z"/>
                        </svg>
                        Paga con PayPal
                    `;

                    actionsDiv.appendChild(paypalButton);
                }
            });
        });
    </script>
</x-app-layout>