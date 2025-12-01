<x-guest-layout>
    <!-- Event Hero Section -->
    <div class="relative bg-gradient-to-r from-rose-500 to-purple-600 overflow-hidden">
        <div class="absolute inset-0 opacity-20">
            <img src="{{ $event->image_url ?? asset('images/event-placeholder.jpg') }}"
                 alt="{{ $event->name }}"
                 class="w-full h-full object-cover">
        </div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 md:py-24">
            <div class="text-center">
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                    {{ $event->name }}
                </h1>
                @if($event->short_description)
                    <p class="text-xl text-white/90 max-w-3xl mx-auto">
                        {{ $event->short_description }}
                    </p>
                @endif
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-200 text-green-800 px-6 py-4 rounded-lg flex items-start">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-200 text-red-800 px-6 py-4 rounded-lg flex items-start">
                    <svg class="w-5 h-5 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                <!-- Left Column: Event Details -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Event Info Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-6">Dettagli Evento</h2>

                        <div class="space-y-4">
                            <!-- Date & Time -->
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-rose-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Data e Ora</p>
                                    <p class="text-lg font-bold text-gray-900">
                                        {{ $event->start_date->format('d/m/Y H:i') }}
                                    </p>
                                    @if($event->end_date)
                                        <p class="text-sm text-gray-600">
                                            Fine: {{ $event->end_date->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="flex items-start">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-600">Luogo</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $event->location }}</p>
                                </div>
                            </div>

                            <!-- Duration -->
                            @if($event->duration_minutes)
                                <div class="flex items-start">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600">Durata</p>
                                        <p class="text-lg font-bold text-gray-900">{{ $event->duration_minutes }} minuti</p>
                                    </div>
                                </div>
                            @endif

                            <!-- Available Spots -->
                            @if($event->max_participants)
                                <div class="flex items-start">
                                    <div class="w-12 h-12 {{ $spotsRemaining > 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-6 h-6 {{ $spotsRemaining > 0 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-600">Posti Disponibili</p>
                                        <p class="text-lg font-bold {{ $spotsRemaining > 0 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $spotsRemaining }} / {{ $event->max_participants }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Description Card -->
                    @if($event->landing_description)
                        <div class="bg-white rounded-lg shadow p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4">Descrizione</h2>
                            <div class="prose prose-rose max-w-none text-gray-700">
                                {!! nl2br(e($event->landing_description)) !!}
                            </div>
                        </div>
                    @endif

                </div>

                <!-- Right Column: Registration Form -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow p-6 sticky top-6">

                        <!-- Pricing Section -->
                        <div class="mb-6 pb-6 border-b border-gray-200">
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Prezzo</h3>

                            @if($event->requiresPayment())
                                <div class="space-y-3">
                                    @if($event->guest_price)
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Ospiti</span>
                                            <span class="text-2xl font-bold text-rose-600">
                                                {{ $event->getFormattedPrice('guest') }}
                                            </span>
                                        </div>
                                    @endif

                                    @if($event->student_price && $event->student_price !== $event->guest_price)
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-600">Studenti</span>
                                            <span class="text-xl font-bold text-purple-600">
                                                {{ $event->getFormattedPrice('student') }}
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-lg font-bold bg-green-100 text-green-800 border-2 border-green-200">
                                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Evento Gratuito
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Registration Status Check -->
                        @if($spotsRemaining <= 0)
                            <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
                                <svg class="w-12 h-12 mx-auto text-red-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-red-800 font-bold">Posti Esauriti</p>
                                <p class="text-sm text-red-600 mt-1">Non è più possibile iscriversi a questo evento.</p>
                            </div>
                        @elseif($event->registration_deadline && now()->isAfter($event->registration_deadline))
                            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                <svg class="w-12 h-12 mx-auto text-yellow-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <p class="text-yellow-800 font-bold">Iscrizioni Chiuse</p>
                                <p class="text-sm text-yellow-600 mt-1">Il termine per le iscrizioni è scaduto.</p>
                            </div>
                        @else
                            <!-- Registration Form -->
                            <h3 class="text-lg font-bold text-gray-900 mb-4">Iscriviti all'evento</h3>

                            <form id="registration-form" method="POST" action="{{ route('public.events.register', $event->slug) }}" x-data="{ submitting: false }">
                                @csrf

                                <div class="space-y-4">

                                    <!-- Full Name -->
                                    <div>
                                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-2">
                                            Nome Completo <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               name="full_name"
                                               id="full_name"
                                               required
                                               value="{{ old('full_name') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('full_name') border-red-500 @enderror">
                                        @error('full_name')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email"
                                               name="email"
                                               id="email"
                                               required
                                               value="{{ old('email') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('email') border-red-500 @enderror">
                                        @error('email')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Phone -->
                                    <div>
                                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                            Telefono <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel"
                                               name="phone"
                                               id="phone"
                                               required
                                               value="{{ old('phone') }}"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('phone') border-red-500 @enderror">
                                        @error('phone')
                                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- GDPR Consents -->
                                    <div class="pt-4 border-t border-gray-200">
                                        <p class="text-sm font-medium text-gray-700 mb-3">Consensi Privacy</p>

                                        <!-- Privacy Policy (Required) -->
                                        <x-gdpr-consent-checkbox
                                            type="privacy"
                                            :required="true"
                                            :checked="old('gdpr_privacy', false)"
                                            :error="$errors->first('gdpr_privacy')"
                                        />

                                        <!-- Marketing (Optional) -->
                                        <x-gdpr-consent-checkbox
                                            type="marketing"
                                            :required="false"
                                            :checked="old('gdpr_marketing', false)"
                                        />

                                        <!-- Newsletter (Optional) -->
                                        <x-gdpr-consent-checkbox
                                            type="newsletter"
                                            :required="false"
                                            :checked="old('gdpr_newsletter', false)"
                                        />
                                    </div>

                                    <!-- reCAPTCHA Hidden Field -->
                                    <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                                    <!-- Submit Button -->
                                    <button type="submit"
                                            x-bind:disabled="submitting"
                                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-base font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                                            @click="submitting = true">
                                        <svg x-show="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <span x-show="!submitting">Iscriviti Ora</span>
                                        <span x-show="submitting">Invio in corso...</span>
                                    </button>

                                </div>
                            </form>

                            <!-- Info Note -->
                            <div class="mt-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs text-blue-800">
                                    <svg class="w-4 h-4 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                    </svg>
                                    Riceverai una email di conferma con un link per accedere alla tua area riservata.
                                </p>
                            </div>

                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script nonce="@cspNonce">
        document.getElementById('registration-form').addEventListener('submit', function(e) {
            e.preventDefault();

            grecaptcha.ready(function() {
                grecaptcha.execute('{{ config('services.recaptcha.site_key') }}', {action: 'event_registration'}).then(function(token) {
                    document.getElementById('g-recaptcha-response').value = token;
                    document.getElementById('registration-form').submit();
                });
            });
        });
    </script>
    @endpush

</x-guest-layout>
