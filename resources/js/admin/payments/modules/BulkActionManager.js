/**
 * BulkActionManager.js
 *
 * Manages bulk operations on payments
 * Handles selection management, bulk actions execution, and progress tracking
 *
 * @version 1.0.0
 */

export class BulkActionManager {
    constructor(options = {}) {
        this.options = {
            csrfToken: null,
            routes: {},
            batchSize: 10,
            enableProgressTracking: true,
            onStateChange: null,
            onProgress: null,
            onComplete: null,
            debug: false,
            ...options
        };

        this.state = {
            selectedPayments: [],
            isProcessing: false,
            currentAction: null,
            progress: {
                total: 0,
                completed: 0,
                failed: 0,
                errors: []
            }
        };

        this.elements = {};
        this.availableActions = this.getAvailableActions();

        this.init();
    }

    /**
     * Initialize the BulkActionManager
     */
    init() {
        console.log('[BulkActionManager] 🔄 Initializing Bulk Action Manager');

        this.cacheElements();
        this.attachEventListeners();
        this.updateUI();

        console.log('[BulkActionManager] ✅ Bulk Action Manager initialized');
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements = {
            bulkActionBtn: document.getElementById('bulkActionBtn'),
            bulkDropdown: document.getElementById('bulkDropdown'),
            bulkModal: document.getElementById('bulkModal'),
            progressModal: document.getElementById('progressModal'),
            progressBar: document.querySelector('[data-progress-bar]'),
            progressText: document.querySelector('[data-progress-text]'),
            selectionCount: document.querySelector('[data-selection-count]'),
            actionButtons: document.querySelectorAll('#bulkDropdown a[data-action]'),
            confirmModal: document.getElementById('confirmModal'),
            confirmText: document.querySelector('[data-confirm-text]'),
            confirmBtn: document.querySelector('[data-confirm-action]'),
            cancelBtn: document.querySelector('[data-cancel-action]')
        };

        console.log('[BulkActionManager] 🎯 Elements cached');
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Bulk action buttons
        this.elements.actionButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const action = button.dataset.action;
                this.handleBulkAction(action);
            });
        });

        // Confirm modal buttons
        if (this.elements.confirmBtn) {
            this.elements.confirmBtn.addEventListener('click', () => {
                this.executeBulkAction();
            });
        }

        if (this.elements.cancelBtn) {
            this.elements.cancelBtn.addEventListener('click', () => {
                this.closeBulkModal();
            });
        }

        console.log('[BulkActionManager] 🎧 Event listeners attached');
    }

    /**
     * Update selection with new payment IDs
     */
    updateSelection(paymentIds) {
        this.state.selectedPayments = paymentIds;
        this.updateUI();

        // Notify parent component
        if (this.options.onStateChange) {
            this.options.onStateChange({
                selectedPayments: this.state.selectedPayments,
                count: paymentIds.length
            });
        }
    }

    /**
     * Handle bulk action initiation
     */
    handleBulkAction(action) {
        if (this.state.selectedPayments.length === 0) {
            this.showNotification('Seleziona almeno un pagamento', 'warning');
            return;
        }

        const actionConfig = this.availableActions[action];
        if (!actionConfig) {
            console.error('[BulkActionManager] Unknown action:', action);
            return;
        }

        this.state.currentAction = action;

        // Show confirmation if required
        if (actionConfig.requiresConfirmation) {
            this.showConfirmationModal(actionConfig);
        } else {
            this.executeBulkAction();
        }
    }

    /**
     * Show confirmation modal
     */
    showConfirmationModal(actionConfig) {
        const count = this.state.selectedPayments.length;
        const message = actionConfig.confirmationMessage.replace('{count}', count);

        if (this.elements.confirmModal) {
            // Use custom modal
            if (this.elements.confirmText) {
                this.elements.confirmText.textContent = message;
            }
            this.elements.confirmModal.classList.remove('hidden');
        } else {
            // Fallback to native confirm
            if (confirm(message)) {
                this.executeBulkAction();
            }
        }
    }

    /**
     * Execute bulk action
     */
    async executeBulkAction() {
        if (!this.state.currentAction || this.state.selectedPayments.length === 0) {
            return;
        }

        this.state.isProcessing = true;
        this.state.progress = {
            total: this.state.selectedPayments.length,
            completed: 0,
            failed: 0,
            errors: []
        };

        this.closeBulkModal();
        this.showProgressModal();
        this.updateUI();

        try {
            await this.processBulkAction();
        } catch (error) {
            console.error('[BulkActionManager] Bulk action failed:', error);
            this.showNotification('Errore durante l\'operazione', 'error');
        } finally {
            this.state.isProcessing = false;
            this.updateUI();
        }
    }

    /**
     * Process bulk action in batches
     */
    async processBulkAction() {
        const paymentIds = [...this.state.selectedPayments];
        const action = this.state.currentAction;
        const batchSize = this.options.batchSize;

        // Process in batches to avoid overwhelming the server
        for (let i = 0; i < paymentIds.length; i += batchSize) {
            const batch = paymentIds.slice(i, i + batchSize);

            try {
                await this.executeBatch(action, batch);
                this.state.progress.completed += batch.length;
            } catch (error) {
                console.error('[BulkActionManager] Batch failed:', error);
                this.state.progress.failed += batch.length;
                this.state.progress.errors.push({
                    batch: batch,
                    error: error.message
                });
            }

            this.updateProgress();

            // Small delay between batches to prevent overwhelming the server
            if (i + batchSize < paymentIds.length) {
                await this.delay(100);
            }
        }

        // Show completion message
        this.showCompletionMessage();

        // Auto-close progress modal after delay
        setTimeout(() => {
            this.closeProgressModal();
            this.reloadPage();
        }, 2000);
    }

    /**
     * Execute a single batch
     */
    async executeBatch(action, paymentIds) {
        const response = await fetch('/admin/payments/bulk-action', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.options.csrfToken
            },
            body: JSON.stringify({
                action: action,
                payment_ids: paymentIds
            })
        });

        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'Operazione fallita');
        }

        return data;
    }

    /**
     * Update progress display
     */
    updateProgress() {
        const { total, completed, failed } = this.state.progress;
        const percentage = Math.round(((completed + failed) / total) * 100);

        if (this.elements.progressBar) {
            this.elements.progressBar.style.width = `${percentage}%`;
        }

        if (this.elements.progressText) {
            this.elements.progressText.textContent =
                `Elaborati: ${completed + failed} di ${total} (${percentage}%)`;
        }

        // Notify parent component
        if (this.options.onProgress) {
            this.options.onProgress({
                total,
                completed,
                failed,
                percentage
            });
        }
    }

    /**
     * Show completion message
     */
    showCompletionMessage() {
        const { total, completed, failed } = this.state.progress;

        let message = '';
        if (failed === 0) {
            message = `✅ Operazione completata con successo! ${completed} pagamenti elaborati.`;
        } else {
            message = `⚠️ Operazione completata con alcuni errori. ${completed} successi, ${failed} errori.`;
        }

        if (this.elements.progressText) {
            this.elements.progressText.textContent = message;
        }

        // Notify parent component
        if (this.options.onComplete) {
            this.options.onComplete({
                total,
                completed,
                failed,
                errors: this.state.progress.errors
            });
        }
    }

    /**
     * Show bulk modal
     */
    openBulkModal() {
        if (this.state.selectedPayments.length === 0) {
            this.showNotification('Seleziona almeno un pagamento', 'warning');
            return;
        }

        if (this.elements.bulkModal) {
            this.elements.bulkModal.classList.remove('hidden');
        } else {
            // Fallback: show dropdown
            if (this.elements.bulkDropdown) {
                this.elements.bulkDropdown.classList.remove('hidden');
            }
        }
    }

    /**
     * Close bulk modal
     */
    closeBulkModal() {
        if (this.elements.bulkModal) {
            this.elements.bulkModal.classList.add('hidden');
        }

        if (this.elements.confirmModal) {
            this.elements.confirmModal.classList.add('hidden');
        }

        this.state.currentAction = null;
    }

    /**
     * Show progress modal
     */
    showProgressModal() {
        if (this.elements.progressModal) {
            this.elements.progressModal.classList.remove('hidden');
        }
    }

    /**
     * Close progress modal
     */
    closeProgressModal() {
        if (this.elements.progressModal) {
            this.elements.progressModal.classList.add('hidden');
        }
    }

    /**
     * Update UI based on current state
     */
    updateUI() {
        const hasSelection = this.state.selectedPayments.length > 0;

        // Update bulk action button
        if (this.elements.bulkActionBtn) {
            this.elements.bulkActionBtn.disabled = !hasSelection || this.state.isProcessing;
        }

        // Update selection count
        if (this.elements.selectionCount) {
            this.elements.selectionCount.textContent = this.state.selectedPayments.length;
        }

        // Update action buttons
        this.elements.actionButtons.forEach(button => {
            button.style.pointerEvents = this.state.isProcessing ? 'none' : 'auto';
            button.style.opacity = this.state.isProcessing ? '0.5' : '1';
        });
    }

    /**
     * Get available bulk actions
     */
    getAvailableActions() {
        return {
            mark_completed: {
                label: 'Segna come Completati',
                icon: 'check-circle',
                confirmationMessage: 'Sei sicuro di voler segnare {count} pagamenti come completati?',
                requiresConfirmation: true,
                destructive: false
            },
            mark_pending: {
                label: 'Segna come In Attesa',
                icon: 'clock',
                confirmationMessage: 'Sei sicuro di voler segnare {count} pagamenti come in attesa?',
                requiresConfirmation: true,
                destructive: false
            },
            send_receipts: {
                label: 'Invia Ricevute',
                icon: 'mail',
                confirmationMessage: 'Sei sicuro di voler inviare le ricevute per {count} pagamenti?',
                requiresConfirmation: true,
                destructive: false
            },
            delete: {
                label: 'Elimina Pagamenti',
                icon: 'trash',
                confirmationMessage: 'ATTENZIONE: Sei sicuro di voler eliminare {count} pagamenti? Questa azione non può essere annullata.',
                requiresConfirmation: true,
                destructive: true
            }
        };
    }

    /**
     * Add custom bulk action
     */
    addCustomAction(key, config) {
        this.availableActions[key] = {
            requiresConfirmation: true,
            destructive: false,
            ...config
        };
    }

    /**
     * Remove bulk action
     */
    removeAction(key) {
        delete this.availableActions[key];
    }

    /**
     * Check if action is available
     */
    isActionAvailable(action) {
        return this.availableActions.hasOwnProperty(action);
    }

    /**
     * Get selection statistics
     */
    getSelectionStats() {
        return {
            count: this.state.selectedPayments.length,
            paymentIds: [...this.state.selectedPayments],
            isProcessing: this.state.isProcessing,
            currentAction: this.state.currentAction
        };
    }

    /**
     * Clear selection
     */
    clearSelection() {
        this.updateSelection([]);
    }

    /**
     * Select all visible payments
     */
    selectAll(paymentIds) {
        this.updateSelection(paymentIds);
    }

    /**
     * Utility methods
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    showNotification(message, type = 'info') {
        console.log(`[BulkActionManager] ${type.toUpperCase()}: ${message}`);

        // For now, use alert - can be enhanced with a proper notification system
        if (type === 'error' || type === 'warning') {
            alert(message);
        }
    }

    reloadPage() {
        location.reload();
    }

    /**
     * Export state for debugging
     */
    exportState() {
        return {
            selectedPayments: [...this.state.selectedPayments],
            isProcessing: this.state.isProcessing,
            currentAction: this.state.currentAction,
            progress: { ...this.state.progress },
            availableActions: { ...this.availableActions }
        };
    }

    /**
     * Get debug information
     */
    getDebugInfo() {
        return {
            state: this.state,
            options: this.options,
            availableActions: this.availableActions,
            elements: Object.keys(this.elements).reduce((acc, key) => {
                acc[key] = !!this.elements[key];
                return acc;
            }, {})
        };
    }

    /**
     * Destroy the bulk action manager
     */
    destroy() {
        this.state.selectedPayments = [];
        this.state.isProcessing = false;
        console.log('[BulkActionManager] 🗑️ Bulk Action Manager destroyed');
    }
}

export default BulkActionManager;