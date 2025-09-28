<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Crea Nuova Galleria
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Configura una nuova galleria multimediale
                </p>
            </div>
            <a href="{{ route('admin.galleries.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Torna alle Gallerie
            </a>
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
        <li class="text-gray-900 font-medium">Crea Nuova</li>
    </x-slot>

<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <!-- Form -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
            <form method="POST" action="{{ route('admin.galleries.store') }}" class="p-6 space-y-6">
            @csrf

            <!-- Title and Description -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                        Titolo Galleria <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="title"
                           name="title"
                           value="{{ old('title') }}"
                           required
                           maxlength="255"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('title') border-red-500 @enderror"
                           placeholder="Es. Spettacolo di Fine Anno 2024">
                    @error('title')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        Tipo Galleria <span class="text-red-500">*</span>
                    </label>
                    <select id="type"
                            name="type"
                            required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('type') border-red-500 @enderror">
                        <option value="">Seleziona tipo</option>
                        @foreach($galleryTypes as $key => $label)
                            <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Descrizione
                </label>
                <textarea id="description"
                          name="description"
                          rows="4"
                          maxlength="1000"
                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('description') border-red-500 @enderror"
                          placeholder="Descrizione della galleria (opzionale)">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Massimo 1000 caratteri</p>
            </div>

            <!-- Course Assignment -->
            <div>
                <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">
                    Corso Associato
                </label>
                <select id="course_id"
                        name="course_id"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200 @error('course_id') border-red-500 @enderror">
                    <option value="">Nessun corso (galleria generale)</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>
                            {{ $course->name }}
                        </option>
                    @endforeach
                </select>
                @error('course_id')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1">Associa la galleria a un corso specifico (opzionale)</p>
            </div>

            <!-- Gallery Settings -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Impostazioni Galleria</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Visibility -->
                    <div>
                        <div class="flex items-center">
                            <input type="hidden" name="is_public" value="0">
                            <input type="checkbox"
                                   id="is_public"
                                   name="is_public"
                                   value="1"
                                   {{ old('is_public') ? 'checked' : '' }}
                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <label for="is_public" class="ml-2 text-sm text-gray-700">
                                Galleria Pubblica
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-6">
                            Le gallerie pubbliche sono visibili a tutti gli studenti e visitatori
                        </p>
                    </div>

                    <!-- Featured -->
                    <div>
                        <div class="flex items-center">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox"
                                   id="is_featured"
                                   name="is_featured"
                                   value="1"
                                   {{ old('is_featured') ? 'checked' : '' }}
                                   class="h-4 w-4 text-rose-600 focus:ring-rose-500 border-gray-300 rounded">
                            <label for="is_featured" class="ml-2 text-sm text-gray-700">
                                Galleria in Evidenza
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1 ml-6">
                            Le gallerie in evidenza appaiono in primo piano nella homepage
                        </p>
                    </div>
                </div>
            </div>

            <!-- Type-specific Information -->
            <div class="bg-rose-50 border border-rose-200 rounded-lg p-4" id="typeInfo" style="display: none;">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-rose-500 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-rose-700">
                        <div id="typeInfoContent"></div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="border-t border-gray-200 pt-6 flex justify-between">
                <a href="{{ route('admin.galleries.index') }}"
                   class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Annulla
                </a>
                <button type="submit"
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                    Crea Galleria
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const typeSelect = document.getElementById('type');
    const typeInfo = document.getElementById('typeInfo');
    const typeInfoContent = document.getElementById('typeInfoContent');

    const typeDescriptions = {
        'foto': 'Galleria dedicata esclusivamente alle foto. Ideale per documentare eventi, spettacoli e momenti importanti.',
        'video': 'Galleria per video. Supporta upload di file video e incorporamento di video da YouTube e Vimeo.',
        'misto': 'Galleria che può contenere sia foto che video. La scelta più versatile per contenuti vari.',
        'spettacoli': 'Galleria specifica per spettacoli e esibizioni. Può contenere foto, video e link esterni.',
        'lezioni': 'Galleria per documentare le lezioni e le attività didattiche.',
        'eventi': 'Galleria per eventi speciali, workshop e manifestazioni della scuola.'
    };

    function updateTypeInfo() {
        const selectedType = typeSelect.value;
        if (selectedType && typeDescriptions[selectedType]) {
            typeInfoContent.textContent = typeDescriptions[selectedType];
            typeInfo.style.display = 'block';
        } else {
            typeInfo.style.display = 'none';
        }
    }

    typeSelect.addEventListener('change', updateTypeInfo);
    updateTypeInfo(); // Initialize on page load
});
</script>
@endpush

</x-app-layout>
