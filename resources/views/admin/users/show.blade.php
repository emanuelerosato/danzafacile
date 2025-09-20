<x-app-layout>
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Dettaglio Studente
                </h2>
                <p class="text-sm text-gray-600 mt-1">
                    Profilo completo di {{ $user->name }}
                </p>
            </div>
            <div class="flex flex-col sm:flex-row items-center gap-3 sm:space-x-3 sm:gap-0">
                <a href="{{ route('admin.users.edit', $user) }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Modifica
                </a>
                @if($user->status !== 'active')
                    <form method="POST" action="{{ route('admin.users.activate', $user) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Attiva
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('admin.users.deactivate', $user) }}" class="inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Disattiva
                        </button>
                    </form>
                @endif
            </div>
        </div>

    <x-slot name="breadcrumb">
        <li class="flex items-center">
            <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">Dashboard</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="flex items-center">
            <a href="{{ route('admin.users.index') }}" class="text-gray-500 hover:text-gray-700">Studenti</a>
            <svg class="w-4 h-4 mx-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </li>
        <li class="text-gray-900 font-medium">{{ $user->name }}</li>

    <div class="space-y-6">
        <!-- Profile Header -->
        <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 overflow-hidden">
            <div class="px-6 py-8">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($user->profile_image)
                            <img class="h-20 w-20 rounded-full object-cover ring-4 ring-white shadow-lg" 
                                 src="{{ Storage::url($user->profile_image) }}" 
                                 alt="{{ $user->name }}">
                        @else
                            <div class="h-20 w-20 bg-gradient-to-r from-rose-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold text-2xl ring-4 ring-white shadow-lg">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="ml-6">
                        <h1 class="text-xl md:text-2xl font-bold text-gray-900">{{ $user->name }}</h1>
                        <p class="text-lg text-gray-600 mt-1">{{ $user->email }}</p>
                        <div class="flex items-center space-x-4 mt-3">
                            @switch($user->status ?? 'active')
                                @case('active')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        Attivo
                                    </span>
                                    @break
                                @case('inactive')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        Non attivo
                                    </span>
                                    @break
                                @case('suspended')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <svg class="w-3 h-3 mr-1.5" fill="currentColor" viewBox="0 0 8 8">
                                            <circle cx="4" cy="4" r="3"/>
                                        </svg>
                                        Sospeso
                                    </span>
                                    @break
                            @endswitch
                            <span class="text-gray-500 text-sm">
                                Iscritto {{ $user->created_at ? $user->created_at->diffForHumans() : 'da tempo' }}
                            </span>
                        </div>
                    </div>
                    <div class="ml-auto">
                        <div class="text-right">
                            <div class="text-sm text-gray-500">ID Studente</div>
                            <div class="text-lg font-mono text-gray-900">#{{ str_pad($user->id, 5, '0', STR_PAD_LEFT) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Personal Information -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informazioni Personali</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nome Completo</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->name ?? 'N/D' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->email ?? 'N/D' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Telefono</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->phone ?? 'N/D' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Data di Nascita</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $user->date_of_birth ? $user->date_of_birth->format('d/m/Y') : 'N/D' }}
                                @if($user->date_of_birth)
                                    <span class="text-gray-500 ml-2">({{ $user->date_of_birth->age }} anni)</span>
                                @endif
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Indirizzo</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->address ?? 'N/D' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Città</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->city ?? 'N/D' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Codice Fiscale</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $user->tax_code ?? 'N/D' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contatto di Emergenza</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->emergency_contact ?? 'N/D' }}</dd>
                        </div>
                    </div>
                    
                    @if($user->bio)
                        <div class="mt-6">
                            <dt class="text-sm font-medium text-gray-500">Note/Biografia</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $user->bio }}</dd>
                        </div>
                    @endif
                </div>

                <!-- Active Courses -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Corsi Attivi</h3>
                        <span class="bg-rose-100 text-rose-800 text-xs font-medium px-2.5 py-1 rounded-full">
                            {{ count($user->activeEnrollments ?? []) }} corsi
                        </span>
                    </div>
                    
                    @forelse($user->activeEnrollments ?? [] as $enrollment)
                        <div class="border border-gray-200 rounded-lg p-4 mb-4 last:mb-0 hover:shadow-md transition-shadow duration-200">
                            <div class="flex items-center justify-between">
                                <div class="flex-1">
                                    <h4 class="font-semibold text-gray-900">{{ $enrollment->course->title ?? 'Corso N/D' }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">
                                        Istruttore: {{ $enrollment->course->instructor ?? 'N/D' }}
                                    </p>
                                    <p class="text-sm text-gray-500 mt-1">
                                        Iscritto il {{ $enrollment->created_at ? $enrollment->created_at->format('d/m/Y') : 'N/D' }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <div class="text-lg font-bold text-gray-900">
                                        €{{ number_format($enrollment->course->price ?? 0, 2) }}
                                    </div>
                                    <div class="text-sm text-gray-500">mensile</div>
                                </div>
                            </div>
                            
                            @if($enrollment->course->schedule_data && is_array($enrollment->course->schedule_data) && count($enrollment->course->schedule_data) > 0)
                                <div class="mt-3 pt-3 border-t border-gray-100">
                                    <div class="text-sm text-gray-600">
                                        <div class="flex items-center mb-2">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span class="font-medium">Orari:</span>
                                        </div>
                                        @foreach($enrollment->course->schedule_data as $slot)
                                            <div class="ml-6 text-xs">
                                                {{ $slot['day'] ?? 'N/A' }}: {{ $slot['start_time'] ?? 'N/A' }} - {{ $slot['end_time'] ?? 'N/A' }}
                                                @if(isset($slot['location']))
                                                    ({{ $slot['location'] }})
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun corso attivo</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Lo studente non è attualmente iscritto a nessun corso.
                            </p>
                        </div>
                    @endforelse
                </div>

                <!-- Payment History -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Storico Pagamenti</h3>
                        <span class="text-sm text-gray-500">
                            Totale: €{{ number_format($user->payments->sum('amount') ?? 0, 2) }}
                        </span>
                    </div>
                    
                    @forelse($user->payments ?? [] as $payment)
                        <div class="flex items-center justify-between p-4 border border-gray-200 rounded-lg mb-3 last:mb-0">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @switch($payment->status ?? 'pending')
                                        @case('completed')
                                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </div>
                                            @break
                                        @case('pending')
                                            <div class="w-8 h-8 bg-orange-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </div>
                                            @break
                                        @case('failed')
                                            <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                            </div>
                                            @break
                                    @endswitch
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $payment->description ?? 'Pagamento corso' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $payment->created_at ? $payment->created_at->format('d/m/Y H:i') : 'N/D' }}
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-lg font-semibold text-gray-900">
                                    €{{ number_format($payment->amount ?? 0, 2) }}
                                </div>
                                @switch($payment->status ?? 'pending')
                                    @case('completed')
                                        <div class="text-xs text-green-600">Completato</div>
                                        @break
                                    @case('pending')
                                        <div class="text-xs text-orange-600">In attesa</div>
                                        @break
                                    @case('failed')
                                        <div class="text-xs text-red-600">Fallito</div>
                                        @break
                                @endswitch
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun pagamento</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Non ci sono pagamenti registrati per questo studente.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Right Column - Quick Stats & Documents -->
            <div class="space-y-6">
                <!-- Quick Stats -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistiche</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Corsi attivi</span>
                            <span class="text-lg font-semibold text-gray-900">
                                {{ count($user->activeEnrollments ?? []) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Totale pagamenti</span>
                            <span class="text-lg font-semibold text-gray-900">
                                €{{ number_format($user->payments->sum('amount') ?? 0, 2) }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Ultimo accesso</span>
                            <span class="text-sm text-gray-900">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Mai' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Documenti caricati</span>
                            <span class="text-lg font-semibold text-gray-900">
                                {{ count($user->documents ?? []) }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Documenti</h3>
                        <button @click="$dispatch('open-modal', 'upload-document')" 
                                class="text-sm text-rose-600 hover:text-rose-800 font-medium">
                            + Aggiungi
                        </button>
                    </div>
                    
                    @forelse($user->documents ?? [] as $document)
                        <div class="flex items-center justify-between p-3 border border-gray-200 rounded-lg mb-3 last:mb-0 hover:bg-gray-50 transition-colors duration-200">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $document->name ?? 'Documento' }}
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        {{ $document->created_at ? $document->created_at->format('d/m/Y') : 'N/D' }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <a href="{{ route('admin.documents.download', $document) }}" 
                                   class="text-rose-600 hover:text-rose-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </a>
                                <button onclick="deleteDocument({{ $document->id }})" 
                                        class="text-red-600 hover:text-red-800">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun documento</h3>
                            <p class="mt-1 text-sm text-gray-500">
                                Lo studente non ha caricato alcun documento.
                            </p>
                        </div>
                    @endforelse
                </div>

                <!-- Medical Information -->
                @if($user->medical_info || $user->allergies || $user->medications)
                    <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informazioni Mediche</h3>
                        <div class="space-y-4 text-sm">
                            @if($user->medical_info)
                                <div>
                                    <dt class="font-medium text-gray-700">Condizioni mediche</dt>
                                    <dd class="mt-1 text-gray-600">{{ $user->medical_info }}</dd>
                                </div>
                            @endif
                            @if($user->allergies)
                                <div>
                                    <dt class="font-medium text-gray-700">Allergie</dt>
                                    <dd class="mt-1 text-gray-600">{{ $user->allergies }}</dd>
                                </div>
                            @endif
                            @if($user->medications)
                                <div>
                                    <dt class="font-medium text-gray-700">Farmaci</dt>
                                    <dd class="mt-1 text-gray-600">{{ $user->medications }}</dd>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Course History -->
        @if($user->enrollmentHistory && $user->enrollmentHistory->count() > 0)
            <div class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg border border-white/20 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Storico Corsi</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Corso
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Periodo
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Stato
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pagamento
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($user->enrollmentHistory as $enrollment)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $enrollment->course->title ?? 'N/D' }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $enrollment->course->instructor ?? 'N/D' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $enrollment->created_at ? $enrollment->created_at->format('d/m/Y') : 'N/D' }}
                                        @if($enrollment->ended_at)
                                            - {{ $enrollment->ended_at->format('d/m/Y') }}
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @switch($enrollment->status ?? 'active')
                                            @case('active')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Attivo
                                                </span>
                                                @break
                                            @case('completed')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Completato
                                                </span>
                                                @break
                                            @case('cancelled')
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                    Annullato
                                                </span>
                                                @break
                                        @endswitch
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        €{{ number_format($enrollment->amount_paid ?? 0, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>

    <!-- Upload Document Modal -->
    <x-modal name="upload-document" maxWidth="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Carica Documento</h3>
                <button @click="$dispatch('close-modal', 'upload-document')" 
                        class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('admin.users.documents.store', $user) }}" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label for="document_name" class="block text-sm font-medium text-gray-700">Nome documento</label>
                        <input type="text" 
                               name="name" 
                               id="document_name"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                               placeholder="Es. Certificato medico"
                               required>
                    </div>
                    
                    <div>
                        <label for="document_file" class="block text-sm font-medium text-gray-700">File</label>
                        <input type="file" 
                               name="file" 
                               id="document_file"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                               class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                               required>
                        <p class="mt-1 text-sm text-gray-500">
                            Formati supportati: PDF, DOC, DOCX, JPG, PNG (max 5MB)
                        </p>
                    </div>
                    
                    <div>
                        <label for="document_description" class="block text-sm font-medium text-gray-700">Descrizione (opzionale)</label>
                        <textarea name="description" 
                                  id="document_description"
                                  rows="3"
                                  class="mt-1 block w-full border-gray-300 rounded-lg focus:ring-rose-500 focus:border-rose-500"
                                  placeholder="Descrizione del documento..."></textarea>
                    </div>
                    
                    <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                        <button type="button" 
                                @click="$dispatch('close-modal', 'upload-document')"
                                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                            Annulla
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-rose-500 to-purple-600 border border-transparent rounded-lg hover:from-rose-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2">
                            Carica Documento
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </x-modal>

    @push('scripts')
    <script>
        function deleteDocument(documentId) {
            if (confirm('Sei sicuro di voler eliminare questo documento? Questa azione non può essere annullata.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/documents/${documentId}`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form);
            }
        }
    </script>
    @endpush
</x-app-layout>
