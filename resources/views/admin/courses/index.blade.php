<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Corsi
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione corsi della tua scuola
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
        <li class="text-gray-900 font-medium">Corsi</li>
    </x-slot>



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Gestione Corsi</h1>
            <p class="text-gray-600">Tutti i corsi della tua scuola di danza</p>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
            <a href="{{ route('admin.courses.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuovo Corso
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Key Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stats-card
            title="Corsi Totali"
            :value="number_format($stats['total_courses'] ?? 0)"
            :subtitle="($stats['active_courses'] ?? 0) . ' attivi'"
            icon="academic-cap"
            color="blue"
            :change="5"
            changeType="increase"
        />

        <x-stats-card
            title="Prossimi Corsi"
            :value="number_format($stats['upcoming_courses'] ?? 0)"
            :subtitle="'In arrivo'"
            icon="clock"
            color="green"
            :change="12"
            changeType="increase"
        />

        <x-stats-card
            title="Iscrizioni"
            :value="number_format($stats['total_enrollments'] ?? 0)"
            :subtitle="'Totali'"
            icon="users"
            color="purple"
            :change="8"
            changeType="increase"
        />

        <x-stats-card
            title="Performance"
            :value="round($stats['total_enrollments'] > 0 ? ($stats['active_courses'] / $stats['total_courses']) * 100 : 0) . '%'"
            :subtitle="'Tasso attivazione'"
            icon="chart-bar"
            color="rose"
            :change="3"
            changeType="increase"
        />
    </div>

    <!-- Course List -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">I Tuoi Corsi ({{ $courses->total() ?? 0 }})</h3>
        </div>

        @if($courses->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($courses as $course)
                    <div class="p-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                                            {{ strtoupper(substr($course->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-medium text-gray-900 truncate">{{ $course->name }}</h4>
                                        <div class="flex items-center space-x-4 mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->level == 'beginner' ? 'bg-green-100 text-green-800' : ($course->level == 'intermediate' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($course->level) }}
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                €{{ number_format($course->price, 2) }} /mese
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                Max {{ $course->max_students }} studenti
                                            </span>
                                            @if($course->instructor)
                                                <span class="text-sm text-gray-500">
                                                    Istruttore: {{ $course->instructor->name }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($course->description)
                                            <p class="text-sm text-gray-600 mt-2 truncate">{{ $course->description }}</p>
                                        @endif
                                        <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                            <span>Inizio: {{ $course->start_date->format('d/m/Y') }}</span>
                                            @if($course->end_date)
                                                <span>Fine: {{ $course->end_date->format('d/m/Y') }}</span>
                                            @endif
                                            @if($course->location)
                                                <span>📍 {{ $course->location }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $course->active ? 'Attivo' : 'Non attivo' }}
                                </span>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.courses.edit', $course) }}"
                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Modifica
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('admin.courses.show', $course) }}"
                                       class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                        Dettagli
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(method_exists($courses, 'links'))
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $courses->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun corso</h3>
                <p class="mt-1 text-sm text-gray-500">Inizia creando il tuo primo corso.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.courses.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuovo Corso
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
</x-app-layout>
