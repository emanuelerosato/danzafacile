/**
 * BulkActionManager - Handles bulk operations on staff schedules
 *
 * Provides bulk operation functionality:
 * - Select all/none schedules
 * - Bulk delete operations
 * - Bulk status updates
 * - Bulk assignment operations
 * - Confirmation dialogs for destructive actions
 * - Progress tracking for long operations
 */

export class BulkActionManager {
    constructor(options = {}) {
        this.options = {
            manager: null,
            confirmActions: true,
            batchSize: 10, // Process items in batches
            progressThreshold: 5, // Show progress for operations with more than X items
            ...options
        };

        // Reference to main manager
        this.manager = this.options.manager;

        // Bulk action state
        this.state = {
            selectedItems: new Set(),
            isProcessing: false,
            currentOperation: null,
            progress: {
                total: 0,
                completed: 0,
                failed: 0
            }
        };

        // UI elements
        this.elements = new Map();

        // Operation queue
        this.operationQueue = [];

        // Initialize
        this.init();
    }

    /**
     * Initialize the bulk action manager
     */
    async init() {
        try {
            this.log('Initializing BulkActionManager...');

            // Setup UI elements
            this.setupElements();

            // Setup event listeners
            this.setupEventListeners();

            // Setup bulk action controls
            this.setupBulkControls();

            this.log('BulkActionManager initialized successfully');

        } catch (error) {
            console.error('Failed to initialize BulkActionManager:', error);
            this.manager.modules.notification?.error('Errore nell\'inizializzazione delle azioni bulk');
        }
    }

