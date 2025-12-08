/**
 * 🔘 SELECTION MANAGER - Gestione Multi-selezione Staff
 *
 * Sostituisce le ~25 righe inline in index.blade.php (righe 321-341)
 *
 * Gestisce:
 * - Select All/None functionality
 * - Indeterminate state management
 * - Persistent selection across actions
 * - Visual feedback animations
 * - Selection count display
 */

export class SelectionManager {
    constructor(staffManager) {
        this.staffManager = staffManager;
        this.selectedItems = [];
        this.isSelectAllIndeterminate = false;

        this.initialize();
        console.log('🔘 SelectionManager initialized');
    }

    /**
     * Inizializza event listeners e UI
     */
    initialize() {
        this.attachEventListeners();
        this.initializeSelectionUI();
        this.updateSelectionCounter();
    }

    /**
     * Registra event listeners
     */
    attachEventListeners() {
        // Select All checkbox - usa event delegation per supportare checkbox che potrebbero non esistere ancora
        document.addEventListener('change', (event) => {
            if (event.target.matches('#select-all-staff')) {
                this.handleSelectAll(event);
            }
        });

        // Individual checkboxes
        this.attachIndividualCheckboxListeners();

        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboardShortcuts.bind(this));

        console.log('✅ Event listeners attached successfully');
    }

    /**
     * Attacca listeners ai checkbox individuali usando event delegation
     */
    attachIndividualCheckboxListeners() {
        // Usa event delegation per gestire checkbox dinamiche
        document.addEventListener('change', (event) => {
            if (event.target.matches('.staff-checkbox')) {
                this.handleIndividualSelection(event);
            }
        });

        console.log('✅ Individual checkbox listeners attached via event delegation');
    }

    /**
     * Inizializza UI per la selezione
     */
    initializeSelectionUI() {
        // Aggiunge classi CSS per feedback visivo
        const style = document.createElement('style');
        style.textContent = `
            .staff-row-selected {
                background-color: #fef3c7 !important;
                border-color: #f59e0b !important;
                transform: scale(1.01);
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            }

            .staff-row {
                transition: all 0.2s ease-in-out;
            }

            .selection-counter {
                transition: all 0.3s ease-in-out;
                transform: translateY(0);
            }

            .selection-counter.show {
                transform: translateY(0);
                opacity: 1;
            }

            .selection-counter.hide {
                transform: translateY(-10px);
                opacity: 0;
            }

            .checkbox-animate {
                transition: all 0.15s ease-in-out;
            }

            .checkbox-animate:checked {
                transform: scale(1.1);
            }
        `;
        document.head.appendChild(style);
    }

    // ==========================================
    // SELECTION HANDLERS
    // ==========================================

    /**
     * Gestisce Select All/None
     */
    handleSelectAll(event) {
        console.log('🔘 handleSelectAll called:', event.target.checked);
        const isChecked = event.target.checked;
        const individualCheckboxes = document.querySelectorAll('.staff-checkbox');
        console.log('📋 Found', individualCheckboxes.length, 'staff checkboxes');

        // Visual feedback immediato
        this.showSelectionAnimation(event.target);

        individualCheckboxes.forEach(checkbox => {
            if (checkbox.checked !== isChecked) {
                checkbox.checked = isChecked;
                this.toggleRowSelection(checkbox, isChecked);
                this.showSelectionAnimation(checkbox);
            }
        });

        // Aggiorna selezione
        this.updateSelectedItems();
        this.updateSelectionCounter();
        this.notifySelectionChange();

        // Aggiorna stato indeterminate
        this.isSelectAllIndeterminate = false;
        this.updateSelectAllState();

        // Feedback sonoro (opzionale)
        this.playSelectionFeedback(isChecked ? 'selectAll' : 'deselectAll');
    }

    /**
     * Gestisce selezione individuale
     */
    handleIndividualSelection(event) {
        console.log('✅ handleIndividualSelection called for:', event.target.value);
        const checkbox = event.target;
        const isChecked = checkbox.checked;
        const staffId = checkbox.value;

        // Visual feedback
        this.showSelectionAnimation(checkbox);
        this.toggleRowSelection(checkbox, isChecked);

        // Aggiorna selezione
        this.updateSelectedItems();
        this.updateSelectAllState();
        this.updateSelectionCounter();
        this.notifySelectionChange();

        // Feedback sonoro
        this.playSelectionFeedback(isChecked ? 'select' : 'deselect');

        console.log(`📋 Staff ${staffId} ${isChecked ? 'selected' : 'deselected'}`);
    }

    /**
     * Gestisce shortcuts da tastiera
     */
    handleKeyboardShortcuts(event) {
        // Ctrl/Cmd + A = Select All
        if ((event.ctrlKey || event.metaKey) && event.key === 'a') {
            event.preventDefault();
            const selectAllCheckbox = document.getElementById('select-all-staff');
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = true;
                this.handleSelectAll({ target: selectAllCheckbox });
            }
        }

        // Escape = Deselect All
        if (event.key === 'Escape' && this.selectedItems.length > 0) {
            this.clearSelection();
        }

        // Delete = Bulk Delete (se ci sono elementi selezionati)
        if (event.key === 'Delete' && this.selectedItems.length > 0) {
            event.preventDefault();
            this.staffManager.performBulkAction('delete', this.selectedItems);
        }
    }

    // ==========================================
    // UI UPDATE METHODS
    // ==========================================

    /**
     * Aggiorna lista elementi selezionati
     */
    updateSelectedItems() {
        const checkedBoxes = document.querySelectorAll('.staff-checkbox:checked');
        this.selectedItems = Array.from(checkedBoxes).map(box => box.value);

        // Aggiorna stato nel manager principale
        this.staffManager.handleSelectionChange(this.selectedItems);
    }

    /**
     * Aggiorna stato Select All checkbox
     */
    updateSelectAllState() {
        const selectAllCheckbox = document.getElementById('select-all-staff');
        const individualCheckboxes = document.querySelectorAll('.staff-checkbox');
        const checkedBoxes = document.querySelectorAll('.staff-checkbox:checked');

        if (!selectAllCheckbox) return;

        if (checkedBoxes.length === 0) {
            // Nessuno selezionato
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
            this.isSelectAllIndeterminate = false;
        } else if (checkedBoxes.length === individualCheckboxes.length) {
            // Tutti selezionati
            selectAllCheckbox.checked = true;
            selectAllCheckbox.indeterminate = false;
            this.isSelectAllIndeterminate = false;
        } else {
            // Alcuni selezionati (indeterminate)
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = true;
            this.isSelectAllIndeterminate = true;
        }
    }

    /**
     * Aggiorna counter selezione
     * DISABLED: Duplicato rispetto al form bulk actions in alto + viola CSP con onclick inline
     */
    updateSelectionCounter() {
        // Counter disabilitato - usa il form "Azioni multiple" in alto
        return;
    }

    /**
     * Crea UI counter selezione
     * DISABLED: Duplicato + viola CSP
     */
    createSelectionCounter() {
        // Non più necessario - usa form bulk actions
        return null;
    }

    /**
     * Toggle visual selection della riga
     */
    toggleRowSelection(checkbox, isSelected) {
        const row = checkbox.closest('[data-staff-id]');
        if (!row) return;

        if (isSelected) {
            row.classList.add('staff-row-selected');
        } else {
            row.classList.remove('staff-row-selected');
        }
    }

    /**
     * Animazione feedback selezione
     */
    showSelectionAnimation(checkbox) {
        checkbox.classList.add('checkbox-animate');

        // Rimuovi animazione dopo completamento
        setTimeout(() => {
            checkbox.classList.remove('checkbox-animate');
        }, 150);
    }

    /**
     * Feedback sonoro per selezioni (opzionale)
     */
    playSelectionFeedback(type) {
        if (!this.staffManager.state.audioFeedbackEnabled) return;

        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();

        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);

        // Frequenze diverse per azioni diverse
        const frequencies = {
            select: 800,
            deselect: 600,
            selectAll: 1000,
            deselectAll: 400
        };

        oscillator.frequency.setValueAtTime(frequencies[type] || 700, audioContext.currentTime);
        gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);

        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 0.1);
    }

    // ==========================================
    // PUBLIC METHODS
    // ==========================================

    /**
     * Pulisce tutta la selezione
     */
    clearSelection() {
        const allCheckboxes = document.querySelectorAll('.staff-checkbox, #select-all-staff');
        allCheckboxes.forEach(checkbox => {
            checkbox.checked = false;
            if (checkbox.classList.contains('staff-checkbox')) {
                this.toggleRowSelection(checkbox, false);
            }
        });

        this.selectedItems = [];
        this.updateSelectionCounter();
        this.updateSelectAllState();
        this.notifySelectionChange();

        console.log('🗑️ All selections cleared');
    }

    /**
     * Seleziona elementi specifici
     */
    selectItems(staffIds) {
        staffIds.forEach(id => {
            const checkbox = document.querySelector(`.staff-checkbox[value="${id}"]`);
            if (checkbox && !checkbox.checked) {
                checkbox.checked = true;
                this.toggleRowSelection(checkbox, true);
                this.showSelectionAnimation(checkbox);
            }
        });

        this.updateSelectedItems();
        this.updateSelectionCounter();
        this.updateSelectAllState();
        this.notifySelectionChange();
    }

    /**
     * Ottiene elementi selezionati
     */
    getSelectedItems() {
        return [...this.selectedItems];
    }

    /**
     * Verifica se ci sono elementi selezionati
     */
    hasSelection() {
        return this.selectedItems.length > 0;
    }

    /**
     * Notifica cambiamento selezione al manager principale
     */
    notifySelectionChange() {
        // Trigger custom event
        document.dispatchEvent(new CustomEvent('staffSelectionChanged', {
            detail: {
                selectedItems: this.selectedItems,
                count: this.selectedItems.length
            }
        }));
    }

    /**
     * Aggiorna selezione dopo modifiche DOM (es. dopo filtri)
     */
    refreshSelection() {
        const visibleCheckboxes = document.querySelectorAll('.staff-checkbox:not([style*="display: none"])');
        const currentSelection = this.selectedItems;

        // Resetta selezione visuale
        this.clearSelection();

        // Riapplica selezione agli elementi ancora visibili
        currentSelection.forEach(id => {
            const checkbox = Array.from(visibleCheckboxes).find(cb => cb.value === id);
            if (checkbox) {
                checkbox.checked = true;
                this.toggleRowSelection(checkbox, true);
            }
        });

        this.updateSelectedItems();
        this.updateSelectionCounter();
        this.updateSelectAllState();
    }
}

// Export per uso globale
export default SelectionManager;