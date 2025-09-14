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
                    Evento
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Data & Ora
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Partecipanti
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Registrazioni
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
            @forelse($events as $event)
                <tr class="hover:bg-gray-50" :class="{ 'bg-blue-50': selectedItems.includes({{ $event->id }}) }">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <input type="checkbox"
                               name="event_ids[]"
                               value="{{ $event->id }}"
                               @change="toggleSelection({{ $event->id }}, $event.target.checked)"
                               :checked="selectedItems.includes({{ $event->id }})"
                               class="rounded border-gray-300 text-rose-600 focus:ring-rose-500">
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-12 w-12">
                                <div class="h-12 w-12 bg-gradient-to-r
                                    @if($event->type === 'Saggio') from-purple-400 to-pink-500
                                    @elseif($event->type === 'Workshop') from-blue-400 to-cyan-500
                                    @elseif($event->type === 'Competizione') from-red-400 to-orange-500
                                    @elseif($event->type === 'Masterclass') from-green-400 to-teal-500
                                    @elseif($event->type === 'Festa') from-yellow-400 to-orange-500
                                    @elseif($event->type === 'Esibizione') from-indigo-400 to-purple-500
                                    @else from-gray-400 to-gray-500 @endif
                                    rounded-full flex items-center justify-center text-white font-bold text-sm">
                                    {{ strtoupper(substr($event->name, 0, 2)) }}
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $event->name }}</div>
                                <div class="text-sm text-gray-500 flex items-center space-x-3">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                        @if($event->type === 'Saggio') bg-purple-100 text-purple-800
                                        @elseif($event->type === 'Workshop') bg-blue-100 text-blue-800
                                        @elseif($event->type === 'Competizione') bg-red-100 text-red-800
                                        @elseif($event->type === 'Masterclass') bg-green-100 text-green-800
                                        @elseif($event->type === 'Festa') bg-yellow-100 text-yellow-800
                                        @elseif($event->type === 'Esibizione') bg-indigo-100 text-indigo-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $event->type }}
                                    </span>
                                    @if($event->location)
                                        <span class="text-gray-500 flex items-center">
                                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            {{ Str::limit($event->location, 20) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col space-y-1">
                            @if($event->start_date)
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm font-medium text-gray-900">{{ $event->start_date->format('d/m/Y') }}</span>
                                </div>
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    <span class="text-sm text-gray-600">{{ $event->start_date->format('H:i') }}</span>
                                    @if($event->end_date && $event->start_date->format('Y-m-d') === $event->end_date->format('Y-m-d'))
                                        <span class="text-gray-500 mx-1">-</span>
                                        <span class="text-sm text-gray-600">{{ $event->end_date->format('H:i') }}</span>
                                    @endif
                                </div>
                            @endif
                            @if($event->end_date && $event->start_date->format('Y-m-d') !== $event->end_date->format('Y-m-d'))
                                <div class="flex items-center">
                                    <svg class="w-4 h-4 mr-1 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span class="text-sm text-gray-600">{{ $event->end_date->format('d/m/Y H:i') }}</span>
                                </div>
                            @endif

                            <!-- Event Status Indicator -->
                            @if($event->is_upcoming)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    In arrivo
                                </span>
                            @elseif($event->is_ongoing)
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    In corso
                                </span>
                            @else
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    Passato
                                </span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($event->max_participants)
                            @php
                                $registrationCount = $event->current_registrations_count;
                                $percentage = $event->max_participants > 0 ? ($registrationCount / $event->max_participants) * 100 : 0;
                            @endphp
                            <div class="flex flex-col space-y-1">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">{{ $registrationCount }}/{{ $event->max_participants }}</span>
                                    <span class="text-xs text-gray-500">{{ number_format($percentage, 0) }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full bg-gradient-to-r from-rose-400 to-purple-500"
                                         style="width: {{ min($percentage, 100) }}%"></div>
                                </div>
                                @if($event->available_spots > 0)
                                    <div class="text-xs text-green-600">{{ $event->available_spots }} posti disponibili</div>
                                @elseif($event->is_full)
                                    <div class="text-xs text-red-600">Completo</div>
                                @endif
                            </div>
                        @else
                            <div class="text-sm text-gray-500">
                                <span class="inline-flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Illimitato
                                </span>
                                @if($event->current_registrations_count > 0)
                                    <div class="text-xs text-gray-600 mt-1">{{ $event->current_registrations_count }} iscritti</div>
                                @endif
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($event->requires_registration)
                            <div class="flex flex-col space-y-1">
                                @php
                                    $regStatus = $event->registration_status;
                                @endphp
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                    @if($regStatus === 'open') bg-green-100 text-green-800
                                    @elseif($regStatus === 'full') bg-red-100 text-red-800
                                    @elseif($regStatus === 'closed') bg-gray-100 text-gray-800
                                    @else bg-yellow-100 text-yellow-800 @endif">
                                    @if($regStatus === 'open') Aperte
                                    @elseif($regStatus === 'full') Complete
                                    @elseif($regStatus === 'closed') Chiuse
                                    @else Non richiesto @endif
                                </span>

                                @if($event->registration_deadline)
                                    <div class="text-xs text-gray-500">
                                        Scadenza: {{ $event->registration_deadline->format('d/m/Y') }}
                                    </div>
                                @endif

                                <!-- Waitlist info -->
                                @php
                                    $waitlistCount = $event->registrations()->waitlist()->count();
                                @endphp
                                @if($waitlistCount > 0)
                                    <div class="text-xs text-orange-600">
                                        {{ $waitlistCount }} in lista d'attesa
                                    </div>
                                @endif
                            </div>
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Non richiesta
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        @if($event->price && $event->price > 0)
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-1 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                </svg>
                                <span class="font-medium">€{{ number_format($event->price, 2, ',', '.') }}</span>
                            </div>
                            @php
                                $confirmedRegistrations = $event->registrations()->confirmed()->count();
                                $potentialRevenue = $confirmedRegistrations * $event->price;
                            @endphp
                            @if($potentialRevenue > 0)
                                <div class="text-xs text-gray-500">
                                    Ricavo: €{{ number_format($potentialRevenue, 2, ',', '.') }}
                                </div>
                            @endif
                        @else
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Gratuito
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col space-y-1">
                            <!-- Active/Inactive Status -->
                            <button @click="toggleStatus({{ $event->id }})"
                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium transition-colors duration-200 {{ $event->active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-red-100 text-red-800 hover:bg-red-200' }}">
                                <span class="w-2 h-2 mr-1.5 rounded-full {{ $event->active ? 'bg-green-400' : 'bg-red-400' }}"></span>
                                <span>{{ $event->active ? 'Attivo' : 'Non attivo' }}</span>
                            </button>

                            <!-- Public/Private Status -->
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $event->is_public ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $event->is_public ? 'Pubblico' : 'Privato' }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex items-center justify-end space-x-2">
                            <a href="{{ route('admin.events.show', $event) }}"
                               class="text-rose-600 hover:text-rose-900 p-1 rounded-full hover:bg-rose-100" title="Visualizza dettagli">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </a>
                            <a href="{{ route('admin.events.edit', $event) }}"
                               class="text-blue-600 hover:text-blue-900 p-1 rounded-full hover:bg-blue-100" title="Modifica">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @if($event->requires_registration)
                                <button @click="showRegisterModal({{ $event->id }}, '{{ $event->name }}')"
                                        class="text-purple-600 hover:text-purple-900 p-1 rounded-full hover:bg-purple-100" title="Gestisci Registrazioni">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </button>
                            @endif
                            <button @click="if(confirm('Sei sicuro di voler eliminare questo evento?')) { deleteEvent({{ $event->id }}) }"
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
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun evento trovato</h3>
                            <p class="mt-1 text-sm text-gray-500">Non ci sono eventi che corrispondono ai filtri selezionati.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.events.create') }}"
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Aggiungi primo evento
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
@if($events->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50">
        {{ $events->links() }}
    </div>
@endif

<script>
    // Add delete function to global scope for table actions
    window.deleteEvent = async function(eventId) {
        try {
            const response = await fetch(`/admin/events/${eventId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Find and remove the row
                const row = document.querySelector(`input[value="${eventId}"]`).closest('tr');
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
    window.toggleStatus = async function(eventId) {
        try {
            const response = await fetch(`/admin/events/${eventId}/toggle-active`, {
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

    // Register modal function placeholder
    window.showRegisterModal = function(eventId, eventName) {
        // This would show a modal for managing registrations
        // For now, redirect to event show page where registrations are managed
        window.location.href = `/admin/events/${eventId}`;
    };
</script>