/**
 * BulkActionManager - Handles bulk operations on selected registrations
 *
 * Features:
 * - Multiple bulk actions (confirm, waitlist, cancel, mark_attended)
 * - Action validation and confirmation
 * - Progress indication
 * - Error handling with rollback
 */

export default class BulkActionManager {
    constructor(options = {}) {
        this.options = {
            actionsSelector: '[data-bulk-action]',
            onBulkAction: () => {},
            selectionManager: null,
            confirmActions: ['cancel', 'delete'],
            ...options
        };

        this.availableActions = {
            'confirm': {
                label: 'Conferma',
                icon: '✅',
                color: 'green',
                description: 'Conferma le registrazioni selezionate'
            },
            'waitlist': {
                label: 'Lista Attesa',
                icon: '⏳',
                color: 'yellow',
                description: 'Sposta in lista d\'attesa'
            },
            'cancel': {
                label: 'Annulla',
                icon: '❌',
                color: 'red',
                description: 'Annulla le registrazioni selezionate'
            },
            'mark_attended': {
                label: 'Partecipato',
                icon: '🎯',
                color: 'purple',
                description: 'Segna come partecipato'
            }
        };

        this.isProcessing = false;
        this.currentAction = null;

        this.init();
        console.log('[BulkActionManager] ✅ Bulk action manager initialized');
    }

    /**
     * Initialize bulk action manager
     */
    init() {
        this.bindEvents();
        this.createActionButtons();
    }

    /**
     * Bind bulk action events
     */
    bindEvents() {
        // Use event delegation for dynamic content
        document.addEventListener('click', (e) => {
            const actionButton = e.target.closest('[data-bulk-action]');
            if (actionButton) {
                e.preventDefault();
                const action = actionButton.dataset.bulkAction;
                this.handleBulkAction(action);
            }
        });

        // Listen for selection changes to update button states
        document.addEventListener('eventRegistration:selectionChanged', (e) => {
            this.updateActionButtons(e.detail.selectionInfo);
        });

        console.log('[BulkActionManager] 🎯 Event listeners attached');
    }

    /**
     * Create action buttons dynamically
     */
    createActionButtons() {
        const bulkActionsContainer = document.querySelector('#bulkActions .flex');
        if (!bulkActionsContainer) return;

        // Clear existing action buttons (keep selected count)
        const actionButtons = bulkActionsContainer.querySelectorAll('[data-bulk-action]');
        actionButtons.forEach(button => button.remove());

        // Create buttons for each available action
        Object.entries(this.availableActions).forEach(([actionKey, actionConfig]) => {
            const button = this.createActionButton(actionKey, actionConfig);
            bulkActionsContainer.appendChild(button);
        });

        console.log('[BulkActionManager] 🔘 Action buttons created');
    }

    /**
     * Create individual action button
     */
    createActionButton(actionKey, config) {
        const button = document.createElement('button');
        button.setAttribute('data-bulk-action', actionKey);
        button.className = `px-3 py-1 bg-${config.color}-600 text-white text-sm rounded hover:bg-${config.color}-700 transition-colors duration-200 flex items-center space-x-1 disabled:opacity-50 disabled:cursor-not-allowed`;
        button.title = config.description;

        button.innerHTML = `
            <span class="text-xs">${config.icon}</span>
            <span>${config.label}</span>
        `;

        return button;
    }

    /**
     * Handle bulk action execution
     */
    async handleBulkAction(action) {
        if (this.isProcessing) {
            console.warn('[BulkActionManager] ⚠️ Action already in progress');
            return;
        }

        const selectedItems = this.getSelectedItems();
        if (selectedItems.length === 0) {
            this.showError('Seleziona almeno una registrazione');
            return;
        }

        const actionConfig = this.availableActions[action];
        if (!actionConfig) {
            this.showError(`Azione "${action}" non supportata`);
            return;
        }

        // Confirm action if required
        if (this.options.confirmActions.includes(action)) {
            const confirmed = await this.confirmAction(action, selectedItems, actionConfig);
            if (!confirmed) return;
        }

        await this.executeAction(action, selectedItems, actionConfig);
    }

    /**
     * Confirm action with user
     */
    async confirmAction(action, selectedItems, config) {
        const count = selectedItems.length;
        const itemText = count === 1 ? 'registrazione' : 'registrazioni';

        const message = `Sei sicuro di voler ${config.label.toLowerCase()} ${count} ${itemText}?`;

        return new Promise((resolve) => {
            // Create custom confirmation modal
            this.showConfirmationModal({
                title: `Conferma ${config.label}`,
                message: message,
                action: config.label,
                icon: config.icon,
                color: config.color,
                onConfirm: () => resolve(true),
                onCancel: () => resolve(false)
            });
        });
    }

    /**
     * Execute bulk action
     */
    async executeAction(action, selectedItems, config) {
        this.isProcessing = true;
        this.currentAction = action;

        try {
            // Update UI to show processing state
            this.showProcessingState(config, selectedItems.length);

            // Execute the action
            await this.options.onBulkAction(action, selectedItems);

            // Show success message
            this.showSuccess(`${config.label} applicato con successo a ${selectedItems.length} registrazioni`);

            console.log(`[BulkActionManager] ✅ Bulk action "${action}" completed for ${selectedItems.length} items`);

        } catch (error) {
            console.error(`[BulkActionManager] ❌ Bulk action "${action}" failed:`, error);
            this.showError(`Errore durante ${config.label.toLowerCase()}: ${error.message}`);
        } finally {
            this.isProcessing = false;
            this.currentAction = null;
            this.hideProcessingState();
        }
    }

