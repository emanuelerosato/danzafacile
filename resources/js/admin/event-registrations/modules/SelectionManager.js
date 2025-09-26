/**
 * SelectionManager - Handles multi-selection functionality
 *
 * Features:
 * - Individual item selection
 * - Select all functionality
 * - Bulk actions UI
 * - Selection persistence across table updates
 */

export default class SelectionManager {
    constructor(options = {}) {
        this.options = {
            selectAllSelector: '#selectAll',
            itemCheckboxSelector: '.registration-checkbox',
            bulkActionsSelector: '#bulkActions',
            selectedCountSelector: '#selectedCount',
            onSelectionChange: () => {},
            onClearSelection: () => {},
            ...options
        };

        this.selectedItems = new Set();
        this.isInitialized = false;

        this.init();
        console.log('[SelectionManager] ✅ Selection manager initialized');
    }

    /**
     * Initialize selection manager
     */
    init() {
        this.bindEvents();
        this.updateUI();
        this.isInitialized = true;
    }

    /**
     * Bind selection events
     */
    bindEvents() {
        // Use event delegation for dynamic content
        document.addEventListener('change', (e) => {
            if (e.target.matches(this.options.selectAllSelector)) {
                this.handleSelectAll(e.target.checked);
            } else if (e.target.matches(this.options.itemCheckboxSelector)) {
                this.handleItemSelection(e.target);
            }
        });

        // Listen for table updates to reinitialize
        document.addEventListener('eventRegistration:tableUpdated', () => {
            this.reinitializeAfterTableUpdate();
        });

        console.log('[SelectionManager] 🎯 Event listeners attached');
    }

    /**
     * Handle select all checkbox
     */
    handleSelectAll(isChecked) {
        const checkboxes = document.querySelectorAll(this.options.itemCheckboxSelector);

        checkboxes.forEach(checkbox => {
            checkbox.checked = isChecked;
            const itemId = checkbox.value;

            if (isChecked) {
                this.selectedItems.add(itemId);
            } else {
                this.selectedItems.delete(itemId);
            }
        });

        this.updateUI();
        this.notifySelectionChange();

        console.log(`[SelectionManager] ${isChecked ? '✅' : '❌'} Select all: ${this.selectedItems.size} items`);
    }

    /**
     * Handle individual item selection
     */
    handleItemSelection(checkbox) {
        const itemId = checkbox.value;

        if (checkbox.checked) {
            this.selectedItems.add(itemId);
        } else {
            this.selectedItems.delete(itemId);
        }

        this.updateSelectAllState();
        this.updateUI();
        this.notifySelectionChange();

        console.log(`[SelectionManager] ${checkbox.checked ? '✅' : '❌'} Item ${itemId}: ${this.selectedItems.size} total selected`);
    }

