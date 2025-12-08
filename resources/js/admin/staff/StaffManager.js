/**
 * 🎯 STAFF MANAGER - Orchestratore principale per la sezione Staff
 *
 * Gestisce:
 * - State management centralizzato
 * - Coordinamento tra moduli
 * - API integration per operazioni CRUD
 * - Event-driven architecture
 * - Global functions registration
 */

import { FilterManager } from './modules/FilterManager.js';
import { SelectionManager } from './modules/SelectionManager.js';
import { BulkActionManager } from './modules/BulkActionManager.js';
import { NotificationManager } from './modules/NotificationManager.js';
import { FormManager } from './modules/FormManager.js';

class StaffManager {
    constructor() {
        this.state = {
            isLoading: false,
            selectedItems: [],
            filters: {
                search: '',
                role: '',
                department: '',
                status: ''
            },
            currentPage: 1,
            totalItems: 0
        };

        this.initializeModules();
        this.attachEventListeners();
        this.registerGlobalFunctions();

        console.log('🎯 StaffManager initialized successfully');
    }

    /**
     * Inizializza tutti i moduli specializzati
     */
    initializeModules() {
        this.filterManager = new FilterManager(this);
        this.selectionManager = new SelectionManager(this);
        this.bulkActionManager = new BulkActionManager(this);
        this.notificationManager = new NotificationManager(this);
        this.formManager = new FormManager(this);
    }

    /**
     * Registra event listeners principali
     */
    attachEventListeners() {
        // Global error handling
        window.addEventListener('unhandledrejection', (event) => {
            console.error('🚨 Unhandled Promise Rejection:', event.reason);
            this.notificationManager.showError('Si è verificato un errore imprevisto');
        });

        // Page unload confirmation se ci sono operazioni in corso
        window.addEventListener('beforeunload', (event) => {
            if (this.state.isLoading) {
                event.preventDefault();
                return 'Ci sono operazioni in corso. Sei sicuro di voler uscire?';
            }
        });
    }

    /**
     * Registra funzioni globali per compatibilità con template Blade
     */
    registerGlobalFunctions() {
        // Funzioni esposte globalmente per i template
        window.toggleStaffStatus = this.toggleStaffStatus.bind(this);
        window.deleteStaff = this.deleteStaff.bind(this);
        window.assignToCourse = this.assignToCourse.bind(this);
        window.performBulkAction = this.performBulkAction.bind(this);
        window.openStaffModal = this.openStaffModal.bind(this);
    }

    // ==========================================
    // STATE MANAGEMENT
    // ==========================================

    /**
     * Imposta lo stato di loading
     */
    setLoading(isLoading) {
        this.state.isLoading = isLoading;
        this.updateLoadingUI(isLoading);
    }

    /**
     * Aggiorna i filtri e ricarica i dati
     */
    updateFilters(newFilters) {
        this.state.filters = { ...this.state.filters, ...newFilters };
        this.filterManager.applyFilters();
    }

    /**
     * Gestisce cambiamenti nella selezione
     */
    handleSelectionChange(selectedItems) {
        this.state.selectedItems = selectedItems;
        this.bulkActionManager.updateBulkActionState(selectedItems.length);
    }

    /**
     * Aggiorna UI loading state
     */
    updateLoadingUI(isLoading) {
        const loadingElements = document.querySelectorAll('[data-loading]');
        loadingElements.forEach(element => {
            if (isLoading) {
                element.classList.add('opacity-50', 'pointer-events-none');
            } else {
                element.classList.remove('opacity-50', 'pointer-events-none');
            }
        });
    }

    // ==========================================
    // API METHODS
    // ==========================================

