<x-app-layout>
    <x-slot name="header">
        <!-- Glassmorphism Header with Breadcrumb -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
            <!-- Breadcrumb Navigation -->
            <nav class="flex mb-4" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('student.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-rose-600">
                            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                </ol>
            </nav>

            <!-- Header Content -->
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <!-- Student Avatar -->
                    <div class="relative">
                        @if(Auth::user()->profile_image_path)
                            <img class="w-16 h-16 rounded-full border-4 border-white shadow-lg"
                                 src="{{ asset('storage/' . Auth::user()->profile_image_path) }}"
                                 alt="{{ Auth::user()->name }}">
                        @else
                            <div class="w-16 h-16 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-xl shadow-lg">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        @endif
                        <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Welcome Message -->
                    <div>
                        <h2 class="text-xl md:text-2xl font-bold bg-gradient-to-r from-rose-600 to-purple-600 bg-clip-text text-transparent">
                            Benvenuto, {{ Auth::user()->name }}! 👋
                        </h2>
                        <p class="text-sm text-gray-600 mt-1 flex items-center">
                            <svg class="w-4 h-4 mr-1 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                            {{ Auth::user()->school->name ?? 'Scuola di Danza' }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1 flex items-center">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            Anno scolastico 2024/2025
                        </p>
                    </div>
                </div>

                <!-- Quick Status & Actions -->
                <div class="flex items-center space-x-4">
                    <!-- Quick Stats -->
                    <div class="hidden md:flex items-center space-x-4 px-4 py-2 bg-gradient-to-r from-rose-50 to-pink-50 rounded-lg border border-rose-200">
                        <div class="text-center">
                            <p class="text-xs font-medium text-gray-500">Corsi Attivi</p>
                            <p class="text-lg font-bold text-rose-600">{{ $stats['active_enrollments'] ?? 3 }}</p>
                        </div>
                        <div class="w-px h-8 bg-rose-300"></div>
                        <div class="text-center">
                            <p class="text-xs font-medium text-gray-500">Presenze %</p>
                            <p class="text-lg font-bold text-green-600">92%</p>
                        </div>
                    </div>

                    <!-- Profile Menu -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 bg-white/60 backdrop-blur-sm rounded-lg border border-white/30 hover:bg-white/80 transition-all duration-200">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white/90 backdrop-blur-sm rounded-lg shadow-lg border border-white/20 py-1 z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-rose-600">
                                Il Mio Profilo
                            </a>
                            <a href="{{ route('student.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-rose-50 hover:text-rose-600">
                                Impostazioni
                            </a>
                            <hr class="my-1 border-gray-200">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Glassmorphism Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- I Miei Corsi -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-rose-400 to-rose-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $stats['active_enrollments'] }}</p>
                            <p class="text-sm text-gray-600">Corsi Attivi</p>
                        </div>
                    </div>
                    @if($stats['active_enrollments'] > 0)
                        <div class="text-xs text-rose-600 bg-rose-50 px-2 py-1 rounded-full font-medium">
                            {{ $stats['active_enrollments'] > 1 ? 'Multipli' : 'Attivo' }}
                        </div>
                    @endif
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs text-gray-500">I tuoi corsi</span>
                    <div class="flex items-center space-x-1">
                        <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.293 9.707a1 1 0 010-1.414l6-6a1 1 0 011.414 0l6 6a1 1 0 01-1.414 1.414L11 5.414V17a1 1 0 11-2 0V5.414L4.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-xs text-green-600 font-medium">Attivo</span>
                    </div>
                </div>
            </div>

            <!-- Lezioni Totali -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ $activeEnrollments->count() * 8 }}</p>
                            <p class="text-sm text-gray-600">Lezioni</p>
                        </div>
                    </div>
                    <div class="text-xs text-purple-600 bg-purple-50 px-2 py-1 rounded-full font-medium">
                        Settembre
                    </div>
                </div>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs text-gray-500">Questo mese</span>
                    <div class="flex items-center space-x-1">
                        <svg class="w-3 h-3 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M12 7a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0V8.414l-4.293 4.293a1 1 0 01-1.414 0L8 10.414l-4.293 4.293a1 1 0 01-1.414-1.414l5-5a1 1 0 011.414 0L11 10.586 14.586 7H12z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="text-xs text-blue-600 font-medium">+3 vs Ago</span>
                    </div>
                </div>
            </div>

            <!-- Presenze -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">{{ floor(($activeEnrollments->count() * 8) * 0.92) }}/{{ $activeEnrollments->count() * 8 }}</p>
                            <p class="text-sm text-gray-600">Presenze</p>
                        </div>
                    </div>
                    <div class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full font-medium">
                        92%
                    </div>
                </div>
                <div class="mt-4">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-xs text-gray-500">Frequenza</span>
                        <span class="text-xs font-medium text-green-600">Eccellente!</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-gradient-to-r from-green-400 to-emerald-500 h-2 rounded-full" style="width: 92%"></div>
                    </div>
                </div>
            </div>

            <!-- Prossimo Pagamento -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300 group">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-cyan-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-gray-900">€{{ number_format($stats['pending_payments'], 0, ',', '.') }}</p>
                            <p class="text-sm text-gray-600">Da Pagare</p>
                        </div>
                    </div>
                    @if($stats['pending_payments'] > 0)
                        <div class="text-xs text-orange-600 bg-orange-50 px-2 py-1 rounded-full font-medium">
                            {{ now()->addDays(15)->format('d M') }}
                        </div>
                    @else
                        <div class="text-xs text-green-600 bg-green-50 px-2 py-1 rounded-full font-medium">
                            In regola
                        </div>
                    @endif
                </div>
                <div class="mt-4 flex items-center justify-between">
                    @if($stats['pending_payments'] > 0)
                        <span class="text-xs text-gray-500">Scadenza tra 15 giorni</span>
                        <button class="text-xs text-blue-600 hover:text-blue-700 font-medium underline">
                            Paga ora
                        </button>
                    @else
                        <span class="text-xs text-green-500">Nessun pagamento in sospeso</span>
                        <span class="text-xs text-gray-400">• Complimenti!</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- My Schedule and Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Today's Schedule -->
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
                    <!-- Header with Gradient -->
                    <div class="bg-gradient-to-r from-rose-50 to-pink-50 rounded-xl p-4 mb-6 border border-rose-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-rose-500 to-pink-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Oggi - {{ now()->format('d F Y') }}</h3>
                                    <p class="text-sm text-gray-600">Le tue lezioni di oggi</p>
                                </div>
                            </div>
                            <a href="{{ route('student.schedule.index') }}"
                               class="inline-flex items-center px-3 py-2 bg-white/60 backdrop-blur-sm rounded-lg text-sm font-medium text-rose-600 hover:text-rose-700 hover:bg-white/80 transition-all duration-200 border border-white/40">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Calendario
                            </a>
                        </div>
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
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
                    <!-- Header with Gradient -->
                    <div class="bg-gradient-to-r from-purple-50 to-violet-50 rounded-xl p-4 mb-6 border border-purple-100">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-violet-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Azioni Rapide</h3>
                                <p class="text-sm text-gray-600">Accesso veloce alle funzioni</p>
                            </div>
                        </div>
                    </div>
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
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
                    <!-- Header with Gradient -->
                    <div class="bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl p-4 mb-6 border border-blue-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-full flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">Messaggi</h3>
                                    <p class="text-sm text-gray-600">Comunicazioni importanti</p>
                                </div>
                            </div>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800 border border-rose-200">
                                2 nuovi
                            </span>
                        </div>
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
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
            <!-- Header with Gradient -->
            <div class="bg-gradient-to-r from-rose-50 to-pink-50 rounded-xl p-4 mb-6 border border-rose-100">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-rose-500 to-pink-500 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">I Miei Corsi</h3>
                            <p class="text-sm text-gray-600">Corsi a cui sei iscritto</p>
                        </div>
                    </div>
                    <a href="{{ route('student.my-courses.index') }}"
                       class="inline-flex items-center px-3 py-2 bg-white/60 backdrop-blur-sm rounded-lg text-sm font-medium text-rose-600 hover:text-rose-700 hover:bg-white/80 transition-all duration-200 border border-white/40">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                        Vedi tutti
                    </a>
                </div>
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
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
                <!-- Header with Gradient -->
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-4 mb-6 border border-green-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">I Miei Progressi</h3>
                                <p class="text-sm text-gray-600">Miglioramenti mensili</p>
                            </div>
                        </div>
                        <div class="text-xs text-green-600 bg-green-100 px-3 py-1 rounded-full font-medium border border-green-200">
                            Settembre 2024
                        </div>
                    </div>
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
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 hover:bg-white/90 transition-all duration-300">
                <!-- Header with Gradient -->
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-4 mb-6 border border-yellow-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-full flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">Riconoscimenti</h3>
                                <p class="text-sm text-gray-600">I tuoi successi</p>
                            </div>
                        </div>
                        <div class="text-xs text-orange-600 bg-orange-100 px-3 py-1 rounded-full font-medium border border-orange-200">
                            3 nuovi
                        </div>
                    </div>
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