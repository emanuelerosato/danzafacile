/**
 * 🎯 STAFF MANAGER ENTRY POINT - Laravel + Alpine.js Integration
 *
 * Entry point principale per l'architettura JavaScript Staff.
 * Integra il StaffManager con Alpine.js e sistema di template Laravel.
 *
 * Questo file viene incluso nei template Blade via @vite
 * e sostituisce completamente le ~80 righe di JavaScript inline.
 */

import StaffManager from './StaffManager.js';

// ==========================================
// INICIALIZACIÓN
// ==========================================

/**
 * Inizializza il sistema quando DOM è ready
 */
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing Staff Management System...');

    // Verifica che siamo in una pagina staff
    if (!isStaffPage()) {
        console.log('👋 Not a staff page, skipping initialization');
        return;
    }

    // Inizializza StaffManager
    initializeStaffManager();

    // Setup Alpine.js integration
    setupAlpineIntegration();

    // Setup global utilities
    setupGlobalUtilities();

    console.log('✅ Staff Management System initialized successfully!');
});

/**
 * Verifica se siamo in una pagina staff
 */
function isStaffPage() {
    const staffIndicators = [
        document.querySelector('[data-page="staff"]'),
        document.querySelector('#staff-form'),
        document.querySelector('.staff-table'),
        document.querySelector('.staff-container'),
        window.location.pathname.includes('/staff')
    ];

    return staffIndicators.some(indicator => indicator);
}

/**
 * Inizializza StaffManager principale
 */
function initializeStaffManager() {
    try {
        // Crea istanza globale StaffManager
        window.staffManager = new StaffManager();

        // Registra manager specializzati globalmente per accesso dai template
        if (window.staffManager.selectionManager) {
            window.staffSelectionManager = window.staffManager.selectionManager;
        }

        if (window.staffManager.bulkActionManager) {
            window.staffBulkActionManager = window.staffManager.bulkActionManager;
        }

        if (window.staffManager.filterManager) {
            window.staffFilterManager = window.staffManager.filterManager;
        }

        if (window.staffManager.formManager) {
            window.staffFormManager = window.staffManager.formManager;
        }

        if (window.staffManager.notificationManager) {
            window.staffNotificationManager = window.staffManager.notificationManager;
        }

        console.log('📋 StaffManager and specialized managers initialized');

    } catch (error) {
        console.error('❌ Failed to initialize StaffManager:', error);

        // Fallback per evitare errori critici
        window.staffManager = {
            initialized: false,
            error: error.message
        };

        // Mostra notifica errore se possibile
        if (window.showNotification) {
            window.showNotification('Errore inizializzazione sistema staff', 'error');
        }
    }
}

/**
 * Setup integrazione Alpine.js
 */
function setupAlpineIntegration() {
    // Se Alpine.js è disponibile, registra componenti staff
    if (window.Alpine) {
        console.log('🏔️ Setting up Alpine.js integration...');

        // Staff Data Component
        window.Alpine.data('staffData', () => ({
            // State
            selectedItems: [],
            isLoading: false,
            filters: {
                search: '',
                role: '',
                department: '',
                status: ''
            },

            // Computed
            get hasSelection() {
                return this.selectedItems.length > 0;
            },

            get selectionCount() {
                return this.selectedItems.length;
            },

            // Methods
            init() {
                console.log('🏔️ Alpine staffData component initialized');

                // Sincronizza con StaffManager se disponibile
                if (window.staffManager && window.staffManager.state) {
                    this.syncWithStaffManager();
                }
            },

            // Sync con StaffManager
            syncWithStaffManager() {
                // Ascolta cambiamenti dal StaffManager
                document.addEventListener('staffSelectionChanged', (event) => {
                    this.selectedItems = event.detail.selectedItems;
                });

                document.addEventListener('staffFiltersChanged', (event) => {
                    this.filters = event.detail.filters;
                });

                document.addEventListener('staffLoadingChanged', (event) => {
                    this.isLoading = event.detail.isLoading;
                });
            },

            // Actions
            toggleSelection(staffId) {
                if (window.staffSelectionManager) {
                    const checkbox = document.querySelector(`.staff-checkbox[value="${staffId}"]`);
                    if (checkbox) {
                        checkbox.checked = !checkbox.checked;
                        window.staffSelectionManager.handleIndividualSelection({ target: checkbox });
                    }
                }
            },

            selectAll() {
                if (window.staffSelectionManager) {
                    const selectAllCheckbox = document.getElementById('select-all-staff');
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = true;
                        window.staffSelectionManager.handleSelectAll({ target: selectAllCheckbox });
                    }
                }
            },

            clearSelection() {
                if (window.staffSelectionManager) {
                    window.staffSelectionManager.clearSelection();
                }
            },

            performBulkAction(action) {
                if (window.staffManager) {
                    window.staffManager.performBulkAction(action);
                }
            },

            updateFilter(key, value) {
                if (window.staffFilterManager) {
                    window.staffFilterManager.updateFilter(key, value);
                }
            },

            clearFilters() {
                if (window.staffFilterManager) {
                    window.staffFilterManager.clearAllFilters();
                }
            }
        }));

        // Staff Form Component
        window.Alpine.data('staffForm', () => ({
            // State
            isDirty: false,
            isValidating: false,
            validationErrors: {},
            availabilityDays: [],
            currentRole: '',

            // Methods
            init() {
                console.log('🏔️ Alpine staffForm component initialized');

                if (window.staffFormManager) {
                    this.syncWithFormManager();
                }
            },

            syncWithFormManager() {
                // Sincronizza stato con FormManager
                if (window.staffFormManager.formState) {
                    this.isDirty = window.staffFormManager.formState.isDirty;
                    this.validationErrors = window.staffFormManager.formState.validationErrors;
                    this.availabilityDays = window.staffFormManager.formState.availabilityDays;
                    this.currentRole = window.staffFormManager.formState.currentRole;
                }
            },

            // Actions
            validateField(fieldName) {
                if (window.staffFormManager) {
                    const field = document.querySelector(`[name="${fieldName}"]`);
                    if (field) window.staffFormManager.validateField(field);
                }
            },

            toggleAvailability(day) {
                if (window.staffFormManager) {
                    window.staffFormManager.toggleAvailabilityDay(day);
                }
            },

            saveDraft() {
                if (window.staffFormManager) {
                    window.staffFormManager.saveDraft();
                }
            }
        }));

        console.log('✅ Alpine.js integration setup completed');
    } else {
        console.log('ℹ️ Alpine.js not found, skipping Alpine integration');
    }
}

