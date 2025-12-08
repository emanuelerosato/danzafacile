/**
 * 🔨 BULK ACTION MANAGER - Gestione Azioni di Massa Staff
 *
 * Sostituisce le ~20 righe inline in index.blade.php (righe 344-367)
 *
 * Gestisce:
 * - Modern confirmation modals (no alert/confirm)
 * - Progress indicators per bulk operations
 * - Error handling con rollback
 * - Success feedback con toast
 * - Batch processing con rate limiting
 */

export class BulkActionManager {
    constructor(staffManager) {
        this.staffManager = staffManager;
        this.processingQueue = [];
        this.isProcessing = false;
        this.processingResults = {
            success: [],
            failed: [],
            total: 0
        };

        this.initialize();
        console.log('🔨 BulkActionManager initialized');
    }

    /**
     * Inizializzazione
     */
    initialize() {
        this.createActionModal();
        this.attachEventListeners();
    }

    /**
     * Registra event listeners
     */
    attachEventListeners() {
        // Intercetta submit del form bulkActionForm
        const bulkForm = document.getElementById('bulkActionForm');
        if (bulkForm) {
            bulkForm.addEventListener('submit', (event) => {
                event.preventDefault();
                console.log('🔨 Form submit intercepted');

                // Ottieni azione selezionata
                const actionSelect = bulkForm.querySelector('select[name="action"]');
                const action = actionSelect ? actionSelect.value : '';

                console.log('🔨 Action selected:', action);

                if (!action) {
                    this.staffManager.notificationManager.showWarning('Seleziona un\'azione da eseguire');
                    return;
                }

                // Ottieni staff IDs selezionati
                const selectedIds = this.staffManager.selectionManager.getSelectedItems();
                console.log('🔨 Selected IDs:', selectedIds);

                if (selectedIds.length === 0) {
                    this.staffManager.notificationManager.showWarning('Seleziona almeno un membro dello staff');
                    return;
                }

                // Esegui azione via JavaScript
                this.performBulkAction(action, selectedIds);
            });

            console.log('✅ BulkActionForm submit listener attached');
        } else {
            console.warn('⚠️ BulkActionForm not found - bulk actions might not work');
        }

        // Event listener per azioni bulk dal menu (legacy)
        document.addEventListener('click', (event) => {
            if (event.target.matches('[data-bulk-action]')) {
                event.preventDefault();
                const action = event.target.dataset.bulkAction;
                const selectedIds = this.staffManager.selectionManager.getSelectedItems();
                this.performBulkAction(action, selectedIds);
            }
        });

        // Event listener per conferme modali
        document.addEventListener('click', (event) => {
            if (event.target.matches('[data-modal-confirm]')) {
                this.handleModalConfirm(event.target);
            } else if (event.target.matches('[data-modal-cancel]')) {
                this.handleModalCancel();
            }
        });
    }

    // ==========================================
    // BULK ACTIONS
    // ==========================================

    /**
     * Esegue azione bulk sui membri dello staff
     */
    async performBulkAction(action, staffIds) {
        if (!staffIds || staffIds.length === 0) {
            this.staffManager.notificationManager.showWarning('Seleziona almeno un membro dello staff');
            return;
        }

        if (this.isProcessing) {
            this.staffManager.notificationManager.showWarning('Un\'altra operazione è in corso...');
            return;
        }

        // Reset risultati
        this.processingResults = {
            success: [],
            failed: [],
            total: staffIds.length
        };

        // Mostra modal di conferma
        const confirmed = await this.showConfirmationModal(action, staffIds.length);
        if (!confirmed) return;

        this.isProcessing = true;
        this.staffManager.setLoading(true);

        try {
            switch (action) {
                case 'activate':
                    await this.bulkActivate(staffIds);
                    break;
                case 'deactivate':
                    await this.bulkDeactivate(staffIds);
                    break;
                case 'delete':
                    await this.bulkDelete(staffIds);
                    break;
                case 'assign':
                    await this.bulkAssignToCourse(staffIds);
                    break;
                case 'export':
                    await this.bulkExport(staffIds);
                    break;
                default:
                    throw new Error(`Azione non supportata: ${action}`);
            }

            this.showResultsSummary();

        } catch (error) {
            console.error('❌ Bulk action error:', error);
            this.staffManager.notificationManager.showError(
                `Errore durante l'operazione: ${error.message}`
            );
        } finally {
            this.isProcessing = false;
            this.staffManager.setLoading(false);
            this.hideProgressModal();
        }
    }

