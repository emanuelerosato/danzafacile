<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Carica Nuovo Documento
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Aggiungi un nuovo documento alla scuola
                </p>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.documents.index') }}" class="text-gray-500 hover:text-gray-700">Documenti</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Carica Documento</li>
    </x-slot>

<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Carica Nuovo Documento
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Aggiungi un nuovo documento alla scuola
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                <a href="{{ route('admin.documents.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m0 7h18"/>
                    </svg>
                    Torna alla Lista
                </a>
            </div>
        </div>

        <!-- Form Card -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <form action="{{ route('admin.documents.store') }}" method="POST" enctype="multipart/form-data"
                  x-data="documentUpload()"
                  x-init="initUpload()"
                  class="space-y-6">
                @csrf
                    <!-- File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            File Documento <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-dashed rounded-2xl border-gray-300 hover:border-rose-400 bg-gradient-to-br from-gray-50/50 to-white/50 backdrop-blur-sm transition-all duration-200"
                             :class="{ 'border-rose-400 bg-gradient-to-br from-rose-50/80 to-pink-50/80': isDragOver }"
                             @drop="handleDrop"
                             @dragover.prevent="isDragOver = true"
                             @dragleave.prevent="isDragOver = false">
                            <div class="space-y-1 text-center">
                                <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <div class="flex text-sm text-gray-600">
                                    <label for="file" class="relative cursor-pointer bg-white rounded-md font-medium text-rose-600 hover:text-rose-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-rose-500">
                                        <span>Carica un file</span>
                                        <input id="file"
                                               name="file"
                                               type="file"
                                               class="sr-only"
                                               required
                                               accept=".pdf,.jpg,.jpeg,.png,.gif,.doc,.docx,.txt"
                                               @change="handleFileSelect">
                                    </label>
                                    <p class="pl-1">o trascina e rilascia</p>
                                </div>
                                <p class="text-xs text-gray-500">PDF, DOC, IMG fino a 10MB</p>
                            </div>
                        </div>

                        <!-- File Preview -->
                        <div x-show="selectedFile" x-cloak class="mt-4">
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-rose-50/80 to-pink-50/80 backdrop-blur-sm rounded-2xl border border-rose-200/50">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <div class="w-10 h-10 bg-rose-100 rounded-lg flex items-center justify-center">
                                            <i class="fas fa-file text-rose-600"></i>
                                        </div>
                                    </div>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-gray-900" x-text="selectedFile?.name"></p>
                                        <p class="text-xs text-gray-500" x-text="formatFileSize(selectedFile?.size)"></p>
                                    </div>
                                </div>
                                <button type="button"
                                        @click="clearFile()"
                                        class="text-gray-400 hover:text-red-500 transition-colors duration-200">
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

                    <!-- Title -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Nome Documento <span class="text-red-500">*</span>
                        </label>
                        <input type="text"
                               id="name"
                               name="name"
                               value="{{ old('name') }}"
                               required
                               maxlength="255"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('name') border-red-300 @enderror"
                               placeholder="Inserisci il nome del documento">
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description field removed for simplicity - not in database schema -->

                    <!-- User/Student Assignment -->
                    <div>
                        <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                            Associa a Studente
                        </label>
                        <select id="user_id"
                                name="user_id"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('user_id') border-red-300 @enderror">
                            <option value="">Documento generale (non associato a uno studente)</option>
                            @if(isset($students))
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" {{ old('user_id') == $student->id ? 'selected' : '' }}>
                                        {{ $student->first_name }} {{ $student->last_name }} ({{ $student->email }})
                                        @if($student->codice_fiscale)
                                            - CF: {{ $student->codice_fiscale }}
                                        @endif
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('user_id')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">
                            Lascia vuoto per creare un documento generale della scuola, oppure seleziona uno studente per associare il documento (es. documento d'identità, certificato medico)
                        </p>
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
                                <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('category')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Settings -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-medium text-gray-900">Impostazioni</h3>

                        <!-- Is Public -->
                        <div class="flex items-center">
                            <input id="is_public"
                                   name="is_public"
                                   type="checkbox"
                                   value="1"
                                   {{ old('is_public') ? 'checked' : '' }}
                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <label for="is_public" class="ml-2 block text-sm text-gray-700">
                                Documento pubblico
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 ml-6">Se abilitato, il documento sarà visibile a tutti gli utenti della scuola</p>

                        <!-- Requires Approval -->
                        <div class="flex items-center">
                            <input id="requires_approval"
                                   name="requires_approval"
                                   type="checkbox"
                                   value="1"
                                   {{ old('requires_approval', true) ? 'checked' : '' }}
                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <label for="requires_approval" class="ml-2 block text-sm text-gray-700">
                                Richiede approvazione
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 ml-6">Se abilitato, il documento dovrà essere approvato prima di essere pubblicato</p>

                        <!-- Expires At -->
                        <div>
                            <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">
                                Data di Scadenza (opzionale)
                            </label>
                            <input type="datetime-local"
                                   id="expires_at"
                                   name="expires_at"
                                   value="{{ old('expires_at') }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent @error('expires_at') border-red-300 @enderror">
                            @error('expires_at')
                                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                <!-- Form Actions -->
                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('admin.documents.index') }}"
                       class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                        Annulla
                    </a>
                    <x-loading-button
                        type="submit"
                        variant="primary"
                        icon="upload"
                        loading-text="Caricamento...">
                        Carica Documento
                    </x-loading-button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function documentUpload() {
    return {
        selectedFile: null,
        isDragOver: false,

        initUpload() {
            // Initialize any required settings
        },

        handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedFile = file;
                this.autoFillName(file.name);
            }
        },

        handleDrop(event) {
            event.preventDefault();
            this.isDragOver = false;

            const files = event.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                this.selectedFile = file;
                this.autoFillName(file.name);

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

        autoFillName(filename) {
            const nameInput = document.getElementById('name');
            if (!nameInput.value) {
                // Remove extension and replace underscores/hyphens with spaces
                const name = filename
                    .replace(/\.[^/.]+$/, '')
                    .replace(/[_-]/g, ' ')
                    .replace(/\b\w/g, l => l.toUpperCase());
                nameInput.value = name;
            }
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
