<x-guest-layout>
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-rose-500 to-purple-600 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                Eventi Pubblici
            </h1>
            <p class="text-xl text-white/90 max-w-2xl mx-auto">
                Scopri i nostri eventi aperti a tutti. Partecipa alle masterclass, workshop e spettacoli speciali.
            </p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if($events->isEmpty())
                <!-- Empty State -->
                <div class="bg-white rounded-lg shadow p-12 text-center">
                    <svg class="w-24 h-24 mx-auto text-gray-400 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Nessun evento disponibile</h3>
                    <p class="text-gray-600">Al momento non ci sono eventi pubblici in programma. Torna presto!</p>
                </div>
            @else
                <!-- Events Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($events as $event)
                        <div class="bg-white rounded-lg shadow overflow-hidden hover:shadow-xl transition-shadow duration-200">
                            <!-- Event Image -->
                            <div class="relative h-48 overflow-hidden">
                                <img src="{{ $event->image_url ?? asset('images/event-placeholder.jpg') }}"
                                     alt="{{ $event->name }}"
                                     class="w-full h-full object-cover">

                                @if($event->isPublic())
                                    <span class="absolute top-4 right-4 inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 border border-green-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        Pubblico
                                    </span>
                                @endif
                            </div>

                            <!-- Event Content -->
                            <div class="p-6">
                                <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">
                                    {{ $event->name }}
                                </h3>

                                <!-- Event Details -->
                                <div class="space-y-2 mb-4">
                                    <!-- Date & Time -->
                                    <div class="flex items-start text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <span>{{ $event->start_date->format('d/m/Y H:i') }}</span>
                                    </div>

                                    <!-- Location -->
                                    <div class="flex items-start text-sm text-gray-600">
                                        <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="line-clamp-2">{{ $event->location }}</span>
                                    </div>

                                    <!-- Available Spots -->
                                    @if($event->max_participants)
                                        @php
                                            $spotsRemaining = $event->max_participants - $event->confirmed_registrations_count;
                                            $spotsPercentage = ($spotsRemaining / $event->max_participants) * 100;
                                        @endphp
                                        <div class="flex items-center text-sm">
                                            <svg class="w-4 h-4 mr-2 flex-shrink-0 {{ $spotsPercentage > 20 ? 'text-green-600' : 'text-orange-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            <span class="{{ $spotsPercentage > 20 ? 'text-green-600' : 'text-orange-600' }} font-medium">
                                                {{ $spotsRemaining }} posti disponibili
                                            </span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Description Preview -->
                                @if($event->short_description)
                                    <p class="text-sm text-gray-600 mb-4 line-clamp-3">
                                        {{ $event->short_description }}
                                    </p>
                                @endif

                                <!-- Price & CTA -->
                                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                    @if($event->requiresPayment())
                                        <div>
                                            <p class="text-2xl font-bold text-rose-600">
                                                {{ $event->getFormattedPrice('guest') }}
                                            </p>
                                            @if($event->student_price && $event->guest_price !== $event->student_price)
                                                <p class="text-xs text-gray-500 mt-1">
                                                    Studenti: {{ $event->getFormattedPrice('student') }}
                                                </p>
                                            @endif
                                        </div>
                                    @else
                                        <p class="text-2xl font-bold text-green-600">
                                            Gratuito
                                        </p>
                                    @endif

                                    <a href="{{ route('public.events.show', $event->slug) }}"
                                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                                        Scopri di più
                                        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-12">
                    {{ $events->links() }}
                </div>
            @endif
        </div>
    </div>
</x-guest-layout>
