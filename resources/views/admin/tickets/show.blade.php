<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Ticket #{{ $ticket->id }} - {{ $ticket->title }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $ticket->user->name }} - {{ $ticket->formatted_created_at }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                @if($ticket->status !== 'closed')
                <form method="POST" action="{{ route('admin.tickets.close', $ticket) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Chiudi Ticket
                    </button>
                </form>
                @else
                <form method="POST" action="{{ route('admin.tickets.reopen', $ticket) }}" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Riapri Ticket
                    </button>
                </form>
                @endif
                <a href="{{ route('admin.tickets.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Torna alla Lista
                </a>
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
            <a href="{{ route('admin.tickets.index') }}" class="text-gray-500 hover:text-gray-700">Ticket</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">#{{ $ticket->id }}</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if(session('success'))
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('success') }}</p>
                    </div>
                </div>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium">{{ session('error') }}</p>
                    </div>
                </div>
            </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Main Content (2/3) -->
                <div class="lg:col-span-2 space-y-6">

                    <!-- Ticket Details Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Dettagli Ticket
                            </h3>
                        </div>
                        <div class="prose max-w-none">
                            <p class="text-gray-900 whitespace-pre-line">{{ $ticket->description }}</p>
                        </div>
                    </div>

                    <!-- Responses Timeline -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                Conversazione ({{ $ticket->responses->count() }})
                            </h3>
                        </div>
                        <div class="space-y-4">
                            @forelse($ticket->responses as $response)
                            <div class="flex items-start space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-r {{ $response->user->role === 'admin' ? 'from-rose-400 to-purple-500' : 'from-blue-400 to-cyan-500' }} rounded-full flex items-center justify-center text-white font-semibold flex-shrink-0">
                                    {{ strtoupper(substr($response->user->name, 0, 2)) }}
                                </div>
                                <div class="flex-1">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center space-x-2">
                                                <span class="font-medium text-gray-900">{{ $response->user->name }}</span>
                                                @if($response->user->role === 'admin')
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-purple-100 text-purple-800">
                                                    Admin
                                                </span>
                                                @else
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                                    Studente
                                                </span>
                                                @endif
                                            </div>
                                            <span class="text-sm text-gray-500">{{ $response->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-gray-900 whitespace-pre-line">{{ $response->message }}</p>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <div class="text-center py-8 text-gray-500">
                                <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                                <p class="text-sm">Nessuna risposta ancora</p>
                            </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Reply Form -->
                    @if($ticket->status !== 'closed')
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                                Rispondi al Ticket
                            </h3>
                        </div>
                        <form method="POST" action="{{ route('admin.tickets.reply', $ticket) }}">
                            @csrf
                            <div class="mb-4">
                                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                                    Messaggio <span class="text-red-500">*</span>
                                </label>
                                <textarea name="message" id="message" rows="4" required
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"
                                          placeholder="Scrivi la tua risposta...">{{ old('message') }}</textarea>
                                @error('message')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all duration-200 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                                Invia Risposta
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-6 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <p class="text-sm text-gray-600">Questo ticket è stato chiuso. Riapri il ticket per rispondere.</p>
                    </div>
                    @endif

                </div>

                <!-- Sidebar (1/3) -->
                <div class="space-y-6">

                    <!-- Ticket Info Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Informazioni</h3>
                        </div>
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Stato</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status_color }}">
                                        {{ ucfirst($ticket->status) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Priorità</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priority_color }}">
                                        {{ ucfirst($ticket->priority) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Categoria</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($ticket->category) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Creato</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ticket->formatted_created_at }}</dd>
                            </div>
                            @if($ticket->closed_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Chiuso</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ticket->closed_at->format('d/m/Y H:i') }}</dd>
                            </div>
                            @endif
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Risposte</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $ticket->responses->count() }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Student Info Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Studente</h3>
                        </div>
                        <div class="flex items-center space-x-3 mb-4">
                            <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-cyan-500 rounded-full flex items-center justify-center text-white font-semibold">
                                {{ strtoupper(substr($ticket->user->name, 0, 2)) }}
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $ticket->user->name }}</p>
                                <p class="text-sm text-gray-500">{{ $ticket->user->email }}</p>
                            </div>
                        </div>
                        <a href="{{ route('admin.students.show', $ticket->user) }}" class="inline-flex items-center text-sm text-rose-600 hover:text-rose-700 font-medium">
                            Vedi Profilo Completo
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <!-- Assign Card -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="border-b border-gray-200 pb-4 mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Assegnazione</h3>
                        </div>
                        <form method="POST" action="{{ route('admin.tickets.assign', $ticket) }}">
                            @csrf
                            @method('PATCH')
                            <div class="mb-4">
                                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">
                                    Assegna a Staff
                                </label>
                                <select name="assigned_to" id="assigned_to"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                    <option value="">Non assegnato</option>
                                    @foreach($staffMembers as $staff)
                                        <option value="{{ $staff->id }}" {{ $ticket->assigned_to == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                                Salva Assegnazione
                            </button>
                        </form>
                    </div>

                </div>

            </div>
        </div>
    </div>
</x-app-layout>