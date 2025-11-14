<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grazie! - DanzaFacile</title>
    <meta name="description" content="Richiesta demo ricevuta con successo">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
    
    <!-- Google Ads Conversion Tracking - DA CONFIGURARE -->
    <!-- gtag('event', 'conversion', {'send_to': 'AW-XXXXXXXXX/XXXXXXXXXXXXX'}); -->
</head>
<body class="bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50">

    <div class="min-h-screen flex items-center justify-center px-4">
        <div class="max-w-2xl w-full">
            <!-- Success Card -->
            <div class="bg-white rounded-3xl shadow-2xl p-8 md:p-12 text-center space-y-6">
                <!-- Checkmark Animation -->
                <div class="flex justify-center">
                    <div class="relative">
                        <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-emerald-600 rounded-full flex items-center justify-center animate-bounce">
                            <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <div class="absolute -top-2 -right-2 text-4xl">🎉</div>
                    </div>
                </div>

                <!-- Heading -->
                <div class="space-y-3">
                    <h1 class="text-4xl md:text-5xl font-black text-gray-900">
                        Richiesta Ricevuta!
                    </h1>
                    <p class="text-xl text-gray-600">
                        Grazie per il tuo interesse in <span class="font-bold text-rose-600">DanzaFacile</span>
                    </p>
                </div>

                <!-- Message -->
                <div class="bg-gradient-to-br from-rose-50 to-purple-50 rounded-2xl p-6 space-y-4">
                    <p class="text-lg text-gray-700 leading-relaxed">
                        <strong class="text-rose-600">Ottima scelta!</strong> Riceverai la demo personalizzata entro <strong>24 ore</strong> direttamente nella tua email.
                    </p>
                    <div class="space-y-2 text-left">
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700"><strong>Setup gratuito</strong> incluso (valore €147)</span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700"><strong>Primo mese gratis</strong> per provare senza impegno</span>
                        </div>
                        <div class="flex items-start space-x-3">
                            <svg class="w-6 h-6 text-green-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span class="text-gray-700"><strong>Garanzia 60 giorni</strong> soddisfatto o rimborsato</span>
                        </div>
                    </div>
                </div>

                <!-- Next Steps -->
                <div class="pt-6 space-y-4">
                    <h3 class="text-xl font-bold text-gray-900">📧 Controlla la tua email!</h3>
                    <p class="text-gray-600">
                        Ti abbiamo inviato una conferma. Se non la vedi, controlla la cartella spam.
                    </p>
                </div>

                <!-- CTA Buttons -->
                <div class="pt-6 space-y-3">
                    <a href="/" class="block w-full bg-gradient-to-r from-rose-500 to-purple-600 text-white px-8 py-4 rounded-xl text-lg font-bold hover:from-rose-600 hover:to-purple-700 transition-all duration-200 transform hover:scale-105 shadow-lg">
                        ← Torna alla Homepage
                    </a>
                    <p class="text-sm text-gray-500">
                        Hai domande? Chiamaci al <a href="tel:+393409295364" class="text-rose-600 font-semibold hover:underline">+39 340 929 5364</a>
                    </p>
                </div>
            </div>

            <!-- Social Proof -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-600">
                    ⚡ <span class="font-bold text-rose-600" id="spots-left">12</span> posti rimasti per l'offerta con setup gratuito
                </p>
            </div>
        </div>
    </div>

    <script>
        // Simulazione posti rimanenti
        const spotsLeftEl = document.getElementById('spots-left');
        let spotsLeft = localStorage.getItem('spotsLeft') || 17;
        if (Math.random() > 0.5) {
            spotsLeft = Math.max(5, parseInt(spotsLeft) - 1);
            localStorage.setItem('spotsLeft', spotsLeft);
        }
        spotsLeftEl.textContent = spotsLeft;

        // TODO: Google Ads Conversion Tracking
        // Quando configuri Google Ads, inserisci qui il codice di conversione:
        /*
        gtag('event', 'conversion', {
            'send_to': 'AW-XXXXXXXXX/XXXXXXXXXXXXX',
            'value': 147.0,
            'currency': 'EUR',
            'transaction_id': ''
        });
        */
    </script>

</body>
</html>
