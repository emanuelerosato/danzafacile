<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Modifica Iscrizione
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Modifica i dettagli dell'iscrizione
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
        <li class="text-gray-900 font-medium">Modifica Iscrizione</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">
                <!-- Header con back button -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('admin.enrollments.show', $enrollment) }}"
                           class="inline-flex items-center p-2 text-gray-600 hover:text-gray-900 hover:bg-white rounded-lg transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </a>
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Modifica Iscrizione</h1>
                            <p class="text-gray-600">{{ $enrollment->user->full_name }} - {{ $enrollment->course->name }}</p>
                        </div>
                    </div>
                </div>

                <!-- Alert Success/Error -->
                <div x-data="{ show: false, message: '', type: 'success' }"
                     @enrollment-updated.window="show = true; message = $event.detail.message; type = 'success'; setTimeout(() => show = false, 5000)"
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
                <div class="bg-white rounded-lg shadow" x-data="enrollmentEditForm()">
                    <form @submit.prevent="submitForm">
                        @csrf
                        @method('PUT')

                        <div class="p-6 space-y-6">
                            <!-- Studente (readonly - mostra info) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Studente
                                </label>
                                <div class="px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($enrollment->user->first_name ?? $enrollment->user->full_name, 0, 1) . substr($enrollment->user->last_name ?? '', 0, 1)) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $enrollment->user->full_name }}</p>
                                            <p class="text-xs text-gray-500">{{ $enrollment->user->email }}</p>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Lo studente non può essere modificato. Crea una nuova iscrizione se necessario.</p>
                            </div>

                            <!-- Corso (readonly - mostra info) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Corso
                                </label>
                                <div class="px-4 py-3 bg-gray-50 border border-gray-300 rounded-lg">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-lg flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($enrollment->course->name, 0, 2)) }}
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium text-gray-900">{{ $enrollment->course->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $enrollment->course->start_date->format('d/m/Y') }} - {{ $enrollment->course->end_date->format('d/m/Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Il corso non può essere modificato. Crea una nuova iscrizione se necessario.</p>
                            </div>

                            <!-- Data Iscrizione -->
                            <div>
                                <label for="enrollment_date" class="block text-sm font-medium text-gray-700 mb-2">
                                    Data Iscrizione
                                </label>
                                <input type="date"
                                       id="enrollment_date"
                                       x-model="formData.enrollment_date"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                                    Stato <span class="text-red-500">*</span>
                                </label>
                                <select id="status"
                                        x-model="formData.status"
                                        required
                                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent transition-colors duration-200">
                                    <option value="active">Attivo</option>
                                    <option value="suspended">Sospeso</option>
                                    <option value="cancelled">Cancellato</option>
                                    <option value="completed">Completato</option>
                                </select>
                            </div>

                            <!-- Note -->
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
                            <a href="{{ route('admin.enrollments.show', $enrollment) }}"
                               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all">
                                Annulla
                            </a>
                            <button type="submit"
                                    :disabled="loading"
                                    :class="loading ? 'opacity-50 cursor-not-allowed' : 'hover:from-rose-600 hover:to-purple-700 transform hover:scale-105'"
                                    class="inline-flex items-center px-6 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                <svg x-show="!loading" class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg x-show="loading" class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="loading ? 'Salvataggio...' : 'Salva Modifiche'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script nonce="@cspNonce">
        function enrollmentEditForm() {
            return {
                loading: false,
                formData: {
                    user_id: {{ $enrollment->user_id }},
                    course_id: {{ $enrollment->course_id }},
                    enrollment_date: '{{ $enrollment->enrollment_date ? $enrollment->enrollment_date->format('Y-m-d') : now()->format('Y-m-d') }}',
                    status: '{{ $enrollment->status }}',
                    notes: '{{ $enrollment->notes ?? '' }}'
                },

                async submitForm() {
                    this.loading = true;

                    try {
                        const response = await fetch('{{ route("admin.enrollments.update", $enrollment) }}', {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (response.ok && data.success) {
                            window.dispatchEvent(new CustomEvent('enrollment-updated', {
                                detail: { message: data.message || 'Iscrizione aggiornata con successo!' }
                            }));

                            // Redirect dopo 1.5 secondi
                            setTimeout(() => {
                                window.location.href = '{{ route("admin.enrollments.show", $enrollment) }}';
                            }, 1500);
                        } else {
                            window.dispatchEvent(new CustomEvent('enrollment-error', {
                                detail: { message: data.message || 'Errore durante l\'aggiornamento' }
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
