<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Utenti
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Tutti gli utenti iscritti alla scuola
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
        <li class="text-gray-900 font-medium">Utenti</li>
    </x-slot>

<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
            <button onclick="openBulkActionsModal()"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Azioni Multiple
            </button>
            <a href="{{ route('admin.users.export') }}"
               class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Esporta CSV
            </a>
        </div>
    </div>

    <div class="space-y-6">
        <!-- Quick Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Totale Studenti</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['total'] ?? 0 }}</dd>
                            <dd class="text-sm text-gray-500">{{ ($stats['active'] ?? 0) }} attivi, {{ ($stats['inactive'] ?? 0) }} non attivi</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Nuove Iscrizioni</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['new_enrollments'] ?? 0 }}</dd>
                            <dd class="text-sm text-gray-500">Questo mese</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Pagamenti in Sospeso</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ $stats['pending_payments'] ?? 0 }}</dd>
                            <dd class="text-sm text-gray-500">Richieste di pagamento</dd>
                        </dl>
                    </div>
                </div>
            </div>

            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-5 w-0 flex-1">
                        <dl>
                            <dt class="text-sm font-medium text-gray-500 truncate">Tasso Attività</dt>
                            <dd class="text-lg font-medium text-gray-900">{{ ($stats['activity_rate'] ?? 0) }}%</dd>
                            <dd class="text-sm text-gray-500">Studenti attivi su totale</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters and Search -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Filtri e Ricerca</h3>
                <div class="flex items-center space-x-2">
                    <button onclick="resetFilters()" 
                            class="text-sm text-gray-500 hover:text-gray-700 underline">
                        Reset filtri
                    </button>
                </div>
            </div>
            
            <form method="GET" action="{{ route('admin.users.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <!-- Search -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                           placeholder="Cerca studenti...">
                </div>
                
                <!-- Status Filter -->
                <select name="status" 
                        class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option value="">Tutti gli stati</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Attivi</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Non attivi</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Sospesi</option>
                </select>
                
                <!-- Course Filter -->
                <select name="course" 
                        class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option value="">Tutti i corsi</option>
                    @foreach($courses ?? [] as $course)
                        <option value="{{ $course->id }}" {{ request('course') == $course->id ? 'selected' : '' }}>
                            {{ $course->title }}
                        </option>
                    @endforeach
                </select>
                
                <!-- Date Filter -->
                <select name="date_filter" 
                        class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option value="">Data iscrizione</option>
                    <option value="today" {{ request('date_filter') == 'today' ? 'selected' : '' }}>Oggi</option>
                    <option value="week" {{ request('date_filter') == 'week' ? 'selected' : '' }}>Ultima settimana</option>
                    <option value="month" {{ request('date_filter') == 'month' ? 'selected' : '' }}>Ultimo mese</option>
                    <option value="year" {{ request('date_filter') == 'year' ? 'selected' : '' }}>Ultimo anno</option>
                </select>
                
                <!-- Apply Filters -->
                <button type="submit" 
                        class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"/>
                    </svg>
                    Filtra
                </button>
            </form>
        </div>

        <!-- Students Table -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-semibold text-gray-900">
                            Lista Studenti ({{ $users->total() ?? 0 }})
                        </h3>
                        <div class="flex items-center space-x-2">
                            <input type="checkbox" 
                                   id="select-all"
                                   onchange="toggleSelectAll(this)"
                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <label for="select-all" class="text-sm text-gray-600">
                                Seleziona tutti
                            </label>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500">Vista:</span>
                        <div class="flex rounded-lg border border-gray-300">
                            <button class="px-3 py-1 text-sm bg-rose-50 text-rose-600 border-r border-gray-300 rounded-l-lg">
                                Tabella
                            </button>
                            <button class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-50 rounded-r-lg">
                                Cards
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/80">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Studente
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contatti
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Corsi Attivi
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stato
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Iscritto
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Azioni
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users ?? [] as $user)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="selected_users[]" 
                                               value="{{ $user->id }}"
                                               class="user-checkbox h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded mr-4">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @if($user->profile_image)
                                                <img class="h-10 w-10 rounded-full object-cover" 
                                                     src="{{ Storage::url($user->profile_image) }}" 
                                                     alt="{{ $user->name }}">
                                            @else
                                                <div class="h-10 w-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                <a href="{{ route('admin.users.show', $user) }}" 
                                                   class="hover:text-rose-600 transition-colors duration-200">
                                                    {{ $user->name }}
                                                </a>
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $user->date_of_birth ? 'Nato il ' . $user->date_of_birth->format('d/m/Y') : 'Data nascita non specificata' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $user->email }}</div>
                                    <div class="text-sm text-gray-500">{{ $user->phone ?? 'N/D' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-1">
                                        @forelse($user->activeEnrollments ?? [] as $enrollment)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                {{ $enrollment->course->title ?? 'Corso N/D' }}
                                            </span>
                                        @empty
                                            <span class="text-sm text-gray-500">Nessun corso attivo</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($user->status ?? 'active')
                                        @case('active')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3"/>
                                                </svg>
                                                Attivo
                                            </span>
                                            @break
                                        @case('inactive')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3"/>
                                                </svg>
                                                Non attivo
                                            </span>
                                            @break
                                        @case('suspended')
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3"/>
                                                </svg>
                                                Sospeso
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <svg class="w-2 h-2 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3"/>
                                                </svg>
                                                N/D
                                            </span>
                                    @endswitch
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/D' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('admin.users.show', $user) }}" 
                                           class="text-rose-600 hover:text-rose-900 transition-colors duration-200"
                                           title="Visualizza dettagli">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('admin.users.edit', $user) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                                           title="Modifica studente">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        <div class="relative">
                                            <button onclick="toggleDropdown({{ $user->id }})"
                                                    class="text-gray-400 hover:text-gray-600 transition-colors duration-200"
                                                    title="Altre azioni">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                </svg>
                                            </button>

                                            <div id="dropdown-{{ $user->id }}" style="display: none;"
                                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                                <div class="py-1">
                                                    @if($user->status !== 'active')
                                                        <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="flex items-center w-full px-4 py-2 text-sm text-green-700 hover:bg-green-50">
                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                                Attiva
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="inline">
                                                            @csrf
                                                            @method('PATCH')
                                                            <button type="submit" 
                                                                    class="flex items-center w-full px-4 py-2 text-sm text-orange-700 hover:bg-orange-50">
                                                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                                </svg>
                                                                Disattiva
                                                            </button>
                                                        </form>
                                                    @endif
                                                    <div class="border-t border-gray-100"></div>
                                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" 
                                                          onsubmit="return confirm('Sei sicuro di voler eliminare questo studente? Questa azione non può essere annullata.')"
                                                          class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="flex items-center w-full px-4 py-2 text-sm text-red-700 hover:bg-red-50">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                            Elimina
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun studente trovato</h3>
                                    <p class="mt-1 text-sm text-gray-500">
                                        Non ci sono studenti che corrispondono ai criteri di ricerca.
                                    </p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if(isset($users) && method_exists($users, 'links'))
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Actions Modal -->
    <div id="bulk-actions-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50" onclick="closeBulkActionsModal()">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" aria-hidden="true">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>
            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full" onclick="event.stopPropagation()">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Azioni Multiple</h3>
                        <button onclick="closeBulkActionsModal()"
                                class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
            
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <button onclick="bulkAction('activate')" 
                            class="flex items-center justify-center p-4 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 transition-colors duration-200">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Attiva Studenti
                    </button>
                    
                    <button onclick="bulkAction('deactivate')" 
                            class="flex items-center justify-center p-4 bg-orange-50 text-orange-700 rounded-lg hover:bg-orange-100 transition-colors duration-200">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Disattiva Studenti
                    </button>
                    
                    <button onclick="bulkAction('export')" 
                            class="flex items-center justify-center p-4 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Esporta Selezionati
                    </button>
                    
                    <button onclick="bulkAction('delete')" 
                            class="flex items-center justify-center p-4 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 transition-colors duration-200">
                        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Elimina Selezionati
                    </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</x-app-layout>

    @push('scripts')
    <script>
        function openBulkActionsModal() {
            document.getElementById('bulk-actions-modal').classList.remove('hidden');
        }

        function closeBulkActionsModal() {
            document.getElementById('bulk-actions-modal').classList.add('hidden');
        }

        function toggleDropdown(userId) {
            const dropdown = document.getElementById('dropdown-' + userId);
            if (dropdown.style.display === 'none') {
                dropdown.style.display = 'block';
            } else {
                dropdown.style.display = 'none';
            }
        }

        function toggleSelectAll(checkbox) {
            const userCheckboxes = document.querySelectorAll('.user-checkbox');
            userCheckboxes.forEach(cb => {
                cb.checked = checkbox.checked;
            });
        }

        function getSelectedUsers() {
            const selected = [];
            document.querySelectorAll('.user-checkbox:checked').forEach(cb => {
                selected.push(cb.value);
            });
            return selected;
        }

        function bulkAction(action) {
            const selected = getSelectedUsers();
            if (selected.length === 0) {
                alert('Seleziona almeno uno studente per continuare.');
                return;
            }

            let confirmMessage = '';
            let url = '';
            
            switch(action) {
                case 'activate':
                    confirmMessage = `Sei sicuro di voler attivare ${selected.length} studente/i?`;
                    url = '{{ route("admin.users.bulk-activate") }}';
                    break;
                case 'deactivate':
                    confirmMessage = `Sei sicuro di voler disattivare ${selected.length} studente/i?`;
                    url = '{{ route("admin.users.bulk-deactivate") }}';
                    break;
                case 'export':
                    // Direct export without confirmation
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.users.bulk-export") }}';
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfToken);
                    
                    selected.forEach(id => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'user_ids[]';
                        input.value = id;
                        form.appendChild(input);
                    });
                    
                    document.body.appendChild(form);
                    form.submit();
                    document.body.removeChild(form);
                    return;
                case 'delete':
                    confirmMessage = `ATTENZIONE: Sei sicuro di voler eliminare ${selected.length} studente/i? Questa azione non può essere annullata.`;
                    url = '{{ route("admin.users.bulk-delete") }}';
                    break;
            }

            if (confirm(confirmMessage)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = url;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);
                
                if (action === 'delete') {
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                }
                
                selected.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = id;
                    form.appendChild(input);
                });
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }

            // Close modal
            closeBulkActionsModal();
        }

        function resetFilters() {
            window.location.href = '{{ route("admin.users.index") }}';
        }
    </script>
    @endpush
</div>
