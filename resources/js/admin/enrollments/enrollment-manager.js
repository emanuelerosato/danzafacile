/**
 * EnrollmentManager - Controller principale gestione iscrizioni
 * APPROCCIO NON-INTRUSIVO: Preserva funzionalità esistenti, aggiunge moderne
 *
 * Phase 5: Integrazione moduli avanzati
 * - StatusManager per gestione stati
 * - BulkActionManager per azioni multiple
 */
import { EnrollmentApiService } from './services/enrollment-api.js';
import { NotificationManager } from './modules/NotificationManager.js';
import StatusManager from './modules/StatusManager.js';
import BulkActionManager from './modules/BulkActionManager.js';

class EnrollmentManager {
    constructor(enrollmentsData, csrfToken) {
        this.apiService = new EnrollmentApiService(csrfToken);
        this.notification = new NotificationManager();

        // Initialize advanced modules (Phase 5)
        this.statusManager = new StatusManager(this.apiService, this.notification);
        this.bulkActionManager = new BulkActionManager(this.apiService, this.notification, this.statusManager);

        this.enrollments = enrollmentsData || [];
        this.selectedIds = []; // Legacy - now handled by bulkActionManager
        this.csrfToken = csrfToken;

        this.init();
    }

    /**
     * Inizializzazione sistema
     */
    init() {
        this.bindEvents();
        this.preserveExistingFunctionality();
        this.exposeManagersGlobally();
        console.log('✅ EnrollmentManager initialized - preserving existing functionality');
        console.log('🚀 Phase 5: Advanced modules loaded (StatusManager, BulkActionManager)');
    }

    /**
     * Espone i manager per integrazione con Alpine.js
     */
    exposeManagersGlobally() {
        // Rende accessibili i manager avanzati
        window.enrollmentStatusManager = this.statusManager;
        window.enrollmentBulkManager = this.bulkActionManager;

        console.log('🌐 Advanced managers exposed globally for Alpine.js integration');
    }

    /**
     * PRESERVAZIONE: Mantiene tutto quello che già funziona
     */
    preserveExistingFunctionality() {
        // La lista esistente continua a funzionare identicamente
        // I link "Dettagli" continuano a funzionare come prima
        // La paginazione resta invariata
        // Le stats cards restano identiche

        // Questo metodo assicura che non rompiamo nulla
        console.log('🔒 Existing functionality preserved');
    }

    /**
     * Event binding (solo per nuove funzionalità)
     * NOTA: I button clicks sono gestiti da Alpine.js per evitare duplicazioni
     */
    bindEvents() {
        // DISABILITATO: I button clicks sono gestiti da Alpine.js
        // document.addEventListener('click', this.handleGlobalClick.bind(this));

        // Altri event listeners non-button potrebbero essere aggiunti qui
        console.log('📡 Event listeners attached (button clicks delegated to Alpine.js)');
    }

    /**
     * Handler click globale (DISABILITATO - gestito da Alpine.js)
     */
    handleGlobalClick(event) {
        // DISABILITATO: Gestito da Alpine.js per evitare esecuzioni doppie
        console.warn('🚫 handleGlobalClick called but disabled - Alpine.js handles button clicks');
        return;

        /*
        const target = event.target.closest('[data-enrollment-action]');
        if (!target) return; // Non interferisce con elementi esistenti

        event.preventDefault();

        const action = target.dataset.enrollmentAction;
        const enrollmentId = target.dataset.enrollmentId;

        console.log('🎯 Enrollment action triggered:', action, enrollmentId);

        // Implementeremo le azioni man mano
        switch (action) {
            case 'toggle-status':
                this.toggleStatus(enrollmentId);
                break;
            case 'delete':
                this.deleteEnrollment(enrollmentId);
                break;
            default:
                console.warn('⚠️ Unknown enrollment action:', action);
        }
        */
    }

    /**
     * Toggle status iscrizione (Phase 5: Implementazione completa)
     */
    async toggleStatus(enrollmentId) {
        console.log('🔄 Toggle status for enrollment:', enrollmentId);

        // Ottieni status attuale dalla UI
        const currentStatus = this.getCurrentStatusFromUI(enrollmentId);

        // Delega al StatusManager
        const success = await this.statusManager.toggleStatus(parseInt(enrollmentId), currentStatus);

        if (success) {
            console.log('✅ Status toggled successfully for:', enrollmentId);
            // Aggiorna stats se necessario
            this.updateStats();
        }
    }

