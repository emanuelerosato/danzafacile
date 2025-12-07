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
     */
    updateSelectionCounter() {
        let counterElement = document.querySelector('.selection-counter');

        if (this.selectedItems.length === 0) {
            // Nascondi counter
            if (counterElement) {
                counterElement.classList.add('hide');
                setTimeout(() => {
                    counterElement.style.display = 'none';
                }, 300);
            }
            return;
        }

        // Crea o aggiorna counter
        if (!counterElement) {
            counterElement = this.createSelectionCounter();
        }

        counterElement.style.display = 'flex';
        counterElement.classList.remove('hide');
        counterElement.classList.add('show');

        const countText = counterElement.querySelector('.selection-count');
        const actionButtons = counterElement.querySelector('.selection-actions');

        if (countText) {
            countText.textContent = `${this.selectedItems.length} staff selezionato${this.selectedItems.length > 1 ? 'i' : ''}`;
        }

        // Mostra/nascondi azioni basate sul numero selezionato
        if (actionButtons) {
            actionButtons.style.display = this.selectedItems.length > 0 ? 'flex' : 'none';
        }
    }

    /**
     * Crea UI counter selezione
     */
    createSelectionCounter() {
        const counter = document.createElement('div');
        counter.className = 'selection-counter fixed bottom-4 left-1/2 transform -translate-x-1/2 bg-white rounded-lg shadow-lg border border-gray-200 px-6 py-3 flex items-center space-x-4 z-50';
        counter.style.display = 'none';

        counter.innerHTML = `
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-yellow-500 rounded-full flex items-center justify-center">
                    <svg class="w-2.5 h-2.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <span class="selection-count text-sm font-medium text-gray-900">0 staff selezionati</span>
            </div>

            <div class="selection-actions flex items-center space-x-2">
                <button onclick="window.staffSelectionManager.clearSelection()"
                        class="px-3 py-1.5 text-sm text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition-colors duration-200">
                    Deseleziona
                </button>

                <button onclick="window.staffManager.performBulkAction('activate')"
                        class="px-3 py-1.5 text-sm text-green-600 hover:text-green-700 hover:bg-green-50 rounded-lg transition-colors duration-200">
                    Attiva
                </button>

                <button onclick="window.staffManager.performBulkAction('deactivate')"
                        class="px-3 py-1.5 text-sm text-yellow-600 hover:text-yellow-700 hover:bg-yellow-50 rounded-lg transition-colors duration-200">
                    Disattiva
                </button>

                <button onclick="window.staffManager.performBulkAction('delete')"
                        class="px-3 py-1.5 text-sm text-red-600 hover:text-red-700 hover:bg-red-50 rounded-lg transition-colors duration-200">
                    Elimina
                </button>
            </div>
        `;

        document.body.appendChild(counter);
        return counter;
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