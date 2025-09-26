<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettagli Turno
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $staffSchedule->title }}
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('admin.staff-schedules.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Torna alla Lista
                </a>
                @if($staffSchedule->isEditable())
                    <a href="{{ route('admin.staff-schedules.edit', $staffSchedule) }}"
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200 transform hover:scale-105">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Modifica
                    </a>
                @endif
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
            <a href="{{ route('admin.staff-schedules.index') }}" class="text-gray-500 hover:text-gray-700">Turni Staff</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">{{ $staffSchedule->title }}</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

        <!-- Status Actions -->
        @if($staffSchedule->canBeConfirmed() || $staffSchedule->canBeCompleted() || $staffSchedule->canBeCancelled() || $staffSchedule->status === 'confirmed')
            <div class="bg-white rounded-lg shadow p-6 mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Azioni Turno</h3>
                <div class="flex flex-wrap gap-3">
                    @if($staffSchedule->canBeConfirmed())
                        <form method="POST" action="{{ route('admin.staff-schedules.confirm', $staffSchedule) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Conferma Turno
                            </button>
                        </form>
                    @endif

                    @if($staffSchedule->canBeCompleted())
                        <form method="POST" action="{{ route('admin.staff-schedules.complete', $staffSchedule) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Segna Completato
                            </button>
                        </form>
                    @endif

                    @if($staffSchedule->status === 'confirmed')
                        <form method="POST" action="{{ route('admin.staff-schedules.no-show', $staffSchedule) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition duration-200">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Segna Assente
                            </button>
                        </form>
                    @endif

                    @if($staffSchedule->canBeCancelled())
                        <form method="POST" action="{{ route('admin.staff-schedules.cancel', $staffSchedule) }}" class="inline">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200"
                                    onclick="return confirm('Sei sicuro di voler annullare questo turno?')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                Annulla Turno
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        @endif

        <!-- Main Details -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Informazioni Generali</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Titolo</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $staffSchedule->title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Tipo</label>
                            <p class="text-lg text-gray-900">{{ $staffSchedule->type_label }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Data</label>
                            <p class="text-lg text-gray-900">
                                {{ $staffSchedule->date->format('d/m/Y') }}
                                <span class="text-sm text-gray-500">({{ $staffSchedule->date->translatedFormat('l') }})</span>
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Orario</label>
                            <p class="text-lg text-gray-900">
                                {{ $staffSchedule->start_time->format('H:i') }} - {{ $staffSchedule->end_time->format('H:i') }}
                                <span class="text-sm text-gray-500">({{ $staffSchedule->duration }})</span>
                            </p>
                        </div>

                        @if($staffSchedule->location)
                            <div>
                                <label class="block text-sm font-medium text-gray-600 mb-1">Luogo</label>
                                <p class="text-lg text-gray-900">{{ $staffSchedule->location }}</p>
                            </div>
                        @endif

                        <div>
                            <label class="block text-sm font-medium text-gray-600 mb-1">Stato</label>
                            <div class="mt-1">
                                {!! $staffSchedule->status_badge !!}
                            </div>
                        </div>
                    </div>

                    @if($staffSchedule->description)
                        <div class="mt-6 pt-6 border-t border-gray-200">
                            <label class="block text-sm font-medium text-gray-600 mb-2">Descrizione</label>
                            <p class="text-gray-900 leading-relaxed">{{ $staffSchedule->description }}</p>
                        </div>
                    @endif
                </div>

                <!-- Financial Information -->
                @if($staffSchedule->hourly_rate || $staffSchedule->max_hours)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Informazioni Finanziarie</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            @if($staffSchedule->hourly_rate)
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Tariffa Oraria</label>
                                    <p class="text-xl font-semibold text-green-600">€{{ number_format($staffSchedule->hourly_rate, 2) }}</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Durata (Minuti)</label>
                                    <p class="text-xl font-semibold text-gray-900">{{ $staffSchedule->duration_in_minutes }} min</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Compenso Calcolato</label>
                                    <p class="text-xl font-semibold text-green-600">€{{ number_format($staffSchedule->calculated_pay, 2) }}</p>
                                </div>
                            @endif

                            @if($staffSchedule->max_hours)
                                <div>
                                    <label class="block text-sm font-medium text-gray-600 mb-1">Ore Massime</label>
                                    <p class="text-lg text-gray-900">{{ $staffSchedule->max_hours }} ore</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Requirements -->
                @if($staffSchedule->requirements && count($staffSchedule->requirements) > 0)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Requisiti Specifici</h3>

                        <ul class="space-y-2">
                            @foreach($staffSchedule->requirements as $requirement)
                                <li class="flex items-center">
                                    <svg class="w-5 h-5 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span class="text-gray-900">{{ $requirement }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Notes -->
                @if($staffSchedule->notes)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Note Aggiuntive</h3>
                        <p class="text-gray-900 leading-relaxed">{{ $staffSchedule->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Right Column -->
            <div class="space-y-8">
                <!-- Staff Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Staff Member</h3>

                    <div class="flex items-center space-x-4 mb-6">
                        <div class="w-16 h-16 bg-gradient-to-r from-indigo-400 to-purple-500 rounded-full flex items-center justify-center text-white text-xl font-bold">
                            {{ strtoupper(substr($staffSchedule->staff->first_name ?? 'N', 0, 1)) }}{{ strtoupper(substr($staffSchedule->staff->last_name ?? 'A', 0, 1)) }}
                        </div>
                        <div>
                            <h4 class="text-xl font-semibold text-gray-900">{{ $staffSchedule->staff->full_name ?? 'N/A' }}</h4>
                            <p class="text-gray-600">{{ $staffSchedule->staff->email ?? '' }}</p>
                        </div>
                    </div>

                    @if($staffSchedule->staff->phone)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Telefono</label>
                            <p class="text-gray-900">{{ $staffSchedule->staff->phone }}</p>
                        </div>
                    @endif

                    @if($staffSchedule->staff->specialization)
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-600 mb-1">Specializzazione</label>
                            <p class="text-gray-900">{{ $staffSchedule->staff->specialization }}</p>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-gray-200">
                        <a href="{{ route('admin.staff.show', $staffSchedule->staff) }}"
                           class="inline-flex items-center text-rose-600 hover:text-rose-800 transition duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Visualizza Profilo Staff
                        </a>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Timeline</h3>

                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></div>
                            <div>
                                <p class="text-sm font-medium text-gray-900">Turno Creato</p>
                                <p class="text-xs text-gray-500">{{ $staffSchedule->created_at->format('d/m/Y H:i') }}</p>
                                @if($staffSchedule->creator)
                                    <p class="text-xs text-gray-500">da {{ $staffSchedule->creator->name }}</p>
                                @endif
                            </div>
                        </div>

                        @if($staffSchedule->confirmed_at)
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-green-500 rounded-full mt-2 mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Turno Confermato</p>
                                    <p class="text-xs text-gray-500">{{ $staffSchedule->confirmed_at->format('d/m/Y H:i') }}</p>
                                    @if($staffSchedule->confirmer)
                                        <p class="text-xs text-gray-500">da {{ $staffSchedule->confirmer->name }}</p>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($staffSchedule->status === 'completed')
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-purple-500 rounded-full mt-2 mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Turno Completato</p>
                                    <p class="text-xs text-gray-500">{{ $staffSchedule->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($staffSchedule->status === 'cancelled')
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-red-500 rounded-full mt-2 mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Turno Annullato</p>
                                    <p class="text-xs text-gray-500">{{ $staffSchedule->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($staffSchedule->status === 'no_show')
                            <div class="flex items-start">
                                <div class="w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></div>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">Segnato come Assente</p>
                                    <p class="text-xs text-gray-500">{{ $staffSchedule->updated_at->format('d/m/Y H:i') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Statistiche Rapide</h3>

                    <div class="space-y-4">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Durata Turno</span>
                            <span class="text-sm font-medium text-gray-900">{{ $staffSchedule->duration }}</span>
                        </div>

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Durata in Minuti</span>
                            <span class="text-sm font-medium text-gray-900">{{ $staffSchedule->duration_in_minutes }} min</span>
                        </div>

                        @if($staffSchedule->hourly_rate)
                            <div class="flex justify-between">
                                <span class="text-sm text-gray-600">Compenso Calcolato</span>
                                <span class="text-sm font-medium text-green-600">€{{ number_format($staffSchedule->calculated_pay, 2) }}</span>
                            </div>
                        @endif

                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Giorni dalla Creazione</span>
                            <span class="text-sm font-medium text-gray-900">{{ $staffSchedule->created_at->diffInDays() }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
