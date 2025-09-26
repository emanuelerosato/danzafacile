/**
 * Staff Schedules - Main entry point for staff schedule management
 *
 * This is the main entry point that initializes and coordinates all
 * staff schedule functionality across different pages.
 *
 * Handles:
 * - Module initialization based on current page
 * - Global event coordination
 * - Cross-module communication
 * - Error handling and debugging
 */

import { StaffScheduleManager } from './staff-schedules/StaffScheduleManager.js';

// Global instance for access from templates
window.staffScheduleManager = null;

/**
 * Initialize staff schedule functionality
 */
function initializeStaffSchedules(options = {}) {
    // Default configuration
    const defaultOptions = {
        debug: import.meta.env.DEV || false,
        autoRefresh: true,
        refreshInterval: 30000,
        ...options
    };

    try {
        console.log('[StaffSchedules] Initializing with options:', defaultOptions);

        // Create manager instance
        window.staffScheduleManager = new StaffScheduleManager(defaultOptions);

        // Setup global error handling
        setupGlobalErrorHandling();

        // Setup page-specific enhancements
        setupPageEnhancements();

        console.log('[StaffSchedules] Initialization completed successfully');

    } catch (error) {
        console.error('[StaffSchedules] Initialization failed:', error);

        // Show user-friendly error message
        if (window.alert) {
            alert('Si è verificato un errore nell\'inizializzazione del sistema. Ricarica la pagina per riprovare.');
        }
    }
}

/**
 * Setup global error handling for the staff schedules system
 */
function setupGlobalErrorHandling() {
    // Catch and handle unhandled Promise rejections
    window.addEventListener('unhandledrejection', (event) => {
        console.error('[StaffSchedules] Unhandled promise rejection:', event.reason);

        if (window.staffScheduleManager?.modules?.notification) {
            window.staffScheduleManager.modules.notification.error(
                'Si è verificato un errore imprevisto. Riprova o ricarica la pagina.'
            );
        }

        // Prevent the default browser behavior
        event.preventDefault();
    });

    // Catch JavaScript errors
    window.addEventListener('error', (event) => {
        console.error('[StaffSchedules] JavaScript error:', event.error);

        if (window.staffScheduleManager?.modules?.notification) {
            window.staffScheduleManager.modules.notification.error(
                'Si è verificato un errore. Controlla la console per i dettagli.'
            );
        }
    });
}

/**
 * Setup page-specific enhancements
 */
function setupPageEnhancements() {
    // Add any page-specific functionality that doesn't belong in modules

    // Example: Setup keyboard shortcuts that work across all pages
    document.addEventListener('keydown', (event) => {
        // Ctrl/Cmd + K: Quick search (if on index page)
        if ((event.ctrlKey || event.metaKey) && event.key === 'k') {
            const searchInput = document.querySelector('#filter-search');
            if (searchInput) {
                event.preventDefault();
                searchInput.focus();
            }
        }

        // Ctrl/Cmd + N: New schedule (if on index page)
        if ((event.ctrlKey || event.metaKey) && event.key === 'n') {
            const createButton = document.querySelector('[href*="/staff-schedules/create"]');
            if (createButton) {
                event.preventDefault();
                window.location.href = createButton.href;
            }
        }
    });

    // Setup responsive enhancements
    setupResponsiveEnhancements();

    // Setup accessibility enhancements
    setupAccessibilityEnhancements();
}

/**
 * Setup responsive design enhancements
 */
function setupResponsiveEnhancements() {
    // Handle mobile menu toggles for filter panels
    const toggleButtons = document.querySelectorAll('[data-mobile-toggle]');

    toggleButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetId = button.dataset.mobileToggle;
            const target = document.getElementById(targetId);

            if (target) {
                target.classList.toggle('hidden');

                // Update button text/icon
                const isHidden = target.classList.contains('hidden');
                button.textContent = isHidden ? 'Mostra filtri' : 'Nascondi filtri';
            }
        });
    });

    // Handle window resize for calendar views
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.staffScheduleManager?.modules?.calendar) {
                window.staffScheduleManager.modules.calendar.render();
            }
        }, 250);
    });
}

/**
 * Setup accessibility enhancements
 */
