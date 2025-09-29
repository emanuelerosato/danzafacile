/**
 * Alpine.js Component: Ticket Manager
 * Gestisce l'interfaccia admin dei ticket con filtri real-time,
 * inline editing e bulk actions
 */

window.ticketManager = function() {
    return {
        // State
        tickets: [],
        selectedTickets: [],
        showBulkModal: false,
        bulkAction: '',
        assignedTo: '',
        isLoading: false,

        // Filters
        filters: {
            search: '',
            status: '',
            priority: '',
            category: '',
            date_from: '',
            date_to: ''
        },

        // Inline editing
        editingTicket: null,
        editingField: null,
        tempValue: null,

        // Initialize
        init() {
            // Load initial tickets from DOM if available
            const ticketsData = document.getElementById('tickets-data');
            if (ticketsData) {
                try {
                    this.tickets = JSON.parse(ticketsData.textContent);
                } catch (e) {
                    console.error('Error parsing tickets data:', e);
                }
            }

            // Listen for filter changes from URL params
            this.loadFiltersFromURL();
        },

        // Load filters from URL parameters
        loadFiltersFromURL() {
            const urlParams = new URLSearchParams(window.location.search);
            this.filters.search = urlParams.get('search') || '';
            this.filters.status = urlParams.get('status') || '';
            this.filters.priority = urlParams.get('priority') || '';
            this.filters.category = urlParams.get('category') || '';
            this.filters.date_from = urlParams.get('date_from') || '';
            this.filters.date_to = urlParams.get('date_to') || '';
        },

        // Toggle ticket selection
        toggleTicket(ticketId) {
            const index = this.selectedTickets.indexOf(ticketId);
            if (index === -1) {
                this.selectedTickets.push(ticketId);
            } else {
                this.selectedTickets.splice(index, 1);
            }
        },

        // Toggle all tickets
        toggleAll() {
            if (this.allSelected) {
                this.selectedTickets = [];
            } else {
                this.selectedTickets = this.tickets.map(t => t.id);
            }
        },

        // Check if ticket is selected
        isSelected(ticketId) {
            return this.selectedTickets.includes(ticketId);
        },

        // Check if all tickets are selected
        get allSelected() {
            return this.tickets.length > 0 &&
                   this.selectedTickets.length === this.tickets.length;
        },

        // Check if any tickets are selected
        get hasSelection() {
            return this.selectedTickets.length > 0;
        },

        // Get selection count
        get selectionCount() {
            return this.selectedTickets.length;
        },

        // Open bulk actions modal
        openBulkModal() {
            if (!this.hasSelection) return;
            this.showBulkModal = true;
        },

        // Close bulk actions modal
        closeBulkModal() {
            this.showBulkModal = false;
            this.bulkAction = '';
            this.assignedTo = '';
        },

        // Execute bulk action
        async executeBulkAction() {
            if (!this.bulkAction || !this.hasSelection) return;

            this.isLoading = true;

            try {
                const response = await fetch('/admin/tickets/bulk-action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        action: this.bulkAction,
                        ticket_ids: this.selectedTickets,
                        assigned_to: this.assignedTo
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Reload page to show updated tickets
                    window.location.reload();
                } else {
                    alert(data.message || 'Errore durante l\'esecuzione dell\'azione');
                }
            } catch (error) {
                console.error('Bulk action error:', error);
                alert('Errore durante l\'esecuzione dell\'azione');
            } finally {
                this.isLoading = false;
            }
        },

        // Start inline editing
        startEdit(ticketId, field, currentValue) {
            this.editingTicket = ticketId;
            this.editingField = field;
            this.tempValue = currentValue;
        },

        // Cancel inline editing
        cancelEdit() {
            this.editingTicket = null;
            this.editingField = null;
            this.tempValue = null;
        },

        // Save inline edit
        async saveEdit(ticketId) {
            if (!this.editingField || this.tempValue === null) return;

            this.isLoading = true;

            try {
                const response = await fetch(`/admin/tickets/${ticketId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        [this.editingField]: this.tempValue
                    })
                });

                const data = await response.json();

                if (data.success) {
                    // Update ticket in local state
                    const ticket = this.tickets.find(t => t.id === ticketId);
                    if (ticket) {
                        ticket[this.editingField] = this.tempValue;
                    }

                    this.cancelEdit();

                    // Reload to show updated data
                    window.location.reload();
                } else {
                    alert(data.message || 'Errore durante l\'aggiornamento');
                }
            } catch (error) {
                console.error('Save edit error:', error);
                alert('Errore durante l\'aggiornamento');
            } finally {
                this.isLoading = false;
            }
        },

        // Check if field is being edited
        isEditing(ticketId, field) {
            return this.editingTicket === ticketId && this.editingField === field;
        },

        // Apply filters (submit form)
        applyFilters() {
            const form = document.getElementById('filters-form');
            if (form) {
                form.submit();
            }
        },

        // Reset filters
        resetFilters() {
            this.filters = {
                search: '',
                status: '',
                priority: '',
                category: '',
                date_from: '',
                date_to: ''
            };

            // Redirect to clean URL
            window.location.href = window.location.pathname;
        },

        // Get active filters count
        get activeFiltersCount() {
            let count = 0;
            if (this.filters.search) count++;
            if (this.filters.status) count++;
            if (this.filters.priority) count++;
            if (this.filters.category) count++;
            if (this.filters.date_from) count++;
            if (this.filters.date_to) count++;
            return count;
        },

        // Check if filters are active
        get hasActiveFilters() {
            return this.activeFiltersCount > 0;
        }
    };
};