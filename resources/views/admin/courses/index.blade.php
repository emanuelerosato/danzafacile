@extends('layouts.app')

@section('title', 'Gestione Corsi')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestione Corsi</h1>
            <p class="text-gray-600">Tutti i corsi della tua scuola di danza</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="alert('Funzionalità in sviluppo')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Azioni Multiple
            </button>
            <a href="{{ route('admin.courses.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuovo Corso
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-stats-card
                title="Totale Corsi"
                :value="$stats['total_courses'] ?? 0"
                icon="academic-cap"
                color="rose"
                :subtitle="($stats['active_courses'] ?? 0) . ' attivi'"
            />

            <x-stats-card
                title="Corsi Attivi"
                :value="$stats['active_courses'] ?? 0"
                icon="users"
                color="purple"
                subtitle="In questo momento"
            />

            <x-stats-card
                title="Corsi in Arrivo"
                :value="$stats['upcoming_courses'] ?? 0"
                icon="currency-dollar"
                color="green"
                subtitle="Prossimi ad iniziare"
            />

            <x-stats-card
                title="Iscrizioni Totali"
                :value="$stats['total_enrollments'] ?? 0"
                icon="chart-bar"
                color="blue"
                subtitle="Tutti i corsi"
            />
        </div>

        <!-- Course List -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">I Tuoi Corsi</h3>
            </div>

            @if($courses->count() > 0)
                <div class="divide-y divide-gray-200">
                    @foreach($courses as $course)
                        <div class="p-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="text-lg font-medium text-gray-900">{{ $course->name }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ $course->description ?? 'Nessuna descrizione' }}</p>
                                    <div class="mt-2 flex items-center space-x-4 text-sm text-gray-500">
                                        <span>Livello: {{ $course->level }}</span>
                                        @if($course->instructor)
                                            <span>Istruttore: {{ $course->instructor->name }}</span>
                                        @endif
                                        <span>Prezzo: €{{ number_format($course->price, 2) }}</span>
                                        <span>Iscritti: {{ $course->enrollments->count() }}/{{ $course->max_students ?? 'Illimitati' }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $course->active ? 'Attivo' : 'Inattivo' }}
                                    </span>
                                    <a href="{{ route('admin.courses.show', $course) }}"
                                       class="inline-flex items-center px-3 py-1 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                        Visualizza
                                    </a>
                                    <a href="{{ route('admin.courses.edit', $course) }}"
                                       class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700">
                                        Modifica
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $courses->links() }}
                </div>
            @else
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun corso trovato</h3>
                    <p class="mt-1 text-sm text-gray-500">Inizia creando il tuo primo corso.</p>
                    <div class="mt-6">
                        <a href="{{ route('admin.courses.create') }}"
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-rose-600 hover:bg-rose-700">
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
@endsection