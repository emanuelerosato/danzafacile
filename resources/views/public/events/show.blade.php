<!DOCTYPE html>
<html lang="it" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- SEO Meta Tags -->
    <title>{{ $event->name }} - Iscriviti Ora</title>
    <meta name="description" content="{{ Str::limit($event->landing_description ?? $event->description, 155) }}">

    <!-- Open Graph -->
    <meta property="og:title" content="{{ $event->name }}">
    <meta property="og:description" content="{{ Str::limit($event->landing_description ?? $event->description, 155) }}">
    <meta property="og:image" content="{{ $event->image_url ?? asset('images/og-image.jpg') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:type" content="event">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $event->name }}">
    <meta name="twitter:description" content="{{ Str::limit($event->landing_description ?? $event->description, 155) }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes pulse-slow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .animate-pulse-slow {
            animation: pulse-slow 3s ease-in-out infinite;
        }

        .gradient-text {
            background: linear-gradient(135deg, #f43f5e 0%, #9333ea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>
</head>

<body class="font-sans antialiased bg-gray-50" x-data="eventLanding()">

    <!-- Hero Section -->
    <section class="relative min-h-screen flex items-center justify-center overflow-hidden">
        <!-- Background Image with Overlay -->
        <div class="absolute inset-0">
            <img src="{{ $event->image_url ?? asset('images/event-hero.jpg') }}"
                 alt="{{ $event->name }}"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-br from-rose-900/90 via-purple-900/85 to-indigo-900/90"></div>

            <!-- Animated Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute top-20 left-20 w-72 h-72 bg-rose-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse-slow"></div>
                <div class="absolute top-40 right-20 w-72 h-72 bg-purple-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse-slow" style="animation-delay: 2s;"></div>
                <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-pink-400 rounded-full mix-blend-multiply filter blur-3xl animate-pulse-slow" style="animation-delay: 4s;"></div>
            </div>
        </div>

        <!-- Content -->
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
            <div class="animate-fade-in-up">
                <!-- Event Type Badge -->
                <div class="inline-flex items-center px-4 py-2 bg-white/10 backdrop-blur-md rounded-full text-white/90 text-sm font-medium mb-6 border border-white/20">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                    </svg>
                    {{ $event->type ?? 'Evento Speciale' }}
                </div>

                <!-- Title -->
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-extrabold text-white mb-6 leading-tight">
                    {{ $event->name }}
                </h1>

                <!-- Subtitle -->
                @if($event->short_description)
                <p class="text-xl sm:text-2xl text-white/90 max-w-3xl mx-auto mb-8 leading-relaxed">
                    {{ $event->short_description }}
                </p>
                @endif

                <!-- Event Meta Info -->
                <div class="flex flex-wrap items-center justify-center gap-6 mb-10 text-white/90">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="font-medium">{{ $event->start_date->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">{{ $event->start_date->format('H:i') }}</span>
                    </div>
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span class="font-medium">{{ $event->location }}</span>
                    </div>
                </div>

                <!-- Countdown Timer -->
                <div class="mb-10">
                    <div class="inline-block bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
                        <p class="text-white/80 text-sm font-medium mb-3">L'evento inizia tra</p>
                        <div class="flex gap-4">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white" x-text="countdown.days">00</div>
                                <div class="text-white/70 text-xs uppercase mt-1">Giorni</div>
                            </div>
                            <div class="text-white text-3xl font-bold">:</div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white" x-text="countdown.hours">00</div>
                                <div class="text-white/70 text-xs uppercase mt-1">Ore</div>
                            </div>
                            <div class="text-white text-3xl font-bold">:</div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white" x-text="countdown.minutes">00</div>
                                <div class="text-white/70 text-xs uppercase mt-1">Minuti</div>
                            </div>
                            <div class="text-white text-3xl font-bold">:</div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-white" x-text="countdown.seconds">00</div>
                                <div class="text-white/70 text-xs uppercase mt-1">Secondi</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    @if($spotsRemaining > 0 && (!$event->registration_deadline || now()->isBefore($event->registration_deadline)))
                        <button @click="scrollToForm"
                                class="inline-flex items-center px-8 py-4 bg-white text-rose-600 text-lg font-bold rounded-full hover:bg-rose-50 transform hover:scale-105 transition-all duration-200 shadow-2xl">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Iscriviti Ora
                        </button>
                    @endif

                    <button @click="scrollToDetails"
                            class="inline-flex items-center px-8 py-4 bg-white/10 backdrop-blur-md text-white text-lg font-semibold rounded-full border-2 border-white/30 hover:bg-white/20 transition-all duration-200">
                        Scopri di più
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                </div>

                <!-- Urgency Badge -->
                @if($event->max_participants && $spotsRemaining <= 10 && $spotsRemaining > 0)
                <div class="mt-8 animate-pulse">
                    <span class="inline-flex items-center px-4 py-2 bg-rose-500 text-white text-sm font-bold rounded-full">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        Solo {{ $spotsRemaining }} posti rimasti!
                    </span>
                </div>
                @endif
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <svg class="w-8 h-8 text-white/60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </div>
    </section>

    <!-- Alert Messages -->
    @if(session('success') || session('error'))
    <div class="relative z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-lg shadow-lg animate-fade-in-up">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-green-500 mr-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold text-green-900">Iscrizione completata!</p>
                        <p class="text-green-800 mt-1">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg shadow-lg animate-fade-in-up">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-500 mr-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-bold text-red-900">Errore</p>
                        <p class="text-red-800 mt-1">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="py-16 bg-gradient-to-br from-gray-50 via-white to-gray-50" id="details-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">

                <!-- Left Column: Details -->
                <div class="lg:col-span-2 space-y-12">

                    <!-- About Section -->
                    <section class="bg-white rounded-2xl shadow-xl p-8 transform hover:scale-[1.01] transition-transform duration-200">
                        <h2 class="text-3xl font-bold mb-6 gradient-text">Cos'è questo evento?</h2>
                        <div class="prose prose-lg prose-rose max-w-none text-gray-700 leading-relaxed">
                            {!! nl2br(e($event->landing_description ?? $event->description)) !!}
                        </div>
                    </section>

                    <!-- Key Highlights -->
                    @if($event->requirements && count($event->requirements) > 0)
                    <section class="bg-gradient-to-br from-rose-50 to-purple-50 rounded-2xl shadow-xl p-8">
                        <h2 class="text-3xl font-bold mb-6 gradient-text">Cosa Imparerai</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($event->requirements as $requirement)
                            <div class="flex items-start bg-white rounded-lg p-4 shadow-sm">
                                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-rose-500 to-purple-600 rounded-lg flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-gray-700 font-medium">{{ $requirement }}</p>
                            </div>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    <!-- Event Details Cards -->
                    <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Date & Time -->
                        <div class="bg-white rounded-2xl shadow-xl p-6 hover:shadow-2xl transition-shadow duration-200">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-rose-400 to-rose-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Data & Ora</h3>
                                    <p class="text-xl font-bold text-gray-900">{{ $event->start_date->format('d M Y') }}</p>
                                    <p class="text-lg text-gray-600">{{ $event->start_date->format('H:i') }}
                                        @if($event->end_date) - {{ $event->end_date->format('H:i') }}@endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="bg-white rounded-2xl shadow-xl p-6 hover:shadow-2xl transition-shadow duration-200">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-purple-400 to-purple-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Dove</h3>
                                    <p class="text-lg font-bold text-gray-900">{{ $event->location }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Duration -->
                        @if($event->duration_minutes)
                        <div class="bg-white rounded-2xl shadow-xl p-6 hover:shadow-2xl transition-shadow duration-200">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br from-blue-400 to-blue-600 rounded-xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Durata</h3>
                                    <p class="text-xl font-bold text-gray-900">{{ $event->duration_minutes }} min</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Available Spots -->
                        @if($event->max_participants)
                        <div class="bg-white rounded-2xl shadow-xl p-6 hover:shadow-2xl transition-shadow duration-200">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-14 h-14 bg-gradient-to-br {{ $spotsRemaining > 0 ? 'from-green-400 to-green-600' : 'from-red-400 to-red-600' }} rounded-xl flex items-center justify-center">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div class="ml-4">
                                    <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-1">Posti</h3>
                                    <p class="text-xl font-bold {{ $spotsRemaining > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $spotsRemaining }} / {{ $event->max_participants }}
                                    </p>
                                    <p class="text-sm text-gray-500">Disponibili</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </section>

                </div>

                <!-- Right Column: Registration Form (Sticky) -->
                <div class="lg:col-span-1">
                    <div id="registration-form-section" class="bg-white rounded-2xl shadow-2xl p-8 lg:sticky lg:top-6">

                        <!-- Price Badge -->
                        <div class="mb-8 text-center">
                            @if($event->requiresPayment())
                                <div class="inline-block">
                                    <div class="text-gray-600 text-sm font-medium mb-2">A partire da</div>
                                    <div class="text-5xl font-extrabold gradient-text">
                                        {{ $event->getFormattedPrice('guest') }}
                                    </div>
                                    @if($event->student_price && $event->student_price !== $event->guest_price)
                                    <div class="mt-2 text-sm text-gray-600">
                                        Studenti: <span class="font-bold">{{ $event->getFormattedPrice('student') }}</span>
                                    </div>
                                    @endif
                                </div>
                            @else
                                <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-400 to-green-600 text-white text-2xl font-bold rounded-full shadow-lg">
                                    <svg class="w-6 h-6 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Gratuito
                                </div>
                            @endif
                        </div>

                        <div class="border-t border-gray-200 pt-8">

                            @if($spotsRemaining <= 0)
                                <!-- Sold Out -->
                                <div class="text-center py-12">
                                    <div class="w-24 h-24 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-12 h-12 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Posti Esauriti</h3>
                                    <p class="text-gray-600">Ci dispiace, tutti i posti per questo evento sono stati prenotati.</p>
                                </div>

                            @elseif($event->registration_deadline && now()->isAfter($event->registration_deadline))
                                <!-- Registration Closed -->
                                <div class="text-center py-12">
                                    <div class="w-24 h-24 mx-auto bg-yellow-100 rounded-full flex items-center justify-center mb-4">
                                        <svg class="w-12 h-12 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Iscrizioni Chiuse</h3>
                                    <p class="text-gray-600">Il termine per le iscrizioni è scaduto.</p>
                                </div>

                            @else
                                <!-- Registration Form -->
                                <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Iscriviti Ora</h3>

                                <form id="registration-form" method="POST" action="{{ route('public.events.register', $event->slug) }}" x-data="{ submitting: false }" class="space-y-5">
                                    @csrf

                                    <!-- Full Name -->
                                    <div>
                                        <label for="full_name" class="block text-sm font-bold text-gray-700 mb-2">
                                            Nome Completo <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text"
                                               name="full_name"
                                               id="full_name"
                                               required
                                               value="{{ old('full_name') }}"
                                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-200 @error('full_name') border-red-500 @enderror"
                                               placeholder="Mario Rossi">
                                        @error('full_name')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Email -->
                                    <div>
                                        <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                                            Email <span class="text-red-500">*</span>
                                        </label>
                                        <input type="email"
                                               name="email"
                                               id="email"
                                               required
                                               value="{{ old('email') }}"
                                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-200 @error('email') border-red-500 @enderror"
                                               placeholder="mario@example.com">
                                        @error('email')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Phone -->
                                    <div>
                                        <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">
                                            Telefono <span class="text-red-500">*</span>
                                        </label>
                                        <input type="tel"
                                               name="phone"
                                               id="phone"
                                               required
                                               value="{{ old('phone') }}"
                                               class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:ring-4 focus:ring-rose-500/20 focus:border-rose-500 transition-all duration-200 @error('phone') border-red-500 @enderror"
                                               placeholder="+39 333 1234567">
                                        @error('phone')
                                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- GDPR Consents -->
                                    <div class="pt-4 border-t-2 border-gray-100 space-y-3">
                                        <p class="text-sm font-bold text-gray-700 mb-3">Consensi Privacy</p>

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
                                            class="w-full py-4 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-lg font-bold rounded-xl hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-4 focus:ring-rose-500/50 transform hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center"
                                            @click="submitting = true">
                                        <svg x-show="submitting" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        <svg x-show="!submitting" class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        <span x-show="!submitting">Conferma Iscrizione</span>
                                        <span x-show="submitting">Invio in corso...</span>
                                    </button>
                                </form>

                                <!-- Security Badge -->
                                <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <div>
                                            <p class="text-sm font-semibold text-blue-900">Dati Sicuri</p>
                                            <p class="text-xs text-blue-700 mt-1">Riceverai un'email con il link di accesso sicuro.</p>
                                        </div>
                                    </div>
                                </div>

                            @endif

                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-bold mb-4">{{ config('app.name') }}</h3>
                    <p class="text-gray-400 text-sm">La tua scuola di danza di fiducia</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Contatti</h3>
                    <p class="text-gray-400 text-sm">{{ $event->location }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-4">Condividi</h3>
                    <div class="flex space-x-4">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                           target="_blank"
                           class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($event->name . ' - ' . url()->current()) }}"
                           target="_blank"
                           class="w-10 h-10 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
                <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Tutti i diritti riservati.</p>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://www.google.com/recaptcha/api.js?render={{ config('services.recaptcha.site_key') }}"></script>
    <script nonce="@cspNonce">
        function eventLanding() {
            return {
                countdown: {
                    days: '00',
                    hours: '00',
                    minutes: '00',
                    seconds: '00'
                },

                init() {
                    this.updateCountdown();
                    setInterval(() => this.updateCountdown(), 1000);
                },

                updateCountdown() {
                    const eventDate = new Date('{{ $event->start_date->toIso8601String() }}');
                    const now = new Date();
                    const diff = eventDate - now;

                    if (diff > 0) {
                        this.countdown.days = String(Math.floor(diff / (1000 * 60 * 60 * 24))).padStart(2, '0');
                        this.countdown.hours = String(Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                        this.countdown.minutes = String(Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                        this.countdown.seconds = String(Math.floor((diff % (1000 * 60)) / 1000)).padStart(2, '0');
                    } else {
                        this.countdown = { days: '00', hours: '00', minutes: '00', seconds: '00' };
                    }
                },

                scrollToForm() {
                    document.getElementById('registration-form-section').scrollIntoView({
                        behavior: 'smooth',
                        block: 'center'
                    });
                },

                scrollToDetails() {
                    document.getElementById('details-section').scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        }

        // reCAPTCHA Form Submission
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

</body>
</html>
