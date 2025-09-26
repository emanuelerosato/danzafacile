/**
 * 🔔 NOTIFICATION MANAGER - Sistema Toast Moderno Staff
 *
 * Sostituisce alert() e confirm() primitivi
 *
 * Gestisce:
 * - Toast notifications (success, error, warning, info)
 * - Progress notifications per operazioni lunghe
 * - Auto-dismiss configurabile
 * - Queue management (max 5 notifiche)
 * - Modern confirmation modals
 */

export class NotificationManager {
    constructor(staffManager) {
        this.staffManager = staffManager;
        this.notifications = [];
        this.maxNotifications = 5;
        this.defaultDuration = 5000; // 5 secondi
        this.progressNotifications = new Map();

        this.initialize();
        console.log('🔔 NotificationManager initialized');
    }

    /**
     * Inizializzazione
     */
    initialize() {
        this.createNotificationContainer();
        this.createConfirmationModal();
        this.attachEventListeners();
    }

    /**
     * Crea container per toast notifications
     */
    createNotificationContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-4 right-4 z-50 space-y-3';
        container.style.maxWidth = '400px';
        document.body.appendChild(container);

        // Stili CSS per animazioni
        const style = document.createElement('style');
        style.textContent = `
            .toast {
                transform: translateX(400px);
                transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
                opacity: 0;
            }

            .toast.show {
                transform: translateX(0);
                opacity: 1;
            }

            .toast.hide {
                transform: translateX(400px);
                opacity: 0;
            }

            .toast-progress-bar {
                animation: toast-progress linear forwards;
            }

            @keyframes toast-progress {
                from { width: 100%; }
                to { width: 0%; }
            }

            .confirmation-modal-backdrop {
                backdrop-filter: blur(4px);
                animation: modalBackdropFadeIn 0.2s ease-out;
            }

            .confirmation-modal-content {
                animation: modalContentSlideIn 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            }

            @keyframes modalBackdropFadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            @keyframes modalContentSlideIn {
                from {
                    opacity: 0;
                    transform: translateY(-20px) scale(0.95);
                }
                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .pulse-ring {
                animation: pulse-ring 1.5s cubic-bezier(0.455, 0.030, 0.515, 0.955) infinite;
            }

            @keyframes pulse-ring {
                0% {
                    transform: scale(0.8);
                    opacity: 1;
                }
                50% {
                    transform: scale(1.2);
                    opacity: 0.3;
                }
                100% {
                    transform: scale(0.8);
                    opacity: 1;
                }
            }
        `;
        document.head.appendChild(style);
    }

    /**
     * Crea modal per conferme
     */
    createConfirmationModal() {
        const modal = document.createElement('div');
        modal.id = 'confirmation-modal';
        modal.className = 'confirmation-modal-backdrop fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50';
        modal.innerHTML = `
            <div class="confirmation-modal-content bg-white rounded-xl shadow-2xl max-w-md w-full mx-4">
                <div id="confirmation-content">
                    <!-- Content will be dynamically inserted -->
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    /**
     * Registra event listeners
     */
    attachEventListeners() {
        // Click outside modal to close
        document.getElementById('confirmation-modal').addEventListener('click', (event) => {
            if (event.target.id === 'confirmation-modal') {
                this.cancelConfirmation();
            }
        });

        // Escape key to close
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                this.cancelConfirmation();
            }
        });

        // Close toast on click
        document.addEventListener('click', (event) => {
            if (event.target.matches('.toast-close')) {
                const toast = event.target.closest('.toast');
                if (toast) this.dismissToast(toast.dataset.toastId);
            }
        });
    }

    // ==========================================
    // TOAST NOTIFICATIONS
    // ==========================================

    /**
     * Mostra toast di successo
     */
    showSuccess(message, duration = this.defaultDuration) {
        return this.showToast('success', message, duration);
    }

    /**
     * Mostra toast di errore
     */
    showError(message, duration = 8000) { // Errori durano di più
        return this.showToast('error', message, duration);
    }

    /**
     * Mostra toast di warning
     */
    showWarning(message, duration = 6000) {
        return this.showToast('warning', message, duration);
    }

    /**
     * Mostra toast informativo
     */
    showInfo(message, duration = this.defaultDuration) {
        return this.showToast('info', message, duration);
    }

    /**
     * Mostra toast generico
     */
    showToast(type, message, duration) {
        const toastId = `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;

        // Rimuovi toast in eccesso se necessario
        this.cleanupOldToasts();

        const toastConfig = {
            success: {
                icon: '✅',
                bgColor: 'bg-green-50',
                borderColor: 'border-green-200',
                textColor: 'text-green-800',
                iconBg: 'bg-green-100',
                progressColor: 'bg-green-500'
            },
            error: {
                icon: '❌',
                bgColor: 'bg-red-50',
                borderColor: 'border-red-200',
                textColor: 'text-red-800',
                iconBg: 'bg-red-100',
                progressColor: 'bg-red-500'
            },
            warning: {
                icon: '⚠️',
                bgColor: 'bg-yellow-50',
                borderColor: 'border-yellow-200',
                textColor: 'text-yellow-800',
                iconBg: 'bg-yellow-100',
                progressColor: 'bg-yellow-500'
            },
            info: {
                icon: 'ℹ️',
                bgColor: 'bg-blue-50',
                borderColor: 'border-blue-200',
                textColor: 'text-blue-800',
                iconBg: 'bg-blue-100',
                progressColor: 'bg-blue-500'
            }
        };

        const config = toastConfig[type] || toastConfig.info;

        const toast = document.createElement('div');
        toast.className = `toast ${config.bgColor} ${config.borderColor} ${config.textColor} border rounded-lg shadow-lg p-4 max-w-sm`;
        toast.dataset.toastId = toastId;
        toast.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="${config.iconBg} rounded-full p-2 text-lg">
                        ${config.icon}
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium">${message}</p>
                    ${duration > 0 ? `
                        <div class="mt-2 h-1 bg-gray-200 rounded-full overflow-hidden">
                            <div class="toast-progress-bar h-full ${config.progressColor} rounded-full"
                                 style="animation-duration: ${duration}ms;"></div>
                        </div>
                    ` : ''}
                </div>
                <div class="ml-2">
                    <button class="toast-close text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        const container = document.getElementById('toast-container');
        container.appendChild(toast);

        // Trigger animation
        setTimeout(() => toast.classList.add('show'), 10);

        // Store notification
        this.notifications.push({
            id: toastId,
            element: toast,
            type: type,
            message: message,
            timestamp: Date.now()
        });

        // Auto-dismiss
        if (duration > 0) {
            setTimeout(() => this.dismissToast(toastId), duration);
        }

        console.log(`🔔 Toast ${type}: ${message}`);
        return toastId;
    }

    /**
     * Rimuovi toast specifico
     */
    dismissToast(toastId) {
        const notification = this.notifications.find(n => n.id === toastId);
        if (!notification) return;

        const toast = notification.element;
        toast.classList.add('hide');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.notifications = this.notifications.filter(n => n.id !== toastId);
        }, 400);
    }

    /**
     * Rimuovi toast vecchi se necessario
     */
    cleanupOldToasts() {
        while (this.notifications.length >= this.maxNotifications) {
            const oldestToast = this.notifications[0];
            this.dismissToast(oldestToast.id);
        }
    }

    /**
     * Rimuovi tutti i toast
     */
    clearAllToasts() {
        this.notifications.forEach(notification => {
            this.dismissToast(notification.id);
        });
    }

    // ==========================================
    // PROGRESS NOTIFICATIONS
    // ==========================================

    /**
     * Mostra notifica di progresso
     */
    showProgress(message, percentage = 0) {
        const progressId = `progress-${Date.now()}`;

        const toast = document.createElement('div');
        toast.className = 'toast bg-blue-50 border-blue-200 text-blue-800 border rounded-lg shadow-lg p-4 max-w-sm';
        toast.dataset.toastId = progressId;
        toast.innerHTML = `
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="bg-blue-100 rounded-full p-2">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-500"></div>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium progress-message">${message}</p>
                    <div class="mt-2">
                        <div class="flex justify-between text-xs text-blue-600 mb-1">
                            <span>Progresso</span>
                            <span class="progress-percentage">${percentage}%</span>
                        </div>
                        <div class="w-full bg-blue-200 rounded-full h-2">
                            <div class="progress-bar bg-blue-500 h-2 rounded-full transition-all duration-300"
                                 style="width: ${percentage}%"></div>
                        </div>
                    </div>
                </div>
            </div>
        `;

        const container = document.getElementById('toast-container');
        container.appendChild(toast);

        setTimeout(() => toast.classList.add('show'), 10);

        this.progressNotifications.set(progressId, {
            element: toast,
            message: message,
            percentage: percentage
        });

        console.log(`📊 Progress: ${message} (${percentage}%)`);
        return progressId;
    }

    /**
     * Aggiorna notifica di progresso
     */
    updateProgress(progressId, message, percentage) {
        const progress = this.progressNotifications.get(progressId);
        if (!progress) return;

        const messageElement = progress.element.querySelector('.progress-message');
        const percentageElement = progress.element.querySelector('.progress-percentage');
        const progressBar = progress.element.querySelector('.progress-bar');

        if (messageElement) messageElement.textContent = message;
        if (percentageElement) percentageElement.textContent = `${percentage}%`;
        if (progressBar) progressBar.style.width = `${percentage}%`;

        progress.message = message;
        progress.percentage = percentage;

        console.log(`📊 Progress updated: ${message} (${percentage}%)`);
    }

    /**
     * Nascondi notifica di progresso
     */
    hideProgress(progressId) {
        const progress = this.progressNotifications.get(progressId);
        if (!progress) return;

        const toast = progress.element;
        toast.classList.add('hide');

        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
            this.progressNotifications.delete(progressId);
        }, 400);

        console.log('📊 Progress hidden');
    }

    // ==========================================
    // CONFIRMATION MODALS
    // ==========================================

    /**
     * Mostra modal di conferma moderno
     */
    async showConfirmation(title, message, confirmText = 'Conferma', cancelText = 'Annulla', type = 'warning') {
        const modal = document.getElementById('confirmation-modal');
        const content = document.getElementById('confirmation-content');

        const typeConfig = {
            danger: {
                icon: '⚠️',
                iconBg: 'bg-red-100',
                iconColor: 'text-red-600',
                buttonBg: 'bg-red-600',
                buttonHover: 'hover:bg-red-700'
            },
            warning: {
                icon: '❓',
                iconBg: 'bg-yellow-100',
                iconColor: 'text-yellow-600',
                buttonBg: 'bg-yellow-600',
                buttonHover: 'hover:bg-yellow-700'
            },
            info: {
                icon: 'ℹ️',
                iconBg: 'bg-blue-100',
                iconColor: 'text-blue-600',
                buttonBg: 'bg-blue-600',
                buttonHover: 'hover:bg-blue-700'
            }
        };

        const config = typeConfig[type] || typeConfig.warning;

        content.innerHTML = `
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="pulse-ring w-12 h-12 ${config.iconBg} rounded-full flex items-center justify-center mr-4">
                        <span class="text-2xl ${config.iconColor}">${config.icon}</span>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">${title}</h3>
                </div>

                <div class="mb-6">
                    <p class="text-gray-600">${message}</p>
                </div>

                <div class="flex justify-end space-x-3">
                    <button id="modal-cancel"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2">
                        ${cancelText}
                    </button>
                    <button id="modal-confirm"
                            class="px-4 py-2 text-sm font-medium text-white ${config.buttonBg} ${config.buttonHover} rounded-lg transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2">
                        ${confirmText}
                    </button>
                </div>
            </div>
        `;

        modal.style.display = 'flex';

        return new Promise((resolve) => {
            this.confirmationResolve = resolve;

            // Attach event listeners
            document.getElementById('modal-confirm').onclick = () => {
                this.hideConfirmationModal();
                resolve(true);
            };

            document.getElementById('modal-cancel').onclick = () => {
                this.hideConfirmationModal();
                resolve(false);
            };
        });
    }

    /**
     * Nascondi modal di conferma
     */
    hideConfirmationModal() {
        const modal = document.getElementById('confirmation-modal');
        modal.style.display = 'none';
        this.confirmationResolve = null;
    }

    /**
     * Cancella conferma (ESC o click outside)
     */
    cancelConfirmation() {
        if (this.confirmationResolve) {
            this.hideConfirmationModal();
            this.confirmationResolve(false);
        }
    }

    // ==========================================
    // UTILITY METHODS
    // ==========================================

    /**
     * Ottieni statistiche notifiche
     */
    getStats() {
        const stats = {
            total: this.notifications.length,
            byType: {}
        };

        this.notifications.forEach(notification => {
            stats.byType[notification.type] = (stats.byType[notification.type] || 0) + 1;
        });

        return stats;
    }

    /**
     * Test per verificare funzionamento
     */
    runTest() {
        console.log('🧪 Testing NotificationManager...');

        this.showInfo('Test notifica info');

        setTimeout(() => {
            this.showSuccess('Test notifica successo');
        }, 500);

        setTimeout(() => {
            this.showWarning('Test notifica warning');
        }, 1000);

        setTimeout(() => {
            this.showError('Test notifica errore');
        }, 1500);

        setTimeout(() => {
            const progressId = this.showProgress('Test progresso', 0);
            let progress = 0;
            const interval = setInterval(() => {
                progress += 20;
                this.updateProgress(progressId, `Test progresso ${progress}%`, progress);
                if (progress >= 100) {
                    clearInterval(interval);
                    setTimeout(() => this.hideProgress(progressId), 1000);
                }
            }, 500);
        }, 2000);

        setTimeout(async () => {
            const confirmed = await this.showConfirmation(
                'Test Conferma',
                'Questa è una conferma di test. Vuoi procedere?'
            );
            this.showInfo(`Conferma: ${confirmed ? 'Accettata' : 'Rifiutata'}`);
        }, 8000);

        console.log('🧪 Test completed!');
    }
}

export default NotificationManager;