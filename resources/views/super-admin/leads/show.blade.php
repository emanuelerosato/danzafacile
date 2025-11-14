<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettaglio Lead #{{ $lead->id }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    {{ $lead->name }} - {{ $lead->email }}
                </p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('super-admin.leads.index') }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    Torna alla lista
                </a>
            </div>
        </div>
    </x-slot>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('super-admin.dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('super-admin.leads.index') }}" class="text-gray-500 hover:text-gray-700">Lead</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">#{{ $lead->id }}</li>
    </x-slot>

    <div class="min-h-screen bg-gradient-to-br from-rose-50 via-pink-50 to-purple-50 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="space-y-6">

                {{-- Alert successo --}}
                @if(session('success'))
                <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-lg flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    {{ session('success') }}
                </div>
                @endif

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Colonna Sinistra - Dettagli Lead --}}
                    <div class="lg:col-span-2 space-y-6">

                        {{-- Informazioni Personali --}}
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex items-center justify-between mb-6">
                                <h3 class="text-lg font-semibold text-gray-900">Informazioni Lead</h3>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($lead->status_color === 'blue') bg-blue-100 text-blue-800
                                    @elseif($lead->status_color === 'yellow') bg-yellow-100 text-yellow-800
                                    @elseif($lead->status_color === 'purple') bg-purple-100 text-purple-800
                                    @elseif($lead->status_color === 'green') bg-green-100 text-green-800
                                    @elseif($lead->status_color === 'red') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ $lead->status_label }}
                                </span>
                            </div>

                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="w-12 h-12 bg-gradient-to-r from-rose-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0">
                                        {{ strtoupper(substr($lead->name, 0, 2)) }}
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-medium text-gray-600">Nome Completo</p>
                                        <p class="text-lg font-semibold text-gray-900">{{ $lead->name }}</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                            </svg>
                                            <p class="text-sm font-medium text-gray-600">Email</p>
                                        </div>
                                        <a href="mailto:{{ $lead->email }}" class="text-sm text-rose-600 hover:text-rose-700 font-medium">
                                            {{ $lead->email }}
                                        </a>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                            </svg>
                                            <p class="text-sm font-medium text-gray-600">Telefono</p>
                                        </div>
                                        <a href="tel:{{ $lead->phone }}" class="text-sm text-rose-600 hover:text-rose-700 font-medium">
                                            {{ $lead->phone }}
                                        </a>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                            </svg>
                                            <p class="text-sm font-medium text-gray-600">Nome Scuola</p>
                                        </div>
                                        <p class="text-sm text-gray-900 font-medium">
                                            {{ $lead->school_name ?? 'Non specificata' }}
                                        </p>
                                    </div>

                                    <div class="bg-gray-50 rounded-lg p-4">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-5 h-5 text-gray-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            <p class="text-sm font-medium text-gray-600">N. Studenti</p>
                                        </div>
                                        <p class="text-sm text-gray-900 font-medium">
                                            {{ $lead->students_count ?? 'Non specificato' }}
                                        </p>
                                    </div>
                                </div>

                                @if($lead->message)
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <div class="flex items-start">
                                        <svg class="w-5 h-5 text-blue-600 mr-2 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                        </svg>
                                        <div class="flex-1">
                                            <p class="text-sm font-medium text-blue-900 mb-1">Messaggio</p>
                                            <p class="text-sm text-blue-800">{{ $lead->message }}</p>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Timeline / Informazioni Tecniche --}}
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline & Dettagli Tecnici</h3>

                            <div class="space-y-4">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-medium text-gray-600">Richiesta Ricevuta</p>
                                        <p class="text-sm text-gray-900 font-semibold">{{ $lead->created_at->format('d/m/Y H:i:s') }}</p>
                                        <p class="text-xs text-gray-500">{{ $lead->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>

                                @if($lead->contacted_at)
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-medium text-gray-600">Primo Contatto</p>
                                        <p class="text-sm text-gray-900 font-semibold">{{ $lead->contacted_at->format('d/m/Y H:i:s') }}</p>
                                        <p class="text-xs text-gray-500">{{ $lead->contacted_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @endif

                                @if($lead->demo_sent_at)
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4 flex-1">
                                        <p class="text-sm font-medium text-gray-600">Demo Inviata</p>
                                        <p class="text-sm text-gray-900 font-semibold">{{ $lead->demo_sent_at->format('d/m/Y H:i:s') }}</p>
                                        <p class="text-xs text-gray-500">{{ $lead->demo_sent_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                @endif

                                <div class="border-t border-gray-200 pt-4 mt-4">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <p class="text-gray-600">IP Address</p>
                                            <p class="text-gray-900 font-mono text-xs">{{ $lead->ip_address ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-gray-600">User Agent</p>
                                            <p class="text-gray-900 font-mono text-xs truncate" title="{{ $lead->user_agent }}">
                                                {{ $lead->user_agent ? Str::limit($lead->user_agent, 30) : 'N/A' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Colonna Destra - Gestione Status & Note --}}
                    <div class="space-y-6">

                        {{-- Form Aggiornamento Status --}}
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Gestione Lead</h3>

                            <form method="POST" action="{{ route('super-admin.leads.update', $lead) }}" class="space-y-4">
                                @csrf
                                @method('PUT')

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <select name="status" required
                                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent">
                                        <option value="nuovo" {{ $lead->status === 'nuovo' ? 'selected' : '' }}>Nuovo</option>
                                        <option value="contattato" {{ $lead->status === 'contattato' ? 'selected' : '' }}>Contattato</option>
                                        <option value="demo_inviata" {{ $lead->status === 'demo_inviata' ? 'selected' : '' }}>Demo Inviata</option>
                                        <option value="interessato" {{ $lead->status === 'interessato' ? 'selected' : '' }}>Interessato</option>
                                        <option value="chiuso_vinto" {{ $lead->status === 'chiuso_vinto' ? 'selected' : '' }}>Chiuso Vinto</option>
                                        <option value="chiuso_perso" {{ $lead->status === 'chiuso_perso' ? 'selected' : '' }}>Chiuso Perso</option>
                                    </select>
                                    @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Note Interne
                                    </label>
                                    <textarea name="notes"
                                              rows="6"
                                              placeholder="Aggiungi note o commenti su questo lead..."
                                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-rose-500 focus:border-transparent resize-none">{{ old('notes', $lead->notes) }}</textarea>
                                    @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <button type="submit"
                                        class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-rose-500 to-purple-600 text-white text-sm font-medium rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 transition-all duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Salva Modifiche
                                </button>
                            </form>
                        </div>

                        {{-- Azioni Rapide --}}
                        <div class="bg-white rounded-lg shadow p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Azioni Rapide</h3>

                            <div class="space-y-2">
                                <a href="mailto:{{ $lead->email }}"
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Invia Email
                                </a>

                                <a href="tel:{{ $lead->phone }}"
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors duration-200">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    Chiama Ora
                                </a>

                                <form method="POST" action="{{ route('super-admin.leads.destroy', $lead) }}"
                                      onsubmit="return confirm('Sei sicuro di voler eliminare questo lead? Questa azione non può essere annullata.');"
                                      class="mt-4 pt-4 border-t border-gray-200">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors duration-200">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Elimina Lead
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
