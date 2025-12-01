<x-guest-layout>
    <!-- Success Header -->
    <div class="bg-gradient-to-r from-green-500 to-emerald-600 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-full mb-6">
                <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                Iscrizione Confermata!
            </h1>
            <p class="text-xl text-white/90">
                La tua partecipazione all'evento è stata registrata con successo
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Event Details Card -->
            <div class="bg-white rounded-lg shadow p-8 mb-6">
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">
                        Ci vediamo all'evento!
                    </h2>
                    <p class="text-gray-600">
                        Abbiamo inviato una email di conferma con tutti i dettagli
                    </p>
                </div>

                <!-- Event Info -->
                <div class="flex flex-col md:flex-row items-start md:items-center gap-6 p-6 bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg border border-rose-100">
                    <div class="w-32 h-32 bg-white rounded-lg overflow-hidden flex-shrink-0 shadow">
                        <img src="{{ $event->image_url ?? asset('images/event-placeholder.jpg') }}"
                             alt="{{ $event->name }}"
                             class="w-full h-full object-cover">
                    </div>
                    <div class="flex-1">
                        <h3 class="text-2xl font-bold text-gray-900 mb-3">{{ $event->name }}</h3>
                        <div class="space-y-2">
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span class="font-medium">{{ $event->start_date->format('d/m/Y') }}</span>
                                <span class="mx-2">•</span>
                                <span>{{ $event->start_date->format('H:i') }}</span>
                            </div>
                            <div class="flex items-center text-gray-700">
                                <svg class="w-5 h-5 mr-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>{{ $event->location }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registration Details -->
                <div class="mt-6 p-6 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-4">Dettagli Iscrizione</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Codice Iscrizione</p>
                            <p class="font-mono font-bold text-gray-900">{{ $eventRegistration->id }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Stato</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                Confermata
                            </span>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Partecipante</p>
                            <p class="font-medium text-gray-900">{{ $eventRegistration->user->name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Email</p>
                            <p class="font-medium text-gray-900">{{ $eventRegistration->user->email }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Next Steps Card -->
            <div class="bg-white rounded-lg shadow p-8 mb-6">
                <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
                    <svg class="w-6 h-6 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                    Prossimi Passi
                </h3>

                <div class="space-y-6">
                    <!-- Step 1 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-full font-bold">
                                1
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-bold text-gray-900 mb-1">Controlla la tua Email</h4>
                            <p class="text-gray-600">
                                Abbiamo inviato una email di conferma a <strong>{{ $eventRegistration->user->email }}</strong> con un link magico per accedere alla tua area riservata.
                            </p>
                            <div class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-sm text-blue-800">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    <strong>Importante:</strong> Controlla anche la cartella spam/posta indesiderata
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-full font-bold">
                                2
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-bold text-gray-900 mb-1">Salva il Link Magico</h4>
                            <p class="text-gray-600">
                                Il link magico ti permette di accedere alla tua dashboard personale senza password. Salvalo nei preferiti per un accesso rapido!
                            </p>
                        </div>
                    </div>

                    <!-- Step 3 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-full font-bold">
                                3
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-bold text-gray-900 mb-1">Visualizza il tuo QR Code</h4>
                            <p class="text-gray-600">
                                Dalla tua dashboard potrai visualizzare e scaricare il QR code da mostrare all'ingresso dell'evento.
                            </p>
                        </div>
                    </div>

                    <!-- Step 4 -->
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <div class="flex items-center justify-center w-10 h-10 bg-gradient-to-r from-rose-500 to-purple-600 text-white rounded-full font-bold">
                                4
                            </div>
                        </div>
                        <div class="ml-4 flex-1">
                            <h4 class="text-lg font-bold text-gray-900 mb-1">Aggiungi al Calendario</h4>
                            <p class="text-gray-600 mb-3">
                                Non dimenticare l'evento! Aggiungilo al tuo calendario.
                            </p>
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ $event->getGoogleCalendarUrl() }}"
                                   target="_blank"
                                   class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 16H5V9h14v11z"/>
                                    </svg>
                                    Google Calendar
                                </a>
                                <a href="{{ $event->getICalUrl() }}"
                                   download="event-{{ $event->slug }}.ics"
                                   class="inline-flex items-center px-3 py-2 bg-white border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-50 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    iCal / Outlook
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Buttons -->
            <div class="flex flex-col sm:flex-row gap-4">
                @auth('guest')
                    <a href="{{ route('guest.dashboard') }}"
                       class="flex-1 inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-lg font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Vai alla Dashboard
                    </a>
                @endauth

                <a href="{{ route('public.events.index') }}"
                   class="flex-1 inline-flex items-center justify-center px-6 py-4 bg-gray-600 text-white text-lg font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Scopri Altri Eventi
                </a>
            </div>

            <!-- Help Section -->
            <div class="mt-8 text-center">
                <p class="text-gray-600 mb-2">
                    Hai domande o hai bisogno di assistenza?
                </p>
                <a href="mailto:{{ config('mail.from.address') }}"
                   class="inline-flex items-center text-rose-600 hover:text-rose-700 font-medium">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Contattaci
                </a>
            </div>

        </div>
    </div>

</x-guest-layout>
