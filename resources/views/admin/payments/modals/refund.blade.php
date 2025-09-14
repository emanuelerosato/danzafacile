<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1" aria-labelledby="refundModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="refundModalLabel">Elabora Rimborso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="refundForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="refund_reason" class="form-label">Motivo del Rimborso <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="refund_reason" name="refund_reason" rows="3"
                                  placeholder="Inserisci il motivo del rimborso..." required></textarea>
                        <div class="form-text">Specifica il motivo per cui viene elaborato questo rimborso.</div>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attenzione:</strong> Questa azione segnerà il pagamento come rimborsato. Assicurati di aver già elaborato il rimborso effettivo attraverso il metodo di pagamento originale.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-undo me-2"></i>Elabora Rimborso
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>