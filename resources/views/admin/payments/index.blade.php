{{-- Usa la versione semplificata del PaymentManager --}}
@vite('resources/js/admin/payments/payment-manager-simple.js')

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Pagamenti
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Monitora e gestisci tutti i pagamenti della scuola
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
        <li class="text-gray-900 font-medium">Pagamenti</li>
    </x-slot>



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8" x-data="paymentManager()">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-end mb-6">
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
            <button @click="openBulkModal()"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Azioni Multiple
            </button>
            <button @click="exportPayments()"
                    :disabled="isLoading"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200 disabled:opacity-50">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-show="!isLoading">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <svg class="w-4 h-4 mr-2 animate-spin" fill="none" viewBox="0 0 24 24" x-show="isLoading">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span x-text="isLoading ? 'Esportando...' : 'Esporta'"></span>
            </button>
            <a href="{{ route('admin.payments.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuovo Pagamento
            </a>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stats-card
            title="Pagamenti Totali"
            :value="number_format($stats['total_payments'] ?? 0)"
            :subtitle="'Gestiti'"
            icon="clipboard-list"
            color="blue"
            :change="15"
            changeType="increase"
        />

        <x-stats-card
            title="Incasso Totale"
            :value="'€' . number_format($stats['completed_amount'] ?? 0, 2, ',', '.')"
            :subtitle="'Completati'"
            icon="currency-dollar"
            color="green"
            :change="22"
            changeType="increase"
        />

        <x-stats-card
            title="In Attesa"
            :value="number_format($stats['pending_payments'] ?? 0)"
            :subtitle="'Da confermare'"
            icon="clock"
            color="yellow"
            :change="5"
            changeType="increase"
        />

        <x-stats-card
            title="Scaduti"
            :value="number_format($stats['overdue_payments'] ?? 0)"
            :subtitle="'Richiedono attenzione'"
            icon="exclamation-triangle"
            color="red"
            :change="-3"
            changeType="decrease"
        />
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg shadow mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Filtri e Ricerca</h3>
        </div>
        <div class="p-6">
            <form id="filtersForm" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Ricerca</label>
                    <input type="text" id="search" name="search"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                           placeholder="Nome, email, ricevuta..."
                           value="{{ request('search') }}">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                    <select id="status" name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="">Tutti</option>
                        @foreach($filterOptions['statuses'] ?? [] as $key => $label)
                            <option value="{{ $key }}"
                                    {{ request('status') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method -->
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700 mb-1">Metodo</label>
                    <select id="payment_method" name="payment_method"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="">Tutti</option>
                        @foreach($filterOptions['methods'] ?? [] as $key => $label)
                            <option value="{{ $key }}"
                                    {{ request('payment_method') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Type -->
                <div>
                    <label for="payment_type" class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                    <select id="payment_type" name="payment_type"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="">Tutti</option>
                        @foreach($filterOptions['types'] ?? [] as $key => $label)
                            <option value="{{ $key }}"
                                    {{ request('payment_type') == $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label for="date_range" class="block text-sm font-medium text-gray-700 mb-1">Periodo</label>
                    <div class="flex space-x-2">
                        <input type="date" name="date_from"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                               value="{{ request('date_from') }}" placeholder="Da">
                        <input type="date" name="date_to"
                               class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                               value="{{ request('date_to') }}" placeholder="A">
                    </div>
                </div>

                <div class="lg:col-span-5 flex justify-end space-x-3">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                        Applica Filtri
                    </button>
                    <a href="{{ route('admin.payments.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        Reset
                    </a>
                    <a href="{{ route('admin.payments.export', request()->all()) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        Esporta CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Lista Pagamenti</h3>
            <div class="relative inline-block text-left">
                <button type="button" id="bulkActionBtn" disabled
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
                        onclick="toggleBulkDropdown()">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    Azioni Multiple
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div id="bulkDropdown" class="hidden absolute right-0 mt-2 w-56 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 z-50">
                    <div class="py-1">
                        <a href="#" data-action="mark_completed" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Segna come Completati</a>
                        <a href="#" data-action="mark_pending" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Segna come In Attesa</a>
                        <a href="#" data-action="send_receipts" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Invia Ricevute</a>
                        <div class="border-t border-gray-100"></div>
                        <a href="#" data-action="delete" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50">Elimina</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-10">
                                <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Studente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo/Dettagli</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Importo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metodo</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stato</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Scadenza</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-32">Azioni</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($payments as $payment)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500 payment-checkbox"
                                       value="{{ $payment->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    @if($payment->user->profile_image_path)
                                        <img src="{{ $payment->user->profile_image_url }}"
                                             class="w-8 h-8 rounded-full mr-3" alt="Avatar">
                                    @else
                                        <div class="w-8 h-8 bg-gray-300 rounded-full mr-3 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">{{ $payment->user->full_name ?? $payment->user->name }}</div>
                                        <div class="text-sm text-gray-500">{{ $payment->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="space-y-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $payment->payment_type_name }}</span>
                                    @if($payment->is_installment)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">Rata {{ $payment->installment_number }}/{{ $payment->total_installments }}</span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    @if($payment->course)
                                        <div class="text-sm text-gray-500">Corso: {{ $payment->course->name }}</div>
                                    @elseif($payment->event)
                                        <div class="text-sm text-gray-500">Evento: {{ $payment->event->name }}</div>
                                    @endif
                                </div>
                                @if($payment->receipt_number)
                                    <div class="text-sm text-gray-500">Ric: {{ $payment->receipt_number }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $payment->formatted_full_amount }}</div>
                                @if($payment->installments && $payment->installments->count() > 0)
                                    <div class="text-sm text-gray-500">
                                        Pagato: € {{ number_format($payment->getTotalPaidForInstallments(), 2, ',', '.') }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">{{ $payment->payment_method_name }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'completed' => 'bg-green-100 text-green-800',
                                        'failed' => 'bg-red-100 text-red-800',
                                        'refunded' => 'bg-blue-100 text-blue-800',
                                        'cancelled' => 'bg-gray-100 text-gray-800',
                                        'processing' => 'bg-blue-100 text-blue-800',
                                        'partial' => 'bg-yellow-100 text-yellow-800'
                                    ];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClasses[$payment->status] ?? 'bg-gray-100 text-gray-800' }}">
                                    {{ $payment->status_name }}
                                </span>
                                @if($payment->is_overdue)
                                    <div class="text-xs text-red-600 mt-1">
                                        <svg class="w-3 h-3 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                        </svg>
                                        Scaduto
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($payment->payment_date)
                                    <div>{{ $payment->payment_date->format('d/m/Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $payment->payment_date->format('H:i') }}</div>
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($payment->due_date)
                                    <div>{{ $payment->due_date->format('d/m/Y') }}</div>
                                    @if($payment->is_overdue)
                                        <div class="text-xs text-red-600">Scaduto</div>
                                    @endif
                                @else
                                    <span class="text-gray-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="relative inline-block text-left">
                                    <button type="button"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200"
                                            data-dropdown-toggle="paymentDropdown{{ $payment->id }}"
                                            onclick="
                                                if(typeof togglePaymentDropdown !== 'undefined') {
                                                    togglePaymentDropdown({{ $payment->id }});
                                                } else {
                                                    const dropdown = document.getElementById('paymentDropdown{{ $payment->id }}');
                                                    if (dropdown) {
                                                        dropdown.classList.toggle('hidden');
                                                    }
                                                }
                                            "
                                            title="Azioni disponibili">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
                                        </svg>
                                    </button>
                                    <div id="paymentDropdown{{ $payment->id }}" class="hidden absolute right-0 mt-2 w-56 rounded-lg shadow-xl bg-white ring-1 ring-black ring-opacity-5 border border-gray-200 z-50">
                                        <div class="py-2">
                                            <!-- Azioni di visualizzazione -->
                                            <a href="{{ route('admin.payments.show', $payment) }}" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-rose-50 hover:text-rose-700 transition-all duration-200">
                                                <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                </svg>
                                                <span class="font-medium">Visualizza Dettagli</span>
                                            </a>
                                            <a href="{{ route('admin.payments.edit', $payment) }}" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-all duration-200">
                                                <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                                <span class="font-medium">Modifica</span>
                                            </a>
                                            @if($payment->status === 'pending')
                                            <!-- Separatore -->
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <!-- Azioni di stato -->
                                            <a href="#" onclick="markCompleted({{ $payment->id }})" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-green-50 hover:text-green-700 transition-all duration-200">
                                                <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                <span class="font-medium">Segna Completato</span>
                                            </a>
                                            @endif
                                            @if($payment->canBeRefunded())
                                            <a href="#" @click.prevent="openRefundModal({{ $payment->id }})" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-orange-50 hover:text-orange-700 transition-all duration-200">
                                                <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                                </svg>
                                                <span class="font-medium">Rimborsa</span>
                                            </a>
                                            @endif
                                            @if($payment->receipt_number)
                                            <!-- Separatore -->
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <!-- Azioni documenti -->
                                            <a href="{{ route('admin.payments.receipt', $payment) }}" target="_blank" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-purple-50 hover:text-purple-700 transition-all duration-200">
                                                <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="font-medium">Scarica Ricevuta</span>
                                            </a>
                                            <a href="#" onclick="sendReceipt({{ $payment->id }})" class="group flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 transition-all duration-200">
                                                <svg class="mr-3 h-4 w-4 text-gray-400 group-hover:text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="font-medium">Invia Ricevuta</span>
                                            </a>
                                            @endif
                                            <!-- Separatore per azioni pericolose -->
                                            <div class="border-t border-gray-100 my-1"></div>
                                            <!-- Azioni pericolose -->
                                            <a href="#" onclick="deletePayment({{ $payment->id }})" class="group flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 hover:text-red-700 transition-all duration-200">
                                                <svg class="mr-3 h-4 w-4 text-red-400 group-hover:text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                                <span class="font-medium">Elimina Pagamento</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center">
                                <div class="text-gray-500">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                    </svg>
                                    <p class="text-sm">Nessun pagamento trovato</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $payments->links() }}
        </div>
        @endif
    </div>
</div>

{{-- Tutti i modali all'interno del componente Alpine.js principale --}}
<!-- Modal Azioni Multiple -->
<div x-show="showBulkModal"
     x-cloak
     class="fixed inset-0 z-50 overflow-y-auto"
     x-transition:enter="ease-out duration-300"
     x-transition:enter-start="opacity-0"
     x-transition:enter-end="opacity-100"
     x-transition:leave="ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0">

    <div class="fixed inset-0 bg-black/50" @click="closeBulkModal()"></div>

    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-md transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">

            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">
                    Azioni Multiple
                </h3>
                <p class="text-sm text-gray-600 mt-1">
                    <span x-text="selectedCount"></span> pagamenti selezionati
                </p>
            </div>

            <div class="px-6 py-4 space-y-3">
                <button @click="performBulkAction('mark_completed')"
                        :disabled="isLoading"
                        class="w-full flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 disabled:opacity-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Marca come Completati
                </button>

                <button @click="performBulkAction('mark_pending')"
                        :disabled="isLoading"
                        class="w-full flex items-center justify-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 disabled:opacity-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Marca come In Attesa
                </button>

                <button @click="performBulkAction('send_receipts')"
                        :disabled="isLoading"
                        class="w-full flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-50">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Invia Ricevute
                </button>

                <div class="border-t border-gray-200 pt-3">
                    <button @click="performBulkAction('delete')"
                            :disabled="isLoading"
                            class="w-full flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 disabled:opacity-50">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Elimina Selezionati
                    </button>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button @click="closeBulkModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Annulla
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Include Refund Modal --}}
@include('admin.payments.modals.refund')

<script>
// Funzioni JavaScript per altre azioni
function sendReceipt(paymentId) {
    console.log('Sending receipt for payment:', paymentId);
    // Implementare logica per inviare ricevuta
}

function deletePayment(paymentId) {
    if (confirm('Sei sicuro di voler eliminare questo pagamento?')) {
        console.log('Deleting payment:', paymentId);
        // Implementare logica per eliminare pagamento
    }
}

function markCompleted(paymentId) {
    if (confirm('Sei sicuro di voler marcare questo pagamento come completato?')) {
        fetch(`/admin/payments/${paymentId}/mark-completed`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Errore: ' + (data.message || 'Operazione fallita'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Errore durante l\'operazione');
        });
    }
}

function deletePayment(paymentId) {
    if (confirm('Sei sicuro di voler eliminare questo pagamento? Questa azione non può essere annullata.')) {
        fetch(`/admin/payments/${paymentId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Errore: ' + (data.message || 'Operazione fallita'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Errore durante l\'operazione');
        });
    }
}

function sendReceipt(paymentId) {
    fetch(`/admin/payments/${paymentId}/send-receipt`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Ricevuta inviata con successo!');
        } else {
            alert('Errore: ' + (data.message || 'Invio fallito'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore durante l\'invio');
    });
}
</script>
</x-app-layout>
