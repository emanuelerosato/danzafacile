@extends('layouts.app')
    @section('content')
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard Admin - {{ $school->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Panoramica completa della tua scuola di danza
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="refreshDashboard()"
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Aggiorna
                </button>
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Esporta
                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition
                         class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-50">
                        <div class="py-1">
                            <a href="{{ route('admin.export', 'students') }}"
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                Studenti
                            </a>
                            <a href="{{ route('admin.export', 'courses') }}"
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                Corsi
                            </a>
                            <a href="{{ route('admin.export', 'payments') }}"
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Pagamenti
                            </a>
                            <div class="border-t border-gray-200"></div>
                            <a href="{{ route('admin.export', 'summary') }}"
                               class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 font-medium">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Riepilogo Completo
                            </a>
                        </div>
                    </div>
                </div>
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
        <li class="text-gray-900 font-medium">Admin</li>
    @endsection

    <div class="space-y-6">
        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach($quickStats as $stat)
                <x-stats-card
                    :title="$stat['title']"
                    :value="$stat['value']"
                    :icon="$stat['icon']"
                    :color="$stat['color']"
                    :subtitle="$stat['subtitle']"
                    :change="$stat['change']"
                />
            @endforeach
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

        <!-- Activity and Events Row -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Activity -->
            <div class="lg:col-span-2 bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Attività Recente</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-6">
                        <!-- Recent Enrollments -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                </svg>
                                Nuove Iscrizioni
                            </h4>
                            <div class="space-y-2">
                                @forelse($recentEnrollments as $enrollment)
                                    <div class="flex items-center justify-between p-3 bg-blue-50/50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                {{ strtoupper(substr($enrollment->user->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $enrollment->user->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $enrollment->course->name }}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $enrollment->enrollment_date->diffForHumans() }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 italic">Nessuna iscrizione recente</p>
                                @endforelse
                            </div>
                        </div>

                        <!-- Recent Payments -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-3 flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Pagamenti Recenti
                            </h4>
                            <div class="space-y-2">
                                @forelse($recentPayments as $payment)
                                    <div class="flex items-center justify-between p-3 bg-green-50/50 rounded-lg">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-xs">
                                                €
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $payment->user->name }}</p>
                                                <p class="text-xs text-gray-500">€ {{ number_format($payment->amount, 2, ',', '.') }}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-gray-400">{{ $payment->payment_date->diffForHumans() }}</span>
                                    </div>
                                @empty
                                    <p class="text-sm text-gray-500 italic">Nessun pagamento recente</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upcoming Events -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-gray-900">Prossimi Eventi</h3>
                        <a href="{{ route('admin.events.index') }}" class="text-sm text-rose-600 hover:text-rose-700">
                            Vedi tutti
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @forelse($upcomingEvents as $event)
                            <div class="border-l-4 border-purple-400 pl-4">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h4 class="text-sm font-medium text-gray-900">{{ $event->name }}</h4>
                                        <p class="text-xs text-gray-500 mt-1">
                                            {{ $event->start_date->format('d M Y, H:i') }}
                                        </p>
                                        @if($event->location)
                                            <p class="text-xs text-gray-400 mt-1">
                                                <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                </svg>
                                                {{ $event->location }}
                                            </p>
                                        @endif
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        {{ ucfirst($event->type) }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 4v10l6-3 6 3V11M8 11h8"/>
                                </svg>
                                <p class="text-sm text-gray-500 mt-2">Nessun evento in programma</p>
                                <a href="{{ route('admin.events.create') }}" class="inline-flex items-center mt-4 px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-rose-700 bg-rose-100 hover:bg-rose-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500">
                                    Crea Evento
                                </a>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Chart -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Andamento Incassi</h3>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Ultimi 12 mesi
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>

            <!-- Enrollments Chart -->
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Iscrizioni Mensili</h3>
                    <div class="flex items-center text-sm text-gray-500">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Nuovi studenti
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="enrollmentsChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Dashboard data from backend
        const analyticsData = @json($analytics);

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: analyticsData.monthly_revenue.map(item => item.month),
                datasets: [{
                    label: 'Incassi (€)',
                    data: analyticsData.monthly_revenue.map(item => item.revenue),
                    borderColor: 'rgb(236, 72, 153)',
                    backgroundColor: 'rgba(236, 72, 153, 0.1)',
                    fill: true,
                    tension: 0.4
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
                                return '€' + value.toLocaleString();
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Enrollments Chart
        const enrollmentsCtx = document.getElementById('enrollmentsChart').getContext('2d');
        new Chart(enrollmentsCtx, {
            type: 'bar',
            data: {
                labels: analyticsData.enrollment_trends.map(item => item.month),
                datasets: [{
                    label: 'Iscrizioni',
                    data: analyticsData.enrollment_trends.map(item => item.enrollments),
                    backgroundColor: 'rgba(59, 130, 246, 0.8)',
                    borderColor: 'rgb(59, 130, 246)',
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
                    legend: {
                        display: false
                    }
                }
            }
        });

        // Refresh Dashboard
        function refreshDashboard() {
            fetch('{{ route('admin.stats') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update charts with new data
                        location.reload(); // Simple approach for now
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Auto refresh every 5 minutes
        setInterval(refreshDashboard, 300000);
    </script>
@endsection