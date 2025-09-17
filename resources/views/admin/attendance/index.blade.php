@extends('layouts.app')

@section('content')
<div class="py-6" x-data="attendanceManager()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Gestione Presenze</h1>
                <p class="text-sm text-gray-600 mt-1">
                    Monitora e gestisci le presenze di studenti a corsi ed eventi
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="#" @click.prevent="openBulkMarkModal()"
                   class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-users-check mr-2"></i>
                    Marcatura Multipla
                </a>
                <a href="#" @click.prevent="exportData()"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-download mr-2"></i>
                    Esporta
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-user-check text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Presenti Oggi
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['today_present'] }} / {{ $stats['today_total'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-percentage text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Tasso Settimana
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['week_avg_attendance'] }}%
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-calendar-check text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Sessioni Mese
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['month_total_sessions'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-rose-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-chart-line text-rose-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Azioni Rapide
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    <a href="#" @click.prevent="quickMarkToday()" class="text-rose-600 hover:text-rose-800 text-sm">
                                        Segna Oggi
                                    </a>
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white rounded-lg shadow-md mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Filtri e Ricerca</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div class="lg:col-span-2">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cerca</label>
                        <div class="relative">
                            <input type="text" id="search" x-model="filters.search" @input.debounce.300ms="applyFilters()"
                                   class="w-full px-4 py-2 pl-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                                   placeholder="Nome studente, corso, evento...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 mb-1">Dal</label>
                        <input type="date" id="date_from" x-model="filters.date_from" @change="applyFilters()"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    </div>

                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 mb-1">Al</label>
                        <input type="date" id="date_to" x-model="filters.date_to" @change="applyFilters()"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                        <select id="status" x-model="filters.status" @change="applyFilters()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                            <option value="">Tutti</option>
                            <option value="present">Presente</option>
                            <option value="absent">Assente</option>
                            <option value="late">Ritardo</option>
                            <option value="excused">Giustificato</option>
                        </select>
                    </div>

                    <!-- Course Filter -->
                    <div>
                        <label for="course_filter" class="block text-sm font-medium text-gray-700 mb-1">Corso</label>
                        <select id="course_filter" x-model="filters.course_id" @change="applyFilters()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                            <option value="">Tutti i corsi</option>
                            @foreach($courses as $course)
                                <option value="{{ $course->id }}">{{ $course->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Event Filter -->
                    <div>
                        <label for="event_filter" class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                        <select id="event_filter" x-model="filters.event_id" @change="applyFilters()"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                            <option value="">Tutti gli eventi</option>
                            @foreach($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reset Filters -->
                    <div class="flex items-end">
                        <button type="button" @click="resetFilters()"
                                class="w-full px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md transition-colors duration-200">
                            <i class="fas fa-undo mr-2"></i>
                            Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Table -->
        <div class="bg-white rounded-lg shadow-md">
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
@endsection