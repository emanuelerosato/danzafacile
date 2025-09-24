# Piano di Refactoring - Gestione Sale (Rooms Management)

## 📊 Analisi Situazione Attuale

### File Analizzato
- **Path**: `/resources/views/admin/rooms/manage.blade.php`
- **Dimensione**: 340 righe
- **Complessità**: Alta (codice misto HTML/CSS/JS)
- **Priorità Refactoring**: 8/10 ⚠️

### Problemi Architetturali Identificati

#### 1. **Architettura JavaScript Antiquata**
```javascript
// ❌ PROBLEMA: Funzioni globali inline
function showEditRoomForm(roomId) { ... }
function showAddRoomForm() { ... }
function deleteRoom(roomId) { ... }

// ❌ PROBLEMA: Event handlers inline
onclick="showEditRoomForm({{ $room->id }})"
onclick="deleteRoom({{ $room->id }})"
```

#### 2. **Separazione Responsabilità Mancante**
- JavaScript misto nel template Blade (340 righe in un file)
- Logica UI accoppiata alla presentazione
- Nessuna modularità del codice

#### 3. **Pattern di Interazione Obsoleti**
```javascript
// ❌ PROBLEMA: Page reload invece di aggiornamenti dinamici
if (data.success) {
    location.reload();
}
```

#### 4. **Gestione Stati Non Dinamica**
- Form modale non reattivo
- Nessun feedback in tempo reale
- UI statica dopo operazioni CRUD

#### 5. **Non Conformità Design System**
- Layout non segue pattern standardizzato
- Componenti non allineati al design system
- Mancanza di consistency con altre sezioni

---

## 🎯 Obiettivi del Refactoring

### Architettura Target
Allineare la gestione sale agli standard moderni del sistema corsi:

1. **Modularità JavaScript** → ES6 Classes e Modules
2. **Separazione Responsabilità** → MVC pattern
3. **Interazioni Dinamiche** → No page reloads
4. **Design System Compliance** → Layout standardizzato
5. **User Experience** → Feedback real-time

---

## 🏗️ Piano di Implementazione Dettagliato

### **FASE 1: Preparazione Architettura (45 min)**

#### 1.1 Creazione Struttura JavaScript Modulare
```bash
# Nuovi file da creare:
resources/js/admin/rooms/
├── room-manager.js                 # Controller principale
├── modules/
│   ├── RoomFormManager.js         # Gestione form add/edit
│   ├── RoomListManager.js         # Gestione lista e filtering
│   └── RoomValidation.js          # Validazione form
```

#### 1.2 Aggiornamento Vite Configuration
```javascript
// vite.config.js - Aggiungere entry point
'resources/js/admin/rooms/room-manager.js'
```

### **FASE 2: Refactoring Template Blade (90 min)**

#### 2.1 Applicazione Design System Standard
```blade
<!-- Target Layout Pattern -->
<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Sale
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestisci le sale della tua scuola di danza
                </p>
            </div>
            <button id="add-room-btn" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg">
                <svg class="w-4 h-4 mr-2"><!-- Heroicon --></svg>
                Nuova Sala
            </button>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400"><!-- Arrow --></svg>
        </li>
        <li class="text-gray-900 font-medium">Gestione Sale</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <!-- Content -->
    </div>
</x-app-layout>
```

#### 2.2 Componenti UI Standardizzati
- **Stats Cards**: Totale sale, sale attive, capacità media
- **Filtri e Ricerca**: Search bar + filtri per stato/tipo
- **Lista Sale**: Cards responsive con azioni
- **Modal Form**: Add/Edit unificato

### **FASE 3: Implementazione JavaScript Moderno (120 min)**

