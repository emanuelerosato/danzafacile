/**
 * Schedule Management Module
 * Handles all schedule slot operations for course editing
 */

export class ScheduleManager {
    constructor(options = {}) {
        this.scheduleSlotIndex = options.scheduleSlotIndex || 0;
        this.availableRooms = options.availableRooms || [];
        this.container = null;
        this.init();
    }

    /**
     * Initialize the schedule manager
     */
    init() {
        this.container = document.getElementById('schedule-container');
        if (!this.container) {
            console.error('[ScheduleManager] Container #schedule-container not found');
            return;
        }

        // Count existing slots to set proper index
        this.scheduleSlotIndex = this.container.querySelectorAll('.schedule-slot').length;

        console.log(`[ScheduleManager] Initialized with ${this.scheduleSlotIndex} existing slots`);
        this.attachEventListeners();
    }

    /**
     * Add event listeners for schedule management
     */
    attachEventListeners() {
        // Delegate click events for remove buttons
        this.container.addEventListener('click', (e) => {
            if (e.target.matches('button[onclick*="removeScheduleSlot"]') ||
                e.target.closest('button[onclick*="removeScheduleSlot"]')) {
                e.preventDefault();
                e.stopPropagation();

                const button = e.target.matches('button') ? e.target : e.target.closest('button');
                this.removeScheduleSlot(button);
            }
        });

        // Delegate change events for time inputs (duration calculation)
        this.container.addEventListener('change', (e) => {
            if (e.target.matches('input[type="time"]')) {
                this.calculateDuration(e.target);
            }
        });
    }

    /**
     * Add a new schedule slot
     */
    addScheduleSlot() {
        if (!this.container) {
            console.error('[ScheduleManager] Container not found');
            return;
        }

        console.log(`[ScheduleManager] Adding new schedule slot #${this.scheduleSlotIndex + 1}`);
        console.log(`[ScheduleManager] Available rooms:`, this.availableRooms);

        const slotHtml = this.generateScheduleSlotHTML(this.scheduleSlotIndex);
        this.container.insertAdjacentHTML('beforeend', slotHtml);

        // Wait for DOM to update, then initialize room dropdown for the new slot
        setTimeout(() => {
            this.initializeLastRoomDropdown();
        }, 10);

        this.scheduleSlotIndex++;

        console.log(`[ScheduleManager] Added new schedule slot #${this.scheduleSlotIndex}`);
    }

    /**
     * Remove a schedule slot
     * @param {HTMLElement} button - The remove button element
     */
    removeScheduleSlot(button) {
        try {
            console.log('[ScheduleManager] Removing schedule slot...');

            const slot = button.closest('.schedule-slot');
            if (!slot) {
                console.error('[ScheduleManager] Could not find schedule slot to remove');
                return;
            }

            console.log('[ScheduleManager] Slot found, removing...');
            slot.remove();

            console.log('[ScheduleManager] Slot removed, updating numbers...');
            this.updateSlotNumbers();

            console.log('[ScheduleManager] Schedule slot removed successfully');
        } catch (error) {
            console.error('[ScheduleManager] Error removing schedule slot:', error);
        }
    }

    /**
     * Update slot numbers after addition/removal
     */
    updateSlotNumbers() {
        try {
            const slots = this.container.querySelectorAll('.schedule-slot');
            console.log(`[ScheduleManager] Updating ${slots.length} slot numbers...`);

            slots.forEach((slot, index) => {
                // Update title
                const title = slot.querySelector('h4');
                if (title) {
                    const daySelect = slot.querySelector('select[name*="[day]"]');
                    const selectedDay = daySelect && daySelect.value ? daySelect.value : '';

                    if (selectedDay) {
                        title.textContent = `${selectedDay} - Orario ${index + 1}`;
                    } else {
                        title.textContent = `Orario ${index + 1}`;
                    }
                }

                // Update field names
                const inputs = slot.querySelectorAll('input, select');
                inputs.forEach(input => {
                    const nameAttr = input.getAttribute('name');
                    if (nameAttr && nameAttr.includes('schedule_slots[')) {
                        const newName = nameAttr.replace(/schedule_slots\[\d+\]/, `schedule_slots[${index}]`);
                        input.setAttribute('name', newName);
                    }
                });
            });

            console.log('[ScheduleManager] Slot numbers updated successfully');
        } catch (error) {
            console.error('[ScheduleManager] Error updating slot numbers:', error);
        }
    }

    /**
     * Calculate duration based on start and end time
     * @param {HTMLInputElement} input - Time input element
     */
    calculateDuration(input) {
        const scheduleDiv = input.closest('.schedule-slot');
        if (!scheduleDiv) return;

        const startTimeInput = scheduleDiv.querySelector('input[name$="[start_time]"]');
        const endTimeInput = scheduleDiv.querySelector('input[name$="[end_time]"]');
        const durationSpan = scheduleDiv.querySelector('.duration-display');

        if (!startTimeInput || !endTimeInput || !durationSpan) return;

        const startTime = startTimeInput.value;
        const endTime = endTimeInput.value;

        if (startTime && endTime) {
            const start = new Date(`2000-01-01 ${startTime}`);
            const end = new Date(`2000-01-01 ${endTime}`);

            if (end > start) {
                const diffMs = end - start;
                const diffMins = Math.floor(diffMs / 60000);
                const hours = Math.floor(diffMins / 60);
                const minutes = diffMins % 60;

                let durationText = '';
                if (hours > 0) {
                    durationText += `${hours}h `;
                }
                if (minutes > 0) {
                    durationText += `${minutes}min`;
                }

                durationSpan.textContent = durationText.trim() || '0min';
            } else {
                durationSpan.textContent = 'Orario non valido';
            }
        } else {
            durationSpan.textContent = '';
        }
    }

