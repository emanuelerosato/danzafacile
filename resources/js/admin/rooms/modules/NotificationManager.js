/**
 * NotificationManager - Gestione notifiche toast
 * Preserva funzionalità identiche al sistema esistente
 */
export class NotificationManager {
    constructor() {
        this.notification = document.getElementById('notification');
        this.notificationMessage = document.getElementById('notificationMessage');
    }

    /**
     * Mostra notifica (identico al sistema esistente)
     */
    show(message, type = 'success') {
        this.notificationMessage.textContent = message;

        // Set color based on type (identico al sistema esistente)
        if (type === 'error') {
            this.notification.querySelector('div').className = 'bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg';
        } else {
            this.notification.querySelector('div').className = 'bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg';
        }

        // Show notification
        this.notification.classList.remove('translate-x-full');

        // Auto hide after 3 seconds (identico al sistema esistente)
        setTimeout(() => {
            this.notification.classList.add('translate-x-full');
        }, 3000);
    }

    /**
     * Mostra notifica di successo
     */
    showSuccess(message) {
        this.show(message, 'success');
    }

    /**
     * Mostra notifica di errore
     */
    showError(message) {
        this.show(message, 'error');
    }
}