#### 3.1 RoomManager Class (Controller Principale)
```javascript
// resources/js/admin/rooms/room-manager.js
import { RoomFormManager } from './modules/RoomFormManager.js';
import { RoomListManager } from './modules/RoomListManager.js';
import { RoomValidation } from './modules/RoomValidation.js';

class RoomManager {
    constructor() {
        this.formManager = new RoomFormManager();
        this.listManager = new RoomListManager();
        this.validation = new RoomValidation();
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadRoomsList();
    }

    bindEvents() {
        // Event delegation per performance
        document.addEventListener('click', this.handleGlobalClick.bind(this));
        document.addEventListener('submit', this.handleFormSubmit.bind(this));
    }

    handleGlobalClick(event) {
        const target = event.target.closest('[data-room-action]');
        if (!target) return;

        const action = target.dataset.roomAction;
        const roomId = target.dataset.roomId;

        switch (action) {
            case 'edit':
                this.editRoom(roomId);
                break;
            case 'delete':
                this.deleteRoom(roomId);
                break;
            case 'add':
                this.addRoom();
                break;
        }
    }

    async editRoom(roomId) {
        await this.formManager.showEditForm(roomId);
    }

    async deleteRoom(roomId) {
        if (await this.confirmDelete()) {
            await this.executeDelete(roomId);
            this.listManager.removeRoomFromDOM(roomId);
        }
    }
}

// Initialize
window.roomManager = new RoomManager();
```

#### 3.2 RoomFormManager Class
```javascript
// resources/js/admin/rooms/modules/RoomFormManager.js
export class RoomFormManager {
    constructor() {
        this.modal = null;
        this.form = null;
        this.initModal();
    }

    async showEditForm(roomId) {
        const roomData = await this.fetchRoomData(roomId);
        this.populateForm(roomData);
        this.modal.show();
    }

    async showAddForm() {
        this.resetForm();
        this.modal.show();
    }

    async submitForm(formData) {
        const isEdit = formData.has('id');
        const url = isEdit ? `/api/admin/rooms/${formData.get('id')}` : '/api/admin/rooms';
        const method = isEdit ? 'PUT' : 'POST';

        const response = await this.sendRequest(url, method, formData);

        if (response.success) {
            this.modal.hide();
            this.showSuccessMessage(response.message);
            return response.data;
        }
    }
}
```

#### 3.3 RoomListManager Class
```javascript
// resources/js/admin/rooms/modules/RoomListManager.js
export class RoomListManager {
    constructor() {
        this.container = document.getElementById('rooms-container');
        this.searchInput = document.getElementById('room-search');
        this.filters = document.querySelectorAll('[data-filter]');
        this.bindSearchEvents();
    }

    async loadRooms(filters = {}) {
        const params = new URLSearchParams(filters);
        const response = await fetch(`/api/admin/rooms?${params}`);
        const data = await response.json();

        this.renderRooms(data.rooms);
        this.updateStats(data.stats);
    }

    renderRooms(rooms) {
        this.container.innerHTML = rooms.map(room => this.roomCardTemplate(room)).join('');
    }

    roomCardTemplate(room) {
        return `
            <div class="bg-white rounded-lg shadow p-6" data-room-id="${room.id}">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">${room.name}</h3>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium ${room.active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'}">
                        ${room.active ? 'Attiva' : 'Non Attiva'}
                    </span>
                </div>

                <div class="space-y-2 text-sm text-gray-600 mb-4">
                    <p><strong>Capacità:</strong> ${room.capacity} persone</p>
                    <p><strong>Tipo:</strong> ${room.type}</p>
                    <p><strong>Attrezzature:</strong> ${room.equipment || 'Nessuna'}</p>
                </div>

                <div class="flex space-x-2">
                    <button data-room-action="edit" data-room-id="${room.id}"
                            class="flex-1 px-3 py-2 bg-blue-600 text-white text-sm rounded-md hover:bg-blue-700">
                        Modifica
                    </button>
                    <button data-room-action="delete" data-room-id="${room.id}"
                            class="px-3 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                        Elimina
                    </button>
                </div>
            </div>
        `;
    }

    addRoomToDOM(roomData) {
        const newCard = this.roomCardTemplate(roomData);
        this.container.insertAdjacentHTML('afterbegin', newCard);
    }

    updateRoomInDOM(roomData) {
        const existingCard = this.container.querySelector(`[data-room-id="${roomData.id}"]`);
        if (existingCard) {
            existingCard.outerHTML = this.roomCardTemplate(roomData);
        }
    }

    removeRoomFromDOM(roomId) {
        const card = this.container.querySelector(`[data-room-id="${roomId}"]`);
        if (card) {
            card.remove();
        }
    }
}
```

