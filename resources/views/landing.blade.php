<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DanzaFacile - Il Software per Scuole di Danza Creato da Chi Vive la Danza</title>
    <meta name="description" content="Il PRIMO software 100% italiano per scuole di danza. Creato da una professionista della danza. Setup gratuito + primo mese gratis per i primi 30 titolari.">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
        .gradient-text { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .pulse-slow { animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite; }
    </style>
</head>
<body class="bg-gray-50">

    <!-- HERO SECTION - Above the Fold -->
    <section class="relative bg-gradient-to-br from-rose-600 via-purple-600 to-indigo-700 text-white overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'1\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Copy Principale -->
                <div class="space-y-8">
                    <!-- Badge -->
                    <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full border border-white/20">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        <span class="text-sm font-medium">✨ OFFERTA LANCIO: Solo per i primi 30 titolari</span>
                    </div>

                    <!-- Headline Potente -->
                    <h1 class="text-4xl lg:text-6xl font-black leading-tight">
                        Gestisci la Tua Scuola di Danza in <span class="text-yellow-300">10 Minuti al Giorno</span> Invece di 4 Ore
                    </h1>

                    <!-- Subheadline -->
                    <p class="text-xl lg:text-2xl text-gray-100 font-medium leading-relaxed">
                        Il <span class="font-bold text-yellow-300">PRIMO software 100% italiano</span> per scuole di danza. Creato da una professionista della danza che <span class="underline">VIVE i tuoi stessi problemi ogni giorno</span>, non da programmatori che non sanno cosa sia un plié.
                    </p>

                    <!-- Benefici Chiave -->
                    <ul class="space-y-4 text-lg">
                        <li class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>Basta fogli Excel e WhatsApp:</strong> Tutto centralizzato in un unico sistema</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>Zero stress sui pagamenti:</strong> Solleciti automatici e tracking in tempo reale</span>
                        </li>
                        <li class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span><strong>Genitori felici:</strong> Area personale dove vedono tutto (presenze, pagamenti, eventi)</span>
                        </li>
                    </ul>

                    <!-- CTA Principale -->
                    <div class="space-y-4">
                        <a href="#demo-form" class="inline-block w-full sm:w-auto">
                            <button class="w-full sm:w-auto bg-yellow-400 text-gray-900 px-8 py-5 rounded-xl text-xl font-black hover:bg-yellow-300 transform hover:scale-105 transition-all duration-200 shadow-2xl pulse-slow">
                                🎁 RICHIEDI DEMO GRATUITA + 1° MESE GRATIS
                            </button>
                        </a>
                        <p class="text-sm text-gray-200">⏰ Setup gratuito (valore €147) - Offerta valida fino al <span class="font-bold text-yellow-300" id="deadline"></span></p>
                    </div>
                </div>

                <!-- Visual / Stats -->
                <div class="relative">
                    <div class="bg-white/10 backdrop-blur-lg rounded-3xl p-8 border border-white/20 space-y-6">
                        <h3 class="text-2xl font-bold text-center mb-6">Quanto Stai Perdendo SENZA DanzaFacile?</h3>

                        <div class="space-y-4">
                            <div class="bg-red-500/20 border-2 border-red-400 rounded-xl p-4">
                                <div class="text-3xl font-black text-red-300">-20 ore/settimana</div>
                                <div class="text-sm text-gray-200">Perse in gestione manuale, telefonate, Excel</div>
                            </div>

                            <div class="bg-red-500/20 border-2 border-red-400 rounded-xl p-4">
                                <div class="text-3xl font-black text-red-300">-15% fatturato</div>
                                <div class="text-sm text-gray-200">Perso per ritardi nei pagamenti e mancati solleciti</div>
                            </div>

                            <div class="bg-red-500/20 border-2 border-red-400 rounded-xl p-4">
                                <div class="text-3xl font-black text-red-300">-€800/mese</div>
                                <div class="text-sm text-gray-200">Costo di un assistente part-time per fare quello che fa DanzaFacile</div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-white/20">
                            <div class="text-center">
                                <div class="text-sm text-gray-300 mb-2">DanzaFacile costa solo:</div>
                                <div class="text-5xl font-black text-yellow-300">€7-€99/mese</div>
                                <div class="text-sm text-gray-200 mt-2">Meno di un caffè al giorno per recuperare ore di vita</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PROBLEM AGITATION - Il Dolore -->
    <section class="py-20 bg-gray-900 text-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl lg:text-5xl font-black mb-6">
                    Riconosci Queste Situazioni? <span class="text-red-400">(Spoiler: Sì)</span>
                </h2>
                <p class="text-xl text-gray-300">Se sei un titolare di scuola di danza, vivi TUTTI i giorni almeno 3 di questi incubi...</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <div class="bg-red-900/30 border-2 border-red-700 rounded-xl p-6 space-y-3">
                    <div class="text-4xl">😫</div>
                    <h3 class="text-xl font-bold text-red-400">Ore Perse in Excel e Registri Cartacei</h3>
                    <p class="text-gray-300">Ogni settimana passi ORE a cercare di capire chi ha pagato, chi deve ancora saldare, quali genitori non hanno firmato l'autorizzazione...</p>
                </div>

                <div class="bg-red-900/30 border-2 border-red-700 rounded-xl p-6 space-y-3">
                    <div class="text-4xl">💸</div>
                    <h3 class="text-xl font-bold text-red-400">Soldi Persi per Ritardi/Mancati Pagamenti</h3>
                    <p class="text-gray-300">Non hai tempo di inseguire ogni genitore moroso. Risultato? Perdi il 10-15% del fatturato potenziale ogni anno.</p>
                </div>

                <div class="bg-red-900/30 border-2 border-red-700 rounded-xl p-6 space-y-3">
                    <div class="text-4xl">📱</div>
                    <h3 class="text-xl font-bold text-red-400">WhatsApp Invaso 24/7</h3>
                    <p class="text-gray-300">"A che ora è la lezione?" "Mio figlio era presente?" "Quanto devo ancora?" 50 messaggi AL GIORNO per cose che dovrebbero essere automatiche.</p>
                </div>

                <div class="bg-red-900/30 border-2 border-red-700 rounded-xl p-6 space-y-3">
                    <div class="text-4xl">🤯</div>
                    <h3 class="text-xl font-bold text-red-400">Caos Totale su Presenze ed Eventi</h3>
                    <p class="text-gray-300">Registro presenze manuale, conta chi viene al saggio, gestione iscrizioni eventi... Un INCUBO che porta via energie preziose.</p>
                </div>
            </div>

            <div class="mt-12 text-center">
                <p class="text-2xl font-bold text-yellow-300">E se ti dicessi che TUTTO questo può sparire in 24 ore?</p>
            </div>
        </div>
    </section>

    <!-- SOLUTION - La Soluzione -->
    <section class="py-20 bg-gradient-to-br from-gray-50 to-gray-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-black mb-6">
                    Ecco Come <span class="gradient-text">DanzaFacile</span> Trasforma la Tua Scuola in 24 Ore
                </h2>
                <p class="text-xl text-gray-700 max-w-3xl mx-auto">
                    Non è "solo un software". È il tuo <strong>assistente personale 24/7</strong> che lavora MENTRE TU insegni, dormi o vivi la tua vita.
                </p>
            </div>

            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-rose-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Gestione Pagamenti Automatica</h3>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Solleciti automatici via email e notifiche app</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Tracking in tempo reale: vedi subito chi deve pagare</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Integrazione PayPal/Bonifico/Carta</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Report finanziari istantanei</span>
                        </li>
                    </ul>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm font-bold text-purple-600">ROI: Recuperi subito il 10-15% di fatturato perso</p>
                    </div>
                </div>

                <!-- Feature 2 -->
                <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">Presenze & Corsi Smart</h3>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Registro presenze digitale (tablet/smartphone)</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Genitori vedono presenze in tempo reale</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Gestione corsi, orari, sale automatica</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Iscrizioni online 24/7</span>
                        </li>
                    </ul>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm font-bold text-cyan-600">ROI: Risparmi 10 ore/settimana di lavoro manuale</p>
                    </div>
                </div>

                <!-- Feature 3 -->
                <div class="bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-2">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold mb-4">App Mobile per Studenti e Genitori</h3>
                    <ul class="space-y-3 text-gray-700">
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span><strong>App dedicata iOS/Android:</strong> Studenti e genitori vedono tutto dal cellulare</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Presenze, pagamenti, documenti sempre disponibili</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Notifiche push per eventi, saggi e comunicazioni</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Upload certificati medici e autorizzazioni direttamente dall'app</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <span class="text-green-600 font-bold">✓</span>
                            <span>Galleria foto/video privata condivisa con le famiglie</span>
                        </li>
                    </ul>
                    <div class="mt-6 pt-6 border-t border-gray-200">
                        <p class="text-sm font-bold text-emerald-600">ROI: Zero domande WhatsApp, genitori felici</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CHI HA CREATO DANZAFACILE -->
    <section class="py-20 bg-white">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-black text-gray-900 mb-4">
                    Creato da Chi Vive la Danza Ogni Giorno
                </h2>
                <p class="text-xl text-gray-600">Non da programmatori in giacca e cravatta, ma da una professionista della danza</p>
            </div>

            <div class="bg-gradient-to-br from-rose-50 to-purple-50 rounded-3xl p-8 md:p-12 shadow-xl">
                <div class="grid md:grid-cols-3 gap-8 items-center">
                    <!-- Foto Daniela Crescenzio -->
                    <div class="flex justify-center">
                        <div class="relative">
                            <img src="{{ asset('images/daniela-crescenzio.jpeg') }}"
                                 alt="Daniela Crescenzio - Fondatrice DanzaFacile"
                                 class="w-48 h-48 rounded-full object-cover object-top shadow-2xl ring-4 ring-white">
                            <div class="absolute -bottom-2 -right-2 bg-yellow-400 rounded-full p-3 shadow-lg">
                                <span class="text-2xl">💃</span>
                            </div>
                        </div>
                    </div>

                    <!-- Testo -->
                    <div class="md:col-span-2 space-y-4 text-gray-700">
                        <p class="text-lg leading-relaxed">
                            <strong class="text-rose-600 text-xl">Ho creato DanzaFacile</strong> perché ero stanca di perdere ore con Excel, WhatsApp e telefonate per gestire la mia scuola di danza.
                        </p>
                        <p class="text-lg leading-relaxed">
                            Come titolare e insegnante, <strong>conosco esattamente i tuoi problemi:</strong> genitori che chiedono le stesse cose 10 volte, pagamenti da rincorrere, presenze da segnare a mano, documenti sparsi ovunque.
                        </p>
                        <p class="text-lg leading-relaxed">
                            DanzaFacile <strong>non è un software generico adattato alla danza.</strong> È nato NELLA danza, PER la danza. Ogni funzione è pensata per rispondere a un problema reale che vivo ogni giorno.
                        </p>
                        <div class="pt-4 flex items-center space-x-2 text-rose-600 font-bold">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span>15+ anni di esperienza nella gestione di scuole di danza</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- PRICING - Offerta Irresistibile -->
    <section class="py-20 bg-gray-900 text-white" id="pricing">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl lg:text-5xl font-black mb-6">
                    Scegli il Piano Perfetto per la Tua Scuola
                </h2>
                <p class="text-xl text-gray-300 mb-8">Setup gratuito (€147 di valore) + Primo mese GRATIS per tutti i piani</p>
                <div class="inline-flex items-center space-x-2 bg-yellow-400 text-gray-900 px-6 py-3 rounded-full font-bold">
                    <span class="animate-pulse">🔥</span>
                    <span>OFFERTA VALIDA SOLO PER I PRIMI 30 TITOLARI</span>
                </div>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Entry Plan -->
                <div class="bg-white text-gray-900 rounded-2xl p-8 border-4 border-gray-300 hover:border-purple-500 transition-all duration-300 transform hover:scale-105">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold mb-2">Entry</h3>
                        <p class="text-gray-600">Perfetto per iniziare</p>
                    </div>
                    <div class="mb-6">
                        <div class="text-5xl font-black mb-2">€7<span class="text-2xl text-gray-600">/mese</span></div>
                        <p class="text-sm text-gray-600">Fino a 25 utenti</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Gestione corsi e presenze</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Pagamenti e solleciti</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Area genitori</span>
                        </li>
                    </ul>
                    <a href="#demo-form" class="block w-full bg-gray-900 text-white text-center py-4 rounded-xl font-bold hover:bg-gray-800 transition-colors">
                        Inizia Gratis
                    </a>
                </div>

                <!-- Basic Plan -->
                <div class="bg-white text-gray-900 rounded-2xl p-8 border-4 border-purple-500 hover:border-purple-600 transition-all duration-300 transform hover:scale-105">
                    <div class="mb-6">
                        <div class="bg-purple-500 text-white text-xs font-bold px-3 py-1 rounded-full inline-block mb-2">PIÙ POPOLARE</div>
                        <h3 class="text-2xl font-bold mb-2">Basic</h3>
                        <p class="text-gray-600">Per scuole in crescita</p>
                    </div>
                    <div class="mb-6">
                        <div class="text-5xl font-black mb-2">€27<span class="text-2xl text-gray-600">/mese</span></div>
                        <p class="text-sm text-gray-600">Fino a 50 utenti</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Tutto di Entry +</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Gestione eventi e saggi</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Galleria foto/video</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Report avanzati</span>
                        </li>
                    </ul>
                    <a href="#demo-form" class="block w-full bg-gradient-to-r from-rose-500 to-purple-600 text-white text-center py-4 rounded-xl font-bold hover:from-rose-600 hover:to-purple-700 transition-all">
                        Inizia Gratis
                    </a>
                </div>

                <!-- Professional Plan -->
                <div class="bg-white text-gray-900 rounded-2xl p-8 border-4 border-gray-300 hover:border-purple-500 transition-all duration-300 transform hover:scale-105">
                    <div class="mb-6">
                        <h3 class="text-2xl font-bold mb-2">Professional</h3>
                        <p class="text-gray-600">Per scuole strutturate</p>
                    </div>
                    <div class="mb-6">
                        <div class="text-5xl font-black mb-2">€47<span class="text-2xl text-gray-600">/mese</span></div>
                        <p class="text-sm text-gray-600">Fino a 150 utenti</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Tutto di Basic +</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Multi-sede</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">Gestione staff avanzata</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm">API per integrazioni</span>
                        </li>
                    </ul>
                    <a href="#demo-form" class="block w-full bg-gray-900 text-white text-center py-4 rounded-xl font-bold hover:bg-gray-800 transition-colors">
                        Inizia Gratis
                    </a>
                </div>

                <!-- Enterprise Plan -->
                <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 text-gray-900 rounded-2xl p-8 border-4 border-yellow-600 transform hover:scale-105 transition-all duration-300 shadow-2xl">
                    <div class="mb-6">
                        <div class="bg-gray-900 text-yellow-400 text-xs font-bold px-3 py-1 rounded-full inline-block mb-2">SCUOLE GRANDI</div>
                        <h3 class="text-2xl font-bold mb-2">Enterprise</h3>
                        <p class="text-gray-700">Soluzione completa</p>
                    </div>
                    <div class="mb-6">
                        <div class="text-5xl font-black mb-2">€99<span class="text-2xl text-gray-700">/mese</span></div>
                        <p class="text-sm text-gray-700 font-bold">Utenti ILLIMITATI</p>
                    </div>
                    <ul class="space-y-3 mb-8">
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm font-semibold">Tutto di Professional +</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm font-semibold">Supporto prioritario 24/7</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm font-semibold">Personalizzazioni su misura</span>
                        </li>
                        <li class="flex items-start space-x-2">
                            <svg class="w-5 h-5 text-green-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-sm font-semibold">Formazione dedicata</span>
                        </li>
                    </ul>
                    <a href="#demo-form" class="block w-full bg-gray-900 text-yellow-400 text-center py-4 rounded-xl font-bold hover:bg-gray-800 transition-colors">
                        Inizia Gratis
                    </a>
                </div>
            </div>

            <div class="mt-12 text-center">
                <p class="text-gray-400 text-sm">💳 Nessuna carta di credito richiesta per il mese gratis • 📞 Cancellazione in qualsiasi momento • ✅ Soddisfatto o rimborsato 60 giorni</p>
            </div>
        </div>
    </section>

    <!-- GARANZIA -->
    <section class="py-16 bg-gradient-to-r from-green-500 to-emerald-600 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-6xl mb-6">🛡️</div>
            <h2 class="text-4xl font-black mb-6">Garanzia "Dormi Sonni Tranquilli"</h2>
            <p class="text-xl mb-8 leading-relaxed">
                Prova DanzaFacile per <span class="font-black text-yellow-300">60 giorni COMPLETI</span>. Se non sei convinta che ti stia facendo risparmiare almeno 10 ore a settimana e recuperare soldi persi... ti rimborsiamo fino all'ultimo centesimo. <span class="font-black">ZERO domande, ZERO storie.</span>
            </p>
            <p class="text-2xl font-bold">Il rischio è TUTTO nostro. Tu hai solo da guadagnare.</p>
        </div>
    </section>

    <!-- DEMO FORM - Conversion Point -->
    <section class="py-20 bg-gradient-to-br from-purple-900 via-purple-800 to-rose-900 text-white" id="demo-form">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl lg:text-5xl font-black mb-6">
                    Richiedi la Tua Demo Gratuita ADESSO
                </h2>
                <p class="text-xl mb-4">
                    Setup Gratuito (€147 di valore) + Primo Mese GRATIS
                </p>
                <div class="inline-flex items-center space-x-2 bg-red-600 px-6 py-3 rounded-full font-bold animate-pulse">
                    <span>⏰</span>
                    <span>Posti rimanenti: <span id="spots-left">17</span>/30</span>
                </div>
            </div>

            <div class="bg-white text-gray-900 rounded-2xl p-8 shadow-2xl">
                <form action="{{ route('landing.demo') }}" method="POST" class="space-y-6">
                    @csrf
                    <div>
                        <label for="name" class="block text-sm font-bold mb-2">Nome e Cognome *</label>
                        <input type="text" id="name" name="name" required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                               placeholder="Es: Maria Rossi">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-bold mb-2">Email *</label>
                        <input type="email" id="email" name="email" required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                               placeholder="Es: maria@scuoladanza.it">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-bold mb-2">Telefono *</label>
                        <input type="tel" id="phone" name="phone" required
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                               placeholder="Es: 333 1234567">
                    </div>

                    <div>
                        <label for="school_name" class="block text-sm font-bold mb-2">Nome della Scuola</label>
                        <input type="text" id="school_name" name="school_name"
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                               placeholder="Es: Accademia Danza Eleganza">
                    </div>

                    <div>
                        <label for="students_count" class="block text-sm font-bold mb-2">Quanti allievi hai?</label>
                        <select id="students_count" name="students_count"
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all">
                            <option value="">Seleziona...</option>
                            <option value="1-25">1-25 allievi (Piano Entry)</option>
                            <option value="26-50">26-50 allievi (Piano Basic)</option>
                            <option value="51-150">51-150 allievi (Piano Professional)</option>
                            <option value="150+">150+ allievi (Piano Enterprise)</option>
                        </select>
                    </div>

                    <div>
                        <label for="message" class="block text-sm font-bold mb-2">Qual è il tuo problema più grande adesso?</label>
                        <textarea id="message" name="message" rows="4"
                                  class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-purple-500 focus:ring-2 focus:ring-purple-200 transition-all"
                                  placeholder="Es: Perdo ore su Excel e non riesco a gestire i solleciti pagamenti..."></textarea>
                    </div>

                    <div class="flex items-start space-x-3">
                        <input type="checkbox" id="privacy" name="privacy" required class="mt-1">
                        <label for="privacy" class="text-sm text-gray-700">
                            Accetto la <a href="#" class="text-purple-600 underline">Privacy Policy</a> e autorizzo il trattamento dei miei dati per ricevere la demo gratuita.
                        </label>
                    </div>

                    <button type="submit"
                            class="w-full bg-gradient-to-r from-yellow-400 to-yellow-500 text-gray-900 py-5 rounded-xl text-xl font-black hover:from-yellow-500 hover:to-yellow-600 transform hover:scale-105 transition-all duration-200 shadow-2xl">
                        🎁 RICHIEDI DEMO + 1° MESE GRATIS ADESSO
                    </button>

                    <p class="text-center text-sm text-gray-600">
                        ✅ Riceverai la demo entro 24 ore • 🔒 I tuoi dati sono al sicuro • 📞 Nessuna chiamata di vendita aggressiva
                    </p>
                </form>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-4xl font-black mb-4">Domande Frequenti</h2>
                <p class="text-xl text-gray-600">Le risposte che cercavi (e che ti tolgono ogni scusa 😉)</p>
            </div>

            <div class="space-y-6">
                <details class="bg-white rounded-xl p-6 shadow-md cursor-pointer group">
                    <summary class="font-bold text-lg flex justify-between items-center">
                        <span>❓ "Non sono brava con la tecnologia, ce la farò?"</span>
                        <span class="group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="mt-4 text-gray-700">
                        DanzaFacile è stato creato PROPRIO per chi non è "tecnologica". Se sai usare WhatsApp, sai usare DanzaFacile. Inoltre ricevi setup gratuito + video tutorial + supporto live. Impossibile non riuscirci.
                    </p>
                </details>

                <details class="bg-white rounded-xl p-6 shadow-md cursor-pointer group">
                    <summary class="font-bold text-lg flex justify-between items-center">
                        <span>❓ "E se non funziona per la mia scuola?"</span>
                        <span class="group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="mt-4 text-gray-700">
                        Hai 60 giorni di garanzia soddisfatto o rimborsato. Se non ti piace, ti rimborsiamo tutto. Il rischio è ZERO.
                    </p>
                </details>

                <details class="bg-white rounded-xl p-6 shadow-md cursor-pointer group">
                    <summary class="font-bold text-lg flex justify-between items-center">
                        <span>❓ "Devo cambiare tutte le mie abitudini?"</span>
                        <span class="group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="mt-4 text-gray-700">
                        No! DanzaFacile si adatta a come lavori TU, non il contrario. Puoi migrare i dati piano piano e continuare a usare Excel mentre impari. Nessuno stress.
                    </p>
                </details>

                <details class="bg-white rounded-xl p-6 shadow-md cursor-pointer group">
                    <summary class="font-bold text-lg flex justify-between items-center">
                        <span>❓ "Quanto tempo serve per impostare tutto?"</span>
                        <span class="group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="mt-4 text-gray-700">
                        Setup gratuito fatto per te in 24 ore. Dopo 1 settimana sei operativa al 100%. Letteralmente meno tempo di quanto passi in un weekend a sistemare Excel.
                    </p>
                </details>

                <details class="bg-white rounded-xl p-6 shadow-md cursor-pointer group">
                    <summary class="font-bold text-lg flex justify-between items-center">
                        <span>❓ "Cosa succede se cambio idea dopo il mese gratis?"</span>
                        <span class="group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="mt-4 text-gray-700">
                        Puoi cancellare in qualsiasi momento con un click. Nessun vincolo, nessuna penale. Se non ti serve più, ciao ciao. Semplice.
                    </p>
                </details>

                <details class="bg-white rounded-xl p-6 shadow-md cursor-pointer group">
                    <summary class="font-bold text-lg flex justify-between items-center">
                        <span>❓ "I miei dati sono al sicuro?"</span>
                        <span class="group-open:rotate-180 transition-transform">▼</span>
                    </summary>
                    <p class="mt-4 text-gray-700">
                        Sì. Server in Italia, backup giornalieri automatici, conformità GDPR totale, crittografia dati. I tuoi dati sono più al sicuro che su Excel nel tuo PC.
                    </p>
                </details>
            </div>
        </div>
    </section>

    <!-- FINAL CTA -->
    <section class="py-20 bg-gradient-to-br from-rose-600 via-purple-600 to-indigo-700 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-black mb-8">
                Ultima Chiamata: Smetti di Perdere Tempo (e Soldi)
            </h2>
            <p class="text-2xl mb-8 leading-relaxed">
                Ogni settimana che passa SENZA DanzaFacile = <span class="font-black text-yellow-300">20 ore perse + €200+ di fatturato perso</span>
            </p>
            <p class="text-xl mb-12">
                Tra 30 giorni potresti ancora essere qui a cercare di far quadrare Excel alle 23:00...<br>
                <span class="font-black text-yellow-300">OPPURE</span> potresti già aver recuperato 80 ore di vita e centinaia di euro.
            </p>

            <a href="#demo-form" class="inline-block">
                <button class="bg-yellow-400 text-gray-900 px-12 py-6 rounded-xl text-2xl font-black hover:bg-yellow-300 transform hover:scale-105 transition-all duration-200 shadow-2xl animate-pulse">
                    SÌ, VOGLIO LA MIA DEMO GRATUITA ADESSO
                </button>
            </a>

            <p class="mt-8 text-sm text-gray-200">
                Setup gratuito (€147) + Primo mese GRATIS • Nessuna carta richiesta • Garanzia 60 giorni
            </p>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-white font-bold text-xl mb-4">DanzaFacile</h3>
                    <p class="text-sm">Il primo software 100% italiano per scuole di danza. Creato da professionisti della danza per professionisti della danza.</p>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">Link Utili</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition-colors">Login</a></li>
                        <li><a href="#pricing" class="hover:text-white transition-colors">Prezzi</a></li>
                        <li><a href="#demo-form" class="hover:text-white transition-colors">Richiedi Demo</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-white font-bold mb-4">Contatti</h4>
                    <ul class="space-y-2 text-sm">
                        <li>📧 info@danzafacile.it</li>
                        <li>📱 +39 340 929 5364</li>
                        <li>🏢 Made with ❤️ in Italia</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm">
                <p>&copy; {{ date('Y') }} DanzaFacile. Tutti i diritti riservati. | P.IVA 03003220732</p>
                <p class="mt-2"><a href="#" class="hover:text-white">Privacy Policy</a> | <a href="#" class="hover:text-white">Termini di Servizio</a></p>
            </div>
        </div>
    </footer>

    <script>
        // Countdown per deadline offerta (7 giorni da oggi)
        const deadline = new Date();
        deadline.setDate(deadline.getDate() + 7);
        document.getElementById('deadline').textContent = deadline.toLocaleDateString('it-IT', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        });

        // Simulazione posti rimanenti (scende ogni volta che qualcuno visita - cookie based)
        const spotsLeftEl = document.getElementById('spots-left');
        let spotsLeft = localStorage.getItem('spotsLeft') || 17;

        // Simula riduzione posti random
        if (Math.random() > 0.5) {
            spotsLeft = Math.max(5, parseInt(spotsLeft) - 1);
            localStorage.setItem('spotsLeft', spotsLeft);
        }

        spotsLeftEl.textContent = spotsLeft;

        // Smooth scroll per CTA
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });

        // Form validation e UX migliorata
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.innerHTML = '⏳ Invio in corso...';
                submitBtn.disabled = true;
            });
        }
    </script>
</body>
</html>
