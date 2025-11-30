<x-guest-layout>
    @section('title', 'Cookie Policy - Danza Facile')
    @section('description', 'Informativa Cookie e Tecnologie di Tracciamento - Danza Facile')

    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Cookie Policy</h1>
            <p class="text-sm text-gray-600">Ultimo aggiornamento: {{ date('d/m/Y') }}</p>
        </div>

        <div class="prose prose-sm max-w-none text-gray-700 space-y-6">
            <!-- Introduzione -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Cosa sono i Cookie</h2>
                <p>
                    I cookie sono piccoli file di testo che i siti web visitati dall'utente inviano al suo terminale
                    (computer, tablet, smartphone) dove vengono memorizzati per essere poi ritrasmessi agli stessi siti
                    alla successiva visita del medesimo utente.
                </p>
                <p class="mt-2">
                    I cookie permettono ai siti di ricordare le tue azioni e preferenze (come login, lingua, dimensioni
                    caratteri) per migliorare l'esperienza di navigazione.
                </p>
            </section>

            <!-- Tipologie Cookie -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Tipologie di Cookie Utilizzati</h2>

                <div class="space-y-4">
                    <!-- Cookie Tecnici -->
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            2.1 Cookie Tecnici (Necessari)
                        </h3>
                        <p class="text-sm mb-3">
                            Sono essenziali per il funzionamento del sito e non richiedono il tuo consenso.
                        </p>
                        <div class="space-y-2">
                            <div class="bg-white rounded p-3">
                                <p class="font-medium text-sm">XSRF-TOKEN</p>
                                <p class="text-xs text-gray-600 mt-1">Durata: Sessione</p>
                                <p class="text-xs text-gray-600">Finalità: Protezione CSRF (Cross-Site Request Forgery)</p>
                            </div>
                            <div class="bg-white rounded p-3">
                                <p class="font-medium text-sm">laravel_session</p>
                                <p class="text-xs text-gray-600 mt-1">Durata: 2 ore</p>
                                <p class="text-xs text-gray-600">Finalità: Gestione sessione utente autenticato</p>
                            </div>
                            <div class="bg-white rounded p-3">
                                <p class="font-medium text-sm">remember_web</p>
                                <p class="text-xs text-gray-600 mt-1">Durata: 5 anni</p>
                                <p class="text-xs text-gray-600">Finalità: Funzione "Ricordami" al login</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cookie Funzionali -->
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            2.2 Cookie Funzionali
                        </h3>
                        <p class="text-sm mb-3">
                            Migliorano l'esperienza utente ricordando le tue preferenze.
                        </p>
                        <div class="space-y-2">
                            <div class="bg-white rounded p-3">
                                <p class="font-medium text-sm">user_preferences</p>
                                <p class="text-xs text-gray-600 mt-1">Durata: 12 mesi</p>
                                <p class="text-xs text-gray-600">Finalità: Salvataggio preferenze interfaccia (tema, lingua)</p>
                            </div>
                            <div class="bg-white rounded p-3">
                                <p class="font-medium text-sm">cookie_consent</p>
                                <p class="text-xs text-gray-600 mt-1">Durata: 12 mesi</p>
                                <p class="text-xs text-gray-600">Finalità: Memorizzazione consenso cookie</p>
                            </div>
                        </div>
                    </div>

                    <!-- Cookie di Analisi -->
                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                        <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                            2.3 Cookie di Analisi (Opzionali)
                        </h3>
                        <p class="text-sm mb-3">
                            Utilizzati per comprendere come i visitatori utilizzano il sito. Richiedono il tuo consenso.
                        </p>
                        <div class="bg-white rounded p-3">
                            <p class="font-medium text-sm">Google Analytics (_ga, _gid, _gat)</p>
                            <p class="text-xs text-gray-600 mt-1">Durata: Variabile (da sessione a 2 anni)</p>
                            <p class="text-xs text-gray-600">Finalità: Analisi traffico e comportamento utenti</p>
                            <p class="text-xs text-gray-600 mt-2">
                                <strong>Provider:</strong> Google LLC -
                                <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="text-purple-600 hover:text-purple-800 underline">Privacy Policy</a>
                            </p>
                        </div>
                    </div>

                    <!-- Cookie di Terze Parti -->
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <h3 class="font-semibold text-gray-900 mb-2 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            2.4 Cookie di Terze Parti
                        </h3>
                        <div class="space-y-2">
                            <div class="bg-white rounded p-3">
                                <p class="font-medium text-sm">PayPal</p>
                                <p class="text-xs text-gray-600 mt-1">Finalità: Gestione pagamenti sicuri</p>
                                <p class="text-xs text-gray-600 mt-1">
                                    <a href="https://www.paypal.com/it/webapps/mpp/ua/privacy-full" target="_blank" rel="noopener" class="text-yellow-600 hover:text-yellow-800 underline">Privacy Policy PayPal</a>
                                </p>
                            </div>
                            <div class="bg-white rounded p-3">
                                <p class="font-medium text-sm">Google reCAPTCHA</p>
                                <p class="text-xs text-gray-600 mt-1">Finalità: Protezione da spam e bot</p>
                                <p class="text-xs text-gray-600 mt-1">
                                    <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" class="text-yellow-600 hover:text-yellow-800 underline">Privacy Policy Google</a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Base Giuridica -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Base Giuridica</h2>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <ul class="space-y-2">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>Cookie Tecnici:</strong> Legittimo interesse (art. 6.1.f GDPR) - Necessari per fornire il servizio</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>Cookie Analitici:</strong> Consenso dell'utente (art. 6.1.a GDPR)</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-purple-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>Cookie Marketing:</strong> Consenso esplicito dell'utente</span>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Gestione Cookie -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Come Gestire i Cookie</h2>
                <p class="mb-3">Hai il diritto di accettare, rifiutare o gestire selettivamente i cookie:</p>

                <div class="space-y-3">
                    <div class="bg-gradient-to-r from-rose-50 to-pink-50 rounded-lg p-4 border border-rose-200">
                        <h4 class="font-semibold text-gray-900 mb-2">4.1 Tramite il Nostro Banner Cookie</h4>
                        <p class="text-sm">
                            Al primo accesso, ti mostriamo un banner dove puoi scegliere quali cookie accettare.
                            Puoi modificare le tue preferenze in qualsiasi momento tramite le impostazioni dell'account.
                        </p>
                    </div>

                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-4 border border-blue-200">
                        <h4 class="font-semibold text-gray-900 mb-2">4.2 Tramite il Browser</h4>
                        <p class="text-sm mb-2">Puoi gestire i cookie attraverso le impostazioni del tuo browser:</p>
                        <ul class="text-sm space-y-1">
                            <li>• <a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Google Chrome</a></li>
                            <li>• <a href="https://support.mozilla.org/it/kb/Gestione%20dei%20cookie" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Mozilla Firefox</a></li>
                            <li>• <a href="https://support.apple.com/it-it/guide/safari/sfri11471/mac" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Safari</a></li>
                            <li>• <a href="https://support.microsoft.com/it-it/microsoft-edge/eliminare-i-cookie-in-microsoft-edge-63947406-40ac-c3b8-57b9-2a946a29ae09" target="_blank" rel="noopener" class="text-blue-600 hover:underline">Microsoft Edge</a></li>
                        </ul>
                    </div>
                </div>

                <div class="mt-3 bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                    <p class="text-sm flex items-start">
                        <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-1.964-1.333-2.732 0L3.732 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <span><strong>Nota:</strong> La disabilitazione dei cookie tecnici potrebbe compromettere il corretto funzionamento del sito e alcune funzionalità potrebbero non essere disponibili.</span>
                    </p>
                </div>
            </section>

            <!-- Durata Cookie -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Durata dei Cookie</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-2">Cookie di Sessione</h4>
                        <p class="text-sm text-gray-700">
                            Vengono cancellati automaticamente alla chiusura del browser.
                            Durata: fino a chiusura browser
                        </p>
                    </div>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <h4 class="font-semibold text-gray-900 mb-2">Cookie Persistenti</h4>
                        <p class="text-sm text-gray-700">
                            Rimangono memorizzati sul dispositivo per un periodo determinato.
                            Durata: da 30 giorni a 24 mesi
                        </p>
                    </div>
                </div>
            </section>

            <!-- Aggiornamenti -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Aggiornamenti della Cookie Policy</h2>
                <p>
                    Questa Cookie Policy può essere aggiornata periodicamente. Ti invitiamo a consultare regolarmente
                    questa pagina per rimanere informato sulle modalità di utilizzo dei cookie.
                </p>
                <p class="mt-2 text-sm text-gray-600">
                    Data ultimo aggiornamento: <strong>{{ date('d/m/Y') }}</strong>
                </p>
            </section>

            <!-- Contatti -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Contatti</h2>
                <div class="bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg p-4 border border-rose-200">
                    <p class="mb-2">Per domande sulla nostra Cookie Policy:</p>
                    <p class="mb-1"><strong>Email:</strong> <a href="mailto:privacy@danzafacile.it" class="text-rose-600 hover:text-rose-800 underline">privacy@danzafacile.it</a></p>
                    <p class="mb-1"><strong>Supporto:</strong> <a href="mailto:support@danzafacile.it" class="text-rose-600 hover:text-rose-800 underline">support@danzafacile.it</a></p>
                </div>
            </section>

            <!-- Link Privacy -->
            <section class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <p class="text-sm">
                    Per maggiori informazioni sul trattamento dei dati personali, consulta la nostra
                    <a href="{{ route('privacy-policy') }}" class="text-blue-600 hover:text-blue-800 underline font-medium">Informativa Privacy</a>.
                </p>
            </section>
        </div>

        <!-- Back to Home -->
        <div class="text-center pt-6 border-t border-gray-200">
            <a href="{{ route('home') }}" class="inline-flex items-center text-sm text-rose-600 hover:text-rose-800 font-medium">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Torna alla Home
            </a>
        </div>
    </div>
</x-guest-layout>
