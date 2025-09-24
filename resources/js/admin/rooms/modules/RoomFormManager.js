/**
 * RoomFormManager - Gestione form add/edit sale
 * Preserva funzionalità identiche al sistema esistente
 */
export class RoomFormManager {
    constructor() {
        this.modal = document.getElementById('roomModal');
        this.form = document.getElementById('roomForm');
        this.modalTitle = document.getElementById('modalTitle');
        this.roomIdField = document.getElementById('roomId');

        // Form fields
        this.nameField = document.getElementById('roomName');
        this.descriptionField = document.getElementById('roomDescription');
        this.capacityField = document.getElementById('roomCapacity');
        this.equipmentField = document.getElementById('roomEquipment');

        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    /**
     * Mostra form per aggiungere nuova sala
     */
    showAddForm() {
        this.modalTitle.textContent = 'Aggiungi Nuova Sala';
        this.form.reset();
        this.roomIdField.value = '';
        this.showModal();
    }

    /**
     * Mostra form per modificare sala esistente
     */
    showEditForm(roomId, roomData) {
        this.modalTitle.textContent = 'Modifica Sala';
        this.roomIdField.value = roomId;
        this.nameField.value = roomData.name;
        this.descriptionField.value = roomData.description || '';
        this.capacityField.value = roomData.capacity || '';
        this.equipmentField.value = roomData.equipment ? roomData.equipment.join(', ') : '';
        this.showModal();
    }

    /**
     * Mostra il modal
     */
    showModal() {
        this.modal.classList.remove('hidden');
        this.modal.classList.add('flex');
    }

    /**
     * Chiude il modal
     */
    closeModal() {
        this.modal.classList.add('hidden');
        this.modal.classList.remove('flex');
    }

    /**
     * Sottomette il form (identico al comportamento esistente)
     */
    async submitForm(event) {
        event.preventDefault();

        const formData = new FormData(event.target);
        const roomId = formData.get('room_id');
        const isEdit = roomId && roomId !== '';

        // Convert equipment string to array (identico al sistema esistente)
        const equipmentString = formData.get('equipment');
        const equipment = equipmentString ?
            equipmentString.split(',').map(item => item.trim()).filter(item => item) : [];

        const data = {
            name: formData.get('name'),
            description: formData.get('description'),
            capacity: formData.get('capacity') ? parseInt(formData.get('capacity')) : null,
            equipment: equipment,
            _token: this.csrfToken
        };

        if (isEdit) {
            data._method = 'PUT';
        }

        const url = isEdit ? `/admin/rooms/${roomId}` : '/admin/rooms';

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken
                },
                body: JSON.stringify(data)
            });

            const responseData = await response.json();

            if (responseData.success) {
                const message = responseData.message ||
                    (isEdit ? 'Sala aggiornata con successo' : 'Sala creata con successo');

                this.closeModal();
                return { success: true, message, data: responseData.data };
            } else {
                const message = responseData.message || 'Errore durante il salvataggio';
                return { success: false, message };
            }
        } catch (error) {
            console.error('Error:', error);
            return { success: false, message: 'Errore di connessione' };
        }
    }

    /**
     * Inizializza eventi del form
     */
    bindEvents() {
        // Submit handler
        this.form.addEventListener('submit', async (event) => {
            const result = await this.submitForm(event);
            // Il risultato sarà gestito dal RoomManager principale
            if (this.onFormSubmit) {
                this.onFormSubmit(result);
            }
        });

        // Close modal on outside click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.closeModal();
            }
        });
    }

    /**
     * Callback per gestire risultato submit
     */
    setFormSubmitCallback(callback) {
        this.onFormSubmit = callback;
    }
}