@extends('layouts.app')

@section('title', 'Super Admin Dashboard')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Dashboard Super Admin</h1>
            <p class="text-muted">Panoramica generale del sistema</p>
        </div>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Aggiornato: {{ now()->format('d/m/Y H:i') }}</span>
            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                <i class="fas fa-sync-alt"></i> Aggiorna
            </button>
        </div>
    </div>

    <!-- Key Statistics -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Scuole Attive</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['schools_active']) }}</div>
                            <div class="text-muted small">su {{ number_format($stats['schools_total']) }} totali</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-school fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Totale Utenti</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($stats['users_total']) }}</div>
                            <div class="text-muted small">{{ number_format($stats['students_total']) }} studenti, {{ number_format($stats['admins_total']) }} admin</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Corsi Attivi</div>
                            <div class="row no-gutters align-items-center">
                                <div class="col-auto">
                                    <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">{{ number_format($stats['courses_active']) }}</div>
                                </div>
                                <div class="col">
                                    <div class="progress progress-sm mr-2">
                                        <div class="progress-bar bg-info" role="progressbar"
                                             style="width: {{ $stats['courses_total'] > 0 ? ($stats['courses_active'] / $stats['courses_total'] * 100) : 0 }}%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-music fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Ricavi Totali</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">€{{ number_format($stats['payments_total'], 2) }}</div>
                            <div class="text-muted small">€{{ number_format($stats['payments_month'], 2) }} questo mese</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="row">
        <!-- Recent Schools -->
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Scuole Recenti</h6>
                    <a href="{{ route('super-admin.schools.index') }}" class="btn btn-sm btn-primary">Vedi Tutte</a>
                </div>
                <div class="card-body">
                    @if($recent_schools->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Email</th>
                                        <th>Stato</th>
                                        <th>Data Creazione</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recent_schools as $school)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-3">
                                                        <div class="icon-circle {{ $school->active ? 'bg-success' : 'bg-secondary' }}">
                                                            <i class="fas fa-school text-white"></i>
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <strong>{{ $school->name }}</strong>
                                                        @if($school->phone)
                                                            <br><small class="text-muted">{{ $school->phone }}</small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $school->email }}</td>
                                            <td>
                                                @if($school->active)
                                                    <span class="badge badge-success">Attiva</span>
                                                @else
                                                    <span class="badge badge-secondary">Inattiva</span>
                                                @endif
                                            </td>
                                            <td>{{ $school->created_at->format('d/m/Y') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-school fa-3x text-muted mb-3"></i>
                            <p class="text-muted">Nessuna scuola registrata</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Activity & Quick Actions -->
        <div class="col-xl-4 col-lg-5">
            <!-- Recent Users -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Utenti Recenti</h6>
                    <a href="{{ route('super-admin.users.index') }}" class="btn btn-sm btn-outline-primary">Vedi</a>
                </div>
                <div class="card-body">
                    @if($recent_users->count() > 0)
                        @foreach($recent_users->take(5) as $user)
                            <div class="d-flex align-items-center mb-3">
                                <div class="mr-3">
                                    <div class="icon-circle bg-primary">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                </div>
                                <div class="flex-grow-1">
                                    <strong class="d-block">{{ $user->name }}</strong>
                                    <small class="text-muted">{{ ucfirst($user->role) }} - {{ $user->created_at->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">Nessun utente recente</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Pending Documents Alert -->
            @if($stats['documents_pending'] > 0)
                <div class="card border-left-warning shadow mb-4">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning"></i>
                            </div>
                            <div>
                                <h6 class="font-weight-bold">Documenti in Attesa</h6>
                                <p class="mb-2">{{ $stats['documents_pending'] }} documenti richiedono approvazione</p>
                                <a href="{{ route('super-admin.documents.index') }}" class="btn btn-warning btn-sm">
                                    Verifica Ora
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Azioni Rapide</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-6 mb-3">
                            <a href="{{ route('super-admin.schools.create') }}" class="btn btn-primary btn-block btn-sm">
                                <i class="fas fa-plus mb-1"></i><br>
                                Nuova Scuola
                            </a>
                        </div>
                        <div class="col-6 mb-3">
                            <a href="{{ route('super-admin.users.create') }}" class="btn btn-success btn-block btn-sm">
                                <i class="fas fa-user-plus mb-1"></i><br>
                                Nuovo Utente
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('super-admin.reports') }}" class="btn btn-info btn-block btn-sm">
                                <i class="fas fa-chart-bar mb-1"></i><br>
                                Report
                            </a>
                        </div>
                        <div class="col-6">
                            <a href="{{ route('super-admin.settings') }}" class="btn btn-warning btn-block btn-sm">
                                <i class="fas fa-cog mb-1"></i><br>
                                Impostazioni
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-weight: bold;
}

.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.text-xs {
    font-size: .7rem;
}

.card {
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important;
}

.progress-sm {
    height: .5rem;
}
</style>

<script>
// Auto refresh every 5 minutes
setTimeout(function() {
    location.reload();
}, 300000);
</script>
@endsection