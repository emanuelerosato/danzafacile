/**
 * Bulk Action Manager
 * Gestisce le operazioni bulk sulle iscrizioni
 *
 * Funzionalità:
 * - Selezione multipla con checkbox
 * - Azioni bulk (cancel, reactivate, delete, export)
 * - Progress tracking per operazioni lunghe
 * - Undo functionality per operazioni reversibili
 */

export default class BulkActionManager {
    constructor(apiService, notification, statusManager = null) {
        this.apiService = apiService;
        this.notification = notification;
        this.statusManager = statusManager;

        this.selectedIds = [];
        this.processing = false;
        this.lastAction = null;

        console.log('📦 BulkActionManager initialized');
    }

    /**
     * Aggiunge/rimuove un ID dalla selezione
     * @param {number} enrollmentId - ID dell'iscrizione
     * @param {boolean} selected - Se selezionato
     */
    toggleSelection(enrollmentId, selected) {
        if (selected) {
            if (!this.selectedIds.includes(enrollmentId)) {
                this.selectedIds.push(enrollmentId);
            }
        } else {
            this.selectedIds = this.selectedIds.filter(id => id !== enrollmentId);
        }

        this.updateSelectionUI();
        this.updateBulkActionsVisibility();

        console.log('🎯 Selection updated:', this.selectedIds);
    }

    /**
     * Seleziona/deseleziona tutti gli elementi
     * @param {boolean} selectAll - Se selezionare tutto
     */
    toggleSelectAll(selectAll) {
        const checkboxes = document.querySelectorAll('[data-enrollment-id] input[type="checkbox"]');

        if (selectAll) {
            // Seleziona tutti
            this.selectedIds = Array.from(checkboxes).map(checkbox => {
                const row = checkbox.closest('[data-enrollment-id]');
                const enrollmentId = parseInt(row.getAttribute('data-enrollment-id'));
                checkbox.checked = true;
                return enrollmentId;
            });
        } else {
            // Deseleziona tutti
            this.selectedIds = [];
            checkboxes.forEach(checkbox => checkbox.checked = false);
        }

        this.updateSelectionUI();
        this.updateBulkActionsVisibility();

        console.log('🎯 Select all toggled:', selectAll, this.selectedIds);
    }

    /**
     * Esegue un'azione bulk
     * @param {string} action - Tipo di azione (cancel, reactivate, delete, export)
     * @returns {Promise<boolean>} - Successo dell'operazione
     */
    async executeBulkAction(action) {
        if (!this.selectedIds.length) {
            this.notification.showError('Nessuna iscrizione selezionata');
            return false;
        }

        if (this.processing) {
            console.warn('⚠️ Bulk action already in progress');
            return false;
        }

        // Conferma operazione
        if (!this.confirmBulkAction(action)) {
            return false;
        }

        this.processing = true;
        this.updateProcessingUI(true);

        try {
            let result;

            switch (action) {
                case 'cancel':
                    result = await this.bulkUpdateStatus('cancelled');
                    break;
                case 'reactivate':
                    result = await this.bulkUpdateStatus('active');
                    break;
                case 'delete':
                    result = await this.bulkDelete();
                    break;
                case 'export':
                    result = await this.bulkExport();
                    break;
                default:
                    throw new Error(`Unknown bulk action: ${action}`);
            }

            if (result.success) {
                this.lastAction = {
                    action,
                    ids: [...this.selectedIds],
                    timestamp: Date.now()
                };

                // Reset selezione dopo successo
                this.clearSelection();

                // Ricarica pagina per operazioni distruttive
                if (['delete', 'cancel', 'reactivate'].includes(action)) {
                    setTimeout(() => location.reload(), 1500);
                }

                return true;
            }

            return false;
        } catch (error) {
            console.error('❌ Bulk action error:', error);
            this.notification.showError('Errore durante l\'operazione bulk');
            return false;
        } finally {
            this.processing = false;
            this.updateProcessingUI(false);
        }
    }

    /**
     * Bulk update dello status
     * @param {string} newStatus - Nuovo status
     * @returns {Promise<Object>} - Risultato dell'operazione
     */
    async bulkUpdateStatus(newStatus) {
        if (this.statusManager) {
            // Usa StatusManager se disponibile
            const success = await this.statusManager.batchUpdateStatus(this.selectedIds, newStatus);
            return { success, message: success ? 'Operazione completata' : 'Operazione fallita' };
        }

        // Fallback to direct API call
        return await this.apiService.bulkAction({
            action: 'update_status',
            ids: this.selectedIds,
            status: newStatus
        });
    }

    /**
     * Bulk delete delle iscrizioni
     * @returns {Promise<Object>} - Risultato dell'operazione
     */
    async bulkDelete() {
        const result = await this.apiService.bulkAction({
            action: 'delete',
            ids: this.selectedIds
        });

        if (result.success) {
            this.notification.showSuccess(result.message || `${this.selectedIds.length} iscrizioni eliminate con successo`);
        } else {
            this.notification.showError(result.message || 'Errore durante l\'eliminazione');
        }

        return result;
    }

