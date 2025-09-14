<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Super Admin Dashboard
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Panoramica generale del sistema
                </p>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-500">Aggiornato: {{ now()->format('d/m/Y H:i') }}</span>
                <button 
                    @click="refreshData()"
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
        <li class="text-gray-900 font-medium">Super Admin</li>
    </x-slot>

    <div x-data="dashboard()" class="space-y-8">
        <!-- Key Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-stats-card 
                title="Scuole Attive"
                :value="number_format($stats['schools_active'])"
                :subtitle="'su ' . number_format($stats['schools_total']) . ' totali'"
                icon="office-building"
                color="rose"
                :change="15"
                changeType="increase"
            />
            
            <x-stats-card 
                title="Totale Utenti"
                :value="number_format($stats['users_total'])"
                :subtitle="number_format($stats['students_total']) . ' studenti, ' . number_format($stats['admins_total']) . ' admin'"
                icon="users"
                color="blue"
                :change="8"
                changeType="increase"
            />
            
            <x-stats-card 
                title="Corsi Attivi"
                :value="number_format($stats['courses_active'])"
                :subtitle="'su ' . number_format($stats['courses_total']) . ' totali'"
                icon="academic-cap"
                color="purple"
                :change="3"
                changeType="decrease"
            />
            
            <x-stats-card 
                title="Ricavi Totali"
                :value="'€' . number_format($stats['payments_total'], 2)"
                :subtitle="'€' . number_format($stats['payments_month'], 2) . ' questo mese'"
                icon="currency-dollar"
                color="green"
                :change="22"
                changeType="increase"
            />
        </div>

        <!-- Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <x-chart-card 
                title="Andamento Registrazioni"
                type="line"
                :data="[
                    'labels' => ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu'],
                    'values' => [12, 19, 15, 25, 22, 30],
                    'label' => 'Nuovi Utenti'
                ]"
            />
            
            <x-chart-card 
                title="Distribuzione Ruoli"
                type="doughnut"
                :data="[
                    'labels' => ['Studenti', 'Admin', 'Super Admin'],
                    'values' => [$stats['students_total'], $stats['admins_total'], 1],
                    'label' => 'Utenti per Ruolo'
                ]"
            />
        </div>

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Recent Schools Table -->
            <div class="lg:col-span-2">
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-rose-50 to-pink-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">🏫 Scuole Recenti</h3>
                            <a href="{{ route('super-admin.schools.index') }}" 
                               class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-pink-600 rounded-lg hover:from-rose-600 hover:to-pink-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                Vedi Tutte
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        @if($recent_schools->count() > 0)
                            <div class="space-y-4">
                                @foreach($recent_schools as $school)
                                    <div class="flex items-center justify-between p-4 bg-gray-50/50 rounded-lg hover:bg-gray-100/50 transition-colors">
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 {{ $school->active ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }} rounded-full flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                                    </svg>
                                                </div>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-medium text-gray-900">{{ $school->name }}</h4>
                                                <p class="text-sm text-gray-500">{{ $school->email }}</p>
                                                @if($school->phone)
                                                    <p class="text-xs text-gray-400">{{ $school->phone }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-3">
                                            @if($school->active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Attiva
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    Inattiva
                                                </span>
                                            @endif
                                            <span class="text-xs text-gray-500">{{ $school->created_at->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-12">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuna scuola registrata</h3>
                                <p class="mt-1 text-sm text-gray-500">Inizia creando la prima scuola del sistema</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar: Recent Users & Quick Actions -->
            <div class="space-y-6">
                <!-- Recent Users -->
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-pink-50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-gray-900">👥 Utenti Recenti</h3>
                            <a href="{{ route('super-admin.users.index') }}" 
                               class="inline-flex items-center text-sm font-medium text-purple-600 hover:text-purple-700 transition-colors">
                                Vedi Tutti
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        @if($recent_users->count() > 0)
                            <div class="space-y-4">
                                @foreach($recent_users->take(5) as $user)
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-full flex items-center justify-center text-white text-sm font-medium">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ ucfirst($user->role) }} • {{ $user->created_at->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                </svg>
                                <p class="mt-2 text-sm text-gray-500">Nessun utente recente</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pending Documents Alert -->
                @if(isset($stats['documents_pending']) && $stats['documents_pending'] > 0)
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
                                <h4 class="text-sm font-medium text-amber-900">Documenti in Attesa</h4>
                                <p class="text-sm text-amber-700 mt-1">{{ $stats['documents_pending'] }} documenti richiedono approvazione</p>
                                <a href="{{ route('super-admin.users.index') }}" 
                                   class="inline-flex items-center mt-3 px-3 py-1.5 text-xs font-medium text-amber-800 bg-amber-200 rounded-lg hover:bg-amber-300 transition-colors">
                                    Verifica Ora
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Quick Actions -->
                <x-quick-actions :actions="[
                    [
                        'label' => 'Nuova Scuola',
                        'url' => route('super-admin.schools.create'),
                        'icon' => 'fas fa-plus',
                        'gradient' => 'from-rose-500 to-pink-600'
                    ],
                    [
                        'label' => 'Nuovo Utente',
                        'url' => route('super-admin.users.create'),
                        'icon' => 'fas fa-user-plus',
                        'gradient' => 'from-green-500 to-emerald-600'
                    ],
                    [
                        'label' => 'Report',
                        'url' => route('super-admin.reports'),
                        'icon' => 'fas fa-chart-bar',
                        'gradient' => 'from-blue-500 to-cyan-600'
                    ],
                    [
                        'label' => 'Impostazioni',
                        'url' => route('super-admin.settings'),
                        'icon' => 'fas fa-cog',
                        'gradient' => 'from-purple-500 to-violet-600'
                    ]
                ]" />
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function dashboard() {
        return {
            refreshing: false,
            refreshData() {
                this.refreshing = true;
                
                // Simulate API call
                setTimeout(() => {
                    this.refreshing = false;
                    location.reload();
                }, 1000);
            }
        }
    }
    </script>
    @endpush
</x-app-layout>