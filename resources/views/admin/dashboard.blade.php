<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dashboard Admin - {{ $currentSchool->name ?? 'Scuola' }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Panoramica completa della tua scuola di danza
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500">Aggiornato: {{ now()->format('d/m/Y H:i') }}</span>
                <button
                    onclick="location.reload()"
                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white/80 border border-gray-300 rounded-lg hover:bg-white hover:shadow-sm transition-all duration-200"
                >
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
        <li class="text-gray-900 font-medium">Admin - {{ $currentSchool->name ?? 'Scuola' }}</li>
    </x-slot>

    <div class="space-y-8">

    <div class="space-y-6">
        <!-- Key Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <x-stats-card
                title="Studenti Totali"
                :value="number_format($quickStats['total_students'] ?? 0)"
                :subtitle="($quickStats['active_students'] ?? 0) . ' attivi'"
                icon="users"
                color="blue"
                :change="$quickStats['students_change'] ?? 0"
                :changeType="$quickStats['students_change_type'] ?? 'neutral'"
            />

            <x-stats-card
                title="Corsi Attivi"
                :value="number_format($quickStats['active_courses'] ?? 0)"
                :subtitle="'su ' . number_format($quickStats['total_courses'] ?? 0) . ' totali'"
                icon="academic-cap"
                color="purple"
                :change="$quickStats['courses_change'] ?? 0"
                :changeType="$quickStats['courses_change_type'] ?? 'neutral'"
            />

            <x-stats-card
                title="Ricavi Mensili"
                :value="'€' . number_format($quickStats['monthly_revenue'] ?? 0, 2)"
                :subtitle="'Mese corrente'"
                icon="currency-dollar"
                color="green"
                :change="$quickStats['revenue_change'] ?? 0"
                :changeType="$quickStats['revenue_change_type'] ?? 'neutral'"
            />

            <x-stats-card
                title="Eventi Prossimi"
                :value="number_format($quickStats['upcoming_events'] ?? 0)"
                :subtitle="number_format($quickStats['total_events'] ?? 0) . ' eventi totali'"
                icon="calendar"
                color="rose"
                :change="$quickStats['events_change'] ?? 0"
                :changeType="$quickStats['events_change_type'] ?? 'neutral'"
            />

            <x-stats-card
                title="Ticket Aperti"
                :value="number_format($quickStats['open_tickets'] ?? 0)"
                :subtitle="($quickStats['urgent_tickets'] ?? 0) . ' urgenti'"
                icon="chat"
                color="orange"
                :change="null"
            />
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <x-chart-card
                title="Andamento Iscrizioni"
                type="line"
                :data="$analytics['enrollment_trends'] ?? [
                    'labels' => ['Nessun dato'],
                    'values' => [0],
                    'label' => 'Nuove Iscrizioni'
                ]"
            />

            <x-chart-card
                title="Distribuzione Corsi"
                type="doughnut"
                :data="$analytics['course_distribution'] ?? [
                    'labels' => ['Nessun dato'],
                    'values' => [0],
                    'label' => 'Studenti per Corso'
                ]"
            />
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Enrollments Table -->
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-rose-50 to-pink-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">💃 Iscrizioni Recenti</h3>
                            <a href="{{ route('admin.enrollments.index') }}"
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-pink-600 rounded-lg hover:from-rose-600 hover:to-pink-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                Vedi Tutte
                            </a>
                        </div>
                    </div>

                    <div class="p-6">
                        @if(isset($recent_enrollments) && $recent_enrollments->count() > 0)
                            <div class="space-y-4">
                                @foreach($recent_enrollments->take(5) as $enrollment)
                                    <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-lg hover:bg-gray-100/50 transition-colors">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                    {{ strtoupper(substr($enrollment->user->name ?? 'N', 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900">{{ $enrollment->user->name ?? 'N/A' }}</h4>
                                                <p class="text-sm text-gray-500">{{ $enrollment->course->name ?? 'N/A' }}</p>
                                                <p class="text-xs text-gray-400">{{ $enrollment->created_at->format('d/m/Y') }}</p>
                                            </div>
                                        </div>
                                        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Attiva
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuna iscrizione recente</h3>
                                <p class="mt-1 text-sm text-gray-500">Le nuove iscrizioni appariranno qui</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar: Quick Actions & Alerts -->
            <div class="space-y-6">
                <!-- Quick Actions Component -->
                <x-quick-actions :actions="[
                    [
                        'label' => 'Nuovo Corso',
                        'url' => route('admin.courses.create'),
                        'icon' => 'fas fa-plus',
                        'gradient' => 'from-rose-500 to-pink-600'
                    ],
                    [
                        'label' => 'Gestisci Iscrizioni',
                        'url' => route('admin.enrollments.index'),
                        'icon' => 'fas fa-user-plus',
                        'gradient' => 'from-blue-500 to-cyan-600'
                    ],
                    [
                        'label' => 'Pagamenti',
                        'url' => route('admin.payments.index'),
                        'icon' => 'fas fa-credit-card',
                        'gradient' => 'from-green-500 to-emerald-600'
                    ],
                    [
                        'label' => 'Report',
                        'url' => route('admin.reports.index'),
                        'icon' => 'fas fa-chart-bar',
                        'gradient' => 'from-purple-500 to-violet-600'
                    ]
                ]" />

                <!-- Pending Payments Alert -->
                @if(isset($quickStats['pending_payments']) && $quickStats['pending_payments'] > 0)
                    <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200 rounded-2xl p-6">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                                    </svg>
                                </div>
                            </div>
                            <div class="flex-1">
                                <h4 class="text-sm font-medium text-amber-900">Pagamenti in Sospeso</h4>
                                <p class="text-sm text-amber-700 mt-1">{{ $quickStats['pending_payments'] }} pagamenti richiedono attenzione</p>
                                <a href="{{ route('admin.payments.index') }}"
                                   class="inline-flex items-center mt-3 px-3 py-1.5 text-xs font-medium text-amber-800 bg-amber-200 rounded-lg hover:bg-amber-300 transition-colors">
                                    Verifica Ora
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- TASK #11: Storage Usage Widget --}}
                @php
                    $storageInfo = app(App\Services\StorageQuotaService::class)->getStorageInfo(auth()->user()->school);
                @endphp

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Spazio Gallerie</h3>

                        @if($storageInfo['unlimited'])
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 2a1 1 0 011 1v1.323l3.954 1.582 1.599-.8a1 1 0 01.894 1.79l-1.233.616 1.738 5.42a1 1 0 01-.285 1.05A3.989 3.989 0 0115 15a3.989 3.989 0 01-2.667-1.019 1 1 0 01-.285-1.05l1.738-5.42-1.233-.617a1 1 0 01.894-1.788l1.599.799L11 4.323V3a1 1 0 011-1zm-5 8.274l-.818 2.552c-.25.78.74 1.43 1.403.926l.07-.07a1.99 1.99 0 012.83 0l.07.07c.662.504 1.652-.145 1.403-.926l-.818-2.552a1.99 1.99 0 00-1.13-1.13l-2.552-.818a1 1 0 00-.926 1.403l.07.07a1.99 1.99 0 000 2.83l-.07.07a1 1 0 00-.926 1.403z"/>
                                </svg>
                                Illimitato
                            </span>
                        @else
                            @if($storageInfo['is_full'])
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Pieno
                                </span>
                            @elseif($storageInfo['is_warning'])
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                    </svg>
                                    Attenzione
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    OK
                                </span>
                            @endif
                        @endif
                    </div>

                    @if($storageInfo['unlimited'])
                        <p class="text-sm text-gray-600 mb-2">
                            <span class="font-semibold text-gray-900">{{ $storageInfo['used_formatted'] }}</span> utilizzati
                        </p>
                        <p class="text-xs text-gray-500">Storage illimitato attivo</p>
                    @else
                        <div class="space-y-3">
                            <div>
                                <div class="flex items-center justify-between text-sm mb-1">
                                    <span class="text-gray-600">Utilizzo:</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ $storageInfo['used_formatted'] }} / {{ $storageInfo['quota_formatted'] }}
                                    </span>
                                </div>

                                {{-- Progress bar --}}
                                <div class="w-full bg-gray-200 rounded-full h-2.5">
                                    <div class="h-2.5 rounded-full transition-all duration-300
                                        @if($storageInfo['is_full']) bg-red-600
                                        @elseif($storageInfo['is_warning']) bg-yellow-500
                                        @else bg-green-500
                                        @endif"
                                         style="width: {{ min($storageInfo['usage_percent'], 100) }}%">
                                    </div>
                                </div>

                                <p class="text-xs text-gray-500 mt-1">
                                    {{ $storageInfo['usage_percent'] }}% utilizzato
                                    @if($storageInfo['expires_at'])
                                        • Scade il {{ $storageInfo['expires_at']->format('d/m/Y') }}
                                    @endif
                                </p>
                            </div>

                            @if($storageInfo['is_warning'] || $storageInfo['is_full'])
                                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-3 rounded">
                                    <div class="flex">
                                        <div class="flex-shrink-0">
                                            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm text-yellow-700">
                                                @if($storageInfo['is_full'])
                                                    Spazio esaurito! Non puoi caricare nuovi media.
                                                @else
                                                    Stai raggiungendo il limite. Considera di acquistare spazio aggiuntivo.
                                                @endif
                                            </p>
                                            <a href="{{ route('admin.billing.storage') }}"
                                               class="inline-flex items-center mt-2 text-sm font-medium text-yellow-700 hover:text-yellow-600">
                                                Acquista Spazio
                                                <svg class="ml-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Recent Tickets Widget -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Ticket Recenti</h3>
                        <a href="{{ route('admin.tickets.index') }}"
                           class="text-sm font-medium text-rose-600 hover:text-rose-700">
                            Vedi Tutti
                        </a>
                    </div>
                    @if(isset($recentTickets) && $recentTickets->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentTickets as $ticket)
                                <a href="{{ route('admin.tickets.show', $ticket) }}"
                                   class="block p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">
                                                {{ $ticket->title }}
                                            </p>
                                            <p class="text-xs text-gray-500 mt-1">
                                                {{ $ticket->user->name }}
                                            </p>
                                        </div>
                                        <div class="ml-2 flex-shrink-0">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $ticket->status_color }}">
                                                {{ ucfirst($ticket->status) }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between mt-2">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $ticket->priority_color }}">
                                            {{ ucfirst($ticket->priority) }}
                                        </span>
                                        <span class="text-xs text-gray-400">
                                            {{ $ticket->created_at->diffForHumans() }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
                            </svg>
                            <p class="mt-2 text-sm text-gray-500">Nessun ticket recente</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-app-layout>