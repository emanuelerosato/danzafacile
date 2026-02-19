/**
 * payment-manager-simple.js
 *
 * Versione semplificata del PaymentManager per evitare conflitti
 * Fornisce funzionalità base senza dipendenze complesse
 *
 * @version 1.0.0
 */

// Registra il componente Alpine.js nel momento giusto (prima dell'init di Alpine)
document.addEventListener('alpine:init', () => {
    Alpine.data('paymentManager', () => ({
        selectedPayments: [],
        isLoading: false,
        showBulkModal: false,
        showRefundModal: false,
        currentPaymentId: null,

        // Inizializzazione
        init() {
            console.log('[PaymentManager] Simple version initialized');
            this.setupDropdowns();
        },

        // Setup dropdown menus
        setupDropdowns() {
            // Gestisce i dropdown dei pagamenti
            document.addEventListener('click', (e) => {
                if (!e.target.closest('[data-dropdown-toggle]')) {
                    // Chiudi tutti i dropdown
                    document.querySelectorAll('[id^="paymentDropdown"]').forEach(dropdown => {
                        dropdown.classList.add('hidden');
                    });
                }
            });
        },

        // Toggle dropdown
        toggleDropdown(paymentId) {
            const dropdown = document.getElementById(`paymentDropdown${paymentId}`);
            if (dropdown) {
                dropdown.classList.toggle('hidden');

                // Chiudi altri dropdown
                document.querySelectorAll('[id^="paymentDropdown"]:not(#paymentDropdown' + paymentId + ')').forEach(d => {
                    d.classList.add('hidden');
                });
            }
        },

        // Toggle bulk dropdown
        toggleBulkDropdown() {
            const dropdown = document.getElementById('bulkDropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        },

        // Mark payment as completed
        async markCompleted(paymentId) {
            if (!confirm('Sei sicuro di voler segnare questo pagamento come completato?')) {
                return;
            }

            this.isLoading = true;

            try {
                const response = await fetch(`/admin/payments/${paymentId}/mark-completed`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Pagamento completato con successo!');
                    location.reload();
                } else {
                    alert('Errore: ' + (data.message || 'Operazione fallita'));
                }
            } catch (error) {
                console.error('Mark completed error:', error);
                alert('Errore durante l\'operazione');
            } finally {
                this.isLoading = false;
            }
        },

        // Send receipt
        async sendReceipt(paymentId) {
            if (!confirm('Vuoi inviare la ricevuta via email?')) {
                return;
            }

            this.isLoading = true;

            try {
                const response = await fetch(`/admin/payments/${paymentId}/send-receipt`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Ricevuta inviata con successo!');
                    location.reload();
                } else {
                    alert('Errore: ' + (data.message || 'Invio fallito'));
                }
            } catch (error) {
                console.error('Send receipt error:', error);
                alert('Errore durante l\'invio');
            } finally {
                this.isLoading = false;
            }
        },

        // Delete payment
        async deletePayment(paymentId) {
            if (!confirm('Sei sicuro di voler eliminare questo pagamento? Questa azione non può essere annullata.')) {
                return;
            }

            this.isLoading = true;

            try {
                const response = await fetch(`/admin/payments/${paymentId}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    }
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Pagamento eliminato con successo!');
                    location.reload();
                } else {
                    alert('Errore: ' + (data.message || 'Eliminazione fallita'));
                }
            } catch (error) {
                console.error('Delete payment error:', error);
                alert('Errore durante l\'eliminazione');
            } finally {
                this.isLoading = false;
            }
        },

        // Azioni multiple
        openBulkModal() {
            if (this.selectedPayments.length === 0) {
                alert('Seleziona almeno un pagamento per continuare.');
                return;
            }
            this.showBulkModal = true;
        },

        closeBulkModal() {
            this.showBulkModal = false;
        },

        // Refund modal actions
        openRefundModal(paymentId) {
            this.currentPaymentId = paymentId;
            this.showRefundModal = true;
        },

        closeRefundModal() {
            this.showRefundModal = false;
            this.currentPaymentId = null;
        },

        // Export semplice
        exportPayments() {
            this.isLoading = true;

            // Costruisci URL con filtri correnti
            const currentUrl = new URL(window.location.href);
            const exportUrl = currentUrl.pathname.replace(/\/$/, '') + '/export' + currentUrl.search;

            // Redirect per il download
            window.location.href = exportUrl;

            // Reset loading dopo un po'
            setTimeout(() => {
                this.isLoading = false;
            }, 2000);
        },

        // Selezione pagamenti
        togglePaymentSelection(paymentId) {
            const index = this.selectedPayments.indexOf(paymentId);
            if (index > -1) {
                this.selectedPayments.splice(index, 1);
            } else {
                this.selectedPayments.push(paymentId);
            }
            this.updateBulkActionsVisibility();
        },

        selectAllPayments() {
            const checkboxes = document.querySelectorAll('input[name="payment_ids[]"]');
            const allSelected = Array.from(checkboxes).every(cb => cb.checked);

            checkboxes.forEach(checkbox => {
                checkbox.checked = !allSelected;
                const paymentId = parseInt(checkbox.value);

                if (!allSelected && !this.selectedPayments.includes(paymentId)) {
                    this.selectedPayments.push(paymentId);
                } else if (allSelected) {
                    const index = this.selectedPayments.indexOf(paymentId);
                    if (index > -1) this.selectedPayments.splice(index, 1);
                }
            });

            this.updateBulkActionsVisibility();
        },

        updateBulkActionsVisibility() {
            // Aggiorna UI per mostrare/nascondere azioni bulk
            const bulkActions = document.querySelector('.bulk-actions');
            if (bulkActions) {
                bulkActions.style.display = this.selectedPayments.length > 0 ? 'block' : 'none';
            }
        },

        // Bulk actions
        async performBulkAction(action) {
            if (this.selectedPayments.length === 0) {
                alert('Nessun pagamento selezionato.');
                return;
            }

            this.isLoading = true;

            try {
                const response = await fetch('/admin/payments/bulk-action', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        action: action,
                        payment_ids: this.selectedPayments
                    })
                });

                const data = await response.json();

                if (data.success) {
                    alert(data.message || 'Operazione completata con successo!');
                    location.reload();
                } else {
                    alert('Errore: ' + (data.message || 'Operazione fallita'));
                }
            } catch (error) {
                console.error('Bulk action error:', error);
                alert('Errore durante l\'operazione');
            } finally {
                this.isLoading = false;
                this.closeBulkModal();
            }
        },

        // Get selected count
        get selectedCount() {
            return this.selectedPayments.length;
        }
    }));
});

console.log('[PaymentManager] Simple version loaded');