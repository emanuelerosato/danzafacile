@extends('layouts.app')

@section('title', 'Impostazioni - Super Admin')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">⚙️ Impostazioni Sistema</h1>
        <a href="{{ route('super-admin.dashboard') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-arrow-left fa-sm text-white-50"></i> Torna al Dashboard
        </a>
    </div>

    <!-- Settings Content -->
    <div class="row">
        <!-- Sistema -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">🖥️ Configurazione Sistema</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label for="siteName">Nome Sistema</label>
                            <input type="text" class="form-control" id="siteName" value="Scuola di Danza" readonly>
                            <small class="form-text text-muted">Nome principale del sistema</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="maintenance">Modalità Manutenzione</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="maintenance">
                                <label class="custom-control-label" for="maintenance">Attiva manutenzione</label>
                            </div>
                            <small class="form-text text-muted">Blocca l'accesso al sistema per manutenzione</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="timezone">Fuso Orario</label>
                            <select class="form-control" id="timezone">
                                <option selected>Europe/Rome</option>
                                <option>Europe/London</option>
                                <option>America/New_York</option>
                            </select>
                        </div>
                        
                        <button type="button" class="btn btn-primary" onclick="saveSystemSettings()">
                            Salva Impostazioni
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Email -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">📧 Configurazione Email</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="form-group">
                            <label for="smtpHost">SMTP Host</label>
                            <input type="text" class="form-control" id="smtpHost" placeholder="smtp.example.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtpPort">SMTP Port</label>
                            <input type="number" class="form-control" id="smtpPort" value="587">
                        </div>
                        
                        <div class="form-group">
                            <label for="smtpUsername">Username</label>
                            <input type="text" class="form-control" id="smtpUsername" placeholder="noreply@scuoladanza.it">
                        </div>
                        
                        <div class="form-group">
                            <label for="emailEnabled">Email Attive</label>
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="emailEnabled" checked>
                                <label class="custom-control-label" for="emailEnabled">Abilita invio email</label>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-success" onclick="saveEmailSettings()">
                            Salva Email
                        </button>
                        <button type="button" class="btn btn-outline-secondary ml-2" onclick="testEmail()">
                            Test Email
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Security Settings -->
    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-danger">🔒 Impostazioni Sicurezza</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="sessionTimeout">Timeout Sessione (minuti)</label>
                                <input type="number" class="form-control" id="sessionTimeout" value="120">
                                <small class="form-text text-muted">Durata massima sessione utente</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="maxLoginAttempts">Max Tentativi Login</label>
                                <input type="number" class="form-control" id="maxLoginAttempts" value="5">
                                <small class="form-text text-muted">Tentativi di login prima del blocco</small>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="passwordMinLength">Lunghezza Min Password</label>
                                <input type="number" class="form-control" id="passwordMinLength" value="8">
                                <small class="form-text text-muted">Caratteri minimi per password</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="requireStrongPassword" checked>
                                <label class="custom-control-label" for="requireStrongPassword">
                                    Richiedi password complesse
                                </label>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="enableTwoFactor">
                                <label class="custom-control-label" for="enableTwoFactor">
                                    Abilita autenticazione a due fattori
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" class="btn btn-danger" onclick="saveSecuritySettings()">
                            Salva Impostazioni Sicurezza
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function saveSystemSettings() {
    // Simulate saving
    alert('Impostazioni sistema salvate con successo!');
}

function saveEmailSettings() {
    // Simulate saving
    alert('Impostazioni email salvate con successo!');
}

function testEmail() {
    // Simulate email test
    alert('Email di test inviata! Controlla la casella di posta.');
}

function saveSecuritySettings() {
    // Simulate saving
    alert('Impostazioni sicurezza salvate con successo!');
}
</script>
@endsection