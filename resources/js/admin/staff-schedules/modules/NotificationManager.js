/**
 * NotificationManager - Handles toast notifications and user feedback
 *
 * Provides notification functionality:
 * - Success, error, warning, info notifications
 * - Auto-dismiss with configurable duration
 * - Manual dismissal
 * - Stack management for multiple notifications
 * - Accessible notifications with ARIA labels
 * - Smooth animations
 */

export class NotificationManager {
    constructor(options = {}) {
        this.options = {
            position: 'top-right', // top-right, top-left, bottom-right, bottom-left, top-center, bottom-center
            duration: 5000, // Auto-dismiss duration in milliseconds
            maxNotifications: 5, // Maximum number of notifications to show at once
            showProgress: true, // Show progress bar for auto-dismiss
            enableSounds: false, // Play notification sounds
            ...options
        };

        // Notification state
        this.state = {
            notifications: new Map(),
            container: null,
            nextId: 1
        };

        // Notification queue for overflow management
        this.queue = [];

        // Initialize
        this.init();
    }

    /**
     * Initialize the notification manager
     */
    init() {
        try {
            this.log('Initializing NotificationManager...');

            // Create notification container
            this.createNotificationContainer();

            // Setup global error handler
            this.setupGlobalErrorHandler();

            this.log('NotificationManager initialized successfully');

        } catch (error) {
            console.error('Failed to initialize NotificationManager:', error);
        }
    }

    /**
     * Create notification container
     */
    createNotificationContainer() {
        // Remove existing container if present
        const existing = document.getElementById('notification-container');
        if (existing) {
            existing.remove();
        }

        // Create new container
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.className = this.getContainerClasses();
        container.setAttribute('role', 'region');
        container.setAttribute('aria-label', 'Notifiche');
        container.setAttribute('aria-live', 'polite');

        // Add to document
        document.body.appendChild(container);
        this.state.container = container;

        this.log('Notification container created');
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
            'bottom-left': 'bottom-4 left-4',
            'top-center': 'top-4 left-1/2 transform -translate-x-1/2',
            'bottom-center': 'bottom-4 left-1/2 transform -translate-x-1/2'
        };

