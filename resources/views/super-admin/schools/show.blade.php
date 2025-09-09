<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettagli Scuola
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Informazioni complete sulla scuola
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('super-admin.schools.edit', $school ?? 1) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifica
                </a>
                <button @click="$dispatch('open-modal', 'suspend-school')" 
                        class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Sospendi
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
            <a href="{{ route('super-admin.schools.index') }}" class="text-gray-500 hover:text-gray-700">Scuole</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Accademia Balletto Milano</li>
    </x-slot>

    <div class="space-y-6">
        <!-- School Header Card -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="bg-gradient-to-r from-rose-500 to-purple-600 p-6">
                <div class="flex items-center space-x-4">
                    <div class="w-20 h-20 bg-white/20 rounded-xl flex items-center justify-center text-white font-bold text-2xl">
                        AB
                    </div>
                    <div class="flex-1 text-white">
                        <h1 class="text-2xl font-bold">Accademia Balletto Milano</h1>
                        <p class="text-rose-100 mt-1">Fondata nel 2020 • Proprietaria: Maria Rossi</p>
                        <div class="flex items-center mt-2">
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                                Attiva
                            </span>
                            <span class="ml-3 text-rose-100 text-sm">
                                ID: #SCH001 • Registrata il 15/01/2024
                            </span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold">4.8</div>
                        <div class="flex items-center">
                            @for ($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= 4 ? 'text-yellow-300' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                        <p class="text-rose-100 text-sm">156 recensioni</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-stats-card 
                title="Studenti Attivi"
                :value="156"
                icon="users"
                color="blue"
                :change="12"
                changeType="increase"
                subtitle="Crescita mensile"
            />
            
            <x-stats-card 
                title="Corsi Attivi"
                :value="12"
                icon="academic-cap"
                color="purple"
                subtitle="8 livelli diversi"
            />
            
            <x-stats-card 
                title="Ricavo Mensile"
                :value="'€12,450'"
                icon="currency-dollar"
                color="green"
                :change="8"
                changeType="increase"
                subtitle="Media per studente: €79.8"
            />
            
            <x-stats-card 
                title="Tasso Soddisfazione"
                :value="'96%'"
                icon="heart"
                color="rose"
                :change="3"
                changeType="increase"
                subtitle="Basato su 156 recensioni"
            />
        </div>

        <!-- Content Tabs -->
        <div x-data="{ activeTab: 'overview' }" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
            <!-- Tab Navigation -->
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 px-6">
                    <button @click="activeTab = 'overview'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'overview', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'overview' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Panoramica
                    </button>
                    <button @click="activeTab = 'courses'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'courses', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'courses' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Corsi
                    </button>
                    <button @click="activeTab = 'students'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'students', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'students' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Studenti
                    </button>
                    <button @click="activeTab = 'finances'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'finances', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'finances' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Finanze
                    </button>
                    <button @click="activeTab = 'settings'" 
                            :class="{ 'border-rose-500 text-rose-600': activeTab === 'settings', 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300': activeTab !== 'settings' }"
                            class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        Impostazioni
                    </button>
                </nav>
            </div>

            <!-- Tab Content -->
            <div class="p-6">
                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'" class="space-y-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- School Info -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Informazioni Scuola</h3>
                            <div class="space-y-3">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span class="text-gray-900">Via della Danza, 123 - Milano (MI)</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <span class="text-gray-900">+39 02 1234567</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-gray-900">info@accademiaballo.mi.it</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9"/>
                                    </svg>
                                    <span class="text-gray-900">www.accademiaballo.mi.it</span>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activity -->
                        <div class="space-y-4">
                            <h3 class="text-lg font-semibold text-gray-900">Attività Recenti</h3>
                            <div class="space-y-3">
                                <div class="flex items-start space-x-3 p-3 bg-green-50 rounded-lg">
                                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Nuova iscrizione</p>
                                        <p class="text-xs text-gray-500">Sofia Bianchi si è iscritta a Danza Moderna - 2 ore fa</p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Pagamento ricevuto</p>
                                        <p class="text-xs text-gray-500">€150 da Marco Verdi per il corso di Balletto Classico - 3 ore fa</p>
                                    </div>
                                </div>

                                <div class="flex items-start space-x-3 p-3 bg-purple-50 rounded-lg">
                                    <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h0a2 2 0 012 2v4m-4 0a2 2 0 00-2 2v6a2 2 0 002 2h4a2 2 0 002-2V9a2 2 0 00-2-2z"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900">Nuovo corso creato</p>
                                        <p class="text-xs text-gray-500">Corso di Hip Hop per principianti - 1 giorno fa</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Performance Chart -->
                    <div class="mt-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Performance Mensile</h3>
                        <div class="h-80 bg-gray-50 rounded-xl p-4">
                            <canvas id="performanceChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Other tabs content would go here -->
                <div x-show="activeTab === 'courses'" class="space-y-4">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Corsi Attivi (12)</h3>
                        <button class="px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700">
                            Nuovo Corso
                        </button>
                    </div>
                    <p class="text-gray-600">Lista dettagliata di tutti i corsi offerti dalla scuola...</p>
                </div>

                <div x-show="activeTab === 'students'" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Studenti (156)</h3>
                    <p class="text-gray-600">Elenco completo degli studenti iscritti...</p>
                </div>

                <div x-show="activeTab === 'finances'" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Analisi Finanziaria</h3>
                    <p class="text-gray-600">Ricavi, costi e analisi finanziaria dettagliata...</p>
                </div>

                <div x-show="activeTab === 'settings'" class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900">Impostazioni Scuola</h3>
                    <p class="text-gray-600">Configurazioni e impostazioni avanzate...</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Suspend School Modal -->
    <x-modal name="suspend-school" maxWidth="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Sospendi Scuola</h3>
                <button @click="$dispatch('close-modal', 'suspend-school')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="mb-6">
                <div class="flex items-center justify-center w-12 h-12 mx-auto bg-orange-100 rounded-full mb-4">
                    <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-center text-gray-700 mb-4">
                    Sei sicuro di voler sospendere <strong>Accademia Balletto Milano</strong>?
                </p>
                <p class="text-sm text-gray-500 text-center">
                    La scuola non potrà più accedere al sistema fino alla riattivazione.
                </p>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Motivo della sospensione
                </label>
                <select class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    <option>Mancato pagamento</option>
                    <option>Violazione termini di servizio</option>
                    <option>Richiesta amministrativa</option>
                    <option>Altro</option>
                </select>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Note aggiuntive
                </label>
                <textarea rows="3" class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500" 
                          placeholder="Inserisci eventuali note sulla sospensione..."></textarea>
            </div>
            
            <div class="flex items-center justify-end space-x-3">
                <button @click="$dispatch('close-modal', 'suspend-school')" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Annulla
                </button>
                <button class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500">
                    Sospendi Scuola
                </button>
            </div>
        </div>
    </x-modal>

    @push('scripts')
    <script>
        // Performance Chart
        const performanceCtx = document.getElementById('performanceChart').getContext('2d');
        new Chart(performanceCtx, {
            type: 'line',
            data: {
                labels: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu'],
                datasets: [{
                    label: 'Iscrizioni',
                    data: [12, 19, 15, 25, 22, 28],
                    borderColor: 'rgb(244, 63, 94)',
                    backgroundColor: 'rgba(244, 63, 94, 0.1)',
                    tension: 0.4
                }, {
                    label: 'Ricavi (€000)',
                    data: [8, 11, 9, 14, 12, 16],
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
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>