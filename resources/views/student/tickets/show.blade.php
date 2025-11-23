<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $ticket->title }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Messaggio #{{ $ticket->id }} - {{ $ticket->formatted_created_at }}
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('student.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('student.tickets.index') }}" class="text-gray-500 hover:text-gray-700">Messaggi</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">{{ Str::limit($ticket->title, 30) }}</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                <!-- Status and Priority Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div class="flex items-center space-x-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->status_color }}">
                                {{ ucfirst($ticket->status === 'open' ? 'Aperto' : ($ticket->status === 'pending' ? 'In Attesa' : 'Chiuso')) }}
                            </span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->priority_color }}">
                                Priorità {{ ucfirst($ticket->priority === 'low' ? 'Bassa' : ($ticket->priority === 'medium' ? 'Media' : ($ticket->priority === 'high' ? 'Alta' : 'Critica'))) }}
                            </span>
                            @if($ticket->is_overdue)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    In Ritardo
                                </span>
                            @endif
                        </div>
                        <div class="text-sm text-gray-500">
                            <div>Categoria: {{ ucfirst($ticket->category) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Original Message -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold">
                            {{ strtoupper(substr($ticket->user->name, 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $ticket->user->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ $ticket->formatted_created_at }}</p>
                                </div>
                                <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full">Messaggio Originale</span>
                            </div>
                            <div class="prose prose-sm max-w-none">
                                <p class="text-gray-800 whitespace-pre-wrap">{{ $ticket->description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Responses -->
                @if($ticket->responses->count() > 0)
                    <div class="space-y-4">
                        <h2 class="text-xl font-semibold text-gray-900 px-2">Conversazione</h2>
                        @foreach($ticket->responses as $response)
                            <div class="bg-white rounded-lg shadow p-6">
                                <div class="flex items-start space-x-4">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-white font-semibold
                                        {{ $response->user_id === $ticket->user_id ?
                                           'bg-gradient-to-r from-rose-400 to-purple-500' :
                                           'bg-gradient-to-r from-blue-400 to-blue-600' }}">
                                        {{ strtoupper(substr($response->user->name, 0, 1)) }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-3">
                                            <div>
                                                <h4 class="font-semibold text-gray-900">{{ $response->user->name }}</h4>
                                                <p class="text-sm text-gray-600">{{ $response->created_at->format('d/m/Y H:i') }}</p>
                                            </div>
                                            <span class="text-xs px-2 py-1 rounded-full
                                                {{ $response->user_id === $ticket->user_id ?
                                                   'bg-purple-100 text-purple-800' :
                                                   'bg-blue-100 text-blue-800' }}">
                                                {{ $response->user_id === $ticket->user_id ? 'Tu' : 'Staff' }}
                                            </span>
                                        </div>
                                        <div class="prose prose-sm max-w-none">
                                            <p class="text-gray-800 whitespace-pre-wrap">{{ $response->message }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Reply Form (if ticket is not closed) -->
                @if($ticket->status !== 'closed')
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aggiungi una Risposta</h3>
                        <form action="{{ route('student.tickets.reply', $ticket) }}" method="POST" id="reply-form">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                        La tua risposta <span class="text-red-500">*</span>
                                    </label>
                                    <textarea name="message"
                                              id="message"
                                              rows="4"
                                              placeholder="Scrivi la tua risposta qui..."
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 resize-none"
                                              required></textarea>
                                    <div class="mt-1 flex justify-between text-sm text-gray-500">
                                        <span>Aggiungi ulteriori informazioni o commenti</span>
                                        <span id="reply-char-count">0/1000</span>
                                    </div>
                                    @error('message')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-200">
                                    <a href="{{ route('student.tickets.index') }}"
                                       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 text-sm font-medium rounded-lg hover:bg-gray-400 transition-colors duration-200">
                                        Torna ai Messaggi
                                    </a>
                                    <button type="submit"
                                            class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                        </svg>
                                        Invia Risposta
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                @else
                    <!-- Closed Ticket Notice -->
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Questo messaggio è stato chiuso</h3>
                        <p class="text-gray-600">Non è più possibile aggiungere risposte a questo messaggio. Se hai bisogno di ulteriore assistenza, crea un nuovo messaggio.</p>
                        <div class="mt-4">
                            <a href="{{ route('student.tickets.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 transition-colors duration-200">
                                Nuovo Messaggio
                            </a>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    @push('scripts')
    <script nonce="@cspNonce">
    document.addEventListener('DOMContentLoaded', function() {
        // Character count for reply
        const messageTextarea = document.getElementById('message');
        const charCount = document.getElementById('reply-char-count');

        if (messageTextarea && charCount) {
            function updateCharCount() {
                const length = messageTextarea.value.length;
                charCount.textContent = `${length}/1000`;

                if (length > 800) {
                    charCount.classList.add('text-yellow-600');
                } else if (length > 950) {
                    charCount.classList.remove('text-yellow-600');
                    charCount.classList.add('text-red-600');
                } else {
                    charCount.classList.remove('text-yellow-600', 'text-red-600');
                }
            }

            messageTextarea.addEventListener('input', updateCharCount);
            updateCharCount(); // Initial count
        }

        // Form submission with loading state
        const form = document.getElementById('reply-form');
        if (form) {
            const submitButton = form.querySelector('button[type="submit"]');

            form.addEventListener('submit', function() {
                submitButton.disabled = true;
                submitButton.innerHTML = `
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Invio in corso...
                `;
            });
        }
    });
    </script>
    @endpush
</x-app-layout>