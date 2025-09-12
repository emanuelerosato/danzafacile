<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $data['title'] }} - PDF Export</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #1f2937;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 2px solid #4f46e5;
        }
        .header h1 {
            color: #4f46e5;
            font-size: 24px;
            margin: 0 0 10px 0;
        }
        .header p {
            color: #6b7280;
            font-size: 14px;
            margin: 5px 0;
        }
        .summary {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .summary h2 {
            color: #1e40af;
            font-size: 18px;
            margin: 0 0 15px 0;
        }
        .summary-grid {
            display: table;
            width: 100%;
        }
        .summary-row {
            display: table-row;
        }
        .summary-cell {
            display: table-cell;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-label {
            font-weight: bold;
            width: 40%;
            color: #374151;
        }
        .summary-value {
            color: #1f2937;
            width: 60%;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .data-table th {
            background-color: #4f46e5;
            color: white;
            font-weight: bold;
            padding: 12px 8px;
            text-align: left;
            border: 1px solid #3730a3;
            font-size: 11px;
        }
        .data-table td {
            padding: 10px 8px;
            border: 1px solid #d1d5db;
            font-size: 10px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .data-table tr:nth-child(odd) {
            background-color: white;
        }
        .footer {
            position: fixed;
            bottom: 20px;
            right: 20px;
            font-size: 10px;
            color: #6b7280;
        }
        .page-break {
            page-break-before: always;
        }
        .no-data {
            text-align: center;
            color: #6b7280;
            font-style: italic;
            padding: 40px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>{{ $data['title'] }}</h1>
        <p><strong>Periodo:</strong> {{ $data['period'] }}</p>
        <p><strong>Generato il:</strong> {{ $generated_at }}</p>
        <p><strong>Sistema Scuola di Danza</strong> - Report Amministrativo</p>
    </div>

    <!-- Summary Section -->
    @if(!empty($data['summary']))
    <div class="summary">
        <h2>📊 Riepilogo</h2>
        <div class="summary-grid">
            @foreach($data['summary'] as $key => $value)
            <div class="summary-row">
                <div class="summary-cell summary-label">
                    {{ ucfirst(str_replace('_', ' ', $key)) }}:
                </div>
                <div class="summary-cell summary-value">
                    {{ $value }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Data Table -->
    @if(!empty($data['items']) && count($data['items']) > 0)
    <div class="data-section">
        <h2 style="color: #1e40af; margin-bottom: 15px;">📋 Dettagli</h2>
        
        <table class="data-table">
            <thead>
                <tr>
                    @foreach(array_keys($data['items'][0]) as $header)
                    <th>{{ ucfirst(str_replace('_', ' ', $header)) }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($data['items'] as $item)
                <tr>
                    @foreach($item as $value)
                    <td>{{ $value }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="no-data">
        <p>📋 Nessun dato disponibile per il periodo selezionato</p>
    </div>
    @endif

    <!-- Footer -->
    <div class="footer">
        <p>Sistema Scuola di Danza - Report generato automaticamente</p>
    </div>
</body>
</html>