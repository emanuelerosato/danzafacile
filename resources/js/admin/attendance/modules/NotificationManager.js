/**
 * NotificationManager - Sistema moderno di notifiche
 * Sostituisce i primitivi alert() con toast notifications
 *
 * FASE 2: JavaScript Modernization
 */
export class NotificationManager {
    constructor() {
        this.container = null;
        this.init();
    }

    /**
     * Inizializza il container delle notifiche
     */
    init() {
        // Crea container se non esiste
        if (!document.getElementById('notification-container')) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.className = 'fixed top-4 right-4 z-50 space-y-2';
            document.body.appendChild(container);
        }
        this.container = document.getElementById('notification-container');
        console.log('📢 NotificationManager initialized');
    }

    /**
     * Mostra notifica di successo
     */
    showSuccess(message, duration = 4000) {
        this.show(message, 'success', duration);
    }

    /**
     * Mostra notifica di errore
     */
    showError(message, duration = 6000) {
        this.show(message, 'error', duration);
    }

    /**
     * Mostra notifica informativa
     */
    showInfo(message, duration = 4000) {
        this.show(message, 'info', duration);
    }

    /**
     * Mostra notifica warning
     */
    showWarning(message, duration = 5000) {
        this.show(message, 'warning', duration);
    }

    /**
     * Sistema principale di notifica
     */
    show(message, type = 'info', duration = 4000) {
        const notification = this.createNotificationElement(message, type);

        // Aggiungi al container
        this.container.appendChild(notification);

        // Animazione di entrata
        setTimeout(() => {
            notification.classList.remove('translate-x-full', 'opacity-0');
        }, 10);

        // Auto-rimozione
        setTimeout(() => {
            this.remove(notification);
        }, duration);

        // Click per chiudere
        const closeBtn = notification.querySelector('.notification-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.remove(notification));
        }
    }

    /**
     * Crea elemento notifica
     */
    createNotificationElement(message, type) {
        const notification = document.createElement('div');
        const { bgClass, iconSvg, borderClass } = this.getTypeStyles(type);

        notification.className = `
            max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto
            flex ring-1 ring-black ring-opacity-5 transform transition-all duration-300
            translate-x-full opacity-0 ${borderClass}
        `.trim().replace(/\s+/g, ' ');

        notification.innerHTML = `
            <div class="flex-1 w-0 p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <div class="${bgClass} rounded-lg p-1">
                            ${iconSvg}
                        </div>
                    </div>
                    <div class="ml-3 flex-1">
                        <p class="text-sm font-medium text-gray-900">
                            ${this.escapeHtml(message)}
                        </p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button class="notification-close bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500">
                            <span class="sr-only">Chiudi</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        `;

        return notification;
    }

    /**
     * Ottieni stili per tipo notifica
     */
    getTypeStyles(type) {
        const styles = {
            success: {
                bgClass: 'bg-green-100',
                borderClass: 'border-l-4 border-green-500',
                iconSvg: `
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                `
            },
            error: {
                bgClass: 'bg-red-100',
                borderClass: 'border-l-4 border-red-500',
                iconSvg: `
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                `
            },
            warning: {
                bgClass: 'bg-yellow-100',
                borderClass: 'border-l-4 border-yellow-500',
                iconSvg: `
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.864-.833-2.634 0L3.098 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                `
            },
            info: {
                bgClass: 'bg-blue-100',
                borderClass: 'border-l-4 border-blue-500',
                iconSvg: `
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                `
            }
        };

        return styles[type] || styles.info;
    }

    /**
     * Rimuovi notifica con animazione
     */
    remove(notification) {
        notification.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }

    /**
     * Escape HTML per sicurezza
     */
    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }

    /**
     * Pulisci tutte le notifiche
     */
    clear() {
        if (this.container) {
            this.container.innerHTML = '';
        }
    }
}