<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Presenze
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione presenze della tua scuola
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
        <li class="text-gray-900 font-medium">Presenze</li>
    </x-slot>



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8" x-data="attendanceManager()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <!-- Header Actions -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="font-semibold text-xl text-gray-800 leading-tight">
                        Gestione Presenze
                    </h1>
                    <p class="text-sm text-gray-600 mt-1">
                        Monitora e gestisci le presenze di studenti a corsi ed eventi
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                    <button @click="openBulkMarkModal()"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Marcatura Multipla
                    </button>
                    <button @click="exportData()"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Esporta
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Presenti Oggi</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['today_present'] }} / {{ $stats['today_total'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Tasso Settimana</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['week_avg_attendance'] }}%</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Sessioni Mese</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['month_total_sessions'] }}</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-rose-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Azioni Rapide</p>
                            <div class="mt-1">
                                <button @click="quickMarkToday()"
                                        class="text-rose-600 hover:text-rose-800 text-sm font-medium transition-colors duration-200">
                                    Segna Oggi →
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Section -->
            <div class="bg-white rounded-lg shadow p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Filtri e Ricerca</h3>
                    <p class="text-sm text-gray-600 mt-1">Filtra le presenze per trovare quello che stai cercando</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cerca</label>
                        <div class="relative">
                            <input type="text" id="search" x-model="filters.search" @input.debounce.300ms="applyFilters()"
                                   class="w-full px-4 py-3 pl-10 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                   placeholder="Nome studente, corso, evento...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-2">Dal</label>
                        <input type="date" id="date_from" x-model="filters.date_from" @change="applyFilters()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-2">Al</label>
                        <input type="date" id="date_to" x-model="filters.date_to" @change="applyFilters()"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                        <select id="status" x-model="filters.status" @change="applyFilters()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                            <option value="">Tutti</option>
                            <option value="present">Presente</option>
                            <option value="absent">Assente</option>
                            <option value="late">Ritardo</option>
                            <option value="excused">Giustificato</option>
                        </select>
                    </div>

                    <!-- Course Filter -->
                    <div>
                        <label for="course_filter" class="block text-sm font-medium text-gray-700 mb-2">Corso</label>
                        <select id="course_filter" x-model="filters.course_id" @change="applyFilters()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                            <option value="">Tutti i corsi</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Event Filter -->
                    <div>
                        <label for="event_filter" class="block text-sm font-medium text-gray-700 mb-2">Evento</label>
                        <select id="event_filter" x-model="filters.event_id" @change="applyFilters()"
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                            <option value="">Tutti gli eventi</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reset Filters -->
                    <div class="flex items-end">
                        <button type="button" @click="resetFilters()"
                                class="inline-flex items-center justify-center w-full px-4 py-3 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Reset Filtri
                        </button>
                    </div>
                </div>
            </div>
        </div>

            <!-- Attendance Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Registro Presenze</h3>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm text-gray-500" x-text="'Mostrando ' + attendanceCount + ' risultati'"></span>
                    </div>
                </div>

            <div id="attendance-table-container">
                @include('admin.attendance.partials.table', ['attendances' => $attendances])
            </div>
        </div>
    </div>

    <!-- Bulk Mark Modal -->
    <div id="bulkMarkModal" x-show="showBulkModal" x-cloak
         class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 text-center">Marcatura Multipla</h3>
                <form @submit.prevent="submitBulkMark()" class="mt-4">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
                        <input type="date" x-model="bulkMark.date" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Corso/Evento</label>
                        <select x-model="bulkMark.type" @change="updateSubjectOptions()" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                            <option value="">Seleziona tipo...</option>
                            <option value="course">Corso</option>
                            <option value="event">Evento</option>
                        </select>
                    </div>
                    <div class="mb-4" x-show="bulkMark.type">
                        <label class="block text-sm font-medium text-gray-700 mb-2" x-text="bulkMark.type === 'course' ? 'Corso' : 'Evento'"></label>
                        <select x-model="bulkMark.subject_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                            <option value="">Seleziona...</option>
                            <template x-for="subject in bulkMark.subjects" :key="subject.id">
                                <option :value="subject.id" x-text="subject.name"></option>
                            </template>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stato Predefinito</label>
                        <select x-model="bulkMark.defaultStatus" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                            <option value="present">Presente</option>
                            <option value="absent">Assente</option>
                            <option value="late">Ritardo</option>
                            <option value="excused">Giustificato</option>
                        </select>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="closeBulkMarkModal()"
                                class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">
                            Annulla
                        </button>
                        <button type="submit"
                                class="px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700 text-white rounded-md transition-all duration-200">
                            Procedi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function attendanceManager() {
    return {
        filters: {
            search: '',
            date_from: '',
            date_to: '',
            status: '',
            course_id: '',
            event_id: ''
        },
        showBulkModal: false,
        attendanceCount: {{ $attendances->total() }},
        bulkMark: {
            date: '{{ now()->format("Y-m-d") }}',
            type: '',
            subject_id: '',
            subjects: [],
            defaultStatus: 'present'
        },
        courses: @json($courses),
        events: @json($events),

        applyFilters() {
            const params = new URLSearchParams(this.filters);

            fetch(`{{ route('admin.attendance.index') }}?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('attendance-table-container').innerHTML = data.data.html;
                    this.attendanceCount = data.data.pagination ?
                        parseInt(data.data.pagination.match(/\d+/)?.[0] || 0) : 0;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('Errore durante il caricamento dei dati', 'error');
            });
        },

        resetFilters() {
            this.filters = {
                search: '',
                date_from: '',
                date_to: '',
                status: '',
                course_id: '',
                event_id: ''
            };
            this.applyFilters();
        },

        openBulkMarkModal() {
            this.showBulkModal = true;
        },

        closeBulkMarkModal() {
            this.showBulkModal = false;
            this.bulkMark = {
                date: '{{ now()->format("Y-m-d") }}',
                type: '',
                subject_id: '',
                subjects: [],
                defaultStatus: 'present'
            };
        },

        updateSubjectOptions() {
            if (this.bulkMark.type === 'course') {
                this.bulkMark.subjects = this.courses;
            } else if (this.bulkMark.type === 'event') {
                this.bulkMark.subjects = this.events;
            } else {
                this.bulkMark.subjects = [];
            }
            this.bulkMark.subject_id = '';
        },

        submitBulkMark() {
            // This would navigate to a dedicated bulk marking interface
            const params = new URLSearchParams({
                date: this.bulkMark.date,
                type: this.bulkMark.type,
                subject_id: this.bulkMark.subject_id,
                default_status: this.bulkMark.defaultStatus
            });

            const route = this.bulkMark.type === 'course' ?
                `{{ url('admin/attendance/course') }}/${this.bulkMark.subject_id}?${params.toString()}` :
                `{{ url('admin/attendance/event') }}/${this.bulkMark.subject_id}?${params.toString()}`;

            window.location.href = route;
        },

        exportData() {
            const params = new URLSearchParams(this.filters);
            window.location.href = `{{ route('admin.attendance.export') }}?${params.toString()}`;
        },

        quickMarkToday() {
            // Quick shortcut to mark attendance for today
            this.bulkMark.date = '{{ now()->format("Y-m-d") }}';
            this.openBulkMarkModal();
        }
    }
}

function showAlert(message, type = 'info') {
    // Simple alert for now - you can replace with a better notification system
    if (type === 'success') {
        alert('✅ ' + message);
    } else if (type === 'error') {
        alert('❌ ' + message);
    } else {
        alert('ℹ️ ' + message);
    }
}
</script>
</x-app-layout>
