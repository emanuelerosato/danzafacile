/**
 * 📝 FORM MANAGER - Gestione Form Staff Avanzata
 *
 * Sostituisce le ~35 righe inline in create.blade.php
 *
 * Gestisce:
 * - Availability days visual selection
 * - Role-based field suggestions
 * - Real-time validation feedback
 * - Smart form hints
 * - Auto-save e draft mode
 */

export class FormManager {
    constructor(staffManager) {
        this.staffManager = staffManager;
        this.formState = {
            isDirty: false,
            isValidating: false,
            validationErrors: {},
            availabilityDays: [],
            currentRole: '',
            autoSaveEnabled: true
        };

        this.validationRules = {
            name: { required: true, minLength: 2, maxLength: 50 },
            email: { required: true, email: true },
            phone: { pattern: /^[\+]?[1-9][\d]{0,15}$/ },
            role: { required: true },
            hourly_rate: { min: 0, max: 1000, decimal: 2 }
        };

        this.autoSaveTimeout = null;
        this.validationTimeout = null;

        this.initialize();
        console.log('📝 FormManager initialized');
    }

    /**
     * Inizializzazione
     */
    initialize() {
        this.attachEventListeners();
        this.setupAvailabilityCalendar();
        this.setupRoleBasedFields();
        this.setupAutoSave();
        this.initializeValidation();
        this.setupFormHints();
    }

