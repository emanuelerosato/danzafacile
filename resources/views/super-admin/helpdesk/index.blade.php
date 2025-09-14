<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Sistema Helpdesk
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione ticket e supporto utenti
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500">Aggiornato: {{ now()->format('d/m/Y H:i') }}</span>
                <button 
                    @click="refreshTickets()"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white/80 border border-gray-300 rounded-lg hover:bg-white hover:shadow-sm transition-all duration-200"
                >
                    <svg class="w-4 h-4 mr-2" :class="{ 'animate-spin': refreshing }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Aggiorna
                </button>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('super-admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Super Admin</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Helpdesk</li>
    </x-slot>

    <div x-data="helpdeskManager()" class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-4 text-center">
                <div class="text-2xl font-bold text-blue-600">{{ number_format($stats['total']) }}</div>
                <div class="text-sm text-gray-600">Totale</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-4 text-center">
                <div class="text-2xl font-bold text-green-600">{{ number_format($stats['open']) }}</div>
                <div class="text-sm text-gray-600">Aperti</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-4 text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ number_format($stats['pending']) }}</div>
                <div class="text-sm text-gray-600">In Attesa</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-4 text-center">
                <div class="text-2xl font-bold text-gray-600">{{ number_format($stats['closed']) }}</div>
                <div class="text-sm text-gray-600">Chiusi</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-4 text-center">
                <div class="text-2xl font-bold text-red-600">{{ number_format($stats['high_priority']) }}</div>
                <div class="text-sm text-gray-600">Alta Priorità</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-4 text-center">
                <div class="text-2xl font-bold text-orange-600">{{ number_format($stats['overdue']) }}</div>
                <div class="text-sm text-gray-600">In Scadenza</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-4 text-center">
                <div class="text-2xl font-bold text-purple-600">{{ number_format($stats['today']) }}</div>
                <div class="text-sm text-gray-600">Oggi</div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-4 text-center">
                <div class="text-2xl font-bold text-indigo-600">{{ number_format($stats['this_week']) }}</div>
                <div class="text-sm text-gray-600">Questa Settimana</div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filtri Avanzati</h3>
            <form method="GET" action="{{ route('super-admin.helpdesk.index') }}" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>Tutti</option>
                        <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Aperti</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>In Attesa</option>
                        <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>Chiusi</option>
                    </select>
                </div>

                <!-- Priority Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Priorità</label>
                    <select name="priority" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="all" {{ $priority === 'all' ? 'selected' : '' }}>Tutte</option>
                        <option value="critical" {{ $priority === 'critical' ? 'selected' : '' }}>Critica</option>
                        <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>Alta</option>
                        <option value="medium" {{ $priority === 'medium' ? 'selected' : '' }}>Media</option>
                        <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>Bassa</option>
                    </select>
                </div>

                <!-- Category Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Categoria</label>
                    <select name="category" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="">Tutte</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date Range -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data Da</label>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                </div>

                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ricerca</label>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Titolo, descrizione, utente..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                </div>

                <!-- Filter Actions -->
                <div class="md:col-span-2 lg:col-span-5 flex items-center justify-between pt-4 border-t border-gray-200">
                    <div class="flex space-x-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Filtra
                        </button>
                        <a href="{{ route('super-admin.helpdesk.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-400 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reset
                        </a>
                    </div>
                    <div class="text-sm text-gray-500">
                        Mostrando {{ $tickets->count() }} di {{ $tickets->total() }} ticket
                    </div>
                </div>
            </form>
        </div>

        <!-- Tickets List -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-rose-50 to-pink-50">
                <h3 class="text-lg font-semibold text-gray-900">🎫 Lista Ticket</h3>
            </div>

            @if($tickets->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50/50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titolo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Utente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorità</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Risposte</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creato</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($tickets as $ticket)
                                <tr class="hover:bg-gray-50/50 transition-colors {{ $ticket->is_overdue ? 'bg-red-50/50' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        #{{ $ticket->id }}
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-medium text-gray-900">
                                            <a href="{{ route('super-admin.helpdesk.show', $ticket) }}" class="hover:text-rose-600 transition-colors">
                                                {{ Str::limit($ticket->title, 50) }}
                                            </a>
                                        </div>
                                        @if($ticket->category)
                                            <div class="text-xs text-gray-500">{{ $ticket->category }}</div>
                                        @endif
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                {{ strtoupper(substr($ticket->user->name, 0, 1)) }}
                                            </div>
                                            <div class="ml-3">
                                                <div class="text-sm font-medium text-gray-900">{{ $ticket->user->name }}</div>
                                                <div class="text-xs text-gray-500">{{ $ticket->user->email }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $ticket->status_color }}">
                                            {{ ucfirst($ticket->status) }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $ticket->priority_color }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $ticket->responses_count }} {{ $ticket->responses_count === 1 ? 'risposta' : 'risposte' }}
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $ticket->formatted_created_at }}</div>
                                        <div class="text-xs text-gray-500">{{ $ticket->time_ago }}</div>
                                    </td>
                                    
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="{{ route('super-admin.helpdesk.show', $ticket) }}" 
                                           class="text-rose-600 hover:text-rose-900 transition-colors">
                                            Visualizza
                                        </a>
                                        
                                        @if($ticket->status !== 'closed')
                                            <form method="POST" action="{{ route('super-admin.helpdesk.close', $ticket) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" 
                                                        onclick="return confirm('Vuoi chiudere questo ticket?')"
                                                        class="text-gray-600 hover:text-gray-900 transition-colors">
                                                    Chiudi
                                                </button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('super-admin.helpdesk.reopen', $ticket) }}" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-600 hover:text-green-900 transition-colors">
                                                    Riapri
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
                    {{ $tickets->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun ticket trovato</h3>
                    <p class="mt-1 text-sm text-gray-500">Non ci sono ticket che corrispondono ai filtri selezionati.</p>
                    <div class="mt-6">
                        <a href="{{ route('super-admin.helpdesk.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-rose-600 hover:bg-rose-700 transition-colors">
                            Visualizza tutti i ticket
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
    function helpdeskManager() {
        return {
            refreshing: false,
            
            refreshTickets() {
                this.refreshing = true;
                setTimeout(() => {
                    this.refreshing = false;
                    window.location.reload();
                }, 1000);
            }
        }
    }
    </script>
    @endpush
</x-app-layout>