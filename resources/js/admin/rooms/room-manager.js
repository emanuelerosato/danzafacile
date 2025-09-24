/**
 * RoomManager - Controller principale gestione sale
 * Mantiene funzionalità identiche preservando il comportamento esistente
 */
import { RoomFormManager } from './modules/RoomFormManager.js';
import { RoomListManager } from './modules/RoomListManager.js';
import { NotificationManager } from './modules/NotificationManager.js';

class RoomManager {
    constructor(roomsData, csrfToken) {
        this.formManager = new RoomFormManager();
        this.listManager = new RoomListManager(roomsData);
        this.notification = new NotificationManager();
        this.csrfToken = csrfToken;

        this.init();
    }

    /**
     * Inizializzazione
     */
    init() {
        this.bindEvents();
        this.setupFormCallback();
    }

    /**
     * Binding eventi globali
     */
    bindEvents() {
        this.formManager.bindEvents();

        // Event delegation per i pulsanti (sostituisce onclick inline)
        document.addEventListener('click', this.handleGlobalClick.bind(this));
    }

    /**
     * Gestore click globale con data attributes (elimina doppia gestione)
     */
    handleGlobalClick(event) {
        const target = event.target.closest('[data-room-action]');
        if (!target) return;

        event.preventDefault();

        const action = target.dataset.roomAction;
        const roomId = target.dataset.roomId;

        switch (action) {
            case 'add':
                this.showAddRoomForm();
                break;
            case 'edit':
                if (roomId) {
                    this.showEditRoomForm(parseInt(roomId));
                }
                break;
            case 'delete':
                if (roomId) {
                    this.deleteRoom(parseInt(roomId));
                }
                break;
            default:
                console.warn('⚠️ Unknown room action:', action);
        }
    }

    /**
     * Setup callback form submit
     */
    setupFormCallback() {
        this.formManager.setFormSubmitCallback((result) => {
            if (result.success) {
                this.notification.showSuccess(result.message);
                // Ricarica pagina (comportamento identico esistente)
                this.listManager.reloadPage();
            } else {
                this.notification.showError(result.message);
            }
        });
    }

    /**
     * Mostra form aggiunta sala (identico al comportamento esistente)
     */
    showAddRoomForm() {
        this.formManager.showAddForm();
    }

    /**
     * Mostra form modifica sala (identico al comportamento esistente)
     */
    showEditRoomForm(roomId) {
        const room = this.listManager.findRoom(roomId);
        if (!room) {
            console.error('❌ Room not found:', roomId);
            return;
        }

        this.formManager.showEditForm(roomId, room);
    }

    /**
     * Chiude modal (identico al comportamento esistente)
     */
    closeRoomModal() {
        this.formManager.closeModal();
    }

    /**
     * Elimina sala (identico al comportamento esistente)
     */
    async deleteRoom(roomId) {
        const room = this.listManager.findRoom(roomId);
        if (!room) return;

        if (!confirm(`Sei sicuro di voler eliminare la sala "${room.name}"?`)) {
            return;
        }

        try {
            const response = await fetch(`/admin/rooms/${roomId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                const message = data.message || 'Sala eliminata con successo';
                this.notification.showSuccess(message);
                this.listManager.removeRoomFromDOM(roomId);
            } else {
                const message = data.message || 'Errore durante l\'eliminazione';
                this.notification.showError(message);
            }
        } catch (error) {
            console.error('Error:', error);
            this.notification.showError('Errore di connessione');
        }
    }
}

// Esporta per uso globale (mantiene compatibilità esistente)
window.RoomManager = RoomManager;

// Espone funzioni globali per compatibilità con onclick esistenti
window.showAddRoomForm = function() {
    if (window.roomManager) {
        window.roomManager.showAddRoomForm();
    }
};

window.showEditRoomForm = function(roomId) {
    if (window.roomManager) {
        window.roomManager.showEditRoomForm(roomId);
    }
};

window.closeRoomModal = function() {
    if (window.roomManager) {
        window.roomManager.closeRoomModal();
    }
};

window.deleteRoom = function(roomId) {
    if (window.roomManager) {
        window.roomManager.deleteRoom(roomId);
    }
};

export default RoomManager;