/**
 * EventsManager - Controller principale gestione eventi
 * APPROCCIO MODERNO: Architettura modulare con separazione concerns
 *
 * FASE 2: JavaScript Modernization
 * - FilterManager per gestione filtri avanzati
 * - BulkActionManager per azioni multiple
 * - NotificationManager per toast notifications
 * - Event-driven architecture per comunicazione componenti
 */

import { NotificationManager } from './modules/NotificationManager.js';
import { FilterManager } from './modules/FilterManager.js';
import { BulkActionManager } from './modules/BulkActionManager.js';

class EventsManager {
    constructor() {
        console.log('[EventsManager] 🚀 Initializing Events Manager v2.0');

        // Core state
        this.selectedItems = [];
        this.bulkActionModal = false;

        // Initialize modules
        this.notificationManager = new NotificationManager();
        this.filterManager = new FilterManager(this);
        this.bulkActionManager = new BulkActionManager(this);

        this.init();
    }

    init() {
        console.log('[EventsManager] 📋 Initializing modules...');

        // Initialize all modules
        this.notificationManager.init();
        this.filterManager.init();
        this.bulkActionManager.init();

        // Setup event listeners
        this.setupEventListeners();

        console.log('[EventsManager] ✅ All modules initialized successfully');
    }

    setupEventListeners() {
        // Listen for notifications
        document.addEventListener('events:notification', (e) => {
            this.notificationManager.show(e.detail.message, e.detail.type);
        });

        // Listen for selection changes
        document.addEventListener('events:selection-changed', (e) => {
            this.selectedItems = e.detail.selectedItems;
            this.updateUI();
        });
    }

    updateUI() {
        // Update UI based on current state
        const bulkButton = document.querySelector('[x-show="selectedItems.length > 0"]');
        if (bulkButton) {
            bulkButton.style.display = this.selectedItems.length > 0 ? 'block' : 'none';
        }

        const selectedCount = document.querySelector('[x-text="selectedItems.length"]');
        if (selectedCount) {
            selectedCount.textContent = this.selectedItems.length;
        }
    }

    // Selection Management
    get allSelected() {
        const checkboxes = document.querySelectorAll('input[name="event_ids[]"]');
        return checkboxes.length > 0 && this.selectedItems.length === checkboxes.length;
    }

    toggleAll(checked) {
        const checkboxes = document.querySelectorAll('input[name="event_ids[]"]');
        this.selectedItems = [];

        if (checked) {
            checkboxes.forEach(checkbox => {
                this.selectedItems.push(parseInt(checkbox.value));
            });
        }

        // Dispatch event for other components
        document.dispatchEvent(new CustomEvent('events:selection-changed', {
            detail: { selectedItems: this.selectedItems }
        }));
    }

    toggleSelection(eventId, checked) {
        if (checked) {
            if (!this.selectedItems.includes(eventId)) {
                this.selectedItems.push(eventId);
            }
        } else {
            const index = this.selectedItems.indexOf(eventId);
            if (index > -1) {
                this.selectedItems.splice(index, 1);
            }
        }

        // Dispatch event for other components
        document.dispatchEvent(new CustomEvent('events:selection-changed', {
            detail: { selectedItems: this.selectedItems }
        }));
    }

    // Modal Management
    showBulkActionModal() {
        this.bulkActionModal = true;
        this.bulkActionManager.showModal();

        // Dispatch event
        document.dispatchEvent(new CustomEvent('events:modal-changed', {
            detail: { bulkActionModal: this.bulkActionModal }
        }));
    }

    hideBulkActionModal() {
        this.bulkActionModal = false;
        this.bulkActionManager.hideModal();

        // Dispatch event
        document.dispatchEvent(new CustomEvent('events:modal-changed', {
            detail: { bulkActionModal: this.bulkActionModal }
        }));
    }

    // Delegate methods to modules
    async applyFilters() {
        return await this.filterManager.applyFilters();
    }

    async performBulkAction(action) {
        return await this.bulkActionManager.performAction(action, this.selectedItems);
    }

    showNotification(message, type = 'success') {
        this.notificationManager.show(message, type);
    }

    // Refresh table after operations
    async refreshTable() {
        console.log('[EventsManager] 🔄 Refreshing events table...');

        try {
            const response = await fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newTable = doc.querySelector('#events-table-container');
                const currentTable = document.querySelector('#events-table-container');

                if (newTable && currentTable) {
                    currentTable.innerHTML = newTable.innerHTML;
                    console.log('[EventsManager] ✅ Table refreshed successfully');
                }
            }
        } catch (error) {
            console.error('[EventsManager] ❌ Error refreshing table:', error);
            this.showNotification('Errore durante l\'aggiornamento della tabella', 'error');
        }
    }
}

export default EventsManager;