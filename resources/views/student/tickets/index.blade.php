<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    I Miei Messaggi
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestisci le tue richieste di assistenza e comunicazioni con la scuola
                </p>
            </div>
            <a href="{{ route('student.tickets.create') }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                </svg>
                Nuovo Messaggio
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
        <li class="text-gray-900 font-medium">Messaggi</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                <!-- Stats Overview -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Totale Messaggi -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Totale Messaggi</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $tickets->total() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Messaggi Aperti -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Aperti</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $tickets->where('status', 'open')->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Messaggi in Attesa -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-yellow-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">In Attesa</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $tickets->where('status', 'pending')->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Messaggi Risolti -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-gray-500 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Risolti</p>
                                <p class="text-2xl font-bold text-gray-900">{{ $tickets->where('status', 'closed')->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters and Search -->
                <div class="bg-white rounded-lg shadow p-6">
        <form method="GET" action="{{ route('student.tickets.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cerca messaggi</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}"
                       placeholder="Titolo o contenuto..."
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Stato</label>
                <select name="status" id="status" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    <option value="">Tutti gli stati</option>
                    <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Aperto</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>In Attesa</option>
                    <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Chiuso</option>
                </select>
            </div>
            <div>
                <label for="priority" class="block text-sm font-medium text-gray-700 mb-2">Priorità</label>
                <select name="priority" id="priority" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                    <option value="">Tutte le priorità</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Bassa</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Media</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                    <option value="critical" {{ request('priority') === 'critical' ? 'selected' : '' }}>Critica</option>
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                    Filtra
                </button>
            </div>
                    </form>
                </div>

                <!-- Tickets List -->
                <div class="bg-white rounded-lg shadow p-6">
        @if($tickets->count() > 0)
            <div class="space-y-4" id="tickets-list">
                @include('student.tickets.partials.list', ['tickets' => $tickets])
            </div>

            <!-- Pagination -->
            <div class="mt-6">
                {{ $tickets->appends(request()->query())->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-medium text-gray-900">Nessun messaggio trovato</h3>
                <p class="mt-2 text-gray-600">Non hai ancora inviato messaggi alla scuola.</p>
                <div class="mt-6">
                    <a href="{{ route('student.tickets.create') }}" class="bg-gradient-to-r from-rose-600 to-purple-600 hover:from-rose-700 hover:to-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-all duration-200">
                        Invia il tuo primo messaggio
                    </a>
                    </div>
                @endif
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script nonce="@cspNonce">
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-submit form when filters change
        const statusSelect = document.getElementById('status');
        const prioritySelect = document.getElementById('priority');

        [statusSelect, prioritySelect].forEach(select => {
            select.addEventListener('change', function() {
                this.form.submit();
            });
        });
    });
    </script>
    @endpush
</x-app-layout>