/**
 * Setup utility globali
 */
function setupGlobalUtilities() {
    // Utility functions per template Blade
    window.StaffUtils = {
        // Format utilities
        formatCurrency(amount) {
            if (window.staffManager && window.staffManager.formatCurrency) {
                return window.staffManager.formatCurrency(amount);
            }
            return new Intl.NumberFormat('it-IT', {
                style: 'currency',
                currency: 'EUR'
            }).format(amount);
        },

        formatDate(dateString) {
            if (window.staffManager && window.staffManager.formatDate) {
                return window.staffManager.formatDate(dateString);
            }
            return new Intl.DateTimeFormat('it-IT', {
                year: 'numeric',
                month: 'short',
                day: 'numeric'
            }).format(new Date(dateString));
        },

        // Status utilities
        getStatusColor(status) {
            const colors = {
                active: 'green',
                inactive: 'red',
                pending: 'yellow',
                suspended: 'gray'
            };
            return colors[status] || 'gray';
        },

        getStatusLabel(status) {
            const labels = {
                active: 'Attivo',
                inactive: 'Inattivo',
                pending: 'In Attesa',
                suspended: 'Sospeso'
            };
            return labels[status] || status;
        },

        // Role utilities
        getRoleLabel(role) {
            const labels = {
                teacher: 'Insegnante',
                admin: 'Amministratore',
                receptionist: 'Receptionist',
                maintenance: 'Manutenzione',
                manager: 'Manager'
            };
            return labels[role] || role;
        },

        getRoleIcon(role) {
            const icons = {
                teacher: '👨‍🏫',
                admin: '👨‍💼',
                receptionist: '👩‍💻',
                maintenance: '🔧',
                manager: '👔'
            };
            return icons[role] || '👤';
        },

        // Notification shortcuts
        showSuccess(message) {
            if (window.staffNotificationManager) {
                window.staffNotificationManager.showSuccess(message);
            } else if (window.showNotification) {
                window.showNotification(message, 'success');
            } else {
                console.log('✅ Success:', message);
            }
        },

        showError(message) {
            if (window.staffNotificationManager) {
                window.staffNotificationManager.showError(message);
            } else if (window.showNotification) {
                window.showNotification(message, 'error');
            } else {
                console.error('❌ Error:', message);
            }
        },

        showWarning(message) {
            if (window.staffNotificationManager) {
                window.staffNotificationManager.showWarning(message);
            } else if (window.showNotification) {
                window.showNotification(message, 'warning');
            } else {
                console.warn('⚠️ Warning:', message);
            }
        },

        // Confirmation utility
        async confirm(title, message, confirmText = 'Conferma', cancelText = 'Annulla') {
            if (window.staffNotificationManager) {
                return await window.staffNotificationManager.showConfirmation(
                    title, message, confirmText, cancelText
                );
            } else {
                return confirm(`${title}\n\n${message}`);
            }
        },

        // Loading utility
        setLoading(isLoading) {
            if (window.staffManager) {
                window.staffManager.setLoading(isLoading);
            }
        }
    };

    // Shortcut functions per compatibilità con template
    window.showStaffSuccess = window.StaffUtils.showSuccess;
    window.showStaffError = window.StaffUtils.showError;
    window.showStaffWarning = window.StaffUtils.showWarning;
    window.confirmStaffAction = window.StaffUtils.confirm;

    console.log('🛠️ Global utilities setup completed');
}

