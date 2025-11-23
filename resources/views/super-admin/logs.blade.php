<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Log Sistema
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Monitoraggio attività e errori del sistema
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    Sistema attivo
                </div>
                <button @click="refreshLogs()" 
                        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white/50 border border-gray-300 rounded-lg hover:bg-white/70 transition-all duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
        <li class="text-gray-900 font-medium">Log Sistema</li>
    </x-slot>

    <!-- Error Message -->
    @if(isset($error))
        <div class="mb-4">
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                {{ $error }}
            </div>
        </div>
    @endif

    <div x-data="logsManager()" class="space-y-6">
        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['total'] ?? 0 }}</h3>
                        <p class="text-sm text-gray-600">Totale Log</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['error'] ?? 0 }}</h3>
                        <p class="text-sm text-gray-600">Errori</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.464 0L4.35 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['warning'] ?? 0 }}</h3>
                        <p class="text-sm text-gray-600">Warning</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['info'] ?? 0 }}</h3>
                        <p class="text-sm text-gray-600">Info</p>
                    </div>
                </div>
            </div>

            <div class="bg-white/80 backdrop-blur-sm rounded-xl shadow-lg border border-white/20 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center">
                            <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900">{{ $stats['debug'] ?? 0 }}</h3>
                        <p class="text-sm text-gray-600">Debug</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters Section -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 mb-6">
            <div class="px-6 py-4">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🔍 Filtri Log</h3>
                <form method="GET" action="{{ route('super-admin.logs') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Level Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Livello</label>
                        <select name="level" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <option value="all" {{ ($level ?? 'all') === 'all' ? 'selected' : '' }}>Tutti</option>
                            <option value="error" {{ ($level ?? '') === 'error' ? 'selected' : '' }}>Errori</option>
                            <option value="warning" {{ ($level ?? '') === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="info" {{ ($level ?? '') === 'info' ? 'selected' : '' }}>Info</option>
                            <option value="debug" {{ ($level ?? '') === 'debug' ? 'selected' : '' }}>Debug</option>
                        </select>
                    </div>

                    <!-- Date Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data</label>
                        <input type="date" 
                               name="date"
                               value="{{ $date ?? '' }}"
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                    </div>

                    <!-- Search Filter -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ricerca</label>
                        <input type="text" 
                               name="search"
                               value="{{ $search ?? '' }}"
                               placeholder="Cerca nei messaggi..."
                               class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-end">
                        <button type="submit" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 transition-colors">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Filtra
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Logs List -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-slate-50">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">📜 Log Entries</h3>
                        <p class="text-sm text-gray-600">
                            @if($pagination['total'] > 0)
                                Mostrati {{ $pagination['from'] }} - {{ $pagination['to'] }} di {{ $pagination['total'] }} log
                            @else
                                Nessun log trovato
                            @endif
                        </p>
                    </div>
                    @if($pagination['total'] > 0)
                        <div class="text-sm text-gray-500">
                            Pagina {{ $pagination['current_page'] }} di {{ $pagination['last_page'] }}
                        </div>
                    @endif
                </div>
            </div>
            
            <div class="divide-y divide-gray-200">
                @forelse($paginatedLogs as $log)
                    <div class="p-6 hover:bg-gray-50/50 transition-colors" x-data="{ expanded: false }">
                        <div class="flex items-start space-x-4">
                            <!-- Log Level Badge -->
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $log['level_color'] }}">
                                    {{ strtoupper($log['level']) }}
                                </span>
                            </div>
                            
                            <!-- Log Content -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-3">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $log['formatted_time'] }}
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            {{ $log['time_ago'] }}
                                        </p>
                                        @if($log['environment'])
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800">
                                                {{ $log['environment'] }}
                                            </span>
                                        @endif
                                    </div>
                                    @if(!empty(trim($log['context'])))
                                        <button @click="expanded = !expanded" 
                                                class="text-gray-400 hover:text-gray-600 transition-colors">
                                            <svg class="w-5 h-5 transform transition-transform" :class="{ 'rotate-180': expanded }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                
                                <div class="mt-2">
                                    <p class="text-sm text-gray-700 leading-relaxed">
                                        {{ $log['message'] }}
                                    </p>
                                </div>
                                
                                @if(!empty(trim($log['context'])))
                                    <div x-show="expanded" 
                                         x-transition:enter="transition ease-out duration-200"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-150"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="mt-3 p-3 bg-gray-50 rounded-lg">
                                        <h4 class="text-xs font-medium text-gray-700 mb-2">Dettagli aggiuntivi:</h4>
                                        <pre class="text-xs text-gray-600 whitespace-pre-wrap font-mono overflow-x-auto">{{ trim($log['context']) }}</pre>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-12 text-center">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun log trovato</h3>
                        <p class="text-gray-600 mb-4">Non ci sono log che corrispondono ai filtri selezionati.</p>
                        <a href="{{ route('super-admin.logs') }}" 
                           class="inline-flex items-center px-4 py-2 text-sm font-medium text-rose-600 hover:text-rose-700">
                            Visualizza tutti i log
                        </a>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Pagination -->
        @if($pagination['total'] > 0 && $pagination['last_page'] > 1)
            <div class="mt-6 bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Mostrati {{ $pagination['from'] }} - {{ $pagination['to'] }} di {{ $pagination['total'] }} risultati
                    </div>
                    
                    <div class="flex items-center space-x-2">
                        @php
                            $currentParams = request()->query();
                        @endphp
                        
                        @if($pagination['current_page'] > 1)
                            <a href="{{ route('super-admin.logs', array_merge($currentParams, ['page' => $pagination['current_page'] - 1])) }}" 
                               class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-500 hover:text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                                </svg>
                                Precedente
                            </a>
                        @endif
                        
                        @for($i = max(1, $pagination['current_page'] - 2); $i <= min($pagination['last_page'], $pagination['current_page'] + 2); $i++)
                            <a href="{{ route('super-admin.logs', array_merge($currentParams, ['page' => $i])) }}" 
                               class="inline-flex items-center px-3 py-1 text-sm font-medium border rounded-md transition-colors {{ $i === $pagination['current_page'] ? 'text-rose-600 bg-rose-50 border-rose-200' : 'text-gray-500 hover:text-gray-700 border-gray-300 hover:bg-gray-50' }}">
                                {{ $i }}
                            </a>
                        @endfor
                        
                        @if($pagination['current_page'] < $pagination['last_page'])
                            <a href="{{ route('super-admin.logs', array_merge($currentParams, ['page' => $pagination['current_page'] + 1])) }}" 
                               class="inline-flex items-center px-3 py-1 text-sm font-medium text-gray-500 hover:text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                                Successivo
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script nonce="@cspNonce">
    function logsManager() {
        return {
            refreshLogs() {
                window.location.reload();
            },
            
            clearFilters() {
                window.location.href = '{{ route("super-admin.logs") }}';
            },
            
            exportLogs() {
                // Future implementation for log export
                alert('Funzionalità di export in sviluppo');
            }
        }
    }
    </script>
    @endpush
</x-app-layout>