    /**
     * Update select all checkbox state
     */
    updateSelectAllState() {
        const selectAllCheckbox = document.querySelector(this.options.selectAllSelector);
        if (!selectAllCheckbox) return;

        const checkboxes = document.querySelectorAll(this.options.itemCheckboxSelector);
        const checkedBoxes = document.querySelectorAll(`${this.options.itemCheckboxSelector}:checked`);

        if (checkboxes.length === 0) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        } else if (checkedBoxes.length === checkboxes.length) {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = true;
        } else if (checkedBoxes.length > 0) {
            selectAllCheckbox.indeterminate = true;
            selectAllCheckbox.checked = false;
        } else {
            selectAllCheckbox.indeterminate = false;
            selectAllCheckbox.checked = false;
        }
    }

    /**
     * Update selection UI
     */
    updateUI() {
        this.updateBulkActionsVisibility();
        this.updateSelectedCount();
        this.updateSelectAllState();
    }

    /**
     * Update bulk actions visibility
     */
    updateBulkActionsVisibility() {
        const bulkActions = document.querySelector(this.options.bulkActionsSelector);
        if (!bulkActions) return;

        if (this.selectedItems.size > 0) {
            bulkActions.classList.remove('hidden');
            this.animateShow(bulkActions);
        } else {
            this.animateHide(bulkActions);
        }
    }

    /**
     * Update selected count display
     */
    updateSelectedCount() {
        const selectedCount = document.querySelector(this.options.selectedCountSelector);
        if (!selectedCount) return;

        const count = this.selectedItems.size;
        selectedCount.textContent = count === 1 ?
            '1 selezionato' :
            `${count} selezionati`;
    }

    /**
     * Animate show bulk actions
     */
    animateShow(element) {
        element.style.transform = 'translateY(10px)';
        element.style.opacity = '0';

        // Trigger reflow
        element.offsetHeight;

        element.style.transition = 'transform 0.2s ease-out, opacity 0.2s ease-out';
        element.style.transform = 'translateY(0)';
        element.style.opacity = '1';
    }

    /**
     * Animate hide bulk actions
     */
    animateHide(element) {
        element.style.transition = 'transform 0.2s ease-in, opacity 0.2s ease-in';
        element.style.transform = 'translateY(10px)';
        element.style.opacity = '0';

        setTimeout(() => {
            element.classList.add('hidden');
            element.style.transform = '';
            element.style.opacity = '';
            element.style.transition = '';
        }, 200);
    }

    /**
     * Clear all selections
     */
    clearSelection() {
        this.selectedItems.clear();

        // Uncheck all checkboxes
        const checkboxes = document.querySelectorAll(this.options.itemCheckboxSelector);
        checkboxes.forEach(checkbox => {
            checkbox.checked = false;
        });

        // Uncheck select all
        const selectAllCheckbox = document.querySelector(this.options.selectAllSelector);
        if (selectAllCheckbox) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        }

        this.updateUI();
        this.notifySelectionChange();

        console.log('[SelectionManager] 🧹 Selection cleared');
    }

    /**
     * Select specific items
     */
    selectItems(itemIds) {
        this.clearSelection();

        itemIds.forEach(itemId => {
            this.selectedItems.add(itemId.toString());

            // Check the checkbox if it exists
            const checkbox = document.querySelector(`${this.options.itemCheckboxSelector}[value="${itemId}"]`);
            if (checkbox) {
                checkbox.checked = true;
            }
        });

        this.updateUI();
        this.notifySelectionChange();

        console.log(`[SelectionManager] 🎯 Selected specific items: ${itemIds.join(', ')}`);
    }

    /**
     * Toggle item selection
     */
    toggleItem(itemId) {
        const checkbox = document.querySelector(`${this.options.itemCheckboxSelector}[value="${itemId}"]`);
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            this.handleItemSelection(checkbox);
        }
    }

    /**
     * Get selected items as array
     */
    getSelectedItems() {
        return Array.from(this.selectedItems);
    }

    /**
     * Get selected items count
     */
    getSelectedCount() {
        return this.selectedItems.size;
    }

    /**
     * Check if any items are selected
     */
    hasSelection() {
        return this.selectedItems.size > 0;
    }

    /**
     * Check if specific item is selected
     */
    isItemSelected(itemId) {
        return this.selectedItems.has(itemId.toString());
    }

    /**
     * Get selection info for display
     */
    getSelectionInfo() {
        const count = this.selectedItems.size;
        const totalItems = document.querySelectorAll(this.options.itemCheckboxSelector).length;

        return {
            count,
            totalItems,
            percentage: totalItems > 0 ? Math.round((count / totalItems) * 100) : 0,
            items: this.getSelectedItems()
        };
    }

    /**
     * Reinitialize after table update
     */
    reinitializeAfterTableUpdate() {
        // Restore selection state for items that still exist
        const stillExistingItems = new Set();
        const checkboxes = document.querySelectorAll(this.options.itemCheckboxSelector);

        checkboxes.forEach(checkbox => {
            const itemId = checkbox.value;
            if (this.selectedItems.has(itemId)) {
                checkbox.checked = true;
                stillExistingItems.add(itemId);
            }
        });

        // Update selected items to only include existing ones
        this.selectedItems = stillExistingItems;

        // Update UI
        this.updateUI();

        // Notify if selection changed
        if (this.selectedItems.size !== stillExistingItems.size) {
            this.notifySelectionChange();
        }

        console.log(`[SelectionManager] 🔄 Reinitialized: ${this.selectedItems.size} items still selected`);
    }

    /**
     * Notify about selection changes
     */
    notifySelectionChange() {
        const selectedItems = this.getSelectedItems();
        const selectionInfo = this.getSelectionInfo();

        this.options.onSelectionChange(selectedItems);

        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('eventRegistration:selectionChanged', {
            detail: { selectedItems, selectionInfo }
        }));
    }

    /**
     * Export selection data
     */
    exportSelectionData() {
        return {
            selectedItems: this.getSelectedItems(),
            selectionInfo: this.getSelectionInfo(),
            timestamp: Date.now()
        };
    }

    /**
     * Import selection data
     */
    importSelectionData(data) {
        if (data && data.selectedItems) {
            this.selectItems(data.selectedItems);
            console.log(`[SelectionManager] 📥 Imported selection: ${data.selectedItems.length} items`);
        }
    }

    /**
     * Validate selection
     */
    validateSelection() {
        const errors = [];
        const selectedItems = this.getSelectedItems();

        if (selectedItems.length === 0) {
            errors.push('Nessun elemento selezionato');
        }

        // Check if selected items still exist in DOM
        selectedItems.forEach(itemId => {
            const checkbox = document.querySelector(`${this.options.itemCheckboxSelector}[value="${itemId}"]`);
            if (!checkbox) {
                errors.push(`Elemento ${itemId} non più disponibile`);
            }
        });

        return {
            isValid: errors.length === 0,
            errors
        };
    }

    /**
     * Destroy selection manager
     */
    destroy() {
        this.clearSelection();
        this.isInitialized = false;
        console.log('[SelectionManager] 🔥 Selection manager destroyed');
    }
}