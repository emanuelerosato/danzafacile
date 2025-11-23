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
                    Corso
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Istruttore
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Iscrizioni
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Prezzo
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
            @forelse($courses as $course)
                <tr class="hover:bg-gray-50" :class="{ 'bg-blue-50': selectedItems.includes({{ $course->id }}) }">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox"
                               name="course_ids[]"
                               value="{{ $course->id }}"
                               @change="toggleSelection({{ $course->id }}, $event.target.checked)"
                               :checked="selectedItems.includes({{ $course->id }})"
                               class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($course->name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $course->name }}</div>
                                <div class="text-sm text-gray-500">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($course->level === 'Principiante') bg-green-100 text-green-800
                                        @elseif($course->level === 'Intermedio') bg-yellow-100 text-yellow-800
                                        @elseif($course->level === 'Avanzato') bg-orange-100 text-orange-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $course->level }}
                                    </span>
                                    @if($course->location)
                                        <span class="ml-2 text-gray-500">{{ $course->location }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($course->instructor)
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-8 w-8">
                                    <div class="h-8 w-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center text-white font-bold text-xs">
                                        {{ strtoupper(substr($course->instructor->first_name ?? $course->instructor->name, 0, 1) . substr($course->instructor->last_name ?? '', 0, 1)) }}
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">{{ $course->instructor->name }}</div>
                                    <div class="text-sm text-gray-500">Istruttore</div>
                                </div>
                            </div>
                        @else
                            <div class="text-sm text-gray-500 italic">Nessun istruttore assegnato</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @php
                            $enrollmentCount = $course->enrollments->count();
                            $availableSpots = max(0, $course->max_students - $enrollmentCount);
                            $enrollmentPercentage = $course->max_students > 0 ? ($enrollmentCount / $course->max_students) * 100 : 0;
                        @endphp
                        <div class="flex flex-col space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-900">{{ $enrollmentCount }}/{{ $course->max_students }}</span>
                                <span class="text-xs text-gray-500">{{ number_format($enrollmentPercentage, 0) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full bg-gradient-to-r from-rose-400 to-purple-500"
                                     style="width: {{ min($enrollmentPercentage, 100) }}%"></div>
                            </div>
                            @if($availableSpots > 0)
                                <div class="text-xs text-green-600">{{ $availableSpots }} posti disponibili</div>
                            @else
                                <div class="text-xs text-red-600">Completo</div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex flex-col space-y-1">
                            @if($course->start_date)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="font-medium">{{ $course->start_date->format('d/m/Y') }}</span>
                                </div>
                            @endif
                            @if($course->end_date)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-gray-600">{{ $course->end_date->format('d/m/Y') }}</span>
                                </div>
                            @endif
                            @if($course->schedule_data && is_array($course->schedule_data) && count($course->schedule_data) > 0)
                                <div class="text-xs text-gray-500">
                                    @foreach($course->schedule_data as $index => $slot)
                                        <div>{{ $slot['day'] ?? 'N/A' }}: {{ $slot['start_time'] ?? 'N/A' }} - {{ $slot['end_time'] ?? 'N/A' }}</div>
                                        @if($index >= 1) @break @endif
                                    @endforeach
                                    @if(count($course->schedule_data) > 2)
                                        <div class="text-gray-400">+{{ count($course->schedule_data) - 2 }} altri...</div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="flex items-center">
                            <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                            </svg>
                            <span class="font-medium">{{ $course->formatted_price }}</span>
                        </div>
                        @php
                            $price = $course->monthly_price ?? $course->price ?? 0;
                            $totalRevenue = $course->enrollments->count() * $price;
                        @endphp
                        @if($totalRevenue > 0)
                            <div class="text-xs text-gray-500">
                                Tot: €{{ number_format($totalRevenue, 2, ',', '.') }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <button @click="toggleStatus({{ $course->id }})"
                                class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors duration-200 {{ $course->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                            <span class="w-2 h-2 mr-2 rounded-full {{ $course->active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                            <span>{{ $course->active ? 'Attivo' : 'Non attivo' }}</span>
                        </button>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('admin.courses.show', $course) }}"
                               class="text-rose-600 hover:text-rose-900 p-1 rounded-full hover:bg-rose-100" title="Visualizza dettagli">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.courses.edit', $course) }}"
                               class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-100" title="Modifica">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.enrollments.index', ['course_id' => $course->id]) }}"
                               class="text-purple-600 hover:text-purple-900 p-1 rounded-full hover:bg-purple-100" title="Gestisci Iscrizioni">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </a>
                            <button @click="if(confirm('Sei sicuro di voler eliminare questo corso?')) { deleteCourse({{ $course->id }}) }"
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
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun corso trovato</h3>
                            <p class="mt-1 text-sm text-gray-500">Non ci sono corsi che corrispondono ai filtri selezionati.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.courses.create') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Aggiungi primo corso
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
@if($courses->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
        {{ $courses->links() }}
    </div>
@endif

<script nonce="@cspNonce">
    // Add delete function to global scope for table actions
    window.deleteCourse = async function(courseId) {
        try {
            const response = await fetch(`/admin/courses/${courseId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Find and remove the row
                const row = document.querySelector(`input[value="${courseId}"]`).closest('tr');
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

    // Add toggle status function
    window.toggleStatus = async function(courseId) {
        try {
            const response = await fetch(`/admin/courses/${courseId}/toggle-active`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Reload the table to show updated status
                if (typeof window.applyFilters === 'function') {
                    window.applyFilters();
                } else {
                    location.reload();
                }

                // Show success message
                const event = new CustomEvent('show-toast', {
                    detail: { message: data.message, type: 'success' }
                });
                window.dispatchEvent(event);
            } else {
                const event = new CustomEvent('show-toast', {
                    detail: { message: data.message || 'Errore durante il cambio di stato', type: 'error' }
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