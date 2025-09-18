@props([
    'fallback' => '',
    'retry' => true,
    'showDetails' => false
])

<!-- Error Boundary Component -->
<div x-data="errorBoundary()" x-init="init()">
    <!-- Main Content -->
    <div x-show="!hasError" {{ $attributes }}>
        {{ $slot }}
    </div>

    <!-- Error Fallback UI -->
    <div x-show="hasError" x-transition
         class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
        <div class="flex flex-col items-center space-y-4">
            <!-- Error Icon -->
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>

            <!-- Error Message -->
            <div>
                <h3 class="text-lg font-semibold text-red-900 mb-2">
                    Si è verificato un errore
                </h3>
                <p class="text-red-700 mb-4">
                    @if($fallback)
                        {{ $fallback }}
                    @else
                        Qualcosa è andato storto. Riprova o contatta il supporto se il problema persiste.
                    @endif
                </p>
            </div>

            <!-- Error Details (Optional) -->
            <div x-show="showDetails && errorDetails" x-transition
                 class="w-full bg-red-100 rounded-lg p-4 text-left">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="text-sm font-medium text-red-900">Dettagli Errore</h4>
                    <button @click="showDetails = false"
                            class="text-red-600 hover:text-red-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <pre x-text="errorDetails" class="text-xs text-red-800 overflow-auto max-h-32"></pre>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
                @if($retry)
                    <button @click="retryAction()"
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Riprova
                    </button>
                @endif

                @if($showDetails)
                    <button @click="showDetails = !showDetails"
                            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="showDetails ? 'Nascondi Dettagli' : 'Mostra Dettagli'"></span>
                    </button>
                @endif

                <button @click="reportError()"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Segnala Problema
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function errorBoundary() {
    return {
        hasError: false,
        errorDetails: '',
        showDetails: {{ $showDetails ? 'true' : 'false' }},

        init() {
            // Global error handler
            window.addEventListener('error', (event) => {
                this.handleError(event.error || event.message, event);
            });

            // Promise rejection handler
            window.addEventListener('unhandledrejection', (event) => {
                this.handleError(event.reason, event);
            });

            // Axios error interceptor if available
            if (window.axios) {
                window.axios.interceptors.response.use(
                    response => response,
                    error => {
                        this.handleAxiosError(error);
                        return Promise.reject(error);
                    }
                );
            }
        },

        handleError(error, event = null) {
            this.hasError = true;
            this.errorDetails = this.formatError(error, event);

            // Log to console for debugging
            console.error('Error captured by boundary:', error, event);

            // Send to error tracking service if available
            if (window.errorTracker) {
                window.errorTracker.captureException(error);
            }
        },

        handleAxiosError(error) {
            let errorMessage = 'Errore di rete';

            if (error.response) {
                // Server responded with error status
                const status = error.response.status;
                switch (status) {
                    case 400:
                        errorMessage = 'Richiesta non valida';
                        break;
                    case 401:
                        errorMessage = 'Non autorizzato - Effettua il login';
                        break;
                    case 403:
                        errorMessage = 'Accesso negato';
                        break;
                    case 404:
                        errorMessage = 'Risorsa non trovata';
                        break;
                    case 419:
                        errorMessage = 'Sessione scaduta - Ricarica la pagina';
                        break;
                    case 422:
                        errorMessage = 'Dati non validi';
                        break;
                    case 429:
                        errorMessage = 'Troppe richieste - Attendi prima di riprovare';
                        break;
                    case 500:
                        errorMessage = 'Errore interno del server';
                        break;
                    case 503:
                        errorMessage = 'Servizio temporaneamente non disponibile';
                        break;
                    default:
                        errorMessage = `Errore HTTP ${status}`;
                }
            } else if (error.request) {
                // Network error
                errorMessage = 'Errore di connessione - Verifica la tua connessione internet';
            }

            this.handleError(new Error(errorMessage), { type: 'axios', originalError: error });
        },

        formatError(error, event) {
            let details = '';

            if (error instanceof Error) {
                details += `Messaggio: ${error.message}\n`;
                if (error.stack) {
                    details += `Stack: ${error.stack}\n`;
                }
            } else {
                details += `Errore: ${JSON.stringify(error)}\n`;
            }

            if (event) {
                details += `Evento: ${event.type || 'unknown'}\n`;
                if (event.filename) {
                    details += `File: ${event.filename}:${event.lineno}:${event.colno}\n`;
                }
            }

            details += `Timestamp: ${new Date().toISOString()}\n`;
            details += `URL: ${window.location.href}\n`;
            details += `User Agent: ${navigator.userAgent}`;

            return details;
        },

        retryAction() {
            this.hasError = false;
            this.errorDetails = '';

            // Try to reload the page or retry the last action
            setTimeout(() => {
                location.reload();
            }, 100);
        },

        reportError() {
            const subject = encodeURIComponent('Segnalazione Errore - Scuola di Danza');
            const body = encodeURIComponent(`
Descrizione del problema:
[Descrivi cosa stavi facendo quando si è verificato l'errore]

Dettagli tecnici:
${this.errorDetails}
            `);

            const mailtoLink = `mailto:support@scuoladidanza.com?subject=${subject}&body=${body}`;
            window.open(mailtoLink);
        }
    }
}
</script>