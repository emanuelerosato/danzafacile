<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Reports & Analytics
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Sistema completo di reporting e analisi dati
                </p>
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
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('super-admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Super Admin</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Reports</li>
    </x-slot>

    <div x-data="reportsManager()" class="space-y-6">
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
            
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
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

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ricerca</label>
                    <div class="relative">
                        <input type="text" 
                               x-model="searchTerm"
                               @input="filterReportData()"
                               placeholder="Cerca in tabella..."
                               class="block w-full px-3 py-2 pl-10 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center" x-show="searchTerm">
                            <button @click="searchTerm = ''; filterReportData()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
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
                            <table class="min-w-full divide-y divide-gray-200 schools-table">
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

                    <!-- Users Report -->
                    <div x-show="selectedReportType === 'users'">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">👥 Report Utenti</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Statistiche Generali</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Utenti totali</span>
                                        <span class="text-sm font-medium" x-text="reportData.users?.total || reportData.total || 0"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Utenti attivi</span>
                                        <span class="text-sm font-medium text-green-600" x-text="reportData.users?.active || reportData.active || 0"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Admin</span>
                                        <span class="text-sm font-medium" x-text="reportData.users?.admins || reportData.admins || 0"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Distribuzione Ruoli</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Studenti</span>
                                        <span class="text-sm font-medium" x-text="reportData.users?.students || reportData.students || 0"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Istruttori</span>
                                        <span class="text-sm font-medium" x-text="reportData.users?.instructors || reportData.instructors || 0"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Tasso Admin</span>
                                        <span class="text-sm font-medium text-blue-600" x-text="Math.round((reportData.users?.admins || reportData.admins || 0) / Math.max(1, reportData.users?.total || reportData.total || 1) * 100) + '%'"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Crescita</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Nuovi nel periodo</span>
                                        <span class="text-sm font-medium text-green-600" x-text="reportData.users?.total || reportData.total || 0"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Tasso attivazione</span>
                                        <span class="text-sm font-medium" x-text="Math.round((reportData.users?.active || reportData.active || 0) / Math.max(1, reportData.users?.total || reportData.total || 1) * 100) + '%'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 users-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ruolo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scuola</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creato</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(\App\Models\User::with('school')->latest()->take(20)->get() as $user)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $user->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $user->role === 'super_admin' ? 'bg-red-100 text-red-800' : 
                                                   ($user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800') }}">
                                                {{ ucfirst($user->role) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->school?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $user->active ? 'Attivo' : 'Inattivo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Payments Report -->
                    <div x-show="selectedReportType === 'payments'">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">💰 Report Pagamenti</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div class="bg-green-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Ricavi Totali</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Importo totale</span>
                                        <span class="text-sm font-medium text-green-600">€<span x-text="(reportData.payments?.total_amount || reportData.total_amount || 0).toFixed(2)"></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Transazioni</span>
                                        <span class="text-sm font-medium" x-text="reportData.payments?.total_count || reportData.total_count || 0"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Completati</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Importo</span>
                                        <span class="text-sm font-medium text-blue-600">€<span x-text="(reportData.payments?.completed || reportData.completed || 0).toFixed(2)"></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Tasso successo</span>
                                        <span class="text-sm font-medium" x-text="Math.round((reportData.payments?.completed || reportData.completed || 0) / Math.max(1, reportData.payments?.total_amount || reportData.total_amount || 1) * 100) + '%'"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">In Attesa</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Importo</span>
                                        <span class="text-sm font-medium text-yellow-600">€<span x-text="(reportData.payments?.pending || reportData.pending || 0).toFixed(2)"></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Percentuale</span>
                                        <span class="text-sm font-medium" x-text="Math.round((reportData.payments?.pending || reportData.pending || 0) / Math.max(1, reportData.payments?.total_amount || reportData.total_amount || 1) * 100) + '%'"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Metriche</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Ticket medio</span>
                                        <span class="text-sm font-medium">€<span x-text="Math.round((reportData.payments?.total_amount || reportData.total_amount || 0) / Math.max(1, reportData.payments?.total_count || reportData.total_count || 1))"></span></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Status</span>
                                        <span class="text-sm font-medium text-green-600">Operativo</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 payments-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Corso</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Importo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metodo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(\App\Models\Payment::with(['user', 'course'])->latest()->take(20)->get() as $payment)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $payment->user?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->course?->title ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">€{{ number_format($payment->amount, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $payment->status === 'completed' ? 'bg-green-100 text-green-800' : ($payment->status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($payment->status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_method ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $payment->payment_date?->format('d/m/Y') ?? $payment->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Courses Report -->
                    <div x-show="selectedReportType === 'courses'">
                        <h4 class="text-lg font-medium text-gray-900 mb-4">🎵 Report Corsi</h4>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div class="bg-blue-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Corsi Totali</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Totali</span>
                                        <span class="text-sm font-medium" x-text="reportData.courses?.total || reportData.total || 0"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Attivi</span>
                                        <span class="text-sm font-medium text-green-600" x-text="reportData.courses?.active || reportData.active || 0"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-green-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Performance</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Tasso attivazione</span>
                                        <span class="text-sm font-medium text-green-600" x-text="Math.round((reportData.courses?.active || reportData.active || 0) / Math.max(1, reportData.courses?.total || reportData.total || 1) * 100) + '%'"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Inattivi</span>
                                        <span class="text-sm font-medium text-red-600" x-text="reportData.courses?.inactive || reportData.inactive || 0"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-yellow-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Difficoltà</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Principiante</span>
                                        <span class="text-sm font-medium" x-text="(reportData.courses?.by_level || reportData.by_level || {}).beginner || 0"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Intermedio</span>
                                        <span class="text-sm font-medium" x-text="(reportData.courses?.by_level || reportData.by_level || {}).intermediate || 0"></span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Avanzato</span>
                                        <span class="text-sm font-medium" x-text="(reportData.courses?.by_level || reportData.by_level || {}).advanced || 0"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-purple-50 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">Iscrizioni</h5>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Totali</span>
                                        <span class="text-sm font-medium">{{ \App\Models\CourseEnrollment::count() }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Media per corso</span>
                                        <span class="text-sm font-medium" x-text="Math.round({{ \App\Models\CourseEnrollment::count() }} / Math.max(1, reportData.courses?.total || reportData.total || 1))"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 courses-table">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Corso</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scuola</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Difficoltà</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Prezzo</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Iscritti</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creato</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach(\App\Models\Course::with(['school', 'enrollments'])->latest()->take(20)->get() as $course)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $course->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $course->school?->name ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $course->level === 'beginner' ? 'bg-green-100 text-green-800' : 
                                                   ($course->level === 'intermediate' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($course->level) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">€{{ number_format($course->price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $course->enrollments->count() }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $course->active ? 'Attivo' : 'Inattivo' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $course->created_at->format('d/m/Y') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('meta')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @endpush

    @push('scripts')
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
            chartData: @json($chartData ?? []),
            searchTerm: '',
            filteredRows: [],
            
            init() {
                this.initCharts();
            },
            
            updateReport() {
                this.loading = true;
                
                // Prepare API parameters
                const params = new URLSearchParams({
                    type: this.selectedReportType,
                    period: this.selectedPeriod
                });
                
                // Add custom date range if selected
                if (this.selectedPeriod === 'custom') {
                    if (this.dateFrom) params.append('date_from', this.dateFrom);
                    if (this.dateTo) params.append('date_to', this.dateTo);
                }
                
                // Make AJAX call to backend API
                fetch(`{{ route('super-admin.reports-api') }}?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        // Handle specific HTTP status codes
                        switch(response.status) {
                            case 422:
                                throw new Error('Parametri di filtro non validi');
                            case 500:
                                throw new Error('Errore interno del server');
                            case 404:
                                throw new Error('Endpoint non trovato');
                            default:
                                throw new Error(`Errore HTTP: ${response.status}`);
                        }
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Update report data
                        this.reportData = data.data.report_data;
                        this.chartData = data.data.chart_data;
                        
                        // Update charts with new data
                        try {
                            this.updateCharts();
                        } catch (chartError) {
                            console.error('⚠️ Chart update failed:', chartError);
                            this.showNotification('Dati aggiornati, ma errore nella visualizzazione grafici', 'warning');
                        }
                        
                        this.showNotification('Report aggiornato con successo', 'success');
                        console.log('✅ Report updated successfully:', data.data.filters);
                    } else {
                        const errorMessage = data.message || 'Errore sconosciuto dall\'API';
                        const errorCode = data.error_code || 'UNKNOWN_ERROR';
                        
                        // Handle specific error codes
                        switch(errorCode) {
                            case 'MISSING_DATE_RANGE':
                                this.showNotification('Seleziona un intervallo di date valido per il periodo personalizzato', 'error');
                                break;
                            case 'DATA_GENERATION_ERROR':
                                this.showNotification('Errore durante la generazione dei dati. Riprova con parametri diversi.', 'error');
                                break;
                            case 'VALIDATION_ERROR':
                                this.showNotification('Parametri non validi. Controlla i filtri selezionati.', 'error');
                                break;
                            default:
                                this.showNotification(errorMessage, 'error');
                        }
                        
                        throw new Error(`API Error: ${errorCode} - ${errorMessage}`);
                    }
                })
                .catch(error => {
                    console.error('❌ Error updating report:', error);
                    
                    // Show user-friendly error message if not already shown
                    if (!error.message.includes('Parametri') && !error.message.includes('Errore')) {
                        this.showNotification('Errore di connessione. Verifica la connessione internet e riprova.', 'error');
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
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
                    period: this.selectedPeriod
                });
                
                if (this.selectedPeriod === 'custom') {
                    if (this.dateFrom) params.append('date_from', this.dateFrom);
                    if (this.dateTo) params.append('date_to', this.dateTo);
                }
                
                // Create the actual export URL
                const url = `{{ route('super-admin.export', ['type' => '__TYPE__']) }}`.replace('__TYPE__', format) + '?' + params.toString();
                
                // Show notification and start download
                alert(`Esportazione ${format.toUpperCase()} in corso. Il download inizierà a breve.`);
                
                // Trigger the actual download
                window.location.href = url;
            },

            filterReportData() {
                if (!this.searchTerm.trim()) {
                    // Reset filtered rows when search is empty
                    this.filteredRows = [];
                    this.showAllTableRows();
                    return;
                }

                const searchLower = this.searchTerm.toLowerCase();
                
                // Hide all rows first
                this.hideAllTableRows();
                
                // Get current active table based on report type
                let tableSelector = '';
                switch(this.selectedReportType) {
                    case 'schools':
                        tableSelector = '.schools-table tbody tr';
                        break;
                    case 'users':
                        tableSelector = '.users-table tbody tr';
                        break;
                    case 'payments':
                        tableSelector = '.payments-table tbody tr';
                        break;
                    case 'courses':
                        tableSelector = '.courses-table tbody tr';
                        break;
                }

                if (tableSelector) {
                    const rows = document.querySelectorAll(tableSelector);
                    this.filteredRows = [];

                    rows.forEach((row, index) => {
                        const rowText = row.textContent.toLowerCase();
                        if (rowText.includes(searchLower)) {
                            row.style.display = '';
                            this.filteredRows.push(index);
                        } else {
                            row.style.display = 'none';
                        }
                    });
                }
            },

            hideAllTableRows() {
                const allRows = document.querySelectorAll('table tbody tr');
                allRows.forEach(row => row.style.display = 'none');
            },

            showAllTableRows() {
                const allRows = document.querySelectorAll('table tbody tr');
                allRows.forEach(row => row.style.display = '');
            },

            showNotification(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg max-w-sm transform transition-all duration-500 translate-x-full opacity-0 ${this.getNotificationStyles(type)}`;
                
                notification.innerHTML = `
                    <div class="flex items-center">
                        <div class="flex-shrink-0 mr-3">
                            ${this.getNotificationIcon(type)}
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-medium">${message}</p>
                        </div>
                        <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                `;
                
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full', 'opacity-0');
                }, 100);
                
                // Auto remove after 5 seconds
                setTimeout(() => {
                    notification.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => notification.remove(), 500);
                }, 5000);
            },

            getNotificationStyles(type) {
                const styles = {
                    success: 'bg-green-100 text-green-800 border border-green-300',
                    error: 'bg-red-100 text-red-800 border border-red-300',
                    warning: 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                    info: 'bg-blue-100 text-blue-800 border border-blue-300'
                };
                return styles[type] || styles.info;
            },

            getNotificationIcon(type) {
                const icons = {
                    success: '<svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                    error: '<svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                    warning: '<svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>',
                    info: '<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
                };
                return icons[type] || icons.info;
            },
            
            initCharts() {
                this.initRevenueChart();
                this.initUserDistributionChart();
                this.initSchoolPerformanceChart();
                this.initCourseDifficultyChart();
            },
            
            updateCharts() {
                // Destroy existing charts before creating new ones
                if (window.revenueChart) {
                    window.revenueChart.destroy();
                }
                if (window.userDistributionChart) {
                    window.userDistributionChart.destroy();
                }
                if (window.schoolPerformanceChart) {
                    window.schoolPerformanceChart.destroy();
                }
                if (window.courseDifficultyChart) {
                    window.courseDifficultyChart.destroy();
                }
                
                // Recreate charts with updated data
                this.initCharts();
            },
            
            initRevenueChart() {
                const ctx = document.getElementById('revenueChart').getContext('2d');
                const revenueData = this.chartData.revenue_trends || { labels: [], data: [] };
                
                window.revenueChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: revenueData.labels,
                        datasets: [{
                            label: 'Ricavi (€)',
                            data: revenueData.data,
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
                                        return '€' + value.toFixed(0);
                                    }
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return 'Ricavi: €' + context.parsed.y.toFixed(2);
                                    }
                                }
                            }
                        }
                    }
                });
            },
            
            initUserDistributionChart() {
                const ctx = document.getElementById('userDistributionChart').getContext('2d');
                const userData = this.chartData.user_distribution || { labels: [], data: [] };
                
                window.userDistributionChart = new Chart(ctx, {
                    type: 'doughnut',
                    data: {
                        labels: userData.labels,
                        datasets: [{
                            data: userData.data,
                            backgroundColor: [
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(168, 85, 247, 0.8)',
                                'rgba(34, 197, 94, 0.8)'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            },
            
            initSchoolPerformanceChart() {
                const ctx = document.getElementById('schoolPerformanceChart').getContext('2d');
                const schoolData = this.chartData.school_performance || { labels: [], students: [], courses: [] };
                
                window.schoolPerformanceChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: schoolData.labels,
                        datasets: [{
                            label: 'Studenti',
                            data: schoolData.students,
                            backgroundColor: 'rgba(59, 130, 246, 0.8)',
                            borderColor: 'rgba(59, 130, 246, 1)',
                            borderWidth: 1
                        }, {
                            label: 'Corsi',
                            data: schoolData.courses,
                            backgroundColor: 'rgba(168, 85, 247, 0.8)',
                            borderColor: 'rgba(168, 85, 247, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            }
                        }
                    }
                });
            },
            
            initCourseDifficultyChart() {
                const ctx = document.getElementById('courseDifficultyChart').getContext('2d');
                const difficultyData = this.chartData.course_difficulty || { labels: [], data: [] };
                
                window.courseDifficultyChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: difficultyData.labels,
                        datasets: [{
                            data: difficultyData.data,
                            backgroundColor: [
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(251, 191, 36, 0.8)',
                                'rgba(239, 68, 68, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(168, 85, 247, 0.8)'
                            ],
                            borderColor: [
                                'rgba(34, 197, 94, 1)',
                                'rgba(251, 191, 36, 1)',
                                'rgba(239, 68, 68, 1)',
                                'rgba(59, 130, 246, 1)',
                                'rgba(168, 85, 247, 1)'
                            ],
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' corsi (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }
        }
    }
    </script>
    @endpush
</x-app-layout>