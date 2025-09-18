<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Iscrizioni
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione iscrizioni della tua scuola
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
        <li class="text-gray-900 font-medium">Iscrizioni</li>
    </x-slot>



<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestione Iscrizioni - {{ $currentSchool->name ?? 'Scuola' }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Tutte le iscrizioni ai corsi della scuola
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="alert('Azioni multiple in sviluppo')"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Azioni Multiple
            </button>
            <button onclick="alert('Nuova iscrizione in sviluppo')"
                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuova Iscrizione
            </button>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stats-card
            title="Totale Iscrizioni"
            :value="number_format($stats['total_enrollments'] ?? 0)"
            :subtitle="($stats['active_enrollments'] ?? 0) . ' attive'"
            icon="clipboard-list"
            color="blue"
            :change="15"
            changeType="increase"
        />

        <x-stats-card
            title="Iscrizioni Attive"
            :value="number_format($stats['active_enrollments'] ?? 0)"
            :subtitle="'In corso'"
            icon="check"
            color="green"
            :change="8"
            changeType="increase"
        />

        <x-stats-card
            title="In Attesa"
            :value="number_format($stats['pending_enrollments'] ?? 0)"
            :subtitle="'Da confermare'"
            icon="clock"
            color="yellow"
            :change="3"
            changeType="increase"
        />

        <x-stats-card
            title="Ricavi Mensili"
            :value="'€' . number_format($stats['monthly_revenue'] ?? 0, 2)"
            :subtitle="'Questo mese'"
            icon="currency-dollar"
            color="purple"
            :change="22"
            changeType="increase"
        />
    </div>

    <!-- Enrollments List -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Lista Iscrizioni ({{ $enrollments->total() ?? 0 }})</h3>
        </div>

        @if($enrollments->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($enrollments as $enrollment)
                    <div class="p-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                            {{ strtoupper(substr($enrollment->user->name ?? 'N/A', 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-medium text-gray-900 truncate">
                                            {{ $enrollment->user->name ?? 'Studente N/A' }}
                                        </h4>
                                        <div class="flex items-center space-x-4 mt-1">
                                            <span class="text-sm text-gray-500">
                                                Corso: {{ $enrollment->course->name ?? 'N/A' }}
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                Iscritto il: {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}
                                            </span>
                                        </div>
                                        @if($enrollment->notes)
                                            <p class="text-sm text-gray-600 mt-2 truncate">{{ $enrollment->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $enrollment->status == 'active' ? 'bg-green-100 text-green-800' : ($enrollment->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($enrollment->status ?? 'Unknown') }}
                                </span>
                                <div class="flex items-center space-x-2">
                                    <button onclick="alert('Modifica iscrizione in sviluppo')"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Modifica
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <button onclick="alert('Vista dettaglio in sviluppo')"
                                            class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                        Dettagli
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(method_exists($enrollments, 'links'))
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $enrollments->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuna iscrizione</h3>
                <p class="mt-1 text-sm text-gray-500">Inizia aggiungendo la prima iscrizione.</p>
                <div class="mt-6">
                    <button onclick="alert('Nuova iscrizione in sviluppo')"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuova Iscrizione
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>
</x-app-layout>
