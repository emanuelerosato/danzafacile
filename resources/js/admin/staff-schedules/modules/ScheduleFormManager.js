/**
 * ScheduleFormManager - Handles form validation, auto-save, and requirement management
 *
 * Replaces the inline JavaScript functionality that was removed from create/edit templates:
 * - Dynamic requirement fields management
 * - Form validation with real-time feedback
 * - Auto-save functionality
 * - Overlap detection and conflict resolution
 */

export class ScheduleFormManager {
    constructor(options = {}) {
        this.options = {
            manager: null,
            validateOnChange: true,
            autoSave: true,
            autoSaveInterval: 30000, // 30 seconds
            debounceDelay: 500,
            ...options
        };

        // Reference to main manager
        this.manager = this.options.manager;

        // Form state
        this.state = {
            isDirty: false,
            isValid: false,
            hasUnsavedChanges: false,
            lastSave: null,
            validationErrors: new Map(),
            requirements: [],
            scheduleOverlaps: []
        };

        // Form elements
        this.form = null;
        this.elements = new Map();

        // Auto-save timer
        this.autoSaveTimer = null;
        this.validationDebouncer = null;

        // Initialize
        this.init();
    }

    /**
     * Initialize the form manager
     */
    async init() {
        try {
            this.log('Initializing ScheduleFormManager...');

            // Find and setup form
            this.setupForm();

            // Setup form elements
            this.setupFormElements();

            // Setup event listeners
            this.setupEventListeners();

            // Setup requirements management
            this.setupRequirementsManagement();

            // Initial validation
            if (this.options.validateOnChange) {
                await this.validateForm();
            }

            // Setup auto-save
            if (this.options.autoSave) {
                this.setupAutoSave();
            }

            this.log('ScheduleFormManager initialized successfully');

        } catch (error) {
            console.error('Failed to initialize ScheduleFormManager:', error);
            this.manager.modules.notification?.error('Errore nell\'inizializzazione del form');
        }
    }

    /**
     * Setup form reference and basic configuration
     */
    setupForm() {
        this.form = document.querySelector('#staff-schedule-form');

        if (!this.form) {
            throw new Error('Staff schedule form not found');
        }

        // Prevent default form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });

        this.log('Form setup completed');
    }

    /**
     * Setup form elements mapping
     */
    setupFormElements() {
        const fieldSelectors = {
            staff_id: '#staff_id',
            type: '#type',
            date: '#date',
            start_time: '#start_time',
            end_time: '#end_time',
            break_start: '#break_start',
            break_end: '#break_end',
            hourly_rate: '#hourly_rate',
            location: '#location',
            notes: '#notes',
            requirements: '[data-requirement-input]'
        };

        Object.entries(fieldSelectors).forEach(([key, selector]) => {
            const element = document.querySelector(selector);
            if (element) {
                this.elements.set(key, element);
            } else if (key === 'requirements') {
                // Requirements are dynamic, will be handled separately
                this.elements.set(key, []);
            }
        });

        this.log('Form elements mapped:', this.elements.size);
    }

    /**
     * Setup event listeners for form interactions
     */
    setupEventListeners() {
        // Individual field validation
        this.elements.forEach((element, key) => {
            if (element && element.addEventListener) {
                element.addEventListener('input', (e) => {
                    this.handleFieldChange(key, e.target.value);
                });

                element.addEventListener('blur', (e) => {
                    this.validateField(key, e.target.value);
                });
            }
        });

        // Time fields overlap detection
        ['start_time', 'end_time', 'break_start', 'break_end'].forEach(field => {
            const element = this.elements.get(field);
            if (element) {
                element.addEventListener('change', () => {
                    this.checkTimeOverlaps();
                });
            }
        });

        // Date change affects overlap detection
        const dateElement = this.elements.get('date');
        if (dateElement) {
            dateElement.addEventListener('change', () => {
                this.checkScheduleConflicts();
            });
        }

        this.log('Event listeners setup completed');
    }

