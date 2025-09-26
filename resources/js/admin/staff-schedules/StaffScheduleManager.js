/**
 * StaffScheduleManager - Main orchestrator for staff schedule management
 *
 * Handles state management, API calls, and module coordination
 * for the staff scheduling system.
 */

import { ScheduleFormManager } from './modules/ScheduleFormManager.js';
import { CalendarManager } from './modules/CalendarManager.js';
import { FilterManager } from './modules/FilterManager.js';
import { NotificationManager } from './modules/NotificationManager.js';
import { BulkActionManager } from './modules/BulkActionManager.js';

export class StaffScheduleManager {
    constructor(options = {}) {
        this.options = {
            baseUrl: '/admin/staff-schedules',
            debug: false,
            autoRefresh: true,
            refreshInterval: 30000, // 30 seconds
            ...options
        };

        // State management
        this.state = {
            schedules: new Map(),
            selectedSchedules: new Set(),
            filters: {
                staff_id: '',
                type: '',
                status: '',
                date_from: '',
                date_to: ''
            },
            loading: false,
            lastUpdate: null,
            currentView: 'list' // 'list', 'calendar'
        };

        // Module instances
        this.modules = {};

        // Event listeners
        this.eventListeners = new Map();

        // Initialize
        this.init();
    }

    /**
     * Initialize the manager and all modules
     */
    async init() {
        try {
            this.log('Initializing StaffScheduleManager...');

            // Initialize notification system first
            this.modules.notification = new NotificationManager({
                position: 'top-right',
                duration: 5000
            });

            // Initialize other modules
            await this.initializeModules();

            // Setup event listeners
            this.setupEventListeners();

            // Load initial data
            await this.loadInitialData();

            this.log('StaffScheduleManager initialized successfully');

        } catch (error) {
            console.error('Failed to initialize StaffScheduleManager:', error);
            this.modules.notification?.error('Errore nell\'inizializzazione del sistema');
        }
    }

    /**
     * Initialize all modules
     */
    async initializeModules() {
        const page = this.getCurrentPage();

        // Form manager for create/edit pages
        if (['create', 'edit'].includes(page)) {
            this.modules.form = new ScheduleFormManager({
                manager: this,
                validateOnChange: true,
                autoSave: true
            });
        }

        // Filter manager for list page
        if (page === 'index') {
            this.modules.filter = new FilterManager({
                manager: this,
                debounceDelay: 300,
                persistState: true
            });

            this.modules.bulkAction = new BulkActionManager({
                manager: this,
                confirmActions: true
            });
        }

        // Calendar manager (available on all pages)
        this.modules.calendar = new CalendarManager({
            manager: this,
            defaultView: 'week',
            timeFormat: '24h'
        });
    }

