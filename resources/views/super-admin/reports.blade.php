@extends('layouts.app')

@section('title', 'Reports - Super Admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">📊 Reports Sistema</h1>
        <a href="{{ route('super-admin.dashboard') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Torna al Dashboard
        </a>
    </div>

    <!-- Reports Content -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Reports Disponibili</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-primary h-100 py-2">
                                <div class="card-body">
                                    <h5>📚 Report Scuole</h5>
                                    <p>Statistiche complete su tutte le scuole registrate nel sistema.</p>
                                    <button class="btn btn-primary btn-sm" onclick="generateReport('schools')">
                                        Genera Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-success h-100 py-2">
                                <div class="card-body">
                                    <h5>👥 Report Utenti</h5>
                                    <p>Analisi dettagliata degli utenti per ruolo e attività.</p>
                                    <button class="btn btn-success btn-sm" onclick="generateReport('users')">
                                        Genera Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-info h-100 py-2">
                                <div class="card-body">
                                    <h5>💰 Report Pagamenti</h5>
                                    <p>Analisi finanziaria completa con ricavi e trend.</p>
                                    <button class="btn btn-info btn-sm" onclick="generateReport('payments')">
                                        Genera Report
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6 mb-4">
                            <div class="card border-left-warning h-100 py-2">
                                <div class="card-body">
                                    <h5>🎵 Report Corsi</h5>
                                    <p>Statistiche sui corsi più popolari e performance.</p>
                                    <button class="btn btn-warning btn-sm" onclick="generateReport('courses')">
                                        Genera Report
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Results -->
    <div id="reportResults" class="row" style="display: none;">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Risultati Report</h6>
                </div>
                <div class="card-body">
                    <div id="reportContent">
                        <!-- Report content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateReport(type) {
    // Show loading
    document.getElementById('reportResults').style.display = 'block';
    document.getElementById('reportContent').innerHTML = '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Generando report...</div>';
    
    // Simulate report generation (in real implementation, this would be an AJAX call)
    setTimeout(function() {
        let content = '<h5>📊 Report ' + type.charAt(0).toUpperCase() + type.slice(1) + '</h5>';
        content += '<p>Report generato il: ' + new Date().toLocaleDateString('it-IT') + '</p>';
        content += '<div class="alert alert-info">Funzionalità reports in sviluppo. Questo è un placeholder per il report ' + type + '.</div>';
        
        document.getElementById('reportContent').innerHTML = content;
    }, 1500);
}
</script>
@endsection