    /**
     * Toggle dello status di un membro dello staff
     */
    async toggleStaffStatus(staffId, currentStatus) {
        if (this.state.isLoading) return;

        try {
            this.setLoading(true);
            const newStatus = currentStatus === 'active' ? 'inactive' : 'active';

            const response = await fetch(`/admin/staff/${staffId}/toggle-active`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status: newStatus })
            });

            const data = await response.json();

            if (response.ok) {
                this.updateStaffStatusUI(staffId, newStatus);
                this.notificationManager.showSuccess(
                    `Staff ${newStatus === 'active' ? 'attivato' : 'disattivato'} con successo`
                );
            } else {
                throw new Error(data.message || 'Errore durante il cambio di status');
            }

        } catch (error) {
            console.error('❌ Toggle status error:', error);
            this.notificationManager.showError('Errore durante il cambio di status: ' + error.message);
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Eliminazione di un membro dello staff
     */
    async deleteStaff(staffId, staffName) {
        if (this.state.isLoading) return;

        const confirmed = await this.notificationManager.showConfirmation(
            'Eliminazione Staff',
            `Sei sicuro di voler eliminare <strong>${staffName}</strong>?<br>
             <small class="text-red-600">Questa azione non può essere annullata.</small>`,
            'Elimina',
            'Annulla'
        );

        if (!confirmed) return;

        try {
            this.setLoading(true);
            this.notificationManager.showProgress('Eliminazione in corso...', 30);

            const response = await fetch(`/admin/staff/${staffId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });

            this.notificationManager.showProgress('Eliminazione in corso...', 70);

            if (response.ok) {
                this.removeStaffFromUI(staffId);
                this.notificationManager.hideProgress();
                this.notificationManager.showSuccess(`${staffName} eliminato con successo`);
                this.updateStatsAfterDelete();
            } else {
                const data = await response.json();
                throw new Error(data.message || 'Errore durante l\'eliminazione');
            }

        } catch (error) {
            console.error('❌ Delete staff error:', error);
            this.notificationManager.hideProgress();
            this.notificationManager.showError('Errore durante l\'eliminazione: ' + error.message);
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Assegnazione staff a un corso
     */
    async assignToCourse(staffId, courseId) {
        if (this.state.isLoading) return;

        try {
            this.setLoading(true);
            this.notificationManager.showProgress('Assegnazione in corso...', 50);

            const response = await fetch(`/admin/staff/${staffId}/assign-course`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ course_id: courseId })
            });

            const data = await response.json();

            if (response.ok) {
                this.notificationManager.hideProgress();
                this.notificationManager.showSuccess('Staff assegnato al corso con successo');
                this.updateStaffAssignmentUI(staffId, data.course);
            } else {
                throw new Error(data.message || 'Errore durante l\'assegnazione');
            }

        } catch (error) {
            console.error('❌ Course assignment error:', error);
            this.notificationManager.hideProgress();
            this.notificationManager.showError('Errore durante l\'assegnazione: ' + error.message);
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Operazioni bulk (delegate al BulkActionManager)
     */
    async performBulkAction(action, selectedIds = null) {
        const idsToProcess = selectedIds || this.state.selectedItems;
        return await this.bulkActionManager.performBulkAction(action, idsToProcess);
    }

    /**
     * Apertura modal staff (delegate al FormManager)
     */
    openStaffModal(staffId = null) {
        this.formManager.openStaffModal(staffId);
    }

    // ==========================================
    // UI UPDATE METHODS
    // ==========================================

    /**
     * Aggiorna UI dopo toggle status
     */
    updateStaffStatusUI(staffId, newStatus) {
        const statusBadge = document.querySelector(`[data-staff-id="${staffId}"] .status-badge`);
        const statusButton = document.querySelector(`[data-staff-id="${staffId}"] [onclick*="toggleStaffStatus"]`);

        if (statusBadge) {
            statusBadge.className = `status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                newStatus === 'active'
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
            }`;
            statusBadge.textContent = newStatus === 'active' ? 'Attivo' : 'Inattivo';
        }

        if (statusButton) {
            statusButton.textContent = newStatus === 'active' ? 'Disattiva' : 'Attiva';
            statusButton.className = `px-3 py-1 text-sm font-medium rounded-lg transition-colors duration-200 ${
                newStatus === 'active'
                    ? 'text-red-600 hover:bg-red-50'
                    : 'text-green-600 hover:bg-green-50'
            }`;
        }
    }

    /**
     * Rimuove staff dalla UI dopo eliminazione
     */
    removeStaffFromUI(staffId) {
        const staffRow = document.querySelector(`[data-staff-id="${staffId}"]`);
        if (staffRow) {
            staffRow.style.transition = 'all 0.3s ease-out';
            staffRow.style.opacity = '0';
            staffRow.style.transform = 'translateX(-20px)';

            setTimeout(() => {
                staffRow.remove();
                this.checkEmptyState();
            }, 300);
        }
    }

    /**
     * Aggiorna UI dopo assegnazione corso
     */
    updateStaffAssignmentUI(staffId, courseData) {
        const assignmentCell = document.querySelector(`[data-staff-id="${staffId}"] .course-assignment`);
        if (assignmentCell && courseData) {
            assignmentCell.innerHTML = `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    ${courseData.name}
                </span>
            `;
        }
    }

    /**
     * Aggiorna statistiche dopo eliminazione
     */
    updateStatsAfterDelete() {
        this.updateStats();
        this.checkEmptyState();
    }

    /**
     * Aggiorna tutte le stats cards basandosi sugli elementi DOM attuali
     */
    updateStats() {
        // Conta tutti gli staff visibili
        const allStaffRows = document.querySelectorAll('[data-staff-id]');
        const totalCount = allStaffRows.length;

        // Conta staff attivi (badge verde)
        let activeCount = 0;
        allStaffRows.forEach(row => {
            const badge = row.querySelector('.status-badge');
            if (badge && (badge.classList.contains('bg-green-100') || badge.textContent.trim().toLowerCase() === 'attivo')) {
                activeCount++;
            }
        });

        // Aggiorna UI stats cards
        const statsCards = [
            { selector: '.bg-gray-50 .text-gray-600:contains("Staff Totale")', value: totalCount },
            { selector: '.bg-gray-50 .text-gray-600:contains("Attivi")', value: activeCount }
        ];

        // Staff Totale
        const totalElements = Array.from(document.querySelectorAll('.bg-gray-50 p.text-sm')).filter(el =>
            el.textContent.includes('Staff Totale')
        );
        if (totalElements.length > 0) {
            const totalCard = totalElements[0].closest('.bg-gray-50');
            const totalNum = totalCard?.querySelector('.text-2xl');
            if (totalNum) totalNum.textContent = totalCount;
        }

        // Attivi
        const activeElements = Array.from(document.querySelectorAll('.bg-gray-50 p.text-sm')).filter(el =>
            el.textContent.includes('Attivi')
        );
        if (activeElements.length > 0) {
            const activeCard = activeElements[0].closest('.bg-gray-50');
            const activeNum = activeCard?.querySelector('.text-2xl');
            if (activeNum) activeNum.textContent = activeCount;
        }

        console.log('📊 Stats updated:', { total: totalCount, active: activeCount });
    }

    /**
     * Controlla se mostrare stato vuoto
     */
    checkEmptyState() {
        const staffRows = document.querySelectorAll('[data-staff-id]');
        const emptyState = document.querySelector('.empty-state');
        const staffTable = document.querySelector('.staff-table');

        if (staffRows.length === 0) {
            if (staffTable) staffTable.style.display = 'none';
            if (emptyState) emptyState.style.display = 'block';
        } else {
            if (staffTable) staffTable.style.display = 'block';
            if (emptyState) emptyState.style.display = 'none';
        }
    }

    // ==========================================
    // UTILITY METHODS
    // ==========================================

    /**
     * Debounce utility per search
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
     * Format currency utility
     */
    formatCurrency(amount) {
        return new Intl.NumberFormat('it-IT', {
            style: 'currency',
            currency: 'EUR'
        }).format(amount);
    }

    /**
     * Format date utility
     */
    formatDate(dateString) {
        return new Intl.DateTimeFormat('it-IT', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }).format(new Date(dateString));
    }
}

// Export per uso come modulo ES6
export default StaffManager;

// Istanza globale per compatibilità con template Blade
window.StaffManager = StaffManager;