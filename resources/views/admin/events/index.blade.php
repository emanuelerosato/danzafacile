@extends('layouts.admin')

@section('title', 'Gestione Eventi')

@section('content')
<div class="space-y-6" x-data="eventsManager">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestione Eventi</h1>
            <p class="text-gray-600">Tutti gli eventi della tua scuola di danza</p>
        </div>

        <div class="flex items-center space-x-3">
            <button @click="bulkActionModal = true"
                    x-show="selectedItems.length > 0"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Azioni Multiple (<span x-text="selectedItems.length"></span>)
            </button>

            <a href="{{ route('admin.events.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuovo Evento
            </a>
        </div>
    </div>

    <!-- Breadcrumbs -->
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-rose-600">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                    </svg>
                    Dashboard
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Eventi</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <!-- Total Events -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Eventi Totali</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_events'] }}</p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">{{ $stats['active_events'] }} attivi</p>
        </div>

        <!-- Upcoming Events -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Eventi in Arrivo</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['upcoming_events'] }}</p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Prossimi eventi</p>
        </div>

        <!-- Total Registrations -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Registrazioni</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total_registrations'] }}</p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Totale iscrizioni</p>
        </div>

        <!-- Active Events -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600">Eventi Attivi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active_events'] }}</p>
                </div>
                <div class="p-3 bg-orange-100 rounded-full">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>
                    </svg>
                </div>
            </div>
            <p class="mt-2 text-xs text-gray-500">Pubblicati e attivi</p>
        </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cerca</label>
                <input type="text"
                       id="search"
                       x-model="filters.search"
                       @input.debounce.300ms="applyFilters"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                       placeholder="Nome evento, tipo, location...">
            </div>

            <!-- Event Type Filter -->
            <div>
                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                <select id="type"
                        x-model="filters.type"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                    <option value="">Tutti i tipi</option>
                    @foreach($eventTypes as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                <select id="status"
                        x-model="filters.status"
                        @change="applyFilters"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                    <option value="">Tutti gli stati</option>
                    <option value="active">Attivo</option>
                    <option value="inactive">Non attivo</option>
                    <option value="upcoming">In arrivo</option>
                    <option value="past">Passati</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end space-x-2">
                <button @click="applyFilters"
                        class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    Filtra
                </button>

                <a href="{{ route('admin.events.export') }}"
                   class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Esporta
                </a>
            </div>
        </div>
    </div>

    <!-- Events Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <div id="events-table-container">
            @include('admin.events.partials.table', ['events' => $events])
        </div>
    </div>

    <!-- Bulk Action Modal -->
    <div x-show="bulkActionModal"
         x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50"
         @click.away="bulkActionModal = false">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Azioni Multiple</h3>

                <div class="space-y-3">
                    <button @click="performBulkAction('activate')"
                            class="w-full text-left px-4 py-2 text-sm text-green-700 hover:bg-green-50 rounded-lg transition-colors">
                        Attiva eventi selezionati
                    </button>
                    <button @click="performBulkAction('deactivate')"
                            class="w-full text-left px-4 py-2 text-sm text-orange-700 hover:bg-orange-50 rounded-lg transition-colors">
                        Disattiva eventi selezionati
                    </button>
                    <button @click="performBulkAction('export')"
                            class="w-full text-left px-4 py-2 text-sm text-blue-700 hover:bg-blue-50 rounded-lg transition-colors">
                        Esporta eventi selezionati
                    </button>
                    <button @click="if(confirm('Sei sicuro di voler eliminare gli eventi selezionati?')) performBulkAction('delete')"
                            class="w-full text-left px-4 py-2 text-sm text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                        Elimina eventi selezionati
                    </button>
                </div>

                <div class="mt-4 flex justify-end">
                    <button @click="bulkActionModal = false"
                            class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">
                        Annulla
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('eventsManager', () => ({
        selectedItems: [],
        bulkActionModal: false,
        filters: {
            search: '',
            type: '',
            status: ''
        },

        get allSelected() {
            const checkboxes = document.querySelectorAll('input[name="event_ids[]"]');
            return checkboxes.length > 0 && this.selectedItems.length === checkboxes.length;
        },

        toggleAll(checked) {
            const checkboxes = document.querySelectorAll('input[name="event_ids[]"]');
            this.selectedItems = [];

            if (checked) {
                checkboxes.forEach(checkbox => {
                    this.selectedItems.push(parseInt(checkbox.value));
                });
            }
        },

        toggleSelection(eventId, checked) {
            if (checked) {
                this.selectedItems.push(eventId);
            } else {
                const index = this.selectedItems.indexOf(eventId);
                if (index > -1) {
                    this.selectedItems.splice(index, 1);
                }
            }
        },

        async applyFilters() {
            try {
                const params = new URLSearchParams();

                Object.keys(this.filters).forEach(key => {
                    if (this.filters[key]) {
                        params.append(key, this.filters[key]);
                    }
                });

                const response = await fetch(`{{ route('admin.events.index') }}?${params.toString()}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    document.getElementById('events-table-container').innerHTML = data.data.html;
                    this.selectedItems = []; // Reset selection
                }
            } catch (error) {
                console.error('Error applying filters:', error);
            }
        },

        async performBulkAction(action) {
            if (this.selectedItems.length === 0) return;

            try {
                const response = await fetch('{{ route('admin.events.bulk-action') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        action: action,
                        event_ids: this.selectedItems
                    })
                });

                const data = await response.json();

                if (data.success) {
                    const event = new CustomEvent('show-toast', {
                        detail: { message: data.message, type: 'success' }
                    });
                    window.dispatchEvent(event);

                    // Refresh the table
                    if (action === 'export') {
                        // Handle export differently
                        window.location.href = data.export_url || '#';
                    } else {
                        this.applyFilters();
                    }

                    this.selectedItems = [];
                    this.bulkActionModal = false;
                } else {
                    const event = new CustomEvent('show-toast', {
                        detail: { message: data.message || 'Errore durante l\'operazione', type: 'error' }
                    });
                    window.dispatchEvent(event);
                }
            } catch (error) {
                console.error('Error:', error);
                const event = new CustomEvent('show-toast', {
                    detail: { message: 'Errore di connessione', type: 'error' }
                });
                window.dispatchEvent(event);
            }
        }
    }));
});

// Global functions for table actions
window.applyFilters = () => {
    if (typeof Alpine !== 'undefined' && Alpine.store) {
        // Trigger filter application
        document.querySelector('[x-data="eventsManager"]').__x.$data.applyFilters();
    }
};
</script>
@endsection