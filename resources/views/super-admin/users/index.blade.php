@extends('layouts.app')

@section('title', 'Gestione Utenti - Super Admin')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50" x-data="usersManagement()">
    <!-- Header Section -->
    <div class="bg-white/30 backdrop-blur-sm border-b border-white/20 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">👥 Gestione Utenti</h1>
                    <p class="text-sm text-gray-600">Amministrazione globale utenti sistema</p>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-500">Totale: {{ $users->total() }} utenti</span>
                    <a href="{{ route('super-admin.users.export', request()->query()) }}" 
                       onclick="this.style.pointerEvents='none'; this.innerHTML='<svg class=\\'animate-spin h-4 w-4 mr-2\\' fill=\\'none\\' stroke=\\'currentColor\\' viewBox=\\'0 0 24 24\\'><circle class=\\'opacity-25\\' cx=\\'12\\' cy=\\'12\\' r=\\'10\\' stroke=\\'currentColor\\' stroke-width=\\'4\\'></circle><path class=\\'opacity-75\\' fill=\\'currentColor\\' d=\\'M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z\\'></path></svg>Generazione...';"
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-all duration-200 shadow-sm hover:shadow-md">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Esporta CSV
                    </a>
                    <a href="{{ route('super-admin.users.create') }}" 
                       class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-pink-600 rounded-lg hover:from-rose-600 hover:to-pink-700 transition-all duration-200 shadow-md hover:shadow-lg">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuovo Utente
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Filters and Search -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
            <form method="GET" action="{{ route('super-admin.users.index') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <!-- Search -->
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cerca Utente</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            <input type="text" 
                                   name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Nome, email..." 
                                   class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                        </div>
                    </div>

                    <!-- Role Filter -->
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Ruolo</label>
                        <select name="role" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <option value="">Tutti i ruoli</option>
                            <option value="super_admin" {{ request('role') == 'super_admin' ? 'selected' : '' }}>Super Admin</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="instructor" {{ request('role') == 'instructor' ? 'selected' : '' }}>Istruttore</option>
                            <option value="student" {{ request('role') == 'student' ? 'selected' : '' }}>Studente</option>
                        </select>
                    </div>

                    <!-- School Filter -->
                    <div>
                        <label for="school_id" class="block text-sm font-medium text-gray-700 mb-2">Scuola</label>
                        <select name="school_id" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <option value="">Tutte le scuole</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->id }}" {{ request('school_id') == $school->id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="block w-full py-2 px-3 border border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500 bg-white/50">
                            <option value="">Tutti</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Attivi</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inattivi</option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-rose-600 hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                            Filtra
                        </button>
                        <a href="{{ route('super-admin.users.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500">
                            Reset
                        </a>
                    </div>
                    
                    <!-- Bulk Actions -->
                    <div class="flex items-center space-x-3" x-show="selectedUsers.length > 0" x-transition>
                        <span class="text-sm text-gray-500" x-text="`${selectedUsers.length} selezionati`"></span>
                        <button @click="bulkAction('activate')" 
                                class="inline-flex items-center px-3 py-1.5 border border-green-300 text-sm font-medium rounded-lg text-green-700 bg-green-50 hover:bg-green-100">
                            Attiva
                        </button>
                        <button @click="bulkAction('deactivate')" 
                                class="inline-flex items-center px-3 py-1.5 border border-red-300 text-sm font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100">
                            Disattiva
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-rose-50 to-pink-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Lista Utenti</h3>
                    <div class="flex items-center space-x-2">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   @change="toggleAllUsers($event.target.checked)"
                                   class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                            <span class="ml-2 text-sm text-gray-600">Seleziona tutti</span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50/50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                <span class="sr-only">Seleziona</span>
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Utente
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Ruolo
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Scuola
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Registrato
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Azioni</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white/30 divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-white/40 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <input type="checkbox" 
                                           :value="{{ $user->id }}"
                                           x-model="selectedUsers"
                                           class="rounded border-gray-300 text-rose-600 shadow-sm focus:border-rose-300 focus:ring focus:ring-rose-200 focus:ring-opacity-50">
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-gradient-to-r from-rose-400 to-pink-500 flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $user->role === 'super_admin' ? 'bg-purple-100 text-purple-800' : '' }}
                                        {{ $user->role === 'admin' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $user->role === 'instructor' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $user->role === 'student' ? 'bg-gray-100 text-gray-800' : '' }}">
                                        {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->school ? $user->school->name : 'Nessuna scuola' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                        {{ $user->active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->active ? 'Attivo' : 'Inattivo' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('super-admin.users.show', $user) }}" 
                                           class="text-gray-600 hover:text-gray-900 transition-colors" 
                                           title="Visualizza">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                        </a>
                                        <a href="{{ route('super-admin.users.edit', $user) }}" 
                                           class="text-rose-600 hover:text-rose-900 transition-colors" 
                                           title="Modifica">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </a>
                                        @if($user->role !== 'super_admin')
                                            <button @click="toggleUserStatus({{ $user->id }}, {{ $user->active ? 'false' : 'true' }})" 
                                                    class="{{ $user->active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' }} transition-colors" 
                                                    title="{{ $user->active ? 'Disattiva' : 'Attiva' }}">
                                                @if($user->active)
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"/>
                                                    </svg>
                                                @else
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                    </svg>
                                                @endif
                                            </button>
                                        @endif
                                        @if($user->role !== 'super_admin' && $user->id !== auth()->id())
                                            <form action="{{ route('super-admin.users.impersonate', $user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="text-purple-600 hover:text-purple-900 transition-colors" 
                                                        title="Impersona utente">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-12 text-center">
                                    <div class="text-gray-500">
                                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                                        </svg>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">Nessun utente trovato</h3>
                                        <p class="text-gray-500">Prova a modificare i filtri di ricerca o crea un nuovo utente.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="bg-white/30 px-6 py-4 border-t border-gray-200">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Alpine.js Users Management -->
<script>
function usersManagement() {
    return {
        selectedUsers: [],
        
        toggleAllUsers(checked) {
            if (checked) {
                this.selectedUsers = @json($users->pluck('id')->toArray());
            } else {
                this.selectedUsers = [];
            }
        },
        
        async toggleUserStatus(userId, status) {
            // Set loading state for the toggle button
            const toggleButton = document.querySelector(`[data-user-id="${userId}"]`);
            const originalHTML = toggleButton?.innerHTML;
            const originalClasses = toggleButton?.className;
            
            if (toggleButton) {
                toggleButton.disabled = true;
                toggleButton.className = originalClasses.replace(/(bg-green-500|bg-red-500)/, 'bg-gray-400');
                toggleButton.innerHTML = `
                    <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                `;
            }
            
            try {
                const response = await fetch(`/super-admin/users/${userId}/toggle-active`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ active: status })
                });
                
                const result = await response.json();
                
                if (response.ok && result.success) {
                    Toast.success(result.message || 'Status aggiornato con successo');
                    window.location.reload();
                } else {
                    const errorMessage = result.message || 'Errore durante l\'aggiornamento dello status utente';
                    Toast.error(errorMessage);
                    console.error('Toggle error:', result);
                    
                    // Restore button state on error
                    if (toggleButton && originalHTML && originalClasses) {
                        toggleButton.disabled = false;
                        toggleButton.className = originalClasses;
                        toggleButton.innerHTML = originalHTML;
                    }
                }
            } catch (error) {
                console.error('Network Error:', error);
                Toast.error('Errore di connessione durante l\'aggiornamento dello status utente');
                
                // Restore button state on network error
                if (toggleButton && originalHTML && originalClasses) {
                    toggleButton.disabled = false;
                    toggleButton.className = originalClasses;
                    toggleButton.innerHTML = originalHTML;
                }
            }
        },
        
        async bulkAction(action) {
            if (this.selectedUsers.length === 0) {
                Toast.warning('Seleziona almeno un utente');
                return;
            }
            
            if (!confirm(`Sei sicuro di voler ${action === 'activate' ? 'attivare' : 'disattivare'} ${this.selectedUsers.length} utenti?`)) {
                return;
            }
            
            // Set loading state for bulk action buttons
            const activateBtn = document.querySelector('[data-bulk-action="activate"]');
            const deactivateBtn = document.querySelector('[data-bulk-action="deactivate"]');
            const originalActivateHTML = activateBtn?.innerHTML;
            const originalDeactivateHTML = deactivateBtn?.innerHTML;
            
            if (activateBtn) activateBtn.disabled = true;
            if (deactivateBtn) deactivateBtn.disabled = true;
            
            const activeButton = action === 'activate' ? activateBtn : deactivateBtn;
            if (activeButton) {
                activeButton.innerHTML = `
                    <div class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Elaborazione...
                    </div>
                `;
            }
            
            try {
                const response = await fetch('/super-admin/users/bulk-action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        action: action,
                        user_ids: this.selectedUsers
                    })
                });
                
                if (response.ok) {
                    Toast.success(`Operazione ${action === 'activate' ? 'attivazione' : 'disattivazione'} completata con successo`);
                    window.location.reload();
                } else {
                    Toast.error('Errore durante l\'operazione bulk');
                    
                    // Restore button states on error
                    if (activateBtn && originalActivateHTML) {
                        activateBtn.disabled = false;
                        activateBtn.innerHTML = originalActivateHTML;
                    }
                    if (deactivateBtn && originalDeactivateHTML) {
                        deactivateBtn.disabled = false;
                        deactivateBtn.innerHTML = originalDeactivateHTML;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                Toast.error('Errore di connessione durante l\'operazione bulk');
                
                // Restore button states on network error
                if (activateBtn && originalActivateHTML) {
                    activateBtn.disabled = false;
                    activateBtn.innerHTML = originalActivateHTML;
                }
                if (deactivateBtn && originalDeactivateHTML) {
                    deactivateBtn.disabled = false;
                    deactivateBtn.innerHTML = originalDeactivateHTML;
                }
            }
        }
    }
}
</script>

@endsection