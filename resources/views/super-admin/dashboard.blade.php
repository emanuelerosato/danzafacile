<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard Super Admin
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Panoramica generale del sistema
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-sm text-gray-500">
                    Ultimo aggiornamento: {{ now()->format('d/m/Y H:i') }}
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
                title="Scuole Attive"
                :value="12"
                icon="office-building"
                color="rose"
                :change="8"
                changeType="increase"
                subtitle="3 nuove questo mese"
            />
            
            <x-stats-card 
                title="Totale Utenti"
                :value="1248"
                icon="users"
                color="purple"
                :change="15"
                changeType="increase"
                subtitle="945 studenti, 303 staff"
            />
            
            <x-stats-card 
                title="Corsi Attivi"
                :value="89"
                icon="academic-cap"
                color="blue"
                :change="5"
                changeType="increase"
                subtitle="Tutte le scuole"
            />
            
            <x-stats-card 
                title="Ricavi Mensili"
                :value="'€45,290'"
                icon="currency-dollar"
                color="green"
                :change="12"
                changeType="increase"
                subtitle="Media per scuola: €3,774"
            />
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Monthly Growth Chart -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Crescita Mensile</h3>
                    <div class="flex items-center space-x-2">
                        <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                            <option>Ultimi 6 mesi</option>
                            <option>Anno corrente</option>
                            <option>Confronta anni</option>
                        </select>
                    </div>
                </div>
                <div class="h-80">
                    <canvas id="growthChart"></canvas>
                </div>
            </div>

            <!-- Schools Distribution -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Distribuzione Scuole</h3>
                    <span class="text-sm text-gray-500">Per regione</span>
                </div>
                <div class="h-80">
                    <canvas id="distributionChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Top Schools -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Recent Activity -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Attività Recenti</h3>
                    <a href="#" class="text-sm text-rose-600 hover:text-rose-700">Vedi tutto</a>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Nuova scuola registrata</p>
                            <p class="text-xs text-gray-500">Danza Moderna Milano - 2 ore fa</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Pagamento elaborato</p>
                            <p class="text-xs text-gray-500">Scuola Balletto Roma - €2,450 - 3 ore fa</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0 w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm text-gray-900">Supporto richiesto</p>
                            <p class="text-xs text-gray-500">Dance Academy Napoli - Problemi pagamento - 5 ore fa</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Performing Schools -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Scuole Top Performance</h3>
                    <span class="text-sm text-gray-500">Questo mese</span>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-white font-bold text-sm">1</div>
                            <div>
                                <p class="font-medium text-gray-900">Accademia Balletto Milano</p>
                                <p class="text-sm text-gray-600">156 studenti • €12,450/mese</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-green-600">+22%</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-gray-50 to-slate-50 rounded-lg border border-gray-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white font-bold text-sm">2</div>
                            <div>
                                <p class="font-medium text-gray-900">Danza Moderna Roma</p>
                                <p class="text-sm text-gray-600">134 studenti • €10,890/mese</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-green-600">+18%</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center justify-between p-3 bg-gradient-to-r from-amber-50 to-yellow-50 rounded-lg border border-amber-200">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-amber-600 rounded-full flex items-center justify-center text-white font-bold text-sm">3</div>
                            <div>
                                <p class="font-medium text-gray-900">Centro Danza Firenze</p>
                                <p class="text-sm text-gray-600">98 studenti • €8,790/mese</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium text-green-600">+15%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Azioni Rapide</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('super-admin.schools.create') }}" 
                   class="flex items-center p-4 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-xl hover:from-rose-600 hover:to-purple-700 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    <span class="font-medium">Nuova Scuola</span>
                </a>
                
                <a href="{{ route('super-admin.reports.index') }}" 
                   class="flex items-center p-4 bg-gradient-to-r from-blue-500 to-cyan-600 text-white rounded-xl hover:from-blue-600 hover:to-cyan-700 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="font-medium">Report</span>
                </a>
                
                <a href="{{ route('super-admin.users.index') }}" 
                   class="flex items-center p-4 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-xl hover:from-green-600 hover:to-emerald-700 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                    </svg>
                    <span class="font-medium">Gestisci Utenti</span>
                </a>
                
                <a href="{{ route('super-admin.settings.index') }}" 
                   class="flex items-center p-4 bg-gradient-to-r from-orange-500 to-yellow-600 text-white rounded-xl hover:from-orange-600 hover:to-yellow-700 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span class="font-medium">Impostazioni</span>
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Growth Chart
        const growthCtx = document.getElementById('growthChart').getContext('2d');
        new Chart(growthCtx, {
            type: 'line',
            data: {
                labels: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu'],
                datasets: [{
                    label: 'Nuove Scuole',
                    data: [2, 3, 1, 4, 2, 3],
                    borderColor: 'rgb(244, 63, 94)',
                    backgroundColor: 'rgba(244, 63, 94, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Nuovi Utenti',
                    data: [45, 89, 67, 123, 98, 134],
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Distribution Chart
        const distributionCtx = document.getElementById('distributionChart').getContext('2d');
        new Chart(distributionCtx, {
            type: 'doughnut',
            data: {
                labels: ['Lombardia', 'Lazio', 'Campania', 'Toscana', 'Altre'],
                datasets: [{
                    data: [4, 3, 2, 2, 1],
                    backgroundColor: [
                        'rgb(244, 63, 94)',
                        'rgb(147, 51, 234)',
                        'rgb(59, 130, 246)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 101, 101)'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>