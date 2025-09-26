<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettagli Registrazione
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Visualizza i dettagli della registrazione evento
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.event-registrations.index') }}" class="text-gray-500 hover:text-gray-700">Registrazioni Eventi</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Dettagli Registrazione</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center justify-between">
                    <div>
                        <div class="flex flex-col sm:flex-row items-start gap-3 sm:space-x-3 sm:gap-0">
                            <h1 class="text-xl md:text-2xl font-bold text-gray-900">
                                Registrazione #{{ $registration->id }}
                            </h1>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($registration->status === 'confirmed') bg-green-100 text-green-800
                                @elseif($registration->status === 'registered') bg-blue-100 text-blue-800
                                @elseif($registration->status === 'waitlist') bg-yellow-100 text-yellow-800
                                @elseif($registration->status === 'cancelled') bg-red-100 text-red-800
                                @elseif($registration->status === 'attended') bg-purple-100 text-purple-800
                                @else bg-gray-100 text-gray-800 @endif">
                                @switch($registration->status)
                                    @case('confirmed')
                                        ✓ Confermato
                                        @break
                                    @case('registered')
                                        📝 Registrato
                                        @break
                                    @case('waitlist')
                                        ⏳ Lista Attesa
                                        @break
                                    @case('cancelled')
                                        ✗ Annullato
                                        @break
                                    @case('attended')
                                        🎯 Partecipato
                                        @break
                                    @default
                                        {{ ucfirst($registration->status) }}
                                @endswitch
                            </span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            Registrazione effettuata il {{ $registration->registration_date->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                        <a href="{{ route('admin.event-registrations.index') }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z"/>
                            </svg>
                            Torna all'Elenco
                        </a>
                    </div>
                </div>

                <!-- Main Content Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Main Details -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Event Information -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <svg class="w-5 h-5 inline mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a1 1 0 012-2h4a1 1 0 012 2v4m-8 6v8a1 1 0 002 2h4a1 1 0 002-2v-8M6 7h12a2 2 0 012 2v8a2 2 0 01-2 2H6a2 2 0 01-2-2V9a2 2 0 012-2z"/>
                                </svg>
                                Dettagli Evento
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome Evento</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $registration->event->name }}</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Data e Ora Inizio</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $registration->event->start_date->format('d/m/Y H:i') }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Data e Ora Fine</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $registration->event->end_date ? $registration->event->end_date->format('d/m/Y H:i') : 'Non specificata' }}</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Tipo Evento</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ ucfirst($registration->event->type) }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Massimo Partecipanti</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $registration->event->max_participants ?? 'Illimitato' }}</p>
                                    </div>
                                </div>
                                @if($registration->event->description)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Descrizione</label>
                                    <p class="mt-1 text-sm text-gray-700">{{ $registration->event->description }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- User Information -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">
                                <svg class="w-5 h-5 inline mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Dettagli Partecipante
                            </h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nome Completo</label>
                                    <p class="mt-1 text-lg font-semibold text-gray-900">{{ $registration->user->name }}</p>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Email</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $registration->user->email }}</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Telefono</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $registration->user->phone ?? 'Non specificato' }}</p>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data di Nascita</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $registration->user->date_of_birth ? $registration->user->date_of_birth->format('d/m/Y') : 'Non specificata' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="space-y-6">
                        <!-- Registration Status -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Status Registrazione</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Status Attuale</label>
                                    <div class="mt-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                            @if($registration->status === 'confirmed') bg-green-100 text-green-800
                                            @elseif($registration->status === 'registered') bg-blue-100 text-blue-800
                                            @elseif($registration->status === 'waitlist') bg-yellow-100 text-yellow-800
                                            @elseif($registration->status === 'cancelled') bg-red-100 text-red-800
                                            @elseif($registration->status === 'attended') bg-purple-100 text-purple-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            @switch($registration->status)
                                                @case('confirmed')
                                                    ✓ Confermato
                                                    @break
                                                @case('registered')
                                                    📝 Registrato
                                                    @break
                                                @case('waitlist')
                                                    ⏳ Lista Attesa
                                                    @break
                                                @case('cancelled')
                                                    ✗ Annullato
                                                    @break
                                                @case('attended')
                                                    🎯 Partecipato
                                                    @break
                                                @default
                                                    {{ ucfirst($registration->status) }}
                                            @endswitch
                                        </span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data Registrazione</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $registration->registration_date->format('d/m/Y H:i') }}</p>
                                </div>

                                @if($registration->confirmed_at)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Data Conferma</label>
                                    <p class="mt-1 text-sm text-gray-900">{{ $registration->confirmed_at->format('d/m/Y H:i') }}</p>
                                </div>
                                @endif

                                @if($registration->notes)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Note</label>
                                    <p class="mt-1 text-sm text-gray-700">{{ $registration->notes }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Azioni Rapide</h3>
                            <div class="space-y-3">
                                @if($registration->status !== 'confirmed')
                                <button onclick="updateStatus({{ $registration->id }}, 'confirmed')"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Conferma
                                </button>
                                @endif

                                @if($registration->status !== 'waitlist')
                                <button onclick="updateStatus({{ $registration->id }}, 'waitlist')"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Lista Attesa
                                </button>
                                @endif

                                @if($registration->status !== 'cancelled')
                                <button onclick="updateStatus({{ $registration->id }}, 'cancelled')"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Annulla
                                </button>
                                @endif

                                @if($registration->status !== 'attended')
                                <button onclick="updateStatus({{ $registration->id }}, 'attended')"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Segna Partecipato
                                </button>
                                @endif

                                <hr class="my-4">

                                <button onclick="deleteRegistration({{ $registration->id }})"
                                        class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Elimina Registrazione
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    @vite('resources/js/admin/event-registrations/event-registrations-manager.js')
    @endpush
</x-app-layout>