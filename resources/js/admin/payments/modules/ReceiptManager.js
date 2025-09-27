/**
 * ReceiptManager.js
 *
 * Manages payment receipt generation and sending
 * Handles PDF generation, email sending, and receipt tracking
 *
 * @version 1.0.0
 */

export class ReceiptManager {
    constructor(options = {}) {
        this.options = {
            csrfToken: null,
            routes: {},
            enableTracking: true,
            autoPreview: false,
            onReceiptSent: null,
            onReceiptGenerated: null,
            onError: null,
            debug: false,
            ...options
        };

        this.state = {
            isGenerating: false,
            isSending: false,
            receiptHistory: [],
            previewCache: new Map()
        };

        this.elements = {};

        this.init();
    }

    /**
     * Initialize the ReceiptManager
     */
    init() {
        console.log('[ReceiptManager] 🧾 Initializing Receipt Manager');

        this.cacheElements();
        this.attachEventListeners();
        this.loadReceiptHistory();

        console.log('[ReceiptManager] ✅ Receipt Manager initialized');
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements = {
            receiptButtons: document.querySelectorAll('[data-action="send-receipt"]'),
            generateButtons: document.querySelectorAll('[data-action="generate-receipt"]'),
            previewButtons: document.querySelectorAll('[data-action="preview-receipt"]'),
            receiptModal: document.getElementById('receiptModal'),
            previewFrame: document.querySelector('[data-receipt-preview]'),
            sendingIndicator: document.querySelector('[data-sending-indicator]'),
            generatingIndicator: document.querySelector('[data-generating-indicator]'),
            receiptHistory: document.querySelector('[data-receipt-history]'),
            bulkReceiptBtn: document.querySelector('[data-bulk-receipts]')
        };

        console.log('[ReceiptManager] 🎯 Elements cached');
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Individual receipt buttons
        this.elements.receiptButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const paymentId = button.dataset.paymentId;
                this.sendReceipt(paymentId);
            });
        });

        // Generate PDF buttons
        this.elements.generateButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const paymentId = button.dataset.paymentId;
                this.generateReceipt(paymentId);
            });
        });

        // Preview buttons
        this.elements.previewButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const paymentId = button.dataset.paymentId;
                this.previewReceipt(paymentId);
            });
        });

        // Bulk receipt button
        if (this.elements.bulkReceiptBtn) {
            this.elements.bulkReceiptBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.handleBulkReceipts();
            });
        }

        console.log('[ReceiptManager] 🎧 Event listeners attached');
    }

    /**
     * Send receipt via email
     */
    async sendReceipt(paymentId) {
        if (this.state.isSending) {
            this.showNotification('Invio già in corso...', 'warning');
            return;
        }

        this.state.isSending = true;
        this.showSendingIndicator(true);

        try {
            const response = await fetch(`/admin/payments/${paymentId}/send-receipt`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.options.csrfToken
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showNotification('Ricevuta inviata con successo!', 'success');
                this.trackReceiptAction(paymentId, 'sent', data);

                // Notify parent component
                if (this.options.onReceiptSent) {
                    this.options.onReceiptSent(data);
                }

                // Update button state
                this.updateReceiptButtonState(paymentId, 'sent');

            } else {
                throw new Error(data.message || 'Errore nell\'invio della ricevuta');
            }

        } catch (error) {
            console.error('[ReceiptManager] Error sending receipt:', error);
            this.showNotification('Errore: ' + error.message, 'error');

            if (this.options.onError) {
                this.options.onError(error);
            }
        } finally {
            this.state.isSending = false;
            this.showSendingIndicator(false);
        }
    }

    /**
     * Generate PDF receipt
     */
    async generateReceipt(paymentId) {
        if (this.state.isGenerating) {
            this.showNotification('Generazione già in corso...', 'warning');
            return;
        }

        this.state.isGenerating = true;
        this.showGeneratingIndicator(true);

        try {
            const response = await fetch(`/admin/payments/${paymentId}/generate-receipt`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.options.csrfToken
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            // Get filename from response headers
            const contentDisposition = response.headers.get('Content-Disposition');
            let filename = 'ricevuta.pdf';

            if (contentDisposition) {
                const filenameMatch = contentDisposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
                if (filenameMatch) {
                    filename = filenameMatch[1].replace(/['"]/g, '');
                }
            }

            // Create blob and download
            const blob = await response.blob();
            this.downloadBlob(blob, filename);

            this.showNotification('Ricevuta generata con successo!', 'success');
            this.trackReceiptAction(paymentId, 'generated', { filename });

            // Notify parent component
            if (this.options.onReceiptGenerated) {
                this.options.onReceiptGenerated({ paymentId, filename });
            }

        } catch (error) {
            console.error('[ReceiptManager] Error generating receipt:', error);
            this.showNotification('Errore nella generazione: ' + error.message, 'error');

            if (this.options.onError) {
                this.options.onError(error);
            }
        } finally {
            this.state.isGenerating = false;
            this.showGeneratingIndicator(false);
        }
    }

    /**
     * Preview receipt in modal
     */
    async previewReceipt(paymentId) {
        // Check if preview is cached
        if (this.state.previewCache.has(paymentId)) {
            this.showPreviewModal(this.state.previewCache.get(paymentId));
            return;
        }

        try {
            const response = await fetch(`/admin/payments/${paymentId}/generate-receipt?preview=1`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': this.options.csrfToken
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const blob = await response.blob();
            const previewUrl = URL.createObjectURL(blob);

            // Cache the preview
            this.state.previewCache.set(paymentId, previewUrl);

            this.showPreviewModal(previewUrl);
            this.trackReceiptAction(paymentId, 'previewed');

        } catch (error) {
            console.error('[ReceiptManager] Error previewing receipt:', error);
            this.showNotification('Errore nel caricamento anteprima', 'error');
        }
    }

    /**
     * Handle bulk receipt sending
     */
    async handleBulkReceipts() {
        // This would integrate with BulkActionManager
        // For now, just show a message
        this.showNotification('Funzione di invio multiplo in sviluppo', 'info');
    }

    /**
     * Download blob as file
     */
    downloadBlob(blob, filename) {
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');

        link.href = url;
        link.download = filename;
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        // Clean up the URL object
        setTimeout(() => URL.revokeObjectURL(url), 100);
    }

    /**
     * Show preview modal
     */
    showPreviewModal(previewUrl) {
        if (!this.elements.receiptModal || !this.elements.previewFrame) {
            // Fallback: open in new window
            window.open(previewUrl, '_blank');
            return;
        }

        this.elements.previewFrame.src = previewUrl;
        this.elements.receiptModal.classList.remove('hidden');
    }

    /**
     * Close preview modal
     */
    closePreviewModal() {
        if (this.elements.receiptModal) {
            this.elements.receiptModal.classList.add('hidden');
        }

        if (this.elements.previewFrame) {
            this.elements.previewFrame.src = '';
        }
    }

    /**
     * Update receipt button state
     */
    updateReceiptButtonState(paymentId, action) {
        const buttons = document.querySelectorAll(`[data-payment-id="${paymentId}"][data-action*="receipt"]`);

        buttons.forEach(button => {
            if (action === 'sent') {
                button.classList.add('receipt-sent');
                button.title = 'Ricevuta già inviata';

                // Update text if it's a send button
                if (button.dataset.action === 'send-receipt') {
                    const icon = button.querySelector('svg');
                    const text = button.querySelector('span') || button;
                    if (text) {
                        text.textContent = 'Inviata';
                    }
                    if (icon) {
                        icon.classList.add('text-green-500');
                    }
                }
            }
        });
    }

    /**
     * Show sending indicator
     */
    showSendingIndicator(show) {
        if (this.elements.sendingIndicator) {
            this.elements.sendingIndicator.style.display = show ? 'block' : 'none';
        }

        // Update button states
        this.elements.receiptButtons.forEach(button => {
            button.disabled = show;
            button.style.opacity = show ? '0.5' : '1';
        });
    }

    /**
     * Show generating indicator
     */
    showGeneratingIndicator(show) {
        if (this.elements.generatingIndicator) {
            this.elements.generatingIndicator.style.display = show ? 'block' : 'none';
        }

        // Update button states
        this.elements.generateButtons.forEach(button => {
            button.disabled = show;
            button.style.opacity = show ? '0.5' : '1';
        });
    }

    /**
     * Track receipt action
     */
    trackReceiptAction(paymentId, action, data = {}) {
        if (!this.options.enableTracking) return;

        const trackingData = {
            paymentId,
            action,
            timestamp: Date.now(),
            data
        };

        this.state.receiptHistory.unshift(trackingData);

        // Keep only last 50 actions
        this.state.receiptHistory = this.state.receiptHistory.slice(0, 50);

        this.saveReceiptHistory();
        this.updateHistoryDisplay();

        console.log('[ReceiptManager] Action tracked:', trackingData);
    }

    /**
     * Load receipt history from localStorage
     */
    loadReceiptHistory() {
        if (!this.options.enableTracking) return;

        try {
            const stored = localStorage.getItem('receipt_history');
            if (stored) {
                this.state.receiptHistory = JSON.parse(stored);
                this.updateHistoryDisplay();
            }
        } catch (error) {
            console.warn('[ReceiptManager] Failed to load receipt history:', error);
        }
    }

    /**
     * Save receipt history to localStorage
     */
    saveReceiptHistory() {
        if (!this.options.enableTracking) return;

        try {
            localStorage.setItem('receipt_history', JSON.stringify(this.state.receiptHistory));
        } catch (error) {
            console.warn('[ReceiptManager] Failed to save receipt history:', error);
        }
    }

    /**
     * Update history display
     */
    updateHistoryDisplay() {
        if (!this.elements.receiptHistory) return;

        const recentActions = this.state.receiptHistory.slice(0, 10);
        const historyHtml = recentActions
            .map(item => this.renderHistoryItem(item))
            .join('');

        this.elements.receiptHistory.innerHTML = historyHtml || '<p class="text-gray-500">Nessuna azione recente</p>';
    }

    /**
     * Render history item
     */
    renderHistoryItem(item) {
        const date = new Date(item.timestamp).toLocaleString('it-IT');
        const actionLabels = {
            sent: 'Inviata',
            generated: 'Generata',
            previewed: 'Anteprima'
        };

        return `
            <div class="flex items-center justify-between p-2 border-b">
                <div>
                    <span class="font-medium">Ricevuta ${actionLabels[item.action] || item.action}</span>
                    <span class="text-sm text-gray-500 ml-2">Pagamento #${item.paymentId}</span>
                </div>
                <span class="text-xs text-gray-400">${date}</span>
            </div>
        `;
    }

    /**
     * Get receipt statistics
     */
    getReceiptStats() {
        return {
            totalActions: this.state.receiptHistory.length,
            actionDistribution: this.state.receiptHistory.reduce((acc, item) => {
                acc[item.action] = (acc[item.action] || 0) + 1;
                return acc;
            }, {}),
            lastAction: this.state.receiptHistory[0] || null,
            cacheSize: this.state.previewCache.size
        };
    }

    /**
     * Clear receipt cache
     */
    clearCache() {
        // Revoke all cached URLs
        for (const [paymentId, url] of this.state.previewCache) {
            URL.revokeObjectURL(url);
        }

        this.state.previewCache.clear();
        console.log('[ReceiptManager] Cache cleared');
    }

    /**
     * Clear receipt history
     */
    clearHistory() {
        this.state.receiptHistory = [];
        this.saveReceiptHistory();
        this.updateHistoryDisplay();
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        console.log(`[ReceiptManager] ${type.toUpperCase()}: ${message}`);

        // For now, use console - can be enhanced with a proper notification system
        if (type === 'error' || type === 'warning') {
            alert(message);
        } else if (type === 'success') {
            // Could show a toast notification
            console.log(`✅ ${message}`);
        }
    }

    /**
     * Validate receipt data
     */
    validateReceiptData(paymentData) {
        const requiredFields = ['id', 'amount', 'user', 'payment_date'];

        for (const field of requiredFields) {
            if (!paymentData[field]) {
                throw new Error(`Missing required field: ${field}`);
            }
        }

        return true;
    }

    /**
     * Destroy the receipt manager
     */
    destroy() {
        this.clearCache();
        this.state.isGenerating = false;
        this.state.isSending = false;

        console.log('[ReceiptManager] 🗑️ Receipt Manager destroyed');
    }

    /**
     * Get debug information
     */
    getDebugInfo() {
        return {
            state: {
                ...this.state,
                previewCache: this.state.previewCache.size
            },
            options: this.options,
            elements: Object.keys(this.elements).reduce((acc, key) => {
                acc[key] = !!this.elements[key];
                return acc;
            }, {}),
            stats: this.getReceiptStats()
        };
    }
}

export default ReceiptManager;