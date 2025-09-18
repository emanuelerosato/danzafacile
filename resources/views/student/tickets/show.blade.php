@extends('layouts.app')

@section('title', 'Messaggio: ' . $ticket->title)

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <!-- Header with glassmorphism -->
    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
        <!-- Breadcrumb Navigation -->
        <nav class="flex mb-4" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('student.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-rose-600">
                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                        </svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <a href="{{ route('student.tickets.index') }}" class="ml-1 text-sm font-medium text-gray-700 hover:text-rose-600 md:ml-2">
                            Messaggi
                        </a>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ Str::limit($ticket->title, 30) }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Ticket Header -->
        <div class="flex justify-between items-start">
            <div class="flex-1">
                <h1 class="text-3xl font-bold text-gray-900 mb-3">{{ $ticket->title }}</h1>
                <div class="flex items-center space-x-6 text-sm text-gray-600">
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3a2 2 0 012-2h4a2 2 0 012 2v4m-6 0v1a2 2 0 002 2h4a2 2 0 002-2V7m-6 0H6a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2h-6z"></path>
                        </svg>
                        ID: #{{ $ticket->id }}
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $ticket->formatted_created_at }}
                    </div>
                    <div class="flex items-center">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                        </svg>
                        {{ ucfirst($ticket->category) }}
                    </div>
                </div>
            </div>

            <!-- Status and Priority Badges -->
            <div class="flex flex-col items-end space-y-2 ml-6">
                <!-- Status Badge -->
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->status_color }}">
                    {{ ucfirst($ticket->status === 'open' ? 'Aperto' : ($ticket->status === 'pending' ? 'In Attesa' : 'Chiuso')) }}
                </span>

                <!-- Priority Badge -->
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $ticket->priority_color }}">
                    Priorità {{ ucfirst($ticket->priority === 'low' ? 'Bassa' :
                               ($ticket->priority === 'medium' ? 'Media' :
                               ($ticket->priority === 'high' ? 'Alta' : 'Critica'))) }}
                </span>

                @if($ticket->is_overdue)
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        In Ritardo
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Ticket Details and Conversation -->
    <div class="space-y-6">
        <!-- Original Message -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
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
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
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
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
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
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                Torna ai Messaggi
                            </a>
                            <button type="submit"
                                    class="bg-gradient-to-r from-rose-600 to-purple-600 hover:from-rose-700 hover:to-purple-700 text-white font-semibold py-2 px-6 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                                <svg class="w-4 h-4 mr-2 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-center">
                <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Questo messaggio è stato chiuso</h3>
                <p class="text-gray-600">Non è più possibile aggiungere risposte a questo messaggio. Se hai bisogno di ulteriore assistenza, crea un nuovo messaggio.</p>
                <div class="mt-4">
                    <a href="{{ route('student.tickets.create') }}"
                       class="bg-rose-600 hover:bg-rose-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                        Nuovo Messaggio
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
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
@endsection