    /**
     * Setup UI elements
     */
    setupElements() {
        const elementSelectors = {
            selectAll: '#select-all-checkbox',
            bulkActions: '#bulk-actions-dropdown',
            bulkDeleteBtn: '#bulk-delete-btn',
            bulkStatusBtn: '#bulk-status-btn',
            selectedCount: '#selected-count',
            actionButtons: '#bulk-action-buttons'
        };

        Object.entries(elementSelectors).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element) {
                this.elements.set(key, element);
            }
        });

        // Get all schedule checkboxes
        this.updateScheduleCheckboxes();

        this.log('Bulk action elements setup completed');
    }

    /**
     * Update schedule checkboxes references
     */
    updateScheduleCheckboxes() {
        const checkboxes = document.querySelectorAll('[data-schedule-checkbox]');
        this.elements.set('scheduleCheckboxes', Array.from(checkboxes));
    }

    /**
     * Setup event listeners
     */
    setupEventListeners() {
        // Select all checkbox
        const selectAllCheckbox = this.elements.get('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                this.handleSelectAll(e.target.checked);
            });
        }

        // Individual schedule checkboxes
        this.setupScheduleCheckboxListeners();

        // Bulk action buttons
        this.setupBulkActionButtons();

        // Listen to manager events
        this.manager.on('schedules:updated', () => {
            this.updateScheduleCheckboxes();
            this.setupScheduleCheckboxListeners();
            this.updateUI();
        });

        this.log('Bulk action event listeners setup completed');
    }

    /**
     * Setup schedule checkbox listeners
     */
    setupScheduleCheckboxListeners() {
        const checkboxes = this.elements.get('scheduleCheckboxes') || [];

        checkboxes.forEach(checkbox => {
            // Remove existing listeners to avoid duplicates
            checkbox.removeEventListener('change', this.handleScheduleCheckboxChange);

            // Add new listener
            checkbox.addEventListener('change', (e) => {
                this.handleScheduleCheckboxChange(e);
            });
        });
    }

    /**
     * Setup bulk action buttons
     */
    setupBulkActionButtons() {
        const bulkDeleteBtn = this.elements.get('bulkDeleteBtn');
        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', () => {
                this.handleBulkDelete();
            });
        }

        const bulkStatusBtn = this.elements.get('bulkStatusBtn');
        if (bulkStatusBtn) {
            bulkStatusBtn.addEventListener('click', () => {
                this.handleBulkStatusUpdate();
            });
        }

        // Setup dropdown actions
        this.setupDropdownActions();
    }

    /**
     * Setup dropdown actions
     */
    setupDropdownActions() {
        const dropdown = this.elements.get('bulkActions');
        if (!dropdown) return;

        const actions = dropdown.querySelectorAll('[data-bulk-action]');

        actions.forEach(action => {
            action.addEventListener('click', (e) => {
                e.preventDefault();
                const actionType = e.target.dataset.bulkAction;
                this.handleBulkAction(actionType);
            });
        });
    }

    /**
     * Setup bulk controls UI
     */
    setupBulkControls() {
        // Create bulk controls if they don't exist
        this.createBulkControls();

        // Update initial state
        this.updateUI();
    }

    /**
     * Create bulk controls UI
     */
    createBulkControls() {
        // Check if bulk controls already exist
        if (document.getElementById('bulk-controls')) return;

        const bulkControlsHtml = `
            <div id="bulk-controls" class="bg-white rounded-lg shadow p-4 mb-6 hidden">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span id="selected-count" class="text-sm font-medium text-gray-700">
                            0 elementi selezionati
                        </span>
                        <button id="clear-selection-btn" class="text-sm text-gray-500 hover:text-gray-700">
                            Deseleziona tutto
                        </button>
                    </div>
                    <div id="bulk-action-buttons" class="flex items-center space-x-3">
                        <div class="relative">
                            <button id="bulk-actions-dropdown" class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                                Azioni
                                <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div id="bulk-actions-menu" class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 hidden z-10">
                                <div class="py-1">
                                    <a href="#" data-bulk-action="activate" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Attiva selezionati
                                    </a>
                                    <a href="#" data-bulk-action="deactivate" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Disattiva selezionati
                                    </a>
                                    <a href="#" data-bulk-action="duplicate" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Duplica selezionati
                                    </a>
                                    <div class="border-t border-gray-100"></div>
                                    <a href="#" data-bulk-action="export" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Esporta selezionati
                                    </a>
                                </div>
                            </div>
                        </div>
                        <button id="bulk-delete-btn" class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Elimina
                        </button>
                    </div>
                </div>
                <div id="bulk-progress" class="mt-4 hidden">
                    <div class="bg-gray-200 rounded-full h-2">
                        <div id="bulk-progress-bar" class="bg-rose-500 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <div class="mt-2 flex justify-between text-sm text-gray-600">
                        <span id="bulk-progress-text">Elaborazione in corso...</span>
                        <span id="bulk-progress-count">0 / 0</span>
                    </div>
                </div>
            </div>
        `;

        // Insert before the main content
        const targetContainer = document.querySelector('.space-y-6') || document.querySelector('.max-w-7xl');
        if (targetContainer) {
            targetContainer.insertAdjacentHTML('afterbegin', bulkControlsHtml);

            // Setup new elements
            this.elements.set('bulkControls', document.getElementById('bulk-controls'));
            this.elements.set('selectedCount', document.getElementById('selected-count'));
            this.elements.set('bulkDeleteBtn', document.getElementById('bulk-delete-btn'));
            this.elements.set('bulkActions', document.getElementById('bulk-actions-dropdown'));

            // Setup dropdown toggle
            this.setupDropdownToggle();

            // Setup clear selection
            const clearBtn = document.getElementById('clear-selection-btn');
            if (clearBtn) {
                clearBtn.addEventListener('click', () => {
                    this.clearSelection();
                });
            }
        }
    }

    /**
     * Setup dropdown toggle functionality
     */
    setupDropdownToggle() {
        const dropdownButton = this.elements.get('bulkActions');
        const dropdownMenu = document.getElementById('bulk-actions-menu');

        if (!dropdownButton || !dropdownMenu) return;

        dropdownButton.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            if (!dropdownButton.contains(e.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
    }

    /**
     * Handle select all checkbox
     */
    handleSelectAll(checked) {
        const checkboxes = this.elements.get('scheduleCheckboxes') || [];

        checkboxes.forEach(checkbox => {
            checkbox.checked = checked;
            const scheduleId = this.getScheduleIdFromCheckbox(checkbox);

            if (checked) {
                this.state.selectedItems.add(scheduleId);
            } else {
                this.state.selectedItems.delete(scheduleId);
            }
        });

        this.updateUI();
        this.log('Select all:', checked, 'Selected items:', this.state.selectedItems.size);
    }

    /**
     * Handle individual schedule checkbox change
     */
    handleScheduleCheckboxChange(event) {
        const checkbox = event.target;
        const scheduleId = this.getScheduleIdFromCheckbox(checkbox);

        if (checkbox.checked) {
            this.state.selectedItems.add(scheduleId);
        } else {
            this.state.selectedItems.delete(scheduleId);
        }

        this.updateSelectAllState();
        this.updateUI();

        this.log('Schedule selection changed:', scheduleId, 'Checked:', checkbox.checked);
    }

    /**
     * Get schedule ID from checkbox element
     */
    getScheduleIdFromCheckbox(checkbox) {
        return checkbox.dataset.scheduleId || checkbox.value;
    }

    /**
     * Update select all checkbox state
     */
    updateSelectAllState() {
        const selectAllCheckbox = this.elements.get('selectAll');
        if (!selectAllCheckbox) return;

        const checkboxes = this.elements.get('scheduleCheckboxes') || [];
        const checkedCount = this.state.selectedItems.size;
        const totalCount = checkboxes.length;

        if (checkedCount === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else if (checkedCount === totalCount) {
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
        } else {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
        }
    }

    /**
     * Update UI based on current state
     */
    updateUI() {
        this.updateSelectedCount();
        this.updateBulkControlsVisibility();
        this.updateActionButtonsState();
    }

    /**
     * Update selected count display
     */
    updateSelectedCount() {
        const countElement = this.elements.get('selectedCount');
        if (!countElement) return;

        const count = this.state.selectedItems.size;
        countElement.textContent = `${count} element${count === 1 ? 'o' : 'i'} selezionat${count === 1 ? 'o' : 'i'}`;
    }

    /**
     * Update bulk controls visibility
     */
    updateBulkControlsVisibility() {
        const bulkControls = this.elements.get('bulkControls');
        if (!bulkControls) return;

        if (this.state.selectedItems.size > 0) {
            bulkControls.classList.remove('hidden');
        } else {
            bulkControls.classList.add('hidden');
        }
    }

    /**
     * Update action buttons state
     */
    updateActionButtonsState() {
        const hasSelection = this.state.selectedItems.size > 0;

        // Update bulk delete button
        const deleteBtn = this.elements.get('bulkDeleteBtn');
        if (deleteBtn) {
            deleteBtn.disabled = !hasSelection || this.state.isProcessing;
        }

        // Update bulk actions dropdown
        const actionsBtn = this.elements.get('bulkActions');
        if (actionsBtn) {
            actionsBtn.disabled = !hasSelection || this.state.isProcessing;
        }
    }

    /**
     * Handle bulk action
     */
    async handleBulkAction(actionType) {
        if (this.state.selectedItems.size === 0) {
            this.manager.modules.notification?.warning('Seleziona almeno un elemento');
            return;
        }

        switch (actionType) {
            case 'activate':
                await this.bulkStatusUpdate('active');
                break;
            case 'deactivate':
                await this.bulkStatusUpdate('inactive');
                break;
            case 'duplicate':
                await this.bulkDuplicate();
                break;
            case 'export':
                await this.bulkExport();
                break;
            default:
                this.log('Unknown bulk action:', actionType);
        }
    }

    /**
     * Handle bulk delete
     */
    async handleBulkDelete() {
        if (this.state.selectedItems.size === 0) {
            this.manager.modules.notification?.warning('Seleziona almeno un elemento da eliminare');
            return;
        }

        const count = this.state.selectedItems.size;
        const confirmMessage = `Sei sicuro di voler eliminare ${count} element${count === 1 ? 'o' : 'i'} selezionat${count === 1 ? 'o' : 'i'}? Questa azione non può essere annullata.`;

        if (this.options.confirmActions && !confirm(confirmMessage)) {
            return;
        }

        await this.executeBulkOperation('delete', Array.from(this.state.selectedItems));
    }

    /**
     * Handle bulk status update
     */
    async handleBulkStatusUpdate() {
        // This would open a modal or dropdown to select new status
        // For now, we'll implement a simple prompt
        const newStatus = prompt('Inserisci il nuovo stato (active, inactive, pending):');

        if (!newStatus) return;

        await this.bulkStatusUpdate(newStatus);
    }

    /**
     * Bulk status update
     */
    async bulkStatusUpdate(status) {
        if (this.state.selectedItems.size === 0) return;

        const operation = {
            type: 'status_update',
            data: { status },
            items: Array.from(this.state.selectedItems)
        };

        await this.executeBulkOperation('status_update', operation.items, operation.data);
    }

    /**
     * Bulk duplicate
     */
    async bulkDuplicate() {
        if (this.state.selectedItems.size === 0) return;

        const confirmMessage = `Vuoi duplicare ${this.state.selectedItems.size} element${this.state.selectedItems.size === 1 ? 'o' : 'i'}?`;

        if (this.options.confirmActions && !confirm(confirmMessage)) {
            return;
        }

        await this.executeBulkOperation('duplicate', Array.from(this.state.selectedItems));
    }

    /**
     * Bulk export
     */
    async bulkExport() {
        if (this.state.selectedItems.size === 0) return;

        try {
            const selectedIds = Array.from(this.state.selectedItems);
            const exportUrl = new URL('/admin/staff-schedules/export', window.location.origin);

            selectedIds.forEach(id => {
                exportUrl.searchParams.append('ids[]', id);
            });

            // Create a temporary link to trigger download
            const link = document.createElement('a');
            link.href = exportUrl.toString();
            link.download = 'staff-schedules-export.csv';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            this.manager.modules.notification?.success('Esportazione avviata');

        } catch (error) {
            this.log('Export failed:', error);
            this.manager.modules.notification?.error('Errore durante l\'esportazione');
        }
    }

    /**
     * Execute bulk operation
     */
    async executeBulkOperation(operationType, items, data = {}) {
        if (this.state.isProcessing) {
            this.manager.modules.notification?.warning('Un\'operazione è già in corso');
            return;
        }

        this.state.isProcessing = true;
        this.state.currentOperation = operationType;
        this.state.progress = {
            total: items.length,
            completed: 0,
            failed: 0
        };

        // Show progress for large operations
        if (items.length >= this.options.progressThreshold) {
            this.showProgress();
        }

        try {
            this.log('Starting bulk operation:', operationType, 'Items:', items.length);

            // Process items in batches
            const batches = this.createBatches(items, this.options.batchSize);

            for (const batch of batches) {
                await this.processBatch(operationType, batch, data);
                this.updateProgress();
            }

            // Success notification
            const successCount = this.state.progress.completed;
            const failedCount = this.state.progress.failed;

            if (failedCount === 0) {
                this.manager.modules.notification?.success(
                    `Operazione completata con successo: ${successCount} element${successCount === 1 ? 'o' : 'i'} elaborat${successCount === 1 ? 'o' : 'i'}`
                );
            } else {
                this.manager.modules.notification?.warning(
                    `Operazione completata parzialmente: ${successCount} successi, ${failedCount} errori`
                );
            }

            // Clear selection after successful operation
            this.clearSelection();

            // Refresh data
            await this.manager.refreshData();

        } catch (error) {
            this.log('Bulk operation failed:', error);
            this.manager.modules.notification?.error('Errore durante l\'operazione bulk');
        } finally {
            this.state.isProcessing = false;
            this.state.currentOperation = null;
            this.hideProgress();
            this.updateUI();
        }
    }

    /**
     * Create batches from items array
     */
    createBatches(items, batchSize) {
        const batches = [];
        for (let i = 0; i < items.length; i += batchSize) {
            batches.push(items.slice(i, i + batchSize));
        }
        return batches;
    }

    /**
     * Process a batch of items
     */
    async processBatch(operationType, batch, data) {
        const promises = batch.map(async (itemId) => {
            try {
                await this.processItem(operationType, itemId, data);
                this.state.progress.completed++;
            } catch (error) {
                this.log('Failed to process item:', itemId, error);
                this.state.progress.failed++;
            }
        });

        await Promise.allSettled(promises);
    }

    /**
     * Process individual item
     */
    async processItem(operationType, itemId, data) {
        switch (operationType) {
            case 'delete':
                await this.manager.deleteSchedule(itemId);
                break;

            case 'status_update':
                await this.manager.updateSchedule(itemId, { status: data.status });
                break;

            case 'duplicate':
                // This would need to be implemented in the manager
                await this.duplicateSchedule(itemId);
                break;

            default:
                throw new Error(`Unknown operation type: ${operationType}`);
        }
    }

    /**
     * Duplicate schedule (placeholder implementation)
     */
    async duplicateSchedule(scheduleId) {
        // This would need to be implemented properly
        // For now, just log the action
        this.log('Duplicating schedule:', scheduleId);

        // Simulate API call
        await new Promise(resolve => setTimeout(resolve, 100));
    }

    /**
     * Show progress indicator
     */
    showProgress() {
        const progressContainer = document.getElementById('bulk-progress');
        if (progressContainer) {
            progressContainer.classList.remove('hidden');
        }
    }

    /**
     * Update progress indicator
     */
    updateProgress() {
        const progressBar = document.getElementById('bulk-progress-bar');
        const progressText = document.getElementById('bulk-progress-text');
        const progressCount = document.getElementById('bulk-progress-count');

        if (progressBar) {
            const percentage = (this.state.progress.completed / this.state.progress.total) * 100;
            progressBar.style.width = `${percentage}%`;
        }

        if (progressText) {
            progressText.textContent = `Elaborazione ${this.state.currentOperation}...`;
        }

        if (progressCount) {
            progressCount.textContent = `${this.state.progress.completed} / ${this.state.progress.total}`;
        }
    }

    /**
     * Hide progress indicator
     */
    hideProgress() {
        const progressContainer = document.getElementById('bulk-progress');
        if (progressContainer) {
            progressContainer.classList.add('hidden');
        }
    }

    /**
     * Clear selection
     */
    clearSelection() {
        this.state.selectedItems.clear();

        // Uncheck all checkboxes
        const checkboxes = this.elements.get('scheduleCheckboxes') || [];
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        // Update select all state
        this.updateSelectAllState();

        // Update UI
        this.updateUI();

        this.log('Selection cleared');
    }

    /**
     * Select all schedules
     */
    selectAll() {
        const selectAllCheckbox = this.elements.get('selectAll');
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = true;
            this.handleSelectAll(true);
        }
    }

    /**
     * Check if there's an active selection
     */
    hasActiveSelection() {
        return this.state.selectedItems.size > 0;
    }

    /**
     * Get selected items
     */
    getSelectedItems() {
        return Array.from(this.state.selectedItems);
    }

    /**
     * Get selected count
     */
    getSelectedCount() {
        return this.state.selectedItems.size;
    }

    /**
     * Debug logging
     */
    log(...args) {
        if (this.manager?.options.debug) {
            console.log('[BulkActionManager]', ...args);
        }
    }

    /**
     * Cleanup
     */
    destroy() {
        // Clear selection
        this.clearSelection();

        // Clear references
        this.elements.clear();
        this.operationQueue = [];

        this.log('BulkActionManager destroyed');
    }
}