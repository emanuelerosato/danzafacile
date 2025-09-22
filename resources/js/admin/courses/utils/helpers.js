/**
 * Helper utilities for course management
 * Safe functions extracted from edit.blade.php
 */

/**
 * Generate room options HTML for select elements
 * @param {Array} rooms - Array of room objects
 * @param {number|null} selectedRoomId - Currently selected room ID
 * @returns {string} HTML options string
 */
export function generateRoomOptions(rooms = [], selectedRoomId = null) {
    let options = '<option value="">Seleziona una sala</option>';

    if (Array.isArray(rooms)) {
        rooms.forEach(room => {
            const selected = room.id == selectedRoomId ? 'selected' : '';
            options += `<option value="${room.id}" ${selected}>${room.name}</option>`;
        });
    }

    return options;
}

/**
 * Calculate duration based on start and end time
 * @param {HTMLInputElement} input - Time input element
 */
export function calculateDuration(input) {
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
 * Add field to a list (equipment, objectives)
 * @param {string} containerId - Container element ID
 * @param {string} fieldName - Input field name
 * @param {string} placeholder - Input placeholder text
 */
export function addListField(containerId, fieldName, placeholder) {
    const container = document.getElementById(containerId);
    if (!container) return;

    const index = container.children.length;
    const fieldHtml = `
        <div class="flex items-center space-x-2 mb-2">
            <input type="text"
                   name="${fieldName}[${index}]"
                   placeholder="${placeholder}"
                   class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
            <button type="button"
                    onclick="this.parentElement.remove()"
                    class="px-3 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors">
                Rimuovi
            </button>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', fieldHtml);
}

/**
 * Error handling utility
 * @param {string} message - Error message
 * @param {Error} error - Error object
 */
export function logError(message, error = null) {
    console.error(`[CourseEdit] ${message}`, error);

    // In production, you might want to send to error tracking service
    if (window.errorTracker) {
        window.errorTracker.log(message, error);
    }
}

/**
 * Show temporary notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, error, warning)
 */
export function showNotification(message, type = 'info') {
    // Simple notification - can be enhanced later
    console.log(`[${type.toUpperCase()}] ${message}`);

    // TODO: Implement proper notification system
    // This is just a placeholder for now
}