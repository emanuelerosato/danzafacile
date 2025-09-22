@props(['course' => null])

<div class="course-form-section">
    <h3>Informazioni Generali</h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Course Name -->
        <div class="course-field-group">
            <label for="name" class="course-field-label">Nome Corso *</label>
            <input type="text"
                   id="name"
                   name="name"
                   value="{{ old('name', $course->name ?? '') }}"
                   required
                   class="course-field-input"
                   placeholder="Es: Danza Classica Intermedio">
            @error('name')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Course Type -->
        <div class="course-field-group">
            <label for="type" class="course-field-label">Tipo Corso *</label>
            <select id="type" name="type" required class="course-field-select">
                <option value="">Seleziona tipo</option>
                <option value="Danza Classica" {{ old('type', $course->type ?? '') == 'Danza Classica' ? 'selected' : '' }}>Danza Classica</option>
                <option value="Danza Moderna" {{ old('type', $course->type ?? '') == 'Danza Moderna' ? 'selected' : '' }}>Danza Moderna</option>
                <option value="Hip Hop" {{ old('type', $course->type ?? '') == 'Hip Hop' ? 'selected' : '' }}>Hip Hop</option>
                <option value="Jazz" {{ old('type', $course->type ?? '') == 'Jazz' ? 'selected' : '' }}>Jazz</option>
                <option value="Contemporary" {{ old('type', $course->type ?? '') == 'Contemporary' ? 'selected' : '' }}>Contemporary</option>
                <option value="Altro" {{ old('type', $course->type ?? '') == 'Altro' ? 'selected' : '' }}>Altro</option>
            </select>
            @error('type')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Difficulty Level -->
        <div class="course-field-group">
            <label for="difficulty_level" class="course-field-label">Livello</label>
            <select id="difficulty_level" name="difficulty_level" class="course-field-select">
                <option value="">Seleziona livello</option>
                <option value="Principiante" {{ old('difficulty_level', $course->difficulty_level ?? '') == 'Principiante' ? 'selected' : '' }}>Principiante</option>
                <option value="Intermedio" {{ old('difficulty_level', $course->difficulty_level ?? '') == 'Intermedio' ? 'selected' : '' }}>Intermedio</option>
                <option value="Avanzato" {{ old('difficulty_level', $course->difficulty_level ?? '') == 'Avanzato' ? 'selected' : '' }}>Avanzato</option>
                <option value="Professionale" {{ old('difficulty_level', $course->difficulty_level ?? '') == 'Professionale' ? 'selected' : '' }}>Professionale</option>
            </select>
            @error('difficulty_level')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Duration -->
        <div class="course-field-group">
            <label for="duration_weeks" class="course-field-label">Durata (settimane)</label>
            <input type="number"
                   id="duration_weeks"
                   name="duration_weeks"
                   value="{{ old('duration_weeks', $course->duration_weeks ?? '') }}"
                   min="1"
                   max="52"
                   class="course-field-input"
                   placeholder="Es: 12">
            @error('duration_weeks')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Max Students -->
        <div class="course-field-group">
            <label for="max_students" class="course-field-label">Massimo Studenti</label>
            <input type="number"
                   id="max_students"
                   name="max_students"
                   value="{{ old('max_students', $course->max_students ?? '') }}"
                   min="1"
                   max="50"
                   class="course-field-input"
                   placeholder="Es: 15">
            @error('max_students')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Price -->
        <div class="course-field-group">
            <label for="price" class="course-field-label">Prezzo (€)</label>
            <input type="number"
                   id="price"
                   name="price"
                   value="{{ old('price', $course->price ?? '') }}"
                   min="0"
                   step="0.01"
                   class="course-field-input"
                   placeholder="Es: 80.00">
            @error('price')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Description -->
    <div class="mt-6">
        <div class="course-field-group">
            <label for="description" class="course-field-label">Descrizione</label>
            <textarea id="description"
                      name="description"
                      class="course-field-textarea"
                      placeholder="Descrivi il corso, gli obiettivi e cosa includerà...">{{ old('description', $course->description ?? '') }}</textarea>
            @error('description')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <!-- Status -->
    <div class="mt-6">
        <div class="course-field-group">
            <label for="status" class="course-field-label">Stato Corso</label>
            <select id="status" name="status" class="course-field-select">
                <option value="draft" {{ old('status', $course->status ?? 'draft') == 'draft' ? 'selected' : '' }}>Bozza</option>
                <option value="active" {{ old('status', $course->status ?? '') == 'active' ? 'selected' : '' }}>Attivo</option>
                <option value="inactive" {{ old('status', $course->status ?? '') == 'inactive' ? 'selected' : '' }}>Inattivo</option>
                <option value="completed" {{ old('status', $course->status ?? '') == 'completed' ? 'selected' : '' }}>Completato</option>
            </select>
            @error('status')
                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>