<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Gallerie
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione gallerie della tua scuola
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
        <li class="text-gray-900 font-medium">Gallerie</li>
    </x-slot>




<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl md:text-2xl font-bold text-gray-900">Gestione Gallerie</h1>
                <p class="text-gray-600">Gestisci le gallerie multimediali della scuola</p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                <a href="{{ route('admin.galleries.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-rose-500 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Nuova Galleria
                </a>
            </div>
        </div>

        <!-- Key Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <x-stats-card
                title="Gallerie Totali"
                :value="number_format($galleries->total())"
                :subtitle="($galleries->where('is_public', true)->count()) . ' pubbliche'"
                icon="images"
                color="blue"
                :change="5"
                changeType="increase"
            />

            <x-stats-card
                title="In Evidenza"
                :value="number_format($galleries->where('is_featured', true)->count())"
                :subtitle="'Gallerie featured'"
                icon="star"
                color="yellow"
                :change="2"
                changeType="increase"
            />

            <x-stats-card
                title="Media Totali"
                :value="number_format($galleries->sum('media_items_count'))"
                :subtitle="'File e video'"
                icon="photo"
                color="green"
                :change="12"
                changeType="increase"
            />

            <x-stats-card
                title="Pubbliche"
                :value="number_format($galleries->where('is_public', true)->count())"
                :subtitle="'Visibili agli studenti'"
                icon="globe"
                color="purple"
                :change="3"
                changeType="increase"
            />
        </div>

        <!-- Filters and Search -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6 mb-6">
            <form method="GET" action="{{ route('admin.galleries.index') }}"
                  class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Ricerca</label>
                    <div class="relative">
                        <input type="text"
                               name="search"
                               value="{{ request('search') }}"
                               placeholder="Titolo, descrizione..."
                               class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tipo</label>
                    <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="">Tutti i tipi</option>
                        @foreach(\App\Models\MediaGallery::getAvailableTypes() as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <!-- Visibility Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Visibilità</label>
                    <select name="visibility" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                        <option value="">Tutte</option>
                        <option value="public" {{ request('visibility') === 'public' ? 'selected' : '' }}>Pubbliche</option>
                        <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                        <option value="featured" {{ request('visibility') === 'featured' ? 'selected' : '' }}>In Evidenza</option>
                    </select>
                </div>
                <!-- Actions -->
                <div class="flex items-end space-x-2">
                    <button type="submit"
                            class="flex-1 bg-rose-600 text-white px-4 py-2 rounded-lg hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-colors duration-200">
                        <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filtra
                    </button>
                    @if(request()->hasAny(['search', 'type', 'visibility']))
                        <a href="{{ route('admin.galleries.index') }}"
                           class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-colors duration-200">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Galleries Table -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900">Gallerie</h3>
                </div>
            </div>

            @if($galleries->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
                    @foreach($galleries as $gallery)
                        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden hover:shadow-xl transition-all duration-200 transform hover:scale-105">
                            <!-- Cover Image -->
                            <div class="aspect-w-16 aspect-h-9 bg-gradient-to-br from-gray-100 to-gray-200">
                                @if($gallery->coverImage && $gallery->coverImage->thumbnail_url)
                                    <img src="{{ $gallery->coverImage->thumbnail_url }}"
                                         alt="{{ $gallery->title }}"
                                         class="w-full h-48 object-cover">
                                @elseif($gallery->mediaItems->first())
                                    <img src="{{ $gallery->mediaItems->first()->thumbnail_url }}"
                                         alt="{{ $gallery->title }}"
                                         class="w-full h-48 object-cover">
                                @else
                                    <div class="w-full h-48 bg-gradient-to-br from-rose-100 to-purple-100 flex items-center justify-center">
                                        <svg class="w-12 h-12 text-rose-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif

                                <!-- Overlay with type and visibility -->
                                <div class="absolute top-2 left-2 flex gap-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-100 text-rose-800">
                                        {{ \App\Models\MediaGallery::getAvailableTypes()[$gallery->type] }}
                                    </span>
                                    @if($gallery->is_featured)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                            </svg>
                                            Featured
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

                                <!-- Media count -->
                                <div class="absolute bottom-2 right-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-black bg-opacity-70 text-white backdrop-blur-sm">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        {{ $gallery->media_items_count }}
                                    </span>
                                </div>
                            </div>

                            <!-- Content -->
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-900 mb-1 line-clamp-1">{{ $gallery->title }}</h3>
                                @if($gallery->description)
                                    <p class="text-sm text-gray-600 mb-2 line-clamp-2">{{ $gallery->description }}</p>
                                @endif

                                <div class="flex items-center justify-between text-xs text-gray-500 mb-3">
                                    <div class="flex items-center">
                                        @if($gallery->course)
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                                            </svg>
                                            {{ $gallery->course->name }}
                                        @else
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            Generale
                                        @endif
                                    </div>
                                    <div class="flex items-center">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                        </svg>
                                        {{ $gallery->createdBy->name }}
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.galleries.show', $gallery) }}"
                                       class="flex-1 bg-rose-50 hover:bg-rose-100 text-rose-700 text-sm px-3 py-2 rounded-lg text-center transition-colors duration-200 flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        Visualizza
                                    </a>
                                    <a href="{{ route('admin.galleries.edit', $gallery) }}"
                                       class="bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm px-3 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                    <form method="POST" action="{{ route('admin.galleries.destroy', $gallery) }}"
                                          class="inline"
                                          onsubmit="return confirm('Sei sicuro di voler eliminare questa galleria e tutti i suoi media?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-50 hover:bg-red-100 text-red-700 text-sm px-3 py-2 rounded-lg transition-colors duration-200 flex items-center justify-center">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

        <!-- Pagination -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 px-6 py-4">
            {{ $galleries->withQueryString()->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 py-12">
            <div class="text-center">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="text-xl font-medium text-gray-900 mb-2">Nessuna galleria trovata</h3>
                <p class="text-gray-600 mb-6">
                    @if(request()->hasAny(['search', 'type', 'visibility']))
                        Modifica i filtri per trovare altre gallerie o crea una nuova galleria.
                    @else
                        Inizia creando la prima galleria multimediale per la tua scuola.
                    @endif
                </p>
                <div class="flex justify-center gap-4">
                    <a href="{{ route('admin.galleries.create') }}"
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Crea Prima Galleria
                    </a>
                    @if(request()->hasAny(['search', 'type', 'visibility']))
                        <a href="{{ route('admin.galleries.index') }}"
                           class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Reset Filtri
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@push('styles')
<style nonce="@cspNonce">
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
    padding-bottom: 56.25%; /* 16:9 ratio */
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
</x-app-layout>
