/**
 * 🔍 FILTER MANAGER - Gestione Filtri e Ricerca Staff
 *
 * Gestisce:
 * - Ricerca in tempo reale
 * - Filtri per ruolo, dipartimento, status
 * - Persistenza filtri in sessionStorage
 * - Reset e clear filters
 * - Visual feedback per risultati
 */

export class FilterManager {
    constructor(staffManager) {
        this.staffManager = staffManager;
        this.currentFilters = {
            search: '',
            role: '',
            department: '',
            status: '',
            dateFrom: '',
            dateTo: ''
        };

        this.searchTimeout = null;
        this.isFiltering = false;

        this.initialize();
        console.log('🔍 FilterManager initialized');
    }

    /**
     * Inizializzazione
     */
    initialize() {
        this.restoreFiltersFromStorage();
        this.attachEventListeners();
        this.setupAdvancedFilters();
        this.updateFilterUI();
    }

    /**
     * Registra event listeners
     */
    attachEventListeners() {
        // Search input
        const searchInput = document.getElementById('staff-search');
        if (searchInput) {
            searchInput.addEventListener('input', this.handleSearchInput.bind(this));
            searchInput.addEventListener('focus', this.handleSearchFocus.bind(this));
            searchInput.addEventListener('blur', this.handleSearchBlur.bind(this));
        }

        // Filter dropdowns
        const filterElements = [
            { id: 'filter-role', key: 'role' },
            { id: 'filter-department', key: 'department' },
            { id: 'filter-status', key: 'status' }
        ];

        filterElements.forEach(({ id, key }) => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', (e) => {
                    this.updateFilter(key, e.target.value);
                });
            }
        });

        // Date range filters
        const dateFromInput = document.getElementById('filter-date-from');
        const dateToInput = document.getElementById('filter-date-to');

        if (dateFromInput) {
            dateFromInput.addEventListener('change', (e) => {
                this.updateFilter('dateFrom', e.target.value);
            });
        }

        if (dateToInput) {
            dateToInput.addEventListener('change', (e) => {
                this.updateFilter('dateTo', e.target.value);
            });
        }

        // Clear filters button
        const clearFiltersButton = document.getElementById('clear-filters');
        if (clearFiltersButton) {
            clearFiltersButton.addEventListener('click', this.clearAllFilters.bind(this));
        }

        // Advanced filters toggle
        const advancedToggle = document.getElementById('toggle-advanced-filters');
        if (advancedToggle) {
            advancedToggle.addEventListener('click', this.toggleAdvancedFilters.bind(this));
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboardShortcuts.bind(this));
    }

    // ==========================================
    // SEARCH FUNCTIONALITY
    // ==========================================

    /**
     * Gestisce input di ricerca con debouncing
     */
    handleSearchInput(event) {
        const query = event.target.value.trim();

        // Visual feedback immediate
        this.updateSearchUI(query.length > 0);

        // Clear previous timeout
        if (this.searchTimeout) {
            clearTimeout(this.searchTimeout);
        }

        // Debounced search
        this.searchTimeout = setTimeout(() => {
            this.updateFilter('search', query);
        }, 300);
    }

    /**
     * Focus su search input
     */
    handleSearchFocus(event) {
        const searchContainer = event.target.closest('.search-container');
        if (searchContainer) {
            searchContainer.classList.add('search-focused');
        }

        // Show search suggestions se disponibili
        this.showSearchSuggestions();
    }

    /**
     * Blur su search input
     */
    handleSearchBlur(event) {
        const searchContainer = event.target.closest('.search-container');
        if (searchContainer) {
            searchContainer.classList.remove('search-focused');
        }

        // Nascondi suggestions con delay per permettere click
        setTimeout(() => {
            this.hideSearchSuggestions();
        }, 200);
    }

    /**
     * Aggiorna UI di ricerca
     */
    updateSearchUI(hasQuery) {
        const searchInput = document.getElementById('staff-search');
        const searchIcon = document.querySelector('.search-icon');
        const clearSearchButton = document.querySelector('.clear-search');

        if (searchInput) {
            searchInput.classList.toggle('has-query', hasQuery);
        }

        if (searchIcon) {
            searchIcon.classList.toggle('text-gray-400', !hasQuery);
            searchIcon.classList.toggle('text-rose-500', hasQuery);
        }

        if (clearSearchButton) {
            clearSearchButton.style.display = hasQuery ? 'block' : 'none';
        }
    }

    /**
     * Mostra suggerimenti di ricerca
     */
    showSearchSuggestions() {
        // Implementazione per suggerimenti basati su staff esistenti
        const suggestions = this.generateSearchSuggestions();
        if (suggestions.length === 0) return;

        const suggestionsContainer = this.createSuggestionsContainer();
        suggestionsContainer.innerHTML = '';

        suggestions.forEach(suggestion => {
            const suggestionElement = document.createElement('div');
            suggestionElement.className = 'px-4 py-2 hover:bg-gray-50 cursor-pointer text-sm text-gray-700 border-b border-gray-100 last:border-b-0';
            suggestionElement.textContent = suggestion;
            suggestionElement.addEventListener('click', () => {
                document.getElementById('staff-search').value = suggestion;
                this.updateFilter('search', suggestion);
                this.hideSearchSuggestions();
            });
            suggestionsContainer.appendChild(suggestionElement);
        });

        suggestionsContainer.style.display = 'block';
    }

    /**
     * Nascondi suggerimenti
     */
    hideSearchSuggestions() {
        const suggestionsContainer = document.getElementById('search-suggestions');
        if (suggestionsContainer) {
            suggestionsContainer.style.display = 'none';
        }
    }

    /**
     * Crea container suggerimenti
     */
    createSuggestionsContainer() {
        let container = document.getElementById('search-suggestions');
        if (!container) {
            container = document.createElement('div');
            container.id = 'search-suggestions';
            container.className = 'absolute top-full left-0 right-0 bg-white border border-gray-200 rounded-lg shadow-lg z-10 max-h-64 overflow-y-auto';
            container.style.display = 'none';

            const searchContainer = document.querySelector('.search-container');
            if (searchContainer) {
                searchContainer.style.position = 'relative';
                searchContainer.appendChild(container);
            }
        }
        return container;
    }

    /**
     * Genera suggerimenti basati su dati esistenti
     */
    generateSearchSuggestions() {
        const staffRows = document.querySelectorAll('[data-staff-id]');
        const suggestions = new Set();

        staffRows.forEach(row => {
            // Estrai nomi, ruoli, dipartimenti dai dati visibili
            const nameElement = row.querySelector('.staff-name');
            const roleElement = row.querySelector('.staff-role');
            const deptElement = row.querySelector('.staff-department');

            if (nameElement) suggestions.add(nameElement.textContent.trim());
            if (roleElement) suggestions.add(roleElement.textContent.trim());
            if (deptElement) suggestions.add(deptElement.textContent.trim());
        });

        return Array.from(suggestions).slice(0, 8); // Max 8 suggestions
    }

    // ==========================================
    // FILTER MANAGEMENT
    // ==========================================

    /**
     * Aggiorna singolo filtro
     */
    updateFilter(key, value) {
        const oldValue = this.currentFilters[key];
        this.currentFilters[key] = value;

        // Salva in storage
        this.saveFiltersToStorage();

        // Aggiorna UI
        this.updateFilterUI();

        // Applica filtri
        this.applyFilters();

        // Log cambiamento
        console.log(`🔍 Filter updated: ${key} = "${value}" (was "${oldValue}")`);
    }

    /**
     * Applica tutti i filtri attivi
     */
    applyFilters() {
        if (this.isFiltering) return;

        this.isFiltering = true;
        this.showFilteringFeedback();

        // Simula delay per feedback UX
        setTimeout(() => {
            this.performFiltering();
            this.isFiltering = false;
            this.hideFilteringFeedback();
        }, 150);
    }

    /**
     * Esegue il filtering sui dati
     */
    performFiltering() {
        const staffRows = document.querySelectorAll('[data-staff-id]');
        let visibleCount = 0;

        staffRows.forEach(row => {
            const shouldShow = this.shouldShowStaffRow(row);

            if (shouldShow) {
                row.style.display = '';
                row.classList.remove('filtered-out');
                visibleCount++;
            } else {
                row.style.display = 'none';
                row.classList.add('filtered-out');
            }
        });

        // Aggiorna contatori
        this.updateFilterResults(visibleCount, staffRows.length);

        // Aggiorna selezione dopo filtri
        if (this.staffManager.selectionManager) {
            this.staffManager.selectionManager.refreshSelection();
        }
    }

    /**
     * Determina se una riga staff deve essere mostrata
     */
    shouldShowStaffRow(row) {
        const { search, role, department, status, dateFrom, dateTo } = this.currentFilters;

        // Search filter
        if (search) {
            const searchableText = this.getSearchableText(row).toLowerCase();
            if (!searchableText.includes(search.toLowerCase())) {
                return false;
            }
        }

        // Role filter
        if (role) {
            const staffRole = this.getStaffAttribute(row, 'role');
            if (staffRole !== role) return false;
        }

        // Department filter
        if (department) {
            const staffDept = this.getStaffAttribute(row, 'department');
            if (staffDept !== department) return false;
        }

        // Status filter
        if (status) {
            const staffStatus = this.getStaffAttribute(row, 'status');
            if (staffStatus !== status) return false;
        }

        // Date range filters
        if (dateFrom || dateTo) {
            const staffDate = this.getStaffAttribute(row, 'created_at');
            if (!this.isDateInRange(staffDate, dateFrom, dateTo)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Estrae testo per ricerca da una riga
     */
    getSearchableText(row) {
        const searchableElements = row.querySelectorAll('.staff-name, .staff-email, .staff-role, .staff-department');
        return Array.from(searchableElements)
            .map(el => el.textContent.trim())
            .join(' ');
    }

    /**
     * Estrae attributo staff da una riga
     */
    getStaffAttribute(row, attribute) {
        const attributeElement = row.querySelector(`.staff-${attribute}`);
        return attributeElement ? attributeElement.textContent.trim() : '';
    }

    /**
     * Verifica se una data è nel range specificato
     */
    isDateInRange(dateString, fromDate, toDate) {
        if (!dateString) return true;

        const date = new Date(dateString);
        const from = fromDate ? new Date(fromDate) : null;
        const to = toDate ? new Date(toDate) : null;

        if (from && date < from) return false;
        if (to && date > to) return false;

        return true;
    }

    // ==========================================
    // UI UPDATES
    // ==========================================

    /**
     * Aggiorna UI filtri
     */
    updateFilterUI() {
        // Aggiorna counter filtri attivi
        this.updateActiveFiltersCount();

        // Aggiorna clear button
        this.updateClearFiltersButton();

        // Aggiorna badge filtri
        this.updateFilterBadges();
    }

    /**
     * Aggiorna contatore filtri attivi
     */
    updateActiveFiltersCount() {
        const activeCount = Object.values(this.currentFilters)
            .filter(value => value && value.length > 0).length;

        const countElement = document.querySelector('.active-filters-count');
        if (countElement) {
            if (activeCount > 0) {
                countElement.textContent = activeCount;
                countElement.style.display = 'inline-flex';
            } else {
                countElement.style.display = 'none';
            }
        }
    }

    /**
     * Aggiorna pulsante clear filtri
     */
    updateClearFiltersButton() {
        const clearButton = document.getElementById('clear-filters');
        if (clearButton) {
            const hasActiveFilters = Object.values(this.currentFilters)
                .some(value => value && value.length > 0);

            clearButton.disabled = !hasActiveFilters;
            clearButton.classList.toggle('opacity-50', !hasActiveFilters);
        }
    }

    /**
     * Aggiorna badge filtri
     */
    updateFilterBadges() {
        const badgeContainer = document.querySelector('.filter-badges');
        if (!badgeContainer) return;

        badgeContainer.innerHTML = '';

        Object.entries(this.currentFilters).forEach(([key, value]) => {
            if (value && value.length > 0) {
                const badge = this.createFilterBadge(key, value);
                badgeContainer.appendChild(badge);
            }
        });
    }

    /**
     * Crea badge per filtro attivo
     */
    createFilterBadge(key, value) {
        const badge = document.createElement('div');
        badge.className = 'inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-rose-100 text-rose-800 border border-rose-200';

        const displayValue = value.length > 20 ? value.substring(0, 20) + '...' : value;

        badge.innerHTML = `
            <span class="mr-1">${this.getFilterLabel(key)}:</span>
            <span class="font-semibold">${displayValue}</span>
            <button class="ml-2 text-rose-600 hover:text-rose-800 focus:outline-none"
                    onclick="window.staffFilterManager.removeFilter('${key}')">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        `;

        return badge;
    }

    /**
     * Ottiene label per filtro
     */
    getFilterLabel(key) {
        const labels = {
            search: 'Ricerca',
            role: 'Ruolo',
            department: 'Dipartimento',
            status: 'Status',
            dateFrom: 'Da',
            dateTo: 'A'
        };
        return labels[key] || key;
    }

    /**
     * Aggiorna risultati filtri
     */
    updateFilterResults(visibleCount, totalCount) {
        const resultsElement = document.querySelector('.filter-results');
        if (resultsElement) {
            if (visibleCount === totalCount) {
                resultsElement.textContent = `${totalCount} staff`;
            } else {
                resultsElement.textContent = `${visibleCount} di ${totalCount} staff`;
                resultsElement.classList.add('filtered');
            }
        }

        // Mostra messaggio se nessun risultato
        this.updateEmptyState(visibleCount === 0);
    }

    /**
     * Aggiorna stato vuoto
     */
    updateEmptyState(isEmpty) {
        const emptyState = document.querySelector('.no-results-state');
        const staffTable = document.querySelector('.staff-table');

        if (isEmpty) {
            if (staffTable) staffTable.style.display = 'none';
            if (emptyState) {
                emptyState.style.display = 'block';
            } else {
                this.createEmptyState();
            }
        } else {
            if (staffTable) staffTable.style.display = '';
            if (emptyState) emptyState.style.display = 'none';
        }
    }

    /**
     * Crea stato vuoto per filtri
     */
    createEmptyState() {
        const emptyState = document.createElement('div');
        emptyState.className = 'no-results-state text-center py-12';
        emptyState.innerHTML = `
            <div class="text-6xl mb-4">🔍</div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun risultato trovato</h3>
            <p class="text-gray-600 mb-4">Prova a modificare i filtri di ricerca</p>
            <button onclick="window.staffFilterManager.clearAllFilters()"
                    class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700 transition-colors duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Pulisci Filtri
            </button>
        `;

        const container = document.querySelector('.staff-container');
        if (container) {
            container.appendChild(emptyState);
        }
    }

    // ==========================================
    // ADVANCED FILTERS
    // ==========================================

    /**
     * Setup filtri avanzati
     */
    setupAdvancedFilters() {
        // Implementazione per filtri avanzati come range date, multi-select, etc.
        const advancedContainer = document.querySelector('.advanced-filters');
        if (!advancedContainer) return;

        // Inizializza date pickers
        this.initializeDatePickers();
    }

    /**
     * Toggle filtri avanzati
     */
    toggleAdvancedFilters() {
        const advancedSection = document.querySelector('.advanced-filters');
        const toggleButton = document.getElementById('toggle-advanced-filters');

        if (advancedSection && toggleButton) {
            const isVisible = advancedSection.style.display !== 'none';
            advancedSection.style.display = isVisible ? 'none' : 'block';
            toggleButton.textContent = isVisible ? 'Mostra Filtri Avanzati' : 'Nascondi Filtri Avanzati';
        }
    }

    /**
     * Inizializza date pickers
     */
    initializeDatePickers() {
        const dateInputs = document.querySelectorAll('input[type="date"]');
        dateInputs.forEach(input => {
            // Imposta data massima ad oggi
            input.max = new Date().toISOString().split('T')[0];
        });
    }

    // ==========================================
    // PUBLIC METHODS
    // ==========================================

    /**
     * Rimuove singolo filtro
     */
    removeFilter(key) {
        this.updateFilter(key, '');

        // Aggiorna campo UI
        const inputElement = document.getElementById(`filter-${key}`) ||
                            document.getElementById(`staff-search`);
        if (inputElement) {
            inputElement.value = '';
        }
    }

    /**
     * Pulisce tutti i filtri
     */
    clearAllFilters() {
        Object.keys(this.currentFilters).forEach(key => {
            this.currentFilters[key] = '';
        });

        // Reset UI elements
        const filterElements = document.querySelectorAll('[id^="filter-"], #staff-search');
        filterElements.forEach(element => {
            element.value = '';
        });

        this.saveFiltersToStorage();
        this.updateFilterUI();
        this.applyFilters();

        this.staffManager.notificationManager.showSuccess('Tutti i filtri sono stati rimossi');
        console.log('🧹 All filters cleared');
    }

    /**
     * Imposta filtri da oggetto
     */
    setFilters(filters) {
        this.currentFilters = { ...this.currentFilters, ...filters };
        this.saveFiltersToStorage();
        this.updateFilterUI();
        this.applyFilters();
    }

    /**
     * Ottiene filtri correnti
     */
    getCurrentFilters() {
        return { ...this.currentFilters };
    }

    // ==========================================
    // PERSISTENCE
    // ==========================================

    /**
     * Salva filtri in sessionStorage
     */
    saveFiltersToStorage() {
        try {
            sessionStorage.setItem('staff-filters', JSON.stringify(this.currentFilters));
        } catch (error) {
            console.warn('Cannot save filters to storage:', error);
        }
    }

    /**
     * Ripristina filtri da sessionStorage
     */
    restoreFiltersFromStorage() {
        try {
            const saved = sessionStorage.getItem('staff-filters');
            if (saved) {
                this.currentFilters = { ...this.currentFilters, ...JSON.parse(saved) };

                // Ripristina UI
                Object.entries(this.currentFilters).forEach(([key, value]) => {
                    if (value) {
                        const element = document.getElementById(`filter-${key}`) ||
                                      document.getElementById('staff-search');
                        if (element) element.value = value;
                    }
                });
            }
        } catch (error) {
            console.warn('Cannot restore filters from storage:', error);
        }
    }

    // ==========================================
    // KEYBOARD SHORTCUTS
    // ==========================================

    /**
     * Gestisce shortcuts da tastiera
     */
    handleKeyboardShortcuts(event) {
        // Ctrl/Cmd + K = Focus search
        if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
            event.preventDefault();
            const searchInput = document.getElementById('staff-search');
            if (searchInput) searchInput.focus();
        }

        // Ctrl/Cmd + Shift + C = Clear filters
        if ((event.ctrlKey || event.metaKey) && event.shiftKey && event.key === 'C') {
            event.preventDefault();
            this.clearAllFilters();
        }
    }

    // ==========================================
    // FEEDBACK METHODS
    // ==========================================

    /**
     * Mostra feedback durante filtering
     */
    showFilteringFeedback() {
        const loadingIndicator = document.querySelector('.filtering-indicator');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'block';
        }
    }

    /**
     * Nascondi feedback filtering
     */
    hideFilteringFeedback() {
        const loadingIndicator = document.querySelector('.filtering-indicator');
        if (loadingIndicator) {
            loadingIndicator.style.display = 'none';
        }
    }
}

// Esposizione globale
export default FilterManager;