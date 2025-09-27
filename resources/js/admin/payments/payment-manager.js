/**
 * payment-manager.js
 *
 * Entry point for the Payments management system
 * Initializes PaymentManager with all modules and Alpine.js integration
 *
 * @version 1.0.0
 */

import { PaymentManager } from './PaymentManager.js';

// Global variables for configuration
let paymentManagerInstance = null;

// Declare paymentManager function early to prevent Alpine.js timing issues
window.paymentManager = function() {
    console.log('[Alpine] Early paymentManager called, delegating to main function');
    return createPaymentManager();
};

/**
 * Initialize the payment system
 */
function initializePaymentSystem() {
    console.log('[PaymentSystem] 🚀 Initializing Payment Management System v1.0');

    try {
        // Create PaymentManager instance
        paymentManagerInstance = new PaymentManager({
            debug: window.APP_DEBUG || false,
            csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
            routes: window.paymentRoutes || {}
        });

        console.log('[PaymentSystem] ✅ Payment system initialized successfully');

        // Make available globally for debugging
        if (window.APP_DEBUG) {
            window.paymentManagerDebug = paymentManagerInstance;
        }

    } catch (error) {
        console.error('[PaymentSystem] ❌ Failed to initialize payment system:', error);

        // Fallback error handling
        window.paymentSystemError = error;
    }
}

/**
 * Alpine.js integration
 * Provides reactive data and methods for the payment templates
 */
function createPaymentManager() {
    // Wait for PaymentManager to be initialized
    if (!paymentManagerInstance) {
        console.warn('[Alpine] PaymentManager not ready, waiting for initialization...');

        // Return a proxy that will update once the manager is ready
        const alpineData = {
            selectedPayments: [],
            isLoading: false,
            _initialized: false,

            openBulkModal() {
                if (paymentManagerInstance) {
                    paymentManagerInstance.bulkActionManager?.openBulkModal();
                } else {
                    console.log('[Alpine] Bulk modal requested (waiting for manager)');
                }
            },

            exportPayments() {
                if (paymentManagerInstance) {
                    paymentManagerInstance.exportManager?.exportPayments();
                } else {
                    console.log('[Alpine] Export requested (fallback)');
                    // Fallback export using current URL
                    const currentUrl = new URL(window.location.href);
                    currentUrl.pathname = currentUrl.pathname.replace('/index', '/export');
                    window.location.href = currentUrl.toString();
                }
            },

            // Auto-update when manager becomes available
            init() {
                console.log('[Alpine] PaymentManager component initialized');

                // Check periodically for manager availability
                const checkManager = () => {
                    if (paymentManagerInstance && !this._initialized) {
                        console.log('[Alpine] PaymentManager now available, updating data');
                        this._initialized = true;

                        // Update with real data
                        Object.assign(this, paymentManagerInstance.alpinePaymentManager());
                    } else if (!paymentManagerInstance) {
                        // Keep checking
                        setTimeout(checkManager, 50);
                    }
                };

                checkManager();
            }
        };

        return alpineData;
    }

    // Return PaymentManager Alpine integration
    return paymentManagerInstance.alpinePaymentManager();
}

/**
 * Global utility functions for backward compatibility
 */
