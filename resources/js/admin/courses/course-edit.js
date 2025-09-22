/**
 * Course Edit Main Module
 * Entry point for all course editing functionality
 */

import { ScheduleManager } from './modules/ScheduleManager.js';
import { generateRoomOptions, calculateDuration, addListField, logError, showNotification } from './utils/helpers.js';

class CourseEditManager {
    constructor() {
        this.scheduleManager = null;
        this.availableRooms = [];
        this.init();
    }

    /**
     * Initialize the course edit manager
     */
    init() {
        console.log('[CourseEdit] 🚀 Initializing Course Edit Manager v2.0');

        // Extract data from page
        this.extractPageData();

        // Initialize components
        this.initializeScheduleManager();
        this.attachGlobalEventListeners();

        // Make functions globally available for onclick handlers
        this.makeGloballyAvailable();

        console.log('[CourseEdit] ✅ Course Edit Manager initialized successfully');
    }

    /**
     * Extract necessary data from the page
     */
    extractPageData() {
        // Extract available rooms from existing dropdowns or global variables
        if (window.availableRooms) {
            this.availableRooms = window.availableRooms;
        } else {
            // Fallback: extract from existing dropdown
            const existingDropdown = document.querySelector('select[name*="room_id"]');
            if (existingDropdown) {
                this.availableRooms = Array.from(existingDropdown.options)
                    .filter(option => option.value)
                    .map(option => ({
                        id: option.value,
                        name: option.textContent
                    }));
            }
        }

        console.log('[CourseEdit] 📋 Available rooms:', this.availableRooms);
    }

    /**
     * Initialize the schedule manager
     */
    initializeScheduleManager() {
        const existingSlots = document.querySelectorAll('.schedule-slot').length;

        this.scheduleManager = new ScheduleManager({
            scheduleSlotIndex: existingSlots,
            availableRooms: this.availableRooms
        });

        console.log('[CourseEdit] 📅 Schedule manager initialized with', existingSlots, 'existing slots');
    }

    /**
     * Attach global event listeners
     */
    attachGlobalEventListeners() {
        // Equipment field management
        const addEquipmentBtn = document.getElementById('add-equipment');
        if (addEquipmentBtn) {
            addEquipmentBtn.addEventListener('click', () => {
                addListField('equipment-container', 'equipment', 'Aggiungi attrezzatura...');
            });
        }

        // Objectives field management
        const addObjectiveBtn = document.getElementById('add-objective');
        if (addObjectiveBtn) {
            addObjectiveBtn.addEventListener('click', () => {
                addListField('objectives-container', 'objectives', 'Aggiungi obiettivo...');
            });
        }

        // Add Schedule Slot button
        const addScheduleBtn = document.getElementById('add-schedule-slot');
        if (addScheduleBtn) {
            addScheduleBtn.addEventListener('click', () => {
                this.scheduleManager.addScheduleSlot();
            });
        }

        console.log('[CourseEdit] 🎯 Global event listeners attached');
    }

    /**
     * Make functions globally available for legacy onclick handlers
     */
    makeGloballyAvailable() {
        // Schedule functions
        window.addScheduleSlot = () => this.scheduleManager.addScheduleSlot();
        window.removeScheduleSlot = (button) => this.scheduleManager.removeScheduleSlot(button);
        window.calculateDuration = (input) => this.scheduleManager.calculateDuration(input);

        // Utility functions
        window.addEquipmentField = () => addListField('equipment-container', 'equipment', 'Aggiungi attrezzatura...');
        window.addObjectiveField = () => addListField('objectives-container', 'objectives', 'Aggiungi obiettivo...');
        window.generateRoomOptions = generateRoomOptions;

        // Make schedule manager available globally for debugging
        window.scheduleManager = this.scheduleManager;

        console.log('[CourseEdit] 🌐 Functions made globally available');
    }

    /**
     * Update room data (called when rooms are modified)
     * @param {Array} rooms - Updated room list
     */
    updateRooms(rooms) {
        this.availableRooms = rooms;
        if (this.scheduleManager) {
            this.scheduleManager.updateAvailableRooms(rooms);
        }
        console.log('[CourseEdit] 🔄 Room data updated');
    }

    /**
     * Refresh all components
     */
    refresh() {
        console.log('[CourseEdit] 🔄 Refreshing components...');

        // Re-extract page data
        this.extractPageData();

        // Update schedule manager
        if (this.scheduleManager) {
            this.scheduleManager.updateAvailableRooms(this.availableRooms);
        }

        console.log('[CourseEdit] ✅ Components refreshed');
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('[CourseEdit] 📱 DOM ready, initializing...');

    try {
        window.courseEditManager = new CourseEditManager();
    } catch (error) {
        console.error('[CourseEdit] ❌ Failed to initialize:', error);
        logError('Failed to initialize CourseEditManager', error);
    }
});

// Handle page visibility changes (for debugging)
document.addEventListener('visibilitychange', () => {
    if (!document.hidden && window.courseEditManager) {
        console.log('[CourseEdit] 👁️ Page visible again, checking state...');
    }
});

export default CourseEditManager;