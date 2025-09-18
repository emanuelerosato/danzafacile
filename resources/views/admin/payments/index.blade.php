<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Pagamenti
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione pagamenti della tua scuola
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



<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 py-8" x-data="paymentManager()">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestione Pagamenti</h1>
            <p class="text-gray-600">Tutti i pagamenti della tua scuola di danza</p>
        </div>
        <div class="flex items-center space-x-3">
            <button @click="openBulkModal()"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                </svg>
                Azioni Multiple
            </button>
            <button @click="exportPayments()"
                    class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Esporta
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
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Filtri e Ricerca</h3>
        </div>
        <div class="p-6">
            <form id="filtersForm" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Search -->
                <div>
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Ricerca</label>
                    <input type="text" id="search" name="search"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                           placeholder="Nome, email, ricevuta..."
                           value="{{ request('search') }}">
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                    <select id="status" name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
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
                            class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700 transition-colors">
                        Applica Filtri
                    </button>
                    <a href="{{ route('admin.payments.index') }}"
                       class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Reset
                    </a>
                    <a href="{{ route('admin.payments.export', request()->all()) }}"
                       class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors">
                        Esporta CSV
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg font-medium text-gray-900">Lista Pagamenti</h3>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-primary btn-sm" id="bulkActionBtn"
                        disabled data-bs-toggle="dropdown">
                    <i class="fas fa-tasks me-1"></i>Azioni Multiple
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" data-action="mark_completed">Segna come Completati</a></li>
                    <li><a class="dropdown-item" href="#" data-action="mark_pending">Segna come In Attesa</a></li>
                    <li><a class="dropdown-item" href="#" data-action="send_receipts">Invia Ricevute</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item text-danger" href="#" data-action="delete">Elimina</a></li>
                </ul>
            </div>
        </div>
        <div class="overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="40">
                                <input type="checkbox" id="selectAll" class="form-check-input">
                            </th>
                            <th>Studente</th>
                            <th>Tipo/Dettagli</th>
                            <th>Importo</th>
                            <th>Metodo</th>
                            <th>Stato</th>
                            <th>Data</th>
                            <th>Scadenza</th>
                            <th width="120">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input payment-checkbox"
                                       value="{{ $payment->id }}">
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($payment->user->profile_image_path)
                                        <img src="{{ $payment->user->profile_image_url }}"
                                             class="rounded-circle me-2" width="32" height="32" alt="Avatar">
                                    @else
                                        <div class="bg-secondary rounded-circle me-2 d-flex align-items-center justify-content-center"
                                             style="width: 32px; height: 32px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-semibold">{{ $payment->user->full_name ?? $payment->user->name }}</div>
                                        <small class="text-muted">{{ $payment->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div>
                                    <span class="badge bg-info">{{ $payment->payment_type_name }}</span>
                                    @if($payment->is_installment)
                                        <span class="badge bg-warning">Rata {{ $payment->installment_number }}/{{ $payment->total_installments }}</span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    @if($payment->course)
                                        <small class="text-muted">Corso: {{ $payment->course->name }}</small>
                                    @elseif($payment->event)
                                        <small class="text-muted">Evento: {{ $payment->event->name }}</small>
                                    @endif
                                </div>
                                @if($payment->receipt_number)
                                    <small class="text-muted">Ric: {{ $payment->receipt_number }}</small>
                                @endif
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $payment->formatted_full_amount }}</div>
                                @if($payment->installments && $payment->installments->count() > 0)
                                    <small class="text-muted">
                                        Pagato: € {{ number_format($payment->getTotalPaidForInstallments(), 2, ',', '.') }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $payment->payment_method_name }}</span>
                            </td>
                            <td>
                                @php
                                    $statusClasses = [
                                        'pending' => 'bg-warning',
                                        'completed' => 'bg-success',
                                        'failed' => 'bg-danger',
                                        'refunded' => 'bg-info',
                                        'cancelled' => 'bg-secondary',
                                        'processing' => 'bg-primary',
                                        'partial' => 'bg-warning'
                                    ];
                                @endphp
                                <span class="badge {{ $statusClasses[$payment->status] ?? 'bg-secondary' }}">
                                    {{ $payment->status_name }}
                                </span>
                                @if($payment->is_overdue)
                                    <br><small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Scaduto</small>
                                @endif
                            </td>
                            <td>
                                @if($payment->payment_date)
                                    {{ $payment->payment_date->format('d/m/Y') }}
                                    <br><small class="text-muted">{{ $payment->payment_date->format('H:i') }}</small>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($payment->due_date)
                                    {{ $payment->due_date->format('d/m/Y') }}
                                    @if($payment->is_overdue)
                                        <br><small class="text-danger">Scaduto</small>
                                    @endif
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle"
                                            data-bs-toggle="dropdown">
                                        <i class="fas fa-cog"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="{{ route('admin.payments.show', $payment) }}">
                                            <i class="fas fa-eye me-1"></i>Visualizza
                                        </a></li>
                                        <li><a class="dropdown-item" href="{{ route('admin.payments.edit', $payment) }}">
                                            <i class="fas fa-edit me-1"></i>Modifica
                                        </a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        @if($payment->status === 'pending')
                                        <li><a class="dropdown-item" href="#" onclick="markCompleted({{ $payment->id }})">
                                            <i class="fas fa-check me-1"></i>Segna Completato
                                        </a></li>
                                        @endif
                                        @if($payment->canBeRefunded())
                                        <li><a class="dropdown-item" href="#" onclick="processRefund({{ $payment->id }})">
                                            <i class="fas fa-undo me-1"></i>Rimborsa
                                        </a></li>
                                        @endif
                                        @if($payment->receipt_number)
                                        <li><a class="dropdown-item" href="{{ route('admin.payments.receipt', $payment) }}" target="_blank">
                                            <i class="fas fa-file-pdf me-1"></i>Scarica Ricevuta
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="sendReceipt({{ $payment->id }})">
                                            <i class="fas fa-envelope me-1"></i>Invia Ricevuta
                                        </a></li>
                                        @endif
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item text-danger" href="#" onclick="deletePayment({{ $payment->id }})">
                                            <i class="fas fa-trash me-1"></i>Elimina
                                        </a></li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>Nessun pagamento trovato</p>
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

