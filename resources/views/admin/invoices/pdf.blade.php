<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Fattura {{ $invoice->invoice_number }}</title>
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
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #2563eb;
        }

        .logo-container {
            margin-bottom: 15px;
        }

        .logo {
            max-width: 200px;
            max-height: 80px;
        }

        .header-text {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
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

        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #2563eb;
            margin: 30px 0 20px 0;
            text-align: center;
        }

        .invoice-meta {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .invoice-meta-left,
        .invoice-meta-right {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }

        .invoice-number {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .invoice-date {
            font-size: 12px;
            color: #666;
        }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin: 20px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #e5e7eb;
        }

        .billing-info {
            margin-bottom: 30px;
            padding: 15px;
            background-color: #f9fafb;
            border-left: 3px solid #2563eb;
        }

        .billing-info p {
            margin-bottom: 5px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #e5e7eb;
            padding: 12px;
            text-align: left;
        }

        .items-table th {
            background-color: #2563eb;
            color: white;
            font-weight: bold;
        }

        .items-table td.amount {
            text-align: right;
            width: 150px;
        }

        .items-table tfoot td {
            font-weight: bold;
            background-color: #f9fafb;
        }

        .total {
            font-size: 16px;
            color: #2563eb;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 10px;
            text-align: center;
            color: #666;
        }

        .footer p {
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <!-- Header con Logo e Info Scuola -->
    <div class="header">
        @if($settings['show_logo'] && ($settings['logo_path'] || $settings['logo_url']))
            <div class="logo-container">
                @if($settings['logo_path'])
                    <img src="{{ storage_path('app/public/' . $settings['logo_path']) }}" class="logo" alt="Logo">
                @elseif($settings['logo_url'])
                    <img src="{{ $settings['logo_url'] }}" class="logo" alt="Logo">
                @endif
            </div>
        @endif

        @if($settings['header_text'])
            <div class="header-text">{{ $settings['header_text'] }}</div>
        @endif

        <div class="school-name">{{ $settings['school_name'] }}</div>
        <div class="school-details">
            @if($settings['school_address']){{ $settings['school_address'] }}<br>@endif
            @if($settings['school_city'] || $settings['school_postal_code'])
                {{ $settings['school_city'] }} {{ $settings['school_postal_code'] }}<br>
            @endif
            @if($settings['school_vat_number'])P.IVA: {{ $settings['school_vat_number'] }}<br>@endif
            @if($settings['school_tax_code'])CF: {{ $settings['school_tax_code'] }}@endif
        </div>
    </div>

    <!-- Titolo Fattura -->
    <div class="invoice-title">FATTURA N. {{ $invoice->invoice_number }}</div>

    <!-- Meta Info -->
    <div class="invoice-meta">
        <div class="invoice-meta-left">
            <div class="invoice-number">Numero: {{ $invoice->invoice_number }}</div>
            <div class="invoice-date">Data: {{ $invoice->invoice_date->format('d/m/Y') }}</div>
        </div>
        <div class="invoice-meta-right" style="text-align: right;">
            <div class="invoice-date">Importo: <strong>{{ $invoice->formatted_amount }}</strong></div>
        </div>
    </div>

    <!-- Intestatario Fattura -->
    <div class="section-title">Intestatario</div>
    <div class="billing-info">
        <p><strong>{{ $invoice->billing_name }}</strong></p>
        @if($invoice->billing_fiscal_code)
            <p>Codice Fiscale: {{ strtoupper($invoice->billing_fiscal_code) }}</p>
        @endif
        @if($invoice->billing_address)
            <p>Indirizzo: {{ $invoice->billing_address }}</p>
        @endif
        <p>Email: {{ $invoice->billing_email }}</p>
    </div>

    <!-- Dettagli Fattura -->
    <div class="section-title">Dettaglio</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Descrizione</th>
                <th class="amount">Importo</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $invoice->description }}</td>
                <td class="amount">€ {{ number_format($invoice->amount, 2, ',', '.') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr>
                <td style="text-align: right;"><strong>TOTALE</strong></td>
                <td class="amount total">€ {{ number_format($invoice->amount, 2, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- Footer -->
    <div class="footer">
        @if($settings['footer_text'])
            <p>{{ $settings['footer_text'] }}</p>
        @endif
        <p>Documento generato il {{ now()->format('d/m/Y \a\l\l\e H:i') }}</p>
        <p>Fattura emessa da {{ $settings['school_name'] }}</p>
    </div>
</body>
</html>
