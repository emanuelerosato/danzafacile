/**
 * NotificationManager - Gestione notifiche toast per Enrollments
 * Preserva il pattern utilizzato in altre sezioni del sistema
 */
export class NotificationManager {
    constructor() {
        this.container = null;
        this.createContainer();
    }

    createContainer() {
        if (document.getElementById('enrollment-notifications')) return;

        this.container = document.createElement('div');
        this.container.id = 'enrollment-notifications';
        this.container.className = 'fixed top-4 right-4 z-50 space-y-2';
        document.body.appendChild(this.container);
    }

    show(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `
            px-6 py-4 rounded-lg shadow-lg text-white font-medium
            transform translate-x-full transition-transform duration-300 ease-out
            ${type === 'error' ? 'bg-red-500' : 'bg-green-500'}
        `;
        toast.textContent = message;

        this.container.appendChild(toast);

        // Show animation
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
        }, 100);

        // Auto hide dopo 3 secondi (stesso pattern di rooms)
        setTimeout(() => {
            toast.classList.add('translate-x-full');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    showSuccess(message) {
        this.show(message, 'success');
    }

    showError(message) {
        this.show(message, 'error');
    }
}