    /**
     * Generate HTML for a new schedule slot
     * @param {number} index - Slot index
     * @returns {string} HTML string
     */
    generateScheduleSlotHTML(index) {
        return `
        <div class="schedule-slot bg-gray-50 hover:bg-gray-100 rounded-lg p-4 border border-gray-200 transition-all duration-200">
            <div class="flex items-center justify-between mb-3">
                <h4 class="font-medium text-gray-900">Orario ${index + 1}</h4>
                <button type="button" onclick="window.scheduleManager.removeScheduleSlot(this)"
                        class="text-red-600 hover:text-red-800 text-sm flex items-center space-x-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span>Rimuovi</span>
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Day Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Giorno</label>
                    <select name="schedule_slots[${index}][day]"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="">Seleziona giorno</option>
                        <option value="Lunedì">Lunedì</option>
                        <option value="Martedì">Martedì</option>
                        <option value="Mercoledì">Mercoledì</option>
                        <option value="Giovedì">Giovedì</option>
                        <option value="Venerdì">Venerdì</option>
                        <option value="Sabato">Sabato</option>
                        <option value="Domenica">Domenica</option>
                    </select>
                </div>

                <!-- Start Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ora Inizio</label>
                    <input type="time" name="schedule_slots[${index}][start_time]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                </div>

                <!-- End Time -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ora Fine</label>
                    <input type="time" name="schedule_slots[${index}][end_time]"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                </div>

                <!-- Duration Display -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Durata</label>
                    <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-600">
                        <span class="duration-display">--</span>
                    </div>
                </div>
            </div>

            <!-- Room Selection -->
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Sala</label>
                <select name="schedule_slots[${index}][room_id]"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent room-dropdown">
                    <option value="">Seleziona una sala</option>
                </select>
            </div>
        </div>
        `;
    }

    /**
     * Initialize room dropdown for a specific slot
     * @param {number} slotIndex - Index of the slot
     */
    initializeRoomDropdown(slotIndex) {
        const slot = this.container.children[slotIndex];
        if (!slot) return;

        const dropdown = slot.querySelector('.room-dropdown');
        if (!dropdown) return;

        // Clear existing options except the first one
        dropdown.innerHTML = '<option value="">Seleziona una sala</option>';

        // Add room options
        this.availableRooms.forEach(room => {
            const option = document.createElement('option');
            option.value = room.id || room;
            option.textContent = room.name || room;
            dropdown.appendChild(option);
        });
    }

    /**
     * Initialize room dropdown for the last added slot
     */
    initializeLastRoomDropdown() {
        console.log('[ScheduleManager] Initializing room dropdown for last slot...');

        const slots = this.container.querySelectorAll('.schedule-slot');
        const lastSlot = slots[slots.length - 1];

        if (!lastSlot) {
            console.error('[ScheduleManager] No slots found');
            return;
        }

        const dropdown = lastSlot.querySelector('.room-dropdown');
        if (!dropdown) {
            console.error('[ScheduleManager] Room dropdown not found in last slot');
            return;
        }

        console.log('[ScheduleManager] Found dropdown, populating with rooms:', this.availableRooms);

        // Clear existing options except the first one
        dropdown.innerHTML = '<option value="">Seleziona una sala</option>';

        // Add room options
        this.availableRooms.forEach(room => {
            const option = document.createElement('option');
            option.value = room.id || room;
            option.textContent = room.name || room;
            dropdown.appendChild(option);
            console.log(`[ScheduleManager] Added room option: ${room.name} (${room.id})`);
        });

        console.log('[ScheduleManager] Room dropdown initialized successfully');
    }

    /**
     * Update available rooms for all dropdowns
     * @param {Array} rooms - Array of room objects or strings
     */
    updateAvailableRooms(rooms) {
        this.availableRooms = rooms;

        // Update all existing room dropdowns
        const dropdowns = this.container.querySelectorAll('.room-dropdown');
        dropdowns.forEach(dropdown => {
            const currentValue = dropdown.value;

            // Rebuild options
            dropdown.innerHTML = '<option value="">Seleziona una sala</option>';

            this.availableRooms.forEach(room => {
                const option = document.createElement('option');
                option.value = room.id || room;
                option.textContent = room.name || room;
                if ((room.id || room) == currentValue) {
                    option.selected = true;
                }
                dropdown.appendChild(option);
            });
        });
    }
}

// Export for global access if needed
window.ScheduleManager = ScheduleManager;