    /**
     * Setup requirements dynamic management
     */
    setupRequirementsManagement() {
        // Setup add requirement button
        const addBtn = document.getElementById('add-requirement-btn');
        if (addBtn) {
            addBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.addRequirement();
            });
        }

        // Setup existing requirements
        this.setupExistingRequirements();

        this.log('Requirements management setup completed');
    }

    /**
     * Setup existing requirement fields
     */
    setupExistingRequirements() {
        const existingRequirements = document.querySelectorAll('[data-requirement-input]');

        existingRequirements.forEach((input, index) => {
            const container = input.closest('[data-requirement-item]');
            if (container) {
                this.state.requirements.push({
                    index,
                    container,
                    input,
                    value: input.value
                });

                // Setup remove button for this requirement
                this.setupRequirementRemove(container, index);
            }
        });
    }

    /**
     * Add new requirement field
     */
    addRequirement() {
        const container = document.getElementById('requirements-container');
        if (!container) return;

        const index = this.state.requirements.length;
        const requirementHtml = `
            <div class="flex items-center space-x-2 mb-2" data-requirement-item>
                <input type="text"
                       name="requirements[]"
                       data-requirement-input
                       placeholder="Descrivi il requisito..."
                       class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                <button type="button"
                        class="px-3 py-3 bg-red-500 text-white rounded-lg hover:bg-red-600 focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200"
                        data-remove-requirement>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        `;

        container.insertAdjacentHTML('beforeend', requirementHtml);

        // Get the newly added elements
        const newContainer = container.lastElementChild;
        const newInput = newContainer.querySelector('[data-requirement-input]');

        // Add to state
        this.state.requirements.push({
            index,
            container: newContainer,
            input: newInput,
            value: ''
        });

        // Setup remove functionality
        this.setupRequirementRemove(newContainer, index);

        // Focus on new input
        newInput.focus();

        // Mark form as dirty
        this.markDirty();

        this.log('Requirement added:', index);
    }

    /**
     * Setup remove button for a requirement
     */
    setupRequirementRemove(container, index) {
        const removeBtn = container.querySelector('[data-remove-requirement]');
        if (removeBtn) {
            removeBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.removeRequirement(index);
            });
        }
    }

    /**
     * Remove requirement field
     */
    removeRequirement(index) {
        const requirement = this.state.requirements.find(req => req.index === index);
        if (!requirement) return;

        // Remove from DOM
        requirement.container.remove();

        // Remove from state
        this.state.requirements = this.state.requirements.filter(req => req.index !== index);

        // Mark form as dirty
        this.markDirty();

        this.log('Requirement removed:', index);
    }

    /**
     * Handle field change events
     */
    handleFieldChange(fieldName, value) {
        this.markDirty();

        if (this.options.validateOnChange) {
            // Debounce validation
            clearTimeout(this.validationDebouncer);
            this.validationDebouncer = setTimeout(() => {
                this.validateField(fieldName, value);
            }, this.options.debounceDelay);
        }
    }

    /**
     * Validate individual field
     */
    async validateField(fieldName, value) {
        const errors = [];

        switch (fieldName) {
            case 'staff_id':
                if (!value) {
                    errors.push('Seleziona un membro dello staff');
                }
                break;

            case 'type':
                if (!value) {
                    errors.push('Seleziona il tipo di turno');
                }
                break;

            case 'date':
                if (!value) {
                    errors.push('Seleziona una data');
                } else if (new Date(value) < new Date().setHours(0,0,0,0)) {
                    errors.push('La data non può essere nel passato');
                }
                break;

            case 'start_time':
                if (!value) {
                    errors.push('Inserisci l\'orario di inizio');
                }
                break;

            case 'end_time':
                if (!value) {
                    errors.push('Inserisci l\'orario di fine');
                } else if (this.elements.get('start_time')?.value && value <= this.elements.get('start_time').value) {
                    errors.push('L\'orario di fine deve essere successivo all\'inizio');
                }
                break;

            case 'hourly_rate':
                if (value && (isNaN(value) || parseFloat(value) < 0)) {
                    errors.push('La tariffa oraria deve essere un numero positivo');
                }
                break;
        }

        // Update validation state
        if (errors.length > 0) {
            this.state.validationErrors.set(fieldName, errors);
        } else {
            this.state.validationErrors.delete(fieldName);
        }

        // Update UI
        this.updateFieldValidation(fieldName, errors);

        // Check overall form validity
        this.updateFormValidity();
    }

    /**
     * Validate entire form
     */
    async validateForm() {
        const requiredFields = ['staff_id', 'type', 'date', 'start_time', 'end_time'];

        for (const fieldName of requiredFields) {
            const element = this.elements.get(fieldName);
            if (element) {
                await this.validateField(fieldName, element.value);
            }
        }

        // Check time overlaps
        await this.checkTimeOverlaps();

        // Check schedule conflicts
        await this.checkScheduleConflicts();
    }

    /**
     * Check for time overlaps within the same schedule
     */
    async checkTimeOverlaps() {
        const startTime = this.elements.get('start_time')?.value;
        const endTime = this.elements.get('end_time')?.value;
        const breakStart = this.elements.get('break_start')?.value;
        const breakEnd = this.elements.get('break_end')?.value;

        const errors = [];

        if (startTime && endTime && breakStart && breakEnd) {
            // Check if break is within work hours
            if (breakStart < startTime || breakEnd > endTime) {
                errors.push('La pausa deve essere compresa nell\'orario di lavoro');
            }

            // Check if break start is before break end
            if (breakStart >= breakEnd) {
                errors.push('L\'orario di inizio pausa deve essere precedente alla fine');
            }
        }

        if (errors.length > 0) {
            this.state.validationErrors.set('time_overlap', errors);
        } else {
            this.state.validationErrors.delete('time_overlap');
        }

        this.updateTimeOverlapUI(errors);
    }

    /**
     * Check for schedule conflicts with other staff schedules
     */
    async checkScheduleConflicts() {
        const staffId = this.elements.get('staff_id')?.value;
        const date = this.elements.get('date')?.value;
        const startTime = this.elements.get('start_time')?.value;
        const endTime = this.elements.get('end_time')?.value;

        if (!staffId || !date || !startTime || !endTime) {
            return;
        }

        try {
            const response = await fetch('/admin/staff-schedules/check-conflicts', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    staff_id: staffId,
                    date: date,
                    start_time: startTime,
                    end_time: endTime,
                    exclude_id: this.getScheduleId() // Exclude current schedule in edit mode
                })
            });

            const data = await response.json();

            if (data.conflicts && data.conflicts.length > 0) {
                this.state.scheduleOverlaps = data.conflicts;
                this.state.validationErrors.set('schedule_conflict',
                    ['Conflitto di orario con altri turni dello stesso membro dello staff']
                );
            } else {
                this.state.scheduleOverlaps = [];
                this.state.validationErrors.delete('schedule_conflict');
            }

            this.updateConflictUI(data.conflicts || []);

        } catch (error) {
            this.log('Error checking schedule conflicts:', error);
        }
    }

    /**
     * Update field validation UI
     */
    updateFieldValidation(fieldName, errors) {
        const element = this.elements.get(fieldName);
        if (!element) return;

        // Remove existing error styles
        element.classList.remove('border-red-500', 'focus:ring-red-500');

        // Remove existing error message
        const existingError = element.parentNode.querySelector('.field-error');
        if (existingError) {
            existingError.remove();
        }

        if (errors.length > 0) {
            // Add error styles
            element.classList.add('border-red-500', 'focus:ring-red-500');

            // Add error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'field-error text-sm text-red-600 mt-1';
            errorDiv.textContent = errors[0]; // Show first error
            element.parentNode.appendChild(errorDiv);
        }
    }

    /**
     * Update time overlap UI
     */
    updateTimeOverlapUI(errors) {
        const container = document.getElementById('time-overlap-errors');
        if (!container) return;

        container.innerHTML = '';

        if (errors.length > 0) {
            container.innerHTML = `
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Errori negli orari</h3>
                            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                                ${errors.map(error => `<li>${error}</li>`).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    /**
     * Update conflict UI
     */
    updateConflictUI(conflicts) {
        const container = document.getElementById('schedule-conflicts');
        if (!container) return;

        container.innerHTML = '';

        if (conflicts.length > 0) {
            container.innerHTML = `
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                        </svg>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-yellow-800">Conflitti di orario</h3>
                            <ul class="mt-2 text-sm text-yellow-700">
                                ${conflicts.map(conflict => `
                                    <li>Conflitto con turno ${conflict.type} dalle ${conflict.start_time} alle ${conflict.end_time}</li>
                                `).join('')}
                            </ul>
                        </div>
                    </div>
                </div>
            `;
        }
    }

    /**
     * Update overall form validity
     */
    updateFormValidity() {
        this.state.isValid = this.state.validationErrors.size === 0;

        // Update submit button state
        const submitBtn = this.form.querySelector('[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = !this.state.isValid;

            if (this.state.isValid) {
                submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');
            } else {
                submitBtn.classList.add('opacity-50', 'cursor-not-allowed');
            }
        }
    }

    /**
     * Setup auto-save functionality
     */
    setupAutoSave() {
        this.autoSaveTimer = setInterval(() => {
            if (this.state.isDirty && this.state.isValid) {
                this.autoSave();
            }
        }, this.options.autoSaveInterval);
    }

    /**
     * Auto-save form data
     */
    async autoSave() {
        if (!this.state.isDirty || !this.state.isValid) return;

        try {
            const formData = this.getFormData();
            formData.append('auto_save', '1');

            const scheduleId = this.getScheduleId();
            const url = scheduleId
                ? `/admin/staff-schedules/${scheduleId}/auto-save`
                : '/admin/staff-schedules/auto-save';

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            if (response.ok) {
                this.state.lastSave = new Date();
                this.state.hasUnsavedChanges = false;
                this.manager.modules.notification?.info('Dati salvati automaticamente');
                this.log('Auto-save completed');
            }

        } catch (error) {
            this.log('Auto-save failed:', error);
        }
    }

    /**
     * Handle form submission
     */
    async handleSubmit() {
        // Final validation
        await this.validateForm();

        if (!this.state.isValid) {
            this.manager.modules.notification?.error('Correggi gli errori prima di salvare');
            return;
        }

        try {
            const formData = this.getFormData();
            const scheduleId = this.getScheduleId();

            if (scheduleId) {
                await this.manager.updateSchedule(scheduleId, formData);
            } else {
                await this.manager.createSchedule(formData);
            }

            this.state.isDirty = false;
            this.state.hasUnsavedChanges = false;

        } catch (error) {
            this.log('Form submission failed:', error);
        }
    }

    /**
     * Get form data as FormData object
     */
    getFormData() {
        const formData = new FormData(this.form);

        // Add requirements
        const requirements = this.state.requirements
            .map(req => req.input.value)
            .filter(value => value.trim() !== '');

        requirements.forEach(requirement => {
            formData.append('requirements[]', requirement);
        });

        return formData;
    }

    /**
     * Get current schedule ID (for edit mode)
     */
    getScheduleId() {
        const idInput = this.form.querySelector('input[name="schedule_id"]');
        return idInput ? idInput.value : null;
    }

    /**
     * Mark form as dirty
     */
    markDirty() {
        this.state.isDirty = true;
        this.state.hasUnsavedChanges = true;
    }

    /**
     * Check if form has unsaved changes
     */
    hasUnsavedChanges() {
        return this.state.hasUnsavedChanges;
    }

    /**
     * Check if form is dirty
     */
    isFormDirty() {
        return this.state.isDirty;
    }

    /**
     * Cancel form editing
     */
    cancelEdit() {
        if (this.state.hasUnsavedChanges) {
            if (confirm('Ci sono modifiche non salvate. Vuoi davvero annullare?')) {
                window.history.back();
            }
        } else {
            window.history.back();
        }
    }

    /**
     * Save form manually
     */
    async save() {
        await this.handleSubmit();
    }

    /**
     * Debug logging
     */
    log(...args) {
        if (this.manager?.options.debug) {
            console.log('[ScheduleFormManager]', ...args);
        }
    }

    /**
     * Cleanup
     */
    destroy() {
        // Clear timers
        if (this.autoSaveTimer) {
            clearInterval(this.autoSaveTimer);
        }

        if (this.validationDebouncer) {
            clearTimeout(this.validationDebouncer);
        }

        // Clear references
        this.form = null;
        this.elements.clear();
        this.state.requirements = [];

        this.log('ScheduleFormManager destroyed');
    }
}