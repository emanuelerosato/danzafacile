@extends('layouts.app')

@section('title', 'Gestione Gallerie')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Gestione Gallerie</h1>
                    <p class="text-sm text-gray-600 mt-1">Gestisci le gallerie multimediali della scuola</p>
                </div>
                <a href="{{ route('admin.galleries.create') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200 flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Nuova Galleria
                </a>
            </div>
        </div>

        <!-- Stats -->
        <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $galleries->total() }}</div>
                    <div class="text-sm text-gray-600">Gallerie Totali</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $galleries->where('is_public', true)->count() }}</div>
                    <div class="text-sm text-gray-600">Pubbliche</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ $galleries->where('is_featured', true)->count() }}</div>
                    <div class="text-sm text-gray-600">In Evidenza</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ $galleries->sum('media_items_count') }}</div>
                    <div class="text-sm text-gray-600">Media Totali</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-6">
        <div class="px-6 py-4">
            <form method="GET" class="flex flex-wrap gap-4 items-center">
                <div class="flex-1 min-w-64">
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Cerca gallerie..."
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <select name="type" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tutti i tipi</option>
                        @foreach(\App\Models\MediaGallery::getAvailableTypes() as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <select name="visibility" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Tutte</option>
                        <option value="public" {{ request('visibility') === 'public' ? 'selected' : '' }}>Pubbliche</option>
                        <option value="private" {{ request('visibility') === 'private' ? 'selected' : '' }}>Private</option>
                        <option value="featured" {{ request('visibility') === 'featured' ? 'selected' : '' }}>In Evidenza</option>
                    </select>
                </div>
                <button type="submit" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-search"></i> Filtra
                </button>
                @if(request()->hasAny(['search', 'type', 'visibility']))
                    <a href="{{ route('admin.galleries.index') }}"
                       class="text-gray-500 hover:text-gray-700 px-3 py-2">
                        <i class="fas fa-times"></i> Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    <!-- Galleries Grid -->
    @if($galleries->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            @foreach($galleries as $gallery)
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow duration-200">
                    <!-- Cover Image -->
                    <div class="aspect-w-16 aspect-h-9 bg-gray-100">
                        @if($gallery->coverImage && $gallery->coverImage->thumbnail_url)
                            <img src="{{ $gallery->coverImage->thumbnail_url }}"
                                 alt="{{ $gallery->title }}"
                                 class="w-full h-48 object-cover">
                        @elseif($gallery->mediaItems->first())
                            <img src="{{ $gallery->mediaItems->first()->thumbnail_url }}"
                                 alt="{{ $gallery->title }}"
                                 class="w-full h-48 object-cover">
                        @else
                            <div class="w-full h-48 bg-gray-200 flex items-center justify-center">
                                <i class="fas fa-images text-4xl text-gray-400"></i>
                            </div>
                        @endif

                        <!-- Overlay with type and visibility -->
                        <div class="absolute top-2 left-2 flex gap-2">
                            <span class="bg-blue-600 text-white text-xs px-2 py-1 rounded-full">
                                {{ \App\Models\MediaGallery::getAvailableTypes()[$gallery->type] }}
                            </span>
                            @if($gallery->is_featured)
                                <span class="bg-yellow-500 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-star"></i>
                                </span>
                            @endif
                            @if($gallery->is_public)
                                <span class="bg-green-500 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-globe"></i>
                                </span>
                            @else
                                <span class="bg-gray-500 text-white text-xs px-2 py-1 rounded-full">
                                    <i class="fas fa-lock"></i>
                                </span>
                            @endif
                        </div>

                        <!-- Media count -->
                        <div class="absolute bottom-2 right-2">
                            <span class="bg-black bg-opacity-70 text-white text-xs px-2 py-1 rounded-full">
                                <i class="fas fa-images"></i> {{ $gallery->media_items_count }}
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
                            <div>
                                @if($gallery->course)
                                    <i class="fas fa-graduation-cap"></i> {{ $gallery->course->name }}
                                @else
                                    <i class="fas fa-school"></i> Generale
                                @endif
                            </div>
                            <div>
                                <i class="fas fa-user"></i> {{ $gallery->createdBy->name }}
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-2">
                            <a href="{{ route('admin.galleries.show', $gallery) }}"
                               class="flex-1 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm px-3 py-2 rounded-lg text-center transition-colors duration-200">
                                <i class="fas fa-eye"></i> Visualizza
                            </a>
                            <a href="{{ route('admin.galleries.edit', $gallery) }}"
                               class="bg-gray-50 hover:bg-gray-100 text-gray-700 text-sm px-3 py-2 rounded-lg transition-colors duration-200">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.galleries.destroy', $gallery) }}"
                                  class="inline"
                                  onsubmit="return confirm('Sei sicuro di voler eliminare questa galleria e tutti i suoi media?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="bg-red-50 hover:bg-red-100 text-red-700 text-sm px-3 py-2 rounded-lg transition-colors duration-200">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-6 py-4">
            {{ $galleries->withQueryString()->links() }}
        </div>
    @else
        <!-- Empty State -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 py-12">
            <div class="text-center">
                <i class="fas fa-images text-6xl text-gray-300 mb-4"></i>
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
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors duration-200">
                        <i class="fas fa-plus"></i> Crea Prima Galleria
                    </a>
                    @if(request()->hasAny(['search', 'type', 'visibility']))
                        <a href="{{ route('admin.galleries.index') }}"
                           class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-3 rounded-lg transition-colors duration-200">
                            <i class="fas fa-times"></i> Reset Filtri
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

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
@endsection