@include('admin.payments.modals.refund')

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllCheckbox = document.getElementById('selectAll');
    const paymentCheckboxes = document.querySelectorAll('.payment-checkbox');
    const bulkActionBtn = document.getElementById('bulkActionBtn');

    selectAllCheckbox.addEventListener('change', function() {
        paymentCheckboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        updateBulkActionBtn();
    });

    paymentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkActionBtn);
    });

    function updateBulkActionBtn() {
        const checkedBoxes = document.querySelectorAll('.payment-checkbox:checked');
        bulkActionBtn.disabled = checkedBoxes.length === 0;
    }

    // Bulk actions
    document.querySelectorAll('.dropdown-menu a[data-action]').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            const checkedPayments = Array.from(document.querySelectorAll('.payment-checkbox:checked'))
                                         .map(cb => cb.value);

            if (checkedPayments.length === 0) {
                alert('Seleziona almeno un pagamento');
                return;
            }

            if (action === 'delete' && !confirm('Sei sicuro di voler eliminare i pagamenti selezionati?')) {
                return;
            }

            bulkAction(action, checkedPayments);
        });
    });

    function bulkAction(action, paymentIds) {
        fetch('{{ route("admin.payments.bulk-action") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                action: action,
                payment_ids: paymentIds
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Errore: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Si è verificato un errore');
        });
    }
});

function markCompleted(paymentId) {
    if (!confirm('Sei sicuro di voler segnare questo pagamento come completato?')) {
        return;
    }

    fetch(`/admin/payments/${paymentId}/mark-completed`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    });
}

function processRefund(paymentId) {
    const reason = prompt('Inserisci il motivo del rimborso:');
    if (!reason) return;

    fetch(`/admin/payments/${paymentId}/refund`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            refund_reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    });
}

function sendReceipt(paymentId) {
    fetch(`/admin/payments/${paymentId}/send-receipt`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Ricevuta inviata con successo!');
        } else {
            alert('Errore: ' + data.message);
        }
    });
}

function deletePayment(paymentId) {
    if (!confirm('Sei sicuro di voler eliminare questo pagamento? Questa azione non può essere annullata.')) {
        return;
    }

    fetch(`/admin/payments/${paymentId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore: ' + data.message);
        }
    });
}
</script>
@endpush</x-app-layout>
