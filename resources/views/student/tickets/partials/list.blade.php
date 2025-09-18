@foreach($tickets as $ticket)
    <div class="flex items-center justify-between p-4 bg-white rounded-xl border border-gray-200 hover:shadow-md transition-all duration-200">
        <div class="flex items-center space-x-4 flex-1">
            <!-- Status Icon -->
            <div class="w-12 h-12 rounded-lg flex items-center justify-center
                {{ $ticket->status === 'open' ? 'bg-green-100' :
                   ($ticket->status === 'pending' ? 'bg-yellow-100' : 'bg-gray-100') }}">
                @if($ticket->status === 'open')
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                @elseif($ticket->status === 'pending')
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @else
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                @endif
            </div>

            <!-- Ticket Info -->
            <div class="flex-1">
                <div class="flex items-start justify-between">
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-1">{{ $ticket->title }}</h4>
                        <p class="text-sm text-gray-600 mb-2">{{ Str::limit($ticket->description, 100) }}</p>
                        <div class="flex items-center space-x-4 text-xs text-gray-500">
                            <span>{{ $ticket->formatted_created_at }}</span>
                            <span>•</span>
                            <span class="capitalize">{{ ucfirst($ticket->category) }}</span>
                            @if($ticket->responses_count > 0)
                                <span>•</span>
                                <span>{{ $ticket->responses_count }} risposta/e</span>
                            @endif
                        </div>
                    </div>

                    <!-- Status and Priority Badges -->
                    <div class="flex flex-col items-end space-y-2 ml-4">
                        <!-- Status Badge -->
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->status_color }}">
                            {{ ucfirst($ticket->status === 'open' ? 'Aperto' : ($ticket->status === 'pending' ? 'In Attesa' : 'Chiuso')) }}
                        </span>

                        <!-- Priority Badge -->
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticket->priority_color }}">
                            {{ ucfirst($ticket->priority === 'low' ? 'Bassa' :
                               ($ticket->priority === 'medium' ? 'Media' :
                               ($ticket->priority === 'high' ? 'Alta' : 'Critica'))) }}
                        </span>

                        @if($ticket->is_overdue)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                In Ritardo
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="ml-4">
            <a href="{{ route('student.tickets.show', $ticket) }}"
               class="bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors duration-200 flex items-center">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Visualizza
            </a>
        </div>
    </div>
@endforeach