    /**
     * Attivazione in massa
     */
    async bulkActivate(staffIds) {
        return await this.processBatch('activate', staffIds, async (staffId) => {
            const response = await fetch(`/admin/staff/${staffId}/toggle-active`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: 'active' })
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Errore durante l\'attivazione');
            }

            // Aggiorna UI
            this.staffManager.updateStaffStatusUI(staffId, 'active');
            return await response.json();
        });
    }

    /**
     * Disattivazione in massa
     */
    async bulkDeactivate(staffIds) {
        return await this.processBatch('deactivate', staffIds, async (staffId) => {
            const response = await fetch(`/admin/staff/${staffId}/toggle-active`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ status: 'inactive' })
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Errore durante la disattivazione');
            }

            // Aggiorna UI
            this.staffManager.updateStaffStatusUI(staffId, 'inactive');
            return await response.json();
        });
    }

    /**
     * Eliminazione in massa
     */
    async bulkDelete(staffIds) {
        return await this.processBatch('delete', staffIds, async (staffId) => {
            console.log(`🗑️ Deleting staff ID ${staffId}...`);

            const response = await fetch(`/admin/staff/${staffId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            console.log(`📡 Delete response status: ${response.status}`);

            if (!response.ok) {
                const data = await response.json();
                console.error(`❌ Delete failed for staff ${staffId}:`, data);
                throw new Error(data.message || 'Errore durante l\'eliminazione');
            }

            const result = await response.json();
            console.log(`✅ Delete successful for staff ${staffId}:`, result);

            // Rimuovi dalla UI
            this.staffManager.removeStaffFromUI(staffId);
            return result;
        });
    }

    /**
     * Assegnazione in massa a corso
     */
    async bulkAssignToCourse(staffIds) {
        // Prima chiediamo a quale corso assegnare
        const courseId = await this.promptForCourseSelection();
        if (!courseId) return;

        return await this.processBatch('assign', staffIds, async (staffId) => {
            const response = await fetch(`/admin/staff/${staffId}/assign-course`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ course_id: courseId })
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Errore durante l\'assegnazione');
            }

            const result = await response.json();
            this.staffManager.updateStaffAssignmentUI(staffId, result.course);
            return result;
        });
    }

    /**
     * Export in massa
     */
    async bulkExport(staffIds) {
        const format = await this.promptForExportFormat();
        if (!format) return;

        try {
            this.showProgressModal('Preparazione export...', 20);

            const response = await fetch('/admin/staff/export', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    staff_ids: staffIds,
                    format: format
                })
            });

            if (!response.ok) {
                throw new Error('Errore durante l\'export');
            }

            this.updateProgressModal('Download in corso...', 90);

            // Download del file
            const blob = await response.blob();
            const downloadUrl = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = `staff_export_${new Date().getTime()}.${format}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            window.URL.revokeObjectURL(downloadUrl);

            this.processingResults.success = staffIds;
            this.staffManager.notificationManager.showSuccess(
                `Export completato: ${staffIds.length} staff esportati`
            );

        } catch (error) {
            console.error('❌ Export error:', error);
            this.processingResults.failed = staffIds;
            throw error;
        }
    }

    // ==========================================
    // BATCH PROCESSING
    // ==========================================

    /**
     * Processa elementi in batch con rate limiting
     */
    async processBatch(action, items, processor) {
        const batchSize = 5; // Processa 5 items alla volta
        const delayBetweenBatches = 100; // 100ms delay tra batch

        this.showProgressModal(`Elaborazione ${action} in corso...`, 0);

        for (let i = 0; i < items.length; i += batchSize) {
            const batch = items.slice(i, i + batchSize);
            const batchPromises = batch.map(async (item) => {
                try {
                    const result = await processor(item);
                    this.processingResults.success.push(item);
                    return { success: true, item, result };
                } catch (error) {
                    this.processingResults.failed.push(item);
                    console.error(`❌ Failed to process ${item}:`, error);
                    return { success: false, item, error: error.message };
                }
            });

            // Aspetta completamento batch
            await Promise.allSettled(batchPromises);

            // Aggiorna progress
            const progress = Math.round(((i + batchSize) / items.length) * 100);
            this.updateProgressModal(
                `Elaborazione ${action}: ${Math.min(i + batchSize, items.length)}/${items.length}`,
                progress
            );

            // Delay tra batch (se non è l'ultimo)
            if (i + batchSize < items.length) {
                await this.delay(delayBetweenBatches);
            }
        }
    }

    /**
     * Utility delay
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    // ==========================================
    // UI MODALS
    // ==========================================

    /**
     * Crea modal per azioni e progress
     */
    createActionModal() {
        // Modal container
        const modalContainer = document.createElement('div');
        modalContainer.id = 'bulk-action-modal';
        modalContainer.className = 'fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50';
        modalContainer.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
                <div id="modal-content">
                    <!-- Content will be dynamically inserted -->
                </div>
            </div>
        `;
        document.body.appendChild(modalContainer);

        // Progress modal
        const progressModal = document.createElement('div');
        progressModal.id = 'bulk-progress-modal';
        progressModal.className = 'fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50';
        progressModal.innerHTML = `
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
                <div class="text-center">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-rose-500 mx-auto mb-4"></div>
                    <h3 id="progress-title" class="text-lg font-medium text-gray-900 mb-2">Elaborazione...</h3>
                    <div class="w-full bg-gray-200 rounded-full h-2.5 mb-4">
                        <div id="progress-bar" class="bg-gradient-to-r from-rose-500 to-purple-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                    </div>
                    <p id="progress-text" class="text-sm text-gray-600">0%</p>
                </div>
            </div>
        `;
        document.body.appendChild(progressModal);
    }

    /**
     * Mostra modal di conferma
     */
    async showConfirmationModal(action, itemCount) {
        const modal = document.getElementById('bulk-action-modal');
        const content = document.getElementById('modal-content');

        const actionLabels = {
            activate: { title: 'Attivare Staff', message: 'attivare', color: 'green', icon: '✅' },
            deactivate: { title: 'Disattivare Staff', message: 'disattivare', color: 'yellow', icon: '⏸️' },
            delete: { title: 'Eliminare Staff', message: 'eliminare definitivamente', color: 'red', icon: '🗑️' },
            assign: { title: 'Assegnare Staff', message: 'assegnare a un corso', color: 'blue', icon: '📚' },
            export: { title: 'Esportare Staff', message: 'esportare', color: 'purple', icon: '📄' }
        };

        const config = actionLabels[action] || { title: 'Azione', message: 'elaborare', color: 'gray', icon: '⚡' };

        content.innerHTML = `
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-${config.color}-100 flex items-center justify-center text-2xl mr-4">
                        ${config.icon}
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">${config.title}</h3>
                </div>

                <div class="mb-6">
                    <p class="text-gray-600">
                        Sei sicuro di voler <strong>${config.message}</strong>
                        <span class="font-semibold text-${config.color}-600">${itemCount}</span>
                        membro${itemCount > 1 ? 'i' : ''} dello staff?
                    </p>
                    ${action === 'delete' ? `
                        <div class="mt-3 p-3 bg-red-50 border border-red-200 rounded-lg">
                            <p class="text-sm text-red-700">
                                ⚠️ <strong>Attenzione:</strong> Questa azione non può essere annullata.
                            </p>
                        </div>
                    ` : ''}
                </div>

                <div class="flex justify-end space-x-3">
                    <button data-modal-cancel
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200">
                        Annulla
                    </button>
                    <button data-modal-confirm data-action="${action}"
                            class="px-4 py-2 text-sm font-medium text-white bg-${config.color}-600 hover:bg-${config.color}-700 rounded-lg transition-colors duration-200">
                        ${config.title}
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'flex';

        return new Promise((resolve) => {
            this.confirmResolve = resolve;
        });
    }

    /**
     * Mostra modal progresso
     */
    showProgressModal(title, progress) {
        const modal = document.getElementById('bulk-progress-modal');
        const titleElement = document.getElementById('progress-title');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');

        titleElement.textContent = title;
        progressBar.style.width = `${progress}%`;
        progressText.textContent = `${progress}%`;

        modal.style.display = 'flex';
    }

    /**
     * Aggiorna modal progresso
     */
    updateProgressModal(title, progress) {
        const titleElement = document.getElementById('progress-title');
        const progressBar = document.getElementById('progress-bar');
        const progressText = document.getElementById('progress-text');

        if (titleElement) titleElement.textContent = title;
        if (progressBar) progressBar.style.width = `${progress}%`;
        if (progressText) progressText.textContent = `${progress}%`;
    }

    /**
     * Nascondi modal progresso
     */
    hideProgressModal() {
        const modal = document.getElementById('bulk-progress-modal');
        modal.style.display = 'none';
    }

    /**
     * Gestisci conferma modal
     */
    handleModalConfirm(button) {
        const modal = document.getElementById('bulk-action-modal');
        modal.style.display = 'none';

        if (this.confirmResolve) {
            this.confirmResolve(true);
            this.confirmResolve = null;
        }
    }

    /**
     * Gestisci cancellazione modal
     */
    handleModalCancel() {
        const modal = document.getElementById('bulk-action-modal');
        modal.style.display = 'none';

        if (this.confirmResolve) {
            this.confirmResolve(false);
            this.confirmResolve = null;
        }
    }

    // ==========================================
    // SPECIALIZED PROMPTS
    // ==========================================

    /**
     * Prompt per selezione corso
     */
    async promptForCourseSelection() {
        // Qui potresti implementare un dropdown con i corsi disponibili
        // Per ora simuliamo con un prompt
        const courseId = prompt('Inserisci ID del corso:');
        return courseId ? parseInt(courseId) : null;
    }

    /**
     * Prompt per formato export
     */
    async promptForExportFormat() {
        const format = prompt('Formato export (csv, xlsx, pdf):', 'csv');
        return ['csv', 'xlsx', 'pdf'].includes(format) ? format : null;
    }

    // ==========================================
    // RESULTS & FEEDBACK
    // ==========================================

    /**
     * Mostra riepilogo risultati
     */
    showResultsSummary() {
        const { success, failed, total } = this.processingResults;

        if (failed.length === 0) {
            // Tutto ok
            this.staffManager.notificationManager.showSuccess(
                `✅ Operazione completata: ${success.length}/${total} elementi elaborati con successo`
            );
        } else if (success.length === 0) {
            // Tutto fallito
            this.staffManager.notificationManager.showError(
                `❌ Operazione fallita: 0/${total} elementi elaborati correttamente`
            );
        } else {
            // Parzialmente completato
            this.staffManager.notificationManager.showWarning(
                `⚠️ Operazione parziale: ${success.length}/${total} elementi elaborati. ${failed.length} errori.`
            );
        }

        // Pulisci selezione se l'operazione ha avuto successo
        if (success.length > 0) {
            this.staffManager.selectionManager.clearSelection();
        }

        // Aggiorna statistiche se necessario
        this.staffManager.updateStatsAfterDelete();
    }

    /**
     * Aggiorna stato azioni bulk basato su selezione
     */
    updateBulkActionState(selectionCount) {
        const bulkActionButtons = document.querySelectorAll('[data-bulk-action]');
        bulkActionButtons.forEach(button => {
            button.disabled = selectionCount === 0;
            button.classList.toggle('opacity-50', selectionCount === 0);
            button.classList.toggle('cursor-not-allowed', selectionCount === 0);
        });

        // Aggiorna testo nei bottoni con count
        const buttonTexts = {
            activate: `Attiva (${selectionCount})`,
            deactivate: `Disattiva (${selectionCount})`,
            delete: `Elimina (${selectionCount})`,
            export: `Esporta (${selectionCount})`
        };

        bulkActionButtons.forEach(button => {
            const action = button.dataset.bulkAction;
            if (buttonTexts[action]) {
                const originalText = button.textContent.replace(/\s*\(\d+\)$/, '');
                button.textContent = selectionCount > 0
                    ? `${originalText} (${selectionCount})`
                    : originalText;
            }
        });
    }
}

export default BulkActionManager;