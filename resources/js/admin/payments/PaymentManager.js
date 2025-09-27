/**
 * PaymentManager.js
 *
 * Main orchestrator for the payments system
 * Coordinates all payment-related functionality and module communication
 *
 * @version 1.0.0
 */

import { BulkActionManager } from './modules/BulkActionManager.js';
import { ReceiptManager } from './modules/ReceiptManager.js';
import { FilterManager } from './modules/FilterManager.js';
import { StatsManager } from './modules/StatsManager.js';
import { ExportManager } from './modules/ExportManager.js';

export class PaymentManager {
    constructor(options = {}) {
        this.options = {
            csrfToken: null,
            routes: {},
            debug: false,
            ...options
        };

        // Module instances
        this.bulkActionManager = null;
        this.receiptManager = null;
        this.filterManager = null;
        this.statsManager = null;
        this.exportManager = null;

        // State management
        this.state = {
            selectedPayments: [],
            currentFilters: {},
            isLoading: false,
            stats: {}
        };

        // Dropdown management
        this.activeDropdown = null;
        this.dropdownClickListener = null;

        // DOM elements cache
        this.elements = {};

        this.init();
    }

    /**
     * Initialize the PaymentManager
     */
    init() {
        console.log('[PaymentManager] 🚀 Initializing Payment Manager v1.0');

        this.extractConfiguration();
        this.cacheElements();
        this.initializeModules();
        this.attachEventListeners();
        this.makeGloballyAvailable();

        console.log('[PaymentManager] ✅ Payment Manager initialized successfully');
    }

    /**
     * Extract configuration from DOM and global variables
     */
    extractConfiguration() {
        // Extract CSRF token
        const csrfMeta = document.querySelector('meta[name="csrf-token"]');
        if (csrfMeta) {
            this.options.csrfToken = csrfMeta.getAttribute('content');
        }

        // Extract routes from window if available
        if (window.paymentRoutes) {
            this.options.routes = { ...this.options.routes, ...window.paymentRoutes };
        }

        console.log('[PaymentManager] 📋 Configuration extracted');
    }

    /**
     * Cache important DOM elements
     */
    cacheElements() {
        this.elements = {
            selectAllCheckbox: document.getElementById('selectAll'),
            paymentCheckboxes: document.querySelectorAll('.payment-checkbox'),
            bulkActionBtn: document.getElementById('bulkActionBtn'),
            bulkDropdown: document.getElementById('bulkDropdown'),
            filtersForm: document.getElementById('filtersForm'),
            paymentsTable: document.querySelector('[data-payments-table]'),
            statsCards: document.querySelectorAll('[data-stats-card]')
        };

        console.log('[PaymentManager] 🎯 DOM elements cached');
    }

    /**
     * Initialize all sub-modules
     */
    initializeModules() {
        try {
            // Initialize BulkActionManager
            this.bulkActionManager = new BulkActionManager({
                csrfToken: this.options.csrfToken,
                routes: this.options.routes,
                onStateChange: (state) => this.handleBulkStateChange(state)
            });

            // Initialize ReceiptManager
            this.receiptManager = new ReceiptManager({
                csrfToken: this.options.csrfToken,
                routes: this.options.routes,
                onReceiptSent: (data) => this.handleReceiptSent(data)
            });

            // Initialize FilterManager
            this.filterManager = new FilterManager({
                form: this.elements.filtersForm,
                onFilterChange: (filters) => this.handleFilterChange(filters)
            });

            // Initialize StatsManager
            this.statsManager = new StatsManager({
                statsCards: this.elements.statsCards,
                onStatsUpdate: (stats) => this.handleStatsUpdate(stats)
            });

            // Initialize ExportManager
            this.exportManager = new ExportManager({
                routes: this.options.routes,
                onExportStart: () => this.handleExportStart(),
                onExportComplete: () => this.handleExportComplete()
            });

            console.log('[PaymentManager] 🔧 All modules initialized');
        } catch (error) {
            console.error('[PaymentManager] ❌ Error initializing modules:', error);
        }
    }

