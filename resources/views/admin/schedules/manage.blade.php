<x-app-layout>

<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Gestione Orari Corsi
            </h2>
            <p class="text-sm text-gray-600 mt-1">
                Modifica gli orari e le assegnazioni dei corsi
            </p>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
            <a href="{{ route('admin.schedules.index') }}"
               class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 transition-all duration-200">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Vista Calendario
            </a>
        </div>
    </div>

    <!-- Courses Management -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Corsi e Orari</h3>
            <p class="text-sm text-gray-600 mt-1">Clicca su un corso per modificare gli orari</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Corso
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Istruttore
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Orario Attuale
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Sala
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Studenti
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Azioni
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($courses as $course)
                    <tr class="hover:bg-gray-50" data-course-id="{{ $course->id }}">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-gradient-to-r from-rose-400 to-purple-500 flex items-center justify-center">
                                        <span class="text-white font-medium text-sm">{{ strtoupper(substr($course->name, 0, 2)) }}</span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">{{ $course->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $course->level }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $course->instructor?->name ?? 'Non assegnato' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($course->schedule_data && is_array($course->schedule_data) && count($course->schedule_data) > 0)
                                    @foreach($course->schedule_data as $index => $slot)
                                        <div class="text-xs mb-1">
                                            {{ $slot['day'] ?? 'N/A' }}: {{ $slot['start_time'] ?? 'N/A' }} - {{ $slot['end_time'] ?? 'N/A' }}
                                        </div>
                                        @if($index >= 1) @break @endif
                                    @endforeach
                                    @if(count($course->schedule_data) > 2)
                                        <div class="text-xs text-gray-500">+{{ count($course->schedule_data) - 2 }} altri...</div>
                                    @endif
                                @else
                                    Non impostato
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $course->location ?: 'Non assegnata' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $course->enrollments->count() }}/{{ $course->max_students }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editSchedule({{ $course->id }})"
                                    class="text-rose-600 hover:text-rose-900 mr-3">
                                Modifica Orario
                            </button>
                            <a href="{{ route('admin.schedules.show', $course->id) }}"
                               class="text-blue-600 hover:text-blue-900">
                                Dettagli
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="text-gray-400">
                                <svg class="w-12 h-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <h3 class="text-lg font-medium mb-2">Nessun corso trovato</h3>
                                <p class="text-gray-500 mb-4">Inizia creando il tuo primo corso</p>
                                <a href="{{ route('admin.courses.create') }}"
                                   class="inline-flex items-center px-4 py-2 bg-rose-600 text-white text-sm font-medium rounded-lg hover:bg-rose-700">
                                    Crea Nuovo Corso
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Schedule Tools -->
    <div class="mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Time Slots Reference -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Fasce Orarie Disponibili</h3>
            <div class="grid grid-cols-3 gap-2 text-sm">
                @foreach($timeSlots as $slot)
                    @if($loop->index < 18)
                    <span class="px-2 py-1 bg-gray-100 rounded text-center">{{ $slot }}</span>
                    @endif
                @endforeach
            </div>
            <div class="mt-4 text-xs text-gray-500">
                <p><strong>Formato orario:</strong> "Lun-Mer-Ven 18:00-19:30"</p>
                <p><strong>Giorni:</strong> Lun, Mar, Mer, Gio, Ven, Sab, Dom</p>
            </div>
        </div>

        <!-- Available Rooms -->
        <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Sale Disponibili</h3>
            <div class="space-y-2">
                @foreach($rooms as $room)
                <div class="flex items-center justify-between p-2 bg-gray-50 rounded">
                    <span class="text-sm font-medium">{{ $room }}</span>
                    <span class="text-xs text-gray-500">
                        {{ $courses->where('location', $room)->count() }} corsi
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<!-- Edit Schedule Modal -->
<div id="editScheduleModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-1/2 lg:w-1/3 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900" id="modalTitle">Modifica Orario Corso</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <form id="scheduleForm" onsubmit="saveSchedule(event)">
                <input type="hidden" id="courseId" name="course_id">

                <div class="mb-4">
                    <label for="schedule" class="block text-sm font-medium text-gray-700 mb-2">
                        Orario
                    </label>
                    <input type="text"
                           id="schedule"
                           name="schedule"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500"
                           placeholder="es. Lun-Mer-Ven 18:00-19:30"
                           required>
                    <p class="text-xs text-gray-500 mt-1">Formato: Giorni-Separati-Da-Trattino Ora-Inizio-Ora-Fine</p>
                </div>

                <div class="mb-4">
                    <label for="location" class="block text-sm font-medium text-gray-700 mb-2">
                        Sala
                    </label>
                    <select id="location"
                            name="location"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                        <option value="">Seleziona sala...</option>
                        @foreach($rooms as $room)
                        <option value="{{ $room }}">{{ $room }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-6">
                    <label for="instructor_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Istruttore
                    </label>
                    <select id="instructor_id"
                            name="instructor_id"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-rose-500 focus:border-rose-500">
                        <option value="">Seleziona istruttore...</option>
                        @foreach($instructors as $instructor)
                        <option value="{{ $instructor->id }}">{{ $instructor->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex justify-end space-x-3">
                    <button type="button"
                            onclick="closeModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors">
                        Annulla
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-rose-600 text-white rounded-md hover:bg-rose-700 transition-colors">
                        Salva Modifiche
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editSchedule(courseId) {
    const row = document.querySelector(`tr[data-course-id="${courseId}"]`);
    const courseName = row.querySelector('.text-sm.font-medium').textContent;
    const currentSchedule = row.cells[2].textContent.trim();
    const currentLocation = row.cells[3].textContent.trim();

    document.getElementById('modalTitle').textContent = `Modifica Orario - ${courseName}`;
    document.getElementById('courseId').value = courseId;
    document.getElementById('schedule').value = currentSchedule === 'Non impostato' ? '' : currentSchedule;
    document.getElementById('location').value = currentLocation === 'Non assegnata' ? '' : currentLocation;

    document.getElementById('editScheduleModal').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('editScheduleModal').classList.add('hidden');
}

function saveSchedule(event) {
    event.preventDefault();

    const courseId = document.getElementById('courseId').value;
    const formData = new FormData(document.getElementById('scheduleForm'));

    fetch(`/admin/schedules/course/${courseId}`, {
        method: 'PUT',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Errore durante il salvataggio: ' + (data.message || 'Errore sconosciuto'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Errore durante il salvataggio');
    });
}

// Close modal on outside click
document.getElementById('editScheduleModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>
</x-app-layout>