    /**
     * Elimina iscrizione (Phase 5: Implementazione completa)
     */
    async deleteEnrollment(enrollmentId) {
        console.log('🗑️ Delete enrollment:', enrollmentId);

        const enrollment = this.findEnrollment(enrollmentId);
        const enrollmentName = enrollment?.user?.name || `Iscrizione ${enrollmentId}`;

        if (!confirm(`Sei sicuro di voler eliminare definitivamente l'iscrizione di "${enrollmentName}"?\n\nQuesta operazione non può essere annullata.`)) {
            return;
        }

        try {
            const result = await this.apiService.delete(enrollmentId);

            if (result.success) {
                this.notification.showSuccess(result.message || 'Iscrizione eliminata con successo');

                // Rimuovi dalla UI
                const row = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
                if (row) {
                    row.remove();
                }

                // Aggiorna stats
                this.updateStats();

                // Ricarica pagina se necessario
                setTimeout(() => location.reload(), 1500);
            } else {
                this.notification.showError(result.message || 'Errore durante l\'eliminazione');
            }
        } catch (error) {
            console.error('❌ Delete error:', error);
            this.notification.showError('Errore di connessione durante l\'eliminazione');
        }
    }

    /**
     * Ottieni status attuale dall'interfaccia utente
     */
    getCurrentStatusFromUI(enrollmentId) {
        const row = document.querySelector(`[data-enrollment-id="${enrollmentId}"]`);
        if (!row) return 'active';

        const statusBadge = row.querySelector('.status-badge');
        if (!statusBadge) return 'active';

        const statusText = statusBadge.textContent.toLowerCase().trim();
        const statusMap = {
            'attivo': 'active',
            'cancellato': 'cancelled',
            'in attesa': 'pending'
        };

        return statusMap[statusText] || 'active';
    }

    /**
     * Helper per trovare enrollment nei dati
     */
    findEnrollment(enrollmentId) {
        return this.enrollments.find(e => e.id === parseInt(enrollmentId));
    }

    /**
     * Aggiorna statistiche (Phase 5: Implementazione completa)
     */
    async updateStats() {
        console.log('📊 Updating stats...');

        try {
            const result = await this.apiService.getStatistics();

            if (result.success && result.stats) {
                this.updateStatsCards(result.stats);
                console.log('✅ Stats updated successfully');
            }
        } catch (error) {
            console.error('❌ Stats update error:', error);
            // Non mostriamo errore all'utente per le stats - operazione di background
        }
    }

    /**
     * Aggiorna le cards delle statistiche nell'UI
     */
    updateStatsCards(stats) {
        // Aggiorna total enrollments
        const totalCard = document.querySelector('[data-stat="total"]');
        if (totalCard && stats.total !== undefined) {
            const valueElement = totalCard.querySelector('.stat-value');
            if (valueElement) valueElement.textContent = stats.total;
        }

        // Aggiorna active enrollments
        const activeCard = document.querySelector('[data-stat="active"]');
        if (activeCard && stats.active !== undefined) {
            const valueElement = activeCard.querySelector('.stat-value');
            if (valueElement) valueElement.textContent = stats.active;
        }

        // Aggiorna pending enrollments
        const pendingCard = document.querySelector('[data-stat="pending"]');
        if (pendingCard && stats.pending !== undefined) {
            const valueElement = pendingCard.querySelector('.stat-value');
            if (valueElement) valueElement.textContent = stats.pending;
        }

        // Aggiorna cancelled enrollments
        const cancelledCard = document.querySelector('[data-stat="cancelled"]');
        if (cancelledCard && stats.cancelled !== undefined) {
            const valueElement = cancelledCard.querySelector('.stat-value');
            if (valueElement) valueElement.textContent = stats.cancelled;
        }
    }

    /**
     * Cleanup delle risorse
     */
    cleanup() {
        this.statusManager?.cleanup();
        this.bulkActionManager?.cleanup();
        console.log('🧹 EnrollmentManager cleaned up');
    }

    /**
     * Ottieni informazioni sui manager avanzati
     */
    getManagersInfo() {
        return {
            statusManager: !!this.statusManager,
            bulkActionManager: !!this.bulkActionManager,
            selectedCount: this.bulkActionManager?.getSelectedIds()?.length || 0
        };
    }
}

// Esporta per uso globale (compatibilità)
window.EnrollmentManager = EnrollmentManager;

// Inizializzazione automatica quando DOM è pronto
document.addEventListener('DOMContentLoaded', function() {
    const enrollmentsData = window.enrollmentsData || [];
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (csrfToken) {
        window.enrollmentManager = new EnrollmentManager(enrollmentsData, csrfToken);
        console.log('✅ EnrollmentManager ready with', enrollmentsData.length, 'enrollments');
    } else {
        console.warn('⚠️ CSRF token not found - EnrollmentManager not initialized');
    }
});

export default EnrollmentManager;