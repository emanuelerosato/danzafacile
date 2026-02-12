<x-app-layout>


<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
                <div>
                    <nav class="flex" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-3">
                            <li class="inline-flex items-center">
                                <a href="{{ route('admin.documents.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-rose-600">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    Documenti
                                </a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <a href="{{ route('admin.documents.show', $document) }}" class="ml-1 text-sm font-medium text-gray-500 hover:text-gray-700 md:ml-2">
                                        {{ Str::limit($document->name, 20) }}
                                    </a>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                    </svg>
                                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Modifica</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-xl md:text-2xl font-bold text-gray-900 mt-1">Modifica Documento</h1>
                    <p class="text-sm text-gray-600 mt-1">Aggiorna le informazioni del documento</p>
                </div>
        </div>
        <form action="{{ route('admin.documents.update', $document) }}" method="POST" enctype="multipart/form-data"
              x-data="documentEdit()"
              x-init="initEdit()"
              class="space-y-6">
            @csrf
            @method('PUT')

            <!-- Main Card -->
            <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50">
                    <h2 class="text-lg font-medium text-gray-900">Informazioni Documento</h2>
                    <p class="text-sm text-gray-600 mt-1">Modifica i dettagli del documento</p>
                </div>

                <!-- Card Body -->
                <div class="p-6 space-y-6">
                    <!-- Current File Info -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                    <i class="{{ $document->file_icon }} text-blue-600"></i>
                                </div>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-sm font-medium text-blue-900">File Attuale</h3>
                                <p class="text-sm text-blue-700">{{ $document->name }}</p>
                                <p class="text-xs text-blue-600">{{ $document->formatted_size }}</p>
                            </div>
                            <div class="ml-auto">
                                <a href="{{ route('admin.documents.download', $document) }}"
                                   class="inline-flex items-center px-3 py-1.5 border border-blue-300 text-xs font-medium rounded text-blue-700 bg-blue-50 hover:bg-blue-100">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3M3 17V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2z"></path>
                                    </svg>
                                    Scarica
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Replace File (Optional) -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sostituisci File (Opzionale)
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-rose-400 transition-colors duration-200"
                             :class="{ 'border-rose-400 bg-rose-50': isDragOver }"
                             @drop="handleDrop"
                             @dragover.prevent="isDragOver = true"
                             @dragleave.prevent="isDragOver = false">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-rose-600 hover:text-rose-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-rose-500">
                                        <span>Carica nuovo file</span>
                                        <input id="file"
                                               name="file"
                                               type="file"
                                               class="sr-only"
                                               accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.txt"
                                               @change="handleFileSelect">
                                    </label>
                                    <p class="pl-1">o trascina e rilascia</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, DOC, IMG fino a 10MB</p>
                                <p class="text-xs text-gray-400">Lascia vuoto per mantenere il file attuale</p>
                            </div>
                        </div>

                        <!-- New File Preview -->
                        <div x-show="selectedFile" x-cloak class="mt-4">
                            <div class="flex items-center justify-between p-4 bg-green-50 rounded-lg border border-green-200">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-file text-green-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-900">Nuovo File</p>
                                        <p class="text-sm text-green-700" x-text="selectedFile?.name"></p>
                                        <p class="text-xs text-green-600" x-text="formatFileSize(selectedFile?.size)"></p>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="clearFile()"
                                        class="text-green-400 hover:text-red-500 transition-colors duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        @error('file')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Documento <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name', $document->name) }}"
                               required
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('name') border-red-300 @enderror"
                               placeholder="Inserisci il nome del documento">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div>
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-2">
                            Categoria <span class="text-red-500">*</span>
                        </label>
                        <select id="category"
                                name="category"
                                required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('category') border-red-300 @enderror">
                            <option value="">Seleziona una categoria</option>
                            @foreach(App\Models\Document::getAvailableCategories() as $key => $label)
                                <option value="{{ $key }}" {{ old('category', $document->category) === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Warning -->
                    @if($document->status === 'approved')
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-yellow-800">Attenzione</h3>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Questo documento è attualmente approvato. Se modifichi le impostazioni di approvazione o carichi un nuovo file,
                                        potrebbe tornare allo stato "In Attesa" e richiedere una nuova approvazione.
                                    </p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end space-x-4">
                <a href="{{ route('admin.documents.show', $document) }}"
                   class="px-6 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                    Annulla
                </a>
                <button type="submit"
                        class="px-6 py-2 text-sm font-medium text-white bg-rose-600 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-colors duration-200">
                    <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Salva Modifiche
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script nonce="@cspNonce">
function documentEdit() {
    return {
        selectedFile: null,
        isDragOver: false,

        initEdit() {
            // Initialize any required settings
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedFile = file;
            }
        },

        handleDrop(event) {
            event.preventDefault();
            this.isDragOver = false;

            const files = event.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                this.selectedFile = file;

                // Set the file input
                const fileInput = document.getElementById('file');
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
            }
        },

        clearFile() {
            this.selectedFile = null;
            const fileInput = document.getElementById('file');
            fileInput.value = '';
        },

        formatFileSize(bytes) {
            if (!bytes) return '';

            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;

            while (bytes >= 1024 && i < units.length - 1) {
                bytes /= 1024;
                i++;
            }

            return `${Math.round(bytes * 100) / 100} ${units[i]}`;
        }
    }
}
</script>
@endpush

</x-app-layout>
