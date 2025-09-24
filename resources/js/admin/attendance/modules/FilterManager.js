/**
 * FilterManager - Gestione moderna dei filtri
 * Separa la logica di filtrazione dalla UI
 *
 * FASE 2: JavaScript Modernization
 */
export class FilterManager {
    constructor(apiService, notificationManager) {
        this.apiService = apiService;
        this.notification = notificationManager;
        this.filters = {
            search: '',
            date_from: '',
            date_to: '',
            status: '',
            course_id: '',
            event_id: ''
        };
        this.attendanceCount = 0;
        this.isLoading = false;
        console.log('🔍 FilterManager initialized');
    }

    /**
     * Ottieni filtri correnti
     */
    getFilters() {
        return { ...this.filters };
    }

    /**
     * Imposta filtro singolo
     */
    setFilter(key, value) {
        if (this.filters.hasOwnProperty(key)) {
            this.filters[key] = value;
            console.log(`🔍 Filter updated: ${key} = ${value}`);
        }
    }

    /**
     * Imposta filtri multipli
     */
    setFilters(newFilters) {
        Object.assign(this.filters, newFilters);
        console.log('🔍 Multiple filters updated:', newFilters);
    }

    /**
     * Reset tutti i filtri
     */
    resetFilters() {
        const defaultFilters = {
            search: '',
            date_from: '',
            date_to: '',
            status: '',
            course_id: '',
            event_id: ''
        };

        this.filters = defaultFilters;
        console.log('🔄 Filters reset to defaults');

        // Aggiorna UI
        this.updateUIInputs();

        // Applica filtri vuoti
        return this.applyFilters();
    }

    /**
     * Applica filtri e carica dati
     */
    async applyFilters() {
        if (this.isLoading) {
            console.log('⏳ Filter application already in progress');
            return;
        }

        this.isLoading = true;
        console.log('🔍 Applying filters:', this.filters);

        try {
            // Mostra loading state
            this.setLoadingState(true);

            // Prepara parametri
            const params = new URLSearchParams();

            Object.keys(this.filters).forEach(key => {
                if (this.filters[key]) {
                    params.append(key, this.filters[key]);
                }
            });

            // Chiama API
            const response = await fetch(this.getFilterUrl() + '?' + params.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Aggiorna tabella
                const container = document.getElementById('attendance-table-container');
                if (container && data.data.html) {
                    container.innerHTML = data.data.html;
                }

                // Aggiorna contatore
                this.updateAttendanceCount(data.data);

                console.log('✅ Filters applied successfully');
            } else {
                throw new Error(data.message || 'Errore durante il filtrage');
            }

        } catch (error) {
            console.error('❌ Filter error:', error);
            this.notification.showError(
                'Errore durante il caricamento dei dati: ' + error.message
            );
        } finally {
            this.isLoading = false;
            this.setLoadingState(false);
        }
    }

    /**
     * Ottieni URL per filtri (configurabile)
     */
    getFilterUrl() {
        return window.location.pathname;
    }

    /**
     * Aggiorna contatore presenze
     */
    updateAttendanceCount(data) {
        // Estrai conteggio dalla paginazione o dai dati
        let count = 0;
        if (data.pagination) {
            const match = data.pagination.match(/(\d+)/);
            count = match ? parseInt(match[0]) : 0;
        }

        this.attendanceCount = count;

        // Aggiorna elemento UI se esiste
        const countElement = document.querySelector('[x-text*="attendanceCount"]');
        if (countElement) {
            countElement.textContent = `Mostrando ${count} risultati`;
        }
    }

    /**
     * Aggiorna input UI con valori filtro correnti
     */
    updateUIInputs() {
        Object.keys(this.filters).forEach(key => {
            const input = document.getElementById(key) ||
                         document.getElementById(key.replace('_', '_filter'));

            if (input) {
                input.value = this.filters[key];

                // Trigger change event per Alpine.js
                input.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    }

    /**
     * Mostra/nascondi loading state
     */
    setLoadingState(isLoading) {
        const container = document.getElementById('attendance-table-container');
        const resetBtn = document.querySelector('[onclick*="resetFilters"]') ||
                        document.querySelector('[\\@click*="resetFilters"]');

        if (isLoading) {
            // Aggiungi loading overlay
            if (container && !container.querySelector('.loading-overlay')) {
                const overlay = document.createElement('div');
                overlay.className = 'loading-overlay absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10';
                overlay.innerHTML = `
                    <div class="flex items-center space-x-2">
                        <svg class="animate-spin h-5 w-5 text-rose-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-gray-600">Caricamento...</span>
                    </div>
                `;

                // Rendi container relativo per overlay
                container.style.position = 'relative';
                container.appendChild(overlay);
            }

            // Disabilita reset button
            if (resetBtn) {
                resetBtn.disabled = true;
                resetBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }

        } else {
            // Rimuovi loading overlay
            const overlay = container?.querySelector('.loading-overlay');
            if (overlay) {
                overlay.remove();
            }

            // Riabilita reset button
            if (resetBtn) {
                resetBtn.disabled = false;
                resetBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    /**
     * Validazione filtri
     */
    validateFilters() {
        const errors = [];

        // Validazione date range
        if (this.filters.date_from && this.filters.date_to) {
            const dateFrom = new Date(this.filters.date_from);
            const dateTo = new Date(this.filters.date_to);

            if (dateFrom > dateTo) {
                errors.push('La data di inizio deve essere precedente alla data di fine');
            }

            // Limite range troppo ampio (es. 1 anno)
            const diffTime = Math.abs(dateTo - dateFrom);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

            if (diffDays > 365) {
                errors.push('Il range di date non può superare un anno');
            }
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Applica filtri con validazione
     */
    async applyFiltersWithValidation() {
        const validation = this.validateFilters();

        if (!validation.isValid) {
            validation.errors.forEach(error => {
                this.notification.showWarning(error);
            });
            return false;
        }

        return await this.applyFilters();
    }

    /**
     * Debounce helper per search
     */
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    /**
     * Quick filters per common actions
     */
    quickFilters = {
        today: () => {
            const today = new Date().toISOString().split('T')[0];
            this.setFilters({ date_from: today, date_to: today });
            return this.applyFilters();
        },

        thisWeek: () => {
            const today = new Date();
            const firstDay = new Date(today.setDate(today.getDate() - today.getDay()));
            const lastDay = new Date(today.setDate(today.getDate() - today.getDay() + 6));

            this.setFilters({
                date_from: firstDay.toISOString().split('T')[0],
                date_to: lastDay.toISOString().split('T')[0]
            });
            return this.applyFilters();
        },

        thisMonth: () => {
            const today = new Date();
            const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
            const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

            this.setFilters({
                date_from: firstDay.toISOString().split('T')[0],
                date_to: lastDay.toISOString().split('T')[0]
            });
            return this.applyFilters();
        },

        presentOnly: () => {
            this.setFilter('status', 'present');
            return this.applyFilters();
        },

        absentOnly: () => {
            this.setFilter('status', 'absent');
            return this.applyFilters();
        }
    };
}