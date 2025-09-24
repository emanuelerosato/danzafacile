/**
 * Status Manager
 * Gestisce le operazioni di cambio stato delle iscrizioni
 *
 * Funzionalità:
 * - Toggle status (active/cancelled/pending)
 * - Batch status updates
 * - Status validation
 * - UI feedback durante operazioni
 */

export default class StatusManager {
    constructor(apiService, notification) {
        this.apiService = apiService;
        this.notification = notification;
        this.processingIds = new Set();

        console.log('📊 StatusManager initialized');
    }

    /**
     * Toggle dello status di una singola iscrizione
     * @param {number} enrollmentId - ID dell'iscrizione
     * @param {string} currentStatus - Status attuale
     * @returns {Promise<boolean>} - Successo dell'operazione
     */
    async toggleStatus(enrollmentId, currentStatus = null) {
        if (this.processingIds.has(enrollmentId)) {
            console.warn('⚠️ Status change already in progress for:', enrollmentId);
            return false;
        }

        // Determina nuovo status
        const newStatus = this.getToggleStatus(currentStatus);
        const statusMessages = {
            'active': 'attivare',
            'cancelled': 'cancellare',
            'pending': 'mettere in attesa'
        };

        const confirmMessage = `Sei sicuro di voler ${statusMessages[newStatus] || 'modificare'} questa iscrizione?`;
        if (!confirm(confirmMessage)) {
            return false;
        }

        this.processingIds.add(enrollmentId);
        this.updateUIForProcessing(enrollmentId, true);

        try {
            console.log(`🔄 Toggling status for enrollment ${enrollmentId}: ${currentStatus} → ${newStatus}`);

            const result = await this.apiService.updateStatus(enrollmentId, { status: newStatus });

            if (result.success) {
                const successMessage = result.message || `Iscrizione ${statusMessages[newStatus]} con successo`;
                this.notification.showSuccess(successMessage);

                // Aggiorna UI immediatamente
                this.updateStatusInUI(enrollmentId, newStatus);
                return true;
            } else {
                this.notification.showError(result.message || 'Errore durante il cambio di status');
                return false;
            }
        } catch (error) {
            console.error('❌ Status toggle error:', error);
            this.notification.showError('Errore di connessione durante il cambio di status');
            return false;
        } finally {
            this.processingIds.delete(enrollmentId);
            this.updateUIForProcessing(enrollmentId, false);
        }
    }

    /**
     * Batch update degli status per più iscrizioni
     * @param {Array<number>} enrollmentIds - Array di ID delle iscrizioni
     * @param {string} newStatus - Nuovo status da applicare
     * @returns {Promise<boolean>} - Successo dell'operazione
     */
    async batchUpdateStatus(enrollmentIds, newStatus) {
        if (!enrollmentIds.length) {
            console.warn('⚠️ No enrollment IDs provided for batch update');
            return false;
        }

        const statusMessages = {
            'active': 'riattivare',
            'cancelled': 'cancellare',
            'pending': 'mettere in attesa'
        };

        const actionMessage = statusMessages[newStatus] || 'modificare';
        const confirmMessage = `Sei sicuro di voler ${actionMessage} ${enrollmentIds.length} iscrizioni?`;

        if (!confirm(confirmMessage)) {
            return false;
        }

        // Marca tutti gli ID come in elaborazione
        enrollmentIds.forEach(id => {
            this.processingIds.add(id);
            this.updateUIForProcessing(id, true);
        });

        try {
            console.log(`🔄 Batch status update: ${enrollmentIds.length} enrollments → ${newStatus}`);

            const result = await this.apiService.bulkAction({
                action: 'update_status',
                ids: enrollmentIds,
                status: newStatus
            });

            if (result.success) {
                const successMessage = result.message || `${enrollmentIds.length} iscrizioni ${statusMessages[newStatus]} con successo`;
                this.notification.showSuccess(successMessage);

                // Aggiorna UI per tutti gli elementi
                enrollmentIds.forEach(id => {
                    this.updateStatusInUI(id, newStatus);
                });

                return true;
            } else {
                this.notification.showError(result.message || 'Errore durante l\'aggiornamento batch');
                return false;
            }
        } catch (error) {
            console.error('❌ Batch status update error:', error);
            this.notification.showError('Errore di connessione durante l\'aggiornamento batch');
            return false;
        } finally {
            // Rimuovi tutti gli ID dalla lista di elaborazione
            enrollmentIds.forEach(id => {
                this.processingIds.delete(id);
                this.updateUIForProcessing(id, false);
            });
        }
    }

