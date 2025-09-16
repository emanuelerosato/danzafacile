@extends('layouts.app')
    @section('content')
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Studenti - {{ $currentSchool->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Tutti gli studenti iscritti alla tua scuola
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <button @click="$dispatch('open-modal', 'import-students')"
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
    @endsection

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Admin</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Studenti</li>
    @endsection

    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-stats-card
                title="Totale Studenti"
                :value="$stats['total_students']"
                icon="users"
                color="rose"
                subtitle="Tutti gli studenti"
            />

            <x-stats-card
                title="Studenti Attivi"
                :value="$stats['active_students']"
                icon="user-check"
                color="green"
                subtitle="{{ round(($stats['active_students'] / max(1, $stats['total_students'])) * 100) }}% del totale"
            />

            <x-stats-card
                title="Nuovi Questo Mese"
                :value="$stats['new_this_month']"
                icon="user-plus"
                color="blue"
                subtitle="Registrazioni recenti"
            />

            <x-stats-card
                title="Con Iscrizioni"
                :value="$stats['with_enrollments']"
                icon="clipboard-check"
                color="purple"
                subtitle="{{ round(($stats['with_enrollments'] / max(1, $stats['total_students'])) * 100) }}% iscritti a corsi"
            />
        </div>

        <!-- Students Table -->
        <div x-data="studentsDataTable()" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <!-- Table Header with Filters -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Search Input -->
                        <div class="relative max-w-md">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input x-model="search"
                                   @input.debounce.300ms="applyFilters()"
                                   type="text"
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-rose-500 focus:border-rose-500"
                                   placeholder="Cerca studenti...">
                        </div>

                        <!-- Status Filter -->
                        <select x-model="statusFilter" @change="applyFilters()" class="block w-40 px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-rose-500 focus:border-rose-500">
                            <option value="">Tutti gli stati</option>
                            <option value="active">Attivi</option>
                            <option value="inactive">Non attivi</option>
                        </select>

                        <!-- Enrollment Filter -->
                        <select x-model="enrollmentFilter" @change="applyFilters()" class="block w-40 px-3 py-2 border border-gray-300 bg-white rounded-lg shadow-sm focus:outline-none focus:ring-rose-500 focus:border-rose-500">
                            <option value="">Tutte le iscrizioni</option>
                            <option value="enrolled">Con iscrizioni</option>
                            <option value="not_enrolled">Senza iscrizioni</option>
                        </select>
                    </div>

                    <div class="flex items-center space-x-2">
                        <!-- Export Button -->
                        <button @click="exportData()"
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Esporta CSV
                        </button>

                        <!-- Bulk Actions -->
                        <div class="relative" x-show="selectedItems.length > 0">
                            <button @click="bulkMenuOpen = !bulkMenuOpen"
                                    class="inline-flex items-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200">
                                <span x-text="`${selectedItems.length} selezionati`"></span>
                                <svg class="ml-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <!-- Bulk Actions Menu -->
                            <div x-show="bulkMenuOpen" @click.away="bulkMenuOpen = false" x-transition
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                                <div class="py-1">
                                    <button @click="bulkAction('activate')"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-green-50 hover:text-green-900">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"/>
                                        </svg>
                                        Attiva
                                    </button>
                                    <button @click="bulkAction('deactivate')"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-yellow-50 hover:text-yellow-900">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                                        </svg>
                                        Disattiva
                                    </button>
                                    <button @click="bulkAction('export')"
                                            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-900">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3"/>
                                        </svg>
                                        Esporta Selezionati
                                    </button>
                                    <div class="border-t border-gray-200"></div>
                                    <button @click="bulkAction('delete')"
                                            class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Elimina
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto" id="students-table-container">
                @include('admin.students.partials.table', ['students' => $students])
            </div>
        </div>
    </div>

    <script>
        function studentsDataTable() {
            return {
                search: '',
                statusFilter: '',
                enrollmentFilter: '',
                selectedItems: [],
                bulkMenuOpen: false,
                loading: false,

                get allSelected() {
                    const visibleItems = document.querySelectorAll('input[type="checkbox"][name="student_ids[]"]');
                    return visibleItems.length > 0 && this.selectedItems.length === visibleItems.length;
                },

                toggleAll(checked) {
                    const visibleItems = document.querySelectorAll('input[type="checkbox"][name="student_ids[]"]');
                    this.selectedItems = checked ? Array.from(visibleItems).map(cb => parseInt(cb.value)) : [];
                },

                toggleSelection(itemId, checked) {
                    if (checked) {
                        if (!this.selectedItems.includes(itemId)) {
                            this.selectedItems.push(itemId);
                        }
                    } else {
                        this.selectedItems = this.selectedItems.filter(id => id !== itemId);
                    }
                },

                async applyFilters() {
                    this.loading = true;

                    try {
                        const params = new URLSearchParams({
                            search: this.search,
                            status: this.statusFilter,
                            enrollment: this.enrollmentFilter
                        });

                        const response = await fetch(`{{ route('admin.students.index') }}?${params}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            document.getElementById('students-table-container').innerHTML = data.data.html;
                            this.selectedItems = [];
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showToast('Errore durante il caricamento', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async toggleStatus(studentId) {
                    this.loading = true;

                    try {
                        const response = await fetch(`/admin/students/${studentId}/toggle-active`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.applyFilters();
                        } else {
                            this.showToast(data.message || 'Errore durante l\'aggiornamento', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showToast('Errore di connessione', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                async bulkAction(action) {
                    if (this.selectedItems.length === 0) return;

                    let confirmMessage = '';
                    switch (action) {
                        case 'delete':
                            confirmMessage = `Sei sicuro di voler eliminare ${this.selectedItems.length} studenti?`;
                            break;
                        case 'activate':
                            confirmMessage = `Attivare ${this.selectedItems.length} studenti?`;
                            break;
                        case 'deactivate':
                            confirmMessage = `Disattivare ${this.selectedItems.length} studenti?`;
                            break;
                    }

                    if (action !== 'export' && !confirm(confirmMessage)) {
                        return;
                    }

                    this.loading = true;
                    this.bulkMenuOpen = false;

                    try {
                        const response = await fetch('/admin/students/bulk-action', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                action: action,
                                student_ids: this.selectedItems
                            })
                        });

                        if (action === 'export') {
                            const blob = await response.blob();
                            const url = window.URL.createObjectURL(blob);
                            const a = document.createElement('a');
                            a.href = url;
                            a.download = 'studenti_selezionati.csv';
                            document.body.appendChild(a);
                            a.click();
                            window.URL.revokeObjectURL(url);
                            document.body.removeChild(a);
                            return;
                        }

                        const data = await response.json();

                        if (data.success) {
                            this.showToast(data.message, 'success');
                            this.selectedItems = [];
                            this.applyFilters();
                        } else {
                            this.showToast(data.message || 'Errore durante l\'operazione', 'error');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.showToast('Errore di connessione', 'error');
                    } finally {
                        this.loading = false;
                    }
                },

                exportData() {
                    window.open('{{ route('admin.students.export') }}', '_blank');
                },

                showToast(message, type = 'info') {
                    const toast = document.createElement('div');
                    toast.className = `fixed top-4 right-4 px-6 py-4 rounded-lg text-white z-50 ${
                        type === 'success' ? 'bg-green-600' :
                        type === 'error' ? 'bg-red-600' :
                        'bg-blue-600'
                    }`;
                    toast.textContent = message;
                    document.body.appendChild(toast);

                    setTimeout(() => {
                        toast.remove();
                    }, 3000);
                }
            }
        }
    </script>

    <!-- Import Modal -->
    <x-modal name="import-students" maxWidth="lg">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Importa Studenti</h3>
                <button @click="$dispatch('close-modal', 'import-students')"
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-4">
                    Carica un file CSV con i dati degli studenti da importare.
                </p>

                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    <div class="mt-4">
                        <label class="cursor-pointer">
                            <span class="mt-2 block text-sm font-medium text-gray-900">
                                Clicca per caricare o trascina qui il file
                            </span>
                            <input type="file" class="sr-only" accept=".csv">
                        </label>
                        <p class="mt-1 text-xs text-gray-500">CSV fino a 10MB</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="#" class="text-sm text-rose-600 hover:text-rose-700">
                    Scarica template di esempio
                </a>
                <div class="flex space-x-3">
                    <button @click="$dispatch('close-modal', 'import-students')"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Annulla
                    </button>
                    <button class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        Importa
                    </button>
                </div>
            </div>
        </div>
    </x-modal>
@endsection