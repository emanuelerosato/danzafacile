/**
 * FilterManager - Handles filtering and search functionality for staff schedules
 *
 * Provides filtering capabilities:
 * - Staff member filtering
 * - Schedule type filtering
 * - Date range filtering
 * - Status filtering
 * - Real-time search with debouncing
 * - Filter state persistence
 */

export class FilterManager {
    constructor(options = {}) {
        this.options = {
            manager: null,
            debounceDelay: 300,
            persistState: true,
            storageKey: 'staff_schedules_filters',
            ...options
        };

        // Reference to main manager
        this.manager = this.options.manager;

        // Filter state
        this.state = {
            filters: {
                staff_id: '',
                type: '',
                status: '',
                date_from: '',
                date_to: '',
                search: ''
            },
            activeFilters: new Set(),
            isLoading: false,
            resultCount: 0
        };

        // Filter elements
        this.elements = new Map();

        // Debounce timers
        this.debounceTimers = new Map();

        // Initialize
        this.init();
    }

    /**
     * Initialize the filter manager
     */
    async init() {
        try {
            this.log('Initializing FilterManager...');

            // Setup filter elements
            this.setupFilterElements();

            // Load persisted state
            if (this.options.persistState) {
                this.loadPersistedState();
            }

            // Setup event listeners
            this.setupEventListeners();

            // Setup filter UI
            this.setupFilterUI();

            // Apply initial filters
            await this.applyFilters();

            this.log('FilterManager initialized successfully');

        } catch (error) {
            console.error('Failed to initialize FilterManager:', error);
            this.manager.modules.notification?.error('Errore nell\'inizializzazione dei filtri');
        }
    }

