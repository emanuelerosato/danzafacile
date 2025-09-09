<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Benvenuto, {{ Auth::user()->name }}!
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    La tua area personale di danza
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">{{ Auth::user()->school->name ?? 'Scuola di Danza' }}</p>
                    <p class="text-xs text-gray-500">Anno scolastico 2024/2025</p>
                </div>
                <div class="w-12 h-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Quick Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <x-stats-card 
                title="I Miei Corsi"
                :value="3"
                icon="academic-cap"
                color="rose"
                subtitle="Corsi attivi"
            />
            
            <x-stats-card 
                title="Lezioni Totali"
                :value="24"
                icon="calendar"
                color="purple"
                subtitle="Questo mese"
            />
            
            <x-stats-card 
                title="Presenze"
                :value="'22/24'"
                icon="check-circle"
                color="green"
                subtitle="92% di frequenza"
            />
            
            <x-stats-card 
                title="Prossimo Pagamento"
                :value="'€255'"
                icon="currency-dollar"
                color="blue"
                subtitle="Scadenza: 15 Ottobre"
            />
        </div>

        <!-- My Schedule and Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Today's Schedule -->
            <div class="lg:col-span-2">
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Oggi - {{ now()->format('d F Y') }}</h3>
                        <a href="{{ route('student.schedule.index') }}" class="text-sm text-rose-600 hover:text-rose-700">Vedi calendario completo</a>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-center p-4 bg-gradient-to-r from-rose-50 to-pink-50 rounded-lg border border-rose-200">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-rose-500 rounded-full flex items-center justify-center text-white font-bold">
                                    16:00
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="font-medium text-gray-900">Danza Classica Intermedio</h4>
                                <p class="text-sm text-gray-600">Sala A • Prof. Martina Rossi</p>
                                <div class="flex items-center mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Confermata
                                    </span>
                                    <span class="ml-2 text-xs text-gray-500">90 minuti</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <button class="p-2 text-rose-600 hover:bg-rose-100 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        
                        <div class="flex items-center p-4 bg-gradient-to-r from-purple-50 to-violet-50 rounded-lg border border-purple-200">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    18:30
                                </div>
                            </div>
                            <div class="ml-4 flex-1">
                                <h4 class="font-medium text-gray-900">Hip Hop Avanzato</h4>
                                <p class="text-sm text-gray-600">Sala B • Prof. Marco Bianchi</p>
                                <div class="flex items-center mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Confermata
                                    </span>
                                    <span class="ml-2 text-xs text-gray-500">60 minuti</span>
                                </div>
                            </div>
                            <div class="flex-shrink-0">
                                <button class="p-2 text-purple-600 hover:bg-purple-100 rounded-full">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tomorrow Preview -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <h4 class="font-medium text-gray-700 mb-3">Domani - {{ now()->addDay()->format('d F') }}</h4>
                        <div class="flex items-center p-3 bg-gray-50 rounded-lg">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gray-400 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    19:00
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <h5 class="font-medium text-gray-900">Danza Contemporanea</h5>
                                <p class="text-sm text-gray-600">Sala C • Prof. Elena Conti</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Actions Card -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Azioni Rapide</h3>
                    <div class="space-y-3">
                        <a href="{{ route('student.courses.index') }}" 
                           class="flex items-center p-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            <span class="font-medium">Esplora Corsi</span>
                        </a>
                        
                        <a href="{{ route('student.payments.index') }}" 
                           class="flex items-center p-3 bg-gradient-to-r from-blue-500 to-cyan-600 text-white rounded-lg hover:from-blue-600 hover:to-cyan-700 transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                            <span class="font-medium">I Miei Pagamenti</span>
                        </a>
                        
                        <a href="{{ route('student.documents.index') }}" 
                           class="flex items-center p-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white rounded-lg hover:from-green-600 hover:to-emerald-700 transition-all duration-200">
                            <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span class="font-medium">Documenti</span>
                        </a>
                    </div>
                </div>

                <!-- Messages Card -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Messaggi</h3>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">2 nuovi</span>
                    </div>
                    
                    <div class="space-y-3">
                        <div class="flex items-start space-x-3 p-3 bg-rose-50 rounded-lg border border-rose-200">
                            <div class="flex-shrink-0 w-8 h-8 bg-rose-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                MR
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Prof. Martina Rossi</p>
                                <p class="text-xs text-gray-600">Ottimo progresso nella tecnica!</p>
                                <p class="text-xs text-gray-400 mt-1">2 ore fa</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-3 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex-shrink-0 w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                SC
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Segreteria</p>
                                <p class="text-xs text-gray-600">Promemoria pagamento mensile</p>
                                <p class="text-xs text-gray-400 mt-1">1 giorno fa</p>
                            </div>
                        </div>
                    </div>
                    
                    <a href="{{ route('messages.index') }}" class="block mt-4 text-center text-sm text-rose-600 hover:text-rose-700">
                        Vedi tutti i messaggi
                    </a>
                </div>
            </div>
        </div>

        <!-- My Courses -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900">I Miei Corsi</h3>
                <a href="{{ route('student.my-courses.index') }}" class="text-sm text-rose-600 hover:text-rose-700">Vedi tutti</a>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <x-course-card 
                    title="Danza Classica Intermedio"
                    description="Perfezionamento della tecnica classica con focus su adagio e allegro"
                    instructor="Prof. Martina Rossi"
                    level="Intermedio"
                    schedule="Lun/Mer/Ven 16:00-17:30"
                    :price="85"
                    status="active"
                    href="{{ route('student.courses.show', 1) }}"
                >
                    <x-slot name="actions">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Iscritto
                            </span>
                            <button class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </x-slot>
                </x-course-card>
                
                <x-course-card 
                    title="Hip Hop Avanzato"
                    description="Stili urban e street dance con coreografie moderne"
                    instructor="Prof. Marco Bianchi"
                    level="Avanzato"
                    schedule="Mar/Gio 18:30-19:30"
                    :price="75"
                    status="active"
                    href="{{ route('student.courses.show', 2) }}"
                >
                    <x-slot name="actions">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Iscritto
                            </span>
                            <button class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </x-slot>
                </x-course-card>
                
                <x-course-card 
                    title="Danza Contemporanea"
                    description="Espressione corporea e movimento fluido"
                    instructor="Prof. Elena Conti"
                    level="Intermedio"
                    schedule="Ven 19:00-20:30"
                    :price="95"
                    status="active"
                    href="{{ route('student.courses.show', 3) }}"
                >
                    <x-slot name="actions">
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Iscritto
                            </span>
                            <button class="p-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </button>
                        </div>
                    </x-slot>
                </x-course-card>
            </div>
        </div>

        <!-- Progress and Achievements -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Progress Tracking -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">I Miei Progressi</h3>
                    <span class="text-sm text-gray-500">Settembre 2024</span>
                </div>
                
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Danza Classica</span>
                            <span class="text-sm text-gray-500">85%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-rose-400 to-pink-500 h-2 rounded-full" style="width: 85%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Eccellente tecnica, continua così!</p>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Hip Hop</span>
                            <span class="text-sm text-gray-500">92%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-purple-400 to-violet-500 h-2 rounded-full" style="width: 92%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Ritmo fantastico, ottima interpretazione!</p>
                    </div>
                    
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Contemporanea</span>
                            <span class="text-sm text-gray-500">78%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-gradient-to-r from-blue-400 to-cyan-500 h-2 rounded-full" style="width: 78%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Buona espressività, lavora sulla fluidità</p>
                    </div>
                </div>
            </div>

            <!-- Achievements -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Riconoscimenti</h3>
                    <span class="text-sm text-gray-500">Ultimi ottenuti</span>
                </div>
                
                <div class="space-y-3">
                    <div class="flex items-center p-3 bg-gradient-to-r from-yellow-50 to-orange-50 rounded-lg border border-yellow-200">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-yellow-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-medium text-gray-900">Studentessa del Mese</h4>
                            <p class="text-sm text-gray-600">Eccellente dedizione e miglioramento</p>
                            <p class="text-xs text-gray-500">Settembre 2024</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-green-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-medium text-gray-900">Presenza Perfetta</h4>
                            <p class="text-sm text-gray-600">100% di presenze per 3 mesi consecutivi</p>
                            <p class="text-xs text-gray-500">Agosto 2024</p>
                        </div>
                    </div>
                    
                    <div class="flex items-center p-3 bg-gradient-to-r from-purple-50 to-violet-50 rounded-lg border border-purple-200">
                        <div class="flex-shrink-0">
                            <div class="w-12 h-12 bg-purple-500 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11.5V14m0-2.5v-6a1.5 1.5 0 113 0m-3 6a1.5 1.5 0 00-3 0v2a7.5 7.5 0 0015 0v-5a1.5 1.5 0 00-3 0m-6-3V11m0-5.5v-1a1.5 1.5 0 013 0v1m0 0V11m0-5.5a1.5 1.5 0 013 0v3"/>
                                </svg>
                            </div>
                        </div>
                        <div class="ml-4">
                            <h4 class="font-medium text-gray-900">Primo Spettacolo</h4>
                            <p class="text-sm text-gray-600">Partecipazione al saggio di primavera</p>
                            <p class="text-xs text-gray-500">Giugno 2024</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>