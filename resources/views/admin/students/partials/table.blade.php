<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left">
                    <input type="checkbox"
                           @change="toggleAll($event.target.checked)"
                           :checked="allSelected"
                           class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Studente
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Contatti
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Iscrizioni
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Pagamenti
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Stato
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Azioni
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($students as $student)
                <tr class="hover:bg-gray-50" :class="{ 'bg-blue-50': selectedItems.includes({{ $student->id }}) }">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox"
                               name="student_ids[]"
                               value="{{ $student->id }}"
                               @change="toggleSelection({{ $student->id }}, $event.target.checked)"
                               :checked="selectedItems.includes({{ $student->id }})"
                               class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                                    {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $student->name }}</div>
                                <div class="text-sm text-gray-500">
                                    @if($student->date_of_birth)
                                        {{ $student->date_of_birth->age }} anni
                                        ({{ $student->date_of_birth->format('d/m/Y') }})
                                    @else
                                        Età non specificata
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            <div class="flex items-center mb-1">
                                <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <a href="mailto:{{ $student->email }}" class="text-rose-600 hover:text-rose-700">
                                    {{ $student->email }}
                                </a>
                            </div>
                            @if($student->phone)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    <a href="tel:{{ $student->phone }}" class="text-gray-700 hover:text-gray-900">
                                        {{ $student->phone }}
                                    </a>
                                </div>
                            @else
                                <div class="text-xs text-gray-400">Telefono non disponibile</div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @php
                            $enrollmentCount = $student->enrollments->count();
                            $activeEnrollments = $student->enrollments()->whereIn('status', ['active', 'enrolled'])->count();
                        @endphp
                        <div class="flex flex-col space-y-1">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span class="font-medium">{{ $activeEnrollments }}</span>
                                <span class="text-gray-500 ml-1">attive</span>
                            </div>
                            @if($enrollmentCount > $activeEnrollments)
                                <div class="text-xs text-gray-500">
                                    +{{ $enrollmentCount - $activeEnrollments }} completate/cancellate
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @php
                            $totalPayments = $student->payments->sum('amount');
                            $pendingPayments = $student->payments()->where('status', 'pending')->sum('amount');
                        @endphp
                        <div class="flex flex-col space-y-1">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-medium">€ {{ number_format($totalPayments, 2, ',', '.') }}</span>
                            </div>
                            @if($pendingPayments > 0)
                                <div class="flex items-center text-orange-600">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01"/>
                                    </svg>
                                    <span class="text-xs">€ {{ number_format($pendingPayments, 2, ',', '.') }} in sospeso</span>
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button @click="toggleStatus({{ $student->id }})"
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors duration-200 {{ $student->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                            <span class="w-2 h-2 mr-2 rounded-full {{ $student->active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                            <span>{{ $student->active ? 'Attivo' : 'Non attivo' }}</span>
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('admin.students.show', $student) }}"
                               class="text-rose-600 hover:text-rose-900 p-1 rounded-full hover:bg-rose-100" title="Visualizza dettagli">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.students.edit', $student) }}"
                               class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-100" title="Modifica">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.enrollments.index', ['student_id' => $student->id]) }}"
                               class="text-purple-600 hover:text-purple-900 p-1 rounded-full hover:bg-purple-100" title="Gestisci Iscrizioni">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </a>
                            <button @click="if(confirm('Sei sicuro di voler eliminare questo studente?')) { deleteStudent({{ $student->id }}) }"
                                    class="text-red-600 hover:text-red-900 p-1 rounded-full hover:bg-red-100" title="Elimina">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-.5a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun studente trovato</h3>
                            <p class="mt-1 text-sm text-gray-500">Non ci sono studenti che corrispondono ai filtri selezionati.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.students.create') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Aggiungi primo studente
                                </a>
                            </div>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($students->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
        {{ $students->links() }}
    </div>
@endif

<script nonce="@cspNonce">
    // Add delete function to global scope for table actions
    window.deleteStudent = async function(studentId) {
        try {
            const response = await fetch(`/admin/students/${studentId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Find and remove the row
                const row = document.querySelector(`input[value="${studentId}"]`).closest('tr');
                row.remove();

                // Show success message
                const event = new CustomEvent('show-toast', {
                    detail: { message: data.message, type: 'success' }
                });
                window.dispatchEvent(event);
            } else {
                const event = new CustomEvent('show-toast', {
                    detail: { message: data.message || 'Errore durante l\'eliminazione', type: 'error' }
                });
                window.dispatchEvent(event);
            }
        } catch (error) {
            console.error('Error:', error);
            const event = new CustomEvent('show-toast', {
                detail: { message: 'Errore di connessione', type: 'error' }
            });
            window.dispatchEvent(event);
        }
    };
</script>