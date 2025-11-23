<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Centro Assistenza
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Trova risposte alle tue domande e impara ad usare la piattaforma
                </p>
            </div>
            <a href="{{ route('student.tickets.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                Contatta Supporto
            </a>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('student.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Centro Assistenza</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                <!-- Welcome Section -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">Benvenuto nel Centro Assistenza</h3>
                        <p class="text-gray-600 max-w-2xl mx-auto">
                            Qui trovi tutte le informazioni di cui hai bisogno per utilizzare al meglio la piattaforma della tua scuola di danza. Esplora le diverse sezioni o cerca una risposta specifica.
                        </p>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="max-w-md mx-auto">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cerca nell'aiuto</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                            <input type="text" id="search" placeholder="Scrivi la tua domanda..."
                                   class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        </div>
                        <p class="text-sm text-gray-500 mt-2">Prova a cercare: "come iscriversi", "pagamenti", "documenti"</p>
                    </div>
                </div>

                <!-- Help Sections Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($helpSections as $section)
                        <a href="{{ route('student.help.section', $section['id']) }}"
                           class="block bg-white rounded-lg shadow hover:shadow-md transition-shadow duration-200 p-6 group">
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-12 h-12 bg-gradient-to-r from-rose-100 to-purple-100 rounded-lg flex items-center justify-center group-hover:from-rose-200 group-hover:to-purple-200 transition-colors duration-200">
                                    @php
                                        $iconPaths = [
                                            'play-circle' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h8m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                            'academic-cap' => 'M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z',
                                            'credit-card' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                                            'document-text' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
                                            'chat-bubble-left-right' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                                            'user-circle' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'
                                        ];
                                    @endphp
                                    <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$section['icon']] ?? $iconPaths['document-text'] }}"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="font-semibold text-gray-900 group-hover:text-rose-600 transition-colors duration-200">
                                        {{ $section['title'] }}
                                    </h3>
                                </div>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">{{ $section['description'] }}</p>
                            <div class="space-y-1">
                                @foreach(array_slice($section['articles'], 0, 3) as $article)
                                    <div class="flex items-center text-sm text-gray-500">
                                        <svg class="w-3 h-3 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                        </svg>
                                        {{ $article }}
                                    </div>
                                @endforeach
                                @if(count($section['articles']) > 3)
                                    <div class="text-sm text-gray-400">
                                        +{{ count($section['articles']) - 3 }} altri articoli
                                    </div>
                                @endif
                            </div>
                        </a>
                    @endforeach
                </div>

                <!-- Frequent Questions -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Domande Frequenti</h3>
                        <p class="text-sm text-gray-600 mt-1">Le risposte alle domande più comuni dei nostri studenti</p>
                    </div>

                    <div class="divide-y divide-gray-200">
                        @foreach($frequentQuestions as $faq)
                            <div class="p-6" x-data="{ open: false }">
                                <button @click="open = !open"
                                        class="flex items-center justify-between w-full text-left">
                                    <h4 class="font-medium text-gray-900">{{ $faq['question'] }}</h4>
                                    <svg class="w-5 h-5 text-gray-500 transform transition-transform duration-200"
                                         :class="{ 'rotate-180': open }"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                <div x-show="open" x-transition class="mt-3">
                                    <p class="text-gray-600">{{ $faq['answer'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Contact Support -->
                <div class="bg-gradient-to-r from-rose-50 to-purple-50 rounded-lg border border-rose-200 p-6">
                    <div class="flex items-center space-x-4">
                        <div class="w-12 h-12 bg-rose-500 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192L5.636 18.364M12 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-semibold text-gray-900">Non hai trovato quello che cercavi?</h3>
                            <p class="text-gray-600 text-sm">Il nostro team di supporto è qui per aiutarti. Invia un messaggio e riceverai una risposta entro 24 ore.</p>
                        </div>
                        <a href="{{ route('student.tickets.create') }}"
                           class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-colors duration-200">
                            Contatta Supporto
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script nonce="@cspNonce">
    // Simple search functionality
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('search');
        const helpSections = document.querySelectorAll('.grid .group');

        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();

            helpSections.forEach(section => {
                const text = section.textContent.toLowerCase();
                if (searchTerm === '' || text.includes(searchTerm)) {
                    section.style.display = 'block';
                } else {
                    section.style.display = 'none';
                }
            });
        });
    });
    </script>
    @endpush
</x-app-layout>