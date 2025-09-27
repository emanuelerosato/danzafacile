<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettagli Pagamento #{{ $payment->receipt_number ?? $payment->id }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Visualizza e gestisci i dettagli del pagamento
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.payments.edit', $payment) }}"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifica
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
            <a href="{{ route('admin.payments.index') }}" class="text-gray-500 hover:text-gray-700">Pagamenti</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Dettagli Pagamento</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                <!-- Payment Status Banner -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            @if($payment->status === 'completed')
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                            @elseif($payment->status === 'pending')
                                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                            @elseif($payment->status === 'failed')
                                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                            @else
                                <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center mr-4">
                                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                </div>
                            @endif
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">
                                    Pagamento
                                    @if($payment->status === 'completed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Completato
                                        </span>
                                    @elseif($payment->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            In Attesa
                                        </span>
                                    @elseif($payment->status === 'failed')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            Fallito
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    @endif
                                </h3>
                                <p class="text-sm text-gray-600">
                                    {{ $payment->payment_type ? ucfirst(str_replace('_', ' ', $payment->payment_type)) : 'N/A' }}
                                    @if($payment->payment_date)
                                        • {{ $payment->payment_date->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-bold text-gray-900">€{{ number_format($payment->amount, 2) }}</p>
                            @if($payment->net_amount && $payment->net_amount != $payment->amount)
                                <p class="text-sm text-gray-600">Netto: €{{ number_format($payment->net_amount, 2) }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Payment Details -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Basic Information -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Informazioni Pagamento</h3>
                            </div>
                            <div class="p-6">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Studente</label>
                                        <p class="text-sm text-gray-900">{{ $payment->user->name ?? 'N/A' }}</p>
                                        @if($payment->user->email)
                                            <p class="text-xs text-gray-600">{{ $payment->user->email }}</p>
                                        @endif
                                    </div>

                                    @if($payment->course)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Corso</label>
                                            <p class="text-sm text-gray-900">{{ $payment->course->name }}</p>
                                            <p class="text-xs text-gray-600">€{{ number_format($payment->course->price, 2) }}</p>
                                        </div>
                                    @endif

                                    @if($payment->event)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Evento</label>
                                            <p class="text-sm text-gray-900">{{ $payment->event->name }}</p>
                                            <p class="text-xs text-gray-600">{{ $payment->event->start_date ? $payment->event->start_date->format('d/m/Y H:i') : 'N/A' }}</p>
                                        </div>
                                    @endif

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Metodo Pagamento</label>
                                        <p class="text-sm text-gray-900">
                                            @switch($payment->payment_method)
                                                @case('cash')
                                                    Contanti
                                                    @break
                                                @case('bank_transfer')
                                                    Bonifico Bancario
                                                    @break
                                                @case('card')
                                                    Carta di Credito/Debito
                                                    @break
                                                @case('paypal')
                                                    PayPal
                                                    @break
                                                @default
                                                    {{ ucfirst($payment->payment_method ?? 'N/A') }}
                                            @endswitch
                                        </p>
                                    </div>

                                    @if($payment->reference_number)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Numero Riferimento</label>
                                            <p class="text-sm text-gray-900 font-mono">{{ $payment->reference_number }}</p>
                                        </div>
                                    @endif

                                    @if($payment->transaction_id)
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">ID Transazione</label>
                                            <p class="text-sm text-gray-900 font-mono">{{ $payment->transaction_id }}</p>
                                        </div>
                                    @endif
                                </div>

                                @if($payment->notes)
                                    <div class="mt-6">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
                                        <p class="text-sm text-gray-900 bg-gray-50 p-3 rounded-lg">{{ $payment->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Installments (if any) -->
                        @if($payment->installments && $payment->installments->count() > 0)
                            <div class="bg-white rounded-lg shadow">
                                <div class="px-6 py-4 border-b border-gray-200">
                                    <h3 class="text-lg font-semibold text-gray-900">Rate Pagamento</h3>
                                </div>
                                <div class="p-6">
                                    <div class="space-y-4">
                                        @foreach($payment->installments as $installment)
                                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">
                                                        Rata #{{ $installment->installment_number }}
                                                    </p>
                                                    <p class="text-xs text-gray-600">
                                                        Scadenza: {{ $installment->due_date ? $installment->due_date->format('d/m/Y') : 'N/A' }}
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <p class="text-sm font-semibold text-gray-900">€{{ number_format($installment->amount, 2) }}</p>
                                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                        @if($installment->status === 'completed') bg-green-100 text-green-800
                                                        @elseif($installment->status === 'pending') bg-yellow-100 text-yellow-800
                                                        @else bg-gray-100 text-gray-800 @endif">
                                                        {{ ucfirst($installment->status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Actions & Summary -->
                    <div class="space-y-6">
                        <!-- Amount Breakdown -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Riepilogo Importi</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Importo Base</span>
                                    <span class="text-sm font-medium text-gray-900">€{{ number_format($payment->amount, 2) }}</span>
                                </div>

                                @if($payment->discount_amount && $payment->discount_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Sconto</span>
                                        <span class="text-sm font-medium text-red-600">-€{{ number_format($payment->discount_amount, 2) }}</span>
                                    </div>
                                @endif

                                @if($payment->tax_amount && $payment->tax_amount > 0)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Tasse</span>
                                        <span class="text-sm font-medium text-gray-900">€{{ number_format($payment->tax_amount, 2) }}</span>
                                    </div>
                                @endif

                                @if($payment->payment_gateway_fee && $payment->payment_gateway_fee > 0)
                                    <div class="flex justify-between">
                                        <span class="text-sm text-gray-600">Commissioni</span>
                                        <span class="text-sm font-medium text-gray-900">€{{ number_format($payment->payment_gateway_fee, 2) }}</span>
                                    </div>
                                @endif

                                @if($payment->net_amount && $payment->net_amount != $payment->amount)
                                    <div class="border-t border-gray-200 pt-3 flex justify-between">
                                        <span class="text-sm font-semibold text-gray-900">Totale Netto</span>
                                        <span class="text-sm font-semibold text-gray-900">€{{ number_format($payment->net_amount, 2) }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Dates -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Date Importanti</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                @if($payment->payment_date)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Data Pagamento</label>
                                        <p class="text-sm text-gray-900">{{ $payment->payment_date->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif

                                @if($payment->due_date)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Scadenza</label>
                                        <p class="text-sm text-gray-900">{{ $payment->due_date->format('d/m/Y') }}</p>
                                    </div>
                                @endif

                                <div>
                                    <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Data Creazione</label>
                                    <p class="text-sm text-gray-900">{{ $payment->created_at->format('d/m/Y H:i') }}</p>
                                </div>

                                @if($payment->updated_at && $payment->updated_at != $payment->created_at)
                                    <div>
                                        <label class="block text-xs font-medium text-gray-500 uppercase tracking-wider">Ultima Modifica</label>
                                        <p class="text-sm text-gray-900">{{ $payment->updated_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="bg-white rounded-lg shadow">
                            <div class="px-6 py-4 border-b border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900">Azioni</h3>
                            </div>
                            <div class="p-6 space-y-3">
                                <a href="{{ route('admin.payments.edit', $payment) }}"
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Modifica Pagamento
                                </a>

                                @if($payment->status === 'pending')
                                    <button onclick="markCompleted({{ $payment->id }})"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        Segna come Completato
                                    </button>
                                @endif

                                @if($payment->status === 'completed')
                                    <button onclick="sendReceipt({{ $payment->id }})"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                        Invia Ricevuta
                                    </button>

                                    <button onclick="processRefundWithModal({{ $payment->id }})"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                        </svg>
                                        Elabora Rimborso
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>