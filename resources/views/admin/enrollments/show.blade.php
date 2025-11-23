<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettagli Iscrizione
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione iscrizione di {{ $enrollment->user->name ?? 'Studente' }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.enrollments.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Torna alla Lista
                </a>
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
            <a href="{{ route('admin.enrollments.index') }}" class="text-gray-500 hover:text-gray-700">Iscrizioni</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Dettaglio</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow mb-6 p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-rose-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl">
                            {{ strtoupper(substr($enrollment->user->name ?? 'N/A', 0, 2)) }}
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900">{{ $enrollment->user->name ?? 'Studente N/A' }}</h3>
                            <p class="text-gray-600">Corso: {{ $enrollment->course->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-500">
                                Iscritto il: {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y') : 'N/A' }}
                            </p>
                        </div>
                    </div>
                    <div x-data="{ status: '{{ $enrollment->status ?? 'unknown' }}' }">
                        <span :class="{
                            'bg-green-100 text-green-800': status === 'active',
                            'bg-yellow-100 text-yellow-800': status === 'pending',
                            'bg-red-100 text-red-800': status === 'cancelled',
                            'bg-gray-100 text-gray-800': status === 'unknown'
                        }" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                            <span x-text="status === 'active' ? 'Attivo' : (status === 'pending' ? 'In Attesa' : (status === 'cancelled' ? 'Cancellato' : 'Sconosciuto'))"></span>
                        </span>

                        @if($enrollment->status !== 'cancelled')
                            <button data-enrollment-action="toggle-status"
                                    data-enrollment-id="{{ $enrollment->id }}"
                                    class="ml-3 inline-flex items-center px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Cancella
                            </button>
                        @else
                            <button data-enrollment-action="toggle-status"
                                    data-enrollment-id="{{ $enrollment->id }}"
                                    class="ml-3 inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm rounded-lg hover:bg-green-700 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Riattiva
                            </button>
                        @endif

                        <button data-enrollment-action="delete"
                                data-enrollment-id="{{ $enrollment->id }}"
                                class="ml-2 inline-flex items-center px-3 py-1 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors duration-200">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Elimina
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabs Content -->
            <div x-data="{ activeTab: 'info' }" class="bg-white rounded-lg shadow">
                <!-- Tab Navigation -->
                <div class="border-b border-gray-200">
                    <nav class="flex space-x-8 px-6">
                        <button @click="activeTab = 'info'"
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'info' }"
                                class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200">
                            Informazioni
                        </button>
                        <button @click="activeTab = 'payments'"
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'payments' }"
                                class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200">
                            Pagamenti ({{ $enrollment->payments->count() ?? 0 }})
                        </button>
                        <button @click="activeTab = 'history'"
                                :class="{ 'border-rose-500 text-rose-600': activeTab === 'history' }"
                                class="py-4 px-1 border-b-2 border-transparent font-medium text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-colors duration-200">
                            Storico
                        </button>
                    </nav>
                </div>

                <!-- Tab Content -->
                <div class="p-6">
                    <!-- Info Tab -->
                    <div x-show="activeTab === 'info'" x-transition class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Studente</h4>
                                <div class="space-y-2 text-sm">
                                    <p class="text-gray-600"><strong class="text-gray-900">Nome:</strong> {{ $enrollment->user->name ?? 'N/A' }}</p>
                                    <p class="text-gray-600"><strong class="text-gray-900">Email:</strong> {{ $enrollment->user->email ?? 'N/A' }}</p>
                                    @if($enrollment->user->phone ?? null)
                                        <p class="text-gray-600"><strong class="text-gray-900">Telefono:</strong> {{ $enrollment->user->phone }}</p>
                                    @endif
                                    @if($enrollment->user->date_of_birth ?? null)
                                        <p class="text-gray-600"><strong class="text-gray-900">Data di nascita:</strong> {{ $enrollment->user->date_of_birth->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-medium text-gray-900 mb-3">Corso</h4>
                                <div class="space-y-2 text-sm">
                                    <p class="text-gray-600"><strong class="text-gray-900">Nome:</strong> {{ $enrollment->course->name ?? 'N/A' }}</p>
                                    <p class="text-gray-600"><strong class="text-gray-900">Livello:</strong> {{ ucfirst($enrollment->course->level ?? 'N/A') }}</p>
                                    @if($enrollment->course->price ?? null)
                                        <p class="text-gray-600"><strong class="text-gray-900">Prezzo:</strong> € {{ number_format($enrollment->course->price, 2, ',', '.') }}</p>
                                    @endif
                                    @if($enrollment->course->start_date ?? null)
                                        <p class="text-gray-600"><strong class="text-gray-900">Inizio:</strong> {{ $enrollment->course->start_date->format('d/m/Y') }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-3">Dettagli Iscrizione</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                                <p class="text-gray-600">
                                    <strong class="text-gray-900">Data iscrizione:</strong>
                                    {{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                                <p class="text-gray-600">
                                    <strong class="text-gray-900">Stato:</strong>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        {{ $enrollment->status == 'active' ? 'bg-green-100 text-green-800' :
                                           ($enrollment->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ ucfirst($enrollment->status ?? 'Unknown') }}
                                    </span>
                                </p>
                                <p class="text-gray-600">
                                    <strong class="text-gray-900">Stato pagamenti:</strong>
                                    {{ ucfirst($enrollment->payment_status ?? 'Non specificato') }}
                                </p>
                                <p class="text-gray-600">
                                    <strong class="text-gray-900">Creata:</strong>
                                    {{ $enrollment->created_at ? $enrollment->created_at->format('d/m/Y H:i') : 'N/A' }}
                                </p>
                            </div>

                            @if($enrollment->notes ?? null)
                                <div class="mt-4">
                                    <h5 class="text-sm font-medium text-gray-900 mb-2">Note</h5>
                                    <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded-lg">{{ $enrollment->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Payments Tab -->
                    <div x-show="activeTab === 'payments'" x-transition>
                        @if(isset($enrollment->payments) && $enrollment->payments->count() > 0)
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Importo</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stato</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metodo</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($enrollment->payments as $payment)
                                            <tr class="hover:bg-gray-50">
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {{ $payment->payment_date ? $payment->payment_date->format('d/m/Y') : 'N/A' }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    € {{ number_format($payment->amount ?? 0, 2, ',', '.') }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                                        {{ ($payment->status ?? '') === 'completed' ? 'bg-green-100 text-green-800' :
                                                           (($payment->status ?? '') === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                        {{ ucfirst($payment->status ?? 'Unknown') }}
                                                    </span>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                                    {{ $payment->payment_method ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun pagamento</h3>
                                <p class="mt-1 text-sm text-gray-500">Nessun pagamento registrato per questa iscrizione.</p>
                            </div>
                        @endif
                    </div>

                    <!-- History Tab -->
                    <div x-show="activeTab === 'history'" x-transition>
                        <div class="space-y-4">
                            <div class="border-l-4 border-blue-400 pl-4 py-2">
                                <div class="text-sm font-medium text-gray-900">Iscrizione creata</div>
                                <div class="text-sm text-gray-600">
                                    {{ $enrollment->created_at ? $enrollment->created_at->format('d/m/Y H:i') : 'Data non disponibile' }}
                                </div>
                            </div>
                            @if($enrollment->updated_at && $enrollment->updated_at != $enrollment->created_at)
                                <div class="border-l-4 border-yellow-400 pl-4 py-2">
                                    <div class="text-sm font-medium text-gray-900">Ultima modifica</div>
                                    <div class="text-sm text-gray-600">{{ $enrollment->updated_at->format('d/m/Y H:i') }}</div>
                                </div>
                            @endif
                            @if($enrollment->status === 'cancelled')
                                <div class="border-l-4 border-red-400 pl-4 py-2">
                                    <div class="text-sm font-medium text-gray-900">Iscrizione cancellata</div>
                                    <div class="text-sm text-gray-600">Stato attuale: Cancellato</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include JavaScript per funzionalità interattive --}}
    @vite('resources/js/admin/enrollments/enrollment-manager.js')

    {{-- Dati per JavaScript --}}
    <script nonce="@cspNonce">
        // Expose enrollment data to JavaScript
        window.enrollmentsData = [@json($enrollment)];
        console.log('📄 Enrollment show page loaded with data:', window.enrollmentsData);
    </script>
</x-app-layout>