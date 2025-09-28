<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $gallery->title }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ \App\Models\MediaGallery::getAvailableTypes()[$gallery->type] }}
                    @if($gallery->course) - {{ $gallery->course->name }}@endif
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
            <a href="{{ route('admin.galleries.index') }}" class="text-gray-500 hover:text-gray-700">Gallerie</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">{{ $gallery->title }}</li>
    </x-slot>

<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Gallery Header -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h1 class="text-2xl font-semibold text-gray-900">{{ $gallery->title }}</h1>
                        <div class="flex gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                {{ \App\Models\MediaGallery::getAvailableTypes()[$gallery->type] }}
                            </span>
                            @if($gallery->is_featured)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    In Evidenza
                                </span>
                            @endif
                            @if($gallery->is_public)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Pubblica
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                    Privata
                                </span>
                            @endif
                        </div>
                    </div>

                    @if($gallery->description)
                        <p class="text-gray-600 mb-3">{{ $gallery->description }}</p>
                    @endif

                    <div class="flex items-center gap-6 text-sm text-gray-500">
                        @if($gallery->course)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                </svg>
                                {{ $gallery->course->name }}
                            </div>
                        @endif
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $gallery->createdBy->name }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $gallery->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $mediaItems->total() }} media
                        </div>
                    </div>
                </div>

                <div class="flex gap-2 ml-4">
                    <a href="{{ route('admin.galleries.edit', $gallery) }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifica
                    </a>
                    <button onclick="openUploadModal()"
                            class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Carica File
                    </button>
                    <button onclick="openLinkModal()"
                            class="inline-flex items-center px-4 py-2 bg-purple-50 hover:bg-purple-100 text-purple-700 text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        Aggiungi Link
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Media Grid -->
    @if($mediaItems->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-6" id="mediaGrid">
            @foreach($mediaItems as $media)
                <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden group media-item hover:shadow-xl transition-all duration-200 transform hover:scale-105" data-id="{{ $media->id }}">
                    <!-- Media Preview -->
                    <div class="aspect-w-16 aspect-h-12 bg-gray-100 relative">
                        @if($media->is_image)
                            <img src="{{ $media->thumbnail_url }}"
                                 alt="{{ $media->title }}"
                                 class="w-full h-32 object-cover cursor-pointer"
                                 onclick="openLightbox('{{ $media->file_url }}', '{{ $media->title }}', 'image')">
                        @elseif($media->is_video)
                            @if($media->type === 'youtube')
                                <div class="w-full h-32 bg-black flex items-center justify-center cursor-pointer relative"
                                     onclick="openLightbox('{{ $media->embed_url }}', '{{ $media->title }}', 'youtube')">
                                    <img src="{{ $media->thumbnail_url }}" class="w-full h-full object-cover">
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fab fa-youtube text-red-500 text-3xl"></i>
                                    </div>
                                </div>
                            @elseif($media->type === 'vimeo')
                                <div class="w-full h-32 bg-black flex items-center justify-center cursor-pointer relative"
                                     onclick="openLightbox('{{ $media->embed_url }}', '{{ $media->title }}', 'vimeo')">
                                    @if($media->thumbnail_url)
                                        <img src="{{ $media->thumbnail_url }}" class="w-full h-full object-cover">
                                    @endif
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fab fa-vimeo text-blue-500 text-3xl"></i>
                                    </div>
                                </div>
                            @else
                                <video class="w-full h-32 object-cover cursor-pointer"
                                       onclick="openLightbox('{{ $media->file_url }}', '{{ $media->title }}', 'video')">
                                    <source src="{{ $media->file_url }}" type="{{ $media->file_type }}">
                                </video>
                            @endif
                        @else
                            <div class="w-full h-32 bg-gray-200 flex items-center justify-center">
                                <i class="{{ $media->file_icon }} text-2xl text-gray-400"></i>
                            </div>
                        @endif

                        <!-- Overlay actions -->
                        <div class="absolute top-2 right-2 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <div class="flex gap-1">
                                @if($media->is_featured)
                                    <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                                        <i class="fas fa-star"></i>
                                    </span>
                                @endif
                                <button onclick="editMedia({{ $media->id }})"
                                        class="bg-blue-500 hover:bg-blue-600 text-white text-xs px-2 py-1 rounded-full transition-colors duration-200">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button onclick="deleteMedia({{ $media->id }})"
                                        class="bg-red-500 hover:bg-red-600 text-white text-xs px-2 py-1 rounded-full transition-colors duration-200">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Media type indicator -->
                        <div class="absolute bottom-2 left-2">
                            @if($media->is_external)
                                <span class="bg-purple-500 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-external-link-alt"></i>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Media Info -->
                    <div class="p-3">
                        <h4 class="font-medium text-gray-900 text-sm line-clamp-1 mb-1">{{ $media->title }}</h4>
                        @if($media->description)
                            <p class="text-xs text-gray-600 line-clamp-2 mb-2">{{ $media->description }}</p>
                        @endif
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <div>
                                @if($media->is_file)
                                    {{ $media->formatted_size }}
                                @else
                                    {{ ucfirst($media->type) }}
                                @endif
                            </div>
                            <div>{{ $media->user->name }}</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 px-6 py-4">
            {{ $mediaItems->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 py-16">
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Galleria vuota</h3>
                <p class="text-gray-600 mb-8">Inizia aggiungendo foto, video o link esterni alla galleria.</p>
                <div class="flex justify-center gap-4">
                    <button onclick="openUploadModal()"
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                        </svg>
                        Carica File
                    </button>
                    <button onclick="openLinkModal()"
                            class="inline-flex items-center px-6 py-3 bg-purple-50 hover:bg-purple-100 text-purple-700 text-sm font-medium rounded-lg transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        Aggiungi Link
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Upload Modal -->
<div id="uploadModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-xl max-w-lg w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Carica File</h3>
            </div>
            <form id="uploadForm" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">File da caricare</label>
                    <input type="file" name="files[]" multiple accept="image/*,video/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Seleziona immagini e video (max 10MB per file)</p>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeUploadModal()"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                        Annulla
                    </button>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Carica
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Link Modal -->
<div id="linkModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-xl max-w-lg w-full">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Aggiungi Link Esterno</h3>
            </div>
            <form id="linkForm" class="p-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">URL</label>
                    <input type="url" name="url" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="https://www.youtube.com/watch?v=...">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                    <select name="type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="youtube">YouTube</option>
                        <option value="vimeo">Vimeo</option>
                        <option value="instagram">Instagram</option>
                        <option value="external_link">Link Generico</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Titolo (opzionale)</label>
                    <input type="text" name="title"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Titolo del contenuto">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Descrizione (opzionale)</label>
                    <textarea name="description" rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Descrizione del contenuto"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="closeLinkModal()"
                            class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                        Annulla
                    </button>
                    <button type="submit"
                            class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        Aggiungi Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox" class="fixed inset-0 bg-black bg-opacity-90 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="relative max-w-4xl max-h-full">
            <button onclick="closeLightbox()"
                    class="absolute top-4 right-4 text-white hover:text-gray-300 text-2xl z-10">
                <i class="fas fa-times"></i>
            </button>
            <div id="lightboxContent"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Upload functionality
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
    document.getElementById('uploadForm').reset();
}

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Caricamento...';
    submitBtn.disabled = true;

    fetch('{{ route("admin.galleries.upload", $gallery) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeUploadModal();
            location.reload(); // Simple reload for now
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore durante il caricamento');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Link functionality
function openLinkModal() {
    document.getElementById('linkModal').classList.remove('hidden');
}

function closeLinkModal() {
    document.getElementById('linkModal').classList.add('hidden');
    document.getElementById('linkForm').reset();
}

document.getElementById('linkForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Aggiunta...';
    submitBtn.disabled = true;

    fetch('{{ route("admin.galleries.external-link", $gallery) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            closeLinkModal();
            location.reload(); // Simple reload for now
        } else {
            alert('Errore: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore durante l\'aggiunta del link');
    })
    .finally(() => {
        submitBtn.innerHTML = originalText;
        submitBtn.disabled = false;
    });
});

// Lightbox functionality
function openLightbox(url, title, type) {
    const lightbox = document.getElementById('lightbox');
    const content = document.getElementById('lightboxContent');

    if (type === 'image') {
        content.innerHTML = `<img src="${url}" alt="${title}" class="max-w-full max-h-full object-contain">`;
    } else if (type === 'youtube' || type === 'vimeo') {
        content.innerHTML = `<iframe src="${url}" class="w-full h-96" frameborder="0" allowfullscreen></iframe>`;
    } else if (type === 'video') {
        content.innerHTML = `<video controls class="max-w-full max-h-full"><source src="${url}" type="video/mp4"></video>`;
    }

    lightbox.classList.remove('hidden');
}

function closeLightbox() {
    document.getElementById('lightbox').classList.add('hidden');
    document.getElementById('lightboxContent').innerHTML = '';
}

// Media management
function editMedia(mediaId) {
    // TODO: Implement edit media modal
    alert('Funzionalità di modifica in arrivo');
}

function deleteMedia(mediaId) {
    if (confirm('Sei sicuro di voler eliminare questo media?')) {
        fetch(`{{ route("admin.galleries.media.delete", ["gallery" => $gallery, "mediaItem" => ":id"]) }}`.replace(':id', mediaId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.querySelector(`[data-id="${mediaId}"]`).remove();
            } else {
                alert('Errore: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Errore durante l\'eliminazione');
        });
    }
}

// Close modals on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeUploadModal();
        closeLinkModal();
        closeLightbox();
    }
});
</script>
@endpush

@push('styles')
<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.aspect-w-16 {
    position: relative;
}

.aspect-h-12 {
    padding-bottom: 75%;
}

.aspect-w-16 > * {
    position: absolute;
    height: 100%;
    width: 100%;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
}
</style>
@endpush
