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
        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        .animate-slide-up {
            animation: slideInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        .bg-shimmer {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            background-size: 1000px 100%;
            animation: shimmer 3s infinite;
        }

        /* Better gradient text with fallback */
        .gradient-text {
            background: linear-gradient(135deg, #f43f5e 0%, #a855f7 50%, #6366f1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Smooth shadow transitions */
        .shadow-glow {
            box-shadow: 0 10px 40px -10px rgba(244, 63, 94, 0.3);
        }

        .shadow-glow:hover {
            box-shadow: 0 20px 60px -10px rgba(244, 63, 94, 0.4);
        }

        /* Better focus states */
        input:focus, textarea:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(244, 63, 94, 0.1);
        }
    </style>
</head>

<body class="font-sans antialiased bg-white" x-data="eventLanding()">

    <!-- Hero Section - Optimized Height -->
    <section class="relative bg-gradient-to-br from-rose-50 via-purple-50 to-indigo-50 overflow-hidden">

        <!-- Background Decorations - Subtle -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute -top-40 -right-40 w-96 h-96 bg-rose-200/30 rounded-full blur-3xl"></div>
            <div class="absolute top-1/2 -left-40 w-96 h-96 bg-purple-200/30 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-40 right-1/3 w-96 h-96 bg-indigo-200/30 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 lg:py-24">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">

                <!-- Left: Content -->
                <div class="text-center lg:text-left space-y-8 animate-slide-up">

                    <!-- Badge -->
                    <div class="inline-flex items-center px-4 py-2 bg-white rounded-full shadow-sm border border-gray-200">
                        <svg class="w-4 h-4 mr-2 text-rose-500" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/>
                        </svg>
                        <span class="text-sm font-semibold text-gray-700">{{ $event->type ?? 'Evento Speciale' }}</span>
                    </div>

                    <!-- Title -->
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-gray-900 leading-tight tracking-tight">
                        {{ $event->name }}
                    </h1>

                    <!-- Subtitle -->
                    @if($event->short_description)
                    <p class="text-lg sm:text-xl text-gray-600 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        {{ $event->short_description }}
                    </p>
                    @endif

                    <!-- Meta Info -->
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-6 text-gray-700">
                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-500 font-medium">Data</div>
                                <div class="text-sm font-bold">{{ $event->start_date->format('d M Y') }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-500 font-medium">Orario</div>
                                <div class="text-sm font-bold">{{ $event->start_date->format('H:i') }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <div class="w-10 h-10 bg-indigo-100 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                </svg>
                            </div>
                            <div class="text-left">
                                <div class="text-xs text-gray-500 font-medium">Luogo</div>
                                <div class="text-sm font-bold">{{ Str::limit($event->location, 20) }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4 pt-4">
                        @if($spotsRemaining > 0 && (!$event->registration_deadline || now()->isBefore($event->registration_deadline)))
                            <button @click="scrollToForm"
                                    class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-gradient-to-r from-rose-500 to-rose-600 text-white text-lg font-bold rounded-xl hover:from-rose-600 hover:to-rose-700 transform hover:scale-105 active:scale-95 transition-all duration-200 shadow-glow">
                                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Iscriviti Ora
                            </button>
                        @endif

                        <button @click="scrollToDetails"
                                class="w-full sm:w-auto inline-flex items-center justify-center px-8 py-4 bg-white text-gray-700 text-lg font-semibold rounded-xl border-2 border-gray-200 hover:border-gray-300 hover:shadow-md transition-all duration-200">
                            Scopri di più
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Urgency Badge -->
                    @if($event->max_participants && $spotsRemaining <= 10 && $spotsRemaining > 0)
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-amber-50 border border-amber-200 rounded-lg">
                        <svg class="w-5 h-5 text-amber-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-sm font-bold text-amber-900">Solo {{ $spotsRemaining }} posti disponibili!</span>
                    </div>
                    @endif

                </div>

                <!-- Right: Countdown Card -->
                <div class="lg:order-last animate-slide-up" style="animation-delay: 0.2s;">
                    <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
                        <div class="text-center mb-6">
                            <p class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-2">L'evento inizia tra</p>
                        </div>

                        <div class="grid grid-cols-4 gap-4">
                            <div class="text-center">
                                <div class="bg-gradient-to-br from-rose-500 to-rose-600 rounded-xl p-4 mb-2">
                                    <div class="text-3xl font-extrabold text-white" x-text="countdown.days">00</div>
                                </div>
                                <div class="text-xs font-semibold text-gray-600 uppercase">Giorni</div>
                            </div>
                            <div class="text-center">
                                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl p-4 mb-2">
                                    <div class="text-3xl font-extrabold text-white" x-text="countdown.hours">00</div>
                                </div>
                                <div class="text-xs font-semibold text-gray-600 uppercase">Ore</div>
                            </div>
                            <div class="text-center">
                                <div class="bg-gradient-to-br from-indigo-500 to-indigo-600 rounded-xl p-4 mb-2">
                                    <div class="text-3xl font-extrabold text-white" x-text="countdown.minutes">00</div>
                                </div>
                                <div class="text-xs font-semibold text-gray-600 uppercase">Minuti</div>
                            </div>
                            <div class="text-center">
                                <div class="bg-gradient-to-br from-pink-500 to-pink-600 rounded-xl p-4 mb-2">
                                    <div class="text-3xl font-extrabold text-white" x-text="countdown.seconds">00</div>
                                </div>
                                <div class="text-xs font-semibold text-gray-600 uppercase">Secondi</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Alert Messages -->
    @if(session('success') || session('error'))
    <div class="relative z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-xl shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-bold text-green-900">Iscrizione completata!</p>
                        <p class="text-sm text-green-800 mt-1">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 p-6 rounded-xl shadow-lg">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="w-6 h-6 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-bold text-red-900">Errore</p>
                        <p class="text-sm text-red-800 mt-1">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Main Content -->
    <div class="py-20 bg-gray-50" id="details-section">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-12">

                <!-- Left Column: Details -->
                <div class="lg:col-span-2 space-y-8">

                    <!-- About Section -->
                    <section class="bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Cos'è questo evento?</h2>
                        <div class="prose prose-lg prose-gray max-w-none leading-relaxed text-gray-700">
                            {!! nl2br(e($event->landing_description ?? $event->description)) !!}
                        </div>
                    </section>

                    <!-- Key Highlights -->
                    @if($event->requirements && count($event->requirements) > 0)
                    <section class="bg-gradient-to-br from-rose-50 to-purple-50 rounded-2xl border border-rose-100 p-8">
                        <h2 class="text-3xl font-bold text-gray-900 mb-6">Cosa Imparerai</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($event->requirements as $requirement)
                            <div class="flex items-start gap-3 bg-white rounded-xl p-4 shadow-sm">
                                <div class="flex-shrink-0 w-8 h-8 bg-gradient-to-br from-rose-500 to-purple-600 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-700 leading-relaxed">{{ $requirement }}</p>
                            </div>
                            @endforeach
                        </div>
                    </section>
                    @endif

                    <!-- Event Details Cards -->
                    <section class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                        <!-- Date & Time -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-rose-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Data & Ora</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $event->start_date->format('d M Y') }}</p>
                                    <p class="text-sm text-gray-600">{{ $event->start_date->format('H:i') }}
                                        @if($event->end_date) - {{ $event->end_date->format('H:i') }}@endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Location -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Dove</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $event->location }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Duration -->
                        @if($event->duration_minutes)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Durata</p>
                                    <p class="text-lg font-bold text-gray-900">{{ $event->duration_minutes }} min</p>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Available Spots -->
                        @if($event->max_participants)
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow">
                            <div class="flex items-start gap-4">
                                <div class="flex-shrink-0 w-12 h-12 {{ $spotsRemaining > 0 ? 'bg-green-100' : 'bg-red-100' }} rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 {{ $spotsRemaining > 0 ? 'text-green-600' : 'text-red-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Posti Disponibili</p>
                                    <p class="text-lg font-bold {{ $spotsRemaining > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $spotsRemaining }} / {{ $event->max_participants }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif

                    </section>

                </div>

                <!-- Right Column: Registration Form (Sticky) -->
                <div class="lg:col-span-1">
                    <div id="registration-form-section" class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 lg:sticky lg:top-6">

                        <!-- Price Badge -->
                        <div class="text-center mb-8 pb-8 border-b border-gray-200">
                            @if($event->requiresPayment())
                                <p class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">Quota iscrizione</p>
                                <div class="text-5xl font-extrabold gradient-text mb-3">
                                    {{ $event->getFormattedPrice('guest') }}
                                </div>
                                @if($event->student_price && $event->student_price !== $event->guest_price)
                                <p class="text-sm text-gray-600">
                                    Studenti: <span class="font-bold text-purple-600">{{ $event->getFormattedPrice('student') }}</span>
                                </p>
                                @endif
                            @else
                                <div class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-green-500 to-emerald-600 text-white text-xl font-bold rounded-full shadow-lg">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    Evento Gratuito
                                </div>
                            @endif
                        </div>

                        @if($spotsRemaining <= 0)
                            <!-- Sold Out -->
                            <div class="text-center py-12">
                                <div class="w-20 h-20 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Posti Esauriti</h3>
                                <p class="text-sm text-gray-600">Tutti i posti sono stati prenotati.</p>
                            </div>

                        @elseif($event->registration_deadline && now()->isAfter($event->registration_deadline))
                            <!-- Registration Closed -->
                            <div class="text-center py-12">
                                <div class="w-20 h-20 mx-auto bg-amber-100 rounded-full flex items-center justify-center mb-4">
                                    <svg class="w-10 h-10 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">Iscrizioni Chiuse</h3>
                                <p class="text-sm text-gray-600">Il termine per le iscrizioni è scaduto.</p>
                            </div>

                        @else
                            <!-- Registration Form -->
                            <h3 class="text-xl font-bold text-gray-900 mb-6">Compila il modulo</h3>

                            <form id="registration-form" method="POST" action="{{ route('public.events.register', $event->slug) }}" x-data="{ submitting: false }" class="space-y-5">
                                @csrf

                                <!-- Full Name -->
                                <div>
                                    <label for="full_name" class="block text-sm font-bold text-gray-700 mb-2">
                                        Nome Completo <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="text"
                                           name="full_name"
                                           id="full_name"
                                           required
                                           value="{{ old('full_name') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:border-rose-500 transition-all @error('full_name') border-red-500 @enderror"
                                           placeholder="Mario Rossi">
                                    @error('full_name')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Email -->
                                <div>
                                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                                        Email <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="email"
                                           name="email"
                                           id="email"
                                           required
                                           value="{{ old('email') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:border-rose-500 transition-all @error('email') border-red-500 @enderror"
                                           placeholder="mario@example.com">
                                    @error('email')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Phone -->
                                <div>
                                    <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">
                                        Telefono <span class="text-rose-500">*</span>
                                    </label>
                                    <input type="tel"
                                           name="phone"
                                           id="phone"
                                           required
                                           value="{{ old('phone') }}"
                                           class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:border-rose-500 transition-all @error('phone') border-red-500 @enderror"
                                           placeholder="+39 333 1234567">
                                    @error('phone')
                                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- GDPR Consents -->
                                <div class="pt-5 border-t border-gray-200 space-y-3">
                                    <p class="text-sm font-bold text-gray-700 mb-3">Consensi Privacy</p>

                                    <x-gdpr-consent-checkbox
                                        type="privacy"
                                        :required="true"
                                        :checked="old('gdpr_privacy', false)"
                                        :error="$errors->first('gdpr_privacy')"
                                    />

                                    <x-gdpr-consent-checkbox
                                        type="marketing"
                                        :required="false"
                                        :checked="old('gdpr_marketing', false)"
                                    />

                                    <x-gdpr-consent-checkbox
                                        type="newsletter"
                                        :required="false"
                                        :checked="old('gdpr_newsletter', false)"
                                    />
                                </div>

                                <!-- reCAPTCHA -->
                                <input type="hidden" name="g-recaptcha-response" id="g-recaptcha-response">

                                <!-- Submit Button -->
                                <button type="submit"
                                        x-bind:disabled="submitting"
                                        class="w-full py-4 bg-gradient-to-r from-rose-500 to-rose-600 text-white text-lg font-bold rounded-xl hover:from-rose-600 hover:to-rose-700 transform hover:scale-[1.02] active:scale-[0.98] transition-all shadow-glow disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none flex items-center justify-center gap-2"
                                        @click="submitting = true">
                                    <svg x-show="submitting" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span x-show="!submitting">Conferma Iscrizione</span>
                                    <span x-show="submitting">Invio in corso...</span>
                                </button>
                            </form>

                            <!-- Security Note -->
                            <div class="mt-6 p-4 bg-blue-50 border border-blue-100 rounded-xl">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <div>
                                        <p class="text-sm font-semibold text-blue-900">Dati Protetti</p>
                                        <p class="text-xs text-blue-700 mt-1">Riceverai un'email con link di accesso sicuro.</p>
                                    </div>
                                </div>
                            </div>

                        @endif

                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                <div>
                    <h3 class="text-lg font-bold mb-3">{{ config('app.name') }}</h3>
                    <p class="text-gray-400 text-sm">La tua scuola di danza di fiducia</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-3">Contatti</h3>
                    <p class="text-gray-400 text-sm">{{ $event->location }}</p>
                </div>
                <div>
                    <h3 class="text-lg font-bold mb-3">Condividi</h3>
                    <div class="flex gap-3">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}"
                           target="_blank"
                           class="w-10 h-10 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($event->name . ' - ' . url()->current()) }}"
                           target="_blank"
                           class="w-10 h-10 bg-gray-800 hover:bg-gray-700 rounded-lg flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 text-center text-sm text-gray-400">
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
        document.getElementById('registration-form')?.addEventListener('submit', function(e) {
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