// ==========================================
// ERROR HANDLING & DEBUGGING
// ==========================================

/**
 * Global error handler per staff system
 */
window.addEventListener('error', function(event) {
    if (event.filename && event.filename.includes('staff')) {
        console.error('🚨 Staff System Error:', {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            error: event.error
        });

        // Mostra notifica errore se il sistema è inizializzato
        if (window.staffNotificationManager) {
            window.staffNotificationManager.showError(
                'Si è verificato un errore nel sistema staff. Ricarica la pagina se necessario.'
            );
        }
    }
});

/**
 * Debug utilities per development
 */
if (import.meta.env?.DEV || window.location.hostname === 'localhost') {
    window.StaffDebug = {
        // System info
        getSystemInfo() {
            return {
                initialized: !!window.staffManager,
                managers: {
                    selection: !!window.staffSelectionManager,
                    bulkAction: !!window.staffBulkActionManager,
                    filter: !!window.staffFilterManager,
                    form: !!window.staffFormManager,
                    notification: !!window.staffNotificationManager
                },
                alpine: !!window.Alpine,
                currentPage: window.location.pathname,
                timestamp: new Date().toISOString()
            };
        },

        // Test notifications
        testNotifications() {
            if (window.staffNotificationManager) {
                console.log('🧪 Running notification tests...');
                window.staffNotificationManager.runTest();
            } else {
                console.warn('NotificationManager not available');
            }
        },

        // Test selection
        testSelection() {
            if (window.staffSelectionManager) {
                console.log('🧪 Testing selection system...');

                // Select first 3 items
                const checkboxes = document.querySelectorAll('.staff-checkbox');
                if (checkboxes.length >= 3) {
                    for (let i = 0; i < 3; i++) {
                        checkboxes[i].checked = true;
                        window.staffSelectionManager.handleIndividualSelection({ target: checkboxes[i] });
                    }

                    setTimeout(() => {
                        window.staffSelectionManager.clearSelection();
                    }, 3000);
                }
            } else {
                console.warn('SelectionManager not available');
            }
        },

        // Clear all data
        clearAllData() {
            // Clear localStorage
            Object.keys(localStorage).forEach(key => {
                if (key.includes('staff')) {
                    localStorage.removeItem(key);
                }
            });

            // Clear sessionStorage
            Object.keys(sessionStorage).forEach(key => {
                if (key.includes('staff')) {
                    sessionStorage.removeItem(key);
                }
            });

            console.log('🗑️ All staff data cleared');
        },

        // Performance stats
        getPerformanceStats() {
            const performance = window.performance;
            const navigation = performance.getEntriesByType('navigation')[0];

            return {
                domLoaded: navigation.domContentLoadedEventEnd - navigation.domContentLoadedEventStart,
                pageLoad: navigation.loadEventEnd - navigation.loadEventStart,
                jsHeapSize: performance.memory ? {
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    limit: performance.memory.jsHeapSizeLimit
                } : 'Not available',
                staffSystemLoad: window.staffManager ?
                    (window.staffManager.initTime || 'Unknown') : 'Not initialized'
            };
        }
    };

    console.log('🐛 Debug utilities loaded. Use window.StaffDebug for debugging.');
}

// ==========================================
// MODULE EXPORTS (per uso in altri file)
// ==========================================

// Se il sistema di moduli ES6 è supportato
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        StaffManager,
        initializeStaffManager,
        setupAlpineIntegration,
        setupGlobalUtilities
    };
}

// AMD support
if (typeof define === 'function' && define.amd) {
    define(['StaffManager'], function(StaffManager) {
        return {
            StaffManager,
            initializeStaffManager,
            setupAlpineIntegration,
            setupGlobalUtilities
        };
    });
}

console.log('📦 Staff Manager Entry Point loaded successfully');

// ==========================================
// FINAL INITIALIZATION CONFIRMATION
// ==========================================

// Conferma che tutto è caricato correttamente
window.addEventListener('load', function() {
    if (window.staffManager && window.staffManager.initialized !== false) {
        console.log('🎉 Staff Management System fully loaded and operational!');

        // Trigger custom event per altri script
        document.dispatchEvent(new CustomEvent('staffSystemReady', {
            detail: {
                timestamp: Date.now(),
                managers: {
                    selection: !!window.staffSelectionManager,
                    bulkAction: !!window.staffBulkActionManager,
                    filter: !!window.staffFilterManager,
                    form: !!window.staffFormManager,
                    notification: !!window.staffNotificationManager
                }
            }
        }));
    } else {
        console.warn('⚠️ Staff Management System loaded with issues');
    }
});