### **FASE 4: API Routes e Controller (60 min)**

#### 4.1 Nuove API Routes
```php
// routes/api.php - Aggiungere
Route::middleware(['auth'])->prefix('admin')->group(function () {
    Route::apiResource('rooms', Admin\RoomController::class);
    Route::get('rooms/search', [Admin\RoomController::class, 'search']);
});
```

#### 4.2 RoomController API Methods
```php
// app/Http/Controllers/Admin/RoomController.php
public function index(Request $request)
{
    $query = Room::where('school_id', auth()->user()->school_id);

    if ($request->has('search')) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    if ($request->has('active')) {
        $query->where('active', $request->boolean('active'));
    }

    $rooms = $query->orderBy('name')->get();

    $stats = [
        'total' => $rooms->count(),
        'active' => $rooms->where('active', true)->count(),
        'average_capacity' => $rooms->avg('capacity')
    ];

    return response()->json(['rooms' => $rooms, 'stats' => $stats]);
}

public function store(StoreRoomRequest $request)
{
    $room = Room::create($request->validated());
    return response()->json(['success' => true, 'data' => $room, 'message' => 'Sala creata con successo']);
}

public function update(UpdateRoomRequest $request, Room $room)
{
    $room->update($request->validated());
    return response()->json(['success' => true, 'data' => $room, 'message' => 'Sala aggiornata con successo']);
}

public function destroy(Room $room)
{
    $room->delete();
    return response()->json(['success' => true, 'message' => 'Sala eliminata con successo']);
}
```

### **FASE 5: Testing e Ottimizzazioni (30 min)**

#### 5.1 Test Funzionalità
- ✅ Creazione sala con validazione
- ✅ Modifica sala con aggiornamento dinamico UI
- ✅ Eliminazione con conferma
- ✅ Ricerca e filtri real-time
- ✅ Responsive design

#### 5.2 Performance Check
- Lazy loading per liste lunghe
- Debounce per search input
- Ottimizzazione query database

---

## ⏱️ Stima Tempi Implementazione

| Fase | Descrizione | Tempo Stimato | Priorità |
|------|-------------|---------------|----------|
| **Fase 1** | Preparazione architettura | 45 min | Alta |
| **Fase 2** | Refactoring template Blade | 90 min | Alta |
| **Fase 3** | JavaScript moderno | 120 min | Critica |
| **Fase 4** | API e Controller | 60 min | Media |
| **Fase 5** | Testing finale | 30 min | Media |
| | **TOTALE** | **5.75 ore** | |

---

## 🎯 Benefici Attesi Post-Refactoring

### **Tecnici**
- ✅ Architettura modulare e mantenibile
- ✅ Performance migliorata (no page reloads)
- ✅ Codice testabile e debuggabile
- ✅ Consistency con standard sistema

### **User Experience**
- ✅ Interazioni fluide e reattive
- ✅ Feedback real-time su operazioni
- ✅ UI moderna e professionale
- ✅ Mobile responsive

### **Manutenibilità**
- ✅ Separazione responsabilità chiara
- ✅ Codice riusabile per altre sezioni
- ✅ Pattern standardizzato replicabile
- ✅ Debug e troubleshooting semplificato

---

## 🚀 Implementazione Consigliata

### Ordine di Priorità
1. **Fase 3** (JavaScript) → Impatto più alto sull'architettura
2. **Fase 2** (Blade) → Allineamento design system
3. **Fase 1** (Setup) → Preparazione infrastruttura
4. **Fase 4** (API) → Completamento backend
5. **Fase 5** (Testing) → Validazione finale

### Note di Implementazione
- Testare ogni fase su ambiente di sviluppo
- Committare progressivamente per tracking
- Mantenere backward compatibility durante migrazione
- Documentare pattern per future sezioni

---

**CONCLUSIONE**: Il refactoring della gestione sale è **NECESSARIO e PRIORITARIO** per mantenere coerenza architetturale e offrire un'esperienza utente moderna e professionale.