/**
 * BulkActionManager - Gestione azioni multiple
 * Gestisce marcature multiple e operazioni bulk
 *
 * FASE 2: JavaScript Modernization
 */
export class BulkActionManager {
    constructor(apiService, notificationManager) {
        this.apiService = apiService;
        this.notification = notificationManager;
        this.isProcessing = false;
        this.bulkMarkData = {
            date: new Date().toISOString().split('T')[0],
            type: '',
            subject_id: '',
            subjects: [],
            defaultStatus: 'present'
        };
        this.courses = [];
        this.events = [];
        console.log('📦 BulkActionManager initialized');
    }

    /**
     * Inizializza con dati corsi ed eventi
     */
    initializeSubjects(courses, events) {
        this.courses = courses || [];
        this.events = events || [];
        console.log(`📦 Subjects initialized: ${this.courses.length} courses, ${this.events.length} events`);
    }

    /**
     * Apri modal marcatura multipla
     */
    openBulkMarkModal() {
        console.log('📦 Opening bulk mark modal');

        // Reset data
        this.bulkMarkData = {
            date: new Date().toISOString().split('T')[0],
            type: '',
            subject_id: '',
            subjects: [],
            defaultStatus: 'present'
        };

        // Aggiorna UI modal
        this.updateModalUI();

        // Mostra modal
        const modal = document.getElementById('bulkMarkModal');
        if (modal) {
            modal.style.display = 'block';
            modal.classList.remove('hidden');

            // Focus su primo input
            const firstInput = modal.querySelector('input[type="date"]');
            if (firstInput) {
                setTimeout(() => firstInput.focus(), 100);
            }
        }
    }

    /**
     * Chiudi modal marcatura multipla
     */
    closeBulkMarkModal() {
        console.log('📦 Closing bulk mark modal');

        const modal = document.getElementById('bulkMarkModal');
        if (modal) {
            modal.style.display = 'none';
            modal.classList.add('hidden');
        }

        // Reset form
        this.resetBulkMarkData();
    }

    /**
     * Reset dati marcatura multipla
     */
    resetBulkMarkData() {
        this.bulkMarkData = {
            date: new Date().toISOString().split('T')[0],
            type: '',
            subject_id: '',
            subjects: [],
            defaultStatus: 'present'
        };
        this.updateModalUI();
    }

    /**
     * Aggiorna opzioni soggetti (course/event)
     */
    updateSubjectOptions() {
        console.log('📦 Updating subject options for type:', this.bulkMarkData.type);

        if (this.bulkMarkData.type === 'course') {
            this.bulkMarkData.subjects = this.courses;
        } else if (this.bulkMarkData.type === 'event') {
            this.bulkMarkData.subjects = this.events;
        } else {
            this.bulkMarkData.subjects = [];
        }

        // Reset subject_id quando cambia tipo
        this.bulkMarkData.subject_id = '';

        // Aggiorna select UI
        this.updateSubjectSelectUI();
    }

    /**
     * Aggiorna UI select soggetti
     */
    updateSubjectSelectUI() {
        const subjectSelect = document.getElementById('bulk_subject_id');
        if (!subjectSelect) return;

        // Pulisci opzioni
        subjectSelect.innerHTML = '<option value="">Seleziona...</option>';

        // Aggiungi nuove opzioni
        this.bulkMarkData.subjects.forEach(subject => {
            const option = document.createElement('option');
            option.value = subject.id;
            option.textContent = subject.name;
            subjectSelect.appendChild(option);
        });

        // Abilita/disabilita select
        subjectSelect.disabled = this.bulkMarkData.subjects.length === 0;
    }

    /**
     * Aggiorna UI modal completa
     */
    updateModalUI() {
        // Data input
        const dateInput = document.getElementById('bulk_date');
        if (dateInput) {
            dateInput.value = this.bulkMarkData.date;
        }

        // Type select
        const typeSelect = document.getElementById('bulk_type');
        if (typeSelect) {
            typeSelect.value = this.bulkMarkData.type;
        }

        // Default status
        const statusSelect = document.getElementById('bulk_default_status');
        if (statusSelect) {
            statusSelect.value = this.bulkMarkData.defaultStatus;
        }

        // Aggiorna subject select
        this.updateSubjectSelectUI();
    }

    /**
     * Invia marcatura multipla
     */
    async submitBulkMark() {
        if (this.isProcessing) {
            console.log('⏳ Bulk mark already in progress');
            return;
        }

        console.log('📦 Submitting bulk mark:', this.bulkMarkData);

        // Validazione
        const validation = this.validateBulkMarkData();
        if (!validation.isValid) {
            validation.errors.forEach(error => {
                this.notification.showWarning(error);
            });
            return;
        }

        try {
            this.isProcessing = true;
            this.setSubmitButtonLoading(true);

            // Naviga alla pagina dedicata per bulk marking
            // Questo approccio è più semplice e user-friendly
            const params = new URLSearchParams({
                date: this.bulkMarkData.date,
                type: this.bulkMarkData.type,
                subject_id: this.bulkMarkData.subject_id,
                default_status: this.bulkMarkData.defaultStatus
            });

            const route = this.bulkMarkData.type === 'course'
                ? `/admin/attendance/course/${this.bulkMarkData.subject_id}?${params.toString()}`
                : `/admin/attendance/event/${this.bulkMarkData.subject_id}?${params.toString()}`;

            console.log('📦 Redirecting to bulk marking interface:', route);

            // Chiudi modal prima di navigare
            this.closeBulkMarkModal();

            // Naviga
            window.location.href = route;

        } catch (error) {
            console.error('❌ Bulk mark error:', error);
            this.notification.showError(
                'Errore durante la marcatura multipla: ' + error.message
            );
        } finally {
            this.isProcessing = false;
            this.setSubmitButtonLoading(false);
        }
    }