    /**
     * Determina il nuovo status per il toggle
     * @param {string} currentStatus - Status attuale
     * @returns {string} - Nuovo status
     */
    getToggleStatus(currentStatus) {
        const toggleMap = {
            'active': 'cancelled',
            'cancelled': 'active',
            'pending': 'active'
        };

        return toggleMap[currentStatus] || 'active';
    }

    /**
     * Aggiorna lo status nell'interfaccia utente
     * @param {number} enrollmentId - ID dell'iscrizione
     * @param {string} newStatus - Nuovo status
     */
    updateStatusInUI(enrollmentId, newStatus) {
        const row = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
        if (!row) return;

        // Aggiorna badge status
        const statusBadge = row.querySelector('.status-badge');
        if (statusBadge) {
            // Rimuovi classi esistenti
            statusBadge.className = statusBadge.className.replace(/(bg-\w+-100|text-\w+-800)/g, '');

            // Aggiungi nuove classi in base allo status
            const statusClasses = {
                'active': 'bg-green-100 text-green-800',
                'cancelled': 'bg-red-100 text-red-800',
                'pending': 'bg-yellow-100 text-yellow-800'
            };

            statusBadge.className += ` ${statusClasses[newStatus] || 'bg-gray-100 text-gray-800'}`;
            statusBadge.textContent = this.getStatusLabel(newStatus);
        }

        // Aggiorna pulsante toggle
        const toggleButton = row.querySelector('[data-enrollment-action="toggle-status"]');
        if (toggleButton) {
            this.updateToggleButton(toggleButton, newStatus);
        }

        console.log(`✅ UI updated for enrollment ${enrollmentId}: status → ${newStatus}`);
    }

    /**
     * Aggiorna UI durante elaborazione
     * @param {number} enrollmentId - ID dell'iscrizione
     * @param {boolean} processing - Se in elaborazione
     */
    updateUIForProcessing(enrollmentId, processing) {
        const row = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
        if (!row) return;

        const actionButtons = row.querySelectorAll('[data-enrollment-action]');
        actionButtons.forEach(button => {
            button.disabled = processing;
            if (processing) {
                button.style.opacity = '0.5';
                button.style.cursor = 'wait';
            } else {
                button.style.opacity = '';
                button.style.cursor = '';
            }
        });
    }

    /**
     * Aggiorna il pulsante di toggle in base allo status
     * @param {HTMLElement} button - Pulsante da aggiornare
     * @param {string} status - Status attuale
     */
    updateToggleButton(button, status) {
        const toggleConfig = {
            'active': {
                class: 'text-red-600 hover:text-red-900 hover:bg-red-100',
                title: 'Cancella iscrizione',
                icon: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z' // X circle
            },
            'cancelled': {
                class: 'text-green-600 hover:text-green-900 hover:bg-green-100',
                title: 'Riattiva iscrizione',
                icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' // Check circle
            },
            'pending': {
                class: 'text-green-600 hover:text-green-900 hover:bg-green-100',
                title: 'Attiva iscrizione',
                icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' // Check circle
            }
        };

        const config = toggleConfig[status] || toggleConfig['active'];

        // Aggiorna classi
        button.className = `p-2 rounded-full transition-colors duration-200 ${config.class}`;
        button.title = config.title;

        // Aggiorna icona SVG
        const svg = button.querySelector('svg path');
        if (svg) {
            svg.setAttribute('d', config.icon);
        }
    }

    /**
     * Ottieni label leggibile per lo status
     * @param {string} status - Status
     * @returns {string} - Label
     */
    getStatusLabel(status) {
        const labels = {
            'active': 'Attivo',
            'cancelled': 'Cancellato',
            'pending': 'In Attesa'
        };

        return labels[status] || status.charAt(0).toUpperCase() + status.slice(1);
    }

    /**
     * Verifica se un'iscrizione è in elaborazione
     * @param {number} enrollmentId - ID dell'iscrizione
     * @returns {boolean} - Se in elaborazione
     */
    isProcessing(enrollmentId) {
        return this.processingIds.has(enrollmentId);
    }

    /**
     * Pulisce lo stato interno
     */
    cleanup() {
        this.processingIds.clear();
        console.log('🧹 StatusManager cleaned up');
    }
}