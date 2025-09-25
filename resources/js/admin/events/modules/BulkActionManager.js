/**
 * BulkActionManager - Gestione azioni multiple per Events
 *
 * RESPONSABILITÀ:
 * - Gestire modal azioni multiple
 * - Eseguire azioni: attiva, disattiva, elimina, esporta
 * - Conferme per azioni distruttive
 * - Loading states durante operazioni
 * - Feedback all'utente
 */

export class BulkActionManager {
    constructor(eventsManager) {
        this.eventsManager = eventsManager;
        this.modal = null;
        this.isProcessing = false;

        // Action handlers
        this.actions = {
            'activate': this.activateEvents.bind(this),
            'deactivate': this.deactivateEvents.bind(this),
            'delete': this.deleteEvents.bind(this),
            'export': this.exportEvents.bind(this)
        };
    }

    init() {
        console.log('[BulkActionManager] ⚡ Initializing Bulk Action Manager...');
        this.setupModal();
        this.setupEventListeners();
        console.log('[BulkActionManager] ✅ Bulk Action Manager initialized');
    }

    setupModal() {
        this.modal = document.querySelector('[x-show="bulkActionModal"]');
        if (!this.modal) {
            console.error('[BulkActionManager] ❌ Bulk action modal not found');
            return;
        }
    }

    setupEventListeners() {
        // Close modal button
        const closeButton = this.modal?.querySelector('[\\@click="bulkActionModal = false"]');
        if (closeButton) {
            closeButton.addEventListener('click', () => {
                this.hideModal();
            });
        }

        // Action buttons
        const actionButtons = this.modal?.querySelectorAll('[\\@click^="performBulkAction"]');
        actionButtons?.forEach(button => {
            const action = this.extractActionFromClick(button.getAttribute('@click'));
            if (action) {
                button.addEventListener('click', () => {
                    this.performAction(action);
                });
            }
        });

        // Show modal button
        const showModalButton = document.querySelector('[\\@click="bulkActionModal = true"]');
        if (showModalButton) {
            showModalButton.addEventListener('click', () => {
                this.showModal();
            });
        }
    }

    extractActionFromClick(clickAttribute) {
        const match = clickAttribute.match(/performBulkAction\\('([^']+)'\\)/);
        return match ? match[1] : null;
    }

    showModal() {
        if (!this.modal) return;

        this.modal.classList.remove('hidden');
        this.modal.classList.add('flex');
        setTimeout(() => {
            this.modal.classList.add('opacity-100');
        }, 10);
    }

    hideModal() {
        if (!this.modal) return;

        this.modal.classList.remove('opacity-100');
        setTimeout(() => {
            this.modal.classList.add('hidden');
            this.modal.classList.remove('flex');
        }, 150);
    }

    async performAction(action, customSelectedItems = null) {
        const selectedItems = customSelectedItems || this.eventsManager.selectedItems;

        if (!selectedItems || selectedItems.length === 0) {
            document.dispatchEvent(new CustomEvent('events:notification', {
                detail: { message: 'Nessun evento selezionato', type: 'warning' }
            }));
            return;
        }

        if (this.isProcessing) {
            console.log('[BulkActionManager] ⏳ Action already in progress...');
            return;
        }

        try {
            console.log(`[BulkActionManager] 🚀 Performing ${action} on ${selectedItems.length} events`);

            this.isProcessing = true;
            this.showLoadingState(action);

            // Execute the appropriate action
            if (this.actions[action]) {
                await this.actions[action](selectedItems);
            } else {
                throw new Error(`Unknown action: ${action}`);
            }

            // Success cleanup
            this.eventsManager.hideBulkActionModal();
            this.clearSelection();
            await this.eventsManager.refreshTable();

        } catch (error) {
            console.error(`[BulkActionManager] ❌ Error performing ${action}:`, error);

            document.dispatchEvent(new CustomEvent('events:notification', {
                detail: {
                    message: `Errore durante l'operazione: ${error.message}`,
                    type: 'error'
                }
            }));
        } finally {
            this.isProcessing = false;
            this.hideLoadingState();
        }
    }

