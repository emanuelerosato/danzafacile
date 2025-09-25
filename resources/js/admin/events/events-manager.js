/**
 * Events Manager - Entry Point
 * APPROCCIO MODERNO: Architettura modulare per gestione eventi
 *
 * FASE 2: JavaScript Modernization COMPLETATA
 * - EventsManager (coordinatore principale)
 * - FilterManager (gestione filtri avanzati)
 * - BulkActionManager (azioni multiple)
 * - NotificationManager (toast notifications moderne)
 * - Event-driven architecture
 * - Separazione delle responsabilità
 */

import EventsManager from './EventsManager.js';

// Initialize Events Manager when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('[EventsApp] 🚀 Initializing Events Application v2.0 - Modular Architecture');

    try {
        // Create global instance
        window.eventsManagerInstance = new EventsManager();

        // Make notification manager globally available for compatibility
        window.notificationManager = window.eventsManagerInstance.notificationManager;

        console.log('[EventsApp] ✅ Events Application initialized successfully');
        console.log('[EventsApp] 📦 Modular architecture loaded:');
        console.log('  - EventsManager (coordinator)');
        console.log('  - FilterManager (advanced filtering)');
        console.log('  - BulkActionManager (multiple actions)');
        console.log('  - NotificationManager (modern toasts)');

    } catch (error) {
        console.error('[EventsApp] ❌ Failed to initialize Events Application:', error);
    }
});

// Global Alpine.js data function for template compatibility
window.eventsManager = function() {
    console.log('[EventsApp] 🎯 Alpine.js eventsManager data function called');

    return {
        // Reactive state properties
        selectedItems: [],
        bulkActionModal: false,
        filters: {
            search: '',
            type: '',
            status: ''
        },

        // Initialize method called by Alpine.js
        init() {
            console.log('[EventsApp] 🔗 Connecting Alpine.js to EventsManager...');

            // Wait for EventsManager to be ready
            const waitForManager = () => {
                if (window.eventsManagerInstance) {
                    // Sync initial state
                    this.selectedItems = window.eventsManagerInstance.selectedItems;
                    this.bulkActionModal = window.eventsManagerInstance.bulkActionModal;
                    this.filters = window.eventsManagerInstance.filterManager.filters;

                    // Listen for events from EventsManager
                    this.setupEventListeners();

                    console.log('[EventsApp] ✅ Alpine.js connected to EventsManager');
                } else {
                    setTimeout(waitForManager, 10);
                }
            };

            waitForManager();
        },

        setupEventListeners() {
            // Listen for selection changes
            document.addEventListener('events:selection-changed', (e) => {
                this.selectedItems = e.detail.selectedItems;
                this.$nextTick(() => {
                    this.updateUI();
                });
            });

            // Listen for modal changes
            document.addEventListener('events:modal-changed', (e) => {
                this.bulkActionModal = e.detail.bulkActionModal;
            });

            // Custom Alpine events
            this.$el.addEventListener('show-bulk-modal', () => {
                if (window.eventsManagerInstance) {
                    window.eventsManagerInstance.showBulkActionModal();
                }
            });

            this.$el.addEventListener('hide-bulk-modal', () => {
                if (window.eventsManagerInstance) {
                    window.eventsManagerInstance.hideBulkActionModal();
                }
            });
        },

        updateUI() {
            // Update checkboxes to match selectedItems
            document.querySelectorAll('input[name="event_ids[]"]').forEach(checkbox => {
                checkbox.checked = this.selectedItems.includes(parseInt(checkbox.value));
            });
        },

        // Computed properties
        get allSelected() {
            const checkboxes = document.querySelectorAll('input[name="event_ids[]"]');
            return checkboxes.length > 0 && this.selectedItems.length === checkboxes.length;
        },

        // Delegate methods to EventsManager
        toggleAll(checked) {
            if (window.eventsManagerInstance) {
                window.eventsManagerInstance.toggleAll(checked);
                this.selectedItems = window.eventsManagerInstance.selectedItems;
            }
        },

        toggleSelection(eventId, checked) {
            if (window.eventsManagerInstance) {
                window.eventsManagerInstance.toggleSelection(eventId, checked);
                this.selectedItems = window.eventsManagerInstance.selectedItems;
            }
        },

        showBulkModal() {
            if (window.eventsManagerInstance) {
                window.eventsManagerInstance.showBulkActionModal();
                this.bulkActionModal = true;
            }
        },

        hideBulkModal() {
            if (window.eventsManagerInstance) {
                window.eventsManagerInstance.hideBulkActionModal();
                this.bulkActionModal = false;
            }
        },

        async applyFilters() {
            if (window.eventsManagerInstance) {
                return await window.eventsManagerInstance.applyFilters();
            }
        },

        async performBulkAction(action) {
            if (window.eventsManagerInstance) {
                const result = await window.eventsManagerInstance.performBulkAction(action);
                if (result && result.success) {
                    this.hideBulkModal();
                    this.selectedItems = [];
                }
                return result;
            }
        }
    };
};

// Handle page visibility changes for optimization
document.addEventListener('visibilitychange', () => {
    if (!document.hidden && window.eventsManagerInstance) {
        console.log('[EventsApp] 👁️ Page visible again, checking state...');
        // Could add refresh logic here if needed
    }
});

// Export for potential external use
export { EventsManager };