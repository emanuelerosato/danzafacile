@extends('layouts.app')
    @section('content')
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Iscrizioni
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Tutte le iscrizioni ai corsi della scuola
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <button @click="$dispatch('open-modal', 'bulk-actions')" 
                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    Azioni Multiple
                </button>
                <button @click="$dispatch('open-modal', 'new-enrollment')" 
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuova Iscrizione
                </button>
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
        <li class="text-gray-900 font-medium">Iscrizioni</li>
    @endsection

    <div class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <x-stats-card 
                title="Totale Iscrizioni"
                :value="156"
                icon="users"
                color="blue"
                :change="8"
                changeType="increase"
                subtitle="12 nuove questo mese"
            />
            
            <x-stats-card 
                title="Iscrizioni Attive"
                :value="142"
                icon="check-circle"
                color="green"
                subtitle="91% del totale"
            />
            
            <x-stats-card 
                title="In Attesa"
                :value="8"
                icon="clock"
                color="yellow"
                subtitle="Conferma pagamento"
            />
            
            <x-stats-card 
                title="Sospese/Annullate"
                :value="6"
                icon="x-circle"
                color="red"
                subtitle="3.8% del totale"
            />
        </div>

        <!-- Filters and Actions -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between space-y-4 lg:space-y-0">
                <div class="flex flex-wrap items-center gap-4">
                    <!-- Status Filter -->
                    <div>
                        <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                            <option>Tutti gli stati</option>
                            <option>Attive</option>
                            <option>In attesa</option>
                            <option>Sospese</option>
                            <option>Annullate</option>
                        </select>
                    </div>

                    <!-- Course Filter -->
                    <div>
                        <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                            <option>Tutti i corsi</option>
                            <option>Danza Classica Intermedio</option>
                            <option>Hip Hop Avanzato</option>
                            <option>Danza Moderna</option>
                            <option>Jazz Dance</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <select class="text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                            <option>Tutti i periodi</option>
                            <option>Ultimo mese</option>
                            <option>Ultimi 3 mesi</option>
                            <option>Anno corrente</option>
                            <option>Periodo personalizzato</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <!-- Search -->
                    <div class="relative">
                        <input type="search" placeholder="Cerca studente..." 
                               class="pl-10 pr-4 py-2 text-sm border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 w-64">
                        <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>

                    <!-- Export -->
                    <button class="inline-flex items-center px-3 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Esporta
                    </button>
                </div>
            </div>
        </div>

        <!-- Enrollments Table -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Lista Iscrizioni</h3>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-6 py-3 text-left">
                                <input type="checkbox" class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Studente
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Corso
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Data Iscrizione
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Stato
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pagamento
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Presenze
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Azioni
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php
                            $enrollments = [
                                [
                                    'student' => ['name' => 'Sofia Verdi', 'email' => 'sofia.verdi@email.com', 'age' => 14],
                                    'course' => ['name' => 'Danza Classica Intermedio', 'code' => 'CLA-INT-001'],
                                    'enrolled_date' => '2024-09-01',
                                    'status' => 'active',
                                    'payment_status' => 'paid',
                                    'attendance' => 95,
                                    'next_payment' => '2024-10-01'
                                ],
                                [
                                    'student' => ['name' => 'Marco Neri', 'email' => 'marco.neri@email.com', 'age' => 16],
                                    'course' => ['name' => 'Hip Hop Avanzato', 'code' => 'HIP-ADV-001'],
                                    'enrolled_date' => '2024-09-03',
                                    'status' => 'active',
                                    'payment_status' => 'pending',
                                    'attendance' => 88,
                                    'next_payment' => '2024-10-03'
                                ],
                                [
                                    'student' => ['name' => 'Giulia Rossi', 'email' => 'giulia.rossi@email.com', 'age' => 15],
                                    'course' => ['name' => 'Danza Moderna', 'code' => 'MOD-BAS-001'],
                                    'enrolled_date' => '2024-09-05',
                                    'status' => 'pending',
                                    'payment_status' => 'pending',
                                    'attendance' => 0,
                                    'next_payment' => '2024-09-05'
                                ],
                                [
                                    'student' => ['name' => 'Luca Bianchi', 'email' => 'luca.bianchi@email.com', 'age' => 13],
                                    'course' => ['name' => 'Jazz Dance', 'code' => 'JAZ-INT-001'],
                                    'enrolled_date' => '2024-08-20',
                                    'status' => 'active',
                                    'payment_status' => 'overdue',
                                    'attendance' => 85,
                                    'next_payment' => '2024-09-20'
                                ],
                                [
                                    'student' => ['name' => 'Emma Ferrari', 'email' => 'emma.ferrari@email.com', 'age' => 12],
                                    'course' => ['name' => 'Danza Classica Intermedio', 'code' => 'CLA-INT-001'],
                                    'enrolled_date' => '2024-09-08',
                                    'status' => 'active',
                                    'payment_status' => 'paid',
                                    'attendance' => 100,
                                    'next_payment' => '2024-10-08'
                                ]
                            ];
                        @endphp

                        @foreach ($enrollments as $enrollment)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                                {{ strtoupper(substr($enrollment['student']['name'], 0, 1) . substr(explode(' ', $enrollment['student']['name'])[1], 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $enrollment['student']['name'] }}</div>
                                            <div class="text-sm text-gray-500">{{ $enrollment['student']['email'] }}</div>
                                            <div class="text-xs text-gray-400">{{ $enrollment['student']['age'] }} anni</div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $enrollment['course']['name'] }}</div>
                                    <div class="text-sm text-gray-500">{{ $enrollment['course']['code'] }}</div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ date('d/m/Y', strtotime($enrollment['enrolled_date'])) }}
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        {{ $enrollment['status'] === 'active' ? 'bg-green-100 text-green-800' : 
                                           ($enrollment['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                            'bg-red-100 text-red-800') }}">
                                        @if ($enrollment['status'] === 'active')
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                            Attiva
                                        @elseif ($enrollment['status'] === 'pending')
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            In Attesa
                                        @else
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                            Sospesa
                                        @endif
                                    </span>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium 
                                        {{ $enrollment['payment_status'] === 'paid' ? 'text-green-600' : 
                                           ($enrollment['payment_status'] === 'pending' ? 'text-yellow-600' : 'text-red-600') }}">
                                        {{ ucfirst($enrollment['payment_status']) }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        Prossimo: {{ date('d/m/Y', strtotime($enrollment['next_payment'])) }}
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="text-sm font-medium 
                                            {{ $enrollment['attendance'] >= 90 ? 'text-green-600' : 
                                               ($enrollment['attendance'] >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                            {{ $enrollment['attendance'] }}%
                                        </div>
                                        <div class="ml-2 w-16 bg-gray-200 rounded-full h-2">
                                            <div class="h-2 rounded-full 
                                                {{ $enrollment['attendance'] >= 90 ? 'bg-green-500' : 
                                                   ($enrollment['attendance'] >= 75 ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                                 style="width: {{ $enrollment['attendance'] }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <button class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-100" title="Dettagli">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </button>
                                        <button class="text-green-600 hover:text-green-900 p-1 rounded-full hover:bg-green-100" title="Modifica">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </button>
                                        <button class="text-rose-600 hover:text-rose-900 p-1 rounded-full hover:bg-rose-100" title="Contatta">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                        </button>
                                        
                                        <div class="relative" x-data="{ open: false }">
                                            <button @click="open = !open" class="text-gray-400 hover:text-gray-600 p-1 rounded-full hover:bg-gray-100">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                                </svg>
                                            </button>
                                            
                                            <div x-show="open" @click.away="open = false" x-transition
                                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-20">
                                                <div class="py-1">
                                                    <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                        </svg>
                                                        Storico Pagamenti
                                                    </button>
                                                    <button class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                        </svg>
                                                        Registro Presenze
                                                    </button>
                                                    @if ($enrollment['status'] === 'active')
                                                        <button class="flex items-center w-full px-4 py-2 text-sm text-orange-600 hover:bg-orange-50">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                            </svg>
                                                            Sospendi Iscrizione
                                                        </button>
                                                    @elseif ($enrollment['status'] === 'pending')
                                                        <button class="flex items-center w-full px-4 py-2 text-sm text-green-600 hover:bg-green-50">
                                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                            </svg>
                                                            Approva Iscrizione
                                                        </button>
                                                    @endif
                                                    <div class="border-t border-gray-100"></div>
                                                    <button class="flex items-center w-full px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                        Elimina Iscrizione
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Mostrando 1-5 di 156 iscrizioni
                    </div>
                    <div class="flex items-center space-x-2">
                        <button class="px-3 py-1 text-sm text-gray-500 hover:text-gray-700">Precedente</button>
                        <button class="px-3 py-1 text-sm bg-rose-600 text-white rounded">1</button>
                        <button class="px-3 py-1 text-sm text-gray-500 hover:text-gray-700">2</button>
                        <button class="px-3 py-1 text-sm text-gray-500 hover:text-gray-700">3</button>
                        <button class="px-3 py-1 text-sm text-gray-500 hover:text-gray-700">Successivo</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- New Enrollment Modal -->
    <x-modal name="new-enrollment" maxWidth="2xl">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Nuova Iscrizione</h3>
                <button @click="$dispatch('close-modal', 'new-enrollment')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Student Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Studente</label>
                        <div class="relative">
                            <input type="text" placeholder="Cerca studente o crea nuovo..." 
                                   class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                            <button type="button" class="absolute right-2 top-2 text-rose-600 hover:text-rose-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </button>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Digita per cercare o clicca + per nuovo studente</p>
                    </div>

                    <!-- Course Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Corso</label>
                        <select class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                            <option>Seleziona corso</option>
                            <option>Danza Classica Intermedio (2 posti liberi)</option>
                            <option>Hip Hop Avanzato (1 posto libero)</option>
                            <option>Danza Moderna (6 posti liberi)</option>
                            <option>Jazz Dance (10 posti liberi)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Enrollment Date -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data Iscrizione</label>
                        <input type="date" value="{{ date('Y-m-d') }}" 
                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                    </div>

                    <!-- Payment Status -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Stato Pagamento</label>
                        <select class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500">
                            <option value="pending">In Attesa</option>
                            <option value="paid">Pagato</option>
                            <option value="partial">Parziale</option>
                        </select>
                    </div>
                </div>

                <!-- Special Options -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Opzioni Speciali</label>
                    <div class="space-y-2">
                        <div class="flex items-center">
                            <input type="checkbox" id="trial_lesson" 
                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <label for="trial_lesson" class="ml-2 text-sm text-gray-900">Lezione di prova gratuita</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="family_discount" 
                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <label for="family_discount" class="ml-2 text-sm text-gray-900">Sconto famiglia applicato</label>
                        </div>
                        <div class="flex items-center">
                            <input type="checkbox" id="send_welcome" checked 
                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <label for="send_welcome" class="ml-2 text-sm text-gray-900">Invia email di benvenuto</label>
                        </div>
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Note</label>
                    <textarea rows="3" 
                              class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                              placeholder="Note aggiuntive sull'iscrizione..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <button type="button" @click="$dispatch('close-modal', 'new-enrollment')" 
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                        Annulla
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500">
                        Crea Iscrizione
                    </button>
                </div>
            </form>
        </div>
    </x-modal>

    <!-- Bulk Actions Modal -->
    <x-modal name="bulk-actions" maxWidth="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Azioni Multiple</h3>
                <button @click="$dispatch('close-modal', 'bulk-actions')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-4">
                    Seleziona l'azione da eseguire sulle iscrizioni selezionate.
                </p>
                
                <div class="space-y-3">
                    <button class="flex items-center w-full p-3 text-left hover:bg-gray-50 rounded-lg border border-gray-200">
                        <svg class="w-5 h-5 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Invia Comunicazione</p>
                            <p class="text-sm text-gray-500">Email o SMS ai studenti selezionati</p>
                        </div>
                    </button>
                    
                    <button class="flex items-center w-full p-3 text-left hover:bg-gray-50 rounded-lg border border-gray-200">
                        <svg class="w-5 h-5 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Esporta Dati</p>
                            <p class="text-sm text-gray-500">Scarica Excel con dettagli iscrizioni</p>
                        </div>
                    </button>
                    
                    <button class="flex items-center w-full p-3 text-left hover:bg-gray-50 rounded-lg border border-gray-200">
                        <svg class="w-5 h-5 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="font-medium text-gray-900">Cambia Stato</p>
                            <p class="text-sm text-gray-500">Modifica stato di più iscrizioni</p>
                        </div>
                    </button>
                    
                    <button class="flex items-center w-full p-3 text-left hover:bg-red-50 rounded-lg border border-red-200">
                        <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        <div>
                            <p class="font-medium text-red-900">Elimina Iscrizioni</p>
                            <p class="text-sm text-red-700">Rimuovi iscrizioni selezionate</p>
                        </div>
                    </button>
                </div>
            </div>
            
            <div class="flex items-center justify-end space-x-3">
                <button @click="$dispatch('close-modal', 'bulk-actions')" 
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500">
                    Chiudi
                </button>
            </div>
        </div>
    </x-modal>
@endsection