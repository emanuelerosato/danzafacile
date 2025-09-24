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



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8" x-data="enrollmentManager()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestione Iscrizioni - {{ $currentSchool->name ?? 'Scuola' }}
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Tutte le iscrizioni ai corsi della scuola
            </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
            <button disabled title="Funzione in sviluppo"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                Azioni Multiple
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

    <!-- Filters Bar (Nuova funzionalità) -->
    <div x-data="enrollmentFilters()" class="bg-white rounded-lg shadow mb-6 p-4">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
            <!-- Search -->
            <div class="flex-1 max-w-lg">
                <div class="relative">
                    <input x-model="filters.search"
                           @input="debounceFilter()"
                           type="text"
                           placeholder="Cerca studente o corso..."
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Filters -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center space-y-2 sm:space-y-0 sm:space-x-3">
                <!-- Status Filter -->
                <select x-model="filters.status"
                        @change="applyFilters()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    <option value="">Tutti gli stati</option>
                    <option value="active">Attivo</option>
                    <option value="pending">In Attesa</option>
                    <option value="cancelled">Cancellato</option>
                </select>

                <!-- Course Filter (Sarà popolato dinamicamente se necessario) -->
                <select x-model="filters.course_id"
                        @change="applyFilters()"
                        class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    <option value="">Tutti i corsi</option>
                    {{-- Popolato dinamicamente o via backend --}}
                </select>

                <!-- Export Button -->
                <button @click="exportData()"
                        :disabled="loading"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="!loading">Esporta</span>
                    <span x-show="loading">Esportando...</span>
                </button>

                <!-- Reset Filters -->
                <button @click="resetFilters()"
                        class="inline-flex items-center px-3 py-2 bg-gray-500 text-white text-sm font-medium rounded-lg hover:bg-gray-600 transition-colors duration-200">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Panel (Nuovo - appare solo con selezioni) -->
    <div x-show="selectedIds.length > 0"
         x-transition
         class="bulk-actions-panel bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <span class="text-sm font-medium text-blue-900">
                    <span x-text="selectedIds.length"></span> iscrizione/i selezionate
                </span>
                <button @click="clearSelection()"
                        class="text-sm text-blue-700 hover:text-blue-900 hover:underline">
                    Deseleziona tutto
                </button>
            </div>

            <div class="flex items-center space-x-2">
                <select x-model="bulkAction"
                        class="text-sm border border-blue-300 rounded px-3 py-1 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">Seleziona azione...</option>
                    <option value="cancel">Cancella selezionate</option>
                    <option value="reactivate">Riattiva selezionate</option>
                    <option value="delete">Elimina selezionate</option>
                    <option value="export">Esporta selezionate</option>
                </select>

                <button @click="executeBulkAction()"
                        :disabled="!bulkAction || processing"
                        class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-700 disabled:opacity-50 transition-colors duration-200">
                    <svg x-show="processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span x-show="!processing">Esegui</span>
                    <span x-show="processing">Elaborazione...</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Enrollments List -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Checkbox Seleziona Tutto (Nuovo) -->
                    <div class="flex items-center">
                        <input x-bind:checked="allSelected"
                               @change="toggleAll($event.target.checked)"
                               type="checkbox"
                               id="select-all-checkbox"
                               class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                        <label class="ml-2 text-sm text-gray-700">
                            Seleziona tutto
                        </label>
                    </div>
                    <div class="h-4 border-l border-gray-300"></div>
                    <h3 class="text-lg font-medium text-gray-900">
                        Lista Iscrizioni ({{ $enrollments->total() ?? 0 }})
                    </h3>
                </div>
            </div>
        </div>

        @if($enrollments->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($enrollments as $enrollment)
                    <div class="p-6 hover:bg-gray-50"
                         :class="{ 'bg-blue-50': selectedIds && selectedIds.includes({{ $enrollment->id }}) }"
                         data-enrollment-id="{{ $enrollment->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <!-- Checkbox Selezione (Nuovo) -->
                                <div class="flex-shrink-0">
                                    <input @change="toggleSelection({{ $enrollment->id }}, $event.target.checked)"
                                           :checked="selectedIds && selectedIds.includes({{ $enrollment->id }})"
                                           type="checkbox"
                                           class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                </div>

                                <!-- Avatar (Preservato) -->
                                <div class="flex-shrink-0">
                                    <div class="w-12 h-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                        {{ strtoupper(substr($enrollment->user->name ?? 'N/A', 0, 2)) }}
                                    </div>
                                </div>

                                <!-- Info Enrollment (Preservato) -->
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

                            <!-- Actions (Espanse) -->
                            <div class="flex items-center space-x-3">
                                <!-- Status Badge (Preservato) -->
                                <span class="status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $enrollment->status == 'active' ? 'bg-green-100 text-green-800' : ($enrollment->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst($enrollment->status ?? 'Unknown') }}
                                </span>

                                <!-- Action Buttons (Espanse) -->
                                <div class="flex items-center space-x-2">
                                    <!-- Dettagli (Preservato) -->
                                    <a href="{{ route('admin.enrollments.show', $enrollment) }}"
                                       class="text-blue-600 hover:text-blue-900 p-2 rounded-full hover:bg-blue-100 transition-colors duration-200"
                                       title="Visualizza dettagli">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>

                                    <!-- Toggle Status (Migliorato) -->
                                    @if($enrollment->status !== 'cancelled')
                                        <button data-enrollment-action="toggle-status"
                                                data-enrollment-id="{{ $enrollment->id }}"
                                                class="text-orange-600 hover:text-orange-900 p-2 rounded-full hover:bg-orange-100 transition-colors duration-200"
                                                title="🔸 Sospendi iscrizione (reversibile)">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    @else
                                        <button data-enrollment-action="toggle-status"
                                                data-enrollment-id="{{ $enrollment->id }}"
                                                class="text-green-600 hover:text-green-900 p-2 rounded-full hover:bg-green-100 transition-colors duration-200"
                                                title="✅ Riattiva iscrizione">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- Delete (Migliorato) -->
                                    <button data-enrollment-action="delete"
                                            data-enrollment-id="{{ $enrollment->id }}"
                                            class="text-red-700 hover:text-red-900 p-2 rounded-full hover:bg-red-100 transition-colors duration-200"
                                            title="⚠️ Elimina DEFINITIVAMENTE (non reversibile!)">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
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
                    <a href="{{ route('admin.students.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700">
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

{{-- JavaScript per funzionalità moderne (non-intrusivo) --}}
@vite('resources/js/admin/enrollments/enrollment-manager.js')

<script>
// Expose data to JavaScript (preserva funzionalità esistenti)
window.enrollmentsData = @json($enrollments->items() ?? []);
console.log('📋 Enrollment index loaded with', (window.enrollmentsData || []).length, 'enrollments');

// Alpine.js component for filters (preserva URL filtering esistente)
function enrollmentFilters() {
    return {
        filters: {
            search: '{{ request('search', '') }}',
            status: '{{ request('status', '') }}',
            course_id: '{{ request('course_id', '') }}'
        },
        loading: false,
        debounceTimeout: null,

        // Inizializzazione - popolamento filtri da URL esistenti
        init() {
            console.log('🔍 Filters initialized with:', this.filters);
            this.populateFromURL();
        },

        // Preserva sistema di filtri via URL già esistente nel backend
        populateFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            this.filters.search = urlParams.get('search') || '';
            this.filters.status = urlParams.get('status') || '';
            this.filters.course_id = urlParams.get('course_id') || '';
        },

        // Debounce per search (evita troppe richieste)
        debounceFilter() {
            clearTimeout(this.debounceTimeout);
            this.debounceTimeout = setTimeout(() => {
                this.applyFilters();
            }, 500);
        },

        // Applica filtri (usa sistema URL esistente del backend)
        applyFilters() {
            const params = new URLSearchParams();

            // Costruisce URL con parametri (sistema già supportato dal backend)
            if (this.filters.search.trim()) params.append('search', this.filters.search.trim());
            if (this.filters.status) params.append('status', this.filters.status);
            if (this.filters.course_id) params.append('course_id', this.filters.course_id);

            // Reindirizza con nuovi parametri (preserva paginazione backend)
            const url = new URL(window.location);
            url.search = params.toString();

            console.log('🔄 Applying filters:', url.toString());
            window.location.href = url.toString();
        },

        // Reset filtri
        resetFilters() {
            console.log('🔄 Resetting filters');
            window.location.href = window.location.pathname;
        },

        // Export data (utilizza API backend esistente)
        async exportData() {
            if (!window.enrollmentManager?.apiService) {
                console.warn('⚠️ API service not available');
                return;
            }

            this.loading = true;
            try {
                const result = await window.enrollmentManager.apiService.export(this.filters);
                if (result && result.success) {
                    window.enrollmentManager.notification.showSuccess(result.message);
                } else {
                    window.enrollmentManager.notification.showError(result.message || 'Errore durante export');
                }
            } catch (error) {
                console.error('Export error:', error);
                window.enrollmentManager.notification.showError('Errore durante l\'export');
            } finally {
                this.loading = false;
            }
        }
    }
}

