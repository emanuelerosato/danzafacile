/**
 * FilterManager.js
 *
 * Manages payment filtering, searching, and form state
 * Handles real-time filtering, state persistence, and filter reset
 *
 * @version 1.0.0
 */

export class FilterManager {
    constructor(options = {}) {
        this.options = {
            form: null,
            debounceDelay: 300,
            enableAutoSubmit: true,
            enableStateStorage: true,
            onFilterChange: null,
            debug: false,
            ...options
        };

        this.state = {
            currentFilters: {},
            isFiltering: false,
            lastSearchQuery: '',
            filterHistory: []
        };

        this.elements = {};
        this.debounceTimer = null;

        this.init();
    }

    /**
     * Initialize the FilterManager
     */
    init() {
        console.log('[FilterManager] 🔍 Initializing Filter Manager');

        this.cacheElements();
        this.loadStoredFilters();
        this.attachEventListeners();
        this.initializeCurrentFilters();

        console.log('[FilterManager] ✅ Filter Manager initialized');
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        const form = this.options.form;
        if (!form) {
            console.error('[FilterManager] ❌ No form provided');
            return;
        }

        this.elements = {
            form: form,
            searchInput: form.querySelector('#search'),
            statusSelect: form.querySelector('#status'),
            paymentMethodSelect: form.querySelector('#payment_method'),
            paymentTypeSelect: form.querySelector('#payment_type'),
            dateFromInput: form.querySelector('input[name="date_from"]'),
            dateToInput: form.querySelector('input[name="date_to"]'),
            submitButton: form.querySelector('button[type="submit"]'),
            resetButton: form.querySelector('a[href*="payments.index"]'),
            exportButton: form.querySelector('a[href*="export"]'),
            filterCount: document.querySelector('[data-filter-count]'),
            clearFiltersBtn: document.querySelector('[data-clear-filters]')
        };

        console.log('[FilterManager] 🎯 Elements cached');
    }

    /**
     * Load stored filters from localStorage
     */
    loadStoredFilters() {
        if (!this.options.enableStateStorage) return;

        try {
            const stored = localStorage.getItem('payments_filters');
            if (stored) {
                const filters = JSON.parse(stored);
                this.applyStoredFilters(filters);
            }
        } catch (error) {
            console.warn('[FilterManager] Failed to load stored filters:', error);
        }
    }