    /**
     * Attach global event listeners
     */
    attachEventListeners() {
        // Selection management
        if (this.elements.selectAllCheckbox) {
            this.elements.selectAllCheckbox.addEventListener('change', (e) => {
                this.handleSelectAll(e.target.checked);
            });
        }

        // Individual payment checkboxes
        this.elements.paymentCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.updateSelectedPayments();
            });
        });

        // Dropdown management
        document.addEventListener('click', (e) => {
            this.handleDocumentClick(e);
        });

        // Payment action buttons
        this.attachPaymentActionListeners();

        console.log('[PaymentManager] 🎧 Event listeners attached');
    }

    /**
     * Attach listeners for individual payment actions
     */
    attachPaymentActionListeners() {
        // Mark completed buttons
        document.querySelectorAll('[data-action="mark-completed"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const paymentId = btn.dataset.paymentId;
                this.markCompleted(paymentId);
            });
        });

        // Refund buttons
        document.querySelectorAll('[data-action="refund"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const paymentId = btn.dataset.paymentId;
                this.processRefund(paymentId);
            });
        });

        // Send receipt buttons
        document.querySelectorAll('[data-action="send-receipt"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const paymentId = btn.dataset.paymentId;
                this.receiptManager.sendReceipt(paymentId);
            });
        });

        // Delete buttons
        document.querySelectorAll('[data-action="delete"]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const paymentId = btn.dataset.paymentId;
                this.deletePayment(paymentId);
            });
        });

        // Dropdown toggles
        document.querySelectorAll('[data-dropdown-toggle]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const dropdownId = btn.dataset.dropdownToggle;
                this.toggleDropdown(dropdownId);
            });
        });
    }

    /**
     * Handle select all functionality
     */
    handleSelectAll(checked) {
        this.elements.paymentCheckboxes.forEach(checkbox => {
            checkbox.checked = checked;
        });
        this.updateSelectedPayments();
    }

    /**
     * Update selected payments and bulk action button state
     */
    updateSelectedPayments() {
        const checkedBoxes = Array.from(this.elements.paymentCheckboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        this.state.selectedPayments = checkedBoxes;

        // Update bulk action button
        if (this.elements.bulkActionBtn) {
            this.elements.bulkActionBtn.disabled = checkedBoxes.length === 0;
        }

        // Update select all checkbox state
        if (this.elements.selectAllCheckbox) {
            const totalCheckboxes = this.elements.paymentCheckboxes.length;
            const checkedCount = checkedBoxes.length;

            this.elements.selectAllCheckbox.checked = checkedCount === totalCheckboxes;
            this.elements.selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < totalCheckboxes;
        }

        // Notify bulk action manager
        if (this.bulkActionManager) {
            this.bulkActionManager.updateSelection(checkedBoxes);
        }
    }

    /**
     * Mark payment as completed
     */
    async markCompleted(paymentId) {
        if (!confirm('Sei sicuro di voler segnare questo pagamento come completato?')) {
            return;
        }

        this.setLoading(true);

        try {
            const response = await fetch(`/admin/payments/${paymentId}/mark-completed`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Pagamento completato con successo!', 'success');
                this.reloadPage();
            } else {
                this.showNotification('Errore: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('[PaymentManager] Error marking payment as completed:', error);
            this.showNotification('Si è verificato un errore', 'error');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Process payment refund using modal
     */
    async processRefund(paymentId) {
        // Open the refund modal
        this.openRefundModal(paymentId);
    }

    /**
     * Open refund modal
     */
    openRefundModal(paymentId) {
        console.log('[PaymentManager] Opening refund modal for payment:', paymentId);

        const modal = document.getElementById('refundModal');
        const form = document.getElementById('refundForm');

        console.log('[PaymentManager] Modal found:', !!modal);
        console.log('[PaymentManager] Form found:', !!form);

        if (!modal || !form) {
            console.error('[PaymentManager] Refund modal not found - modal:', !!modal, 'form:', !!form);
            alert('Modal di rimborso non trovato. Ricarica la pagina e riprova.');
            return;
        }

        // Set payment ID in form
        form.dataset.paymentId = paymentId;

        // Clear previous content
        const textarea = document.getElementById('refund_reason');
        if (textarea) {
            textarea.value = '';
        }

        // Open modal
        modal.dispatchEvent(new CustomEvent('open-modal'));

        // Initialize validation
        setTimeout(() => {
            if (window.FormValidator) {
                window.FormValidator.init('#refundForm');
            }
        }, 100);

        // Set up form submission (remove previous handlers)
        form.onsubmit = (e) => this.handleRefundSubmit(e, paymentId);
    }

    /**
     * Handle refund form submission
     */
    async handleRefundSubmit(e, paymentId) {
        e.preventDefault();
        console.log('[PaymentManager] Handling refund submit for payment:', paymentId);

        const reasonField = document.getElementById('refund_reason');
        const reason = reasonField?.value?.trim();

        if (!reason) {
            this.showNotification('Inserisci il motivo del rimborso', 'error');
            return;
        }

        if (reason.length < 10) {
            this.showNotification('Il motivo deve essere di almeno 10 caratteri', 'error');
            return;
        }

        console.log('[PaymentManager] Sending refund request...');

        this.setLoading(true);

        try {
            const response = await fetch(`/admin/payments/${paymentId}/refund`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken
                },
                body: JSON.stringify({
                    refund_reason: reason
                })
            });

            const data = await response.json();

            if (data.success) {
                // Close modal
                document.getElementById('refundModal').dispatchEvent(new CustomEvent('close-modal'));

                // Reset form
                if (reasonField) reasonField.value = '';

                this.showNotification('Rimborso elaborato con successo!', 'success');
                this.reloadPage();
            } else {
                this.showNotification('Errore: ' + data.message, 'error');
            }

        } catch (error) {
            console.error('[PaymentManager] Refund error:', error);
            this.showNotification('Si è verificato un errore', 'error');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Delete payment
     */
    async deletePayment(paymentId) {
        if (!confirm('Sei sicuro di voler eliminare questo pagamento? Questa azione non può essere annullata.')) {
            return;
        }

        this.setLoading(true);

        try {
            const response = await fetch(`/admin/payments/${paymentId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Pagamento eliminato con successo!', 'success');
                this.reloadPage();
            } else {
                this.showNotification('Errore: ' + data.message, 'error');
            }
        } catch (error) {
            console.error('[PaymentManager] Error deleting payment:', error);
            this.showNotification('Si è verificato un errore', 'error');
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Toggle dropdown visibility
     */
    toggleDropdown(dropdownId) {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) return;

        // Close any existing dropdown
        this.closeActiveDropdown();

        // Close all other payment dropdowns if this is a payment dropdown
        if (dropdownId.startsWith('paymentDropdown')) {
            document.querySelectorAll('[id^="paymentDropdown"]').forEach(d => {
                if (d.id !== dropdownId) {
                    d.classList.add('hidden');
                }
            });
        }

        dropdown.classList.toggle('hidden');

        if (!dropdown.classList.contains('hidden')) {
            this.setActiveDropdown(dropdownId);
        }
    }

    /**
     * Set active dropdown and add click listener
     */
    setActiveDropdown(dropdownId) {
        this.activeDropdown = dropdownId;

        // Remove any existing listener
        if (this.dropdownClickListener) {
            document.removeEventListener('click', this.dropdownClickListener);
        }

        // Add new listener
        this.dropdownClickListener = (e) => {
            const dropdown = document.getElementById(this.activeDropdown);
            const isClickInsideDropdown = e.target.closest(`#${this.activeDropdown}`);
            const isClickOnTrigger = e.target.closest(`[data-dropdown-toggle="${this.activeDropdown}"]`);

            if (!isClickInsideDropdown && !isClickOnTrigger) {
                this.closeActiveDropdown();
            }
        };

        document.addEventListener('click', this.dropdownClickListener);
    }

    /**
     * Close active dropdown
     */
    closeActiveDropdown() {
        if (this.activeDropdown) {
            const dropdown = document.getElementById(this.activeDropdown);
            if (dropdown) {
                dropdown.classList.add('hidden');
            }

            if (this.dropdownClickListener) {
                document.removeEventListener('click', this.dropdownClickListener);
                this.dropdownClickListener = null;
            }

            this.activeDropdown = null;
        }
    }

    /**
     * Handle document click for dropdown management
     */
    handleDocumentClick(e) {
        // This is handled by individual dropdown listeners
        // Keep this method for potential future global click handling
    }

    /**
     * Module event handlers
     */
    handleBulkStateChange(state) {
        console.log('[PaymentManager] Bulk state changed:', state);
    }

    handleReceiptSent(data) {
        this.showNotification('Ricevuta inviata con successo!', 'success');
    }

    handleFilterChange(filters) {
        this.state.currentFilters = filters;
        console.log('[PaymentManager] Filters changed:', filters);
    }

    handleStatsUpdate(stats) {
        this.state.stats = stats;
        console.log('[PaymentManager] Stats updated:', stats);
    }

    handleExportStart() {
        this.showNotification('Esportazione in corso...', 'info');
    }

    handleExportComplete() {
        this.showNotification('Esportazione completata!', 'success');
    }

    /**
     * Utility methods
     */
    setLoading(loading) {
        this.state.isLoading = loading;

        // Update UI to show loading state
        if (loading) {
            document.body.style.cursor = 'wait';
        } else {
            document.body.style.cursor = 'default';
        }
    }

    showNotification(message, type = 'info') {
        // For now, use alert - can be enhanced with a proper notification system
        if (type === 'error') {
            alert('Errore: ' + message);
        } else {
            alert(message);
        }
    }

    reloadPage() {
        location.reload();
    }

    /**
     * Make globally available for backward compatibility
     */
    makeGloballyAvailable() {
        // Export individual functions for global access
        window.markCompleted = (paymentId) => this.markCompleted(paymentId);
        window.processRefund = (paymentId) => this.processRefund(paymentId);
        window.sendReceipt = (paymentId) => this.receiptManager?.sendReceipt(paymentId);
        window.deletePayment = (paymentId) => this.deletePayment(paymentId);
        window.toggleBulkDropdown = () => this.toggleDropdown('bulkDropdown');
        window.togglePaymentDropdown = (paymentId) => this.toggleDropdown(`paymentDropdown${paymentId}`);

        // Export the manager itself
        window.paymentManager = this;

        console.log('[PaymentManager] 🌐 Functions made globally available');
    }

    /**
     * Alpine.js integration
     */
    alpinePaymentManager() {
        return {
            selectedPayments: this.state.selectedPayments,

            openBulkModal() {
                if (this.bulkActionManager) {
                    this.bulkActionManager.openBulkModal();
                }
            },

            exportPayments() {
                if (this.exportManager) {
                    this.exportManager.exportPayments();
                }
            }
        };
    }

    /**
     * Debug information
     */
    getDebugInfo() {
        return {
            version: '1.0.0',
            state: this.state,
            modules: {
                bulkActionManager: !!this.bulkActionManager,
                receiptManager: !!this.receiptManager,
                filterManager: !!this.filterManager,
                statsManager: !!this.statsManager,
                exportManager: !!this.exportManager
            },
            elements: Object.keys(this.elements).reduce((acc, key) => {
                acc[key] = !!this.elements[key];
                return acc;
            }, {})
        };
    }
}

// Export for ES6 modules
export default PaymentManager;