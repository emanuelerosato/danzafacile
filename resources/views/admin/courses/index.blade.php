<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Gestione Corsi
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Gestione corsi della tua scuola
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
        <li class="text-gray-900 font-medium">Corsi</li>
    </x-slot>



<div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8"><div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-900">Gestione Corsi</h1>
            <p class="text-gray-600">Tutti i corsi della tua scuola di danza</p>
        </div>
        <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
            <a href="{{ route('admin.courses.create') }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nuovo Corso
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    <!-- Key Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <x-stats-card
            title="Corsi Totali"
            :value="number_format($stats['total_courses'] ?? 0)"
            :subtitle="($stats['active_courses'] ?? 0) . ' attivi'"
            icon="academic-cap"
            color="blue"
            :change="$stats['course_change'] ?? 0"
            :changeType="($stats['course_change'] ?? 0) >= 0 ? 'increase' : 'decrease'"
        />

        <x-stats-card
            title="Prossimi Corsi"
            :value="number_format($stats['upcoming_courses'] ?? 0)"
            :subtitle="'In arrivo'"
            icon="clock"
            color="green"
            :change="$stats['upcoming_change'] ?? 0"
            changeType="increase"
        />

        <x-stats-card
            title="Iscrizioni"
            :value="number_format($stats['total_enrollments'] ?? 0)"
            :subtitle="'Totali'"
            icon="users"
            color="purple"
            :change="$stats['enrollment_change'] ?? 0"
            :changeType="($stats['enrollment_change'] ?? 0) >= 0 ? 'increase' : 'decrease'"
        />

        <x-stats-card
            title="Performance"
            :value="$stats['performance_rate'] . '%'"
            :subtitle="'Tasso attivazione'"
            icon="chart-bar"
            color="rose"
            :change="abs($stats['performance_rate'] - 75)"
            :changeType="$stats['performance_rate'] > 75 ? 'increase' : 'decrease'"
        />
    </div>

    <!-- Course List -->
    <div class="bg-white/80 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">I Tuoi Corsi ({{ $courses->total() ?? 0 }})</h3>
        </div>

        @if($courses->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($courses as $course)
                    <div class="p-6 hover:bg-gray-50" data-course-id="{{ $course->id }}">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-gradient-to-r from-rose-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-lg">
                                            {{ strtoupper(substr($course->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-lg font-medium text-gray-900 truncate">{{ $course->name }}</h4>
                                        <div class="flex items-center space-x-4 mt-1">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->level == 'beginner' ? 'bg-green-100 text-green-800' : ($course->level == 'intermediate' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                                {{ ucfirst($course->level) }}
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                {{ $course->formatted_price }} /mese
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                Max {{ $course->max_students }} studenti
                                            </span>
                                            @if($course->instructor)
                                                <span class="text-sm text-gray-500">
                                                    Istruttore: {{ $course->instructor->name }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($course->description)
                                            <p class="text-sm text-gray-600 mt-2 truncate">{{ $course->description }}</p>
                                        @endif
                                        <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                            <span>Inizio: {{ $course->start_date->format('d/m/Y') }}</span>
                                            @if($course->end_date)
                                                <span>Fine: {{ $course->end_date->format('d/m/Y') }}</span>
                                            @endif
                                            @if($course->location)
                                                <span>📍 {{ $course->location }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $course->active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                    {{ $course->active ? 'Attivo' : 'Non attivo' }}
                                </span>
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.courses.edit', $course) }}"
                                       class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                        Modifica
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <a href="{{ route('admin.courses.show', $course) }}"
                                       class="text-gray-600 hover:text-gray-900 text-sm font-medium">
                                        Dettagli
                                    </a>
                                    <span class="text-gray-300">|</span>
                                    <button onclick="showDeleteModal({{ $course->id }}, '{{ addslashes($course->name) }}', {{ $course->enrollments->count() }})"
                                            class="text-red-600 hover:text-red-900 text-sm font-medium">
                                        Elimina
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            @if(method_exists($courses, 'links'))
                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
                    {{ $courses->links() }}
                </div>
            @endif
        @else
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun corso</h3>
                <p class="mt-1 text-sm text-gray-500">Inizia creando il tuo primo corso.</p>
                <div class="mt-6">
                    <a href="{{ route('admin.courses.create') }}"
                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-rose-500 to-purple-600 hover:from-rose-600 hover:to-purple-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Nuovo Corso
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 hidden z-50">
    <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <!-- Modal Header -->
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center">
                <div class="flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100">
                    <svg class="h-6 w-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">Eliminare corso?</h3>
                    <p class="text-sm text-gray-500">Questa azione non può essere annullata</p>
                </div>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-sm text-gray-700 mb-4">
                Sei sicuro di voler eliminare il corso <strong id="courseName" class="text-gray-900"></strong>?
            </p>
            <div id="courseWarnings" class="space-y-2"></div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-end space-x-3 rounded-b-lg">
            <button id="cancelDelete" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Annulla
            </button>
            <button id="confirmDelete" class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-red-600 border border-transparent rounded-md shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50 disabled:cursor-not-allowed">
                <svg id="deleteSpinner" class="hidden animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <span id="deleteButtonText">Elimina corso</span>
            </button>
        </div>
    </div>
</div>

<!-- Toast Notification -->
<div id="toast" class="fixed top-4 right-4 max-w-xs bg-white border border-gray-200 rounded-xl shadow-lg z-50 hidden">
    <div class="flex p-4">
        <div class="flex-shrink-0">
            <svg id="toastIcon" class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
        </div>
        <div class="ml-3">
            <p id="toastMessage" class="text-sm text-gray-700"></p>
        </div>
        <div class="ml-auto pl-3">
            <button onclick="hideToast()" class="inline-flex text-gray-400 hover:text-gray-600">
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        </div>
    </div>
</div>

<script>
    let currentCourseId = null;

    // Show delete modal with course information
    window.showDeleteModal = function(courseId, courseName, enrollmentCount) {
        currentCourseId = courseId;

        // Set course name
        document.getElementById('courseName').textContent = courseName;

        // Clear previous warnings
        const warningsDiv = document.getElementById('courseWarnings');
        warningsDiv.innerHTML = '';

        // Add warnings based on course data
        if (enrollmentCount > 0) {
            warningsDiv.innerHTML += `
                <div class="flex items-center p-2 text-sm text-red-700 bg-red-100 rounded-lg">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    Corso con ${enrollmentCount} student${enrollmentCount === 1 ? 'e' : 'i'} iscritt${enrollmentCount === 1 ? 'o' : 'i'}
                </div>
            `;
        }

        // Show modal
        document.getElementById('deleteModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };

    // Hide delete modal
    function hideDeleteModal() {
        document.getElementById('deleteModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
        currentCourseId = null;
    }

    // Show toast notification
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const icon = document.getElementById('toastIcon');
        const messageEl = document.getElementById('toastMessage');

        messageEl.textContent = message;

        // Set icon and colors based on type
        if (type === 'success') {
            toast.className = 'fixed top-4 right-4 max-w-xs bg-green-50 border border-green-200 rounded-xl shadow-lg z-50';
            icon.className = 'h-4 w-4 text-green-600';
            icon.innerHTML = '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>';
        } else {
            toast.className = 'fixed top-4 right-4 max-w-xs bg-red-50 border border-red-200 rounded-xl shadow-lg z-50';
            icon.className = 'h-4 w-4 text-red-600';
            icon.innerHTML = '<path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>';
        }

        toast.classList.remove('hidden');

        // Auto hide after 4 seconds
        setTimeout(() => {
            hideToast();
        }, 4000);
    }

    // Hide toast notification
    function hideToast() {
        document.getElementById('toast').classList.add('hidden');
    }

    // Delete course function
    window.deleteCourse = async function() {
        if (!currentCourseId) return;

        const deleteButton = document.getElementById('confirmDelete');
        const deleteButtonText = document.getElementById('deleteButtonText');
        const deleteSpinner = document.getElementById('deleteSpinner');

        // Show loading state
        deleteButton.disabled = true;
        deleteButtonText.textContent = 'Eliminando...';
        deleteSpinner.classList.remove('hidden');

        try {
            // Create form data with method spoofing
            const formData = new FormData();
            formData.append('_method', 'DELETE');
            formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}');

            const response = await fetch(`{{ url('/admin/courses') }}/${currentCourseId}`, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            });

            console.log('Response status:', response.status, 'OK:', response.ok);

            // Check if course was actually deleted (status 200 or redirect 302)
            if (response.ok || response.status === 302) {
                try {
                    const data = await response.json();
                    showToast(data.message || 'Corso eliminato con successo', 'success');
                    console.log('Server response:', data);
                } catch (e) {
                    console.log('JSON parse failed, but response was ok');
                    showToast('Corso eliminato con successo', 'success');
                }

                // Remove course card from DOM (optimistic update)
                console.log('Looking for course card with ID:', currentCourseId);
                const courseCard = document.querySelector(`[data-course-id="${currentCourseId}"]`);
                console.log('Found course card:', courseCard);

                if (courseCard) {
                    console.log('Removing course card...');
                    courseCard.style.transition = 'all 0.5s ease-out';
                    courseCard.style.opacity = '0';
                    courseCard.style.transform = 'translateX(-100%)';
                    setTimeout(() => {
                        courseCard.remove();
                        console.log('Course card removed from DOM');

                        // Check if no courses left to show empty state
                        const remainingCards = document.querySelectorAll('[data-course-id]');
                        console.log('Remaining course cards:', remainingCards.length);
                        if (remainingCards.length === 0) {
                            console.log('No courses left, reloading page...');
                            setTimeout(() => location.reload(), 500);
                        }
                    }, 500);
                } else {
                    console.log('Course card not found, reloading page...');
                    setTimeout(() => location.reload(), 1000);
                }

            } else {
                console.log('Response not ok, status:', response.status);
                try {
                    const errorData = await response.json();
                    console.log('Error response:', errorData);
                    showToast(errorData.message || 'Errore durante l\'eliminazione', 'error');
                } catch (e) {
                    console.log('Could not parse error response');
                    showToast('Errore durante l\'eliminazione del corso', 'error');
                }
            }
        } catch (error) {
            console.error('Network error:', error);
            showToast('Errore di connessione', 'error');
        } finally {
            // Reset button state
            deleteButton.disabled = false;
            deleteButtonText.textContent = 'Elimina corso';
            deleteSpinner.classList.add('hidden');
            hideDeleteModal();
        }
    };

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Cancel delete
        document.getElementById('cancelDelete').addEventListener('click', hideDeleteModal);

        // Confirm delete
        document.getElementById('confirmDelete').addEventListener('click', deleteCourse);

        // Close modal on background click
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                hideDeleteModal();
            }
        });

        // ESC key to close modal
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideDeleteModal();
            }
        });
    });
</script>
</x-app-layout>
