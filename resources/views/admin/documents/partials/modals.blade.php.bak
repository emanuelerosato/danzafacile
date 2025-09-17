<!-- Reject Document Modal -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Rifiuta Documento</h3>
                <button onclick="closeRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <p class="text-sm text-gray-600 mb-4">
                Stai per rifiutare il documento: <strong id="rejectDocumentTitle"></strong>
            </p>

            <form id="rejectForm" method="POST">
                @csrf
                <div class="mb-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo del rifiuto <span class="text-red-500">*</span>
                    </label>
                    <textarea id="rejection_reason"
                              name="rejection_reason"
                              rows="4"
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                              placeholder="Inserisci il motivo per cui questo documento viene rifiutato..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <button type="button"
                            onclick="closeRejectModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                        Annulla
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Rifiuta Documento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bulk Reject Modal -->
<div id="bulkRejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 hidden">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Rifiuta Documenti Selezionati</h3>
                <button onclick="closeBulkRejectModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <p class="text-sm text-gray-600 mb-4">
                Stai per rifiutare <span id="bulkRejectCount"></span> documenti selezionati.
            </p>

            <form id="bulkRejectForm" method="POST" action="{{ route('admin.documents.bulk-action') }}">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div id="bulkRejectDocumentIds"></div>

                <div class="mb-4">
                    <label for="bulk_rejection_reason" class="block text-sm font-medium text-gray-700 mb-2">
                        Motivo del rifiuto <span class="text-red-500">*</span>
                    </label>
                    <textarea id="bulk_rejection_reason"
                              name="rejection_reason"
                              rows="4"
                              required
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-transparent"
                              placeholder="Inserisci il motivo per cui questi documenti vengono rifiutati..."></textarea>
                </div>

                <div class="flex items-center justify-end space-x-3">
                    <button type="button"
                            onclick="closeBulkRejectModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                        Annulla
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Rifiuta Documenti
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function closeRejectModal() {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const textarea = document.getElementById('rejection_reason');

    if (modal) modal.classList.add('hidden');
    if (form) form.reset();
    if (textarea) textarea.value = '';
}

function closeBulkRejectModal() {
    const modal = document.getElementById('bulkRejectModal');
    const form = document.getElementById('bulkRejectForm');
    const textarea = document.getElementById('bulk_rejection_reason');
    const container = document.getElementById('bulkRejectDocumentIds');

    if (modal) modal.classList.add('hidden');
    if (form) form.reset();
    if (textarea) textarea.value = '';
    if (container) container.innerHTML = '';
}

function showBulkRejectModal(documentIds) {
    const modal = document.getElementById('bulkRejectModal');
    const countSpan = document.getElementById('bulkRejectCount');
    const container = document.getElementById('bulkRejectDocumentIds');

    if (modal && countSpan && container) {
        countSpan.textContent = documentIds.length;

        // Clear previous inputs
        container.innerHTML = '';

        // Add hidden inputs for document IDs
        documentIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'documents[]';
            input.value = id;
            container.appendChild(input);
        });

        modal.classList.remove('hidden');
    }
}

// Override the original bulkAction function to handle reject with modal
window.originalBulkAction = window.bulkAction;
window.bulkAction = function(action) {
    const checkboxes = document.querySelectorAll('input[name="document_ids[]"]:checked');
    const ids = Array.from(checkboxes).map(cb => cb.value);

    if (ids.length === 0) {
        alert('Seleziona almeno un documento');
        return;
    }

    if (action === 'reject') {
        showBulkRejectModal(ids);
        return;
    }

    // For other actions, use the original function
    window.originalBulkAction(action);
};

// Close modals when clicking outside
document.addEventListener('click', function(event) {
    const rejectModal = document.getElementById('rejectModal');
    const bulkRejectModal = document.getElementById('bulkRejectModal');

    if (event.target === rejectModal) {
        closeRejectModal();
    }

    if (event.target === bulkRejectModal) {
        closeBulkRejectModal();
    }
});

// Close modals with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeRejectModal();
        closeBulkRejectModal();
    }
});
</script>