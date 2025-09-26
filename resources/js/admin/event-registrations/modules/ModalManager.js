/**
 * ModalManager - Handles modal functionality for event registration creation
 *
 * Features:
 * - Modal open/close with animations
 * - Form validation and submission
 * - Dynamic user loading based on event selection
 * - Keyboard shortcuts (Escape to close)
 * - Backdrop click to close
 */

export default class ModalManager {
    constructor(options = {}) {
        this.options = {
            modalSelector: '#addRegistrationModal',
            formSelector: '#addRegistrationForm',
            eventSelectSelector: '#modal_event_id',
            userSelectSelector: '#modal_user_id',
            onSubmit: () => {},
            onClose: () => {},
            onOpen: () => {},
            ...options
        };

        this.modal = null;
        this.form = null;
        this.isOpen = false;
        this.isSubmitting = false;

        this.init();
        console.log('[ModalManager] ✅ Modal manager initialized');
    }

    /**
     * Initialize modal manager
     */
    init() {
        this.modal = document.querySelector(this.options.modalSelector);
        this.form = document.querySelector(this.options.formSelector);

        if (!this.modal || !this.form) {
            console.error('[ModalManager] Modal or form not found');
            return;
        }

        this.bindEvents();
    }

    /**
     * Bind modal events
     */
    bindEvents() {
        // Form submission
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });

        // Event selection change
        const eventSelect = document.querySelector(this.options.eventSelectSelector);
        if (eventSelect) {
            eventSelect.addEventListener('change', (e) => {
                this.handleEventChange(e.target.value);
            });
        }

        // Close button
        const closeButtons = this.modal.querySelectorAll('[data-modal-close]');
        closeButtons.forEach(button => {
            button.addEventListener('click', () => this.close());
        });

        // Backdrop click
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.close();
            }
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (this.isOpen && e.key === 'Escape') {
                this.close();
            }
        });

        console.log('[ModalManager] 🎯 Event listeners attached');
    }

    /**
     * Open modal
     */
    open() {
        if (this.isOpen) return;

        this.isOpen = true;
        this.modal.classList.remove('hidden');
        this.modal.classList.add('flex');

        // Animation
        requestAnimationFrame(() => {
            this.modal.style.opacity = '1';
            const modalContent = this.modal.querySelector('div div');
            if (modalContent) {
                modalContent.style.transform = 'scale(1)';
            }
        });

        // Focus first input
        const firstInput = this.form.querySelector('select, input, textarea');
        if (firstInput) {
            setTimeout(() => firstInput.focus(), 100);
        }

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Callback
        this.options.onOpen();

        console.log('[ModalManager] 🚀 Modal opened');
    }

    /**
     * Close modal
     */
    close() {
        if (!this.isOpen) return;

        this.isOpen = false;

        // Animation out
        this.modal.style.opacity = '0';
        const modalContent = this.modal.querySelector('div div');
        if (modalContent) {
            modalContent.style.transform = 'scale(0.95)';
        }

        setTimeout(() => {
            this.modal.classList.add('hidden');
            this.modal.classList.remove('flex');
            this.resetForm();
        }, 200);

        // Restore body scroll
        document.body.style.overflow = '';

        // Callback
        this.options.onClose();

        console.log('[ModalManager] 👋 Modal closed');
    }

    /**
     * Handle form submission
     */
    async handleSubmit() {
        if (this.isSubmitting) return;

        const formData = this.validateAndGetFormData();
        if (!formData) return;

        this.isSubmitting = true;
        this.showSubmittingState();

        try {
            await this.options.onSubmit(formData);
        } catch (error) {
            console.error('[ModalManager] Submit error:', error);
            this.showSubmitError(error.message);
        } finally {
            this.isSubmitting = false;
            this.hideSubmittingState();
        }
    }

    /**
     * Validate form and get form data
     */
    validateAndGetFormData() {
        const formData = new FormData(this.form);
        const errors = [];

        // Required field validation
        const eventId = formData.get('event_id');
        const userId = formData.get('user_id');

        if (!eventId) {
            errors.push('Seleziona un evento');
        }

        if (!userId) {
            errors.push('Seleziona un utente');
        }

        // Show errors if any
        if (errors.length > 0) {
            this.showValidationErrors(errors);
            return null;
        }

        this.clearValidationErrors();
        return formData;
    }

    /**
     * Show validation errors
     */
    showValidationErrors(errors) {
        // Remove existing error display
        this.clearValidationErrors();

        // Create error container
        const errorContainer = document.createElement('div');
        errorContainer.className = 'validation-errors bg-red-50 border border-red-200 rounded-lg p-4 mb-4';
        errorContainer.innerHTML = `
            <div class="flex items-start">
                <svg class="w-5 h-5 text-red-500 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h4 class="text-red-800 font-medium mb-1">Errori di validazione:</h4>
                    <ul class="text-red-700 text-sm space-y-1">
                        ${errors.map(error => `<li>• ${error}</li>`).join('')}
                    </ul>
                </div>
            </div>
        `;

        // Insert before form
        this.form.parentNode.insertBefore(errorContainer, this.form);

        // Auto-hide after 5 seconds
        setTimeout(() => {
            this.clearValidationErrors();
        }, 5000);
    }

    /**
     * Clear validation errors
     */
    clearValidationErrors() {
        const existingErrors = this.modal.querySelectorAll('.validation-errors');
        existingErrors.forEach(error => error.remove());

        // Clear field error states
        const errorFields = this.form.querySelectorAll('.border-red-500');
        errorFields.forEach(field => {
            field.classList.remove('border-red-500');
        });
    }

    /**
     * Show submitting state
     */
    showSubmittingState() {
        const submitButton = this.form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = `
                <div class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Registrando...
                </div>
            `;
        }

        // Disable all form fields
        const formFields = this.form.querySelectorAll('input, select, textarea, button');
        formFields.forEach(field => {
            field.disabled = true;
        });
    }

    /**
     * Hide submitting state
     */
    hideSubmittingState() {
        const submitButton = this.form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Registra';
        }

        // Re-enable form fields
        const formFields = this.form.querySelectorAll('input, select, textarea, button');
        formFields.forEach(field => {
            field.disabled = false;
        });
    }

    /**
     * Show submit error
     */
    showSubmitError(message) {
        this.showValidationErrors([message]);
    }

    /**
     * Handle event selection change
     */
    async handleEventChange(eventId) {
        console.log('[ModalManager] 🔄 Event changed to:', eventId);

        const userSelect = document.querySelector(this.options.userSelectSelector);
        if (!userSelect) {
            console.warn('[ModalManager] ⚠️ User select not found');
            return;
        }

        if (!eventId) {
            this.clearUserOptions();
            return;
        }

        try {
            this.showUserLoading();
            console.log('[ModalManager] 📡 Fetching users for event:', eventId);
            const users = await this.fetchAvailableUsers(eventId);
            console.log('[ModalManager] 👥 Received users:', users.length);
            this.populateUserOptions(users);
        } catch (error) {
            console.error('[ModalManager] ❌ Error fetching users:', error);
            this.showUserError('Errore nel caricamento degli utenti');
        }
    }

    /**
     * Clear user options
     */
    clearUserOptions() {
        const userSelect = document.querySelector(this.options.userSelectSelector);
        if (userSelect) {
            userSelect.innerHTML = '<option value="">Seleziona utente...</option>';
            userSelect.disabled = false;
        }
    }

    /**
     * Show user loading state
     */
    showUserLoading() {
        const userSelect = document.querySelector(this.options.userSelectSelector);
        if (userSelect) {
            userSelect.innerHTML = '<option value="">Caricamento utenti...</option>';
            userSelect.disabled = true;
        }
    }

    /**
     * Show user error
     */
    showUserError(message) {
        const userSelect = document.querySelector(this.options.userSelectSelector);
        if (userSelect) {
            userSelect.innerHTML = `<option value="">${message}</option>`;
            userSelect.disabled = true;
        }
    }

    /**
     * Populate user options
     */
    populateUserOptions(users) {
        const userSelect = document.querySelector(this.options.userSelectSelector);
        if (!userSelect) return;

        let html = '<option value="">Seleziona utente...</option>';

        users.forEach(user => {
            html += `<option value="${user.id}">${user.name} (${user.email})</option>`;
        });

        userSelect.innerHTML = html;
        userSelect.disabled = false;

        console.log(`[ModalManager] 👥 Loaded ${users.length} users`);
    }

    /**
     * Fetch available users for event
     */
    async fetchAvailableUsers(eventId) {
        const response = await fetch(`/admin/event-registrations/event/${eventId}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        return data.users || [];
    }

    /**
     * Reset form to initial state
     */
    resetForm() {
        if (this.form) {
            this.form.reset();
            this.clearUserOptions();
            this.clearValidationErrors();
        }
    }

    /**
     * Pre-fill form with data
     */
    prefillForm(data) {
        Object.entries(data).forEach(([key, value]) => {
            const field = this.form.querySelector(`[name="${key}"]`);
            if (field) {
                field.value = value;

                // Trigger change event for selects
                if (field.tagName === 'SELECT') {
                    field.dispatchEvent(new Event('change'));
                }
            }
        });

        console.log('[ModalManager] 📝 Form pre-filled with data');
    }

    /**
     * Get form data as object
     */
    getFormData() {
        const formData = new FormData(this.form);
        const data = {};

        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        return data;
    }

    /**
     * Check if modal is open
     */
    isModalOpen() {
        return this.isOpen;
    }

    /**
     * Check if form is submitting
     */
    isFormSubmitting() {
        return this.isSubmitting;
    }

    /**
     * Add custom validation rule
     */
    addValidationRule(fieldName, validator, errorMessage) {
        if (!this.customValidators) {
            this.customValidators = new Map();
        }

        this.customValidators.set(fieldName, { validator, errorMessage });
        console.log(`[ModalManager] ➕ Added validation rule for ${fieldName}`);
    }

    /**
     * Remove custom validation rule
     */
    removeValidationRule(fieldName) {
        if (this.customValidators) {
            this.customValidators.delete(fieldName);
            console.log(`[ModalManager] ➖ Removed validation rule for ${fieldName}`);
        }
    }

    /**
     * Validate with custom rules
     */
    validateWithCustomRules(formData) {
        if (!this.customValidators) return [];

        const errors = [];

        this.customValidators.forEach((rule, fieldName) => {
            const fieldValue = formData.get(fieldName);
            if (!rule.validator(fieldValue)) {
                errors.push(rule.errorMessage);
            }
        });

        return errors;
    }

    /**
     * Destroy modal manager
     */
    destroy() {
        this.close();
        console.log('[ModalManager] 🔥 Modal manager destroyed');
    }
}