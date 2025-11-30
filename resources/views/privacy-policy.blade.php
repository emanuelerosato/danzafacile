<x-guest-layout>
    @section('title', 'Privacy Policy - Danza Facile')
    @section('description', 'Informativa Privacy e Protezione dei Dati Personali - Danza Facile')

    <div class="space-y-6">
        <div class="text-center">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Informativa Privacy</h1>
            <p class="text-sm text-gray-600">Ultimo aggiornamento: {{ date('d/m/Y') }}</p>
        </div>

        <div class="prose prose-sm max-w-none text-gray-700 space-y-6">
            <!-- Introduzione -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">1. Introduzione</h2>
                <p>
                    Benvenuti su Danza Facile. La presente Informativa Privacy descrive come raccogliamo, utilizziamo e proteggiamo
                    i tuoi dati personali in conformità al Regolamento Generale sulla Protezione dei Dati (GDPR - Regolamento UE 2016/679)
                    e alla normativa italiana vigente in materia di protezione dei dati personali.
                </p>
            </section>

            <!-- Titolare del Trattamento -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">2. Titolare del Trattamento</h2>
                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <p class="mb-2"><strong>Titolare del Trattamento:</strong> Danza Facile</p>
                    <p class="mb-2"><strong>Email:</strong> privacy@danzafacile.it</p>
                    <p><strong>Sede:</strong> [Indirizzo da inserire]</p>
                </div>
            </section>

            <!-- Dati Raccolti -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">3. Dati Personali Raccolti</h2>
                <p>Raccogliamo le seguenti categorie di dati personali:</p>

                <div class="mt-3 space-y-3">
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <h3 class="font-semibold text-gray-900 mb-2">3.1 Dati di Registrazione</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            <li>Nome e Cognome</li>
                            <li>Indirizzo Email</li>
                            <li>Numero di Telefono</li>
                            <li>Data di Nascita</li>
                            <li>Indirizzo di Residenza</li>
                        </ul>
                    </div>

                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <h3 class="font-semibold text-gray-900 mb-2">3.2 Dati di Navigazione</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            <li>Indirizzo IP</li>
                            <li>Browser e sistema operativo</li>
                            <li>Pagine visitate e orario di accesso</li>
                            <li>Cookie tecnici e di analisi</li>
                        </ul>
                    </div>

                    <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                        <h3 class="font-semibold text-gray-900 mb-2">3.3 Dati di Pagamento</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm">
                            <li>Informazioni per la fatturazione</li>
                            <li>Transazioni tramite PayPal (gestite direttamente dal provider)</li>
                            <li>Storico pagamenti e ricevute</li>
                        </ul>
                    </div>
                </div>
            </section>

            <!-- Finalità del Trattamento -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">4. Finalità del Trattamento</h2>
                <p>I tuoi dati personali vengono trattati per le seguenti finalità:</p>

                <div class="mt-3 space-y-2">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p><strong>Gestione Account:</strong> Creazione e gestione del tuo account utente</p>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p><strong>Erogazione Servizi:</strong> Iscrizioni ai corsi, gestione eventi e attività</p>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p><strong>Gestione Pagamenti:</strong> Elaborazione pagamenti e invio ricevute fiscali</p>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p><strong>Comunicazioni:</strong> Invio notifiche relative ai corsi e agli eventi</p>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p><strong>Assistenza:</strong> Supporto tecnico e customer care</p>
                    </div>
                    <div class="flex items-start">
                        <svg class="w-5 h-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <p><strong>Obblighi di Legge:</strong> Adempimenti fiscali e contabili</p>
                    </div>
                </div>
            </section>

            <!-- Base Giuridica -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">5. Base Giuridica del Trattamento</h2>
                <p>Il trattamento dei tuoi dati si basa su:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li><strong>Esecuzione contrattuale:</strong> necessità di erogare i servizi richiesti</li>
                    <li><strong>Obblighi di legge:</strong> adempimenti fiscali e normativi</li>
                    <li><strong>Consenso:</strong> per attività di marketing (solo se espressamente accordato)</li>
                    <li><strong>Legittimo interesse:</strong> miglioramento dei servizi e sicurezza della piattaforma</li>
                </ul>
            </section>

            <!-- Condivisione Dati -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">6. Condivisione dei Dati</h2>
                <p>I tuoi dati personali possono essere comunicati a:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li><strong>Provider di Servizi:</strong> PayPal per gestione pagamenti, AWS per hosting</li>
                    <li><strong>Professionisti:</strong> commercialisti per adempimenti fiscali</li>
                    <li><strong>Autorità Competenti:</strong> in caso di richieste legittime delle autorità</li>
                </ul>
                <p class="mt-2 text-sm text-gray-600">
                    Non vendiamo né cediamo i tuoi dati a terze parti per finalità commerciali.
                </p>
            </section>

            <!-- Conservazione Dati -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">7. Conservazione dei Dati</h2>
                <p>I dati personali vengono conservati per:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li><strong>Dati dell'account:</strong> fino alla cancellazione dell'account</li>
                    <li><strong>Dati di pagamento:</strong> 10 anni per obblighi fiscali</li>
                    <li><strong>Cookie tecnici:</strong> massimo 12 mesi</li>
                    <li><strong>Log di sistema:</strong> massimo 6 mesi</li>
                </ul>
            </section>

            <!-- Diritti dell'Utente -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">8. I Tuoi Diritti (GDPR)</h2>
                <p>Ai sensi del GDPR, hai diritto a:</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                    <div class="bg-gradient-to-br from-rose-50 to-pink-50 rounded-lg p-3 border border-rose-200">
                        <h4 class="font-semibold text-gray-900 mb-1">Accesso</h4>
                        <p class="text-sm text-gray-700">Ottenere copia dei tuoi dati personali</p>
                    </div>
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-lg p-3 border border-blue-200">
                        <h4 class="font-semibold text-gray-900 mb-1">Rettifica</h4>
                        <p class="text-sm text-gray-700">Correggere dati inesatti o incompleti</p>
                    </div>
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-lg p-3 border border-green-200">
                        <h4 class="font-semibold text-gray-900 mb-1">Cancellazione</h4>
                        <p class="text-sm text-gray-700">Richiedere la cancellazione dei dati</p>
                    </div>
                    <div class="bg-gradient-to-br from-purple-50 to-violet-50 rounded-lg p-3 border border-purple-200">
                        <h4 class="font-semibold text-gray-900 mb-1">Portabilità</h4>
                        <p class="text-sm text-gray-700">Ricevere i dati in formato strutturato</p>
                    </div>
                    <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-lg p-3 border border-yellow-200">
                        <h4 class="font-semibold text-gray-900 mb-1">Opposizione</h4>
                        <p class="text-sm text-gray-700">Opporti a determinati trattamenti</p>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-pink-50 rounded-lg p-3 border border-red-200">
                        <h4 class="font-semibold text-gray-900 mb-1">Revoca Consenso</h4>
                        <p class="text-sm text-gray-700">Revocare il consenso in qualsiasi momento</p>
                    </div>
                </div>

                <div class="mt-4 bg-rose-50 rounded-lg p-4 border border-rose-200">
                    <p class="text-sm">
                        <strong>Come esercitare i tuoi diritti:</strong><br>
                        Invia una richiesta a <a href="mailto:privacy@danzafacile.it" class="text-rose-600 hover:text-rose-800 underline">privacy@danzafacile.it</a>
                        oppure accedi alla sezione Profilo del tuo account.
                    </p>
                </div>
            </section>

            <!-- Sicurezza -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">9. Misure di Sicurezza</h2>
                <p>Adottiamo misure tecniche e organizzative adeguate per proteggere i tuoi dati:</p>
                <ul class="list-disc list-inside mt-2 space-y-1">
                    <li>Crittografia SSL/TLS per le comunicazioni</li>
                    <li>Backup regolari dei dati</li>
                    <li>Controlli di accesso e autenticazione</li>
                    <li>Monitoraggio continuo della sicurezza</li>
                    <li>Formazione del personale sulla privacy</li>
                </ul>
            </section>

            <!-- Modifiche -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">10. Modifiche all'Informativa</h2>
                <p>
                    Ci riserviamo il diritto di modificare la presente Informativa Privacy. Le modifiche saranno pubblicate
                    su questa pagina con indicazione della data di ultimo aggiornamento. Ti consigliamo di consultare
                    periodicamente questa pagina.
                </p>
            </section>

            <!-- Contatti -->
            <section>
                <h2 class="text-xl font-semibold text-gray-900 mb-3">11. Contatti</h2>
                <div class="bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg p-4 border border-rose-200">
                    <p class="mb-2">Per qualsiasi domanda o richiesta relativa alla privacy:</p>
                    <p class="mb-1"><strong>Email:</strong> <a href="mailto:privacy@danzafacile.it" class="text-rose-600 hover:text-rose-800 underline">privacy@danzafacile.it</a></p>
                    <p class="mb-1"><strong>Supporto:</strong> <a href="mailto:support@danzafacile.it" class="text-rose-600 hover:text-rose-800 underline">support@danzafacile.it</a></p>
                    <p class="text-sm text-gray-600 mt-2">Hai anche il diritto di presentare un reclamo all'Autorità Garante per la Protezione dei Dati Personali.</p>
                </div>
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
