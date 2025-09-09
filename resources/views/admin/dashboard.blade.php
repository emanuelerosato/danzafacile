<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard Admin - {{ Auth::user()->school->name ?? 'Scuola di Danza' }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione della tua scuola di danza
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-500">
                    Anno scolastico 2024/2025
                </span>
                <button @click="location.reload()" 
                        class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </button>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Key Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-stats-card 
                title="Studenti Attivi"
                :value="156"
                icon="users"
                color="rose"
                :change="8"
                changeType="increase"
                subtitle="12 nuovi questo mese"
            />
            
            <x-stats-card 
                title="Corsi Attivi"
                :value="12"
                icon="academic-cap"
                color="purple"
                :change="2"
                changeType="increase"
                subtitle="3 in partenza a breve"
            />
            
            <x-stats-card 
                title="Ricavi Mensili"
                :value="'€12,450'"
                icon="currency-dollar"
                color="green"
                :change="15"
                changeType="increase"
                subtitle="Target: €15,000"
            />
            
            <x-stats-card 
                title="Presenze Media"
                :value="'92%'"
                icon="check-circle"
                color="blue"
                :change="3"
                changeType="increase"
                subtitle="Ultima settimana"
            />
        </div>

        <!-- Quick Actions and Calendar -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Quick Actions -->
            <div class="lg:col-span-1">
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Azioni Rapide</h3>
                    <div class="space-y-3">
                        <a href="{{ route('admin.courses.create') }}" 
                           class="flex items-center p-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            <span class="font-medium">Nuovo Corso</span>
                        </a>
                        
                        <a href="{{ route('admin.enrollments.index') }}" 
                           class="flex items-center p-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white rounded-lg hover:from-blue-600 hover:to-cyan-700 transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                            </svg>
                            <span class="font-medium">Gestisci Iscrizioni</span>
                        </a>
                        
                        <a href="{{ route('admin.payments.index') }}" 
                           class="flex items-center p-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span class="font-medium">Pagamenti</span>
                        </a>
                        
                        <a href="{{ route('admin.reports.index') }}" 
                           class="flex items-center p-3 bg-gradient-to-r from-orange-500 to-yellow-600 text-white rounded-lg hover:from-orange-600 hover:to-yellow-700 transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            <span class="font-medium">Report</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Calendar -->
            <div class="lg:col-span-2">
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Calendario Lezioni</h3>
                        <div class="flex items-center space-x-2">
                            <button class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>
                            <span class="font-medium text-gray-900">Settembre 2024</span>
                            <button class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Mini Calendar -->
                    <div class="grid grid-cols-7 gap-1 mb-4">
                        <div class="text-center text-xs font-medium text-gray-500 py-2">L</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">M</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">M</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">G</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">V</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">S</div>
                        <div class="text-center text-xs font-medium text-gray-500 py-2">D</div>
                        
                        <!-- Calendar days -->
                        <div class="text-center text-sm text-gray-400 py-2">30</div>
                        <div class="text-center text-sm text-gray-400 py-2">31</div>
                        <div class="text-center text-sm text-gray-900 py-2">1</div>
                        <div class="text-center text-sm text-gray-900 py-2 bg-rose-100 rounded">2</div>
                        <div class="text-center text-sm text-gray-900 py-2">3</div>
                        <div class="text-center text-sm text-gray-900 py-2">4</div>
                        <div class="text-center text-sm text-gray-900 py-2">5</div>
                        <!-- More days... -->
                    </div>
                    
                    <!-- Today's Lessons -->
                    <div class="border-t border-gray-200 pt-4">
                        <h4 class="font-medium text-gray-900 mb-3">Lezioni di Oggi</h4>
                        <div class="space-y-2">
                            <div class="flex items-center p-3 bg-rose-50 rounded-lg border border-rose-200">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">Danza Classica Avanzato</p>
                                    <p class="text-sm text-gray-600">16:00 - 17:30 • Sala A • Prof. Martina Rossi</p>
                                </div>
                                <div class="text-sm font-medium text-rose-600">15 studenti</div>
                            </div>
                            
                            <div class="flex items-center p-3 bg-purple-50 rounded-lg border border-purple-200">
                                <div class="flex-1">
                                    <p class="font-medium text-gray-900">Hip Hop Junior</p>
                                    <p class="text-sm text-gray-600">18:00 - 19:00 • Sala B • Prof. Marco Bianchi</p>
                                </div>
                                <div class="text-sm font-medium text-purple-600">22 studenti</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Overview and Recent Enrollments -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Popular Courses -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Corsi Più Popolari</h3>
                    <a href="{{ route('admin.courses.index') }}" class="text-sm text-rose-600 hover:text-rose-700">Vedi tutti</a>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-rose-50 to-pink-50 rounded-lg border border-rose-200">
                        <div>
                            <p class="font-medium text-gray-900">Danza Classica Intermedio</p>
                            <p class="text-sm text-gray-600">28/30 studenti iscritti</p>
                        </div>
                        <div class="text-right">
                            <div class="w-16 bg-gray-200 rounded-full h-2 mb-1">
                                <div class="bg-rose-500 h-2 rounded-full" style="width: 93%"></div>
                            </div>
                            <span class="text-xs text-gray-500">93%</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-purple-50 to-violet-50 rounded-lg border border-purple-200">
                        <div>
                            <p class="font-medium text-gray-900">Hip Hop Avanzato</p>
                            <p class="text-sm text-gray-600">24/25 studenti iscritti</p>
                        </div>
                        <div class="text-right">
                            <div class="w-16 bg-gray-200 rounded-full h-2 mb-1">
                                <div class="bg-purple-500 h-2 rounded-full" style="width: 96%"></div>
                            </div>
                            <span class="text-xs text-gray-500">96%</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-lg border border-blue-200">
                        <div>
                            <p class="font-medium text-gray-900">Danza Moderna</p>
                            <p class="text-sm text-gray-600">19/25 studenti iscritti</p>
                        </div>
                        <div class="text-right">
                            <div class="w-16 bg-gray-200 rounded-full h-2 mb-1">
                                <div class="bg-blue-500 h-2 rounded-full" style="width: 76%"></div>
                            </div>
                            <span class="text-xs text-gray-500">76%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Attività Recenti</h3>
                    <span class="text-sm text-gray-500">Ultime 24 ore</span>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Nuova iscrizione</p>
                            <p class="text-xs text-gray-500">Sofia Verdi si è iscritta a Danza Classica Intermedio</p>
                            <p class="text-xs text-gray-400">2 ore fa</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Pagamento ricevuto</p>
                            <p class="text-xs text-gray-500">Marco Neri ha pagato la quota mensile - €85</p>
                            <p class="text-xs text-gray-400">4 ore fa</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Lezione cancellata</p>
                            <p class="text-xs text-gray-500">Jazz Dance Principianti - 10 settembre rinviata</p>
                            <p class="text-xs text-gray-400">6 ore fa</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Nuovo messaggio</p>
                            <p class="text-xs text-gray-500">Giulia Rossi ha inviato una richiesta</p>
                            <p class="text-xs text-gray-400">8 ore fa</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Financial Overview -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Panoramica Finanziaria</h3>
                <div class="flex items-center space-x-2">
                    <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                        <option>Settembre 2024</option>
                        <option>Agosto 2024</option>
                        <option>Luglio 2024</option>
                    </select>
                </div>
            </div>
            
            <div class="h-64">
                <canvas id="financialChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Financial Chart
        const financialCtx = document.getElementById('financialChart').getContext('2d');
        new Chart(financialCtx, {
            type: 'bar',
            data: {
                labels: ['Sett 1', 'Sett 2', 'Sett 3', 'Sett 4'],
                datasets: [{
                    label: 'Entrate',
                    data: [2800, 3200, 2950, 3500],
                    backgroundColor: 'rgba(244, 63, 94, 0.8)',
                    borderColor: 'rgb(244, 63, 94)',
                    borderWidth: 1
                }, {
                    label: 'Spese',
                    data: [1200, 1400, 1300, 1500],
                    backgroundColor: 'rgba(147, 51, 234, 0.8)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                },
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
    </script>
    @endpush
</x-app-layout>