    /**
     * Setup filter elements mapping
     */
    setupFilterElements() {
        const filterSelectors = {
            staff_id: '#filter-staff',
            type: '#filter-type',
            status: '#filter-status',
            date_from: '#filter-date-from',
            date_to: '#filter-date-to',
            search: '#filter-search',
            reset: '#reset-filters-btn',
            toggle: '#toggle-filters-btn'
        };

        Object.entries(filterSelectors).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element) {
                this.elements.set(key, element);
            }
        });

        this.log('Filter elements mapped:', this.elements.size);
    }

    /**
     * Load persisted filter state from localStorage
     */
    loadPersistedState() {
        try {
            const saved = localStorage.getItem(this.options.storageKey);
            if (saved) {
                const persistedState = JSON.parse(saved);
                this.state.filters = { ...this.state.filters, ...persistedState };

                // Apply saved values to form elements
                Object.entries(this.state.filters).forEach(([key, value]) => {
                    const element = this.elements.get(key);
                    if (element && value) {
                        element.value = value;
                        this.state.activeFilters.add(key);
                    }
                });

                this.log('Loaded persisted filter state');
            }
        } catch (error) {
            this.log('Error loading persisted state:', error);
        }
    }

    /**
     * Persist filter state to localStorage
     */
    persistState() {
        if (!this.options.persistState) return;

        try {
            localStorage.setItem(this.options.storageKey, JSON.stringify(this.state.filters));
        } catch (error) {
            this.log('Error persisting state:', error);
        }
    }

    /**
     * Setup event listeners for filter elements
     */
    setupEventListeners() {
        // Filter change events
        ['staff_id', 'type', 'status'].forEach(filterKey => {
            const element = this.elements.get(filterKey);
            if (element) {
                element.addEventListener('change', () => {
                    this.handleFilterChange(filterKey, element.value);
                });
            }
        });

        // Date range filters
        ['date_from', 'date_to'].forEach(filterKey => {
            const element = this.elements.get(filterKey);
            if (element) {
                element.addEventListener('change', () => {
                    this.handleDateFilterChange(filterKey, element.value);
                });
            }
        });

        // Search input with debouncing
        const searchElement = this.elements.get('search');
        if (searchElement) {
            searchElement.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value);
            });

            // Clear search on escape
            searchElement.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.clearSearch();
                }
            });
        }

        // Reset filters button
        const resetElement = this.elements.get('reset');
        if (resetElement) {
            resetElement.addEventListener('click', () => {
                this.resetFilters();
            });
        }

        // Toggle filters visibility
        const toggleElement = this.elements.get('toggle');
        if (toggleElement) {
            toggleElement.addEventListener('click', () => {
                this.toggleFiltersVisibility();
            });
        }

        this.log('Filter event listeners setup completed');
    }

    /**
     * Setup filter UI components
     */
    setupFilterUI() {
        // Create filter summary if not exists
        this.createFilterSummary();

        // Update active filter indicators
        this.updateActiveFilterIndicators();

        // Setup filter badges
        this.setupFilterBadges();

        this.log('Filter UI setup completed');
    }

    /**
     * Create filter summary display
     */
    createFilterSummary() {
        const existingSummary = document.getElementById('filter-summary');
        if (existingSummary) return;

        const summaryHtml = `
            <div id="filter-summary" class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6 hidden">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <span class="text-sm font-medium text-gray-700">Filtri attivi:</span>
                        <div id="active-filter-badges" class="flex items-center space-x-2">
                            <!-- Filter badges will be rendered here -->
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span id="filter-result-count" class="text-sm text-gray-600"></span>
                        <button id="clear-all-filters" class="text-sm text-rose-600 hover:text-rose-700">
                            Rimuovi tutti
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Insert after filter form or at the beginning of content
        const filterForm = document.querySelector('.filter-form, .filters-container');
        const targetContainer = filterForm?.parentNode || document.querySelector('.space-y-6');

        if (targetContainer) {
            if (filterForm) {
                filterForm.insertAdjacentHTML('afterend', summaryHtml);
            } else {
                targetContainer.insertAdjacentHTML('afterbegin', summaryHtml);
            }

            // Setup clear all button
            const clearAllBtn = document.getElementById('clear-all-filters');
            if (clearAllBtn) {
                clearAllBtn.addEventListener('click', () => {
                    this.resetFilters();
                });
            }
        }
    }

    /**
     * Setup filter badges container
     */
    setupFilterBadges() {
        const badgesContainer = document.getElementById('active-filter-badges');
        if (!badgesContainer) return;

        // Clear existing badges
        badgesContainer.innerHTML = '';

        // Render active filter badges
        this.renderFilterBadges(badgesContainer);
    }

    /**
     * Render filter badges
     */
    renderFilterBadges(container) {
        const filterLabels = {
            staff_id: 'Staff',
            type: 'Tipo',
            status: 'Stato',
            date_from: 'Da',
            date_to: 'A',
            search: 'Ricerca'
        };

        this.state.activeFilters.forEach(filterKey => {
            const value = this.state.filters[filterKey];
            if (!value) return;

            const badge = document.createElement('span');
            badge.className = 'inline-flex items-center px-2 py-1 bg-rose-100 text-rose-800 text-xs font-medium rounded-full';
            badge.innerHTML = `
                ${filterLabels[filterKey]}: ${this.getFilterDisplayValue(filterKey, value)}
                <button type="button" class="ml-1 w-4 h-4 text-rose-600 hover:text-rose-800" data-remove-filter="${filterKey}">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            `;

            // Setup remove button
            const removeBtn = badge.querySelector('[data-remove-filter]');
            if (removeBtn) {
                removeBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    this.removeFilter(filterKey);
                });
            }

            container.appendChild(badge);
        });
    }

    /**
     * Get display value for filter
     */
    getFilterDisplayValue(filterKey, value) {
        switch (filterKey) {
            case 'staff_id':
                const staffElement = this.elements.get('staff_id');
                const selectedOption = staffElement?.querySelector(`option[value="${value}"]`);
                return selectedOption?.textContent || value;

            case 'type':
            case 'status':
                const element = this.elements.get(filterKey);
                const option = element?.querySelector(`option[value="${value}"]`);
                return option?.textContent || value;

            case 'date_from':
            case 'date_to':
                return this.formatDateDisplay(value);

            case 'search':
                return value.length > 20 ? value.substring(0, 20) + '...' : value;

            default:
                return value;
        }
    }

    /**
     * Format date for display
     */
    formatDateDisplay(dateString) {
        try {
            const date = new Date(dateString);
            return date.toLocaleDateString('it-IT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        } catch {
            return dateString;
        }
    }

    /**
     * Handle filter change
     */
    handleFilterChange(filterKey, value) {
        this.updateFilter(filterKey, value);
        this.debouncedApplyFilters();
    }

    /**
     * Handle date filter change
     */
    handleDateFilterChange(filterKey, value) {
        this.updateFilter(filterKey, value);

        // Validate date range
        if (this.validateDateRange()) {
            this.debouncedApplyFilters();
        }
    }

    /**
     * Handle search input
     */
    handleSearchInput(value) {
        this.updateFilter('search', value);
        this.debouncedApplyFilters();
    }

    /**
     * Update single filter
     */
    updateFilter(filterKey, value) {
        const oldValue = this.state.filters[filterKey];
        this.state.filters[filterKey] = value;

        // Update active filters set
        if (value && value.trim() !== '') {
            this.state.activeFilters.add(filterKey);
        } else {
            this.state.activeFilters.delete(filterKey);
        }

        // Update UI
        if (oldValue !== value) {
            this.updateFilterUI();
            this.persistState();
        }
    }

    /**
     * Validate date range
     */
    validateDateRange() {
        const dateFrom = this.state.filters.date_from;
        const dateTo = this.state.filters.date_to;

        if (dateFrom && dateTo && new Date(dateFrom) > new Date(dateTo)) {
            this.manager.modules.notification?.warning('La data di inizio deve essere precedente alla data di fine');
            return false;
        }

        return true;
    }

    /**
     * Debounced apply filters
     */
    debouncedApplyFilters() {
        clearTimeout(this.debounceTimers.get('apply'));
        this.debounceTimers.set('apply', setTimeout(() => {
            this.applyFilters();
        }, this.options.debounceDelay));
    }

    /**
     * Apply filters
     */
    async applyFilters() {
        this.state.isLoading = true;
        this.updateLoadingState();

        try {
            // Update manager filters
            this.manager.updateFilters(this.state.filters);

            // Load filtered data
            const result = await this.manager.loadSchedules(this.state.filters);

            // Update result count
            this.state.resultCount = result.schedules?.length || 0;
            this.updateResultCount();

            this.log('Filters applied successfully', this.state.filters);

        } catch (error) {
            this.log('Error applying filters:', error);
            this.manager.modules.notification?.error('Errore nell\'applicazione dei filtri');
        } finally {
            this.state.isLoading = false;
            this.updateLoadingState();
        }
    }

    /**
     * Reset all filters
     */
    resetFilters() {
        // Clear all filter values
        Object.keys(this.state.filters).forEach(key => {
            this.state.filters[key] = '';
            const element = this.elements.get(key);
            if (element) {
                element.value = '';
            }
        });

        // Clear active filters
        this.state.activeFilters.clear();

        // Update UI
        this.updateFilterUI();

        // Apply empty filters
        this.applyFilters();

        // Clear persisted state
        if (this.options.persistState) {
            localStorage.removeItem(this.options.storageKey);
        }

        this.log('All filters reset');
    }

    /**
     * Remove specific filter
     */
    removeFilter(filterKey) {
        const element = this.elements.get(filterKey);
        if (element) {
            element.value = '';
        }

        this.updateFilter(filterKey, '');
        this.applyFilters();

        this.log('Filter removed:', filterKey);
    }

    /**
     * Clear search
     */
    clearSearch() {
        const searchElement = this.elements.get('search');
        if (searchElement) {
            searchElement.value = '';
            searchElement.focus();
        }

        this.updateFilter('search', '');
        this.applyFilters();
    }

    /**
     * Toggle filters visibility
     */
    toggleFiltersVisibility() {
        const filterContainer = document.querySelector('.filters-container, .filter-form');
        if (!filterContainer) return;

        const isHidden = filterContainer.classList.contains('hidden');

        if (isHidden) {
            filterContainer.classList.remove('hidden');
        } else {
            filterContainer.classList.add('hidden');
        }

        // Update toggle button text
        const toggleElement = this.elements.get('toggle');
        if (toggleElement) {
            toggleElement.textContent = isHidden ? 'Nascondi filtri' : 'Mostra filtri';
        }
    }

    /**
     * Update filter UI components
     */
    updateFilterUI() {
        this.updateActiveFilterIndicators();
        this.updateFilterSummary();
        this.setupFilterBadges();
    }

    /**
     * Update active filter indicators
     */
    updateActiveFilterIndicators() {
        // Update individual filter elements visual state
        this.state.activeFilters.forEach(filterKey => {
            const element = this.elements.get(filterKey);
            if (element) {
                element.classList.add('border-rose-500', 'ring-1', 'ring-rose-500');
            }
        });

        // Remove indicators from inactive filters
        Object.keys(this.state.filters).forEach(filterKey => {
            if (!this.state.activeFilters.has(filterKey)) {
                const element = this.elements.get(filterKey);
                if (element) {
                    element.classList.remove('border-rose-500', 'ring-1', 'ring-rose-500');
                }
            }
        });
    }

    /**
     * Update filter summary visibility
     */
    updateFilterSummary() {
        const summary = document.getElementById('filter-summary');
        if (!summary) return;

        if (this.state.activeFilters.size > 0) {
            summary.classList.remove('hidden');
        } else {
            summary.classList.add('hidden');
        }
    }

    /**
     * Update result count display
     */
    updateResultCount() {
        const countElement = document.getElementById('filter-result-count');
        if (countElement) {
            const count = this.state.resultCount;
            countElement.textContent = `${count} risultat${count === 1 ? 'o' : 'i'}`;
        }
    }

    /**
     * Update loading state
     */
    updateLoadingState() {
        const applyBtn = document.querySelector('[data-filter-apply]');
        if (applyBtn) {
            applyBtn.disabled = this.state.isLoading;

            if (this.state.isLoading) {
                applyBtn.classList.add('opacity-50', 'cursor-not-allowed');
                applyBtn.textContent = 'Caricamento...';
            } else {
                applyBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                applyBtn.textContent = 'Applica filtri';
            }
        }
    }

    /**
     * Get current filters
     */
    getFilters() {
        return { ...this.state.filters };
    }

    /**
     * Get active filters
     */
    getActiveFilters() {
        const active = {};
        this.state.activeFilters.forEach(filterKey => {
            active[filterKey] = this.state.filters[filterKey];
        });
        return active;
    }

    /**
     * Check if any filters are active
     */
    hasActiveFilters() {
        return this.state.activeFilters.size > 0;
    }

    /**
     * Pre-fill filters (useful for navigation from other pages)
     */
    setFilters(filters) {
        Object.entries(filters).forEach(([key, value]) => {
            if (value !== null && value !== undefined && value !== '') {
                const element = this.elements.get(key);
                if (element) {
                    element.value = value;
                }
                this.updateFilter(key, value);
            }
        });

        this.applyFilters();
    }

    /**
     * Debug logging
     */
    log(...args) {
        if (this.manager?.options.debug) {
            console.log('[FilterManager]', ...args);
        }
    }

    /**
     * Cleanup
     */
    destroy() {
        // Clear debounce timers
        this.debounceTimers.forEach(timer => clearTimeout(timer));
        this.debounceTimers.clear();

        // Clear references
        this.elements.clear();
        this.state.activeFilters.clear();

        this.log('FilterManager destroyed');
    }
}