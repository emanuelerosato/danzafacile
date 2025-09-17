<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Report Analytics - {{ ucfirst($period) }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #4F46E5;
        }

        .header h1 {
            color: #4F46E5;
            font-size: 24px;
            margin: 0 0 10px 0;
        }

        .header .subtitle {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }

        .period-info {
            background-color: #F8F9FA;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #4F46E5;
        }

        .period-info h3 {
            margin: 0 0 10px 0;
            color: #4F46E5;
            font-size: 16px;
        }

        .period-info p {
            margin: 5px 0;
            color: #666;
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background-color: #fff;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .metric-card h4 {
            color: #374151;
            font-size: 14px;
            margin: 0 0 10px 0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #F3F4F6;
        }

        .metric-item:last-child {
            border-bottom: none;
        }

        .metric-label {
            color: #6B7280;
            font-size: 11px;
        }

        .metric-value {
            font-weight: 600;
            color: #111827;
        }

        .metric-value.highlight {
            color: #059669;
        }

        .metric-value.warning {
            color: #D97706;
        }

        .summary {
            background-color: #F0F9FF;
            border: 1px solid #0284C7;
            border-radius: 8px;
            padding: 20px;
            margin-top: 30px;
        }

        .summary h3 {
            color: #0284C7;
            margin: 0 0 15px 0;
            font-size: 16px;
        }

        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 15px;
        }

        .summary-item {
            text-align: center;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #0284C7;
            display: block;
        }

        .summary-label {
            font-size: 10px;
            color: #6B7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #9CA3AF;
            font-size: 10px;
        }

        .page-break {
            page-break-after: always;
        }

        @media print {
            .metric-card {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1>Report Analytics</h1>
        <div class="subtitle">{{ config('app.name', 'School Management System') }}</div>
        <div class="subtitle">Periodo: {{ ucfirst($period) }}</div>
    </div>

    <!-- Period Information -->
    <div class="period-info">
        <h3>Informazioni Periodo</h3>
        <p><strong>Tipo Periodo:</strong> {{ ucfirst($period) }}</p>
        <p><strong>Dal:</strong> {{ $startDate->format('d/m/Y') }}</p>
        <p><strong>Al:</strong> {{ $endDate->format('d/m/Y') }}</p>
        <p><strong>Report Generato:</strong> {{ $generatedAt->format('d/m/Y H:i') }}</p>
    </div>

    <!-- Metrics Grid -->
    <div class="metrics-grid">
        <!-- Studenti -->
        <div class="metric-card">
            <h4>👥 Studenti</h4>
            <div class="metric-item">
                <span class="metric-label">Totali</span>
                <span class="metric-value">{{ number_format($metrics['students']['total']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Nuovi (periodo)</span>
                <span class="metric-value highlight">{{ number_format($metrics['students']['new']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Attivi</span>
                <span class="metric-value">{{ number_format($metrics['students']['active']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Tasso Attivazione</span>
                <span class="metric-value">{{ $metrics['students']['total'] > 0 ? number_format(($metrics['students']['active'] / $metrics['students']['total']) * 100, 1) : 0 }}%</span>
            </div>
        </div>

        <!-- Corsi -->
        <div class="metric-card">
            <h4>📚 Corsi</h4>
            <div class="metric-item">
                <span class="metric-label">Totali</span>
                <span class="metric-value">{{ number_format($metrics['courses']['total']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Attivi</span>
                <span class="metric-value highlight">{{ number_format($metrics['courses']['active']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Capacità Utilizzata</span>
                <span class="metric-value {{ $metrics['courses']['capacity_usage'] > 80 ? 'warning' : '' }}">{{ number_format($metrics['courses']['capacity_usage'], 1) }}%</span>
            </div>
        </div>

        <!-- Eventi -->
        <div class="metric-card">
            <h4>🎉 Eventi</h4>
            <div class="metric-item">
                <span class="metric-label">Totali</span>
                <span class="metric-value">{{ number_format($metrics['events']['total']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Prossimi</span>
                <span class="metric-value highlight">{{ number_format($metrics['events']['upcoming']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Questo Periodo</span>
                <span class="metric-value">{{ number_format($metrics['events']['this_period']) }}</span>
            </div>
        </div>

        <!-- Staff -->
        <div class="metric-card">
            <h4>👨‍🏫 Staff</h4>
            <div class="metric-item">
                <span class="metric-label">Totali</span>
                <span class="metric-value">{{ number_format($metrics['staff']['total']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Attivi</span>
                <span class="metric-value highlight">{{ number_format($metrics['staff']['active']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Istruttori</span>
                <span class="metric-value">{{ number_format($metrics['staff']['instructors']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">% Istruttori</span>
                <span class="metric-value">{{ $metrics['staff']['total'] > 0 ? number_format(($metrics['staff']['instructors'] / $metrics['staff']['total']) * 100, 1) : 0 }}%</span>
            </div>
        </div>

        <!-- Pagamenti -->
        <div class="metric-card">
            <h4>💰 Pagamenti</h4>
            <div class="metric-item">
                <span class="metric-label">Incassi Totali</span>
                <span class="metric-value highlight">€{{ number_format($metrics['payments']['total_amount'], 2) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Periodo</span>
                <span class="metric-value">€{{ number_format($metrics['payments']['this_period_amount'], 2) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">In Sospeso</span>
                <span class="metric-value warning">€{{ number_format($metrics['payments']['pending_amount'], 2) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Numero Pagamenti</span>
                <span class="metric-value">{{ number_format($metrics['payments']['count']) }}</span>
            </div>
        </div>

        <!-- Presenze -->
        <div class="metric-card">
            <h4>✅ Presenze</h4>
            <div class="metric-item">
                <span class="metric-label">Totali</span>
                <span class="metric-value">{{ number_format($metrics['attendance']['total']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Questo Periodo</span>
                <span class="metric-value highlight">{{ number_format($metrics['attendance']['this_period']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Tasso Presenza</span>
                <span class="metric-value {{ $metrics['attendance']['rate'] > 75 ? 'highlight' : 'warning' }}">{{ number_format($metrics['attendance']['rate'], 1) }}%</span>
            </div>
        </div>
    </div>

    <!-- Summary -->
    <div class="summary">
        <h3>Riepilogo Performance</h3>
        <div class="summary-grid">
            <div class="summary-item">
                <span class="summary-value">{{ number_format($metrics['students']['total']) }}</span>
                <span class="summary-label">Studenti Totali</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">€{{ number_format($metrics['payments']['total_amount']) }}</span>
                <span class="summary-label">Fatturato Totale</span>
            </div>
            <div class="summary-item">
                <span class="summary-value">{{ number_format($metrics['attendance']['rate'], 1) }}%</span>
                <span class="summary-label">Tasso Presenza</span>
            </div>
        </div>
    </div>

    <!-- Additional Metrics -->
    <div class="page-break"></div>

    <div class="metrics-grid">
        <!-- Documenti -->
        <div class="metric-card">
            <h4>📄 Documenti</h4>
            <div class="metric-item">
                <span class="metric-label">Totali</span>
                <span class="metric-value">{{ number_format($metrics['documents']['total']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">In Attesa Approvazione</span>
                <span class="metric-value warning">{{ number_format($metrics['documents']['pending_approval']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Approvati</span>
                <span class="metric-value highlight">{{ number_format($metrics['documents']['approved']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Tasso Approvazione</span>
                <span class="metric-value">{{ $metrics['documents']['total'] > 0 ? number_format(($metrics['documents']['approved'] / $metrics['documents']['total']) * 100, 1) : 0 }}%</span>
            </div>
        </div>

        <!-- Gallerie -->
        <div class="metric-card">
            <h4>🖼️ Media & Gallerie</h4>
            <div class="metric-item">
                <span class="metric-label">Gallerie Totali</span>
                <span class="metric-value">{{ number_format($metrics['galleries']['total']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">File Multimediali</span>
                <span class="metric-value highlight">{{ number_format($metrics['galleries']['total_media']) }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label">Media per Galleria</span>
                <span class="metric-value">{{ $metrics['galleries']['total'] > 0 ? number_format($metrics['galleries']['total_media'] / $metrics['galleries']['total'], 1) : 0 }}</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>Report generato automaticamente dal sistema {{ config('app.name') }} il {{ $generatedAt->format('d/m/Y \a\l\l\e H:i') }}</p>
        <p>Questo documento contiene informazioni confidenziali dell'istituzione</p>
    </div>
</body>
</html>