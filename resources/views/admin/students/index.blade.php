<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Studenti
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione studenti della tua scuola
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
        <li class="text-gray-900 font-medium">Studenti</li>
    </x-slot>



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestione Studenti - {{ $currentSchool->name }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Tutti gli studenti iscritti alla tua scuola
            </p>
        </div>
        <div class="flex items-center space-x-3">
            <button disabled title="Funzione in sviluppo"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                </svg>
                Importa
            </button>
            <a href="{{ route('admin.students.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuovo Studente
            </a>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stats-card
            title="Totale Studenti"
            :value="number_format($stats['total_students'] ?? 0)"
            :subtitle="($stats['active_students'] ?? 0) . ' attivi'"
            icon="users"
            color="blue"
            :change="12"
            changeType="increase"
        />

        <x-stats-card
            title="Studenti Attivi"
            :value="number_format($stats['active_students'] ?? 0)"
            :subtitle="'Attualmente attivi'"
            icon="check"
            color="green"
            :change="5"
            changeType="increase"
        />

        <x-stats-card
            title="Nuovi Questo Mese"
            :value="number_format($stats['new_this_month'] ?? 0)"
            :subtitle="'Registrazioni recenti'"
            icon="plus"
            color="purple"
            :change="18"
            changeType="increase"
        />

        <x-stats-card
            title="Con Iscrizioni"
            :value="number_format($stats['with_enrollments'] ?? 0)"
            :subtitle="'Iscritti a corsi'"
            icon="clipboard-check"
            color="rose"
            :change="7"
            changeType="increase"
        />
    </div>

    <!-- Students List -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Lista Studenti ({{ $students->total() ?? 0 }})</h3>
        </div>

        @if($students->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($students as $student)
                    <div class="p-6 hover:bg-gray-50">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-r from-rose-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                            {{ strtoupper(substr($student->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-medium text-gray-900 truncate">{{ $student->name }}</h4>
                                        <div class="flex items-center space-x-4 mt-1">
                                            <span class="text-sm text-gray-500">{{ $student->email }}</span>
                                            @if($student->phone)
                                                <span class="text-sm text-gray-500">📞 {{ $student->phone }}</span>
                                            @endif
                                        </div>
                                        @if($student->date_of_birth)
                                            <p class="text-sm text-gray-600 mt-2">
                                                Nato il: {{ $student->date_of_birth->format('d/m/Y') }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $student->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $student->active ? 'Attivo' : 'Non attivo' }}
                                </span>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.students.edit', $student) }}"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Modifica
                                    </button>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('admin.students.show', $student) }}"
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
            @if(method_exists($students, 'links'))
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $students->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuno studente</h3>
                <p class="mt-1 text-sm text-gray-500">Inizia aggiungendo il primo studente alla tua scuola.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.students.create') }}"
                            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuovo Studente
                    </button>
                </div>
            </div>
        @endif
    </div>
</div>

</x-app-layout>