    async activateEvents(eventIds) {
        const response = await fetch('/admin/events/bulk-activate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ event_ids: eventIds })
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Errore durante l\'attivazione');
        }

        const result = await response.json();
        document.dispatchEvent(new CustomEvent('events:notification', {
            detail: { message: `${result.count} eventi attivati con successo`, type: 'success' }
        }));
    }

    async deactivateEvents(eventIds) {
        const response = await fetch('/admin/events/bulk-deactivate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ event_ids: eventIds })
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Errore durante la disattivazione');
        }

        const result = await response.json();
        document.dispatchEvent(new CustomEvent('events:notification', {
            detail: { message: `${result.count} eventi disattivati con successo`, type: 'success' }
        }));
    }

    async deleteEvents(eventIds) {
        // Confirmation for destructive action
        if (!confirm(`Sei sicuro di voler eliminare definitivamente ${eventIds.length} eventi? Questa azione non può essere annullata.`)) {
            throw new Error('Operazione annullata dall\'utente');
        }

        const response = await fetch('/admin/events/bulk-delete', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ event_ids: eventIds })
        });

        if (!response.ok) {
            const data = await response.json();
            throw new Error(data.message || 'Errore durante l\'eliminazione');
        }

        const result = await response.json();
        document.dispatchEvent(new CustomEvent('events:notification', {
            detail: { message: `${result.count} eventi eliminati con successo`, type: 'success' }
        }));
    }

    async exportEvents(eventIds) {
        try {
            const response = await fetch('/admin/events/bulk-export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ event_ids: eventIds })
            });

            if (!response.ok) {
                throw new Error('Errore durante l\'export');
            }

            // Handle file download
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = `eventi-selezionati-${new Date().toISOString().slice(0, 10)}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(url);

            document.dispatchEvent(new CustomEvent('events:notification', {
                detail: { message: `Export di ${eventIds.length} eventi completato`, type: 'success' }
            }));

        } catch (error) {
            throw new Error(`Errore durante l'export: ${error.message}`);
        }
    }

    clearSelection() {
        // Clear the events manager selection
        this.eventsManager.selectedItems = [];

        // Uncheck all checkboxes
        document.querySelectorAll('input[name="event_ids[]"]').forEach(checkbox => {
            checkbox.checked = false;
        });

        // Uncheck "select all" checkbox
        const selectAllCheckbox = document.querySelector('input[type="checkbox"][\\@change*="toggleAll"]');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
        }

        // Dispatch selection changed event
        document.dispatchEvent(new CustomEvent('events:selection-changed', {
            detail: { selectedItems: [] }
        }));
    }

    showLoadingState(action) {
        const actionButtons = this.modal?.querySelectorAll('[\\@click^="performBulkAction"]');

        // Disable all buttons
        actionButtons?.forEach(button => {
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
        });

        // Show loading state on the specific action button
        const actionButton = Array.from(actionButtons || []).find(button => {
            const buttonAction = this.extractActionFromClick(button.getAttribute('@click'));
            return buttonAction === action;
        });

        if (actionButton) {
            const originalText = actionButton.textContent;
            actionButton.setAttribute('data-original-text', originalText);
            actionButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Elaborazione...
            `;
        }
    }

    hideLoadingState() {
        const actionButtons = this.modal?.querySelectorAll('[\\@click^="performBulkAction"]');

        actionButtons?.forEach(button => {
            button.disabled = false;
            button.classList.remove('opacity-50', 'cursor-not-allowed');

            // Restore original text if available
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.textContent = originalText;
                button.removeAttribute('data-original-text');
            }
        });
    }

    // Get confirmation message based on action and count
    getConfirmationMessage(action, count) {
        const messages = {
            activate: `Attivare ${count} eventi selezionati?`,
            deactivate: `Disattivare ${count} eventi selezionati?`,
            delete: `Eliminare definitivamente ${count} eventi selezionati? Questa azione non può essere annullata.`,
            export: `Esportare ${count} eventi selezionati?`
        };

        return messages[action] || `Eseguire l'azione su ${count} eventi selezionati?`;
    }
}