/**
 * EventRegistrationsManager - Main orchestrator for event registrations management
 *
 * This class coordinates all event registration functionalities including:
 * - Filtering and search
 * - Selection management
 * - Bulk operations
 * - Modal management
 * - API interactions
 */

import FilterManager from './modules/FilterManager.js';
import SelectionManager from './modules/SelectionManager.js';
import BulkActionManager from './modules/BulkActionManager.js';
import NotificationManager from './modules/NotificationManager.js';
import ModalManager from './modules/ModalManager.js';

export default class EventRegistrationsManager {
    constructor(options = {}) {
        console.log('[EventRegistrationsManager] 🚀 Initializing Event Registrations Manager v1.0');

        this.options = {
            tableSelector: '#registrationsTable',
            filtersFormSelector: '#filtersForm',
            modalSelector: '#addRegistrationModal',
            ...options
        };

        // State management
        this.state = {
            isLoading: false,
            selectedItems: [],
            filters: {
                search: '',
                event_id: '',
                status: '',
                date_from: '',
                date_to: ''
            }
        };

        // Initialize managers
        this.initializeManagers();

        // Bind events
        this.bindEvents();

        // Make globally available for backward compatibility
        this.makeGloballyAvailable();

        console.log('[EventRegistrationsManager] ✅ Initialization complete');
    }

    /**
     * Initialize all specialized managers
     */
    initializeManagers() {
        console.log('[EventRegistrationsManager] 📦 Initializing specialized managers...');

        this.filterManager = new FilterManager({
            formSelector: this.options.filtersFormSelector,
            onFilterChange: (filters) => this.handleFilterChange(filters),
            onClearFilters: () => this.handleClearFilters()
        });

        this.selectionManager = new SelectionManager({
            onSelectionChange: (selectedItems) => this.handleSelectionChange(selectedItems),
            onClearSelection: () => this.handleClearSelection()
        });

        this.bulkActionManager = new BulkActionManager({
            onBulkAction: (action, items) => this.handleBulkAction(action, items),
            selectionManager: this.selectionManager
        });

        this.notificationManager = new NotificationManager({
            position: 'top-right',
            duration: 3000
        });

        this.modalManager = new ModalManager({
            modalSelector: this.options.modalSelector,
            onSubmit: (formData) => this.handleModalSubmit(formData),
            onClose: () => this.handleModalClose()
        });

        console.log('[EventRegistrationsManager] ✅ All managers initialized');
    }

    /**
     * Bind global events and dispatchers
     */
    bindEvents() {
        // Custom event listeners for cross-module communication
        document.addEventListener('eventRegistration:filterApplied', (event) => {
            this.reloadTable(event.detail.filters);
        });

        document.addEventListener('eventRegistration:bulkActionCompleted', (event) => {
            this.notificationManager.showSuccess(event.detail.message);
            this.reloadTable();
            this.selectionManager.clearSelection();
        });

        document.addEventListener('eventRegistration:registrationCreated', (event) => {
            this.notificationManager.showSuccess('Registrazione creata con successo');
            this.reloadTable();
            this.modalManager.close();
        });

        document.addEventListener('eventRegistration:error', (event) => {
            this.notificationManager.showError(event.detail.message);
        });
    }

    /**
     * Handle filter changes
     */
    handleFilterChange(filters) {
        this.state.filters = { ...this.state.filters, ...filters };
        this.applyFilters();
    }

    /**
     * Handle clear filters
     */
    handleClearFilters() {
        this.state.filters = {
            search: '',
            event_id: '',
            status: '',
            date_from: '',
            date_to: ''
        };
        this.applyFilters();
    }

    /**
     * Handle selection changes
     */
    handleSelectionChange(selectedItems) {
        this.state.selectedItems = selectedItems;

        // Dispatch event for other components
        document.dispatchEvent(new CustomEvent('eventRegistration:selectionChanged', {
            detail: { selectedItems, count: selectedItems.length }
        }));
    }

    /**
     * Handle clear selection
     */
    handleClearSelection() {
        this.state.selectedItems = [];
        this.selectionManager.clearSelection();
    }