    /**
     * Bulk export delle iscrizioni
     * @returns {Promise<Object>} - Risultato dell'operazione
     */
    async bulkExport() {
        const result = await this.apiService.bulkAction({
            action: 'export',
            ids: this.selectedIds
        });

        if (result.success) {
            this.notification.showSuccess(result.message || `Export di ${this.selectedIds.length} iscrizioni completato`);

            // Se il server restituisce un URL per il download
            if (result.downloadUrl) {
                window.open(result.downloadUrl, '_blank');
            }
        } else {
            this.notification.showError(result.message || 'Errore durante l\'export');
        }

        return result;
    }

    /**
     * Conferma dell'azione bulk
     * @param {string} action - Tipo di azione
     * @returns {boolean} - Se confermato
     */
    confirmBulkAction(action) {
        const count = this.selectedIds.length;
        const messages = {
            cancel: `Sei sicuro di voler cancellare ${count} iscrizioni?`,
            reactivate: `Sei sicuro di voler riattivare ${count} iscrizioni?`,
            delete: `⚠️ Sei sicuro di voler eliminare definitivamente ${count} iscrizioni?\n\nQuesta operazione non può essere annullata!`,
            export: `Procedere con l'export di ${count} iscrizioni?`
        };

        const message = messages[action] || `Procedere con l'operazione su ${count} iscrizioni?`;
        return confirm(message);
    }

    /**
     * Aggiorna UI durante elaborazione
     * @param {boolean} processing - Se in elaborazione
     */
    updateProcessingUI(processing) {
        const bulkPanel = document.querySelector('.bulk-actions-panel');
        if (!bulkPanel) return;

        const buttons = bulkPanel.querySelectorAll('button, select');
        buttons.forEach(element => {
            element.disabled = processing;
        });

        // Aggiorna classe del pannello per indicare elaborazione
        if (processing) {
            bulkPanel.classList.add('opacity-75');
        } else {
            bulkPanel.classList.remove('opacity-75');
        }
    }

    /**
     * Aggiorna UI della selezione
     */
    updateSelectionUI() {
        // Aggiorna contatore selezioni
        const counter = document.querySelector('.selection-counter');
        if (counter) {
            counter.textContent = `${this.selectedIds.length} selezionate`;
        }

        // Aggiorna stato checkbox "select all"
        const selectAllCheckbox = document.querySelector('#select-all-checkbox');
        if (selectAllCheckbox) {
            const totalRows = document.querySelectorAll('[data-enrollment-id]').length;
            selectAllCheckbox.checked = this.selectedIds.length === totalRows && totalRows > 0;
            selectAllCheckbox.indeterminate = this.selectedIds.length > 0 && this.selectedIds.length < totalRows;
        }

        // Aggiorna stile righe selezionate
        document.querySelectorAll('[data-enrollment-id]').forEach(row => {
            const enrollmentId = parseInt(row.getAttribute('data-enrollment-id'));
            const isSelected = this.selectedIds.includes(enrollmentId);

            if (isSelected) {
                row.classList.add('bg-blue-50', 'border-blue-200');
            } else {
                row.classList.remove('bg-blue-50', 'border-blue-200');
            }
        });
    }

    /**
     * Mostra/nasconde il pannello delle azioni bulk
     */
    updateBulkActionsVisibility() {
        const bulkPanel = document.querySelector('.bulk-actions-panel');
        if (!bulkPanel) return;

        if (this.selectedIds.length > 0) {
            bulkPanel.classList.remove('hidden');
            bulkPanel.classList.add('flex');
        } else {
            bulkPanel.classList.add('hidden');
            bulkPanel.classList.remove('flex');
        }
    }

    /**
     * Pulisce la selezione
     */
    clearSelection() {
        this.selectedIds = [];

        // Deseleziona tutti i checkbox
        document.querySelectorAll('[data-enrollment-id] input[type="checkbox"]').forEach(checkbox => {
            checkbox.checked = false;
        });

        const selectAllCheckbox = document.querySelector('#select-all-checkbox');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }

        this.updateSelectionUI();
        this.updateBulkActionsVisibility();

        console.log('🧹 Selection cleared');
    }

    /**
     * Ottieni statistiche sulla selezione corrente
     * @returns {Object} - Statistiche
     */
    getSelectionStats() {
        const stats = {
            total: this.selectedIds.length,
            statuses: {}
        };

        this.selectedIds.forEach(id => {
            const row = document.querySelector(`[data-enrollment-id="${id}"]`);
            if (row) {
                const statusBadge = row.querySelector('.status-badge');
                const status = statusBadge ? statusBadge.textContent.toLowerCase().trim() : 'unknown';
                stats.statuses[status] = (stats.statuses[status] || 0) + 1;
            }
        });

        return stats;
    }

    /**
     * Verifica se ci sono selezioni
     * @returns {boolean} - Se ci sono elementi selezionati
     */
    hasSelection() {
        return this.selectedIds.length > 0;
    }

    /**
     * Ottieni gli ID selezionati
     * @returns {Array<number>} - Array degli ID selezionati
     */
    getSelectedIds() {
        return [...this.selectedIds];
    }

    /**
     * Ottieni informazioni sull'ultima azione eseguita
     * @returns {Object|null} - Informazioni sull'ultima azione
     */
    getLastAction() {
        return this.lastAction;
    }

    /**
     * Pulisce lo stato interno
     */
    cleanup() {
        this.clearSelection();
        this.lastAction = null;
        this.processing = false;
        console.log('🧹 BulkActionManager cleaned up');
    }
}