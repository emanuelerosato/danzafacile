<x-app-layout>


<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Breadcrumb -->
    <nav class="mb-6">
        <ol class="flex items-center space-x-2 text-sm text-gray-600">
            <li><a href="{{ route('admin.dashboard') }}" class="hover:text-gray-900">Dashboard</a></li>
            <li><i class="fas fa-chevron-right text-gray-400 mx-2"></i></li>
            <li><a href="{{ route('admin.galleries.index') }}" class="hover:text-gray-900">Gallerie</a></li>
            <li><i class="fas fa-chevron-right text-gray-400 mx-2"></i></li>
            <li class="text-gray-900">Crea Nuova</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-sm border border-gray-200 mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Crea Nuova Galleria</h1>
                    <p class="text-sm text-gray-600 mt-1">Configura una nuova galleria multimediale</p>
                </div>
                <a href="{{ route('admin.galleries.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left"></i> Torna alle Gallerie
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20-sm border border-gray-200">
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
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('title') border-red-500 @enderror"
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
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror">
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
                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('description') border-red-500 @enderror"
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
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('course_id') border-red-500 @enderror">
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
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
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
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
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
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4" id="typeInfo" style="display: none;">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                    <div class="text-sm text-blue-700">
                        <div id="typeInfoContent"></div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="border-t border-gray-200 pt-6 flex justify-between">
                <a href="{{ route('admin.galleries.index') }}"
                   class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-times"></i> Annulla
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-save"></i> Crea Galleria
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