    /**
     * Handle bulk actions
     */
    async handleBulkAction(action, items) {
        if (items.length === 0) {
            this.notificationManager.showWarning('Seleziona almeno una registrazione');
            return;
        }

        try {
            this.setLoading(true);

            const response = await this.performBulkAction(action, items);

            if (response.success) {
                document.dispatchEvent(new CustomEvent('eventRegistration:bulkActionCompleted', {
                    detail: { action, items, message: response.message }
                }));
            } else {
                this.notificationManager.showError(response.message || 'Errore durante l\'operazione');
            }
        } catch (error) {
            console.error('[EventRegistrationsManager] Bulk action error:', error);
            this.notificationManager.showError('Errore di connessione');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Handle modal form submission
     */
    async handleModalSubmit(formData) {
        try {
            this.setLoading(true);

            const response = await this.createRegistration(formData);

            if (response.success) {
                // Show success notification
                this.notificationManager.showSuccess(response.message || 'Registrazione creata con successo');

                // Close modal
                this.modalManager.close();

                // Reload table to show new registration
                await this.reloadTable();

                // Dispatch event for other components
                document.dispatchEvent(new CustomEvent('eventRegistration:registrationCreated', {
                    detail: { registration: response.registration }
                }));
            } else {
                this.notificationManager.showError(response.message || 'Errore durante la creazione');
            }
        } catch (error) {
            console.error('[EventRegistrationsManager] Modal submit error:', error);
            this.notificationManager.showError('Errore di connessione');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Handle modal close
     */
    handleModalClose() {
        // Reset any form states if needed
        console.log('[EventRegistrationsManager] Modal closed');
    }

    /**
     * Apply current filters
     */
    async applyFilters() {
        try {
            this.setLoading(true);
            await this.reloadTable(this.state.filters);
        } catch (error) {
            console.error('[EventRegistrationsManager] Apply filters error:', error);
            this.notificationManager.showError('Errore durante il caricamento');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Reload table with optional filters
     */
    async reloadTable(filters = null) {
        const filtersToApply = filters || this.state.filters;
        const params = new URLSearchParams(filtersToApply);

        try {
            const response = await fetch(`/admin/event-registrations?${params.toString()}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            // Update table content
            const tableContainer = document.querySelector(this.options.tableSelector);
            if (tableContainer) {
                tableContainer.innerHTML = data.html;

                // Reinitialize selection after table update
                this.selectionManager.reinitializeAfterTableUpdate();

                console.log('[EventRegistrationsManager] 📋 Table reloaded successfully');
            }
        } catch (error) {
            console.error('[EventRegistrationsManager] Reload table error:', error);
            throw error;
        }
    }

    /**
     * Perform bulk action API call
     */
    async performBulkAction(action, registrationIds) {
        const response = await fetch('/admin/event-registrations/bulk-update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                registration_ids: registrationIds,
                action: action
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    /**
     * Create new registration API call
     */
    async createRegistration(formData) {
        const response = await fetch('/admin/event-registrations', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        return await response.json();
    }

    /**
     * Update single registration status
     */
    async updateRegistrationStatus(registrationId, status) {
        try {
            this.setLoading(true);

            const response = await fetch(`/admin/event-registrations/${registrationId}/status`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: status })
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                this.notificationManager.showSuccess(data.message);
                await this.reloadTable();
            } else {
                this.notificationManager.showError(data.message || 'Errore durante l\'aggiornamento');
            }
        } catch (error) {
            console.error('[EventRegistrationsManager] Update status error:', error);
            this.notificationManager.showError('Errore di connessione');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Delete registration
     */
    async deleteRegistration(registrationId) {
        if (!confirm('Sei sicuro di voler eliminare questa registrazione? Questa azione non può essere annullata.')) {
            return;
        }

        try {
            this.setLoading(true);

            const response = await fetch(`/admin/event-registrations/${registrationId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                this.notificationManager.showSuccess(data.message);
                await this.reloadTable();
            } else {
                this.notificationManager.showError(data.message || 'Errore durante l\'eliminazione');
            }
        } catch (error) {
            console.error('[EventRegistrationsManager] Delete registration error:', error);
            this.notificationManager.showError('Errore di connessione');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Export registrations
     */
    exportRegistrations() {
        const params = new URLSearchParams(this.state.filters);
        const exportUrl = `/admin/event-registrations/export?${params.toString()}`;

        window.open(exportUrl, '_blank');
        this.notificationManager.showInfo('Download del file CSV avviato...');
    }

    /**
     * View registration details
     */
    viewRegistration(registrationId) {
        window.location.href = `/admin/event-registrations/${registrationId}`;
    }

    /**
     * Set loading state
     */
    setLoading(isLoading) {
        this.state.isLoading = isLoading;

        // Dispatch loading state change
        document.dispatchEvent(new CustomEvent('eventRegistration:loadingStateChanged', {
            detail: { isLoading }
        }));
    }

    /**
     * Make functions globally available for backward compatibility
     */
    makeGloballyAvailable() {
        // Global functions for template onclick handlers
        window.updateStatus = (registrationId, status) => this.updateRegistrationStatus(registrationId, status);
        window.deleteRegistration = (registrationId) => this.deleteRegistration(registrationId);
        window.viewRegistration = (registrationId) => this.viewRegistration(registrationId);
        window.exportRegistrations = () => this.exportRegistrations();
        window.openAddRegistrationModal = () => this.modalManager.open();
        window.closeAddRegistrationModal = () => this.modalManager.close();

        // Global functions for bulk actions
        window.bulkAction = (action) => {
            const selectedItems = this.selectionManager.getSelectedItems();
            this.handleBulkAction(action, selectedItems);
        };

        window.clearSelection = () => this.handleClearSelection();

        // Filter functions
        window.applyFilters = () => this.applyFilters();
        window.clearFilters = () => this.handleClearFilters();

        console.log('[EventRegistrationsManager] 🌐 Global functions registered');
    }

    /**
     * Get current state
     */
    getState() {
        return { ...this.state };
    }

    /**
     * Get manager instances
     */
    getManagers() {
        return {
            filter: this.filterManager,
            selection: this.selectionManager,
            bulkAction: this.bulkActionManager,
            notification: this.notificationManager,
            modal: this.modalManager
        };
    }
}