/**
 * StatsManager.js
 *
 * Manages payment statistics display and real-time updates
 * Handles stats cards, charts, and dashboard metrics
 *
 * @version 1.0.0
 */

export class StatsManager {
    constructor(options = {}) {
        this.options = {
            statsCards: null,
            enableRealTimeUpdates: true,
            updateInterval: 30000, // 30 seconds
            enableAnimations: true,
            onStatsUpdate: null,
            debug: false,
            ...options
        };

        this.state = {
            currentStats: {},
            isLoading: false,
            lastUpdate: null,
            updateTimer: null
        };

        this.elements = {};
        this.animationQueue = [];

        this.init();
    }

    /**
     * Initialize the StatsManager
     */
    init() {
        console.log('[StatsManager] 📊 Initializing Stats Manager');

        this.cacheElements();
        this.extractCurrentStats();
        this.attachEventListeners();

        if (this.options.enableRealTimeUpdates) {
            this.startRealTimeUpdates();
        }

        console.log('[StatsManager] ✅ Stats Manager initialized');
    }

    /**
     * Cache DOM elements
     */
    cacheElements() {
        this.elements = {
            statsCards: this.options.statsCards || document.querySelectorAll('[data-stats-card]'),
            totalPayments: document.querySelector('[data-stat="total_payments"]'),
            completedAmount: document.querySelector('[data-stat="completed_amount"]'),
            pendingPayments: document.querySelector('[data-stat="pending_payments"]'),
            overduePayments: document.querySelector('[data-stat="overdue_payments"]'),
            thisMonthRevenue: document.querySelector('[data-stat="this_month_revenue"]'),
            refreshButton: document.querySelector('[data-refresh-stats]'),
            lastUpdateText: document.querySelector('[data-last-update]'),
            loadingIndicator: document.querySelector('[data-stats-loading]')
        };

        console.log('[StatsManager] 🎯 Elements cached');
    }

    /**
     * Extract current stats from DOM
     */
    extractCurrentStats() {
        this.state.currentStats = {
            total_payments: this.extractStatValue('total_payments'),
            completed_amount: this.extractStatValue('completed_amount'),
            pending_payments: this.extractStatValue('pending_payments'),
            overdue_payments: this.extractStatValue('overdue_payments'),
            this_month_revenue: this.extractStatValue('this_month_revenue')
        };

        this.state.lastUpdate = Date.now();
        this.updateLastUpdateDisplay();

        console.log('[StatsManager] 📋 Current stats extracted:', this.state.currentStats);
    }

    /**
     * Extract stat value from DOM element
     */
    extractStatValue(statKey) {
        const element = this.elements[statKey.replace('_', '')];
        if (!element) return null;

        const text = element.textContent.trim();

        // Try to extract numeric value
        const numericMatch = text.match(/[\d.,]+/);
        if (numericMatch) {
            return parseFloat(numericMatch[0].replace(/[.,]/g, match => match === ',' ? '.' : ''));
        }

        return text;
    }

    /**
     * Attach event listeners
     */
    attachEventListeners() {
        // Refresh button
        if (this.elements.refreshButton) {
            this.elements.refreshButton.addEventListener('click', (e) => {
                e.preventDefault();
                this.refreshStats();
            });
        }

        // Stats cards hover effects
        this.elements.statsCards.forEach(card => {
            card.addEventListener('mouseenter', () => {
                this.handleCardHover(card, true);
            });

            card.addEventListener('mouseleave', () => {
                this.handleCardHover(card, false);
            });
        });

        console.log('[StatsManager] 🎧 Event listeners attached');
    }

    /**
     * Start real-time updates
     */
    startRealTimeUpdates() {
        if (this.state.updateTimer) {
            clearInterval(this.state.updateTimer);
        }

        this.state.updateTimer = setInterval(() => {
            this.fetchStats();
        }, this.options.updateInterval);

        console.log('[StatsManager] ⏰ Real-time updates started');
    }

