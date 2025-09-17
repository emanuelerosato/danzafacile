<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <input type="checkbox" id="select-all" class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'title', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                       class="group flex items-center hover:text-gray-700">
                        Documento
                        <svg class="ml-1 h-3 w-3 text-gray-400 group-hover:text-gray-600" fill="currentColor" viewBox="0 0 12 12">
                            <path d="M6 2L4 4h4L6 2zM6 10l2-2H4l2 2z"/>
                        </svg>
                    </a>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Categoria
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'status', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                       class="group flex items-center hover:text-gray-700">
                        Stato
                        <svg class="ml-1 h-3 w-3 text-gray-400 group-hover:text-gray-600" fill="currentColor" viewBox="0 0 12 12">
                            <path d="M6 2L4 4h4L6 2zM6 10l2-2H4l2 2z"/>
                        </svg>
                    </a>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Caricato da
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <a href="{{ request()->fullUrlWithQuery(['sort' => 'created_at', 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
                       class="group flex items-center hover:text-gray-700">
                        Data
                        <svg class="ml-1 h-3 w-3 text-gray-400 group-hover:text-gray-600" fill="currentColor" viewBox="0 0 12 12">
                            <path d="M6 2L4 4h4L6 2zM6 10l2-2H4l2 2z"/>
                        </svg>
                    </a>
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Dimensione
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">Azioni</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($documents as $document)
                <tr class="hover:bg-gray-50 {{ $document->is_expired ? 'bg-red-50' : '' }}">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox" name="document_ids[]" value="{{ $document->id }}"
                               class="document-checkbox rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                                <div class="h-10 w-10 rounded-lg bg-gray-100 flex items-center justify-center">
                                    <i class="{{ $document->file_icon }} text-gray-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">
                                    <a href="{{ route('admin.documents.show', $document) }}"
                                       class="hover:text-rose-600 transition-colors duration-200">
                                        {{ Str::limit($document->title, 40) }}
                                    </a>
                                    @if($document->is_expired)
                                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800">
                                            Scaduto
                                        </span>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-500">
                                    {{ $document->original_filename }}
                                </div>
                                @if($document->description)
                                    <div class="text-xs text-gray-400 mt-1">
                                        {{ Str::limit($document->description, 60) }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                            {{ $document->category_name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $document->status_class }}">
                            {{ $document->status_name }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8">
                                <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="text-xs font-medium text-gray-600">
                                        {{ substr($document->uploadedBy->name, 0, 2) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-2">
                                <div class="text-sm text-gray-900">{{ $document->uploadedBy->name }}</div>
                                <div class="text-xs text-gray-500">{{ $document->uploadedBy->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div>{{ $document->created_at->format('d/m/Y') }}</div>
                        <div class="text-xs">{{ $document->created_at->format('H:i') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $document->formatted_size }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <!-- Download -->
                            <a href="{{ route('admin.documents.download', $document) }}"
                               class="text-blue-600 hover:text-blue-900 transition-colors duration-200"
                               title="Scarica">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                </svg>
                            </a>

                            <!-- View -->
                            <a href="{{ route('admin.documents.show', $document) }}"
                               class="text-gray-600 hover:text-gray-900 transition-colors duration-200"
                               title="Visualizza">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>

                            @if($document->status === 'pending')
                                <!-- Approve -->
                                <form method="POST" action="{{ route('admin.documents.approve', $document) }}" class="inline">
                                    @csrf
                                    <button type="submit"
                                            onclick="return confirm('Sei sicuro di voler approvare questo documento?')"
                                            class="text-green-600 hover:text-green-900 transition-colors duration-200"
                                            title="Approva">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </button>
                                </form>

                                <!-- Reject -->
                                <button onclick="showRejectModal({{ $document->id }}, '{{ $document->title }}')"
                                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                        title="Rifiuta">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </button>
                            @endif

                            <!-- Edit -->
                            <a href="{{ route('admin.documents.edit', $document) }}"
                               class="text-indigo-600 hover:text-indigo-900 transition-colors duration-200"
                               title="Modifica">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                </svg>
                            </a>

                            <!-- Delete -->
                            <form method="POST" action="{{ route('admin.documents.destroy', $document) }}" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        onclick="return confirm('Sei sicuro di voler eliminare questo documento? Questa azione non può essere annullata.')"
                                        class="text-red-600 hover:text-red-900 transition-colors duration-200"
                                        title="Elimina">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun documento trovato</h3>
                            <p class="text-gray-500 mb-4">Non ci sono documenti che corrispondono ai criteri di ricerca.</p>
                            <a href="{{ route('admin.documents.create') }}"
                               class="inline-flex items-center px-4 py-2 bg-rose-600 text-white rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-colors duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                </svg>
                                Carica il primo documento
                            </a>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($documents->hasPages())
    <div class="px-6 py-4 border-t border-gray-100">
        {{ $documents->appends(request()->query())->links() }}
    </div>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Select all functionality
    const selectAllCheckbox = document.getElementById('select-all');
    const documentCheckboxes = document.querySelectorAll('.document-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            documentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActionsVisibility();
        });
    }

    documentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateBulkActionsVisibility();

            // Update select all checkbox state
            if (selectAllCheckbox) {
                const checkedCount = document.querySelectorAll('.document-checkbox:checked').length;
                selectAllCheckbox.checked = checkedCount === documentCheckboxes.length;
                selectAllCheckbox.indeterminate = checkedCount > 0 && checkedCount < documentCheckboxes.length;
            }
        });
    });

    function updateBulkActionsVisibility() {
        const checkedCheckboxes = document.querySelectorAll('.document-checkbox:checked');
        const bulkActionsContainer = document.querySelector('[x-data*="selectedDocs"]');

        if (bulkActionsContainer && bulkActionsContainer.__x) {
            bulkActionsContainer.__x.$data.selectedDocs = Array.from(checkedCheckboxes).map(cb => cb.value);
        }
    }
});

function showRejectModal(documentId, documentTitle) {
    const modal = document.getElementById('rejectModal');
    const form = document.getElementById('rejectForm');
    const titleSpan = document.getElementById('rejectDocumentTitle');

    if (modal && form && titleSpan) {
        titleSpan.textContent = documentTitle;
        form.action = `/admin/documents/${documentId}/reject`;

        // Show modal (assuming you're using a modal library or custom implementation)
        modal.classList.remove('hidden');
    }
}
</script>