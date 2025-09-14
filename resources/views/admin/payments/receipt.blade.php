<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ricevuta di Pagamento - {{ $payment->receipt_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 20px;
        }

        .school-info {
            flex: 2;
        }

        .school-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }

        .school-details {
            font-size: 11px;
            color: #666;
            line-height: 1.3;
        }

        .receipt-info {
            flex: 1;
            text-align: right;
        }

        .receipt-title {
            font-size: 18px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 10px;
        }

        .receipt-number {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .receipt-date {
            font-size: 11px;
            color: #666;
        }

        .payment-details {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 15px;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 5px;
        }

        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-group {
            margin-bottom: 15px;
        }

        .detail-label {
            font-weight: bold;
            color: #374151;
            margin-bottom: 3px;
        }

        .detail-value {
            color: #6b7280;
        }

        .amount-section {
            background-color: #f8fafc;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
        }

        .amount-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
            padding: 5px 0;
        }

        .amount-row.total {
            border-top: 2px solid #2563eb;
            margin-top: 15px;
            padding-top: 15px;
            font-weight: bold;
            font-size: 16px;
        }

        .amount-label {
            flex: 1;
        }

        .amount-value {
            font-weight: bold;
            text-align: right;
            min-width: 100px;
        }

        .installment-section {
            margin-top: 30px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            overflow: hidden;
        }

        .installment-header {
            background-color: #2563eb;
            color: white;
            padding: 12px 15px;
            font-weight: bold;
        }

        .installment-table {
            width: 100%;
            border-collapse: collapse;
        }

        .installment-table th,
        .installment-table td {
            padding: 10px 15px;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }

        .installment-table th {
            background-color: #f8fafc;
            font-weight: bold;
            color: #374151;
        }

        .installment-table tr:last-child td {
            border-bottom: none;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-completed {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background-color: #fef3c7;
            color: #92400e;
        }

        .status-failed {
            background-color: #fecaca;
            color: #991b1b;
        }

        .status-refunded {
            background-color: #e0e7ff;
            color: #3730a3;
        }

        .notes-section {
            margin-top: 30px;
            padding: 15px;
            background-color: #fafafa;
            border-left: 4px solid #2563eb;
        }

        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 10px;
            color: #666;
        }

        .qr-code {
            float: right;
            margin-left: 20px;
        }

        @media print {
            body {
                font-size: 11px;
            }
            .container {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="school-info">
                @if($school->logo_path)
                    <img src="{{ public_path('storage/' . $school->logo_path) }}" alt="Logo" style="height: 60px; margin-bottom: 10px;">
                @endif
                <div class="school-name">{{ $school->name }}</div>
                <div class="school-details">
                    @if($school->address)
                        {{ $school->address }}<br>
                    @endif
                    @if($school->city && $school->postal_code)
                        {{ $school->postal_code }} {{ $school->city }}<br>
                    @endif
                    @if($school->phone)
                        Tel: {{ $school->phone }}<br>
                    @endif
                    @if($school->email)
                        Email: {{ $school->email }}<br>
                    @endif
                    @if($school->website)
                        Web: {{ $school->website }}
                    @endif
                </div>
            </div>
            <div class="receipt-info">
                <div class="receipt-title">RICEVUTA DI PAGAMENTO</div>
                <div class="receipt-number">N. {{ $payment->receipt_number }}</div>
                <div class="receipt-date">{{ $generated_at->format('d/m/Y H:i') }}</div>
            </div>
        </div>

        <!-- Student Information -->
        <div class="payment-details">
            <div class="section-title">Dati Studente</div>
            <div class="details-grid">
                <div class="detail-group">
                    <div class="detail-label">Nome Completo</div>
                    <div class="detail-value">{{ $payment->user->full_name ?? $payment->user->name }}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Email</div>
                    <div class="detail-value">{{ $payment->user->email }}</div>
                </div>
                @if($payment->user->phone)
                <div class="detail-group">
                    <div class="detail-label">Telefono</div>
                    <div class="detail-value">{{ $payment->user->phone }}</div>
                </div>
                @endif
                @if($payment->user->address)
                <div class="detail-group">
                    <div class="detail-label">Indirizzo</div>
                    <div class="detail-value">{{ $payment->user->address }}</div>
                </div>
                @endif
            </div>
        </div>

        <!-- Payment Information -->
        <div class="payment-details">
            <div class="section-title">Dettagli Pagamento</div>
            <div class="details-grid">
                <div class="detail-group">
                    <div class="detail-label">Tipo Pagamento</div>
                    <div class="detail-value">{{ $payment->payment_type_name }}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Metodo di Pagamento</div>
                    <div class="detail-value">{{ $payment->payment_method_name }}</div>
                </div>
                @if($payment->course)
                <div class="detail-group">
                    <div class="detail-label">Corso</div>
                    <div class="detail-value">{{ $payment->course->name }}</div>
                </div>
                @endif
                @if($payment->event)
                <div class="detail-group">
                    <div class="detail-label">Evento</div>
                    <div class="detail-value">{{ $payment->event->name }}</div>
                </div>
                @endif
                @if($payment->transaction_id)
                <div class="detail-group">
                    <div class="detail-label">ID Transazione</div>
                    <div class="detail-value">{{ $payment->transaction_id }}</div>
                </div>
                @endif
                @if($payment->reference_number)
                <div class="detail-group">
                    <div class="detail-label">Riferimento</div>
                    <div class="detail-value">{{ $payment->reference_number }}</div>
                </div>
                @endif
                <div class="detail-group">
                    <div class="detail-label">Data Pagamento</div>
                    <div class="detail-value">{{ $payment->payment_date ? $payment->payment_date->format('d/m/Y H:i') : 'Non specificata' }}</div>
                </div>
                <div class="detail-group">
                    <div class="detail-label">Stato</div>
                    <div class="detail-value">
                        <span class="status-badge status-{{ $payment->status }}">{{ $payment->status_name }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Amount Breakdown -->
        <div class="amount-section">
            <div class="amount-row">
                <span class="amount-label">Importo Base:</span>
                <span class="amount-value">€ {{ number_format($payment->amount, 2, ',', '.') }}</span>
            </div>
            @if($payment->discount_amount > 0)
            <div class="amount-row">
                <span class="amount-label">Sconto:</span>
                <span class="amount-value">- € {{ number_format($payment->discount_amount, 2, ',', '.') }}</span>
            </div>
            @endif
            @if($payment->tax_amount > 0)
            <div class="amount-row">
                <span class="amount-label">Tasse/IVA:</span>
                <span class="amount-value">+ € {{ number_format($payment->tax_amount, 2, ',', '.') }}</span>
            </div>
            @endif
            @if($payment->payment_gateway_fee > 0)
            <div class="amount-row">
                <span class="amount-label">Commissioni Gateway:</span>
                <span class="amount-value">+ € {{ number_format($payment->payment_gateway_fee, 2, ',', '.') }}</span>
            </div>
            @endif
            <div class="amount-row total">
                <span class="amount-label">TOTALE PAGATO:</span>
                <span class="amount-value">€ {{ number_format($payment->net_amount ?? $payment->amount, 2, ',', '.') }}</span>
            </div>
        </div>

        <!-- Installments Section -->
        @if($payment->installments && $payment->installments->count() > 0)
        <div class="installment-section">
            <div class="installment-header">
                Piano Rateale ({{ $payment->installments->count() }} rate)
            </div>
            <table class="installment-table">
                <thead>
                    <tr>
                        <th>Rata</th>
                        <th>Importo</th>
                        <th>Scadenza</th>
                        <th>Stato</th>
                        <th>Data Pagamento</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payment->installments as $installment)
                    <tr>
                        <td>{{ $installment->installment_number }}/{{ $installment->total_installments }}</td>
                        <td>€ {{ number_format($installment->amount, 2, ',', '.') }}</td>
                        <td>{{ $installment->due_date ? $installment->due_date->format('d/m/Y') : 'N/A' }}</td>
                        <td>
                            <span class="status-badge status-{{ $installment->status }}">{{ $installment->status_name }}</span>
                        </td>
                        <td>{{ $installment->payment_date ? $installment->payment_date->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        <!-- Notes -->
        @if($payment->notes)
        <div class="notes-section">
            <div class="section-title">Note</div>
            <p>{{ $payment->notes }}</p>
        </div>
        @endif

        @if($payment->refund_reason)
        <div class="notes-section">
            <div class="section-title">Motivo Rimborso</div>
            <p>{{ $payment->refund_reason }}</p>
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>Ricevuta generata automaticamente da {{ $school->name }} il {{ $generated_at->format('d/m/Y \a\l\l\e H:i') }}</p>
            <p>Documento valido ai fini fiscali secondo le normative vigenti</p>
            @if($generated_by)
            <p>Elaborata da: {{ $generated_by->full_name ?? $generated_by->name }}</p>
            @endif
        </div>
    </div>
</body>
</html>