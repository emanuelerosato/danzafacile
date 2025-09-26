/**
 * Event Registrations Manager Entry Point
 *
 * This file serves as the entry point for the event registrations management system.
 * It initializes the main EventRegistrationsManager and provides Alpine.js integration.
 */

import EventRegistrationsManager from './EventRegistrationsManager.js';

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('[EventRegistrations] 🚀 Initializing Event Registrations Management System');

    try {
        // Initialize the main manager
        const manager = new EventRegistrationsManager({
            // Configuration can be passed here
            tableSelector: '#registrationsTable',
            filtersFormSelector: '#filtersForm',
            modalSelector: '#addRegistrationModal'
        });

        // Make manager globally available for debugging
        window.eventRegistrationsManager = manager;

        // Alpine.js data function for backward compatibility
        window.eventRegistrationsData = function() {
            return {
                // Reactive data properties
                selectedItems: [],
                bulkActionModal: false,
                filters: {
                    search: '',
                    event_id: '',
                    status: '',
                    date_from: '',
                    date_to: ''
                },
                isLoading: false,

                // Methods
                init() {
                    console.log('[Alpine] 🔌 Event Registrations Alpine.js component initialized');

                    // Listen for manager state changes
                    document.addEventListener('eventRegistration:selectionChanged', (e) => {
                        this.selectedItems = e.detail.selectedItems;
                    });

                    document.addEventListener('eventRegistration:loadingStateChanged', (e) => {
                        this.isLoading = e.detail.isLoading;
                    });

                    // Sync initial state
                    const managerState = manager.getState();
                    this.selectedItems = managerState.selectedItems;
                    this.filters = { ...managerState.filters };
                },

                // Computed properties
                get selectedCount() {
                    return this.selectedItems.length;
                },

                get hasSelection() {
                    return this.selectedItems.length > 0;
                },

                get activeFiltersCount() {
                    return Object.values(this.filters).filter(value =>
                        value && value.toString().trim() !== ''
                    ).length;
                },

                // Filter methods
                updateFilter(key, value) {
                    this.filters[key] = value;
                    // Delegate to manager
                    manager.handleFilterChange({ [key]: value });
                },

                applyFilters() {
                    manager.applyFilters();
                },

                clearFilters() {
                    this.filters = {
                        search: '',
                        event_id: '',
                        status: '',
                        date_from: '',
                        date_to: ''
                    };
                    manager.handleClearFilters();
                },

                // Selection methods
                toggleSelection(itemId) {
                    const selectionManager = manager.getManagers().selection;
                    selectionManager.toggleItem(itemId);
                },

                clearSelection() {
                    const selectionManager = manager.getManagers().selection;
                    selectionManager.clearSelection();
                },

                // Bulk action methods
                executeBulkAction(action) {
                    if (this.selectedItems.length === 0) {
                        alert('Seleziona almeno una registrazione');
                        return;
                    }

                    manager.handleBulkAction(action, this.selectedItems);
                },

                // Modal methods
                openModal() {
                    const modalManager = manager.getManagers().modal;
                    modalManager.open();
                },

                closeModal() {
                    const modalManager = manager.getManagers().modal;
                    modalManager.close();
                },

                // Export method
                exportData() {
                    manager.exportRegistrations();
                },

                // Status update method
                updateStatus(registrationId, status) {
                    manager.updateRegistrationStatus(registrationId, status);
                },

                // Delete method
                deleteRegistration(registrationId) {
                    manager.deleteRegistration(registrationId);
                },

                // View method
                viewRegistration(registrationId) {
                    manager.viewRegistration(registrationId);
                }
            };
        };

        // Make specific functions globally available for onclick handlers
        window.updateStatus = (registrationId, status) => manager.updateRegistrationStatus(registrationId, status);
        window.deleteRegistration = (registrationId) => manager.deleteRegistration(registrationId);
        window.viewRegistration = (registrationId) => manager.viewRegistration(registrationId);
        window.exportRegistrations = () => manager.exportRegistrations();
        window.openAddRegistrationModal = () => {
            const modalManager = manager.getManagers().modal;
            modalManager.open();
        };
        window.closeAddRegistrationModal = () => {
            const modalManager = manager.getManagers().modal;
            modalManager.close();
        };
        window.applyFilters = () => manager.applyFilters();
        window.clearFilters = () => manager.handleClearFilters();
        window.bulkAction = (action) => {
            const selectedItems = manager.getManagers().selection.getSelectedItems();
            manager.handleBulkAction(action, selectedItems);
        };
        window.clearSelection = () => {
            manager.getManagers().selection.clearSelection();
        };

        console.log('[EventRegistrations] ✅ System initialized successfully');
        console.log('[EventRegistrations] 🌐 Global functions registered for template compatibility');

        // Dispatch ready event
        document.dispatchEvent(new CustomEvent('eventRegistration:systemReady', {
            detail: { manager }
        }));

    } catch (error) {
        console.error('[EventRegistrations] ❌ Initialization failed:', error);

        // Show user-friendly error
        const errorMessage = document.createElement('div');
        errorMessage.className = 'fixed top-4 right-4 bg-red-50 border border-red-200 rounded-lg p-4 max-w-sm z-50';
        errorMessage.innerHTML = `
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="text-red-800 font-medium">Errore di Sistema</h4>
                    <p class="text-red-700 text-sm mt-1">Impossibile inizializzare il sistema di gestione registrazioni. Ricarica la pagina.</p>
                    <button onclick="this.parentElement.parentElement.parentElement.remove()"
                            class="text-red-600 hover:text-red-800 text-xs mt-2 underline">
                        Chiudi
                    </button>
                </div>
            </div>
        `;

        document.body.appendChild(errorMessage);

        // Auto-hide after 10 seconds
        setTimeout(() => {
            if (errorMessage.parentNode) {
                errorMessage.parentNode.removeChild(errorMessage);
            }
        }, 10000);
    }
});

// Handle page visibility changes for cleanup
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        console.log('[EventRegistrations] 👁️ Page hidden, cleaning up resources');
        // Cleanup logic if needed
    } else {
        console.log('[EventRegistrations] 👁️ Page visible again, checking state');
        // Refresh logic if needed
        if (window.eventRegistrationsManager) {
            // Could refresh data here if needed
        }
    }
});

// Export for module usage
export { EventRegistrationsManager };