function setupAccessibilityEnhancements() {
    // Enhance focus management for modals and dropdowns
    document.addEventListener('keydown', (event) => {
        // Escape key handling for modals
        if (event.key === 'Escape') {
            // Close any open dropdowns
            const openDropdowns = document.querySelectorAll('.dropdown-menu:not(.hidden)');
            openDropdowns.forEach(dropdown => {
                dropdown.classList.add('hidden');
            });

            // Close any open modals
            const openModals = document.querySelectorAll('.modal:not(.hidden)');
            openModals.forEach(modal => {
                modal.classList.add('hidden');
            });
        }
    });

    // Improve focus indicators for keyboard navigation
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });

    document.addEventListener('mousedown', () => {
        document.body.classList.remove('keyboard-navigation');
    });

    // Add CSS for keyboard navigation if not present
    if (!document.querySelector('#keyboard-navigation-styles')) {
        const style = document.createElement('style');
        style.id = 'keyboard-navigation-styles';
        style.textContent = `
            .keyboard-navigation *:focus {
                outline: 2px solid #f43f5e !important;
                outline-offset: 2px !important;
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Utility function to get current page context
 */
function getCurrentPageContext() {
    const path = window.location.pathname;
    const segments = path.split('/').filter(Boolean);

    return {
        isAdminPanel: segments.includes('admin'),
        isStaffSchedules: segments.includes('staff-schedules'),
        page: getStaffSchedulePage(segments),
        scheduleId: getScheduleIdFromPath(segments)
    };
}

/**
 * Get current staff schedule page type
 */
function getStaffSchedulePage(pathSegments) {
    const staffScheduleIndex = pathSegments.findIndex(segment => segment === 'staff-schedules');

    if (staffScheduleIndex === -1) return null;

    const nextSegment = pathSegments[staffScheduleIndex + 1];

    if (!nextSegment) return 'index';
    if (nextSegment === 'create') return 'create';
    if (nextSegment === 'edit') return 'edit';
    if (/^\d+$/.test(nextSegment)) {
        const afterId = pathSegments[staffScheduleIndex + 2];
        return afterId === 'edit' ? 'edit' : 'show';
    }

    return 'index';
}

/**
 * Get schedule ID from URL path
 */
function getScheduleIdFromPath(pathSegments) {
    const staffScheduleIndex = pathSegments.findIndex(segment => segment === 'staff-schedules');

    if (staffScheduleIndex === -1) return null;

    const nextSegment = pathSegments[staffScheduleIndex + 1];

    if (nextSegment && /^\d+$/.test(nextSegment)) {
        return parseInt(nextSegment, 10);
    }

    return null;
}

/**
 * Expose utilities for debugging and testing
 */
function exposeDebuggingUtilities() {
    if (import.meta.env.DEV) {
        window.staffScheduleDebug = {
            getManager: () => window.staffScheduleManager,
            getCurrentPage: getCurrentPageContext,
            reinitialize: (options) => {
                if (window.staffScheduleManager) {
                    window.staffScheduleManager.destroy();
                }
                initializeStaffSchedules(options);
            },
            testNotifications: () => {
                const notification = window.staffScheduleManager?.modules?.notification;
                if (notification) {
                    notification.success('Test success notification');
                    setTimeout(() => notification.warning('Test warning notification'), 1000);
                    setTimeout(() => notification.error('Test error notification'), 2000);
                    setTimeout(() => notification.info('Test info notification'), 3000);
                }
            }
        };

        console.log('[StaffSchedules] Debug utilities available at window.staffScheduleDebug');
    }
}

/**
 * Initialize when DOM is ready
 */
function initializeWhenReady() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initializeStaffSchedules();
            exposeDebuggingUtilities();
        });
    } else {
        // DOM is already ready
        initializeStaffSchedules();
        exposeDebuggingUtilities();
    }
}

/**
 * Public API for manual initialization (if needed)
 */
window.initializeStaffSchedules = initializeStaffSchedules;

/**
 * Auto-initialize if we're on a staff schedules page
 */
const pageContext = getCurrentPageContext();
if (pageContext.isStaffSchedules) {
    console.log('[StaffSchedules] Auto-initializing for staff schedules page:', pageContext.page);
    initializeWhenReady();
}

/**
 * Export for module usage
 */
export {
    initializeStaffSchedules,
    getCurrentPageContext,
    setupGlobalErrorHandling,
    setupPageEnhancements
};