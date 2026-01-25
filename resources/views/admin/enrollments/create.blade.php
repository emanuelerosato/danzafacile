<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Nuova Iscrizione
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Iscri uno studente a un corso
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
            <a href="{{ route('admin.enrollments.index') }}" class="text-gray-500 hover:text-gray-700">Iscrizioni</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">Nuova Iscrizione</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <!-- Header con back button -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.enrollments.index') }}"
                           class="inline-flex items-center p-2 text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Nuova Iscrizione</h1>
                            <p class="text-gray-600">Iscri uno studente a un corso disponibile</p>
                        </div>
                    </div>
                </div>

                <!-- Alert Success/Error -->
                <div x-data="{ show: false, message: '', type: 'success' }"
                     @enrollment-created.window="show = true; message = $event.detail.message; type = 'success'; setTimeout(() => show = false, 5000)"
                     @enrollment-error.window="show = true; message = $event.detail.message; type = 'error'; setTimeout(() => show = false, 5000)">
                    <div x-show="show"
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform scale-90"
                         x-transition:enter-end="opacity-100 transform scale-100"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100 transform scale-100"
                         x-transition:leave-end="opacity-0 transform scale-90"
                         :class="type === 'success' ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200'"
                         class="border rounded-lg p-4 flex items-start"
                         style="display: none;">
                        <svg x-show="type === 'success'" class="w-5 h-5 text-green-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <svg x-show="type === 'error'" class="w-5 h-5 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <p :class="type === 'success' ? 'text-green-800' : 'text-red-800'" x-text="message"></p>
                    </div>
                </div>

                <!-- Form -->
                <div class="bg-white rounded-lg shadow" x-data="enrollmentForm()">
                    <form @submit.prevent="submitForm">
                        @csrf

                        <div class="p-6 space-y-6">
                            <!-- Studente -->
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Studente <span class="text-red-500">*</span>
                                </label>
                                <select id="user_id"
                                        x-model="formData.user_id"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    <option value="">-- Seleziona Studente --</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->id }}"
                                                {{ isset($selectedStudentId) && $selectedStudentId == $student->id ? 'selected' : '' }}>
                                            {{ $student->full_name }} ({{ $student->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Corso -->
                            <div>
                                <label for="course_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Corso <span class="text-red-500">*</span>
                                </label>
                                <select id="course_id"
                                        x-model="formData.course_id"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    <option value="">-- Seleziona Corso --</option>
                                    @foreach($courses as $course)
                                        <option value="{{ $course->id }}">
                                            {{ $course->name }} ({{ $course->start_date->format('d/m/Y') }} - {{ $course->end_date->format('d/m/Y') }})
                                        </option>
                                    @endforeach
                                </select>
                                @if($courses->isEmpty())
                                    <p class="mt-2 text-sm text-yellow-600">
                                        ⚠️ Nessun corso disponibile con inizio futuro.
                                        <a href="{{ route('admin.courses.create') }}" class="underline hover:text-yellow-700">Crea un nuovo corso</a>
                                    </p>
                                @endif
                            </div>

                            <!-- Data Iscrizione (opzionale) -->
                            <div>
                                <label for="enrollment_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data Iscrizione
                                </label>
                                <input type="date"
                                       id="enrollment_date"
                                       x-model="formData.enrollment_date"
                                       :value="formData.enrollment_date"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                <p class="mt-1 text-xs text-gray-500">Se non specificata, verrà usata la data odierna</p>
                            </div>

                            <!-- Note (opzionali) -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                    Note
                                </label>
                                <textarea id="notes"
                                          x-model="formData.notes"
                                          rows="3"
                                          maxlength="500"
                                          placeholder="Note aggiuntive sull'iscrizione..."
                                          class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200"></textarea>
                                <p class="mt-1 text-xs text-gray-500">Massimo 500 caratteri</p>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex items-center justify-between rounded-b-lg">
                            <a href="{{ route('admin.enrollments.index') }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all">
                                Annulla
                            </a>
                            <button type="submit"
                                    :disabled="loading"
                                    :class="loading ? 'opacity-50 cursor-not-allowed' : 'hover:from-rose-600 hover:to-purple-700 transform hover:scale-105'"
                                    class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                <svg x-show="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="loading ? 'Creazione in corso...' : 'Crea Iscrizione'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script nonce="@cspNonce">
        function enrollmentForm() {
            return {
                loading: false,
                formData: {
                    user_id: '{{ $selectedStudentId ?? '' }}',
                    course_id: '',
                    enrollment_date: new Date().toISOString().split('T')[0],
                    notes: ''
                },

                async submitForm() {
                    // Validazione base
                    if (!this.formData.user_id || !this.formData.course_id) {
                        window.dispatchEvent(new CustomEvent('enrollment-error', {
                            detail: { message: 'Seleziona uno studente e un corso' }
                        }));
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch('{{ route("admin.enrollments.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.dispatchEvent(new CustomEvent('enrollment-created', {
                                detail: { message: data.message || 'Iscrizione creata con successo!' }
                            }));

                            // Redirect dopo 1.5 secondi
                            setTimeout(() => {
                                window.location.href = '{{ route("admin.enrollments.index") }}';
                            }, 1500);
                        } else {
                            window.dispatchEvent(new CustomEvent('enrollment-error', {
                                detail: { message: data.message || 'Errore durante la creazione dell\'iscrizione' }
                            }));
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        window.dispatchEvent(new CustomEvent('enrollment-error', {
                            detail: { message: 'Errore di connessione. Riprova.' }
                        }));
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>
</x-app-layout>