    /**
     * Setup global event listeners
     */
    setupEventListeners() {
        // Page visibility change
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden && this.options.autoRefresh) {
                this.refreshData();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardShortcuts(e);
        });

        // Before unload warning for unsaved changes
        window.addEventListener('beforeunload', (e) => {
            if (this.hasUnsavedChanges()) {
                e.preventDefault();
                e.returnValue = '';
            }
        });
    }

    /**
     * Handle keyboard shortcuts
     */
    handleKeyboardShortcuts(event) {
        // Ctrl/Cmd + S: Save form
        if ((event.ctrlKey || event.metaKey) && event.key === 's') {
            event.preventDefault();
            if (this.modules.form) {
                this.modules.form.save();
            }
        }

        // Ctrl/Cmd + A: Select all (in list view)
        if ((event.ctrlKey || event.metaKey) && event.key === 'a' && this.getCurrentPage() === 'index') {
            event.preventDefault();
            this.modules.bulkAction?.selectAll();
        }

        // Escape: Cancel current action
        if (event.key === 'Escape') {
            this.cancelCurrentAction();
        }
    }

    /**
     * Load initial data based on current page
     */
    async loadInitialData() {
        const page = this.getCurrentPage();

        switch (page) {
            case 'index':
                await this.loadSchedules();
                break;
            case 'show':
                await this.loadScheduleDetails();
                break;
            case 'create':
            case 'edit':
                await this.loadFormData();
                break;
        }
    }

    /**
     * API Methods
     */

    /**
     * Load schedules with current filters
     */
    async loadSchedules(filters = {}) {
        this.setLoading(true);

        try {
            const queryParams = new URLSearchParams({
                ...this.state.filters,
                ...filters
            });

            const response = await fetch(`${this.options.baseUrl}?${queryParams}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            this.updateSchedules(data.schedules || []);

            return data;

        } catch (error) {
            this.log('Error loading schedules:', error);
            this.modules.notification?.error('Errore nel caricamento dei turni');
            throw error;
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Create new schedule
     */
    async createSchedule(scheduleData) {
        this.setLoading(true);

        try {
            const formData = new FormData();
            Object.entries(scheduleData).forEach(([key, value]) => {
                if (Array.isArray(value)) {
                    value.forEach(item => formData.append(`${key}[]`, item));
                } else if (value !== null && value !== undefined) {
                    formData.append(key, value);
                }
            });

            const response = await fetch(`${this.options.baseUrl}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Errore nella creazione del turno');
            }

            this.modules.notification?.success('Turno creato con successo!');

            // Redirect to show page if provided
            if (data.redirect) {
                window.location.href = data.redirect;
                return;
            }

            return data;

        } catch (error) {
            this.log('Error creating schedule:', error);
            this.modules.notification?.error(error.message);
            throw error;
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Update existing schedule
     */
    async updateSchedule(scheduleId, scheduleData) {
        this.setLoading(true);

        try {
            const formData = new FormData();
            formData.append('_method', 'PUT');

            Object.entries(scheduleData).forEach(([key, value]) => {
                if (Array.isArray(value)) {
                    value.forEach(item => formData.append(`${key}[]`, item));
                } else if (value !== null && value !== undefined) {
                    formData.append(key, value);
                }
            });

            const response = await fetch(`${this.options.baseUrl}/${scheduleId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            const data = await response.json();

            if (!response.ok) {
                throw new Error(data.message || 'Errore nell\'aggiornamento del turno');
            }

            this.modules.notification?.success('Turno aggiornato con successo!');

            // Update local state
            if (data.schedule) {
                this.updateSchedule(data.schedule);
            }

            return data;

        } catch (error) {
            this.log('Error updating schedule:', error);
            this.modules.notification?.error(error.message);
            throw error;
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * Delete schedule
     */
    async deleteSchedule(scheduleId) {
        this.setLoading(true);

        try {
            const response = await fetch(`${this.options.baseUrl}/${scheduleId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) {
                const data = await response.json();
                throw new Error(data.message || 'Errore nell\'eliminazione del turno');
            }

            this.modules.notification?.success('Turno eliminato con successo!');

            // Remove from local state
            this.state.schedules.delete(scheduleId);
            this.state.selectedSchedules.delete(scheduleId);

            return true;

        } catch (error) {
            this.log('Error deleting schedule:', error);
            this.modules.notification?.error(error.message);
            throw error;
        } finally {
            this.setLoading(false);
        }
    }

    /**
     * State Management Methods
     */

    updateSchedules(schedules) {
        schedules.forEach(schedule => {
            this.state.schedules.set(schedule.id, schedule);
        });
        this.state.lastUpdate = new Date();
        this.emit('schedules:updated', schedules);
    }

    setLoading(loading) {
        this.state.loading = loading;
        this.emit('loading:changed', loading);
    }

    updateFilters(filters) {
        this.state.filters = { ...this.state.filters, ...filters };
        this.emit('filters:changed', this.state.filters);
    }

    /**
     * Utility Methods
     */

    getCurrentPage() {
        const path = window.location.pathname;
        if (path.includes('/create')) return 'create';
        if (path.includes('/edit')) return 'edit';
        if (path.match(/\/\d+$/)) return 'show';
        return 'index';
    }

    hasUnsavedChanges() {
        return this.modules.form?.hasUnsavedChanges() || false;
    }

    cancelCurrentAction() {
        // Delegate to appropriate module
        if (this.modules.form?.isFormDirty()) {
            this.modules.form.cancelEdit();
        }

        if (this.modules.bulkAction?.hasActiveSelection()) {
            this.modules.bulkAction.clearSelection();
        }
    }

    refreshData() {
        const page = this.getCurrentPage();

        if (page === 'index') {
            this.loadSchedules();
        }
    }

    /**
     * Event Management
     */

    on(event, callback) {
        if (!this.eventListeners.has(event)) {
            this.eventListeners.set(event, []);
        }
        this.eventListeners.get(event).push(callback);
    }

    off(event, callback) {
        if (this.eventListeners.has(event)) {
            const listeners = this.eventListeners.get(event);
            const index = listeners.indexOf(callback);
            if (index > -1) {
                listeners.splice(index, 1);
            }
        }
    }

    emit(event, data) {
        if (this.eventListeners.has(event)) {
            this.eventListeners.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    this.log('Error in event callback:', error);
                }
            });
        }
    }

    /**
     * Debug logging
     */
    log(...args) {
        if (this.options.debug) {
            console.log('[StaffScheduleManager]', ...args);
        }
    }

    /**
     * Cleanup
     */
    destroy() {
        // Cleanup modules
        Object.values(this.modules).forEach(module => {
            if (typeof module.destroy === 'function') {
                module.destroy();
            }
        });

        // Clear event listeners
        this.eventListeners.clear();

        this.log('StaffScheduleManager destroyed');
    }
}

// Export for global access
window.StaffScheduleManager = StaffScheduleManager;