    /**
     * Apply stored filters to form elements
     */
    applyStoredFilters(filters) {
        Object.keys(filters).forEach(key => {
            const element = this.elements.form?.querySelector(`[name="${key}"]`);
            if (element && filters[key]) {
                element.value = filters[key];
            }
        });
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Search input with debouncing
        if (this.elements.searchInput) {
            this.elements.searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value);
            });

            this.elements.searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    this.submitFilters();
                }
            });
        }

        // Select elements
        ['statusSelect', 'paymentMethodSelect', 'paymentTypeSelect'].forEach(key => {
            if (this.elements[key]) {
                this.elements[key].addEventListener('change', () => {
                    this.handleFilterChange();
                });
            }
        });

        // Date inputs
        ['dateFromInput', 'dateToInput'].forEach(key => {
            if (this.elements[key]) {
                this.elements[key].addEventListener('change', () => {
                    this.handleDateChange();
                });
            }
        });

        // Form submission
        if (this.elements.form) {
            this.elements.form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.submitFilters();
            });
        }

        // Reset button
        if (this.elements.resetButton) {
            this.elements.resetButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.resetFilters();
            });
        }

        // Clear filters button
        if (this.elements.clearFiltersBtn) {
            this.elements.clearFiltersBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.clearAllFilters();
            });
        }

        console.log('[FilterManager] 🎧 Event listeners attached');
    }

    /**
     * Initialize current filters from form state
     */
    initializeCurrentFilters() {
        this.state.currentFilters = this.getCurrentFilters();
        this.updateFilterCount();
        this.updateExportUrl();
    }

    /**
     * Handle search input with debouncing
     */
    handleSearchInput(value) {
        this.state.lastSearchQuery = value;

        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        this.debounceTimer = setTimeout(() => {
            if (this.options.enableAutoSubmit) {
                this.handleFilterChange();
            }
        }, this.options.debounceDelay);
    }

    /**
     * Handle filter change (select elements)
     */
    handleFilterChange() {
        if (this.options.enableAutoSubmit) {
            this.submitFilters();
        } else {
            this.updateFilterCount();
        }
    }

    /**
     * Handle date range changes with validation
     */
    handleDateChange() {
        const dateFrom = this.elements.dateFromInput?.value;
        const dateTo = this.elements.dateToInput?.value;

        // Validate date range
        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            this.showNotification('La data di inizio non può essere successiva alla data di fine', 'warning');
            return;
        }

        if (this.options.enableAutoSubmit) {
            this.submitFilters();
        } else {
            this.updateFilterCount();
        }
    }

    /**
     * Submit filters
     */
    async submitFilters() {
        const filters = this.getCurrentFilters();

        this.setFilteringState(true);
        this.storeFilters(filters);

        try {
            // Add filters to history
            this.addToHistory(filters);

            // Update state
            this.state.currentFilters = filters;
            this.updateFilterCount();
            this.updateExportUrl();

            // Notify parent component
            if (this.options.onFilterChange) {
                this.options.onFilterChange(filters);
            }

            // Submit form normally (page reload with filters)
            this.elements.form.submit();

        } catch (error) {
            console.error('[FilterManager] Error applying filters:', error);
            this.showNotification('Errore durante l\'applicazione dei filtri', 'error');
        } finally {
            this.setFilteringState(false);
        }
    }

    /**
     * Reset all filters
     */
    resetFilters() {
        if (this.elements.form) {
            this.elements.form.reset();
        }

        this.state.currentFilters = {};
        this.state.lastSearchQuery = '';

        this.clearStoredFilters();
        this.updateFilterCount();
        this.updateExportUrl();

        // Navigate to clean URL
        window.location.href = this.elements.resetButton.href;
    }

    /**
     * Clear all filters without page reload
     */
    clearAllFilters() {
        if (this.elements.form) {
            this.elements.form.reset();
        }

        this.state.currentFilters = {};
        this.state.lastSearchQuery = '';

        this.clearStoredFilters();
        this.updateFilterCount();
        this.updateExportUrl();

        // Optionally trigger filter change
        if (this.options.onFilterChange) {
            this.options.onFilterChange({});
        }
    }

    /**
     * Get current filter values from form
     */
    getCurrentFilters() {
        const filters = {};
        const formData = new FormData(this.elements.form);

        for (const [key, value] of formData.entries()) {
            if (value && value.trim() !== '') {
                filters[key] = value.trim();
            }
        }

        return filters;
    }

    /**
     * Update filter count display
     */
    updateFilterCount() {
        const activeFilters = Object.keys(this.state.currentFilters).length;

        if (this.elements.filterCount) {
            this.elements.filterCount.textContent = activeFilters;
            this.elements.filterCount.style.display = activeFilters > 0 ? 'inline' : 'none';
        }

        // Update clear filters button visibility
        if (this.elements.clearFiltersBtn) {
            this.elements.clearFiltersBtn.style.display = activeFilters > 0 ? 'inline-flex' : 'none';
        }
    }

    /**
     * Update export URL with current filters
     */
    updateExportUrl() {
        if (!this.elements.exportButton) return;

        const baseUrl = this.elements.exportButton.href.split('?')[0];
        const params = new URLSearchParams(this.state.currentFilters);

        this.elements.exportButton.href = params.toString() ?
            `${baseUrl}?${params.toString()}` : baseUrl;
    }

    /**
     * Store filters in localStorage
     */
    storeFilters(filters) {
        if (!this.options.enableStateStorage) return;

        try {
            localStorage.setItem('payments_filters', JSON.stringify(filters));
        } catch (error) {
            console.warn('[FilterManager] Failed to store filters:', error);
        }
    }

    /**
     * Clear stored filters
     */
    clearStoredFilters() {
        if (!this.options.enableStateStorage) return;

        try {
            localStorage.removeItem('payments_filters');
        } catch (error) {
            console.warn('[FilterManager] Failed to clear stored filters:', error);
        }
    }

    /**
     * Add filters to history
     */
    addToHistory(filters) {
        this.state.filterHistory.unshift({
            filters: { ...filters },
            timestamp: Date.now()
        });

        // Keep only last 10 entries
        this.state.filterHistory = this.state.filterHistory.slice(0, 10);
    }

    /**
     * Set filtering state
     */
    setFilteringState(isFiltering) {
        this.state.isFiltering = isFiltering;

        // Update UI to show loading state
        if (this.elements.submitButton) {
            this.elements.submitButton.disabled = isFiltering;
            this.elements.submitButton.textContent = isFiltering ? 'Applicando...' : 'Applica Filtri';
        }

        // Update form opacity
        if (this.elements.form) {
            this.elements.form.style.opacity = isFiltering ? '0.7' : '1';
        }
    }

    /**
     * Show notification (can be enhanced with a proper notification system)
     */
    showNotification(message, type = 'info') {
        console.log(`[FilterManager] ${type.toUpperCase()}: ${message}`);

        // For now, use alert - can be enhanced with a proper notification system
        if (type === 'error' || type === 'warning') {
            alert(message);
        }
    }

    /**
     * Get available quick filters
     */
    getQuickFilters() {
        return [
            {
                label: 'Completati oggi',
                filters: {
                    status: 'completed',
                    date_from: new Date().toISOString().split('T')[0]
                }
            },
            {
                label: 'In attesa',
                filters: {
                    status: 'pending'
                }
            },
            {
                label: 'Scaduti',
                filters: {
                    overdue: 'true'
                }
            },
            {
                label: 'Questo mese',
                filters: {
                    date_from: new Date(new Date().getFullYear(), new Date().getMonth(), 1).toISOString().split('T')[0],
                    date_to: new Date().toISOString().split('T')[0]
                }
            }
        ];
    }

    /**
     * Apply quick filter
     */
    applyQuickFilter(quickFilter) {
        // Clear current form
        this.elements.form.reset();

        // Apply quick filter values
        Object.keys(quickFilter.filters).forEach(key => {
            const element = this.elements.form.querySelector(`[name="${key}"]`);
            if (element) {
                element.value = quickFilter.filters[key];
            }
        });

        // Submit filters
        this.submitFilters();
    }

    /**
     * Export current filter state
     */
    exportFilterState() {
        return {
            currentFilters: { ...this.state.currentFilters },
            lastSearchQuery: this.state.lastSearchQuery,
            filterHistory: [...this.state.filterHistory]
        };
    }

    /**
     * Import filter state
     */
    importFilterState(state) {
        this.state.currentFilters = state.currentFilters || {};
        this.state.lastSearchQuery = state.lastSearchQuery || '';
        this.state.filterHistory = state.filterHistory || [];

        this.applyStoredFilters(this.state.currentFilters);
        this.updateFilterCount();
        this.updateExportUrl();
    }

    /**
     * Destroy the filter manager
     */
    destroy() {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        // Remove event listeners would go here if needed
        console.log('[FilterManager] 🗑️ Filter Manager destroyed');
    }

    /**
     * Get debug information
     */
    getDebugInfo() {
        return {
            state: this.state,
            options: this.options,
            elements: Object.keys(this.elements).reduce((acc, key) => {
                acc[key] = !!this.elements[key];
                return acc;
            }, {})
        };
    }
}

export default FilterManager;