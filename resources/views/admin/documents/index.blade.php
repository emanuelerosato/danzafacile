<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Documenti
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione documenti della tua scuola
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
        <li class="text-gray-900 font-medium">Documenti</li>
    </x-slot>




<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Gestione Documenti</h1>
                <p class="text-gray-600">Tutti i documenti della tua scuola</p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                <a href="{{ route('admin.documents.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Carica Documento
                </a>
            </div>
        </div>
        <!-- Key Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-stats-card
                title="Totale Documenti"
                :value="number_format($statistics['total'] ?? 0)"
                :subtitle="($statistics['approved'] ?? 0) . ' approvati'"
                icon="document"
                color="blue"
                :change="$statistics['pending'] ?? 0"
                changeType="increase"
            />

            <x-stats-card
                title="In Attesa"
                :value="number_format($statistics['pending'] ?? 0)"
                :subtitle="'Da approvare'"
                icon="clock"
                color="yellow"
                :change="5"
                changeType="increase"
            />

            <x-stats-card
                title="Approvati"
                :value="number_format($statistics['approved'] ?? 0)"
                :subtitle="'Attualmente approvati'"
                icon="check"
                color="green"
                :change="7"
                changeType="increase"
            />

            <x-stats-card
                title="Spazio Occupato"
                :value="$statistics['total_size'] ?? '0 B'"
                :subtitle="'Storage utilizzato'"
                icon="folder"
                color="purple"
                :change="12"
                changeType="increase"
            />
        </div>

        <!-- Filters and Search -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
            <form method="GET" action="{{ route('admin.documents.index') }}"
                  x-data="documentsFilters()"
                  x-init="initFilters()"
                  class="grid grid-cols-1 md:grid-cols-4 gap-4">

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ricerca</label>
                    <div class="relative">
                        <input type="text"
                               name="search"
                               x-model="filters.search"
                               placeholder="Titolo, descrizione, nome file..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                    <select name="status" x-model="filters.status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="">Tutti gli stati</option>
                        <option value="pending">In Attesa</option>
                        <option value="approved">Approvato</option>
                        <option value="rejected">Rifiutato</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Categoria</label>
                    <select name="category" x-model="filters.category"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="">Tutte le categorie</option>
                        @foreach(App\Models\Document::getAvailableCategories() as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="flex-1 bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filtra
                    </button>
                    <a href="{{ route('admin.documents.index') }}"
                       class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Documents Table -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Documenti</h3>
                    <div class="flex items-center space-x-2">
                        <!-- Bulk Actions -->
                        <div x-data="{ showBulkActions: false, selectedDocs: [] }"
                             x-show="selectedDocs.length > 0"
                             x-cloak
                             class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600" x-text="selectedDocs.length + ' selezionati'"></span>
                            <div class="relative">
                                <button @click="showBulkActions = !showBulkActions"
                                        class="px-3 py-1.5 text-sm bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200">
                                    Azioni
                                    <svg class="w-4 h-4 ml-1 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m19 9-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="showBulkActions"
                                     @click.away="showBulkActions = false"
                                     x-cloak
                                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg border border-gray-100 z-10">
                                    <div class="py-1">
                                        <button onclick="bulkAction('approve')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <svg class="w-4 h-4 inline mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Approva Selezionati
                                        </button>
                                        <button onclick="bulkAction('reject')" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            <svg class="w-4 h-4 inline mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Rifiuta Selezionati
                                        </button>
                                        <div class="border-t border-gray-100"></div>
                                        <button onclick="bulkAction('delete')" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            Elimina Selezionati
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="documents-table-container">
                @include('admin.documents.partials.documents-table', compact('documents'))
            </div>
        </div>
    </div>
</div>

@include('admin.documents.partials.modals')

@push('scripts')
<script>
function documentsFilters() {
    return {
        filters: {
            search: '{{ request('search') }}',
            status: '{{ request('status') }}',
            category: '{{ request('category') }}'
        },

        initFilters() {
            // Auto-submit form on select changes
            this.$watch('filters.status', () => this.submitFilters());
            this.$watch('filters.category', () => this.submitFilters());
        },

        submitFilters() {
            this.$el.submit();
        }
    }
}

function bulkAction(action) {
    const checkboxes = document.querySelectorAll('input[name="document_ids[]"]:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        alert('Seleziona almeno un documento');
        return;
    }

    let message = '';
    switch(action) {
        case 'approve':
            message = `Sei sicuro di voler approvare ${ids.length} documento/i?`;
            break;
        case 'reject':
            const reason = prompt('Inserisci il motivo del rifiuto:');
            if (!reason) return;
            message = `Sei sicuro di voler rifiutare ${ids.length} documento/i?`;
            break;
        case 'delete':
            message = `Sei sicuro di voler eliminare ${ids.length} documento/i? Questa azione non può essere annullata.`;
            break;
    }

    if (confirm(message)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.documents.bulk-action") }}';

        // CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);

        // Action
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
        form.appendChild(actionInput);

        // Document IDs
        ids.forEach(id => {
            const idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'documents[]';
            idInput.value = id;
            form.appendChild(idInput);
        });

        // Rejection reason for reject action
        if (action === 'reject') {
            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'rejection_reason';
            reasonInput.value = reason;
            form.appendChild(reasonInput);
        }

        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endpush

</x-app-layout>