// Alpine.js Component: Enrollment Manager (Unified Component)
function enrollmentManager() {
    return {
        selectedIds: [],
        bulkAction: '',
        processing: false,

        get allSelected() {
            const totalRows = document.querySelectorAll('[data-enrollment-id]').length;
            return this.selectedIds.length === totalRows && totalRows > 0;
        },

        // Inizializzazione
        init() {
            console.log('🎯 Alpine.js EnrollmentManager initialized');

            // Aggiunge event listener per pulsanti azione
            this.bindActionButtons();

            // Aggiorna quando il BulkActionManager JS è pronto
            this.$nextTick(() => {
                if (window.enrollmentBulkManager) {
                    // Sincronizza con il manager JavaScript
                    setInterval(() => {
                        const jsSelectedIds = window.enrollmentBulkManager.getSelectedIds();
                        if (JSON.stringify(this.selectedIds) !== JSON.stringify(jsSelectedIds)) {
                            this.selectedIds = [...jsSelectedIds];
                        }
                    }, 100);
                }
            });
        },

        // Bind event listener per i pulsanti di azione
        bindActionButtons() {
            document.addEventListener('click', (event) => {
                const button = event.target.closest('[data-enrollment-action]');
                if (!button) return;

                event.preventDefault();
                const action = button.dataset.enrollmentAction;
                const enrollmentId = button.dataset.enrollmentId;

                console.log('🎯 Action button clicked:', action, enrollmentId);

                switch (action) {
                    case 'toggle-status':
                        this.toggleEnrollmentStatus(parseInt(enrollmentId));
                        break;
                    case 'delete':
                        this.deleteEnrollment(parseInt(enrollmentId));
                        break;
                    default:
                        console.warn('⚠️ Unknown action:', action);
                }
            });
        },

        // Toggle selezione di un elemento
        toggleSelection(enrollmentId, isSelected) {
            if (isSelected) {
                if (!this.selectedIds.includes(enrollmentId)) {
                    this.selectedIds.push(enrollmentId);
                }
            } else {
                this.selectedIds = this.selectedIds.filter(id => id !== enrollmentId);
            }

            // Delega al manager JavaScript se disponibile
            if (window.enrollmentBulkManager) {
                window.enrollmentBulkManager.toggleSelection(enrollmentId, isSelected);
            }

            console.log('🎯 Selection updated:', this.selectedIds);
        },

        // Toggle select all
        toggleAll(selectAll) {
            const enrollmentRows = document.querySelectorAll('[data-enrollment-id]');

            if (selectAll) {
                this.selectedIds = Array.from(enrollmentRows).map(row =>
                    parseInt(row.getAttribute('data-enrollment-id'))
                );

                // Aggiorna checkboxes individuali
                enrollmentRows.forEach(row => {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = true;
                });
            } else {
                this.selectedIds = [];

                // Aggiorna checkboxes individuali
                enrollmentRows.forEach(row => {
                    const checkbox = row.querySelector('input[type="checkbox"]');
                    if (checkbox) checkbox.checked = false;
                });
            }

            // Delega al manager JavaScript se disponibile
            if (window.enrollmentBulkManager) {
                window.enrollmentBulkManager.toggleSelectAll(selectAll);
            }

            console.log('🎯 Select all toggled:', selectAll, this.selectedIds);
        },

        // Esegue azione bulk
        async executeBulkAction() {
            if (!this.bulkAction || !this.selectedIds.length) {
                console.warn('⚠️ No action or selection');
                return;
            }

            this.processing = true;

            // Delega al manager JavaScript se disponibile
            if (window.enrollmentBulkManager) {
                const success = await window.enrollmentBulkManager.executeBulkAction(this.bulkAction);
                if (success) {
                    this.bulkAction = '';
                    this.selectedIds = [];
                }
                this.processing = false;
                return;
            }

            // Fallback se manager JavaScript non disponibile
            if (!window.enrollmentManager?.apiService) {
                console.error('❌ API service not available');
                this.processing = false;
                return;
            }

            try {
                const result = await window.enrollmentManager.apiService.bulkAction({
                    action: this.bulkAction,
                    ids: this.selectedIds
                });

                if (result.success) {
                    window.enrollmentManager.notification.showSuccess(result.message);
                    this.selectedIds = [];
                    this.bulkAction = '';
                    setTimeout(() => location.reload(), 1500);
                } else {
                    window.enrollmentManager.notification.showError(result.message);
                }
            } catch (error) {
                console.error('Bulk action error:', error);
                window.enrollmentManager.notification.showError('Errore durante l\'operazione');
            } finally {
                this.processing = false;
            }
        },

        // Controlla se un elemento è selezionato
        isSelected(enrollmentId) {
            return this.selectedIds.includes(enrollmentId);
        },

        // Toggle status di un enrollment
        async toggleEnrollmentStatus(enrollmentId) {
            console.log('🔄 Toggle status for enrollment:', enrollmentId);

            // Ottieni CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('❌ CSRF token not found');
                return;
            }

            // Determina status attuale dalla UI
            const row = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
            const statusBadge = row?.querySelector('.status-badge');
            const currentStatus = statusBadge?.textContent.toLowerCase().trim();

            const statusMap = {
                'attivo': 'active',
                'cancellato': 'cancelled',
                'in attesa': 'pending'
            };
            const mappedStatus = statusMap[currentStatus] || 'active';

            // Determina azione e nuovo status
            const action = mappedStatus === 'cancelled' ? 'reactivate' : 'cancel';
            const newStatus = mappedStatus === 'cancelled' ? 'active' : 'cancelled';

            // Conferma azione
            const actionMessages = {
                'cancel': '🔸 Sospendere questa iscrizione?\n\n• L\'iscrizione sarà temporaneamente sospesa\n• Può essere riattivata in qualsiasi momento\n• I dati rimangono salvati',
                'reactivate': '✅ Riattivare questa iscrizione?\n\n• L\'iscrizione tornerà attiva\n• Lo studente potrà accedere nuovamente al corso'
            };

            if (!confirm(actionMessages[action])) {
                return;
            }

            try {
                const response = await fetch(`/admin/enrollments/${enrollmentId}/${action}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    // Mostra messaggio di successo
                    this.showNotification(result.message || 'Status aggiornato con successo', 'success');

                    // Aggiorna UI
                    this.updateStatusInUI(enrollmentId, newStatus);
                } else {
                    this.showNotification(result.message || 'Errore durante l\'aggiornamento', 'error');
                }
            } catch (error) {
                console.error('❌ Toggle status error:', error);
                this.showNotification('Errore di connessione', 'error');
            }
        },

        // Elimina enrollment
        async deleteEnrollment(enrollmentId) {
            console.log('🗑️ Delete enrollment:', enrollmentId);

            // Conferma eliminazione
            const enrollmentRow = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
            const studentName = enrollmentRow?.querySelector('h4')?.textContent || 'questo studente';

            if (!confirm(`⚠️ ELIMINAZIONE DEFINITIVA\n\nEliminare l'iscrizione di ${studentName}?\n\n• L'iscrizione sarà CANCELLATA per sempre\n• Tutti i dati associati andranno persi\n• NON È POSSIBILE ANNULLARE questa operazione\n\nSei SICURO di voler procedere?`)) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('❌ CSRF token not found');
                return;
            }

            try {
                const response = await fetch(`/admin/enrollments/${enrollmentId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                });

                const result = await response.json();

                if (result.success) {
                    this.showNotification(result.message || 'Iscrizione eliminata con successo', 'success');

                    // Rimuovi dalla UI
                    const row = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
                    if (row) {
                        row.remove();
                    }

                    // Ricarica dopo 2 secondi per aggiornare contatori
                    setTimeout(() => location.reload(), 2000);
                } else {
                    this.showNotification(result.message || 'Errore durante l\'eliminazione', 'error');
                }
            } catch (error) {
                console.error('❌ Delete error:', error);
                this.showNotification('Errore di connessione durante l\'eliminazione', 'error');
            }
        },

        // Aggiorna status nella UI
        updateStatusInUI(enrollmentId, newStatus) {
            const row = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
            if (!row) return;

            const statusBadge = row.querySelector('.status-badge');
            if (statusBadge) {
                // Rimuovi classi esistenti
                statusBadge.className = statusBadge.className.replace(/(bg-\w+-100|text-\w+-800)/g, '');

                // Aggiungi nuove classi
                const statusClasses = {
                    'active': 'bg-green-100 text-green-800',
                    'cancelled': 'bg-red-100 text-red-800',
                    'pending': 'bg-yellow-100 text-yellow-800'
                };

                statusBadge.className += ` ${statusClasses[newStatus] || 'bg-gray-100 text-gray-800'}`;
                statusBadge.textContent = {
                    'active': 'Attivo',
                    'cancelled': 'Cancellato',
                    'pending': 'In Attesa'
                }[newStatus] || newStatus;
            }

            // Aggiorna pulsante toggle
            const toggleButton = row.querySelector('[data-enrollment-action="toggle-status"]');
            if (toggleButton) {
                if (newStatus === 'cancelled') {
                    toggleButton.className = 'text-green-600 hover:text-green-900 p-2 rounded-full hover:bg-green-100 transition-colors duration-200';
                    toggleButton.title = 'Riattiva iscrizione';
                    toggleButton.querySelector('path').setAttribute('d', 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z');
                } else {
                    toggleButton.className = 'text-red-600 hover:text-red-900 p-2 rounded-full hover:bg-red-100 transition-colors duration-200';
                    toggleButton.title = 'Sospendi iscrizione';
                    toggleButton.querySelector('path').setAttribute('d', 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z');
                }
            }
        },

        // Sistema di notifiche
        showNotification(message, type = 'success') {
            // Usa il notification manager se disponibile
            if (window.enrollmentManager?.notification) {
                if (type === 'success') {
                    window.enrollmentManager.notification.showSuccess(message);
                } else {
                    window.enrollmentManager.notification.showError(message);
                }
                return;
            }

            // Fallback con alert
            if (type === 'error') {
                alert('❌ ' + message);
            } else {
                alert('✅ ' + message);
            }
        },

        // Pulisce la selezione
        clearSelection() {
            this.selectedIds = [];

            // Deseleziona tutti i checkbox
            document.querySelectorAll('[data-enrollment-id] input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            const selectAllCheckbox = document.querySelector('#select-all-checkbox');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
        }
    }
}
</script>

</x-app-layout>
