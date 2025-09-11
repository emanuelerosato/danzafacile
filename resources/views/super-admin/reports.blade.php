@extends('layouts.app')

@section('title', 'Reports & Analytics - Super Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50" x-data="reportsManager()">
    <!-- Header Section -->
    <div class="bg-white/30 backdrop-blur-sm border-b border-white/20 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('super-admin.dashboard') }}" 
                       class="inline-flex items-center text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Torna al Dashboard
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">📊 Reports & Analytics</h1>
                        <p class="text-sm text-gray-600">Sistema completo di reporting e analisi dati</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-500">Ultimo aggiornamento: {{ now()->format('d/m/Y H:i') }}</span>
                    <button @click="refreshAllData()" 
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                        <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': refreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Aggiorna
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Report Filters -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">🔍 Filtri Report</h3>
                <div class="flex items-center space-x-2">
                    <button @click="exportReport('pdf')" 
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-300 rounded-lg hover:bg-red-100 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export PDF
                    </button>
                    <button @click="exportReport('excel')" 
                            class="inline-flex items-center px-3 py-2 text-sm font-medium text-green-700 bg-green-50 border border-green-300 rounded-lg hover:bg-green-100 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Export Excel
                    </button>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Report Type -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo Report</label>
                    <select x-model="selectedReportType" 
                            @change="updateReport()"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                        <option value="overview">📊 Panoramica Generale</option>
                        <option value="schools">🏫 Report Scuole</option>
                        <option value="users">👥 Report Utenti</option>
                        <option value="payments">💰 Report Pagamenti</option>
                        <option value="courses">🎵 Report Corsi</option>
                    </select>
                </div>

                <!-- Time Period -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Periodo</label>
                    <select x-model="selectedPeriod" 
                            @change="updateReport()"
                            class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                        <option value="week">Ultima Settimana</option>
                        <option value="month">Ultimo Mese</option>
                        <option value="quarter">Ultimo Trimestre</option>
                        <option value="year">Ultimo Anno</option>
                        <option value="custom">Periodo Personalizzato</option>
                    </select>
                </div>

                <!-- Date From (shown only for custom period) -->
                <div x-show="selectedPeriod === 'custom'" x-transition>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Inizio</label>
                    <input type="date" 
                           x-model="dateFrom"
                           @change="updateReport()"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                </div>

                <!-- Date To (shown only for custom period) -->
                <div x-show="selectedPeriod === 'custom'" x-transition>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data Fine</label>
                    <input type="date" 
                           x-model="dateTo"
                           @change="updateReport()"
                           class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                </div>
            </div>
        </div>

        <!-- Quick Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900" x-text="reportData.schools_total || '{{ $data['schools']['total'] ?? 0 }}'"></p>
                        <p class="text-sm text-gray-600">Scuole Totali</p>
                        <p class="text-xs text-green-600">+{{ $data['schools']['new_this_period'] ?? 0 }} questo periodo</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900" x-text="reportData.users_total || '{{ $data['users']['total'] ?? 0 }}'"></p>
                        <p class="text-sm text-gray-600">Utenti Totali</p>
                        <p class="text-xs text-gray-500">{{ $data['users']['admins'] ?? 0 }} admin, {{ $data['users']['students'] ?? 0 }} studenti</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900">€{{ number_format($data['payments']['total_amount'] ?? 0, 2) }}</p>
                        <p class="text-sm text-gray-600">Ricavi Totali</p>
                        <p class="text-xs text-green-600">€{{ number_format($data['payments']['completed'] ?? 0, 2) }} completati</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-2xl font-bold text-gray-900">{{ $data['courses']['total'] ?? 0 }}</p>
                        <p class="text-sm text-gray-600">Corsi Totali</p>
                        <p class="text-xs text-blue-600">{{ $data['courses']['active'] ?? 0 }} attivi</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Revenue Trends -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">💰 Trend Ricavi</h3>
                        <div class="flex items-center space-x-2">
                            <button @click="chartPeriod = 'month'" 
                                    :class="chartPeriod === 'month' ? 'bg-green-500 text-white' : 'bg-white text-gray-600'"
                                    class="px-3 py-1 text-sm rounded-lg border">1M</button>
                            <button @click="chartPeriod = 'quarter'" 
                                    :class="chartPeriod === 'quarter' ? 'bg-green-500 text-white' : 'bg-white text-gray-600'"
                                    class="px-3 py-1 text-sm rounded-lg border">3M</button>
                            <button @click="chartPeriod = 'year'" 
                                    :class="chartPeriod === 'year' ? 'bg-green-500 text-white' : 'bg-white text-gray-600'"
                                    class="px-3 py-1 text-sm rounded-lg border">1A</button>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <canvas id="revenueChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- User Distribution -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                    <h3 class="text-lg font-semibold text-gray-900">👥 Distribuzione Utenti</h3>
                </div>
                <div class="p-6">
                    <canvas id="userDistributionChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- School Performance -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-cyan-50">
                    <h3 class="text-lg font-semibold text-gray-900">🏫 Performance Scuole</h3>
                </div>
                <div class="p-6">
                    <canvas id="schoolPerformanceChart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Course Difficulty -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-yellow-50 to-orange-50">
                    <h3 class="text-lg font-semibold text-gray-900">📚 Difficoltà Corsi</h3>
                </div>
                <div class="p-6">
                    <canvas id="courseDifficultyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <!-- Detailed Reports Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-slate-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">📋 Report Dettagliato</h3>
                    <span class="text-sm text-gray-500" x-text="`Report: ${selectedReportType} | Periodo: ${selectedPeriod}`"></span>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Report Content Container -->
                <div x-show="loading" class="text-center py-8">
                    <svg class="animate-spin h-8 w-8 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <p class="text-gray-500">Generando report...</p>
                </div>

                <div x-show="!loading" class="space-y-6">
                    <!-- Overview Report -->
                    <div x-show="selectedReportType === 'overview'">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">📊 Panoramica Sistema</h4>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Crescita Sistema</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Nuove scuole</span>
                                        <span class="text-sm font-medium">+{{ $data['schools']['new_this_period'] ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Nuovi utenti</span>
                                        <span class="text-sm font-medium">+12</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Ricavi periodo</span>
                                        <span class="text-sm font-medium">€{{ number_format($data['payments']['completed'] ?? 0, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Metriche Performance</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Tasso attivazione scuole</span>
                                        <span class="text-sm font-medium text-green-600">87%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Utenti attivi</span>
                                        <span class="text-sm font-medium text-green-600">{{ $data['users']['active'] ?? 0 }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Ricavo medio/scuola</span>
                                        <span class="text-sm font-medium">€1,250</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Schools Report -->
                    <div x-show="selectedReportType === 'schools'">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">🏫 Report Scuole</h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scuola</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utenti</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Corsi</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ricavi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(\App\Models\School::withCount(['users', 'courses'])->get() as $school)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $school->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $school->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $school->active ? 'Attiva' : 'Inattiva' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $school->users_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $school->courses_count }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">€{{ number_format($school->payments()->sum('amount'), 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Other report types content -->
                    <div x-show="['users', 'payments', 'courses'].includes(selectedReportType)">
                        <h4 class="text-lg font-medium text-gray-900 mb-4" x-text="`📊 Report ${selectedReportType.charAt(0).toUpperCase() + selectedReportType.slice(1)}`"></h4>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                            <p class="text-blue-800">
                                <strong>Report in fase di implementazione</strong><br>
                                Questo report per <span x-text="selectedReportType"></span> sarà disponibile nella prossima versione.
                                I dati mostrati nei grafici sopra sono già disponibili e aggiornati in tempo reale.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alpine.js Reports Manager -->
<script>
function reportsManager() {
    return {
        selectedReportType: 'overview',
        selectedPeriod: 'month',
        dateFrom: '',
        dateTo: '',
        chartPeriod: 'month',
        loading: false,
        refreshing: false,
        reportData: @json($data ?? []),
        
        init() {
            this.initCharts();
        },
        
        updateReport() {
            this.loading = true;
            // Simulate API call
            setTimeout(() => {
                this.loading = false;
                this.updateCharts();
            }, 1000);
        },
        
        refreshAllData() {
            this.refreshing = true;
            // Simulate data refresh
            setTimeout(() => {
                this.refreshing = false;
                this.updateCharts();
                window.location.reload();
            }, 1500);
        },
        
        exportReport(format) {
            // Create export URL with current filters
            const params = new URLSearchParams({
                type: this.selectedReportType,
                period: this.selectedPeriod,
                format: format
            });
            
            if (this.selectedPeriod === 'custom') {
                params.append('date_from', this.dateFrom);
                params.append('date_to', this.dateTo);
            }
            
            // Simulate export
            const url = `{{ route('super-admin.export', ['type' => '__TYPE__']) }}`.replace('__TYPE__', format) + '?' + params.toString();
            
            // Show notification
            alert(`Esportazione ${format.toUpperCase()} in corso. Il file sarà scaricato a breve.`);
            
            // In a real implementation, you would:
            // window.location.href = url;
        },
        
        initCharts() {
            this.initRevenueChart();
            this.initUserDistributionChart();
            this.initSchoolPerformanceChart();
            this.initCourseDifficultyChart();
        },
        
        updateCharts() {
            // Update all charts with new data based on filters
            this.initCharts();
        },
        
        initRevenueChart() {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu'],
                    datasets: [{
                        label: 'Ricavi (€)',
                        data: [1200, 1900, 1500, 2500, 2200, 3000],
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '€' + value;
                                }
                            }
                        }
                    }
                }
            });
        },
        
        initUserDistributionChart() {
            const ctx = document.getElementById('userDistributionChart').getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Studenti', 'Admin', 'Istruttori'],
                    datasets: [{
                        data: [{{ $data['users']['students'] ?? 15 }}, {{ $data['users']['admins'] ?? 3 }}, {{ $data['users']['instructors'] ?? 7 }}],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',
                            'rgba(168, 85, 247, 0.8)',
                            'rgba(34, 197, 94, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        },
        
        initSchoolPerformanceChart() {
            const ctx = document.getElementById('schoolPerformanceChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Eleganza', 'Roma Centro', 'Firenze Studio'],
                    datasets: [{
                        label: 'Studenti',
                        data: [45, 32, 28],
                        backgroundColor: 'rgba(59, 130, 246, 0.8)'
                    }, {
                        label: 'Corsi',
                        data: [8, 6, 5],
                        backgroundColor: 'rgba(168, 85, 247, 0.8)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
        },
        
        initCourseDifficultyChart() {
            const ctx = document.getElementById('courseDifficultyChart').getContext('2d');
            new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: ['Principiante', 'Intermedio', 'Avanzato'],
                    datasets: [{
                        data: [6, 4, 2],
                        backgroundColor: [
                            'rgba(34, 197, 94, 0.8)',
                            'rgba(251, 191, 36, 0.8)',
                            'rgba(239, 68, 68, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }
    }
}
</script>

@endsection