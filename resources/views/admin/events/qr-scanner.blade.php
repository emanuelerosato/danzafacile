<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Scanner QR Code - Check-in Evento
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Scansiona i QR code dei partecipanti per registrare il check-in
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.events.index') }}" class="text-gray-500 hover:text-gray-700">Eventi</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Scanner QR</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="space-y-6">

                <!-- Event Info Card -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-20 h-20 bg-gray-200 rounded-lg overflow-hidden flex-shrink-0">
                                <img src="{{ $event->image_url ?? asset('images/event-placeholder.jpg') }}"
                                     alt="{{ $event->name }}"
                                     class="w-full h-full object-cover">
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-gray-900">{{ $event->name }}</h3>
                                <div class="mt-1 space-y-1 text-sm text-gray-600">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $event->start_date->format('d/m/Y H:i') }}
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $event->location }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.events.show', $event->id) }}"
                           class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                            Torna all'Evento
                        </a>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                    <!-- Total Registrations -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Totale Iscritti</p>
                                <p class="text-2xl font-bold text-gray-900" id="stat-total">{{ $stats['total'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Checked In -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Check-in Effettuati</p>
                                <p class="text-2xl font-bold text-green-600" id="stat-checked-in">{{ $stats['checked_in'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Pending -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">In Attesa</p>
                                <p class="text-2xl font-bold text-yellow-600" id="stat-pending">{{ $stats['pending'] }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Check-in Rate -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Tasso Check-in</p>
                                <p class="text-2xl font-bold text-purple-600" id="stat-rate">{{ $stats['rate'] }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- QR Scanner Section -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

                    <!-- Left: Scanner Interface -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            Scanner QR Code
                        </h3>

                        <!-- Camera View -->
                        <div id="qr-reader" class="w-full bg-gray-900 rounded-lg overflow-hidden mb-4" style="min-height: 300px;"></div>

                        <!-- Scanner Controls -->
                        <div class="flex gap-2 mb-4">
                            <button id="start-scanner"
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Avvia Scanner
                            </button>
                            <button id="stop-scanner"
                                    disabled
                                    class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/>
                                </svg>
                                Ferma Scanner
                            </button>
                        </div>

                        <!-- Manual Token Input (Fallback) -->
                        <div class="border-t border-gray-200 pt-4">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Inserimento Manuale</h4>
                            <form id="manual-checkin-form" class="flex gap-2">
                                @csrf
                                <input type="text"
                                       id="manual-token"
                                       name="token"
                                       placeholder="Inserisci codice token..."
                                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent text-sm">
                                <button type="submit"
                                        class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Check-in
                                </button>
                            </form>
                        </div>

                        <!-- Scan Result Display -->
                        <div id="scan-result" class="mt-4 hidden">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>

                    <!-- Right: Recent Check-ins -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Check-in Recenti
                        </h3>

                        <div id="recent-checkins" class="space-y-3 max-h-96 overflow-y-auto">
                            @forelse($event->registrations()->where('checked_in_at', '!=', null)->latest('checked_in_at')->limit(10)->get() as $registration)
                                <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg">
                                    <div class="flex items-center flex-1 min-w-0">
                                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $registration->user->name }}
                                            </p>
                                            <p class="text-xs text-gray-600">
                                                {{ $registration->checked_in_at->format('H:i:s') }}
                                            </p>
                                        </div>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                                        {{ $registration->participant_type === 'guest' ? 'Ospite' : 'Studente' }}
                                    </span>
                                </div>
                            @empty
                                <div class="text-center py-8 text-gray-500">
                                    <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <p class="text-sm">Nessun check-in ancora effettuato</p>
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script nonce="@cspNonce">
        let html5QrCode = null;
        let isScanning = false;

        const startBtn = document.getElementById('start-scanner');
        const stopBtn = document.getElementById('stop-scanner');
        const scanResult = document.getElementById('scan-result');
        const recentCheckins = document.getElementById('recent-checkins');

        // Start Scanner
        startBtn.addEventListener('click', function() {
            html5QrCode = new Html5Qrcode("qr-reader");

            html5QrCode.start(
                { facingMode: "environment" },
                { fps: 10, qrbox: { width: 250, height: 250 } },
                onScanSuccess,
                onScanFailure
            ).then(() => {
                isScanning = true;
                startBtn.disabled = true;
                stopBtn.disabled = false;
            }).catch(err => {
                showError('Impossibile avviare la fotocamera: ' + err);
            });
        });

        // Stop Scanner
        stopBtn.addEventListener('click', function() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    isScanning = false;
                    startBtn.disabled = false;
                    stopBtn.disabled = true;
                });
            }
        });

        // QR Code Scan Success Handler
        function onScanSuccess(decodedText, decodedResult) {
            try {
                // Parse QR data (JSON format from QRCodeService)
                const qrData = JSON.parse(decodedText);

                // Send check-in request
                performCheckin(qrData.token);

            } catch (error) {
                // If not JSON, try as plain token
                performCheckin(decodedText);
            }
        }

        function onScanFailure(error) {
            // Ignore scan failures (continuous scanning)
        }

        // Perform Check-in
        function performCheckin(token) {
            fetch('{{ route('admin.events.checkin') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    event_id: {{ $event->id }},
                    token: token
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess(data);
                    updateStats(data.stats);
                    addRecentCheckin(data.participant);

                    // Beep sound on success
                    playSuccessSound();
                } else {
                    showError(data.message);
                    playErrorSound();
                }
            })
            .catch(error => {
                showError('Errore durante il check-in. Riprova.');
                console.error('Check-in error:', error);
            });
        }

        // Manual Check-in Form
        document.getElementById('manual-checkin-form').addEventListener('submit', function(e) {
            e.preventDefault();
            const token = document.getElementById('manual-token').value.trim();
            if (token) {
                performCheckin(token);
                document.getElementById('manual-token').value = '';
            }
        });

        // Show Success Message
        function showSuccess(data) {
            scanResult.className = 'mt-4 p-4 bg-green-100 border border-green-200 rounded-lg';
            scanResult.innerHTML = `
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-green-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold text-green-800">Check-in Confermato!</p>
                        <p class="text-sm text-green-700 mt-1">
                            <strong>${data.participant.name}</strong><br>
                            ${data.participant.email}<br>
                            Tipo: ${data.participant.type === 'guest' ? 'Ospite' : 'Studente'}
                        </p>
                    </div>
                </div>
            `;
            scanResult.classList.remove('hidden');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                scanResult.classList.add('hidden');
            }, 5000);
        }

        // Show Error Message
        function showError(message) {
            scanResult.className = 'mt-4 p-4 bg-red-100 border border-red-200 rounded-lg';
            scanResult.innerHTML = `
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-red-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold text-red-800">Errore Check-in</p>
                        <p class="text-sm text-red-700 mt-1">${message}</p>
                    </div>
                </div>
            `;
            scanResult.classList.remove('hidden');

            // Auto-hide after 5 seconds
            setTimeout(() => {
                scanResult.classList.add('hidden');
            }, 5000);
        }

        // Update Stats
        function updateStats(stats) {
            document.getElementById('stat-total').textContent = stats.total;
            document.getElementById('stat-checked-in').textContent = stats.checked_in;
            document.getElementById('stat-pending').textContent = stats.pending;
            document.getElementById('stat-rate').textContent = stats.rate + '%';
        }

        // Add Recent Check-in
        function addRecentCheckin(participant) {
            const checkinHtml = `
                <div class="flex items-center justify-between p-3 bg-green-50 border border-green-200 rounded-lg animate-pulse">
                    <div class="flex items-center flex-1 min-w-0">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="ml-3 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">${participant.name}</p>
                            <p class="text-xs text-gray-600">${new Date().toLocaleTimeString('it-IT')}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                        ${participant.type === 'guest' ? 'Ospite' : 'Studente'}
                    </span>
                </div>
            `;

            recentCheckins.insertAdjacentHTML('afterbegin', checkinHtml);

            // Remove animation after 2 seconds
            setTimeout(() => {
                recentCheckins.firstElementChild.classList.remove('animate-pulse');
            }, 2000);
        }

        // Sound Effects
        function playSuccessSound() {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.2);
        }

        function playErrorSound() {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 300;
            oscillator.type = 'square';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.3);
        }

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (html5QrCode && isScanning) {
                html5QrCode.stop();
            }
        });
    </script>
    @endpush

</x-app-layout>
