<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    QR Code Evento
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Mostra questo codice all'ingresso dell'evento
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('guest.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">QR Code</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="space-y-6">

                <!-- Event Info Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex flex-col md:flex-row items-start gap-6">
                        <!-- Event Image -->
                        <div class="w-32 h-32 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                            <img src="{{ $eventRegistration->event->image_url ?? asset('images/event-placeholder.jpg') }}"
                                 alt="{{ $eventRegistration->event->name }}"
                                 class="w-full h-full object-cover">
                        </div>

                        <!-- Event Details -->
                        <div class="flex-1">
                            <h3 class="text-2xl font-bold text-gray-900 mb-3">
                                {{ $eventRegistration->event->name }}
                            </h3>

                            <div class="space-y-2 text-gray-700">
                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="font-medium">{{ $eventRegistration->event->start_date->format('d/m/Y') }}</span>
                                    <span class="mx-2">•</span>
                                    <span>{{ $eventRegistration->event->start_date->format('H:i') }}</span>
                                </div>

                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    <span>{{ $eventRegistration->event->location }}</span>
                                </div>

                                <div class="flex items-center">
                                    <svg class="w-5 h-5 mr-3 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    <span>{{ $eventRegistration->user->name }}</span>
                                </div>
                            </div>

                            <!-- Registration Status -->
                            <div class="mt-4">
                                @if($eventRegistration->checked_in_at)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 border border-green-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Check-in Effettuato
                                    </span>
                                    <p class="text-sm text-gray-600 mt-1">
                                        {{ $eventRegistration->checked_in_at->format('d/m/Y H:i') }}
                                    </p>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 border border-blue-200">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Pronto per il Check-in
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Code Card -->
                <div class="bg-white rounded-lg shadow p-8">
                    <div class="text-center">
                        <h3 class="text-xl font-bold text-gray-900 mb-6">Il Tuo QR Code</h3>

                        <!-- QR Code Display -->
                        <div class="inline-block p-6 bg-white rounded-lg border-4 border-gray-200">
                            <img src="{{ $qrCodeUrl }}"
                                 alt="QR Code per {{ $eventRegistration->event->name }}"
                                 class="w-64 h-64 md:w-80 md:h-80">
                        </div>

                        <!-- Registration Token -->
                        <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm font-medium text-gray-700 mb-2">Codice Iscrizione</p>
                            <p class="text-2xl font-mono font-bold text-gray-900 tracking-wider">
                                {{ strtoupper(substr($eventRegistration->token, 0, 8)) }}
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                (In caso di problemi con il QR code, fornisci questo codice)
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                            <!-- Download Button -->
                            <a href="{{ $qrCodeUrl }}"
                               download="qrcode-{{ $eventRegistration->id }}.png"
                               class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-base font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Scarica QR Code
                            </a>

                            <!-- Print Button -->
                            <button onclick="window.print()"
                                    class="inline-flex items-center justify-center px-6 py-3 bg-gray-600 text-white text-base font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                </svg>
                                Stampa QR Code
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Instructions Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                        <svg class="w-6 h-6 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        Istruzioni
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-rose-100 text-rose-600 rounded-full font-bold text-sm">
                                    1
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-700">
                                    <strong>All'arrivo all'evento</strong>, mostra questo QR code allo staff all'ingresso
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-rose-100 text-rose-600 rounded-full font-bold text-sm">
                                    2
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-700">
                                    <strong>Il QR code può essere scansionato</strong> direttamente dal tuo telefono o da una stampa
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="flex items-center justify-center w-8 h-8 bg-rose-100 text-rose-600 rounded-full font-bold text-sm">
                                    3
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-gray-700">
                                    <strong>Dopo la scansione</strong>, riceverai una conferma del check-in e potrai accedere all'evento
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Security Notice -->
                    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                        <div class="flex items-start">
                            <svg class="w-5 h-5 text-yellow-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <div class="text-sm text-yellow-800">
                                <p class="font-medium mb-1">Importante - Non Condividere</p>
                                <p>Questo QR code è personale e univoco. Non condividerlo con altre persone. Ogni QR code può essere utilizzato una sola volta per il check-in.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Back Button -->
                <div class="flex justify-center">
                    <a href="{{ route('guest.dashboard') }}"
                       class="inline-flex items-center px-6 py-3 bg-gray-600 text-white text-base font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                        </svg>
                        Torna alla Dashboard
                    </a>
                </div>

            </div>
        </div>
    </div>

    @push('styles')
    <style>
        @media print {
            /* Hide everything except QR code when printing */
            body * {
                visibility: hidden;
            }
            .bg-white.rounded-lg.shadow.p-8,
            .bg-white.rounded-lg.shadow.p-8 * {
                visibility: visible;
            }
            .bg-white.rounded-lg.shadow.p-8 {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                page-break-after: avoid;
            }
            /* Hide buttons when printing */
            button,
            a[download],
            .bg-gray-50 {
                display: none !important;
            }
        }
    </style>
    @endpush

</x-app-layout>
