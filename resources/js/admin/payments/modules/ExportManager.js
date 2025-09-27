/**
 * ExportManager.js
 *
 * Manages payment data export functionality
 * Handles CSV exports, PDF reports, and custom export formats
 *
 * @version 1.0.0
 */

export class ExportManager {
    constructor(options = {}) {
        this.options = {
            routes: {},
            defaultFormat: 'csv',
            enableProgress: true,
            onExportStart: null,
            onExportComplete: null,
            onExportError: null,
            debug: false,
            ...options
        };

        this.state = {
            isExporting: false,
            currentExport: null,
            exportHistory: []
        };

        this.availableFormats = this.getAvailableFormats();
        this.elements = {};

        this.init();
    }

    /**
     * Initialize the ExportManager
     */
    init() {
        console.log('[ExportManager] 📤 Initializing Export Manager');

        this.cacheElements();
        this.attachEventListeners();
        this.loadExportHistory();

        console.log('[ExportManager] ✅ Export Manager initialized');
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements = {
            exportButton: document.querySelector('[data-export-payments]'),
            exportModal: document.getElementById('exportModal'),
            formatSelect: document.querySelector('[data-export-format]'),
            dateRangeInputs: document.querySelectorAll('[data-export-date]'),
            includeFiltersCheckbox: document.querySelector('[data-include-filters]'),
            exportProgress: document.querySelector('[data-export-progress]'),
            exportForm: document.querySelector('[data-export-form]'),
            downloadLink: document.querySelector('[data-download-link]'),
            exportHistory: document.querySelector('[data-export-history]')
        };

        console.log('[ExportManager] 🎯 Elements cached');
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Export button
        if (this.elements.exportButton) {
            this.elements.exportButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.exportPayments();
            });
        }

        // Export form submission
        if (this.elements.exportForm) {
            this.elements.exportForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleCustomExport();
            });
        }

        // Format change
        if (this.elements.formatSelect) {
            this.elements.formatSelect.addEventListener('change', (e) => {
                this.handleFormatChange(e.target.value);
            });
        }

        console.log('[ExportManager] 🎧 Event listeners attached');
    }

    /**
     * Export payments with current filters
     */
    async exportPayments(format = null, customOptions = {}) {
        if (this.state.isExporting) {
            this.showNotification('Esportazione già in corso...', 'warning');
            return;
        }

        const exportFormat = format || this.options.defaultFormat;
        const exportConfig = this.availableFormats[exportFormat];

        if (!exportConfig) {
            this.showNotification('Formato di esportazione non supportato', 'error');
            return;
        }

        this.state.isExporting = true;
        this.state.currentExport = {
            format: exportFormat,
            startTime: Date.now(),
            options: customOptions
        };

        // Notify start
        if (this.options.onExportStart) {
            this.options.onExportStart();
        }

        this.showProgress(true);

        try {
            await this.executeExport(exportFormat, customOptions);
            this.addToHistory(this.state.currentExport);

            // Notify completion
            if (this.options.onExportComplete) {
                this.options.onExportComplete();
            }

        } catch (error) {
            console.error('[ExportManager] Export failed:', error);
            this.showNotification('Errore durante l\'esportazione', 'error');

            // Notify error
            if (this.options.onExportError) {
                this.options.onExportError(error);
            }
        } finally {
            this.state.isExporting = false;
            this.state.currentExport = null;
            this.showProgress(false);
        }
    }

    /**
     * Execute export based on format
     */
    async executeExport(format, options = {}) {
        switch (format) {
            case 'csv':
                await this.exportCSV(options);
                break;
            case 'excel':
                await this.exportExcel(options);
                break;
            case 'pdf':
                await this.exportPDF(options);
                break;
            case 'json':
                await this.exportJSON(options);
                break;
            default:
                throw new Error(`Unsupported export format: ${format}`);
        }
    }

    /**
     * Export as CSV
     */
    async exportCSV(options = {}) {
        const params = this.buildExportParams(options);
        const url = this.buildExportUrl('csv', params);

        // Trigger download
        this.triggerDownload(url);

        // Simulate progress for user feedback
        await this.simulateProgress();
    }

    /**
     * Export as Excel
     */
    async exportExcel(options = {}) {
        const params = this.buildExportParams(options);
        const url = this.buildExportUrl('excel', params);

        this.triggerDownload(url);
        await this.simulateProgress();
    }

    /**
     * Export as PDF report
     */
    async exportPDF(options = {}) {
        const params = this.buildExportParams(options);
        const url = this.buildExportUrl('pdf', params);

        this.triggerDownload(url);
        await this.simulateProgress();
    }

    /**
     * Export as JSON
     */
    async exportJSON(options = {}) {
        try {
            const params = this.buildExportParams(options);
            const response = await fetch(this.buildExportUrl('json', params));

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            this.downloadJSON(data, `payments_export_${Date.now()}.json`);

        } catch (error) {
            console.error('[ExportManager] JSON export failed:', error);
            throw error;
        }
    }

    /**
     * Handle custom export from modal
     */
    async handleCustomExport() {
        const formData = new FormData(this.elements.exportForm);
        const options = {
            format: formData.get('format') || 'csv',
            dateFrom: formData.get('date_from'),
            dateTo: formData.get('date_to'),
            includeFilters: formData.get('include_filters') === 'on',
            columns: Array.from(formData.getAll('columns'))
        };

        this.closeExportModal();
        await this.exportPayments(options.format, options);
    }

    /**
     * Build export parameters
     */
    buildExportParams(options = {}) {
        const params = new URLSearchParams();

        // Add current page filters if not specified otherwise
        if (options.includeFilters !== false) {
            const currentUrl = new URL(window.location.href);
            currentUrl.searchParams.forEach((value, key) => {
                if (key !== 'page') { // Exclude pagination
                    params.append(key, value);
                }
            });
        }

        // Add custom date range
        if (options.dateFrom) {
            params.set('date_from', options.dateFrom);
        }
        if (options.dateTo) {
            params.set('date_to', options.dateTo);
        }

        // Add format-specific options
        if (options.format) {
            params.set('format', options.format);
        }

        if (options.columns && options.columns.length > 0) {
            params.set('columns', options.columns.join(','));
        }

        return params;
    }

    /**
     * Build export URL
     */
    buildExportUrl(format, params) {
        const baseUrl = this.options.routes.export || '/admin/payments/export';
        return params.toString() ? `${baseUrl}?${params.toString()}` : baseUrl;
    }

    /**
     * Trigger file download
     */
    triggerDownload(url) {
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    /**
     * Download JSON data as file
     */
    downloadJSON(data, filename) {
        const jsonString = JSON.stringify(data, null, 2);
        const blob = new Blob([jsonString], { type: 'application/json' });
        const url = URL.createObjectURL(blob);

        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        link.style.display = 'none';

        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        URL.revokeObjectURL(url);
    }

    /**
     * Simulate progress for user feedback
     */
    async simulateProgress() {
        const steps = [20, 40, 60, 80, 100];
        for (const step of steps) {
            this.updateProgress(step);
            await this.delay(200);
        }
    }

    /**
     * Update progress display
     */
    updateProgress(percentage) {
        if (this.elements.exportProgress) {
            const progressBar = this.elements.exportProgress.querySelector('.progress-bar');
            const progressText = this.elements.exportProgress.querySelector('.progress-text');

            if (progressBar) {
                progressBar.style.width = `${percentage}%`;
            }

            if (progressText) {
                progressText.textContent = `${percentage}%`;
            }
        }
    }

    /**
     * Show/hide progress indicator
     */
    showProgress(show) {
        if (this.elements.exportProgress) {
            this.elements.exportProgress.style.display = show ? 'block' : 'none';
        }

        if (show) {
            this.updateProgress(0);
        }
    }

    /**
     * Handle format change
     */
    handleFormatChange(format) {
        const formatConfig = this.availableFormats[format];
        if (!formatConfig) return;

        // Update UI based on format capabilities
        console.log('[ExportManager] Format changed to:', format);
    }

    /**
     * Get available export formats
     */
    getAvailableFormats() {
        return {
            csv: {
                label: 'CSV',
                description: 'Comma-separated values file',
                mimeType: 'text/csv',
                extension: '.csv',
                supportsCustomColumns: true
            },
            excel: {
                label: 'Excel',
                description: 'Microsoft Excel spreadsheet',
                mimeType: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                extension: '.xlsx',
                supportsCustomColumns: true
            },
            pdf: {
                label: 'PDF',
                description: 'Portable Document Format report',
                mimeType: 'application/pdf',
                extension: '.pdf',
                supportsCustomColumns: false
            },
            json: {
                label: 'JSON',
                description: 'JavaScript Object Notation data',
                mimeType: 'application/json',
                extension: '.json',
                supportsCustomColumns: true
            }
        };
    }

    /**
     * Add export to history
     */
    addToHistory(exportData) {
        this.state.exportHistory.unshift({
            ...exportData,
            completedAt: Date.now(),
            id: this.generateId()
        });

        // Keep only last 10 exports
        this.state.exportHistory = this.state.exportHistory.slice(0, 10);

        this.saveExportHistory();
        this.updateHistoryDisplay();
    }

    /**
     * Load export history from localStorage
     */
    loadExportHistory() {
        try {
            const stored = localStorage.getItem('payments_export_history');
            if (stored) {
                this.state.exportHistory = JSON.parse(stored);
                this.updateHistoryDisplay();
            }
        } catch (error) {
            console.warn('[ExportManager] Failed to load export history:', error);
        }
    }

    /**
     * Save export history to localStorage
     */
    saveExportHistory() {
        try {
            localStorage.setItem('payments_export_history', JSON.stringify(this.state.exportHistory));
        } catch (error) {
            console.warn('[ExportManager] Failed to save export history:', error);
        }
    }

    /**
     * Update history display
     */
    updateHistoryDisplay() {
        if (!this.elements.exportHistory) return;

        const historyHtml = this.state.exportHistory
            .map(item => this.renderHistoryItem(item))
            .join('');

        this.elements.exportHistory.innerHTML = historyHtml || '<p class="text-gray-500">Nessuna esportazione recente</p>';
    }

    /**
     * Render history item
     */
    renderHistoryItem(item) {
        const date = new Date(item.completedAt).toLocaleString('it-IT');
        const formatConfig = this.availableFormats[item.format];

        return `
            <div class="flex items-center justify-between p-2 border-b">
                <div>
                    <span class="font-medium">${formatConfig?.label || item.format.toUpperCase()}</span>
                    <span class="text-sm text-gray-500 ml-2">${date}</span>
                </div>
                <button class="text-blue-600 hover:text-blue-800 text-sm"
                        onclick="exportManager.repeatExport('${item.id}')">
                    Ripeti
                </button>
            </div>
        `;
    }

    /**
     * Repeat previous export
     */
    async repeatExport(exportId) {
        const exportItem = this.state.exportHistory.find(item => item.id === exportId);
        if (!exportItem) {
            this.showNotification('Esportazione non trovata', 'error');
            return;
        }

        await this.exportPayments(exportItem.format, exportItem.options);
    }

    /**
     * Open export modal
     */
    openExportModal() {
        if (this.elements.exportModal) {
            this.elements.exportModal.classList.remove('hidden');
        }
    }

    /**
     * Close export modal
     */
    closeExportModal() {
        if (this.elements.exportModal) {
            this.elements.exportModal.classList.add('hidden');
        }
    }

    /**
     * Utility methods
     */
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    }

    showNotification(message, type = 'info') {
        console.log(`[ExportManager] ${type.toUpperCase()}: ${message}`);

        // For now, use console - can be enhanced with a proper notification system
        if (type === 'error' || type === 'warning') {
            console.warn(message);
        }
    }

    /**
     * Get export statistics
     */
    getExportStats() {
        return {
            totalExports: this.state.exportHistory.length,
            formatDistribution: this.state.exportHistory.reduce((acc, item) => {
                acc[item.format] = (acc[item.format] || 0) + 1;
                return acc;
            }, {}),
            lastExport: this.state.exportHistory[0] || null
        };
    }

    /**
     * Clear export history
     */
    clearHistory() {
        this.state.exportHistory = [];
        this.saveExportHistory();
        this.updateHistoryDisplay();
    }

    /**
     * Destroy the export manager
     */
    destroy() {
        this.state.isExporting = false;
        this.state.currentExport = null;
        console.log('[ExportManager] 🗑️ Export Manager destroyed');
    }

    /**
     * Get debug information
     */
    getDebugInfo() {
        return {
            state: this.state,
            options: this.options,
            availableFormats: this.availableFormats,
            elements: Object.keys(this.elements).reduce((acc, key) => {
                acc[key] = !!this.elements[key];
                return acc;
            }, {})
        };
    }
}

export default ExportManager;