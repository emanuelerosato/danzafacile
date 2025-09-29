@vite('resources/js/admin/tickets/ticket-manager.js')

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Ticket
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestisci le richieste di supporto degli studenti
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="location.reload()" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Aggiorna
                </button>
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
        <li class="text-gray-900 font-medium">Ticket</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8" x-data="ticketManager()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <x-stats-card
                        title="Totale Ticket"
                        :value="number_format($stats['total'])"
                        subtitle="Tutti i ticket"
                        icon="clipboard-list"
                        color="blue"
                        :change="null"
                    />

                    <x-stats-card
                        title="Aperti"
                        :value="number_format($stats['open'])"
                        subtitle="Da gestire"
                        icon="exclamation-circle"
                        color="green"
                        :change="null"
                    />

                    <x-stats-card
                        title="In Attesa"
                        :value="number_format($stats['pending'])"
                        subtitle="In lavorazione"
                        icon="clock"
                        color="yellow"
                        :change="null"
                    />

                    <x-stats-card
                        title="Urgenti"
                        :value="number_format($stats['high_priority'])"
                        subtitle="Alta priorità"
                        icon="fire"
                        color="red"
                        :change="null"
                    />
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="border-b border-gray-200 pb-4 mb-6 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Filtri e Ricerca</h3>
                        <span x-show="hasActiveFilters" x-transition class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                            <span x-text="activeFiltersCount"></span> filtri attivi
                        </span>
                    </div>
                    <form id="filters-form" method="GET" action="{{ route('admin.tickets.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Ricerca</label>
                            <input type="text" id="search" name="search"
                                   x-model="filters.search"
                                   value="{{ request('search') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   placeholder="Titolo, descrizione...">
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                            <select id="status" name="status"
                                    x-model="filters.status"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Tutti</option>
                                @foreach($filterOptions['statuses'] as $key => $label)
                                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Priority -->
                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700 mb-1">Priorità</label>
                            <select id="priority" name="priority"
                                    x-model="filters.priority"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Tutte</option>
                                @foreach($filterOptions['priorities'] as $key => $label)
                                    <option value="{{ $key }}" {{ request('priority') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Category -->
                        <div>
                            <label for="category" class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                            <select id="category" name="category"
                                    x-model="filters.category"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                <option value="">Tutte</option>
                                @foreach($filterOptions['categories'] as $key => $label)
                                    <option value="{{ $key }}" {{ request('category') == $key ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Date Range -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Periodo</label>
                            <div class="flex space-x-2">
                                <input type="date" name="date_from"
                                       x-model="filters.date_from"
                                       value="{{ request('date_from') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                                <input type="date" name="date_to"
                                       x-model="filters.date_to"
                                       value="{{ request('date_to') }}"
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500">
                            </div>
                        </div>

                        <div class="md:col-span-5 flex justify-end space-x-3">
                            <button type="submit"
                                    class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200">
                                Applica Filtri
                            </button>
                            <a href="{{ route('admin.tickets.index') }}"
                               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Bulk Actions Toolbar -->
                <div x-show="hasSelection"
                     x-transition
                     class="bg-rose-50 border border-rose-200 rounded-lg p-4 flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-gray-900">
                            <span x-text="selectionCount"></span> ticket selezionati
                        </span>
                        <button @click="openBulkModal()"
                                class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-xs font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200">
                            Azioni Multiple
                        </button>
                        <button @click="selectedTickets = []"
                                class="inline-flex items-center px-3 py-1.5 bg-gray-600 text-white text-xs font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                            Deseleziona Tutto
                        </button>
                    </div>
                </div>

                <!-- Tickets Table -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Lista Ticket ({{ $tickets->total() }})</h3>
                        <label class="flex items-center space-x-2 cursor-pointer">
                            <input type="checkbox"
                                   @change="toggleAll()"
                                   :checked="allSelected"
                                   class="w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                            <span class="text-sm text-gray-600">Seleziona tutti</span>
                        </label>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left">
                                        <input type="checkbox"
                                               @change="toggleAll()"
                                               :checked="allSelected"
                                               class="w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Studente</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oggetto</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Categoria</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorità</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stato</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($tickets as $ticket)
                                <tr class="hover:bg-gray-50" :class="{ 'bg-rose-50': isSelected({{ $ticket->id }}) }">
                                    <td class="px-4 py-4 whitespace-nowrap">
                                        <input type="checkbox"
                                               @change="toggleTicket({{ $ticket->id }})"
                                               :checked="isSelected({{ $ticket->id }})"
                                               class="w-4 h-4 text-rose-600 border-gray-300 rounded focus:ring-rose-500">
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $ticket->id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gray-300 rounded-full mr-3 flex items-center justify-center">
                                                <span class="text-xs font-medium text-gray-600">
                                                    {{ strtoupper(substr($ticket->user->name, 0, 2)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $ticket->user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $ticket->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">{{ Str::limit($ticket->title, 40) }}</div>
                                        <div class="text-sm text-gray-500">{{ Str::limit($ticket->description, 60) }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            {{ $filterOptions['categories'][$ticket->category] ?? $ticket->category }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priority_color }}">
                                            {{ $filterOptions['priorities'][$ticket->priority] ?? $ticket->priority }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status_color }}">
                                            {{ $filterOptions['statuses'][$ticket->status] ?? $ticket->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->created_at->format('d/m/Y H:i') }}
                                        <div class="text-xs text-gray-400">{{ $ticket->time_ago }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.tickets.show', $ticket) }}"
                                           class="inline-flex items-center px-3 py-1.5 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-xs font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Vedi
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="px-6 py-12 text-center">
                                        <div class="text-gray-500">
                                            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                                            </svg>
                                            <p class="text-sm">Nessun ticket trovato</p>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($tickets->hasPages())
                    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                        {{ $tickets->links() }}
                    </div>
                    @endif
                </div>

                <!-- Bulk Actions Modal -->
                <div x-show="showBulkModal"
                     style="display: none"
                     x-transition
                     class="fixed inset-0 z-50 overflow-y-auto"
                     @click.self="closeBulkModal()">
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="closeBulkModal()"></div>

                        <div class="relative inline-block w-full max-w-md overflow-hidden text-left align-middle transition-all transform bg-white rounded-lg shadow-xl">
                            <div class="px-6 py-4 bg-gradient-to-r from-rose-50 to-pink-50 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Azioni Multiple</h3>
                                <p class="text-sm text-gray-600 mt-1">
                                    <span x-text="selectionCount"></span> ticket selezionati
                                </p>
                            </div>

                            <div class="px-6 py-4 space-y-4">
                                <!-- Select Action -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Seleziona Azione</label>
                                    <select x-model="bulkAction"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                        <option value="">-- Seleziona --</option>
                                        <option value="close">Chiudi Ticket</option>
                                        <option value="reopen">Riapri Ticket</option>
                                        <option value="assign">Assegna a Staff</option>
                                    </select>
                                </div>

                                <!-- Assign To (conditional) -->
                                <div x-show="bulkAction === 'assign'" x-transition>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Assegna a</label>
                                    <select x-model="assignedTo"
                                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                        <option value="">-- Seleziona Staff --</option>
                                        @php
                                            $staffMembers = \App\Models\User::where('school_id', Auth::user()->school_id)
                                                           ->where('role', 'admin')
                                                           ->get();
                                        @endphp
                                        @foreach($staffMembers as $staff)
                                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                                <button @click="closeBulkModal()"
                                        type="button"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                                    Annulla
                                </button>
                                <button @click="executeBulkAction()"
                                        :disabled="!bulkAction || isLoading"
                                        type="button"
                                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200">
                                    <svg x-show="isLoading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span x-text="isLoading ? 'Elaborazione...' : 'Esegui'"></span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>