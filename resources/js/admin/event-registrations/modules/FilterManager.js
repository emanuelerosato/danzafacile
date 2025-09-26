/**
 * FilterManager - Handles advanced filtering functionality
 *
 * Features:
 * - Real-time search with debounce
 * - Multi-field filtering
 * - Filter validation
 * - URL state management
 */

export default class FilterManager {
    constructor(options = {}) {
        this.options = {
            formSelector: '#filtersForm',
            debounceDelay: 300,
            onFilterChange: () => {},
            onClearFilters: () => {},
            ...options
        };

        this.form = document.querySelector(this.options.formSelector);
        this.debounceTimer = null;
        this.currentFilters = this.extractFiltersFromForm();

        this.init();
        console.log('[FilterManager] ✅ Filter manager initialized');
    }

    /**
     * Initialize filter manager
     */
    init() {
        if (!this.form) {
            console.error('[FilterManager] Form not found:', this.options.formSelector);
            return;
        }

        this.bindEvents();
        this.initializeFromURL();
    }

    /**
     * Bind form events
     */
    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleFormSubmit();
        });

        // Real-time search with debounce
        const searchInput = this.form.querySelector('#search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.handleSearchInput(e.target.value);
            });
        }

        // Select changes
        const selectElements = this.form.querySelectorAll('select');
        selectElements.forEach(select => {
            select.addEventListener('change', () => {
                this.handleFilterChange();
            });
        });

        // Date inputs
        const dateInputs = this.form.querySelectorAll('input[type="date"]');
        dateInputs.forEach(dateInput => {
            dateInput.addEventListener('change', () => {
                this.validateDateRange();
                this.handleFilterChange();
            });
        });

        console.log('[FilterManager] 🎯 Events bound to form elements');
    }

    /**
     * Handle search input with debounce
     */
    handleSearchInput(value) {
        if (this.debounceTimer) {
            clearTimeout(this.debounceTimer);
        }

        this.debounceTimer = setTimeout(() => {
            const filters = this.extractFiltersFromForm();

            // Only trigger if search actually changed
            if (filters.search !== this.currentFilters.search) {
                this.currentFilters = filters;
                this.options.onFilterChange(filters);
                this.updateURL(filters);
            }
        }, this.options.debounceDelay);
    }

    /**
     * Handle form submission
     */
    handleFormSubmit() {
        const filters = this.extractFiltersFromForm();

        if (this.validateFilters(filters)) {
            this.currentFilters = filters;
            this.options.onFilterChange(filters);
            this.updateURL(filters);

            // Dispatch custom event
            document.dispatchEvent(new CustomEvent('eventRegistration:filterApplied', {
                detail: { filters }
            }));
        }
    }

    /**
     * Handle general filter change
     */
    handleFilterChange() {
        const filters = this.extractFiltersFromForm();

        if (this.validateFilters(filters)) {
            this.currentFilters = filters;
            this.options.onFilterChange(filters);
            this.updateURL(filters);
        }
    }

    /**
     * Clear all filters
     */
    clearFilters() {
        // Reset form
        this.form.reset();

        // Clear URL parameters
        this.updateURL({});

        // Reset current filters
        this.currentFilters = {
            search: '',
            event_id: '',
            status: '',
            date_from: '',
            date_to: ''
        };

        // Trigger callback
        this.options.onClearFilters();

        // Dispatch custom event
        document.dispatchEvent(new CustomEvent('eventRegistration:filtersCleared'));

        console.log('[FilterManager] 🧹 Filters cleared');
    }

    /**
     * Extract filters from form
     */
    extractFiltersFromForm() {
        const formData = new FormData(this.form);
        const filters = {};

        // Get all form fields
        for (let [key, value] of formData.entries()) {
            filters[key] = value.trim();
        }

        // Ensure all expected fields exist
        return {
            search: filters.search || '',
            event_id: filters.event_id || '',
            status: filters.status || '',
            date_from: filters.date_from || '',
            date_to: filters.date_to || '',
            ...filters
        };
    }

    /**
     * Validate filters
     */
    validateFilters(filters) {
        const errors = [];

        // Date range validation
        if (filters.date_from && filters.date_to) {
            const fromDate = new Date(filters.date_from);
            const toDate = new Date(filters.date_to);

            if (fromDate > toDate) {
                errors.push('La data di inizio deve essere precedente alla data di fine');
            }
        }

        // Future date validation
        if (filters.date_from) {
            const fromDate = new Date(filters.date_from);
            const maxDate = new Date();
            maxDate.setFullYear(maxDate.getFullYear() + 2); // Allow 2 years in future

            if (fromDate > maxDate) {
                errors.push('La data di inizio non può essere troppo nel futuro');
            }
        }

        // Search length validation
        if (filters.search && filters.search.length > 0 && filters.search.length < 2) {
            errors.push('La ricerca deve contenere almeno 2 caratteri');
        }

        // Display errors if any
        if (errors.length > 0) {
            this.showValidationErrors(errors);
            return false;
        }

        this.clearValidationErrors();
        return true;
    }

    /**
     * Validate date range specifically
     */
    validateDateRange() {
        const dateFromInput = this.form.querySelector('#date_from');
        const dateToInput = this.form.querySelector('#date_to');

        if (dateFromInput && dateToInput && dateFromInput.value && dateToInput.value) {
            const fromDate = new Date(dateFromInput.value);
            const toDate = new Date(dateToInput.value);

            if (fromDate > toDate) {
                dateToInput.setCustomValidity('La data di fine deve essere successiva alla data di inizio');
                dateToInput.classList.add('border-red-500');
            } else {
                dateToInput.setCustomValidity('');
                dateToInput.classList.remove('border-red-500');
            }
        }
    }

    /**
     * Show validation errors
     */
    showValidationErrors(errors) {
        // Remove existing error messages
        this.clearValidationErrors();

        // Create error container
        const errorContainer = document.createElement('div');
        errorContainer.className = 'validation-errors bg-red-50 border border-red-200 rounded-lg p-4 mb-4';
        errorContainer.innerHTML = `
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="text-red-800 font-medium mb-1">Errori di validazione:</h4>
                    <ul class="text-red-700 text-sm space-y-1">
                        ${errors.map(error => `<li>• ${error}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;

        // Insert error container before form
        this.form.parentNode.insertBefore(errorContainer, this.form);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.clearValidationErrors();
        }, 5000);
    }

    /**
     * Clear validation errors
     */
    clearValidationErrors() {
        const existingErrors = document.querySelectorAll('.validation-errors');
        existingErrors.forEach(error => error.remove());

        // Clear input error states
        const errorInputs = this.form.querySelectorAll('.border-red-500');
        errorInputs.forEach(input => {
            input.classList.remove('border-red-500');
            input.setCustomValidity('');
        });
    }

    /**
     * Update URL with current filters
     */
    updateURL(filters) {
        const url = new URL(window.location);
        const params = url.searchParams;

        // Clear existing filter parameters
        ['search', 'event_id', 'status', 'date_from', 'date_to'].forEach(key => {
            params.delete(key);
        });

        // Add current filters
        Object.entries(filters).forEach(([key, value]) => {
            if (value && value.toString().trim()) {
                params.set(key, value);
            }
        });

        // Update URL without page reload
        history.replaceState({}, '', url.toString());
    }

    /**
     * Initialize filters from URL parameters
     */
    initializeFromURL() {
        const urlParams = new URLSearchParams(window.location.search);
        const filters = {};

        // Extract filter parameters from URL
        ['search', 'event_id', 'status', 'date_from', 'date_to'].forEach(key => {
            const value = urlParams.get(key);
            if (value) {
                filters[key] = value;
            }
        });

        // Apply filters to form
        Object.entries(filters).forEach(([key, value]) => {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = value;
            }
        });

        this.currentFilters = this.extractFiltersFromForm();
        console.log('[FilterManager] 🔗 Initialized from URL parameters:', this.currentFilters);
    }

    /**
     * Set filters programmatically
     */
    setFilters(filters) {
        Object.entries(filters).forEach(([key, value]) => {
            const input = this.form.querySelector(`[name="${key}"]`);
            if (input) {
                input.value = value || '';
            }
        });

        this.currentFilters = this.extractFiltersFromForm();
        this.updateURL(this.currentFilters);
    }

    /**
     * Get current filters
     */
    getCurrentFilters() {
        return { ...this.currentFilters };
    }

    /**
     * Check if filters are active
     */
    hasActiveFilters() {
        return Object.values(this.currentFilters).some(value =>
            value && value.toString().trim() !== ''
        );
    }

    /**
     * Get filter summary for display
     */
    getFilterSummary() {
        const activeFilters = Object.entries(this.currentFilters)
            .filter(([key, value]) => value && value.toString().trim() !== '');

        if (activeFilters.length === 0) {
            return 'Nessun filtro attivo';
        }

        const summary = activeFilters.map(([key, value]) => {
            switch (key) {
                case 'search':
                    return `Ricerca: "${value}"`;
                case 'event_id':
                    const eventOption = this.form.querySelector(`[name="event_id"] option[value="${value}"]`);
                    return `Evento: ${eventOption ? eventOption.textContent : value}`;
                case 'status':
                    const statusMap = {
                        'registered': 'Registrato',
                        'confirmed': 'Confermato',
                        'waitlist': 'Lista Attesa',
                        'cancelled': 'Annullato',
                        'attended': 'Partecipato'
                    };
                    return `Status: ${statusMap[value] || value}`;
                case 'date_from':
                    return `Dal: ${value}`;
                case 'date_to':
                    return `Al: ${value}`;
                default:
                    return `${key}: ${value}`;
            }
        }).join(', ');

        return summary;
    }
}