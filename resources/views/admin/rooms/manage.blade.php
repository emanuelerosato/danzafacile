<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Sale
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestisci le sale della tua scuola
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
        <li class="text-gray-900 font-medium">Gestione Sale</li>
    </x-slot>

<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Gestione Sale</h1>
                <p class="text-gray-600">Tutte le sale della tua scuola di danza</p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                <button onclick="showAddRoomForm()"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuova Sala
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <!-- Rooms List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Sale Attive</h3>

            <div id="rooms-container" class="space-y-4">
                @forelse($rooms as $room)
                    <div class="room-item bg-gray-50 hover:bg-gray-100 rounded-lg p-4 border border-gray-200 transition-all duration-200" data-room-id="{{ $room->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-medium text-gray-900">{{ $room->name }}</h4>
                                @if($room->description)
                                    <p class="text-sm text-gray-600 mt-1">{{ $room->description }}</p>
                                @endif
                                <div class="flex items-center space-x-4 mt-2">
                                    @if($room->capacity)
                                        <span class="text-xs text-gray-500 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Capacità: {{ $room->capacity }} persone
                                        </span>
                                    @endif
                                    @if($room->equipment && count($room->equipment) > 0)
                                        <span class="text-xs text-gray-500 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                                            </svg>
                                            {{ implode(', ', $room->equipment) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <button onclick="showEditRoomForm({{ $room->id }})"
                                        class="inline-flex items-center px-3 py-1.5 text-sm text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Modifica
                                </button>
                                <button onclick="deleteRoom({{ $room->id }})"
                                        class="inline-flex items-center px-3 py-1.5 text-sm text-red-600 hover:text-red-800 hover:bg-red-50 rounded-lg transition-colors">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                    Elimina
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-2 4h2M9 15h2"/>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Nessuna sala configurata</h3>
                        <p class="text-gray-500 mb-4">Aggiungi la prima sala per iniziare</p>
                        <button onclick="showAddRoomForm()"
                                class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Aggiungi Prima Sala
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
</div>

<!-- Add/Edit Room Modal -->
<div id="roomModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Aggiungi Nuova Sala</h3>
                <button onclick="closeRoomModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="roomForm" onsubmit="submitRoomForm(event)">
                <input type="hidden" id="roomId" name="room_id">

                <div class="space-y-4">
                    <div>
                        <label for="roomName" class="block text-sm font-medium text-gray-700 mb-1">Nome Sala *</label>
                        <input type="text" id="roomName" name="name" required
                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                               placeholder="es. Sala A, Studio Principale">
                    </div>

                    <div>
                        <label for="roomDescription" class="block text-sm font-medium text-gray-700 mb-1">Descrizione</label>
                        <textarea id="roomDescription" name="description" rows="3"
                                  class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                  placeholder="Descrizione opzionale della sala"></textarea>
                    </div>

                    <div>
                        <label for="roomCapacity" class="block text-sm font-medium text-gray-700 mb-1">Capacità (persone)</label>
                        <input type="number" id="roomCapacity" name="capacity" min="1"
                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                               placeholder="es. 20">
                    </div>

                    <div>
                        <label for="roomEquipment" class="block text-sm font-medium text-gray-700 mb-1">Attrezzature</label>
                        <input type="text" id="roomEquipment" name="equipment"
                               class="w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                               placeholder="es. Specchi, Sbarre, Impianto audio (separati da virgola)">
                        <p class="text-xs text-gray-500 mt-1">Separa le attrezzature con virgole</p>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeRoomModal()"
                            class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                        Annulla
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-lg">
                        Salva Sala
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="notification" class="fixed top-4 right-4 transform translate-x-full transition-transform duration-300 z-50">
    <div class="bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
        <span id="notificationMessage">Operazione completata</span>
    </div>
</div>

<script>
let roomsData = @json($rooms);

// Show add room form
function showAddRoomForm() {
    document.getElementById('modalTitle').textContent = 'Aggiungi Nuova Sala';
    document.getElementById('roomForm').reset();
    document.getElementById('roomId').value = '';
    document.getElementById('roomModal').classList.remove('hidden');
    document.getElementById('roomModal').classList.add('flex');
}

// Show edit room form
function showEditRoomForm(roomId) {
    const room = roomsData.find(r => r.id === roomId);
    if (!room) {
        console.error('❌ Room not found:', roomId);
        return;
    }

    document.getElementById('modalTitle').textContent = 'Modifica Sala';
    document.getElementById('roomId').value = room.id;
    document.getElementById('roomName').value = room.name;
    document.getElementById('roomDescription').value = room.description || '';
    document.getElementById('roomCapacity').value = room.capacity || '';
    document.getElementById('roomEquipment').value = room.equipment ? room.equipment.join(', ') : '';

    document.getElementById('roomModal').classList.remove('hidden');
    document.getElementById('roomModal').classList.add('flex');
}

// Close modal
function closeRoomModal() {
    document.getElementById('roomModal').classList.add('hidden');
    document.getElementById('roomModal').classList.remove('flex');
}

// Submit form
function submitRoomForm(event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const roomId = formData.get('room_id');
    const isEdit = roomId && roomId !== '';

    // Convert equipment string to array
    const equipmentString = formData.get('equipment');
    const equipment = equipmentString ? equipmentString.split(',').map(item => item.trim()).filter(item => item) : [];

    const data = {
        name: formData.get('name'),
        description: formData.get('description'),
        capacity: formData.get('capacity') ? parseInt(formData.get('capacity')) : null,
        equipment: equipment,
        _token: '{{ csrf_token() }}'
    };

    if (isEdit) {
        data._method = 'PUT';
    }

    const url = isEdit ? `/admin/rooms/${roomId}` : '/admin/rooms';

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || (isEdit ? 'Sala aggiornata con successo' : 'Sala creata con successo'), 'success');
            closeRoomModal();
            location.reload(); // Reload to show updated list
        } else {
            showNotification(data.message || 'Errore durante il salvataggio', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Errore di connessione', 'error');
    });
}

// Delete room
function deleteRoom(roomId) {
    const room = roomsData.find(r => r.id === roomId);
    if (!room) return;

    if (!confirm(`Sei sicuro di voler eliminare la sala "${room.name}"?`)) {
        return;
    }

    fetch(`/admin/rooms/${roomId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(data.message || 'Sala eliminata con successo', 'success');
            document.querySelector(`[data-room-id="${roomId}"]`).remove();

            // Update roomsData
            roomsData = roomsData.filter(r => r.id !== roomId);
        } else {
            showNotification(data.message || 'Errore durante l\'eliminazione', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Errore di connessione', 'error');
    });
}

// Show notification
function showNotification(message, type = 'success') {
    const notification = document.getElementById('notification');
    const notificationMessage = document.getElementById('notificationMessage');

    notificationMessage.textContent = message;

    // Set color based on type
    if (type === 'error') {
        notification.querySelector('div').className = 'bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg';
    } else {
        notification.querySelector('div').className = 'bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg';
    }

    // Show notification
    notification.classList.remove('translate-x-full');

    // Auto hide after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
    }, 3000);
}

// Close modal on outside click
document.getElementById('roomModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRoomModal();
    }
});
</script>
</x-app-layout>