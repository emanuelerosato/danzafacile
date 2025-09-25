<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettagli Evento
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione dettagli della tua scuola
                </p>
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
        <li class="text-gray-900 font-medium">Dettagli</li>
    </x-slot>



<div class="py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6 flex items-center justify-between">
            <div>
                <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ $event->name }}</h1>
                    @if($event->active)
                        <span class="px-2 py-1 text-xs font-semibold bg-green-100 text-green-800 rounded-full">Attivo</span>
                    @else
                        <span class="px-2 py-1 text-xs font-semibold bg-red-100 text-red-800 rounded-full">Non Attivo</span>
                    @endif
                    <span class="px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">{{ $event->type }}</span>
                </div>
                <p class="text-sm text-gray-600 mt-1">
                    Creato il {{ $event->created_at->format('d/m/Y H:i') }}
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                <a href="{{ route('admin.events.edit', $event) }}"
                   class="bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Modifica
                </a>
                <a href="{{ route('admin.events.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Torna agli Eventi
                </a>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-users text-blue-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Registrazioni Totali
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['total_registrations'] }}
                                    @if($event->max_participants)
                                        / {{ $event->max_participants }}
                                    @endif
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-check text-green-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Confermate
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['confirmed_registrations'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-clock text-yellow-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Lista d'Attesa
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    {{ $stats['waitlist_count'] }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-md rounded-lg">
                <div class="p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                                <i class="fas fa-euro-sign text-purple-600"></i>
                            </div>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dl>
                                <dt class="text-sm font-medium text-gray-500 truncate">
                                    Ricavi Totali
                                </dt>
                                <dd class="text-lg font-medium text-gray-900">
                                    €{{ number_format($stats['total_revenue'], 2, ',', '.') }}
                                </dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Event Details -->
            <div class="lg:col-span-2">
                <div class="bg-white shadow-md rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Dettagli Evento</h3>
                    </div>
                    <div class="px-6 py-4 space-y-4">
                        @if($event->description)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Descrizione</dt>
                                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-line">{{ $event->description }}</dd>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Data Inizio</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                    {{ $event->start_date->format('d/m/Y') }}
                                    <span class="ml-2">
                                        <i class="fas fa-clock mr-1 text-gray-400"></i>
                                        {{ $event->start_date->format('H:i') }}
                                    </span>
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Data Fine</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <i class="fas fa-calendar mr-2 text-gray-400"></i>
                                    {{ $event->end_date->format('d/m/Y') }}
                                    <span class="ml-2">
                                        <i class="fas fa-clock mr-1 text-gray-400"></i>
                                        {{ $event->end_date->format('H:i') }}
                                    </span>
                                </dd>
                            </div>
                        </div>

                        @if($event->location)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Luogo</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <i class="fas fa-map-marker-alt mr-2 text-gray-400"></i>
                                    {{ $event->location }}
                                </dd>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Prezzo</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <i class="fas fa-euro-sign mr-2 text-gray-400"></i>
                                    @if($event->price > 0)
                                        €{{ number_format($event->price, 2, ',', '.') }}
                                    @else
                                        Gratuito
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Posti Disponibili</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <i class="fas fa-chair mr-2 text-gray-400"></i>
                                    @if($event->max_participants)
                                        {{ $stats['available_spots'] }} / {{ $event->max_participants }}
                                    @else
                                        Illimitati
                                    @endif
                                </dd>
                            </div>
                        </div>

                        @if($event->requires_registration && $event->registration_deadline)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Scadenza Registrazione</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <i class="fas fa-calendar-times mr-2 text-gray-400"></i>
                                    {{ $event->registration_deadline->format('d/m/Y H:i') }}
                                </dd>
                            </div>
                        @endif

                        @if($event->requirements && count($event->requirements) > 0)
                            <div>
                                <dt class="text-sm font-medium text-gray-500 mb-2">Requisiti</dt>
                                <dd class="mt-1">
                                    <ul class="text-sm text-gray-900 space-y-1">
                                        @foreach($event->requirements as $requirement)
                                            <li class="flex items-start">
                                                <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5 text-xs"></i>
                                                {{ $requirement }}
                                            </li>
                                        @endforeach
                                    </ul>
                                </dd>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Visibilità</dt>
                                <dd class="mt-1 text-sm">
                                    @if($event->is_public)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="fas fa-globe mr-1"></i>
                                            Pubblico
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-lock mr-1"></i>
                                            Privato
                                        </span>
                                    @endif
                                </dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-gray-500">Registrazione</dt>
                                <dd class="mt-1 text-sm">
                                    @if($event->requires_registration)
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-user-plus mr-1"></i>
                                            Richiesta
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-user-minus mr-1"></i>
                                            Non Richiesta
                                        </span>
                                    @endif
                                </dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions & Registrations -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white shadow-md rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Azioni Rapide</h3>
                    </div>
                    <div class="p-6 space-y-3">
                        @if($event->requires_registration)
                            <button type="button" onclick="openRegisterModal()"
                                    class="w-full bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700 text-white px-4 py-2 rounded-lg transition-all duration-200">
                                <i class="fas fa-user-plus mr-2"></i>
                                Registra Utente
                            </button>
                        @endif

                        <button type="button" onclick="toggleEventStatus({{ $event->id }}, {{ $event->active ? 'false' : 'true' }})"
                                class="w-full {{ $event->active ? 'bg-red-100 hover:bg-red-200 text-red-700' : 'bg-green-100 hover:bg-green-200 text-green-700' }} px-4 py-2 rounded-lg transition-colors duration-200">
                            @if($event->active)
                                <i class="fas fa-pause mr-2"></i>
                                Disattiva Evento
                            @else
                                <i class="fas fa-play mr-2"></i>
                                Attiva Evento
                            @endif
                        </button>

                        <a href="{{ route('admin.events.export') }}?event_ids[]={{ $event->id }}"
                           class="w-full bg-blue-100 hover:bg-blue-200 text-blue-700 px-4 py-2 rounded-lg transition-colors duration-200 inline-block text-center">
                            <i class="fas fa-download mr-2"></i>
                            Esporta Dati
                        </a>

                        <button type="button" onclick="deleteEvent({{ $event->id }})"
                                class="w-full bg-red-100 hover:bg-red-200 text-red-700 px-4 py-2 rounded-lg transition-colors duration-200">
                            <i class="fas fa-trash mr-2"></i>
                            Elimina Evento
                        </button>
                    </div>
                </div>

                <!-- Recent Registrations -->
                @if($event->registrations->count() > 0)
                <div class="bg-white shadow-md rounded-lg">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Registrazioni Recenti</h3>
                    </div>
                    <div class="divide-y divide-gray-200 max-h-64 overflow-y-auto">
                        @foreach($event->registrations()->with('user')->latest()->take(5)->get() as $registration)
                            <div class="p-4 flex items-center justify-between">
                                <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                                    <div class="w-8 h-8 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                                        {{ strtoupper(substr($registration->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">{{ $registration->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $registration->created_at->format('d/m/Y H:i') }}</p>
                                    </div>
                                </div>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $registration->status === 'confirmed' ? 'bg-green-100 text-green-800' :
                                       ($registration->status === 'waitlist' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800') }}">
                                    {{ ucfirst($registration->status) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                    @if($event->registrations->count() > 5)
                        <div class="px-6 py-3 bg-gray-50 text-center">
                            <a href="#" class="text-sm text-blue-600 hover:text-blue-800">
                                Vedi tutte le registrazioni ({{ $event->registrations->count() }})
                            </a>
                        </div>
                    @endif
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Register User Modal -->
@if($event->requires_registration)
<div id="registerModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <h3 class="text-lg font-medium text-gray-900 text-center">Registra Utente all'Evento</h3>
            <form id="registerUserForm" class="mt-4">
                <div class="mb-4">
                    <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">Seleziona Utente</label>
                    <select id="user_id" name="user_id" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent" required>
                        <option value="">Seleziona utente...</option>
                        <!-- Users will be loaded via AJAX -->
                    </select>
                </div>
                <div class="mb-4">
                    <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">Note (opzionale)</label>
                    <textarea id="notes" name="notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent" placeholder="Note aggiuntive..."></textarea>
                </div>
                <div class="flex justify-end space-x-3">
                    <button type="button" onclick="closeRegisterModal()" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">
                        Annulla
                    </button>
                    <button type="submit" class="px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700 text-white rounded-md transition-all duration-200">
                        Registra
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
function openRegisterModal() {
    // Load users for selection
    fetch('{{ route("admin.users.index") }}?format=json')
        .then(response => response.json())
        .then(data => {
            const userSelect = document.getElementById('user_id');
            userSelect.innerHTML = '<option value="">Seleziona utente...</option>';

            if (data.data && data.data.data) {
                data.data.data.forEach(user => {
                    const option = document.createElement('option');
                    option.value = user.id;
                    option.textContent = `${user.name} (${user.email})`;
                    userSelect.appendChild(option);
                });
            }
        })
        .catch(error => {
            console.error('Error loading users:', error);
            showAlert('Errore nel caricamento degli utenti', 'error');
        });

    document.getElementById('registerModal').classList.remove('hidden');
}

function closeRegisterModal() {
    document.getElementById('registerModal').classList.add('hidden');
    document.getElementById('registerUserForm').reset();
}

// Handle registration form submission
document.getElementById('registerUserForm')?.addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);

    fetch('{{ route("admin.events.register-user", $event) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            closeRegisterModal();
            location.reload(); // Refresh to show new registration
        } else {
            showAlert(data.message || 'Errore durante la registrazione', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Errore durante la registrazione', 'error');
    });
});

function toggleEventStatus(eventId, newStatus) {
    if (!confirm(`Sei sicuro di voler ${newStatus ? 'attivare' : 'disattivare'} questo evento?`)) {
        return;
    }

    fetch(`/admin/events/${eventId}/toggle-active`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            location.reload();
        } else {
            showAlert(data.message || 'Errore durante l\'operazione', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Errore durante l\'operazione', 'error');
    });
}

function deleteEvent(eventId) {
    if (!confirm('Sei sicuro di voler eliminare questo evento? Questa azione non può essere annullata.')) {
        return;
    }

    fetch(`/admin/events/${eventId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showAlert(data.message, 'success');
            window.location.href = '{{ route("admin.events.index") }}';
        } else {
            showAlert(data.message || 'Errore durante l\'eliminazione', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('Errore durante l\'eliminazione', 'error');
    });
}

function showAlert(message, type = 'info') {
    // Simple alert for now - you can replace with a better notification system
    if (type === 'success') {
        alert('✅ ' + message);
    } else if (type === 'error') {
        alert('❌ ' + message);
    } else {
        alert('ℹ️ ' + message);
    }
}
</script>
</x-app-layout>