        return `${baseClasses} ${positionClasses[this.options.position] || positionClasses['top-right']}`;
    }

    /**
     * Setup global error handler
     */
    setupGlobalErrorHandler() {
        // Catch unhandled errors
        window.addEventListener('error', (event) => {
            this.error('Si è verificato un errore imprevisto');
            this.log('Global error caught:', event.error);
        });

        // Catch unhandled promise rejections
        window.addEventListener('unhandledrejection', (event) => {
            this.error('Si è verificato un errore imprevisto');
            this.log('Unhandled promise rejection:', event.reason);
        });
    }

    /**
     * Show success notification
     */
    success(message, options = {}) {
        return this.show(message, 'success', options);
    }

    /**
     * Show error notification
     */
    error(message, options = {}) {
        return this.show(message, 'error', { duration: 0, ...options }); // Errors don't auto-dismiss by default
    }

    /**
     * Show warning notification
     */
    warning(message, options = {}) {
        return this.show(message, 'warning', options);
    }

    /**
     * Show info notification
     */
    info(message, options = {}) {
        return this.show(message, 'info', options);
    }

    /**
     * Show notification
     */
    show(message, type = 'info', options = {}) {
        const config = {
            duration: this.options.duration,
            showProgress: this.options.showProgress,
            dismissible: true,
            ...options
        };

        const notification = {
            id: this.state.nextId++,
            message,
            type,
            config,
            timestamp: Date.now(),
            element: null,
            timer: null,
            progressTimer: null
        };

        // Check if we have room for this notification
        if (this.state.notifications.size >= this.options.maxNotifications) {
            this.queue.push(notification);
            return notification.id;
        }

        // Create and show notification
        this.createNotificationElement(notification);
        this.state.notifications.set(notification.id, notification);

        // Setup auto-dismiss
        if (config.duration > 0) {
            this.setupAutoDismiss(notification);
        }

        // Play sound if enabled
        if (this.options.enableSounds) {
            this.playNotificationSound(type);
        }

        this.log('Notification shown:', notification);
        return notification.id;
    }

    /**
     * Create notification element
     */
    createNotificationElement(notification) {
        const { message, type, config } = notification;

        // Create notification element
        const element = document.createElement('div');
        element.className = this.getNotificationClasses(type);
        element.setAttribute('role', 'alert');
        element.setAttribute('data-notification-id', notification.id);

        // Build notification content
        element.innerHTML = `
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    ${this.getNotificationIcon(type)}
                </div>
                <div class="ml-3 flex-1">
                    <p class="text-sm font-medium ${this.getTextColor(type)}">
                        ${this.escapeHtml(message)}
                    </p>
                    ${config.showProgress && config.duration > 0 ? this.createProgressBar() : ''}
                </div>
                ${config.dismissible ? this.createCloseButton(type) : ''}
            </div>
        `;

        // Setup event listeners
        this.setupNotificationEventListeners(element, notification);

        // Add to container with animation
        this.addToContainer(element);

        notification.element = element;
    }

    /**
     * Get notification CSS classes
     */
    getNotificationClasses(type) {
        const baseClasses = 'pointer-events-auto w-full max-w-sm bg-white rounded-lg shadow-lg ring-1 ring-black ring-opacity-5 mb-4 transform transition-all duration-300 ease-in-out';

        const typeClasses = {
            success: 'border-l-4 border-green-400',
            error: 'border-l-4 border-red-400',
            warning: 'border-l-4 border-yellow-400',
            info: 'border-l-4 border-blue-400'
        };

        return `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
    }

    /**
     * Get notification icon
     */
    getNotificationIcon(type) {
        const icons = {
            success: `
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            `,
            error: `
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            `,
            warning: `
                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                </svg>
            `,
            info: `
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            `
        };

        return icons[type] || icons.info;
    }

    /**
     * Get text color for notification type
     */
    getTextColor(type) {
        const colors = {
            success: 'text-green-800',
            error: 'text-red-800',
            warning: 'text-yellow-800',
            info: 'text-blue-800'
        };

        return colors[type] || colors.info;
    }

    /**
     * Create progress bar
     */
    createProgressBar() {
        return `
            <div class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-1">
                    <div class="progress-bar h-1 rounded-full transition-all duration-100 ease-linear bg-current opacity-30" style="width: 100%"></div>
                </div>
            </div>
        `;
    }

    /**
     * Create close button
     */
    createCloseButton(type) {
        return `
            <div class="ml-4 flex-shrink-0 flex">
                <button type="button" class="inline-flex ${this.getTextColor(type)} hover:opacity-75 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 rounded-md p-1" data-dismiss-notification>
                    <span class="sr-only">Chiudi notifica</span>
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;
    }

    /**
     * Setup notification event listeners
     */
    setupNotificationEventListeners(element, notification) {
        // Close button
        const closeBtn = element.querySelector('[data-dismiss-notification]');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                this.dismiss(notification.id);
            });
        }

        // Pause auto-dismiss on hover
        if (notification.config.duration > 0) {
            element.addEventListener('mouseenter', () => {
                this.pauseAutoDismiss(notification);
            });

            element.addEventListener('mouseleave', () => {
                this.resumeAutoDismiss(notification);
            });
        }

        // Keyboard accessibility
        element.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.dismiss(notification.id);
            }
        });
    }

    /**
     * Add notification to container with animation
     */
    addToContainer(element) {
        // Initial state for animation
        element.style.opacity = '0';
        element.style.transform = this.getInitialTransform();

        this.state.container.appendChild(element);

        // Trigger animation
        requestAnimationFrame(() => {
            element.style.opacity = '1';
            element.style.transform = 'translateX(0) scale(1)';
        });
    }

    /**
     * Get initial transform for entrance animation
     */
    getInitialTransform() {
        const transforms = {
            'top-right': 'translateX(100%) scale(0.95)',
            'top-left': 'translateX(-100%) scale(0.95)',
            'bottom-right': 'translateX(100%) scale(0.95)',
            'bottom-left': 'translateX(-100%) scale(0.95)',
            'top-center': 'translateY(-100%) scale(0.95)',
            'bottom-center': 'translateY(100%) scale(0.95)'
        };

        return transforms[this.options.position] || transforms['top-right'];
    }

    /**
     * Setup auto-dismiss timer
     */
    setupAutoDismiss(notification) {
        const { duration } = notification.config;

        notification.timer = setTimeout(() => {
            this.dismiss(notification.id);
        }, duration);

        // Setup progress bar animation
        if (notification.config.showProgress) {
            this.setupProgressAnimation(notification);
        }
    }

    /**
     * Setup progress bar animation
     */
    setupProgressAnimation(notification) {
        const progressBar = notification.element?.querySelector('.progress-bar');
        if (!progressBar) return;

        // Animate progress bar from 100% to 0%
        progressBar.style.transition = `width ${notification.config.duration}ms linear`;

        requestAnimationFrame(() => {
            progressBar.style.width = '0%';
        });
    }

    /**
     * Pause auto-dismiss
     */
    pauseAutoDismiss(notification) {
        if (notification.timer) {
            clearTimeout(notification.timer);
            notification.timer = null;
        }

        // Pause progress bar
        const progressBar = notification.element?.querySelector('.progress-bar');
        if (progressBar) {
            const computedStyle = window.getComputedStyle(progressBar);
            const currentWidth = computedStyle.width;
            progressBar.style.width = currentWidth;
            progressBar.style.transition = 'none';
        }
    }

    /**
     * Resume auto-dismiss
     */
    resumeAutoDismiss(notification) {
        if (notification.config.duration === 0) return;

        const progressBar = notification.element?.querySelector('.progress-bar');
        let remainingTime = notification.config.duration;

        if (progressBar) {
            const currentWidth = parseFloat(progressBar.style.width) || 0;
            remainingTime = (currentWidth / 100) * notification.config.duration;

            if (remainingTime > 0) {
                progressBar.style.transition = `width ${remainingTime}ms linear`;
                progressBar.style.width = '0%';
            }
        }

        if (remainingTime > 0) {
            notification.timer = setTimeout(() => {
                this.dismiss(notification.id);
            }, remainingTime);
        }
    }

    /**
     * Dismiss notification
     */
    dismiss(id) {
        const notification = this.state.notifications.get(id);
        if (!notification) return;

        // Clear timers
        if (notification.timer) {
            clearTimeout(notification.timer);
        }

        // Animate out
        this.animateOut(notification.element, () => {
            // Remove from DOM
            if (notification.element && notification.element.parentNode) {
                notification.element.parentNode.removeChild(notification.element);
            }

            // Remove from state
            this.state.notifications.delete(id);

            // Show queued notification
            this.showQueuedNotification();

            this.log('Notification dismissed:', id);
        });
    }

    /**
     * Animate notification out
     */
    animateOut(element, callback) {
        if (!element) {
            callback?.();
            return;
        }

        element.style.transition = 'all 300ms ease-in-out';
        element.style.opacity = '0';
        element.style.transform = this.getExitTransform();
        element.style.maxHeight = '0';
        element.style.marginBottom = '0';
        element.style.paddingTop = '0';
        element.style.paddingBottom = '0';

        setTimeout(callback, 300);
    }

    /**
     * Get exit transform for animation
     */
    getExitTransform() {
        const transforms = {
            'top-right': 'translateX(100%)',
            'top-left': 'translateX(-100%)',
            'bottom-right': 'translateX(100%)',
            'bottom-left': 'translateX(-100%)',
            'top-center': 'translateY(-100%)',
            'bottom-center': 'translateY(100%)'
        };

        return transforms[this.options.position] || transforms['top-right'];
    }

    /**
     * Show queued notification
     */
    showQueuedNotification() {
        if (this.queue.length === 0) return;

        const notification = this.queue.shift();
        this.createNotificationElement(notification);
        this.state.notifications.set(notification.id, notification);

        if (notification.config.duration > 0) {
            this.setupAutoDismiss(notification);
        }
    }

    /**
     * Clear all notifications
     */
    clearAll() {
        this.state.notifications.forEach((notification, id) => {
            this.dismiss(id);
        });

        this.queue = [];
        this.log('All notifications cleared');
    }

    /**
     * Play notification sound
     */
    playNotificationSound(type) {
        if (!this.options.enableSounds) return;

        try {
            // Create audio context if needed
            if (!this.audioContext) {
                this.audioContext = new (window.AudioContext || window.webkitAudioContext)();
            }

            // Simple beep sounds for different types
            const frequencies = {
                success: 800,
                error: 300,
                warning: 600,
                info: 500
            };

            const frequency = frequencies[type] || frequencies.info;
            const oscillator = this.audioContext.createOscillator();
            const gainNode = this.audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(this.audioContext.destination);

            oscillator.frequency.value = frequency;
            oscillator.type = 'sine';
            gainNode.gain.value = 0.1;

            oscillator.start();
            oscillator.stop(this.audioContext.currentTime + 0.2);

        } catch (error) {
            this.log('Sound playback failed:', error);
        }
    }

    /**
     * Escape HTML to prevent XSS
     */
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Get notification count
     */
    getCount() {
        return this.state.notifications.size;
    }

    /**
     * Get queued notification count
     */
    getQueuedCount() {
        return this.queue.length;
    }

    /**
     * Update options
     */
    updateOptions(newOptions) {
        this.options = { ...this.options, ...newOptions };

        // Update container position if changed
        if (newOptions.position && this.state.container) {
            this.state.container.className = this.getContainerClasses();
        }
    }

    /**
     * Debug logging
     */
    log(...args) {
        if (typeof window !== 'undefined' && window.console && window.console.log) {
            console.log('[NotificationManager]', ...args);
        }
    }

    /**
     * Cleanup
     */
    destroy() {
        // Clear all notifications
        this.clearAll();

        // Remove container
        if (this.state.container && this.state.container.parentNode) {
            this.state.container.parentNode.removeChild(this.state.container);
        }

        // Close audio context
        if (this.audioContext) {
            this.audioContext.close();
        }

        // Clear references
        this.state.container = null;
        this.state.notifications.clear();
        this.queue = [];

        this.log('NotificationManager destroyed');
    }
}