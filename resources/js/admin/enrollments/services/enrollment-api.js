/**
 * EnrollmentApiService - Integrazione con API backend esistenti
 * Utilizza gli endpoint già implementati nel controller (459 righe)
 */
export class EnrollmentApiService {
    constructor(csrfToken) {
        this.csrfToken = csrfToken;
        this.baseUrl = '/api/v1/admin/enrollments'; // Correzione: usa endpoint API
    }

    /**
     * Elimina iscrizione (endpoint esistente)
     */
    async delete(enrollmentId) {
        try {
            const response = await fetch(`${this.baseUrl}/${enrollmentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Delete enrollment error:', error);
            return { success: false, message: 'Errore di connessione' };
        }
    }

    /**
     * Aggiorna status iscrizione (endpoint cancel/reactivate esistenti)
     */
    async updateStatus(enrollmentId, statusData) {
        const targetStatus = statusData.status || statusData;
        const endpoint = targetStatus === 'cancelled' ? 'cancel' : 'reactivate';
        try {
            const response = await fetch(`${this.baseUrl}/${enrollmentId}/${endpoint}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Update status error:', error);
            return { success: false, message: 'Errore di connessione' };
        }
    }

    /**
     * Operazioni bulk (endpoint esistente)
     */
    async bulkAction(params) {
        // Supporta sia oggetti che parametri separati per retrocompatibilità
        let action, enrollmentIds, status;

        if (typeof params === 'object' && params.action) {
            action = params.action;
            enrollmentIds = params.ids || params.enrollment_ids;
            status = params.status;
        } else {
            // Legacy: bulkAction(action, ids)
            action = arguments[0];
            enrollmentIds = arguments[1];
        }

        try {
            const payload = {
                action: action,
                enrollment_ids: enrollmentIds
            };

            // Aggiungi status se presente (per update_status)
            if (status) {
                payload.status = status;
            }

            const response = await fetch(`${this.baseUrl}/bulk-action`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            });
            return await response.json();
        } catch (error) {
            console.error('Bulk action error:', error);
            return { success: false, message: 'Errore di connessione' };
        }
    }

    /**
     * Statistiche (endpoint API esistente)
     */
    async getStatistics(period = 'month') {
        try {
            const response = await fetch(`/api/v1/admin/enrollments/statistics?period=${period}`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken,
                    'Accept': 'application/json'
                }
            });
            return await response.json();
        } catch (error) {
            console.error('Statistics error:', error);
            return { success: false, message: 'Errore nel caricamento statistiche' };
        }
    }

    /**
     * Export (endpoint esistente)
     */
    async export(filters = {}) {
        try {
            const params = new URLSearchParams(filters);
            const response = await fetch(`${this.baseUrl}/export?${params}`, {
                headers: {
                    'X-CSRF-TOKEN': this.csrfToken
                }
            });

            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `iscrizioni-export-${new Date().toISOString().split('T')[0]}.xlsx`;
                a.click();
                window.URL.revokeObjectURL(url);
                return { success: true, message: 'Export completato' };
            }
            return { success: false, message: 'Errore durante export' };
        } catch (error) {
            console.error('Export error:', error);
            return { success: false, message: 'Errore di connessione' };
        }
    }
}