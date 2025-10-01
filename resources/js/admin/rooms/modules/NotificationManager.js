/**
 * NotificationManager - Gestione notifiche toast
 * Preserva funzionalità identiche al sistema esistente
 */
export class NotificationManager {
    constructor() {
        this.notification = document.getElementById('notification');
        this.notificationMessage = document.getElementById('notificationMessage');
        this.notificationContent = document.getElementById('notificationContent');
    }

    /**
     * Mostra notifica (identico al sistema esistente)
     */
    show(message, type = 'success') {
        this.notificationMessage.textContent = message;

        // Set color and icon based on type
        if (type === 'error') {
            this.notificationContent.className = 'bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3';
        } else {
            this.notificationContent.className = 'bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3';
        }

        // Show notification (rimuove hidden e translate-x-full)
        this.notification.classList.remove('hidden');
        this.notification.classList.remove('translate-x-full');

        // Auto hide after 3 seconds (identico al sistema esistente)
        setTimeout(() => {
            this.notification.classList.add('translate-x-full');

            // Nascondi completamente dopo la transizione
            setTimeout(() => {
                this.notification.classList.add('hidden');
            }, 300); // Durata della transizione
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