    /**
     * Show processing state
     */
    showProcessingState(config, itemCount) {
        const bulkActions = document.querySelector('#bulkActions');
        if (!bulkActions) return;

        // Disable all action buttons
        const actionButtons = bulkActions.querySelectorAll('[data-bulk-action]');
        actionButtons.forEach(button => {
            button.disabled = true;
            button.classList.add('opacity-50', 'cursor-not-allowed');
        });

        // Show processing indicator
        const processingIndicator = document.createElement('div');
        processingIndicator.id = 'bulk-processing';
        processingIndicator.className = 'flex items-center space-x-2 ml-4';
        processingIndicator.innerHTML = `
            <div class="animate-spin rounded-full h-4 w-4 border-2 border-b-transparent border-${config.color}-600"></div>
            <span class="text-sm text-${config.color}-600 font-medium">
                ${config.label} in corso... (${itemCount} elementi)
            </span>
        `;

        bulkActions.querySelector('.flex').appendChild(processingIndicator);
    }

    /**
     * Hide processing state
     */
    hideProcessingState() {
        // Remove processing indicator
        const processingIndicator = document.querySelector('#bulk-processing');
        if (processingIndicator) {
            processingIndicator.remove();
        }

        // Re-enable action buttons
        const bulkActions = document.querySelector('#bulkActions');
        if (bulkActions) {
            const actionButtons = bulkActions.querySelectorAll('[data-bulk-action]');
            actionButtons.forEach(button => {
                button.disabled = false;
                button.classList.remove('opacity-50', 'cursor-not-allowed');
            });
        }
    }

    /**
     * Update action buttons based on selection
     */
    updateActionButtons(selectionInfo) {
        const actionButtons = document.querySelectorAll('[data-bulk-action]');

        actionButtons.forEach(button => {
            const action = button.dataset.bulkAction;
            const isDisabled = selectionInfo.count === 0 || this.isProcessing;

            button.disabled = isDisabled;
            button.classList.toggle('opacity-50', isDisabled);
            button.classList.toggle('cursor-not-allowed', isDisabled);
        });
    }

    /**
     * Show confirmation modal
     */
    showConfirmationModal(options) {
        // Remove existing modal
        const existingModal = document.querySelector('#bulk-confirmation-modal');
        if (existingModal) {
            existingModal.remove();
        }

        const modal = document.createElement('div');
        modal.id = 'bulk-confirmation-modal';
        modal.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center';

        modal.innerHTML = `
            <div class="relative bg-white rounded-lg shadow-xl max-w-md mx-4">
                <div class="p-6">
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-${options.color}-100 rounded-lg flex items-center justify-center mr-4">
                            <span class="text-2xl">${options.icon}</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900">${options.title}</h3>
                    </div>

                    <p class="text-gray-600 mb-6">${options.message}</p>

                    <div class="flex justify-end space-x-3">
                        <button id="bulk-cancel" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                            Annulla
                        </button>
                        <button id="bulk-confirm" class="px-4 py-2 bg-${options.color}-600 text-white rounded-lg hover:bg-${options.color}-700 transition-colors duration-200">
                            ${options.action}
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Bind modal events
        modal.querySelector('#bulk-cancel').addEventListener('click', () => {
            modal.remove();
            options.onCancel();
        });

        modal.querySelector('#bulk-confirm').addEventListener('click', () => {
            modal.remove();
            options.onConfirm();
        });

        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
                options.onCancel();
            }
        });

        // Close on Escape key
        const handleEscape = (e) => {
            if (e.key === 'Escape') {
                modal.remove();
                options.onCancel();
                document.removeEventListener('keydown', handleEscape);
            }
        };
        document.addEventListener('keydown', handleEscape);
    }

    /**
     * Get selected items from selection manager
     */
    getSelectedItems() {
        if (this.options.selectionManager) {
            return this.options.selectionManager.getSelectedItems();
        }

        // Fallback: get from checkboxes directly
        const checkboxes = document.querySelectorAll('.registration-checkbox:checked');
        return Array.from(checkboxes).map(checkbox => checkbox.value);
    }

    /**
     * Show success message
     */
    showSuccess(message) {
        document.dispatchEvent(new CustomEvent('eventRegistration:bulkActionSuccess', {
            detail: { message }
        }));
    }

    /**
     * Show error message
     */
    showError(message) {
        document.dispatchEvent(new CustomEvent('eventRegistration:bulkActionError', {
            detail: { message }
        }));
    }

    /**
     * Add custom bulk action
     */
    addAction(actionKey, config) {
        this.availableActions[actionKey] = {
            label: config.label,
            icon: config.icon || '⚙️',
            color: config.color || 'gray',
            description: config.description || config.label,
            handler: config.handler
        };

        // Recreate buttons
        this.createActionButtons();

        console.log(`[BulkActionManager] ➕ Added custom action: ${actionKey}`);
    }

    /**
     * Remove bulk action
     */
    removeAction(actionKey) {
        delete this.availableActions[actionKey];
        this.createActionButtons();

        console.log(`[BulkActionManager] ➖ Removed action: ${actionKey}`);
    }

    /**
     * Get available actions
     */
    getAvailableActions() {
        return { ...this.availableActions };
    }

    /**
     * Check if processing
     */
    isProcessingAction() {
        return this.isProcessing;
    }

    /**
     * Get current action
     */
    getCurrentAction() {
        return this.currentAction;
    }

    /**
     * Cancel current action (if possible)
     */
    cancelCurrentAction() {
        if (this.isProcessing) {
            console.log('[BulkActionManager] 🛑 Attempting to cancel current action');
            // Implementation depends on specific action requirements
        }
    }
}