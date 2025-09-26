/**
 * NotificationManager - Modern toast notification system
 *
 * Features:
 * - Multiple notification types (success, error, warning, info)
 * - Auto-dismiss with configurable duration
 * - Manual dismiss capability
 * - Position and animation options
 * - Queue management for multiple notifications
 */

export default class NotificationManager {
    constructor(options = {}) {
        this.options = {
            position: 'top-right', // top-right, top-left, bottom-right, bottom-left
            duration: 3000,
            maxNotifications: 5,
            animations: true,
            ...options
        };

        this.notifications = new Map();
        this.container = null;
        this.notificationId = 0;

        this.init();
        console.log('[NotificationManager] ✅ Notification manager initialized');
    }

    /**
     * Initialize notification manager
     */
    init() {
        this.createContainer();
        this.bindGlobalEvents();
    }

    /**
     * Create notification container
     */
    createContainer() {
        this.container = document.createElement('div');
        this.container.id = 'notification-container';
        this.container.className = this.getContainerClasses();

        document.body.appendChild(this.container);

        console.log(`[NotificationManager] 📦 Container created at ${this.options.position}`);
    }

    /**
     * Get container CSS classes based on position
     */
    getContainerClasses() {
        const baseClasses = 'fixed z-50 pointer-events-none';
        const positionClasses = {
            'top-right': 'top-4 right-4',
            'top-left': 'top-4 left-4',
            'bottom-right': 'bottom-4 right-4',
            'bottom-left': 'bottom-4 left-4'
        };

        return `${baseClasses} ${positionClasses[this.options.position] || positionClasses['top-right']}`;
    }

    /**
     * Bind global events for integration
     */
    bindGlobalEvents() {
        // Listen for bulk action events
        document.addEventListener('eventRegistration:bulkActionSuccess', (e) => {
            this.showSuccess(e.detail.message);
        });

        document.addEventListener('eventRegistration:bulkActionError', (e) => {
            this.showError(e.detail.message);
        });

        // Listen for general events
        document.addEventListener('eventRegistration:error', (e) => {
            this.showError(e.detail.message);
        });

        document.addEventListener('eventRegistration:success', (e) => {
            this.showSuccess(e.detail.message);
        });

        document.addEventListener('eventRegistration:warning', (e) => {
            this.showWarning(e.detail.message);
        });

        document.addEventListener('eventRegistration:info', (e) => {
            this.showInfo(e.detail.message);
        });
    }

    /**
     * Show success notification
     */
    showSuccess(message, options = {}) {
        return this.show(message, 'success', options);
    }

    /**
     * Show error notification
     */
    showError(message, options = {}) {
        return this.show(message, 'error', {
            duration: 5000, // Errors stay longer
            ...options
        });
    }

    /**
     * Show warning notification
     */
    showWarning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    /**
     * Show info notification
     */
    showInfo(message, options = {}) {
        return this.show(message, 'info', options);
    }

    /**
     * Show notification with custom type
     */
    show(message, type = 'info', options = {}) {
        const config = {
            duration: this.options.duration,
            dismissible: true,
            actions: [],
            ...options
        };

        const notification = this.createNotification(message, type, config);
        this.addNotification(notification);

        return notification.id;
    }