    /**
     * Registra event listeners
     */
    attachEventListeners() {
        // Form submission
        const staffForm = document.getElementById('staff-form');
        if (staffForm) {
            staffForm.addEventListener('submit', this.handleFormSubmit.bind(this));
        }

        // Input changes per validation
        const formInputs = document.querySelectorAll('#staff-form input, #staff-form select, #staff-form textarea');
        formInputs.forEach(input => {
            input.addEventListener('input', this.handleInputChange.bind(this));
            input.addEventListener('blur', this.handleInputBlur.bind(this));
            input.addEventListener('focus', this.handleInputFocus.bind(this));
        });

        // Role selection
        const roleSelect = document.getElementById('role');
        if (roleSelect) {
            roleSelect.addEventListener('change', this.handleRoleChange.bind(this));
        }

        // Availability days
        const availabilityCheckboxes = document.querySelectorAll('.availability-day');
        availabilityCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', this.handleAvailabilityChange.bind(this));
        });

        // Photo upload
        const photoInput = document.getElementById('photo');
        if (photoInput) {
            photoInput.addEventListener('change', this.handlePhotoUpload.bind(this));
        }

        // Form reset
        const resetButton = document.querySelector('[type="reset"]');
        if (resetButton) {
            resetButton.addEventListener('click', this.handleFormReset.bind(this));
        }

        // Window beforeunload per unsaved changes
        window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
    }

    // ==========================================
    // FORM SUBMISSION
    // ==========================================

    /**
     * Gestisce submit del form
     */
    async handleFormSubmit(event) {
        event.preventDefault();

        if (this.formState.isValidating) {
            this.staffManager.notificationManager.showWarning('Validazione in corso...');
            return;
        }

        // Validazione completa
        const isValid = await this.validateForm();
        if (!isValid) {
            this.staffManager.notificationManager.showError('Correggi gli errori nel form prima di continuare');
            this.focusFirstError();
            return;
        }

        // Mostra loading
        this.setFormLoading(true);

        try {
            const formData = this.collectFormData();
            const response = await this.submitFormData(formData);

            if (response.success) {
                this.handleSubmissionSuccess(response);
            } else {
                throw new Error(response.message || 'Errore durante il salvataggio');
            }

        } catch (error) {
            console.error('❌ Form submission error:', error);
            this.handleSubmissionError(error);
        } finally {
            this.setFormLoading(false);
        }
    }

    /**
     * Raccoglie dati dal form
     */
    collectFormData() {
        const form = document.getElementById('staff-form');
        const formData = new FormData(form);

        // Aggiungi availability days
        formData.append('availability_days', JSON.stringify(this.formState.availabilityDays));

        // Aggiungi metadata
        formData.append('_timestamp', Date.now());
        formData.append('_form_version', '2.0');

        return formData;
    }

    /**
     * Invia dati al server
     */
    async submitFormData(formData) {
        const form = document.getElementById('staff-form');
        const isEditing = form.dataset.staffId;
        const url = isEditing ? `/admin/staff/${form.dataset.staffId}` : '/admin/staff';
        const method = isEditing ? 'PUT' : 'POST';

        if (method === 'PUT') {
            formData.append('_method', 'PUT');
        }

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        return await response.json();
    }

    /**
     * Gestisce successo submission
     */
    handleSubmissionSuccess(response) {
        this.formState.isDirty = false;
        this.clearDraft();

        this.staffManager.notificationManager.showSuccess(
            response.message || 'Staff salvato con successo!'
        );

        // Redirect o aggiorna UI
        if (response.redirect) {
            setTimeout(() => {
                window.location.href = response.redirect;
            }, 1500);
        } else if (response.data) {
            this.updateFormWithData(response.data);
        }
    }

    /**
     * Gestisce errore submission
     */
    handleSubmissionError(error) {
        // Gestisce errori di validazione dal server
        if (error.errors) {
            this.displayValidationErrors(error.errors);
        }

        this.staffManager.notificationManager.showError(
            error.message || 'Errore durante il salvataggio'
        );
    }

    // ==========================================
    // VALIDATION
    // ==========================================

    /**
     * Inizializza sistema di validazione
     */
    initializeValidation() {
        // Crea container per messaggi di errore
        this.createValidationUI();

        // Setup validazione real-time
        this.setupRealTimeValidation();
    }

    /**
     * Gestisce cambiamento input
     */
    handleInputChange(event) {
        const input = event.target;
        this.formState.isDirty = true;

        // Clear previous validation timeout
        if (this.validationTimeout) {
            clearTimeout(this.validationTimeout);
        }

        // Debounced validation
        this.validationTimeout = setTimeout(() => {
            this.validateField(input);
        }, 500);

        // Auto-save
        this.scheduleAutoSave();

        // Update hints
        this.updateFieldHints(input);
    }

    /**
     * Gestisce blur su input
     */
    handleInputBlur(event) {
        const input = event.target;
        this.validateField(input);
    }

    /**
     * Gestisce focus su input
     */
    handleInputFocus(event) {
        const input = event.target;
        this.showFieldHints(input);
        this.clearFieldError(input);
    }

    /**
     * Valida singolo campo
     */
    async validateField(input) {
        const fieldName = input.name;
        const value = input.value.trim();
        const rules = this.validationRules[fieldName];

        if (!rules) return true;

        const errors = [];

        // Required validation
        if (rules.required && !value) {
            errors.push('Campo obbligatorio');
        }

        // Length validations
        if (value && rules.minLength && value.length < rules.minLength) {
            errors.push(`Minimo ${rules.minLength} caratteri`);
        }

        if (value && rules.maxLength && value.length > rules.maxLength) {
            errors.push(`Massimo ${rules.maxLength} caratteri`);
        }

        // Email validation
        if (value && rules.email && !this.isValidEmail(value)) {
            errors.push('Email non valida');
        }

        // Pattern validation
        if (value && rules.pattern && !rules.pattern.test(value)) {
            errors.push('Formato non valido');
        }

        // Numeric validations
        if (value && rules.min !== undefined) {
            const numValue = parseFloat(value);
            if (numValue < rules.min) {
                errors.push(`Minimo ${rules.min}`);
            }
        }

        if (value && rules.max !== undefined) {
            const numValue = parseFloat(value);
            if (numValue > rules.max) {
                errors.push(`Massimo ${rules.max}`);
            }
        }

        // Custom async validations
        if (value && rules.custom) {
            const customErrors = await rules.custom(value, input);
            if (customErrors) errors.push(...customErrors);
        }

        // Server-side unique validation per email
        if (fieldName === 'email' && value && !errors.length) {
            const uniqueError = await this.validateEmailUnique(value, input);
            if (uniqueError) errors.push(uniqueError);
        }

        // Update UI
        if (errors.length > 0) {
            this.showFieldError(input, errors[0]);
            this.formState.validationErrors[fieldName] = errors;
            return false;
        } else {
            this.clearFieldError(input);
            delete this.formState.validationErrors[fieldName];
            return true;
        }
    }

    /**
     * Valida tutto il form
     */
    async validateForm() {
        this.formState.isValidating = true;
        const formInputs = document.querySelectorAll('#staff-form input, #staff-form select, #staff-form textarea');
        const validationPromises = [];

        formInputs.forEach(input => {
            if (input.name) {
                validationPromises.push(this.validateField(input));
            }
        });

        const results = await Promise.all(validationPromises);
        this.formState.isValidating = false;

        return results.every(result => result === true);
    }

    /**
     * Verifica unicità email
     */
    async validateEmailUnique(email, input) {
        try {
            const formElement = document.getElementById('staff-form');
            const staffId = formElement.dataset.staffId;

            const response = await fetch('/admin/staff/validate-email', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    email: email,
                    exclude_id: staffId || null
                })
            });

            const data = await response.json();

            if (!data.unique) {
                return 'Email già in uso';
            }

            return null;

        } catch (error) {
            console.warn('Email validation error:', error);
            return null;
        }
    }

    /**
     * Utility email validation
     */
    isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // ==========================================
    // AVAILABILITY CALENDAR
    // ==========================================

    /**
     * Setup calendario disponibilità
     */
    setupAvailabilityCalendar() {
        const availabilityContainer = document.querySelector('.availability-container');
        if (!availabilityContainer) return;

        // Inizializza giorni selezionati
        const selectedDays = document.querySelectorAll('.availability-day:checked');
        this.formState.availabilityDays = Array.from(selectedDays).map(cb => cb.value);

        // Crea UI calendario visuale
        this.createVisualCalendar();
    }

    /**
     * Crea calendario visuale
     */
    createVisualCalendar() {
        const container = document.querySelector('.availability-visual');
        if (!container) return;

        const days = [
            { key: 'monday', label: 'Lun', full: 'Lunedì' },
            { key: 'tuesday', label: 'Mar', full: 'Martedì' },
            { key: 'wednesday', label: 'Mer', full: 'Mercoledì' },
            { key: 'thursday', label: 'Gio', full: 'Giovedì' },
            { key: 'friday', label: 'Ven', full: 'Venerdì' },
            { key: 'saturday', label: 'Sab', full: 'Sabato' },
            { key: 'sunday', label: 'Dom', full: 'Domenica' }
        ];

        container.innerHTML = days.map(day => `
            <div class="availability-day-visual ${this.formState.availabilityDays.includes(day.key) ? 'selected' : ''}"
                 data-day="${day.key}"
                 title="${day.full}">
                <span class="day-label">${day.label}</span>
                <div class="day-indicator"></div>
            </div>
        `).join('');

        // Attach click handlers
        container.querySelectorAll('.availability-day-visual').forEach(dayElement => {
            dayElement.addEventListener('click', (e) => {
                const day = e.currentTarget.dataset.day;
                this.toggleAvailabilityDay(day);
            });
        });

        // Stili CSS
        this.addAvailabilityStyles();
    }

    /**
     * Gestisce cambiamento disponibilità
     */
    handleAvailabilityChange(event) {
        const day = event.target.value;
        const isChecked = event.target.checked;

        if (isChecked) {
            if (!this.formState.availabilityDays.includes(day)) {
                this.formState.availabilityDays.push(day);
            }
        } else {
            this.formState.availabilityDays = this.formState.availabilityDays.filter(d => d !== day);
        }

        // Aggiorna calendario visuale
        this.updateVisualCalendar();
        this.formState.isDirty = true;
        this.scheduleAutoSave();

        console.log('📅 Availability updated:', this.formState.availabilityDays);
    }

    /**
     * Toggle giorno disponibilità
     */
    toggleAvailabilityDay(day) {
        const checkbox = document.querySelector(`.availability-day[value="${day}"]`);
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            this.handleAvailabilityChange({ target: checkbox });
        }
    }

    /**
     * Aggiorna calendario visuale
     */
    updateVisualCalendar() {
        const dayElements = document.querySelectorAll('.availability-day-visual');
        dayElements.forEach(element => {
            const day = element.dataset.day;
            const isSelected = this.formState.availabilityDays.includes(day);
            element.classList.toggle('selected', isSelected);
        });
    }

    /**
     * Aggiunge stili per calendario
     */
    addAvailabilityStyles() {
        if (document.getElementById('availability-styles')) return;

        const style = document.createElement('style');
        style.id = 'availability-styles';
        style.textContent = `
            .availability-visual {
                display: grid;
                grid-template-columns: repeat(7, 1fr);
                gap: 8px;
                margin-top: 16px;
            }

            .availability-day-visual {
                display: flex;
                flex-direction: column;
                align-items: center;
                padding: 12px 8px;
                border: 2px solid #e5e7eb;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s ease-in-out;
                background: white;
            }

            .availability-day-visual:hover {
                border-color: #f59e0b;
                transform: translateY(-2px);
                shadow: 0 4px 12px rgba(0,0,0,0.1);
            }

            .availability-day-visual.selected {
                border-color: #059669;
                background: #ecfdf5;
                color: #059669;
            }

            .availability-day-visual.selected .day-indicator {
                background: #059669;
                transform: scale(1);
            }

            .day-label {
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                margin-bottom: 4px;
            }

            .day-indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                background: #d1d5db;
                transform: scale(0.5);
                transition: all 0.2s ease-in-out;
            }
        `;
        document.head.appendChild(style);
    }

    // ==========================================
    // ROLE-BASED FUNCTIONALITY
    // ==========================================

    /**
     * Setup campi basati su ruolo
     */
    setupRoleBasedFields() {
        const roleSelect = document.getElementById('role');
        if (roleSelect && roleSelect.value) {
            this.handleRoleChange({ target: roleSelect });
        }
    }

    /**
     * Gestisce cambio ruolo
     */
    handleRoleChange(event) {
        const newRole = event.target.value;
        const oldRole = this.formState.currentRole;
        this.formState.currentRole = newRole;

        console.log(`👔 Role changed: ${oldRole} → ${newRole}`);

        // Aggiorna campi visibili
        this.updateRoleBasedFields(newRole);

        // Suggerimenti basati su ruolo
        this.showRoleBasedSuggestions(newRole);

        // Aggiorna validazioni
        this.updateRoleBasedValidation(newRole);

        this.formState.isDirty = true;
        this.scheduleAutoSave();
    }

    /**
     * Aggiorna campi basati su ruolo
     */
    updateRoleBasedFields(role) {
        const roleBasedFields = {
            teacher: ['specialties', 'hourly_rate', 'certifications'],
            admin: ['permissions', 'access_level'],
            receptionist: ['shift_hours', 'languages'],
            maintenance: ['skills', 'availability']
        };

        // Nascondi tutti i campi speciali
        Object.values(roleBasedFields).flat().forEach(field => {
            const fieldElement = document.querySelector(`[data-role-field="${field}"]`);
            if (fieldElement) {
                fieldElement.style.display = 'none';
                fieldElement.classList.remove('required');
            }
        });

        // Mostra campi per ruolo selezionato
        const fieldsToShow = roleBasedFields[role] || [];
        fieldsToShow.forEach(field => {
            const fieldElement = document.querySelector(`[data-role-field="${field}"]`);
            if (fieldElement) {
                fieldElement.style.display = 'block';

                // Aggiungi required se necessario
                if (this.isFieldRequiredForRole(field, role)) {
                    fieldElement.classList.add('required');
                }
            }
        });
    }

    /**
     * Mostra suggerimenti per ruolo
     */
    showRoleBasedSuggestions(role) {
        const suggestions = {
            teacher: {
                hourly_rate: '25-50',
                specialties: 'Danza Classica, Moderna, Hip-Hop'
            },
            admin: {
                access_level: 'full'
            },
            receptionist: {
                shift_hours: '9:00-17:00',
                languages: 'Italiano, Inglese'
            }
        };

        const roleSuggestions = suggestions[role];
        if (!roleSuggestions) return;

        Object.entries(roleSuggestions).forEach(([field, suggestion]) => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input && !input.value) {
                input.placeholder = `es. ${suggestion}`;
                input.setAttribute('data-suggestion', suggestion);
            }
        });
    }

    /**
     * Verifica se campo è obbligatorio per ruolo
     */
    isFieldRequiredForRole(field, role) {
        const requiredFields = {
            teacher: ['hourly_rate', 'specialties'],
            admin: ['access_level']
        };

        return (requiredFields[role] || []).includes(field);
    }

    /**
     * Aggiorna validazioni per ruolo
     */
    updateRoleBasedValidation(role) {
        // Aggiorna regole di validazione dinamiche
        if (role === 'teacher') {
            this.validationRules.hourly_rate = { required: true, min: 10, max: 200 };
            this.validationRules.specializations = { required: true, minLength: 3 };
        } else {
            delete this.validationRules.hourly_rate;
            delete this.validationRules.specializations;
        }

        // Re-valida campi interessati
        const affectedFields = document.querySelectorAll('[name="hourly_rate"], [name="specializations"]');
        affectedFields.forEach(field => this.validateField(field));
    }

    // ==========================================
    // AUTO-SAVE & DRAFTS
    // ==========================================

    /**
     * Setup auto-save
     */
    setupAutoSave() {
        // Ripristina draft se disponibile
        this.restoreDraft();

        // Setup auto-save periodico
        setInterval(() => {
            if (this.formState.isDirty && this.formState.autoSaveEnabled) {
                this.saveDraft();
            }
        }, 30000); // Ogni 30 secondi
    }

    /**
     * Schedula auto-save
     */
    scheduleAutoSave() {
        if (!this.formState.autoSaveEnabled) return;

        if (this.autoSaveTimeout) {
            clearTimeout(this.autoSaveTimeout);
        }

        this.autoSaveTimeout = setTimeout(() => {
            this.saveDraft();
        }, 2000); // 2 secondi di inattività
    }

    /**
     * Salva draft
     */
    saveDraft() {
        try {
            const formData = this.getFormDataForDraft();
            const draftKey = this.getDraftKey();

            localStorage.setItem(draftKey, JSON.stringify({
                data: formData,
                timestamp: Date.now(),
                version: '1.0'
            }));

            this.showDraftSavedIndicator();
            console.log('💾 Draft saved automatically');

        } catch (error) {
            console.warn('Cannot save draft:', error);
        }
    }

    /**
     * Ripristina draft
     */
    restoreDraft() {
        try {
            const draftKey = this.getDraftKey();
            const draft = localStorage.getItem(draftKey);

            if (draft) {
                const draftData = JSON.parse(draft);
                const age = Date.now() - draftData.timestamp;

                // Draft valido solo per 24 ore
                if (age < 24 * 60 * 60 * 1000) {
                    this.showDraftRestorePrompt(draftData);
                } else {
                    localStorage.removeItem(draftKey);
                }
            }

        } catch (error) {
            console.warn('Cannot restore draft:', error);
        }
    }

    /**
     * Mostra prompt per ripristino draft
     */
    async showDraftRestorePrompt(draftData) {
        const confirmed = await this.staffManager.notificationManager.showConfirmation(
            'Ripristina Bozza',
            `È stata trovata una bozza salvata automaticamente il ${new Date(draftData.timestamp).toLocaleString()}. Vuoi ripristinarla?`,
            'Ripristina',
            'Ignora',
            'info'
        );

        if (confirmed) {
            this.restoreFormFromDraft(draftData.data);
            this.staffManager.notificationManager.showSuccess('Bozza ripristinata!');
        } else {
            this.clearDraft();
        }
    }

    /**
     * Ottiene chiave per draft
     */
    getDraftKey() {
        const form = document.getElementById('staff-form');
        const staffId = form.dataset.staffId;
        return staffId ? `staff-draft-${staffId}` : 'staff-draft-new';
    }

    /**
     * Ottiene dati form per draft
     */
    getFormDataForDraft() {
        const form = document.getElementById('staff-form');
        const formData = new FormData(form);
        const data = {};

        for (const [key, value] of formData.entries()) {
            data[key] = value;
        }

        data.availability_days = this.formState.availabilityDays;
        return data;
    }

    /**
     * Ripristina form da draft
     */
    restoreFormFromDraft(draftData) {
        Object.entries(draftData).forEach(([key, value]) => {
            if (key === 'availability_days') {
                // Ripristina availability days
                this.formState.availabilityDays = value || [];
                this.updateAvailabilityCheckboxes();
                this.updateVisualCalendar();
            } else {
                const input = document.querySelector(`[name="${key}"]`);
                if (input) {
                    input.value = value;
                    this.handleInputChange({ target: input });
                }
            }
        });

        this.formState.isDirty = true;
    }

    /**
     * Aggiorna checkbox availability
     */
    updateAvailabilityCheckboxes() {
        const checkboxes = document.querySelectorAll('.availability-day');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.formState.availabilityDays.includes(checkbox.value);
        });
    }

    /**
     * Pulisce draft
     */
    clearDraft() {
        const draftKey = this.getDraftKey();
        localStorage.removeItem(draftKey);
    }

    /**
     * Mostra indicatore draft salvato
     */
    showDraftSavedIndicator() {
        const indicator = document.querySelector('.draft-saved-indicator');
        if (indicator) {
            indicator.style.display = 'block';
            indicator.textContent = `💾 Bozza salvata ${new Date().toLocaleTimeString()}`;

            setTimeout(() => {
                indicator.style.display = 'none';
            }, 3000);
        }
    }

    // ==========================================
    // UI HELPERS
    // ==========================================

    /**
     * Mostra errore campo
     */
    showFieldError(input, message) {
        // Rimuovi errori precedenti
        this.clearFieldError(input);

        // Aggiungi classe errore
        input.classList.add('border-red-300', 'bg-red-50', 'focus:ring-red-500', 'focus:border-red-500');
        input.classList.remove('border-gray-300', 'focus:ring-rose-500', 'focus:border-rose-500');

        // Crea messaggio errore
        const errorElement = document.createElement('p');
        errorElement.className = 'field-error text-sm text-red-600 mt-1';
        errorElement.textContent = message;

        // Inserisci dopo il campo
        input.parentNode.appendChild(errorElement);
    }

    /**
     * Pulisce errore campo
     */
    clearFieldError(input) {
        // Rimuovi classi errore
        input.classList.remove('border-red-300', 'bg-red-50', 'focus:ring-red-500', 'focus:border-red-500');
        input.classList.add('border-gray-300', 'focus:ring-rose-500', 'focus:border-rose-500');

        // Rimuovi messaggio errore
        const errorElement = input.parentNode.querySelector('.field-error');
        if (errorElement) {
            errorElement.remove();
        }
    }

    /**
     * Focus primo errore
     */
    focusFirstError() {
        const firstErrorField = document.querySelector('.border-red-300');
        if (firstErrorField) {
            firstErrorField.focus();
            firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }

    /**
     * Imposta stato loading form
     */
    setFormLoading(isLoading) {
        const submitButton = document.querySelector('#staff-form [type="submit"]');
        const form = document.getElementById('staff-form');

        if (isLoading) {
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = `
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white mr-2"></div>
                    Salvataggio...
                `;
            }
            if (form) form.classList.add('opacity-75', 'pointer-events-none');
        } else {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Salva Staff';
            }
            if (form) form.classList.remove('opacity-75', 'pointer-events-none');
        }
    }

    // ==========================================
    // EVENT HANDLERS
    // ==========================================

    /**
     * Gestisce upload foto
     */
    handlePhotoUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validazione file
        const validTypes = ['image/jpeg', 'image/png', 'image/webp'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!validTypes.includes(file.type)) {
            this.staffManager.notificationManager.showError('Formato non supportato. Usa JPG, PNG o WebP.');
            event.target.value = '';
            return;
        }

        if (file.size > maxSize) {
            this.staffManager.notificationManager.showError('File troppo grande. Massimo 5MB.');
            event.target.value = '';
            return;
        }

        // Preview immagine
        this.showImagePreview(file);

        this.formState.isDirty = true;
        this.scheduleAutoSave();
    }

    /**
     * Mostra preview immagine
     */
    showImagePreview(file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            const preview = document.querySelector('.photo-preview');
            if (preview) {
                preview.innerHTML = `
                    <img src="${e.target.result}" alt="Preview" class="w-32 h-32 object-cover rounded-lg shadow">
                    <button type="button" onclick="window.staffFormManager.removePhotoPreview()"
                            class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600 transition-colors">
                        ×
                    </button>
                `;
                preview.style.display = 'block';
            }
        };
        reader.readAsDataURL(file);
    }

    /**
     * Rimuove preview foto
     */
    removePhotoPreview() {
        const photoInput = document.getElementById('photo');
        const preview = document.querySelector('.photo-preview');

        if (photoInput) photoInput.value = '';
        if (preview) {
            preview.innerHTML = '';
            preview.style.display = 'none';
        }

        this.formState.isDirty = true;
    }

    /**
     * Gestisce reset form
     */
    handleFormReset(event) {
        if (this.formState.isDirty) {
            const confirmed = confirm('Sei sicuro di voler resettare il form? Tutte le modifiche verranno perse.');
            if (!confirmed) {
                event.preventDefault();
                return;
            }
        }

        // Reset stato
        this.formState.isDirty = false;
        this.formState.validationErrors = {};
        this.formState.availabilityDays = [];

        // Pulisci errori
        document.querySelectorAll('.field-error').forEach(error => error.remove());

        // Reset visual calendar
        this.updateVisualCalendar();

        // Pulisci draft
        this.clearDraft();

        this.staffManager.notificationManager.showInfo('Form resettato');
    }

    /**
     * Gestisce beforeunload
     */
    handleBeforeUnload(event) {
        if (this.formState.isDirty) {
            event.preventDefault();
            return 'Ci sono modifiche non salvate. Sei sicuro di voler uscire?';
        }
    }

    // ==========================================
    // PUBLIC METHODS
    // ==========================================

    /**
     * Apre modal staff (per integrazione)
     */
    openStaffModal(staffId = null) {
        // Implementazione per modal se necessario
        console.log('📝 Opening staff modal for:', staffId || 'new staff');
    }

    /**
     * Setup hints per i campi
     */
    setupFormHints() {
        // Implementazione per suggerimenti e tooltip
    }

    /**
     * Mostra hints per campo
     */
    showFieldHints(input) {
        // Implementazione per hints contestuali
    }

    /**
     * Aggiorna hints campo
     */
    updateFieldHints(input) {
        // Implementazione per aggiornamento hints in real-time
    }

    /**
     * Crea UI per validazione
     */
    createValidationUI() {
        // Implementazione per UI messaggi validazione
    }

    /**
     * Setup validazione real-time
     */
    setupRealTimeValidation() {
        // Implementazione per validazione in tempo reale
    }

    /**
     * Mostra errori validazione server
     */
    displayValidationErrors(errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            const input = document.querySelector(`[name="${field}"]`);
            if (input && messages.length > 0) {
                this.showFieldError(input, messages[0]);
            }
        });
    }

    /**
     * Aggiorna form con dati
     */
    updateFormWithData(data) {
        // Implementazione per aggiornamento form con dati server
    }
}

// Export globale
export default FormManager;