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



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
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
                            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $enrollment->status == 'active' ? 'bg-green-100 text-green-800' : ($enrollment->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($enrollment->status ?? 'Unknown') }}
                                </span>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.enrollments.show', $enrollment) }}"
                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Dettagli
                                    </a>
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
</script>

</x-app-layout>