    /**
     * Validazione dati marcatura multipla
     */
    validateBulkMarkData() {
        const errors = [];

        // Data richiesta
        if (!this.bulkMarkData.date) {
            errors.push('La data è obbligatoria');
        }

        // Tipo richiesto
        if (!this.bulkMarkData.type) {
            errors.push('Seleziona il tipo (Corso o Evento)');
        }

        // Soggetto richiesto
        if (!this.bulkMarkData.subject_id) {
            errors.push('Seleziona un corso o evento specifico');
        }

        // Data non futura (opzionale warning)
        if (this.bulkMarkData.date) {
            const selectedDate = new Date(this.bulkMarkData.date);
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);

            if (selectedDate > tomorrow) {
                errors.push('La data selezionata è nel futuro');
            }
        }

        return {
            isValid: errors.length === 0,
            errors: errors
        };
    }

    /**
     * Imposta stato loading per submit button
     */
    setSubmitButtonLoading(isLoading) {
        const submitBtn = document.getElementById('bulk_submit_btn');
        if (!submitBtn) return;

        if (isLoading) {
            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-50', 'cursor-not-allowed');

            const originalText = submitBtn.textContent;
            submitBtn.dataset.originalText = originalText;
            submitBtn.innerHTML = `
                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Processando...
            `;
        } else {
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-50', 'cursor-not-allowed');

            if (submitBtn.dataset.originalText) {
                submitBtn.textContent = submitBtn.dataset.originalText;
            }
        }
    }

    /**
     * Quick bulk actions
     */
    quickActions = {
        // Segna tutti presenti per oggi
        markAllPresentToday: async () => {
            this.bulkMarkData.date = new Date().toISOString().split('T')[0];
            this.bulkMarkData.defaultStatus = 'present';
            this.openBulkMarkModal();
        },

        // Prepara marcatura per corso specifico
        prepareForCourse: (courseId) => {
            this.bulkMarkData.type = 'course';
            this.bulkMarkData.subject_id = courseId;
            this.updateSubjectOptions();
            this.openBulkMarkModal();
        },

        // Prepara marcatura per evento specifico
        prepareForEvent: (eventId) => {
            this.bulkMarkData.type = 'event';
            this.bulkMarkData.subject_id = eventId;
            this.updateSubjectOptions();
            this.openBulkMarkModal();
        }
    };

    /**
     * Gestione eventi UI
     */
    bindUIEvents() {
        // Date change
        const dateInput = document.getElementById('bulk_date');
        if (dateInput) {
            dateInput.addEventListener('change', (e) => {
                this.bulkMarkData.date = e.target.value;
            });
        }

        // Type change
        const typeSelect = document.getElementById('bulk_type');
        if (typeSelect) {
            typeSelect.addEventListener('change', (e) => {
                this.bulkMarkData.type = e.target.value;
                this.updateSubjectOptions();
            });
        }

        // Subject change
        const subjectSelect = document.getElementById('bulk_subject_id');
        if (subjectSelect) {
            subjectSelect.addEventListener('change', (e) => {
                this.bulkMarkData.subject_id = e.target.value;
            });
        }

        // Default status change
        const statusSelect = document.getElementById('bulk_default_status');
        if (statusSelect) {
            statusSelect.addEventListener('change', (e) => {
                this.bulkMarkData.defaultStatus = e.target.value;
            });
        }

        // Submit button
        const submitBtn = document.getElementById('bulk_submit_btn');
        if (submitBtn) {
            submitBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.submitBulkMark();
            });
        }

        // Cancel button
        const cancelBtn = document.getElementById('bulk_cancel_btn');
        if (cancelBtn) {
            cancelBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.closeBulkMarkModal();
            });
        }

        // Modal backdrop click
        const modal = document.getElementById('bulkMarkModal');
        if (modal) {
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    this.closeBulkMarkModal();
                }
            });
        }

        // ESC key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !modal?.classList.contains('hidden')) {
                this.closeBulkMarkModal();
            }
        });

        console.log('📦 UI events bound');
    }

    /**
     * Aggiorna bulk mark data
     */
    updateBulkMarkData(key, value) {
        if (this.bulkMarkData.hasOwnProperty(key)) {
            this.bulkMarkData[key] = value;
            console.log(`📦 Bulk mark data updated: ${key} = ${value}`);

            // Trigger UI update se necessario
            if (key === 'type') {
                this.updateSubjectOptions();
            }
        }
    }

    /**
     * Ottieni stato corrente
     */
    getBulkMarkData() {
        return { ...this.bulkMarkData };
    }
}