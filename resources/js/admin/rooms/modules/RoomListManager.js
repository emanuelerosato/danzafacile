/**
 * RoomListManager - Gestione lista sale
 * Preserva funzionalità identiche al sistema esistente
 */
export class RoomListManager {
    constructor(roomsData) {
        this.roomsData = roomsData;
        this.container = document.getElementById('rooms-container');
    }

    /**
     * Trova una sala nei dati
     */
    findRoom(roomId) {
        return this.roomsData.find(r => r.id === parseInt(roomId));
    }

    /**
     * Rimuove una sala dal DOM (identico al comportamento esistente)
     */
    removeRoomFromDOM(roomId) {
        const roomElement = document.querySelector(`[data-room-id="${roomId}"]`);
        if (roomElement) {
            roomElement.remove();
            // Aggiorna i dati locali
            this.roomsData = this.roomsData.filter(r => r.id !== parseInt(roomId));
        }
    }

    /**
     * Aggiorna i dati delle sale
     */
    updateRoomsData(newRoomsData) {
        this.roomsData = newRoomsData;
    }

    /**
     * Ottiene i dati delle sale
     */
    getRoomsData() {
        return this.roomsData;
    }

    /**
     * Ricarica la pagina (comportamento identico esistente)
     * In futuro si potrà cambiare per aggiornamenti dinamici
     */
    reloadPage() {
        location.reload();
    }
}