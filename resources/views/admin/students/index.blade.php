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



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <!-- Header rimosso: già presente in x-slot header -->
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
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

    <!-- Search and Filters -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="p-6">
            <form method="GET" action="{{ route('admin.students.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Search Input -->
                    <div class="md:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                            Cerca Studente
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text"
                                   name="search"
                                   id="search"
                                   value="{{ request('search') }}"
                                   placeholder="Nome, cognome, email, telefono..."
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                            @if(request('search'))
                                <a href="{{ route('admin.students.index') }}"
                                   class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Stato
                        </label>
                        <select name="status"
                                id="status"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                            <option value="">Tutti gli stati</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Attivi</option>
                            <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Non Attivi</option>
                        </select>
                    </div>
                </div>

                <!-- Advanced Filters (Optional - can be toggled) -->
                <div class="flex items-center justify-between pt-2">
                    <div class="text-sm text-gray-600">
                        @if(request()->hasAny(['search', 'status', 'sort', 'direction']))
                            <span class="inline-flex items-center">
                                Filtri attivi:
                                @if(request('search'))
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                        Ricerca: "{{ request('search') }}"
                                    </span>
                                @endif
                                @if(request('status'))
                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        {{ request('status') === 'active' ? 'Attivi' : 'Non Attivi' }}
                                    </span>
                                @endif
                            </span>
                        @endif
                    </div>

                    <div class="flex items-center space-x-3">
                        @if(request()->hasAny(['search', 'status', 'sort', 'direction']))
                            <a href="{{ route('admin.students.index') }}"
                               class="inline-flex items-center px-4 py-2 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Reset Filtri
                            </a>
                        @endif
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Cerca
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Students List -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h3 class="text-lg font-medium text-gray-900">Lista Studenti ({{ $students->total() ?? 0 }})</h3>

            <!-- Sort Options -->
            <div class="flex items-center space-x-2">
                <label for="sort" class="text-sm text-gray-600">Ordina per:</label>
                <select id="sort-select"
                        class="text-sm border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                        onchange="window.location.href = this.value">
                    <option value="{{ route('admin.students.index', array_merge(request()->all(), ['sort' => 'name', 'direction' => 'asc'])) }}"
                            {{ request('sort') === 'name' && request('direction') === 'asc' ? 'selected' : '' }}>
                        Nome (A-Z)
                    </option>
                    <option value="{{ route('admin.students.index', array_merge(request()->all(), ['sort' => 'name', 'direction' => 'desc'])) }}"
                            {{ request('sort') === 'name' && request('direction') === 'desc' ? 'selected' : '' }}>
                        Nome (Z-A)
                    </option>
                    <option value="{{ route('admin.students.index', array_merge(request()->all(), ['sort' => 'created_at', 'direction' => 'desc'])) }}"
                            {{ request('sort') === 'created_at' && request('direction') === 'desc' ? 'selected' : '' }}>
                        Più Recenti
                    </option>
                    <option value="{{ route('admin.students.index', array_merge(request()->all(), ['sort' => 'created_at', 'direction' => 'asc'])) }}"
                            {{ request('sort') === 'created_at' && request('direction') === 'asc' ? 'selected' : '' }}>
                        Meno Recenti
                    </option>
                </select>
            </div>
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
                            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $student->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $student->active ? 'Attivo' : 'Non attivo' }}
                                </span>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.students.edit', $student) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Modifica
                                    </a>
                                    <a href="{{ route('admin.students.show', $student) }}"
                                       class="inline-flex items-center px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-50 rounded-lg transition-colors">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Dettagli
                                    </a>
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
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuovo Studente
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

</x-app-layout>