    /**
     * Stop real-time updates
     */
    stopRealTimeUpdates() {
        if (this.state.updateTimer) {
            clearInterval(this.state.updateTimer);
            this.state.updateTimer = null;
        }

        console.log('[StatsManager] ⏹️ Real-time updates stopped');
    }

    /**
     * Refresh stats manually
     */
    async refreshStats() {
        await this.fetchStats();
        this.showNotification('Statistiche aggiornate!', 'success');
    }

    /**
     * Fetch stats from server
     */
    async fetchStats() {
        if (this.state.isLoading) return;

        this.setLoadingState(true);

        try {
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                             document.querySelector('input[name="_token"]')?.value || '';

            const response = await fetch('/admin/payments/stats', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();

            if (data.success) {
                this.updateStats(data.data || data);
            } else {
                throw new Error(data.message || 'Failed to fetch stats');
            }
        } catch (error) {
            console.error('[StatsManager] Error fetching stats:', error);
            this.showNotification('Errore nel caricamento delle statistiche', 'error');
        } finally {
            this.setLoadingState(false);
        }
    }

    /**
     * Update stats display
     */
    updateStats(newStats) {
        const oldStats = { ...this.state.currentStats };
        this.state.currentStats = { ...newStats };
        this.state.lastUpdate = Date.now();

        // Update each stat with animation
        Object.keys(newStats).forEach(key => {
            const oldValue = oldStats[key];
            const newValue = newStats[key];

            if (oldValue !== newValue) {
                this.animateStatUpdate(key, oldValue, newValue);
            }
        });

        this.updateLastUpdateDisplay();

        // Notify parent component
        if (this.options.onStatsUpdate) {
            this.options.onStatsUpdate(this.state.currentStats);
        }

        console.log('[StatsManager] 📊 Stats updated:', this.state.currentStats);
    }

    /**
     * Animate stat update
     */
    animateStatUpdate(statKey, oldValue, newValue) {
        const element = this.elements[statKey.replace('_', '')];
        if (!element || !this.options.enableAnimations) {
            this.updateStatDisplay(statKey, newValue);
            return;
        }

        // Add to animation queue
        this.animationQueue.push({
            element,
            statKey,
            oldValue,
            newValue,
            startTime: Date.now()
        });

        this.processAnimationQueue();
    }

    /**
     * Process animation queue
     */
    processAnimationQueue() {
        if (this.animationQueue.length === 0) return;

        const animation = this.animationQueue.shift();
        this.animateStat(animation);

        // Process next animation after a delay
        if (this.animationQueue.length > 0) {
            setTimeout(() => this.processAnimationQueue(), 100);
        }
    }

    /**
     * Animate individual stat
     */
    animateStat(animation) {
        const { element, statKey, oldValue, newValue } = animation;
        const duration = 1000; // 1 second
        const steps = 30;
        const stepDuration = duration / steps;

        let currentStep = 0;

        // Add highlight effect
        element.closest('[data-stats-card]')?.classList.add('stats-updating');

        const interval = setInterval(() => {
            currentStep++;
            const progress = currentStep / steps;
            const currentValue = this.interpolateValue(oldValue, newValue, progress);

            this.updateStatDisplay(statKey, currentValue, element);

            if (currentStep >= steps) {
                clearInterval(interval);
                this.updateStatDisplay(statKey, newValue, element);

                // Remove highlight effect
                setTimeout(() => {
                    element.closest('[data-stats-card]')?.classList.remove('stats-updating');
                }, 200);
            }
        }, stepDuration);
    }

    /**
     * Interpolate between two values
     */
    interpolateValue(oldValue, newValue, progress) {
        if (typeof oldValue !== 'number' || typeof newValue !== 'number') {
            return progress > 0.5 ? newValue : oldValue;
        }

        return oldValue + (newValue - oldValue) * this.easeOutCubic(progress);
    }

    /**
     * Easing function for smooth animations
     */
    easeOutCubic(t) {
        return 1 - Math.pow(1 - t, 3);
    }

    /**
     * Update stat display
     */
    updateStatDisplay(statKey, value, element = null) {
        const targetElement = element || this.elements[statKey.replace('_', '')];
        if (!targetElement) return;

        let displayValue = value;

        // Format based on stat type
        if (statKey.includes('amount') || statKey.includes('revenue')) {
            displayValue = this.formatCurrency(value);
        } else if (typeof value === 'number') {
            displayValue = this.formatNumber(value);
        }

        targetElement.textContent = displayValue;
    }

    /**
     * Format currency values
     */
    formatCurrency(value) {
        if (typeof value !== 'number') return value;

        return new Intl.NumberFormat('it-IT', {
            style: 'currency',
            currency: 'EUR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(value);
    }

    /**
     * Format number values
     */
    formatNumber(value) {
        if (typeof value !== 'number') return value;

        return new Intl.NumberFormat('it-IT').format(Math.round(value));
    }

    /**
     * Handle card hover effects
     */
    handleCardHover(card, isHovering) {
        if (!this.options.enableAnimations) return;

        if (isHovering) {
            card.classList.add('stats-hover');
        } else {
            card.classList.remove('stats-hover');
        }
    }

    /**
     * Update last update display
     */
    updateLastUpdateDisplay() {
        if (!this.elements.lastUpdateText || !this.state.lastUpdate) return;

        const lastUpdate = new Date(this.state.lastUpdate);
        const timeString = lastUpdate.toLocaleTimeString('it-IT', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        });

        this.elements.lastUpdateText.textContent = `Ultimo aggiornamento: ${timeString}`;
    }

    /**
     * Set loading state
     */
    setLoadingState(isLoading) {
        this.state.isLoading = isLoading;

        if (this.elements.loadingIndicator) {
            this.elements.loadingIndicator.style.display = isLoading ? 'block' : 'none';
        }

        if (this.elements.refreshButton) {
            this.elements.refreshButton.disabled = isLoading;
            this.elements.refreshButton.style.opacity = isLoading ? '0.5' : '1';
        }

        // Add loading class to stats cards
        this.elements.statsCards.forEach(card => {
            if (isLoading) {
                card.classList.add('stats-loading');
            } else {
                card.classList.remove('stats-loading');
            }
        });
    }

    /**
     * Get stats summary
     */
    getStatsSummary() {
        const stats = this.state.currentStats;

        return {
            totalRevenue: stats.completed_amount || 0,
            pendingRevenue: stats.pending_amount || 0,
            totalPayments: stats.total_payments || 0,
            completionRate: stats.total_payments > 0 ?
                Math.round((stats.completed_payments || 0) / stats.total_payments * 100) : 0,
            overdueCount: stats.overdue_payments || 0,
            thisMonthRevenue: stats.this_month_revenue || 0
        };
    }

    /**
     * Export stats for reporting
     */
    exportStats() {
        return {
            currentStats: { ...this.state.currentStats },
            lastUpdate: this.state.lastUpdate,
            summary: this.getStatsSummary(),
            timestamp: Date.now()
        };
    }

    /**
     * Show notification
     */
    showNotification(message, type = 'info') {
        console.log(`[StatsManager] ${type.toUpperCase()}: ${message}`);

        // For now, use console - can be enhanced with a proper notification system
        if (this.options.debug || type === 'error') {
            console.log(`[StatsManager] ${message}`);
        }
    }

    /**
     * Reset stats to initial state
     */
    resetStats() {
        this.extractCurrentStats();
        this.setLoadingState(false);
    }

    /**
     * Destroy the stats manager
     */
    destroy() {
        this.stopRealTimeUpdates();
        this.animationQueue = [];
        this.state = {
            currentStats: {},
            isLoading: false,
            lastUpdate: null,
            updateTimer: null
        };

        console.log('[StatsManager] 🗑️ Stats Manager destroyed');
    }

    /**
     * Get debug information
     */
    getDebugInfo() {
        return {
            state: this.state,
            options: this.options,
            elements: Object.keys(this.elements).reduce((acc, key) => {
                acc[key] = !!this.elements[key];
                return acc;
            }, {}),
            animationQueue: this.animationQueue.length
        };
    }
}

export default StatsManager;