    /**
     * Create notification element
     */
    createNotification(message, type, config) {
        const id = ++this.notificationId;
        const typeConfig = this.getTypeConfig(type);

        const notification = {
            id,
            type,
            message,
            config,
            element: null,
            timer: null
        };

        // Create DOM element
        const element = document.createElement('div');
        element.className = `notification pointer-events-auto transform transition-all duration-300 ease-in-out mb-3 ${this.getNotificationClasses(type)}`;
        element.setAttribute('data-notification-id', id);

        element.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 ${typeConfig.iconBg} rounded-lg flex items-center justify-center">
                        ${typeConfig.icon}
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium ${typeConfig.textColor}">${message}</p>
                    ${config.actions.length > 0 ? this.createActionButtons(config.actions) : ''}
                </div>
                ${config.dismissible ? `
                    <div class="ml-4 flex-shrink-0">
                        <button class="notification-close inline-flex text-gray-400 hover:text-gray-600 focus:outline-none" data-notification-id="${id}">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                ` : ''}
            </div>
        `;

        notification.element = element;

        // Bind close event
        if (config.dismissible) {
            const closeButton = element.querySelector('.notification-close');
            closeButton.addEventListener('click', () => {
                this.dismiss(id);
            });
        }

        // Auto-dismiss timer
        if (config.duration > 0) {
            notification.timer = setTimeout(() => {
                this.dismiss(id);
            }, config.duration);
        }

        return notification;
    }

    /**
     * Get type configuration
     */
    getTypeConfig(type) {
        const configs = {
            success: {
                icon: '<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>',
                iconBg: 'bg-green-100',
                textColor: 'text-green-800',
                bgColor: 'bg-green-50',
                borderColor: 'border-green-200'
            },
            error: {
                icon: '<svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>',
                iconBg: 'bg-red-100',
                textColor: 'text-red-800',
                bgColor: 'bg-red-50',
                borderColor: 'border-red-200'
            },
            warning: {
                icon: '<svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.998-.833-2.768 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>',
                iconBg: 'bg-yellow-100',
                textColor: 'text-yellow-800',
                bgColor: 'bg-yellow-50',
                borderColor: 'border-yellow-200'
            },
            info: {
                icon: '<svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
                iconBg: 'bg-blue-100',
                textColor: 'text-blue-800',
                bgColor: 'bg-blue-50',
                borderColor: 'border-blue-200'
            }
        };

        return configs[type] || configs.info;
    }

    /**
     * Get notification CSS classes
     */
    getNotificationClasses(type) {
        const typeConfig = this.getTypeConfig(type);
        return `max-w-sm w-full ${typeConfig.bgColor} ${typeConfig.borderColor} border rounded-lg shadow-lg p-4`;
    }

    /**
     * Create action buttons
     */
    createActionButtons(actions) {
        if (actions.length === 0) return '';

        const buttonsHtml = actions.map(action => `
            <button class="notification-action text-xs font-medium ${action.style || 'text-blue-600 hover:text-blue-500'} mr-3"
                    data-action="${action.key}">
                ${action.label}
            </button>
        `).join('');

        return `<div class="mt-2">${buttonsHtml}</div>`;
    }

    /**
     * Add notification to container
     */
    addNotification(notification) {
        // Remove oldest notification if at max limit
        if (this.notifications.size >= this.options.maxNotifications) {
            const oldestId = this.notifications.keys().next().value;
            this.dismiss(oldestId);
        }

        // Store notification
        this.notifications.set(notification.id, notification);

        // Add to DOM with animation
        if (this.options.animations) {
            notification.element.style.transform = this.getInitialTransform();
            notification.element.style.opacity = '0';
        }

        this.container.appendChild(notification.element);

        // Animate in
        if (this.options.animations) {
            requestAnimationFrame(() => {
                notification.element.style.transform = 'translateX(0) translateY(0) scale(1)';
                notification.element.style.opacity = '1';
            });
        }

        console.log(`[NotificationManager] 📢 Notification ${notification.id} added (${notification.type})`);
    }

    /**
     * Get initial transform for animation
     */
    getInitialTransform() {
        switch (this.options.position) {
            case 'top-right':
            case 'bottom-right':
                return 'translateX(100%) scale(0.9)';
            case 'top-left':
            case 'bottom-left':
                return 'translateX(-100%) scale(0.9)';
            default:
                return 'translateY(-20px) scale(0.9)';
        }
    }

    /**
     * Dismiss notification
     */
    dismiss(id) {
        const notification = this.notifications.get(id);
        if (!notification) return;

        // Clear timer
        if (notification.timer) {
            clearTimeout(notification.timer);
        }

        // Animate out
        if (this.options.animations && notification.element) {
            notification.element.style.transform = this.getFinalTransform();
            notification.element.style.opacity = '0';

            setTimeout(() => {
                this.removeNotification(id);
            }, 300);
        } else {
            this.removeNotification(id);
        }

        console.log(`[NotificationManager] 👋 Notification ${id} dismissed`);
    }

    /**
     * Get final transform for animation
     */
    getFinalTransform() {
        switch (this.options.position) {
            case 'top-right':
            case 'bottom-right':
                return 'translateX(100%) scale(0.9)';
            case 'top-left':
            case 'bottom-left':
                return 'translateX(-100%) scale(0.9)';
            default:
                return 'translateY(-20px) scale(0.9)';
        }
    }

    /**
     * Remove notification from DOM and memory
     */
    removeNotification(id) {
        const notification = this.notifications.get(id);
        if (!notification) return;

        // Remove from DOM
        if (notification.element && notification.element.parentNode) {
            notification.element.parentNode.removeChild(notification.element);
        }

        // Remove from memory
        this.notifications.delete(id);
    }

    /**
     * Dismiss all notifications
     */
    dismissAll() {
        const ids = Array.from(this.notifications.keys());
        ids.forEach(id => this.dismiss(id));

        console.log('[NotificationManager] 🧹 All notifications dismissed');
    }

    /**
     * Update notification
     */
    update(id, newMessage, newType) {
        const notification = this.notifications.get(id);
        if (!notification) return false;

        notification.message = newMessage;
        if (newType) notification.type = newType;

        // Update DOM
        const messageElement = notification.element.querySelector('p');
        if (messageElement) {
            messageElement.textContent = newMessage;
        }

        return true;
    }

    /**
     * Show notification with progress bar
     */
    showProgress(message, progress = 0, options = {}) {
        const config = {
            duration: 0, // Don't auto-dismiss
            dismissible: false,
            ...options
        };

        const notification = this.createProgressNotification(message, progress, config);
        this.addNotification(notification);

        return notification.id;
    }

    /**
     * Create progress notification
     */
    createProgressNotification(message, progress, config) {
        const id = ++this.notificationId;

        const notification = {
            id,
            type: 'progress',
            message,
            config,
            progress,
            element: null,
            timer: null
        };

        // Create DOM element
        const element = document.createElement('div');
        element.className = 'notification pointer-events-auto transform transition-all duration-300 ease-in-out mb-3 max-w-sm w-full bg-blue-50 border border-blue-200 rounded-lg shadow-lg p-4';
        element.setAttribute('data-notification-id', id);

        element.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                    </div>
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium text-blue-800">${message}</p>
                    <div class="mt-2">
                        <div class="bg-blue-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" style="width: ${progress}%"></div>
                        </div>
                        <p class="text-xs text-blue-600 mt-1 text-right">${progress}%</p>
                    </div>
                </div>
            </div>
        `;

        notification.element = element;
        return notification;
    }