function setupGlobalUtilities() {
    // Error tracker for debugging
    window.paymentSystemInfo = {
        version: '1.0.0',
        initialized: !!paymentManagerInstance,
        timestamp: Date.now()
    };

    // Global error handler
    window.addEventListener('error', (event) => {
        if (event.filename && event.filename.includes('payment')) {
            console.error('[PaymentSystem] JavaScript Error:', event.error);
        }
    });

    // Export utilities for template usage
    window.paymentUtils = {
        formatCurrency(amount) {
            return new Intl.NumberFormat('it-IT', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('it-IT');
        },

        debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
    };
}

/**
 * Document ready handler
 */
document.addEventListener('DOMContentLoaded', () => {
    console.log('[PaymentSystem] 📱 DOM ready, starting initialization...');

    setupGlobalUtilities();
    initializePaymentSystem();

    // Verify initialization
    setTimeout(() => {
        if (paymentManagerInstance) {
            console.log('[PaymentSystem] 🎉 All systems operational');
        } else {
            console.warn('[PaymentSystem] ⚠️ System not fully initialized');
        }
    }, 100);
});

/**
 * Page visibility change handler
 */
document.addEventListener('visibilitychange', () => {
    if (!document.hidden && paymentManagerInstance) {
        console.log('[PaymentSystem] 👁️ Page visible, checking system state...');

        // Refresh stats if system is active
        if (paymentManagerInstance.statsManager) {
            paymentManagerInstance.statsManager.refreshStats();
        }
    }
});

/**
 * Beforeunload handler for cleanup
 */
window.addEventListener('beforeunload', () => {
    if (paymentManagerInstance) {
        console.log('[PaymentSystem] 🧹 Cleaning up before page unload...');

        // Stop real-time updates
        if (paymentManagerInstance.statsManager) {
            paymentManagerInstance.statsManager.stopRealTimeUpdates();
        }

        // Clear receipt cache
        if (paymentManagerInstance.receiptManager) {
            paymentManagerInstance.receiptManager.clearCache();
        }
    }
});

/**
 * Global functions for template usage
 * These maintain backward compatibility with existing onclick handlers
 */

// Mark payment as completed
window.markCompleted = function(paymentId) {
    if (paymentManagerInstance) {
        return paymentManagerInstance.markCompleted(paymentId);
    } else {
        console.error('[PaymentSystem] PaymentManager not available');
        return Promise.reject(new Error('PaymentManager not initialized'));
    }
};

// Process refund
window.processRefund = function(paymentId) {
    if (paymentManagerInstance) {
        return paymentManagerInstance.processRefund(paymentId);
    } else {
        console.error('[PaymentSystem] PaymentManager not available');
        return Promise.reject(new Error('PaymentManager not initialized'));
    }
};

// Send receipt
window.sendReceipt = function(paymentId) {
    if (paymentManagerInstance && paymentManagerInstance.receiptManager) {
        return paymentManagerInstance.receiptManager.sendReceipt(paymentId);
    } else {
        console.error('[PaymentSystem] ReceiptManager not available');
        return Promise.reject(new Error('ReceiptManager not initialized'));
    }
};

// Delete payment
window.deletePayment = function(paymentId) {
    if (paymentManagerInstance) {
        return paymentManagerInstance.deletePayment(paymentId);
    } else {
        console.error('[PaymentSystem] PaymentManager not available');
        return Promise.reject(new Error('PaymentManager not initialized'));
    }
};

// Toggle dropdowns
window.toggleBulkDropdown = function() {
    if (paymentManagerInstance) {
        return paymentManagerInstance.toggleDropdown('bulkDropdown');
    } else {
        console.error('[PaymentSystem] PaymentManager not available');
    }
};

window.togglePaymentDropdown = function(paymentId) {
    if (paymentManagerInstance) {
        return paymentManagerInstance.toggleDropdown(`paymentDropdown${paymentId}`);
    } else {
        console.error('[PaymentSystem] PaymentManager not available');
    }
};

// Close active dropdown
window.closeActiveDropdown = function() {
    if (paymentManagerInstance) {
        return paymentManagerInstance.closeActiveDropdown();
    }
};

/**
 * Export Alpine.js function - Already defined early for timing issues
 */
// window.paymentManager already defined at the top

/**
 * Export main manager for external access
 */
window.getPaymentManager = function() {
    return paymentManagerInstance;
};

/**
 * Development helpers
 */
if (window.APP_DEBUG) {
    window.paymentSystemDebug = {
        getInstance: () => paymentManagerInstance,
        getInfo: () => paymentManagerInstance?.getDebugInfo(),
        restart: () => {
            console.log('[PaymentSystem] 🔄 Restarting system...');
            if (paymentManagerInstance) {
                // Cleanup existing instance
                Object.keys(paymentManagerInstance).forEach(key => {
                    if (paymentManagerInstance[key] && typeof paymentManagerInstance[key].destroy === 'function') {
                        paymentManagerInstance[key].destroy();
                    }
                });
            }
            initializePaymentSystem();
        }
    };

    console.log('[PaymentSystem] 🔧 Debug utilities available at window.paymentSystemDebug');
}

console.log('[PaymentSystem] 📋 Entry point loaded successfully');