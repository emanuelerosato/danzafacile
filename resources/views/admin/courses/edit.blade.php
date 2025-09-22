<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifica Corso
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione modifica della tua scuola
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
            <a href="{{ route('admin.courses.index') }}" class="text-gray-500 hover:text-gray-700">Corsi</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900">Modifica Corso</li>
    </x-slot>

    <div class="course-edit-container py-8">
        <form method="POST" action="{{ route('admin.courses.update', $course) }}" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')

            {{-- Course Information Section --}}
            <x-admin.courses.course-info-form :course="$course" />

            {{-- Schedule Management Section --}}
            <div class="course-form-section">
                <h3>Orari del Corso</h3>

                <div id="schedule-container" class="schedule-container">
                    @if(!empty($scheduleData))
                        @foreach($scheduleData as $index => $slot)
                            <div class="schedule-slot">
                                <div class="schedule-slot-header">
                                    <h4 class="schedule-slot-title">
                                        {{ $slot['day'] ? $slot['day'] . ' - ' : '' }}Orario {{ $index + 1 }}
                                    </h4>
                                    @if($index > 0)
                                        <button type="button" onclick="removeScheduleSlot(this)" class="schedule-remove-btn">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                            </svg>
                                            <span>Rimuovi</span>
                                        </button>
                                    @endif
                                </div>

                                <div class="schedule-grid">
                                    <div class="course-field-group">
                                        <label class="course-field-label">Giorno</label>
                                        <select name="schedule_slots[{{ $index }}][day]" class="course-field-select">
                                            <option value="">Seleziona giorno</option>
                                            @foreach(['Lunedì', 'Martedì', 'Mercoledì', 'Giovedì', 'Venerdì', 'Sabato', 'Domenica'] as $day)
                                                <option value="{{ $day }}" {{ ($slot['day'] ?? '') == $day ? 'selected' : '' }}>{{ $day }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="course-field-group">
                                        <label class="course-field-label">Ora Inizio</label>
                                        <input type="time" name="schedule_slots[{{ $index }}][start_time]"
                                               value="{{ $slot['start_time'] ?? '' }}"
                                               class="course-field-input"
                                               onchange="calculateDuration(this)">
                                    </div>

                                    <div class="course-field-group">
                                        <label class="course-field-label">Ora Fine</label>
                                        <input type="time" name="schedule_slots[{{ $index }}][end_time]"
                                               value="{{ $slot['end_time'] ?? '' }}"
                                               class="course-field-input"
                                               onchange="calculateDuration(this)">
                                    </div>

                                    <div class="course-field-group">
                                        <label class="course-field-label">Durata</label>
                                        <div class="schedule-duration-display">
                                            <span class="duration-display">{{ $slot['duration'] ?? '--' }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <label class="course-field-label">Sala</label>
                                    <select name="schedule_slots[{{ $index }}][room_id]" class="course-field-select room-dropdown">
                                        <option value="">Seleziona una sala</option>
                                        @foreach($availableRooms as $index => $roomName)
                                            <option value="{{ $index }}" {{ ($slot['room_id'] ?? '') == $index ? 'selected' : '' }}>
                                                {{ $roomName }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <button type="button" id="add-schedule-slot" class="add-schedule-btn group" onclick="addScheduleSlot()">
                    <svg class="add-schedule-btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    <span class="add-schedule-btn-text">Aggiungi Orario</span>
                </button>
            </div>

            {{-- Equipment Section --}}
            <div class="course-form-section">
                <h3>Attrezzatura Necessaria</h3>
                <div id="equipment-container" class="dynamic-list-container">
                    @if(!empty($equipment))
                        @foreach($equipment as $index => $item)
                            <div class="dynamic-list-item">
                                <input type="text" name="equipment[{{ $index }}]" value="{{ $item }}"
                                       placeholder="Aggiungi attrezzatura..." class="dynamic-list-input">
                                <button type="button" onclick="this.parentElement.remove()" class="dynamic-list-remove">
                                    Rimuovi
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add-equipment" class="dynamic-list-add">
                    Aggiungi Attrezzatura
                </button>
            </div>

            {{-- Objectives Section --}}
            <div class="course-form-section">
                <h3>Obiettivi del Corso</h3>
                <div id="objectives-container" class="dynamic-list-container">
                    @if(!empty($objectives))
                        @foreach($objectives as $index => $item)
                            <div class="dynamic-list-item">
                                <input type="text" name="objectives[{{ $index }}]" value="{{ $item }}"
                                       placeholder="Aggiungi obiettivo..." class="dynamic-list-input">
                                <button type="button" onclick="this.parentElement.remove()" class="dynamic-list-remove">
                                    Rimuovi
                                </button>
                            </div>
                        @endforeach
                    @endif
                </div>
                <button type="button" id="add-objective" class="dynamic-list-add">
                    Aggiungi Obiettivo
                </button>
            </div>

            {{-- Media Gallery Section --}}
            @if($course->media && $course->media->count() > 0)
            <div class="course-form-section">
                <h3>Galleria Media</h3>
                <div class="media-gallery-container">
                    @foreach($course->media as $media)
                        <div class="media-gallery-item group">
                            @if($media->type === 'image')
                                <img src="{{ $media->url }}" alt="Course media" class="media-gallery-image">
                            @else
                                <video class="media-gallery-image" controls>
                                    <source src="{{ $media->url }}" type="video/mp4">
                                </video>
                            @endif
                            <div class="media-gallery-overlay">
                                <button type="button" class="media-gallery-remove" onclick="removeMedia({{ $media->id }})">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Action Buttons --}}
            <div class="course-action-buttons">
                <div class="flex space-x-4">
                    <a href="{{ route('admin.courses.index') }}" class="course-btn-secondary">
                        Annulla
                    </a>
                    <button type="submit" name="action" value="save" class="course-btn-primary">
                        Salva Modifiche
                    </button>
                </div>

                <div class="flex space-x-2">
                    <button type="submit" name="action" value="duplicate" class="course-btn-secondary">
                        Duplica Corso
                    </button>
                    <button type="button" onclick="deleteCourse()" class="course-btn-danger">
                        Elimina Corso
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Include CSS and JS --}}
    @push('styles')
        @vite('resources/css/admin/courses/course-edit.css')
    @endpush

    @push('scripts')
        @vite('resources/js/admin/courses/course-edit.js')
        <script>
            // Pass data to JavaScript modules
            window.availableRooms = @json(collect($availableRooms)->mapWithKeys(function($room, $index) { return [$index => $room]; })->toArray());
            window.scheduleSlotIndex = {{ count($scheduleData ?? []) }};

            // Legacy functions for onclick handlers (will be deprecated)
            function deleteCourse() {
                if (confirm('Sei sicuro di voler eliminare questo corso? Questa azione non può essere annullata.')) {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.courses.destroy", $course) }}';
                    form.innerHTML = '@csrf @method("DELETE")';
                    document.body.appendChild(form);
                    form.submit();
                }
            }

            function removeMedia(mediaId) {
                if (confirm('Sei sicuro di voler rimuovere questo media?')) {
                    // Implementation for media removal
                    console.log('Remove media:', mediaId);
                }
            }
        </script>
    @endpush
</x-app-layout>