    /**
     * Update progress notification
     */
    updateProgress(id, progress, message) {
        const notification = this.notifications.get(id);
        if (!notification || notification.type !== 'progress') return false;

        notification.progress = progress;
        if (message) notification.message = message;

        // Update DOM
        const progressBar = notification.element.querySelector('.bg-blue-600');
        const progressText = notification.element.querySelector('.text-xs');
        const messageElement = notification.element.querySelector('p');

        if (progressBar) progressBar.style.width = `${progress}%`;
        if (progressText) progressText.textContent = `${progress}%`;
        if (message && messageElement) messageElement.textContent = message;

        return true;
    }

    /**
     * Get active notifications count
     */
    getActiveCount() {
        return this.notifications.size;
    }

    /**
     * Get all active notifications
     */
    getActiveNotifications() {
        return Array.from(this.notifications.values());
    }

    /**
     * Clear all notifications and reset
     */
    clear() {
        this.dismissAll();
        if (this.container) {
            this.container.innerHTML = '';
        }
    }

    /**
     * Destroy notification manager
     */
    destroy() {
        this.clear();
        if (this.container && this.container.parentNode) {
            this.container.parentNode.removeChild(this.container);
        }
        this.notifications.clear();

        console.log('[NotificationManager] 🔥 Notification manager destroyed');
    }
}