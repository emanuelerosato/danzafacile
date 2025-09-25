/**
 * FilterManager - Gestione filtri avanzati per Events
 *
 * RESPONSABILITÀ:
 * - Gestire filtri di ricerca, tipo, stato
 * - Debounce per ottimizzazione performance
 * - Aggiornamento URL con parametri
 * - Validazione filtri
 * - Loading states
 * - Export filtrato
 */

export class FilterManager {
    constructor(eventsManager) {
        this.eventsManager = eventsManager;
        this.filters = {
            search: '',
            type: '',
            status: ''
        };
        this.debounceTimeout = null;
        this.isLoading = false;
    }

    init() {
        console.log('[FilterManager] 📊 Initializing Filter Manager...');
        this.setupEventListeners();
        this.loadFiltersFromURL();
        console.log('[FilterManager] ✅ Filter Manager initialized');
    }

    setupEventListeners() {
        // Search input with debounce
        const searchInput = document.getElementById('search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.filters.search = e.target.value;
                this.debouncedApplyFilters();
            });
        }

        // Type filter
        const typeFilter = document.getElementById('type');
        if (typeFilter) {
            typeFilter.addEventListener('change', (e) => {
                this.filters.type = e.target.value;
                this.applyFilters();
            });
        }

        // Status filter
        const statusFilter = document.getElementById('status');
        if (statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                this.filters.status = e.target.value;
                this.applyFilters();
            });
        }

        // Clear filters button
        const clearButton = document.querySelector('[data-action="clear-filters"]');
        if (clearButton) {
            clearButton.addEventListener('click', () => {
                this.clearFilters();
            });
        }
    }

    loadFiltersFromURL() {
        const params = new URLSearchParams(window.location.search);

        this.filters.search = params.get('search') || '';
        this.filters.type = params.get('type') || '';
        this.filters.status = params.get('status') || '';

        this.updateFormInputs();
    }

    updateFormInputs() {
        const searchInput = document.getElementById('search');
        const typeFilter = document.getElementById('type');
        const statusFilter = document.getElementById('status');

        if (searchInput) searchInput.value = this.filters.search;
        if (typeFilter) typeFilter.value = this.filters.type;
        if (statusFilter) statusFilter.value = this.filters.status;
    }

    debouncedApplyFilters(delay = 300) {
        clearTimeout(this.debounceTimeout);
        this.debounceTimeout = setTimeout(() => {
            this.applyFilters();
        }, delay);
    }

    async applyFilters() {
        if (this.isLoading) {
            console.log('[FilterManager] ⏳ Already applying filters, skipping...');
            return;
        }

        this.isLoading = true;
        this.showLoadingState();

        try {
            console.log('[FilterManager] 🔍 Applying filters:', this.filters);

            // Validate filters
            const validation = this.validateFilters();
            if (!validation.valid) {
                document.dispatchEvent(new CustomEvent('events:notification', {
                    detail: { message: validation.message, type: 'warning' }
                }));
                return;
            }

            // Prepare URL parameters
            const params = new URLSearchParams();
            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params.append(key, this.filters[key]);
                }
            });

            const url = `${window.location.pathname}?${params.toString()}`;

            // Fetch filtered results
            const response = await fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                }
            });

            if (response.ok) {
                const html = await response.text();
                this.updateTable(html);
                this.updateURL(url);

                // Notify success
                document.dispatchEvent(new CustomEvent('events:notification', {
                    detail: { message: 'Filtri applicati con successo', type: 'success' }
                }));
            } else {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

        } catch (error) {
            console.error('[FilterManager] ❌ Error applying filters:', error);

            document.dispatchEvent(new CustomEvent('events:notification', {
                detail: { message: 'Errore nell\'applicazione dei filtri', type: 'error' }
            }));
        } finally {
            this.isLoading = false;
            this.hideLoadingState();
        }
    }

    clearFilters() {
        console.log('[FilterManager] 🧹 Clearing all filters...');

        this.filters = {
            search: '',
            type: '',
            status: ''
        };

        this.updateFormInputs();
        this.applyFilters();
    }

    updateTable(html) {
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // Update table
        const newTable = doc.querySelector('#events-table-container');
        const currentTable = document.querySelector('#events-table-container');

        if (newTable && currentTable) {
            currentTable.innerHTML = newTable.innerHTML;
            console.log('[FilterManager] ✅ Table updated successfully');
        }

        // Update stats if present
        const statsContainer = document.querySelector('.grid');
        if (statsContainer) {
            const newStats = doc.querySelector('.grid');
            if (newStats) {
                statsContainer.innerHTML = newStats.innerHTML;
                console.log('[FilterManager] ✅ Stats updated successfully');
            }
        }
    }

    updateURL(url) {
        if (window.history && window.history.pushState) {
            window.history.pushState(null, null, url);
        }
    }

    showLoadingState() {
        const filterButton = document.querySelector('[\\@click="applyFilters"]');
        if (filterButton) {
            filterButton.disabled = true;
            filterButton.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Filtrando...
            `;
        }
    }

    hideLoadingState() {
        const filterButton = document.querySelector('[\\@click="applyFilters"]');
        if (filterButton) {
            filterButton.disabled = false;
            filterButton.innerHTML = `
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                </svg>
                Filtra
            `;
        }
    }

    validateFilters() {
        // Check minimum search length
        if (this.filters.search && this.filters.search.length < 2) {
            return {
                valid: false,
                message: 'La ricerca deve contenere almeno 2 caratteri'
            };
        }

        return { valid: true };
    }

    async exportFiltered() {
        try {
            const params = new URLSearchParams(this.filters);
            const exportUrl = `${window.location.pathname.replace('/index', '/export')}?${params.toString()}`;

            // Create download link
            const link = document.createElement('a');
            link.href = exportUrl;
            link.download = `eventi-export-${new Date().toISOString().slice(0, 10)}.xlsx`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            document.dispatchEvent(new CustomEvent('events:notification', {
                detail: { message: 'Export avviato con successo', type: 'success' }
            }));

        } catch (error) {
            console.error('[FilterManager] ❌ Error exporting:', error);

            document.dispatchEvent(new CustomEvent('events:notification', {
                detail: { message: 'Errore durante l\'export', type: 'error' }
            }));
        }
    }

    // Get current filters for other components
    getCurrentFilters() {
        return { ...this.filters };
    }

    // Set filters programmatically
    setFilters(newFilters) {
        this.filters = { ...this.filters, ...newFilters };
        this.updateFormInputs();
        this.applyFilters();
    }
}